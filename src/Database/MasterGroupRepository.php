<?php
/**
 * AttributeHub — Master Group Repository
 *
 * @package AttributeHub\Free\Database
 */

namespace AttributeHub\Free\Database;

defined( 'ABSPATH' ) || exit;

/**
 * CRUD operations for the ah_master_groups table.
 */
class MasterGroupRepository {

	/**
	 * Creates a new master group.
	 *
	 * @param string $label    Display label, e.g. "Black".
	 * @param string $taxonomy Taxonomy slug, e.g. "pa_color".
	 * @param array  $args     Optional: description, slug, sort_order, is_hidden, meta.
	 * @return int|false The new row ID, or false on failure.
	 */
	public function create( string $label, string $taxonomy, array $args = array() ) {
		global $wpdb;

		$slug = ! empty( $args['slug'] ) ? sanitize_title( $args['slug'] ) : sanitize_title( $label );
		$now  = current_time( 'mysql' );

		$data = array(
			'label'       => sanitize_text_field( $label ),
			'slug'        => $slug,
			'taxonomy'    => sanitize_key( $taxonomy ),
			'description' => isset( $args['description'] ) ? sanitize_textarea_field( $args['description'] ) : '',
			'sort_order'  => isset( $args['sort_order'] ) ? absint( $args['sort_order'] ) : 0,
			'is_hidden'   => ! empty( $args['is_hidden'] ) ? 1 : 0,
			'meta'        => isset( $args['meta'] ) ? wp_json_encode( $args['meta'] ) : null,
			'created_at'  => $now,
			'updated_at'  => $now,
		);

		$result = $wpdb->insert( // phpcs:ignore WordPress.DB.DirectDatabaseQuery
			Schema::master_groups(),
			$data,
			array( '%s', '%s', '%s', '%s', '%d', '%d', '%s', '%s', '%s' )
		);

		if ( false === $result ) {
			return false;
		}

		QueryCache::flush_taxonomy( $taxonomy );

		return $wpdb->insert_id;
	}

