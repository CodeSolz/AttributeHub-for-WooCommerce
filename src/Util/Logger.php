<?php
/**
 * AttributeHub — Logger
 *
 * @package AttributeHub\Free\Util
 */

namespace AttributeHub\Free\Util;

defined( 'ABSPATH' ) || exit;

/**
 * Wraps WC_Logger for plugin-specific logging.
 * Logs appear in WooCommerce > Status > Logs with source 'attributehub'.
 */
class Logger {

	const SOURCE = 'attributehub';

	/** @var \WC_Logger|null */
	private static ?\WC_Logger $logger = null;

	/**
	 * Returns the WC_Logger instance.
	 */
	private static function get_logger(): \WC_Logger {
		if ( null === self::$logger ) {
			self::$logger = wc_get_logger();
		}
		return self::$logger;
	}

	/**
	 * Logs a debug-level message.
	 *
	 * @param string $message
	 * @param array  $context Additional context.
	 */
	public static function debug( string $message, array $context = array() ): void {
		if ( ! defined( 'WP_DEBUG' ) || ! WP_DEBUG ) {
			return;
		}
		self::get_logger()->debug( $message, self::build_context( $context ) );
	}

	/**
	 * Logs an info-level message.
	 */
	public static function info( string $message, array $context = array() ): void {
		self::get_logger()->info( $message, self::build_context( $context ) );
	}

	/**
	 * Logs a warning-level message.
	 */
	public static function warning( string $message, array $context = array() ): void {
		self::get_logger()->warning( $message, self::build_context( $context ) );
	}

	/**
	 * Logs an error-level message.
	 */
	public static function error( string $message, array $context = array() ): void {
		self::get_logger()->error( $message, self::build_context( $context ) );
	}

	/**
	 * Builds the WC logger context array.
	 */
	private static function build_context( array $context ): array {
		return array_merge( array( 'source' => self::SOURCE ), $context );
	}
}
