<?php
/**
 * Conservative front-end performance controls.
 *
 * @package Foxfire_Child
 */

defined( 'ABSPATH' ) || exit;

/**
 * Whether this request contains catalog/cart interactions that need WooCommerce
 * front-end state and add-to-cart JavaScript.
 */
function foxfire_has_commerce_interactions(): bool {
	if ( is_front_page() || is_home() ) {
		return true;
	}

	if ( function_exists( 'is_woocommerce' ) && is_woocommerce() ) {
		return true;
	}

	if ( ( function_exists( 'is_cart' ) && is_cart() )
		|| ( function_exists( 'is_checkout' ) && is_checkout() )
		|| ( function_exists( 'is_account_page' ) && is_account_page() ) ) {
		return true;
	}

	return is_search() && 'product' === get_query_var( 'post_type' );
}

/**
 * Remove assets whose matching Storefront/WooCommerce UI has been replaced.
 *
 * Handles are dequeued, not deregistered, so an integration may deliberately
 * enqueue them again at a later priority if it introduces the matching block.
 */
function foxfire_performance_dequeue_unused_assets(): void {
	$unused_styles = array(
		'wc-blocks-style',
		'storefront-gutenberg-blocks',
		'storefront-fonts',
		'storefront-child-style',
	);

	foreach ( $unused_styles as $handle ) {
		wp_dequeue_style( $handle );
	}

	$replaced_storefront_scripts = array(
		'storefront-navigation',
		'storefront-header-cart',
		'storefront-handheld-footer-bar',
	);

	foreach ( $replaced_storefront_scripts as $handle ) {
		wp_dequeue_script( $handle );
	}

	if ( ! foxfire_has_commerce_interactions() ) {
		$commerce_only_scripts = array(
			'wc-jquery-blockui',
			'wc-add-to-cart',
			'wc-js-cookie',
			'woocommerce',
			'wc-cart-fragments',
		);

		foreach ( $commerce_only_scripts as $handle ) {
			wp_dequeue_script( $handle );
		}
	}
}
add_action( 'wp_enqueue_scripts', 'foxfire_performance_dequeue_unused_assets', 100 );

/**
 * Opt project-owned scripts into WordPress's dependency-aware defer strategy.
 */
function foxfire_performance_defer_project_scripts(): void {
	$handles = array(
		'foxfire-shell',
		'foxfire-homepage',
		'foxfire-shop-js',
		'foxfire-pdp',
		'foxfire-atc-modal',
		'foxfire-cart-js',
		'foxfire-checkout-js',
		'foxfire-account',
		'foxfire-testing-coa',
	);

	foreach ( $handles as $handle ) {
		if ( wp_script_is( $handle, 'enqueued' ) ) {
			wp_script_add_data( $handle, 'strategy', 'defer' );
		}
	}
}
add_action( 'wp_enqueue_scripts', 'foxfire_performance_defer_project_scripts', 110 );

/**
 * Preload the heading font used by the above-the-fold page title.
 */
function foxfire_performance_preload_heading_font(): void {
	$font_path = FOXFIRE_CHILD_DIR . '/assets/fonts/dm-sans-normal-latin.woff2';
	if ( ! file_exists( $font_path ) ) {
		return;
	}

	echo '<link rel="preload" href="' . esc_url( FOXFIRE_CHILD_URI . '/assets/fonts/dm-sans-normal-latin.woff2' ) . '" as="font" type="font/woff2" crossorigin>' . "\n";
}
add_action( 'wp_head', 'foxfire_performance_preload_heading_font', 1 );

/**
 * Generate modern WebP sub-sizes for future JPEG media uploads when supported.
 *
 * @param array<string, string> $formats  Output format map.
 * @param string|null           $filename Source filename, when available.
 * @param string|null           $mime_type Source MIME type, when available.
 * @return array<string, string>
 */
function foxfire_performance_modern_image_subsizes( array $formats, ?string $filename, ?string $mime_type ): array {
	unset( $filename );

	if ( 'image/jpeg' === $mime_type && wp_image_editor_supports( array( 'mime_type' => 'image/webp' ) ) ) {
		$formats['image/jpeg'] = 'image/webp';
	}

	return $formats;
}
add_filter( 'image_editor_output_format', 'foxfire_performance_modern_image_subsizes', 10, 3 );

/**
 * Remove legacy emoji discovery code; native emoji rendering remains intact.
 */
function foxfire_performance_disable_emoji_assets(): void {
	remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
	remove_action( 'wp_print_styles', 'print_emoji_styles' );
	remove_action( 'wp_enqueue_scripts', 'wp_enqueue_emoji_styles' );
}
add_action( 'init', 'foxfire_performance_disable_emoji_assets' );
add_filter( 'emoji_svg_url', '__return_false' );
