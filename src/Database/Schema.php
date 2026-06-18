<?php
/**
 * AttributeHub — Database Schema
 *
 * @package AttributeHub\Free\Database
 */

namespace AttributeHub\Free\Database;

defined( 'ABSPATH' ) || exit;

/**
 * Holds table name registry and CREATE TABLE SQL for all plugin tables.
 * All repositories must get table names from this class — no hardcoded strings.
 */
class Schema {

	// -------------------------------------------------------------------------
	// Table name registry
	// -------------------------------------------------------------------------

	/**
	 * Returns the master_groups table name.
	 */
	public static function master_groups(): string {
		return $GLOBALS['wpdb']->prefix . 'ah_master_groups';
	}

	/**
	 * Returns the value_mappings table name.
	 */
	public static function value_mappings(): string {
		return $GLOBALS['wpdb']->prefix . 'ah_value_mappings';
	}

	/**
	 * Returns the scan_results table name.
	 */
	public static function scan_results(): string {
		return $GLOBALS['wpdb']->prefix . 'ah_scan_results';
	}

	/**
	 * Returns the secondary_tags table name (Pro).
	 */
	public static function secondary_tags(): string {
		return $GLOBALS['wpdb']->prefix . 'ah_secondary_tags';
	}

	/**
	 * Returns the mapping_rules table name (Pro).
	 */
	public static function mapping_rules(): string {
		return $GLOBALS['wpdb']->prefix . 'ah_mapping_rules';
	}

	/**
	 * Returns the filter_analytics table name (Pro).
	 */
	public static function filter_analytics(): string {
		return $GLOBALS['wpdb']->prefix . 'ah_filter_analytics';
	}

	/**
	 * Returns the audit_log table name (Pro).
	 */
	public static function audit_log(): string {
		return $GLOBALS['wpdb']->prefix . 'ah_audit_log';
	}

	// -------------------------------------------------------------------------
	// CREATE TABLE SQL (for dbDelta)
	// -------------------------------------------------------------------------

	/**
	 * Returns the CREATE TABLE SQL for all free plugin tables.
	 * dbDelta() is safe to call repeatedly — only makes changes when schema differs.
	 *
	 * @param string $charset_collate Result of $wpdb->get_charset_collate()
	 * @return string[] Array of SQL statements.
	 */
	public static function get_free_tables_sql( string $charset_collate ): array {
		$master_groups  = self::master_groups();
		$value_mappings = self::value_mappings();
		$scan_results   = self::scan_results();

		return array(
			// Table 1: ah_master_groups
			"CREATE TABLE {$master_groups} (
				id          BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
				label       VARCHAR(191) NOT NULL,
				slug        VARCHAR(191) NOT NULL,
				taxonomy    VARCHAR(191) NOT NULL,
				description TEXT,
				sort_order  INT(11) NOT NULL DEFAULT 0,
				is_hidden   TINYINT(1) NOT NULL DEFAULT 0,
				meta        LONGTEXT,
				created_at  DATETIME NOT NULL,
				updated_at  DATETIME NOT NULL,
				PRIMARY KEY  (id),
				UNIQUE KEY   slug_taxonomy (slug(100), taxonomy(50)),
				KEY          taxonomy (taxonomy(50)),
				KEY          is_hidden (is_hidden)
			) {$charset_collate};",

			// Table 2: ah_value_mappings
			"CREATE TABLE {$value_mappings} (
				id              BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
				taxonomy        VARCHAR(191) NOT NULL,
				term_id         BIGINT(20) UNSIGNED NOT NULL,
				raw_value       VARCHAR(191) NOT NULL,
				master_group_id BIGINT(20) UNSIGNED NOT NULL,
				is_active       TINYINT(1) NOT NULL DEFAULT 1,
				mapped_by       VARCHAR(20) NOT NULL DEFAULT 'manual',
				created_at      DATETIME NOT NULL,
				updated_at      DATETIME NOT NULL,
				PRIMARY KEY     (id),
				UNIQUE KEY      term_taxonomy (term_id, taxonomy(50)),
				KEY             master_group_id (master_group_id),
				KEY             taxonomy_active (taxonomy(50), is_active),
				KEY             mapped_by (mapped_by)
			) {$charset_collate};",

