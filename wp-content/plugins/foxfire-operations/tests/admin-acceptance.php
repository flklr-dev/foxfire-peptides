<?php
/**
 * Local, destructive-but-self-cleaning acceptance test for Foxfire Operations.
 *
 * Run with:
 * docker compose run --rm wpcli eval-file wp-content/plugins/foxfire-operations/tests/admin-acceptance.php
 *
 * @package Foxfire_Operations
 */

defined( 'ABSPATH' ) || exit;

if ( ! in_array( wp_get_environment_type(), array( 'local', 'development' ), true ) ) {
	throw new RuntimeException( 'Admin acceptance tests may run only in a local or development environment.' );
}

if ( ! class_exists( 'WooCommerce' ) || ! function_exists( 'foxfire_operations_sync_role_capabilities' ) ) {
	throw new RuntimeException( 'WooCommerce and Foxfire Operations must both be active.' );
}

require_once ABSPATH . 'wp-admin/includes/user.php';

$foxfire_results       = array();
$foxfire_cleanup       = array(
	'orders'   => array(),
	'products' => array(),
	'coupons'  => array(),
	'posts'    => array(),
	'users'    => array(),
	'auth_buckets' => array(),
);
$foxfire_original_user = get_current_user_id();
$foxfire_original_post = $_POST;
$foxfire_original_get  = $_GET;
$foxfire_original_awaiting_payment = WC()->session ? WC()->session->get( 'order_awaiting_payment', null ) : null;
$foxfire_original_recent_checkout  = WC()->session ? WC()->session->get( 'foxfire_recent_checkout_order', null ) : null;
$foxfire_original_homepage_products_exists = false !== get_option( FOXFIRE_OPERATIONS_HOMEPAGE_PRODUCTS_OPTION, false );
$foxfire_original_homepage_products        = get_option( FOXFIRE_OPERATIONS_HOMEPAGE_PRODUCTS_OPTION, array() );
$foxfire_original_homepage_audit_exists    = false !== get_option( FOXFIRE_OPERATIONS_HOMEPAGE_PRODUCTS_AUDIT_OPTION, false );
$foxfire_original_homepage_audit           = get_option( FOXFIRE_OPERATIONS_HOMEPAGE_PRODUCTS_AUDIT_OPTION, array() );

$foxfire_check = static function ( bool $condition, string $label ) use ( &$foxfire_results ): void {
	$foxfire_results[] = array( 'passed' => $condition, 'label' => $label );
	if ( ! $condition ) {
		throw new RuntimeException( $label );
	}
};

