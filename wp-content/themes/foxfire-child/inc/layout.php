<?php
/**
 * Global content layout — full-width, no blog sidebar.
 *
 * Phase 1 has no blog. Storefront defaults to a right sidebar with WordPress
 * starter widgets (Recent Posts, Archives, Categories), which is not part of
 * the Foxfire information architecture (DESIGN.md §5, §8).
 *
 * @package Foxfire_Child
 */

defined( 'ABSPATH' ) || exit;

/**
 * Remove the Storefront sidebar everywhere.
 */
function foxfire_layout_remove_sidebar(): void {
	remove_action( 'storefront_sidebar', 'storefront_get_sidebar', 10 );
}
add_action( 'wp', 'foxfire_layout_remove_sidebar', 5 );

/**
 * Force full-width body classes so content is not reserved for a sidebar.
 *
 * @param string[] $classes Body classes.
 * @return string[]
 */
function foxfire_layout_body_classes( array $classes ): array {
	$classes = array_values(
		array_diff( $classes, array( 'right-sidebar', 'left-sidebar' ) )
	);

	$classes[] = 'storefront-full-width-content';
	$classes[] = 'ff-full-width';

	return $classes;
}
add_filter( 'body_class', 'foxfire_layout_body_classes', 20 );

/**
 * Strip the internal "(TBD)" marker from product category names on the
 * frontend. The suffix tracks provisional taxonomy (PRD §5.2) for the team and
 * must not appear in customer-facing output such as breadcrumbs or product meta.
 *
 * Note: the `get_term` and `get_{$taxonomy}` filters both receive a WP_Term
 * object, not a string.
 *
 * @param WP_Term|mixed $term Term object.
 * @return WP_Term|mixed
 */
function foxfire_layout_clean_term_object( $term ) {
	if ( is_admin() || ! ( $term instanceof WP_Term ) ) {
		return $term;
	}

	if ( 'product_cat' !== $term->taxonomy || ! function_exists( 'foxfire_get_category_display_name' ) ) {
		return $term;
	}

	$term->name = foxfire_get_category_display_name( $term->name );

	return $term;
}
add_filter( 'get_term', 'foxfire_layout_clean_term_object' );

/**
 * Clean the archive title, which is rendered from a string rather than a term.
 *
 * @param string|mixed $title Archive title.
 * @return string|mixed
 */
function foxfire_layout_clean_term_title( $title ) {
	if ( ! is_string( $title ) || ! is_tax( 'product_cat' ) ) {
		return $title;
	}

	return function_exists( 'foxfire_get_category_display_name' )
		? foxfire_get_category_display_name( $title )
		: $title;
}
add_filter( 'single_term_title', 'foxfire_layout_clean_term_title' );

/**
 * Hide Storefront's page/post title block on WooCommerce pages that render
 * their own header (the shop archive supplies a catalog header instead).
 */
function foxfire_layout_disable_wc_page_title(): bool {
	return false;
}
add_filter( 'woocommerce_show_page_title', 'foxfire_layout_disable_wc_page_title' );
