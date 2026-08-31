<?php
/**
 * Lost password form — Chunk 1L.
 *
 * @package Foxfire_Child
 */

defined( 'ABSPATH' ) || exit;

do_action( 'woocommerce_before_lost_password_form' );
?>

<div class="ff-auth-page ff-auth-page--single">

	<div class="ff-auth-card">

		<div class="ff-auth-header">
			<div class="ff-auth-logo-badge" aria-hidden="true">
				<svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="18" height="11" x="3" y="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
			</div>
			<h1 class="ff-auth-title"><?php esc_html_e( 'Reset Password', 'foxfire-child' ); ?></h1>
			<p class="ff-auth-desc"><?php esc_html_e( 'Lost your password? Please enter your username or email address. You will receive a link to create a new password via email.', 'foxfire-child' ); ?></p>
		</div>

		<form method="post" class="ff-auth-form woocommerce-ResetPassword lost_reset_password">

			<div class="ff-form-group">
				<label for="user_login" class="ff-field-label">
					<?php esc_html_e( 'Username or Email', 'foxfire-child' ); ?> <span class="required" aria-hidden="true">*</span>
				</label>
				<input class="ff-form-input woocommerce-Input woocommerce-Input--text input-text" type="text" name="user_login" id="user_login" autocomplete="username" required />
			</div>

			<?php do_action( 'woocommerce_lostpassword_form' ); ?>

			<div class="ff-form-submit-wrap">
				<input type="hidden" name="wc_reset_password" value="true" />
				<button type="submit" class="ff-btn ff-btn--primary ff-btn--full woocommerce-Button button" value="<?php esc_attr_e( 'Reset password', 'foxfire-child' ); ?>">
					<?php esc_html_e( 'Send Reset Link', 'foxfire-child' ); ?> &rarr;
				</button>
			</div>

			<?php wp_nonce_field( 'lost_password', 'woocommerce-lost-password-nonce' ); ?>

		</form>

		<div class="ff-auth-card-footer">
			<a href="<?php echo esc_url( wc_get_page_permalink( 'myaccount' ) ); ?>" class="ff-back-link">
				&larr; <?php esc_html_e( 'Back to Sign In', 'foxfire-child' ); ?>
			</a>
		</div>

	</div>

</div>

<?php do_action( 'woocommerce_after_lost_password_form' ); ?>
