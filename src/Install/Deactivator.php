<?php
/**
 * AttributeHub — Plugin Deactivator
 *
 * @package AttributeHub\Free\Install
 */

namespace AttributeHub\Free\Install;

defined( 'ABSPATH' ) || exit;

/**
 * Handles plugin deactivation: flushes caches, clears scheduled events.
 * Does NOT delete data (that happens in uninstall.php).
 */
class Deactivator {

	/**
	 * Run on plugin deactivation.
	 * Called via register_deactivation_hook().
	 */
	public static function deactivate(): void {
		self::clear_transients();
		self::unschedule_events();
		flush_rewrite_rules();
	}

	/**
	 * Clears all AttributeHub transient caches.
	 */
	private static function clear_transients(): void {
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
	 * Removes any WP-Cron events scheduled by the plugin.
	 */
	private static function unschedule_events(): void {
		$cron_hooks = array(
			'attributehub_scheduled_attribute_scan',
			'attributehub_daily_license_check',
			'attributehub_send_unmapped_report',
		);

		foreach ( $cron_hooks as $hook ) {
			$timestamp = wp_next_scheduled( $hook );
			if ( $timestamp ) {
				wp_unschedule_event( $timestamp, $hook );
			}
		}
	}
}
