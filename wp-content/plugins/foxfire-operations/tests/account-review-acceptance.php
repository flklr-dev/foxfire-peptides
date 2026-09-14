<?php
/** Local-only customer portal acceptance tests. Own fixtures are always removed. */
defined( 'ABSPATH' ) || exit;
if ( ! foxfire_operations_local_mail_is_enabled() ) {
	throw new RuntimeException( 'These tests require local Mailpit delivery.' );
}
require_once ABSPATH . 'wp-admin/includes/user.php';
class Foxfire_Account_Review_Redirect extends Error {}
$original_user = get_current_user_id();
$original_post = $_POST;
$original_request = $_REQUEST;
$original_server = $_SERVER;
$original_query = $GLOBALS['wp']->query_vars;
$users = $products = $orders = $attachments = $checks = $mail = array();
$run = 'portal-' . strtolower( wp_generate_password( 8, false, false ) );
$email = $run . '@example.test';
$changed_email = $run . '-updated@example.test';
$password = wp_generate_password( 24 );
$password2 = wp_generate_password( 24 );
$password3 = wp_generate_password( 24 );
$last_redirect = '';
$redirect = static function ( $url ) use ( &$last_redirect ) { $last_redirect = $url; throw new Foxfire_Account_Review_Redirect( $url ); };
$observer = static function ( $args ) use ( &$mail ) { $mail[] = $args; return $args; };
$check = static function ( bool $condition, string $label ) use ( &$checks ): void {
	$checks[] = array( 'pass' => $condition, 'label' => $label );
	if ( ! $condition ) { throw new RuntimeException( $label ); }
};
$invoke = static function ( callable $handler ): void {
	wc_clear_notices();
	$_REQUEST = $_POST;
	try { $handler(); } catch ( Foxfire_Account_Review_Redirect $e ) {}
};
$render = static function ( callable $callback ): string { ob_start(); $callback(); return ob_get_clean(); };
$failure = null;
try {
	$_SERVER['REMOTE_ADDR'] = '192.0.2.210';
	$_SERVER['SERVER_NAME'] = 'localhost';
	$_SERVER['HTTP_HOST'] = 'localhost:8080';
	$_SERVER['REQUEST_METHOD'] = 'POST';
	WC()->initialize_session();
	WC()->mailer();
	add_filter( 'wp_redirect', $redirect, -999 );
	add_filter( 'wp_mail', $observer, 999 );
	wp_set_current_user( 0 );
	$check( 'yes' === get_option( 'woocommerce_enable_myaccount_registration' ) && 'no' === get_option( 'woocommerce_registration_generate_password' ), 'Customer self-registration and immediate password choice enabled' );
	$_POST = array( 'register' => 'Create Account', 'email' => $email, 'password' => $password, 'terms_agree' => 'on', 'woocommerce-register-nonce' => wp_create_nonce( 'woocommerce-register' ) );
	$invoke( array( 'WC_Form_Handler', 'process_registration' ) );
	$user = get_user_by( 'email', $email );
	if ( $user ) { $users[] = $user->ID; }
	$check( $user instanceof WP_User && in_array( 'customer', $user->roles, true ), 'Native registration form creates customer, not an operator' );
	$check( get_current_user_id() === $user->ID, 'Registration signs the customer in' );
	$check( count( array_filter( $mail, static fn( $m ) => in_array( $email, (array) $m['to'], true ) ) ) > 0, 'New-account email accepted by local transport for the new customer' );
	wp_logout();
	$check( ! is_user_logged_in(), 'Customer sign-out clears the logged-in user' );
	// The form handler caches its nonce at bootstrap; exercise its core authenticator here.
	$login = wp_signon( array( 'user_login' => $email, 'user_password' => $password, 'remember' => false ), false );
	$check( $login instanceof WP_User && $login->ID === $user->ID, 'Sign-in authenticator accepts the self-registered customer credentials' );
	wp_set_current_user( $user->ID );
	$_POST = array( 'action' => 'save_account_details', 'account_first_name' => 'Local', 'account_last_name' => 'Reviewer', 'account_display_name' => 'Local Reviewer', 'account_email' => $changed_email, 'password_current' => $password, 'password_1' => $password2, 'password_2' => $password2, 'save-account-details-nonce' => wp_create_nonce( 'save_account_details' ) );
	$invoke( array( 'WC_Form_Handler', 'save_account_details' ) );
	$user = get_userdata( $user->ID );
	$check( $user->first_name === 'Local' && $user->last_name === 'Reviewer' && $user->user_email === $changed_email && wp_check_password( $password2, $user->user_pass, $user->ID ), 'Native Account Details saves name, email and password' );
	foreach ( array( 'billing', 'shipping' ) as $type ) {
		$GLOBALS['wp']->query_vars = array( 'edit-address' => $type );
		$_POST = array( 'action' => 'edit_address', 'woocommerce-edit-address-nonce' => wp_create_nonce( 'woocommerce-edit_address' ) );
		foreach ( array( 'first_name' => 'Local', 'last_name' => 'Reviewer', 'country' => 'US', 'address_1' => '123 Test Street', 'city' => 'Boston', 'state' => 'MA', 'postcode' => '02108', 'phone' => '2025550123', 'email' => $changed_email ) as $key => $value ) { $_POST[$type . '_' . $key] = $value; }
		$invoke( array( 'WC_Form_Handler', 'save_address' ) );
		$customer = new WC_Customer( $user->ID );
		$check( '123 Test Street' === $customer->{'get_' . $type . '_address_1'}() && '02108' === $customer->{'get_' . $type . '_postcode'}(), 'Native ' . $type . ' address form saves customer-owned information' );
	}
	$_POST['account_first_name'] = 'INVALID';
	$_POST['action'] = 'save_account_details';
	$_POST['save-account-details-nonce'] = 'invalid';
	$invoke( array( 'WC_Form_Handler', 'save_account_details' ) );
	$check( 'Local' === get_userdata( $user->ID )->first_name, 'Invalid account-save nonce does not modify account data' );
	wp_logout();
	$before = count( $mail );
	foxfire_operations_maybe_send_password_reset( $changed_email );
	$check( count( $mail ) > $before && str_contains( end( $mail )['message'], 'lost-password' ), 'Forgotten-password request emits a real local WooCommerce reset email' );
	$key = get_password_reset_key( $user );
	WP_Session_Tokens::get_instance( $user->ID )->create( time() + HOUR_IN_SECONDS );
	$_POST = array( 'wc_reset_password' => 'true', 'password_1' => $password3, 'password_2' => $password3, 'reset_key' => $key, 'reset_login' => $user->user_login, 'woocommerce-reset-password-nonce' => wp_create_nonce( 'reset_password' ) );
	$valid_reset_post = $_POST;
	$_POST['woocommerce-reset-password-nonce'] = 'invalid';
	$invoke( array( 'WC_Form_Handler', 'process_reset_password' ) );
	$check( wp_check_password( $password2, get_userdata( $user->ID )->user_pass, $user->ID ) && 0 === get_current_user_id(), 'Invalid reset nonce neither changes password nor signs in' );
	$_POST = $valid_reset_post;
	$_POST['password_2'] = 'Mismatch-only-local-test';
	$invoke( array( 'WC_Form_Handler', 'process_reset_password' ) );
	$check( wp_check_password( $password2, get_userdata( $user->ID )->user_pass, $user->ID ) && 0 === get_current_user_id(), 'Mismatched reset passwords do not change the account or sign in' );
	$_POST = $valid_reset_post;
	$invoke( array( 'WC_Form_Handler', 'process_reset_password' ) );
	$check( wp_check_password( $password3, get_userdata( $user->ID )->user_pass, $user->ID ), 'Native reset form changes the password with a valid key' );
	$check( 0 === get_current_user_id() && empty( WP_Session_Tokens::get_instance( $user->ID )->get_all() ), 'Successful reset leaves customer signed out and revokes their sessions' );
	$check( add_query_arg( 'password-reset', 'true', wc_get_page_permalink( 'myaccount' ) ) === $last_redirect, 'Successful reset redirects to Sign In with the success notice' );
	$check( is_wp_error( check_password_reset_key( $key, $user->user_login ) ), 'Used reset key cannot be reused' );
	wp_set_current_user( $user->ID );
	$attachment = wp_insert_attachment( array( 'post_title' => 'LOCAL TEST ONLY - not a real COA', 'post_mime_type' => 'application/pdf', 'guid' => home_url( '/local-test-only-report.pdf' ), 'post_status' => 'inherit' ) );
	$attachments[] = $attachment;
	$product = new WC_Product_Simple();
	$product->set_name( 'LOCAL TEST ONLY ' . $run );
	$product->set_status( 'draft' );
	$product->set_regular_price( '10' );
	$product->save();
	$products[] = $product->get_id();
	update_post_meta( $product->get_id(), 'foxfire_batch_lot', 'LOCAL-BATCH-A' );
	update_post_meta( $product->get_id(), 'foxfire_coa_url', home_url( '/local-test-only-url-report.pdf' ) );
	update_field( 'field_foxfire_coa_file', $attachment, $product->get_id() );
	$document = foxfire_operations_product_document( $product->get_id() );
	$check( $document['url'] === wp_get_attachment_url( $attachment ), 'Admin-uploaded COA attachment takes precedence over URL' );
	$order = wc_create_order( array( 'customer_id' => $user->ID, 'status' => 'pending' ) );
	$orders[] = $order->get_id();
	$order->set_billing_email( $changed_email );
	$item = new WC_Order_Item_Product();
	$item->set_product( $product );
	$item->set_quantity( 1 );
	$item->set_total( '10' );
	foxfire_operations_snapshot_order_document( $item, '', array( 'product_id' => $product->get_id(), 'variation_id' => 0 ), $order );
	$order->add_item( $item );
	$order->update_meta_data( '_foxfire_tracking_number', 'LOCAL-TRACK-ONLY' );
	$order->update_meta_data( '_foxfire_tracking_url', 'https://example.test/local-tracking' );
	$order->save();
	$check( 'LOCAL-BATCH-A' === $item->get_meta( 'Batch / Lot', true ) && $document['url'] === $item->get_meta( 'COA Report URL', true ), 'Checkout snapshots the purchased batch and available report on the order line' );
	$owned = wc_get_orders( array( 'customer_id' => $user->ID, 'return' => 'ids', 'limit' => -1 ) );
	$check( in_array( $order->get_id(), $owned, true ) && current_user_can( 'view_order', $order->get_id() ), 'Customer can list and view their own order' );
	$tracking = $render( static fn() => foxfire_operations_render_account_tracking( $order ) );
	$check( str_contains( $tracking, 'LOCAL-TRACK-ONLY' ) && str_contains( $tracking, 'https://example.test/local-tracking' ), 'Order detail shows available shipment number and tracking link' );
	$docs = $render( 'foxfire_operations_render_customer_documents' );
	$check( str_contains( $docs, 'LOCAL-BATCH-A' ) && str_contains( $docs, esc_url( $document['url'] ) ), 'Account COA endpoint renders the customer order and report link' );
	update_post_meta( $product->get_id(), 'foxfire_batch_lot', 'LOCAL-BATCH-B' );
	update_field( 'field_foxfire_coa_file', false, $product->get_id() );
	update_post_meta( $product->get_id(), 'foxfire_coa_url', home_url( '/local-test-only-report-b.pdf' ) );
	$preserved = foxfire_operations_order_item_document( $item );
	$check( 'LOCAL-BATCH-A' === $preserved['batch'] && $document['url'] === $preserved['url'], 'Changing the catalog batch/report does not relabel the historical order' );
	$item->update_meta_data( 'COA Report URL', home_url( '/local-test-only-corrected-a.pdf' ) );
	$item->save();
	$check( str_contains( foxfire_operations_order_item_document( $item )['url'], 'corrected-a.pdf' ), 'Native order-item metadata can replace a specific order report' );
	$legacy = new WC_Order_Item_Product(); $legacy->set_product( $product );
	$check( ! foxfire_operations_order_item_document( $legacy )['recorded'], 'Older orders never imply a recorded shipment batch' );
	$check( '' === foxfire_operations_document_url( 'javascript:alert(1)' ) && '' === foxfire_operations_document_url( home_url( '/testing-coa/' ) ), 'Unsafe report URLs and directory placeholders rejected' );
	$other = wp_insert_user( array( 'user_login' => $run . '-other', 'user_email' => $run . '-other@example.test', 'user_pass' => wp_generate_password( 24 ), 'role' => 'customer' ) );
	$users[] = $other; wp_set_current_user( $other );
	$check( ! current_user_can( 'view_order', $order->get_id() ) && '' === $render( static fn() => foxfire_operations_render_order_documents( $order ) ), 'Another customer cannot view the order or its document section' );
	$check( ! str_contains( $render( 'foxfire_operations_render_customer_documents' ), 'LOCAL-BATCH-A' ), 'Account document query does not leak another customer orders' );
	wp_set_current_user( 0 );
	$check( '' === $render( 'foxfire_operations_render_customer_documents' ), 'Anonymous visitors cannot render customer documents' );
	$before = count( $mail );
	$order->update_status( 'processing' );
	$order->update_status( 'completed' );
	$order->update_status( 'shipped' );
	$check( count( $mail ) >= $before + 3, 'Processing, completed and shipped emails accepted by local transport' );
	$admins = get_users( array( 'role' => 'administrator', 'number' => 1, 'fields' => 'ID' ) );
	wp_set_current_user( (int) $admins[0] );
	$panel = $render( static fn() => foxfire_operations_render_order_documents_admin( $order ) );
	$check( str_contains( $panel, 'foxfire_order_documents' ) && str_contains( $panel, 'foxfire_order_documents_nonce' ), 'Admin document panel is available on fulfilled orders' );
	$_POST = array( 'foxfire_order_documents_nonce' => wp_create_nonce( 'foxfire_save_order_documents_' . $order->get_id() ), 'foxfire_order_documents' => array( $item->get_id() => array( 'batch' => 'LOCAL-BATCH-A', 'url' => home_url( '/admin-corrected-a.pdf' ) ) ) );
	foxfire_operations_save_order_documents( $order->get_id(), $order );
	$edited = wc_get_order( $order->get_id() )->get_item( $item->get_id() );
	$check( str_contains( $edited->get_meta( 'COA Report URL', true ), 'admin-corrected-a.pdf' ) && 'shipped' === wc_get_order( $order->get_id() )->get_status(), 'Admin replaces fulfilled-order report without changing status or quantity' );
	$_POST['foxfire_order_documents'][$item->get_id()]['url'] = 'javascript:alert(1)';
	foxfire_operations_save_order_documents( $order->get_id(), $order );
	$check( str_contains( wc_get_order( $order->get_id() )->get_item( $item->get_id() )->get_meta( 'COA Report URL', true ), 'admin-corrected-a.pdf' ), 'Unsafe admin report URL preserves the saved document' );
	$_POST['foxfire_order_documents'][$item->get_id()]['url'] = home_url( '/should-not-save.pdf' );
	$_POST['foxfire_order_documents_nonce'] = 'invalid';
	foxfire_operations_save_order_documents( $order->get_id(), $order );
	$check( str_contains( wc_get_order( $order->get_id() )->get_item( $item->get_id() )->get_meta( 'COA Report URL', true ), 'admin-corrected-a.pdf' ), 'Invalid document-save nonce does not modify the order' );
	wp_set_current_user( $user->ID );
	$_POST['foxfire_order_documents_nonce'] = wp_create_nonce( 'foxfire_save_order_documents_' . $order->get_id() );
	foxfire_operations_save_order_documents( $order->get_id(), $order );
	$check( str_contains( wc_get_order( $order->get_id() )->get_item( $item->get_id() )->get_meta( 'COA Report URL', true ), 'admin-corrected-a.pdf' ), 'Customer cannot change order documents even with a valid nonce' );
	$check( 'info@foxfirepeptides.com' === get_option( 'woocommerce_email_from_address' ), 'Configured WooCommerce sender uses info email' );
	$check( str_contains( wc_get_account_endpoint_url( 'batch-coa' ), '/my-account/batch-coa/' ) && isset( WC()->query->get_query_vars()['batch-coa'] ), 'Account navigation targets the registered customer COA endpoint' );
} catch ( Throwable $e ) { $failure = $e->getMessage(); }
finally {
	remove_filter( 'wp_redirect', $redirect, -999 ); remove_filter( 'wp_mail', $observer, 999 );
	foreach ( $orders as $id ) { $order = wc_get_order( $id ); if ( $order ) { $order->delete( true ); } }
	foreach ( $products as $id ) { wp_delete_post( $id, true ); }
	foreach ( $attachments as $id ) { wp_delete_post( $id, true ); }
	foreach ( $users as $id ) { if ( $id && ! is_wp_error( $id ) ) { wp_delete_user( $id ); } }
	foreach ( array( 'login_ip', 'reset_ip' ) as $scope ) { foxfire_operations_clear_auth_bucket( $scope, '192.0.2.210' ); }
	foreach ( array( $email, $changed_email ) as $identity ) { foreach ( array( 'login_identity', 'reset_identity' ) as $scope ) { foxfire_operations_clear_auth_bucket( $scope, $identity ); } }
	wp_set_current_user( $original_user ); $_POST = $original_post; $_REQUEST = $original_request; $_SERVER = $original_server; $GLOBALS['wp']->query_vars = $original_query;
}
foreach ( $checks as $result ) { WP_CLI::log( ( $result['pass'] ? 'PASS' : 'FAIL' ) . ': ' . $result['label'] ); }
if ( $failure ) { WP_CLI::error( $failure ); }
WP_CLI::success( count( $checks ) . ' customer portal checks passed; local messages remain in Mailpit only. Test fixtures removed.' );
