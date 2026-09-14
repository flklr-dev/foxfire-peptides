<?php
/** Checkout safety during private review; native gateway administration is unchanged. */
defined( 'ABSPATH' ) || exit;

function foxfire_operations_checkout_is_review_mode(): bool {
	$host = (string) wp_parse_url( home_url( '/' ), PHP_URL_HOST );
	return 'staging' === wp_get_environment_type()
		|| 0 === stripos( $host, 'staging.' )
		|| 'yes' === get_option( 'foxfire_checkout_review_mode', 'no' );
}

function foxfire_operations_checkout_review_message(): string {
	return __( 'Payments are disabled during site review. No order will be placed.', 'foxfire-operations' );
}

function foxfire_operations_checkout_review_gateways( $gateways ): array {
	return foxfire_operations_checkout_is_review_mode() ? array() : $gateways;
}
add_filter( 'woocommerce_available_payment_gateways', 'foxfire_operations_checkout_review_gateways', 1000 );

/** Runs before customer/order creation, including carts that need no payment. */
function foxfire_operations_checkout_review_validate( $data, $errors ): void {
	if ( foxfire_operations_checkout_is_review_mode() ) {
		$errors->add( 'foxfire_checkout_review', foxfire_operations_checkout_review_message() );
	}
}
add_action( 'woocommerce_after_checkout_validation', 'foxfire_operations_checkout_review_validate', 1000, 2 );

/** Also prevent the alternative Store API checkout from creating review orders. */
function foxfire_operations_checkout_review_rest( $result, $server, $request ) {
	if ( foxfire_operations_checkout_is_review_mode() && preg_match( '#^/wc/store/v\d+/checkout(?:/|$)#', $request->get_route() ) ) {
		return new WP_Error( 'foxfire_checkout_review', foxfire_operations_checkout_review_message(), array( 'status' => 403 ) );
	}
	return $result;
}
add_filter( 'rest_pre_dispatch', 'foxfire_operations_checkout_review_rest', 1000, 3 );
