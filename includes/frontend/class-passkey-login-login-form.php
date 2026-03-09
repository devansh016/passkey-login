<?php
/**
 * Login form integration.
 *
 * @package passkey-login
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Passkey_Login_Login_Form {
	/**
	 * Setup hooks.
	 *
	 * @return void
	 */
	public function init(): void {
		add_action( 'login_form', array( $this, 'render_login_button' ) );
		add_action( 'login_enqueue_scripts', array( $this, 'enqueue_assets' ) );
	}

	/**
	 * Render passkey login button.
	 *
	 * @return void
	 */
	public function render_login_button(): void {
		if ( ! Passkey_Login_Settings::passkeys_enabled() || '1' !== (string) Passkey_Login_Settings::get( 'show_login_button' ) ) {
			return;
		}

		echo '<div class="passkey-login-login-wrap">';
		echo '<div class="passkey-login-divider" aria-hidden="true"><span>' . esc_html__( 'or', 'passkey-login' ) . '</span></div>';
		echo '<p class="passkey-login-button-wrap">';
		echo '<button type="button" id="passkey-login-login" class="button button-secondary button-large">';
		echo '<span class="passkey-login-label">' . esc_html__( 'Sign in with a passkey', 'passkey-login' ) . '</span>';
		echo '</button>';
		echo '</p>';
		echo '</div>';
		echo '<input type="hidden" name="passkey_login_passkey_assertion" id="passkey-login-assertion" value="" />';
		echo '<input type="hidden" name="passkey_login_passkey_nonce" value="' . esc_attr( wp_create_nonce( 'passkey_login_passkey_login' ) ) . '" />';
		echo '<p id="passkey-login-status" class="message" style="display:none;"></p>';
	}

	/**
	 * Enqueue login assets.
	 *
	 * @return void
	 */
	public function enqueue_assets(): void {
		if ( ! Passkey_Login_Settings::passkeys_enabled() || '1' !== (string) Passkey_Login_Settings::get( 'show_login_button' ) ) {
			return;
		}

		wp_enqueue_style(
			'passkey-login-login',
			PASSKEY_LOGIN_PLUGIN_URL . 'assets/src/css/login.css',
			array(),
			PASSKEY_LOGIN_VERSION
		);

		wp_enqueue_script(
			'passkey-login-authenticate',
			PASSKEY_LOGIN_PLUGIN_URL . 'assets/src/js/passkey-authenticate.js',
			array(),
			PASSKEY_LOGIN_VERSION,
			true
		);

		wp_localize_script(
			'passkey-login-authenticate',
			'passkeyLoginAuth',
			array(
				'restUrl' => esc_url_raw( rest_url( 'passkey-login/v1' ) ),
				'nonce'   => wp_create_nonce( 'wp_rest' ),
				'i18n'    => array(
					'notSupported' => __( 'Passkeys are not supported on this browser.', 'passkey-login' ),
					'failed'       => __( 'Passkey authentication failed.', 'passkey-login' ),
				),
			)
		);
	}
}
