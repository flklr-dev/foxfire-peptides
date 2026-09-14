<?php
/** Require the combined checkout acknowledgement even without the hidden field. */
defined( 'ABSPATH' ) || exit;
function foxfire_operations_validate_legal_acknowledgement( array $data, WP_Error $errors ): void {
	if ( empty( $data['terms'] ) && ! $errors->get_error_message( 'terms' ) ) {
		$errors->add( 'terms', __( 'Please confirm the age, Terms & Conditions, and research-use acknowledgement before placing your order.', 'foxfire-operations' ) );
	}
}
add_action( 'woocommerce_after_checkout_validation', 'foxfire_operations_validate_legal_acknowledgement', 999, 2 );
