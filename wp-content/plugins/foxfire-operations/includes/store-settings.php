<?php
/**
 * Administrator-only shipping and payment configuration helpers.
 *
 * WooCommerce remains the source of truth for zones, rates, gateways, and
 * customer-facing payment instructions.
 *
 * @package Foxfire_Operations
 */

defined( 'ABSPATH' ) || exit;

/** Load and register the disabled-by-default Foxfire manual gateways. */
function foxfire_operations_load_manual_gateways(): void {
	if ( ! class_exists( 'WC_Payment_Gateway' ) ) {
		return;
	}

	require_once FOXFIRE_OPERATIONS_DIR . 'includes/manual-gateways.php';
	add_filter( 'woocommerce_payment_gateways', 'foxfire_operations_register_manual_gateways' );
}
add_action( 'plugins_loaded', 'foxfire_operations_load_manual_gateways', 20 );

/**
 * Add the Foxfire manual gateways to WooCommerce.
 *
 * @param string[] $gateways Registered gateway classes.
 * @return string[]
 */
function foxfire_operations_register_manual_gateways( array $gateways ): array {
	$gateways[] = 'Foxfire_Operations_Gateway_Wise';
	$gateways[] = 'Foxfire_Operations_Gateway_Zelle';
	$gateways[] = 'Foxfire_Operations_Gateway_GCash';

	return $gateways;
}

/** Register the Administrator-only store configuration overview. */
function foxfire_operations_register_store_configuration_page(): void {
	add_submenu_page(
		'foxfire-operations',
		__( 'Store Configuration', 'foxfire-operations' ),
		__( 'Store Configuration', 'foxfire-operations' ),
		FOXFIRE_OPERATIONS_SENSITIVE_CAPABILITY,
		'foxfire-store-configuration',
		'foxfire_operations_render_store_configuration_page'
	);
}
add_action( 'admin_menu', 'foxfire_operations_register_store_configuration_page', 40 );

/**
 * Return the customer-visible amount for a shipping rate.
 */
function foxfire_operations_shipping_rate_display_cost( WC_Shipping_Rate $rate ): float {
	$cost = (float) $rate->get_cost();
	if ( function_exists( 'WC' ) && WC()->cart && WC()->cart->display_prices_including_tax() ) {
		$cost += (float) $rate->get_shipping_tax();
	}

	return max( 0.0, $cost );
}

/**
 * Automatically select one WooCommerce rate for the storefront.
 *
 * Free shipping wins whenever WooCommerce says it is eligible. Otherwise the
 * least expensive eligible flat rate wins; if a zone has no flat rate, the
 * least expensive available method is used. Thresholds and costs remain
 * entirely controlled by the matching WooCommerce shipping zone.
 *
 * @param array<string, WC_Shipping_Rate> $rates   Eligible rates.
 * @param array<string, mixed>            $package Shipping package.
 * @return array<string, WC_Shipping_Rate>
 */
function foxfire_operations_choose_automatic_shipping_rate( array $rates, array $package ): array {
	unset( $package );

	if ( count( $rates ) <= 1 ) {
		return $rates;
	}

	$sort_by_cost = static function ( WC_Shipping_Rate $left, WC_Shipping_Rate $right ): int {
		$cost_comparison = foxfire_operations_shipping_rate_display_cost( $left ) <=> foxfire_operations_shipping_rate_display_cost( $right );
		return 0 !== $cost_comparison ? $cost_comparison : strcmp( $left->get_id(), $right->get_id() );
	};

	$free_rates = array_filter(
		$rates,
		static fn( WC_Shipping_Rate $rate ): bool => 'free_shipping' === $rate->get_method_id()
	);

	if ( ! empty( $free_rates ) ) {
		uasort( $free_rates, $sort_by_cost );
		$selected = reset( $free_rates );

		$paid_rates = array_filter(
			$rates,
			static fn( WC_Shipping_Rate $rate ): bool => 'free_shipping' !== $rate->get_method_id() && foxfire_operations_shipping_rate_display_cost( $rate ) > 0
		);
		if ( ! empty( $paid_rates ) ) {
			uasort( $paid_rates, $sort_by_cost );
			$comparison_rate = reset( $paid_rates );
			$selected->add_meta_data( '_foxfire_comparison_shipping_cost', foxfire_operations_shipping_rate_display_cost( $comparison_rate ) );
		}

		return array( $selected->get_id() => $selected );
	}

	$flat_rates = array_filter(
		$rates,
		static fn( WC_Shipping_Rate $rate ): bool => 'flat_rate' === $rate->get_method_id()
	);
	$candidates = ! empty( $flat_rates ) ? $flat_rates : $rates;
	uasort( $candidates, $sort_by_cost );
	$selected = reset( $candidates );

	return array( $selected->get_id() => $selected );
}
add_filter( 'woocommerce_package_rates', 'foxfire_operations_choose_automatic_shipping_rate', 100, 2 );

