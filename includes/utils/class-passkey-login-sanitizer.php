<?php
/**
 * Sanitizer helpers.
 *
 * @package passkey-login
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Passkey_Login_Sanitizer {
	/**
	 * Sanitize text field.
	 *
	 * @param mixed $value Raw value.
	 * @return string
	 */
	public static function text( $value ): string {
		return sanitize_text_field( wp_unslash( (string) $value ) );
	}

	/**
	 * Sanitize key.
	 *
	 * @param mixed $value Raw key.
	 * @return string
	 */
	public static function key( $value ): string {
		return sanitize_key( wp_unslash( (string) $value ) );
	}

	/**
	 * Sanitize integer.
	 *
	 * @param mixed $value Raw number.
	 * @return int
	 */
	public static function absint( $value ): int {
		return absint( $value );
	}

	/**
	 * Sanitize boolean flag.
	 *
	 * @param mixed $value Raw bool.
	 * @return bool
	 */
	public static function bool( $value ): bool {
		return (bool) rest_sanitize_boolean( $value );
	}

	/**
	 * Decode JSON object safely.
	 *
	 * @param mixed $value Raw JSON.
	 * @return array<string,mixed>
	 */
	public static function json_object( $value ): array {
		if ( is_array( $value ) ) {
			return $value;
		}

		$decoded = json_decode( (string) $value, true );
		if ( ! is_array( $decoded ) ) {
			return array();
		}

		return $decoded;
	}
}
