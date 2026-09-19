<?php
/** Required Terms and separate 21+ research-use acknowledgements. */
defined( 'ABSPATH' ) || exit;
$terms_url = wc_get_page_permalink( 'terms' );
if ( ! $terms_url ) $terms_url = home_url( '/terms-and-conditions/' );
?>
<div class="woocommerce-terms-and-conditions-wrapper">
	<p class="form-row validate-required ff-checkout-acknowledgement">
		<label class="woocommerce-form__label woocommerce-form__label-for-checkbox checkbox" for="terms">
			<input type="checkbox" class="woocommerce-form__input woocommerce-form__input-checkbox input-checkbox" name="terms" id="terms" value="1" required aria-required="true" />
			<span class="woocommerce-terms-and-conditions-checkbox-text"><?php
				printf(
					/* translators: %s: Terms and Conditions link. */
					esc_html__( 'I have read and agree to the %s.', 'foxfire-child' ),
					'<a href="' . esc_url( $terms_url ) . '" target="_blank" rel="noopener noreferrer">' . esc_html__( 'Terms & Conditions', 'foxfire-child' ) . '</a>'
				);
			?></span><span class="required" aria-hidden="true">*</span>
		</label>
	</p>
	<p class="form-row validate-required ff-checkout-acknowledgement">
		<label class="woocommerce-form__label woocommerce-form__label-for-checkbox checkbox" for="foxfire_age_research_acknowledgement">
			<input type="checkbox" class="woocommerce-form__input woocommerce-form__input-checkbox input-checkbox" name="foxfire_age_research_acknowledgement" id="foxfire_age_research_acknowledgement" value="1" required aria-required="true" />
			<span><?php esc_html_e( 'I confirm that I am 21 years of age or older and acknowledge that all products are sold strictly for laboratory research and analytical purposes only and are not intended for human consumption or medical use.', 'foxfire-child' ); ?></span><span class="required" aria-hidden="true">*</span>
		</label>
	</p>
	<input type="hidden" name="terms-field" value="1" />
</div>
