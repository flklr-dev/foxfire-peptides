<?php
/**
 * Checkout customizations & test gateway configuration — Chunk 1K.
 *
 * @package Foxfire_Child
 */

defined( 'ABSPATH' ) || exit;

/**
 * Clean up Storefront layout on checkout page.
 */
function foxfire_checkout_cleanup_layout(): void {
	if ( is_checkout() ) {
		remove_action( 'storefront_before_content', 'woocommerce_breadcrumb', 10 );
		remove_action( 'woocommerce_before_main_content', 'woocommerce_breadcrumb', 20 );
		remove_action( 'storefront_page', 'storefront_page_header', 10 );

		// Remove the blue "Have a coupon? Click here to enter your code" banner
		remove_action( 'woocommerce_before_checkout_form', 'woocommerce_checkout_coupon_form', 10 );

		// The order-received template renders a Foxfire-designed order summary
		// while still firing the normal WooCommerce extension hooks.
		if ( is_order_received_page() ) {
			remove_action( 'woocommerce_thankyou', 'woocommerce_order_details_table', 10 );
		}
	}
}
add_action( 'wp', 'foxfire_checkout_cleanup_layout', 10 );

/**
 * Enqueue checkout scripts and styles.
 */
function foxfire_checkout_enqueue_scripts(): void {
	if ( is_checkout() ) {
		$version = defined( 'FOXFIRE_CHILD_VERSION' ) ? FOXFIRE_CHILD_VERSION : '1.2.0';
		$js_path = FOXFIRE_CHILD_DIR . '/assets/js/checkout.js';

		wp_enqueue_script(
			'foxfire-checkout-js',
			FOXFIRE_CHILD_URI . '/assets/js/checkout.js',
			array( 'jquery', 'wc-checkout' ),
			file_exists( $js_path ) ? (string) filemtime( $js_path ) : $version,
			true
		);

		wp_localize_script(
			'foxfire-checkout-js',
			'foxfire_checkout_params',
			array(
				'ajax_url'           => admin_url( 'admin-ajax.php' ),
				'nonce'              => wp_create_nonce( 'foxfire_checkout_nonce' ),
				'i18n_processing'    => __( 'Processing Order...', 'foxfire-child' ),
				'i18n_offline'       => __( 'You appear to be offline. Reconnect before placing the order; no order was submitted.', 'foxfire-child' ),
				'i18n_network_error' => __( 'The connection was interrupted while placing your order. It may already have been received. Check your Orders page or confirmation email before trying again.', 'foxfire-child' ),
			)
		);
	}
}
add_action( 'wp_enqueue_scripts', 'foxfire_checkout_enqueue_scripts', 30 );

/**
 * AJAX handler to save address directly to user's WordPress account meta and WC customer session.
 */
