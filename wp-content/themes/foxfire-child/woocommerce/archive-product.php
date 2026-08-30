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
			<div class="ff-shop-toolbar__count">
				<?php woocommerce_result_count(); ?>
			</div>
			<div class="ff-shop-toolbar__ordering">
				<?php woocommerce_catalog_ordering(); ?>
			</div>
		</div>

		<?php
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