	/**
	 * Finds a master group by ID.
	 *
	 * @param int $id
	 * @return object|null
	 */
	public function find( int $id ): ?object {
		global $wpdb;

		$table = Schema::master_groups();
		$row   = $wpdb->get_row( // phpcs:ignore WordPress.DB.DirectDatabaseQuery
			$wpdb->prepare( "SELECT * FROM {$table} WHERE id = %d LIMIT 1", $id ) // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		);

		return $row ?: null;
	}

	/**
	 * Finds a master group by slug + taxonomy.
	 *
	 * @param string $slug
	 * @param string $taxonomy
	 * @return object|null
	 */
	public function find_by_slug( string $slug, string $taxonomy ): ?object {
		global $wpdb;

		$table = Schema::master_groups();
		$row   = $wpdb->get_row( // phpcs:ignore WordPress.DB.DirectDatabaseQuery
			$wpdb->prepare(
				"SELECT * FROM {$table} WHERE slug = %s AND taxonomy = %s LIMIT 1", // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
				sanitize_title( $slug ),
				sanitize_key( $taxonomy )
			)
		);

		return $row ?: null;
	}

	/**
	 * Returns all master groups for a taxonomy.
	 *
	 * @param string $taxonomy
	 * @param bool   $include_hidden Include groups with is_hidden=1.
	 * @return object[]
	 */
	public function find_all_by_taxonomy( string $taxonomy, bool $include_hidden = false ): array {
		global $wpdb;

		$cache_key = QueryCache::master_list_key( $taxonomy ) . ( $include_hidden ? '_all' : '' );
		$cached    = QueryCache::get( $cache_key );

		if ( false !== $cached ) {
			return $cached;
		}

		$table       = Schema::master_groups();
		$hidden_sql  = $include_hidden ? '' : ' AND is_hidden = 0';
		$results     = $wpdb->get_results( // phpcs:ignore WordPress.DB.DirectDatabaseQuery
			$wpdb->prepare(
				"SELECT * FROM {$table} WHERE taxonomy = %s{$hidden_sql} ORDER BY sort_order ASC, label ASC", // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
				sanitize_key( $taxonomy )
			)
		);

		$results = $results ?: array();
		QueryCache::set( $cache_key, $results );

		return $results;
	}

	/**
	 * Returns all master groups across all taxonomies.
	 *
	 * @return object[]
	 */
	public function find_all(): array {
		global $wpdb;

		$table = Schema::master_groups();
		return $wpdb->get_results( "SELECT * FROM {$table} ORDER BY taxonomy ASC, sort_order ASC, label ASC" ) ?: array(); // phpcs:ignore WordPress.DB.DirectDatabaseQuery,WordPress.DB.PreparedSQL.NotPrepared
	}

	/**
	 * Updates an existing master group.
	 *
	 * @param int   $id
	 * @param array $data Fields to update (label, slug, description, sort_order, is_hidden, meta).
	 * @return bool
	 */
	public function update( int $id, array $data ): bool {
		global $wpdb;

		$existing = $this->find( $id );
		if ( ! $existing ) {
			return false;
		}

		$update = array();
		$format = array();

		if ( isset( $data['label'] ) ) {
			$update['label']      = sanitize_text_field( $data['label'] );
			$format[]             = '%s';
		}
		if ( isset( $data['slug'] ) ) {
			$update['slug']       = sanitize_title( $data['slug'] );
			$format[]             = '%s';
		}
		if ( isset( $data['description'] ) ) {
			$update['description'] = sanitize_textarea_field( $data['description'] );
			$format[]              = '%s';
		}
		if ( isset( $data['sort_order'] ) ) {
			$update['sort_order'] = absint( $data['sort_order'] );
			$format[]             = '%d';
		}
		if ( isset( $data['is_hidden'] ) ) {
			$update['is_hidden']  = (int) (bool) $data['is_hidden'];
			$format[]             = '%d';
		}
		if ( isset( $data['meta'] ) ) {
			$update['meta']       = wp_json_encode( $data['meta'] );
			$format[]             = '%s';
		}

		if ( empty( $update ) ) {
			return true;
		}

		$update['updated_at'] = current_time( 'mysql' );
		$format[]             = '%s';

		$result = $wpdb->update( // phpcs:ignore WordPress.DB.DirectDatabaseQuery
			Schema::master_groups(),
			$update,
			array( 'id' => $id ),
			$format,
			array( '%d' )
		);

		QueryCache::flush_taxonomy( $existing->taxonomy );

		return false !== $result;
	}

	/**
	 * Deletes a master group by ID.
	 * Note: Does NOT cascade-delete mappings — caller must handle that.
	 *
	 * @param int $id
	 * @return bool
	 */
	public function delete( int $id ): bool {
		global $wpdb;

		$existing = $this->find( $id );
		if ( ! $existing ) {
			return false;
		}

		$result = $wpdb->delete( // phpcs:ignore WordPress.DB.DirectDatabaseQuery
			Schema::master_groups(),
			array( 'id' => $id ),
			array( '%d' )
		);

		QueryCache::flush_taxonomy( $existing->taxonomy );

		return false !== $result;
	}

	/**
	 * Searches master groups by label.
	 *
	 * @param string $term     Search term.
	 * @param string $taxonomy Optional taxonomy filter.
	 * @return object[]
	 */
	public function search( string $term, string $taxonomy = '' ): array {
		global $wpdb;

		$table      = Schema::master_groups();
		$like       = '%' . $wpdb->esc_like( sanitize_text_field( $term ) ) . '%';
		$tax_clause = $taxonomy ? $wpdb->prepare( ' AND taxonomy = %s', sanitize_key( $taxonomy ) ) : '';

		return $wpdb->get_results( // phpcs:ignore WordPress.DB.DirectDatabaseQuery
			$wpdb->prepare(
				"SELECT * FROM {$table} WHERE label LIKE %s{$tax_clause} ORDER BY label ASC LIMIT 50", // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
				$like
			)
		) ?: array();
	}

	/**
	 * Updates sort_order for a list of master group IDs.
	 *
	 * @param array $ordered_ids Ordered array of IDs (first = sort_order 10, next = 20, etc.)
	 * @param string $taxonomy  Taxonomy to flush cache for.
	 */
	public function reorder( array $ordered_ids, string $taxonomy ): void {
		global $wpdb;

		$table      = Schema::master_groups();
		$sort_order = 10;

		foreach ( $ordered_ids as $id ) {
			$wpdb->update( // phpcs:ignore WordPress.DB.DirectDatabaseQuery
				$table,
				array( 'sort_order' => $sort_order, 'updated_at' => current_time( 'mysql' ) ),
				array( 'id' => absint( $id ) ),
				array( '%d', '%s' ),
				array( '%d' )
			);
			$sort_order += 10;
		}

		QueryCache::flush_taxonomy( $taxonomy );
	}

	/**
	 * Gets the count of master groups for a taxonomy.
	 */
	public function count_by_taxonomy( string $taxonomy ): int {
		global $wpdb;

		$table = Schema::master_groups();
		return (int) $wpdb->get_var( // phpcs:ignore WordPress.DB.DirectDatabaseQuery
			$wpdb->prepare( "SELECT COUNT(*) FROM {$table} WHERE taxonomy = %s", sanitize_key( $taxonomy ) ) // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		);
	}
}
