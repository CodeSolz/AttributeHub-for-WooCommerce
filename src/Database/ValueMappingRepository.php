<?php
/**
 * AttributeHub — Value Mapping Repository
 *
 * @package AttributeHub\Free\Database
 */

namespace AttributeHub\Free\Database;

defined( 'ABSPATH' ) || exit;

/**
 * CRUD operations for the ah_value_mappings table.
 * This is the core mapping layer — never modifies WC terms.
 */
class ValueMappingRepository {

	/**
	 * Maps a raw WC term to a master group.
	 *
	 * @param int    $term_id         WC term_id (never modified).
	 * @param string $taxonomy        Taxonomy slug, e.g. 'pa_color'.
	 * @param int    $master_group_id FK to ah_master_groups.id.
	 * @param string $mapped_by       How mapping was created: manual|auto|rule|csv.
	 * @return int|false New row ID, or false on failure.
	 */
	public function map( int $term_id, string $taxonomy, int $master_group_id, string $mapped_by = 'manual' ) {
		global $wpdb;

		// Get the raw term slug as a snapshot (for display only)
		$term      = get_term( $term_id, $taxonomy );
		$raw_value = ( $term && ! is_wp_error( $term ) ) ? $term->slug : '';
		$now       = current_time( 'mysql' );

		// Use INSERT ... ON DUPLICATE KEY UPDATE to handle re-mapping gracefully
		$table  = Schema::value_mappings();
		$result = $wpdb->query( // phpcs:ignore WordPress.DB.DirectDatabaseQuery
			$wpdb->prepare(
				// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
				"INSERT INTO {$table} (taxonomy, term_id, raw_value, master_group_id, is_active, mapped_by, created_at, updated_at)
				 VALUES (%s, %d, %s, %d, 1, %s, %s, %s)
				 ON DUPLICATE KEY UPDATE
				   master_group_id = VALUES(master_group_id),
				   raw_value       = VALUES(raw_value),
				   mapped_by       = VALUES(mapped_by),
				   is_active       = 1,
				   updated_at      = VALUES(updated_at)",
				sanitize_key( $taxonomy ),
				$term_id,
				sanitize_text_field( $raw_value ),
				$master_group_id,
				sanitize_text_field( $mapped_by ),
				$now,
				$now
			)
		);

		if ( false === $result ) {
			return false;
		}

		// Flush mapping cache for this taxonomy
		QueryCache::flush_taxonomy( $taxonomy );

		/**
		 * Fires when a mapping is created or updated.
		 *
		 * @param int    $term_id
		 * @param string $taxonomy
		 * @param int    $master_group_id
		 * @param string $mapped_by
		 */
		do_action( 'attributehub_mapping_created', $term_id, $taxonomy, $master_group_id, $mapped_by );

		return $wpdb->insert_id ?: true;
	}

	/**
	 * Removes the mapping for a term.
	 *
	 * @param int    $term_id
	 * @param string $taxonomy
	 * @return bool
	 */
	public function unmap( int $term_id, string $taxonomy ): bool {
		global $wpdb;

		$result = $wpdb->delete( // phpcs:ignore WordPress.DB.DirectDatabaseQuery
			Schema::value_mappings(),
			array(
				'term_id'  => $term_id,
				'taxonomy' => sanitize_key( $taxonomy ),
			),
			array( '%d', '%s' )
		);

		QueryCache::flush_taxonomy( $taxonomy );

		do_action( 'attributehub_mapping_removed', $term_id, $taxonomy );

		return false !== $result;
	}

	/**
	 * Gets the master group data for a term (via its mapping row).
	 *
	 * @param int    $term_id
	 * @param string $taxonomy
	 * @return object|null The mapping row (with master_group_id), or null.
	 */
	public function get_master_for_term( int $term_id, string $taxonomy ): ?object {
		global $wpdb;

		$table = Schema::value_mappings();
		$row   = $wpdb->get_row( // phpcs:ignore WordPress.DB.DirectDatabaseQuery
			$wpdb->prepare(
				"SELECT * FROM {$table} WHERE term_id = %d AND taxonomy = %s AND is_active = 1 LIMIT 1", // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
				$term_id,
				sanitize_key( $taxonomy )
			)
		);

		return $row ?: null;
	}

	/**
	 * Gets all raw term_ids mapped to a master group in a taxonomy.
	 *
	 * @param int    $master_group_id
	 * @param string $taxonomy
	 * @return int[]
	 */
	public function get_all_term_ids_for_master( int $master_group_id, string $taxonomy ): array {
		global $wpdb;

		$table = Schema::value_mappings();
		$ids   = $wpdb->get_col( // phpcs:ignore WordPress.DB.DirectDatabaseQuery
			$wpdb->prepare(
				"SELECT term_id FROM {$table} WHERE master_group_id = %d AND taxonomy = %s AND is_active = 1", // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
				$master_group_id,
				sanitize_key( $taxonomy )
			)
		);

		return array_map( 'intval', $ids ?: array() );
	}

