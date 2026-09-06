<?php
/**
 * Security boundaries for Foxfire store operators.
 *
 * @package Foxfire_Operations
 */

defined( 'ABSPATH' ) || exit;

/**
 * Sensitive WooCommerce admin pages reserved for Administrators.
 *
 * @return string[]
 */
function foxfire_operations_sensitive_admin_pages(): array {
	return array(
		'wc-settings',
		'wc-status',
		'wc-addons',
		'wc-admin-settings',
	);
}

/**
 * Sensitive WooCommerce Admin SPA paths reserved for Administrators.
 *
 * @return string[]
 */
function foxfire_operations_sensitive_wc_admin_paths(): array {
	return array(
		'/extensions',
		'/payments',
		'/settings',
		'/setup-wizard',
	);
}

/**
 * Determine whether the current wp-admin request targets sensitive configuration.
 */
function foxfire_operations_is_sensitive_admin_request(): bool {
	// Read-only routing values do not require a nonce. Mutations are blocked before handlers run.
	$page = isset( $_GET['page'] ) ? sanitize_key( wp_unslash( $_GET['page'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	if ( in_array( $page, foxfire_operations_sensitive_admin_pages(), true ) ) {
		return true;
	}

	if ( 'wc-admin' !== $page ) {
		return false;
	}

	$path = isset( $_GET['path'] ) ? sanitize_text_field( wp_unslash( $_GET['path'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	foreach ( foxfire_operations_sensitive_wc_admin_paths() as $prefix ) {
		if ( str_starts_with( $path, $prefix ) ) {
			return true;
		}

	}

	return false;
}

/**
 * Block direct URL and form-post access before WooCommerce settings handlers run.
 */
function foxfire_operations_protect_sensitive_admin_pages(): void {
	if ( ! foxfire_operations_is_restricted_manager() || ! foxfire_operations_is_sensitive_admin_request() ) {
		return;
	}

	wp_die(
		esc_html__( 'This area contains sensitive store configuration and is restricted to site administrators.', 'foxfire-operations' ),
		esc_html__( 'Access denied', 'foxfire-operations' ),
		array( 'response' => 403 )
	);
}
add_action( 'admin_init', 'foxfire_operations_protect_sensitive_admin_pages', -100 );

/**
 * Hide sensitive menus as a usability measure. Direct requests remain blocked above.
 */
function foxfire_operations_hide_sensitive_menus(): void {
	if ( ! foxfire_operations_is_restricted_manager() ) {
		return;
	}

	remove_submenu_page( 'woocommerce', 'wc-settings' );
	remove_submenu_page( 'woocommerce', 'wc-status' );
	remove_submenu_page( 'woocommerce', 'wc-addons' );
}
add_action( 'admin_menu', 'foxfire_operations_hide_sensitive_menus', 999 );

/**
 * WooCommerce AJAX actions that mutate sensitive configuration.
 *
 * @return string[]
 */
function foxfire_operations_sensitive_ajax_actions(): array {
	return array(
		'woocommerce_shipping_classes_save_changes',
		'woocommerce_shipping_providers_save_changes',
		'woocommerce_shipping_zone_add_method',
		'woocommerce_shipping_zone_methods_save_changes',
		'woocommerce_shipping_zone_methods_save_settings',
		'woocommerce_shipping_zone_remove_method',
		'woocommerce_shipping_zones_save_changes',
		'woocommerce_tax_rates_save_changes',
		'woocommerce_toggle_gateway_enabled',
		'woocommerce_update_api_key',
	);
}

/**
 * Stop sensitive admin-ajax mutations even if a restricted manager obtains a valid WC nonce.
 */
function foxfire_operations_protect_sensitive_ajax(): void {
	if ( ! foxfire_operations_is_restricted_manager() ) {
		return;
	}

	wp_send_json_error(
		array( 'message' => __( 'Administrator permission is required for this operation.', 'foxfire-operations' ) ),
		403
	);
}

foreach ( foxfire_operations_sensitive_ajax_actions() as $foxfire_operations_ajax_action ) {
	add_action( 'wp_ajax_' . $foxfire_operations_ajax_action, 'foxfire_operations_protect_sensitive_ajax', -100 );
}
unset( $foxfire_operations_ajax_action );

/**
 * Identify REST routes that may expose or change secrets and global store settings.
 */
function foxfire_operations_is_sensitive_rest_route( string $route ): bool {
	$patterns = array(
		'#^/wc/v[0-9]+/(?:settings|payment_gateways|shipping|system_status|webhooks)(?:/|$)#',
		'#^/wc-admin/(?:options|onboarding|payment-gateway-suggestions|plugins|themes)(?:/|$)#',
		'#^/wc/v[0-9]+/data/currencies/current(?:/|$)#',
	);

	foreach ( $patterns as $pattern ) {
		if ( 1 === preg_match( $pattern, $route ) ) {
			return true;
		}
	}

	return false;
}

/**
 * Deny sensitive WooCommerce REST callbacks for routine operators.
 *
 * This runs after authentication/route permission checks and immediately before
 * the endpoint callback, preventing reads and writes through application passwords.
 *
 * @param WP_HTTP_Response|WP_Error|null $response Existing response.
 * @param array<string, mixed>           $handler  Route handler.
 * @param WP_REST_Request                $request  Current request.
 * @return WP_HTTP_Response|WP_Error|null
 */
function foxfire_operations_protect_sensitive_rest( $response, array $handler, WP_REST_Request $request ) {
	unset( $handler );

	if ( ! foxfire_operations_is_restricted_manager() || ! foxfire_operations_is_sensitive_rest_route( $request->get_route() ) ) {
		return $response;
	}

	return new WP_Error(
		'foxfire_sensitive_settings_forbidden',
		__( 'Administrator permission is required for sensitive store settings.', 'foxfire-operations' ),
		array( 'status' => 403 )
	);
}
add_filter( 'rest_request_before_callbacks', 'foxfire_operations_protect_sensitive_rest', 5, 3 );

/**
 * Disable WordPress application passwords for routine Shop Managers.
 *
 * Re-enable later only for a documented, narrowly scoped integration. Normal
 * browser logins and Administrator integrations are unaffected.
 *
 * @param bool    $available Current availability.
 * @param WP_User $user      User being evaluated.
 */
function foxfire_operations_limit_application_passwords( bool $available, WP_User $user ): bool {
	if ( user_can( $user, FOXFIRE_OPERATIONS_CAPABILITY ) && ! user_can( $user, FOXFIRE_OPERATIONS_SENSITIVE_CAPABILITY ) ) {
		return false;
	}

	return $available;
}
add_filter( 'wp_is_application_passwords_available_for_user', 'foxfire_operations_limit_application_passwords', 20, 2 );
