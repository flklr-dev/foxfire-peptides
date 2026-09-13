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
foreach ( array( 'recovery-healing' => 'Peptide Research', 'glp-1-agonists' => 'GLP-1 Research' ) as $slug => $name ) {
	$term = get_term_by( 'slug', $slug, 'product_cat' );
	$check( $term instanceof WP_Term && $name === $term->name, 'The category label is correct and its original slug is retained: ' . $name );
}
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
