<?php
/**
 * Foxfire checkout coupon form. WooCommerce handles applying and removing codes.
 *
 * @package Foxfire_Child
 * @version 9.8.0
 */

defined( 'ABSPATH' ) || exit;

if ( ! wc_coupons_enabled() ) {
	return;
}
?>
<div class="ff-checkout-coupon">
	<div class="ff-checkout-coupon__prompt">
		<span><?php esc_html_e( 'Have a promo code?', 'foxfire-child' ); ?></span>
		<a href="#" class="showcoupon" role="button" aria-label="<?php esc_attr_e( 'Enter your promo code', 'foxfire-child' ); ?>" aria-controls="woocommerce-checkout-form-coupon" aria-expanded="false"><?php esc_html_e( 'Enter code', 'foxfire-child' ); ?></a>
	</div>

	<form class="checkout_coupon woocommerce-form-coupon ff-checkout-coupon__form" method="post" style="display:none" id="woocommerce-checkout-form-coupon">
		<label for="coupon_code"><?php esc_html_e( 'Promo code', 'foxfire-child' ); ?></label>
		<div class="ff-checkout-coupon__controls">
			<div class="ff-checkout-coupon__input-wrap">
				<input type="text" name="coupon_code" class="input-text" id="coupon_code" placeholder="<?php esc_attr_e( 'Enter your code', 'foxfire-child' ); ?>" autocomplete="off" spellcheck="false" />
			</div>
			<button type="submit" class="button ff-checkout-coupon__button" name="apply_coupon" value="<?php esc_attr_e( 'Apply coupon', 'foxfire-child' ); ?>"><?php esc_html_e( 'Apply code', 'foxfire-child' ); ?></button>
		</div>
		<?php if ( ! is_user_logged_in() ) : ?>
			<p class="ff-checkout-coupon__login-help">
				<?php esc_html_e( 'Using a members-only code?', 'foxfire-child' ); ?>
				<a href="#" class="showlogin"><?php esc_html_e( 'Log in to Foxfire', 'foxfire-child' ); ?></a>
				<?php esc_html_e( 'first.', 'foxfire-child' ); ?>
			</p>
		<?php endif; ?>
	</form>
</div>
