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

	$shop_url       = foxfire_get_shop_url();
	$current_cat_id = is_product_category() ? get_queried_object_id() : 0;
	$is_all_active  = is_shop() && ! is_product_category() && ! is_search();
	$product_counts = wp_count_posts( 'product' );
	$total_count    = isset( $product_counts->publish ) ? (int) $product_counts->publish : 0;
	?>
	<nav class="ff-category-pills" aria-label="<?php esc_attr_e( 'Product categories', 'foxfire-child' ); ?>">
		<div class="ff-category-pills__wrapper">
			<div class="ff-category-pills__scroll" role="tablist">
				<a
					href="<?php echo esc_url( $shop_url ); ?>"
					class="ff-category-pills__item <?php echo $is_all_active ? 'ff-category-pills__item--active' : ''; ?>"
					<?php echo $is_all_active ? 'aria-current="page"' : ''; ?>
					aria-label="<?php echo esc_attr( sprintf( __( 'Filter by All Compounds, %d products available', 'foxfire-child' ), $total_count ) ); ?>"
					role="tab"
					aria-selected="<?php echo $is_all_active ? 'true' : 'false'; ?>"
				>
					<span class="ff-category-pills__label"><?php esc_html_e( 'All Compounds', 'foxfire-child' ); ?></span>
					<span class="ff-category-pills__count"><?php echo esc_html( (string) $total_count ); ?></span>
				</a>

				<?php foreach ( $categories as $category ) : ?>
					<?php
					if ( ! $category instanceof WP_Term ) {
						continue;
					}
					$is_active  = ( $current_cat_id === $category->term_id );
					$clean_name = foxfire_get_category_display_name( $category->name );
					$cat_count  = (int) $category->count;
					?>
					<a
						href="<?php echo esc_url( get_term_link( $category ) ); ?>"
						class="ff-category-pills__item <?php echo $is_active ? 'ff-category-pills__item--active' : ''; ?>"
						<?php echo $is_active ? 'aria-current="page"' : ''; ?>
						aria-label="<?php echo esc_attr( sprintf( __( 'Filter by %s, %d products available', 'foxfire-child' ), $clean_name, $cat_count ) ); ?>"
						role="tab"
						aria-selected="<?php echo $is_active ? 'true' : 'false'; ?>"
					>
						<span class="ff-category-pills__label"><?php echo esc_html( $clean_name ); ?></span>
						<span class="ff-category-pills__count"><?php echo esc_html( (string) $cat_count ); ?></span>
					</a>
				<?php endforeach; ?>
			</div>
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

		if ( ! empty( $coa_url ) && function_exists( 'foxfire_product_has_coa_document' ) && foxfire_product_has_coa_document( $product->get_id() ) ) {
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

	$title = is_product_category() ? single_term_title( '', false ) : __( 'Premium Research Peptides', 'foxfire-child' );
	$title = foxfire_get_category_display_name( $title );

	$subtitle = is_product_category()
		? term_description()
		: __( 'Browse research compounds with available batch, stock, and COA information. Review each product and its available documentation before ordering.', 'foxfire-child' );
	?>
	<header class="ff-shop-header">
		<div class="ff-shop-header__content">
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

/**
 * Keep the loop button's accessible name aligned with its visible label.
 *
 * WooCommerce provides the product-specific action through aria-describedby;
 * repeating a different action in aria-label causes a WCAG label-in-name
 * failure after we standardize the visible catalog label.
 *
 * @param array<string, mixed> $args Template arguments.
 * @param WC_Product           $product Product object.
 * @return array<string, mixed>
 */
function foxfire_align_loop_add_to_cart_accessible_name( array $args, WC_Product $product ): array {
	if ( ! isset( $args['attributes'] ) || ! is_array( $args['attributes'] ) ) {
		$args['attributes'] = array();
	}

	$args['attributes']['aria-label'] = $product->add_to_cart_text();

	return $args;
}
add_filter( 'woocommerce_loop_add_to_cart_args', 'foxfire_align_loop_add_to_cart_accessible_name', 30, 2 );

/**
 * Rename "Default sorting" to "Relevance" in the catalog orderby dropdown.
 *
 * @param array<string, string> $orderby Sorting options.
 * @return array<string, string>
 */
function foxfire_rename_default_sorting_to_relevance( array $orderby ): array {
	if ( isset( $orderby['menu_order'] ) ) {
		$orderby['menu_order'] = __( 'Relevance', 'foxfire-child' );
	}
	return $orderby;
}
add_filter( 'woocommerce_catalog_orderby', 'foxfire_rename_default_sorting_to_relevance', 20 );
add_filter( 'woocommerce_default_catalog_orderby_options', 'foxfire_rename_default_sorting_to_relevance', 20 );

/**
 * Enqueue shop JS and pass AJAX params on shop / product-category pages.
 */
function foxfire_shop_enqueue_scripts(): void {
	if ( ! is_shop() && ! is_product_category() ) {
		return;
	}

	$version  = defined( 'FOXFIRE_CHILD_VERSION' ) ? FOXFIRE_CHILD_VERSION : '1.2.0';
	$js_path  = FOXFIRE_CHILD_DIR . '/assets/js/shop.js';

	wp_enqueue_script(
		'foxfire-shop-js',
		FOXFIRE_CHILD_URI . '/assets/js/shop.js',
		array(),
		file_exists( $js_path ) ? (string) filemtime( $js_path ) : $version,
		true
	);

	wp_localize_script(
		'foxfire-shop-js',
		'ffShopParams',
		array(
			'ajax_url' => admin_url( 'admin-ajax.php' ),
			'nonce'    => wp_create_nonce( 'foxfire_shop_search_nonce' ),
		)
	);
}
add_action( 'wp_enqueue_scripts', 'foxfire_shop_enqueue_scripts', 30 );

/**
 * AJAX handler: search products and return rendered card HTML.
 *
 * Accepts POST params: search (string), nonce (string).
 * Returns JSON: { success, data: { html, count, count_text } }
 */
function foxfire_ajax_search_products(): void {
	check_ajax_referer( 'foxfire_shop_search_nonce', 'nonce' );

	$search = isset( $_POST['search'] ) ? sanitize_text_field( wp_unslash( $_POST['search'] ) ) : '';

	$args = array(
		'post_type'      => 'product',
		'post_status'    => 'publish',
		'posts_per_page' => 50,
		'orderby'        => 'menu_order title',
		'order'          => 'ASC',
	);

	if ( ! empty( $search ) ) {
		$args['s'] = $search;
	}

	$query = new WP_Query( $args );
	$count = $query->found_posts;

	ob_start();

	if ( $query->have_posts() ) {
		while ( $query->have_posts() ) {
			$query->the_post();
			wc_get_template_part( 'content', 'product' );
		}
	}

	$html = ob_get_clean();
	wp_reset_postdata();

	// Build the result count text.
	if ( empty( $search ) ) {
		$count_text = sprintf(
			/* translators: %d: total number of results */
			_n( 'Showing the single result', 'Showing all %d results', $count, 'foxfire-child' ),
			$count
		);
	} else {
		$count_text = sprintf(
			/* translators: %d: number of results */
			_n( '%d result found', '%d results found', $count, 'foxfire-child' ),
			$count
		);
	}

	wp_send_json_success(
		array(
			'html'       => $html,
			'count'      => $count,
			'count_text' => $count_text,
		)
	);
}
add_action( 'wp_ajax_foxfire_search_products', 'foxfire_ajax_search_products' );
add_action( 'wp_ajax_nopriv_foxfire_search_products', 'foxfire_ajax_search_products' );
