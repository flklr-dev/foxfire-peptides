<?php
/**
 * View Order template — Chunk 1L.
 *
 * Single unified container layout matching checkout & account design.
 *
 * @package Foxfire_Child
 */

defined( 'ABSPATH' ) || exit;

$order_status = $order->get_status();

// Shipping Address Preparation
$ship_name = trim( $order->get_shipping_first_name() . ' ' . $order->get_shipping_last_name() );
if ( empty( $ship_name ) ) {
	$ship_name = trim( $order->get_billing_first_name() . ' ' . $order->get_billing_last_name() );
}
$ship_street = trim( $order->get_shipping_address_1() . ( $order->get_shipping_address_2() ? ', ' . $order->get_shipping_address_2() : '' ) );
$ship_country = $order->get_shipping_country();
$ship_country_name = ( $ship_country && WC()->countries && isset( WC()->countries->countries[ $ship_country ] ) ) ? WC()->countries->countries[ $ship_country ] : $ship_country;
$ship_state = $order->get_shipping_state();
$ship_states = ( $ship_country && WC()->countries ) ? WC()->countries->get_states( $ship_country ) : array();
$ship_state_name = ( $ship_state && ! empty( $ship_states[ $ship_state ] ) ) ? $ship_states[ $ship_state ] : $ship_state;
$ship_postcode = $order->get_shipping_postcode();
$ship_city = $order->get_shipping_city();

$ship_parts = array_filter( array(
	$ship_street,
	$ship_city,
	trim( $ship_state_name . ' ' . $ship_postcode ),
	$ship_country_name,
) );
$formatted_ship_address = implode( ', ', $ship_parts );

// Billing & Contact Preparation
$bill_name  = trim( $order->get_billing_first_name() . ' ' . $order->get_billing_last_name() );
$bill_phone = $order->get_billing_phone();
$bill_email = $order->get_billing_email();
?>

