<?php
/** Read-only local acceptance checks for marketing opt-in placement and records. */
defined( 'ABSPATH' ) || exit;

$original_post = $_POST;
$captured      = array();
$checks        = array();
$fake_user_id  = 987654321;
$observe_meta  = static function ( $check, $user_id, $key, $value ) use ( &$captured, $fake_user_id ) {
	if ( $fake_user_id === (int) $user_id && str_starts_with( $key, '_foxfire_marketing_' ) ) {
		$captured[ $key ] = $value;
		return true; // Short-circuit the write; no user or fixture is created.
	}
	return $check;
};
$assert = static function ( bool $passes, string $label ) use ( &$checks ): void {
	$checks[] = $label;
	if ( ! $passes ) {
		throw new RuntimeException( $label );
	}
};

add_filter( 'update_user_metadata', $observe_meta, 10, 4 );
try {
	$_POST = array();
	ob_start();
	wc_get_template( 'myaccount/form-login.php' );
	$registration = ob_get_clean();
	ob_start();
	wc_get_template( 'checkout/terms.php' );
	$checkout = ob_get_clean();

	$age_position     = strpos( $registration, 'id="age_research_agree"' );
	$account_position = strpos( $registration, 'id="foxfire_registration_marketing_optin"' );
	$checkout_age     = strpos( $checkout, 'id="foxfire_age_research_acknowledgement"' );
	$checkout_optin   = strpos( $checkout, 'id="foxfire_marketing_optin"' );
	$assert( false !== $age_position && false !== $account_position && $account_position > $age_position, 'Registration opt-in follows the 21+ acknowledgement' );
	$assert( false !== $checkout_age && false !== $checkout_optin && $checkout_optin > $checkout_age, 'Checkout opt-in follows the 21+ acknowledgement' );
	$assert( 1 === preg_match( '/<input[^>]*id="foxfire_registration_marketing_optin"[^>]*>/', $registration, $account_input )
		&& ! str_contains( $account_input[0], 'checked' ) && ! str_contains( $account_input[0], 'disabled' )
		&& 1 === preg_match( '/<input[^>]*id="foxfire_marketing_optin"[^>]*>/', $checkout, $checkout_input )
		&& ! str_contains( $checkout_input[0], 'checked' ) && ! str_contains( $checkout_input[0], 'disabled' ), 'Both optional checkboxes start unchecked and enabled' );
	$assert( 1 === substr_count( $registration, foxfire_marketing_consent_text() ) && 1 === substr_count( $checkout, foxfire_marketing_consent_text() ), 'Both opt-ins use the same consent statement' );

	$_POST = array( 'register' => 'Create Account', 'woocommerce-register-nonce' => wp_create_nonce( 'woocommerce-register' ) );
	foxfire_record_registration_marketing_consent( $fake_user_id );
	$assert( array() === $captured, 'No user consent is recorded when unchecked' );
	$_POST['foxfire_marketing_optin'] = '1';
	foxfire_record_registration_marketing_consent( $fake_user_id );
	$assert( 'yes' === ( $captured['_foxfire_marketing_optin'] ?? '' )
		&& 'account_registration' === ( $captured['_foxfire_marketing_optin_source'] ?? '' )
		&& foxfire_marketing_consent_text() === ( $captured['_foxfire_marketing_optin_statement'] ?? '' )
		&& ! empty( $captured['_foxfire_marketing_optin_at'] )
		&& ! empty( $captured['_foxfire_marketing_optin_version'] ), 'Explicit registration consent records source, statement, version and UTC time' );
	$captured = array();
	$_POST['woocommerce-register-nonce'] = 'invalid';
	foxfire_record_registration_marketing_consent( $fake_user_id );
	$assert( array() === $captured, 'Invalid registration nonce cannot record consent' );

	$_POST = array();
	$order = new WC_Order(); // Not saved.
	foxfire_record_checkout_marketing_consent( $order );
	$assert( '' === $order->get_meta( '_foxfire_marketing_optin' ), 'Unchecked checkout records no consent' );
	$_POST['foxfire_marketing_optin'] = '1';
	foxfire_record_checkout_marketing_consent( $order );
	$assert( 'yes' === $order->get_meta( '_foxfire_marketing_optin' )
		&& 'checkout' === $order->get_meta( '_foxfire_marketing_optin_source' )
		&& foxfire_marketing_consent_text() === $order->get_meta( '_foxfire_marketing_optin_statement' )
		&& '' !== $order->get_meta( '_foxfire_marketing_optin_at' ), 'Explicit checkout consent records the same audit fields' );

	echo 'PASS: ' . count( $checks ) . " newsletter consent checks. No user, order or subscription was created.\n";
} finally {
	remove_filter( 'update_user_metadata', $observe_meta, 10 );
	$_POST = $original_post;
}
