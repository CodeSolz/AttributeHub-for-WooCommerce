<?php
/**
 * AttributeHub — Admin Menu Registrar
 *
 * @package AttributeHub\Free\Admin
 */

namespace AttributeHub\Free\Admin;

defined( 'ABSPATH' ) || exit;

/**
 * Registers the AttributeHub top-level admin menu and all submenus.
 */
class MenuRegistrar {

	/**
	 * Registers menus. Called on admin_menu hook.
	 */
	public function register(): void {
		// Top-level menu
		add_menu_page(
			__( 'AttributeHub', 'attributehub-for-woocommerce' ),
			__( 'AttributeHub', 'attributehub-for-woocommerce' ),
			'manage_woocommerce',
			'attributehub',
			array( $this, 'render_dashboard' ),
			$this->get_menu_icon(),
			56 // Position: after WooCommerce (55)
		);

		// Dashboard (mirrors top-level)
		add_submenu_page(
			'attributehub',
			__( 'Dashboard', 'attributehub-for-woocommerce' ),
			__( 'Dashboard', 'attributehub-for-woocommerce' ),
			'manage_woocommerce',
			'attributehub',
			array( $this, 'render_dashboard' )
		);

		// Attribute Scanner
		add_submenu_page(
			'attributehub',
			__( 'Attribute Scanner', 'attributehub-for-woocommerce' ),
			__( 'Scanner', 'attributehub-for-woocommerce' ),
			'manage_woocommerce',
			'attributehub-scanner',
			array( $this, 'render_scanner' )
		);

		// Master Directory
		add_submenu_page(
			'attributehub',
			__( 'Master Attribute Directory', 'attributehub-for-woocommerce' ),
			__( 'Master Labels', 'attributehub-for-woocommerce' ),
			'manage_woocommerce',
			'attributehub-masters',
			array( $this, 'render_masters' )
		);

		// Mapping Editor
		add_submenu_page(
			'attributehub',
			__( 'Mapping Editor', 'attributehub-for-woocommerce' ),
			__( 'Map Values', 'attributehub-for-woocommerce' ),
			'manage_woocommerce',
			'attributehub-mappings',
			array( $this, 'render_mappings' )
		);

		// Preview
		add_submenu_page(
			'attributehub',
			__( 'Filter Preview', 'attributehub-for-woocommerce' ),
			__( 'Preview', 'attributehub-for-woocommerce' ),
			'manage_woocommerce',
			'attributehub-preview',
			array( $this, 'render_preview' )
		);

		// Settings
		add_submenu_page(
			'attributehub',
			__( 'AttributeHub Settings', 'attributehub-for-woocommerce' ),
			__( 'Settings', 'attributehub-for-woocommerce' ),
			'manage_woocommerce',
			'attributehub-settings',
			array( $this, 'render_settings' )
		);

		// Allow Pro plugin to add its own submenus
		do_action( 'attributehub_register_pro_menus' );
	}

	/**
	 * Renders the Dashboard admin page.
	 */
	public function render_dashboard(): void {
		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			wp_die( esc_html__( 'You do not have permission to access this page.', 'attributehub-for-woocommerce' ) );
		}
		pages\DashboardPage::render();
	}

	/**
	 * Renders the Scanner admin page.
	 */
	public function render_scanner(): void {
		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			wp_die( esc_html__( 'You do not have permission to access this page.', 'attributehub-for-woocommerce' ) );
		}
		pages\ScannerPage::render();
	}

	/**
	 * Renders the Master Directory admin page.
	 */
	public function render_masters(): void {
		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			wp_die( esc_html__( 'You do not have permission to access this page.', 'attributehub-for-woocommerce' ) );
		}
		pages\MasterDirectoryPage::render();
	}

	/**
	 * Renders the Mapping Editor admin page.
	 */
	public function render_mappings(): void {
		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			wp_die( esc_html__( 'You do not have permission to access this page.', 'attributehub-for-woocommerce' ) );
		}
		pages\MappingEditorPage::render();
	}

	/**
	 * Renders the Preview admin page.
	 */
	public function render_preview(): void {
		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			wp_die( esc_html__( 'You do not have permission to access this page.', 'attributehub-for-woocommerce' ) );
		}
		pages\PreviewPage::render();
	}

	/**
	 * Renders the Settings admin page.
	 */
	public function render_settings(): void {
		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			wp_die( esc_html__( 'You do not have permission to access this page.', 'attributehub-for-woocommerce' ) );
		}
		pages\SettingsPage::render();
	}

	/**
	 * Returns the SVG icon for the menu.
	 * Uses a data URI of a simple mapping/filter icon.
	 */
	private function get_menu_icon(): string {
		// Simple SVG: two rectangles connected by an arrow (mapping concept)
		$svg = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
			<rect x="1" y="6" width="8" height="4" rx="1"/>
			<rect x="15" y="6" width="8" height="4" rx="1"/>
			<line x1="9" y1="8" x2="15" y2="8"/>
			<polyline points="13,6 15,8 13,10"/>
			<rect x="1" y="14" width="8" height="4" rx="1"/>
			<rect x="15" y="14" width="8" height="4" rx="1"/>
			<line x1="9" y1="16" x2="15" y2="16"/>
			<polyline points="13,14 15,16 13,18"/>
		</svg>';

		return 'data:image/svg+xml;base64,' . base64_encode( $svg ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode
	}
}
