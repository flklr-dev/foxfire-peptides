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
	if ( ! is_front_page() && ! is_home() && ! is_page_template( 'page-faq.php' ) ) {
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
 * Filter candidate homepage IDs to public products customers can currently buy.
 *
 * @param int[] $candidate_ids Candidate product IDs in display order.
 * @param int   $limit         Maximum IDs to return.
 * @return int[]
 */
function foxfire_homepage_filter_available_product_ids( array $candidate_ids, int $limit ): array {
	$available_ids = array();
	foreach ( array_map( 'absint', $candidate_ids ) as $product_id ) {
		if ( $product_id <= 0 || in_array( $product_id, $available_ids, true ) || 'product' !== get_post_type( $product_id ) || 'publish' !== get_post_status( $product_id ) ) {
			continue;
		}

		$product = wc_get_product( $product_id );
		if (
			$product instanceof WC_Product
			&& 'hidden' !== $product->get_catalog_visibility()
			&& $product->is_in_stock()
			&& $product->is_purchasable()
		) {
			$available_ids[] = $product_id;
		}

		if ( count( $available_ids ) >= $limit ) {
			break;
		}
	}

	return $available_ids;
}

/**
 * Query administrator-prioritized, featured, or latest products for the
 * homepage showcase, in that order.
 *
 * @param int $limit Number of products to return.
 * @return WP_Query
 */
function foxfire_get_homepage_products( int $limit = 8 ): WP_Query {
	$limit = max( 1, min( 8, $limit ) );
	$ids   = function_exists( 'foxfire_operations_get_homepage_product_ids' )
		? foxfire_operations_get_homepage_product_ids( true )
		: array();

	if ( empty( $ids ) && function_exists( 'wc_get_featured_product_ids' ) ) {
		$ids = foxfire_homepage_filter_available_product_ids( wc_get_featured_product_ids(), $limit );
	}

	if ( ! empty( $ids ) ) {
		return new WP_Query(
			array(
				'post_type'           => 'product',
				'post_status'         => 'publish',
				'post__in'            => array_slice( $ids, 0, $limit ),
				'posts_per_page'      => $limit,
				'orderby'             => 'post__in',
				'ignore_sticky_posts' => true,
				'no_found_rows'       => true,
			)
		);
	}

	$recent_query = new WP_Query(
		array(
			'post_type'              => 'product',
			'post_status'            => 'publish',
			'posts_per_page'         => max( 32, $limit * 4 ),
			'orderby'                => 'date',
			'order'                  => 'DESC',
			'fields'                 => 'ids',
			'ignore_sticky_posts'    => true,
			'no_found_rows'          => true,
			'update_post_meta_cache' => false,
			'update_post_term_cache' => false,
		)
	);
	$ids = foxfire_homepage_filter_available_product_ids( $recent_query->posts, $limit );

	return new WP_Query(
		array(
			'post_type'           => 'product',
			'post_status'         => 'publish',
			'post__in'            => ! empty( $ids ) ? $ids : array( 0 ),
			'posts_per_page'      => $limit,
			'orderby'             => 'post__in',
			'ignore_sticky_posts' => true,
			'no_found_rows'       => true,
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
 * Suppress breadcrumbs on the homepage.
 */
function foxfire_homepage_remove_breadcrumbs(): void {
	if ( is_front_page() || is_home() ) {
		remove_action( 'storefront_before_content', 'woocommerce_breadcrumb', 10 );
	}
}
add_action( 'wp', 'foxfire_homepage_remove_breadcrumbs', 10 );
