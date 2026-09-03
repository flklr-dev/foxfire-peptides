<?php
/**
 * Product Detail Page (PDP) customizations — Chunk 1G.
 *
 * 1 / 3 / 5 Vial quantity tier selectors, single-vial stock deduction,
 * simplified Certificate of Analysis (COA) block, batch/lot display,
 * storage/handling specs, and mobile sticky Add to Cart bar.
 *
 * @package Foxfire_Child
 */

defined( 'ABSPATH' ) || exit;

/**
 * Enqueue PDP-specific scripts.
 */
function foxfire_pdp_enqueue_scripts(): void {
	if ( ! is_product() ) {
		return;
	}

	$pdp_js = FOXFIRE_CHILD_DIR . '/assets/js/pdp.js';
	if ( file_exists( $pdp_js ) ) {
		wp_enqueue_script(
			'foxfire-pdp',
			FOXFIRE_CHILD_URI . '/assets/js/pdp.js',
			array( 'jquery' ),
			(string) filemtime( $pdp_js ),
			true
		);
	}
}
add_action( 'wp_enqueue_scripts', 'foxfire_pdp_enqueue_scripts', 35 );

/**
 * Output "Back to Shop" button above the single product layout.
 */
function foxfire_pdp_back_button(): void {
	$shop_url = foxfire_get_shop_url();
	?>
	<nav class="ff-pdp-back" aria-label="<?php esc_attr_e( 'Breadcrumb back link', 'foxfire-child' ); ?>">
		<a href="<?php echo esc_url( $shop_url ); ?>" class="ff-pdp-back__link">
			<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
				<line x1="19" y1="12" x2="5" y2="12"></line>
				<polyline points="12 19 5 12 12 5"></polyline>
			</svg>
			<span><?php esc_html_e( 'Back to Shop', 'foxfire-child' ); ?></span>
		</a>
	</nav>
	<?php
}
add_action( 'woocommerce_before_single_product', 'foxfire_pdp_back_button', 10 );

/**
 * Output trust badge and compound category above single product title.
 */
function foxfire_pdp_eyebrow(): void {
	global $product;
	if ( ! $product instanceof WC_Product ) {
		return;
	}

	$terms = get_the_terms( $product->get_id(), 'product_cat' );
	$cat_name = '';
	if ( ! empty( $terms ) && ! is_wp_error( $terms ) ) {
		$first_cat = reset( $terms );
		$cat_name  = foxfire_get_category_display_name( $first_cat->name );
	}

	$coa_url = function_exists( 'foxfire_get_product_coa_url' ) ? foxfire_get_product_coa_url( $product->get_id() ) : '';
	?>
	<div class="ff-pdp-eyebrow">
		<?php if ( ! empty( $cat_name ) ) : ?>
			<span class="ff-pdp-eyebrow__category"><?php echo esc_html( $cat_name ); ?></span>
		<?php endif; ?>
		<?php if ( ! empty( $coa_url ) ) : ?>
			<span class="ff-badge ff-badge--tested">
				<span class="ff-badge__icon" aria-hidden="true">✓</span>
				<?php esc_html_e( 'COA Available', 'foxfire-child' ); ?>
			</span>
		<?php endif; ?>
	</div>
	<?php
}
add_action( 'woocommerce_single_product_summary', 'foxfire_pdp_eyebrow', 4 );

/**
 * Output batch and SKU metadata block below price.
 */
function foxfire_pdp_meta_highlights(): void {
	global $product;
	if ( ! $product instanceof WC_Product ) {
		return;
	}

	$product_id = $product->get_id();
	$batch_lot  = function_exists( 'foxfire_get_product_batch_lot' ) ? foxfire_get_product_batch_lot( $product_id ) : '';
	$sku        = $product->get_sku();
	$stock_text = $product->is_in_stock() ? __( 'In Stock', 'foxfire-child' ) : __( 'Out of Stock', 'foxfire-child' );
	$stock_cls  = $product->is_in_stock() ? 'ff-stock--in-stock' : 'ff-stock--out-of-stock';
	?>
	<div class="ff-pdp-highlights">
		<div class="ff-pdp-highlights__row">
			<span class="ff-pdp-highlights__stock <?php echo esc_attr( $stock_cls ); ?>">
				<span class="ff-pdp-highlights__dot" aria-hidden="true"></span>
				<?php echo esc_html( $stock_text ); ?>
			</span>

			<?php if ( ! empty( $batch_lot ) ) : ?>
				<span class="ff-pdp-highlights__item">
					<strong><?php esc_html_e( 'Batch:', 'foxfire-child' ); ?></strong>
					<code><?php echo esc_html( $batch_lot ); ?></code>
				</span>
			<?php endif; ?>

			<?php if ( ! empty( $sku ) ) : ?>
				<span class="ff-pdp-highlights__item">
					<strong><?php esc_html_e( 'SKU:', 'foxfire-child' ); ?></strong>
					<code><?php echo esc_html( $sku ); ?></code>
				</span>
			<?php endif; ?>
		</div>
	</div>
	<?php
}
add_action( 'woocommerce_single_product_summary', 'foxfire_pdp_meta_highlights', 15 );

