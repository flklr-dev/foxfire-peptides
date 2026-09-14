<?php
/** Required combined age, terms and research-use acknowledgement. */
defined( 'ABSPATH' ) || exit;
$terms_url = wc_get_page_permalink( 'terms' );
if ( ! $terms_url ) $terms_url = home_url( '/terms-and-conditions/' );
?>
<div class="woocommerce-terms-and-conditions-wrapper">
	<p class="form-row validate-required">
		<label class="woocommerce-form__label woocommerce-form__label-for-checkbox checkbox" for="terms">
			<input type="checkbox" class="woocommerce-form__input woocommerce-form__input-checkbox input-checkbox" name="terms" id="terms" value="1" required aria-required="true" />
			<span class="woocommerce-terms-and-conditions-checkbox-text"><?php
				printf(
					esc_html__( 'I confirm that I am at least 18 years old, have read and agree to the %s, and understand that products offered by Foxfire Peptides are sold for research use only and are not intended for human consumption.', 'foxfire-child' ),
					'<a href="' . esc_url( $terms_url ) . '">' . esc_html__( 'Terms & Conditions', 'foxfire-child' ) . '</a>'
				);
			?></span><span class="required" aria-hidden="true">*</span>
		</label>
		<input type="hidden" name="terms-field" value="1" />
	</p>
</div>
