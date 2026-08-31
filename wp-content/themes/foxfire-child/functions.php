<?php
/**
 * Foxfire Peptides child theme bootstrap.
 *
 * @package Foxfire_Child
 */

defined( 'ABSPATH' ) || exit;

define( 'FOXFIRE_CHILD_VERSION', '1.2.0' );
define( 'FOXFIRE_CHILD_DIR', get_stylesheet_directory() );
define( 'FOXFIRE_CHILD_URI', get_stylesheet_directory_uri() );

require_once FOXFIRE_CHILD_DIR . '/inc/shell.php';
require_once FOXFIRE_CHILD_DIR . '/inc/layout.php';
require_once FOXFIRE_CHILD_DIR . '/inc/product-fields.php';
require_once FOXFIRE_CHILD_DIR . '/inc/shop.php';
require_once FOXFIRE_CHILD_DIR . '/inc/pdp.php';
require_once FOXFIRE_CHILD_DIR . '/inc/homepage.php';
require_once FOXFIRE_CHILD_DIR . '/inc/testing-coa.php';
require_once FOXFIRE_CHILD_DIR . '/inc/cart.php';
require_once FOXFIRE_CHILD_DIR . '/inc/checkout.php';
require_once FOXFIRE_CHILD_DIR . '/inc/account.php';

/**
 * Enqueue parent theme, fonts, and Foxfire design system styles.
 */
function foxfire_child_enqueue_assets(): void {
	$parent_handle = 'storefront-style';
	$parent_theme  = wp_get_theme( 'storefront' );

	wp_enqueue_style(
		$parent_handle,
		get_template_directory_uri() . '/style.css',
		array(),
		$parent_theme->get( 'Version' )
	);

	wp_enqueue_style(
		'foxfire-fonts',
		'https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,500;0,9..40,600;0,9..40,700;1,9..40,400&family=Source+Sans+3:ital,wght@0,400;0,600;0,700;1,400&display=swap',
		array(),
		null
	);

	$styles = array(
		'foxfire-tokens'      => 'assets/css/tokens.css',
		'foxfire-base'        => 'assets/css/base.css',
		'foxfire-woocommerce' => 'assets/css/woocommerce.css',
	);

	foreach ( $styles as $handle => $relative_path ) {
		$path = FOXFIRE_CHILD_DIR . '/' . $relative_path;
		wp_enqueue_style(
			$handle,
			FOXFIRE_CHILD_URI . '/' . $relative_path,
			array( $parent_handle, 'foxfire-fonts' ),
			file_exists( $path ) ? (string) filemtime( $path ) : FOXFIRE_CHILD_VERSION
		);
	}
}
add_action( 'wp_enqueue_scripts', 'foxfire_child_enqueue_assets', 20 );

/**
 * Declare WooCommerce support explicitly (parent also does; kept for clarity).
 */
function foxfire_child_woocommerce_support(): void {
	add_theme_support( 'woocommerce' );
	add_theme_support( 'wc-product-gallery-zoom' );
	add_theme_support( 'wc-product-gallery-lightbox' );
	add_theme_support( 'wc-product-gallery-slider' );
}
add_action( 'after_setup_theme', 'foxfire_child_woocommerce_support' );