/**
 * Output 1 / 3 / 5 Vial quantity selector pills above the Add to Cart button.
 */
function foxfire_render_quantity_tiers(): void {
	global $product;
	if ( ! $product instanceof WC_Product ) {
		return;
	}

	$product_id = $product->get_id();

	if ( function_exists( 'foxfire_is_tier_pricing_enabled' ) && ! foxfire_is_tier_pricing_enabled( $product_id ) ) {
		return;
	}

	$discounts  = function_exists( 'foxfire_get_product_tier_discounts' ) ? foxfire_get_product_tier_discounts( $product_id ) : array( 3 => 0, 5 => 0 );
	$base_price = (float) $product->get_price();
	?>
	<div class="ff-tier-selector" data-base-price="<?php echo esc_attr( (string) $base_price ); ?>">
		<span class="ff-tier-selector__label"><?php esc_html_e( 'Select Quantity:', 'foxfire-child' ); ?></span>
		<div class="ff-tier-selector__grid">
			<!-- 1 Vial -->
			<button type="button" class="ff-tier-btn is-active" data-qty="1">
				<span class="ff-tier-btn__header">
					<span class="ff-tier-btn__qty"><?php esc_html_e( '1 Vial', 'foxfire-child' ); ?></span>
				</span>
				<span class="ff-tier-btn__pricing" data-tier-price="1">
					<?php echo wc_price( $base_price ); ?>
				</span>
			</button>

			<!-- 3 Vials -->
			<button type="button" class="ff-tier-btn" data-qty="3" data-discount="<?php echo esc_attr( (string) $discounts[3] ); ?>">
				<span class="ff-tier-btn__header">
					<span class="ff-tier-btn__qty"><?php esc_html_e( '3 Vials', 'foxfire-child' ); ?></span>
					<?php if ( ! empty( $discounts[3] ) && $discounts[3] > 0 ) : ?>
						<span class="ff-tier-btn__badge"><?php printf( esc_html__( 'Save %s%%', 'foxfire-child' ), esc_html( (string) $discounts[3] ) ); ?></span>
					<?php endif; ?>
				</span>
				<span class="ff-tier-btn__pricing" data-tier-price="3">
					<?php
					$p3 = $base_price * 3 * ( 1 - ( (float) $discounts[3] / 100 ) );
					echo wc_price( $p3 );
					?>
				</span>
			</button>

			<!-- 5 Vials -->
			<button type="button" class="ff-tier-btn" data-qty="5" data-discount="<?php echo esc_attr( (string) $discounts[5] ); ?>">
				<span class="ff-tier-btn__header">
					<span class="ff-tier-btn__qty"><?php esc_html_e( '5 Vials', 'foxfire-child' ); ?></span>
					<?php if ( ! empty( $discounts[5] ) && $discounts[5] > 0 ) : ?>
						<span class="ff-tier-btn__badge"><?php printf( esc_html__( 'Save %s%%', 'foxfire-child' ), esc_html( (string) $discounts[5] ) ); ?></span>
					<?php endif; ?>
				</span>
				<span class="ff-tier-btn__pricing" data-tier-price="5">
					<?php
					$p5 = $base_price * 5 * ( 1 - ( (float) $discounts[5] / 100 ) );
					echo wc_price( $p5 );
					?>
				</span>
			</button>
		</div>
	</div>
	<?php
}
add_action( 'woocommerce_before_add_to_cart_quantity', 'foxfire_render_quantity_tiers', 10 );

/**
 * Render 2-column specifications & batch verification grid below the main product area.
 * Left Column: Batch Verification & Testing (aligned with product image above).
 * Right Column: Storage & Handling Specifications (aligned with product details above).
 */
