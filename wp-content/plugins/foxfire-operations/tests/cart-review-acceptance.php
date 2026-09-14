<?php
/** Local-only, disposable-fixture acceptance for Cart review points 1–3. */
defined( 'ABSPATH' ) || exit;
if ( 'local' !== wp_get_environment_type() ) {
	throw new RuntimeException( 'Cart acceptance runs only locally.' );
}
$products = $orders = $coupon_ids = $settings = array();
$zone = null;
$saved = array( WC()->cart, WC()->customer, WC()->session, get_current_user_id() );
$check = static function ( $condition, $label ) {
	if ( ! $condition ) throw new RuntimeException( 'FAIL: ' . $label );
	WP_CLI::log( 'PASS: ' . $label );
};
$email_hooks = array();
foreach ( array( 'new_order', 'cancelled_order', 'failed_order', 'customer_on_hold_order', 'customer_processing_order', 'customer_completed_order', 'customer_shipped_order' ) as $email ) {
	$email_hooks[] = 'woocommerce_email_enabled_' . $email;
	add_filter( end( $email_hooks ), '__return_false', 999 );
}
$configure = static function ( $id, $method, $values ) use ( &$settings ) {
	$key = 'woocommerce_' . $method . '_' . $id . '_settings';
	$settings[] = $key; // Only options belonging to this test's newly created instances.
	update_option( $key, $values );
	WC_Cache_Helper::get_transient_version( 'shipping', true );
};
try {
	$check( 'yes' === get_option( 'woocommerce_manage_stock' ), 'Native stock management is enabled' );
	wp_set_current_user( 0 );
	WC()->session = new WC_Session_Handler(); // No initialization or real browser/customer session.
	WC()->customer = new WC_Customer( 0, false );
	WC()->cart = new WC_Cart();
	WC()->customer->set_is_vat_exempt( true );
	$product = new WC_Product_Simple();
	$product->set_name( 'Temporary cart review fixture' );
	$product->set_status( 'publish' );
	$product->set_catalog_visibility( 'hidden' );
	$product->set_regular_price( '40' );
	$product->set_manage_stock( true );
	$product->set_stock_quantity( 20 );
	$products[] = $id = $product->save();
	update_post_meta( $id, 'foxfire_tier_enable', '0' );
	$key = WC()->cart->add_to_cart( $id, 2 );
	WC()->cart->calculate_totals();
	$check( $key && 2 === WC()->cart->get_cart()[ $key ]['quantity'] && 80.0 === (float) WC()->cart->get_subtotal(), 'Two vials add exactly two and subtotal is 80' );
	WC()->cart->set_quantity( $key, 3 );
	$check( 120.0 === (float) WC()->cart->get_subtotal(), 'Cart quantity change updates the subtotal to 120' );
	$check( 20 === wc_get_product( $id )->get_stock_quantity(), 'Cart changes do not deduct inventory prematurely' );
	$order = wc_create_order();
	$orders[] = $order->get_id();
	$order->add_product( wc_get_product( $id ), 3 );
	$order->calculate_totals();
	$check( 20 === wc_get_product( $id )->get_stock_quantity(), 'Pending unpaid order does not deduct stock' );
	$order->update_status( 'on-hold' );
	$check( 17 === wc_get_product( $id )->get_stock_quantity(), 'Accepted/on-hold order deducts exactly three individual vials' );
	$order->update_status( 'processing' );
	$order->update_status( 'completed' );
	wc_maybe_reduce_stock_levels( $order->get_id() );
	$check( 17 === wc_get_product( $id )->get_stock_quantity(), 'Processing, completion and duplicate events do not double-deduct stock' );
	$order->update_status( 'cancelled' );
	$check( 20 === wc_get_product( $id )->get_stock_quantity(), 'Cancellation restores the deducted quantity once' );
	wc_maybe_increase_stock_levels( $order->get_id() );
	$check( 20 === wc_get_product( $id )->get_stock_quantity(), 'Duplicate restoration cannot increase stock twice' );
	$parent = new WC_Product_Variable();
	$parent->set_name( 'Temporary cart stock pool fixture' );
	$parent->set_status( 'publish' );
	$parent->set_catalog_visibility( 'hidden' );
	$parent->set_manage_stock( true );
	$parent->set_stock_quantity( 20 );
	$products[] = $parent_id = $parent->save();
	$variation = new WC_Product_Variation();
	$variation->set_parent_id( $parent_id );
	$variation->set_regular_price( '40' );
	$products[] = $variation_id = $variation->save();
	$pool_order = wc_create_order();
	$orders[] = $pool_order->get_id();
	$pool_order->add_product( wc_get_product( $variation_id ), 5 );
	$pool_order->calculate_totals();
	$pool_order->update_status( 'processing' );
	$check( 15 === wc_get_product( $parent_id )->get_stock_quantity(), 'Variation using parent inventory deducts five vials from the shared pool' );
	$pool_order->update_status( 'cancelled' );
	$variation = wc_get_product( $variation_id );
	$variation->set_manage_stock( true );
	$variation->set_stock_quantity( 12 );
	$variation->save();
	$strength_order = wc_create_order();
	$orders[] = $strength_order->get_id();
	$strength_order->add_product( wc_get_product( $variation_id ), 2 );
	$strength_order->calculate_totals();
	$strength_order->update_status( 'processing' );
	$check( 10 === wc_get_product( $variation_id )->get_stock_quantity() && 20 === wc_get_product( $parent_id )->get_stock_quantity(), 'Strength-specific inventory deducts only the selected variation' );
	$strength_order->update_status( 'cancelled' );

	$zone = new WC_Shipping_Zone();
	$zone->set_zone_name( 'Temporary cart acceptance ' . wp_generate_uuid4() );
	$zone->set_zone_order( -100 );
	$zone->add_location( 'AQ', 'country' );
	$postcode = 'FF' . strtoupper( wp_generate_password( 10, false, false ) );
	$zone->add_location( $postcode, 'postcode' );
	$zone->save();
	$flat_id = $zone->add_shipping_method( 'flat_rate' );
	$free_id = $zone->add_shipping_method( 'free_shipping' );
	$flat_settings = array( 'title' => 'Test standard shipping', 'cost' => '15', 'tax_status' => 'none' );
	$free_settings = array( 'title' => 'Test free shipping', 'requires' => 'min_amount', 'min_amount' => '100', 'ignore_discounts' => 'no' );
	$configure( $flat_id, 'flat_rate', $flat_settings );
	$configure( $free_id, 'free_shipping', $free_settings );
	WC()->customer->set_shipping_country( 'AQ' );
	WC()->customer->set_shipping_postcode( $postcode );
	$package = foxfire_operations_current_shipping_package();
	$check( $zone->get_id() === WC_Shipping_Zones::get_zone_matching_package( $package )->get_id(), 'Shipping fixtures use their isolated test-only destination' );
	$progress = foxfire_operations_get_free_shipping_progress();
	$check( $progress['qualified'] && 100.0 === $progress['minimum'], 'Saved free-shipping threshold drives the progress display' );
	$check( ( new WC_Shipping_Free_Shipping( $free_id ) )->is_available( $package ), 'Native shipping agrees with the qualified display' );
	WC()->cart->set_quantity( $key, 2 );
	$progress = foxfire_operations_get_free_shipping_progress();
	$check( ! $progress['qualified'] && 20.0 === $progress['remaining'], 'Below-threshold display shows the correct remaining amount' );
	$check( ! ( new WC_Shipping_Free_Shipping( $free_id ) )->is_available( $package ), 'Native shipping rejects free shipping below the threshold' );
	$flat = new WC_Shipping_Flat_Rate( $flat_id );
	$rates = $flat->get_rates_for_package( $package );
	$check( 15.0 === (float) reset( $rates )->get_cost(), 'Native standard shipping uses the saved charge' );
	$flat_settings['cost'] = '18';
	$configure( $flat_id, 'flat_rate', $flat_settings );
	$rates = ( new WC_Shipping_Flat_Rate( $flat_id ) )->get_rates_for_package( $package );
	$check( 18.0 === (float) reset( $rates )->get_cost(), 'Changing the saved charge changes the actual shipping rate' );
	$free_settings['min_amount'] = '150';
	$configure( $free_id, 'free_shipping', $free_settings );
	$check( 70.0 === foxfire_operations_get_free_shipping_progress()['remaining'], 'Changing the saved threshold updates the progress display' );

	$coupon = new WC_Coupon();
	$coupon->set_code( 'ff-cart-review-' . wp_generate_uuid4() );
	$coupon->set_discount_type( 'percent' );
	$coupon->set_amount( 20 );
	$coupon->set_date_expires( time() + DAY_IN_SECONDS );
	$coupon->set_product_ids( array( $id ) );
	$coupon->set_usage_limit( 5 );
	$coupon_ids[] = $coupon->save();
	$read = new WC_Coupon( $coupon->get_id() );
	$check( 20.0 === (float) $read->get_amount() && 5 === $read->get_usage_limit() && $read->get_date_expires()->getTimestamp() > time(), 'Native coupon amount, limits and expiration persist' );
	$check( WC()->cart->apply_coupon( $coupon->get_code() ), 'Saved valid coupon can be applied through the real cart' );
	WC()->cart->calculate_totals();
	$check( 16.0 === (float) WC()->cart->get_discount_total() && 64.0 === (float) WC()->cart->get_cart_contents_total(), 'Coupon updates cart discount and order amounts correctly' );
	$check( 86.0 === foxfire_operations_get_free_shipping_progress()['remaining'], 'Free-shipping progress honors the saved discount rule' );
	$free_settings['ignore_discounts'] = 'yes';
	$configure( $free_id, 'free_shipping', $free_settings );
	$check( 70.0 === foxfire_operations_get_free_shipping_progress()['remaining'], 'Admin ignore-discounts setting is respected' );
	WC()->cart->remove_coupon( $coupon->get_code() );
	$coupon->set_date_expires( time() - HOUR_IN_SECONDS );
	$coupon->save();
	$check( is_wp_error( ( new WC_Discounts( WC()->cart ) )->is_coupon_valid( new WC_Coupon( $coupon->get_id() ) ) ), 'Expired coupons are rejected by native WooCommerce validation' );

	// Render real recommendations from the existing catalog without adding them to a cart.
	$recommendations = array();
	foreach ( wc_get_products( array( 'status' => 'publish', 'limit' => -1 ) ) as $candidate ) {
		if ( ! in_array( $candidate->get_id(), $products, true ) && $candidate->is_visible() ) $recommendations[] = $candidate->get_id();
		if ( 3 === count( $recommendations ) ) break;
	}
	$check( 3 === count( $recommendations ), 'Existing catalog provides three visible recommendation targets' );
	WC()->cart->cart_contents[ $key ]['data']->set_cross_sell_ids( $recommendations );
	wc_setup_loop( array( 'name' => 'cross-sells' ) );
	ob_start();
	woocommerce_cross_sell_display( 3, 3 );
	$html = ob_get_clean();
	$check( false !== strpos( $html, 'You May Be Interested In' ), 'Existing recommended-products section is retained' );
	$check( 3 === substr_count( $html, 'ff-product-card__view-product' ) && false === strpos( $html, 'add_to_cart_button' ), 'All three recommendation buttons are View Product, not cart actions' );
	$check( false === strpos( $html, '?add-to-cart=' ) && false === stripos( $html, 'Recovery &amp; Healing' ) && false === stripos( $html, 'GLP-1 Agonists' ), 'Recommendation links do not add items and legacy category labels are absent' );
	wc_reset_loop();
	wc_setup_loop( array( 'name' => 'related' ) );
	ob_start();
	wc_get_template( 'single-product/related.php', array( 'related_products' => array_map( 'wc_get_product', $recommendations ) ) );
	$related_html = ob_get_clean();
	$check( false !== strpos( $related_html, 'You May Also Like' ) && 3 === substr_count( $related_html, 'ff-product-card__view-product' ) && false === strpos( $related_html, 'add_to_cart_button' ), 'Single-product recommendations also use View Product links' );
	wc_reset_loop();
	WP_CLI::success( 'Cart review points 1–3 passed; no payments were enabled.' );
} finally {
	foreach ( $orders as $order_id ) { $fixture = wc_get_order( $order_id ); if ( $fixture ) $fixture->delete( true ); }
	foreach ( $coupon_ids as $coupon_id ) { $fixture = new WC_Coupon( $coupon_id ); $fixture->delete( true ); }
	if ( $zone ) $zone->delete();
	foreach ( array_unique( $settings ) as $option ) delete_option( $option );
	foreach ( array_reverse( $products ) as $product_id ) { $fixture = wc_get_product( $product_id ); if ( $fixture ) $fixture->delete( true ); }
	foreach ( $email_hooks as $hook ) remove_filter( $hook, '__return_false', 999 );
	list( WC()->cart, WC()->customer, WC()->session, $user_id ) = $saved;
	wp_set_current_user( $user_id );
	WC_Cache_Helper::get_transient_version( 'shipping', true );
}
