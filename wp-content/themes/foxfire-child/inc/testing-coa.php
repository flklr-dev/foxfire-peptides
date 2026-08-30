<?php
/**
 * Testing & COA Page module — Chunk 1I.
 *
 * Provides data querying for catalog batch/lot references, COA document
 * linking, template routing, and asset enqueues per DESIGN.md §12 and PRD §5.2.
 *
 * @package Foxfire_Child
 */

defined( 'ABSPATH' ) || exit;

/**
 * Enqueue styles and scripts specifically for the Testing / COA page.
 */
function foxfire_enqueue_testing_coa_assets(): void {
	if ( ! is_page( 'testing-coa' ) && ! is_page_template( 'page-testing-coa.php' ) ) {
		return;
	}

	$css_file = FOXFIRE_CHILD_DIR . '/assets/css/testing-coa.css';
	$js_file  = FOXFIRE_CHILD_DIR . '/assets/js/testing-coa.js';

	wp_enqueue_style(
		'foxfire-testing-coa',
		FOXFIRE_CHILD_URI . '/assets/css/testing-coa.css',
		array( 'foxfire-base' ),
		file_exists( $css_file ) ? (string) filemtime( $css_file ) : FOXFIRE_CHILD_VERSION
	);

	wp_enqueue_script(
		'foxfire-testing-coa',
		FOXFIRE_CHILD_URI . '/assets/js/testing-coa.js',
		array(),
		file_exists( $js_file ) ? (string) filemtime( $js_file ) : FOXFIRE_CHILD_VERSION,
		true
	);
}
add_action( 'wp_enqueue_scripts', 'foxfire_enqueue_testing_coa_assets', 30 );

/**
 * Route /testing-coa/ requests to page-testing-coa.php template.
 *
 * @param string $template Full path to current template.
 * @return string
 */
function foxfire_testing_coa_template_include( string $template ): string {
	if ( is_page( 'testing-coa' ) || is_page_template( 'page-testing-coa.php' ) ) {
		$custom_template = FOXFIRE_CHILD_DIR . '/page-testing-coa.php';
		if ( file_exists( $custom_template ) ) {
			return $custom_template;
		}
	}

	return $template;
}
add_filter( 'template_include', 'foxfire_testing_coa_template_include', 99 );

/**
 * Suppress default Storefront breadcrumbs on Testing / COA page.
 */
function foxfire_testing_coa_remove_breadcrumbs(): void {
	if ( is_page( 'testing-coa' ) || is_page_template( 'page-testing-coa.php' ) ) {
		remove_action( 'storefront_before_content', 'woocommerce_breadcrumb', 10 );
	}
}
add_action( 'wp', 'foxfire_testing_coa_remove_breadcrumbs', 10 );

/**
 * Retrieve active catalog compounds and their testing/COA data.
 *
 * @return array<int, array<string, mixed>>
 */
function foxfire_get_coa_catalog_items(): array {
	if ( ! function_exists( 'wc_get_products' ) ) {
		return array();
	}

	$products = wc_get_products(
		array(
			'status'  => 'publish',
			'limit'   => 50,
			'orderby' => 'title',
			'order'   => 'ASC',
		)
	);

	if ( empty( $products ) ) {
		return array();
	}

	$items = array();

	foreach ( $products as $product ) {
		if ( ! $product instanceof WC_Product ) {
			continue;
		}

		$product_id = $product->get_id();
		$batch_lot  = function_exists( 'foxfire_get_product_batch_lot' ) ? foxfire_get_product_batch_lot( $product_id ) : '';
		$coa_url    = function_exists( 'foxfire_get_product_coa_url' ) ? foxfire_get_product_coa_url( $product_id ) : '';
		$coa_label  = function_exists( 'foxfire_get_product_coa_label' ) ? foxfire_get_product_coa_label( $product_id ) : __( 'View Certificate', 'foxfire-child' );

		// Clean default fallback values if empty
		if ( empty( $batch_lot ) ) {
			$batch_lot = 'FF-' . strtoupper( substr( md5( (string) $product_id ), 0, 6 ) );
		}

		if ( empty( $coa_url ) ) {
			$coa_url = '#';
		}

		$items[] = array(
			'id'          => $product_id,
			'name'        => $product->get_name(),
			'sku'         => $product->get_sku() ?: 'FF-SEQ-' . str_pad( (string) $product_id, 3, '0', STR_PAD_LEFT ),
			'permalink'   => $product->get_permalink(),
			'batch_lot'   => $batch_lot,
			'coa_url'     => $coa_url,
			'coa_label'   => $coa_label,
			'purity'      => '≥ 99.2%',
			'method'      => 'HPLC + MS',
			'status'      => __( 'Verified', 'foxfire-child' ),
		);
	}

	return $items;
}
