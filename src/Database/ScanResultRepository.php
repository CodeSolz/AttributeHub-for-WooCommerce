<?php
/**
 * AttributeHub — Scan Result Repository
 *
 * @package AttributeHub\Free\Database
 */

namespace AttributeHub\Free\Database;

defined( 'ABSPATH' ) || exit;

/**
 * CRUD operations for the ah_scan_results table.
 */
class ScanResultRepository {

	/**
	 * Stores a batch of scan results for a taxonomy.
	 * Replaces previous scan results for the same taxonomy (keeps only latest run).
	 *
	 * @param string $scan_run_id UUID for this scan run.
	 * @param string $taxonomy    Taxonomy slug.
	 * @param array  $results     Array of result arrays.
	 * @return bool
	 */
	public function store_results( string $scan_run_id, string $taxonomy, array $results ): bool {
		global $wpdb;

		$table = Schema::scan_results();

		// Delete previous results for this taxonomy before inserting new ones
		$wpdb->delete( // phpcs:ignore WordPress.DB.DirectDatabaseQuery
			$table,
			array( 'taxonomy' => sanitize_key( $taxonomy ) ),
			array( '%s' )
		);

		if ( empty( $results ) ) {
			return true;
		}

		$now     = current_time( 'mysql' );
		$success = true;

		foreach ( $results as $result ) {
			$inserted = $wpdb->insert( // phpcs:ignore WordPress.DB.DirectDatabaseQuery
				$table,
				array(
					'scan_run_id'   => sanitize_text_field( $scan_run_id ),
					'taxonomy'      => sanitize_key( $taxonomy ),
					'term_id'       => absint( $result['term_id'] ?? 0 ),
					'raw_value'     => sanitize_text_field( $result['raw_value'] ?? '' ),
					'product_count' => absint( $result['product_count'] ?? 0 ),
					'is_mapped'     => (int) ! empty( $result['is_mapped'] ),
					'issue_type'    => isset( $result['issue_type'] ) ? sanitize_text_field( $result['issue_type'] ) : null,
					'similar_to'    => isset( $result['similar_to'] ) ? absint( $result['similar_to'] ) : null,
					'scanned_at'    => $now,
				),
				array( '%s', '%s', '%d', '%s', '%d', '%d', '%s', '%d', '%s' )
			);

			if ( false === $inserted ) {
				$success = false;
			}
		}

		// Update last scan timestamp
		update_option( 'attributehub_last_scan_' . sanitize_key( $taxonomy ), $now );

		return $success;
	}

	/**
	 * Gets all scan results for a specific scan run.
	 *
	 * @param string $scan_run_id
	 * @return object[]
	 */
	public function get_by_run_id( string $scan_run_id ): array {
		global $wpdb;

		$table = Schema::scan_results();
		return $wpdb->get_results( // phpcs:ignore WordPress.DB.DirectDatabaseQuery
			$wpdb->prepare(
				"SELECT * FROM {$table} WHERE scan_run_id = %s ORDER BY product_count DESC", // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
				sanitize_text_field( $scan_run_id )
			)
		) ?: array();
	}

	/**
	 * Gets the most recent scan_run_id for a taxonomy.
	 *
	 * @param string $taxonomy
	 * @return string|null
	 */
	public function get_latest_run_id( string $taxonomy ): ?string {
		global $wpdb;

		$table = Schema::scan_results();
		$id    = $wpdb->get_var( // phpcs:ignore WordPress.DB.DirectDatabaseQuery
			$wpdb->prepare(
				"SELECT scan_run_id FROM {$table} WHERE taxonomy = %s ORDER BY scanned_at DESC LIMIT 1", // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
				sanitize_key( $taxonomy )
			)
		);

		return $id ?: null;
	}

	/**
	 * Gets all unmapped scan results for a taxonomy (from latest run).
	 *
	 * @param string $taxonomy
	 * @return object[]
	 */
	public function get_unmapped( string $taxonomy ): array {
		global $wpdb;

		$table = Schema::scan_results();
		return $wpdb->get_results( // phpcs:ignore WordPress.DB.DirectDatabaseQuery
			$wpdb->prepare(
				"SELECT * FROM {$table}
				 WHERE taxonomy = %s AND is_mapped = 0
				 ORDER BY product_count DESC", // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
				sanitize_key( $taxonomy )
			)
		) ?: array();
	}