function foxfire_ajax_save_checkout_address(): void {
	check_ajax_referer( 'foxfire_checkout_nonce', 'nonce' );

	$user_id = get_current_user_id();

	$fields = array(
		'billing_first_name' => sanitize_text_field( $_POST['billing_first_name'] ?? '' ),
		'billing_last_name'  => sanitize_text_field( $_POST['billing_last_name'] ?? '' ),
		'billing_email'      => sanitize_email( $_POST['billing_email'] ?? '' ),
		'billing_phone'      => sanitize_text_field( $_POST['billing_phone'] ?? '' ),
		'billing_country'    => sanitize_text_field( $_POST['billing_country'] ?? '' ),
		'billing_state'      => sanitize_text_field( $_POST['billing_state'] ?? '' ),
		'billing_city'       => sanitize_text_field( $_POST['billing_city'] ?? '' ),
		'billing_postcode'   => sanitize_text_field( $_POST['billing_postcode'] ?? '' ),
		'billing_address_1'  => sanitize_text_field( $_POST['billing_address_1'] ?? '' ),
		'billing_address_2'  => sanitize_text_field( $_POST['billing_address_2'] ?? '' ),
	);

	$set_default = ! empty( $_POST['set_default'] );

	// Update WooCommerce customer session object
	if ( function_exists( 'WC' ) && is_object( WC()->customer ) ) {
		foreach ( $fields as $key => $val ) {
			$setter = 'set_' . $key;
			if ( method_exists( WC()->customer, $setter ) ) {
				WC()->customer->$setter( $val );
			}
		}
		if ( $set_default ) {
			WC()->customer->set_shipping_first_name( $fields['billing_first_name'] );
			WC()->customer->set_shipping_last_name( $fields['billing_last_name'] );
			WC()->customer->set_shipping_country( $fields['billing_country'] );
			WC()->customer->set_shipping_state( $fields['billing_state'] );
			WC()->customer->set_shipping_city( $fields['billing_city'] );
			WC()->customer->set_shipping_postcode( $fields['billing_postcode'] );
			WC()->customer->set_shipping_address_1( $fields['billing_address_1'] );
			WC()->customer->set_shipping_address_2( $fields['billing_address_2'] );
		}
		WC()->customer->save();
	}

	// If user is logged in, PERMANENTLY save to account database (wp_usermeta)
	if ( $user_id > 0 ) {
		foreach ( $fields as $key => $val ) {
			update_user_meta( $user_id, $key, $val );
		}
		if ( $set_default ) {
			update_user_meta( $user_id, 'shipping_first_name', $fields['billing_first_name'] );
			update_user_meta( $user_id, 'shipping_last_name', $fields['billing_last_name'] );
			update_user_meta( $user_id, 'shipping_country', $fields['billing_country'] );
			update_user_meta( $user_id, 'shipping_state', $fields['billing_state'] );
			update_user_meta( $user_id, 'shipping_city', $fields['billing_city'] );
			update_user_meta( $user_id, 'shipping_postcode', $fields['billing_postcode'] );
			update_user_meta( $user_id, 'shipping_address_1', $fields['billing_address_1'] );
			update_user_meta( $user_id, 'shipping_address_2', $fields['billing_address_2'] );
		}
	}

	wp_send_json_success( array( 'message' => __( 'Address saved to account successfully.', 'foxfire-child' ) ) );
}
add_action( 'wp_ajax_foxfire_save_checkout_address', 'foxfire_ajax_save_checkout_address' );
add_action( 'wp_ajax_nopriv_foxfire_save_checkout_address', 'foxfire_ajax_save_checkout_address' );

/**
 * Determine whether the current browser may recover a recently attempted order.
 *
 * The WooCommerce session value is not enough on its own: logged-in customers
 * must own the order, while a guest session may recover only a guest order.
 * Recovery is deliberately time-limited so an old completed purchase does not
 * remain on every future empty-cart screen.
 */
function foxfire_customer_can_recover_checkout_order( WC_Order $order ): bool {
	$allowed_statuses = array( 'pending', 'failed', 'on-hold', 'processing', 'shipped', 'completed' );
	if ( ! in_array( $order->get_status(), $allowed_statuses, true ) ) {
		return false;
	}

	$current_user_id = get_current_user_id();
	$order_user_id   = (int) $order->get_customer_id();
	if ( $current_user_id > 0 ? $order_user_id !== $current_user_id : 0 !== $order_user_id ) {
		return false;
	}

	$created = $order->get_date_created();
	if ( ! $created ) {
		return false;
	}

	/** Filter the short-lived empty-cart recovery window, in seconds. */
	$window = max( HOUR_IN_SECONDS, absint( apply_filters( 'foxfire_checkout_recovery_window', DAY_IN_SECONDS, $order ) ) );
	return $created->getTimestamp() >= time() - $window;
}

/**
 * Remember the server-created order until its confirmation page is reached.
 *
 * WooCommerce already stores an awaiting-payment order, but clears that value
 * on its confirmation route. This separate marker covers a response that is
 * interrupted between successful order processing and that final page.
 */
function foxfire_remember_recent_checkout_order( int $order_id, array $posted_data, WC_Order $order ): void {
	unset( $posted_data );

	if ( $order_id <= 0 || $order->get_id() !== $order_id || ! function_exists( 'WC' ) || ! WC()->session ) {
		return;
	}

	WC()->session->set( 'foxfire_recent_checkout_order', $order_id );
}
add_action( 'woocommerce_checkout_order_processed', 'foxfire_remember_recent_checkout_order', 10, 3 );

/** Clear the recovery marker after the verified confirmation page renders. */
function foxfire_clear_recent_checkout_order( int $order_id ): void {
	if ( $order_id <= 0 || ! function_exists( 'WC' ) || ! WC()->session ) {
		return;
	}

	if ( $order_id === absint( WC()->session->get( 'foxfire_recent_checkout_order' ) ) ) {
		WC()->session->__unset( 'foxfire_recent_checkout_order' );
	}
}
add_action( 'woocommerce_thankyou', 'foxfire_clear_recent_checkout_order', 1 );

/**
 * Return the order stored by WooCommerce before its payment handoff.
 *
 * @param int $order_id Optional explicit ID used by isolated acceptance tests.
 */
