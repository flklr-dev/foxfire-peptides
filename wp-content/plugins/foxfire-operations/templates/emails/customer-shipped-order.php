<?php
/**
 * Customer shipped order email.
 *
 * @package Foxfire_Operations
 */

defined( 'ABSPATH' ) || exit;

do_action( 'woocommerce_email_header', $email_heading, $email );
?>
<p>
	<?php
	if ( $order->get_billing_first_name() ) {
		/* translators: %s: customer first name. */
		printf( esc_html__( 'Hi %s,', 'foxfire-operations' ), esc_html( $order->get_billing_first_name() ) );
	} else {
		esc_html_e( 'Hi,', 'foxfire-operations' );
	}
	?>
</p>
<p><?php esc_html_e( 'Your order has been shipped. The shipment details and ordered items are below.', 'foxfire-operations' ); ?></p>
<?php
do_action( 'woocommerce_email_order_details', $order, $sent_to_admin, $plain_text, $email );
do_action( 'woocommerce_email_order_meta', $order, $sent_to_admin, $plain_text, $email );
do_action( 'woocommerce_email_customer_details', $order, $sent_to_admin, $plain_text, $email );

if ( $additional_content ) {
	echo wp_kses_post( wpautop( wptexturize( $additional_content ) ) );
}

do_action( 'woocommerce_email_footer', $email );

