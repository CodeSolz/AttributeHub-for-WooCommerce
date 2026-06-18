<?php
/**
 * AttributeHub — Plugin Activator
 *
 * @package AttributeHub\Free\Install
 */

namespace AttributeHub\Free\Install;

use AttributeHub\Free\Database\Schema;

defined( 'ABSPATH' ) || exit;

/**
 * Handles plugin activation: creates DB tables and sets default options.
 */
class Activator {

	/**
	 * Run on plugin activation.
	 * Called via register_activation_hook().
	 */
	public static function activate(): void {
		if ( ! current_user_can( 'activate_plugins' ) ) {
			return;
		}

		self::create_tables();
		self::set_default_options();
		self::schedule_events();

		// Flush rewrite rules on next load
		update_option( 'attributehub_flush_rewrite', 1 );
	}

	/**
	 * Creates all free plugin database tables using dbDelta().
	 */
	private static function create_tables(): void {
		global $wpdb;

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';

		$charset_collate = $wpdb->get_charset_collate();
		$sql             = Schema::get_free_tables_sql( $charset_collate );

		dbDelta( $sql );

		update_option( 'attributehub_db_version', ATTRIBUTEHUB_DB_VERSION );
	}

	/**
	 * Sets default plugin options on first activation.
	 */
	private static function set_default_options(): void {
		$defaults = array(
			'hide_unmapped'           => false,
			'cache_ttl'               => 86400,
			'ugliness_threshold'      => 40,
			'duplicate_threshold'     => 2,
			'override_term_name'      => true,
			'override_layered_nav'    => true,
			'delete_data_on_uninstall' => false,
		);

		// Only set defaults if option doesn't exist yet
		if ( false === get_option( 'attributehub_settings' ) ) {
			add_option( 'attributehub_settings', $defaults );
		}
	}

	/**
	 * Schedule any WP-Cron events needed by the free plugin.
	 */
	private static function schedule_events(): void {
		// Free plugin has no scheduled events (Pro adds scheduled scans)
	}
}
