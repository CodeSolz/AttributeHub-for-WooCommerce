<?php
/**
 * AttributeHub — Dependency checks
 *
 * @package AttributeHub\Free\Install
 */

namespace AttributeHub\Free\Install;

defined( 'ABSPATH' ) || exit;

/**
 * Handles WooCommerce version and dependency checks.
 */
class Dependencies {

	/** Minimum WooCommerce version required */
	const MIN_WC_VERSION = '6.0';

	/**
	 * Checks if the installed WooCommerce version meets the minimum requirement.
	 */
	public static function check_wc_version(): bool {
		return defined( 'WC_VERSION' ) && version_compare( WC_VERSION, self::MIN_WC_VERSION, '>=' );
	}

	/**
	 * Admin notice: WooCommerce is not installed/active.
	 */
	public static function missing_wc_notice(): void {
		if ( ! current_user_can( 'activate_plugins' ) ) {
			return;
		}

		$message = sprintf(
			/* translators: 1: Plugin name 2: WooCommerce URL */
			__( '<strong>%1$s</strong> requires <a href="%2$s" target="_blank">WooCommerce</a> to be installed and active.', 'attributehub-for-woocommerce' ),
			'AttributeHub for WooCommerce',
			'https://wordpress.org/plugins/woocommerce/'
		);

		printf( '<div class="notice notice-error"><p>%s</p></div>', wp_kses_post( $message ) );
	}

	/**
	 * Admin notice: WooCommerce version is too old.
	 */
	public static function outdated_wc_notice(): void {
		if ( ! current_user_can( 'activate_plugins' ) ) {
			return;
		}

		$message = sprintf(
			/* translators: 1: Plugin name 2: Minimum WC version */
			__( '<strong>%1$s</strong> requires WooCommerce %2$s or higher. Please update WooCommerce.', 'attributehub-for-woocommerce' ),
			'AttributeHub for WooCommerce',
			self::MIN_WC_VERSION
		);

		printf( '<div class="notice notice-error"><p>%s</p></div>', wp_kses_post( $message ) );
	}
}
