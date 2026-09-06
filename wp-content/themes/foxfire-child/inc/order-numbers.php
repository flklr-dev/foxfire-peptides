<?php
/**
 * Secure Order Numbers System — Foxfire Peptides.
 *
 * Replaces sequential, guessable WordPress post IDs (195, 196, 197...)
 * with industry-standard, branded, randomized 6-digit order identifiers (e.g. FF-849201).
 * Masks URLs, handles order lookup, admin search, and customer endpoints.
 *
 * @package Foxfire_Child
 */

defined( 'ABSPATH' ) || exit;

/**
 * Generate a cryptographically secure, unique 6-digit order number with 'FF-' prefix.
 *
 * @return string E.g. "FF-849201"
 */
function foxfire_generate_unique_order_number(): string {
	global $wpdb;

	for ( $attempts = 0; $attempts < 25; $attempts++ ) {
		$random_digits = (string) wp_rand( 100000, 999999 );
		$candidate     = 'FF-' . $random_digits;

		// Check if candidate already exists in order metadata
		$exists = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key = '_foxfire_order_number' AND meta_value = %s LIMIT 1",
				$candidate
			)
		);

		if ( ! $exists ) {
			return $candidate;
		}
	}

	// Fallback using timestamp component + random
	return 'FF-' . substr( (string) time(), -4 ) . (string) wp_rand( 10, 99 );
}

/**
 * Assign and persist a secure order number for a WooCommerce order if not already set.
 *
 * @param WC_Order|int $order Order object or ID.
 * @return string Secure order number.
 */
function foxfire_assign_secure_order_number( $order ): string {
	if ( is_numeric( $order ) ) {
		$order = wc_get_order( (int) $order );
	}

	if ( ! $order instanceof WC_Order ) {
		return '';
	}

	$existing = $order->get_meta( '_foxfire_order_number', true );
	if ( ! empty( $existing ) ) {
		return (string) $existing;
	}

	$new_number = foxfire_generate_unique_order_number();
	$order->update_meta_data( '_foxfire_order_number', $new_number );
	$order->save_meta_data();

	return $new_number;
}

/**
 * Look up the real numeric post ID for an order given a secure order number or identifier.
 * Supports "FF-849201", "849201", "#FF-849201", and legacy numeric post IDs.
 *
 * @param string|int $identifier Order number or ID.
 * @return int Real order post ID, or 0 if not found.
 */
function foxfire_get_order_id_by_custom_number( $identifier ): int {
	global $wpdb;

	$identifier = trim( (string) $identifier );
	if ( empty( $identifier ) ) {
		return 0;
	}

	// Clean up leading '#'
	$clean_term  = ltrim( $identifier, '#' );
	$with_prefix = ( 0 === strpos( $clean_term, 'FF-' ) ) ? $clean_term : 'FF-' . $clean_term;

	// 1. Search in order metadata
	$order_id = $wpdb->get_var(
		$wpdb->prepare(
			"SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key = '_foxfire_order_number' AND (meta_value = %s OR meta_value = %s) LIMIT 1",
			$clean_term,
			$with_prefix
		)
	);

	if ( $order_id ) {
		return (int) $order_id;
	}

	// 2. Legacy fallback: check if it's already a valid numeric shop_order ID
	if ( ctype_digit( $identifier ) ) {
		$check = (int) $identifier;
		if ( 'shop_order' === get_post_type( $check ) ) {
			return $check;
		}
	}

	return 0;
}

/**
 * Filter WooCommerce order number to return the secure, branded order number.
 */
add_filter( 'woocommerce_order_number', function( $order_number, $order ) {
	if ( ! $order instanceof WC_Order ) {
		$order = wc_get_order( $order_number );
	}

	if ( $order instanceof WC_Order ) {
		$custom = $order->get_meta( '_foxfire_order_number', true );
		if ( ! empty( $custom ) ) {
			return (string) $custom;
		}
		return foxfire_assign_secure_order_number( $order );
	}

	return (string) $order_number;
}, 10, 2 );

/**
 * Automatically assign secure order number on order creation.
 */
add_action( 'woocommerce_checkout_order_created', 'foxfire_assign_secure_order_number', 10, 1 );
add_action( 'woocommerce_new_order', 'foxfire_assign_secure_order_number', 10, 1 );

