<?php
/**
 * Checkout Payment Methods & Place Order Template — Chunk 1K.
 *
 * @package Foxfire_Child
 */

defined( 'ABSPATH' ) || exit;

$site_host              = (string) wp_parse_url( home_url( '/' ), PHP_URL_HOST );
$is_staging_hostname    = 0 === stripos( $site_host, 'staging.' );
$is_private_environment = 'production' !== wp_get_environment_type() || $is_staging_hostname;

if ( $is_private_environment ) {
	$no_payment_methods_message = __( 'Checkout is in review mode. Payment methods are intentionally disabled on this staging site, so no order will be submitted.', 'foxfire-child' );
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

	<?php if ( WC()->cart->needs_payment() ) : ?>
		<ul class="wc_payment_methods payment_methods methods ff-payment-methods">
			<?php
			if ( ! empty( $available_gateways ) ) {
				foreach ( $available_gateways as $gateway ) {
					wc_get_template( 'checkout/payment-method.php', array( 'gateway' => $gateway ) );
				}
			} else {
				echo '<li class="woocommerce-notice woocommerce-notice--info woocommerce-info">' . esc_html( apply_filters( 'woocommerce_no_available_payment_methods_message', $no_payment_methods_message ) ) . '</li>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
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

</div>

<?php
if ( ! wp_doing_ajax() ) {
	do_action( 'woocommerce_review_order_after_payment' );
}
