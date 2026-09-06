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
 * Define customer account menu items in clean, simplified order:
 * 1. Orders (default landing)
 * 2. Addresses
 * 3. Account Details
 * 4. Batch / COA Lookup
 * 5. Logout
 *
 * @param array<string, string> $items Existing menu items.
 * @return array<string, string> Filtered and reordered menu items.
 */
function foxfire_account_custom_menu_items( array $items ): array {
	return array(
		'orders'          => __( 'Orders', 'foxfire-child' ),
		'edit-address'    => __( 'Addresses', 'foxfire-child' ),
		'edit-account'    => __( 'Account Details', 'foxfire-child' ),
		'batch-coa'       => __( 'Batch / COA Lookup', 'foxfire-child' ),
		'customer-logout' => __( 'Logout', 'foxfire-child' ),
	);
}
add_filter( 'woocommerce_account_menu_items', 'foxfire_account_custom_menu_items', 99 );

/**
 * Map custom menu endpoints to their proper URLs.
 */
add_filter( 'woocommerce_get_endpoint_url', function( $url, $endpoint, $value, $permalink ) {
	if ( 'batch-coa' === $endpoint ) {
		return home_url( '/testing-coa/' );
	}
	return $url;
}, 10, 4 );

/**
 * Redirect logged-in customers from root /my-account/ (dashboard) and obsolete downloads directly to Orders.
 */
add_action( 'template_redirect', function(): void {
	if ( ! is_account_page() || ! is_user_logged_in() ) {
		return;
	}

	// If visiting obsolete downloads endpoint or root account dashboard (no active endpoint)
	if ( is_wc_endpoint_url( 'downloads' ) || ! is_wc_endpoint_url() ) {
		wp_safe_redirect( wc_get_account_endpoint_url( 'orders' ) );
		exit;
	}
} );

/**
 * Remove WooCommerce customer email verification banner from Orders page.
 */
add_action( 'init', function(): void {
	if ( function_exists( 'wc_get_container' ) && class_exists( '\Automattic\WooCommerce\Internal\CustomerEmailVerification\VerificationController' ) ) {
		try {
			$controller = wc_get_container()->get( \Automattic\WooCommerce\Internal\CustomerEmailVerification\VerificationController::class );
			if ( $controller ) {
				remove_action( 'woocommerce_before_account_orders', array( $controller, 'render_prompt' ) );
				remove_action( 'woocommerce_before_account_orders', array( $controller, 'print_result_notice' ), 5 );
			}
		} catch ( \Throwable $e ) {
			// Container or service unavailable; no-op.
		}
	}
}, 20 );

/**
 * Direct customer login and registration to Orders page by default.
 */
add_filter( 'woocommerce_login_redirect', function( string $redirect, $user ): string {
	$account_url = untrailingslashit( wc_get_page_permalink( 'myaccount' ) );
	$current_url = untrailingslashit( $redirect );

	if ( empty( $redirect ) || $current_url === $account_url ) {
		return wc_get_account_endpoint_url( 'orders' );
	}
	return $redirect;
}, 10, 2 );

add_filter( 'woocommerce_registration_redirect', function( string $redirect ): string {
	return wc_get_account_endpoint_url( 'orders' );
}, 10, 1 );

/**
 * Ensure address fields on the Edit Address account page match the exact ordering,
 * priorities, classes, and structure of the checkout delivery modal.
 *
 * @param array<string, mixed> $fields Address fields.
 * @param string               $load_address Address type ('billing' or 'shipping').
 * @return array<string, mixed> Custom ordered and styled fields.
 */
