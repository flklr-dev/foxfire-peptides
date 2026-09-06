<?php
/**
 * Edit account form — Chunk 1L.
 *
 * @package Foxfire_Child
 */

defined( 'ABSPATH' ) || exit;

do_action( 'woocommerce_before_edit_account_form' );
?>

<div class="ff-account-details-section">

	<div class="ff-section-title-wrap">
		<h1 class="ff-section-title"><?php esc_html_e( 'Account Details', 'foxfire-child' ); ?></h1>
		<p class="ff-section-subtext"><?php esc_html_e( 'Manage your personal profile and account password credentials.', 'foxfire-child' ); ?></p>
	</div>

	<form class="ff-account-details-form woocommerce-EditAccountForm edit-account" action="" method="post" <?php do_action( 'woocommerce_edit_account_form_tag' ); ?> >

		<?php do_action( 'woocommerce_edit_account_form_start' ); ?>

		<!-- Personal Details Section -->
		<section class="ff-form-section">
			<h2 class="ff-form-section-title"><?php esc_html_e( 'Personal Information', 'foxfire-child' ); ?></h2>

			<div class="ff-form-row ff-form-row--two-col">
				<div class="ff-form-group">
					<label for="account_first_name" class="ff-field-label">
						<?php esc_html_e( 'First Name', 'foxfire-child' ); ?> <span class="required" aria-hidden="true">*</span>
					</label>
					<input type="text" class="ff-form-input woocommerce-Input woocommerce-Input--text input-text" name="account_first_name" id="account_first_name" autocomplete="given-name" value="<?php echo esc_attr( $user->first_name ); ?>" required />
				</div>

				<div class="ff-form-group">
					<label for="account_last_name" class="ff-field-label">
						<?php esc_html_e( 'Last Name', 'foxfire-child' ); ?> <span class="required" aria-hidden="true">*</span>
					</label>
					<input type="text" class="ff-form-input woocommerce-Input woocommerce-Input--text input-text" name="account_last_name" id="account_last_name" autocomplete="family-name" value="<?php echo esc_attr( $user->last_name ); ?>" required />
				</div>
			</div>

			<div class="ff-form-group">
				<label for="account_display_name" class="ff-field-label">
					<?php esc_html_e( 'Display Name', 'foxfire-child' ); ?> <span class="required" aria-hidden="true">*</span>
				</label>
				<input type="text" class="ff-form-input woocommerce-Input woocommerce-Input--text input-text" name="account_display_name" id="account_display_name" value="<?php echo esc_attr( $user->display_name ); ?>" required />
				<span class="ff-field-hint"><?php esc_html_e( 'This is how your name will be displayed in your account section and reviews.', 'foxfire-child' ); ?></span>
			</div>

			<div class="ff-form-group">
				<label for="account_email" class="ff-field-label">
					<?php esc_html_e( 'Email Address', 'foxfire-child' ); ?> <span class="required" aria-hidden="true">*</span>
				</label>
				<input type="email" class="ff-form-input woocommerce-Input woocommerce-Input--email input-text" name="account_email" id="account_email" autocomplete="email" value="<?php echo esc_attr( $user->user_email ); ?>" required />
			</div>
		</section>

		<!-- Password Change Section -->
		<section class="ff-form-section">
			<h2 class="ff-form-section-title"><?php esc_html_e( 'Password Change', 'foxfire-child' ); ?></h2>
			<p class="ff-form-section-subtext"><?php esc_html_e( 'Only fill out these fields if you want to change your account password.', 'foxfire-child' ); ?></p>

			<div class="ff-form-group">
				<label for="password_current" class="ff-field-label">
					<?php esc_html_e( 'Current Password', 'foxfire-child' ); ?>
				</label>
				<div class="ff-password-input-wrap">
					<input type="password" class="ff-form-input woocommerce-Input woocommerce-Input--password input-text" name="password_current" id="password_current" autocomplete="current-password" />
					<button type="button" class="ff-pwd-toggle" aria-label="<?php esc_attr_e( 'Toggle password visibility', 'foxfire-child' ); ?>">
						<svg class="ff-pwd-eye-open" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/></svg>
						<svg class="ff-pwd-eye-closed" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m15 18-.722-3.25"/><path d="M2 8a10.645 10.645 0 0 0 20 0"/><path d="m20 15-1.726-2.05"/><path d="m4 15 1.726-2.05"/><path d="m9 18 .722-3.25"/></svg>
					</button>
				</div>
			</div>

			<div class="ff-form-group">
				<label for="password_1" class="ff-field-label">
					<?php esc_html_e( 'New Password', 'foxfire-child' ); ?>
				</label>
				<div class="ff-password-input-wrap">
					<input type="password" class="ff-form-input woocommerce-Input woocommerce-Input--password input-text" name="password_1" id="password_1" autocomplete="new-password" />
					<button type="button" class="ff-pwd-toggle" aria-label="<?php esc_attr_e( 'Toggle password visibility', 'foxfire-child' ); ?>">
						<svg class="ff-pwd-eye-open" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/></svg>
						<svg class="ff-pwd-eye-closed" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m15 18-.722-3.25"/><path d="M2 8a10.645 10.645 0 0 0 20 0"/><path d="m20 15-1.726-2.05"/><path d="m4 15 1.726-2.05"/><path d="m9 18 .722-3.25"/></svg>
					</button>
				</div>
			</div>

			<div class="ff-form-group">
				<label for="password_2" class="ff-field-label">
					<?php esc_html_e( 'Confirm New Password', 'foxfire-child' ); ?>
				</label>
				<div class="ff-password-input-wrap">
					<input type="password" class="ff-form-input woocommerce-Input woocommerce-Input--password input-text" name="password_2" id="password_2" autocomplete="new-password" />
					<button type="button" class="ff-pwd-toggle" aria-label="<?php esc_attr_e( 'Toggle password visibility', 'foxfire-child' ); ?>">
						<svg class="ff-pwd-eye-open" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/></svg>
						<svg class="ff-pwd-eye-closed" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m15 18-.722-3.25"/><path d="M2 8a10.645 10.645 0 0 0 20 0"/><path d="m20 15-1.726-2.05"/><path d="m4 15 1.726-2.05"/><path d="m9 18 .722-3.25"/></svg>
					</button>
				</div>
			</div>
		</section>

		<?php do_action( 'woocommerce_edit_account_form' ); ?>

		<div class="ff-form-submit-wrap">
			<?php wp_nonce_field( 'save_account_details', 'save-account-details-nonce' ); ?>
			<button type="submit" class="ff-btn ff-btn--primary woocommerce-Button button" name="save_account_details" value="<?php esc_attr_e( 'Save changes', 'foxfire-child' ); ?>">
				<?php esc_html_e( 'Save Changes', 'foxfire-child' ); ?>
			</button>
			<input type="hidden" name="action" value="save_account_details" />
		</div>

		<?php do_action( 'woocommerce_edit_account_form_end' ); ?>

	</form>

</div>

<?php do_action( 'woocommerce_after_edit_account_form' ); ?>
