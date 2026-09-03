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

		// Remove the blue "Have a coupon? Click here to enter your code" banner
		remove_action( 'woocommerce_before_checkout_form', 'woocommerce_checkout_coupon_form', 10 );
	}
}
add_action( 'wp', 'foxfire_checkout_cleanup_layout', 10 );

/**
 * Ensure test payment gateways are enabled for local development:
 * 1. Credit / Debit Card (Test)
 * 2. Cash on Delivery (Test)
 */
function foxfire_setup_test_payment_gateways(): void {
	// Enable Card Test Gateway (via BACS settings)
	update_option(
		'woocommerce_bacs_settings',
		array(
			'enabled'      => 'yes',
			'title'        => __( 'Credit / Debit Card (Test)', 'foxfire-child' ),
			'description'  => __( 'Simulate credit or debit card checkout for testing. No live payment will be processed.', 'foxfire-child' ),
			'instructions' => __( 'Your test analytical research order has been placed successfully.', 'foxfire-child' ),
		)
	);

	// Enable COD (Cash on Delivery Test)
	update_option(
		'woocommerce_cod_settings',
		array(
			'enabled'      => 'yes',
			'title'        => __( 'Cash on Delivery (Test)', 'foxfire-child' ),
			'description'  => __( 'Pay with cash upon delivery test order. Exact change is appreciated.', 'foxfire-child' ),
			'instructions' => __( 'Payment will be collected in cash upon delivery.', 'foxfire-child' ),
		)
	);

	// Disable Cheque gateway if active
	$cheque_settings = get_option( 'woocommerce_cheque_settings', array() );
	if ( is_array( $cheque_settings ) && isset( $cheque_settings['enabled'] ) && 'yes' === $cheque_settings['enabled'] ) {
		$cheque_settings['enabled'] = 'no';
		update_option( 'woocommerce_cheque_settings', $cheque_settings );
	}
}
add_action( 'init', 'foxfire_setup_test_payment_gateways', 5 );

/**
 * Filter available payment gateways on checkout to ensure only the requested test gateways appear in order.
 */
function foxfire_filter_available_payment_gateways( array $gateways ): array {
	$result = array();

	// 1. Credit / Debit Card (Test)
	if ( isset( $gateways['bacs'] ) ) {
		$gateways['bacs']->title       = __( 'Credit / Debit Card (Test)', 'foxfire-child' );
		$gateways['bacs']->description = __( 'Simulate credit or debit card checkout for testing. No live payment will be processed.', 'foxfire-child' );
		$result['bacs']                = $gateways['bacs'];
	}

	// 2. Cash on Delivery (Test)
	if ( isset( $gateways['cod'] ) ) {
		$gateways['cod']->title       = __( 'Cash on Delivery (Test)', 'foxfire-child' );
		$gateways['cod']->description = __( 'Pay with cash upon delivery test order. Exact change is appreciated.', 'foxfire-child' );
		$result['cod']                = $gateways['cod'];
	}

	// Select first method by default if none chosen
	if ( ! empty( $result ) && WC()->session ) {
		$chosen = WC()->session->get( 'chosen_payment_method' );
		if ( empty( $chosen ) || ! isset( $result[ $chosen ] ) ) {
			$keys = array_keys( $result );
			WC()->session->set( 'chosen_payment_method', $keys[0] );
			$result[ $keys[0] ]->chosen = true;
		}
	}

	return ! empty( $result ) ? $result : $gateways;
}
add_filter( 'woocommerce_available_payment_gateways', 'foxfire_filter_available_payment_gateways', 999 );

/**
 * Automatically configure Foxfire United States shipping zone and methods if not present:
 * - Standard Shipping ($9.95)
 * - Free Standard Shipping on orders $150+
 */
