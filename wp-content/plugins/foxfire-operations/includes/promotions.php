<?php
/**
 * Native WooCommerce coupon workflow and Foxfire account restriction.
 *
 * @package Foxfire_Operations
 */

defined( 'ABSPATH' ) || exit;

/** Whether this coupon requires a signed-in Foxfire customer. */
function foxfire_operations_coupon_requires_login( WC_Coupon $coupon ): bool {
	return $coupon->get_id() > 0 && 'yes' === get_post_meta( $coupon->get_id(), '_foxfire_requires_login', true );
}

/** Add the opt-in restriction to the native coupon editor. */
function foxfire_operations_coupon_login_option( int $coupon_id, WC_Coupon $coupon ): void {
	unset( $coupon_id );

	woocommerce_wp_checkbox(
		array(
			'id'          => '_foxfire_requires_login',
			'label'       => __( 'Requires Foxfire login', 'foxfire-operations' ),
			'description' => __( 'Only signed-in Foxfire customers can use this code. For one use per account, also set Usage limit per user to 1.', 'foxfire-operations' ),
			'desc_tip'    => false,
			'value'       => foxfire_operations_coupon_requires_login( $coupon ) ? 'yes' : 'no',
		)
	);
}
add_action( 'woocommerce_coupon_options', 'foxfire_operations_coupon_login_option', 10, 2 );

