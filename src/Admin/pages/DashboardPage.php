<?php
/**
 * AttributeHub — Dashboard Page
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
 * Renders the AttributeHub dashboard admin page.
 */
class DashboardPage {

	/**
	 * Static render entry point (called from MenuRegistrar).
	 */
	public static function render(): void {
		$page = new self();
		$page->output();
	}

	/**
	 * Renders the dashboard.
	 */
	public function output(): void {
		$scanner      = AttributeScanner::instance();
		$master_repo  = new MasterGroupRepository();
		$mapping_repo = new ValueMappingRepository();
		$scan_repo    = new ScanResultRepository();

		$taxonomies   = $scanner->get_all_attribute_taxonomies();
		$stats        = $this->build_stats( $taxonomies, $master_repo, $mapping_repo, $scan_repo );
		$totals       = $this->build_totals( $stats );

		\AttributeHub\Free\Util\TemplateLoader::load(
			'admin/dashboard.php',
			array(
				'stats'      => $stats,
				'totals'     => $totals,
				'taxonomies' => $taxonomies,
			)
		);
	}

	/**
	 * Builds per-taxonomy stats rows.
	 */
	private function build_stats( array $taxonomies, MasterGroupRepository $master_repo, ValueMappingRepository $mapping_repo, ScanResultRepository $scan_repo ): array {
		$stats = array();

		foreach ( $taxonomies as $taxonomy ) {
			$tax_obj      = get_taxonomy( $taxonomy );
			$total_terms  = wp_count_terms( $taxonomy );
			$mapped_count = $mapping_repo->count_mapped( $taxonomy );
			$masters      = $master_repo->count_by_taxonomy( $taxonomy );
			$last_scan    = $scan_repo->get_last_scan_time( $taxonomy );

			$stats[ $taxonomy ] = array(
				'taxonomy'     => $taxonomy,
				'label'        => $tax_obj ? $tax_obj->labels->name : $taxonomy,
				'total_terms'  => is_wp_error( $total_terms ) ? 0 : (int) $total_terms,
				'mapped'       => $mapped_count,
				'unmapped'     => max( 0, ( is_wp_error( $total_terms ) ? 0 : (int) $total_terms ) - $mapped_count ),
				'masters'      => $masters,
				'last_scan'    => $last_scan,
				'mapped_pct'   => ( ! is_wp_error( $total_terms ) && $total_terms > 0 )
					? round( ( $mapped_count / (int) $total_terms ) * 100 )
					: 0,
			);
		}

		return $stats;
	}

	/**
	 * Builds site-wide totals.
	 */
	private function build_totals( array $stats ): array {
		$totals = array(
			'taxonomies'  => count( $stats ),
			'total_terms' => 0,
			'mapped'      => 0,
			'unmapped'    => 0,
			'masters'     => 0,
		);

		foreach ( $stats as $row ) {
			$totals['total_terms'] += $row['total_terms'];
			$totals['mapped']      += $row['mapped'];
			$totals['unmapped']    += $row['unmapped'];
			$totals['masters']     += $row['masters'];
		}

		$totals['mapped_pct'] = $totals['total_terms'] > 0
			? round( ( $totals['mapped'] / $totals['total_terms'] ) * 100 )
			: 0;

		return $totals;
	}

	/**
	 * AJAX handler: triggers an attribute scan (all taxonomies or a specific one).
	 */
	public function ajax_scan(): void {
		check_ajax_referer( 'attributehub_admin_nonce', 'attributehub_nonce' );
		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			wp_send_json_error( array( 'message' => __( 'Insufficient permissions.', 'attributehub-for-woocommerce' ) ), 403 );
			wp_die();
		}

		$scanner  = AttributeScanner::instance();
		$taxonomy = sanitize_key( wp_unslash( $_POST['taxonomy'] ?? '' ) );

		if ( $taxonomy ) {
			$scanner->run_scan( $taxonomy );
		} else {
			// Scan all attribute taxonomies
			foreach ( $scanner->get_all_attribute_taxonomies() as $tax ) {
				$scanner->run_scan( $tax );
			}
		}

		wp_send_json_success( array(
			'message' => __( 'Scan completed.', 'attributehub-for-woocommerce' ),
		) );
	}
}
