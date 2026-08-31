<?php
/**
 * Custom Output a single payment method — Chunk 1K Redesign.
 *
 * @package Foxfire_Child
 */

defined( 'ABSPATH' ) || exit;
?>
<li class="wc_payment_method payment_method_<?php echo esc_attr( $gateway->id ); ?><?php echo $gateway->chosen ? ' is-selected' : ''; ?>">
	<div class="ff-payment-method-header">
		<label for="payment_method_<?php echo esc_attr( $gateway->id ); ?>" class="ff-payment-label">
			<span class="ff-custom-radio" aria-hidden="true">
				<input id="payment_method_<?php echo esc_attr( $gateway->id ); ?>" type="radio" class="input-radio ff-native-radio" name="payment_method" value="<?php echo esc_attr( $gateway->id ); ?>" <?php checked( $gateway->chosen, true ); ?> data-order_button_text="<?php echo esc_attr( $gateway->order_button_text ); ?>" />
				<span class="ff-radio-dot"></span>
			</span>
			<span class="ff-payment-title"><?php echo wp_kses_post( $gateway->get_title() ); ?></span>
		</label>

		<div class="ff-payment-icon" aria-hidden="true">
			<?php if ( 'bacs' === $gateway->id ) : ?>
				<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><line x1="3" y1="21" x2="21" y2="21"></line><line x1="3" y1="10" x2="21" y2="10"></line><polyline points="5 10 5 21"></polyline><polyline points="19 10 19 21"></polyline><polyline points="10 10 10 21"></polyline><polyline points="14 10 14 21"></polyline><polygon points="12 2 20 7 4 7"></polygon></svg>
			<?php elseif ( 'cod' === $gateway->id || 'cheque' === $gateway->id ) : ?>
				<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="5" width="20" height="14" rx="2"></rect><line x1="2" y1="10" x2="22" y2="10"></line></svg>
			<?php else : ?>
				<?php echo $gateway->get_icon(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			<?php endif; ?>
		</div>
	</div>

	<?php if ( $gateway->has_fields() || $gateway->get_description() ) : ?>
		<div class="payment_box payment_method_<?php echo esc_attr( $gateway->id ); ?>" <?php if ( ! $gateway->chosen ) : ?>style="display:none;"<?php endif; ?>>
			<?php $gateway->payment_fields(); ?>
		</div>
	<?php endif; ?>
</li>
