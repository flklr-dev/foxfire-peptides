<?php
/**
 * Import generated product images from the theme into the media library
 * and attach them as product featured images.
 *
 * Run via: wp eval-file import-product-images.php
 *
 * @package Foxfire_Child
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit( "This script must run inside WordPress.\n" );
}

if ( ! class_exists( 'WooCommerce' ) ) {
	WP_CLI::error( 'WooCommerce is not active.' );
}

require_once ABSPATH . 'wp-admin/includes/file.php';
require_once ABSPATH . 'wp-admin/includes/media.php';
require_once ABSPATH . 'wp-admin/includes/image.php';

/**
 * Log helper.
 */
function foxfire_img_log( string $message ): void {
	if ( defined( 'WP_CLI' ) && WP_CLI ) {
		WP_CLI::log( $message );
	} else {
		echo $message . PHP_EOL;
	}
}

/**
 * Product slug => image filename in assets/images/products/.
 *
 * @var array<string, string>
 */
$image_map = array(
	'bpc-157'            => 'bpc-157.jpg',
	'retatrutide-reta'   => 'retatrutide.jpg',
	'tirzepatide-tirz'   => 'tirzepatide.jpg',
	'wolverine-blend'    => 'wolverine-blend.jpg',
	'ghk-cu'             => 'ghk-cu.jpg',
	'semaglutide-sema'   => 'semaglutide.jpg',
	'tb-500'             => 'tb-500.jpg',
	'kpv'                => 'kpv.jpg',
	'mots-c'             => 'mots-c.jpg',
	'pt-141'             => 'pt-141.jpg',
	'klow'               => 'klow.jpg',
	'selank'             => 'selank.jpg',
	'semax'              => 'semax.jpg',
	'mt-1'               => 'mt-1.jpg',
	'mt-2'               => 'mt-2.jpg',
	'5-amino-1mq'        => '5-amino-1mq.jpg',
	'cagrilintide-cagri' => 'cagrilintide.jpg',
	'sermorelin'         => 'sermorelin.jpg',
	'tesamorelin'        => 'tesamorelin.jpg',
);

$theme_images_dir = get_stylesheet_directory() . '/assets/images/products/';

/**
 * Find a product ID by slug.
 */
function foxfire_img_product_id( string $slug ): int {
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
 * Find an existing attachment created by this script.
 */
function foxfire_img_existing_attachment( string $filename ): int {
	$existing = get_posts(
		array(
			'post_type'      => 'attachment',
			'post_status'    => 'inherit',
			'posts_per_page' => 1,
			'fields'         => 'ids',
			'meta_query'     => array(
				array(
					'key'   => '_foxfire_source_image',
					'value' => $filename,
				),
			),
		)
	);

	return ! empty( $existing[0] ) ? (int) $existing[0] : 0;
}

/**
 * Copy a theme image into uploads and create an attachment.
 */
function foxfire_img_import( string $source_path, string $filename, string $title ): int {
	$existing = foxfire_img_existing_attachment( $filename );
	if ( $existing > 0 ) {
		wp_delete_attachment( $existing, true );
	}

	$upload_dir = wp_upload_dir();
	if ( ! empty( $upload_dir['error'] ) ) {
		foxfire_img_log( 'Upload dir error: ' . $upload_dir['error'] );
		return 0;
	}

	$dest_name = wp_unique_filename( $upload_dir['path'], $filename );
	$dest_path = trailingslashit( $upload_dir['path'] ) . $dest_name;

	if ( ! copy( $source_path, $dest_path ) ) {
		foxfire_img_log( "Could not copy {$filename} into uploads." );
		return 0;
	}

	$filetype = wp_check_filetype( $dest_name, null );

	$attachment_id = wp_insert_attachment(
		array(
			'post_title'     => $title,
			'post_content'   => '',
			'post_status'    => 'inherit',
			'post_mime_type' => $filetype['type'] ? $filetype['type'] : 'image/jpeg',
		),
		$dest_path
	);

	if ( is_wp_error( $attachment_id ) || 0 === $attachment_id ) {
		foxfire_img_log( "Could not create attachment for {$filename}." );
		return 0;
	}

	wp_update_attachment_metadata(
		$attachment_id,
		wp_generate_attachment_metadata( $attachment_id, $dest_path )
	);

	update_post_meta( $attachment_id, '_foxfire_source_image', $filename );
	update_post_meta( $attachment_id, '_wp_attachment_image_alt', $title );

	return (int) $attachment_id;
}

foxfire_img_log( '=== Foxfire — Product image import ===' );

if ( ! is_dir( $theme_images_dir ) ) {
	WP_CLI::error( 'Image directory not found: ' . $theme_images_dir );
}

$attached = 0;

foreach ( $image_map as $product_slug => $filename ) {
	$product_id = foxfire_img_product_id( $product_slug );

	if ( 0 === $product_id ) {
		foxfire_img_log( "Skipped (no product): {$product_slug}" );
		continue;
	}

	$source_path = $theme_images_dir . $filename;

	if ( ! file_exists( $source_path ) ) {
		foxfire_img_log( "Skipped (missing file): {$filename}" );
		continue;
	}

	$title         = get_the_title( $product_id );
	$attachment_id = foxfire_img_import( $source_path, $filename, $title );

	if ( 0 === $attachment_id ) {
		continue;
	}

	set_post_thumbnail( $product_id, $attachment_id );
	wp_update_post(
		array(
			'ID'          => $attachment_id,
			'post_parent' => $product_id,
		)
	);

	++$attached;
	foxfire_img_log( "Attached {$filename} -> {$title} ({$product_id})" );
}

/**
 * Remove the broken SVG-in-PNG placeholder created by the 1E seed so products
 * fall back to WooCommerce's own placeholder instead of a zero-byte image.
 */
$bad_placeholder = (int) get_option( 'foxfire_product_placeholder_image_id', 0 );
if ( $bad_placeholder > 0 ) {
	$meta = wp_get_attachment_metadata( $bad_placeholder );
	if ( empty( $meta['width'] ) ) {
		wp_delete_attachment( $bad_placeholder, true );
		delete_option( 'foxfire_product_placeholder_image_id' );
		foxfire_img_log( 'Removed invalid placeholder image attachment.' );
	}
}

wc_delete_product_transients();

foxfire_img_log( '' );
foxfire_img_log( "Done — {$attached} product image(s) attached." );
