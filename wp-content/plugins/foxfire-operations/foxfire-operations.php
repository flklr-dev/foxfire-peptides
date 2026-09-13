<?php
/**
 * Plugin Name: Foxfire Operations
 * Plugin URI:  https://foxfirepeptides.com
 * Description: Secure, theme-independent store operations and client administration for Foxfire Peptides.
 * Version:     0.13.0
 * Author:      Foxfire Peptides
 * Text Domain: foxfire-operations
 * Requires at least: 6.9
 * Requires PHP: 8.2
 * WC requires at least: 9.0
 * WC tested up to: 11.0
 *
 * @package Foxfire_Operations
 */

defined( 'ABSPATH' ) || exit;

define( 'FOXFIRE_OPERATIONS_VERSION', '0.13.0' );
define( 'FOXFIRE_OPERATIONS_ROLE_VERSION', '2' );
define( 'FOXFIRE_OPERATIONS_CONTENT_VERSION', '2' );
define( 'FOXFIRE_OPERATIONS_FILE', __FILE__ );
define( 'FOXFIRE_OPERATIONS_DIR', plugin_dir_path( __FILE__ ) );
define( 'FOXFIRE_OPERATIONS_URL', plugin_dir_url( __FILE__ ) );
define( 'FOXFIRE_OPERATIONS_CAPABILITY', 'foxfire_manage_operations' );
define( 'FOXFIRE_OPERATIONS_SENSITIVE_CAPABILITY', 'foxfire_manage_sensitive_settings' );

add_action(
	'before_woocommerce_init',
	static function (): void {
		if ( class_exists( '\\Automattic\\WooCommerce\\Utilities\\FeaturesUtil' ) ) {
			\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', FOXFIRE_OPERATIONS_FILE, true );
		}
	}
);

// Defense in depth: editing PHP from wp-admin is never required for store operations.
if ( ! defined( 'DISALLOW_FILE_EDIT' ) ) {
	define( 'DISALLOW_FILE_EDIT', true );
}

require_once FOXFIRE_OPERATIONS_DIR . 'includes/roles.php';
require_once FOXFIRE_OPERATIONS_DIR . 'includes/security.php';
require_once FOXFIRE_OPERATIONS_DIR . 'includes/customer-auth.php';
require_once FOXFIRE_OPERATIONS_DIR . 'includes/local-mail.php';
require_once FOXFIRE_OPERATIONS_DIR . 'includes/pricing.php';
require_once FOXFIRE_OPERATIONS_DIR . 'includes/product-admin.php';
require_once FOXFIRE_OPERATIONS_DIR . 'includes/quantity-admin.php';
require_once FOXFIRE_OPERATIONS_DIR . 'includes/promotions.php';
require_once FOXFIRE_OPERATIONS_DIR . 'includes/orders.php';
require_once FOXFIRE_OPERATIONS_DIR . 'includes/homepage-merchandising.php';
require_once FOXFIRE_OPERATIONS_DIR . 'includes/content-admin.php';
require_once FOXFIRE_OPERATIONS_DIR . 'includes/seo.php';
require_once FOXFIRE_OPERATIONS_DIR . 'includes/store-settings.php';
require_once FOXFIRE_OPERATIONS_DIR . 'includes/admin.php';

register_activation_hook( __FILE__, 'foxfire_operations_activate' );
register_deactivation_hook( __FILE__, 'foxfire_operations_deactivate' );

/**
 * Install role capabilities and persist the schema version.
 */
function foxfire_operations_activate(): void {
	foxfire_operations_sync_role_capabilities();
	update_option( 'foxfire_operations_role_version', FOXFIRE_OPERATIONS_ROLE_VERSION, false );
}

/**
 * Remove only capabilities owned by this plugin.
 */
function foxfire_operations_deactivate(): void {
	foxfire_operations_remove_role_capabilities();
	delete_option( 'foxfire_operations_role_version' );
}

/**
 * Re-apply capabilities after WooCommerce creates or repairs its roles.
 */
function foxfire_operations_maybe_sync_roles(): void {
	if ( FOXFIRE_OPERATIONS_ROLE_VERSION === get_option( 'foxfire_operations_role_version' ) ) {
		return;
	}

	foxfire_operations_sync_role_capabilities();
	update_option( 'foxfire_operations_role_version', FOXFIRE_OPERATIONS_ROLE_VERSION, false );
}
add_action( 'init', 'foxfire_operations_maybe_sync_roles', 20 );
add_action( 'woocommerce_installed', 'foxfire_operations_sync_role_capabilities', 20 );
add_action( 'woocommerce_updated', 'foxfire_operations_sync_role_capabilities', 20 );

/**
 * Show a clear dependency notice without exposing configuration details.
 */
function foxfire_operations_woocommerce_notice(): void {
	if ( class_exists( 'WooCommerce' ) || ! current_user_can( 'activate_plugins' ) ) {
		return;
	}

	echo '<div class="notice notice-error"><p>';
	echo esc_html__( 'Foxfire Operations requires WooCommerce to be installed and active.', 'foxfire-operations' );
	echo '</p></div>';
}
add_action( 'admin_notices', 'foxfire_operations_woocommerce_notice' );
