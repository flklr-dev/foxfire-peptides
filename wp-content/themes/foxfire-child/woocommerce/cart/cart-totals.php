<?php
/**
 * Cart totals template — Chunk 1J.
 *
 * @package Foxfire_Child
 */

defined( 'ABSPATH' ) || exit;

?>
<div class="cart_totals <?php echo ( WC()->customer->has_calculated_shipping() ) ? 'calculated_shipping' : ''; ?> ff-cart-summary-card">

	<?php do_action( 'woocommerce_before_cart_totals' ); ?>

	<h2 class="ff-cart-summary-card__title"><?php esc_html_e( 'Order Summary', 'foxfire-child' ); ?></h2>

	<div class="ff-cart-summary-card__breakdown">
		<!-- Subtotal -->
		<div class="ff-cart-summary-row cart-subtotal">
			<span class="ff-cart-summary-label"><?php esc_html_e( 'Subtotal', 'foxfire-child' ); ?></span>
			<span class="ff-cart-summary-value"><?php wc_cart_totals_subtotal_html(); ?></span>
		</div>

		<!-- Coupons -->
		<?php foreach ( WC()->cart->get_coupons() as $code => $coupon ) : ?>
			<div class="ff-cart-summary-row cart-discount coupon-<?php echo esc_attr( sanitize_title( $code ) ); ?>">
				<span class="ff-cart-summary-label"><?php wc_cart_totals_coupon_label( $coupon ); ?></span>
				<span class="ff-cart-summary-value ff-cart-summary-value--discount"><?php wc_cart_totals_coupon_html( $coupon ); ?></span>
			</div>
		<?php endforeach; ?>

		<!-- Automatically selected shipping amount -->
		<?php if ( WC()->cart->needs_shipping() ) : ?>
			<div class="ff-cart-summary-row ff-cart-shipping-row">
				<span class="ff-cart-summary-label"><?php esc_html_e( 'Shipping', 'foxfire-child' ); ?></span>
				<div class="ff-cart-summary-value ff-checkout-shipping-pricing">
					<?php foxfire_render_shipping_methods(); ?>
				</div>
			</div>
		<?php endif; ?>

		<!-- Fees -->
		<?php foreach ( WC()->cart->get_fees() as $fee ) : ?>
			<div class="ff-cart-summary-row fee">
				<span class="ff-cart-summary-label"><?php echo esc_html( $fee->name ); ?></span>
				<span class="ff-cart-summary-value"><?php wc_cart_totals_fee_html( $fee ); ?></span>
			</div>
		<?php endforeach; ?>

		<!-- Tax -->
		<?php
		if ( wc_tax_enabled() && ! WC()->cart->display_prices_including_tax() ) {
			$taxable_address = WC()->customer->get_taxable_address();
			$estimated_text  = '';

			if ( WC()->customer->is_customer_outside_base() && ! WC()->customer->has_calculated_shipping() ) {
				/* translators: %s location. */
				$estimated_text = sprintf( ' <small>' . esc_html__( '(estimated for %s)', 'foxfire-child' ) . '</small>', WC()->countries->estimated_for_prefix( $taxable_address[0] ) . WC()->countries->countries[ $taxable_address[0] ] );
			}

			if ( 'itemized' === get_option( 'woocommerce_tax_total_display' ) ) {
				foreach ( WC()->cart->get_tax_totals() as $code => $tax ) { // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
					?>
					<div class="ff-cart-summary-row tax-rate tax-rate-<?php echo esc_attr( sanitize_title( $code ) ); ?>">
						<span class="ff-cart-summary-label"><?php echo esc_html( $tax->label ) . $estimated_text; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
						<span class="ff-cart-summary-value"><?php echo wp_kses_post( $tax->formatted_amount ); ?></span>
					</div>
					<?php
				}
			} else {
				?>
				<div class="ff-cart-summary-row tax-total">
					<span class="ff-cart-summary-label"><?php echo esc_html( WC()->countries->tax_or_vat() ) . $estimated_text; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
					<span class="ff-cart-summary-value"><?php wc_cart_totals_taxes_total_html(); ?></span>
				</div>
				<?php
			}
		}
		?>

		<?php do_action( 'woocommerce_cart_totals_before_order_total' ); ?>

		<!-- Total -->
		<div class="ff-cart-summary-row ff-cart-summary-row--total order-total">
			<span class="ff-cart-summary-label"><?php esc_html_e( 'Total', 'foxfire-child' ); ?></span>
			<span class="ff-cart-summary-value"><?php wc_cart_totals_order_total_html(); ?></span>
		</div>

		<?php do_action( 'woocommerce_cart_totals_after_order_total' ); ?>
	</div>

	<!-- Primary Fox Orange Checkout Button -->
	<div class="wc-proceed-to-checkout ff-cart-proceed-wrap">
		<?php do_action( 'woocommerce_proceed_to_checkout' ); ?>
	</div>

	<!-- Embedded Foxfire Quality & Transparency Box -->
	<div class="ff-cart-guarantee-box">
		<h3 class="ff-cart-guarantee-box__title"><?php esc_html_e( 'FOXFIRE QUALITY & TRANSPARENCY', 'foxfire-child' ); ?></h3>
		<ul class="ff-cart-guarantee-box__list">
			<li>
				<span class="ff-guarantee-icon" aria-hidden="true">
					<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2v4a2 2 0 0 0 2 2h4"/><path d="M4 22h16a2 2 0 0 0 2-2V7.5L14.5 2H6a2 2 0 0 0-2 2v4"/><path d="m3 15 2 2 4-4"/></svg>
				</span>
				<div>
					<strong><?php esc_html_e( 'Batch & COA Access', 'foxfire-child' ); ?></strong>
					<p><?php esc_html_e( 'View available testing information and batch/lot documentation.', 'foxfire-child' ); ?></p>
				</div>
			</li>
			<li>
				<span class="ff-guarantee-icon" aria-hidden="true">
					<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg>
				</span>
				<div>
					<strong><?php esc_html_e( 'Transparent Product Information', 'foxfire-child' ); ?></strong>
					<p><?php esc_html_e( 'Clear product, inventory, and testing information in one place.', 'foxfire-child' ); ?></p>
				</div>
			</li>
			<li>
				<span class="ff-guarantee-icon" aria-hidden="true">
					<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10"/></svg>
				</span>
				<div>
					<strong><?php esc_html_e( 'Research Use Only', 'foxfire-child' ); ?></strong>
					<p><?php esc_html_e( 'Products are presented for research-use purposes.', 'foxfire-child' ); ?></p>
				</div>
			</li>
		</ul>
	</div>

	<?php do_action( 'woocommerce_after_cart_totals' ); ?>

</div>