function foxfire_setup_shipping_methods(): void {
	if ( ! class_exists( 'WC_Shipping_Zones' ) ) {
		return;
	}

	$zones = WC_Shipping_Zones::get_zones();
	if ( empty( $zones ) ) {
		$zone = new WC_Shipping_Zone();
		$zone->set_zone_name( 'United States' );
		$zone->set_zone_order( 1 );
		$zone->add_location( 'US', 'country' );
		$zone->save();

		// Add Flat Rate ($9.95)
		$flat_id = $zone->add_shipping_method( 'flat_rate' );
		if ( $flat_id ) {
			$flat = WC_Shipping_Zones::get_shipping_method( $flat_id );
			if ( $flat ) {
				$flat->update_option( 'title', __( 'Standard Shipping', 'foxfire-child' ) );
				$flat->update_option( 'cost', '9.95' );
			}
		}

		// Add Free Shipping ($150+)
		$free_id = $zone->add_shipping_method( 'free_shipping' );
		if ( $free_id ) {
			$free = WC_Shipping_Zones::get_shipping_method( $free_id );
			if ( $free ) {
				$free->update_option( 'title', __( 'Free Standard Shipping on orders $150+', 'foxfire-child' ) );
				$free->update_option( 'requires', 'min_amount' );
				$free->update_option( 'min_amount', '150' );
			}
		}
	}
}
add_action( 'init', 'foxfire_setup_shipping_methods', 6 );

/**
 * Filter shipping rates on checkout:
 * - When subtotal >= $150: Free Standard Shipping on orders $150+ ($0.00)
 * - When subtotal < $150: Standard Shipping ($9.95)
 */
function foxfire_filter_checkout_shipping_rates( array $rates, array $package ): array {
	$subtotal = isset( $package['contents_cost'] ) ? (float) $package['contents_cost'] : 0.0;
	if ( 0.0 === $subtotal && function_exists( 'WC' ) && is_object( WC()->cart ) ) {
		$subtotal = (float) WC()->cart->get_displayed_subtotal();
	}

	$free_threshold = 150.00;

	if ( $subtotal >= $free_threshold ) {
		$free_rates = array();
		foreach ( $rates as $rate_id => $rate ) {
			if ( 'free_shipping' === $rate->method_id || false !== stripos( $rate->label, 'free' ) ) {
				$rate->set_label( __( 'Free Standard Shipping on orders $150+', 'foxfire-child' ) );
				$rate->set_cost( 0.00 );
				$free_rates[ $rate_id ] = $rate;
			}
		}

		if ( ! empty( $free_rates ) ) {
			return $free_rates;
		}

		// Fallback rate if none was found in zone
		$rate_id = 'foxfire_free_shipping';
		$rate    = new WC_Shipping_Rate(
			$rate_id,
			__( 'Free Standard Shipping on orders $150+', 'foxfire-child' ),
			0.00,
			array(),
			'free_shipping'
		);
		return array( $rate_id => $rate );
	}

	// Under $150: return only Standard Shipping ($9.95)
	$standard_rates = array();
	foreach ( $rates as $rate_id => $rate ) {
		if ( 'free_shipping' !== $rate->method_id && false === stripos( $rate->label, 'free' ) ) {
			$rate->set_label( __( 'Standard Shipping', 'foxfire-child' ) );
			$rate->set_cost( 9.95 );
			$standard_rates[ $rate_id ] = $rate;
		}
	}

	if ( ! empty( $standard_rates ) ) {
		return $standard_rates;
	}

	// Fallback rate
	$rate_id = 'foxfire_standard_shipping';
	$rate    = new WC_Shipping_Rate(
		$rate_id,
		__( 'Standard Shipping', 'foxfire-child' ),
		9.95,
		array(),
		'flat_rate'
	);
	return array( $rate_id => $rate );
}
add_filter( 'woocommerce_package_rates', 'foxfire_filter_checkout_shipping_rates', 100, 2 );

/**
 * Ensure shipping calculation is always ready on checkout,
 * and pre-populate US as default country if customer hasn't set one yet.
 */