function foxfire_get_recoverable_checkout_order( int $order_id = 0 ): ?WC_Order {
	if ( $order_id <= 0 ) {
		if ( ! function_exists( 'WC' ) || ! WC()->session ) {
			return null;
		}
		$order_id = absint( WC()->session->get( 'order_awaiting_payment' ) );
		if ( $order_id <= 0 ) {
			$order_id = absint( WC()->session->get( 'foxfire_recent_checkout_order' ) );
		}
	}

	$order = $order_id > 0 ? wc_get_order( $order_id ) : false;
	return $order instanceof WC_Order && foxfire_customer_can_recover_checkout_order( $order ) ? $order : null;
}

/** Render a safe resume/status action on the empty-cart screen. */
function foxfire_render_checkout_recovery(): void {
	$order = foxfire_get_recoverable_checkout_order();
	if ( ! $order ) {
		return;
	}

	$needs_payment = $order->needs_payment();
	$url           = $needs_payment ? $order->get_checkout_payment_url() : $order->get_checkout_order_received_url();
	?>
	<section class="ff-checkout-recovery" aria-labelledby="ff-checkout-recovery-title">
		<h2 id="ff-checkout-recovery-title" class="ff-checkout-recovery__title">
			<?php echo esc_html( $needs_payment ? __( 'Continue your pending order', 'foxfire-child' ) : __( 'Your order may already be complete', 'foxfire-child' ) ); ?>
		</h2>
		<p class="ff-checkout-recovery__copy">
			<?php
			echo esc_html(
				$needs_payment
					? __( 'We found a recent order waiting for payment. Continue that order instead of creating a duplicate.', 'foxfire-child' )
					: __( 'We found the order created by your recent checkout. Review its confirmation and status before placing another order.', 'foxfire-child' )
			);
			?>
		</p>
		<a class="ff-btn ff-btn--secondary ff-checkout-recovery__button" href="<?php echo esc_url( $url ); ?>">
			<?php echo esc_html( $needs_payment ? __( 'Continue payment', 'foxfire-child' ) : __( 'View order confirmation', 'foxfire-child' ) ); ?>
		</a>
	</section>
	<?php
}

/**
 * Customize WooCommerce checkout form fields for a modern DTC structure.
 *
 * @param array<string, array<string, mixed>> $fields Default checkout fields.
 * @return array<string, array<string, mixed>>
 */
