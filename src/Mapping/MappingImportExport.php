<?php
/**
 * AttributeHub — Mapping Import/Export
 *
 * @package AttributeHub\Free\Mapping
 */

namespace AttributeHub\Free\Mapping;

use AttributeHub\Free\Database\MasterGroupRepository;
use AttributeHub\Free\Database\ValueMappingRepository;

defined( 'ABSPATH' ) || exit;

/**
 * CSV export of mapping configuration.
 * CSV import is a Pro feature (scaffold provided here).
 */
class MappingImportExport {

	/** @var MasterGroupRepository */
	private MasterGroupRepository $master_repo;

	/** @var ValueMappingRepository */
	private ValueMappingRepository $mapping_repo;

	public function __construct() {
		$this->master_repo  = new MasterGroupRepository();
		$this->mapping_repo = new ValueMappingRepository();
	}

	/**
	 * Generates CSV content for all mappings (or a specific taxonomy).
	 *
	 * CSV columns: attribute, backend_value, master_label, master_slug, mapped_by
	 *
	 * @param string $taxonomy Empty = all taxonomies.
	 * @return string CSV content.
	 */
	public function export_csv( string $taxonomy = '' ): string {
		global $wpdb;

		$mappings_table = \AttributeHub\Free\Database\Schema::value_mappings();
		$masters_table  = \AttributeHub\Free\Database\Schema::master_groups();

		$tax_clause = $taxonomy ? $wpdb->prepare( 'AND vm.taxonomy = %s', sanitize_key( $taxonomy ) ) : '';

		$rows = $wpdb->get_results( // phpcs:ignore WordPress.DB.DirectDatabaseQuery,WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			"SELECT
			   vm.taxonomy AS attribute,
			   vm.raw_value AS backend_value,
			   mg.label AS master_label,
			   mg.slug AS master_slug,
			   vm.mapped_by
			 FROM {$mappings_table} vm
			 INNER JOIN {$masters_table} mg ON vm.master_group_id = mg.id
			 WHERE vm.is_active = 1 {$tax_clause}
			 ORDER BY vm.taxonomy ASC, mg.label ASC, vm.raw_value ASC"
		);

		ob_start();
		$output = fopen( 'php://output', 'w' ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fopen

		// BOM for Excel UTF-8 compatibility
		fputs( $output, "\xEF\xBB\xBF" ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fputs

		// Header row
		fputcsv( $output, array( 'attribute', 'backend_value', 'master_label', 'master_slug', 'mapped_by' ) );

		foreach ( $rows ?: array() as $row ) {
			fputcsv( $output, array(
				$row->attribute,
				$row->backend_value,
				$row->master_label,
				$row->master_slug,
				$row->mapped_by,
			) );
		}

		fclose( $output ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fclose

		return ob_get_clean();
	}

	/**
	 * Returns the suggested filename for CSV export.
	 *
	 * @param string $taxonomy
	 * @return string
	 */
	public function get_export_filename( string $taxonomy = '' ): string {
		$date   = gmdate( 'Y-m-d' );
		$suffix = $taxonomy ? '-' . sanitize_key( $taxonomy ) : '';
		return "attributehub-mappings{$suffix}-{$date}.csv";
	}

	/**
	 * Sends a CSV export as a file download response.
	 * Exits after output.
	 *
	 * @param string $taxonomy
	 */
	public function stream_export_download( string $taxonomy = '' ): void {
		$filename = $this->get_export_filename( $taxonomy );
		$content  = $this->export_csv( $taxonomy );

		header( 'Content-Type: text/csv; charset=utf-8' );
		header( 'Content-Disposition: attachment; filename="' . sanitize_file_name( $filename ) . '"' );
		header( 'Content-Length: ' . strlen( $content ) );
		header( 'Pragma: no-cache' );
		header( 'Expires: 0' );

		echo $content; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		exit;
	}

	/**
	 * Import scaffold — full implementation in Pro.
	 * This stub allows the Pro plugin to extend this class.
	 *
	 * @param string $csv_content Raw CSV string.
	 * @return array [ 'imported' => int, 'skipped' => int, 'errors' => string[] ]
	 */
	public function import_csv( string $csv_content ): array {
		if ( ! attributehub()->is_pro() ) {
			return array(
				'imported' => 0,
				'skipped'  => 0,
				'errors'   => array( __( 'CSV import requires AttributeHub Pro.', 'attributehub-for-woocommerce' ) ),
			);
		}

		// Pro plugin overrides this method
		return array( 'imported' => 0, 'skipped' => 0, 'errors' => array() );
	}
}
