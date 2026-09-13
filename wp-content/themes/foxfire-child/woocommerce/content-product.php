<?php
/**
 * The template for displaying product content within loops (Chunk 1F).
 *
 * @package Foxfire_Child
 */

defined( 'ABSPATH' ) || exit;

global $product;

if ( empty( $product ) || ! $product->is_visible() ) {
	return;
}

$product_id = $product->get_id();
$categories = wc_get_product_category_list( $product_id, ', ' );
$batch_lot  = function_exists( 'foxfire_get_product_batch_lot' ) ? foxfire_get_product_batch_lot( $product_id ) : '';
$classes    = wc_get_product_class( array( 'ff-product-card' ), $product );
?>

<li <?php wc_product_class( $classes, $product ); ?>>
	<div class="ff-product-card__inner">
		<div class="ff-product-card__media">
			<?php
			if ( function_exists( 'foxfire_render_product_card_badges' ) ) {
				foxfire_render_product_card_badges();
			}
			?>
			<a href="<?php the_permalink(); ?>" class="ff-product-card__image-link" tabindex="-1" aria-hidden="true">
				<?php
				if ( has_post_thumbnail( $product_id ) ) {
					global $foxfire_product_card_image_position;
					$foxfire_product_card_image_position = isset( $foxfire_product_card_image_position ) ? (int) $foxfire_product_card_image_position : 0;
					$foxfire_product_card_image_position++;

					$image_attributes = array(
						'class'    => 'ff-product-card__img',
						'alt'      => the_title_attribute( array( 'echo' => false ) ),
						'decoding' => 'async',
					);

					// Homepage cards sit below the hero; keep its LCP image first in line.
					if ( is_front_page() || is_home() ) {
						$image_attributes['loading']       = 'lazy';
						$image_attributes['fetchpriority'] = 'low';
					} elseif (
						1 === $foxfire_product_card_image_position
						&& ( ( function_exists( 'is_shop' ) && is_shop() ) || ( function_exists( 'is_product_category' ) && is_product_category() ) )
					) {
						$image_attributes['loading']       = 'eager';
						$image_attributes['fetchpriority'] = 'high';
					} else {
						$image_attributes['loading'] = 'lazy';
					}

					echo get_the_post_thumbnail(
						$product_id,
						'woocommerce_thumbnail',
						$image_attributes
					);
				} else {
					echo wc_placeholder_img( 'woocommerce_thumbnail' );
				}
				?>
			</a>
		</div>

		<div class="ff-product-card__body">
			<?php
			$terms = get_the_terms( $product_id, 'product_cat' );
			if ( ! empty( $terms ) && ! is_wp_error( $terms ) ) :
				$first_cat = reset( $terms );
				$cat_name  = str_replace( ' (TBD)', '', $first_cat->name );
				?>
				<span class="ff-product-card__category"><?php echo esc_html( $cat_name ); ?></span>
			<?php endif; ?>

			<h2 class="ff-product-card__title">
				<a href="<?php the_permalink(); ?>" class="ff-product-card__title-link">
					<?php the_title(); ?>
				</a>
			</h2>

			<?php if ( ! empty( $batch_lot ) ) : ?>
				<div class="ff-product-card__batch">
					<span class="ff-product-card__batch-label"><?php esc_html_e( 'Batch:', 'foxfire-child' ); ?></span>
					<span class="ff-product-card__batch-value"><?php echo esc_html( $batch_lot ); ?></span>
				</div>
			<?php endif; ?>

			<div class="ff-product-card__price">
				<?php echo $product->get_price_html(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			</div>

			<div class="ff-product-card__action">
				<?php if ( wc_get_loop_prop( 'foxfire_homepage_showcase', false ) || wc_get_loop_prop( 'foxfire_catalog_showcase', false ) ) : ?>
					<a href="<?php echo esc_url( $product->get_permalink() ); ?>" class="button ff-product-card__view-product" aria-label="<?php echo esc_attr( sprintf( __( 'View product: %s', 'foxfire-child' ), $product->get_name() ) ); ?>">
						<?php esc_html_e( 'VIEW PRODUCT', 'foxfire-child' ); ?>
					</a>
				<?php else : ?>
					<?php woocommerce_template_loop_add_to_cart(); ?>
				<?php endif; ?>
			</div>
		</div>
	</div>
</li>
