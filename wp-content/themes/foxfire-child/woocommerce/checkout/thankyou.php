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
			<?php
			$order_items = $order->get_items( apply_filters( 'woocommerce_purchase_order_item_types', 'line_item' ) );
			?>

			<!-- Single-Column Seamless Order Confirmation Layout -->
			<div class="ff-thankyou-single-col">

				<!-- 1. Center-Aligned Status Header -->
				<header class="ff-thankyou-header">
					<div class="ff-thankyou-status-icon" aria-hidden="true">
						<svg width="30" height="30" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
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

				<?php
				// Preserve WooCommerce gateway output such as manual-payment instructions.
				ob_start();
				do_action( 'woocommerce_thankyou_' . $order->get_payment_method(), $order->get_id() );
				$gateway_output = trim( (string) ob_get_clean() );
				if ( '' !== $gateway_output ) {
					echo '<div class="ff-thankyou-gateway-output">' . $gateway_output . '</div>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Trusted WooCommerce gateway hook output.
				}
				do_action( 'woocommerce_thankyou', $order->get_id() );
				?>

				<!-- 3. White order-details card using Foxfire design tokens. -->
				<section class="ff-thankyou-section ff-thankyou-order-details" aria-labelledby="ff-thankyou-order-details-title">
					<?php do_action( 'woocommerce_order_details_before_order_table', $order ); ?>

					<div class="ff-thankyou-section-header">
						<h2 id="ff-thankyou-order-details-title" class="ff-thankyou-section-heading"><?php esc_html_e( 'Order details', 'foxfire-child' ); ?></h2>
						<span class="ff-thankyou-item-count">
							<?php
							printf(
								/* translators: %d: number of items in the order. */
								esc_html( _n( '%d item', '%d items', $order->get_item_count(), 'foxfire-child' ) ),
								absint( $order->get_item_count() )
							);
							?>
						</span>
					</div>

					<div class="ff-thankyou-items-list">
						<?php do_action( 'woocommerce_order_details_before_order_table_items', $order ); ?>
						<?php foreach ( $order_items as $item_id => $item ) : ?>
							<?php
							if ( ! apply_filters( 'woocommerce_order_item_visible', true, $item ) ) {
								continue;
							}

							$product           = $item->get_product();
							$is_visible        = $product && $product->is_visible();
							$product_permalink = apply_filters( 'woocommerce_order_item_permalink', $is_visible ? $product->get_permalink( $item ) : '', $item, $order );
							$thumbnail          = $product ? $product->get_image( 'woocommerce_thumbnail' ) : wc_placeholder_img( 'woocommerce_thumbnail' );
							$quantity           = $item->get_quantity();
							$refunded_quantity  = $order->get_qty_refunded_for_item( $item_id );
							$display_quantity   = $refunded_quantity ? max( 0, $quantity + $refunded_quantity ) : $quantity;
							?>
							<div class="ff-thankyou-item">
								<div class="ff-thankyou-item__media">
									<?php if ( $product_permalink ) : ?>
										<a href="<?php echo esc_url( $product_permalink ); ?>" aria-label="<?php echo esc_attr( $item->get_name() ); ?>"><?php echo wp_kses_post( $thumbnail ); ?></a>
									<?php else : ?>
										<?php echo wp_kses_post( $thumbnail ); ?>
									<?php endif; ?>
									<span class="ff-thankyou-item__qty" aria-label="<?php echo esc_attr( sprintf( /* translators: %d: product quantity. */ __( 'Quantity %d', 'foxfire-child' ), $display_quantity ) ); ?>"><?php echo esc_html( (string) $display_quantity ); ?></span>
								</div>
								<div class="ff-thankyou-item__info">
									<?php
									$product_name = apply_filters( 'woocommerce_order_item_name', $item->get_name(), $item, $is_visible );
									if ( $product_permalink ) {
										echo '<a class="ff-thankyou-item__title" href="' . esc_url( $product_permalink ) . '">' . esc_html( wp_strip_all_tags( $product_name ) ) . '</a>';
									} else {
										echo '<span class="ff-thankyou-item__title">' . esc_html( wp_strip_all_tags( $product_name ) ) . '</span>';
									}

									do_action( 'woocommerce_order_item_meta_start', $item_id, $item, $order, false );
									echo wp_kses_post( wc_display_item_meta( $item, array( 'echo' => false ) ) );
									do_action( 'woocommerce_order_item_meta_end', $item_id, $item, $order, false );
									?>
								</div>
								<div class="ff-thankyou-item__price"><?php echo wp_kses_post( $order->get_formatted_line_subtotal( $item ) ); ?></div>
							</div>
						<?php endforeach; ?>
						<?php do_action( 'woocommerce_order_details_after_order_table_items', $order ); ?>
					</div>

					<div class="ff-thankyou-totals">
						<?php foreach ( $order->get_order_item_totals() as $key => $total ) : ?>
							<?php $is_order_total = 'order_total' === $key; ?>
							<div class="ff-thankyou-total-row<?php echo $is_order_total ? ' ff-thankyou-total-row--final' : ''; ?>">
								<span class="ff-thankyou-total-label"><?php echo esc_html( $total['label'] ); ?></span>
								<span class="ff-thankyou-total-val<?php echo $is_order_total ? ' ff-thankyou-total-val--orange' : ''; ?>"><?php echo wp_kses_post( $total['value'] ); ?></span>
							</div>
						<?php endforeach; ?>
					</div>

					<?php if ( $order->get_customer_note() ) : ?>
						<div class="ff-thankyou-customer-note">
							<strong><?php esc_html_e( 'Order note', 'foxfire-child' ); ?></strong>
							<p><?php echo wp_kses( nl2br( wc_wptexturize_order_note( $order->get_customer_note() ) ), array( 'br' => array() ) ); ?></p>
						</div>
					<?php endif; ?>

					<?php do_action( 'woocommerce_order_details_after_order_table', $order ); ?>
				</section>

				<?php do_action( 'woocommerce_after_order_details', $order ); ?>

				<!-- 4. Navigation Action Button (Single Clean Primary CTA) -->
				<a href="<?php echo esc_url( function_exists( 'foxfire_get_shop_url' ) ? foxfire_get_shop_url() : wc_get_page_permalink( 'shop' ) ); ?>" class="ff-btn ff-btn--primary ff-thankyou-continue-btn">
					<?php esc_html_e( 'Continue Shopping', 'foxfire-child' ); ?> &rarr;
				</a>

			</div>

		<?php endif; ?>

	<?php else : ?>

		<div class="ff-thankyou-single-col">
			<h1 class="ff-thankyou-title"><?php esc_html_e( 'Thank you! Your order has been received.', 'foxfire-child' ); ?></h1>
			<p class="ff-thankyou-subtext"><?php esc_html_e( 'Thank you for choosing Foxfire Peptides.', 'foxfire-child' ); ?></p>
			<a href="<?php echo esc_url( wc_get_page_permalink( 'shop' ) ); ?>" class="ff-btn ff-btn--primary ff-thankyou-continue-btn">
				<?php esc_html_e( 'Continue Shopping', 'foxfire-child' ); ?> &rarr;
			</a>
		</div>

	<?php endif; ?>

</div>
