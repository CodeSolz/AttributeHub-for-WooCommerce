<?php
/**
 * AttributeHub — Master Directory Page
 *
 * @package AttributeHub\Free\Admin\pages
 */

namespace AttributeHub\Free\Admin\pages;

use AttributeHub\Free\Database\MasterGroupRepository;
use AttributeHub\Free\Database\ValueMappingRepository;
use AttributeHub\Free\Mapping\MappingValidator;
use AttributeHub\Free\Scanner\AttributeScanner;

defined( 'ABSPATH' ) || exit;

/**
 * Renders the Master Attribute Directory admin page and handles its AJAX calls.
 */
class MasterDirectoryPage {

	public static function render(): void {
		$page = new self();
		$page->output();
	}

	public function output(): void {
		$scanner     = AttributeScanner::instance();
		$master_repo = new MasterGroupRepository();
		$taxonomies  = $scanner->get_all_attribute_taxonomies();

		$taxonomy   = sanitize_key( wp_unslash( $_GET['taxonomy'] ?? '' ) );
		$active_tax = in_array( $taxonomy, $taxonomies, true ) ? $taxonomy : ( $taxonomies[0] ?? '' );

		$masters = $active_tax ? $master_repo->find_all_by_taxonomy( $active_tax, true ) : array();

		// Augment each master with mapped term count
		$mapping_repo = new ValueMappingRepository();
		foreach ( $masters as &$master ) {
			$master->mapped_count = count( $mapping_repo->get_all_term_ids_for_master( (int) $master->id, $active_tax ) );
		}
		unset( $master );

		\AttributeHub\Free\Util\TemplateLoader::load(
			'admin/master-directory.php',
			array(
				'taxonomies' => $taxonomies,
				'active_tax' => $active_tax,
				'masters'    => $masters,
			)
		);
	}

	/**
	 * AJAX: Creates a new master group.
	 */
	public function create_master(): void {
		check_ajax_referer( 'attributehub_admin_nonce', 'attributehub_nonce' );
		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			wp_send_json_error( array( 'message' => __( 'Insufficient permissions.', 'attributehub-for-woocommerce' ) ), 403 );
			wp_die();
		}

		$label       = sanitize_text_field( wp_unslash( $_POST['label'] ?? '' ) );
		$taxonomy    = sanitize_key( wp_unslash( $_POST['taxonomy'] ?? '' ) );
		$description = sanitize_textarea_field( wp_unslash( $_POST['description'] ?? '' ) );
		$is_hidden   = ! empty( $_POST['is_hidden'] ) ? 1 : 0;

		if ( empty( $label ) || empty( $taxonomy ) ) {
			wp_send_json_error( array( 'message' => __( 'Label and taxonomy are required.', 'attributehub-for-woocommerce' ) ) );
			return;
		}

		$validator = new MappingValidator();
		$slug      = ! empty( $_POST['slug'] ) ? sanitize_title( wp_unslash( $_POST['slug'] ) ) : sanitize_title( $label );
		$safe_slug = $validator->suggest_safe_slug( $slug, $taxonomy );

		$master_repo = new MasterGroupRepository();
		$id          = $master_repo->create( $label, $taxonomy, array(
			'slug'        => $safe_slug,
			'description' => $description,
			'is_hidden'   => $is_hidden,
		) );

		if ( false === $id ) {
			wp_send_json_error( array( 'message' => __( 'Failed to create master group.', 'attributehub-for-woocommerce' ) ) );
			return;
		}

		$master = $master_repo->find( $id );
		do_action( 'attributehub_master_group_created', $id, array( 'label' => $label, 'taxonomy' => $taxonomy ) );

		wp_send_json_success( array(
			'id'      => $id,
			'master'  => $master,
			'message' => __( 'Master group created.', 'attributehub-for-woocommerce' ),
		) );
	}

	/**
	 * AJAX: Updates a master group.
	 */
	public function update_master(): void {
		check_ajax_referer( 'attributehub_admin_nonce', 'attributehub_nonce' );
		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			wp_send_json_error( array( 'message' => __( 'Insufficient permissions.', 'attributehub-for-woocommerce' ) ), 403 );
			wp_die();
		}

		$id   = absint( wp_unslash( $_POST['id'] ?? 0 ) );
		$data = array();

		if ( ! empty( $_POST['label'] ) ) {
			$data['label'] = sanitize_text_field( wp_unslash( $_POST['label'] ) );
		}
		if ( ! empty( $_POST['slug'] ) ) {
			$data['slug'] = sanitize_title( wp_unslash( $_POST['slug'] ) );
		}
		if ( isset( $_POST['is_hidden'] ) ) {
			$data['is_hidden'] = absint( wp_unslash( $_POST['is_hidden'] ) );
		}
		if ( isset( $_POST['description'] ) ) {
			$data['description'] = sanitize_textarea_field( wp_unslash( $_POST['description'] ) );
		}

		$master_repo = new MasterGroupRepository();
		$result      = $master_repo->update( $id, $data );

		if ( ! $result ) {
			wp_send_json_error( array( 'message' => __( 'Failed to update master group.', 'attributehub-for-woocommerce' ) ) );
			return;
		}

		$master = $master_repo->find( $id );

		wp_send_json_success( array(
			'master'  => $master,
			'message' => __( 'Master group updated.', 'attributehub-for-woocommerce' ),
		) );
	}

	/**
	 * AJAX: Deletes a master group and its mappings.
	 */
	public function delete_master(): void {
		check_ajax_referer( 'attributehub_admin_nonce', 'attributehub_nonce' );
		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			wp_send_json_error( array( 'message' => __( 'Insufficient permissions.', 'attributehub-for-woocommerce' ) ), 403 );
			wp_die();
		}

		$id          = absint( wp_unslash( $_POST['id'] ?? 0 ) );
		$master_repo = new MasterGroupRepository();
		$master      = $master_repo->find( $id );

		if ( ! $master ) {
			wp_send_json_error( array( 'message' => __( 'Master group not found.', 'attributehub-for-woocommerce' ) ) );
			return;
		}

		// Delete all mappings for this master first
		$mapping_repo = new ValueMappingRepository();
		$mapping_repo->delete_by_master( $id, $master->taxonomy );

		$result = $master_repo->delete( $id );

		if ( ! $result ) {
			wp_send_json_error( array( 'message' => __( 'Failed to delete master group.', 'attributehub-for-woocommerce' ) ) );
			return;
		}

		wp_send_json_success( array( 'message' => __( 'Master group deleted.', 'attributehub-for-woocommerce' ) ) );
	}

	/**
	 * AJAX: Reorders master groups.
	 */
	public function reorder_masters(): void {
		check_ajax_referer( 'attributehub_admin_nonce', 'attributehub_nonce' );
		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			wp_send_json_error( array( 'message' => __( 'Insufficient permissions.', 'attributehub-for-woocommerce' ) ), 403 );
			wp_die();
		}

		$taxonomy    = sanitize_key( wp_unslash( $_POST['taxonomy'] ?? '' ) );
		$ordered_ids = array_map( 'absint', (array) wp_unslash( $_POST['ids'] ?? array() ) );

		if ( empty( $taxonomy ) || empty( $ordered_ids ) ) {
			wp_send_json_error( array( 'message' => __( 'Invalid data.', 'attributehub-for-woocommerce' ) ) );
			return;
		}

		$master_repo = new MasterGroupRepository();
		$master_repo->reorder( $ordered_ids, $taxonomy );

		wp_send_json_success( array( 'message' => __( 'Order saved.', 'attributehub-for-woocommerce' ) ) );
	}
}