function foxfire_customize_checkout_fields( array $fields ): array {
	// 1. First Name (Row 1 - Left)
	if ( isset( $fields['billing']['billing_first_name'] ) ) {
		$fields['billing']['billing_first_name']['priority']    = 10;
		$fields['billing']['billing_first_name']['label']       = __( 'First name', 'foxfire-child' );
		$fields['billing']['billing_first_name']['placeholder'] = __( 'First name', 'foxfire-child' );
		$fields['billing']['billing_first_name']['class']       = array( 'form-row-first', 'ff-field-wrap' );
		$fields['billing']['billing_first_name']['required']    = true;
	}

	// 2. Last Name (Row 1 - Right)
	if ( isset( $fields['billing']['billing_last_name'] ) ) {
		$fields['billing']['billing_last_name']['priority']    = 20;
		$fields['billing']['billing_last_name']['label']       = __( 'Last name', 'foxfire-child' );
		$fields['billing']['billing_last_name']['placeholder'] = __( 'Last name', 'foxfire-child' );
		$fields['billing']['billing_last_name']['class']       = array( 'form-row-last', 'ff-field-wrap' );
		$fields['billing']['billing_last_name']['required']    = true;
	}

	// 3. Email (Row 2 - Left)
	if ( isset( $fields['billing']['billing_email'] ) ) {
		$fields['billing']['billing_email']['priority']    = 30;
		$fields['billing']['billing_email']['label']       = __( 'Email address', 'foxfire-child' );
		$fields['billing']['billing_email']['placeholder'] = __( 'your.email@example.com', 'foxfire-child' );
		$fields['billing']['billing_email']['class']       = array( 'form-row-first', 'ff-field-wrap' );
		$fields['billing']['billing_email']['required']    = true;
	}

	// 4. Phone (Row 2 - Right) — REQUIRED
	if ( isset( $fields['billing']['billing_phone'] ) ) {
		$fields['billing']['billing_phone']['priority']    = 40;
		$fields['billing']['billing_phone']['label']       = __( 'Phone number', 'foxfire-child' );
		$fields['billing']['billing_phone']['placeholder'] = __( 'Phone number (for delivery updates)', 'foxfire-child' );
		$fields['billing']['billing_phone']['class']       = array( 'form-row-last', 'ff-field-wrap' );
		$fields['billing']['billing_phone']['required']    = true;
	}

	// 5. Country (Row 3 - Full Width)
	if ( isset( $fields['billing']['billing_country'] ) ) {
		$fields['billing']['billing_country']['priority'] = 50;
		$fields['billing']['billing_country']['label']    = __( 'Country', 'foxfire-child' );
		$fields['billing']['billing_country']['class']    = array( 'form-row-wide', 'ff-field-wrap', 'address-field' );
		$fields['billing']['billing_country']['required'] = true;
	}

	// 6. Street Address Line 1 (Row 4 - Full Width)
	if ( isset( $fields['billing']['billing_address_1'] ) ) {
		$fields['billing']['billing_address_1']['priority']    = 60;
		$fields['billing']['billing_address_1']['label']       = __( 'Street address', 'foxfire-child' );
		$fields['billing']['billing_address_1']['placeholder'] = __( 'House number and street name', 'foxfire-child' );
		$fields['billing']['billing_address_1']['class']       = array( 'form-row-wide', 'ff-field-wrap', 'address-field' );
		$fields['billing']['billing_address_1']['required']    = true;
	}

	// 7. Street Address Line 2 (Row 5 - Full Width, Optional)
	if ( isset( $fields['billing']['billing_address_2'] ) ) {
		$fields['billing']['billing_address_2']['priority']    = 70;
		$fields['billing']['billing_address_2']['label']       = __( 'Apartment, suite, unit (optional)', 'foxfire-child' );
		$fields['billing']['billing_address_2']['label_class'] = array( 'screen-reader-text' );
		$fields['billing']['billing_address_2']['placeholder'] = __( 'Apartment, suite, unit, building (optional)', 'foxfire-child' );
		$fields['billing']['billing_address_2']['class']       = array( 'form-row-wide', 'ff-field-wrap', 'address-field' );
		$fields['billing']['billing_address_2']['required']    = false;
	}

	// 8. Town / City (Row 6 - Left Column)
	if ( isset( $fields['billing']['billing_city'] ) ) {
		$fields['billing']['billing_city']['priority']    = 80;
		$fields['billing']['billing_city']['label']       = __( 'Town / City', 'foxfire-child' );
		$fields['billing']['billing_city']['placeholder'] = __( 'Town / City', 'foxfire-child' );
		$fields['billing']['billing_city']['class']       = array( 'form-row-first', 'ff-field-wrap', 'address-field' );
		$fields['billing']['billing_city']['required']    = true;
	}

	// 9. ZIP / Postal code (Row 6 - Right Column, strictly paired with Town / City)
	if ( isset( $fields['billing']['billing_postcode'] ) ) {
		$fields['billing']['billing_postcode']['priority']    = 85;
		$fields['billing']['billing_postcode']['label']       = __( 'ZIP / Postal code', 'foxfire-child' );
		$fields['billing']['billing_postcode']['placeholder'] = __( 'ZIP / Postal code', 'foxfire-child' );
		$fields['billing']['billing_postcode']['class']       = array( 'form-row-last', 'ff-field-wrap', 'address-field' );
		$fields['billing']['billing_postcode']['required']    = true;
	}

	// 10. State / Region (Row 7 - Full Width)
	if ( isset( $fields['billing']['billing_state'] ) ) {
		$fields['billing']['billing_state']['priority']    = 90;
		$fields['billing']['billing_state']['label']       = __( 'State / Region', 'foxfire-child' );
		$fields['billing']['billing_state']['placeholder'] = __( 'Select State / Region', 'foxfire-child' );
		$fields['billing']['billing_state']['class']       = array( 'form-row-wide', 'ff-field-wrap', 'address-field' );
		$fields['billing']['billing_state']['required']    = true;
	}

	// Disable order notes completely
	unset( $fields['order']['order_comments'] );

	// Sort fields by priority so associative array order matches the priority hierarchy
	uasort( $fields['billing'], function ( $a, $b ) {
		$a_priority = $a['priority'] ?? 100;
		$b_priority = $b['priority'] ?? 100;
		return $a_priority <=> $b_priority;
	} );

	return $fields;
}
add_filter( 'woocommerce_checkout_fields', 'foxfire_customize_checkout_fields', 999 );

/**
 * Filter billing fields directly as well.
 */
