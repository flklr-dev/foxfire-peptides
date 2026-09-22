<?php
/** Local-only checks for the opt-in Foxfire account coupon restriction. */
defined( 'ABSPATH' ) || exit;

if ( 'local' !== wp_get_environment_type() ) {
	throw new RuntimeException( 'Coupon login acceptance runs only locally.' );
}

require_once ABSPATH . 'wp-admin/includes/user.php';

$saved      = array( WC()->cart, WC()->customer, WC()->session, get_current_user_id(), $_POST );
$products   = array();
$coupons    = array();
$users      = array();
$check      = static function ( bool $condition, string $label ): void {
	if ( ! $condition ) {
		throw new RuntimeException( 'FAIL: ' . $label );
	}
	WP_CLI::log( 'PASS: ' . $label );
};

try {
	wp_set_current_user( 0 );
	$_POST        = array();
	WC()->session  = new WC_Session_Handler(); // Keep browser/customer sessions untouched.
	WC()->customer = new WC_Customer( 0, false );
	WC()->cart     = new WC_Cart();

	$product = new WC_Product_Simple();
	$product->set_name( 'Temporary coupon login fixture' );
	$product->set_status( 'publish' );
	$product->set_catalog_visibility( 'hidden' );
	$product->set_regular_price( '40' );
	$products[] = $product->save();
	update_post_meta( $product->get_id(), 'foxfire_tier_enable', '0' );
	$check( (bool) WC()->cart->add_to_cart( $product->get_id() ), 'Isolated guest cart has a disposable product' );

	$coupon = new WC_Coupon();
	$coupon->set_code( 'ff-login-acceptance-' . wp_generate_uuid4() );
	$coupon->set_discount_type( 'percent' );
	$coupon->set_amount( 10 );
	$coupon->set_usage_limit_per_user( 1 );
	$coupons[] = $coupon->save();
	$check( ! foxfire_operations_coupon_requires_login( $coupon ), 'New coupon allows guests by default' );
	$check( WC()->cart->apply_coupon( $coupon->get_code() ), 'Ordinary coupon still works for a guest' );
	WC()->cart->remove_coupon( $coupon->get_code() );
	wc_clear_notices();

	$staff_id = wp_insert_user(
		array(
			'user_login' => 'ff-coupon-staff-' . strtolower( wp_generate_password( 8, false, false ) ),
			'user_pass'  => wp_generate_password( 24 ),
			'user_email' => 'ff-coupon-staff-' . strtolower( wp_generate_password( 8, false, false ) ) . '@example.test',
			'role'       => 'shop_manager',
		)
	);
	$check( ! is_wp_error( $staff_id ), 'Disposable Shop Manager was created' );
	$users[] = $staff_id;
	wp_set_current_user( $staff_id );
	$_POST['_foxfire_requires_login'] = 'yes';
	foxfire_operations_save_coupon_login_option( $coupon->get_id(), $coupon );
	$check( foxfire_operations_coupon_requires_login( new WC_Coupon( $coupon->get_id() ) ), 'Shop Manager can save the login checkbox' );
	if ( ! function_exists( 'woocommerce_wp_checkbox' ) ) {
		require_once WC()->plugin_path() . '/includes/admin/wc-meta-box-functions.php';
	}
	ob_start();
	do_action( 'woocommerce_coupon_options', $coupon->get_id(), $coupon );
	$editor_html = ob_get_clean();
	$check( str_contains( $editor_html, 'Requires Foxfire login' ) && str_contains( $editor_html, 'id="_foxfire_requires_login"' ) && str_contains( $editor_html, 'checked=' ), 'Coupon editor shows the checked login option' );
	ob_start();
	foxfire_operations_render_coupon_review_metabox( get_post( $coupon->get_id() ) );
	$review_html = ob_get_clean();
	$check( str_contains( $review_html, 'Foxfire login' ) && str_contains( $review_html, 'Required' ), 'Promotion review summarizes the login restriction' );
	wp_set_current_user( 0 );

	$validation = ( new WC_Discounts( WC()->cart ) )->is_coupon_valid( new WC_Coupon( $coupon->get_id() ) );
	$check( is_wp_error( $validation ) && str_contains( $validation->get_error_message(), wc_get_page_permalink( 'myaccount' ) ), 'Direct guest coupon validation returns a sign-in link' );
	$check( ! WC()->cart->apply_coupon( $coupon->get_code() ) && ! WC()->cart->has_discount( $coupon->get_code() ), 'Guest cannot apply restricted coupon to cart or checkout' );
	$check( wc_has_notice( foxfire_operations_coupon_login_message(), 'error' ), 'Guest receives the Foxfire login error' );
	$check( 1 === WC()->cart->get_cart_contents_count(), 'Rejected coupon does not disturb cart contents' );
	wc_clear_notices();

	$customer_id = wp_insert_user(
		array(
			'user_login' => 'ff-coupon-customer-' . strtolower( wp_generate_password( 8, false, false ) ),
			'user_pass'  => wp_generate_password( 24 ),
			'user_email' => 'ff-coupon-customer-' . strtolower( wp_generate_password( 8, false, false ) ) . '@example.test',
			'role'       => 'customer',
		)
	);
	$check( ! is_wp_error( $customer_id ), 'Disposable Foxfire customer was created' );
	$users[] = $customer_id;
	wp_set_current_user( $customer_id );
	$check( WC()->cart->apply_coupon( $coupon->get_code() ), 'Signed-in customer can apply restricted coupon' );
	WC()->cart->calculate_totals();
	$check( 4.0 === (float) WC()->cart->get_discount_total(), 'Signed-in customer receives the expected discount' );

	wp_set_current_user( 0 ); // Simulate logout while the code remains in the cart.
	WC()->cart->calculate_totals();
	$check( 0.0 === (float) WC()->cart->get_discount_total(), 'Logged-out cart cannot retain a discounted total' );
	$check( foxfire_operations_guest_applied_login_coupon(), 'Checkout detects the stale restricted coupon' );
	do_action( 'woocommerce_checkout_process' );
	$check( wc_has_notice( foxfire_operations_coupon_login_message(), 'error' ), 'Checkout submission blocks the logged-out customer' );
	$errors = new WP_Error();
	foxfire_operations_validate_checkout_coupon_login_final( array(), $errors );
	$check( in_array( 'foxfire_coupon_login_required', $errors->get_error_codes(), true ), 'Final checkout guard rejects the stale guest coupon' );
	$check( 1 === WC()->cart->get_cart_contents_count(), 'Checkout rejection preserves the product in cart' );
	WC()->cart->remove_coupon( $coupon->get_code() );
	wc_clear_notices();

	wp_set_current_user( $customer_id );
	$coupon->increase_usage_count( $customer_id );
	$check( ! WC()->cart->apply_coupon( $coupon->get_code() ), 'WooCommerce per-user limit rejects the second use' );
	wc_clear_notices();

	wp_set_current_user( $staff_id );
	unset( $_POST['_foxfire_requires_login'] );
	foxfire_operations_save_coupon_login_option( $coupon->get_id(), $coupon );
	$check( ! foxfire_operations_coupon_requires_login( new WC_Coupon( $coupon->get_id() ) ), 'Unchecking the option restores ordinary guest eligibility' );

	WP_CLI::success( 'Coupon login restriction passed with disposable fixtures.' );
} finally {
	foreach ( $coupons as $coupon_id ) {
		$fixture = new WC_Coupon( $coupon_id );
		if ( $fixture->get_id() ) {
			$fixture->delete( true );
		}
	}
	foreach ( $products as $product_id ) {
		$fixture = wc_get_product( $product_id );
		if ( $fixture ) {
			$fixture->delete( true );
		}
	}
	foreach ( $users as $user_id ) {
		wp_delete_user( $user_id );
	}
	list( WC()->cart, WC()->customer, WC()->session, $user_id, $_POST ) = $saved;
	wp_set_current_user( $user_id );
}
