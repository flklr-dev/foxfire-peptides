<?php
/**
 * Quantity-tier pricing owned by the operations plugin.
 *
 * @package Foxfire_Operations
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Determine whether quantity pricing is enabled for a product.
 *
 * Empty legacy values remain enabled so existing products keep their previous
 * storefront behaviour until a manager explicitly disables the feature.
 *
 * @param int $product_id Product ID.
 * @return bool
 */
function foxfire_operations_tier_pricing_enabled( $product_id ) {
	$value = get_post_meta( $product_id, 'foxfire_tier_enable', true );

	return '' === $value || '0' !== (string) $value;
}

/**
 * Return the validated quantity-tier percentages for a product.
 *
 * @param int $product_id Product ID.
 * @return array{3: float, 5: float}
 */
function foxfire_operations_get_tier_discounts( $product_id ) {
	return array(
		3 => min( 50, max( 0, (float) get_post_meta( $product_id, 'foxfire_tier_3_discount', true ) ) ),
		5 => min( 50, max( 0, (float) get_post_meta( $product_id, 'foxfire_tier_5_discount', true ) ) ),
	);
}

/**
 * Calculate a unit price using the highest qualifying quantity tier.
 *
 * This is intentionally a pure function so the pricing rule can be tested
 * without constructing a WooCommerce cart session.
 *
 * @param float $base_price Active WooCommerce unit price.
 * @param int   $quantity   Cart quantity.
 * @param array $discounts  Tier percentages keyed by minimum quantity.
 * @return float
 */
function foxfire_operations_calculate_tier_unit_price( $base_price, $quantity, $discounts ) {
	$base_price = max( 0, (float) $base_price );
	$quantity   = max( 0, absint( $quantity ) );
	$discount   = 0;

	if ( $quantity >= 5 && isset( $discounts[5] ) ) {
		$discount = (float) $discounts[5];
	} elseif ( $quantity >= 3 && isset( $discounts[3] ) ) {
		$discount = (float) $discounts[3];
	}

	$discount = min( 50, max( 0, $discount ) );

	return $base_price * ( 1 - ( $discount / 100 ) );
}

/**
 * Apply product quantity tiers to cart line-item unit prices.
 *
 * The base is the product's current stored WooCommerce price. This means an
 * active sale price is discounted when tiers are enabled. WooCommerce coupons
 * are then calculated by WooCommerce after these line prices are established.
 *
 * @param WC_Cart $cart WooCommerce cart instance.
 * @return void
 */
function foxfire_operations_apply_tier_discounts( $cart ) {
	if ( is_admin() && ! wp_doing_ajax() ) {
		return;
	}

	if ( ! $cart instanceof WC_Cart ) {
		return;
	}

	foreach ( $cart->get_cart() as $cart_item ) {
		if ( empty( $cart_item['data'] ) || ! $cart_item['data'] instanceof WC_Product ) {
			continue;
		}

		$product_id = ! empty( $cart_item['product_id'] ) ? absint( $cart_item['product_id'] ) : 0;
		$price_id   = ! empty( $cart_item['variation_id'] ) ? absint( $cart_item['variation_id'] ) : $product_id;

		if ( ! $product_id || ! $price_id ) {
			continue;
		}

		$base_price = get_post_meta( $price_id, '_price', true );
		if ( '' === $base_price ) {
			$source_product = wc_get_product( $price_id );
			$base_price     = $source_product ? $source_product->get_price( 'edit' ) : '';
		}

		if ( '' === $base_price || ! is_numeric( $base_price ) ) {
			continue;
		}

		$unit_price = (float) $base_price;
		if ( foxfire_operations_tier_pricing_enabled( $product_id ) ) {
			$unit_price = foxfire_operations_calculate_tier_unit_price(
				$unit_price,
				isset( $cart_item['quantity'] ) ? $cart_item['quantity'] : 0,
				foxfire_operations_get_tier_discounts( $product_id )
			);
		}

		$cart_item['data']->set_price( wc_format_decimal( $unit_price ) );
	}
}
add_action( 'woocommerce_before_calculate_totals', 'foxfire_operations_apply_tier_discounts', 20 );

