<?php
/**
 * Product custom-field read helpers — Chunk 1E & 1G.
 *
 * Field registration and validation are owned by the theme-independent
 * Foxfire Operations plugin. These helpers keep storefront presentation
 * isolated from administrative business rules.
 *
 * @package Foxfire_Child
 */

defined( 'ABSPATH' ) || exit;

/**
 * Batch / lot number for a product.
 */
function foxfire_get_product_batch_lot( int $product_id = 0 ): string {
	$product_id = $product_id > 0 ? $product_id : get_the_ID();
	$value      = foxfire_get_product_field( 'foxfire_batch_lot', $product_id );

	return is_string( $value ) ? $value : '';
}

/**
 * Primary COA URL for a product (uploaded file first, then URL fallback).
 */
function foxfire_get_product_coa_url( int $product_id = 0 ): string {
	$product_id = $product_id > 0 ? $product_id : get_the_ID();
	$file_url   = foxfire_get_product_field( 'foxfire_coa_file', $product_id );

	if ( is_string( $file_url ) && '' !== $file_url ) {
		return $file_url;
	}

	$url = foxfire_get_product_field( 'foxfire_coa_url', $product_id );

	return is_string( $url ) ? $url : '';
}

/**
 * Whether the product has a batch-specific COA document rather than a link
 * back to the general directory.
 */
function foxfire_product_has_coa_document( int $product_id = 0 ): bool {
	$coa_url = trim( foxfire_get_product_coa_url( $product_id ) );

	if ( '' === $coa_url || '#' === $coa_url ) {
		return false;
	}

	$directory_url  = foxfire_get_page_url( 'testing-coa', '/testing-coa/' );
	$coa_path       = untrailingslashit( (string) wp_parse_url( $coa_url, PHP_URL_PATH ) );
	$directory_path = untrailingslashit( (string) wp_parse_url( $directory_url, PHP_URL_PATH ) );

	return '' === $directory_path || $coa_path !== $directory_path;
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

	return __( 'Not provided', 'foxfire-child' );
}

/**
 * Check if 1/3/5 quantity tiers are enabled for a product.
 */
function foxfire_is_tier_pricing_enabled( int $product_id = 0 ): bool {
	$product_id = $product_id > 0 ? $product_id : get_the_ID();
	if ( function_exists( 'foxfire_operations_tier_pricing_enabled' ) ) {
		return foxfire_operations_tier_pricing_enabled( $product_id );
	}
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
		3 => max( 0.0, min( 50.0, $disc_3 ) ),
		5 => max( 0.0, min( 50.0, $disc_5 ) ),
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
