<?php
/**
 * Seed product categories and development products (Chunk 1E & 1G).
 *
 * Configured per client requirements:
 * - 1 standard strength for general products (simple products)
 * - 2 strengths for popular GLP-1s (variable products)
 * - 1 / 3 / 5 vial quantity tier support with single-vial inventory pools
 *
 * Run via: wp eval-file seed-products.php (see scripts/seed-products.ps1)
 *
 * @package Foxfire_Child
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit( "This script must run inside WordPress.\n" );
}

if ( ! class_exists( 'WooCommerce' ) ) {
	WP_CLI::error( 'WooCommerce is not active.' );
}

/**
 * Log helper when WP-CLI is available.
 *
 * @param string $message Log message.
 */
function foxfire_seed_log( string $message ): void {
	if ( defined( 'WP_CLI' ) && WP_CLI ) {
		WP_CLI::log( $message );
	} else {
		echo $message . PHP_EOL;
	}
}

/**
 * Get product ID by slug, or 0.
 */
function foxfire_seed_product_id( string $slug ): int {
	$posts = get_posts(
		array(
			'post_type'      => 'product',
			'name'           => $slug,
			'post_status'    => array( 'publish', 'draft', 'private' ),
			'posts_per_page' => 1,
			'fields'         => 'ids',
		)
	);

	return ! empty( $posts[0] ) ? (int) $posts[0] : 0;
}

/**
 * Ensure plugin is installed and active.
 */
function foxfire_seed_ensure_plugin( string $slug, string $plugin_file ): void {
	if ( ! file_exists( WP_PLUGIN_DIR . '/' . $plugin_file ) ) {
		WP_CLI::error( "Required plugin missing: {$slug}. Run: wp plugin install {$slug} --activate" );
	}

	if ( ! is_plugin_active( $plugin_file ) ) {
		activate_plugin( $plugin_file );
		foxfire_seed_log( "Activated plugin: {$slug}" );
	}
}

/**
 * Import a product image from the theme assets directory into the media library.
 *
 * @param string $filename File name in assets/images/products/.
 * @param string $title    Attachment title.
 * @return int Attachment ID, or fallback placeholder ID.
 */
function foxfire_seed_product_image_id_by_filename( string $filename, string $title ): int {
	$option_key = 'foxfire_img_' . sanitize_key( $filename );
	$stored_id  = (int) get_option( $option_key, 0 );

	if ( $stored_id > 0 && wp_attachment_is_image( $stored_id ) ) {
		return $stored_id;
	}

	$source = get_stylesheet_directory() . '/assets/images/products/' . $filename;
	if ( ! file_exists( $source ) ) {
		return foxfire_seed_placeholder_image_id();
	}

	require_once ABSPATH . 'wp-admin/includes/file.php';
	require_once ABSPATH . 'wp-admin/includes/media.php';
	require_once ABSPATH . 'wp-admin/includes/image.php';

	$upload_dir = wp_upload_dir();
	if ( ! empty( $upload_dir['error'] ) ) {
		return foxfire_seed_placeholder_image_id();
	}

	$dest = trailingslashit( $upload_dir['path'] ) . $filename;
	if ( ! copy( $source, $dest ) ) {
		return foxfire_seed_placeholder_image_id();
	}

	$filetype   = wp_check_filetype( $filename, null );
	$attachment = array(
		'post_title'     => $title,
		'post_content'   => '',
		'post_status'    => 'inherit',
		'post_mime_type' => $filetype['type'],
	);

	$attachment_id = wp_insert_attachment( $attachment, $dest );
	if ( is_wp_error( $attachment_id ) || 0 === $attachment_id ) {
		return foxfire_seed_placeholder_image_id();
	}

	$metadata = wp_generate_attachment_metadata( $attachment_id, $dest );
	wp_update_attachment_metadata( $attachment_id, $metadata );

	update_option( $option_key, $attachment_id );
	return $attachment_id;
}

/**
 * Generate or get a placeholder image ID.
 */
