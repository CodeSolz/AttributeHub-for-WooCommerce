<?php
/**
 * Plugin Name:       AttributeHub for WooCommerce
 * Plugin URI:        https://codesolz.net/our-products/wordpress-plugin/attributehub-for-woocommerce/
 * Description:       Master attribute mapping for WooCommerce. Map messy supplier codes and imported attribute values to clean customer-facing filters — without touching your backend data.
 * Version:           1.0.0
 * Author:            CodeSolz
 * Author URI:        https://codesolz.net
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       attributehub-for-woocommerce
 * Domain Path:       /languages
 * Requires at least: 5.8
 * Requires PHP:      8.0
 * Requires Plugins:  woocommerce
 * WC requires at least: 6.0
 * WC tested up to:   9.9
 *
 * @package AttributeHub
 */

defined( 'ABSPATH' ) || exit;

// Plugin constants
define( 'ATTRIBUTEHUB_VERSION',    '1.0.0' );
define( 'ATTRIBUTEHUB_DB_VERSION', '1.0.0' );
define( 'ATTRIBUTEHUB_FILE',       __FILE__ );
define( 'ATTRIBUTEHUB_DIR',        plugin_dir_path( __FILE__ ) );
define( 'ATTRIBUTEHUB_URL',        plugin_dir_url( __FILE__ ) );
define( 'ATTRIBUTEHUB_SLUG',       'attributehub-for-woocommerce' );
define( 'ATTRIBUTEHUB_BASENAME',   plugin_basename( __FILE__ ) );

// Composer autoloader
if ( file_exists( ATTRIBUTEHUB_DIR . 'vendor/autoload.php' ) ) {
	require_once ATTRIBUTEHUB_DIR . 'vendor/autoload.php';
} else {
	// Autoload fallback: manual PSR-4 loader for environments without Composer
	spl_autoload_register( function ( $class ) {
		$prefix    = 'AttributeHub\\Free\\';
		$base_dir  = ATTRIBUTEHUB_DIR . 'src/';
		$len       = strlen( $prefix );

		if ( strncmp( $prefix, $class, $len ) !== 0 ) {
			return;
		}

		$relative_class = substr( $class, $len );
		$file           = $base_dir . str_replace( '\\', '/', $relative_class ) . '.php';

		if ( file_exists( $file ) ) {
			require $file;
		}
	} );
}

// Activation + deactivation hooks
register_activation_hook( __FILE__, array( 'AttributeHub\\Free\\Install\\Activator', 'activate' ) );
register_deactivation_hook( __FILE__, array( 'AttributeHub\\Free\\Install\\Deactivator', 'deactivate' ) );

// Declare WooCommerce HPOS compatibility
add_action( 'before_woocommerce_init', function () {
	if ( class_exists( \Automattic\WooCommerce\Utilities\FeaturesUtil::class ) ) {
		\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', __FILE__, true );
	}
} );

// Boot plugin after WooCommerce has loaded (priority 10)
add_action( 'plugins_loaded', function () {
	if ( ! class_exists( 'WooCommerce' ) ) {
		add_action( 'admin_notices', array( 'AttributeHub\\Free\\Install\\Dependencies', 'missing_wc_notice' ) );
		return;
	}

	if ( ! AttributeHub\Free\Install\Dependencies::check_wc_version() ) {
		add_action( 'admin_notices', array( 'AttributeHub\\Free\\Install\\Dependencies', 'outdated_wc_notice' ) );
		return;
	}

	AttributeHub\Free\Plugin::instance();
}, 10 );

/**
 * Global helper function to access the plugin instance.
 *
 * @return AttributeHub\Free\Plugin
 */
function attributehub(): AttributeHub\Free\Plugin {
	return AttributeHub\Free\Plugin::instance();
}
