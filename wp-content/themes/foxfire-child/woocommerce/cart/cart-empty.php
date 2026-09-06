<?php
/**
 * Empty cart page template.
 *
 * @package Foxfire_Child
 */

defined( 'ABSPATH' ) || exit;

$shop_url = function_exists( 'foxfire_get_shop_url' ) ? foxfire_get_shop_url() : wc_get_page_permalink( 'shop' );
?>

<div class="ff-cart-empty">
	<div class="ff-cart-empty__icon" aria-hidden="true">
		<!-- Modern Shopping Bag Icon -->
		<svg width="44" height="44" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
			<path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4Z"/>
			<path d="M3 6h18"/>
			<path d="M16 10a4 4 0 0 1-8 0"/>
		</svg>
	</div>

	<h1 class="ff-cart-empty__title"><?php esc_html_e( 'Your cart is empty', 'foxfire-child' ); ?></h1>
	<p class="ff-cart-empty__desc">
		<?php esc_html_e( 'Browse our products and find what you need.', 'foxfire-child' ); ?>
	</p>

	<div class="ff-cart-empty__actions">
		<a href="<?php echo esc_url( $shop_url ); ?>" class="ff-btn ff-btn--primary ff-cart-empty__btn">
			<?php esc_html_e( 'Shop Now', 'foxfire-child' ); ?>
		</a>
	</div>
</div>
