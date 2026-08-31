<?php
/**
 * Single-Column DTC Thankyou / Order Confirmation Template — Chunk 1K Redesign.
 *
 * @package Foxfire_Child
 */

defined( 'ABSPATH' ) || exit;
?>

<div class="ff-thankyou-page">

	<?php if ( $order ) : ?>

		<?php do_action( 'woocommerce_before_thankyou', $order->get_id() ); ?>

		<?php if ( $order->has_status( 'failed' ) ) : ?>

			<!-- Order Failed Notice -->
			<div class="ff-thankyou-failed-card">
				<div class="ff-thankyou-failed-icon">
					<svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
						<circle cx="12" cy="12" r="10"></circle>
						<line x1="15" y1="9" x2="9" y2="15"></line>
						<line x1="9" y1="9" x2="15" y2="15"></line>
					</svg>
				</div>
				<h1 class="ff-thankyou-title"><?php esc_html_e( 'Order Payment Failed', 'foxfire-child' ); ?></h1>
				<p class="ff-thankyou-subtext"><?php esc_html_e( 'Your transaction could not be completed. Please attempt your purchase again.', 'foxfire-child' ); ?></p>
				<div class="ff-thankyou-actions-bar">
					<a href="<?php echo esc_url( $order->get_checkout_payment_url() ); ?>" class="ff-btn ff-btn--primary"><?php esc_html_e( 'Pay Now', 'foxfire-child' ); ?></a>
					<a href="<?php echo esc_url( wc_get_page_permalink( 'shop' ) ); ?>" class="ff-btn ff-btn--outline"><?php esc_html_e( 'Return to Shop', 'foxfire-child' ); ?></a>
				</div>
			</div>

		<?php else : ?>

			<!-- Single-Column Seamless Order Confirmation Layout -->
			<div class="ff-thankyou-single-col">

				<!-- 1. Center-Aligned Status Header -->
				<header class="ff-thankyou-header">
					<div class="ff-thankyou-status-icon" aria-hidden="true">
						<svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
							<polyline points="20 6 9 17 4 12"></polyline>
						</svg>
					</div>
					<span class="ff-thankyou-status-badge"><?php esc_html_e( 'Order Confirmed', 'foxfire-child' ); ?></span>
					<h1 class="ff-thankyou-title">
						<?php
						$first_name = $order->get_billing_first_name();
						if ( ! empty( $first_name ) ) {
							printf(
								/* translators: %s: customer first name */
								esc_html__( 'Thank you, %s!', 'foxfire-child' ),
								esc_html( $first_name )
							);
						} else {
							esc_html_e( 'Thank you for your order!', 'foxfire-child' );
						}
						?>
					</h1>
					<p class="ff-thankyou-subtext">
						<?php
						printf(
							/* translators: 1: order number, 2: customer email */
							esc_html__( 'Your order %1$s has been received. A confirmation receipt has been sent to %2$s.', 'foxfire-child' ),
							'<strong>#' . esc_html( $order->get_order_number() ) . '</strong>',
							'<strong>' . esc_html( $order->get_billing_email() ) . '</strong>'
						);
						?>
					</p>
				</header>

				<!-- 2. Quick Order Metadata Bar -->
				<div class="ff-thankyou-meta-bar">
					<div class="ff-thankyou-meta-col">
						<span class="ff-meta-label"><?php esc_html_e( 'Order Number', 'foxfire-child' ); ?></span>
						<span class="ff-meta-val">#<?php echo esc_html( $order->get_order_number() ); ?></span>
					</div>
					<div class="ff-thankyou-meta-col">
						<span class="ff-meta-label"><?php esc_html_e( 'Date', 'foxfire-child' ); ?></span>
						<span class="ff-meta-val"><?php echo esc_html( wc_format_datetime( $order->get_date_created() ) ); ?></span>
					</div>
					<div class="ff-thankyou-meta-col">
						<span class="ff-meta-label"><?php esc_html_e( 'Total Amount', 'foxfire-child' ); ?></span>
						<span class="ff-meta-val ff-meta-val--orange"><?php echo wp_kses_post( $order->get_formatted_order_total() ); ?></span>
					</div>
					<div class="ff-thankyou-meta-col">
						<span class="ff-meta-label"><?php esc_html_e( 'Payment Method', 'foxfire-child' ); ?></span>
						<span class="ff-meta-val"><?php echo wp_kses_post( $order->get_payment_method_title() ); ?></span>
					</div>
				</div>

				<!-- 3. Itemized Order Summary -->
				<section class="ff-thankyou-section">
					<h2 class="ff-thankyou-section-heading"><?php esc_html_e( 'Order Summary', 'foxfire-child' ); ?></h2>

					<div class="ff-thankyou-items-list">
						<?php
						foreach ( $order->get_items() as $item_id => $item ) {
							$product          = $item->get_product();
							$thumbnail        = $product ? $product->get_image( 'woocommerce_thumbnail' ) : '';
							$batch_lot        = $product ? get_field( 'foxfire_batch_lot', $product->get_id() ) : '';
							$qty              = $item->get_quantity();
							$subtotal_display = $order->get_formatted_line_subtotal( $item );
							?>
							<div class="ff-thankyou-item">
								<div class="ff-thankyou-item__media">
									<?php echo $thumbnail; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
									<span class="ff-thankyou-item__qty"><?php echo esc_html( (string) $qty ); ?></span>
								</div>

								<div class="ff-thankyou-item__info">
									<h3 class="ff-thankyou-item__title"><?php echo esc_html( $item->get_name() ); ?></h3>
									<?php if ( ! empty( $batch_lot ) ) : ?>
										<span class="ff-thankyou-item__batch"><?php esc_html_e( 'Lot:', 'foxfire-child' ); ?> <?php echo esc_html( $batch_lot ); ?></span>
									<?php endif; ?>
								</div>

								<div class="ff-thankyou-item__price">
									<?php echo $subtotal_display; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
								</div>
							</div>
							<?php
						}
						?>
					</div>

					<!-- Pricing Breakdown -->
					<div class="ff-thankyou-totals">
						<?php
						foreach ( $order->get_order_item_totals() as $key => $total ) {
							$is_total = 'order_total' === $key;
							?>
							<div class="ff-thankyou-total-row <?php echo $is_total ? 'ff-thankyou-total-row--final' : ''; ?>">
								<span class="ff-thankyou-total-label"><?php echo esc_html( $total['label'] ); ?></span>
								<span class="ff-thankyou-total-val <?php echo $is_total ? 'ff-thankyou-total-val--orange' : ''; ?>"><?php echo wp_kses_post( $total['value'] ); ?></span>
							</div>
							<?php
						}
						?>
					</div>
				</section>

				<!-- 4. Shipping & Customer Contact Details -->
				<section class="ff-thankyou-section">
					<h2 class="ff-thankyou-section-heading"><?php esc_html_e( 'Shipping & Customer Information', 'foxfire-child' ); ?></h2>
					<div class="ff-addresses-grid">
						<div class="ff-address-box">
							<span class="ff-address-box__label"><?php esc_html_e( 'Shipping Address', 'foxfire-child' ); ?></span>
							<address class="ff-address-text">
								<?php echo wp_kses_post( $order->get_formatted_shipping_address() ? $order->get_formatted_shipping_address() : $order->get_formatted_billing_address() ); ?>
							</address>
						</div>

						<div class="ff-address-box">
							<span class="ff-address-box__label"><?php esc_html_e( 'Contact Details', 'foxfire-child' ); ?></span>
							<p class="ff-address-text">
								<strong><?php esc_html_e( 'Email:', 'foxfire-child' ); ?></strong> <?php echo esc_html( $order->get_billing_email() ); ?><br/>
								<?php if ( $order->get_billing_phone() ) : ?>
									<strong><?php esc_html_e( 'Phone:', 'foxfire-child' ); ?></strong> <?php echo esc_html( $order->get_billing_phone() ); ?>
								<?php endif; ?>
							</p>
						</div>
					</div>
				</section>

				<!-- Payment Instructions (if any) -->
				<?php do_action( 'woocommerce_thankyou_' . $order->get_payment_method(), $order->get_id() ); ?>

				<!-- 5. Navigation Action Buttons -->
				<div class="ff-thankyou-actions-bar">
					<a href="<?php echo esc_url( home_url( '/testing-coa/' ) ); ?>" class="ff-btn ff-btn--outline">
						<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M14 2v4a2 2 0 0 0 2 2h4"/><path d="M4 22h16a2 2 0 0 0 2-2V7.5L14.5 2H6a2 2 0 0 0-2 2v4"/><path d="m3 15 2 2 4-4"/></svg>
						<?php esc_html_e( 'View Batch COAs', 'foxfire-child' ); ?>
					</a>

					<a href="<?php echo esc_url( function_exists( 'foxfire_get_shop_url' ) ? foxfire_get_shop_url() : wc_get_page_permalink( 'shop' ) ); ?>" class="ff-btn ff-btn--primary">
						<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m15 18-6-6 6-6"/></svg>
						<?php esc_html_e( 'Continue Shopping', 'foxfire-child' ); ?>
					</a>
				</div>

			</div>

		<?php endif; ?>

	<?php else : ?>

		<div class="ff-thankyou-single-col">
			<h1 class="ff-thankyou-title"><?php esc_html_e( 'Thank you! Your order has been received.', 'foxfire-child' ); ?></h1>
			<p class="ff-thankyou-subtext"><?php esc_html_e( 'Thank you for choosing Foxfire Peptides.', 'foxfire-child' ); ?></p>
			<div class="ff-thankyou-actions-bar">
				<a href="<?php echo esc_url( wc_get_page_permalink( 'shop' ) ); ?>" class="ff-btn ff-btn--primary"><?php esc_html_e( 'Continue Shopping', 'foxfire-child' ); ?></a>
			</div>
		</div>

	<?php endif; ?>

</div>