function foxfire_seed_placeholder_image_id(): int {
	$option_key = 'foxfire_placeholder_image_id';
	$stored_id  = (int) get_option( $option_key, 0 );

	if ( $stored_id > 0 && wp_attachment_is_image( $stored_id ) ) {
		return $stored_id;
	}

	$svg = '<?xml version="1.0" encoding="UTF-8"?>
<svg xmlns="http://www.w3.org/2000/svg" width="600" height="600" viewBox="0 0 600 600">
  <rect width="600" height="600" fill="#f7f7f7"/>
  <rect x="20" y="20" width="560" height="560" fill="none" stroke="#e0e0e0" stroke-width="2"/>
  <text x="300" y="280" font-family="sans-serif" font-size="22" font-weight="bold" fill="#2b2b2b" text-anchor="middle">FOXFIRE PEPTIDES</text>
  <text x="300" y="320" font-family="sans-serif" font-size="14" fill="#4a4a4a" text-anchor="middle">Research Compound</text>
  <circle cx="300" cy="200" r="32" fill="#e85a0c"/>
</svg>';

	$upload_dir = wp_upload_dir();
	$filename   = 'foxfire-product-placeholder.svg';
	$filepath   = trailingslashit( $upload_dir['path'] ) . $filename;

	if ( false === file_put_contents( $filepath, $svg ) ) { // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_file_put_contents
		return 0;
	}

	$attachment = array(
		'post_title'     => 'Foxfire Product Placeholder',
		'post_content'   => '',
		'post_status'    => 'inherit',
		'post_mime_type' => 'image/svg+xml',
	);

	$attachment_id = wp_insert_attachment( $attachment, $filepath );
	if ( is_wp_error( $attachment_id ) || 0 === $attachment_id ) {
		return 0;
	}

	update_option( $option_key, $attachment_id );
	return $attachment_id;
}

/**
 * Ensure a product category exists.
 */
function foxfire_seed_ensure_category( string $name, string $slug, string $description = '' ): int {
	$term = get_term_by( 'slug', $slug, 'product_cat' );
	if ( $term && ! is_wp_error( $term ) ) {
		return (int) $term->term_id;
	}

	$result = wp_insert_term(
		$name,
		'product_cat',
		array(
			'slug'        => $slug,
			'description' => $description,
		)
	);

	if ( is_wp_error( $result ) ) {
		WP_CLI::error( 'Failed to create category ' . $name . ': ' . $result->get_error_message() );
	}

	foxfire_seed_log( "Created category: {$name}" );
	return (int) $result['term_id'];
}

/**
 * Assign categories to a product.
 */
function foxfire_seed_set_categories( int $product_id, array $term_ids ): void {
	wp_set_object_terms( $product_id, array_map( 'intval', $term_ids ), 'product_cat' );
}

/**
 * Set ACF/meta fields on a product.
 */
function foxfire_seed_set_product_fields( int $product_id, array $fields ): void {
	foreach ( $fields as $key => $value ) {
		if ( function_exists( 'update_field' ) ) {
			update_field( $key, $value, $product_id );
		}
		update_post_meta( $product_id, $key, $value );
	}
}

/**
 * Attach placeholder featured image.
 */
function foxfire_seed_set_image( int $product_id, int $image_id ): void {
	if ( $image_id <= 0 ) {
		return;
	}
	set_post_thumbnail( $product_id, $image_id );
}

/**
 * Create or update a simple product.
 */
