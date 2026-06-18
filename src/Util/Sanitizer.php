<?php
/**
 * AttributeHub — Input Sanitizer
 *
 * @package AttributeHub\Free\Util
 */

namespace AttributeHub\Free\Util;

defined( 'ABSPATH' ) || exit;

/**
 * Sanitization helpers wrapping WordPress core functions.
 */
class Sanitizer {

	/**
	 * Sanitizes a master group label (text, allows spaces).
	 */
	public static function label( string $value ): string {
		return sanitize_text_field( $value );
	}

	/**
	 * Sanitizes a slug (URL-safe, lowercase).
	 */
	public static function slug( string $value ): string {
		return sanitize_title( $value );
	}

	/**
	 * Sanitizes a taxonomy slug (pa_color format).
	 */
	public static function taxonomy( string $value ): string {
		return sanitize_key( $value );
	}

	/**
	 * Sanitizes a raw attribute value snapshot.
	 */
	public static function raw_value( string $value ): string {
		return sanitize_text_field( $value );
	}

	/**
	 * Sanitizes an integer ID.
	 *
	 * @param mixed $value
	 */
	public static function id( $value ): int {
		return absint( $value );
	}

	/**
	 * Sanitizes an array of integer IDs.
	 *
	 * @param mixed $value
	 * @return int[]
	 */
	public static function id_array( $value ): array {
		if ( ! is_array( $value ) ) {
			return array();
		}
		return array_map( 'absint', $value );
	}

	/**
	 * Sanitizes a boolean value from various input forms.
	 *
	 * @param mixed $value
	 */
	public static function bool( $value ): bool {
		if ( is_bool( $value ) ) {
			return $value;
		}
		return in_array( $value, array( '1', 'true', 'yes', 1, true ), true );
	}

	/**
	 * Sanitizes a settings array, applying appropriate sanitization per key.
	 *
	 * @param array $input Raw input array.
	 * @param array $schema Schema defining key => type pairs.
	 * @return array Sanitized settings.
	 */
	public static function settings_array( array $input, array $schema ): array {
		$sanitized = array();

		foreach ( $schema as $key => $type ) {
			if ( ! isset( $input[ $key ] ) ) {
				continue;
			}

			switch ( $type ) {
				case 'bool':
					$sanitized[ $key ] = self::bool( $input[ $key ] );
					break;
				case 'int':
					$sanitized[ $key ] = absint( $input[ $key ] );
					break;
				case 'text':
					$sanitized[ $key ] = sanitize_text_field( $input[ $key ] );
					break;
				case 'email':
					$sanitized[ $key ] = sanitize_email( $input[ $key ] );
					break;
				case 'slug':
					$sanitized[ $key ] = self::slug( $input[ $key ] );
					break;
				default:
					$sanitized[ $key ] = sanitize_text_field( $input[ $key ] );
			}
		}

		return $sanitized;
	}

	/**
	 * Sanitizes an AJAX request's mapped_by value.
	 */
	public static function mapped_by( string $value ): string {
		$allowed = array( 'manual', 'auto', 'rule', 'csv' );
		return in_array( $value, $allowed, true ) ? $value : 'manual';
	}
}
