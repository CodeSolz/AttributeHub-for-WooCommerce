<?php
/**
 * AttributeHub Free Plugin Bootstrap
 *
 * @package AttributeHub\Free
 */

namespace AttributeHub\Free;

defined( 'ABSPATH' ) || exit;

/**
 * Main plugin class. Singleton.
 */
final class Plugin {

	/** @var Plugin|null */
	private static ?Plugin $instance = null;

	/** @var array<string, object> Loaded hook class instances */
	private array $loaded_hooks = array();

	/**
	 * Returns the singleton instance.
	 */
	public static function instance(): Plugin {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Private constructor — use instance().
	 */
	private function __construct() {
		$this->load_hook_classes();
		$this->run_updater();

		/**
		 * Fires after the free plugin has fully booted.
		 * Pro plugin uses this to initialize and extend free functionality.
		 *
		 * @param Plugin $plugin The free plugin instance.
		 */
		do_action( 'attributehub_loaded', $this );
	}

	/**
	 * Auto-instantiate all classes in src/Hooks/ directory.
	 * Each class registers its own WordPress hooks in its constructor.
	 */
	private function load_hook_classes(): void {
		$hook_files = glob( ATTRIBUTEHUB_DIR . 'src/Hooks/*.php' );

		if ( empty( $hook_files ) ) {
			return;
		}

		foreach ( $hook_files as $file ) {
			$class_name = 'AttributeHub\\Free\\Hooks\\' . basename( $file, '.php' );

			if ( class_exists( $class_name ) && ! array_key_exists( $class_name, $this->loaded_hooks ) ) {
				$this->loaded_hooks[ $class_name ] = new $class_name();
			}
		}
	}

	/**
	 * Run DB schema updater on each load.
	 */
	private function run_updater(): void {
		Install\Updater::maybe_update();
	}

	// -------------------------------------------------------------------------
	// Subsystem accessors (lazy singletons)
	// -------------------------------------------------------------------------

	/**
	 * Returns the MappingEngine instance.
	 */
	public function mapping(): Mapping\MappingEngine {
		return Mapping\MappingEngine::instance();
	}

	/**
	 * Returns the AttributeScanner instance.
	 */
	public function scanner(): Scanner\AttributeScanner {
		return Scanner\AttributeScanner::instance();
	}

	/**
	 * Returns the LayeredNavIntegration instance.
	 */
	public function frontend(): Frontend\LayeredNavIntegration {
		return Frontend\LayeredNavIntegration::instance();
	}

	// -------------------------------------------------------------------------
	// State helpers
	// -------------------------------------------------------------------------

	/**
	 * Returns the plugin version.
	 */
	public function version(): string {
		return ATTRIBUTEHUB_VERSION;
	}

	/**
	 * Returns true if the Pro plugin is active.
	 */
	public function is_pro(): bool {
		return defined( 'ATTRIBUTEHUB_PRO_VERSION' );
	}

	/**
	 * Prevent cloning.
	 */
	private function __clone() {}

	/**
	 * Prevent unserialization.
	 */
	public function __wakeup() {
		throw new \Exception( 'Cannot unserialize singleton.' );
	}
}
