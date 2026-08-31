<?php
/**
 * Checkout customizations & test gateway configuration — Chunk 1K.
 *
 * @package Foxfire_Child
 */

defined( 'ABSPATH' ) || exit;

/**
 * Clean up Storefront layout on checkout page.
 */
function foxfire_checkout_cleanup_layout(): void {
	if ( is_checkout() ) {
		remove_action( 'storefront_before_content', 'woocommerce_breadcrumb', 10 );
		remove_action( 'woocommerce_before_main_content', 'woocommerce_breadcrumb', 20 );
		remove_action( 'storefront_page', 'storefront_page_header', 10 );
	}
}
add_action( 'wp', 'foxfire_checkout_cleanup_layout', 10 );

/**
 * Ensure test payment gateways are enabled for local development.
 */
function foxfire_setup_test_payment_gateways(): void {
	// Enable BACS (Direct Bank Transfer / Wire) if not explicitly set
	$bacs_settings = get_option( 'woocommerce_bacs_settings' );
	if ( empty( $bacs_settings ) || ! is_array( $bacs_settings ) || ( isset( $bacs_settings['enabled'] ) && 'yes' !== $bacs_settings['enabled'] ) ) {
		update_option(
			'woocommerce_bacs_settings',
			array(
				'enabled'      => 'yes',
				'title'        => __( 'Direct Bank Transfer / Wire', 'foxfire-child' ),
				'description'  => __( 'Make your payment directly into our bank account. Please use your Order ID as the payment reference.', 'foxfire-child' ),
				'instructions' => __( 'Thank you for your analytical research order. Wire transfer instructions will be sent with your confirmation email.', 'foxfire-child' ),
			)
		);
	}

	// Enable COD (Cash / Offline Test Checkout)
	$cod_settings = get_option( 'woocommerce_cod_settings' );
	if ( empty( $cod_settings ) || ! is_array( $cod_settings ) || ( isset( $cod_settings['enabled'] ) && 'yes' !== $cod_settings['enabled'] ) ) {
		update_option(
			'woocommerce_cod_settings',
			array(
				'enabled'      => 'yes',
				'title'        => __( 'Test Order (No Payment Required)', 'foxfire-child' ),
				'description'  => __( 'Simulate an analytical order placement without entering payment credentials.', 'foxfire-child' ),
				'instructions' => __( 'Your test analytical research order has been recorded successfully.', 'foxfire-child' ),
			)
		);
	}
}
add_action( 'init', 'foxfire_setup_test_payment_gateways', 5 );

/**
 * Enqueue checkout scripts and styles.
 */
function foxfire_checkout_enqueue_scripts(): void {
	if ( is_checkout() ) {
		$version = defined( 'FOXFIRE_CHILD_VERSION' ) ? FOXFIRE_CHILD_VERSION : '1.2.0';
		$js_path = FOXFIRE_CHILD_DIR . '/assets/js/checkout.js';

		wp_enqueue_script(
			'foxfire-checkout-js',
			FOXFIRE_CHILD_URI . '/assets/js/checkout.js',
			array( 'jquery', 'wc-checkout' ),
			file_exists( $js_path ) ? (string) filemtime( $js_path ) : $version,
			true
		);
	}
}
add_action( 'wp_enqueue_scripts', 'foxfire_checkout_enqueue_scripts', 30 );

/**
 * Customize WooCommerce checkout form fields for a modern DTC structure.
 *
 * @param array<string, array<string, mixed>> $fields Default checkout fields.
 * @return array<string, array<string, mixed>>
 */
function foxfire_customize_checkout_fields( array $fields ): array {
	// Billing / Contact Fields
	if ( isset( $fields['billing']['billing_first_name'] ) ) {
		$fields['billing']['billing_first_name']['priority']    = 10;
		$fields['billing']['billing_first_name']['placeholder'] = __( 'First name', 'foxfire-child' );
		$fields['billing']['billing_first_name']['class']       = array( 'form-row-first', 'ff-field-wrap' );
	}

	if ( isset( $fields['billing']['billing_last_name'] ) ) {
		$fields['billing']['billing_last_name']['priority']    = 20;
		$fields['billing']['billing_last_name']['placeholder'] = __( 'Last name', 'foxfire-child' );
		$fields['billing']['billing_last_name']['class']       = array( 'form-row-last', 'ff-field-wrap' );
	}

	if ( isset( $fields['billing']['billing_email'] ) ) {
		$fields['billing']['billing_email']['priority']    = 1;
		$fields['billing']['billing_email']['placeholder'] = __( 'your.email@example.com', 'foxfire-child' );
		$fields['billing']['billing_email']['class']       = array( 'form-row-wide', 'ff-field-wrap' );
	}

	if ( isset( $fields['billing']['billing_phone'] ) ) {
		$fields['billing']['billing_phone']['priority']    = 30;
		$fields['billing']['billing_phone']['placeholder'] = __( 'Phone number (for delivery updates)', 'foxfire-child' );
		$fields['billing']['billing_phone']['class']       = array( 'form-row-wide', 'ff-field-wrap' );
	}

	if ( isset( $fields['billing']['billing_address_1'] ) ) {
		$fields['billing']['billing_address_1']['placeholder'] = __( 'Street address', 'foxfire-child' );
		$fields['billing']['billing_address_1']['class']       = array( 'form-row-wide', 'ff-field-wrap' );
	}

	if ( isset( $fields['billing']['billing_address_2'] ) ) {
		$fields['billing']['billing_address_2']['placeholder'] = __( 'Apartment, suite, unit, building (optional)', 'foxfire-child' );
		$fields['billing']['billing_address_2']['class']       = array( 'form-row-wide', 'ff-field-wrap' );
	}

	if ( isset( $fields['billing']['billing_city'] ) ) {
		$fields['billing']['billing_city']['placeholder'] = __( 'City', 'foxfire-child' );
		$fields['billing']['billing_city']['class']       = array( 'form-row-first', 'ff-field-wrap' );
	}

	if ( isset( $fields['billing']['billing_state'] ) ) {
		$fields['billing']['billing_state']['placeholder'] = __( 'State / Province', 'foxfire-child' );
		$fields['billing']['billing_state']['class']       = array( 'form-row-last', 'ff-field-wrap' );
	}

	if ( isset( $fields['billing']['billing_postcode'] ) ) {
		$fields['billing']['billing_postcode']['placeholder'] = __( 'ZIP / Postal code', 'foxfire-child' );
		$fields['billing']['billing_postcode']['class']       = array( 'form-row-wide', 'ff-field-wrap' );
	}

	// Order Notes
	if ( isset( $fields['order']['order_comments'] ) ) {
		$fields['order']['order_comments']['placeholder'] = __( 'Special notes about your research analytical order or delivery preferences (optional)...', 'foxfire-child' );
		$fields['order']['order_comments']['class']       = array( 'form-row-wide', 'ff-field-wrap' );
	}

	return $fields;
}
add_filter( 'woocommerce_checkout_fields', 'foxfire_customize_checkout_fields', 20 );
