<?php
/**
 * AttributeHub — WordPress Hooks
 *
 * @package AttributeHub\Free\Hooks
 */

namespace AttributeHub\Free\Hooks;

defined( 'ABSPATH' ) || exit;

/**
 * Registers general WordPress integration hooks:
 * plugin row meta, action links, flush rewrites, HPOS compat.
 *
 * Auto-instantiated by Plugin::load_hook_classes().
 */
class WordPressHooks {

	public function __construct() {
		add_filter( 'plugin_action_links_' . ATTRIBUTEHUB_BASENAME, array( $this, 'add_action_links' ) );
		add_filter( 'plugin_row_meta', array( $this, 'add_row_meta' ), 10, 2 );
		add_action( 'admin_init', array( $this, 'maybe_flush_rewrites' ) );
	}

	/**
	 * Adds "Settings" link to plugin list row.
	 *
	 * @param string[] $links Existing action links.
	 * @return string[]
	 */
	public function add_action_links( array $links ): array {
		$settings_link = sprintf(
			'<a href="%s">%s</a>',
			esc_url( admin_url( 'admin.php?page=attributehub-settings' ) ),
			esc_html__( 'Settings', 'attributehub-for-woocommerce' )
		);

		array_unshift( $links, $settings_link );

		if ( ! attributehub()->is_pro() ) {
			$links[] = sprintf(
				'<a href="%s" style="color:#e04f21;font-weight:bold;">%s</a>',
				'https://codesolz.net/our-products/wordpress-plugin/attributehub-for-woocommerce',
				esc_html__( 'Upgrade to Pro', 'attributehub-for-woocommerce' )
			);
		}

		return $links;
	}

	/**
	 * Adds documentation and support links to plugin row meta.
	 *
	 * @param string[] $links    Existing meta links.
	 * @param string   $file     Plugin basename.
	 * @return string[]
	 */
	public function add_row_meta( array $links, string $file ): array {
		if ( $file !== ATTRIBUTEHUB_BASENAME ) {
			return $links;
		}

		$links[] = sprintf(
			'<a href="%s" target="_blank">%s</a>',
			'https://docs.codesolz.net/attributehub-for-woocommerce',
			esc_html__( 'Documentation', 'attributehub-for-woocommerce' )
		);

		$links[] = sprintf(
			'<a href="%s" target="_blank">%s</a>',
			'https://wordpress.org/support/plugin/attributehub-for-woocommerce/',
			esc_html__( 'Support', 'attributehub-for-woocommerce' )
		);

		return $links;
	}

	/**
	 * Flushes rewrite rules once after activation.
	 */
	public function maybe_flush_rewrites(): void {
		if ( get_option( 'attributehub_flush_rewrite' ) ) {
			flush_rewrite_rules();
			delete_option( 'attributehub_flush_rewrite' );
		}
	}
}
