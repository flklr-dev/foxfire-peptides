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

	<div class="ff-account-edit-address-section">

		<div class="ff-section-title-wrap">
			<a href="<?php echo esc_url( wc_get_account_endpoint_url( 'edit-address' ) ); ?>" class="ff-back-link">
				&larr; <?php esc_html_e( 'Back to Saved Addresses', 'foxfire-child' ); ?>
			</a>
			<h1 class="ff-section-title"><?php echo esc_html( $page_title ); ?></h1>
			<p class="ff-section-subtext"><?php esc_html_e( 'Update your address details below.', 'foxfire-child' ); ?></p>
		</div>

		<form method="post" class="ff-edit-address-form">

			<div class="woocommerce-address-fields">
				<?php do_action( "woocommerce_before_edit_address_form_{$load_address}" ); ?>

				<div class="woocommerce-address-fields__field-wrapper ff-fields-grid">
					<?php
					foreach ( $address as $key => $field ) {
						woocommerce_form_field( $key, $field, wc_get_post_data_by_key( $key, $field['value'] ) );
					}
					?>
				</div>

				<?php do_action( "woocommerce_after_edit_address_form_{$load_address}" ); ?>

				<div class="ff-form-submit-wrap">
					<button type="submit" class="ff-btn ff-btn--primary" name="save_address" value="<?php esc_attr_e( 'Save address', 'foxfire-child' ); ?>">
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
