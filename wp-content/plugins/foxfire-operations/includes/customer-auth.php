<?php
/**
 * Customer authentication and password-reset hardening.
 *
 * @package Foxfire_Operations
 */

defined( 'ABSPATH' ) || exit;

/**
 * Return conservative application-layer authentication limits.
 *
 * Host/CDN throttling remains required in production because WordPress
 * transients are not a replacement for an edge rate limiter.
 *
 * @return array<string, array{limit:int,window:int}>
 */
function foxfire_operations_auth_rate_limit_policy(): array {
	$policy = array(
		'login_ip'       => array( 'limit' => 20, 'window' => 15 * MINUTE_IN_SECONDS ),
		'login_identity' => array( 'limit' => 7, 'window' => 15 * MINUTE_IN_SECONDS ),
		'reset_ip'       => array( 'limit' => 5, 'window' => 15 * MINUTE_IN_SECONDS ),
		'reset_identity' => array( 'limit' => 3, 'window' => 30 * MINUTE_IN_SECONDS ),
	);

	/**
	 * Filter authentication limits for a documented hosting environment.
	 *
	 * Limits must remain positive integers. Invalid overrides fall back to the
	 * values above so an integration cannot accidentally disable protection.
	 *
	 * @param array<string, array{limit:int,window:int}> $policy Limits by bucket.
	 */
	$filtered = apply_filters( 'foxfire_operations_auth_rate_limit_policy', $policy );
	if ( ! is_array( $filtered ) ) {
		return $policy;
	}

	foreach ( $policy as $scope => $defaults ) {
		if ( ! isset( $filtered[ $scope ]['limit'], $filtered[ $scope ]['window'] ) ) {
			continue;
		}

		$limit  = absint( $filtered[ $scope ]['limit'] );
		$window = absint( $filtered[ $scope ]['window'] );
		if ( $limit > 0 && $window >= MINUTE_IN_SECONDS ) {
			$policy[ $scope ] = array( 'limit' => $limit, 'window' => $window );
		}
	}

	return $policy;
}

/** Return the direct peer IP without trusting spoofable proxy headers. */
function foxfire_operations_request_ip(): string {
	$ip = isset( $_SERVER['REMOTE_ADDR'] ) ? trim( (string) $_SERVER['REMOTE_ADDR'] ) : '';

	/**
	 * Allow a hosting integration to provide an address only after it has
	 * validated the platform's trusted-proxy chain. Raw forwarding headers are
	 * intentionally never read here.
	 */
	$ip = (string) apply_filters( 'foxfire_operations_request_ip', $ip );
	return false !== filter_var( $ip, FILTER_VALIDATE_IP ) ? $ip : 'unknown';
}

/** Build a non-reversible transient key without storing an email, login, or IP. */
function foxfire_operations_auth_bucket_key( string $scope, string $identifier ): string {
	$digest = hash_hmac( 'sha256', strtolower( trim( $scope . '|' . $identifier ) ), wp_salt( 'auth' ) );
	return 'ff_auth_' . substr( $digest, 0, 40 );
}

/**
 * Return a live rate-limit bucket.
 *
 * @return array{count:int,reset:int}
 */
function foxfire_operations_get_auth_bucket( string $scope, string $identifier ): array {
	$key    = foxfire_operations_auth_bucket_key( $scope, $identifier );
	$bucket = get_transient( $key );

	if ( ! is_array( $bucket ) || ! isset( $bucket['count'], $bucket['reset'] ) || (int) $bucket['reset'] <= time() ) {
		return array( 'count' => 0, 'reset' => 0 );
	}

	return array( 'count' => absint( $bucket['count'] ), 'reset' => absint( $bucket['reset'] ) );
}

/** Whether a named authentication bucket has reached its configured limit. */
function foxfire_operations_auth_bucket_is_limited( string $scope, string $identifier ): bool {
	$policy = foxfire_operations_auth_rate_limit_policy();
	if ( ! isset( $policy[ $scope ] ) ) {
		return true;
	}

	$bucket = foxfire_operations_get_auth_bucket( $scope, $identifier );
	return $bucket['count'] >= $policy[ $scope ]['limit'];
}

