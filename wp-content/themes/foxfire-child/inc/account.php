<?php
/**
 * Customer Account & My Account Portal integration — Chunk 1L.
 *
 * @package Foxfire_Child
 */

defined( 'ABSPATH' ) || exit;

/**
 * Auto-enable registration on My Account page.
 */
add_filter( 'pre_option_woocommerce_enable_myaccount_registration', function() {
	return 'yes';
} );

/**
 * Enable automatic username generation from customer email for seamless DTC registration.
 */
add_filter( 'pre_option_woocommerce_registration_generate_username', function() {
	return 'yes';
} );

/**
 * Allow customer to set password immediately upon registration (instant account access).
 */
add_filter( 'pre_option_woocommerce_registration_generate_password', function() {
	return 'no';
} );

/**
 * Enqueue My Account scripts and styles.
 */
function foxfire_account_enqueue_scripts(): void {
	if ( is_account_page() ) {
		$script_path = FOXFIRE_CHILD_DIR . '/assets/js/account.js';
		$script_uri  = FOXFIRE_CHILD_URI . '/assets/js/account.js';

		wp_enqueue_script(
			'foxfire-account',
			$script_uri,
			array( 'jquery' ),
			file_exists( $script_path ) ? (string) filemtime( $script_path ) : FOXFIRE_CHILD_VERSION,
			true
		);
	}
}
add_action( 'wp_enqueue_scripts', 'foxfire_account_enqueue_scripts', 20 );

/**
 * Remove default boilerplate privacy policy text (replaced by standard DTC terms checkbox).
 */
function foxfire_account_cleanup_storefront(): void {
	remove_action( 'woocommerce_register_form', 'wc_registration_privacy_policy_text', 20 );
}
add_action( 'init', 'foxfire_account_cleanup_storefront' );

/**
 * Clean up Storefront layout on account page.
 */
function foxfire_account_cleanup_layout(): void {
	if ( is_account_page() ) {
		remove_action( 'storefront_before_content', 'woocommerce_breadcrumb', 10 );
		remove_action( 'woocommerce_before_main_content', 'woocommerce_breadcrumb', 20 );
		remove_action( 'storefront_page', 'storefront_page_header', 10 );
	}
}
add_action( 'wp', 'foxfire_account_cleanup_layout', 10 );

/**
 * Helper: Get user initials for avatar.
 *
 * @param int $user_id User ID.
 * @return string Initials (e.g. "KD" or "F").
 */
function foxfire_get_user_initials( int $user_id ): string {
	$user = get_userdata( $user_id );
	if ( ! $user ) {
		return 'F';
	}

	$first = ! empty( $user->first_name ) ? mb_substr( $user->first_name, 0, 1 ) : '';
	$last  = ! empty( $user->last_name ) ? mb_substr( $user->last_name, 0, 1 ) : '';

	if ( $first && $last ) {
		return strtoupper( $first . $last );
	}

	if ( ! empty( $user->display_name ) ) {
		return strtoupper( mb_substr( $user->display_name, 0, 1 ) );
	}

	return strtoupper( mb_substr( $user->user_login, 0, 1 ) );
}

/**
 * Helper: Get customer's latest order.
 *
 * @param int $user_id User ID.
 * @return WC_Order|null Latest order or null.
 */
function foxfire_get_customer_latest_order( int $user_id ): ?WC_Order {
	$orders = wc_get_orders(
		array(
			'customer_id' => $user_id,
			'limit'       => 1,
			'orderby'     => 'date',
			'order'       => 'DESC',
		)
	);

	return ! empty( $orders ) ? $orders[0] : null;
}
