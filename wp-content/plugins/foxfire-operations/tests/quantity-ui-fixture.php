<?php
/** Explicit local-only, hidden generic UI fixtures; run again with `cleanup` after review. */
defined( 'ABSPATH' ) || exit;
if ( 'local' !== wp_get_environment_type() ) throw new RuntimeException( 'Local UI fixtures only.' );
$key = 'foxfire_quantity_ui_fixtures';
$existing = get_option( $key, array() );
if ( 'stock' === ( $args[0] ?? '' ) && $existing ) {
	$p = wc_get_product( $existing[1] );
	if ( ! $p || 'quantity-ui-test' !== $p->get_meta( '_foxfire_test_fixture' ) ) throw new RuntimeException( 'Expected temporary fixture only.' );
	$p->update_meta_data( '_foxfire_quantity_options', array( 10 => array( 'mode' => 'discount', 'value' => '10' ), 20 => array( 'mode' => 'discount', 'value' => '20' ) ) );
	$p->save();
	wc_delete_product_transients( $p->get_id() );
	WP_CLI::success( 'Temporary fixture has 10/20 quantity options for stock-limit checks.' );
	return;
}
if ( 'cleanup' === ( $args[0] ?? '' ) ) {
	foreach ( array_reverse( $existing ) as $id ) {
		$p = wc_get_product( $id );
		if ( $p && 'quantity-ui-test' === $p->get_meta( '_foxfire_test_fixture' ) ) $p->delete( true );
	}
	delete_option( $key );
	WP_CLI::success( 'Temporary quantity UI products removed.' );
	return;
}
if ( $existing ) throw new RuntimeException( 'Clean up the previous quantity fixtures first.' );
$simple = new WC_Product_Simple();
$simple->set_name( 'Quantity UI Test Item' );
$simple->set_status( 'publish' );
$simple->set_catalog_visibility( 'hidden' );
$simple->set_regular_price( '50' );
$simple->set_sale_price( '40' );
$simple->set_manage_stock( true );
$simple->set_stock_quantity( 20 );
$simple->update_meta_data( '_foxfire_test_fixture', 'quantity-ui-test' );
$ids = array( $simple->save() );
update_option( $key, $ids, false );
$variable = new WC_Product_Variable();
$variable->set_name( 'Quantity UI Strength Test Item' );
$variable->set_status( 'publish' );
$variable->set_catalog_visibility( 'hidden' );
$attribute = new WC_Product_Attribute();
$attribute->set_name( 'Strength' );
$attribute->set_options( array( 'Low', 'High' ) );
$attribute->set_visible( true );
$attribute->set_variation( true );
$variable->set_attributes( array( $attribute ) );
$variable->update_meta_data( '_foxfire_test_fixture', 'quantity-ui-test' );
$rules = foxfire_operations_validate_quantity_options( array(
	array( 'quantity' => 1, 'mode' => 'discount', 'value' => '0' ),
	array( 'quantity' => 2, 'mode' => 'discount', 'value' => '5' ),
	array( 'quantity' => 5, 'mode' => 'discount', 'value' => '10' ),
	array( 'quantity' => 10, 'mode' => 'discount', 'value' => '20' ),
) );
$variable->update_meta_data( '_foxfire_quantity_options', $rules );
$ids[] = $parent = $variable->save();
update_option( $key, $ids, false );
foreach ( array( 'Low' => 40, 'High' => 80 ) as $strength => $price ) {
	$v = new WC_Product_Variation();
	$v->set_parent_id( $parent );
	$v->set_attributes( array( 'strength' => $strength ) );
	$v->set_regular_price( $price );
	$v->set_manage_stock( true );
	$v->set_stock_quantity( 'High' === $strength ? 7 : 20 );
	$v->update_meta_data( '_foxfire_test_fixture', 'quantity-ui-test' );
	if ( 'High' === $strength ) $v->update_meta_data( '_foxfire_quantity_options', array( 5 => array( 'mode' => 'total', 'value' => '375' ) ) );
	$ids[] = $v->save();
	update_option( $key, $ids, false );
}
WC_Product_Variable::sync( $parent );
WP_CLI::log( wp_json_encode( array( 'simple_id' => $simple->get_id(), 'simple_url' => get_permalink( $simple->get_id() ), 'variable_id' => $parent, 'variable_url' => get_permalink( $parent ) ) ) );
