<?php
/**
 * AttributeHub — Query Cache
 *
 * @package AttributeHub\Free\Database
 */

namespace AttributeHub\Free\Database;

defined( 'ABSPATH' ) || exit;

/**
 * Transient-based per-taxonomy cache for mapping data.
 * All cache keys are prefixed with 'ah_' to allow bulk clearing.
 */
class QueryCache {

	/** @var int Default TTL in seconds (24 hours) */
	const DEFAULT_TTL = 86400;

	/**
	 * Gets the TTL for transient caches.
	 */
	public static function get_ttl(): int {
		$settings = get_option( 'attributehub_settings', array() );
		$ttl      = absint( $settings['cache_ttl'] ?? self::DEFAULT_TTL );
		return apply_filters( 'attributehub_cache_ttl', max( 60, $ttl ) );
	}

	/**
	 * Gets a cached value by key.
	 *
	 * @param string $key Cache key (without prefix).
	 * @return mixed|false
	 */
	public static function get( string $key ) {
		return get_transient( 'attributehub_' . $key );
	}

	/**
	 * Sets a cached value.
	 *
	 * @param string $key   Cache key (without prefix).
	 * @param mixed  $value Value to cache.
	 * @param int    $ttl   TTL in seconds. 0 = use plugin default.
	 */
	public static function set( string $key, $value, int $ttl = 0 ): bool {
		$ttl = $ttl > 0 ? $ttl : self::get_ttl();
		return set_transient( 'attributehub_' . $key, $value, $ttl );
	}

	/**
	 * Deletes a specific cached value.
	 *
	 * @param string $key Cache key (without prefix).
	 */
	public static function delete( string $key ): bool {
		return delete_transient( 'attributehub_' . $key );
	}

	/**
	 * Clears all AttributeHub transient caches.
	 * Called on plugin deactivation, uninstall, and mapping changes.
	 */
	public static function flush_all(): void {
		global $wpdb;

		$wpdb->query( // phpcs:ignore WordPress.DB.DirectDatabaseQuery
			$wpdb->prepare(
				"DELETE FROM {$wpdb->options} WHERE option_name LIKE %s OR option_name LIKE %s", // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
				$wpdb->esc_like( '_transient_attributehub_' ) . '%',
				$wpdb->esc_like( '_transient_timeout_attributehub_' ) . '%'
			)
		);
	}

	/**
	 * Clears caches for a specific taxonomy.
	 *
	 * @param string $taxonomy Taxonomy slug, e.g. 'pa_color'.
	 */
	public static function flush_taxonomy( string $taxonomy ): void {
		$suffix = sanitize_key( $taxonomy );
		self::delete( 'mapping_' . $suffix );
		self::delete( 'master_list_' . $suffix );
		self::delete( 'master_list_' . $suffix . '_all' ); // also flush include_hidden=true variant
		self::delete( 'unmapped_' . $suffix );
	}

	/**
	 * Builds a cache key for a taxonomy mapping map.
	 *
	 * @param string $taxonomy
	 * @return string
	 */
	public static function mapping_key( string $taxonomy ): string {
		return 'mapping_' . sanitize_key( $taxonomy );
	}

	/**
	 * Builds a cache key for a taxonomy's master group list.
	 */
	public static function master_list_key( string $taxonomy ): string {
		return 'master_list_' . sanitize_key( $taxonomy );
	}
}
