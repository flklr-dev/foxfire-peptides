<?php
/** Local read-only checks for the approved catalog review. */
defined( 'ABSPATH' ) || exit;
if ( 'local' !== wp_get_environment_type() ) {
	WP_CLI::error( 'Run catalog review checks only in the local environment.' );
}
$check = static function ( bool $passed, string $message ): void {
	if ( ! $passed ) {
		WP_CLI::error( $message );
	}
	WP_CLI::log( 'PASS: ' . $message );
};
$check( -1 === foxfire_products_per_page(), 'The catalog has no per-page product limit' );
$categories = array(
	'metabolic-research'    => array(
		'name'        => 'Metabolic Research',
		'description' => 'Compounds for laboratory metabolic research.',
	),
	'regenerative-research' => array(
		'name'        => 'Regenerative Research',
		'description' => 'Compounds for laboratory regenerative research.',
	),
	'specialty-research'    => array(
		'name'        => 'Specialty Research',
		'description' => 'Specialized compounds for laboratory research.',
	),
);
$legacy_slugs  = array( 'glp-1-agonists', 'recovery-healing', 'support-compounds' );
$legacy_labels = array( 'GLP-1 Agonists', 'GLP-1 Research', 'Recovery & Healing', 'Peptide Research', 'Support Compounds' );
foreach ( $categories as $slug => $category ) {
	$term = get_term_by( 'slug', $slug, 'product_cat' );
	$check( $term instanceof WP_Term && $category['name'] === $term->name, 'The category label and canonical slug are correct: ' . $category['name'] );
	$check( $term instanceof WP_Term && $category['description'] === $term->description, 'The category description matches the revised terminology: ' . $category['name'] );
	$check( $term instanceof WP_Term && $term->count > 0, 'The renamed category retains its product assignments: ' . $category['name'] );
}
foreach ( $legacy_slugs as $legacy_slug ) {
	$check( false === get_term_by( 'slug', $legacy_slug, 'product_cat' ), 'The legacy category slug is no longer canonical: ' . $legacy_slug );
}

ob_start();
foxfire_render_category_pills();
$category_pills = ob_get_clean();
foreach ( array_merge( array( 'All Compounds', 'Blends' ), array_column( $categories, 'name' ) ) as $label ) {
	$check( str_contains( $category_pills, esc_html( $label ) ), 'The Shop filter displays the category label: ' . $label );
}
foreach ( $categories as $slug => $category ) {
	$term      = get_term_by( 'slug', $slug, 'product_cat' );
	$term_link = $term instanceof WP_Term ? get_term_link( $term ) : new WP_Error( 'missing_term' );
	$check(
		! is_wp_error( $term_link ) && str_contains( $category_pills, 'href="' . esc_url( $term_link ) . '"' ),
		'The Shop filter links directly to the canonical category URL: ' . $category['name']
	);
}
foreach ( $legacy_labels as $label ) {
	$check( ! str_contains( $category_pills, esc_html( $label ) ), 'The Shop filter omits the superseded category label: ' . $label );
}

ob_start();
get_template_part( 'template-parts/header/site-header' );
$header_html = ob_get_clean();
$home_position = strpos( $header_html, 'Home' );
$testing_position = strpos( $header_html, 'Testing &amp; COAs' );
$check( false !== $home_position && false !== $testing_position && $home_position < $testing_position, 'Home appears before Testing & COAs in the primary navigation' );

$previous_showcase = wc_get_loop_prop( 'foxfire_catalog_showcase', false );
$previous_product = $GLOBALS['product'] ?? null;
$previous_post = $GLOBALS['post'] ?? null;
try {
	wc_set_loop_prop( 'foxfire_catalog_showcase', true );
	foreach ( array( 'simple', 'variable' ) as $type ) {
		$products = wc_get_products( array( 'status' => 'publish', 'type' => $type, 'limit' => 1 ) );
		$check( ! empty( $products ), 'A public ' . $type . ' product is available for verification' );
		$product = $products[0];
		$GLOBALS['product'] = $product;
		$GLOBALS['post'] = get_post( $product->get_id() );
		setup_postdata( $GLOBALS['post'] );
		ob_start();
		wc_get_template_part( 'content', 'product' );
		$html = ob_get_clean();
		$check( str_contains( $html, 'href="' . esc_url( $product->get_permalink() ) . '" class="button ff-product-card__view-product"' ), 'The ' . $type . ' catalog card links to its existing product page' );
		$check( ! str_contains( $html, 'ajax_add_to_cart' ) && ! str_contains( $html, '?add-to-cart=' ), 'The ' . $type . ' catalog card cannot add directly to the cart' );
		$check( 'Add to Cart' === $product->single_add_to_cart_text(), 'The actual ' . $type . ' product-page button still says Add to Cart' );
	}
} finally {
	wc_set_loop_prop( 'foxfire_catalog_showcase', $previous_showcase );
	wp_reset_postdata();
	$GLOBALS['product'] = $previous_product;
	$GLOBALS['post'] = $previous_post;
}
WP_CLI::success( 'Catalog checks passed without changing saved products, users or options.' );