function foxfire_customize_address_to_edit( array $fields, string $load_address ): array {
	$prefix = $load_address . '_';

	// Remove company field to match checkout
	unset( $fields[ $prefix . 'company' ] );

	// 1. First name (Row 1 Left)
	if ( isset( $fields[ $prefix . 'first_name' ] ) ) {
		$fields[ $prefix . 'first_name' ]['priority']    = 10;
		$fields[ $prefix . 'first_name' ]['label']       = __( 'First name', 'foxfire-child' );
		$fields[ $prefix . 'first_name' ]['placeholder'] = __( 'First name', 'foxfire-child' );
		$fields[ $prefix . 'first_name' ]['class']       = array( 'form-row-first', 'ff-field-wrap' );
		$fields[ $prefix . 'first_name' ]['required']    = true;
	}

	// 2. Last name (Row 1 Right)
	if ( isset( $fields[ $prefix . 'last_name' ] ) ) {
		$fields[ $prefix . 'last_name' ]['priority']    = 20;
		$fields[ $prefix . 'last_name' ]['label']       = __( 'Last name', 'foxfire-child' );
		$fields[ $prefix . 'last_name' ]['placeholder'] = __( 'Last name', 'foxfire-child' );
		$fields[ $prefix . 'last_name' ]['class']       = array( 'form-row-last', 'ff-field-wrap' );
		$fields[ $prefix . 'last_name' ]['required']    = true;
	}

	// 3. Email (Row 2 Left - if billing)
	if ( isset( $fields[ $prefix . 'email' ] ) ) {
		$fields[ $prefix . 'email' ]['priority']    = 30;
		$fields[ $prefix . 'email' ]['label']       = __( 'Email address', 'foxfire-child' );
		$fields[ $prefix . 'email' ]['placeholder'] = __( 'your.email@example.com', 'foxfire-child' );
		$fields[ $prefix . 'email' ]['class']       = array( 'form-row-first', 'ff-field-wrap' );
		$fields[ $prefix . 'email' ]['required']    = true;
	}

	// 4. Phone (Row 2 Right - if billing or shipping)
	if ( isset( $fields[ $prefix . 'phone' ] ) ) {
		$fields[ $prefix . 'phone' ]['priority']    = 40;
		$fields[ $prefix . 'phone' ]['label']       = __( 'Phone number', 'foxfire-child' );
		$fields[ $prefix . 'phone' ]['placeholder'] = __( 'Phone number (for delivery updates)', 'foxfire-child' );
		$fields[ $prefix . 'phone' ]['class']       = isset( $fields[ $prefix . 'email' ] ) ? array( 'form-row-last', 'ff-field-wrap' ) : array( 'form-row-wide', 'ff-field-wrap' );
		$fields[ $prefix . 'phone' ]['required']    = true;
	}

	// 5. Country (Row 3 - Full Width)
	if ( isset( $fields[ $prefix . 'country' ] ) ) {
		$fields[ $prefix . 'country' ]['priority'] = 50;
		$fields[ $prefix . 'country' ]['label']    = __( 'Country', 'foxfire-child' );
		$fields[ $prefix . 'country' ]['class']    = array( 'form-row-wide', 'ff-field-wrap', 'address-field' );
		$fields[ $prefix . 'country' ]['required'] = true;
	}

	// 6. Street Address Line 1 (Row 4 - Full Width)
	if ( isset( $fields[ $prefix . 'address_1' ] ) ) {
		$fields[ $prefix . 'address_1' ]['priority']    = 60;
		$fields[ $prefix . 'address_1' ]['label']       = __( 'Street address', 'foxfire-child' );
		$fields[ $prefix . 'address_1' ]['placeholder'] = __( 'House number and street name', 'foxfire-child' );
		$fields[ $prefix . 'address_1' ]['class']       = array( 'form-row-wide', 'ff-field-wrap', 'address-field' );
		$fields[ $prefix . 'address_1' ]['required']    = true;
	}

	// 7. Street Address Line 2 (Row 5 - Full Width, optional)
	if ( isset( $fields[ $prefix . 'address_2' ] ) ) {
		$fields[ $prefix . 'address_2' ]['priority']    = 70;
		$fields[ $prefix . 'address_2' ]['label']       = __( 'Apartment, suite, unit (optional)', 'foxfire-child' );
		$fields[ $prefix . 'address_2' ]['placeholder'] = __( 'Apartment, suite, unit, building (optional)', 'foxfire-child' );
		$fields[ $prefix . 'address_2' ]['class']       = array( 'form-row-wide', 'ff-field-wrap', 'address-field' );
		$fields[ $prefix . 'address_2' ]['required']    = false;
	}

	// 8. Town / City (Row 6 - Left Column)
	if ( isset( $fields[ $prefix . 'city' ] ) ) {
		$fields[ $prefix . 'city' ]['priority']    = 80;
		$fields[ $prefix . 'city' ]['label']       = __( 'Town / City', 'foxfire-child' );
		$fields[ $prefix . 'city' ]['placeholder'] = __( 'Town / City', 'foxfire-child' );
		$fields[ $prefix . 'city' ]['class']       = array( 'form-row-first', 'ff-field-wrap', 'address-field' );
		$fields[ $prefix . 'city' ]['required']    = true;
	}

	// 9. ZIP / Postal code (Row 6 - Right Column, strictly paired with Town / City)
	if ( isset( $fields[ $prefix . 'postcode' ] ) ) {
		$fields[ $prefix . 'postcode' ]['priority']    = 85;
		$fields[ $prefix . 'postcode' ]['label']       = __( 'ZIP / Postal code', 'foxfire-child' );
		$fields[ $prefix . 'postcode' ]['placeholder'] = __( 'ZIP / Postal code', 'foxfire-child' );
		$fields[ $prefix . 'postcode' ]['class']       = array( 'form-row-last', 'ff-field-wrap', 'address-field' );
		$fields[ $prefix . 'postcode' ]['required']    = true;
	}

	// 10. State / Region (Row 7 - Full Width)
	if ( isset( $fields[ $prefix . 'state' ] ) ) {
		$fields[ $prefix . 'state' ]['priority']    = 90;
		$fields[ $prefix . 'state' ]['label']       = __( 'State / Region', 'foxfire-child' );
		$fields[ $prefix . 'state' ]['placeholder'] = __( 'Select State / Region', 'foxfire-child' );
		$fields[ $prefix . 'state' ]['class']       = array( 'form-row-wide', 'ff-field-wrap', 'address-field' );
		$fields[ $prefix . 'state' ]['required']    = true;
	}

	uasort( $fields, function ( $a, $b ) {
		$a_priority = $a['priority'] ?? 100;
		$b_priority = $b['priority'] ?? 100;
		return $a_priority <=> $b_priority;
	} );

	return $fields;
}
add_filter( 'woocommerce_address_to_edit', 'foxfire_customize_address_to_edit', 999, 2 );

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
