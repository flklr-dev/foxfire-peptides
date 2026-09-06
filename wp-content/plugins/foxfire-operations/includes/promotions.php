<?php
/**
 * Native WooCommerce coupon workflow guidance.
 *
 * @package Foxfire_Operations
 */

defined( 'ABSPATH' ) || exit;

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

	$discount_type = $coupon->get_discount_type();
	$amount        = (float) $coupon->get_amount();
	$amount_label  = 'percent' === $discount_type
		? wc_format_decimal( $amount, 2 ) . '%'
		: wp_strip_all_tags( wc_price( $amount ) );
	$expiry        = $coupon->get_date_expires();
	$usage_limit   = (int) $coupon->get_usage_limit();
	$per_user      = (int) $coupon->get_usage_limit_per_user();
	$restricted    = ! empty( $coupon->get_product_ids() )
		|| ! empty( $coupon->get_excluded_product_ids() )
		|| ! empty( $coupon->get_product_categories() )
		|| ! empty( $coupon->get_excluded_product_categories() )
		|| '' !== (string) $coupon->get_minimum_amount()
		|| '' !== (string) $coupon->get_maximum_amount()
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
