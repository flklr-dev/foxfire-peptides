<?php
/**
 * The Template for displaying product archives, including the main shop page (Chunk 1F).
 *
 * @package Foxfire_Child
 */

defined( 'ABSPATH' ) || exit;

get_header( 'shop' );

/**
 * Hook: woocommerce_before_main_content.
 *
 * @hooked woocommerce_output_content_wrapper - 10 (outputs opening divs for the content)
 * @hooked woocommerce_breadcrumb - 20
 * @hooked WC_Structured_Data::generate_website_data() - 30
 */
do_action( 'woocommerce_before_main_content' );
?>

<div class="ff-shop-container">
	<?php
	/**
	 * Custom Foxfire Catalog Header & Category Pills.
	 */
	foxfire_render_shop_header();
	?>

	<?php if ( woocommerce_product_loop() ) : ?>

		<div class="ff-shop-toolbar">
			<div class="ff-shop-toolbar__search">
				<form role="search" method="get" class="ff-shop-search-form" action="#">
					<div class="ff-shop-search-input-wrap">
						<input
							type="search"
							id="ff-shop-search-input"
							class="ff-shop-search-input"
							placeholder="<?php esc_attr_e( 'Search Compounds', 'foxfire-child' ); ?>"
							value="<?php echo get_search_query(); ?>"
							name="s"
							title="<?php esc_attr_e( 'Search compounds', 'foxfire-child' ); ?>"
						/>
						<input type="hidden" name="post_type" value="product" />
						<?php if ( get_search_query() ) : ?>
							<a href="<?php echo esc_url( foxfire_get_shop_url() ); ?>" class="ff-shop-search-clear" aria-label="<?php esc_attr_e( 'Clear search', 'foxfire-child' ); ?>">✕</a>
						<?php endif; ?>
						<button type="submit" class="ff-shop-search-submit" aria-label="<?php esc_attr_e( 'Submit search', 'foxfire-child' ); ?>">
							<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
								<circle cx="11" cy="11" r="8"></circle>
								<line x1="21" y1="21" x2="16.65" y2="16.65"></line>
							</svg>
						</button>
					</div>
				</form>
			</div>

			<div class="ff-shop-toolbar__meta">
				<div class="ff-shop-toolbar__count">
					<?php woocommerce_result_count(); ?>
				</div>
				<div class="ff-shop-toolbar__ordering">
					<?php woocommerce_catalog_ordering(); ?>
				</div>
			</div>
		</div>

		<?php
		$previous_showcase = wc_get_loop_prop( 'foxfire_catalog_showcase', false );
		wc_set_loop_prop( 'foxfire_catalog_showcase', true );
		woocommerce_product_loop_start();

		if ( wc_get_loop_prop( 'total' ) ) {
			while ( have_posts() ) {
				the_post();

				/**
				 * Hook: woocommerce_shop_loop.
				 */
				do_action( 'woocommerce_shop_loop' );

				wc_get_template_part( 'content', 'product' );
			}
		}

		woocommerce_product_loop_end();
		wc_set_loop_prop( 'foxfire_catalog_showcase', $previous_showcase );

		/**
		 * Hook: woocommerce_after_shop_loop.
		 *
		 * @hooked woocommerce_pagination - 10
		 */
		do_action( 'woocommerce_after_shop_loop' );
		?>

	<?php else : ?>

		<div class="ff-shop-empty">
			<?php
			/**
			 * Hook: woocommerce_no_products_found.
			 *
			 * @hooked wc_no_products_found - 10
			 */
			do_action( 'woocommerce_no_products_found' );
			?>
		</div>

	<?php endif; ?>
</div>

<?php
/**
 * Hook: woocommerce_after_main_content.
 *
 * @hooked woocommerce_output_content_wrapper_end - 10 (outputs closing divs for the content)
 */
do_action( 'woocommerce_after_main_content' );

/**
 * Hook: woocommerce_sidebar.
 * Intentionally omitted for a clean full-width shop grid per DESIGN.md §8.
 */

get_footer( 'shop' );
