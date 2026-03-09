<?php
/**
 * Uninstall cleanup for Passkey Login.
 *
 * @package passkey-login
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

global $wpdb;

/**
 * Drop plugin tables for the current blog.
 *
 * @return void
 */
function passkey_login_drop_site_tables(): void {
	global $wpdb;

	$tables = array(
		$wpdb->prefix . 'passkey_login_credentials',
		$wpdb->prefix . 'passkey_login_challenges',
	);

	foreach ( $tables as $table ) {
		// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- table names are generated from trusted WordPress prefixes.
		$wpdb->query( "DROP TABLE IF EXISTS {$table}" );
	}
}

if ( is_multisite() ) {
	$site_ids = get_sites(
		array(
			'fields' => 'ids',
		)
	);

	foreach ( $site_ids as $site_id ) {
		switch_to_blog( (int) $site_id );
		delete_option( 'passkey_login_settings' );
		passkey_login_drop_site_tables();
		restore_current_blog();
	}

	delete_site_option( 'passkey_login_network_settings' );

	$main_prefix = $wpdb->get_blog_prefix( get_main_site_id() );
	$audit_tables = array(
		$main_prefix . 'passkey_login_network_audit_log',
	);

	foreach ( $audit_tables as $audit_table ) {
		// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- table names are generated from trusted WordPress prefixes.
		$wpdb->query( "DROP TABLE IF EXISTS {$audit_table}" );
	}
} else {
	delete_option( 'passkey_login_settings' );
	passkey_login_drop_site_tables();
}