function foxfire_render_pdp_info_sections(): void {
	global $product;
	if ( ! $product instanceof WC_Product ) {
		return;
	}

	$product_id = $product->get_id();
	$coa_url    = function_exists( 'foxfire_get_product_coa_url' ) ? foxfire_get_product_coa_url( $product_id ) : '';
	$coa_label  = function_exists( 'foxfire_get_product_coa_label' ) ? foxfire_get_product_coa_label( $product_id ) : __( 'View Certificate of Analysis', 'foxfire-child' );
	$batch_lot  = function_exists( 'foxfire_get_product_batch_lot' ) ? foxfire_get_product_batch_lot( $product_id ) : '';
	$testing_pg = foxfire_get_page_url( 'testing-coa', '/testing-coa/' );
	$final_url  = ! empty( $coa_url ) ? $coa_url : $testing_pg;
	?>
	<div class="ff-pdp-info-grid">
		<!-- Left Column: Batch Verification & Testing -->
		<div class="ff-pdp-info-grid__col ff-pdp-info-grid__col--left">
			<section class="ff-pdp-coa-box" aria-labelledby="ff-coa-heading">
				<div class="ff-pdp-coa-box__header">
					<div class="ff-pdp-coa-box__title-group">
						<span class="ff-pdp-coa-box__icon" aria-hidden="true">
							<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
								<path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path>
								<polyline points="9 12 11 14 15 10"></polyline>
							</svg>
						</span>
						<h3 id="ff-coa-heading" class="ff-pdp-coa-box__title">
							<?php esc_html_e( 'Batch Verification & Testing', 'foxfire-child' ); ?>
						</h3>
					</div>

					<?php if ( ! empty( $batch_lot ) ) : ?>
						<span class="ff-pdp-coa-box__batch-tag">
							<code><?php printf( esc_html__( 'Lot #%s', 'foxfire-child' ), esc_html( $batch_lot ) ); ?></code>
						</span>
					<?php endif; ?>
				</div>

				<p class="ff-pdp-coa-box__text">
					<?php esc_html_e( 'Every research batch is independently tested to ensure compound integrity and quality. View the laboratory Certificate of Analysis (COA) for full documentation.', 'foxfire-child' ); ?>
				</p>

				<div class="ff-pdp-coa-box__footer">
					<a
						href="<?php echo esc_url( $final_url ); ?>"
						target="_blank"
						rel="noopener noreferrer"
						class="ff-pdp-coa-box__cta"
					>
						<span><?php echo esc_html( $coa_label ); ?></span>
						<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
							<path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"></path>
							<polyline points="15 3 21 3 21 9"></polyline>
							<line x1="10" y1="14" x2="21" y2="3"></line>
						</svg>
					</a>
				</div>
			</section>
		</div>

		<!-- Right Column: Storage & Handling Specifications -->
		<div class="ff-pdp-info-grid__col ff-pdp-info-grid__col--right">
			<section class="ff-pdp-specs-card" aria-label="<?php esc_attr_e( 'Storage & Handling Specifications', 'foxfire-child' ); ?>">
				<div class="ff-pdp-specs-card__header">
					<span class="ff-pdp-specs-card__icon" aria-hidden="true">
						<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
							<path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path>
							<polyline points="3.27 6.96 12 12.01 20.73 6.96"></polyline>
							<line x1="12" y1="22.08" x2="12" y2="12"></line>
						</svg>
					</span>
					<h4 class="ff-pdp-specs-card__title"><?php esc_html_e( 'Storage & Handling Specifications', 'foxfire-child' ); ?></h4>
				</div>
				<ul class="ff-pdp-specs-card__list">
					<li>
						<span class="ff-specs-label"><?php esc_html_e( 'Form:', 'foxfire-child' ); ?></span>
						<span class="ff-specs-value"><?php esc_html_e( 'Lyophilized powder in sterile sealed glass vial', 'foxfire-child' ); ?></span>
					</li>
					<li>
						<span class="ff-specs-label"><?php esc_html_e( 'Storage:', 'foxfire-child' ); ?></span>
						<span class="ff-specs-value"><?php esc_html_e( 'Store at -20°C for long-term stability; protect from light.', 'foxfire-child' ); ?></span>
					</li>
					<li>
						<span class="ff-specs-label"><?php esc_html_e( 'Notice:', 'foxfire-child' ); ?></span>
						<span class="ff-specs-value"><?php esc_html_e( 'For laboratory research use only. Not for human or animal consumption.', 'foxfire-child' ); ?></span>
					</li>
				</ul>
			</section>
		</div>
	</div>
	<?php
}
add_action( 'woocommerce_after_single_product_summary', 'foxfire_render_pdp_info_sections', 15 );

/**
 * Render mobile sticky Add to Cart bar.
 */
function foxfire_render_mobile_sticky_atc(): void {
	if ( ! is_product() ) {
		return;
	}

	global $product;
	if ( ! $product instanceof WC_Product ) {
		return;
	}

	$product_id = $product->get_id();
	$price_html = $product->get_price_html();
	?>
	<aside id="ff-sticky-atc" class="ff-sticky-atc" aria-label="<?php esc_attr_e( 'Quick add to cart', 'foxfire-child' ); ?>">
		<div class="ff-sticky-atc__inner">
			<div class="ff-sticky-atc__info">
				<?php if ( has_post_thumbnail( $product_id ) ) : ?>
					<div class="ff-sticky-atc__thumb">
						<?php echo get_the_post_thumbnail( $product_id, 'thumbnail' ); ?>
					</div>
				<?php endif; ?>
				<div class="ff-sticky-atc__text">
					<p class="ff-sticky-atc__title"><?php the_title(); ?></p>
					<p class="ff-sticky-atc__price"><?php echo wp_kses_post( $price_html ); ?></p>
				</div>
			</div>

			<div class="ff-sticky-atc__actions">
				<button type="button" class="ff-sticky-atc__button" data-ff-sticky-trigger>
					<?php esc_html_e( 'Add to Cart', 'foxfire-child' ); ?>
				</button>
			</div>
		</div>
	</aside>
	<?php
}
add_action( 'wp_footer', 'foxfire_render_mobile_sticky_atc', 25 );

