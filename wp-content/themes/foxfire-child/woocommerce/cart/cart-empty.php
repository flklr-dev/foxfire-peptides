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
		<svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
			<path d="m2 7 4.41-4.41A2 2 0 0 1 7.83 2h8.34a2 2 0 0 1 1.42.59L22 7"/>
			<path d="M4 12v8a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-8"/>
			<circle cx="12" cy="12" r="2"/>
		</svg>
	</div>

	<h1 class="ff-cart-empty__title"><?php esc_html_e( 'Your cart is currently empty', 'foxfire-child' ); ?></h1>
	<p class="ff-cart-empty__desc">
		<?php esc_html_e( 'Explore our catalog of third-party verified laboratory peptides and research compounds.', 'foxfire-child' ); ?>
	</p>

	<div class="ff-cart-empty__actions">
		<a href="<?php echo esc_url( $shop_url ); ?>" class="ff-btn ff-btn--primary ff-cart-empty__btn">
			<?php esc_html_e( 'Explore Compounds', 'foxfire-child' ); ?>
			<span aria-hidden="true">&rarr;</span>
		</a>
	</div>
</div>
