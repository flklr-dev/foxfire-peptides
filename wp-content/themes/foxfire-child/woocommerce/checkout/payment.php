<?php
/**
 * Checkout Payment Methods & Place Order Template — Chunk 1K.
 *
 * @package Foxfire_Child
 */

defined( 'ABSPATH' ) || exit;

if ( ! wp_doing_ajax() ) {
	do_action( 'woocommerce_review_order_before_payment' );
}
?>

<div id="payment" class="woocommerce-checkout-payment ff-checkout-payment">
	
	<h3 class="ff-checkout-payment__title"><?php esc_html_e( 'Payment Method', 'foxfire-child' ); ?></h3>

	<?php if ( WC()->cart->needs_payment() ) : ?>
		<ul class="wc_payment_methods payment_methods methods ff-payment-methods">
			<?php
			if ( ! empty( $available_gateways ) ) {
				foreach ( $available_gateways as $gateway ) {
					wc_get_template( 'checkout/payment-method.php', array( 'gateway' => $gateway ) );
				}
			} else {
				echo '<li class="woocommerce-notice woocommerce-notice--info woocommerce-info">' . esc_html( apply_filters( 'woocommerce_no_available_payment_methods_message', WC()->customer->get_billing_country() ? __( 'Sorry, it seems that there are no available payment methods for your state. Please contact us if you require assistance or wish to make alternate arrangements.', 'foxfire-child' ) : __( 'Please fill in your details above to see available payment methods.', 'foxfire-child' ) ) ) . '</li>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			}
			?>
		</ul>
	<?php endif; ?>

	<div class="form-row place-order ff-checkout-place-order">
		<noscript>
			<?php
			/* translators: $1. link to terms page, $2. closing a tag */
			printf( esc_html__( 'Since your browser does not support JavaScript, or it is disabled, please ensure you click the %1$sUpdate Totals%2$s button before placing your order. You may be charged more than the amount stated above if you fail to do so.', 'foxfire-child' ), '<a href="' . esc_url( wc_get_cart_url() ) . '">', '</a>' );
			?>
			<br/><button type="submit" class="button alt" name="woocommerce_checkout_update_totals" value="<?php esc_attr_e( 'Update totals', 'foxfire-child' ); ?>"><?php esc_html_e( 'Update totals', 'foxfire-child' ); ?></button>
		</noscript>

		<?php wc_get_template( 'checkout/terms.php' ); ?>

		<?php do_action( 'woocommerce_review_order_before_submit' ); ?>

		<?php echo apply_filters( 'woocommerce_order_button_html', '<button type="submit" class="button alt ff-place-order-btn" name="woocommerce_checkout_place_order" id="place_order" value="' . esc_attr( $order_button_text ) . '" data-value="' . esc_attr( $order_button_text ) . '">' . esc_html( $order_button_text ) . ' &rarr;</button>' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>

		<?php do_action( 'woocommerce_review_order_after_submit' ); ?>

		<?php wp_nonce_field( 'woocommerce-process_checkout', 'woocommerce-process-checkout-nonce' ); ?>
	</div>

	<!-- Point-of-Purchase Guarantee & Trust Assurances -->
	<div class="ff-checkout-guarantee-box">
		<ul class="ff-checkout-guarantee-list">
			<li>
				<span class="ff-guarantee-icon" aria-hidden="true">
					<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg>
				</span>
				<span><strong><?php esc_html_e( 'Secure Checkout', 'foxfire-child' ); ?></strong> &mdash; <?php esc_html_e( 'Encrypted and protected order processing.', 'foxfire-child' ); ?></span>
			</li>
			<li>
				<span class="ff-guarantee-icon" aria-hidden="true">
					<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><rect width="20" height="14" x="2" y="5" rx="2"/><line x1="2" y1="10" x2="22" y2="10"/></svg>
				</span>
				<span><strong><?php esc_html_e( 'Discreet Tracked Shipping', 'foxfire-child' ); ?></strong> &mdash; <?php esc_html_e( 'Fast dispatch with tracking on all orders.', 'foxfire-child' ); ?></span>
			</li>
			<li>
				<span class="ff-guarantee-icon" aria-hidden="true">
					<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10"/></svg>
				</span>
				<span><strong><?php esc_html_e( 'Quality Guaranteed', 'foxfire-child' ); ?></strong> &mdash; <?php esc_html_e( 'Independent batch testing & verified purity.', 'foxfire-child' ); ?></span>
			</li>
		</ul>
	</div>

</div>

<?php
if ( ! wp_doing_ajax() ) {
	do_action( 'woocommerce_review_order_after_payment' );
}
