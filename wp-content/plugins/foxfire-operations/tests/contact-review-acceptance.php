<?php
/** Local-only template submission tests; real messages stay inside Mailpit. */
defined( 'ABSPATH' ) || exit;
if ( ! foxfire_operations_local_mail_is_enabled() ) throw new RuntimeException( 'Use the isolated local Mailpit environment only.' );
$saved_post = $_POST;
$saved_server = $_SERVER;
$saved_global_post = $GLOBALS['post'] ?? null;
$ip = '192.0.2.201';
$rate_key = 'foxfire_contact_rl_' . md5( $ip );
$old_rate = get_transient( $rate_key );
$confirmation = 'Thank you. Your message has been received. We’ll get back to you as soon as possible.';
$check = static function ( bool $passed, string $label ): void {
	if ( ! $passed ) throw new RuntimeException( 'FAIL: ' . $label );
	WP_CLI::log( 'PASS: ' . $label );
};
$capture = null;
$observer = static function ( $atts ) use ( &$capture ) { $capture = $atts; return $atts; };
$render = static function (): string {
	ob_start();
	include get_stylesheet_directory() . '/page-contact.php';
	$html = ob_get_clean();
	// WP's get_footer uses require_once; render the shared footer explicitly for repeated simulated requests.
	if ( ! str_contains( $html, '<div class="ff-site-footer">' ) ) {
		ob_start();
		foxfire_render_site_footer();
		$html .= ob_get_clean();
	}
	return $html;
};
try {
	$contact = get_page_by_path( 'contact' );
	$check( $contact instanceof WP_Post, 'Contact page exists' );
	$GLOBALS['post'] = $contact;
	setup_postdata( $contact );
	$_SERVER['REQUEST_METHOD'] = 'GET';
	$_SERVER['REMOTE_ADDR'] = $ip;
	$_SERVER['SERVER_NAME'] = 'localhost';
	$_POST = array();
	$html = $render();
	$check( str_contains( $html, 'mailto:info@foxfirepeptides.com' ), 'Contact uses clickable info email' );
	$check( str_contains( $html, 'Messages Accepted 24/7' ) && str_contains( $html, 'We’ll respond as soon as possible' ), 'Contact availability matches approved wording' );
	$check( ! str_contains( $html, 'Monday through Friday' ) && ! str_contains( $html, '12–24 business hours' ), 'Old business-hour promises are absent' );
	$check( str_contains( $html, 'class="ff-site-footer__links"' ) && substr_count( $html, 'mailto:info@foxfirepeptides.com' ) >= 2, 'Footer shares the contact email' );
	$schema = foxfire_operations_public_content_schema();
	$check( 'email' === $schema['support_email']['type'], 'Recipient remains an admin-managed validated email field' );
	$_SERVER['REQUEST_METHOD'] = 'POST';
	$_POST = array( 'foxfire_contact_submit' => '1', 'foxfire_contact_nonce' => wp_create_nonce( 'foxfire_contact_action' ), 'ff_name' => 'Local Contact Review', 'ff_email' => 'review@example.test', 'ff_subject' => 'general', 'ff_message' => 'CONTACT-REVIEW-LOCAL-20260913: isolated delivery test, no real customer data.' );
	delete_transient( $rate_key );
	add_filter( 'wp_mail', $observer );
	$html = $render();
	$check( str_contains( $html, $confirmation ), 'A real locally sent submission shows the exact confirmation' );
	$check( 'info@foxfirepeptides.com' === $capture['to'], 'Real local form notification targets info email' );
	$check( str_contains( $capture['message'], 'CONTACT-REVIEW-LOCAL-20260913' ) && in_array( 'Reply-To: Local Contact Review <review@example.test>', $capture['headers'], true ), 'Notification includes the message and safe reply-to address' );
	$fail_mail = static fn() => false;
	add_filter( 'pre_wp_mail', $fail_mail, 999 );
	delete_transient( $rate_key );
	$html = $render();
	$check( str_contains( $html, 'Your message could not be sent.' ) && ! str_contains( $html, $confirmation ), 'Failed mail does not show success' );
	$check( str_contains( $html, 'CONTACT-REVIEW-LOCAL-20260913' ) && str_contains( $html, 'class="ff-contact-form"' ), 'Failed sends preserve the form and message' );
	remove_filter( 'pre_wp_mail', $fail_mail, 999 );
	$override = static function () { return array( 'support_email' => 'different-recipient@example.test' ); };
	$fake_mail = static fn() => true;
	add_filter( 'pre_option_foxfire_public_content', $override );
	add_filter( 'pre_wp_mail', $fake_mail, 999 );
	delete_transient( $rate_key );
	$html = $render();
	$check( 'different-recipient@example.test' === $capture['to'] && substr_count( $html, 'mailto:different-recipient@example.test' ) >= 2, 'An admin recipient change propagates to form, Contact and footer without code edits' );
	remove_filter( 'pre_option_foxfire_public_content', $override );
	$_POST['foxfire_contact_nonce'] = 'invalid';
	$capture = null;
	$html = $render();
	$check( null === $capture && str_contains( $html, 'Security check failed.' ), 'Invalid nonce rejects submission without sending mail' );
} finally {
	remove_filter( 'wp_mail', $observer );
	if ( isset( $fail_mail ) ) remove_filter( 'pre_wp_mail', $fail_mail, 999 );
	if ( isset( $fake_mail ) ) remove_filter( 'pre_wp_mail', $fake_mail, 999 );
	if ( isset( $override ) ) remove_filter( 'pre_option_foxfire_public_content', $override );
	delete_transient( $rate_key );
	if ( false !== $old_rate ) set_transient( $rate_key, $old_rate, HOUR_IN_SECONDS );
	$_POST = $saved_post;
	$_SERVER = $saved_server;
	wp_reset_postdata();
	$GLOBALS['post'] = $saved_global_post;
}
WP_CLI::success( 'Contact review tests passed; real test notification is captured locally in Mailpit, not delivered externally.' );