try {
	foxfire_operations_sync_role_capabilities();

	$manager_role = get_role( 'shop_manager' );
	$admin_role   = get_role( 'administrator' );
	$foxfire_check( $manager_role instanceof WP_Role, 'Shop Manager role exists' );
	$foxfire_check( $admin_role instanceof WP_Role, 'Administrator role exists' );
	$foxfire_check( $manager_role->has_cap( FOXFIRE_OPERATIONS_CAPABILITY ), 'Shop Manager has routine operations capability' );
	$foxfire_check( ! $manager_role->has_cap( FOXFIRE_OPERATIONS_SENSITIVE_CAPABILITY ), 'Shop Manager lacks sensitive-settings capability' );
	$foxfire_check( $admin_role->has_cap( FOXFIRE_OPERATIONS_CAPABILITY ) && $admin_role->has_cap( FOXFIRE_OPERATIONS_SENSITIVE_CAPABILITY ), 'Administrator retains both Foxfire capabilities' );

	foreach ( foxfire_operations_forbidden_manager_capabilities() as $forbidden_capability ) {
		$foxfire_check( ! $manager_role->has_cap( $forbidden_capability ), 'Shop Manager role denies ' . $forbidden_capability );
	}

	$run_id = strtolower( wp_generate_password( 10, false, false ) );
	$manager_id = wp_insert_user(
		array(
			'user_login'   => 'ff_accept_manager_' . $run_id,
			'user_pass'    => wp_generate_password( 48, true, true ),
			'user_email'   => 'manager-' . $run_id . '@example.test',
			'display_name' => 'Foxfire Acceptance Manager',
			'role'         => 'shop_manager',
		)
	);
	if ( is_wp_error( $manager_id ) ) {
		throw new RuntimeException( $manager_id->get_error_message() );
	}
	$foxfire_cleanup['users'][] = $manager_id;

	$customer_id = wp_insert_user(
		array(
			'user_login'   => 'ff_accept_customer_' . $run_id,
			'user_pass'    => wp_generate_password( 48, true, true ),
			'user_email'   => 'customer-' . $run_id . '@example.test',
			'display_name' => 'Foxfire Acceptance Customer',
			'role'         => 'customer',
		)
	);
	if ( is_wp_error( $customer_id ) ) {
		throw new RuntimeException( $customer_id->get_error_message() );
	}
	$foxfire_cleanup['users'][] = $customer_id;

	$foxfire_check(
		false !== has_filter( 'authenticate', 'foxfire_operations_throttle_login' )
			&& false !== has_filter( 'authenticate', 'foxfire_operations_normalize_login_error' )
			&& false !== has_action( 'wp_loaded', 'foxfire_operations_process_customer_lost_password' ),
		'Customer authentication hardening hooks are active'
	);
	$raw_identity = 'Sensitive.Customer+' . $run_id . '@example.test';
	$bucket_key   = foxfire_operations_auth_bucket_key( 'login_identity', $raw_identity );
	$foxfire_check( ! str_contains( strtolower( $bucket_key ), strtolower( $raw_identity ) ) && 48 === strlen( $bucket_key ), 'Authentication buckets do not expose raw identities' );

	$limited_identity = 'rate-' . $run_id . '@example.test';
	$foxfire_cleanup['auth_buckets'][] = array( 'login_identity', $limited_identity );
	$login_limit = foxfire_operations_auth_rate_limit_policy()['login_identity']['limit'];
	for ( $attempt = 0; $attempt < $login_limit; ++$attempt ) {
		foxfire_operations_record_auth_attempt( 'login_identity', $limited_identity );
	}
	$foxfire_check( foxfire_operations_auth_bucket_is_limited( 'login_identity', $limited_identity ), 'Repeated login failures trigger application throttling' );
	foxfire_operations_clear_auth_bucket( 'login_identity', $limited_identity );
	$foxfire_check( ! foxfire_operations_auth_bucket_is_limited( 'login_identity', $limited_identity ), 'Successful authentication can clear the identity throttle' );

	$unknown_error  = foxfire_operations_normalize_login_error( new WP_Error( 'invalid_username', 'Account missing' ), '', '' );
	$password_error = foxfire_operations_normalize_login_error( new WP_Error( 'incorrect_password', 'Wrong password' ), '', '' );
	$foxfire_check(
		$unknown_error->get_error_code() === $password_error->get_error_code()
			&& $unknown_error->get_error_message() === $password_error->get_error_message(),
		'Invalid account and password failures use the same neutral response'
	);
	$foxfire_check( foxfire_operations_find_reset_user( 'customer-' . $run_id . '@example.test' ) instanceof WP_User, 'Password recovery resolves an eligible customer without exposing it publicly' );
	$foxfire_check( HOUR_IN_SECONDS === (int) apply_filters( 'password_reset_expiration', DAY_IN_SECONDS ), 'Password reset links expire after one hour' );
	$foxfire_check( 12 * HOUR_IN_SECONDS === (int) apply_filters( 'auth_cookie_expiration', 2 * DAY_IN_SECONDS, $manager_id, false ), 'Non-remembered operator sessions are capped at twelve hours' );
	$foxfire_check( 2 * DAY_IN_SECONDS === (int) apply_filters( 'auth_cookie_expiration', 14 * DAY_IN_SECONDS, $manager_id, true ), 'Remembered operator sessions are capped at two days' );
	$foxfire_check( 14 * DAY_IN_SECONDS === (int) apply_filters( 'auth_cookie_expiration', 14 * DAY_IN_SECONDS, $customer_id, true ), 'Remembered customer sessions retain the fourteen-day WordPress policy' );

	wp_set_current_user( $manager_id );
	$manager = wp_get_current_user();
	$foxfire_check( current_user_can( FOXFIRE_OPERATIONS_CAPABILITY ), 'Shop Manager can open Foxfire Operations' );
	$foxfire_check( current_user_can( 'edit_products' ) && current_user_can( 'edit_shop_orders' ) && current_user_can( 'edit_shop_coupons' ), 'Shop Manager retains products, orders, and coupons access' );
	$foxfire_check( current_user_can( 'upload_files' ), 'Shop Manager retains media upload access for products and COAs' );
	$foxfire_check( ! current_user_can( 'manage_options' ) && ! current_user_can( 'list_users' ) && ! current_user_can( 'activate_plugins' ), 'Shop Manager cannot manage settings, users, or plugins' );
	$foxfire_check( false === apply_filters( 'wp_is_application_passwords_available_for_user', true, $manager ), 'Shop Manager application passwords are disabled' );

	$manager->add_cap( 'manage_options' );
	$foxfire_check( ! current_user_can( 'manage_options' ), 'Runtime boundary defeats accidental capability drift' );

	$_GET = array( 'page' => 'wc-settings' );
	$foxfire_check( foxfire_operations_is_sensitive_admin_request(), 'WooCommerce settings direct request is classified sensitive' );
	$_GET = array( 'page' => 'wc-admin', 'path' => '/payments/overview' );
	$foxfire_check( foxfire_operations_is_sensitive_admin_request(), 'WooCommerce Payments SPA path is classified sensitive' );
	$_GET = array( 'page' => 'wc-admin', 'path' => '/analytics/overview' );
	$foxfire_check( ! foxfire_operations_is_sensitive_admin_request(), 'WooCommerce Analytics remains routine operational access' );
	$foxfire_check( foxfire_operations_is_sensitive_rest_route( '/wc/v3/settings/general' ), 'Sensitive WooCommerce REST settings route is recognized' );
	$foxfire_check( ! foxfire_operations_is_sensitive_rest_route( '/wc/v3/products' ), 'Routine WooCommerce REST product route is not overblocked' );
	$rest_result = foxfire_operations_protect_sensitive_rest( null, array(), new WP_REST_Request( 'GET', '/wc/v3/settings/general' ) );
	$foxfire_check( is_wp_error( $rest_result ) && 403 === (int) $rest_result->get_error_data()['status'], 'Sensitive REST request is denied with HTTP 403' );

	$manager_links = wp_list_pluck( foxfire_operations_get_admin_links(), 'label' );
	$foxfire_check( in_array( 'Products & Inventory', $manager_links, true ) && in_array( 'Homepage Products', $manager_links, true ) && in_array( 'Site Content', $manager_links, true ), 'Shop Manager hub exposes routine workflows' );
	$foxfire_check( ! in_array( 'Store Configuration', $manager_links, true ) && ! in_array( 'Shipping Settings', $manager_links, true ) && ! in_array( 'Payment Settings', $manager_links, true ), 'Shop Manager hub hides sensitive workflows' );
	ob_start();
	foxfire_operations_render_homepage_products_page();
	$homepage_admin_html = ob_get_clean();
	$foxfire_check(
		str_contains( $homepage_admin_html, 'foxfire_save_homepage_products' )
			&& FOXFIRE_OPERATIONS_HOMEPAGE_PRODUCTS_LIMIT === substr_count( $homepage_admin_html, 'name="foxfire_homepage_product_ids[]"' ),
		'Homepage product editor renders its protected form and eight ordered positions'
	);

	$ordinary_page_id = wp_insert_post(
		array(
			'post_title'  => 'Foxfire Acceptance Unapproved Page ' . $run_id,
			'post_type'   => 'page',
			'post_status' => 'draft',
		)
	);
	$policy_page_id = wp_insert_post(
		array(
			'post_title'  => 'Foxfire Acceptance Policy ' . $run_id,
			'post_type'   => 'page',
			'post_status' => 'draft',
		)
	);
	if ( ! is_int( $ordinary_page_id ) || ! is_int( $policy_page_id ) || $ordinary_page_id <= 0 || $policy_page_id <= 0 ) {
		throw new RuntimeException( 'Could not create temporary page records.' );
	}
	$foxfire_cleanup['posts'][] = $ordinary_page_id;
	$foxfire_cleanup['posts'][] = $policy_page_id;
	update_post_meta( $policy_page_id, '_wp_page_template', 'page-shipping-policy.php' );
	$foxfire_check( ! current_user_can( 'edit_post', $ordinary_page_id ), 'Shop Manager cannot edit an unapproved WordPress page' );
	$foxfire_check( current_user_can( 'edit_post', $policy_page_id ), 'Shop Manager can edit an approved Foxfire policy page' );
	$foxfire_check( ! current_user_can( 'delete_post', $policy_page_id ), 'Shop Manager cannot delete an approved policy page' );
	$foxfire_check( foxfire_operations_can_manage_public_content(), 'Shop Manager can use the scoped Site Content editor' );

	$product = new WC_Product_Simple();
	$product->set_name( 'Foxfire Acceptance Simple ' . $run_id );
	$product->set_status( 'draft' );
	$product->set_sku( 'FF-ACCEPT-S-' . strtoupper( $run_id ) );
	$product->set_regular_price( '100' );
	$product->set_sale_price( '80' );
	$product->set_manage_stock( true );
	$product->set_stock_quantity( 20 );
	$product_id = $product->save();
	$foxfire_cleanup['products'][] = $product_id;
	update_post_meta( $product_id, 'foxfire_batch_lot', 'LOT-' . strtoupper( $run_id ) );
	update_post_meta( $product_id, 'foxfire_testing_summary', 'pending' );
	update_post_meta( $product_id, 'foxfire_tier_enable', '1' );
	update_post_meta( $product_id, 'foxfire_tier_3_discount', '5' );
	update_post_meta( $product_id, 'foxfire_tier_5_discount', '10' );
	$foxfire_check( current_user_can( 'edit_post', $product_id ), 'Shop Manager can edit WooCommerce products despite page restrictions' );
	$foxfire_check( 76.0 === foxfire_operations_calculate_tier_unit_price( 80, 3, array( 3 => 5, 5 => 10 ) ), 'Three-unit pricing uses the active sale price' );
	$foxfire_check( 72.0 === foxfire_operations_calculate_tier_unit_price( 80, 5, array( 3 => 5, 5 => 10 ) ), 'Five-unit pricing applies the highest qualifying tier' );
	$foxfire_check( '' === foxfire_operations_sanitize_coa_url( 'javascript:alert(1)' ), 'Unsafe COA URL is rejected' );
	$foxfire_check( 50.0 === (float) foxfire_operations_sanitize_tier_discount( 99 ), 'Tier discounts are capped at fifty percent' );

	$variable = new WC_Product_Variable();
	$variable->set_name( 'Foxfire Acceptance Variable ' . $run_id );
	$variable->set_status( 'draft' );
	$attribute = new WC_Product_Attribute();
	$attribute->set_name( 'Strength' );
	$attribute->set_options( array( '10 mg', '20 mg' ) );
	$attribute->set_visible( true );
	$attribute->set_variation( true );
	$variable->set_attributes( array( $attribute ) );
	$variable_id = $variable->save();
	$foxfire_cleanup['products'][] = $variable_id;
	foreach ( array( '10 mg' => 45, '20 mg' => 70 ) as $strength => $price ) {
		$variation = new WC_Product_Variation();
		$variation->set_parent_id( $variable_id );
		$variation->set_attributes( array( 'strength' => $strength ) );
		$variation->set_regular_price( (string) $price );
		$variation->set_manage_stock( true );
		$variation->set_stock_quantity( 10 );
		$variation_id = $variation->save();
		$foxfire_cleanup['products'][] = $variation_id;
	}
	$foxfire_check( 2 === count( wc_get_products( array( 'type' => 'variation', 'parent' => $variable_id, 'limit' => -1 ) ) ), 'Variable product retains independently priced and stocked variations' );

	$homepage_first = new WC_Product_Simple();
	$homepage_first->set_name( 'Foxfire Acceptance Homepage First ' . $run_id );
	$homepage_first->set_status( 'publish' );
	$homepage_first->set_regular_price( '60' );
	$homepage_first->set_stock_status( 'instock' );
	$homepage_first_id = $homepage_first->save();
	$foxfire_cleanup['products'][] = $homepage_first_id;

	$homepage_second = new WC_Product_Simple();
	$homepage_second->set_name( 'Foxfire Acceptance Homepage Second ' . $run_id );
	$homepage_second->set_status( 'publish' );
	$homepage_second->set_regular_price( '70' );
	$homepage_second->set_stock_status( 'instock' );
	$homepage_second_id = $homepage_second->save();
	$foxfire_cleanup['products'][] = $homepage_second_id;

	$homepage_validation = foxfire_operations_validate_homepage_product_ids( array( (string) $homepage_second_id, (string) $homepage_first_id, '' ) );
	$foxfire_check( ! is_wp_error( $homepage_validation ) && array( $homepage_second_id, $homepage_first_id ) === $homepage_validation, 'Homepage product validation preserves the submitted priority order' );
	$foxfire_check( is_wp_error( foxfire_operations_validate_homepage_product_ids( array( $homepage_first_id, $homepage_first_id ) ) ), 'Homepage product validation rejects duplicates' );
	$foxfire_check( is_wp_error( foxfire_operations_validate_homepage_product_ids( range( 1, FOXFIRE_OPERATIONS_HOMEPAGE_PRODUCTS_LIMIT + 1 ) ) ), 'Homepage product validation enforces the eight-product limit' );
	$foxfire_check( is_wp_error( foxfire_operations_validate_homepage_product_ids( array( $product_id ) ) ), 'Homepage product validation rejects unpublished products' );

	update_option( FOXFIRE_OPERATIONS_HOMEPAGE_PRODUCTS_OPTION, $homepage_validation, false );
	$foxfire_check( array( $homepage_second_id, $homepage_first_id ) === foxfire_operations_get_homepage_product_ids( false ), 'Saved homepage priority order is retrieved without reordering' );
	$foxfire_check( array( $homepage_second_id, $homepage_first_id ) === foxfire_operations_get_homepage_product_ids( true ), 'Published purchasable homepage products remain eligible' );

	if ( function_exists( 'foxfire_get_homepage_products' ) ) {
		$homepage_query     = foxfire_get_homepage_products( FOXFIRE_OPERATIONS_HOMEPAGE_PRODUCTS_LIMIT );
		$homepage_query_ids = array_map( 'absint', wp_list_pluck( $homepage_query->posts, 'ID' ) );
		$foxfire_check( array( $homepage_second_id, $homepage_first_id ) === $homepage_query_ids, 'Homepage storefront query honors the exact saved priority order' );
	}

	$homepage_second->set_stock_status( 'outofstock' );
	$homepage_second->save();
	$foxfire_check( array( $homepage_first_id ) === foxfire_operations_get_homepage_product_ids( true ), 'Out-of-stock selected products are omitted from the public homepage' );
	$homepage_second->set_stock_status( 'instock' );
	$homepage_second->save();

	$coupon = new WC_Coupon();
	$coupon->set_code( 'ffaccept' . $run_id );
	$coupon->set_discount_type( 'percent' );
	$coupon->set_amount( 10 );
	$coupon->set_minimum_amount( 50 );
	$coupon->set_usage_limit( 5 );
	$coupon->set_usage_limit_per_user( 1 );
	$coupon->set_exclude_sale_items( true );
	$coupon->set_product_ids( array( $product_id ) );
	$coupon_id = $coupon->save();
	$foxfire_cleanup['coupons'][] = $coupon_id;
	$foxfire_check( 10.0 === (float) $coupon->get_amount() && 1 === $coupon->get_usage_limit_per_user(), 'Restricted coupon settings persist correctly' );

	$order = wc_create_order( array( 'customer_id' => $customer_id ) );
	if ( is_wp_error( $order ) ) {
		throw new RuntimeException( $order->get_error_message() );
	}
	$foxfire_cleanup['orders'][] = $order->get_id();
	$order->add_product( wc_get_product( $product_id ), 1 );
	$order->set_address(
		array(
			'first_name' => 'Acceptance',
			'last_name'  => 'Customer',
			'address_1'  => '123 Test Street',
			'city'       => 'Manila',
			'state'      => 'NCR',
			'postcode'   => '1000',
			'country'    => 'PH',
			'email'      => 'customer-' . $run_id . '@example.test',
		),
		'billing'
	);
	$order->set_address(
		array(
			'first_name' => 'Acceptance',
			'last_name'  => 'Customer',
			'address_1'  => '123 Test Street',
			'city'       => 'Manila',
			'state'      => 'NCR',
			'postcode'   => '1000',
			'country'    => 'PH',
		),
		'shipping'
	);
	$order->calculate_totals();
	$order->save();
	$order->add_order_note( 'Foxfire acceptance private note.', false, true );

	$_POST = array(
		'foxfire_order_fulfillment_nonce' => wp_create_nonce( 'foxfire_save_order_fulfillment_' . $order->get_id() ),
		'foxfire_shipping_carrier'        => '<b>Test Carrier</b>',
		'foxfire_tracking_number'         => " TRACK-123\n",
		'foxfire_tracking_url'            => 'https://example.com/track/TRACK-123',
		'foxfire_shipped_date'            => '2026-09-07T10:30',
		'foxfire_needs_follow_up'         => 'yes',
	);
	foxfire_operations_save_order_fulfillment( $order->get_id(), $order );
	$order = wc_get_order( $order->get_id() );
	$foxfire_check( 'Test Carrier' === $order->get_meta( '_foxfire_shipping_carrier', true ), 'Carrier is sanitized and saved' );
	$foxfire_check( 'TRACK-123' === $order->get_meta( '_foxfire_tracking_number', true ), 'Tracking number is sanitized and saved' );
	$foxfire_check( 'https://example.com/track/TRACK-123' === $order->get_meta( '_foxfire_tracking_url', true ), 'HTTPS tracking URL is saved' );
	$foxfire_check( 'yes' === $order->get_meta( '_foxfire_needs_follow_up', true ), 'Follow-up queue flag is saved' );
	$foxfire_check( '' !== $order->get_meta( '_foxfire_shipped_date_gmt', true ), 'Shipped date is normalized to GMT' );

	$saved_url  = $order->get_meta( '_foxfire_tracking_url', true );
	$saved_date = $order->get_meta( '_foxfire_shipped_date_gmt', true );
	$_POST['foxfire_tracking_url'] = 'javascript:alert(1)';
	$_POST['foxfire_shipped_date'] = 'not-a-date';
	foxfire_operations_save_order_fulfillment( $order->get_id(), $order );
	$order = wc_get_order( $order->get_id() );
	$foxfire_check( $saved_url === $order->get_meta( '_foxfire_tracking_url', true ), 'Invalid tracking URL cannot overwrite a saved URL' );
	$foxfire_check( $saved_date === $order->get_meta( '_foxfire_shipped_date_gmt', true ), 'Invalid shipped date cannot overwrite a saved date' );

	$_POST['foxfire_order_fulfillment_nonce'] = 'invalid';
	$_POST['foxfire_shipping_carrier'] = 'Nonce Bypass';
	foxfire_operations_save_order_fulfillment( $order->get_id(), $order );
	$order = wc_get_order( $order->get_id() );
	$foxfire_check( 'Test Carrier' === $order->get_meta( '_foxfire_shipping_carrier', true ), 'Invalid fulfillment nonce cannot alter order metadata' );

	$order->set_billing_address_1( '456 Corrected Billing Avenue' );
	$order->set_shipping_address_1( '789 Corrected Shipping Road' );
	$order->save();
	$order = wc_get_order( $order->get_id() );
	$foxfire_check( '456 Corrected Billing Avenue' === $order->get_billing_address_1() && '789 Corrected Shipping Road' === $order->get_shipping_address_1(), 'Order billing and shipping addresses remain editable' );

	wp_set_current_user( $customer_id );
	$foxfire_check( ! foxfire_operations_can_manage_homepage_products(), 'Customer cannot manage homepage product priorities' );
	ob_start();
	foxfire_operations_render_account_tracking( $order );
	$tracking_html = ob_get_clean();
	$foxfire_check( str_contains( $tracking_html, 'TRACK-123' ) && str_contains( $tracking_html, 'https://example.com/track/TRACK-123' ), 'Order owner can view saved tracking details' );
	$foxfire_check( ! current_user_can( FOXFIRE_OPERATIONS_CAPABILITY ), 'Customer cannot access Foxfire administration' );
	$recoverable_order = foxfire_get_recoverable_checkout_order( $order->get_id() );
	$foxfire_check( $recoverable_order instanceof WC_Order && $recoverable_order->get_id() === $order->get_id(), 'Recent checkout order can be recovered only by its customer' );
	WC()->session->__unset( 'order_awaiting_payment' );
	foxfire_remember_recent_checkout_order( $order->get_id(), array(), $order );
	ob_start();
	foxfire_render_checkout_recovery();
	$recovery_html = ob_get_clean();
	$foxfire_check( str_contains( $recovery_html, 'ff-checkout-recovery' ) && str_contains( $recovery_html, 'Continue payment' ), 'Order owner receives a session-backed checkout recovery action' );

	wp_set_current_user( $manager_id );
	$foxfire_check( null === foxfire_get_recoverable_checkout_order( $order->get_id() ), 'Another authenticated user cannot recover a customer order' );
	ob_start();
	foxfire_render_checkout_recovery();
	$wrong_owner_recovery_html = ob_get_clean();
	$foxfire_check( '' === trim( $wrong_owner_recovery_html ), 'Checkout recovery renders no order details for a different user' );
	foxfire_clear_recent_checkout_order( $order->get_id() );
	$refund_one = wc_create_refund(
		array(
			'amount'         => 10,
			'reason'         => 'Foxfire acceptance partial refund',
			'order_id'       => $order->get_id(),
			'refund_payment' => false,
			'restock_items'  => false,
		)
	);
	$foxfire_check( $refund_one instanceof WC_Order_Refund, 'Manual partial refund record can be created' );
	$order = wc_get_order( $order->get_id() );
	$remaining = (float) $order->get_remaining_refund_amount();
	$refund_two = wc_create_refund(
		array(
			'amount'         => $remaining,
			'reason'         => 'Foxfire acceptance final refund',
			'order_id'       => $order->get_id(),
			'refund_payment' => false,
			'restock_items'  => false,
		)
	);
	$foxfire_check( $refund_two instanceof WC_Order_Refund, 'Manual full-refund remainder can be recorded' );
	$order = wc_get_order( $order->get_id() );
	$foxfire_check( abs( (float) $order->get_total_refunded() - (float) $order->get_total() ) < 0.001, 'Full and partial refund records reconcile to the order total' );

	$flat_expensive = new WC_Shipping_Rate( 'flat_rate:90', 'Flat 20', 20, array(), 'flat_rate', 90 );
	$flat_cheapest  = new WC_Shipping_Rate( 'flat_rate:91', 'Flat 10', 10, array(), 'flat_rate', 91 );
	$free_shipping  = new WC_Shipping_Rate( 'free_shipping:92', 'Free', 0, array(), 'free_shipping', 92 );
	$paid_choice = foxfire_operations_choose_automatic_shipping_rate(
		array( $flat_expensive->get_id() => $flat_expensive, $flat_cheapest->get_id() => $flat_cheapest ),
		array()
	);
	$free_choice = foxfire_operations_choose_automatic_shipping_rate(
		array( $flat_cheapest->get_id() => $flat_cheapest, $free_shipping->get_id() => $free_shipping ),
		array()
	);
	$foxfire_check( array_key_first( $paid_choice ) === $flat_cheapest->get_id(), 'Automatic shipping chooses the least expensive eligible flat rate' );
	$foxfire_check( array_key_first( $free_choice ) === $free_shipping->get_id(), 'Eligible free shipping automatically overrides paid shipping' );
	$foxfire_check( 10.0 === (float) reset( $free_choice )->get_meta_data()['_foxfire_comparison_shipping_cost'], 'Free shipping retains the paid comparison amount for display' );

	$gateway = new Foxfire_Operations_Gateway_Wise();
	$clean_gateway_title = $gateway->validate_text_field( 'title', '<b>Title</b><script>alert(1)</script>' );
	$foxfire_check( ! str_contains( $clean_gateway_title, '<' ) && str_contains( $clean_gateway_title, 'Title' ) && strlen( $clean_gateway_title ) <= 120, 'Manual payment title strips HTML and respects its length limit' );
	$foxfire_check( ! str_contains( $gateway->validate_textarea_field( 'instructions', "Line one\n<script>alert(1)</script>" ), '<script>' ), 'Manual payment instructions strip HTML' );

	$security_checks = foxfire_operations_get_security_checks();
	$failed_security = array_filter( $security_checks, static fn( array $check ): bool => 'fail' === $check['status'] );
	$foxfire_check( empty( $failed_security ), 'Live Foxfire security-baseline checks pass locally' );
} catch ( Throwable $error ) {
	$foxfire_results[] = array( 'passed' => false, 'label' => 'Failure: ' . $error->getMessage() );
} finally {
	$_POST = $foxfire_original_post;
	$_GET  = $foxfire_original_get;
	wp_set_current_user( $foxfire_original_user );
	if ( WC()->session ) {
		if ( null === $foxfire_original_awaiting_payment ) {
			WC()->session->__unset( 'order_awaiting_payment' );
		} else {
			WC()->session->set( 'order_awaiting_payment', $foxfire_original_awaiting_payment );
		}
		if ( null === $foxfire_original_recent_checkout ) {
			WC()->session->__unset( 'foxfire_recent_checkout_order' );
		} else {
			WC()->session->set( 'foxfire_recent_checkout_order', $foxfire_original_recent_checkout );
		}
	}

	if ( $foxfire_original_homepage_products_exists ) {
		update_option( FOXFIRE_OPERATIONS_HOMEPAGE_PRODUCTS_OPTION, $foxfire_original_homepage_products, false );
	} else {
		delete_option( FOXFIRE_OPERATIONS_HOMEPAGE_PRODUCTS_OPTION );
	}
	if ( $foxfire_original_homepage_audit_exists ) {
		update_option( FOXFIRE_OPERATIONS_HOMEPAGE_PRODUCTS_AUDIT_OPTION, $foxfire_original_homepage_audit, false );
	} else {
		delete_option( FOXFIRE_OPERATIONS_HOMEPAGE_PRODUCTS_AUDIT_OPTION );
	}

	foreach ( array_reverse( $foxfire_cleanup['orders'] ) as $order_id ) {
		$order = wc_get_order( $order_id );
		if ( $order instanceof WC_Order ) {
			$order->delete( true );
		}
	}
	foreach ( array_reverse( $foxfire_cleanup['coupons'] ) as $coupon_id ) {
		$coupon = new WC_Coupon( $coupon_id );
		if ( $coupon->get_id() ) {
			$coupon->delete( true );
		}
	}
	foreach ( array_reverse( $foxfire_cleanup['products'] ) as $cleanup_product_id ) {
		$cleanup_product = wc_get_product( $cleanup_product_id );
		if ( $cleanup_product instanceof WC_Product ) {
			$cleanup_product->delete( true );
		}
	}
	foreach ( array_reverse( $foxfire_cleanup['posts'] ) as $post_id ) {
		wp_delete_post( $post_id, true );
	}
	foreach ( array_reverse( $foxfire_cleanup['users'] ) as $user_id ) {
		delete_transient( 'foxfire_order_errors_' . $user_id );
		wp_delete_user( $user_id );
	}
	foreach ( $foxfire_cleanup['auth_buckets'] as $auth_bucket ) {
		foxfire_operations_clear_auth_bucket( $auth_bucket[0], $auth_bucket[1] );
	}
}

$foxfire_failed = 0;
foreach ( $foxfire_results as $result ) {
	echo ( $result['passed'] ? '[PASS] ' : '[FAIL] ' ) . $result['label'] . PHP_EOL;
	if ( ! $result['passed'] ) {
		++$foxfire_failed;
	}
}

echo PHP_EOL . sprintf( 'Admin acceptance result: %d passed, %d failed.', count( $foxfire_results ) - $foxfire_failed, $foxfire_failed ) . PHP_EOL;
if ( $foxfire_failed > 0 ) {
	exit( 1 );
}
