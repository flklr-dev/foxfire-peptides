<?php
/** Local, self-cleaning checks for native main-image replacement and inheritance. */
defined( 'ABSPATH' ) || exit;
if ( 'local' !== wp_get_environment_type() ) {
	throw new RuntimeException( 'Product image acceptance tests run only locally.' );
}
$ids = array();
$previous_product = $GLOBALS['product'] ?? null;
$previous_post = $GLOBALS['post'] ?? null;
$previous_home = wc_get_loop_prop( 'foxfire_homepage_showcase', false );
$previous_shop = wc_get_loop_prop( 'foxfire_catalog_showcase', false );
$check = static function ( bool $passed, string $label ): void {
	if ( ! $passed ) throw new RuntimeException( 'FAIL: ' . $label );
	WP_CLI::log( 'PASS: ' . $label );
};
$render = static function ( WC_Product $product, string $surface ): string {
	$GLOBALS['product'] = $product;
	$GLOBALS['post'] = get_post( $product->get_id() );
	setup_postdata( $GLOBALS['post'] );
	wc_set_loop_prop( 'foxfire_homepage_showcase', 'home' === $surface );
	wc_set_loop_prop( 'foxfire_catalog_showcase', 'shop' === $surface );
	ob_start();
	if ( 'pdp' === $surface ) woocommerce_show_product_images();
	else wc_get_template_part( 'content', 'product' );
	return ob_get_clean();
};
try {
	$images = get_posts( array( 'post_type' => 'attachment', 'post_status' => 'inherit', 'post_mime_type' => 'image', 'numberposts' => 2, 'fields' => 'ids' ) );
	$check( 2 === count( $images ), 'Two existing Media Library images are available without uploading or changing files' );
	$role = get_role( 'shop_manager' );
	$check( $role instanceof WP_Role && $role->has_cap( 'upload_files' ) && $role->has_cap( 'edit_products' ), 'Shop Managers can upload images and edit products' );
	$product = new WC_Product_Simple();
	$product->set_name( 'Temporary product image acceptance fixture' );
	$product->set_status( 'publish' );
	$product->set_regular_price( '40' );
	$ids[] = $id = $product->save();
	// Renderable only inside this test: never expose a temporary catalog product.
	$product->set_catalog_visibility( 'hidden' );
	$product->save();
	$make_visible = static fn( $visible, $product_id ) => $id === $product_id ? true : $visible;
	add_filter( 'woocommerce_product_is_visible', $make_visible, 10, 2 );
	foreach ( $images as $index => $image_id ) {
		$product->set_image_id( $image_id );
		$product->save();
		$product = wc_get_product( $id );
		$check( $image_id === get_post_thumbnail_id( $id ), 'Native main-image assignment persists: replacement ' . ( $index + 1 ) );
		foreach ( array( 'shop', 'home', 'pdp' ) as $surface ) {
			$html = $render( $product, $surface );
			$size = 'pdp' === $surface ? 'woocommerce_single' : 'woocommerce_thumbnail';
			$source = esc_url( wp_get_attachment_image_url( $image_id, $size ) );
			$check( str_contains( $html, 'src="' . $source . '"' ), $surface . ' uses the currently assigned image: replacement ' . ( $index + 1 ) );
			if ( $index ) {
				$old_source = esc_url( wp_get_attachment_image_url( $images[0], $size ) );
				$check( ! str_contains( $html, 'src="' . $old_source . '"' ), $surface . ' no longer renders the previous main image' );
			}
		}
	}
	$parent = new WC_Product_Variable();
	$parent->set_name( 'Temporary image inheritance fixture' );
	$parent->set_status( 'publish' );
	$parent->set_catalog_visibility( 'hidden' );
	$parent->set_image_id( $images[0] );
	$ids[] = $parent_id = $parent->save();
	$variation = new WC_Product_Variation();
	$variation->set_parent_id( $parent_id );
	$variation->set_status( 'publish' );
	$variation->set_regular_price( '40' );
	$ids[] = $variation_id = $variation->save();
	$check( $images[0] === ( new WC_Product_Variation( $variation_id ) )->get_image_id(), 'An empty strength image inherits the main product image' );
	$parent->set_image_id( $images[1] );
	$parent->save();
	// Fresh objects model the next storefront request, not WC's same-request identity cache.
	$check( $images[1] === ( new WC_Product_Variation( $variation_id ) )->get_image_id(), 'Strength inheritance follows a main-image replacement' );
	$variation = new WC_Product_Variation( $variation_id );
	$variation->set_image_id( $images[0] );
	$variation->save();
	$check( $images[0] === ( new WC_Product_Variation( $variation_id ) )->get_image_id(), 'An explicitly assigned strength image remains an intentional override' );
} finally {
	if ( isset( $make_visible ) ) remove_filter( 'woocommerce_product_is_visible', $make_visible, 10 );
	foreach ( array_reverse( $ids ) as $fixture_id ) wp_delete_post( $fixture_id, true );
	wc_set_loop_prop( 'foxfire_homepage_showcase', $previous_home );
	wc_set_loop_prop( 'foxfire_catalog_showcase', $previous_shop );
	wp_reset_postdata();
	$GLOBALS['product'] = $previous_product;
	$GLOBALS['post'] = $previous_post;
}
WP_CLI::success( 'Product image checks passed; only temporary test products were changed and removed. Media Library files and the real catalog are unchanged.' );
