<?php
/**
 * Orders history list — Chunk 1L.
 *
 * @package Foxfire_Child
 */

defined( 'ABSPATH' ) || exit;

do_action( 'woocommerce_before_account_orders', $has_orders );
?>

<div class="ff-account-orders-section">

	<div class="ff-section-title-wrap">
		<h1 class="ff-section-title"><?php esc_html_e( 'Order History', 'foxfire-child' ); ?></h1>
		<p class="ff-section-subtext"><?php esc_html_e( 'Review past analytical orders, verify tracking details, and lookup batch COAs.', 'foxfire-child' ); ?></p>
	</div>

	<?php if ( $has_orders ) : ?>

		<div class="ff-orders-list">
			<?php
			foreach ( $customer_orders->orders as $customer_order ) :
				$order      = wc_get_order( $customer_order ); // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
				$item_count = $order->get_item_count();
				$status     = $order->get_status();
				$order_url  = $order->get_view_order_url();
				?>
				<article class="ff-order-card">
					
					<!-- Order Card Top Bar -->
					<div class="ff-order-card__header">
						<div class="ff-order-card__meta">
							<span class="ff-order-num">#<?php echo esc_html( $order->get_order_number() ); ?></span>
							<span class="ff-order-date"><?php echo esc_html( wc_format_datetime( $order->get_date_created() ) ); ?></span>
						</div>
						<div class="ff-order-card__status">
							<span class="ff-order-badge ff-order-badge--<?php echo esc_attr( $status ); ?>">
								<?php echo esc_html( wc_get_order_status_name( $status ) ); ?>
							</span>
						</div>
					</div>

					<!-- Order Items Preview -->
					<div class="ff-order-card__items">
						<?php
						$items = $order->get_items();
						foreach ( $items as $item_id => $item ) :
							$product   = $item->get_product();
							$thumbnail = $product ? $product->get_image( 'woocommerce_thumbnail' ) : '';
							$batch_lot = $product ? get_field( 'foxfire_batch_lot', $product->get_id() ) : '';
							?>
							<div class="ff-order-item-row">
								<div class="ff-order-item-thumb">
									<?php echo $thumbnail; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
								</div>
								<div class="ff-order-item-info">
									<strong class="ff-order-item-name"><?php echo esc_html( $item->get_name() ); ?></strong>
									<span class="ff-order-item-meta">
										<?php esc_html_e( 'Qty:', 'foxfire-child' ); ?> <?php echo esc_html( (string) $item->get_quantity() ); ?>
										<?php if ( ! empty( $batch_lot ) ) : ?>
											&bull; <span class="ff-order-batch-tag"><?php esc_html_e( 'Lot:', 'foxfire-child' ); ?> <?php echo esc_html( $batch_lot ); ?></span>
										<?php endif; ?>
									</span>
								</div>
								<div class="ff-order-item-price">
									<?php echo wp_kses_post( $order->get_formatted_line_subtotal( $item ) ); ?>
								</div>
							</div>
						<?php endforeach; ?>
					</div>

					<!-- Order Card Bottom Bar -->
					<div class="ff-order-card__footer">
						<div class="ff-order-card__total">
							<span class="ff-total-label"><?php esc_html_e( 'Total Amount:', 'foxfire-child' ); ?></span>
							<strong class="ff-total-val"><?php echo wp_kses_post( $order->get_formatted_order_total() ); ?></strong>
						</div>
						<div class="ff-order-card__actions">
							<a href="<?php echo esc_url( home_url( '/testing-coa/' ) ); ?>" class="ff-btn ff-btn--outline ff-btn--sm">
								<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2v4a2 2 0 0 0 2 2h4"/><path d="M4 22h16a2 2 0 0 0 2-2V7.5L14.5 2H6a2 2 0 0 0-2 2v4"/><path d="m3 15 2 2 4-4"/></svg>
								<?php esc_html_e( 'COA Lookup', 'foxfire-child' ); ?>
							</a>

							<a href="<?php echo esc_url( $order_url ); ?>" class="ff-btn ff-btn--primary ff-btn--sm">
								<?php esc_html_e( 'View Details', 'foxfire-child' ); ?> &rarr;
							</a>
						</div>
					</div>

				</article>
			<?php endforeach; ?>
		</div>

		<?php do_action( 'woocommerce_before_account_orders_pagination' ); ?>

		<?php if ( 1 < $customer_orders->max_num_pages ) : ?>
			<nav class="ff-orders-pagination" aria-label="<?php esc_attr_e( 'Orders Pagination', 'foxfire-child' ); ?>">
				<?php if ( 1 !== $current_page ) : ?>
					<a class="ff-btn ff-btn--outline ff-btn--sm" href="<?php echo esc_url( wc_get_endpoint_url( 'orders', $current_page - 1 ) ); ?>">&larr; <?php esc_html_e( 'Previous', 'foxfire-child' ); ?></a>
				<?php endif; ?>

				<span class="ff-pagination-current">
					<?php
					printf(
						/* translators: 1: current page, 2: max pages */
						esc_html__( 'Page %1$s of %2$s', 'foxfire-child' ),
						esc_html( (string) $current_page ),
						esc_html( (string) $customer_orders->max_num_pages )
					);
					?>
				</span>

				<?php if ( intval( $customer_orders->max_num_pages ) !== $current_page ) : ?>
					<a class="ff-btn ff-btn--outline ff-btn--sm" href="<?php echo esc_url( wc_get_endpoint_url( 'orders', $current_page + 1 ) ); ?>"><?php esc_html_e( 'Next', 'foxfire-child' ); ?> &rarr;</a>
				<?php endif; ?>
			</nav>
		<?php endif; ?>

	<?php else : ?>

		<!-- Empty Orders State -->
		<div class="ff-orders-empty-card">
			<div class="ff-orders-empty-icon" aria-hidden="true">
				<svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m7.5 4.27 9 5.15"/><path d="M21 8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16Z"/><path d="m3.3 7 8.7 5 8.7-5"/><path d="M12 22V12"/></svg>
			</div>
			<h2 class="ff-orders-empty-title"><?php esc_html_e( 'No orders placed yet', 'foxfire-child' ); ?></h2>
			<p class="ff-orders-empty-desc"><?php esc_html_e( 'When you place research compound orders, they will appear here with batch tracking and COA references.', 'foxfire-child' ); ?></p>
			<a href="<?php echo esc_url( function_exists( 'foxfire_get_shop_url' ) ? foxfire_get_shop_url() : wc_get_page_permalink( 'shop' ) ); ?>" class="ff-btn ff-btn--primary">
				<?php esc_html_e( 'Explore Compounds', 'foxfire-child' ); ?> &rarr;
			</a>
		</div>

	<?php endif; ?>

</div>

<?php do_action( 'woocommerce_after_account_orders', $has_orders ); ?>
