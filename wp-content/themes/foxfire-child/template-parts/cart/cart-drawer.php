<?php
/**
 * Slide-Over Cart Drawer Template
 *
 * @package Foxfire_Child
 */

defined( 'ABSPATH' ) || exit;

$cart_count   = is_object( WC()->cart ) ? WC()->cart->get_cart_contents_count() : 0;
$cart_total   = is_object( WC()->cart ) ? WC()->cart->get_cart_subtotal() : '$0.00';
$raw_subtotal = is_object( WC()->cart ) ? WC()->cart->get_displayed_subtotal() : 0;
$free_ship_threshold = 200;
$amount_left  = max( 0, $free_ship_threshold - $raw_subtotal );
$progress_pct = min( 100, round( ( $raw_subtotal / $free_ship_threshold ) * 100 ) );
$checkout_url = wc_get_checkout_url();
$cart_url     = wc_get_cart_url();
$shop_url     = function_exists( 'foxfire_get_shop_url' ) ? foxfire_get_shop_url() : wc_get_page_permalink( 'shop' );
?>

<div id="ff-cart-drawer-overlay" class="ff-cart-drawer-overlay" aria-hidden="true"></div>

<aside id="ff-cart-drawer" class="ff-cart-drawer" aria-labelledby="ff-drawer-title" aria-modal="true" role="dialog" aria-hidden="true">
	<div class="ff-cart-drawer__inner">

		<!-- Drawer Header -->
		<header class="ff-cart-drawer__header">
			<div class="ff-cart-drawer__title-wrap">
				<h2 id="ff-drawer-title" class="ff-cart-drawer__title">
					<?php esc_html_e( 'Cart', 'foxfire-child' ); ?>
					<span class="ff-cart-drawer__count-badge">(<?php echo esc_html( $cart_count ); ?>)</span>
				</h2>
			</div>
			<button type="button" class="ff-cart-drawer__close" aria-label="<?php esc_attr_e( 'Close cart', 'foxfire-child' ); ?>" data-ff-drawer-close>
				<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
					<line x1="18" y1="6" x2="6" y2="18"></line>
					<line x1="6" y1="6" x2="18" y2="18"></line>
				</svg>
			</button>
		</header>

		<!-- Free Shipping Meter -->
		<div class="ff-cart-drawer__shipping-meter">
			<div class="ff-shipping-meter">
				<div class="ff-shipping-meter__text">
					<?php if ( $amount_left > 0 ) : ?>
						<span><?php printf( esc_html__( 'Add %s more to unlock Free Tracked Shipping', 'foxfire-child' ), '<strong>' . wp_kses_post( wc_price( $amount_left ) ) . '</strong>' ); ?></span>
					<?php else : ?>
						<span class="ff-shipping-meter__unlocked">
							<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="20 6 9 17 4 12"></polyline></svg>
							<?php esc_html_e( 'You have unlocked Free Tracked Shipping!', 'foxfire-child' ); ?>
						</span>
					<?php endif; ?>
				</div>
				<div class="ff-shipping-meter__bar">
					<div class="ff-shipping-meter__progress" style="width: <?php echo esc_attr( $progress_pct ); ?>%;"></div>
				</div>
			</div>
		</div>

		<!-- Drawer Body / Live Items -->
		<div class="ff-cart-drawer__body">
			<?php if ( is_object( WC()->cart ) && ! WC()->cart->is_empty() ) : ?>
				<ul class="ff-cart-drawer__items">
					<?php
					foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) :
						$_product   = apply_filters( 'woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_key );
						$product_id = apply_filters( 'woocommerce_cart_item_product_id', $cart_item['product_id'], $cart_item, $cart_item_key );

						if ( ! $_product || ! $_product->exists() || $cart_item['quantity'] <= 0 || ! apply_filters( 'woocommerce_cart_item_visible', true, $cart_item, $cart_item_key ) ) {
							continue;
						}

						$product_permalink = apply_filters( 'woocommerce_cart_item_permalink', $_product->is_visible() ? $_product->get_permalink( $cart_item ) : '', $cart_item, $cart_item_key );
						$thumbnail         = apply_filters( 'woocommerce_cart_item_thumbnail', $_product->get_image( 'woocommerce_thumbnail' ), $cart_item, $cart_item_key );
						$batch_lot         = get_field( 'foxfire_batch_lot', $product_id );
						$item_price        = WC()->cart->get_product_price( $_product );
						$item_subtotal     = WC()->cart->get_product_subtotal( $_product, $cart_item['quantity'] );
						?>
						<li class="ff-drawer-item" data-cart-key="<?php echo esc_attr( $cart_item_key ); ?>">
							<!-- Thumbnail -->
							<div class="ff-drawer-item__media">
								<?php if ( $product_permalink ) : ?>
									<a href="<?php echo esc_url( $product_permalink ); ?>"><?php echo $thumbnail; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></a>
								<?php else : ?>
									<?php echo $thumbnail; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
								<?php endif; ?>
							</div>

							<!-- Item Details -->
							<div class="ff-drawer-item__content">
								<div class="ff-drawer-item__header">
									<h3 class="ff-drawer-item__title">
										<?php if ( $product_permalink ) : ?>
											<a href="<?php echo esc_url( $product_permalink ); ?>"><?php echo wp_kses_post( $_product->get_name() ); ?></a>
										<?php else : ?>
											<?php echo wp_kses_post( $_product->get_name() ); ?>
										<?php endif; ?>
									</h3>

									<!-- Remove Button -->
									<a
										href="<?php echo esc_url( wc_get_cart_remove_url( $cart_item_key ) ); ?>"
										class="ff-drawer-item__remove"
										aria-label="<?php esc_attr_e( 'Remove item', 'foxfire-child' ); ?>"
										data-cart-key="<?php echo esc_attr( $cart_item_key ); ?>"
									>
										<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
											<polyline points="3 6 5 6 21 6"></polyline>
											<path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
										</svg>
									</a>
								</div>

								<!-- Meta / Batch -->
								<div class="ff-drawer-item__meta">
									<?php echo wc_get_formatted_cart_item_data( $cart_item ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
									<?php if ( ! empty( $batch_lot ) ) : ?>
										<span class="ff-drawer-item__batch"><?php esc_html_e( 'Lot:', 'foxfire-child' ); ?> <?php echo esc_html( $batch_lot ); ?></span>
									<?php endif; ?>
								</div>

								<!-- Stepper & Subtotal Row -->
								<div class="ff-drawer-item__footer">
									<div class="ff-drawer-stepper">
										<button type="button" class="ff-drawer-stepper__btn ff-drawer-stepper__btn--minus" data-action="minus" aria-label="<?php esc_attr_e( 'Decrease quantity', 'foxfire-child' ); ?>">&minus;</button>
										<span class="ff-drawer-stepper__val"><?php echo esc_html( $cart_item['quantity'] ); ?></span>
										<button type="button" class="ff-drawer-stepper__btn ff-drawer-stepper__btn--plus" data-action="plus" aria-label="<?php esc_attr_e( 'Increase quantity', 'foxfire-child' ); ?>">&plus;</button>
									</div>

									<div class="ff-drawer-item__price-wrap">
										<span class="ff-drawer-item__subtotal"><?php echo $item_subtotal; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
									</div>
								</div>
							</div>
						</li>
					<?php endforeach; ?>
				</ul>
			<?php else : ?>
				<div class="ff-drawer-empty">
					<div class="ff-drawer-empty__icon" aria-hidden="true">
						<svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
							<path d="m2 7 4.41-4.41A2 2 0 0 1 7.83 2h8.34a2 2 0 0 1 1.42.59L22 7"/>
							<path d="M4 12v8a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-8"/>
							<circle cx="12" cy="12" r="2"/>
						</svg>
					</div>
					<h3 class="ff-drawer-empty__title"><?php esc_html_e( 'Your cart is currently empty', 'foxfire-child' ); ?></h3>
					<p class="ff-drawer-empty__text"><?php esc_html_e( 'Explore our catalog of third-party verified laboratory peptides.', 'foxfire-child' ); ?></p>
					<a href="<?php echo esc_url( $shop_url ); ?>" class="ff-btn ff-btn--primary ff-drawer-empty__btn" data-ff-drawer-close>
						<?php esc_html_e( 'Explore Compounds', 'foxfire-child' ); ?>
					</a>
				</div>
			<?php endif; ?>
		</div>

		<!-- Drawer Footer (Subtotal & Actions) -->
		<?php if ( is_object( WC()->cart ) && ! WC()->cart->is_empty() ) : ?>
			<footer class="ff-cart-drawer__footer">
				<div class="ff-cart-drawer__subtotal-row">
					<span class="ff-cart-drawer__subtotal-label"><?php esc_html_e( 'Subtotal', 'foxfire-child' ); ?></span>
					<span class="ff-cart-drawer__subtotal-value"><?php echo $cart_total; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
				</div>
				<p class="ff-cart-drawer__tax-note"><?php esc_html_e( 'Taxes & shipping calculated at checkout.', 'foxfire-child' ); ?></p>

				<div class="ff-cart-drawer__actions">
					<a href="<?php echo esc_url( $checkout_url ); ?>" class="ff-btn ff-btn--primary ff-cart-drawer__checkout-btn">
						<?php esc_html_e( 'Proceed to Checkout', 'foxfire-child' ); ?>
						<span aria-hidden="true">&rarr;</span>
					</a>
					<a href="<?php echo esc_url( $cart_url ); ?>" class="ff-cart-drawer__view-cart-link">
						<?php esc_html_e( 'View Cart Details', 'foxfire-child' ); ?>
					</a>
				</div>

				<div class="ff-cart-drawer__trust-badges">
					<span>
						<svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10"/></svg>
						<?php esc_html_e( 'Batch COA Verified', 'foxfire-child' ); ?>
					</span>
					<span>
						<svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect width="20" height="14" x="2" y="5" rx="2"/><line x1="2" y1="10" x2="22" y2="10"/></svg>
						<?php esc_html_e( 'Discreet Packaging', 'foxfire-child' ); ?>
					</span>
				</div>
			</footer>
		<?php endif; ?>

	</div>
</aside>
