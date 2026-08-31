<?php
/**
 * View Order template — Chunk 1L.
 *
 * @package Foxfire_Child
 */

defined( 'ABSPATH' ) || exit;

$order_status = $order->get_status();
?>

<div class="ff-account-view-order">

	<div class="ff-view-order-header">
		<a href="<?php echo esc_url( wc_get_account_endpoint_url( 'orders' ) ); ?>" class="ff-back-link">
			&larr; <?php esc_html_e( 'Back to All Orders', 'foxfire-child' ); ?>
		</a>
		
		<div class="ff-view-order-title-row">
			<div>
				<h1 class="ff-section-title">
					<?php
					printf(
						/* translators: %s: order number */
						esc_html__( 'Order #%s', 'foxfire-child' ),
						esc_html( $order->get_order_number() )
					);
					?>
				</h1>
				<span class="ff-view-order-date">
					<?php
					printf(
						/* translators: 1: formatted date, 2: formatted time */
						esc_html__( 'Placed on %1$s', 'foxfire-child' ),
						esc_html( wc_format_datetime( $order->get_date_created() ) )
					);
					?>
				</span>
			</div>
			<div class="ff-view-order-status">
				<span class="ff-order-badge ff-order-badge--<?php echo esc_attr( $order_status ); ?>">
					<?php echo esc_html( wc_get_order_status_name( $order_status ) ); ?>
				</span>
			</div>
		</div>
	</div>

	<!-- Order Items Card -->
	<section class="ff-view-order-card">
		<h2 class="ff-view-order-card__title"><?php esc_html_e( 'Ordered Items', 'foxfire-child' ); ?></h2>

		<div class="ff-view-order-items">
			<?php
			foreach ( $order->get_items() as $item_id => $item ) :
				$product   = $item->get_product();
				$thumbnail = $product ? $product->get_image( 'woocommerce_thumbnail' ) : '';
				$batch_lot = $product ? get_field( 'foxfire_batch_lot', $product->get_id() ) : '';
				?>
				<div class="ff-view-order-item-row">
					<div class="ff-view-order-item__thumb">
						<?php echo $thumbnail; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					</div>
					<div class="ff-view-order-item__info">
						<strong class="ff-view-order-item__name"><?php echo esc_html( $item->get_name() ); ?></strong>
						<div class="ff-view-order-item__meta">
							<span><?php esc_html_e( 'Quantity:', 'foxfire-child' ); ?> <?php echo esc_html( (string) $item->get_quantity() ); ?></span>
							<?php if ( ! empty( $batch_lot ) ) : ?>
								&bull; <span class="ff-order-batch-tag"><?php esc_html_e( 'Batch Lot:', 'foxfire-child' ); ?> <?php echo esc_html( $batch_lot ); ?></span>
							<?php endif; ?>
						</div>
					</div>
					<div class="ff-view-order-item__price">
						<?php echo wp_kses_post( $order->get_formatted_line_subtotal( $item ) ); ?>
					</div>
				</div>
			<?php endforeach; ?>
		</div>

		<!-- Order Totals -->
		<div class="ff-view-order-totals">
			<?php
			foreach ( $order->get_order_item_totals() as $key => $total ) :
				$is_total = 'order_total' === $key;
				?>
				<div class="ff-view-order-total-row <?php echo $is_total ? 'ff-view-order-total-row--final' : ''; ?>">
					<span class="ff-total-label"><?php echo esc_html( $total['label'] ); ?></span>
					<span class="ff-total-val <?php echo $is_total ? 'ff-total-val--orange' : ''; ?>"><?php echo wp_kses_post( $total['value'] ); ?></span>
				</div>
			<?php endforeach; ?>
		</div>
	</section>

	<!-- Addresses & Customer Information -->
	<div class="ff-view-order-addresses-grid">
		<div class="ff-view-order-card">
			<h2 class="ff-view-order-card__title"><?php esc_html_e( 'Shipping Address', 'foxfire-child' ); ?></h2>
			<address class="ff-address-text">
				<?php echo wp_kses_post( $order->get_formatted_shipping_address() ? $order->get_formatted_shipping_address() : $order->get_formatted_billing_address() ); ?>
			</address>
		</div>

		<div class="ff-view-order-card">
			<h2 class="ff-view-order-card__title"><?php esc_html_e( 'Billing & Contact', 'foxfire-child' ); ?></h2>
			<address class="ff-address-text">
				<?php echo wp_kses_post( $order->get_formatted_billing_address() ); ?><br/>
				<strong><?php esc_html_e( 'Email:', 'foxfire-child' ); ?></strong> <?php echo esc_html( $order->get_billing_email() ); ?><br/>
				<?php if ( $order->get_billing_phone() ) : ?>
					<strong><?php esc_html_e( 'Phone:', 'foxfire-child' ); ?></strong> <?php echo esc_html( $order->get_billing_phone() ); ?>
				<?php endif; ?>
			</address>
		</div>
	</div>

	<!-- Bottom Action Bar -->
	<div class="ff-view-order-actions-bar">
		<a href="<?php echo esc_url( home_url( '/testing-coa/' ) ); ?>" class="ff-btn ff-btn--outline">
			<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2v4a2 2 0 0 0 2 2h4"/><path d="M4 22h16a2 2 0 0 0 2-2V7.5L14.5 2H6a2 2 0 0 0-2 2v4"/><path d="m3 15 2 2 4-4"/></svg>
			<?php esc_html_e( 'Lookup Batch COAs', 'foxfire-child' ); ?>
		</a>

		<a href="<?php echo esc_url( function_exists( 'foxfire_get_shop_url' ) ? foxfire_get_shop_url() : wc_get_page_permalink( 'shop' ) ); ?>" class="ff-btn ff-btn--primary">
			<?php esc_html_e( 'Explore Compounds', 'foxfire-child' ); ?> &rarr;
		</a>
	</div>

</div>
