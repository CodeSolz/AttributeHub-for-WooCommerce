<?php
/**
 * AttributeHub — Schema Updater
 *
 * @package AttributeHub\Free\Install
 */

namespace AttributeHub\Free\Install;

use AttributeHub\Free\Database\Schema;

defined( 'ABSPATH' ) || exit;

/**
 * Handles database schema migrations on plugin version upgrades.
 * Runs on every plugins_loaded to detect version drift.
 */
class Updater {

	/**
	 * Checks if a DB update is needed and runs it.
	 * Called from Plugin::__construct() via run_updater().
	 */
	public static function maybe_update(): void {
		$installed_version = get_option( 'attributehub_db_version', '0.0.0' );

		if ( version_compare( $installed_version, ATTRIBUTEHUB_DB_VERSION, '<' ) ) {
			self::run_updates( $installed_version );
		}
	}

	/**
	 * Runs all necessary updates from the installed version to current.
	 *
	 * @param string $from_version The currently installed DB version.
	 */
	private static function run_updates( string $from_version ): void {
		global $wpdb;

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';

		$charset_collate = $wpdb->get_charset_collate();

		// Re-run dbDelta for all tables (safe — only modifies if schema differs)
		dbDelta( Schema::get_free_tables_sql( $charset_collate ) );

		// Version-specific migrations go here as the plugin evolves:
		// if ( version_compare( $from_version, '1.1.0', '<' ) ) {
		//     self::migrate_to_110();
		// }

		update_option( 'attributehub_db_version', ATTRIBUTEHUB_DB_VERSION );

		// Flush transient caches after schema update
		$wpdb->query( // phpcs:ignore WordPress.DB.DirectDatabaseQuery
			$wpdb->prepare(
				"DELETE FROM {$wpdb->options} WHERE option_name LIKE %s OR option_name LIKE %s", // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
				$wpdb->esc_like( '_transient_ah_' ) . '%',
				$wpdb->esc_like( '_transient_timeout_ah_' ) . '%'
			)
		);
	}
}
