<?php
/**
 * Cart Page Template — Chunk 1J.
 *
 * @package Foxfire_Child
 */

defined( 'ABSPATH' ) || exit;

$raw_subtotal        = is_object( WC()->cart ) ? WC()->cart->get_displayed_subtotal() : 0;
$free_ship_threshold = 200;
$amount_left         = max( 0, $free_ship_threshold - $raw_subtotal );
$progress_pct        = min( 100, round( ( $raw_subtotal / $free_ship_threshold ) * 100 ) );
$shop_url            = function_exists( 'foxfire_get_shop_url' ) ? foxfire_get_shop_url() : wc_get_page_permalink( 'shop' );

do_action( 'woocommerce_before_cart' ); ?>

<div class="ff-cart-page">
	<!-- Cart Top Header with Sleek Inline Shipping Progress Bar -->
	<header class="ff-cart-header">
		<h1 class="ff-cart-header__title"><?php esc_html_e( 'Review Order', 'foxfire-child' ); ?></h1>

		<!-- Sleek Inline Free Shipping Progress Bar (Directly below title, above the 2-column grid) -->
		<div class="ff-cart-shipping-bar">
			<div class="ff-cart-shipping-bar__message">
				<?php if ( $amount_left > 0 ) : ?>
					<span class="ff-shipping-icon" aria-hidden="true">
						<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="1" y="3" width="15" height="13"></rect><polygon points="16 8 20 8 23 11 23 16 16 16 8"></polygon><circle cx="5.5" cy="18.5" r="2.5"></circle><circle cx="18.5" cy="18.5" r="2.5"></circle></svg>
					</span>
					<span><?php printf( esc_html__( 'Add %s more to qualify for Free Shipping', 'foxfire-child' ), '<strong>' . wp_kses_post( wc_price( $amount_left ) ) . '</strong>' ); ?></span>
				<?php else : ?>
					<span class="ff-shipping-icon ff-shipping-icon--unlocked" aria-hidden="true">
						<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>
					</span>
					<span class="ff-shipping-unlocked-text"><?php esc_html_e( 'You have qualified for Free Shipping!', 'foxfire-child' ); ?></span>
				<?php endif; ?>
			</div>
			<div class="ff-cart-shipping-bar__track">
				<div class="ff-cart-shipping-bar__fill" style="width: <?php echo esc_attr( $progress_pct ); ?>%;"></div>
			</div>
		</div>
	</header>

	<!-- 2-Column Cart Grid Layout (Both Columns Top-Aligned) -->
	<div class="ff-cart-grid">

		<!-- Left Column: Line Items -->
		<div class="ff-cart-main">
			<form class="woocommerce-cart-form" action="<?php echo esc_url( wc_get_cart_url() ); ?>" method="post">
				<?php do_action( 'woocommerce_before_cart_table' ); ?>

				<div class="ff-cart-items-list">
					<!-- Fox Orange Header Row (Desktop) -->
					<div class="ff-cart-items-header">
						<span class="ff-th-thumb"><?php esc_html_e( 'Item', 'foxfire-child' ); ?></span>
						<span class="ff-th-info"><?php esc_html_e( 'Compound', 'foxfire-child' ); ?></span>
						<span class="ff-th-price"><?php esc_html_e( 'Price', 'foxfire-child' ); ?></span>
						<span class="ff-th-qty"><?php esc_html_e( 'Quantity', 'foxfire-child' ); ?></span>
						<span class="ff-th-subtotal"><?php esc_html_e( 'Total', 'foxfire-child' ); ?></span>
						<span class="ff-th-remove"><span class="screen-reader-text"><?php esc_html_e( 'Remove', 'foxfire-child' ); ?></span></span>
					</div>

					<?php do_action( 'woocommerce_before_cart_contents' ); ?>

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
						$item_price        = apply_filters( 'woocommerce_cart_item_price', WC()->cart->get_product_price( $_product ), $cart_item, $cart_item_key );
						$item_subtotal     = apply_filters( 'woocommerce_cart_item_subtotal', WC()->cart->get_product_subtotal( $_product, $cart_item['quantity'] ), $cart_item, $cart_item_key );
						?>
						<div class="ff-cart-card <?php echo esc_attr( apply_filters( 'woocommerce_cart_item_class', 'cart_item', $cart_item, $cart_item_key ) ); ?>">
							
							<!-- 1. Thumbnail -->
							<div class="ff-cart-card__thumb">
								<?php if ( $product_permalink ) : ?>
									<a href="<?php echo esc_url( $product_permalink ); ?>" class="ff-cart-card__thumb-link"><?php echo $thumbnail; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></a>
								<?php else : ?>
									<span class="ff-cart-card__thumb-link"><?php echo $thumbnail; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
								<?php endif; ?>
							</div>

							<!-- 2. Compound Details (Title, Variation, Lot) -->
							<div class="ff-cart-card__info">
								<h2 class="ff-cart-card__title">
									<?php if ( $product_permalink ) : ?>
										<a href="<?php echo esc_url( $product_permalink ); ?>"><?php echo wp_kses_post( $_product->get_name() ); ?></a>
									<?php else : ?>
										<?php echo wp_kses_post( $_product->get_name() ); ?>
									<?php endif; ?>
								</h2>

								<div class="ff-cart-card__meta">
									<?php echo wc_get_formatted_cart_item_data( $cart_item ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
								</div>

								<!-- Mobile Price Line -->
								<div class="ff-cart-card__price-mobile">
									<span class="ff-cart-mobile-label"><?php esc_html_e( 'Price:', 'foxfire-child' ); ?></span>
									<span class="ff-cart-price-val"><?php echo $item_price; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
								</div>
							</div>

							<!-- 3. Desktop Price -->
							<div class="ff-cart-card__price">
								<span class="ff-cart-price-val"><?php echo $item_price; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
							</div>

							<!-- 4. Quantity Stepper -->
							<div class="ff-cart-card__qty">
								<div class="quantity ff-qty-wrapper">
									<button type="button" class="ff-qty-btn ff-qty-btn--minus" data-action="minus" aria-label="<?php esc_attr_e( 'Decrease quantity', 'foxfire-child' ); ?>">&minus;</button>
									<input
										type="number"
										id="quantity_<?php echo esc_attr( $cart_item_key ); ?>"
										class="input-text qty text ff-qty-input"
										step="1"
										min="0"
										max="<?php echo esc_attr( $_product->get_max_purchase_quantity() ); ?>"
										name="cart[<?php echo esc_attr( $cart_item_key ); ?>][qty]"
										value="<?php echo esc_attr( $cart_item['quantity'] ); ?>"
										title="<?php esc_attr_e( 'Qty', 'foxfire-child' ); ?>"
										size="4"
										placeholder=""
										inputmode="numeric"
										autocomplete="off"
									/>
									<button type="button" class="ff-qty-btn ff-qty-btn--plus" data-action="plus" aria-label="<?php esc_attr_e( 'Increase quantity', 'foxfire-child' ); ?>">&plus;</button>
								</div>
							</div>

							<!-- 5. Subtotal -->
							<div class="ff-cart-card__subtotal">
								<span class="ff-cart-mobile-label"><?php esc_html_e( 'Total:', 'foxfire-child' ); ?></span>
								<span class="ff-cart-subtotal-val"><?php echo $item_subtotal; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
							</div>

							<!-- 6. Remove Trash Button -->
							<div class="ff-cart-card__remove">
								<a
									href="<?php echo esc_url( wc_get_cart_remove_url( $cart_item_key ) ); ?>"
									class="ff-cart-remove-link"
									aria-label="<?php esc_attr_e( 'Remove item', 'foxfire-child' ); ?>"
									title="<?php esc_attr_e( 'Remove from cart', 'foxfire-child' ); ?>"
								>
									<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
										<polyline points="3 6 5 6 21 6"></polyline>
										<path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
										<line x1="10" y1="11" x2="10" y2="17"></line>
										<line x1="14" y1="11" x2="14" y2="17"></line>
									</svg>
								</a>
							</div>
						</div>
					<?php endforeach; ?>

					<?php do_action( 'woocommerce_cart_contents' ); ?>
				</div>

				<!-- Actions Bar Below Cart Items (Clean row, no bulky white card) -->
				<div class="ff-cart-actions-bar">
					<div class="ff-cart-actions-bar__left">
						<a href="<?php echo esc_url( $shop_url ); ?>" class="ff-continue-shopping-link">
							<span class="ff-arrow" aria-hidden="true">&larr;</span>
							<span><?php esc_html_e( 'Continue Shopping', 'foxfire-child' ); ?></span>
						</a>
					</div>

					<div class="ff-cart-actions-bar__right">
						<?php if ( wc_coupons_enabled() ) : ?>
							<div class="coupon ff-coupon-box">
								<label for="coupon_code" class="screen-reader-text"><?php esc_html_e( 'Coupon code:', 'foxfire-child' ); ?></label>
								<input type="text" name="coupon_code" class="input-text ff-coupon-input" id="coupon_code" value="" placeholder="<?php esc_attr_e( 'Promo code', 'foxfire-child' ); ?>" />
								<button type="submit" class="button ff-coupon-btn" name="apply_coupon" value="<?php esc_attr_e( 'Apply coupon', 'foxfire-child' ); ?>"><?php esc_html_e( 'Apply', 'foxfire-child' ); ?></button>
								<?php do_action( 'woocommerce_cart_coupon' ); ?>
							</div>
						<?php endif; ?>

						<button type="submit" class="button ff-update-cart-btn" name="update_cart" value="<?php esc_attr_e( 'Update cart', 'foxfire-child' ); ?>"><?php esc_html_e( 'Update Cart', 'foxfire-child' ); ?></button>

						<?php do_action( 'woocommerce_cart_actions' ); ?>
						<?php wp_nonce_field( 'woocommerce-cart', 'woocommerce-cart-nonce' ); ?>
					</div>
				</div>

				<?php do_action( 'woocommerce_after_cart_contents' ); ?>
				<?php do_action( 'woocommerce_after_cart_table' ); ?>
			</form>
		</div>

		<!-- Right Column: Clean Sticky Order Summary Card (ONLY) -->
		<div class="ff-cart-sidebar">
			<?php woocommerce_cart_totals(); ?>

			<!-- Continue Shopping Link on Mobile (Below Order Summary) -->
			<div class="ff-cart-continue-mobile">
				<a href="<?php echo esc_url( $shop_url ); ?>" class="ff-continue-shopping-link">
					<span class="ff-arrow" aria-hidden="true">&larr;</span>
					<span><?php esc_html_e( 'Continue Shopping', 'foxfire-child' ); ?></span>
				</a>
			</div>
		</div>

	</div>

	<?php do_action( 'woocommerce_after_cart' ); ?>
</div>
