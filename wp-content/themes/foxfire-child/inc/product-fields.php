<?php
/**
 * Product custom fields (ACF) and read helpers — Chunk 1E & 1G.
 *
 * Batch/lot, COA fields, and 1/3/5 quantity tier settings per PRD §5.2.
 * Primary COA reference is a URL; optional file upload is supported
 * when a PDF is hosted in the media library.
 *
 * @package Foxfire_Child
 */

defined( 'ABSPATH' ) || exit;

/**
 * Register ACF field group for WooCommerce products.
 */
function foxfire_register_product_field_group(): void {
	if ( ! function_exists( 'acf_add_local_field_group' ) ) {
		return;
	}

	acf_add_local_field_group(
		array(
			'key'                   => 'group_foxfire_product',
			'title'                 => __( 'Testing & Batch Information', 'foxfire-child' ),
			'fields'                => array(
				array(
					'key'          => 'field_foxfire_batch_lot',
					'label'        => __( 'Batch / Lot Number', 'foxfire-child' ),
					'name'         => 'foxfire_batch_lot',
					'type'         => 'text',
					'instructions' => __( 'Current batch or lot identifier for this product listing (e.g. FF-RT2601).', 'foxfire-child' ),
					'required'     => 0,
				),
				array(
					'key'          => 'field_foxfire_coa_url',
					'label'        => __( 'COA Link (URL)', 'foxfire-child' ),
					'name'         => 'foxfire_coa_url',
					'type'         => 'url',
					'instructions' => __( 'Direct link to Certificate of Analysis PDF/report. If empty, points to /testing-coa/ portal.', 'foxfire-child' ),
					'required'     => 0,
				),
				array(
					'key'          => 'field_foxfire_coa_file',
					'label'        => __( 'COA File (optional)', 'foxfire-child' ),
					'name'         => 'foxfire_coa_file',
					'type'         => 'file',
					'instructions' => __( 'Optional PDF upload from media library if no external URL is used.', 'foxfire-child' ),
					'return_format' => 'url',
					'library'      => 'all',
					'mime_types'   => 'pdf',
					'required'     => 0,
				),
				array(
					'key'           => 'field_foxfire_coa_label',
					'label'         => __( 'COA Link Label', 'foxfire-child' ),
					'name'          => 'foxfire_coa_label',
					'type'          => 'text',
					'instructions'  => __( 'Button text on product page. Defaults to “View Certificate of Analysis”.', 'foxfire-child' ),
					'default_value' => __( 'View Certificate of Analysis', 'foxfire-child' ),
					'required'      => 0,
				),
				array(
					'key'           => 'field_foxfire_testing_summary',
					'label'         => __( 'Testing Result / Summary', 'foxfire-child' ),
					'name'          => 'foxfire_testing_summary',
					'type'          => 'text',
					'instructions'  => __( 'Simple testing summary (defaults to "Information Available").', 'foxfire-child' ),
					'default_value' => __( 'Information Available', 'foxfire-child' ),
					'required'      => 0,
				),
				array(
					'key'           => 'field_foxfire_tier_enable',
					'label'         => __( 'Enable 1 / 3 / 5 Vial Purchasing', 'foxfire-child' ),
					'name'          => 'foxfire_tier_enable',
					'type'          => 'true_false',
					'instructions'  => __( 'Allow customers to choose 1, 3, or 5 vials on the product page.', 'foxfire-child' ),
					'default_value' => 1,
					'ui'            => 1,
				),
				array(
					'key'           => 'field_foxfire_tier_3_discount',
					'label'         => __( '3-Vial Discount (%)', 'foxfire-child' ),
					'name'          => 'foxfire_tier_3_discount',
					'type'          => 'number',
					'instructions'  => __( 'Optional % savings when buying 3 vials (e.g. 5 for 5% off). Leave 0 for standard pricing.', 'foxfire-child' ),
					'default_value' => 0,
					'min'           => 0,
					'max'           => 100,
				),
				array(
					'key'           => 'field_foxfire_tier_5_discount',
					'label'         => __( '5-Vial Discount (%)', 'foxfire-child' ),
					'name'          => 'foxfire_tier_5_discount',
					'type'          => 'number',
					'instructions'  => __( 'Optional % savings when buying 5 vials (e.g. 10 for 10% off). Leave 0 for standard pricing.', 'foxfire-child' ),
					'default_value' => 0,
					'min'           => 0,
					'max'           => 100,
				),
			),
			'location'              => array(
				array(
					array(
						'param'    => 'post_type',
						'operator' => '==',
						'value'    => 'product',
					),
				),
			),
			'menu_order'            => 0,
			'position'              => 'normal',
			'style'                 => 'default',
			'label_placement'       => 'top',
			'instruction_placement' => 'label',
			'active'                => true,
		)
	);
}
add_action( 'acf/init', 'foxfire_register_product_field_group' );

