<?php
/**
 * AttributeHub — WooCommerce Hooks
 *
 * @package AttributeHub\Free\Hooks
 */

namespace AttributeHub\Free\Hooks;

use AttributeHub\Free\Admin\MenuRegistrar;
use AttributeHub\Free\Admin\EnqueueScripts;
use AttributeHub\Free\Admin\AjaxHandler;
use AttributeHub\Free\Admin\ProductMetabox;
use AttributeHub\Free\Frontend\LayeredNavIntegration;

defined( 'ABSPATH' ) || exit;

/**
 * Centralised registration of all WooCommerce-related hooks.
 * Admin and frontend hooks are split by is_admin() check.
 *
 * Auto-instantiated by Plugin::load_hook_classes().
 */
class WooCommerceHooks {

	public function __construct() {
		// Admin hooks
		if ( is_admin() ) {
			$this->register_admin_hooks();
		}

		// Frontend hooks (and ajax which runs on both)
		$this->register_shared_hooks();

		if ( ! is_admin() ) {
			$this->register_frontend_hooks();
		}
	}

	/**
	 * Admin-only hooks.
	 */
	private function register_admin_hooks(): void {
		// Admin menus
		$menu = new MenuRegistrar();
		add_action( 'admin_menu', array( $menu, 'register' ) );

		// Admin scripts
		$enqueue = new EnqueueScripts();
		add_action( 'admin_enqueue_scripts', array( $enqueue, 'enqueue_admin' ) );

		// Product metabox
		$metabox = new ProductMetabox();
		add_action( 'add_meta_boxes', array( $metabox, 'register' ) );
	}

	/**
	 * Hooks that run in both admin and frontend contexts (e.g. AJAX).
	 */
	private function register_shared_hooks(): void {
		// AJAX dispatcher — runs on wp_ajax_* (admin-ajax.php, always admin context)
		$ajax = new AjaxHandler();
		$ajax->register_hooks();
	}

	/**
	 * Frontend-only hooks.
	 */
	private function register_frontend_hooks(): void {
		// Frontend scripts/styles
		$enqueue = new EnqueueScripts();
		add_action( 'wp_enqueue_scripts', array( $enqueue, 'enqueue_frontend' ) );

		// Layered nav integration (filter override + term display)
		LayeredNavIntegration::instance()->register_hooks();
	}
}
