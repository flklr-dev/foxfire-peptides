<?php
/**
 * Checkout Form Template — Shopee-style unified single container with Address Modal.
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

// Retrieve pre-filled address details
$user_id    = get_current_user_id();
$first_name = $checkout->get_value( 'billing_first_name' );
$last_name  = $checkout->get_value( 'billing_last_name' );
$phone      = $checkout->get_value( 'billing_phone' );
$email      = $checkout->get_value( 'billing_email' );
$address_1  = $checkout->get_value( 'billing_address_1' );
$address_2  = $checkout->get_value( 'billing_address_2' );
$city       = $checkout->get_value( 'billing_city' );
$state      = $checkout->get_value( 'billing_state' );
$postcode   = $checkout->get_value( 'billing_postcode' );
$country    = $checkout->get_value( 'billing_country' );

// Fallback to user meta for logged-in users if checkout fields are blank
if ( $user_id > 0 ) {
	$first_name = $first_name ?: get_user_meta( $user_id, 'billing_first_name', true );
	$last_name  = $last_name ?: get_user_meta( $user_id, 'billing_last_name', true );
	$phone      = $phone ?: get_user_meta( $user_id, 'billing_phone', true );
	$email      = $email ?: ( get_user_meta( $user_id, 'billing_email', true ) ?: wp_get_current_user()->user_email );
	$address_1  = $address_1 ?: get_user_meta( $user_id, 'billing_address_1', true );
	$address_2  = $address_2 ?: get_user_meta( $user_id, 'billing_address_2', true );
	$city       = $city ?: get_user_meta( $user_id, 'billing_city', true );
	$state      = $state ?: get_user_meta( $user_id, 'billing_state', true );
	$postcode   = $postcode ?: get_user_meta( $user_id, 'billing_postcode', true );
	$country    = $country ?: ( get_user_meta( $user_id, 'billing_country', true ) ?: 'US' );
}

$full_name = trim( $first_name . ' ' . $last_name );
$has_saved_address = ( ! empty( $address_1 ) && ! empty( $full_name ) );

$country_name = ( $country && WC()->countries && isset( WC()->countries->countries[ $country ] ) ) ? WC()->countries->countries[ $country ] : $country;
$states_list  = ( $country && WC()->countries ) ? WC()->countries->get_states( $country ) : array();
$state_name   = ( $state && ! empty( $states_list[ $state ] ) ) ? $states_list[ $state ] : $state;

$address_parts = array_filter( array(
	$address_1,
	$address_2,
	$city,
	trim( $state_name . ' ' . $postcode ),
	$country_name,
) );
$formatted_address = implode( ', ', $address_parts );
?>

<div class="ff-checkout-page">
	<!-- Checkout Header -->
	<header class="ff-checkout-header">
		<h1 class="ff-checkout-header__title"><?php esc_html_e( 'Checkout', 'foxfire-child' ); ?></h1>
	</header>

	<form name="checkout" method="post" class="checkout woocommerce-checkout ff-checkout-form" action="<?php echo esc_url( wc_get_checkout_url() ); ?>" enctype="multipart/form-data">

		<div class="ff-checkout-flow">
			
			<!-- Unified Single Container (Shopee Vibes) -->
			<div class="ff-checkout-container">

				<!-- Section 1: Delivery Address (Shopee Card Only — NO raw fields on page) -->
				<?php do_action( 'woocommerce_checkout_before_customer_details' ); ?>

				<div class="ff-checkout-section-wrap ff-checkout-address-section">
					<div id="customer_details" style="display:none;"></div>

					<!-- Shopee Address Card (Clicking opens the modal) -->
					<div class="ff-shopee-address-card <?php echo $has_saved_address ? 'has-saved-address' : 'no-saved-address'; ?>" id="ffShopeeAddressCard" role="button" tabindex="0" aria-label="<?php esc_attr_e( 'Click to set or change delivery address', 'foxfire-child' ); ?>">
						
						<!-- Desktop Header: Title on left, Action button on right (NO chevron on web) -->
						<div class="ff-shopee-address__header">
							<div class="ff-shopee-address__title">
								<svg class="ff-shopee-loc-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#e85a0c" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
									<path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path>
									<circle cx="12" cy="10" r="3"></circle>
								</svg>
								<span><?php esc_html_e( 'Delivery Address', 'foxfire-child' ); ?></span>
							</div>
							<button type="button" class="ff-address-change-btn" id="ffAddressChangeBtnDesktop">
								<?php echo $has_saved_address ? esc_html__( 'Change', 'foxfire-child' ) : esc_html__( '+ Set Address', 'foxfire-child' ); ?>
							</button>
						</div>

						<!-- Desktop Content: ONE single line, NO second pin, NO chevron -->
						<div class="ff-shopee-address__desktop-content">
							<div class="ff-shopee-address__desktop-line" id="ffDesktopAddressLine" style="<?php echo $has_saved_address ? 'display:flex;' : 'display:none;'; ?>">
								<strong class="ff-address-name" id="ffPreviewName"><?php echo esc_html( $full_name ); ?></strong>
								<span class="ff-address-phone" id="ffPreviewPhone" style="<?php echo $phone ? 'display:inline;' : 'display:none;'; ?>"><?php echo esc_html( $phone ); ?></span>
								<span class="ff-address-text" id="ffPreviewAddress"><?php echo esc_html( $formatted_address ); ?></span>
							</div>
							<div class="ff-shopee-address__desktop-empty" id="ffDesktopAddressEmpty" style="<?php echo $has_saved_address ? 'display:none;' : 'display:block;'; ?>">
								<span class="ff-address-empty-text"><?php esc_html_e( 'No delivery address provided yet. Click to enter your contact and shipping details.', 'foxfire-child' ); ?></span>
							</div>
						</div>

						<!-- Mobile View: Clean original Shopee row (Hidden on desktop) -->
						<div class="ff-shopee-address__mobile-view">
							<div class="ff-shopee-address__mobile-pin">
								<svg class="ff-shopee-loc-icon" width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="#e85a0c" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
									<path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path>
									<circle cx="12" cy="10" r="3"></circle>
								</svg>
							</div>
							<div class="ff-shopee-address__mobile-info">
								<div class="ff-shopee-address__mobile-row-1">
									<strong class="ff-mobile-name" id="ffMobilePreviewName"><?php echo esc_html( $full_name ? $full_name : __( 'Set delivery address', 'foxfire-child' ) ); ?></strong>
									<span class="ff-mobile-phone" id="ffMobilePreviewPhone" style="<?php echo $phone ? 'display:inline;' : 'display:none;'; ?>"><?php echo esc_html( $phone ); ?></span>
								</div>
								<div class="ff-shopee-address__mobile-row-2">
									<span class="ff-mobile-address" id="ffMobilePreviewAddress"><?php echo esc_html( $formatted_address ? $formatted_address : __( 'Click to enter your delivery and contact details', 'foxfire-child' ) ); ?></span>
								</div>
							</div>
							<div class="ff-shopee-address__mobile-chevron">
								<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
									<polyline points="9 18 15 12 9 6"></polyline>
								</svg>
							</div>
						</div>

					</div>

				</div>

				<?php do_action( 'woocommerce_checkout_after_customer_details' ); ?>

				<!-- Section 2: Products Ordered, Subtotal, Shipment, Total & Section 3: Payment Method -->
				<?php do_action( 'woocommerce_checkout_before_order_review' ); ?>

				<div id="order_review" class="woocommerce-checkout-review-order ff-checkout-review-order">
					<?php do_action( 'woocommerce_checkout_order_review' ); ?>
				</div>

				<?php do_action( 'woocommerce_checkout_after_order_review' ); ?>

			</div>

		</div>

		<!-- Delivery Address & Contact MODAL (Popup dialog — hidden by default) -->
		<div class="ff-address-modal" id="ffAddressModal" aria-hidden="true" role="dialog" aria-labelledby="ffAddressModalTitle">
			<div class="ff-address-modal__backdrop" id="ffAddressModalBackdrop"></div>
			
			<div class="ff-address-modal__dialog">
				
				<div class="ff-address-modal__header">
					<h3 class="ff-address-modal__title" id="ffAddressModalTitle">
						<svg class="ff-shopee-loc-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#e85a0c" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
							<path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path>
							<circle cx="12" cy="10" r="3"></circle>
						</svg>
						<span><?php esc_html_e( 'Delivery & Contact Details', 'foxfire-child' ); ?></span>
					</h3>
					<button type="button" class="ff-address-modal__close" id="ffAddressModalClose" aria-label="<?php esc_attr_e( 'Close', 'foxfire-child' ); ?>">
						<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
					</button>
				</div>

				<div class="ff-address-modal__body">
					
					<?php if ( ! is_user_logged_in() && 'yes' === get_option( 'woocommerce_enable_checkout_login_reminder' ) ) : ?>
						<div class="ff-address-modal__login-hint">
							<span><?php esc_html_e( 'Have an account?', 'foxfire-child' ); ?></span>
							<a href="<?php echo esc_url( wc_get_page_permalink( 'myaccount' ) ); ?>" class="ff-link-orange"><?php esc_html_e( 'Log in', 'foxfire-child' ); ?></a>
						</div>
					<?php endif; ?>

					<div class="ff-address-fields-grid">
						<?php
						$billing_fields = $checkout->get_checkout_fields( 'billing' );
						if ( ! empty( $billing_fields ) ) {
							uasort( $billing_fields, function ( $a, $b ) {
								$a_priority = $a['priority'] ?? 100;
								$b_priority = $b['priority'] ?? 100;
								return $a_priority <=> $b_priority;
							} );
							foreach ( $billing_fields as $key => $field ) {
								woocommerce_form_field( $key, $field, $checkout->get_value( $key ) );
							}
						}
						?>

						<!-- Set as Default Address Checkbox (Auto-enabled) -->
						<p class="form-row form-row-wide ff-default-address-row ff-checkbox-row">
							<label class="woocommerce-form__label woocommerce-form__label-for-checkbox checkbox">
								<input class="woocommerce-form__input woocommerce-form__input-checkbox input-checkbox" id="ff_set_default_address" type="checkbox" name="ff_set_default_address" value="1" checked="checked" />
								<span><?php esc_html_e( 'Set as default delivery address', 'foxfire-child' ); ?></span>
							</label>
						</p>
					</div>

					<?php if ( ! is_user_logged_in() && $checkout->is_registration_enabled() ) : ?>
						<div class="ff-checkout-account-fields">
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
					<?php endif; ?>

				</div>

				<div class="ff-address-modal__footer">
					<button type="button" class="ff-address-modal__save-btn" id="ffAddressModalSave">
						<?php esc_html_e( 'Save & Use Address', 'foxfire-child' ); ?>
					</button>
				</div>

			</div>
		</div>

	</form>

	<!-- Minimalist Center Processing Toast (Matches Added to Cart HUD style) -->
	<div id="ffCheckoutProcessingOverlay" class="ff-checkout-processing-overlay" style="display: none;" aria-hidden="true" role="status" aria-live="polite">
		<div class="ff-checkout-processing-toast">
			<div class="ff-checkout-processing-spinner" aria-hidden="true"></div>
			<p class="ff-checkout-processing-title"><?php esc_html_e( 'Processing your order', 'foxfire-child' ); ?></p>
			<span class="ff-checkout-processing-sub"><?php esc_html_e( 'Please do not close..', 'foxfire-child' ); ?></span>
		</div>
	</div>

</div>

<?php do_action( 'woocommerce_after_checkout_form', $checkout ); ?>