			// Table 3: ah_scan_results
			"CREATE TABLE {$scan_results} (
				id            BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
				scan_run_id   VARCHAR(36) NOT NULL,
				taxonomy      VARCHAR(191) NOT NULL,
				term_id       BIGINT(20) UNSIGNED NOT NULL,
				raw_value     VARCHAR(191) NOT NULL,
				product_count INT(11) NOT NULL DEFAULT 0,
				is_mapped     TINYINT(1) NOT NULL DEFAULT 0,
				issue_type    VARCHAR(50) DEFAULT NULL,
				similar_to    BIGINT(20) UNSIGNED DEFAULT NULL,
				scanned_at    DATETIME NOT NULL,
				PRIMARY KEY   (id),
				KEY           scan_run_id (scan_run_id),
				KEY           taxonomy_mapped (taxonomy(50), is_mapped),
				KEY           issue_type (issue_type),
				KEY           scanned_at (scanned_at)
			) {$charset_collate};",
		);
	}

	/**
	 * Returns the CREATE TABLE SQL for all Pro plugin tables.
	 *
	 * @param string $charset_collate
	 * @return string[]
	 */
	public static function get_pro_tables_sql( string $charset_collate ): array {
		$secondary_tags    = self::secondary_tags();
		$mapping_rules     = self::mapping_rules();
		$filter_analytics  = self::filter_analytics();
		$audit_log         = self::audit_log();

		return array(
			// Pro Table 1: ah_secondary_tags
			"CREATE TABLE {$secondary_tags} (
				id              BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
				product_id      BIGINT(20) UNSIGNED NOT NULL,
				taxonomy        VARCHAR(191) NOT NULL,
				master_group_id BIGINT(20) UNSIGNED NOT NULL,
				assigned_by     VARCHAR(20) NOT NULL DEFAULT 'manual',
				created_at      DATETIME NOT NULL,
				PRIMARY KEY     (id),
				UNIQUE KEY      product_taxonomy_group (product_id, taxonomy(50), master_group_id),
				KEY             taxonomy_group (taxonomy(50), master_group_id),
				KEY             product_id (product_id)
			) {$charset_collate};",

			// Pro Table 2: ah_mapping_rules
			"CREATE TABLE {$mapping_rules} (
				id              BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
				taxonomy        VARCHAR(191) NOT NULL,
				rule_type       VARCHAR(30) NOT NULL,
				pattern         VARCHAR(191) NOT NULL,
				master_group_id BIGINT(20) UNSIGNED NOT NULL,
				is_active       TINYINT(1) NOT NULL DEFAULT 1,
				sort_order      INT(11) NOT NULL DEFAULT 0,
				created_at      DATETIME NOT NULL,
				PRIMARY KEY     (id),
				KEY             taxonomy_active_order (taxonomy(50), is_active, sort_order)
			) {$charset_collate};",

			// Pro Table 3: ah_filter_analytics
			"CREATE TABLE {$filter_analytics} (
				id               BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
				taxonomy         VARCHAR(191) NOT NULL,
				master_group_id  BIGINT(20) UNSIGNED NOT NULL,
				click_date       DATE NOT NULL,
				click_count      INT(11) NOT NULL DEFAULT 0,
				conversion_count INT(11) NOT NULL DEFAULT 0,
				session_hash     VARCHAR(32) DEFAULT NULL,
				PRIMARY KEY      (id),
				UNIQUE KEY       taxonomy_group_date (taxonomy(50), master_group_id, click_date),
				KEY              click_date (click_date),
				KEY              master_group_id (master_group_id)
			) {$charset_collate};",

			// Pro Table 4: ah_audit_log
			"CREATE TABLE {$audit_log} (
				id               BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
				action_type      VARCHAR(30) NOT NULL,
				actor_id         BIGINT(20) UNSIGNED NOT NULL DEFAULT 0,
				taxonomy         VARCHAR(191) NOT NULL,
				term_id          BIGINT(20) UNSIGNED DEFAULT NULL,
				master_group_id  BIGINT(20) UNSIGNED DEFAULT NULL,
				old_value        TEXT DEFAULT NULL,
				new_value        TEXT DEFAULT NULL,
				source           VARCHAR(30) NOT NULL DEFAULT 'manual',
				created_at       DATETIME NOT NULL,
				PRIMARY KEY      (id),
				KEY              action_type (action_type),
				KEY              actor_id (actor_id),
				KEY              taxonomy (taxonomy(50)),
				KEY              created_at (created_at)
			) {$charset_collate};",
		);
	}
}
