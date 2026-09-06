<?php
/**
 * Template Name: Contact Us
 *
 * Contact Us page for Foxfire Peptides.
 * Clean, structured layout with robust spam & security protection:
 *  - WordPress nonce verification (CSRF prevention)
 *  - Honeypot anti-spam field
 *  - Server-side input sanitization via WordPress APIs
 *  - Strict input length limits
 *  - Email header injection prevention
 *  - Allowlist-based subject validation
 *  - Rate limiting via transients (max 3 submissions per IP per hour)
 *
 * @package Foxfire_Child
 */

defined( 'ABSPATH' ) || exit;

// ── Form State ───────────────────────────────────────────────────────────────
$form_status  = '';
$form_message = '';
$form_data    = array(
	'name'         => '',
	'email'        => '',
	'order_number' => '',
	'subject'      => 'general',
	'message'      => '',
);

// ── Allowed subject keys (allowlist — no user value passes through unchecked) ─
$allowed_subjects = array(
	'general'   => 'General Inquiry',
	'order'     => 'Order Status & Tracking',
	'coa'       => 'COA / Testing Question',
	'bulk'      => 'Bulk / Wholesale Inquiry',
	'technical' => 'Website or Account Issue',
);

// ── Process Submission ───────────────────────────────────────────────────────
if ( 'POST' === $_SERVER['REQUEST_METHOD'] && isset( $_POST['foxfire_contact_submit'] ) ) {

	// 1. CSRF — WordPress nonce.
	$raw_nonce = isset( $_POST['foxfire_contact_nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['foxfire_contact_nonce'] ) ) : '';
	if ( ! wp_verify_nonce( $raw_nonce, 'foxfire_contact_action' ) ) {
		$form_status  = 'error';
		$form_message = __( 'Security check failed. Please refresh the page and try again.', 'foxfire-child' );

	// 2. Honeypot — filled means bot.
	} elseif ( ! empty( $_POST['ff_website_hp'] ) ) {
		// Silent success — don't tip off bots.
		$form_status  = 'success';
		$form_message = __( 'Thank you! Your message has been received. We will be in touch shortly.', 'foxfire-child' );

	} else {

		// 3. Rate limiting — max 3 submissions per IP per hour via transients.
		$ip_key       = 'foxfire_contact_rl_' . md5( sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ?? '' ) ) );
		$submit_count = (int) get_transient( $ip_key );
		if ( $submit_count >= 3 ) {
			$form_status  = 'error';
			$form_message = __( 'Too many messages submitted. Please wait a while before trying again.', 'foxfire-child' );
		} else {

			// 4. Extract inputs, sanitize & enforce length limits.
			$raw_name     = trim( wp_unslash( $_POST['ff_name'] ?? '' ) );
			$raw_email    = trim( wp_unslash( $_POST['ff_email'] ?? '' ) );
			$raw_order    = trim( wp_unslash( $_POST['ff_order_number'] ?? '' ) );
			$raw_message  = trim( wp_unslash( $_POST['ff_message'] ?? '' ) );
			$subject_raw  = sanitize_key( wp_unslash( $_POST['ff_subject'] ?? '' ) );

			$name         = substr( sanitize_text_field( $raw_name ), 0, 100 );
			$email        = substr( sanitize_email( $raw_email ), 0, 254 );
			$order_number = substr( sanitize_text_field( $raw_order ), 0, 40 );
			$message      = substr( sanitize_textarea_field( $raw_message ), 0, 3000 );

			// Allowlist subject — fall back to 'general'.
			$subject_key = array_key_exists( $subject_raw, $allowed_subjects ) ? $subject_raw : 'general';

			$form_data = array(
				'name'         => $name,
				'email'        => substr( sanitize_text_field( $raw_email ), 0, 254 ),
				'order_number' => $order_number,
				'subject'      => $subject_key,
				'message'      => $message,
			);

			// 5. Required field & email format validation.
			if ( empty( $name ) || empty( $raw_email ) || empty( $message ) ) {
				$form_status  = 'error';
				$form_message = __( 'Please fill in all required fields (Name, Email, and Message).', 'foxfire-child' );
			} elseif ( ! is_email( $email ) ) {
				$form_status  = 'error';
				$form_message = __( 'Please enter a valid email address.', 'foxfire-child' );
			} elseif ( strlen( $message ) < 10 ) {
				$form_status  = 'error';
				$form_message = __( 'Please provide a bit more detail in your message.', 'foxfire-child' );
			} else {

				// 6. Build notification email.
				$admin_email  = get_option( 'admin_email' );
				$site_name    = get_bloginfo( 'name' );
				$subject_text = $allowed_subjects[ $subject_key ];

				// Prevent email header injection: strip CR/LF from name & email.
				$safe_name  = str_replace( array( "\r", "\n" ), '', $name );
				$safe_email = str_replace( array( "\r", "\n" ), '', $email );

				$email_subject = sprintf( '[%s] %s from %s', $site_name, $subject_text, $safe_name );

				$email_body  = sprintf( "New message via Foxfire Peptides contact form\n\n" );
				$email_body .= sprintf( "Name:    %s\n", $safe_name );
				$email_body .= sprintf( "Email:   %s\n", $safe_email );
				if ( ! empty( $order_number ) ) {
					$email_body .= sprintf( "Order #: %s\n", $order_number );
				}
				$email_body .= sprintf( "Topic:   %s\n", $subject_text );
				$email_body .= sprintf( "Date:    %s\n\n", current_time( 'mysql' ) );
				$email_body .= sprintf( "Message:\n%s\n\n", $message );
				$email_body .= "---\nSent from the Foxfire Peptides contact form.";

				$headers = array(
					'From: ' . $site_name . ' <' . ( $admin_email ?: 'noreply@foxfirepeptides.com' ) . '>',
					'Reply-To: ' . $safe_name . ' <' . $safe_email . '>',
					'Content-Type: text/plain; charset=UTF-8',
				);

				wp_mail( $admin_email, $email_subject, $email_body, $headers );

				// 7. Increment rate-limit counter (1-hour TTL).
				set_transient( $ip_key, $submit_count + 1, HOUR_IN_SECONDS );

				$form_status  = 'success';
				$form_message = __( 'Thank you! Your message has been sent. Our team will respond within 12–24 business hours.', 'foxfire-child' );

				// Clear form on success.
				$form_data = array(
					'name'         => '',
					'email'        => '',
					'order_number' => '',
					'subject'      => 'general',
					'message'      => '',
				);
			}
		}
	}
}

