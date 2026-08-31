<?php
/**
 * Reset password form — Chunk 1L.
 *
 * @package Foxfire_Child
 */

defined( 'ABSPATH' ) || exit;

do_action( 'woocommerce_before_reset_password_form' );
?>

<div class="ff-auth-page ff-auth-page--single">

	<div class="ff-auth-card">

		<div class="ff-auth-header">
			<div class="ff-auth-logo-badge" aria-hidden="true">
				<svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="18" height="11" x="3" y="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
			</div>
			<h1 class="ff-auth-title"><?php esc_html_e( 'Enter New Password', 'foxfire-child' ); ?></h1>
			<p class="ff-auth-desc"><?php esc_html_e( 'Enter your new password below to regain access to your research account.', 'foxfire-child' ); ?></p>
		</div>

		<form method="post" class="ff-auth-form woocommerce-ResetPassword lost_reset_password">

			<div class="ff-form-group">
				<label for="password_1" class="ff-field-label">
					<?php esc_html_e( 'New Password', 'foxfire-child' ); ?> <span class="required" aria-hidden="true">*</span>
				</label>
				<div class="ff-password-input-wrap">
					<input type="password" class="ff-form-input woocommerce-Input woocommerce-Input--text input-text" name="password_1" id="password_1" autocomplete="new-password" required />
					<button type="button" class="ff-pwd-toggle" aria-label="<?php esc_attr_e( 'Toggle password visibility', 'foxfire-child' ); ?>">
						<svg class="ff-pwd-eye-open" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/></svg>
						<svg class="ff-pwd-eye-closed" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m15 18-.722-3.25"/><path d="M2 8a10.645 10.645 0 0 0 20 0"/><path d="m20 15-1.726-2.05"/><path d="m4 15 1.726-2.05"/><path d="m9 18 .722-3.25"/></svg>
					</button>
				</div>
			</div>

			<div class="ff-form-group">
				<label for="password_2" class="ff-field-label">
					<?php esc_html_e( 'Re-enter New Password', 'foxfire-child' ); ?> <span class="required" aria-hidden="true">*</span>
				</label>
				<div class="ff-password-input-wrap">
					<input type="password" class="ff-form-input woocommerce-Input woocommerce-Input--text input-text" name="password_2" id="password_2" autocomplete="new-password" required />
					<button type="button" class="ff-pwd-toggle" aria-label="<?php esc_attr_e( 'Toggle password visibility', 'foxfire-child' ); ?>">
						<svg class="ff-pwd-eye-open" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/></svg>
						<svg class="ff-pwd-eye-closed" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m15 18-.722-3.25"/><path d="M2 8a10.645 10.645 0 0 0 20 0"/><path d="m20 15-1.726-2.05"/><path d="m4 15 1.726-2.05"/><path d="m9 18 .722-3.25"/></svg>
					</button>
				</div>
			</div>

			<input type="hidden" name="reset_key" value="<?php echo esc_attr( $args['key'] ); ?>" />
			<input type="hidden" name="reset_login" value="<?php echo esc_attr( $args['login'] ); ?>" />

			<?php do_action( 'woocommerce_resetpassword_form' ); ?>

			<div class="ff-form-submit-wrap">
				<input type="hidden" name="wc_reset_password" value="true" />
				<button type="submit" class="ff-btn ff-btn--primary ff-btn--full woocommerce-Button button" value="<?php esc_attr_e( 'Save', 'foxfire-child' ); ?>">
					<?php esc_html_e( 'Update Password', 'foxfire-child' ); ?> &rarr;
				</button>
			</div>

			<?php wp_nonce_field( 'reset_password', 'woocommerce-reset-password-nonce' ); ?>

		</form>

	</div>

</div>

<?php do_action( 'woocommerce_after_reset_password_form' ); ?>