function foxfire_seed_simple_product( array $args, int $image_id ): int {
	$slug = $args['slug'];
	$id   = foxfire_seed_product_id( $slug );

	if ( $id > 0 ) {
		$product = wc_get_product( $id );
		foxfire_seed_log( "Product exists: {$args['name']} ({$id})" );
	} else {
		$product = new WC_Product_Simple();
		foxfire_seed_log( "Creating product: {$args['name']}" );
	}

	$product->set_name( $args['name'] );
	$product->set_slug( $slug );
	$product->set_status( 'publish' );
	$product->set_catalog_visibility( 'visible' );
	$product->set_description( $args['description'] );
	$product->set_short_description( $args['short_description'] );
	$product->set_sku( $args['sku'] );
	$product->set_regular_price( $args['regular_price'] );
	$product->set_manage_stock( true );
	$product->set_stock_quantity( (int) $args['stock_quantity'] );
	$product->set_stock_status( 'instock' );

	$id = $product->save();
	foxfire_seed_set_categories( $id, $args['category_ids'] );
	foxfire_seed_set_product_fields( $id, $args['fields'] );
	foxfire_seed_set_image( $id, $image_id );

	return (int) $id;
}

/**
 * Create or update a variable product with strength variations.
 */
function foxfire_seed_variable_product( array $args, array $variations, int $image_id ): int {
	$slug = $args['slug'];
	$id   = foxfire_seed_product_id( $slug );

	if ( $id > 0 ) {
		$product = wc_get_product( $id );
		if ( ! $product instanceof WC_Product_Variable ) {
			wp_delete_post( $id, true );
			$id = 0;
		} else {
			foxfire_seed_log( "Product exists: {$args['name']} ({$id})" );
		}
	}

	if ( $id <= 0 ) {
		$product = new WC_Product_Variable();
		foxfire_seed_log( "Creating product: {$args['name']}" );
	}

	$product->set_name( $args['name'] );
	$product->set_slug( $slug );
	$product->set_status( 'publish' );
	$product->set_catalog_visibility( 'visible' );
	$product->set_description( $args['description'] );
	$product->set_short_description( $args['short_description'] );
	$product->set_manage_stock( false );

	$attribute = new WC_Product_Attribute();
	$attribute->set_id( 0 );
	$attribute->set_name( 'Strength' );
	$attribute->set_options( array_keys( $variations ) );
	$attribute->set_position( 0 );
	$attribute->set_visible( true );
	$attribute->set_variation( true );
	$product->set_attributes( array( $attribute ) );

	$id = $product->save();
	foxfire_seed_set_categories( $id, $args['category_ids'] );
	foxfire_seed_set_product_fields( $id, $args['fields'] );
	foxfire_seed_set_image( $id, $image_id );

	$product = wc_get_product( $id );
	if ( ! $product instanceof WC_Product_Variable ) {
		return (int) $id;
	}

	$existing_children = $product->get_children();
	foreach ( $existing_children as $child_id ) {
		wp_delete_post( $child_id, true );
	}

	foreach ( $variations as $strength => $price ) {
		$variation = new WC_Product_Variation();
		$variation->set_parent_id( $id );
		$variation->set_attributes( array( 'strength' => $strength ) );
		$variation->set_regular_price( $price );
		$variation->set_sku( $args['sku'] . '-' . strtoupper( str_replace( array( ' ', '.' ), '', $strength ) ) );
		$variation->set_manage_stock( true );
		$variation->set_stock_quantity( 35 );
		$variation->set_stock_status( 'instock' );
		$variation->set_status( 'publish' );
		$variation->save();
	}

	return (int) $id;
}

/**
 * Link related products (upsells / cross-sells).
 */
function foxfire_seed_set_related( int $product_id, array $upsell_ids, array $crosssell_ids ): void {
	$product = wc_get_product( $product_id );
	if ( ! $product ) {
		return;
	}
	$product->set_upsell_ids( $upsell_ids );
	$product->set_cross_sell_ids( $crosssell_ids );
	$product->save();
}

foxfire_seed_log( '=== Foxfire Peptides — Product Seed ===' );

foxfire_seed_ensure_plugin( 'advanced-custom-fields', 'advanced-custom-fields/acf.php' );