add_filter( 'woocommerce_shipping_cost_requires_address', '__return_false', 99 );

function foxfire_prepare_checkout_shipping(): void {
	if ( function_exists( 'WC' ) && is_object( WC()->customer ) ) {
		if ( empty( WC()->customer->get_shipping_country() ) ) {
			WC()->customer->set_shipping_country( 'US' );
			WC()->customer->set_billing_country( 'US' );
		}
	}
}
add_action( 'template_redirect', 'foxfire_prepare_checkout_shipping', 5 );
add_action( 'woocommerce_before_checkout_form', 'foxfire_prepare_checkout_shipping', 5 );

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

		wp_localize_script(
			'foxfire-checkout-js',
			'foxfire_checkout_params',
			array(
				'ajax_url' => admin_url( 'admin-ajax.php' ),
				'nonce'    => wp_create_nonce( 'foxfire_checkout_nonce' ),
			)
		);
	}
}
add_action( 'wp_enqueue_scripts', 'foxfire_checkout_enqueue_scripts', 30 );

/**
 * AJAX handler to save address directly to user's WordPress account meta and WC customer session.
 */
function foxfire_ajax_save_checkout_address(): void {
	check_ajax_referer( 'foxfire_checkout_nonce', 'nonce' );

	$user_id = get_current_user_id();

	$fields = array(
		'billing_first_name' => sanitize_text_field( $_POST['billing_first_name'] ?? '' ),
		'billing_last_name'  => sanitize_text_field( $_POST['billing_last_name'] ?? '' ),
		'billing_email'      => sanitize_email( $_POST['billing_email'] ?? '' ),
		'billing_phone'      => sanitize_text_field( $_POST['billing_phone'] ?? '' ),
		'billing_country'    => sanitize_text_field( $_POST['billing_country'] ?? '' ),
		'billing_state'      => sanitize_text_field( $_POST['billing_state'] ?? '' ),
		'billing_city'       => sanitize_text_field( $_POST['billing_city'] ?? '' ),
		'billing_postcode'   => sanitize_text_field( $_POST['billing_postcode'] ?? '' ),
		'billing_address_1'  => sanitize_text_field( $_POST['billing_address_1'] ?? '' ),
		'billing_address_2'  => sanitize_text_field( $_POST['billing_address_2'] ?? '' ),
	);

	$set_default = ! empty( $_POST['set_default'] );

	// Update WooCommerce customer session object
	if ( function_exists( 'WC' ) && is_object( WC()->customer ) ) {
		foreach ( $fields as $key => $val ) {
			$setter = 'set_' . $key;
			if ( method_exists( WC()->customer, $setter ) ) {
				WC()->customer->$setter( $val );
			}
		}
		if ( $set_default ) {
			WC()->customer->set_shipping_first_name( $fields['billing_first_name'] );
			WC()->customer->set_shipping_last_name( $fields['billing_last_name'] );
			WC()->customer->set_shipping_country( $fields['billing_country'] );
			WC()->customer->set_shipping_state( $fields['billing_state'] );
			WC()->customer->set_shipping_city( $fields['billing_city'] );
			WC()->customer->set_shipping_postcode( $fields['billing_postcode'] );
			WC()->customer->set_shipping_address_1( $fields['billing_address_1'] );
			WC()->customer->set_shipping_address_2( $fields['billing_address_2'] );
		}
		WC()->customer->save();
	}

	// If user is logged in, PERMANENTLY save to account database (wp_usermeta)
	if ( $user_id > 0 ) {
		foreach ( $fields as $key => $val ) {
			update_user_meta( $user_id, $key, $val );
		}
		if ( $set_default ) {
			update_user_meta( $user_id, 'shipping_first_name', $fields['billing_first_name'] );
			update_user_meta( $user_id, 'shipping_last_name', $fields['billing_last_name'] );
			update_user_meta( $user_id, 'shipping_country', $fields['billing_country'] );
			update_user_meta( $user_id, 'shipping_state', $fields['billing_state'] );
			update_user_meta( $user_id, 'shipping_city', $fields['billing_city'] );
			update_user_meta( $user_id, 'shipping_postcode', $fields['billing_postcode'] );
			update_user_meta( $user_id, 'shipping_address_1', $fields['billing_address_1'] );
			update_user_meta( $user_id, 'shipping_address_2', $fields['billing_address_2'] );
		}
	}

	wp_send_json_success( array( 'message' => __( 'Address saved to account successfully.', 'foxfire-child' ) ) );
}
add_action( 'wp_ajax_foxfire_save_checkout_address', 'foxfire_ajax_save_checkout_address' );
add_action( 'wp_ajax_nopriv_foxfire_save_checkout_address', 'foxfire_ajax_save_checkout_address' );