function foxfire_customize_billing_fields( array $fields ): array {
	if ( isset( $fields['billing_first_name'] ) ) {
		$fields['billing_first_name']['priority'] = 10;
		$fields['billing_first_name']['class']    = array( 'form-row-first', 'ff-field-wrap' );
		$fields['billing_first_name']['required'] = true;
	}
	if ( isset( $fields['billing_last_name'] ) ) {
		$fields['billing_last_name']['priority'] = 20;
		$fields['billing_last_name']['class']    = array( 'form-row-last', 'ff-field-wrap' );
		$fields['billing_last_name']['required'] = true;
	}
	if ( isset( $fields['billing_email'] ) ) {
		$fields['billing_email']['priority'] = 30;
		$fields['billing_email']['class']    = array( 'form-row-first', 'ff-field-wrap' );
		$fields['billing_email']['required'] = true;
	}
	if ( isset( $fields['billing_phone'] ) ) {
		$fields['billing_phone']['priority'] = 40;
		$fields['billing_phone']['label']    = __( 'Phone number', 'foxfire-child' );
		$fields['billing_phone']['class']    = array( 'form-row-last', 'ff-field-wrap' );
		$fields['billing_phone']['required'] = true;
	}
	if ( isset( $fields['billing_country'] ) ) {
		$fields['billing_country']['priority'] = 50;
		$fields['billing_country']['label']    = __( 'Country', 'foxfire-child' );
		$fields['billing_country']['class']    = array( 'form-row-wide', 'ff-field-wrap', 'address-field' );
		$fields['billing_country']['required'] = true;
	}
	if ( isset( $fields['billing_address_1'] ) ) {
		$fields['billing_address_1']['priority'] = 60;
		$fields['billing_address_1']['class']    = array( 'form-row-wide', 'ff-field-wrap', 'address-field' );
		$fields['billing_address_1']['required'] = true;
	}
	if ( isset( $fields['billing_address_2'] ) ) {
		$fields['billing_address_2']['priority'] = 70;
		$fields['billing_address_2']['class']    = array( 'form-row-wide', 'ff-field-wrap', 'address-field' );
		$fields['billing_address_2']['required'] = false;
	}
	if ( isset( $fields['billing_city'] ) ) {
		$fields['billing_city']['priority'] = 80;
		$fields['billing_city']['class']    = array( 'form-row-first', 'ff-field-wrap', 'address-field' );
		$fields['billing_city']['required'] = true;
	}
	if ( isset( $fields['billing_postcode'] ) ) {
		$fields['billing_postcode']['priority'] = 85;
		$fields['billing_postcode']['class']    = array( 'form-row-last', 'ff-field-wrap', 'address-field' );
		$fields['billing_postcode']['required'] = true;
	}
	if ( isset( $fields['billing_state'] ) ) {
		$fields['billing_state']['priority'] = 90;
		$fields['billing_state']['label']    = __( 'State / Region', 'foxfire-child' );
		$fields['billing_state']['class']    = array( 'form-row-wide', 'ff-field-wrap', 'address-field' );
		$fields['billing_state']['required'] = true;
	}

	uasort( $fields, function ( $a, $b ) {
		$a_priority = $a['priority'] ?? 100;
		$b_priority = $b['priority'] ?? 100;
		return $a_priority <=> $b_priority;
	} );

	return $fields;
}
add_filter( 'woocommerce_billing_fields', 'foxfire_customize_billing_fields', 999 );

/**
 * Lock in address locale priorities and clean labels so WooCommerce address-i18n.js
 * never moves Phone to the bottom, never separates Town / City and ZIP, and uses
 * "Country" and "State / Region" consistently.
 */