$placeholder_id = foxfire_seed_placeholder_image_id();
$bpc_image_id   = foxfire_seed_product_image_id_by_filename( 'bpc-157.jpg', 'BPC-157 Research Peptide' );
$reta_image_id  = foxfire_seed_product_image_id_by_filename( 'retatrutide.jpg', 'Retatrutide (RETA) Research Peptide' );
$tirz_image_id  = foxfire_seed_product_image_id_by_filename( 'tirzepatide.jpg', 'Tirzepatide (TIRZ) Research Peptide' );
$wolv_image_id  = foxfire_seed_product_image_id_by_filename( 'wolverine-blend.jpg', 'Wolverine Blend Research Peptide' );
$ghk_image_id   = foxfire_seed_product_image_id_by_filename( 'ghk-cu.jpg', 'GHK-Cu Copper Peptide' );
$sema_image_id  = foxfire_seed_product_image_id_by_filename( 'semaglutide.jpg', 'Semaglutide (SEMA) Research Peptide' );
$tb_image_id    = foxfire_seed_product_image_id_by_filename( 'tb-500.jpg', 'TB-500 Research Peptide' );
$kpv_image_id   = foxfire_seed_product_image_id_by_filename( 'kpv.jpg', 'KPV Research Peptide' );
$motsc_image_id = foxfire_seed_product_image_id_by_filename( 'mots-c.jpg', 'MOTS-c Research Peptide' );
$pt141_image_id = foxfire_seed_product_image_id_by_filename( 'pt-141.jpg', 'PT-141 Research Peptide' );
$klow_image_id   = foxfire_seed_product_image_id_by_filename( 'klow.jpg', 'KLOW Blend Research Peptide' );
$selank_image_id = foxfire_seed_product_image_id_by_filename( 'selank.jpg', 'Selank Research Peptide' );
$semax_image_id  = foxfire_seed_product_image_id_by_filename( 'semax.jpg', 'Semax Research Peptide' );
$mt1_image_id    = foxfire_seed_product_image_id_by_filename( 'mt-1.jpg', 'MT-1 Research Peptide' );
$mt2_image_id    = foxfire_seed_product_image_id_by_filename( 'mt-2.jpg', 'MT-2 Research Peptide' );
$amino_image_id  = foxfire_seed_product_image_id_by_filename( '5-amino-1mq.jpg', '5-Amino-1MQ Research Peptide' );
$cagri_image_id  = foxfire_seed_product_image_id_by_filename( 'cagrilintide.jpg', 'Cagrilintide (CAGRI) Research Peptide' );
$serm_image_id   = foxfire_seed_product_image_id_by_filename( 'sermorelin.jpg', 'Sermorelin Research Peptide' );
$tesa_image_id   = foxfire_seed_product_image_id_by_filename( 'tesamorelin.jpg', 'Tesamorelin Research Peptide' );

$coa_page_url   = foxfire_get_page_url( 'testing-coa', '/testing-coa/' );

$cat_glp1      = foxfire_seed_ensure_category( 'GLP-1 Agonists', 'glp-1-agonists', 'GLP-1 receptor research compounds.' );
$cat_recovery  = foxfire_seed_ensure_category( 'Recovery & Healing', 'recovery-healing', 'Cellular repair and recovery research peptides.' );
$cat_blends    = foxfire_seed_ensure_category( 'Blends', 'blends', 'Specialized multi-peptide research blends.' );
$cat_support   = foxfire_seed_ensure_category( 'Support Compounds', 'support-compounds', 'Ancillary and cellular support research compounds.' );

$placeholder_desc = '[PLACEHOLDER] Research specifications for this product will be provided by the client. For laboratory research use only.';
$placeholder_short = '[PLACEHOLDER] Laboratory research compound. Lyophilized powder in sterile vial.';

$base_fields = static function ( string $batch_lot ) use ( $coa_page_url ): array {
	return array(
		'foxfire_batch_lot'        => $batch_lot,
		'foxfire_coa_url'          => $coa_page_url,
		'foxfire_coa_label'        => 'View Certificate of Analysis',
		'foxfire_tier_enable'      => 1,
		'foxfire_tier_3_discount'  => 5,
		'foxfire_tier_5_discount'  => 10,
	);
};

