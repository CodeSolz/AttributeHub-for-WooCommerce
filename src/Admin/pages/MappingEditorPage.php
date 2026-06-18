<?php
/**
 * AttributeHub — Mapping Editor Page
 *
 * @package AttributeHub\Free\Admin\pages
 */

namespace AttributeHub\Free\Admin\pages;

use AttributeHub\Free\Database\MasterGroupRepository;
use AttributeHub\Free\Database\ValueMappingRepository;
use AttributeHub\Free\Database\ScanResultRepository;
use AttributeHub\Free\Scanner\AttributeScanner;

defined( 'ABSPATH' ) || exit;

/**
 * Renders the two-panel Mapping Editor and handles map/unmap AJAX calls.
 */
class MappingEditorPage {

	public static function render(): void {
		$page = new self();
		$page->output();
	}

	public function output(): void {
		$scanner      = AttributeScanner::instance();
		$master_repo  = new MasterGroupRepository();
		$mapping_repo = new ValueMappingRepository();

		$taxonomies = $scanner->get_all_attribute_taxonomies();
		$taxonomy   = sanitize_key( wp_unslash( $_GET['taxonomy'] ?? '' ) );
		$active_tax = in_array( $taxonomy, $taxonomies, true ) ? $taxonomy : ( $taxonomies[0] ?? '' );

		$masters        = $active_tax ? $master_repo->find_all_by_taxonomy( $active_tax, true ) : array();
		$unmapped_terms = $active_tax ? $mapping_repo->get_unmapped_terms( $active_tax ) : array();

		// Augment masters with their mapped raw terms
		foreach ( $masters as &$master ) {
			$term_ids     = $mapping_repo->get_all_term_ids_for_master( (int) $master->id, $active_tax );
			$mapped_terms = array();

			foreach ( $term_ids as $term_id ) {
				$term = get_term( $term_id, $active_tax );
				if ( $term && ! is_wp_error( $term ) ) {
					$mapped_terms[] = array(
						'term_id' => $term_id,
						'name'    => $term->name,
						'slug'    => $term->slug,
						'count'   => $term->count,
					);
				}
			}

			$master->mapped_terms = $mapped_terms;
		}
		unset( $master );

		\AttributeHub\Free\Util\TemplateLoader::load(
			'admin/mapping-editor.php',
			array(
				'taxonomies'     => $taxonomies,
				'active_tax'     => $active_tax,
				'masters'        => $masters,
				'unmapped_terms' => $unmapped_terms,
			)
		);
	}

	/**
	 * AJAX: Maps a term to a master group (creates master first if new_label is given).
	 */
	public function map_term(): void {
		check_ajax_referer( 'attributehub_admin_nonce', 'attributehub_nonce' );
		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			wp_send_json_error( array( 'message' => __( 'Insufficient permissions.', 'attributehub-for-woocommerce' ) ), 403 );
			wp_die();
		}

		$term_id         = absint( wp_unslash( $_POST['term_id'] ?? 0 ) );
		$taxonomy        = sanitize_key( wp_unslash( $_POST['taxonomy'] ?? '' ) );
		$master_group_id = absint( wp_unslash( $_POST['master_group_id'] ?? 0 ) );
		$new_label       = sanitize_text_field( wp_unslash( $_POST['new_label'] ?? '' ) );

		if ( ! $term_id || ! $taxonomy ) {
			wp_send_json_error( array( 'message' => __( 'Invalid mapping data.', 'attributehub-for-woocommerce' ) ) );
			return;
		}

		// If a new label was provided, create the master group first
		if ( ! $master_group_id && $new_label ) {
			$master_repo = new MasterGroupRepository();
			$validator   = new \AttributeHub\Free\Mapping\MappingValidator();
			$slug        = $validator->suggest_safe_slug( sanitize_title( $new_label ), $taxonomy );
			$master_group_id = (int) $master_repo->create( $new_label, $taxonomy, array( 'slug' => $slug ) );
		}

		if ( ! $master_group_id ) {
			wp_send_json_error( array( 'message' => __( 'Invalid mapping data.', 'attributehub-for-woocommerce' ) ) );
			return;
		}

		$mapping_repo = new ValueMappingRepository();
		$result       = $mapping_repo->map( $term_id, $taxonomy, $master_group_id, 'manual' );

		if ( false === $result ) {
			wp_send_json_error( array( 'message' => __( 'Failed to save mapping.', 'attributehub-for-woocommerce' ) ) );
			return;
		}

		// Update scan results to mark this term as mapped
		$scan_repo = new ScanResultRepository();
		$scan_repo->mark_as_mapped( $term_id, $taxonomy );

