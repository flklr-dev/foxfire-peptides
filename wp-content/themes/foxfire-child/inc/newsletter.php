<?php
/**
 * Newsletter preview. Enrollment stays unavailable until an approved provider
 * is connected; never imply that a submitted address was subscribed.
 *
 * @package Foxfire_Child
 */

defined( 'ABSPATH' ) || exit;

function foxfire_newsletter_preview_submit(): void {
	if ( ! isset( $_POST['foxfire_newsletter_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['foxfire_newsletter_nonce'] ) ), 'foxfire_newsletter_preview' ) ) {
		wp_die( esc_html__( 'The signup form expired. Please refresh the page and try again.', 'foxfire-child' ), '', array( 'response' => 403 ) );
	}

	$source = isset( $_POST['foxfire_newsletter_source'] ) && 'home' === sanitize_key( wp_unslash( $_POST['foxfire_newsletter_source'] ) ) ? 'home' : 'footer';
	$email  = isset( $_POST['foxfire_newsletter_email'] ) ? sanitize_email( wp_unslash( $_POST['foxfire_newsletter_email'] ) ) : '';
	$status = is_email( $email ) ? 'not-connected' : 'invalid';
	$referer = wp_get_referer() ?: home_url( '/' );
	$target  = add_query_arg( array( 'ff_newsletter' => $status, 'ff_newsletter_source' => $source ), remove_query_arg( array( 'ff_newsletter', 'ff_newsletter_source' ), $referer ) );
	wp_safe_redirect( $target . ( 'home' === $source ? '#newsletter-home' : '#newsletter-footer' ) );
	exit;
}
add_action( 'admin_post_foxfire_newsletter_preview', 'foxfire_newsletter_preview_submit' );
add_action( 'admin_post_nopriv_foxfire_newsletter_preview', 'foxfire_newsletter_preview_submit' );

/** Keep the account and checkout opt-in statement identical. */
function foxfire_marketing_consent_text(): string {
	return __( 'Email me Foxfire updates, testing news, and research compound announcements (optional).', 'foxfire-child' );
}

/** Only an explicit checked value counts as marketing consent. */
function foxfire_marketing_optin_checked(): bool {
	return isset( $_POST['foxfire_marketing_optin'] )
		&& is_string( $_POST['foxfire_marketing_optin'] )
		&& '1' === sanitize_text_field( wp_unslash( $_POST['foxfire_marketing_optin'] ) );
}

/** Common audit fields for future platform syncing; no external enrollment yet. */
function foxfire_marketing_consent_record( string $source ): array {
	return array(
		'_foxfire_marketing_optin'           => 'yes',
		'_foxfire_marketing_optin_at'        => current_time( 'mysql', true ),
		'_foxfire_marketing_optin_source'    => $source,
		'_foxfire_marketing_optin_statement' => foxfire_marketing_consent_text(),
		'_foxfire_marketing_optin_version'   => '2026-09-20-v1',
	);
}

/**
 * Preserve explicit checkout consent on the order for a future approved email
 * platform integration. An unchecked box never creates a subscription record.
 */
function foxfire_record_checkout_marketing_consent( WC_Order $order ): void {
	if ( ! foxfire_marketing_optin_checked() ) {
		return;
	}

	foreach ( foxfire_marketing_consent_record( 'checkout' ) as $key => $value ) {
		$order->update_meta_data( $key, $value );
	}
}
add_action( 'woocommerce_checkout_create_order', 'foxfire_record_checkout_marketing_consent' );

/** Record account opt-in only after WooCommerce successfully creates a user. */
function foxfire_record_registration_marketing_consent( int $customer_id ): void {
	if ( ! isset( $_POST['register'], $_POST['woocommerce-register-nonce'] )
		|| ! is_string( $_POST['woocommerce-register-nonce'] )
		|| ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['woocommerce-register-nonce'] ) ), 'woocommerce-register' )
		|| ! foxfire_marketing_optin_checked() ) {
		return;
	}

	foreach ( foxfire_marketing_consent_record( 'account_registration' ) as $key => $value ) {
		update_user_meta( $customer_id, $key, $value );
	}
}
add_action( 'woocommerce_created_customer', 'foxfire_record_registration_marketing_consent', 30 );