// 1. Popular GLP-1s: 2 Strengths (15mg | 30mg)
$reta_id = foxfire_seed_variable_product(
	array(
		'name'              => 'Retatrutide (RETA)',
		'slug'              => 'retatrutide-reta',
		'sku'               => 'FF-RETA',
		'description'       => $placeholder_desc,
		'short_description' => $placeholder_short,
		'category_ids'      => array( $cat_glp1 ),
		'fields'            => $base_fields( 'FF-RT2601' ),
	),
	array(
		'15mg' => '129.99',
		'30mg' => '229.99',
	),
	$reta_image_id
);

$tirz_id = foxfire_seed_variable_product(
	array(
		'name'              => 'Tirzepatide (TIRZ)',
		'slug'              => 'tirzepatide-tirz',
		'sku'               => 'FF-TIRZ',
		'description'       => $placeholder_desc,
		'short_description' => $placeholder_short,
		'category_ids'      => array( $cat_glp1 ),
		'fields'            => $base_fields( 'FF-TZ2601' ),
	),
	array(
		'15mg' => '119.99',
		'30mg' => '209.99',
	),
	$tirz_image_id
);

$sema_id = foxfire_seed_variable_product(
	array(
		'name'              => 'Semaglutide (SEMA)',
		'slug'              => 'semaglutide-sema',
		'sku'               => 'FF-SEMA',
		'description'       => $placeholder_desc,
		'short_description' => $placeholder_short,
		'category_ids'      => array( $cat_glp1 ),
		'fields'            => $base_fields( 'FF-SM2601' ),
	),
	array(
		'15mg' => '79.99',
		'30mg' => '139.99',
	),
	$sema_image_id
);

$cagri_id = foxfire_seed_variable_product(
	array(
		'name'              => 'Cagrilintide (CAGRI)',
		'slug'              => 'cagrilintide-cagri',
		'sku'               => 'FF-CAGRI',
		'description'       => $placeholder_desc,
		'short_description' => $placeholder_short,
		'category_ids'      => array( $cat_glp1 ),
		'fields'            => $base_fields( 'FF-CG2601' ),
	),
	array(
		'5mg'  => '79.99',
		'10mg' => '139.99',
	),
	$cagri_image_id
);

// 2. Single-Strength Compounds (1 Standard Strength)
$bpc_id = foxfire_seed_simple_product(
	array(
		'name'              => 'BPC-157',
		'slug'              => 'bpc-157',
		'sku'               => 'FF-BPC157-10MG',
		'regular_price'     => '69.99',
		'stock_quantity'    => 50,
		'description'       => $placeholder_desc,
		'short_description' => $placeholder_short,
		'category_ids'      => array( $cat_recovery ),
		'fields'            => $base_fields( 'FF-BP2601' ),
	),
	$bpc_image_id
);

$tb500_id = foxfire_seed_simple_product(
	array(
		'name'              => 'TB-500',
		'slug'              => 'tb-500',
		'sku'               => 'FF-TB500-10MG',
		'regular_price'     => '69.99',
		'stock_quantity'    => 45,
		'description'       => $placeholder_desc,
		'short_description' => $placeholder_short,
		'category_ids'      => array( $cat_recovery ),
		'fields'            => $base_fields( 'FF-TB2601' ),
	),
	$tb_image_id
);

$kpv_id = foxfire_seed_simple_product(
	array(
		'name'              => 'KPV',
		'slug'              => 'kpv',
		'sku'               => 'FF-KPV-10MG',
		'regular_price'     => '59.99',
		'stock_quantity'    => 40,
		'description'       => $placeholder_desc,
		'short_description' => $placeholder_short,
		'category_ids'      => array( $cat_recovery ),
		'fields'            => $base_fields( 'FF-KP2601' ),
	),
	$kpv_image_id
);

