<?php
/**
 * Homepage (Front Page) customizations & data providers — Chunk 1H.
 *
 * @package Foxfire_Child
 */

defined( 'ABSPATH' ) || exit;

/**
 * Enqueue homepage-specific assets on the front page.
 */
function foxfire_homepage_enqueue_assets(): void {
	if ( ! is_front_page() && ! is_home() ) {
		return;
	}

	$css_file = FOXFIRE_CHILD_DIR . '/assets/css/homepage.css';
	$js_file  = FOXFIRE_CHILD_DIR . '/assets/js/homepage.js';

	wp_enqueue_style(
		'foxfire-homepage',
		FOXFIRE_CHILD_URI . '/assets/css/homepage.css',
		array( 'foxfire-base', 'foxfire-woocommerce' ),
		file_exists( $css_file ) ? (string) filemtime( $css_file ) : FOXFIRE_CHILD_VERSION
	);

	wp_enqueue_script(
		'foxfire-homepage',
		FOXFIRE_CHILD_URI . '/assets/js/homepage.js',
		array(),
		file_exists( $js_file ) ? (string) filemtime( $js_file ) : FOXFIRE_CHILD_VERSION,
		true
	);
}
add_action( 'wp_enqueue_scripts', 'foxfire_homepage_enqueue_assets', 30 );

/**
 * Query featured or latest products for the homepage showcase.
 *
 * @param int $limit Number of products to return.
 * @return WP_Query
 */
function foxfire_get_homepage_products( int $limit = 8 ): WP_Query {
	return new WP_Query(
		array(
			'post_type'      => 'product',
			'post_status'    => 'publish',
			'posts_per_page' => $limit,
			'orderby'        => 'date',
			'order'          => 'DESC',
		)
	);
}

/**
 * Get category cards data for the homepage category entry grid.
 *
 * @return array<int, array<string, mixed>>
 */
function foxfire_get_homepage_categories(): array {
	if ( ! taxonomy_exists( 'product_cat' ) ) {
		return array();
	}

	$terms = get_terms(
		array(
			'taxonomy'   => 'product_cat',
			'hide_empty' => false,
			'parent'     => 0,
			'orderby'    => 'count',
			'order'      => 'DESC',
		)
	);

	if ( is_wp_error( $terms ) || empty( $terms ) ) {
		return array();
	}

	$cards = array();
	$icons = array(
		'glp-1-agonists'    => '<svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2v20M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>',
		'recovery-healing'  => '<svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 12h-4l-3 9L9 3l-3 9H2"/></svg>',
		'blends'            => '<svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="8" cy="8" r="6"/><circle cx="16" cy="16" r="6"/></svg>',
		'support-compounds' => '<svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>',
	);

	foreach ( $terms as $term ) {
		if ( ! $term instanceof WP_Term || 'uncategorized' === $term->slug ) {
			continue;
		}

		$display_name = function_exists( 'foxfire_get_category_display_name' )
			? foxfire_get_category_display_name( $term->name )
			: str_replace( ' (TBD)', '', $term->name );

		$icon = $icons[ $term->slug ] ?? '<svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="18" height="18" x="3" y="3" rx="2"/><path d="M3 9h18M9 21V9"/></svg>';

		$cards[] = array(
			'term_id' => $term->term_id,
			'name'    => $display_name,
			'slug'    => $term->slug,
			'count'   => (int) $term->count,
			'link'    => get_term_link( $term ),
			'icon'    => $icon,
		);
	}

	return array_slice( $cards, 0, 4 );
}

/**
 * Ensure the custom front-page template is ALWAYS loaded on the site homepage,
 * regardless of WordPress show_on_front or page_on_front settings.
 *
 * @param string $template Template path.
 * @return string
 */
function foxfire_homepage_template_include( string $template ): string {
	if ( is_front_page() || ( is_home() && ! is_paged() ) ) {
		$front_page = FOXFIRE_CHILD_DIR . '/front-page.php';
		if ( file_exists( $front_page ) ) {
			return $front_page;
		}
	}
	return $template;
}
add_filter( 'template_include', 'foxfire_homepage_template_include', 99 );

/**
 * Suppress breadcrumbs on the homepage.
 */
function foxfire_homepage_remove_breadcrumbs(): void {
	if ( is_front_page() || is_home() ) {
		remove_action( 'storefront_before_content', 'woocommerce_breadcrumb', 10 );
	}
}
add_action( 'wp', 'foxfire_homepage_remove_breadcrumbs', 10 );