/**
 * Mask Order Received (Thank You) URL to use the secure order number instead of database ID.
 */
add_filter( 'woocommerce_get_checkout_order_received_url', function( string $url, $order ): string {
	if ( $order instanceof WC_Order ) {
		$custom_num = $order->get_order_number();
		$order_id   = $order->get_id();
		if ( ! empty( $custom_num ) && (string) $custom_num !== (string) $order_id ) {
			$url = str_replace( "/order-received/{$order_id}/", "/order-received/{$custom_num}/", $url );
		}
	}
	return $url;
}, 20, 2 );

/**
 * Mask View Order URL in My Account to use the secure order number.
 */
add_filter( 'woocommerce_get_view_order_url', function( string $url, $order ): string {
	if ( $order instanceof WC_Order ) {
		$custom_num = $order->get_order_number();
		$order_id   = $order->get_id();
		if ( ! empty( $custom_num ) && (string) $custom_num !== (string) $order_id ) {
			$url = str_replace( "/view-order/{$order_id}/", "/view-order/{$custom_num}/", $url );
		}
	}
	return $url;
}, 20, 2 );

/**
 * Intercept WordPress query vars for order-received and view-order endpoints.
 * Translates custom order numbers (FF-XXXXXX) into the real numeric post ID
 * so WooCommerce controllers render the order properly without 404s.
 */
function foxfire_resolve_custom_order_endpoint_query_vars( $wp ): void {
	if ( ! is_object( $wp ) || empty( $wp->query_vars ) ) {
		return;
	}

	foreach ( array( 'order-received', 'view-order' ) as $endpoint ) {
		if ( ! empty( $wp->query_vars[ $endpoint ] ) ) {
			$val = (string) $wp->query_vars[ $endpoint ];
			// If not a small post ID, resolve from custom number
			$real_id = foxfire_get_order_id_by_custom_number( $val );
			if ( $real_id > 0 ) {
				$wp->query_vars[ $endpoint ] = $real_id;
				set_query_var( $endpoint, $real_id );
			}
		}
	}
}
add_action( 'parse_request', 'foxfire_resolve_custom_order_endpoint_query_vars', 1 );
add_action( 'template_redirect', function(): void {
	global $wp;
	foxfire_resolve_custom_order_endpoint_query_vars( $wp );
}, 5 );

/**
 * Allow resolving order in Thank You page shortcode handler.
 */
add_filter( 'woocommerce_thankyou_order_id', function( $order_id ) {
	if ( ! empty( $order_id ) && ! is_numeric( $order_id ) ) {
		$real_id = foxfire_get_order_id_by_custom_number( (string) $order_id );
		if ( $real_id > 0 ) {
			return $real_id;
		}
	}
	return $order_id;
}, 10, 1 );

/**
 * Enable searching orders by secure order number in WP Admin order list.
 */
add_filter( 'woocommerce_shop_order_search_results', function( array $order_ids, string $term ): array {
	global $wpdb;

	$term = trim( $term );
	if ( empty( $term ) ) {
		return $order_ids;
	}

	$clean_term  = ltrim( $term, '#' );
	$with_prefix = ( 0 === strpos( $clean_term, 'FF-' ) ) ? $clean_term : 'FF-' . $clean_term;

	$matched = $wpdb->get_col(
		$wpdb->prepare(
			"SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key = '_foxfire_order_number' AND (meta_value = %s OR meta_value = %s)",
			$clean_term,
			$with_prefix
		)
	);

	if ( ! empty( $matched ) ) {
		$order_ids = array_unique( array_merge( $order_ids, array_map( 'intval', $matched ) ) );
	}

	return $order_ids;
}, 10, 2 );

/**
 * Allow customer order tracking by secure order number.
 */
add_filter( 'woocommerce_shortcode_order_tracking_order_id', function( $order_id ) {
	if ( ! empty( $_REQUEST['orderid'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$input_val = sanitize_text_field( wp_unslash( $_REQUEST['orderid'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$real_id   = foxfire_get_order_id_by_custom_number( $input_val );
		if ( $real_id > 0 ) {
			return $real_id;
		}
	}
	return $order_id;
}, 10, 1 );