/** Save only the checkbox from WooCommerce's nonce- and capability-checked coupon editor. */
function foxfire_operations_save_coupon_login_option( int $coupon_id, WC_Coupon $coupon ): void {
	unset( $coupon );

	if ( ! current_user_can( 'edit_post', $coupon_id ) ) {
		return;
	}

	$requires_login = isset( $_POST['_foxfire_requires_login'] ) && 'yes' === wc_clean( wp_unslash( $_POST['_foxfire_requires_login'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Missing
	update_post_meta( $coupon_id, '_foxfire_requires_login', $requires_login ? 'yes' : 'no' );
}
add_action( 'woocommerce_coupon_options_save', 'foxfire_operations_save_coupon_login_option', 10, 2 );

/** Error with a direct link to the Foxfire account sign-in page. */
function foxfire_operations_coupon_login_message(): string {
	return sprintf(
		/* translators: %s: link to Foxfire account sign-in */
		__( 'This promo code requires a Foxfire account. %s to use it.', 'foxfire-operations' ),
		'<a href="' . esc_url( wc_get_page_permalink( 'myaccount' ) ) . '">' . esc_html__( 'Log in', 'foxfire-operations' ) . '</a>'
	);
}

/** WooCommerce calls this for cart, checkout, and direct coupon requests. */
function foxfire_operations_validate_coupon_login( bool $valid, WC_Coupon $coupon ): bool {
	return $valid && ( ! foxfire_operations_coupon_requires_login( $coupon ) || is_user_logged_in() );
}
add_filter( 'woocommerce_coupon_is_valid', 'foxfire_operations_validate_coupon_login', 10, 2 );

/** Replace WooCommerce's generic filtered-coupon error only for this restriction. */
function foxfire_operations_coupon_login_error( string $message, int $error_code, $coupon ): string {
	if ( WC_Coupon::E_WC_COUPON_INVALID_FILTERED === $error_code && $coupon instanceof WC_Coupon && foxfire_operations_coupon_requires_login( $coupon ) && ! is_user_logged_in() ) {
		return foxfire_operations_coupon_login_message();
	}

	return $message;
}
add_filter( 'woocommerce_coupon_error', 'foxfire_operations_coupon_login_error', 10, 3 );

/** Find an applied restricted code without changing the cart. */
function foxfire_operations_guest_applied_login_coupon(): bool {
	if ( is_user_logged_in() || ! WC()->cart ) {
		return false;
	}

	foreach ( WC()->cart->get_applied_coupons() as $code ) {
		if ( foxfire_operations_coupon_requires_login( new WC_Coupon( $code ) ) ) {
			return true;
		}
	}

	return false;
}

/** Block a stale signed-in cart if the customer logged out before submitting. */
function foxfire_operations_validate_checkout_coupon_login(): void {
	if ( foxfire_operations_guest_applied_login_coupon() ) {
		wc_add_notice( foxfire_operations_coupon_login_message(), 'error' );
	}
}
add_action( 'woocommerce_checkout_process', 'foxfire_operations_validate_checkout_coupon_login', 1 );

/** Final classic-checkout guard before WooCommerce creates a customer or order. */
function foxfire_operations_validate_checkout_coupon_login_final( $data, WP_Error $errors ): void {
	unset( $data );

	if ( foxfire_operations_guest_applied_login_coupon() ) {
		$errors->add( 'foxfire_coupon_login_required', foxfire_operations_coupon_login_message() );
	}
}
add_action( 'woocommerce_after_checkout_validation', 'foxfire_operations_validate_checkout_coupon_login_final', 2, 2 );

/**
 * Add a read-only promotion review panel to the native coupon editor.
 *
 * WooCommerce remains responsible for saving, permission checks, nonces, and
 * applying coupons. Foxfire only summarizes high-impact settings for staff.
 *
 * @return void
 */
function foxfire_operations_add_coupon_review_metabox(): void {
	if ( ! current_user_can( 'edit_shop_coupons' ) ) {
		return;
	}

	add_meta_box(
		'foxfire-promotion-review',
		__( 'Foxfire Promotion Review', 'foxfire-operations' ),
		'foxfire_operations_render_coupon_review_metabox',
		'shop_coupon',
		'side',
		'high'
	);
}
add_action( 'add_meta_boxes_shop_coupon', 'foxfire_operations_add_coupon_review_metabox' );

/**
 * Render a compact review of the saved native WooCommerce coupon settings.
 *
 * @param WP_Post $post Current coupon post.
 * @return void
 */
function foxfire_operations_render_coupon_review_metabox( WP_Post $post ): void {
	if ( ! current_user_can( 'edit_shop_coupons' ) ) {
		return;
	}

	if ( 'auto-draft' === $post->post_status || ! $post->ID ) {
		echo '<p>' . esc_html__( 'Before publishing, set the promotion scope, expiry, total usage limit, per-customer limit, and stacking rules in Coupon data.', 'foxfire-operations' ) . '</p>';
		echo '<p class="description">' . esc_html__( 'Save the draft to refresh this review.', 'foxfire-operations' ) . '</p>';
		return;
	}

	$coupon = new WC_Coupon( $post->ID );
	if ( ! $coupon->get_id() ) {
		return;
	}

	$discount_type  = $coupon->get_discount_type();
	$amount         = (float) $coupon->get_amount();
	$amount_label   = 'percent' === $discount_type
		? wc_format_decimal( $amount, 2 ) . '%'
		: wp_strip_all_tags( wc_price( $amount ) );
	$expiry         = $coupon->get_date_expires();
	$usage_limit    = (int) $coupon->get_usage_limit();
	$per_user       = (int) $coupon->get_usage_limit_per_user();
	$login_required = foxfire_operations_coupon_requires_login( $coupon );
	$restricted     = ! empty( $coupon->get_product_ids() )
		|| ! empty( $coupon->get_excluded_product_ids() )
		|| ! empty( $coupon->get_product_categories() )
		|| ! empty( $coupon->get_excluded_product_categories() )
		|| (float) $coupon->get_minimum_amount() > 0
		|| (float) $coupon->get_maximum_amount() > 0
		|| ! empty( $coupon->get_email_restrictions() );

	$review_items = array(
		array(
			'label'  => __( 'Discount', 'foxfire-operations' ),
			'value'  => $amount_label . ' — ' . wc_get_coupon_type( $discount_type ),
			'status' => $amount > 0 ? 'pass' : 'review',
		),
		array(
			'label'  => __( 'Scope', 'foxfire-operations' ),
			'value'  => $restricted ? __( 'Restricted', 'foxfire-operations' ) : __( 'Storewide — confirm intentionally', 'foxfire-operations' ),
			'status' => $restricted ? 'pass' : 'review',
		),
		array(
			'label'  => __( 'Expires', 'foxfire-operations' ),
			'value'  => $expiry ? wc_format_datetime( $expiry ) : __( 'Never — confirm intentionally', 'foxfire-operations' ),
			'status' => $expiry ? 'pass' : 'review',
		),
		array(
			'label'  => __( 'Total limit', 'foxfire-operations' ),
			'value'  => $usage_limit > 0 ? (string) $usage_limit : __( 'Unlimited — confirm intentionally', 'foxfire-operations' ),
			'status' => $usage_limit > 0 ? 'pass' : 'review',
		),
		array(
			'label'  => __( 'Per customer', 'foxfire-operations' ),
			'value'  => $per_user > 0 ? (string) $per_user : __( 'Unlimited — confirm intentionally', 'foxfire-operations' ),
			'status' => $per_user > 0 ? 'pass' : 'review',
		),
		array(
			'label'  => __( 'Foxfire login', 'foxfire-operations' ),
			'value'  => $login_required ? __( 'Required', 'foxfire-operations' ) : __( 'Not required', 'foxfire-operations' ),
			'status' => 'pass',
		),
		array(
			'label'  => __( 'Sale stacking', 'foxfire-operations' ),
			'value'  => $coupon->get_exclude_sale_items() ? __( 'Sale items excluded', 'foxfire-operations' ) : __( 'Can combine with sale and quantity pricing', 'foxfire-operations' ),
			'status' => $coupon->get_exclude_sale_items() ? 'pass' : 'review',
		),
		array(
			'label'  => __( 'Free shipping', 'foxfire-operations' ),
			'value'  => $coupon->get_free_shipping() ? __( 'Enabled — verify authorization', 'foxfire-operations' ) : __( 'Not enabled', 'foxfire-operations' ),
			'status' => $coupon->get_free_shipping() ? 'review' : 'pass',
		),
	);
	?>
	<dl class="foxfire-promotion-review">
		<?php foreach ( $review_items as $item ) : ?>
			<div class="foxfire-promotion-review__item foxfire-promotion-review__item--<?php echo esc_attr( $item['status'] ); ?>">
				<dt><?php echo esc_html( $item['label'] ); ?></dt>
				<dd><?php echo esc_html( $item['value'] ); ?></dd>
			</div>
		<?php endforeach; ?>
	</dl>
	<p class="description"><?php esc_html_e( 'Review labels are operational warnings, not errors. Coupon data above is read from WooCommerce after the coupon is saved.', 'foxfire-operations' ); ?></p>
	<?php
}
