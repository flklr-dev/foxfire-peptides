<?php
/**
 * My Addresses overview — Chunk 1L.
 *
 * @package Foxfire_Child
 */

defined( 'ABSPATH' ) || exit;

$customer_id = get_current_user_id();

if ( ! wc_ship_to_billing_address_only() && wc_shipping_enabled() ) {
	$get_addresses = apply_filters(
		'woocommerce_my_account_get_addresses',
		array(
			'billing'  => __( 'Billing Address', 'foxfire-child' ),
			'shipping' => __( 'Shipping Address', 'foxfire-child' ),
		),
		$customer_id
	);
} else {
	$get_addresses = apply_filters(
		'woocommerce_my_account_get_addresses',
		array(
			'billing' => __( 'Billing Address', 'foxfire-child' ),
		),
		$customer_id
	);
}

$customer = new WC_Customer( $customer_id );
?>

<div class="ff-account-addresses-section">

	<div class="ff-section-title-wrap">
		<h1 class="ff-section-title"><?php esc_html_e( 'Saved Addresses', 'foxfire-child' ); ?></h1>
		<p class="ff-section-subtext"><?php esc_html_e( 'The following addresses are used on checkout by default for rapid order processing.', 'foxfire-child' ); ?></p>
	</div>

	<div class="ff-addresses-cards-grid">
		<?php foreach ( $get_addresses as $name => $address_title ) : ?>
			<?php
			$edit_url = wc_get_endpoint_url( 'edit-address', $name );
			$is_ship  = 'shipping' === $name;

			// Extract address components via WC_Customer with user meta fallbacks
			if ( $is_ship ) {
				$first_name = $customer ? $customer->get_shipping_first_name() : '';
				$last_name  = $customer ? $customer->get_shipping_last_name() : '';
				$street_1   = $customer ? $customer->get_shipping_address_1() : '';
				$street_2   = $customer ? $customer->get_shipping_address_2() : '';
				$city       = $customer ? $customer->get_shipping_city() : '';
				$state      = $customer ? $customer->get_shipping_state() : '';
				$postcode   = $customer ? $customer->get_shipping_postcode() : '';
				$country    = $customer ? $customer->get_shipping_country() : '';
			} else {
				$first_name = $customer ? $customer->get_billing_first_name() : '';
				$last_name  = $customer ? $customer->get_billing_last_name() : '';
				$street_1   = $customer ? $customer->get_billing_address_1() : '';
				$street_2   = $customer ? $customer->get_billing_address_2() : '';
				$city       = $customer ? $customer->get_billing_city() : '';
				$state      = $customer ? $customer->get_billing_state() : '';
				$postcode   = $customer ? $customer->get_billing_postcode() : '';
				$country    = $customer ? $customer->get_billing_country() : '';
			}

			// Fallbacks
			if ( empty( $first_name ) && empty( $last_name ) ) {
				$first_name = get_user_meta( $customer_id, $name . '_first_name', true );
				$last_name  = get_user_meta( $customer_id, $name . '_last_name', true );
			}
			if ( empty( $first_name ) && empty( $last_name ) ) {
				$first_name = get_user_meta( $customer_id, 'first_name', true );
				$last_name  = get_user_meta( $customer_id, 'last_name', true );
			}

			$full_name = trim( $first_name . ' ' . $last_name );
			if ( empty( $full_name ) ) {
				$user_data = get_userdata( $customer_id );
				$full_name = $user_data ? $user_data->display_name : '';
			}

			if ( empty( $street_1 ) ) {
				$street_1 = get_user_meta( $customer_id, $name . '_address_1', true );
				$street_2 = get_user_meta( $customer_id, $name . '_address_2', true );
			}
			if ( empty( $city ) ) {
				$city = get_user_meta( $customer_id, $name . '_city', true );
			}
			if ( empty( $state ) ) {
				$state = get_user_meta( $customer_id, $name . '_state', true );
			}
			if ( empty( $postcode ) ) {
				$postcode = get_user_meta( $customer_id, $name . '_postcode', true );
			}
			if ( empty( $country ) ) {
				$country = get_user_meta( $customer_id, $name . '_country', true );
			}

			// Resolve full country name
			$country_name = $country;
			if ( function_exists( 'WC' ) && WC()->countries && isset( WC()->countries->countries[ $country ] ) ) {
				$country_name = WC()->countries->countries[ $country ];
			}

			// Resolve full state name
			$state_name = $state;
			if ( function_exists( 'WC' ) && WC()->countries && ! empty( $country ) ) {
				$states = WC()->countries->get_states( $country );
				if ( is_array( $states ) && isset( $states[ $state ] ) ) {
					$state_name = $states[ $state ];
				}
			}

			// Format Row 2: Street Address, City, Region / State in one row
			$street_parts    = array_filter( array( $street_1, $street_2 ) );
			$street_combined = implode( ', ', $street_parts );
			$row_2_parts     = array_filter( array( $street_combined, $city, $state_name ) );
			$row_2           = implode( ', ', $row_2_parts );

			// Format Row 3: Country , Zip in one row
			$row_3_parts = array_filter( array( $country_name, $postcode ) );
			$row_3       = implode( ', ', $row_3_parts );

			$has_address = ! empty( $street_combined ) || ! empty( $city ) || ! empty( $postcode );
			?>
			<div class="ff-address-card <?php echo $has_address ? 'has-address' : 'is-empty'; ?>">
				<div class="ff-address-card__header">
					<div class="ff-address-card__header-left">
						<div class="ff-address-card__icon" aria-hidden="true">
							<?php if ( $is_ship ) : ?>
								<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 10c0 4.993-5.539 10.193-7.399 11.799a1 1 0 0 1-1.202 0C9.539 20.193 4 14.993 4 10a8 8 0 0 1 16 0"/><circle cx="12" cy="10" r="3"/></svg>
							<?php else : ?>
								<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="20" height="14" x="2" y="5" rx="2"/><line x1="2" x2="22" y1="10" y2="10"/></svg>
							<?php endif; ?>
						</div>
						<h2 class="ff-address-card__title"><?php echo esc_html( $address_title ); ?></h2>
					</div>
					
					<!-- Edit button moved to header (replacing default badge) -->
					<a href="<?php echo esc_url( $edit_url ); ?>" class="ff-address-card__edit-btn" aria-label="<?php echo esc_attr( sprintf( __( 'Edit %s', 'foxfire-child' ), $address_title ) ); ?>">
						<svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 20h9"/><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"/></svg>
						<span><?php echo $has_address ? esc_html__( 'Edit', 'foxfire-child' ) : esc_html__( 'Add', 'foxfire-child' ); ?></span>
					</a>
				</div>

				<div class="ff-address-card__body">
					<?php if ( $has_address ) : ?>
						<div class="ff-address-info-rows">
							<!-- Row 1: Name -->
							<div class="ff-address-info-row ff-address-info-row--name">
								<strong><?php echo esc_html( $full_name ); ?></strong>
							</div>

							<!-- Row 2: Street Address, City, Region / State in one row -->
							<?php if ( ! empty( $row_2 ) ) : ?>
								<div class="ff-address-info-row ff-address-info-row--street">
									<?php echo esc_html( $row_2 ); ?>
								</div>
							<?php endif; ?>

							<!-- Row 3: Country , Zip in one row -->
							<?php if ( ! empty( $row_3 ) ) : ?>
								<div class="ff-address-info-row ff-address-info-row--country">
									<?php echo esc_html( $row_3 ); ?>
								</div>
							<?php endif; ?>
						</div>
					<?php else : ?>
						<p class="ff-address-card__empty-text">
							<?php esc_html_e( 'No address registered yet. Click Edit to add your default details.', 'foxfire-child' ); ?>
						</p>
					<?php endif; ?>
				</div>
			</div>
		<?php endforeach; ?>
	</div>

</div>
