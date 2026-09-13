<?php
/** Local, self-cleaning quantity configuration and real WooCommerce cart acceptance tests. */
defined( 'ABSPATH' ) || exit;
if ( 'local' !== wp_get_environment_type() ) {
	throw new RuntimeException( 'Quantity acceptance tests run only locally.' );
}
if ( ! class_exists( 'WC_Admin_Meta_Boxes' ) ) {
	require_once WC_ABSPATH . 'includes/admin/class-wc-admin-meta-boxes.php';
}
$ids = array();
$user = get_current_user_id();
$post = $_POST;
$cart = WC()->cart;
$customer = WC()->customer;
$session = WC()->session;
$admin_errors = WC_Admin_Meta_Boxes::$meta_box_errors;
$check = static function ( $condition, $label ) {
	if ( ! $condition ) throw new RuntimeException( 'FAIL: ' . $label );
	WP_CLI::log( 'PASS: ' . $label );
};
try {
	$manager = get_users( array( 'role' => 'shop_manager', 'number' => 1 ) );
	if ( ! $manager ) $manager = get_users( array( 'role' => 'administrator', 'number' => 1 ) );
	wp_set_current_user( $manager[0]->ID );
	$product = new WC_Product_Simple();
	$product->set_name( 'Temporary quantity acceptance fixture' );
	$product->set_status( 'publish' );
	$product->set_catalog_visibility( 'hidden' );
	$product->set_regular_price( '100' );
	$product->set_sale_price( '80' );
	$product->set_manage_stock( true );
	$product->set_stock_quantity( 20 );
	$ids[] = $id = $product->save();
	update_post_meta( $id, 'foxfire_tier_3_discount', '5' );
	update_post_meta( $id, 'foxfire_tier_5_discount', '10' );
	$legacy = foxfire_operations_get_quantity_options( $id );
	$check( array( 1, 3, 5 ) === array_keys( $legacy ), 'Unedited products keep legacy quantity choices' );
	$check( abs( foxfire_operations_quantity_unit_price( 80, 3, $legacy ) - 76 ) < 0.001, 'Legacy sale-price discount is preserved' );
	$rows = array(
		array( 'quantity' => 10, 'mode' => 'total', 'value' => '650' ),
		array( 'quantity' => 1, 'mode' => 'discount', 'value' => '0' ),
		array( 'quantity' => 2, 'mode' => 'discount', 'value' => '5' ),
		array( 'quantity' => 5, 'mode' => 'total', 'value' => '349.99' ),
	);
	$_POST = array( '_foxfire_quantity_nonce' => wp_create_nonce( 'foxfire_quantity_save' ), 'foxfire_quantity_enabled' => '1', 'foxfire_quantity_rows' => $rows );
	foxfire_operations_save_quantity_panel( $product );
	$product->save();
	$options = foxfire_operations_get_quantity_options( $id );
	$check( array( 1, 2, 5, 10 ) === array_keys( $options ), 'Authorized admin save persists configurable sorted quantities' );
	$check( abs( foxfire_operations_quantity_unit_price( 80, 2, $options ) - 76 ) < 0.001, 'Custom discount uses the active sale price' );
	$check( abs( foxfire_operations_quantity_unit_price( 80, 5, $options ) * 5 - 349.99 ) < 0.001, 'Custom total price is divided into individual-vial pricing without losing cents' );
	$check( abs( foxfire_operations_quantity_unit_price( 80, 7, $options ) * 7 - 489.986 ) < 0.001, 'Cart quantities between presets use the highest qualifying row' );
	foreach ( array(
		array(),
		array_merge( $rows, array( $rows[0] ) ),
		array( array( 'quantity' => '1.5', 'mode' => 'discount', 'value' => '5' ) ),
		array( array( 'quantity' => 0, 'mode' => 'discount', 'value' => '5' ) ),
		array( array( 'quantity' => 1, 'mode' => 'discount', 'value' => '101' ) ),
		array( array( 'quantity' => 1, 'mode' => 'total', 'value' => '-1' ) ),
		array( array( 'quantity' => 1, 'mode' => 'total', 'value' => '25oops' ) ),
		array( array( 'quantity' => 1, 'mode' => 'evil', 'value' => '5' ) ),
	) as $invalid ) {
		$check( is_wp_error( foxfire_operations_validate_quantity_options( $invalid ) ), 'Invalid quantity/price configuration is rejected' );
	}
	$_POST['foxfire_quantity_rows'] = array_merge( $rows, array( $rows[0] ) );
	foxfire_operations_save_quantity_panel( $product );
	$product->save();
	$check( $options === foxfire_operations_get_quantity_options( $id ), 'Invalid admin saves preserve the previous configuration' );
	$_POST['foxfire_quantity_rows'] = array( array( 'quantity' => 9, 'mode' => 'total', 'value' => 0 ) );
	$_POST['_foxfire_quantity_nonce'] = 'invalid';
	foxfire_operations_save_quantity_panel( $product );
	$product->save();
	$check( $options === foxfire_operations_get_quantity_options( $id ), 'Invalid nonce cannot change pricing' );
	wp_set_current_user( 0 );
	$_POST['_foxfire_quantity_nonce'] = wp_create_nonce( 'foxfire_quantity_save' );
	foxfire_operations_save_quantity_panel( $product );
	$product->save();
	$check( $options === foxfire_operations_get_quantity_options( $id ), 'Unauthorized users cannot change quantity settings' );
	wp_set_current_user( $manager[0]->ID );
	$variable = new WC_Product_Variable();
	$variable->set_name( 'Temporary strength acceptance fixture' );
	$variable->set_status( 'publish' );
	$variable->set_catalog_visibility( 'hidden' );
	$attribute = new WC_Product_Attribute();
	$attribute->set_name( 'Strength' );
	$attribute->set_options( array( 'Low', 'High' ) );
	$attribute->set_visible( true );
	$attribute->set_variation( true );
	$variable->set_attributes( array( $attribute ) );
	$variable->update_meta_data( '_foxfire_quantity_options', $options );
	$ids[] = $parent_id = $variable->save();
	$variations = array();
	foreach ( array( 'Low' => 40, 'High' => 80 ) as $strength => $price ) {
		$v = new WC_Product_Variation();
		$v->set_parent_id( $parent_id );
		$v->set_attributes( array( 'strength' => $strength ) );
		$v->set_regular_price( $price );
		$v->set_manage_stock( true );
		$v->set_stock_quantity( 12 );
		$ids[] = $v->save();
		$variations[] = $v;
	}
	WC_Product_Variable::sync( $parent_id );
	$v = $variations[1];
	$_POST = array( 'foxfire_quantity_variation_nonce' => array( wp_create_nonce( 'foxfire_quantity_variation_' . $v->get_id() ) ), 'foxfire_quantity_variation' => array( array( array( 'quantity' => 5, 'mode' => 'total', 'value' => '375' ) ) ) );
	foxfire_operations_save_variation_quantity( $v->get_id(), 0 );
	$rules = foxfire_operations_get_quantity_options( $parent_id, $v->get_id() );
	$check( abs( foxfire_operations_quantity_unit_price( 80, 5, $rules ) * 5 - 375 ) < 0.001, 'Authorized variation save persists strength-specific total prices' );
	$check( 349.99 === (float) foxfire_operations_get_quantity_options( $parent_id, $variations[0]->get_id() )[5]['value'], 'Other strengths retain their parent rules' );
	$data = $variable->get_available_variation( $v );
	$check( abs( $data['foxfire_quantity_prices'][5]['total'] - wc_get_price_to_display( $v, array( 'price' => 75, 'qty' => 5 ) ) ) < 0.001, 'Variation payload provides tax-aware server prices for every preset' );
	WC()->session = new WC_Session_Handler();
	WC()->customer = new WC_Customer( 0, false );
	WC()->cart = new WC_Cart();
	wp_set_current_user( 0 );
	$key = WC()->cart->add_to_cart( $id, 2 );
	$check( (bool) $key && 2 === WC()->cart->get_cart()[ $key ]['quantity'], 'Actual WooCommerce add-to-cart adds exactly the selected two vials' );
	WC()->cart->calculate_totals();
	$check( abs( WC()->cart->get_cart()[ $key ]['data']->get_price() - 76 ) < 0.001, 'Actual cart totals use the saved discount rather than submitted prices' );
	WC()->cart->set_quantity( $key, 5 );
	WC()->cart->calculate_totals();
	$check( abs( WC()->cart->get_cart()[ $key ]['line_subtotal'] - 349.99 ) < 0.011, 'Actual cart line subtotal matches the configured five-vial total' );
	WC()->cart->calculate_totals();
	$check( abs( WC()->cart->get_cart()[ $key ]['line_subtotal'] - 349.99 ) < 0.011, 'Repeated calculations do not compound discounts' );
	$variation_key = WC()->cart->add_to_cart( $parent_id, 5, $v->get_id(), $v->get_variation_attributes() );
	WC()->cart->calculate_totals();
	$check( (bool) $variation_key && abs( WC()->cart->get_cart()[ $variation_key ]['line_subtotal'] - 375 ) < 0.011, 'Actual variation cart line uses its strength-specific override' );
	$check( false === WC()->cart->add_to_cart( $id, 30 ), 'Stock validation rejects quantities exceeding individual-vial stock' );
	$check( 20 === wc_get_product( $id )->get_stock_quantity(), 'Cart operations do not prematurely deduct stock' );
	update_post_meta( $id, 'foxfire_tier_enable', '0' );
	WC()->cart->calculate_totals();
	$check( abs( WC()->cart->get_cart()[ $key ]['data']->get_price() - 80 ) < 0.001, 'Disabling quantity pricing restores the active base price' );
	WP_CLI::success( 'Quantity configuration, security, strengths, pricing and cart checks passed.' );
} finally {
	foreach ( array_reverse( $ids ) as $fixture_id ) {
		$fixture = wc_get_product( $fixture_id );
		if ( $fixture ) $fixture->delete( true );
	}
	$_POST = $post;
	wp_set_current_user( $user );
	WC()->cart = $cart;
	WC()->customer = $customer;
	WC()->session = $session;
	WC_Admin_Meta_Boxes::$meta_box_errors = $admin_errors;
}
