<?php
/** Disable old local test methods and explicitly enable private checkout review. */
defined( 'ABSPATH' ) || exit;
if ( 'local' !== wp_get_environment_type() ) {
	WP_CLI::error( 'This settings script runs only on localhost.' );
}
$keys = array( 'woocommerce_bacs_settings', 'woocommerce_cod_settings', 'foxfire_checkout_review_mode' );
if ( false === get_option( 'foxfire_checkout_review_backup_v1', false ) ) {
	$backup = array();
	foreach ( $keys as $key ) {
		$backup[ $key ] = get_option( $key, null );
	}
	add_option( 'foxfire_checkout_review_backup_v1', $backup, '', false );
}
foreach ( array( 'bacs', 'cod' ) as $gateway ) {
	$key = 'woocommerce_' . $gateway . '_settings';
	$settings = get_option( $key, array() );
	$settings['enabled'] = 'no';
	update_option( $key, $settings );
}
update_option( 'foxfire_checkout_review_mode', 'yes' );
WP_CLI::success( 'Local review mode enabled; old local BACS/COD test methods disabled. No live gateway or external email configured.' );
