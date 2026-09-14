<?php
/**
 * Checkout Payment Methods & Place Order Template — Chunk 1K.
 *
 * @package Foxfire_Child
 */

defined( 'ABSPATH' ) || exit;

$is_review_mode = function_exists( 'foxfire_operations_checkout_is_review_mode' ) && foxfire_operations_checkout_is_review_mode();

if ( $is_review_mode ) {
	$available_gateways = array();
	$no_payment_methods_message = foxfire_operations_checkout_review_message();
} elseif ( WC()->customer->get_billing_country() ) {
	$no_payment_methods_message = __( 'No payment methods are currently available for your billing location. Please verify your details or contact support.', 'foxfire-child' );
} else {
	$no_payment_methods_message = __( 'Enter your billing details to view available payment methods.', 'foxfire-child' );
}

if ( ! wp_doing_ajax() ) {
	do_action( 'woocommerce_review_order_before_payment' );
}
?>

<div id="payment" class="woocommerce-checkout-payment ff-checkout-payment">
	
	<h2 class="ff-checkout-payment__title"><?php esc_html_e( 'Payment Method', 'foxfire-child' ); ?></h2>

	<?php if ( $is_review_mode || WC()->cart->needs_payment() ) : ?>
		<?php if ( ! empty( $available_gateways ) ) : ?>
			<ul class="wc_payment_methods payment_methods methods ff-payment-methods">
				<?php foreach ( $available_gateways as $gateway ) : ?>
					<?php wc_get_template( 'checkout/payment-method.php', array( 'gateway' => $gateway ) ); ?>
				<?php endforeach; ?>
			</ul>
		<?php else : ?>
			<div class="ff-payment-notice" role="status" aria-atomic="true">
				<svg class="ff-payment-notice__icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true" focusable="false">
					<circle cx="12" cy="12" r="9" />
					<path d="M12 11v6m0-10v.01" />
				</svg>
				<p class="ff-payment-notice__message"><?php echo esc_html( apply_filters( 'woocommerce_no_available_payment_methods_message', $no_payment_methods_message ) ); ?></p>
			</div>
		<?php endif; ?>
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

		<?php echo apply_filters( 'woocommerce_order_button_html', '<button type="submit" class="button alt ff-place-order-btn' . ( $is_review_mode ? ' is-disabled' : '' ) . '" name="woocommerce_checkout_place_order" id="place_order" value="' . esc_attr( $order_button_text ) . '" data-value="' . esc_attr( $order_button_text ) . '"' . ( $is_review_mode ? ' data-review-mode="1" disabled' : '' ) . '>' . esc_html( $order_button_text ) . ' &rarr;</button>' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>

		<?php do_action( 'woocommerce_review_order_after_submit' ); ?>

		<?php wp_nonce_field( 'woocommerce-process_checkout', 'woocommerce-process-checkout-nonce' ); ?>
	</div>

</div>

<?php
if ( ! wp_doing_ajax() ) {
	do_action( 'woocommerce_review_order_after_payment' );
}
