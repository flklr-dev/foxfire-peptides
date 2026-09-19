<?php
/** Require and record the checkout Terms and 21+ research-use acknowledgements. */
defined( 'ABSPATH' ) || exit;

/** Whether an acknowledgement was explicitly checked in the checkout request. */
function foxfire_operations_checkout_acknowledgement_checked( string $field ): bool {
	if ( ! isset( $_POST[ $field ] ) || ! is_scalar( $_POST[ $field ] ) ) {
		return false;
	}

	return '1' === sanitize_text_field( wp_unslash( (string) $_POST[ $field ] ) );
}

/**
 * Browser-required attributes are only a usability aid. Enforce both
 * acknowledgements on the server before WooCommerce creates a customer/order.
 */
function foxfire_operations_validate_legal_acknowledgement( array $data, WP_Error $errors ): void {
	if ( empty( $data['terms'] ) && ! $errors->get_error_message( 'terms' ) ) {
		$errors->add( 'terms', __( 'Please read and agree to the Terms & Conditions before placing your order.', 'foxfire-operations' ) );
	}

	if ( ! foxfire_operations_checkout_acknowledgement_checked( 'foxfire_age_research_acknowledgement' ) ) {
		$errors->add(
			'foxfire_age_research_acknowledgement',
			__( 'Please confirm that you are 21 years of age or older and acknowledge the laboratory research-use restriction before placing your order.', 'foxfire-operations' )
		);
	}
}
add_action( 'woocommerce_after_checkout_validation', 'foxfire_operations_validate_legal_acknowledgement', 999, 2 );

/** Record the accepted statement and version on the order for auditability. */
function foxfire_operations_record_checkout_acknowledgement( WC_Order $order, array $data ): void {
	unset( $data );

	if ( ! foxfire_operations_checkout_acknowledgement_checked( 'foxfire_age_research_acknowledgement' ) ) {
		return;
	}

	$order->update_meta_data( '_foxfire_age_research_acknowledgement', 'yes' );
	$order->update_meta_data( '_foxfire_age_research_acknowledgement_version', '2026-09-16-21-plus' );
	$order->update_meta_data( '_foxfire_age_research_acknowledged_gmt', gmdate( 'Y-m-d H:i:s' ) );
}
add_action( 'woocommerce_checkout_create_order', 'foxfire_operations_record_checkout_acknowledgement', 20, 2 );