get_header();

?>
<main id="main-content" class="ff-contact-page" tabindex="-1">
	<div class="ff-contact-page__inner">

		<!-- Page Header -->
		<header class="ff-contact-header">
			<span class="ff-contact-header__label"><?php esc_html_e( 'Customer Care & Inquiries', 'foxfire-child' ); ?></span>
			<h1 class="ff-contact-header__title"><?php esc_html_e( 'Contact Us', 'foxfire-child' ); ?></h1>
			<p class="ff-contact-header__lead">
				<?php esc_html_e( 'Have questions about products, batch documentation, or your order? We are here to help.', 'foxfire-child' ); ?>
			</p>
		</header>

		<!-- Main 2-Column Layout: Left Info Container + Right Form Card -->
		<div class="ff-contact-layout">

			<!-- Left Column: Contact Information Container -->
			<aside class="ff-contact-info-col" aria-label="<?php esc_attr_e( 'Contact Information', 'foxfire-child' ); ?>">
				<div class="ff-contact-info-card">

					<div class="ff-contact-info-card__header">
						<span class="ff-contact-info-card__eyebrow"><?php esc_html_e( 'Direct Contact', 'foxfire-child' ); ?></span>
						<h2 class="ff-contact-info-card__title"><?php esc_html_e( 'How to Reach Us', 'foxfire-child' ); ?></h2>
						<p class="ff-contact-info-card__desc">
							<?php esc_html_e( 'Our team is available Monday through Friday to assist with inquiries, documentation, and orders.', 'foxfire-child' ); ?>
						</p>
					</div>

					<div class="ff-contact-info-list">

						<!-- Email -->
						<div class="ff-contact-info-item">
							<div class="ff-contact-info-item__icon" aria-hidden="true">
								<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
									<path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path>
									<polyline points="22,6 12,13 2,6"></polyline>
								</svg>
							</div>
							<div class="ff-contact-info-item__content">
								<span class="ff-contact-info-item__label"><?php esc_html_e( 'Email', 'foxfire-child' ); ?></span>
								<a href="mailto:support@foxfirepeptides.com" class="ff-contact-info-item__value ff-contact-info-item__link">support@foxfirepeptides.com</a>
							</div>
						</div>

						<!-- Response Time -->
						<div class="ff-contact-info-item">
							<div class="ff-contact-info-item__icon" aria-hidden="true">
								<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
									<circle cx="12" cy="12" r="10"></circle>
									<polyline points="12 6 12 12 16 14"></polyline>
								</svg>
							</div>
							<div class="ff-contact-info-item__content">
								<span class="ff-contact-info-item__label"><?php esc_html_e( 'Response Time', 'foxfire-child' ); ?></span>
								<span class="ff-contact-info-item__value"><?php esc_html_e( '12–24 business hours', 'foxfire-child' ); ?></span>
							</div>
						</div>

						<!-- Hours -->
						<div class="ff-contact-info-item">
							<div class="ff-contact-info-item__icon" aria-hidden="true">
								<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
									<rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
									<line x1="16" y1="2" x2="16" y2="6"></line>
									<line x1="8" y1="2" x2="8" y2="6"></line>
									<line x1="3" y1="10" x2="21" y2="10"></line>
								</svg>
							</div>
							<div class="ff-contact-info-item__content">
								<span class="ff-contact-info-item__label"><?php esc_html_e( 'Hours', 'foxfire-child' ); ?></span>
								<span class="ff-contact-info-item__value"><?php esc_html_e( 'Mon – Fri, 9 AM – 5 PM EST', 'foxfire-child' ); ?></span>
							</div>
						</div>

					</div>

					<div class="ff-contact-trust-note">
						<div class="ff-contact-trust-note__icon" aria-hidden="true">
							<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
								<path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path>
							</svg>
						</div>
						<p class="ff-contact-trust-note__text">
							<?php esc_html_e( 'Every inquiry is received and handled directly by the Foxfire team.', 'foxfire-child' ); ?>
						</p>
					</div>

				</div>
			</aside>


			<!-- Right: Contact Form -->
			<section class="ff-contact-form-col" aria-labelledby="contact-form-title">

				<div class="ff-contact-form-card">
					<header class="ff-contact-form-card__header">
						<h2 id="contact-form-title" class="ff-contact-form-card__title"><?php esc_html_e( 'Send Us a Message', 'foxfire-child' ); ?></h2>
						<p class="ff-contact-form-card__desc"><?php esc_html_e( 'Fill in the details below and we will get back to you as soon as possible.', 'foxfire-child' ); ?></p>
					</header>

					<?php if ( ! empty( $form_message ) ) : ?>
						<div
							class="ff-form-alert ff-form-alert--<?php echo esc_attr( $form_status ); ?>"
							role="alert"
							aria-live="polite"
						>
							<?php if ( 'success' === $form_status ) : ?>
								<svg class="ff-form-alert__icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
							<?php else : ?>
								<svg class="ff-form-alert__icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>
							<?php endif; ?>
							<?php echo esc_html( $form_message ); ?>
						</div>
					<?php endif; ?>

					<?php if ( 'success' !== $form_status ) : ?>
					<form
						method="post"
						action="<?php echo esc_url( get_permalink() ); ?>#contact-form-title"
						class="ff-contact-form"
						novalidate
					>
						<?php wp_nonce_field( 'foxfire_contact_action', 'foxfire_contact_nonce' ); ?>

						<!-- Honeypot: hidden from real users, filled by bots -->
						<div class="ff-hp-field" aria-hidden="true" style="position:absolute;left:-9999px;width:1px;height:1px;overflow:hidden;">
							<label for="ff_website_hp"><?php esc_html_e( 'Leave blank', 'foxfire-child' ); ?></label>
							<input type="text" name="ff_website_hp" id="ff_website_hp" tabindex="-1" autocomplete="off" value="" />
						</div>

						<!-- Row 1: Name + Email -->
						<div class="ff-form-row ff-form-row--split">
							<div class="ff-form-group">
								<label for="ff_name" class="ff-form-label">
									<?php esc_html_e( 'Your Name', 'foxfire-child' ); ?> <span class="ff-required" aria-label="<?php esc_attr_e( 'required', 'foxfire-child' ); ?>">*</span>
								</label>
								<input
									type="text"
									name="ff_name"
									id="ff_name"
									class="ff-form-input<?php echo ( 'error' === $form_status && empty( $form_data['name'] ) ) ? ' ff-form-input--invalid' : ''; ?>"
									value="<?php echo esc_attr( $form_data['name'] ); ?>"
									placeholder="<?php esc_attr_e( 'Full name', 'foxfire-child' ); ?>"
									maxlength="100"
									autocomplete="name"
									required
								/>
							</div>
							<div class="ff-form-group">
								<label for="ff_email" class="ff-form-label">
									<?php esc_html_e( 'Email Address', 'foxfire-child' ); ?> <span class="ff-required" aria-label="<?php esc_attr_e( 'required', 'foxfire-child' ); ?>">*</span>
								</label>
								<input
									type="email"
									name="ff_email"
									id="ff_email"
									class="ff-form-input<?php echo ( 'error' === $form_status && empty( $form_data['email'] ) ) ? ' ff-form-input--invalid' : ''; ?>"
									value="<?php echo esc_attr( $form_data['email'] ); ?>"
									placeholder="<?php esc_attr_e( 'you@example.com', 'foxfire-child' ); ?>"
									maxlength="254"
									autocomplete="email"
									required
								/>
							</div>
						</div>

						<!-- Row 2: Topic + Order # -->
						<div class="ff-form-row ff-form-row--split">
							<div class="ff-form-group">
								<label for="ff_subject" class="ff-form-label">
									<?php esc_html_e( 'Topic', 'foxfire-child' ); ?> <span class="ff-required" aria-label="<?php esc_attr_e( 'required', 'foxfire-child' ); ?>">*</span>
								</label>
								<select name="ff_subject" id="ff_subject" class="ff-form-select" required>
									<?php foreach ( $allowed_subjects as $key => $label ) : ?>
										<option value="<?php echo esc_attr( $key ); ?>" <?php selected( $form_data['subject'], $key ); ?>>
											<?php echo esc_html( $label ); ?>
										</option>
									<?php endforeach; ?>
								</select>
							</div>
							<div class="ff-form-group">
								<label for="ff_order_number" class="ff-form-label">
									<?php esc_html_e( 'Order Number', 'foxfire-child' ); ?> <span class="ff-optional"><?php esc_html_e( '(if applicable)', 'foxfire-child' ); ?></span>
								</label>
								<input
									type="text"
									name="ff_order_number"
									id="ff_order_number"
									class="ff-form-input"
									value="<?php echo esc_attr( $form_data['order_number'] ); ?>"
									placeholder="<?php esc_attr_e( 'e.g. 1042', 'foxfire-child' ); ?>"
									maxlength="40"
									autocomplete="off"
									inputmode="numeric"
									pattern="[0-9]*"
								/>
							</div>
						</div>

						<!-- Row 3: Message -->
						<div class="ff-form-group">
							<label for="ff_message" class="ff-form-label">
								<?php esc_html_e( 'Message', 'foxfire-child' ); ?> <span class="ff-required" aria-label="<?php esc_attr_e( 'required', 'foxfire-child' ); ?>">*</span>
							</label>
							<textarea
								name="ff_message"
								id="ff_message"
								rows="5"
								class="ff-form-textarea<?php echo ( 'error' === $form_status && empty( $form_data['message'] ) ) ? ' ff-form-input--invalid' : ''; ?>"
								placeholder="<?php esc_attr_e( 'Tell us how we can help…', 'foxfire-child' ); ?>"
								maxlength="3000"
								required
							><?php echo esc_textarea( $form_data['message'] ); ?></textarea>
							<span class="ff-form-hint"><?php esc_html_e( 'Max 3,000 characters.', 'foxfire-child' ); ?></span>
						</div>

						<!-- Submit -->
						<div class="ff-form-action">
							<button
								type="submit"
								name="foxfire_contact_submit"
								value="1"
								class="button button--primary ff-contact-submit-btn"
							><?php esc_html_e( 'Send Message', 'foxfire-child' ); ?></button>
						</div>

					</form>
					<?php endif; ?>

				</div><!-- /.ff-contact-form-card -->

			</section>

		</div><!-- /.ff-contact-layout -->

	</div><!-- /.ff-contact-page__inner -->
</main>
<?php
get_footer();
