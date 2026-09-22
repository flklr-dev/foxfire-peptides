<?php
/** Stage 5 local inventory acceptance with disposable WooCommerce records. */
defined( 'ABSPATH' ) || exit;

if ( 'local' !== wp_get_environment_type() || ! foxfire_operations_local_mail_is_enabled() ) {
	throw new RuntimeException( 'Run only on the isolated local store with Mailpit enabled.' );
}

$saved    = array( WC()->cart, WC()->customer, WC()->session, get_current_user_id() );
$products = array();
$orders   = array();
$coupons  = array();
$users    = array();
$mail     = array();
$checks   = 0;
$only_fixture_cancellation = null;
$label    = 'Temporary Stage 5 inventory ' . strtolower( wp_generate_password( 8, false, false ) );
$check    = static function ( bool $condition, string $message ) use ( &$checks ): void {
	if ( ! $condition ) {
		throw new RuntimeException( 'FAIL: ' . $message );
	}
	++$checks;
	WP_CLI::log( 'PASS: ' . $message );
};
$capture_mail = static function ( array $mail_data ) use ( &$mail ): void {
	$mail[] = $mail_data;
};

add_action( 'wp_mail_succeeded', $capture_mail );

try {
	$check( 'yes' === get_option( 'woocommerce_manage_stock' ), 'Global stock management is enabled' );
	$check( 'yes' === get_option( 'woocommerce_notify_low_stock' ) && 'yes' === get_option( 'woocommerce_notify_no_stock' ), 'Low and out-of-stock notifications are enabled' );
	$check( 'info@foxfirepeptides.com' === get_option( 'woocommerce_stock_email_recipient' ), 'Stock notification recipient is the approved Foxfire address' );
	$check( foxfire_operations_checkout_is_review_mode(), 'Customer checkout remains protected by review mode' );

	wp_set_current_user( 0 );
	WC()->session  = new WC_Session_Handler();
	WC()->customer = new WC_Customer( 0, false );
	WC()->cart     = new WC_Cart();
	WC()->mailer();

	$product = new WC_Product_Simple();
	$product->set_name( $label );
	$product->set_status( 'publish' );
	$product->set_catalog_visibility( 'hidden' );
	$product->set_regular_price( '25' );
	$product->set_manage_stock( true );
	$product->set_stock_quantity( 2 );
	$product->set_low_stock_amount( 1 );
	$product->set_backorders( 'no' );
	$products[] = $product_id = $product->save();
	$check( 2 === wc_get_product( $product_id )->get_stock_quantity(), 'Disposable simple product starts with two vials' );
	$check( 'no' === wc_get_product( $product_id )->get_backorders(), 'Backorders are disabled' );

	$order1 = wc_create_order();
	$orders[] = $order1->get_id();
	$order1->add_product( wc_get_product( $product_id ), 1 );
	$order1->calculate_totals();
	$check( 2 === wc_get_product( $product_id )->get_stock_quantity(), 'Cart and pending order do not reduce stock early' );
	$order1->update_status( 'processing' );
	$check( 1 === wc_get_product( $product_id )->get_stock_quantity(), 'First processed order reduces 2 to 1' );
	$check( 'instock' === wc_get_product( $product_id )->get_stock_status(), 'One remaining vial stays in stock' );
	$check( count( array_filter( $mail, static fn( $message ) => str_contains( strtolower( $message['subject'] ), 'low in stock' ) && str_contains( $message['message'], $label ) && in_array( 'info@foxfirepeptides.com', (array) $message['to'], true ) ) ) > 0, 'Low-stock alert is accepted by local SMTP/Mailpit for the configured recipient' );

	$cart_key = WC()->cart->add_to_cart( $product_id, 1 );
	$check( (bool) $cart_key, 'Customer can add the last available vial to an existing cart' );
	$order2 = wc_create_order();
	$orders[] = $order2->get_id();
	$order2->add_product( wc_get_product( $product_id ), 1 );
	$order2->calculate_totals();
	$order2->update_status( 'processing' );
	$check( 0 === wc_get_product( $product_id )->get_stock_quantity(), 'Second processed order reduces 1 to 0' );
	$check( 'outofstock' === wc_get_product( $product_id )->get_stock_status(), 'Zero vials automatically changes status to Out of stock' );
	$check( count( array_filter( $mail, static fn( $message ) => str_contains( strtolower( $message['subject'] ), 'out of stock' ) && str_contains( $message['message'], $label ) && in_array( 'info@foxfirepeptides.com', (array) $message['to'], true ) ) ) > 0, 'Out-of-stock alert is accepted by local SMTP/Mailpit for the configured recipient' );
	$check( ! wc_get_product( $product_id )->is_in_stock(), 'Storefront availability marks the product unavailable' );
	$check( false === WC()->cart->add_to_cart( $product_id, 1 ), 'A new add-to-cart attempt fails at zero stock' );
	wc_clear_notices();
	// In a real checkout request WooCommerce reloads cart product objects from DB.
	// Refresh this in-memory CLI cart so it sees the other order's stock change.
	WC()->cart->cart_contents[ $cart_key ]['data'] = wc_get_product( $product_id );
	$check( ! WC()->cart->check_cart_items() && wc_notice_count( 'error' ) > 0, 'A cart left open before depletion fails stock validation at checkout' );
	wc_clear_notices();
	WC()->cart->empty_cart();

	$order2->update_status( 'cancelled' );
	$check( 1 === wc_get_product( $product_id )->get_stock_quantity(), 'Cancelling the last order restores exactly one vial' );
	$check( 'instock' === wc_get_product( $product_id )->get_stock_status(), 'Restored vial becomes available again' );
	wc_maybe_increase_stock_levels( $order2->get_id() );
	$check( 1 === wc_get_product( $product_id )->get_stock_quantity(), 'Duplicate restoration does not add another vial' );
	$order1->update_status( 'cancelled' );
	$check( 2 === wc_get_product( $product_id )->get_stock_quantity(), 'Cancelling both test orders returns inventory to two' );

	$product->set_stock_quantity( 5 );
	$product->save();
	$order3 = wc_create_order();
	$orders[] = $order3->get_id();
	$order3->add_product( wc_get_product( $product_id ), 3 );
	$order3->calculate_totals();
	$order3->update_status( 'processing' );
	$check( 2 === wc_get_product( $product_id )->get_stock_quantity(), 'Buying three vials reduces 5 to 2' );
	$order3->update_status( 'cancelled' );
	$check( 5 === wc_get_product( $product_id )->get_stock_quantity(), 'Cancelling the three-vial order restores exactly three' );

	$parent = new WC_Product_Variable();
	$parent->set_name( $label . ' strengths' );
	$parent->set_status( 'publish' );
	$parent->set_catalog_visibility( 'hidden' );
	$attribute = new WC_Product_Attribute();
	$attribute->set_name( 'Strength' );
	$attribute->set_options( array( '5 mg', '10 mg' ) );
	$attribute->set_visible( true );
	$attribute->set_variation( true );
	$parent->set_attributes( array( $attribute ) );
	$products[] = $parent_id = $parent->save();
	$variation_ids = array();
	foreach ( array( '5 mg', '10 mg' ) as $strength ) {
		$variation = new WC_Product_Variation();
		$variation->set_parent_id( $parent_id );
		$variation->set_attributes( array( 'strength' => $strength ) );
		$variation->set_status( 'publish' );
		$variation->set_regular_price( '30' );
		$variation->set_manage_stock( true );
		$variation->set_stock_quantity( 5 );
		$variation->set_backorders( 'no' );
		$products[] = $variation_ids[] = $variation->save();
	}
	$variation_order = wc_create_order();
	$orders[] = $variation_order->get_id();
	$variation_order->add_product( wc_get_product( $variation_ids[0] ), 3 );
	$variation_order->calculate_totals();
	$variation_order->update_status( 'processing' );
	$check( 2 === wc_get_product( $variation_ids[0] )->get_stock_quantity() && 5 === wc_get_product( $variation_ids[1] )->get_stock_quantity(), 'Buying three of one strength leaves the other strength unchanged' );
	$variation_order->update_status( 'cancelled' );
	$check( 5 === wc_get_product( $variation_ids[0] )->get_stock_quantity(), 'Variation stock is restored on cancellation' );

	$pending = wc_create_order();
	$orders[] = $pending->get_id();
	$pending->add_product( wc_get_product( $product_id ), 1 );
	$pending->calculate_totals();
	wc_reserve_stock_for_order( $pending );
	$check( 1 === wc_get_held_stock_quantity( wc_get_product( $product_id ) ), 'Pending checkout reserves one vial without reducing physical stock' );
	$check( 5 === wc_get_product( $product_id )->get_stock_quantity(), 'Pending reservation leaves recorded stock at five' );
	wc_release_stock_for_order( $pending );
	$check( 0 === wc_get_held_stock_quantity( wc_get_product( $product_id ) ), 'Releasing an unpaid order removes its stock reservation' );
	$check( 60 === (int) get_option( 'woocommerce_hold_stock_minutes' ), 'Local Pending payment Hold Stock remains 60 minutes' );

	$expired = wc_create_order();
	$orders[] = $expired->get_id();
	$expired->add_product( wc_get_product( $product_id ), 1 );
	$expired->set_created_via( 'checkout' );
	$expired->save();
	wc_reserve_stock_for_order( $expired );
	$check( 1 === wc_get_held_stock_quantity( wc_get_product( $product_id ) ), 'Expired checkout fixture holds one vial before cleanup' );
	// The installed WooCommerce CPT data store selects unpaid orders by
	// post_modified, not date_created. Age only this disposable row.
	global $wpdb;
	$old_time = gmdate( 'Y-m-d H:i:s', time() - 61 * MINUTE_IN_SECONDS );
	$wpdb->update( $wpdb->posts, array( 'post_modified' => $old_time, 'post_modified_gmt' => $old_time ), array( 'ID' => $expired->get_id() ), array( '%s', '%s' ), array( '%d' ) );
	$check( in_array( $expired->get_id(), array_map( 'intval', WC_Data_Store::load( 'order' )->get_unpaid_orders( time() - 60 * MINUTE_IN_SECONDS ) ), true ), 'Fixture qualifies for the unpaid-order expiry job' );
	$only_fixture_cancellation = static fn( $should_cancel, $candidate ) => $candidate->get_id() === $expired->get_id();
	add_filter( 'woocommerce_cancel_unpaid_order', $only_fixture_cancellation, 999, 2 );
	wc_cancel_unpaid_orders();
	remove_filter( 'woocommerce_cancel_unpaid_order', $only_fixture_cancellation, 999 );
	$only_fixture_cancellation = null;
	$check( 'cancelled' === wc_get_order( $expired->get_id() )->get_status(), 'WooCommerce cancels an aged checkout Pending payment order after 60 minutes' );
	$check( 0 === wc_get_held_stock_quantity( wc_get_product( $product_id ) ), 'Automatic cancellation releases the held vial' );

	require_once ABSPATH . 'wp-admin/includes/user.php';
	$customer_id = wp_insert_user( array(
		'user_login' => 'ff-stage5-' . strtolower( wp_generate_password( 8, false, false ) ),
		'user_pass'  => wp_generate_password( 24 ),
		'user_email' => 'ff-stage5-' . strtolower( wp_generate_password( 8, false, false ) ) . '@example.test',
		'role'       => 'customer',
	) );
	$check( ! is_wp_error( $customer_id ), 'Disposable Foxfire customer account was created' );
	$users[] = $customer_id;
	$coupon = new WC_Coupon();
	$coupon->set_code( 'ff-stage5-' . wp_generate_uuid4() );
	$coupon->set_discount_type( 'percent' );
	$coupon->set_amount( 10 );
	$coupon->set_usage_limit_per_user( 1 );
	$coupons[] = $coupon->save();
	update_post_meta( $coupon->get_id(), '_foxfire_requires_login', 'yes' );
	wp_set_current_user( $customer_id );
	WC()->customer = new WC_Customer( $customer_id, false );
	$check( (bool) WC()->cart->add_to_cart( $product_id, 1 ) && WC()->cart->apply_coupon( $coupon->get_code() ), 'Signed-in customer applies a disposable member coupon' );
	$check( 2.5 === (float) WC()->cart->get_discount_total(), 'Cart shows the expected 10% vial discount' );
	$coupon_order = wc_create_order( array( 'customer_id' => $customer_id ) );
	$orders[] = $coupon_order->get_id();
	$coupon_order->add_product( wc_get_product( $product_id ), 1 );
	$coupon_order->apply_coupon( $coupon->get_code() );
	$coupon_order->calculate_totals();
	$check( 2.5 === (float) $coupon_order->get_discount_total() && 22.5 === (float) $coupon_order->get_total(), 'Order record persists the coupon and discounted total' );
	$coupon_order->update_status( 'processing' );
	$check( 1 === (int) ( new WC_Coupon( $coupon->get_id() ) )->get_usage_count(), 'Processed order records one coupon redemption' );
	WC()->cart->remove_coupon( $coupon->get_code() );
	wc_clear_notices();
	$check( ! WC()->cart->apply_coupon( $coupon->get_code() ), 'Same account cannot redeem that code a second time' );
	wc_clear_notices();
	$coupon_order->update_status( 'cancelled' );
	WC()->cart->empty_cart();

	WP_CLI::success( $checks . ' Stage 5 local inventory and coupon checks passed. Checkout payment remains disabled; target-host email delivery and real checkout purchases remain separate launch checks.' );
} finally {
	if ( $only_fixture_cancellation ) {
		remove_filter( 'woocommerce_cancel_unpaid_order', $only_fixture_cancellation, 999 );
	}
	foreach ( $orders as $order_id ) {
		$order = wc_get_order( $order_id );
		if ( $order ) {
			wc_release_stock_for_order( $order );
			$order->delete( true );
		}
	}
	foreach ( array_reverse( $products ) as $product_id ) {
		$product = wc_get_product( $product_id );
		if ( $product ) {
			$product->delete( true );
		}
	}
	foreach ( $coupons as $coupon_id ) {
		$coupon = new WC_Coupon( $coupon_id );
		if ( $coupon->get_id() ) {
			$coupon->delete( true );
		}
	}
	foreach ( $users as $user_id ) {
		wp_delete_user( $user_id );
	}
	remove_action( 'wp_mail_succeeded', $capture_mail );
	list( WC()->cart, WC()->customer, WC()->session, $user_id ) = $saved;
	wp_set_current_user( $user_id );
	wc_clear_notices();
}
