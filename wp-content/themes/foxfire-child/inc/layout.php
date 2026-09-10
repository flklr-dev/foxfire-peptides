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
 * Read client-managed plain text with a theme fallback.
 *
 * The visual theme remains functional when Foxfire Operations is unavailable.
 *
 * @param string $key      Registered content key.
 * @param string $fallback Built-in theme wording.
 * @return string
 */
function foxfire_get_managed_content( string $key, string $fallback ): string {
	return function_exists( 'foxfire_operations_get_public_content' )
		? foxfire_operations_get_public_content( $key, $fallback )
		: $fallback;
}

/** Whether the current policy page explicitly uses its revisioned editor body. */
function foxfire_should_render_editor_page(): bool {
	$page_id = get_queried_object_id();
	return $page_id > 0
		&& function_exists( 'foxfire_operations_should_use_editor_content' )
		&& foxfire_operations_should_use_editor_content( $page_id );
}

/** Render approved editor content in the existing Foxfire policy visual shell. */
function foxfire_render_editor_policy_page(): void {
	$page_id = get_queried_object_id();
	$content = apply_filters( 'the_content', get_post_field( 'post_content', $page_id ) );
	?>
	<main id="main-content" class="ff-policy-page ff-managed-policy-page" tabindex="-1">
		<div class="ff-policy-page__inner">
			<header class="ff-policy-header">
				<p class="ff-policy-header__label"><?php esc_html_e( 'Legal', 'foxfire-child' ); ?></p>
				<h1 class="ff-policy-header__title"><?php echo esc_html( get_the_title( $page_id ) ); ?></h1>
				<div class="ff-policy-header__meta">
					<span class="ff-policy-meta-item"><?php printf( esc_html__( 'Last updated: %s', 'foxfire-child' ), esc_html( get_the_modified_date( '', $page_id ) ) ); ?></span>
				</div>
			</header>
			<article class="ff-policy-section ff-managed-policy-page__content">
				<?php echo wp_kses_post( $content ); ?>
			</article>
		</div>
	</main>
	<?php
}

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