/** Record one failed or reset authentication request. */
function foxfire_operations_record_auth_attempt( string $scope, string $identifier ): void {
	$policy = foxfire_operations_auth_rate_limit_policy();
	if ( ! isset( $policy[ $scope ] ) ) {
		return;
	}

	$key    = foxfire_operations_auth_bucket_key( $scope, $identifier );
	$bucket = foxfire_operations_get_auth_bucket( $scope, $identifier );
	$reset  = $bucket['reset'] > time() ? $bucket['reset'] : time() + $policy[ $scope ]['window'];

	set_transient(
		$key,
		array( 'count' => $bucket['count'] + 1, 'reset' => $reset ),
		max( MINUTE_IN_SECONDS, $reset - time() )
	);
}

/** Clear a bucket after successful authentication or during a test cleanup. */
function foxfire_operations_clear_auth_bucket( string $scope, string $identifier ): void {
	delete_transient( foxfire_operations_auth_bucket_key( $scope, $identifier ) );
}

/** Normalize a submitted login without persisting the raw value. */
function foxfire_operations_auth_identity( string $login ): string {
	$normalized = strtolower( trim( sanitize_text_field( $login ) ) );
	return '' !== $normalized ? $normalized : 'empty';
}

/** Reject a password check before expensive hashing after repeated failures. */
function foxfire_operations_throttle_login( $user, string $username, string $password ) {
	unset( $password );

	$identity = foxfire_operations_auth_identity( $username );
	$ip       = foxfire_operations_request_ip();
	if ( foxfire_operations_auth_bucket_is_limited( 'login_ip', $ip ) || foxfire_operations_auth_bucket_is_limited( 'login_identity', $identity ) ) {
		return new WP_Error(
			'foxfire_login_throttled',
			__( 'Sign-in is temporarily unavailable after repeated attempts. Please wait 15 minutes before trying again.', 'foxfire-operations' )
		);
	}

	return $user;
}
add_filter( 'authenticate', 'foxfire_operations_throttle_login', 5, 3 );

/** Replace account-specific credential failures with one neutral response. */
function foxfire_operations_normalize_login_error( $user, string $username, string $password ) {
	unset( $username, $password );

	if ( ! is_wp_error( $user ) ) {
		return $user;
	}

	$enumerating_codes = array( 'invalid_username', 'invalid_email', 'incorrect_password' );
	if ( empty( array_intersect( $enumerating_codes, $user->get_error_codes() ) ) ) {
		return $user;
	}

	return new WP_Error(
		'foxfire_invalid_credentials',
		__( 'Sign-in failed. Check your credentials, or wait a few minutes if you have tried several times.', 'foxfire-operations' )
	);
}
add_filter( 'authenticate', 'foxfire_operations_normalize_login_error', 99, 3 );

/** Count failed logins by direct IP and non-reversible identity bucket. */
function foxfire_operations_record_login_failure( string $username, WP_Error $error ): void {
	unset( $error );
	foxfire_operations_record_auth_attempt( 'login_ip', foxfire_operations_request_ip() );
	foxfire_operations_record_auth_attempt( 'login_identity', foxfire_operations_auth_identity( $username ) );
}
add_action( 'wp_login_failed', 'foxfire_operations_record_login_failure', 10, 2 );

/** Clear only the successful identity bucket; retain IP failures against spraying. */
function foxfire_operations_clear_successful_login_limit( string $user_login, WP_User $user ): void {
	unset( $user );
	foxfire_operations_clear_auth_bucket( 'login_identity', foxfire_operations_auth_identity( $user_login ) );
}
add_action( 'wp_login', 'foxfire_operations_clear_successful_login_limit', 10, 2 );

/** Resolve a reset request without disclosing whether the account exists. */
function foxfire_operations_find_reset_user( string $login ) {
	$login = trim( sanitize_text_field( $login ) );
	$user  = get_user_by( 'login', $login );

	if ( ! $user && is_email( $login ) ) {
		$user = get_user_by( 'email', $login );
	}

	return $user instanceof WP_User ? $user : false;
}

