<?php
/**
 * Compact checkout login prompt, retaining WooCommerce's native login form.
 *
 * @package Foxfire_Child
 * @version 10.0.0
 */

defined( 'ABSPATH' ) || exit;

$registration_at_checkout   = WC_Checkout::instance()->is_registration_enabled();
$login_reminder_at_checkout = 'yes' === get_option( 'woocommerce_enable_checkout_login_reminder' );

if ( is_user_logged_in() ) {
	return;
}

if ( $login_reminder_at_checkout ) :
	?>
	<div class="ff-checkout-login">
		<span><?php esc_html_e( 'Returning customer?', 'foxfire-child' ); ?></span>
		<a href="#" class="showlogin"><?php esc_html_e( 'Log in', 'foxfire-child' ); ?></a>
	</div>
	<?php
endif;

if ( $registration_at_checkout || $login_reminder_at_checkout ) {
	// Show the form again after a login attempt so errors remain actionable.
	$show_form = isset( $_POST['login'] ); // phpcs:ignore WordPress.Security.NonceVerification.Missing

	woocommerce_login_form(
		array(
			'redirect' => wc_get_checkout_url(),
			'hidden'   => ! $show_form,
		)
	);
}
