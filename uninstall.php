<?php
/**
 * AttributeHub for WooCommerce — Uninstall
 *
 * Runs when the plugin is deleted (not deactivated).
 * Drops all plugin tables and removes all plugin options/transients.
 *
 * @package AttributeHub
 */

defined( 'WP_UNINSTALL_PLUGIN' ) || exit;

global $wpdb;

// Respect the "delete data on uninstall" setting
$settings = get_option( 'attributehub_settings', array() );
if ( empty( $settings['delete_data_on_uninstall'] ) ) {
	return;
}

// Drop free plugin tables
$tables = array(
	$wpdb->prefix . 'ah_master_groups',
	$wpdb->prefix . 'ah_value_mappings',
	$wpdb->prefix . 'ah_scan_results',
	// Pro tables (safe to attempt even if they don't exist)
	$wpdb->prefix . 'ah_secondary_tags',
	$wpdb->prefix . 'ah_mapping_rules',
	$wpdb->prefix . 'ah_filter_analytics',
);

foreach ( $tables as $table ) {
	$wpdb->query( "DROP TABLE IF EXISTS `{$table}`" ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery,WordPress.DB.PreparedSQL.NotPrepared -- table name comes from $wpdb->prefix, not user input
}

// Delete all plugin options
$options = array(
	'attributehub_db_version',
	'attributehub_pro_db_version',
	'attributehub_settings',
	'attributehub_pro_license',
);

foreach ( $options as $option ) {
	delete_option( $option );
}

// Delete per-taxonomy last-scan options
$wpdb->query( // phpcs:ignore WordPress.DB.DirectDatabaseQuery
	$wpdb->prepare(
		"DELETE FROM {$wpdb->options} WHERE option_name LIKE %s", // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$wpdb->esc_like( 'attributehub_last_scan_' ) . '%'
	)
);

// Delete all AttributeHub transients
$wpdb->query( // phpcs:ignore WordPress.DB.DirectDatabaseQuery
	$wpdb->prepare(
		"DELETE FROM {$wpdb->options} WHERE option_name LIKE %s OR option_name LIKE %s", // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$wpdb->esc_like( '_transient_attributehub_' ) . '%',
		$wpdb->esc_like( '_transient_timeout_attributehub_' ) . '%'
	)
);
