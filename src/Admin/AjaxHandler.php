<?php
/**
 * AttributeHub — AJAX Handler (Central Dispatcher)
 *
 * @package AttributeHub\Free\Admin
 */

namespace AttributeHub\Free\Admin;

defined( 'ABSPATH' ) || exit;

/**
 * Central AJAX dispatcher for all AttributeHub admin AJAX calls.
 *
 * Request format:
 *   action: 'attributehub_ajax'
 *   attributehub_nonce: [nonce]
 *   attributehub_method: 'ClassName@method_name'
 *   ...additional params
 */
class AjaxHandler {

	/**
	 * Registers the AJAX action. Called by WooCommerceHooks.
	 */
	public function register_hooks(): void {
		add_action( 'wp_ajax_attributehub_ajax', array( $this, 'dispatch' ) );
	}

	/**
	 * Returns the explicit allowlist of permitted AJAX handler class–method pairs.
	 *
	 * Keys match the `attributehub_method` value sent by JavaScript.
	 * Values are [ FQCN, method_name ].
	 *
	 * @return array<string, array{0: string, 1: string}>
	 */
	private function get_allowed_methods(): array {
		$allowed = array(
			'DashboardPage@ajax_scan'             => array( pages\DashboardPage::class,        'ajax_scan'       ),
			'ScannerPage@run_scan'                => array( pages\ScannerPage::class,           'run_scan'        ),
			'ScannerPage@get_results'             => array( pages\ScannerPage::class,           'get_results'     ),
			'MasterDirectoryPage@create_master'   => array( pages\MasterDirectoryPage::class,   'create_master'   ),
			'MasterDirectoryPage@update_master'   => array( pages\MasterDirectoryPage::class,   'update_master'   ),
			'MasterDirectoryPage@delete_master'   => array( pages\MasterDirectoryPage::class,   'delete_master'   ),
			'MasterDirectoryPage@reorder_masters' => array( pages\MasterDirectoryPage::class,   'reorder_masters' ),
			'MappingEditorPage@map_term'          => array( pages\MappingEditorPage::class,     'map_term'        ),
			'MappingEditorPage@unmap_term'        => array( pages\MappingEditorPage::class,     'unmap_term'      ),
			'MappingEditorPage@bulk_map'          => array( pages\MappingEditorPage::class,     'bulk_map'        ),
			'MappingEditorPage@unmap_all'         => array( pages\MappingEditorPage::class,     'unmap_all'       ),
			'MappingEditorPage@get_masters'       => array( pages\MappingEditorPage::class,     'get_masters'     ),
			'MappingEditorPage@get_unmapped'      => array( pages\MappingEditorPage::class,     'get_unmapped'    ),
			'MappingEditorPage@export_csv'        => array( pages\MappingEditorPage::class,     'export_csv'      ),
			'SettingsPage@flush_cache'            => array( pages\SettingsPage::class,          'flush_cache'          ),
			'SettingsPage@save_settings_ajax'     => array( pages\SettingsPage::class,          'save_settings_ajax'   ),
		);

		/**
		 * Allows the Pro plugin to register additional AJAX handlers.
		 * Each entry must be: 'ClassName@method_name' => [ FQCN, 'method_name' ]
		 */
		return apply_filters( 'attributehub_ajax_allowed_methods', $allowed );
	}

	/**
	 * Dispatches an AJAX request to the appropriate class method.
	 */
	public function dispatch(): void {
		// check_ajax_referer searches $_REQUEST, handling both POST calls and GET-based file downloads.
		if ( ! check_ajax_referer( 'attributehub_admin_nonce', 'attributehub_nonce', false ) ) {
			wp_send_json_error( array( 'message' => __( 'Security check failed.', 'attributehub-for-woocommerce' ) ), 403 );
			return;
		}

		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			wp_send_json_error( array( 'message' => __( 'Insufficient permissions.', 'attributehub-for-woocommerce' ) ), 403 );
			return;
		}

		// Use $_REQUEST so GET-based downloads (export_csv) work alongside POST calls.
		$method = sanitize_text_field( wp_unslash( $_REQUEST['attributehub_method'] ?? '' ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- nonce already verified above

		if ( empty( $method ) ) {
			wp_send_json_error( array( 'message' => __( 'No method specified.', 'attributehub-for-woocommerce' ) ) );
			return;
		}

		$allowed = $this->get_allowed_methods();

		if ( ! isset( $allowed[ $method ] ) ) {
			wp_send_json_error( array( 'message' => __( 'Method not allowed.', 'attributehub-for-woocommerce' ) ), 403 );
			return;
		}

		list( $full_class, $method_name ) = $allowed[ $method ];

		try {
			$instance = new $full_class();
			$result   = $instance->{$method_name}();

			// If the method returned a value, send it as success data.
			// Methods that stream output (e.g. export_csv) terminate before reaching here.
			if ( null !== $result ) {
				wp_send_json_success( $result );
			}
		} catch ( \Exception $e ) {
			wp_send_json_error( array( 'message' => $e->getMessage() ) );
		}
	}
}
