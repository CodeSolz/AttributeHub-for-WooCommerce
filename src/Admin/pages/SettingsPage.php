<?php
/**
 * AttributeHub — Settings Page
 *
 * @package AttributeHub\Free\Admin\pages
 */

namespace AttributeHub\Free\Admin\pages;

use AttributeHub\Free\Util\Sanitizer;

defined( 'ABSPATH' ) || exit;

/**
 * Renders the tabbed Settings admin page and handles settings save.
 */
class SettingsPage {

	/** Settings option key */
	const OPTION_KEY = 'attributehub_settings';

	/** Settings schema: key => type */
	const SCHEMA = array(
		'hide_unmapped'           => 'bool',
		'cache_ttl'               => 'int',
		'ugliness_threshold'      => 'int',
		'duplicate_threshold'     => 'int',
		'override_term_name'      => 'bool',
		'override_layered_nav'    => 'bool',
		'delete_data_on_uninstall' => 'bool',
	);

	public static function render(): void {
		$page = new self();

		// Handle form submission
		if ( isset( $_POST['attributehub_settings_nonce'] ) ) {
			$page->save_settings();
		}

		$page->output();
	}

	public function output(): void {
		// Show success notice after redirect-on-save
		if ( ! empty( $_GET['settings-updated'] ) ) {
			add_settings_error( 'attributehub_settings', 'settings_updated', __( 'Settings saved.', 'attributehub-for-woocommerce' ), 'updated' );
		}

		$settings = get_option( self::OPTION_KEY, array() );

		// Apply defaults for missing keys
		$settings = wp_parse_args( $settings, array(
			'hide_unmapped'            => false,
			'cache_ttl'                => 86400,
			'ugliness_threshold'       => 40,
			'duplicate_threshold'      => 2,
			'override_term_name'       => true,
			'override_layered_nav'     => true,
			'delete_data_on_uninstall' => false,
		) );

		$tabs = apply_filters( 'attributehub_settings_tabs', array(
			'general' => __( 'General', 'attributehub-for-woocommerce' ),
			'display' => __( 'Display', 'attributehub-for-woocommerce' ),
			'scanner' => __( 'Scanner', 'attributehub-for-woocommerce' ),
		) );

		$active_tab = sanitize_key( wp_unslash( $_GET['tab'] ?? 'general' ) );

		\AttributeHub\Free\Util\TemplateLoader::load(
			'admin/settings.php',
			array(
				'settings' => $settings,
				'tabs'     => $tabs,
				'active'   => $active_tab,
			)
		);
	}

	/**
	 * AJAX: Saves settings and returns JSON — used by the AJAX form submit handler.
	 */
	public function save_settings_ajax(): void {
		check_ajax_referer( 'attributehub_admin_nonce', 'attributehub_nonce' );
		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			wp_send_json_error( array( 'message' => __( 'Insufficient permissions.', 'attributehub-for-woocommerce' ) ), 403 );
			wp_die();
		}

		// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- sanitized below via Sanitizer
		$raw_settings = isset( $_POST['attributehub_settings'] ) && is_array( $_POST['attributehub_settings'] )
			? wp_unslash( $_POST['attributehub_settings'] )
			: array();

		$active_tab = sanitize_key( wp_unslash( $_POST['ah_active_tab'] ?? 'general' ) );

		// Unchecked checkboxes are absent from POST — force bool fields to false for the active tab.
		if ( 'general' === $active_tab ) {
			foreach ( self::SCHEMA as $key => $type ) {
				if ( 'bool' === $type && ! isset( $raw_settings[ $key ] ) ) {
					$raw_settings[ $key ] = false;
				}
			}
		}

		$sanitized = Sanitizer::settings_array( $raw_settings, self::SCHEMA );
		$existing  = get_option( self::OPTION_KEY, array() );
		$merged    = array_merge( $existing, $sanitized );

		update_option( self::OPTION_KEY, $merged );
		\AttributeHub\Free\Database\QueryCache::flush_all();

		wp_send_json_success( array( 'message' => __( 'Settings saved.', 'attributehub-for-woocommerce' ) ) );
	}

	/**
	 * AJAX: Flushes all mapping caches.
	 */
	public function flush_cache(): void {
		check_ajax_referer( 'attributehub_admin_nonce', 'attributehub_nonce' );
		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			wp_send_json_error( array( 'message' => __( 'Insufficient permissions.', 'attributehub-for-woocommerce' ) ), 403 );
			wp_die();
		}

		\AttributeHub\Free\Database\QueryCache::flush_all();
		wp_send_json_success( array( 'message' => __( 'Cache cleared.', 'attributehub-for-woocommerce' ) ) );
	}

	/**
	 * Handles settings form submission and redirects back on success.
	 */
	private function save_settings(): void {
		if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['attributehub_settings_nonce'] ) ), 'attributehub_save_settings' ) ) {
			add_settings_error( 'attributehub_settings', 'nonce_failed', __( 'Security check failed. Please try again.', 'attributehub-for-woocommerce' ), 'error' );
			return;
		}

		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			add_settings_error( 'attributehub_settings', 'no_permission', __( 'You do not have permission to change settings.', 'attributehub-for-woocommerce' ), 'error' );
			return;
		}

		// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- sanitized below via Sanitizer
		$raw_settings = isset( $_POST['attributehub_settings'] ) && is_array( $_POST['attributehub_settings'] )
			? wp_unslash( $_POST['attributehub_settings'] )
			: array();

		$sanitized = Sanitizer::settings_array( $raw_settings, self::SCHEMA );

		// Merge with existing (preserve keys not present on this tab's form)
		$existing = get_option( self::OPTION_KEY, array() );
		$merged   = array_merge( $existing, $sanitized );

		update_option( self::OPTION_KEY, $merged );

		\AttributeHub\Free\Database\QueryCache::flush_all();

		// Redirect after save (prevents duplicate submission on page refresh)
		$active_tab = sanitize_key( wp_unslash( $_POST['ah_active_tab'] ?? 'general' ) );
		wp_safe_redirect( add_query_arg(
			array(
				'page'             => 'attributehub-settings',
				'tab'              => $active_tab,
				'settings-updated' => '1',
			),
			admin_url( 'admin.php' )
		) );
		exit;
	}
}
