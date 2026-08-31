<?php
/**
 * Shop / Product Listing Page (PLP) customizations — Chunk 1F.
 *
 * @package Foxfire_Child
 */

defined( 'ABSPATH' ) || exit;

/**
 * Render category pills navigation bar for the shop / archive pages.
 */
function foxfire_render_category_pills(): void {
	if ( ! taxonomy_exists( 'product_cat' ) ) {
		return;
	}

	$categories = get_terms(
		array(
			'taxonomy'   => 'product_cat',
			'hide_empty' => true,
			'parent'     => 0,
			'orderby'    => 'name',
			'order'      => 'ASC',
		)
	);

	if ( is_wp_error( $categories ) || empty( $categories ) ) {
		return;
	}

	$shop_url = foxfire_get_shop_url();
	$current_cat_id = is_product_category() ? get_queried_object_id() : 0;
	$is_all_active  = is_shop() && ! is_product_category() && ! is_search();
	?>
	<nav class="ff-category-pills" aria-label="<?php esc_attr_e( 'Product categories', 'foxfire-child' ); ?>">
		<div class="ff-category-pills__scroll">
			<a
				href="<?php echo esc_url( $shop_url ); ?>"
				class="ff-category-pills__item <?php echo $is_all_active ? 'ff-category-pills__item--active' : ''; ?>"
			>
				<?php esc_html_e( 'All Compounds', 'foxfire-child' ); ?>
			</a>

			<?php foreach ( $categories as $category ) : ?>
				<?php
				if ( ! $category instanceof WP_Term ) {
					continue;
				}
				$is_active = ( $current_cat_id === $category->term_id );
				$clean_name = foxfire_get_category_display_name( $category->name );
				?>
				<a
					href="<?php echo esc_url( get_term_link( $category ) ); ?>"
					class="ff-category-pills__item <?php echo $is_active ? 'ff-category-pills__item--active' : ''; ?>"
				>
					<?php echo esc_html( $clean_name ); ?>
					<span class="ff-category-pills__count"><?php echo esc_html( (string) $category->count ); ?></span>
				</a>
			<?php endforeach; ?>
		</div>
	</nav>
	<?php
}

/**
 * Render trust badge and sale badge on product card.
 */
function foxfire_render_product_card_badges(): void {
	global $product;
	if ( ! $product instanceof WC_Product ) {
		return;
	}

	echo '<div class="ff-product-card__badges">';

	if ( $product->is_on_sale() ) {
		echo '<span class="ff-badge ff-badge--sale">' . esc_html__( 'Sale', 'foxfire-child' ) . '</span>';
	}

	if ( $product->is_in_stock() ) {
		$coa_url = function_exists( 'foxfire_get_product_coa_url' ) ? foxfire_get_product_coa_url( $product->get_id() ) : '';

		if ( ! empty( $coa_url ) ) {
			echo '<span class="ff-badge ff-badge--tested">' . esc_html__( 'COA Available', 'foxfire-child' ) . '</span>';
		} else {
			echo '<span class="ff-badge ff-badge--stock">' . esc_html__( 'In Stock', 'foxfire-child' ) . '</span>';
		}
	} else {
		echo '<span class="ff-badge ff-badge--out-of-stock">' . esc_html__( 'Out of Stock', 'foxfire-child' ) . '</span>';
	}

	echo '</div>';
}

/**
 * Output catalog hero / header with trust note.
 */
function foxfire_render_shop_header(): void {
	if ( ! is_shop() && ! is_product_category() ) {
		return;
	}

	$title = is_product_category() ? single_term_title( '', false ) : __( 'Research Compounds & Peptides', 'foxfire-child' );
	$title = foxfire_get_category_display_name( $title );

	/**
	 * Catalog copy is client-supplied (PRD §9). Placeholder text is used until
	 * approved wording is delivered — no testing or purity claims are invented here.
	 */
	$subtitle = is_product_category()
		? term_description()
		: __( 'Lab-grade research compounds verified by independent third-party laboratories. Batch-specific Certificates of Analysis available for every sequence.', 'foxfire-child' );
	?>
	<header class="ff-shop-header">
		<div class="ff-shop-header__content">
			<div class="ff-shop-header__eyebrow">
				<span class="ff-shop-header__dot" aria-hidden="true"></span>
				<?php esc_html_e( 'For research use only. Not for human consumption.', 'foxfire-child' ); ?>
			</div>
			<h1 class="ff-shop-header__title"><?php echo esc_html( $title ); ?></h1>
			<?php if ( ! empty( $subtitle ) ) : ?>
				<div class="ff-shop-header__description">
					<?php echo wp_kses_post( wpautop( $subtitle ) ); ?>
				</div>
			<?php endif; ?>
		</div>
	</header>
	<?php
	foxfire_render_category_pills();
}

/**
 * Remove Storefront breadcrumbs on shop and product archive pages.
 */
function foxfire_shop_remove_breadcrumbs(): void {
	remove_action( 'storefront_before_content', 'woocommerce_breadcrumb', 10 );
	remove_action( 'woocommerce_before_main_content', 'woocommerce_breadcrumb', 20 );
}
add_action( 'wp', 'foxfire_shop_remove_breadcrumbs', 10 );

/**
 * Remove Storefront's duplicate sorting/result-count block below the grid.
 *
 * The archive template renders a single toolbar above the products; Storefront
 * repeats the same controls after the loop.
 */
function foxfire_remove_duplicate_shop_sorting(): void {
	remove_action( 'woocommerce_after_shop_loop', 'storefront_sorting_wrapper', 9 );
	remove_action( 'woocommerce_after_shop_loop', 'woocommerce_catalog_ordering', 10 );
	remove_action( 'woocommerce_after_shop_loop', 'woocommerce_result_count', 20 );
	remove_action( 'woocommerce_after_shop_loop', 'storefront_sorting_wrapper_close', 31 );
}
add_action( 'wp', 'foxfire_remove_duplicate_shop_sorting', 20 );

/**
 * Customize number of products per page.
 */
function foxfire_products_per_page(): int {
	return 12;
}
add_filter( 'loop_shop_per_page', 'foxfire_products_per_page', 20 );

/**
 * Customize number of product columns in loop.
 */
function foxfire_loop_columns(): int {
	return 3;
}
add_filter( 'loop_shop_columns', 'foxfire_loop_columns', 20 );

/**
 * Ensure all product card action buttons display "Add to Cart" across simple and variable products.
 *
 * @param string $text Button text.
 * @param WC_Product $product Product object.
 * @return string
 */
function foxfire_custom_add_to_cart_text( string $text, WC_Product $product ): string {
	return __( 'Add to Cart', 'foxfire-child' );
}
add_filter( 'woocommerce_product_add_to_cart_text', 'foxfire_custom_add_to_cart_text', 20, 2 );
add_filter( 'woocommerce_product_single_add_to_cart_text', 'foxfire_custom_add_to_cart_text', 20, 2 );