		wp_send_json_success( array( 'message' => __( 'Mapping saved.', 'attributehub-for-woocommerce' ) ) );
	}

	/**
	 * AJAX: Removes a term mapping.
	 */
	public function unmap_term(): void {
		check_ajax_referer( 'attributehub_admin_nonce', 'attributehub_nonce' );
		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			wp_send_json_error( array( 'message' => __( 'Insufficient permissions.', 'attributehub-for-woocommerce' ) ), 403 );
			wp_die();
		}

		$term_id  = absint( wp_unslash( $_POST['term_id'] ?? 0 ) );
		$taxonomy = sanitize_key( wp_unslash( $_POST['taxonomy'] ?? '' ) );

		if ( ! $term_id || ! $taxonomy ) {
			wp_send_json_error( array( 'message' => __( 'Invalid data.', 'attributehub-for-woocommerce' ) ) );
			return;
		}

		$mapping_repo = new ValueMappingRepository();
		$result       = $mapping_repo->unmap( $term_id, $taxonomy );

		if ( ! $result ) {
			wp_send_json_error( array( 'message' => __( 'Failed to remove mapping.', 'attributehub-for-woocommerce' ) ) );
			return;
		}

		wp_send_json_success( array( 'message' => __( 'Mapping removed.', 'attributehub-for-woocommerce' ) ) );
	}

	/**
	 * AJAX: Bulk maps multiple terms to a master group.
	 */
	public function bulk_map(): void {
		check_ajax_referer( 'attributehub_admin_nonce', 'attributehub_nonce' );
		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			wp_send_json_error( array( 'message' => __( 'Insufficient permissions.', 'attributehub-for-woocommerce' ) ), 403 );
			wp_die();
		}

		$term_ids        = array_map( 'absint', (array) wp_unslash( $_POST['term_ids'] ?? array() ) );
		$taxonomy        = sanitize_key( wp_unslash( $_POST['taxonomy'] ?? '' ) );
		$master_group_id = absint( wp_unslash( $_POST['master_group_id'] ?? 0 ) );

		if ( empty( $term_ids ) || ! $taxonomy || ! $master_group_id ) {
			wp_send_json_error( array( 'message' => __( 'Invalid bulk mapping data.', 'attributehub-for-woocommerce' ) ) );
			return;
		}

		$mappings = array_map( fn( $id ) => array(
			'term_id'         => $id,
			'taxonomy'        => $taxonomy,
			'master_group_id' => $master_group_id,
			'mapped_by'       => 'manual',
		), $term_ids );

		$mapping_repo = new ValueMappingRepository();
		$count        = $mapping_repo->bulk_map( $mappings );

		wp_send_json_success( array(
			'count'   => $count,
			'message' => sprintf(
				/* translators: %d = number of terms mapped */
				_n( '%d term mapped.', '%d terms mapped.', $count, 'attributehub-for-woocommerce' ),
				$count
			),
		) );
	}

	/**
	 * AJAX: Removes all term mappings for a master group.
	 */
	public function unmap_all(): void {
		check_ajax_referer( 'attributehub_admin_nonce', 'attributehub_nonce' );
		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			wp_send_json_error( array( 'message' => __( 'Insufficient permissions.', 'attributehub-for-woocommerce' ) ), 403 );
			wp_die();
		}

		$master_id = absint( wp_unslash( $_POST['master_id'] ?? 0 ) );
		$taxonomy  = sanitize_key( wp_unslash( $_POST['taxonomy'] ?? '' ) );

		if ( ! $master_id || ! $taxonomy ) {
			wp_send_json_error( array( 'message' => __( 'Invalid data.', 'attributehub-for-woocommerce' ) ) );
			return;
		}

		$mapping_repo = new ValueMappingRepository();
		$mapping_repo->delete_by_master( $master_id, $taxonomy );

		wp_send_json_success( array( 'message' => __( 'All mappings removed.', 'attributehub-for-woocommerce' ) ) );
	}

	/**
	 * AJAX: Gets the master groups list for a taxonomy (for modal/dropdowns).
	 */
	public function get_masters(): void {
		check_ajax_referer( 'attributehub_admin_nonce', 'attributehub_nonce' );
		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			wp_send_json_error( array( 'message' => __( 'Insufficient permissions.', 'attributehub-for-woocommerce' ) ), 403 );
			wp_die();
		}

		$taxonomy    = sanitize_key( wp_unslash( $_POST['taxonomy'] ?? '' ) );
		$master_repo = new MasterGroupRepository();
		$masters     = $master_repo->find_all_by_taxonomy( $taxonomy );

		wp_send_json_success( $masters );
	}

	/**
	 * AJAX: Gets unmapped terms for a taxonomy (for left panel refresh).
	 */
	public function get_unmapped(): void {
		check_ajax_referer( 'attributehub_admin_nonce', 'attributehub_nonce' );
		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			wp_send_json_error( array( 'message' => __( 'Insufficient permissions.', 'attributehub-for-woocommerce' ) ), 403 );
			wp_die();
		}

		$taxonomy     = sanitize_key( wp_unslash( $_POST['taxonomy'] ?? '' ) );
		$mapping_repo = new ValueMappingRepository();
		$terms        = $mapping_repo->get_unmapped_terms( $taxonomy );

		wp_send_json_success( array( 'terms' => $terms ) );
	}

	/**
	 * AJAX: Triggers CSV export download.
	 */
	public function export_csv(): void {
		check_ajax_referer( 'attributehub_admin_nonce', 'attributehub_nonce' );
		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			wp_send_json_error( array( 'message' => __( 'Insufficient permissions.', 'attributehub-for-woocommerce' ) ), 403 );
			wp_die();
		}

		// Taxonomy arrives via GET (browser navigation download), so use $_REQUEST.
		$taxonomy = sanitize_key( wp_unslash( $_REQUEST['taxonomy'] ?? '' ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- nonce already verified above
		$imex     = new \AttributeHub\Free\Mapping\MappingImportExport();
		$imex->stream_export_download( $taxonomy );
	}
}
