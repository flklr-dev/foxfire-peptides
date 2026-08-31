<?php
/**
 * Checkout Form Template — Chunk 1K.
 *
 * @package Foxfire_Child
 */

defined( 'ABSPATH' ) || exit;

do_action( 'woocommerce_before_checkout_form', $checkout );

// If checkout registration is required and not logged in, customer cannot checkout.
if ( ! $checkout->is_registration_enabled() && $checkout->is_registration_required() && ! is_user_logged_in() ) {
	echo esc_html( apply_filters( 'woocommerce_checkout_must_be_logged_in_message', __( 'You must be logged in to checkout.', 'foxfire-child' ) ) );
	return;
}
?>

<div class="ff-checkout-page">
	<!-- Checkout Header -->
	<header class="ff-checkout-header">
		<h1 class="ff-checkout-header__title"><?php esc_html_e( 'Checkout', 'foxfire-child' ); ?></h1>
	</header>

	<form name="checkout" method="post" class="checkout woocommerce-checkout ff-checkout-form" action="<?php echo esc_url( wc_get_checkout_url() ); ?>" enctype="multipart/form-data">

		<div class="ff-checkout-grid">

			<!-- Left Column: Customer & Shipping Details -->
			<div class="ff-checkout-main">

				<?php if ( $checkout->get_checkout_fields() ) : ?>

					<?php do_action( 'woocommerce_checkout_before_customer_details' ); ?>

					<div class="ff-checkout-sections" id="customer_details">
						
						<!-- Section 1: Customer Contact Information -->
						<div class="ff-checkout-section ff-checkout-section--contact">
							<div class="ff-checkout-section__header">
								<h2 class="ff-checkout-section__title">
									<span class="ff-step-badge">1</span>
									<?php esc_html_e( 'Contact Information', 'foxfire-child' ); ?>
								</h2>
								<?php if ( ! is_user_logged_in() && 'yes' === get_option( 'woocommerce_enable_checkout_login_reminder' ) ) : ?>
									<span class="ff-checkout-login-prompt">
										<?php esc_html_e( 'Have an account?', 'foxfire-child' ); ?>
										<a href="<?php echo esc_url( wc_get_page_permalink( 'myaccount' ) ); ?>" class="ff-link-orange"><?php esc_html_e( 'Log in', 'foxfire-child' ); ?></a>
									</span>
								<?php endif; ?>
							</div>

							<div class="ff-checkout-section__fields">
								<?php
								$billing_fields = $checkout->get_checkout_fields( 'billing' );
								if ( isset( $billing_fields['billing_email'] ) ) {
									woocommerce_form_field( 'billing_email', $billing_fields['billing_email'], $checkout->get_value( 'billing_email' ) );
								}
								if ( isset( $billing_fields['billing_phone'] ) ) {
									woocommerce_form_field( 'billing_phone', $billing_fields['billing_phone'], $checkout->get_value( 'billing_phone' ) );
								}
								?>
							</div>
						</div>

						<!-- Section 2: Shipping / Delivery Address -->
						<div class="ff-checkout-section ff-checkout-section--shipping">
							<div class="ff-checkout-section__header">
								<h2 class="ff-checkout-section__title">
									<span class="ff-step-badge">2</span>
									<?php esc_html_e( 'Shipping Address', 'foxfire-child' ); ?>
								</h2>
								<span class="ff-checkout-section__note">
									<?php esc_html_e( 'Discreet packaging used for all shipments.', 'foxfire-child' ); ?>
								</span>
							</div>

							<div class="ff-checkout-section__fields">
								<?php
								// Render rest of billing/shipping fields (First Name, Last Name, Address, City, State, Postcode, Country)
								foreach ( $billing_fields as $key => $field ) {
									if ( 'billing_email' === $key || 'billing_phone' === $key ) {
										continue;
									}
									woocommerce_form_field( $key, $field, $checkout->get_value( $key ) );
								}
								?>
							</div>
						</div>

						<!-- Section 3: Account Creation & Billing Notes -->
						<?php if ( ! is_user_logged_in() && $checkout->is_registration_enabled() ) : ?>
							<div class="ff-checkout-section ff-checkout-section--account">
								<div class="ff-checkout-section__fields">
									<?php if ( ! $checkout->is_registration_required() ) : ?>
										<p class="form-row form-row-wide create-account ff-checkbox-row">
											<label class="woocommerce-form__label woocommerce-form__label-for-checkbox checkbox">
												<input class="woocommerce-form__input woocommerce-form__input-checkbox input-checkbox" id="createaccount" <?php checked( ( true === $checkout->get_value( 'createaccount' ) || ( true === apply_filters( 'woocommerce_create_account_default_checked', false ) ) ), true ); ?> type="checkbox" name="createaccount" value="1" />
												<span><?php esc_html_e( 'Save my information for faster analytical research reorders', 'foxfire-child' ); ?></span>
											</label>
										</p>
									<?php endif; ?>

									<?php do_action( 'woocommerce_before_checkout_registration_form', $checkout ); ?>

									<?php if ( $checkout->get_checkout_fields( 'account' ) ) : ?>
										<div class="create-account">
											<?php foreach ( $checkout->get_checkout_fields( 'account' ) as $key => $field ) : ?>
												<?php woocommerce_form_field( $key, $field, $checkout->get_value( $key ) ); ?>
											<?php endforeach; ?>
											<div class="clear"></div>
										</div>
									<?php endif; ?>

									<?php do_action( 'woocommerce_after_checkout_registration_form', $checkout ); ?>
								</div>
							</div>
						<?php endif; ?>

						<!-- Section 4: Order Notes / Additional Information -->
						<?php if ( apply_filters( 'woocommerce_enable_order_notes_field', 'yes' === get_option( 'woocommerce_enable_order_comments', 'yes' ) ) ) : ?>
							<div class="ff-checkout-section ff-checkout-section--notes">
								<div class="ff-checkout-section__header">
									<h2 class="ff-checkout-section__title">
										<span class="ff-step-badge">3</span>
										<?php esc_html_e( 'Order Notes & Instructions', 'foxfire-child' ); ?>
										<small class="ff-optional-label">(<?php esc_html_e( 'Optional', 'foxfire-child' ); ?>)</small>
									</h2>
								</div>
								<div class="ff-checkout-section__fields">
									<?php foreach ( $checkout->get_checkout_fields( 'order' ) as $key => $field ) : ?>
										<?php woocommerce_form_field( $key, $field, $checkout->get_value( $key ) ); ?>
									<?php endforeach; ?>
								</div>
							</div>
						<?php endif; ?>

					</div>

					<?php do_action( 'woocommerce_checkout_after_customer_details' ); ?>

				<?php endif; ?>

			</div>

			<!-- Right Column: Sticky Order Summary & Payment Sidebar -->
			<div class="ff-checkout-sidebar">
				<div class="ff-checkout-summary-card">
					<h2 class="ff-checkout-summary-card__title" id="order_review_heading"><?php esc_html_e( 'Order Summary', 'foxfire-child' ); ?></h2>

					<?php do_action( 'woocommerce_checkout_before_order_review' ); ?>

					<div id="order_review" class="woocommerce-checkout-review-order">
						<?php do_action( 'woocommerce_checkout_order_review' ); ?>
					</div>

					<?php do_action( 'woocommerce_checkout_after_order_review' ); ?>
				</div>
			</div>

		</div>

	</form>

</div>

<?php do_action( 'woocommerce_after_checkout_form', $checkout ); ?>