	/**
	 * Gets all duplicate-flagged scan results for a taxonomy.
	 *
	 * @param string $taxonomy
	 * @return object[]
	 */
	public function get_duplicates( string $taxonomy ): array {
		global $wpdb;

		$table = Schema::scan_results();
		return $wpdb->get_results( // phpcs:ignore WordPress.DB.DirectDatabaseQuery
			$wpdb->prepare(
				"SELECT * FROM {$table}
				 WHERE taxonomy = %s AND issue_type = 'duplicate'
				 ORDER BY raw_value ASC", // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
				sanitize_key( $taxonomy )
			)
		) ?: array();
	}

	/**
	 * Gets all ugly-flagged scan results for a taxonomy.
	 *
	 * @param string $taxonomy
	 * @return object[]
	 */
	public function get_ugly( string $taxonomy ): array {
		global $wpdb;

		$table = Schema::scan_results();
		return $wpdb->get_results( // phpcs:ignore WordPress.DB.DirectDatabaseQuery
			$wpdb->prepare(
				"SELECT * FROM {$table}
				 WHERE taxonomy = %s AND issue_type = 'ugly'
				 ORDER BY product_count DESC", // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
				sanitize_key( $taxonomy )
			)
		) ?: array();
	}

	/**
	 * Gets the timestamp of the last scan for a taxonomy.
	 *
	 * @param string $taxonomy
	 * @return string|null MySQL datetime or null if never scanned.
	 */
	public function get_last_scan_time( string $taxonomy ): ?string {
		$time = get_option( 'attributehub_last_scan_' . sanitize_key( $taxonomy ) );
		return $time ?: null;
	}

	/**
	 * Gets a summary of scan results for a taxonomy.
	 * Returns [total, mapped, unmapped, ugly, duplicate].
	 *
	 * @param string $taxonomy
	 * @return array
	 */
	public function get_taxonomy_summary( string $taxonomy ): array {
		global $wpdb;

		$table = Schema::scan_results();
		$row   = $wpdb->get_row( // phpcs:ignore WordPress.DB.DirectDatabaseQuery
			$wpdb->prepare(
				"SELECT
				   COUNT(*) AS total,
				   SUM(is_mapped) AS mapped,
				   SUM(is_mapped = 0) AS unmapped,
				   SUM(issue_type = 'ugly') AS ugly,
				   SUM(issue_type = 'duplicate') AS duplicate
				 FROM {$table}
				 WHERE taxonomy = %s", // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
				sanitize_key( $taxonomy )
			)
		);

		return array(
			'total'     => (int) ( $row->total ?? 0 ),
			'mapped'    => (int) ( $row->mapped ?? 0 ),
			'unmapped'  => (int) ( $row->unmapped ?? 0 ),
			'ugly'      => (int) ( $row->ugly ?? 0 ),
			'duplicate' => (int) ( $row->duplicate ?? 0 ),
		);
	}

	/**
	 * Gets unmapped scan results newer than a given datetime (Pro: email digest).
	 *
	 * @param string $taxonomy
	 * @param string $since MySQL datetime; empty string = all time.
	 * @return object[]
	 */
	public function get_new_unmapped_since( string $taxonomy, string $since = '' ): array {
		global $wpdb;

		$table = Schema::scan_results();

		if ( $since ) {
			// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			return $wpdb->get_results( $wpdb->prepare( // phpcs:ignore WordPress.DB.DirectDatabaseQuery
				"SELECT * FROM {$table}
				 WHERE taxonomy = %s AND is_mapped = 0 AND scanned_at > %s
				 ORDER BY product_count DESC",
				sanitize_key( $taxonomy ),
				sanitize_text_field( $since )
			) ) ?: array();
		}

		return $this->get_unmapped( $taxonomy );
	}

	/**
	 * Marks a term as mapped in the scan results table.
	 * Called after a manual mapping is created.
	 *
	 * @param int    $term_id
	 * @param string $taxonomy
	 */
	public function mark_as_mapped( int $term_id, string $taxonomy ): void {
		global $wpdb;

		$wpdb->update( // phpcs:ignore WordPress.DB.DirectDatabaseQuery
			Schema::scan_results(),
			array( 'is_mapped' => 1 ),
			array( 'term_id' => $term_id, 'taxonomy' => sanitize_key( $taxonomy ) ),
			array( '%d' ),
			array( '%d', '%s' )
		);
	}
}
