<?php
/**
 * AttributeHub — Script/Style Enqueuing
 *
 * @package AttributeHub\Free\Admin
 */

namespace AttributeHub\Free\Admin;

defined( 'ABSPATH' ) || exit;

/**
 * Handles enqueueing of admin and frontend CSS/JS.
 */
class EnqueueScripts {

	/** Screens where admin scripts should load */
	const ADMIN_SCREENS = array(
		'toplevel_page_attributehub',
		'attributehub_page_attributehub-scanner',
		'attributehub_page_attributehub-masters',
		'attributehub_page_attributehub-mappings',
		'attributehub_page_attributehub-preview',
		'attributehub_page_attributehub-settings',
	);

	/**
	 * Enqueues admin scripts on AttributeHub admin pages.
	 * Hooked to admin_enqueue_scripts.
	 *
	 * @param string $hook_suffix Current admin page hook suffix.
	 */
	public function enqueue_admin( string $hook_suffix ): void {
		if ( ! $this->is_ah_admin_page( $hook_suffix ) && ! $this->is_product_edit_page( $hook_suffix ) ) {
			return;
		}

		// Admin CSS
		wp_enqueue_style(
			'attributehub-admin',
			ATTRIBUTEHUB_URL . 'assets/css/admin.css',
			array(),
			ATTRIBUTEHUB_VERSION
		);

		// Admin JS (depends on jQuery + jQuery UI sortable + draggable)
		wp_enqueue_script( 'jquery-ui-sortable' );
		wp_enqueue_script( 'jquery-ui-draggable' );
		wp_enqueue_script( 'jquery-ui-droppable' );

		// SweetAlert2 — bundled locally; do not load from CDN.
		if ( ! wp_script_is( 'sweetalert2', 'registered' ) ) {
			wp_register_script(
				'sweetalert2',
				ATTRIBUTEHUB_URL . 'assets/js/plugins/sweetalert2.all.min.js',
				array(),
				'11.26.25',
				true
			);
		}
		wp_enqueue_script( 'sweetalert2' );

		if ( ! wp_style_is( 'sweetalert2', 'registered' ) ) {
			wp_register_style(
				'sweetalert2',
				ATTRIBUTEHUB_URL . 'assets/js/plugins/sweetalert2.min.css',
				array(),
				'11.26.25'
			);
		}
		wp_enqueue_style( 'sweetalert2' );

		wp_enqueue_script(
			'attributehub-admin',
			ATTRIBUTEHUB_URL . 'assets/js/admin.js',
			array( 'jquery', 'jquery-ui-sortable', 'jquery-ui-draggable', 'jquery-ui-droppable', 'sweetalert2', 'wp-i18n' ),
			ATTRIBUTEHUB_VERSION,
			true
		);

		// Localize script data
		wp_localize_script(
			'attributehub-admin',
			'attributehubAdmin',
			array(
				'ajaxUrl'   => admin_url( 'admin-ajax.php' ),
				'nonce'     => wp_create_nonce( 'attributehub_admin_nonce' ),
				'strings'   => array(
					'mapSuccess'    => __( 'Mapping saved.', 'attributehub-for-woocommerce' ),
					'mapError'      => __( 'Failed to save mapping.', 'attributehub-for-woocommerce' ),
					'confirmDelete' => __( 'Are you sure you want to delete this master group?', 'attributehub-for-woocommerce' ),
					'scanning'      => __( 'Scanning attributes...', 'attributehub-for-woocommerce' ),
					'scanComplete'  => __( 'Scan complete.', 'attributehub-for-woocommerce' ),
					'loading'       => __( 'Loading...', 'attributehub-for-woocommerce' ),
					'selectMaster'  => __( 'Select existing master…', 'attributehub-for-woocommerce' ),
				),
				'isPro'      => attributehub()->is_pro(),
				'upgradeUrl' => 'https://codesolz.net/our-products/wordpress-plugin/attributehub-for-woocommerce',
				'adminUrl'   => admin_url( 'admin.php' ),
			)
		);

		// Scanner page: load scanner JS with progress bar support
		if ( $hook_suffix === 'attributehub_page_attributehub-scanner' ) {
			wp_enqueue_script(
				'attributehub-scanner',
				ATTRIBUTEHUB_URL . 'assets/js/scanner.js',
				array( 'attributehub-admin' ),
				ATTRIBUTEHUB_VERSION,
				true
			);
		}

		// Allow Pro plugin to enqueue additional assets
		do_action( 'attributehub_enqueue_admin_scripts', $hook_suffix );
	}

	/**
	 * Enqueues frontend scripts on shop/archive pages.
	 * Minimal CSS only — for label override cosmetics.
	 */
	public function enqueue_frontend(): void {
		if ( ! is_shop() && ! is_product_taxonomy() ) {
			return;
		}

		wp_enqueue_style(
			'attributehub-frontend',
			ATTRIBUTEHUB_URL . 'assets/css/frontend.css',
			array(),
			ATTRIBUTEHUB_VERSION
		);

		// Allow Pro to add analytics tracking script
		do_action( 'attributehub_enqueue_frontend_scripts' );
	}

	/**
	 * Checks if the current page is an AttributeHub admin page.
	 */
	private function is_ah_admin_page( string $hook_suffix ): bool {
		return in_array( $hook_suffix, self::ADMIN_SCREENS, true )
			|| str_starts_with( $hook_suffix, 'attributehub_page_attributehub-' );
	}

	/**
	 * Checks if the current page is the WooCommerce product edit page.
	 */
	private function is_product_edit_page( string $hook_suffix ): bool {
		global $post_type;
		return $hook_suffix === 'post.php' && $post_type === 'product';
	}
}
