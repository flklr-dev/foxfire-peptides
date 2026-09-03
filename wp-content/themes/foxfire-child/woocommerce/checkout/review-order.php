<?php
/**
 * Review Order Table / Summary Template — Chunk 1K.
 *
 * @package Foxfire_Child
 */

defined( 'ABSPATH' ) || exit;
?>

<div class="ff-checkout-review-wrap">

	<!-- Products Ordered Header -->
	<div class="ff-checkout-review-header">
		<h3 class="ff-checkout-review-title"><?php esc_html_e( 'Products Ordered', 'foxfire-child' ); ?></h3>
	</div>

	<!-- Itemized Compounds List -->
	<div class="ff-checkout-items-list">
		<?php
		do_action( 'woocommerce_review_order_before_cart_contents' );

		foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) {
			$_product = apply_filters( 'woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_key );

			if ( ! $_product || ! $_product->exists() || $cart_item['quantity'] <= 0 || ! apply_filters( 'woocommerce_checkout_cart_item_visible', true, $cart_item, $cart_item_key ) ) {
				continue;
			}

			$product_id        = apply_filters( 'woocommerce_cart_item_product_id', $cart_item['product_id'], $cart_item, $cart_item_key );
			$product_permalink = apply_filters( 'woocommerce_cart_item_permalink', $_product->is_visible() ? $_product->get_permalink( $cart_item ) : '', $cart_item, $cart_item_key );
			$thumbnail         = apply_filters( 'woocommerce_cart_item_thumbnail', $_product->get_image( 'woocommerce_thumbnail' ), $cart_item, $cart_item_key );
			$batch_lot         = get_field( 'foxfire_batch_lot', $product_id );
			$subtotal          = apply_filters( 'woocommerce_cart_item_subtotal', WC()->cart->get_product_subtotal( $_product, $cart_item['quantity'] ), $cart_item, $cart_item_key );
			?>
			<div class="ff-checkout-item <?php echo esc_attr( apply_filters( 'woocommerce_cart_item_class', 'cart_item', $cart_item, $cart_item_key ) ); ?>">
				
				<!-- Product Media with Qty Bubble -->
				<div class="ff-checkout-item__media">
					<?php echo $thumbnail; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					<span class="ff-checkout-item__qty-badge"><?php echo esc_html( (string) $cart_item['quantity'] ); ?></span>
				</div>

				<!-- Product Info -->
				<div class="ff-checkout-item__info">
					<h3 class="ff-checkout-item__title"><?php echo wp_kses_post( $_product->get_name() ); ?></h3>
					
					<div class="ff-checkout-item__meta">
						<?php echo wc_get_formatted_cart_item_data( $cart_item ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					</div>
				</div>

				<!-- Subtotal -->
				<div class="ff-checkout-item__total">
					<span><?php echo $subtotal; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
				</div>

			</div>
			<?php
		}

		do_action( 'woocommerce_review_order_after_cart_contents' );
		?>
	</div>

	<!-- Pricing Breakdown -->
	<div class="ff-checkout-breakdown">
		
		<!-- Subtotal -->
		<div class="ff-checkout-row cart-subtotal">
			<span class="ff-checkout-row__label"><?php esc_html_e( 'Subtotal', 'foxfire-child' ); ?></span>
			<span class="ff-checkout-row__value"><?php wc_cart_totals_subtotal_html(); ?></span>
		</div>

		<!-- Coupons -->
		<?php foreach ( WC()->cart->get_coupons() as $code => $coupon ) : ?>
			<div class="ff-checkout-row cart-discount coupon-<?php echo esc_attr( sanitize_title( $code ) ); ?>">
				<span class="ff-checkout-row__label"><?php wc_cart_totals_coupon_label( $coupon ); ?></span>
				<span class="ff-checkout-row__value ff-checkout-row__value--discount"><?php wc_cart_totals_coupon_html( $coupon ); ?></span>
			</div>
		<?php endforeach; ?>

		<!-- Shipping / Shipment Row -->
		<?php if ( WC()->cart->needs_shipping() ) : ?>
			<?php
			$raw_subtotal     = is_object( WC()->cart ) ? (float) WC()->cart->get_displayed_subtotal() : 0.0;
			$is_free_shipping = ( $raw_subtotal >= 150.00 );
			?>
			<?php do_action( 'woocommerce_review_order_before_shipping' ); ?>
			<div class="ff-checkout-row ff-checkout-shipping-row">
				<span class="ff-checkout-row__label"><?php esc_html_e( 'Shipment', 'foxfire-child' ); ?></span>
				<div class="ff-checkout-shipping-pricing ff-checkout-row__value">
					<?php if ( $is_free_shipping ) : ?>
						<del class="ff-shipping-was-price"><?php echo wp_kses_post( wc_price( 9.95 ) ); ?></del>
						<ins class="ff-shipping-now-price"><?php echo wp_kses_post( wc_price( 0.00 ) ); ?></ins>
					<?php else : ?>
						<span class="ff-shipping-now-price"><?php echo wp_kses_post( wc_price( 9.95 ) ); ?></span>
					<?php endif; ?>
				</div>

				<!-- Hidden WooCommerce shipping inputs for form processing -->
				<div class="screen-reader-text" style="display:none !important;">
					<?php wc_cart_totals_shipping_html(); ?>
				</div>
			</div>
			<?php do_action( 'woocommerce_review_order_after_shipping' ); ?>
		<?php endif; ?>

		<!-- Fees -->
		<?php foreach ( WC()->cart->get_fees() as $fee ) : ?>
			<div class="ff-checkout-row fee">
				<span class="ff-checkout-row__label"><?php echo esc_html( $fee->name ); ?></span>
				<span class="ff-checkout-row__value"><?php wc_cart_totals_fee_html( $fee ); ?></span>
			</div>
		<?php endforeach; ?>

		<!-- Tax -->
		<?php if ( wc_tax_enabled() && ! WC()->cart->display_prices_including_tax() ) : ?>
			<?php if ( 'itemized' === get_option( 'woocommerce_tax_total_display' ) ) : ?>
				<?php foreach ( WC()->cart->get_tax_totals() as $code => $tax ) : // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited ?>
					<div class="ff-checkout-row tax-rate tax-rate-<?php echo esc_attr( sanitize_title( $code ) ); ?>">
						<span class="ff-checkout-row__label"><?php echo esc_html( $tax->label ); ?></span>
						<span class="ff-checkout-row__value"><?php echo wp_kses_post( $tax->formatted_amount ); ?></span>
					</div>
				<?php endforeach; ?>
			<?php else : ?>
				<div class="ff-checkout-row tax-total">
					<span class="ff-checkout-row__label"><?php echo esc_html( WC()->countries->tax_or_vat() ); ?></span>
					<span class="ff-checkout-row__value"><?php wc_cart_totals_taxes_total_html(); ?></span>
				</div>
			<?php endif; ?>
		<?php endif; ?>

		<?php do_action( 'woocommerce_review_order_before_order_total' ); ?>

		<!-- Total -->
		<div class="ff-checkout-row ff-checkout-row--total order-total">
			<span class="ff-checkout-row__label"><?php esc_html_e( 'Total', 'foxfire-child' ); ?></span>
			<span class="ff-checkout-row__value ff-checkout-row__value--total"><?php wc_cart_totals_order_total_html(); ?></span>
		</div>

		<?php do_action( 'woocommerce_review_order_after_order_total' ); ?>

	</div>

</div>
