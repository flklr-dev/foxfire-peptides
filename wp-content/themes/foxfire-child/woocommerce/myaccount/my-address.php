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
			'billing'  => __( 'Billing address', 'foxfire-child' ),
			'shipping' => __( 'Shipping address', 'foxfire-child' ),
		),
		$customer_id
	);
} else {
	$get_addresses = apply_filters(
		'woocommerce_my_account_get_addresses',
		array(
			'billing' => __( 'Billing address', 'foxfire-child' ),
		),
		$customer_id
	);
}
?>

<div class="ff-account-addresses-section">

	<div class="ff-section-title-wrap">
		<h1 class="ff-section-title"><?php esc_html_e( 'Saved Addresses', 'foxfire-child' ); ?></h1>
		<p class="ff-section-subtext"><?php esc_html_e( 'The following addresses are used on the checkout page by default for rapid order processing.', 'foxfire-child' ); ?></p>
	</div>

	<div class="ff-addresses-cards-grid">
		<?php foreach ( $get_addresses as $name => $address_title ) : ?>
			<?php
			$address = wc_get_account_formatted_address( $name );
			$edit_url = wc_get_endpoint_url( 'edit-address', $name );
			?>
			<div class="ff-address-card">
				<div class="ff-address-card__header">
					<div class="ff-address-card__icon" aria-hidden="true">
						<?php if ( 'shipping' === $name ) : ?>
							<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m7.5 4.27 9 5.15"/><path d="M21 8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16Z"/><path d="m3.3 7 8.7 5 8.7-5"/><path d="M12 22V12"/></svg>
						<?php else : ?>
							<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="20" height="14" x="2" y="5" rx="2"/><line x1="2" x2="22" y1="10" y2="10"/></svg>
						<?php endif; ?>
					</div>
					<h2 class="ff-address-card__title"><?php echo esc_html( $address_title ); ?></h2>
				</div>

				<div class="ff-address-card__body">
					<address class="ff-address-text">
						<?php
						echo $address ? wp_kses_post( $address ) : esc_html__( 'You have not set up this type of address yet.', 'foxfire-child' );
						?>
					</address>
				</div>

				<div class="ff-address-card__footer">
					<a href="<?php echo esc_url( $edit_url ); ?>" class="ff-btn ff-btn--outline ff-btn--sm">
						<?php echo $address ? esc_html__( 'Edit Address', 'foxfire-child' ) : esc_html__( 'Add Address', 'foxfire-child' ); ?> &rarr;
					</a>
				</div>
			</div>
		<?php endforeach; ?>
	</div>

</div>