/**
 * Customize WooCommerce checkout form fields for a modern DTC structure.
 *
 * @param array<string, array<string, mixed>> $fields Default checkout fields.
 * @return array<string, array<string, mixed>>
 */
function foxfire_customize_checkout_fields( array $fields ): array {
	// 1. First Name (Row 1 - Left)
	if ( isset( $fields['billing']['billing_first_name'] ) ) {
		$fields['billing']['billing_first_name']['priority']    = 10;
		$fields['billing']['billing_first_name']['label']       = __( 'First name', 'foxfire-child' );
		$fields['billing']['billing_first_name']['placeholder'] = __( 'First name', 'foxfire-child' );
		$fields['billing']['billing_first_name']['class']       = array( 'form-row-first', 'ff-field-wrap' );
		$fields['billing']['billing_first_name']['required']    = true;
	}

	// 2. Last Name (Row 1 - Right)
	if ( isset( $fields['billing']['billing_last_name'] ) ) {
		$fields['billing']['billing_last_name']['priority']    = 20;
		$fields['billing']['billing_last_name']['label']       = __( 'Last name', 'foxfire-child' );
		$fields['billing']['billing_last_name']['placeholder'] = __( 'Last name', 'foxfire-child' );
		$fields['billing']['billing_last_name']['class']       = array( 'form-row-last', 'ff-field-wrap' );
		$fields['billing']['billing_last_name']['required']    = true;
	}

	// 3. Email (Row 2 - Left)
	if ( isset( $fields['billing']['billing_email'] ) ) {
		$fields['billing']['billing_email']['priority']    = 30;
		$fields['billing']['billing_email']['label']       = __( 'Email address', 'foxfire-child' );
		$fields['billing']['billing_email']['placeholder'] = __( 'your.email@example.com', 'foxfire-child' );
		$fields['billing']['billing_email']['class']       = array( 'form-row-first', 'ff-field-wrap' );
		$fields['billing']['billing_email']['required']    = true;
	}

	// 4. Phone (Row 2 - Right) — REQUIRED
	if ( isset( $fields['billing']['billing_phone'] ) ) {
		$fields['billing']['billing_phone']['priority']    = 40;
		$fields['billing']['billing_phone']['label']       = __( 'Phone number', 'foxfire-child' );
		$fields['billing']['billing_phone']['placeholder'] = __( 'Phone number (for delivery updates)', 'foxfire-child' );
		$fields['billing']['billing_phone']['class']       = array( 'form-row-last', 'ff-field-wrap' );
		$fields['billing']['billing_phone']['required']    = true;
	}

	// 5. Country (Row 3 - Full Width)
	if ( isset( $fields['billing']['billing_country'] ) ) {
		$fields['billing']['billing_country']['priority'] = 50;
		$fields['billing']['billing_country']['label']    = __( 'Country', 'foxfire-child' );
		$fields['billing']['billing_country']['class']    = array( 'form-row-wide', 'ff-field-wrap', 'address-field' );
		$fields['billing']['billing_country']['required'] = true;
	}

	// 6. State / Region (Row 4 - Full Width)
	if ( isset( $fields['billing']['billing_state'] ) ) {
		$fields['billing']['billing_state']['priority']    = 60;
		$fields['billing']['billing_state']['label']       = __( 'State / Region', 'foxfire-child' );
		$fields['billing']['billing_state']['placeholder'] = __( 'Select State / Region', 'foxfire-child' );
		$fields['billing']['billing_state']['class']       = array( 'form-row-wide', 'ff-field-wrap', 'address-field' );
		$fields['billing']['billing_state']['required']    = true;
	}

	// 7. City (Row 5 - Left)
	if ( isset( $fields['billing']['billing_city'] ) ) {
		$fields['billing']['billing_city']['priority']    = 70;
		$fields['billing']['billing_city']['label']       = __( 'City', 'foxfire-child' );
		$fields['billing']['billing_city']['placeholder'] = __( 'City', 'foxfire-child' );
		$fields['billing']['billing_city']['class']       = array( 'form-row-first', 'ff-field-wrap', 'address-field' );
		$fields['billing']['billing_city']['required']    = true;
	}

	// 8. ZIP / Postal code (Row 5 - Right)
	if ( isset( $fields['billing']['billing_postcode'] ) ) {
		$fields['billing']['billing_postcode']['priority']    = 80;
		$fields['billing']['billing_postcode']['label']       = __( 'ZIP / Postal code', 'foxfire-child' );
		$fields['billing']['billing_postcode']['placeholder'] = __( 'ZIP / Postal code', 'foxfire-child' );
		$fields['billing']['billing_postcode']['class']       = array( 'form-row-last', 'ff-field-wrap', 'address-field' );
		$fields['billing']['billing_postcode']['required']    = true;
	}

	// 9. Street Address Line 1 (Row 6 - Full Width)
	if ( isset( $fields['billing']['billing_address_1'] ) ) {
		$fields['billing']['billing_address_1']['priority']    = 90;
		$fields['billing']['billing_address_1']['label']       = __( 'Street address', 'foxfire-child' );
		$fields['billing']['billing_address_1']['placeholder'] = __( 'House number and street name', 'foxfire-child' );
		$fields['billing']['billing_address_1']['class']       = array( 'form-row-wide', 'ff-field-wrap', 'address-field' );
		$fields['billing']['billing_address_1']['required']    = true;
	}

	// 10. Street Address Line 2 (Row 6 - Full Width, Optional)
	if ( isset( $fields['billing']['billing_address_2'] ) ) {
		$fields['billing']['billing_address_2']['priority']    = 100;
		$fields['billing']['billing_address_2']['label']       = __( 'Apartment, suite, unit (optional)', 'foxfire-child' );
		$fields['billing']['billing_address_2']['label_class'] = array( 'screen-reader-text' );
		$fields['billing']['billing_address_2']['placeholder'] = __( 'Apartment, suite, unit, building (optional)', 'foxfire-child' );
		$fields['billing']['billing_address_2']['class']       = array( 'form-row-wide', 'ff-field-wrap', 'address-field' );
		$fields['billing']['billing_address_2']['required']    = false;
	}

	// Disable order notes completely
	unset( $fields['order']['order_comments'] );

	// Sort fields by priority so associative array order matches the priority hierarchy
	uasort( $fields['billing'], function ( $a, $b ) {
		$a_priority = $a['priority'] ?? 100;
		$b_priority = $b['priority'] ?? 100;
		return $a_priority <=> $b_priority;
	} );

	return $fields;
}
add_filter( 'woocommerce_checkout_fields', 'foxfire_customize_checkout_fields', 999 );

