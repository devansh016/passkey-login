<?php
/**
 * WordPress authentication hook integration.
 *
 * @package passkey-login
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Passkey_Login_Authenticator {
	/**
	 * Init hooks.
	 *
	 * @return void
	 */
	public function init(): void {
		add_filter( 'authenticate', array( $this, 'authenticate' ), 30, 3 );
	}

	/**
	 * Authenticate using passkey assertion when present.
	 *
	 * @param WP_User|WP_Error|null $user Existing auth result.
	 * @param string                $username Username.
	 * @param string                $password Password.
	 * @return WP_User|WP_Error|null
	 */
	public function authenticate( $user, string $username, string $password ) {
		if ( ! Passkey_Login_Settings::passkeys_enabled() ) {
			return $user;
		}

		if ( $user instanceof WP_User ) {
			return $user;
		}

		if ( empty( $_POST['passkey_login_passkey_assertion'] ) ) {
			return $user;
		}

		if ( ! isset( $_POST['passkey_login_passkey_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['passkey_login_passkey_nonce'] ) ), 'passkey_login_passkey_login' ) ) {
			return new WP_Error( 'passkey_login_invalid_nonce', __( 'Security check failed.', 'passkey-login' ) );
		}

		// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- JSON payload is validated and normalized by Passkey_Login_Sanitizer::json_object().
		$assertion_raw = wp_unslash( $_POST['passkey_login_passkey_assertion'] );
		if ( ! is_string( $assertion_raw ) ) {
			return new WP_Error( 'passkey_login_invalid_payload', __( 'Invalid passkey payload.', 'passkey-login' ) );
		}
		$assertion = Passkey_Login_Sanitizer::json_object( $assertion_raw );
		$webauthn  = new Passkey_Login_WebAuthn();
		$result    = $webauthn->complete_authentication( $assertion );

		if ( is_wp_error( $result ) ) {
			// User explicitly attempted passkey auth; surface the passkey error.
			return $result;
		}

		$auth_user = get_user_by( 'id', (int) $result['user_id'] );
		if ( ! $auth_user instanceof WP_User ) {
			return new WP_Error( 'passkey_login_user_not_found', __( 'Could not load authenticated user.', 'passkey-login' ) );
		}

		return $auth_user;
	}
}