/**
 * Batch / lot number for a product.
 */
function foxfire_get_product_batch_lot( int $product_id = 0 ): string {
	$product_id = $product_id > 0 ? $product_id : get_the_ID();
	$value      = foxfire_get_product_field( 'foxfire_batch_lot', $product_id );

	return is_string( $value ) ? $value : '';
}

/**
 * Primary COA URL for a product (URL field, or uploaded file fallback).
 */
function foxfire_get_product_coa_url( int $product_id = 0 ): string {
	$product_id = $product_id > 0 ? $product_id : get_the_ID();
	$url        = foxfire_get_product_field( 'foxfire_coa_url', $product_id );

	if ( is_string( $url ) && '' !== $url ) {
		return $url;
	}

	$file_url = foxfire_get_product_field( 'foxfire_coa_file', $product_id );

	return is_string( $file_url ) ? $file_url : '';
}

/**
 * COA link label for display.
 */
function foxfire_get_product_coa_label( int $product_id = 0 ): string {
	$product_id = $product_id > 0 ? $product_id : get_the_ID();
	$label      = foxfire_get_product_field( 'foxfire_coa_label', $product_id );

	if ( is_string( $label ) && '' !== $label ) {
		return $label;
	}

	return __( 'View Certificate of Analysis', 'foxfire-child' );
}

/**
 * Get simple testing result / summary for a product.
 */
function foxfire_get_product_testing_summary( int $product_id = 0 ): string {
	$product_id = $product_id > 0 ? $product_id : get_the_ID();
	$summary    = foxfire_get_product_field( 'foxfire_testing_summary', $product_id );

	if ( is_string( $summary ) && '' !== trim( $summary ) && 'Purity & Identity Verified' !== trim( $summary ) ) {
		return trim( $summary );
	}

	return __( 'Information Available', 'foxfire-child' );
}

/**
 * Check if 1/3/5 quantity tiers are enabled for a product.
 */
function foxfire_is_tier_pricing_enabled( int $product_id = 0 ): bool {
	$product_id = $product_id > 0 ? $product_id : get_the_ID();
	$enabled    = foxfire_get_product_field( 'foxfire_tier_enable', $product_id );

	// Default to enabled (true) if field is not explicitly set to 0/false
	return false !== $enabled && '0' !== $enabled;
}

/**
 * Get tier discount percentages (3-vial and 5-vial) for a product.
 *
 * @param int $product_id
 * @return array{3: float, 5: float}
 */
function foxfire_get_product_tier_discounts( int $product_id = 0 ): array {
	$product_id = $product_id > 0 ? $product_id : get_the_ID();

	$disc_3 = floatval( foxfire_get_product_field( 'foxfire_tier_3_discount', $product_id ) );
	$disc_5 = floatval( foxfire_get_product_field( 'foxfire_tier_5_discount', $product_id ) );

	return array(
		3 => max( 0.0, min( 100.0, $disc_3 ) ),
		5 => max( 0.0, min( 100.0, $disc_5 ) ),
	);
}

/**
 * Read an ACF/meta product field.
 */
function foxfire_get_product_field( string $field_name, int $product_id ): mixed {
	if ( function_exists( 'get_field' ) ) {
		$value = get_field( $field_name, $product_id );
		if ( null !== $value && false !== $value && '' !== $value ) {
			return $value;
		}
	}

	return get_post_meta( $product_id, $field_name, true );
}

/**
 * Whether ACF is available for product editing.
 */
function foxfire_product_fields_ready(): bool {
	return function_exists( 'acf_add_local_field_group' );
}