$wolverine_id = foxfire_seed_simple_product(
	array(
		'name'              => 'Wolverine Blend',
		'slug'              => 'wolverine-blend',
		'sku'               => 'FF-WOLVERINE',
		'regular_price'     => '119.99',
		'stock_quantity'    => 30,
		'description'       => $placeholder_desc,
		'short_description' => $placeholder_short,
		'category_ids'      => array( $cat_blends, $cat_recovery ),
		'fields'            => $base_fields( 'FF-WV2601' ),
	),
	$wolv_image_id
);

$klow_id = foxfire_seed_simple_product(
	array(
		'name'              => 'KLOW Blend',
		'slug'              => 'klow',
		'sku'               => 'FF-KLOW-10MG',
		'regular_price'     => '129.99',
		'stock_quantity'    => 25,
		'description'       => $placeholder_desc,
		'short_description' => $placeholder_short,
		'category_ids'      => array( $cat_blends, $cat_recovery ),
		'fields'            => $base_fields( 'FF-KL2601' ),
	),
	$klow_image_id
);

$ghk_id = foxfire_seed_simple_product(
	array(
		'name'              => 'GHK-Cu',
		'slug'              => 'ghk-cu',
		'sku'               => 'FF-GHKCU-50MG',
		'regular_price'     => '49.99',
		'stock_quantity'    => 40,
		'description'       => $placeholder_desc,
		'short_description' => $placeholder_short,
		'category_ids'      => array( $cat_support ),
		'fields'            => $base_fields( 'FF-GH2601' ),
	),
	$ghk_image_id
);

$motsc_id = foxfire_seed_simple_product(
	array(
		'name'              => 'MOTS-c',
		'slug'              => 'mots-c',
		'sku'               => 'FF-MOTSC-10MG',
		'regular_price'     => '59.99',
		'stock_quantity'    => 35,
		'description'       => $placeholder_desc,
		'short_description' => $placeholder_short,
		'category_ids'      => array( $cat_support ),
		'fields'            => $base_fields( 'FF-MC2601' ),
	),
	$motsc_image_id
);

$pt141_id = foxfire_seed_simple_product(
	array(
		'name'              => 'PT-141',
		'slug'              => 'pt-141',
		'sku'               => 'FF-PT141-10MG',
		'regular_price'     => '49.99',
		'stock_quantity'    => 35,
		'description'       => $placeholder_desc,
		'short_description' => $placeholder_short,
		'category_ids'      => array( $cat_support ),
		'fields'            => $base_fields( 'FF-PT2601' ),
	),
	$pt141_image_id
);

$selank_id = foxfire_seed_simple_product(
	array(
		'name'              => 'Selank',
		'slug'              => 'selank',
		'sku'               => 'FF-SELANK-10MG',
		'regular_price'     => '54.99',
		'stock_quantity'    => 30,
		'description'       => $placeholder_desc,
		'short_description' => $placeholder_short,
		'category_ids'      => array( $cat_support ),
		'fields'            => $base_fields( 'FF-SL2601' ),
	),
	$selank_image_id
);

$semax_id = foxfire_seed_simple_product(
	array(
		'name'              => 'Semax',
		'slug'              => 'semax',
		'sku'               => 'FF-SEMAX-10MG',
		'regular_price'     => '54.99',
		'stock_quantity'    => 30,
		'description'       => $placeholder_desc,
		'short_description' => $placeholder_short,
		'category_ids'      => array( $cat_support ),
		'fields'            => $base_fields( 'FF-SX2601' ),
	),
	$semax_image_id
);

$mt1_id = foxfire_seed_simple_product(
	array(
		'name'              => 'MT-1',
		'slug'              => 'mt-1',
		'sku'               => 'FF-MT1-10MG',
		'regular_price'     => '44.99',
		'stock_quantity'    => 30,
		'description'       => $placeholder_desc,
		'short_description' => $placeholder_short,
		'category_ids'      => array( $cat_support ),
		'fields'            => $base_fields( 'FF-M12601' ),
	),
	$mt1_image_id
);