/**
 * Build a package for matching the current customer to a WooCommerce zone.
 *
 * @return array<string, mixed>|null
 */
function foxfire_operations_current_shipping_package(): ?array {
	if ( ! function_exists( 'WC' ) || ! WC()->customer || ! WC()->cart ) {
		return null;
	}

	$country  = WC()->customer->get_shipping_country();
	$state    = WC()->customer->get_shipping_state();
	$postcode = WC()->customer->get_shipping_postcode();
	$city     = WC()->customer->get_shipping_city();

	if ( '' === $country ) {
		return null;
	}

	return array(
		'contents'        => WC()->cart->get_cart(),
		'contents_cost'   => WC()->cart->get_cart_contents_total(),
		'applied_coupons' => WC()->cart->get_applied_coupons(),
		'user'            => array( 'ID' => get_current_user_id() ),
		'destination'     => array(
			'country'   => $country,
			'state'     => $state,
			'postcode'  => $postcode,
			'city'      => $city,
			'address'   => WC()->customer->get_shipping_address(),
			'address_1' => WC()->customer->get_shipping_address(),
			'address_2' => WC()->customer->get_shipping_address_2(),
		),
		'cart_subtotal'   => WC()->cart->get_displayed_subtotal(),
	);
}

/**
 * Return progress data for a saved minimum-order free-shipping method.
 *
 * Coupon-dependent methods are deliberately excluded because a monetary
 * progress message would not accurately describe their eligibility rule.
 *
 * @return array{minimum:float,current:float,remaining:float,percent:float,qualified:bool}|null
 */
function foxfire_operations_get_free_shipping_progress(): ?array {
	if ( ! class_exists( 'WC_Shipping_Zones' ) ) {
		return null;
	}

	$package = foxfire_operations_current_shipping_package();
	if ( null === $package ) {
		return null;
	}

	$zone = WC_Shipping_Zones::get_zone_matching_package( $package );
	if ( ! $zone instanceof WC_Shipping_Zone ) {
		return null;
	}

	$minimum = null;
	$current = null;
	foreach ( $zone->get_shipping_methods( true ) as $method ) {
		if ( 'free_shipping' !== $method->id || 'min_amount' !== $method->get_option( 'requires' ) ) {
			continue;
		}

		$candidate = (float) $method->get_option( 'min_amount', 0 );
		if ( $candidate <= 0 || ( null !== $minimum && $candidate >= $minimum ) ) {
			continue;
		}

		$minimum = $candidate;
		$current = (float) WC()->cart->get_displayed_subtotal();
		if ( 'no' === $method->get_option( 'ignore_discounts', 'no' ) ) {
			$current -= (float) WC()->cart->get_discount_total();
			if ( WC()->cart->display_prices_including_tax() ) {
				$current -= (float) WC()->cart->get_discount_tax();
			}
		}
	}

	if ( null === $minimum || null === $current ) {
		return null;
	}

	$decimals = wc_get_price_decimals();
	$current  = max( 0.0, round( $current, $decimals ) );

	return array(
		'minimum'   => $minimum,
		'current'   => $current,
		'remaining' => max( 0.0, round( $minimum - $current, $decimals ) ),
		'percent'   => min( 100.0, ( $current / $minimum ) * 100 ),
		'qualified' => $current >= $minimum,
	);
}