/**
 * Filter billing fields directly as well.
 */
function foxfire_customize_billing_fields( array $fields ): array {
	if ( isset( $fields['billing_first_name'] ) ) {
		$fields['billing_first_name']['priority'] = 10;
		$fields['billing_first_name']['class']    = array( 'form-row-first', 'ff-field-wrap' );
		$fields['billing_first_name']['required'] = true;
	}
	if ( isset( $fields['billing_last_name'] ) ) {
		$fields['billing_last_name']['priority'] = 20;
		$fields['billing_last_name']['class']    = array( 'form-row-last', 'ff-field-wrap' );
		$fields['billing_last_name']['required'] = true;
	}
	if ( isset( $fields['billing_email'] ) ) {
		$fields['billing_email']['priority'] = 30;
		$fields['billing_email']['class']    = array( 'form-row-first', 'ff-field-wrap' );
		$fields['billing_email']['required'] = true;
	}
	if ( isset( $fields['billing_phone'] ) ) {
		$fields['billing_phone']['priority'] = 40;
		$fields['billing_phone']['label']    = __( 'Phone number', 'foxfire-child' );
		$fields['billing_phone']['class']    = array( 'form-row-last', 'ff-field-wrap' );
		$fields['billing_phone']['required'] = true;
	}
	if ( isset( $fields['billing_country'] ) ) {
		$fields['billing_country']['priority'] = 50;
		$fields['billing_country']['label']    = __( 'Country', 'foxfire-child' );
		$fields['billing_country']['class']    = array( 'form-row-wide', 'ff-field-wrap', 'address-field' );
		$fields['billing_country']['required'] = true;
	}
	if ( isset( $fields['billing_state'] ) ) {
		$fields['billing_state']['priority'] = 60;
		$fields['billing_state']['label']    = __( 'State / Region', 'foxfire-child' );
		$fields['billing_state']['class']    = array( 'form-row-wide', 'ff-field-wrap', 'address-field' );
		$fields['billing_state']['required'] = true;
	}
	if ( isset( $fields['billing_city'] ) ) {
		$fields['billing_city']['priority'] = 70;
		$fields['billing_city']['class']    = array( 'form-row-first', 'ff-field-wrap', 'address-field' );
		$fields['billing_city']['required'] = true;
	}
	if ( isset( $fields['billing_postcode'] ) ) {
		$fields['billing_postcode']['priority'] = 80;
		$fields['billing_postcode']['class']    = array( 'form-row-last', 'ff-field-wrap', 'address-field' );
		$fields['billing_postcode']['required'] = true;
	}
	if ( isset( $fields['billing_address_1'] ) ) {
		$fields['billing_address_1']['priority'] = 90;
		$fields['billing_address_1']['class']    = array( 'form-row-wide', 'ff-field-wrap', 'address-field' );
		$fields['billing_address_1']['required'] = true;
	}
	if ( isset( $fields['billing_address_2'] ) ) {
		$fields['billing_address_2']['priority'] = 100;
		$fields['billing_address_2']['class']    = array( 'form-row-wide', 'ff-field-wrap', 'address-field' );
		$fields['billing_address_2']['required'] = false;
	}

	uasort( $fields, function ( $a, $b ) {
		$a_priority = $a['priority'] ?? 100;
		$b_priority = $b['priority'] ?? 100;
		return $a_priority <=> $b_priority;
	} );

	return $fields;
}
add_filter( 'woocommerce_billing_fields', 'foxfire_customize_billing_fields', 999 );

// Disable order notes field globally in WooCommerce
add_filter( 'woocommerce_enable_order_notes_field', '__return_false', 999 );

/**
 * Dequeue selectWoo & select2 on checkout to prevent dropdown trapping & z-index issues.
 * Native select elements provide 100% reliable dropdown experience on both mobile & desktop.
 */
function foxfire_disable_checkout_select2(): void {
	if ( is_checkout() ) {
		wp_dequeue_script( 'selectWoo' );
		wp_dequeue_style( 'select2' );
	}
}
add_action( 'wp_enqueue_scripts', 'foxfire_disable_checkout_select2', 100 );

// Terms and conditions must be unchecked by default
add_filter( 'woocommerce_terms_is_checked_default', '__return_false', 999 );


