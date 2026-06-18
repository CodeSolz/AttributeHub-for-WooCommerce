<?php
/**
 * AttributeHub — Template Loader
 *
 * @package AttributeHub\Free\Util
 */

namespace AttributeHub\Free\Util;

defined( 'ABSPATH' ) || exit;

/**
 * Loads plugin templates. Allows themes to override via
 * wp-content/themes/my-theme/attributehub/{template}.php
 */
class TemplateLoader {

	/**
	 * Loads a template file, passing data as local variables.
	 *
	 * Template lookup order:
	 * 1. wp-content/themes/{active-theme}/attributehub/{template}
	 * 2. wp-content/plugins/attributehub-for-woocommerce/templates/{template}
	 *
	 * @param string $template Relative path, e.g. 'admin/dashboard.php'
	 * @param array  $data     Variables to extract into template scope.
	 * @param bool   $return   If true, returns output instead of printing.
	 * @return string|void
	 */
	public static function load( string $template, array $data = array(), bool $return = false ) {
		$template_file = self::locate( $template );

		if ( ! $template_file ) {
			// translators: %s is the template file path
			_doing_it_wrong( __METHOD__, sprintf( esc_html__( 'AttributeHub template not found: %s', 'attributehub-for-woocommerce' ), esc_html( $template ) ), esc_html( ATTRIBUTEHUB_VERSION ) );
			return;
		}

		if ( $return ) {
			ob_start();
		}

		// Extract data into local scope for template
		if ( ! empty( $data ) ) {
			extract( $data, EXTR_SKIP ); // phpcs:ignore WordPress.PHP.DontExtract.extract_extract
		}

		include $template_file;

		if ( $return ) {
			return ob_get_clean();
		}
	}

	/**
	 * Locates a template file, checking theme overrides first.
	 *
	 * @param string $template Relative template path.
	 * @return string|false Absolute path or false if not found.
	 */
	public static function locate( string $template ) {
		$locations = array(
			trailingslashit( get_stylesheet_directory() ) . 'attributehub/' . $template,
			trailingslashit( get_template_directory() ) . 'attributehub/' . $template,
			ATTRIBUTEHUB_DIR . 'templates/' . $template,
		);

		foreach ( $locations as $location ) {
			if ( file_exists( $location ) ) {
				return $location;
			}
		}

		return false;
	}
}
