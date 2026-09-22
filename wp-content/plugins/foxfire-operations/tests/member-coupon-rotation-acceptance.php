<?php
/** Local-only WooCommerce coupon terms and rollover checks with disposable data. */
defined( 'ABSPATH' ) || exit;

if ( 'local' !== wp_get_environment_type() ) {
	throw new RuntimeException( 'Member coupon rotation acceptance runs only locally.' );
}

require_once ABSPATH . 'wp-admin/includes/user.php';

$saved    = array( WC()->cart, WC()->customer, WC()->session, get_current_user_id() );
$products = array();
$coupons  = array();
$users    = array();
$check    = static function ( bool $condition, string $label ): void {
	if ( ! $condition ) {
		throw new RuntimeException( 'FAIL: ' . $label );
	}
	WP_CLI::log( 'PASS: ' . $label );
};

try {
	wp_set_current_user( 0 );
	WC()->session  = new WC_Session_Handler(); // Never attach to a browser session.
	WC()->customer = new WC_Customer( 0, false );
	WC()->cart     = new WC_Cart();

	$regular = new WC_Product_Simple();
	$regular->set_name( 'Temporary member coupon regular product' );
	$regular->set_status( 'publish' );
	$regular->set_catalog_visibility( 'hidden' );
	$regular->set_regular_price( '100' );
	$products[] = $regular->save();
	update_post_meta( $regular->get_id(), 'foxfire_tier_enable', '0' );

	$sale = new WC_Product_Simple();
	$sale->set_name( 'Temporary member coupon sale product' );
	$sale->set_status( 'publish' );
	$sale->set_catalog_visibility( 'hidden' );
	$sale->set_regular_price( '100' );
	$sale->set_sale_price( '80' );
	$products[] = $sale->save();
	update_post_meta( $sale->get_id(), 'foxfire_tier_enable', '0' );

	$check( (bool) WC()->cart->add_to_cart( $regular->get_id(), 2 ), 'Isolated cart contains two regular-priced items' );
	$check( 200.0 === (float) WC()->cart->get_subtotal(), 'Fixture subtotal is 200' );

	$coupon = new WC_Coupon();
	$coupon->set_code( 'ff-member-terms-' . wp_generate_uuid4() );
	$coupon->set_discount_type( 'percent' );
	$coupon->set_amount( 10 );
	$coupons[] = $coupon->save();
	$check( WC()->cart->apply_coupon( $coupon->get_code() ), 'Guest can apply an ordinary 10% coupon' );
	$check( 20.0 === (float) WC()->cart->get_discount_total(), 'Percentage discount is 10% of the eligible subtotal' );
	WC()->cart->remove_coupon( $coupon->get_code() );
	wc_clear_notices();

	$coupon->set_discount_type( 'fixed_cart' );
	$coupon->set_amount( 15 );
	$coupon->save();
	$check( WC()->cart->apply_coupon( $coupon->get_code() ), 'Fixed cart coupon applies' );
	$check( 15.0 === (float) WC()->cart->get_discount_total(), 'Fixed cart amount is taken once from the whole cart' );
	WC()->cart->remove_coupon( $coupon->get_code() );
	wc_clear_notices();

	$coupon->set_discount_type( 'fixed_product' );
	$coupon->save();
	$check( WC()->cart->apply_coupon( $coupon->get_code() ), 'Fixed product coupon applies' );
	$check( 30.0 === (float) WC()->cart->get_discount_total(), 'Fixed product amount applies to each of two eligible items' );
	WC()->cart->remove_coupon( $coupon->get_code() );
	wc_clear_notices();

	$coupon->set_discount_type( 'percent' );
	$coupon->set_amount( 10 );
	$coupon->set_minimum_amount( 250 );
	$coupon->save();
	$check( ! WC()->cart->apply_coupon( $coupon->get_code() ), 'Minimum spend above subtotal rejects the code' );
	wc_clear_notices();
	$coupon->set_minimum_amount( 200 );
	$coupon->save();
	$check( WC()->cart->apply_coupon( $coupon->get_code() ), 'Minimum spend equal to subtotal accepts the code' );
	WC()->cart->remove_coupon( $coupon->get_code() );
	wc_clear_notices();

	WC()->cart->empty_cart();
	WC()->cart->add_to_cart( $sale->get_id() );
	$coupon->set_minimum_amount( 0 );
	$coupon->set_exclude_sale_items( true );
	$coupon->save();
	$check( ! WC()->cart->apply_coupon( $coupon->get_code() ), 'Sale exclusion rejects a cart with only sale items' );
	wc_clear_notices();

	$coupon->set_exclude_sale_items( false );
	$coupon->set_date_expires( time() - HOUR_IN_SECONDS );
	$coupon->save();
	$check( ! WC()->cart->apply_coupon( $coupon->get_code() ), 'Expired coupon cannot be applied' );
	wc_clear_notices();

	WC()->cart->empty_cart();
	WC()->cart->add_to_cart( $regular->get_id() );
	$old = new WC_Coupon();
	$old->set_code( 'ff-member-old-' . wp_generate_uuid4() );
	$old->set_discount_type( 'fixed_cart' );
	$old->set_amount( 10 );
	$coupons[] = $old->save();
	$new = new WC_Coupon();
	$new->set_code( 'ff-member-new-' . wp_generate_uuid4() );
	$new->set_discount_type( 'fixed_cart' );
	$new->set_amount( 20 );
	$new->set_usage_limit_per_user( 1 );
	$coupons[] = $new->save();
	$check( WC()->cart->apply_coupon( $old->get_code() ), 'Old code applies before rollover' );
	$check( 10.0 === (float) WC()->cart->get_discount_total(), 'Old code applies its configured value' );
	WC()->cart->remove_coupon( $old->get_code() );
	wc_clear_notices();
	$old->set_status( 'draft' );
	$old->save();
	$check( ! WC()->cart->apply_coupon( $old->get_code() ), 'Unpublished old code stops working' );
	wc_clear_notices();
	$check( WC()->cart->apply_coupon( $new->get_code() ), 'New code works after rollover' );
	$check( 20.0 === (float) WC()->cart->get_discount_total(), 'New code applies its configured value' );
	WC()->cart->remove_coupon( $new->get_code() );
	wc_clear_notices();

	$customer_id = wp_insert_user(
		array(
			'user_login' => 'ff-member-rotation-' . strtolower( wp_generate_password( 8, false, false ) ),
			'user_pass'  => wp_generate_password( 24 ),
			'user_email' => 'ff-member-rotation-' . strtolower( wp_generate_password( 8, false, false ) ) . '@example.test',
			'role'       => 'customer',
		)
	);
	$check( ! is_wp_error( $customer_id ), 'Disposable customer account was created' );
	$users[] = $customer_id;
	wp_set_current_user( $customer_id );
	$check( WC()->cart->apply_coupon( $new->get_code() ), 'Customer can use the new code once' );
	WC()->cart->remove_coupon( $new->get_code() );
	$new->increase_usage_count( $customer_id ); // Simulate one recorded redemption without placing an order.
	wc_clear_notices();
	$check( ! WC()->cart->apply_coupon( $new->get_code() ), 'Same account cannot use the same code twice' );
	$check( 1 === WC()->cart->get_cart_contents_count(), 'Rejected second use preserves the cart' );

	WP_CLI::success( 'Member coupon terms and rotation passed with disposable fixtures.' );
} finally {
	foreach ( $coupons as $coupon_id ) {
		$fixture = new WC_Coupon( $coupon_id );
		if ( $fixture->get_id() ) {
			$fixture->delete( true );
		} else {
			wp_delete_post( $coupon_id, true );
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
	list( WC()->cart, WC()->customer, WC()->session, $user_id ) = $saved;
	wp_set_current_user( $user_id );
}