/**
 * Remove Storefront's floating side product pagination, duplicate sticky ATC, product data tabs, redundant default meta, and duplicate upsells.
 */
function foxfire_remove_storefront_pdp_elements(): void {
	remove_action( 'woocommerce_after_single_product_summary', 'storefront_single_product_pagination', 30 );
	remove_action( 'storefront_after_footer', 'storefront_sticky_single_add_to_cart', 999 );
	remove_action( 'woocommerce_after_single_product_summary', 'woocommerce_output_product_data_tabs', 10 );
	remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_meta', 40 );
	remove_action( 'woocommerce_after_single_product_summary', 'storefront_upsell_display', 15 );
	remove_action( 'woocommerce_after_single_product_summary', 'woocommerce_upsell_display', 15 );
}
add_action( 'wp', 'foxfire_remove_storefront_pdp_elements', 20 );

/**
 * Configure single merged Related Products section (4 items in 4 columns).
 */
function foxfire_related_products_args( array $args ): array {
	$args['posts_per_page'] = 4;
	$args['columns']        = 4;
	return $args;
}
add_filter( 'woocommerce_output_related_products_args', 'foxfire_related_products_args' );

/**
 * Customize Related Products section heading.
 */
function foxfire_related_products_heading(): string {
	return __( 'You May Also Like', 'foxfire-child' );
}
add_filter( 'woocommerce_product_related_products_heading', 'foxfire_related_products_heading' );

/**
 * Ensure "You May Also Like" always returns 4 products by backfilling
 * with popular/catalog items if the current category has fewer than 4 items.
 *
 * @param int[] $related_posts Array of related product IDs.
 * @param int   $product_id    Current product ID.
 * @param array $args          Arguments containing 'limit' and 'excluded_ids'.
 * @return int[]
 */
function foxfire_ensure_minimum_related_products( array $related_posts, int $product_id, array $args = array() ): array {
	$limit = isset( $args['limit'] ) ? (int) $args['limit'] : 4;
	if ( $limit <= 0 ) {
		$limit = 4;
	}

	if ( count( $related_posts ) >= $limit ) {
		return array_slice( $related_posts, 0, $limit );
	}

	$needed  = $limit - count( $related_posts );
	$exclude = array_unique( array_merge( array( $product_id ), $related_posts ) );

	$backfill = get_posts(
		array(
			'post_type'      => 'product',
			'post_status'    => 'publish',
			'posts_per_page' => $needed,
			'post__not_in'   => $exclude,
			'fields'         => 'ids',
			'orderby'        => 'rand',
		)
	);

	if ( ! empty( $backfill ) ) {
		$related_posts = array_merge( $related_posts, array_map( 'intval', $backfill ) );
	}

	return $related_posts;
}
add_filter( 'woocommerce_related_products', 'foxfire_ensure_minimum_related_products', 20, 3 );

/**
 * Completely suppress default WooCommerce product data tabs.
 */
add_filter( 'woocommerce_product_tabs', '__return_empty_array', 98 );

/**
 * Explicitly disable WooCommerce gallery hover zoom.
 */
function foxfire_disable_product_gallery_zoom(): void {
	remove_theme_support( 'wc-product-gallery-zoom' );
	remove_theme_support( 'wc-product-gallery-lightbox' );
}
add_action( 'after_setup_theme', 'foxfire_disable_product_gallery_zoom', 100 );

/**
 * Filter short description to remove [PLACEHOLDER] text and provide clean copy.
 */
function foxfire_clean_pdp_short_description( string $desc ): string {
	if ( is_product() ) {
		if ( false !== stripos( $desc, '[PLACEHOLDER]' ) || empty( trim( strip_tags( $desc ) ) ) ) {
			return '<p class="ff-pdp-description-text">' . esc_html__( 'High-purity lyophilized research peptide. Supplied in a sterile, sealed glass vial for in-vitro laboratory research and analytical inquiry.', 'foxfire-child' ) . '</p>';
		}
	}
	return $desc;
}
add_filter( 'woocommerce_short_description', 'foxfire_clean_pdp_short_description', 20 );


