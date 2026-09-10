<?php
/**
 * Login & Registration form — Chunk 1L.
 *
 * @package Foxfire_Child
 */

defined( 'ABSPATH' ) || exit;

$enable_registration = 'yes' === get_option( 'woocommerce_enable_myaccount_registration', 'yes' );
?>

<div class="ff-auth-page">
	
	<div class="ff-auth-card">
		
		<!-- Auth Header -->
		<div class="ff-auth-header">
			<div class="ff-auth-logo-badge" aria-hidden="true">
				<svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
			</div>
			<h1 class="ff-auth-title"><?php esc_html_e( 'Customer Portal', 'foxfire-child' ); ?></h1>
			<p class="ff-auth-desc"><?php esc_html_e( 'Sign in to review orders, track shipments, and access available batch COA documents.', 'foxfire-child' ); ?></p>
		</div>

		<?php if ( $enable_registration ) : ?>
			<!-- Tab Switcher -->
			<div class="ff-auth-tabs" role="tablist">
				<button type="button" class="ff-auth-tab-btn is-active" data-tab="ff-tab-login" role="tab" aria-selected="true" aria-controls="ff-tab-login">
					<?php esc_html_e( 'Sign In', 'foxfire-child' ); ?>
				</button>
				<button type="button" class="ff-auth-tab-btn" data-tab="ff-tab-register" role="tab" aria-selected="false" aria-controls="ff-tab-register">
					<?php esc_html_e( 'Create Account', 'foxfire-child' ); ?>
				</button>
			</div>
		<?php endif; ?>

		<!-- Tab Panel 1: Sign In -->
		<div id="ff-tab-login" class="ff-auth-panel is-active" role="tabpanel">
			<form class="ff-auth-form woocommerce-form woocommerce-form-login login" method="post">

				<?php do_action( 'woocommerce_login_form_start' ); ?>

				<div class="ff-form-group">
					<label for="username" class="ff-field-label">
						<?php esc_html_e( 'Email Address or Username', 'foxfire-child' ); ?> <span class="required" aria-hidden="true">*</span>
					</label>
					<input type="text" class="ff-form-input woocommerce-Input woocommerce-Input--text input-text" name="username" id="username" autocomplete="username" value="<?php echo ( ! empty( $_POST['username'] ) ) ? esc_attr( wp_unslash( $_POST['username'] ) ) : ''; ?>" required />
				</div>

				<div class="ff-form-group">
					<div class="ff-field-header-row">
						<label for="password" class="ff-field-label">
							<?php esc_html_e( 'Password', 'foxfire-child' ); ?> <span class="required" aria-hidden="true">*</span>
						</label>
						<a href="<?php echo esc_url( wp_lostpassword_url() ); ?>" class="ff-forgot-link">
							<?php esc_html_e( 'Forgot password?', 'foxfire-child' ); ?>
						</a>
					</div>
					<div class="ff-password-input-wrap">
						<input class="ff-form-input woocommerce-Input woocommerce-Input--text input-text" type="password" name="password" id="password" autocomplete="current-password" required />
						<button type="button" class="ff-pwd-toggle" aria-label="<?php esc_attr_e( 'Toggle password visibility', 'foxfire-child' ); ?>">
							<svg class="ff-pwd-eye-open" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/></svg>
							<svg class="ff-pwd-eye-closed" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m15 18-.722-3.25"/><path d="M2 8a10.645 10.645 0 0 0 20 0"/><path d="m20 15-1.726-2.05"/><path d="m4 15 1.726-2.05"/><path d="m9 18 .722-3.25"/></svg>
						</button>
					</div>
				</div>

				<?php do_action( 'woocommerce_login_form' ); ?>

				<div class="ff-form-group ff-form-group--remember">
					<label class="ff-checkbox-label">
						<input class="woocommerce-form__input woocommerce-form__input-checkbox" name="rememberme" type="checkbox" id="rememberme" value="forever" />
						<span><?php esc_html_e( 'Keep me signed in', 'foxfire-child' ); ?></span>
					</label>
				</div>

				<div class="ff-form-submit-wrap">
					<?php wp_nonce_field( 'woocommerce-login', 'woocommerce-login-nonce' ); ?>
					<button type="submit" class="ff-btn ff-btn--primary ff-btn--full woocommerce-button button woocommerce-form-login__submit" name="login" value="<?php esc_attr_e( 'Sign In', 'foxfire-child' ); ?>">
						<?php esc_html_e( 'Sign In', 'foxfire-child' ); ?>
					</button>
				</div>

				<?php do_action( 'woocommerce_login_form_end' ); ?>

			</form>
		</div>

		<?php if ( $enable_registration ) : ?>
			<!-- Tab Panel 2: Create Account -->
			<div id="ff-tab-register" class="ff-auth-panel" role="tabpanel" style="display: none;">
				<form method="post" class="ff-auth-form woocommerce-form woocommerce-form-register register" <?php do_action( 'woocommerce_register_form_tag' ); ?> >

					<?php do_action( 'woocommerce_register_form_start' ); ?>

					<?php if ( 'no' === get_option( 'woocommerce_registration_generate_username' ) ) : ?>
						<div class="ff-form-group">
							<label for="reg_username" class="ff-field-label">
								<?php esc_html_e( 'Username', 'foxfire-child' ); ?> <span class="required" aria-hidden="true">*</span>
							</label>
							<input type="text" class="ff-form-input woocommerce-Input woocommerce-Input--text input-text" name="username" id="reg_username" autocomplete="username" value="<?php echo ( ! empty( $_POST['username'] ) ) ? esc_attr( wp_unslash( $_POST['username'] ) ) : ''; ?>" required />
						</div>
					<?php endif; ?>

					<div class="ff-form-group">
						<label for="reg_email" class="ff-field-label">
							<?php esc_html_e( 'Email Address', 'foxfire-child' ); ?> <span class="required" aria-hidden="true">*</span>
						</label>
						<input type="email" class="ff-form-input woocommerce-Input woocommerce-Input--text input-text" name="email" id="reg_email" autocomplete="email" value="<?php echo ( ! empty( $_POST['email'] ) ) ? esc_attr( wp_unslash( $_POST['email'] ) ) : ''; ?>" required />
					</div>

					<?php if ( 'no' === get_option( 'woocommerce_registration_generate_password' ) ) : ?>
						<div class="ff-form-group">
							<label for="reg_password" class="ff-field-label">
								<?php esc_html_e( 'Password', 'foxfire-child' ); ?> <span class="required" aria-hidden="true">*</span>
							</label>
							<div class="ff-password-input-wrap">
								<input type="password" class="ff-form-input woocommerce-Input woocommerce-Input--text input-text" name="password" id="reg_password" autocomplete="new-password" required />
								<button type="button" class="ff-pwd-toggle" aria-label="<?php esc_attr_e( 'Toggle password visibility', 'foxfire-child' ); ?>">
									<svg class="ff-pwd-eye-open" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/></svg>
									<svg class="ff-pwd-eye-closed" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m15 18-.722-3.25"/><path d="M2 8a10.645 10.645 0 0 0 20 0"/><path d="m20 15-1.726-2.05"/><path d="m4 15 1.726-2.05"/><path d="m9 18 .722-3.25"/></svg>
								</button>
							</div>
						</div>
					<?php endif; ?>

					<?php do_action( 'woocommerce_register_form' ); ?>

					<!-- Terms of Service & Privacy Policy Agreement -->
					<div class="ff-form-group ff-form-group--terms">
						<label class="ff-checkbox-label">
							<input class="woocommerce-form__input woocommerce-form__input-checkbox" name="terms_agree" type="checkbox" id="terms_agree" required />
							<span>
								<?php
								printf(
									/* translators: 1: terms url, 2: privacy url */
									esc_html__( 'By creating an account, you agree to the %1$sTerms of Service%2$s and %3$sPrivacy Policy%4$s.', 'foxfire-child' ),
									'<a href="' . esc_url( home_url( '/terms/' ) ) . '" target="_blank" class="ff-terms-link">',
									'</a>',
									'<a href="' . esc_url( home_url( '/privacy-policy/' ) ) . '" target="_blank" class="ff-terms-link">',
									'</a>'
								);
								?>
								<span class="required" aria-hidden="true">*</span>
							</span>
						</label>
					</div>

					<div class="ff-form-submit-wrap">
						<?php wp_nonce_field( 'woocommerce-register', 'woocommerce-register-nonce' ); ?>
						<button type="submit" class="ff-btn ff-btn--primary ff-btn--full woocommerce-Button woocommerce-button button woocommerce-form-register__submit" name="register" value="<?php esc_attr_e( 'Create Account', 'foxfire-child' ); ?>">
							<?php esc_html_e( 'Create Account', 'foxfire-child' ); ?>
						</button>
					</div>

					<?php do_action( 'woocommerce_register_form_end' ); ?>

				</form>
			</div>
		<?php endif; ?>

	</div>

</div>
