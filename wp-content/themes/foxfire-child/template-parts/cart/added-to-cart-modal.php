<?php
/**
 * Minimalist Center Toast / HUD: Added to Cart
 *
 * Black translucent toast with green checkmark and "Added to your cart" text.
 * Auto-dismisses after ~2 seconds.
 *
 * @package Foxfire_Child
 */

defined( 'ABSPATH' ) || exit;
?>

<div id="ff-atc-toast" class="ff-atc-toast" role="status" aria-live="polite" aria-hidden="true">
	<div class="ff-atc-toast__content">
		<div class="ff-atc-toast__icon" aria-hidden="true">
			<svg width="34" height="34" viewBox="0 0 24 24" fill="none" stroke="#22c55e" stroke-width="2.8" stroke-linecap="round" stroke-linejoin="round">
				<polyline points="20 6 9 17 4 12"></polyline>
			</svg>
		</div>
		<p class="ff-atc-toast__text"><?php esc_html_e( 'Added to your cart', 'foxfire-child' ); ?></p>
	</div>
</div>
