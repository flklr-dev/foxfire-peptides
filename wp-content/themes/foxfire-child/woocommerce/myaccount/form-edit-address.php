<?php
/**
 * Edit address form — Chunk 1L.
 *
 * @package Foxfire_Child
 */

defined( 'ABSPATH' ) || exit;

$page_title = ( 'billing' === $load_address ) ? esc_html__( 'Billing address', 'foxfire-child' ) : esc_html__( 'Shipping address', 'foxfire-child' );

do_action( 'woocommerce_before_edit_account_address_form' );
?>

<?php if ( ! $load_address ) : ?>
	<?php wc_get_template( 'myaccount/my-address.php' ); ?>
<?php else : ?>

	<div class="ff-account-edit-address-card">

		<!-- Card Header (matching checkout delivery header) -->
		<div class="ff-edit-address-header">
			<div class="ff-edit-address-header__title-group">
				<div class="ff-edit-address-header__icon" aria-hidden="true">
					<svg class="ff-shopee-loc-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#e85a0c" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
						<path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path>
						<circle cx="12" cy="10" r="3"></circle>
					</svg>
				</div>
				<div>
					<h1 class="ff-section-title"><?php echo esc_html( $page_title ); ?></h1>
					<p class="ff-section-subtext"><?php esc_html_e( 'Update your address details for rapid order checkout and delivery.', 'foxfire-child' ); ?></p>
				</div>
			</div>

			<a href="<?php echo esc_url( wc_get_account_endpoint_url( 'edit-address' ) ); ?>" class="ff-edit-address-back-btn">
				<span><?php esc_html_e( 'Back', 'foxfire-child' ); ?></span>
			</a>
		</div>

		<!-- Card Body (Checkout Address Grid) -->
		<form method="post" class="ff-edit-address-form">

			<div class="woocommerce-address-fields">
				<?php do_action( "woocommerce_before_edit_address_form_{$load_address}" ); ?>

				<div class="woocommerce-address-fields__field-wrapper ff-address-fields-grid">
					<?php
					uasort( $address, function ( $a, $b ) {
						$a_priority = $a['priority'] ?? 100;
						$b_priority = $b['priority'] ?? 100;
						return $a_priority <=> $b_priority;
					} );

					foreach ( $address as $key => $field ) {
						woocommerce_form_field( $key, $field, wc_get_post_data_by_key( $key, $field['value'] ) );
					}
					?>
				</div>

				<?php do_action( "woocommerce_after_edit_address_form_{$load_address}" ); ?>

				<!-- Card Footer (Save Button matching checkout modal footer) -->
				<div class="ff-edit-address-footer">
					<button type="submit" class="ff-address-modal__save-btn ff-edit-address-submit-btn" name="save_address" value="<?php esc_attr_e( 'Save address', 'foxfire-child' ); ?>">
						<?php esc_html_e( 'Save Address', 'foxfire-child' ); ?>
					</button>
					<?php wp_nonce_field( 'woocommerce-edit_address', 'woocommerce-edit-address-nonce' ); ?>
					<input type="hidden" name="action" value="edit_address" />
				</div>
			</div>

		</form>

	</div>

<?php endif; ?>

<?php do_action( 'woocommerce_after_edit_account_address_form' ); ?>
