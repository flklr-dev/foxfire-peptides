<?php
/** Apply approved local email settings without configuring external mail or payments. */
defined( 'ABSPATH' ) || exit;
if ( 'local' !== wp_get_environment_type() ) {
	WP_CLI::error( 'Local account review settings only.' );
}
update_option( 'woocommerce_email_from_address', 'info@foxfirepeptides.com' );
update_option( 'woocommerce_email_from_name', 'Foxfire Peptides' );
foreach ( array( 'new_order', 'cancelled_order', 'failed_order' ) as $email ) {
	$key = 'woocommerce_' . $email . '_settings';
	$settings = get_option( $key, array() );
	if ( is_array( $settings ) && isset( $settings['recipient'] ) ) {
		$settings['recipient'] = str_replace( 'support@foxfirepeptides.com', 'info@foxfirepeptides.com', $settings['recipient'] );
		update_option( $key, $settings );
	}
}
WP_CLI::success( 'Local customer email branding updated; Mailpit and staging delivery guards unchanged.' );
