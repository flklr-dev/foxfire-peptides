<?php
/**
 * Force purge old attachments and freshly re-import all 15 product images
 * directly from wp-content/themes/foxfire-child/assets/images/products/.
 *
 * Run via: wp eval-file wp-content/themes/foxfire-child/reimport-runner.php
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once ABSPATH . 'wp-admin/includes/file.php';
require_once ABSPATH . 'wp-admin/includes/media.php';
require_once ABSPATH . 'wp-admin/includes/image.php';

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

echo "=== Purging Old Attachments and Re-importing Current Folder Images ===\n";

$upload_dir = wp_upload_dir();

foreach ( $image_map as $slug => $filename ) {
	$posts = get_posts( array(
		'post_type'      => 'product',
		'name'           => $slug,
		'posts_per_page' => 1,
		'post_status'    => 'any',
	) );
	$product = ! empty( $posts[0] ) ? $posts[0] : null;

	if ( ! $product ) {
		echo "Product not found for slug: {$slug}\n";
		continue;
	}

	$product_id = $product->ID;
	$source_file = $theme_images_dir . $filename;

	if ( ! file_exists( $source_file ) ) {
		echo "Source file missing: {$source_file}\n";
		continue;
	}

	// Remove existing featured image thumbnail attachment if exists
	$old_thumbnail_id = (int) get_post_thumbnail_id( $product_id );
	if ( $old_thumbnail_id > 0 ) {
		wp_delete_attachment( $old_thumbnail_id, true );
	}

	// Delete any options cache
	$option_key = 'foxfire_img_' . sanitize_key( $filename );
	$old_cached_id = (int) get_option( $option_key, 0 );
	if ( $old_cached_id > 0 && $old_cached_id !== $old_thumbnail_id ) {
		wp_delete_attachment( $old_cached_id, true );
	}
	delete_option( $option_key );

	// Delete any attachments matching _foxfire_source_image
	$existing_attachments = get_posts( array(
		'post_type'      => 'attachment',
		'post_status'    => 'inherit',
		'posts_per_page' => -1,
		'fields'         => 'ids',
		'meta_query'     => array(
			array(
				'key'   => '_foxfire_source_image',
				'value' => $filename,
			),
		),
	) );
	foreach ( $existing_attachments as $att_id ) {
		wp_delete_attachment( $att_id, true );
	}

	// Copy fresh file to uploads
	$dest_name = wp_unique_filename( $upload_dir['path'], $filename );
	$dest_path = trailingslashit( $upload_dir['path'] ) . $dest_name;

	if ( ! copy( $source_file, $dest_path ) ) {
		echo "Failed to copy {$filename} to uploads.\n";
		continue;
	}

	$filetype = wp_check_filetype( $dest_name, null );
	$title    = $product->post_title . ' Research Peptide';

	$attachment_id = wp_insert_attachment(
		array(
			'post_title'     => $title,
			'post_content'   => '',
			'post_status'    => 'inherit',
			'post_mime_type' => $filetype['type'] ?: 'image/jpeg',
			'post_parent'    => $product_id,
		),
		$dest_path,
		$product_id
	);

	if ( is_wp_error( $attachment_id ) || 0 === $attachment_id ) {
		echo "Failed to insert attachment for {$filename}\n";
		continue;
	}

	wp_update_attachment_metadata(
		$attachment_id,
		wp_generate_attachment_metadata( $attachment_id, $dest_path )
	);

	update_post_meta( $attachment_id, '_foxfire_source_image', $filename );
	update_post_meta( $attachment_id, '_wp_attachment_image_alt', $title );
	update_option( $option_key, $attachment_id, false );

	set_post_thumbnail( $product_id, $attachment_id );

	echo "SUCCESS: {$product->post_title} -> Attached new image from {$filename} (Attachment ID {$attachment_id})\n";
}

wc_delete_product_transients();
wp_cache_flush();

echo "=== All 15 Product Images Refreshed Successfully! ===\n";