function foxfire_override_address_locale_priorities( array $locale ): array {
	if ( isset( $locale['default'] ) ) {
		$locale['default']['phone']['priority']     = 40;
		$locale['default']['phone']['label']        = __( 'Phone number', 'foxfire-child' );
		$locale['default']['country']['priority']   = 50;
		$locale['default']['country']['label']      = __( 'Country', 'foxfire-child' );
		$locale['default']['address_1']['priority'] = 60;
		$locale['default']['address_2']['priority'] = 70;
		$locale['default']['city']['priority']      = 80;
		$locale['default']['postcode']['priority']  = 85;
		$locale['default']['state']['priority']     = 90;
		$locale['default']['state']['label']        = __( 'State / Region', 'foxfire-child' );
	}
	foreach ( $locale as $code => &$country_fields ) {
		$country_fields['country']['priority'] = 50;
		$country_fields['country']['label']    = __( 'Country', 'foxfire-child' );

		if ( isset( $country_fields['phone'] ) ) {
			$country_fields['phone']['priority'] = 40;
			$country_fields['phone']['label']    = __( 'Phone number', 'foxfire-child' );
		}
		if ( isset( $country_fields['address_1'] ) ) {
			$country_fields['address_1']['priority'] = 60;
		}
		if ( isset( $country_fields['address_2'] ) ) {
			$country_fields['address_2']['priority'] = 70;
		}
		if ( isset( $country_fields['city'] ) ) {
			$country_fields['city']['priority'] = 80;
		}
		if ( isset( $country_fields['postcode'] ) ) {
			$country_fields['postcode']['priority'] = 85;
		}
		if ( isset( $country_fields['state'] ) ) {
			$country_fields['state']['priority'] = 90;
			$country_fields['state']['label']    = __( 'State / Region', 'foxfire-child' );
		}
	}
	unset( $country_fields );
	return $locale;
}
add_filter( 'woocommerce_get_country_locale', 'foxfire_override_address_locale_priorities', 999 );

add_filter( 'woocommerce_get_country_locale_default', function ( array $fields ): array {
	if ( isset( $fields['phone'] ) ) {
		$fields['phone']['priority'] = 40;
		$fields['phone']['label']    = __( 'Phone number', 'foxfire-child' );
	}
	if ( isset( $fields['country'] ) ) {
		$fields['country']['priority'] = 50;
		$fields['country']['label']    = __( 'Country', 'foxfire-child' );
	}
	if ( isset( $fields['address_1'] ) ) {
		$fields['address_1']['priority'] = 60;
	}
	if ( isset( $fields['address_2'] ) ) {
		$fields['address_2']['priority'] = 70;
	}
	if ( isset( $fields['city'] ) ) {
		$fields['city']['priority'] = 80;
	}
	if ( isset( $fields['postcode'] ) ) {
		$fields['postcode']['priority'] = 85;
	}
	if ( isset( $fields['state'] ) ) {
		$fields['state']['priority'] = 90;
		$fields['state']['label']    = __( 'State / Region', 'foxfire-child' );
	}
	return $fields;
}, 999 );

add_filter( 'woocommerce_default_address_fields', function ( array $fields ): array {
	if ( isset( $fields['phone'] ) ) {
		$fields['phone']['priority'] = 40;
		$fields['phone']['label']    = __( 'Phone number', 'foxfire-child' );
	}
	if ( isset( $fields['country'] ) ) {
		$fields['country']['priority'] = 50;
		$fields['country']['label']    = __( 'Country', 'foxfire-child' );
	}
	if ( isset( $fields['address_1'] ) ) {
		$fields['address_1']['priority'] = 60;
	}
	if ( isset( $fields['address_2'] ) ) {
		$fields['address_2']['priority'] = 70;
	}
	if ( isset( $fields['city'] ) ) {
		$fields['city']['priority'] = 80;
	}
	if ( isset( $fields['postcode'] ) ) {
		$fields['postcode']['priority'] = 85;
	}
	if ( isset( $fields['state'] ) ) {
		$fields['state']['priority'] = 90;
		$fields['state']['label']    = __( 'State / Region', 'foxfire-child' );
	}
	return $fields;
}, 999 );

// Clean up core WooCommerce gettext translations for Country and State labels
add_filter( 'gettext', function ( string $translation, string $text, string $domain ): string {
	if ( 'woocommerce' === $domain ) {
		if ( 'Country / Region' === $text || 'Country / Region' === $translation ) {
			return 'Country';
		}
		if ( 'State / County' === $text || 'State / County' === $translation ) {
			return 'State / Region';
		}
	}
	return $translation;
}, 999, 3 );

// Disable order notes field globally in WooCommerce
add_filter( 'woocommerce_enable_order_notes_field', '__return_false', 999 );

/**
 * Dequeue selectWoo & select2 on checkout to prevent dropdown trapping & z-index issues.
 * Native select elements provide 100% reliable dropdown experience on both mobile & desktop.
 */
function foxfire_disable_checkout_select2(): void {
	if ( is_checkout() || is_account_page() ) {
		wp_dequeue_script( 'selectWoo' );
		wp_dequeue_style( 'select2' );
	}
}
add_action( 'wp_enqueue_scripts', 'foxfire_disable_checkout_select2', 100 );

// Terms and conditions must be unchecked by default
add_filter( 'woocommerce_terms_is_checked_default', '__return_false', 999 );