/** Send a WooCommerce reset message only when the neutral request is eligible. */
function foxfire_operations_maybe_send_password_reset( string $login ): void {
	$identity = foxfire_operations_auth_identity( $login );
	$ip       = foxfire_operations_request_ip();
	$limited  = foxfire_operations_auth_bucket_is_limited( 'reset_ip', $ip ) || foxfire_operations_auth_bucket_is_limited( 'reset_identity', $identity );

	foxfire_operations_record_auth_attempt( 'reset_ip', $ip );
	foxfire_operations_record_auth_attempt( 'reset_identity', $identity );

	if ( $limited || ! class_exists( 'WC_Shortcode_My_Account' ) ) {
		return;
	}

	$user = foxfire_operations_find_reset_user( $login );
	if ( ! $user ) {
		return;
	}

	$errors = new WP_Error();
	do_action( 'lostpassword_post', $errors, $user );
	$allow = apply_filters( 'allow_password_reset', true, $user->ID );
	if ( $errors->has_errors() || ! $allow || is_wp_error( $allow ) ) {
		return;
	}

	do_action( 'retrieve_password', $user->user_login );
	$key = get_password_reset_key( $user );
	if ( is_wp_error( $key ) ) {
		return;
	}

	WC()->mailer();
	do_action( 'woocommerce_reset_password_notification', $user->user_login, $key );
}

/** Redirect to the same generic confirmation for valid, invalid, or limited reset requests. */
function foxfire_operations_password_reset_confirmation_redirect(): void {
	wp_safe_redirect( add_query_arg( 'reset-link-sent', 'true', wc_get_account_endpoint_url( 'lost-password' ) ) );
	exit;
}

/** Intercept the WooCommerce reset request before its account-enumerating handler. */
function foxfire_operations_process_customer_lost_password(): void {
	if ( ! isset( $_POST['wc_reset_password'], $_POST['user_login'] ) || ! is_string( $_POST['user_login'] ) ) {
		return;
	}

	$nonce = isset( $_POST['woocommerce-lost-password-nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['woocommerce-lost-password-nonce'] ) ) : '';
	if ( ! wp_verify_nonce( $nonce, 'lost_password' ) ) {
		return;
	}

	foxfire_operations_maybe_send_password_reset( wp_unslash( $_POST['user_login'] ) );
	foxfire_operations_password_reset_confirmation_redirect();
}
add_action( 'wp_loaded', 'foxfire_operations_process_customer_lost_password', 19 );

/** Route direct wp-login.php reset requests through the same neutral workflow. */
function foxfire_operations_route_core_lost_password(): void {
	if ( 'POST' === strtoupper( (string) ( $_SERVER['REQUEST_METHOD'] ?? 'GET' ) ) && isset( $_POST['user_login'] ) && is_string( $_POST['user_login'] ) ) {
		foxfire_operations_maybe_send_password_reset( wp_unslash( $_POST['user_login'] ) );
		foxfire_operations_password_reset_confirmation_redirect();
	}

	wp_safe_redirect( wc_get_account_endpoint_url( 'lost-password' ) );
	exit;
}
add_action( 'login_form_lostpassword', 'foxfire_operations_route_core_lost_password' );
add_action( 'login_form_retrievepassword', 'foxfire_operations_route_core_lost_password' );

/** Limit reset-link exposure if a mailbox or forwarded message is compromised. */
function foxfire_operations_password_reset_expiration(): int {
	return HOUR_IN_SECONDS;
}
add_filter( 'password_reset_expiration', 'foxfire_operations_password_reset_expiration' );

/**
 * Limit privileged WordPress sessions more aggressively than customer logins.
 *
 * Non-remembered cookies still close with the browser, but their server-side
 * token is capped at twelve hours. "Remember me" for an operator is capped at
 * two days. Customer lifetimes retain WordPress defaults (two/fourteen days).
 */
function foxfire_operations_privileged_auth_cookie_expiration( int $length, int $user_id, bool $remember ): int {
	$user = get_userdata( $user_id );
	if ( ! $user instanceof WP_User || empty( array_intersect( array( 'administrator', 'shop_manager' ), $user->roles ) ) ) {
		return $length;
	}

	return $remember ? 2 * DAY_IN_SECONDS : 12 * HOUR_IN_SECONDS;
}
add_filter( 'auth_cookie_expiration', 'foxfire_operations_privileged_auth_cookie_expiration', 20, 3 );
