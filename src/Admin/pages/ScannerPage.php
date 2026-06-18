<?php
/**
 * AttributeHub — Scanner Page
 *
 * @package AttributeHub\Free\Admin\pages
 */

namespace AttributeHub\Free\Admin\pages;

use AttributeHub\Free\Scanner\AttributeScanner;
use AttributeHub\Free\Database\ScanResultRepository;

defined( 'ABSPATH' ) || exit;

/**
 * Renders the Attribute Scanner admin page and handles scan AJAX calls.
 */
class ScannerPage {

	public static function render(): void {
		$page = new self();
		$page->output();
	}

	public function output(): void {
		$scanner   = AttributeScanner::instance();
		$scan_repo = new ScanResultRepository();

		$taxonomies   = $scanner->get_all_attribute_taxonomies();
		$taxonomy     = sanitize_key( wp_unslash( $_GET['taxonomy'] ?? '' ) );
		$active_tax   = in_array( $taxonomy, $taxonomies, true ) ? $taxonomy : ( $taxonomies[0] ?? '' );

		// Get latest scan results for active taxonomy
		$latest_run_id = $active_tax ? $scan_repo->get_latest_run_id( $active_tax ) : null;
		$results       = $latest_run_id ? $scan_repo->get_by_run_id( $latest_run_id ) : array();
		$summary       = $active_tax ? $scan_repo->get_taxonomy_summary( $active_tax ) : array();
		$last_scan     = $active_tax ? $scan_repo->get_last_scan_time( $active_tax ) : null;

		\AttributeHub\Free\Util\TemplateLoader::load(
			'admin/scanner.php',
			array(
				'taxonomies'    => $taxonomies,
				'active_tax'    => $active_tax,
				'results'       => $results,
				'summary'       => $summary,
				'last_scan'     => $last_scan,
				'latest_run_id' => $latest_run_id,
			)
		);
	}

	/**
	 * AJAX: Runs a scan and returns progress/results.
	 * Called via attributehub_ajax with attributehub_method='Admin\\pages\\ScannerPage@run_scan'.
	 */
	public function run_scan(): void {
		check_ajax_referer( 'attributehub_admin_nonce', 'attributehub_nonce' );
		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			wp_send_json_error( array( 'message' => __( 'Insufficient permissions.', 'attributehub-for-woocommerce' ) ), 403 );
			wp_die();
		}

		$taxonomy    = sanitize_key( wp_unslash( $_POST['taxonomy'] ?? '' ) );
		$scan_run_id = AttributeScanner::instance()->run_scan( $taxonomy );
		$scan_repo   = new ScanResultRepository();
		$summary     = $scan_repo->get_taxonomy_summary( $taxonomy );

		wp_send_json_success( array(
			'scan_run_id' => $scan_run_id,
			'summary'     => $summary,
			'message'     => __( 'Scan completed successfully.', 'attributehub-for-woocommerce' ),
		) );
	}

	/**
	 * AJAX: Gets scan results for a taxonomy.
	 * Called via attributehub_ajax with attributehub_method='Admin\\pages\\ScannerPage@get_results'.
	 */
	public function get_results(): void {
		check_ajax_referer( 'attributehub_admin_nonce', 'attributehub_nonce' );
		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			wp_send_json_error( array( 'message' => __( 'Insufficient permissions.', 'attributehub-for-woocommerce' ) ), 403 );
			wp_die();
		}

		$taxonomy    = sanitize_key( wp_unslash( $_POST['taxonomy'] ?? '' ) );
		$filter      = sanitize_text_field( wp_unslash( $_POST['filter'] ?? 'all' ) ); // all|unmapped|ugly|duplicate
		$scan_repo   = new ScanResultRepository();

		switch ( $filter ) {
			case 'unmapped':
				$results = $scan_repo->get_unmapped( $taxonomy );
				break;
			case 'ugly':
				$results = $scan_repo->get_ugly( $taxonomy );
				break;
			case 'duplicate':
				$results = $scan_repo->get_duplicates( $taxonomy );
				break;
			default:
				$run_id  = $scan_repo->get_latest_run_id( $taxonomy );
				$results = $run_id ? $scan_repo->get_by_run_id( $run_id ) : array();
		}

		wp_send_json_success( array( 'results' => $results ) );
	}
}