	/**
	 * Gets all WC terms in a taxonomy that have NO mapping yet.
	 * Returns basic term data: term_id, name, slug, count.
	 *
	 * @param string $taxonomy
	 * @return array
	 */
	public function get_unmapped_terms( string $taxonomy ): array {
		global $wpdb;

		$mappings_table = Schema::value_mappings();
		$terms_table    = $wpdb->terms;
		$term_tax_table = $wpdb->term_taxonomy;

		// phpcs:disable WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		return $wpdb->get_results( // phpcs:ignore WordPress.DB.DirectDatabaseQuery
			$wpdb->prepare(
				"SELECT t.term_id, t.name, t.slug, tt.count
				 FROM {$terms_table} t
				 INNER JOIN {$term_tax_table} tt ON t.term_id = tt.term_id
				 WHERE tt.taxonomy = %s
				   AND t.term_id NOT IN (
				       SELECT term_id FROM {$mappings_table}
				       WHERE taxonomy = %s AND is_active = 1
				   )
				 ORDER BY tt.count DESC",
				sanitize_key( $taxonomy ),
				sanitize_key( $taxonomy )
			)
		) ?: array();
		// phpcs:enable WordPress.DB.PreparedSQL.InterpolatedNotPrepared
	}

	/**
	 * Batch maps multiple terms at once.
	 * Skips rows that would duplicate an existing active mapping.
	 *
	 * @param array $mappings Array of ['term_id', 'taxonomy', 'master_group_id', 'mapped_by'].
	 * @return int Number of rows successfully inserted/updated.
	 */
	public function bulk_map( array $mappings ): int {
		$count = 0;

		foreach ( $mappings as $m ) {
			$result = $this->map(
				absint( $m['term_id'] ),
				sanitize_key( $m['taxonomy'] ),
				absint( $m['master_group_id'] ),
				sanitize_text_field( $m['mapped_by'] ?? 'manual' )
			);

			if ( false !== $result ) {
				$count++;
			}
		}

		return $count;
	}

	/**
	 * Checks whether a term is currently mapped.
	 *
	 * @param int    $term_id
	 * @param string $taxonomy
	 * @return bool
	 */
	public function is_mapped( int $term_id, string $taxonomy ): bool {
		global $wpdb;

		$table = Schema::value_mappings();
		$count = (int) $wpdb->get_var( // phpcs:ignore WordPress.DB.DirectDatabaseQuery
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$table} WHERE term_id = %d AND taxonomy = %s AND is_active = 1", // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
				$term_id,
				sanitize_key( $taxonomy )
			)
		);

		return $count > 0;
	}

	/**
	 * Gets the full mapping map for a taxonomy: [term_id => master_group_id].
	 * Used by MappingEngine to build its cache.
	 *
	 * @param string $taxonomy
	 * @return array<int, int>
	 */
	public function get_full_taxonomy_map( string $taxonomy ): array {
		global $wpdb;

		$table = Schema::value_mappings();
		$rows  = $wpdb->get_results( // phpcs:ignore WordPress.DB.DirectDatabaseQuery
			$wpdb->prepare(
				"SELECT term_id, master_group_id FROM {$table} WHERE taxonomy = %s AND is_active = 1", // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
				sanitize_key( $taxonomy )
			)
		);

		$map = array();
		foreach ( $rows ?: array() as $row ) {
			$map[ (int) $row->term_id ] = (int) $row->master_group_id;
		}

		return $map;
	}

	/**
	 * Returns count of mapped terms for a taxonomy.
	 */
	public function count_mapped( string $taxonomy ): int {
		global $wpdb;

		$table = Schema::value_mappings();
		return (int) $wpdb->get_var( // phpcs:ignore WordPress.DB.DirectDatabaseQuery
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$table} WHERE taxonomy = %s AND is_active = 1", // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
				sanitize_key( $taxonomy )
			)
		);
	}

	/**
	 * Deletes all mappings for a master group (before deleting the group).
	 *
	 * @param int    $master_group_id
	 * @param string $taxonomy
	 */
	public function delete_by_master( int $master_group_id, string $taxonomy ): void {
		global $wpdb;

		$wpdb->delete( // phpcs:ignore WordPress.DB.DirectDatabaseQuery
			Schema::value_mappings(),
			array(
				'master_group_id' => $master_group_id,
				'taxonomy'        => sanitize_key( $taxonomy ),
			),
			array( '%d', '%s' )
		);

		QueryCache::flush_taxonomy( $taxonomy );
	}
}