/** Render the Administrator-only configuration overview. */
function foxfire_operations_render_store_configuration_page(): void {
	if ( ! current_user_can( FOXFIRE_OPERATIONS_SENSITIVE_CAPABILITY ) ) {
		wp_die(
			esc_html__( 'Administrator permission is required for store-wide shipping and payment configuration.', 'foxfire-operations' ),
			esc_html__( 'Access denied', 'foxfire-operations' ),
			array( 'response' => 403 )
		);
	}

	$zones    = class_exists( 'WC_Shipping_Zones' ) ? WC_Shipping_Zones::get_zones() : array();
	$rest     = class_exists( 'WC_Shipping_Zones' ) ? WC_Shipping_Zones::get_zone( 0 ) : null;
	$gateways = function_exists( 'WC' ) && WC()->payment_gateways() ? WC()->payment_gateways()->payment_gateways() : array();
	?>
	<div class="wrap ff-ops-wrap ff-store-configuration">
		<h1><?php esc_html_e( 'Store Configuration', 'foxfire-operations' ); ?></h1>
		<p class="ff-ops-intro"><?php esc_html_e( 'Review the active WooCommerce shipping and payment configuration here. Change values only in the linked native WooCommerce screens; no theme template controls the authoritative rate, threshold, gateway, or instruction.', 'foxfire-operations' ); ?></p>

		<?php if ( in_array( wp_get_environment_type(), array( 'local', 'development' ), true ) ) : ?>
			<div class="notice notice-info inline"><p><strong><?php esc_html_e( 'Local test configuration:', 'foxfire-operations' ); ?></strong> <?php esc_html_e( 'Every shipping rate, threshold, destination, gateway, and instruction shown on this environment is test data and is not approved for production use.', 'foxfire-operations' ); ?></p></div>
		<?php endif; ?>

		<div class="notice notice-warning inline"><p><strong><?php esc_html_e( 'Client confirmation required:', 'foxfire-operations' ); ?></strong> <?php esc_html_e( 'Do not enable production shipping or payment methods until regions, rates, account ownership, refund handling, and customer instructions are approved. Never enter passwords, API secrets, recovery codes, or private keys into customer instructions.', 'foxfire-operations' ); ?></p></div>

		<h2><?php esc_html_e( 'Shipping zones and methods', 'foxfire-operations' ); ?></h2>
		<p><a class="button button-primary" href="<?php echo esc_url( admin_url( 'admin.php?page=wc-settings&tab=shipping' ) ); ?>"><?php esc_html_e( 'Manage shipping in WooCommerce', 'foxfire-operations' ); ?></a></p>
		<table class="widefat striped ff-ops-table">
			<thead><tr><th><?php esc_html_e( 'Zone', 'foxfire-operations' ); ?></th><th><?php esc_html_e( 'Locations', 'foxfire-operations' ); ?></th><th><?php esc_html_e( 'Saved methods', 'foxfire-operations' ); ?></th></tr></thead>
			<tbody>
				<?php foreach ( array_merge( array_values( $zones ), $rest instanceof WC_Shipping_Zone ? array( array( 'zone_id' => 0, 'zone_name' => __( 'Locations not covered by your other zones', 'foxfire-operations' ), 'zone_locations' => array(), 'shipping_methods' => $rest->get_shipping_methods( false ) ) ) : array() ) as $zone_data ) : ?>
					<?php
					$locations = array();
					foreach ( (array) ( $zone_data['zone_locations'] ?? array() ) as $location ) {
						if ( is_object( $location ) && isset( $location->code ) ) {
							$locations[] = (string) $location->code;
						}
					}
					$method_summaries = array();
					foreach ( (array) ( $zone_data['shipping_methods'] ?? array() ) as $method ) {
						if ( ! is_object( $method ) ) {
							continue;
						}
						$state = 'yes' === $method->enabled ? __( 'enabled', 'foxfire-operations' ) : __( 'disabled', 'foxfire-operations' );
						$detail = '';
						if ( 'flat_rate' === $method->id ) {
							$detail = (string) $method->get_option( 'cost', '' );
						} elseif ( 'free_shipping' === $method->id && 'min_amount' === $method->get_option( 'requires' ) ) {
							$detail = sprintf( /* translators: %s: saved minimum order amount. */ __( 'minimum %s', 'foxfire-operations' ), (string) $method->get_option( 'min_amount', '' ) );
						}
						$method_summaries[] = trim( wp_strip_all_tags( $method->get_title() . ' — ' . $state . ( '' !== $detail ? ' (' . $detail . ')' : '' ) ) );
					}
					?>
					<tr>
						<th scope="row"><?php echo esc_html( (string) ( $zone_data['zone_name'] ?? __( 'Shipping zone', 'foxfire-operations' ) ) ); ?></th>
						<td><?php echo esc_html( ! empty( $locations ) ? implode( ', ', $locations ) : __( 'Fallback zone', 'foxfire-operations' ) ); ?></td>
						<td><?php echo esc_html( ! empty( $method_summaries ) ? implode( '; ', $method_summaries ) : __( 'No methods configured', 'foxfire-operations' ) ); ?></td>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>

		<h2><?php esc_html_e( 'Payment methods', 'foxfire-operations' ); ?></h2>
		<p><a class="button button-primary" href="<?php echo esc_url( admin_url( 'admin.php?page=wc-settings&tab=checkout' ) ); ?>"><?php esc_html_e( 'Manage payments in WooCommerce', 'foxfire-operations' ); ?></a></p>
		<table class="widefat striped ff-ops-table">
			<thead><tr><th><?php esc_html_e( 'Method', 'foxfire-operations' ); ?></th><th><?php esc_html_e( 'Customer title', 'foxfire-operations' ); ?></th><th><?php esc_html_e( 'Status', 'foxfire-operations' ); ?></th><th><?php esc_html_e( 'Configuration', 'foxfire-operations' ); ?></th></tr></thead>
			<tbody>
				<?php foreach ( $gateways as $gateway ) : ?>
					<?php
					$is_manual      = str_starts_with( $gateway->id, 'foxfire_manual_' );
					$instructions   = $is_manual ? trim( (string) $gateway->get_option( 'instructions', '' ) ) : '';
					$customer_title = trim( wp_strip_all_tags( (string) $gateway->get_title() ) );
					$is_enabled     = 'yes' === $gateway->enabled;
					$is_test_title  = 1 === preg_match( '/\btest\b/i', $customer_title );
					$needs_review   = $is_manual && $is_enabled && '' === $instructions;

					if ( $needs_review ) {
						$status_class = 'fail';
						$status_label = __( 'Incomplete', 'foxfire-operations' );
					} elseif ( $is_enabled && $is_test_title && 'production' === wp_get_environment_type() ) {
						$status_class = 'fail';
						$status_label = __( 'Action required: test label', 'foxfire-operations' );
					} elseif ( $is_enabled && $is_test_title ) {
						$status_class = 'neutral';
						$status_label = __( 'Enabled local test', 'foxfire-operations' );
					} else {
						$status_class = $is_enabled ? 'pass' : 'neutral';
						$status_label = $is_enabled ? __( 'Enabled', 'foxfire-operations' ) : __( 'Disabled', 'foxfire-operations' );
					}
					?>
					<tr>
						<th scope="row"><?php echo esc_html( wp_strip_all_tags( $gateway->get_method_title() ) ); ?></th>
						<td><?php echo esc_html( '' !== $customer_title ? $customer_title : __( 'Not set', 'foxfire-operations' ) ); ?></td>
						<td><span class="ff-ops-status ff-ops-status--<?php echo esc_attr( $status_class ); ?>"><?php echo esc_html( $status_label ); ?></span></td>
						<td><a class="button" href="<?php echo esc_url( admin_url( 'admin.php?page=wc-settings&tab=checkout&section=' . sanitize_key( $gateway->id ) ) ); ?>"><?php esc_html_e( 'Configure', 'foxfire-operations' ); ?></a></td>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
	</div>
	<?php
}
