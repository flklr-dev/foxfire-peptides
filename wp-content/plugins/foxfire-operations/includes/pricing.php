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

	ksort( $discounts, SORT_NUMERIC );
	foreach ( $discounts as $minimum => $percentage ) {
		if ( $quantity >= (int) $minimum ) {
			$discount = (float) $percentage;
		}
	}

	$discount = min( 50, max( 0, $discount ) );

	return $base_price * ( 1 - ( $discount / 100 ) );
}

/** Validate an entire configuration atomically; invalid saves retain prior settings. */
function foxfire_operations_validate_quantity_options( $rows ) {
	if ( ! is_array( $rows ) || ! count( $rows ) || count( $rows ) > 12 ) {
		return new WP_Error( 'quantity_rows', __( 'Add between 1 and 12 quantity options.', 'foxfire-operations' ) );
	}
	$options = array();
	foreach ( $rows as $row ) {
		if ( ! is_array( $row ) || ! isset( $row['quantity'], $row['mode'], $row['value'] ) || ! is_scalar( $row['quantity'] ) || ! is_scalar( $row['value'] ) ) {
			return new WP_Error( 'quantity_row', __( 'Each quantity option needs a quantity, pricing type and value.', 'foxfire-operations' ) );
		}
		$quantity = filter_var( $row['quantity'], FILTER_VALIDATE_INT, array( 'options' => array( 'min_range' => 1, 'max_range' => 10000 ) ) );
		$mode = $row['mode'];
		if ( ! preg_match( '/^[0-9.,\s]+$/D', (string) $row['value'] ) ) {
			return new WP_Error( 'quantity_number', __( 'Enter a valid numeric discount or total price.', 'foxfire-operations' ) );
		}
		$value = wc_format_decimal( $row['value'] );
		if ( ! $quantity || isset( $options[ $quantity ] ) || ! in_array( $mode, array( 'discount', 'total' ), true ) || '' === $value || ! is_numeric( $value ) || ! is_finite( (float) $value ) || (float) $value < 0 || ( 'discount' === $mode && (float) $value > 100 ) || ( 'total' === $mode && (float) $value > 1000000 ) ) {
			return new WP_Error( 'quantity_value', __( 'Use unique whole quantities from 1 to 10,000, discounts from 0 to 100%, and non-negative total prices up to 1,000,000.', 'foxfire-operations' ) );
		}
		$options[ $quantity ] = array( 'mode' => $mode, 'value' => 'total' === $mode ? wc_format_decimal( $value, wc_get_price_decimals() ) : $value );
	}
	ksort( $options, SORT_NUMERIC );
	return $options;
}

/** Read configured quantities, falling back to existing 1/3/5 settings without a migration. */
function foxfire_operations_get_quantity_options( $product_id, $variation_id = 0 ) {
	$stored = get_post_meta( $product_id, '_foxfire_quantity_options', true );
	$rows = array();
	if ( is_array( $stored ) ) {
		foreach ( $stored as $quantity => $rule ) {
			$rows[] = is_array( $rule ) ? array_merge( $rule, array( 'quantity' => $quantity ) ) : array();
		}
	}
	$options = $rows ? foxfire_operations_validate_quantity_options( $rows ) : null;
	if ( ! is_array( $options ) ) {
		$legacy = foxfire_operations_get_tier_discounts( $product_id );
		$options = array( 1 => array( 'mode' => 'discount', 'value' => '0' ) );
		foreach ( $legacy as $quantity => $discount ) {
			$options[ $quantity ] = array( 'mode' => 'discount', 'value' => (string) $discount );
		}
	}
	if ( $variation_id && (int) wp_get_post_parent_id( $variation_id ) === (int) $product_id ) {
		$overrides = get_post_meta( $variation_id, '_foxfire_quantity_options', true );
		$override_rows = array();
		foreach ( is_array( $overrides ) ? $overrides : array() as $quantity => $rule ) {
			if ( isset( $options[ $quantity ] ) && is_array( $rule ) ) {
				$override_rows[] = array_merge( $rule, array( 'quantity' => $quantity ) );
			}
		}
		$validated = $override_rows ? foxfire_operations_validate_quantity_options( $override_rows ) : null;
		if ( is_array( $validated ) ) {
			$options = array_replace( $options, $validated );
		}
	}
	return $options;
}

/** Highest qualifying row determines the unit price, also for cart quantities between presets. */
function foxfire_operations_quantity_unit_price( $base_price, $quantity, $options ) {
	$unit_price = max( 0, (float) $base_price );
	ksort( $options, SORT_NUMERIC );
	foreach ( $options as $minimum => $rule ) {
		if ( (int) $quantity >= (int) $minimum ) {
			$unit_price = 'total' === $rule['mode'] ? (float) $rule['value'] / (int) $minimum : (float) $base_price * ( 1 - (float) $rule['value'] / 100 );
		}
	}
	return max( 0, $unit_price );
}

/** Storefront prices use WooCommerce tax-display rules; submitted prices are never trusted. */
function foxfire_operations_quantity_display_prices( $product, $options ) {
	$prices = array();
	foreach ( $options as $quantity => $rule ) {
		$unit = foxfire_operations_quantity_unit_price( $product->get_price(), $quantity, $options );
		$total = wc_get_price_to_display( $product, array( 'price' => $unit, 'qty' => $quantity ) );
		$prices[ $quantity ] = array( 'html' => wc_price( $total ), 'total' => $total, 'discount' => 'discount' === $rule['mode'] ? (float) $rule['value'] : 0 );
	}
	return $prices;
}

function foxfire_operations_variation_quantity_prices( $data, $parent, $variation ) {
	if ( foxfire_operations_tier_pricing_enabled( $parent->get_id() ) ) {
		$data['foxfire_quantity_prices'] = foxfire_operations_quantity_display_prices( $variation, foxfire_operations_get_quantity_options( $parent->get_id(), $variation->get_id() ) );
	}
	return $data;
}
add_filter( 'woocommerce_available_variation', 'foxfire_operations_variation_quantity_prices', 10, 3 );

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
			$unit_price = foxfire_operations_quantity_unit_price(
				$unit_price,
				isset( $cart_item['quantity'] ) ? $cart_item['quantity'] : 0,
				foxfire_operations_get_quantity_options( $product_id, $price_id !== $product_id ? $price_id : 0 )
			);
		}

		$cart_item['data']->set_price( wc_format_decimal( $unit_price ) );
	}
}
add_action( 'woocommerce_before_calculate_totals', 'foxfire_operations_apply_tier_discounts', 20 );