$mt2_id = foxfire_seed_simple_product(
	array(
		'name'              => 'MT-2',
		'slug'              => 'mt-2',
		'sku'               => 'FF-MT2-10MG',
		'regular_price'     => '49.99',
		'stock_quantity'    => 35,
		'description'       => $placeholder_desc,
		'short_description' => $placeholder_short,
		'category_ids'      => array( $cat_support ),
		'fields'            => $base_fields( 'FF-M22601' ),
	),
	$mt2_image_id
);

$amino_id = foxfire_seed_simple_product(
	array(
		'name'              => '5-Amino-1MQ',
		'slug'              => '5-amino-1mq',
		'sku'               => 'FF-5AMINO-50MG',
		'regular_price'     => '69.99',
		'stock_quantity'    => 30,
		'description'       => $placeholder_desc,
		'short_description' => $placeholder_short,
		'category_ids'      => array( $cat_support ),
		'fields'            => $base_fields( 'FF-5A2601' ),
	),
	$amino_image_id
);

$serm_id = foxfire_seed_simple_product(
	array(
		'name'              => 'Sermorelin',
		'slug'              => 'sermorelin',
		'sku'               => 'FF-SERM-10MG',
		'regular_price'     => '59.99',
		'stock_quantity'    => 30,
		'description'       => $placeholder_desc,
		'short_description' => $placeholder_short,
		'category_ids'      => array( $cat_recovery, $cat_support ),
		'fields'            => $base_fields( 'FF-SR2601' ),
	),
	$serm_image_id
);

$tesa_id = foxfire_seed_simple_product(
	array(
		'name'              => 'Tesamorelin',
		'slug'              => 'tesamorelin',
		'sku'               => 'FF-TESA-10MG',
		'regular_price'     => '79.99',
		'stock_quantity'    => 30,
		'description'       => $placeholder_desc,
		'short_description' => $placeholder_short,
		'category_ids'      => array( $cat_recovery, $cat_support ),
		'fields'            => $base_fields( 'FF-TS2601' ),
	),
	$tesa_image_id
);

foxfire_seed_set_related( $bpc_id, array( $wolverine_id, $tb500_id ), array( $ghk_id, $kpv_id ) );
foxfire_seed_set_related( $reta_id, array( $tirz_id, $sema_id ), array( $bpc_id, $cagri_id ) );
foxfire_seed_set_related( $sema_id, array( $tirz_id, $reta_id ), array( $cagri_id, $motsc_id ) );
foxfire_seed_set_related( $tb500_id, array( $bpc_id, $wolverine_id ), array( $kpv_id ) );
foxfire_seed_set_related( $wolverine_id, array( $bpc_id, $tb500_id ), array( $reta_id ) );
foxfire_seed_set_related( $klow_id, array( $wolverine_id, $bpc_id ), array( $tb500_id ) );
foxfire_seed_set_related( $selank_id, array( $semax_id ), array( $motsc_id ) );
foxfire_seed_set_related( $semax_id, array( $selank_id ), array( $motsc_id ) );
foxfire_seed_set_related( $mt1_id, array( $mt2_id ), array( $ghk_id ) );
foxfire_seed_set_related( $mt2_id, array( $mt1_id ), array( $pt141_id ) );
foxfire_seed_set_related( $cagri_id, array( $sema_id, $reta_id ), array( $tirz_id ) );
foxfire_seed_set_related( $serm_id, array( $tesa_id ), array( $bpc_id ) );
foxfire_seed_set_related( $tesa_id, array( $serm_id ), array( $tb500_id ) );
foxfire_seed_set_related( $amino_id, array( $motsc_id ), array( $kpv_id ) );

wc_delete_product_transients();

foxfire_seed_log( '' );
foxfire_seed_log( 'Seed complete — 19 products configured with 1/2 strengths and 1/3/5 quantity tiers.' );
foxfire_seed_log( 'Shop: ' . wc_get_page_permalink( 'shop' ) );
