<?php
/**
 * Product Detail Page (PDP) customizations — Chunk 1G.
 *
 * Prominent Certificate of Analysis (COA) block, batch/lot display,
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
 * Render prominent Certificate of Analysis (COA) block on the PDP (PRD §5.11, DESIGN.md §9 & §12).
 */
function foxfire_render_pdp_coa_block(): void {
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
	<section class="ff-pdp-coa-box" aria-labelledby="ff-coa-heading">
		<div class="ff-pdp-coa-box__header">
			<div class="ff-pdp-coa-box__title-group">
				<span class="ff-pdp-coa-box__icon" aria-hidden="true">
					<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
						<path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path>
						<path d="m9 12 2 2 4-4"></path>
					</svg>
				</span>
				<h3 id="ff-coa-heading" class="ff-pdp-coa-box__title">
					<?php esc_html_e( 'Testing & Certificate of Analysis', 'foxfire-child' ); ?>
				</h3>
			</div>
		</div>

		<p class="ff-pdp-coa-box__text">
			<?php esc_html_e( 'A Certificate of Analysis (COA) is a laboratory document describing the testing performed on a product batch. [PLACEHOLDER] Client-approved testing description pending — no testing method, laboratory, or purity claim is stated here until confirmed.', 'foxfire-child' ); ?>
		</p>

		<div class="ff-pdp-coa-box__footer">
			<div class="ff-pdp-coa-box__batch-meta">
				<?php if ( ! empty( $batch_lot ) ) : ?>
					<span class="ff-pdp-coa-box__batch-tag">
						<?php printf( esc_html__( 'Lot: %s', 'foxfire-child' ), esc_html( $batch_lot ) ); ?>
					</span>
				<?php endif; ?>
			</div>

			<a
				href="<?php echo esc_url( $final_url ); ?>"
				target="_blank"
				rel="noopener noreferrer"
				class="ff-pdp-coa-box__cta"
			>
				<span><?php echo esc_html( $coa_label ); ?></span>
				<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
					<path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"></path>
					<polyline points="15 3 21 3 21 9"></polyline>
					<line x1="10" y1="14" x2="21" y2="3"></line>
				</svg>
			</a>
		</div>
	</section>
	<?php
}
add_action( 'woocommerce_single_product_summary', 'foxfire_render_pdp_coa_block', 35 );

/**
 * Output storage / handling card.
 *
 * Structure only — all specification values are client-supplied (PRD §9).
 */
function foxfire_pdp_specs_box(): void {
	?>
	<div class="ff-pdp-specs-card">
		<h4 class="ff-pdp-specs-card__title"><?php esc_html_e( 'Storage & Handling', 'foxfire-child' ); ?></h4>
		<ul class="ff-pdp-specs-card__list">
			<li>
				<strong><?php esc_html_e( 'Form:', 'foxfire-child' ); ?></strong>
				<span><?php esc_html_e( '[PLACEHOLDER] Client to supply', 'foxfire-child' ); ?></span>
			</li>
			<li>
				<strong><?php esc_html_e( 'Storage:', 'foxfire-child' ); ?></strong>
				<span><?php esc_html_e( '[PLACEHOLDER] Client to supply', 'foxfire-child' ); ?></span>
			</li>
			<li>
				<strong><?php esc_html_e( 'Shelf life:', 'foxfire-child' ); ?></strong>
				<span><?php esc_html_e( '[PLACEHOLDER] Client to supply', 'foxfire-child' ); ?></span>
			</li>
			<li>
				<strong><?php esc_html_e( 'Usage notice:', 'foxfire-child' ); ?></strong>
				<span><?php esc_html_e( 'For research use only. Not for human consumption.', 'foxfire-child' ); ?></span>
			</li>
		</ul>
	</div>
	<?php
}
add_action( 'woocommerce_single_product_summary', 'foxfire_pdp_specs_box', 40 );

/**
 * Render mobile sticky Add to Cart bar (DESIGN.md §15).
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