<div class="ff-account-view-order">
	<?php do_action( 'woocommerce_order_details_before_order_table', $order ); ?>

	<!-- One Whole Unified Container -->
	<div class="ff-view-order-unified-card">

		<!-- 1. Header Row -->
		<div class="ff-view-order-card-header">
			<div class="ff-view-order-card-header__left">
				<div class="ff-view-order-card-header__icon" aria-hidden="true">
					<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
						<path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4Z"/>
						<path d="M3 6h18"/>
						<path d="M16 10a4 4 0 0 1-8 0"/>
					</svg>
				</div>
				<div class="ff-view-order-card-header__info">
					<div class="ff-view-order-title-line">
						<h1 class="ff-section-title">
							<?php
							printf(
								/* translators: %s: order number */
								esc_html__( 'Order #%s', 'foxfire-child' ),
								esc_html( $order->get_order_number() )
							);
							?>
						</h1>
						<span class="ff-order-badge ff-order-badge--<?php echo esc_attr( $order_status ); ?>">
							<?php echo esc_html( wc_get_order_status_name( $order_status ) ); ?>
						</span>
					</div>
					<p class="ff-section-subtext">
						<?php
						printf(
							/* translators: 1: formatted date */
							esc_html__( 'Placed on %1$s', 'foxfire-child' ),
							esc_html( wc_format_datetime( $order->get_date_created() ) )
						);
						?>
					</p>
				</div>
			</div>

			<a href="<?php echo esc_url( wc_get_account_endpoint_url( 'orders' ) ); ?>" class="ff-view-order-back-btn">
				<span><?php esc_html_e( 'Back', 'foxfire-child' ); ?></span>
			</a>
		</div>

		<!-- 2. Address & Billing Rows Section (Checkout/Shopee style) -->
		<div class="ff-view-order-address-section">

			<!-- Delivery Address Row -->
			<div class="ff-view-order-address-row">
				<div class="ff-view-order-address-label">
					<svg class="ff-shopee-loc-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#e85a0c" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
						<path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path>
						<circle cx="12" cy="10" r="3"></circle>
					</svg>
					<span><?php esc_html_e( 'Delivery Address:', 'foxfire-child' ); ?></span>
				</div>
				<div class="ff-view-order-address-content">
					<strong class="ff-address-name"><?php echo esc_html( $ship_name ? $ship_name : __( 'No name specified', 'foxfire-child' ) ); ?></strong>
					<?php if ( $bill_phone ) : ?>
						<span class="ff-address-phone"><?php echo esc_html( $bill_phone ); ?></span>
					<?php endif; ?>
					<span class="ff-address-text"><?php echo esc_html( $formatted_ship_address ? $formatted_ship_address : __( 'No delivery address recorded', 'foxfire-child' ) ); ?></span>
				</div>
			</div>

			<!-- Billing & Contact Row -->
			<div class="ff-view-order-address-row ff-view-order-address-row--billing">
				<div class="ff-view-order-address-label">
					<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
						<rect width="20" height="14" x="2" y="5" rx="2"/><line x1="2" x2="22" y1="10" y2="10"/>
					</svg>
					<span><?php esc_html_e( 'Billing & Contact:', 'foxfire-child' ); ?></span>
				</div>
				<div class="ff-view-order-address-content">
					<strong class="ff-address-name"><?php echo esc_html( $bill_name ? $bill_name : $ship_name ); ?></strong>
					<?php if ( $bill_email ) : ?>
						<span class="ff-address-email"><?php echo esc_html( $bill_email ); ?></span>
					<?php endif; ?>
					<?php if ( $bill_phone ) : ?>
						<span class="ff-address-phone"><?php echo esc_html( $bill_phone ); ?></span>
					<?php endif; ?>
				</div>
			</div>

		</div>

		<!-- 3. Ordered Items (Compact) -->
		<div class="ff-view-order-items-section">
			<div class="ff-view-order-items-header">
				<h2 class="ff-view-order-section-title"><?php esc_html_e( 'Ordered Items', 'foxfire-child' ); ?></h2>
				<span class="ff-view-order-items-badge"><?php printf( esc_html__( '%d item(s)', 'foxfire-child' ), $order->get_item_count() ); ?></span>
			</div>

			<div class="ff-view-order-items">
				<?php
				foreach ( $order->get_items() as $item_id => $item ) :
					$product   = $item->get_product();
					$thumbnail = $product ? $product->get_image( 'woocommerce_thumbnail' ) : '';
					?>
					<div class="ff-view-order-item-row">
						<div class="ff-view-order-item__thumb">
							<?php echo $thumbnail; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
						</div>
						<div class="ff-view-order-item__info">
							<strong class="ff-view-order-item__name"><?php echo esc_html( $item->get_name() ); ?></strong>
							<span class="ff-view-order-item__meta">
								<?php esc_html_e( 'Qty:', 'foxfire-child' ); ?> <?php echo esc_html( (string) $item->get_quantity() ); ?>
							</span>
						</div>
						<div class="ff-view-order-item__price">
							<?php echo wp_kses_post( $order->get_formatted_line_subtotal( $item ) ); ?>
						</div>
					</div>
				<?php endforeach; ?>
			</div>
		</div>

		<!-- 4. Order Totals Breakdown -->
		<div class="ff-view-order-totals-section">
			<div class="ff-view-order-totals">
				<?php
				foreach ( $order->get_order_item_totals() as $key => $total ) :
					$is_total    = 'order_total' === $key;
					$is_shipping = 'shipping' === $key;
					?>
					<div class="ff-view-order-total-row <?php echo $is_total ? 'ff-view-order-total-row--final' : ''; ?>">
						<span class="ff-total-label"><?php echo esc_html( $total['label'] ); ?></span>
						<span class="ff-total-val <?php echo $is_total ? 'ff-total-val--orange' : ''; ?>">
							<?php
							if ( $is_shipping ) {
								$ship_total = (float) $order->get_shipping_total();
								if ( 0.0 === $ship_total || 0 === (int) $ship_total ) {
									echo '<span class="ff-view-order-shipping-pricing"><del class="ff-shipping-was-price">' . wp_kses_post( wc_price( 9.90 ) ) . '</del> <ins class="ff-shipping-now-price">' . wp_kses_post( wc_price( 0.00 ) ) . '</ins></span>';
								} else {
									echo wp_kses_post( $total['value'] );
								}
							} else {
								echo wp_kses_post( $total['value'] );
							}
							?>
						</span>
					</div>
				<?php endforeach; ?>
			</div>
		</div>

	</div>

	<?php do_action( 'woocommerce_order_details_after_order_table', $order ); ?>

</div>
