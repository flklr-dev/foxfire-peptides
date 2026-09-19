<?php
/** Local checkout review acceptance. Native order factories use disposable fixtures only. */
defined( 'ABSPATH' ) || exit;
if ( ! foxfire_operations_local_mail_is_enabled() ) {
	throw new RuntimeException( 'Requires localhost with Mailpit, never external mail.' );
}
require_once ABSPATH . 'wp-admin/includes/user.php';
$saved = array( WC()->cart, WC()->customer, WC()->session, get_current_user_id(), $_POST, $_REQUEST, $_SERVER );
$products = $orders = $users = $mail = array();
$run = 'checkout-' . strtolower( wp_generate_password( 8, false, false ) );
$email = $run . '@example.test';
$account_email = $run . '-account@example.test';
$checks = 0;
$detached_sessions = array();
$check = static function ( $condition, $label ) use ( &$checks ) {
	if ( ! $condition ) throw new RuntimeException( 'FAIL: ' . $label );
	++$checks;
	WP_CLI::log( 'PASS: ' . $label );
};
$render = static function ( callable $callback ) { ob_start(); try { $callback(); return ob_get_contents(); } finally { ob_end_clean(); } };
$capture = static function ( $args ) use ( &$mail, $email, $account_email ) {
	if ( in_array( $email, (array) $args['to'], true ) || in_array( $account_email, (array) $args['to'], true ) ) $mail[] = $args;
};
$disabled_admin_emails = array( 'new_order', 'cancelled_order', 'failed_order' );
foreach ( $disabled_admin_emails as $id ) add_filter( 'woocommerce_email_enabled_' . $id, '__return_false', 999 );
add_action( 'wp_mail_succeeded', $capture );
$no_review_option = static fn() => 'no';
$staging_home = static fn() => 'https://staging.foxfirepeptides.com';
$failure = null;
try {
	wp_set_current_user( 0 );
	$_POST = $_REQUEST = array();
	$_SERVER['REMOTE_ADDR'] = '192.0.2.211';
	$_SERVER['HTTP_HOST'] = 'localhost:8080';
	$_SERVER['SERVER_NAME'] = 'localhost';
	WC()->session = new WC_Session_Handler(); // Uninitialized: never touch a browser/customer session.
	WC()->customer = new WC_Customer( 0, true ); // Guest data belongs to the isolated WC session, not a user record.
	WC()->cart = new WC_Cart();
	WC()->customer->set_is_vat_exempt( true );
	WC()->mailer();
	$checkout = WC()->checkout();
	$check( ! $checkout->is_registration_required(), 'Guest checkout is enabled and does not require an account' );
	$check( $checkout->is_registration_enabled(), 'Optional checkout account creation is enabled' );
	$check( 'yes' === get_option( 'woocommerce_enable_checkout_login_reminder' ), 'Existing-customer checkout sign-in is enabled' );
	$check( foxfire_operations_checkout_is_review_mode(), 'Local private checkout review mode is active' );
	$check( array() === WC()->payment_gateways()->get_available_payment_gateways(), 'No customer payment methods are exposed during review' );
	$registered = WC()->payment_gateways()->payment_gateways();
	$check( isset( $registered['bacs'], $registered['cod'] ), 'Native gateway objects remain registered for administrator configuration' );
	$check( 'no' === $registered['bacs']->enabled && 'no' === $registered['cod']->enabled, 'Old local BACS and COD test methods are disabled' );
	$sentinel = array( 'future_approved_gateway' => new stdClass() );
	add_filter( 'pre_option_foxfire_checkout_review_mode', $no_review_option );
	$check( $sentinel === foxfire_operations_checkout_review_gateways( $sentinel ), 'Review guard does not remove gateways when explicit review is off outside staging' );
	add_filter( 'pre_option_home', $staging_home );
	$check( foxfire_operations_checkout_is_review_mode() && array() === foxfire_operations_checkout_review_gateways( $sentinel ), 'Staging hostname remains protected even without the saved review flag' );
	remove_filter( 'pre_option_home', $staging_home );
	remove_filter( 'pre_option_foxfire_checkout_review_mode', $no_review_option );
	foreach ( array( 'GET', 'POST' ) as $method ) {
		$response = rest_do_request( new WP_REST_Request( $method, '/wc/store/v1/checkout' ) );
		$check( 403 === $response->get_status() && 'foxfire_checkout_review' === $response->get_data()['code'], $method . ' Store API checkout blocked before order/draft creation' );
	}
	$check( null === foxfire_operations_checkout_review_rest( null, null, new WP_REST_Request( 'GET', '/wc/v3/orders' ) ), 'Normal administrative order API is not blocked by the checkout guard' );
	// Multiple CLI cart instances must not let an earlier empty cart clear fixture shipping.
	$fixture_session = ( new ReflectionProperty( WC_Cart::class, 'session' ) )->getValue( WC()->cart );
	foreach ( $GLOBALS['wp_filter']['woocommerce_after_calculate_totals']->callbacks as $priority => $callbacks ) {
		foreach ( $callbacks as $callback ) {
			$handler = $callback['function'];
			if ( is_array( $handler ) && $handler[0] instanceof WC_Cart_Session && $handler[0] !== $fixture_session ) {
				$detached_sessions[] = array( $handler, $priority, $callback['accepted_args'] );
				remove_action( 'woocommerce_after_calculate_totals', $handler, $priority );
			}
		}
	}

	$product = new WC_Product_Simple();
	$product->set_name( 'Disposable checkout acceptance ' . $run );
	$product->set_status( 'publish' );
	$product->set_catalog_visibility( 'hidden' );
	$product->set_regular_price( '10' );
	$products[] = $product_id = $product->save();
	update_post_meta( $product_id, 'foxfire_tier_enable', '0' );
	$key = WC()->cart->add_to_cart( $product_id, 3 );
	$check( (bool) $key, 'Isolated guest cart accepts exactly three fixture units' );
	$address = array( 'first_name' => 'Local', 'last_name' => 'Reviewer', 'country' => 'US', 'state' => 'MA', 'address_1' => '123 Fixture Street', 'address_2' => '', 'city' => 'Boston', 'postcode' => '02108', 'phone' => '2025550123', 'email' => $email );
	foreach ( array( 'billing', 'shipping' ) as $type ) foreach ( $address as $field => $value ) {
		$_POST[ $type . '_' . $field ] = $value;
		if ( is_callable( array( WC()->customer, 'set_' . $type . '_' . $field ) ) ) WC()->customer->{'set_' . $type . '_' . $field}( $value );
	}
	$_POST['terms'] = '1'; $_POST['terms-field'] = '1'; $_POST['foxfire_age_research_acknowledgement'] = '1';
	$_POST['woocommerce-process-checkout-nonce'] = wp_create_nonce( 'woocommerce-process_checkout' );
	$packages = WC()->shipping()->calculate_shipping( WC()->cart->get_shipping_packages() );
	$check( ! empty( $packages[0]['rates'] ), 'Saved native shipping settings provide rates for the valid US fixture destination' );
	wc_get_chosen_shipping_method_for_package( 0, $packages[0] );
	WC()->cart->calculate_totals();
	$_POST['shipping_method'] = WC()->session->get( 'chosen_shipping_methods' );
	$data = $checkout->get_posted_data();
	$check( 0 === $data['createaccount'] && empty( $data['account_password'] ), 'Native guest posted data does not require account fields' );
	$form = $render( static fn() => wc_get_template( 'checkout/form-checkout.php', array( 'checkout' => $checkout ) ) );
	$check( str_contains( $form, 'Save my information for faster checkout' ) && ! str_contains( $form, 'analytical research reorders' ), 'Requested save-information wording renders exactly' );
	$check( str_contains( $form, 'id="ffAddressModal"' ) && str_contains( $form, 'id="billing_address_1"' ) && str_contains( $form, 'id="billing_email"' ), 'Delivery popup and native address/contact fields remain intact' );
	$check( str_contains( $form, 'id="createaccount"' ) && str_contains( $form, 'Have an account?' ) && str_contains( $form, 'name="login"' ), 'Anonymous checkout retains optional account checkbox and existing-customer sign-in form' );
	$terms_url = wc_get_page_permalink( 'terms' );
	$check( 15 === wc_get_page_id( 'terms' ) && 'publish' === get_post_status( wc_get_page_id( 'terms' ) ) && str_contains( $form, esc_url( $terms_url ) ) && str_contains( $form, 'id="terms"' ), 'Required terms checkbox links to the published current Terms & Conditions page' );
	$check( 1 === substr_count( $form, 'id="terms"' ) && 1 === substr_count( $form, 'id="foxfire_age_research_acknowledgement"' ) && str_contains( $form, 'I confirm that I am 21 years of age or older' ) && str_contains( $form, 'not intended for human consumption or medical use.' ), 'Separate required Terms and exact 21+ research-use acknowledgements are displayed' );
	preg_match( '/<input[^>]+id="terms"[^>]*>/i', $form, $terms_input );
	preg_match( '/<input[^>]+id="foxfire_age_research_acknowledgement"[^>]*>/i', $form, $age_input );
	$check( isset( $terms_input[0], $age_input[0] ) && str_contains( $terms_input[0], 'required' ) && str_contains( $age_input[0], 'required' ) && ! preg_match( '/\bchecked\b/i', $terms_input[0] . $age_input[0] ), 'Both acknowledgements are required and never pre-checked on initial rendering' );
	$check( strpos( $form, 'id="terms"' ) < strpos( $form, 'id="place_order"' ) && ! str_contains( $form, 'class="woocommerce-terms-and-conditions-link"' ), 'Acknowledgement precedes Place Order and its Terms link opens the actual policy rather than an inline disclosure' );
	$check( str_contains( $form, 'data-review-mode="1" disabled' ) && str_contains( $form, foxfire_operations_checkout_review_message() ), 'Checkout displays review notice and a server-rendered disabled Place Order button' );
	$check( str_contains( $form, 'class="ff-payment-notice" role="status"' ) && ! str_contains( $form, 'woocommerce-notice--info' ), 'Payment availability uses the custom accessible status, not a default notice banner' );
	$validate = new ReflectionMethod( WC_Checkout::class, 'validate_checkout' );
	$errors = new WP_Error();
	$validate->invokeArgs( $checkout, array( &$data, &$errors ) );
	if ( $errors->get_error_message( 'shipping' ) ) {
		WP_CLI::log( 'Fixture shipping diagnostic: ' . wp_strip_all_tags( $errors->get_error_message( 'shipping' ) ) . '; chosen=' . wp_json_encode( WC()->session->get( 'chosen_shipping_methods' ) ) . '; rates=' . wp_json_encode( array_map( static fn( $package ) => array_keys( $package['rates'] ), WC()->shipping()->get_packages() ) ) );
	}
	$check( $errors->get_error_codes() === array( 'payment', 'foxfire_checkout_review' ), 'Complete guest billing/shipping data validates; only intentional payment/review blocks remain (' . implode( ', ', $errors->get_error_codes() ) . ')' );
	$missing_terms = $data; $missing_terms['terms'] = 0; $errors = new WP_Error();
	$validate->invokeArgs( $checkout, array( &$missing_terms, &$errors ) );
	$check( in_array( 'terms', $errors->get_error_codes(), true ), 'Server rejects missing terms even if client-side validation is bypassed' );
	$missing_terms['terms-field'] = 0; $errors = new WP_Error();
	$validate->invokeArgs( $checkout, array( &$missing_terms, &$errors ) );
	$check( in_array( 'terms', $errors->get_error_codes(), true ), 'Removing the native hidden terms field cannot bypass required acknowledgement' );
	unset( $_POST['foxfire_age_research_acknowledgement'] );
	$errors = new WP_Error();
	$validate->invokeArgs( $checkout, array( &$data, &$errors ) );
	$check( in_array( 'foxfire_age_research_acknowledgement', $errors->get_error_codes(), true ), 'Server independently rejects a missing 21+ research-use acknowledgement' );
	$_POST['foxfire_age_research_acknowledgement'] = '1';
	$missing_address = $data; $missing_address['billing_address_1'] = ''; $missing_address['billing_email'] = ''; $errors = new WP_Error();
	$validate->invokeArgs( $checkout, array( &$missing_address, &$errors ) );
	$check( in_array( 'billing_address_1_required', $errors->get_error_codes(), true ) && in_array( 'billing_email_required', $errors->get_error_codes(), true ), 'Server still requires guest address and contact email' );
	// Run actual normal checkout while protected: observe that it never reaches order creation.
	$created = false;
	$watch_creation = static function () use ( &$created ) { $created = true; };
	add_action( 'woocommerce_checkout_order_created', $watch_creation );
	$_POST['woocommerce-process-checkout-nonce'] = wp_create_nonce( 'woocommerce-process_checkout' );
	$_REQUEST = $_POST; wc_clear_notices(); $checkout->process_checkout();
	if ( ! wc_has_notice( foxfire_operations_checkout_review_message(), 'error' ) ) WP_CLI::log( 'Fixture submission diagnostic: ' . wp_json_encode( wc_get_notices( 'error' ) ) );
	$check( ! $created && ! get_user_by( 'email', $email ) && wc_has_notice( foxfire_operations_checkout_review_message(), 'error' ), 'Actual paid review checkout creates neither order nor customer' );
	$product->set_regular_price( '0' ); $product->set_price( '0' ); $product->set_virtual( true ); $product->save();
	WC()->cart->cart_contents[ $key ]['data'] = wc_get_product( $product_id );
	WC()->cart->calculate_totals();
	$check( ! WC()->cart->needs_payment() && 0.0 === (float) WC()->cart->get_total( 'edit' ), 'Zero-total bypass fixture really needs no payment' );
	wc_clear_notices(); $checkout->process_checkout();
	$check( ! $created && wc_has_notice( foxfire_operations_checkout_review_message(), 'error' ), 'Actual zero-total checkout is also blocked before order creation' );
	remove_action( 'woocommerce_checkout_order_created', $watch_creation );
	wc_clear_notices();

	// Low-level native factory verification is NOT a successful payment/checkout transaction.
	$product->set_regular_price( '10' ); $product->set_price( '10' ); $product->set_virtual( false ); $product->save();
	WC()->cart->cart_contents[ $key ]['data'] = wc_get_product( $product_id );
	WC()->cart->calculate_totals();
	$data['payment_method'] = '';
	$order_id = $checkout->create_order( $data );
	$check( ! is_wp_error( $order_id ), 'Native checkout order factory persists disposable guest order data without any payment' );
	$orders[] = $order_id; $order = wc_get_order( $order_id );
	$items = $order->get_items(); $item = reset( $items );
	$check( 0 === $order->get_customer_id() && $email === $order->get_billing_email() && 'Local' === $order->get_billing_first_name(), 'Saved guest order records customer contact information without an account' );
	$check( $product_id === $item->get_product_id() && 3 === $item->get_quantity() && 30.0 === (float) $item->get_total(), 'Saved order records the correct product, individual-unit quantity and line total' );
	$check( 'yes' === $order->get_meta( '_foxfire_age_research_acknowledgement' ) && '2026-09-16-21-plus' === $order->get_meta( '_foxfire_age_research_acknowledgement_version' ) && $order->get_meta( '_foxfire_age_research_acknowledged_gmt' ), 'Saved order records the accepted 21+ research-use statement and version' );
	$shipping = (float) WC()->cart->get_shipping_total();
	$check( $shipping > 0 && $shipping === (float) $order->get_shipping_total() && 30.0 + $shipping === (float) $order->get_total() && 1 === count( $order->get_items( 'shipping' ) ), 'Saved order contains shipping method, configured shipping charge and matching total' );
	$check( '123 Fixture Street' === $order->get_shipping_address_1() && 'US' === $order->get_shipping_country() && 'pending' === $order->get_status() && '' === $order->get_payment_method() && ! $order->get_date_paid(), 'Shipping address and unpaid pending state are recorded without inventing a card payment' );
	$order->update_meta_data( '_foxfire_tracking_number', 'LOCAL-CHECKOUT-TRACK' );
	$order->update_meta_data( '_foxfire_tracking_url', 'https://example.test/local-checkout-tracking' );
	$order->update_meta_data( '_foxfire_shipping_carrier', 'Local Fixture Carrier' );
	$order->save();
	$check( 'LOCAL-CHECKOUT-TRACK' === foxfire_operations_get_tracking_details( wc_get_order( $order_id ) )['tracking'], 'Available tracking metadata persists on the order' );
	foreach ( array( 'on-hold', 'processing', 'completed', 'shipped' ) as $status ) {
		$before = count( $mail ); $order->update_status( $status );
		$check( count( $mail ) > $before, $status . ' customer email is accepted by actual local SMTP/Mailpit transport' );
	}
	$check( str_contains( end( $mail )['message'], 'LOCAL-CHECKOUT-TRACK' ) && str_contains( end( $mail )['message'], 'local-checkout-tracking' ), 'Shipment email includes recorded tracking number and link' );
	$customer_emails = WC()->mailer()->get_emails();
	$check( 'info@foxfirepeptides.com' === $customer_emails['WC_Email_Customer_Completed_Order']->get_from_address(), 'Configured WooCommerce customer-email sender is info@foxfirepeptides.com' );
	$check( ! str_contains( end( $mail )['message'], 'support@foxfirepeptides.com' ), 'Customer shipment email has no old support email reference' );

	// Native optional customer helper, isolated from submission and no gateway enabled.
	$_POST['createaccount'] = '1'; $_POST['billing_email'] = $account_email;
	$_POST['account_password'] = wp_generate_password( 24 );
	$account_data = $checkout->get_posted_data();
	$check( 1 === $account_data['createaccount'] && '' !== $account_data['account_password'], 'Choosing account creation passes optional credentials to native checkout data' );
	(new ReflectionMethod( WC_Checkout::class, 'process_customer' ))->invoke( $checkout, $account_data );
	$user = get_user_by( 'email', $account_email );
	if ( $user ) $users[] = $user->ID;
	$check( $user instanceof WP_User && in_array( 'customer', $user->roles, true ), 'Native checkout customer helper creates a customer account when explicitly chosen' );
	$customer_order = $checkout->create_order( $account_data );
	$check( ! is_wp_error( $customer_order ), 'Native order factory also records account-associated orders' );
	$orders[] = $customer_order;
	$check( $user->ID === wc_get_order( $customer_order )->get_customer_id(), 'Registered-customer order is associated with the correct account' );
	$check( count( array_filter( $mail, static fn( $message ) => in_array( $account_email, (array) $message['to'], true ) ) ) > 0, 'Checkout-created account email reaches local Mailpit transport' );
	WP_CLI::log( 'Mailpit fixture recipients: ' . $email . ', ' . $account_email );
} catch ( Throwable $error ) { $failure = $error->getMessage(); }
finally {
	remove_filter( 'pre_option_home', $staging_home );
	remove_filter( 'pre_option_foxfire_checkout_review_mode', $no_review_option );
	if ( isset( $watch_creation ) ) remove_action( 'woocommerce_checkout_order_created', $watch_creation );
	remove_action( 'wp_mail_succeeded', $capture );
	foreach ( $orders as $id ) { $fixture = wc_get_order( $id ); if ( $fixture ) $fixture->delete( true ); }
	foreach ( $products as $id ) { $fixture = wc_get_product( $id ); if ( $fixture ) $fixture->delete( true ); }
	foreach ( $users as $id ) wp_delete_user( $id );
	foreach ( $disabled_admin_emails as $id ) remove_filter( 'woocommerce_email_enabled_' . $id, '__return_false', 999 );
	foreach ( $detached_sessions as $callback ) add_action( 'woocommerce_after_calculate_totals', $callback[0], $callback[1], $callback[2] );
	list( WC()->cart, WC()->customer, WC()->session, $user_id, $_POST, $_REQUEST, $_SERVER ) = $saved;
	wp_set_current_user( $user_id );
	WC_Cache_Helper::get_transient_version( 'shipping', true );
}
if ( $failure ) WP_CLI::error( $failure );
WP_CLI::success( $checks . ' checkout checks passed. Own fixtures removed; messages captured locally only; payment methods remain disabled.' );
