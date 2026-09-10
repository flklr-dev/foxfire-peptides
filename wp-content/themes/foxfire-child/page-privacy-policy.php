<?php
/**
 * Template Name: Privacy Policy
 *
 * Draft privacy policy for Foxfire Peptides WooCommerce store.
 * Structured per POLICY_WRITING_GUIDE.md §3.
 * All [CLIENT TO CONFIRM] markers must be completed before launch.
 *
 * DRAFT — For client and legal review. Not attorney-reviewed.
 *
 * @package Foxfire_Child
 */

defined( 'ABSPATH' ) || exit;

get_header();

if ( foxfire_should_render_editor_page() ) {
	foxfire_render_editor_policy_page();
	get_footer();
	return;
}

$effective_date = 'September 2026';
$last_updated   = 'September 4, 2026';
$contact_url    = foxfire_get_page_url( 'contact', '/contact/' );
$terms_url      = foxfire_get_page_url( 'terms-and-conditions', '/terms-and-conditions/' );
$refund_url     = foxfire_get_page_url( 'refund-and-returns-policy', '/refund-and-returns-policy/' );
?>
<main id="main-content" class="ff-policy-page" tabindex="-1">
	<div class="ff-policy-page__inner">

		<header class="ff-policy-header">
			<p class="ff-policy-header__label"><?php esc_html_e( 'Legal', 'foxfire-child' ); ?></p>
			<h1 class="ff-policy-header__title"><?php esc_html_e( 'Privacy Policy', 'foxfire-child' ); ?></h1>
			<div class="ff-policy-header__meta">
				<span class="ff-policy-meta-item"><?php printf( esc_html__( 'Effective: %s', 'foxfire-child' ), esc_html( $effective_date ) ); ?></span>
				<span class="ff-policy-meta-sep" aria-hidden="true">&middot;</span>
				<span class="ff-policy-meta-item"><?php printf( esc_html__( 'Last updated: %s', 'foxfire-child' ), esc_html( $last_updated ) ); ?></span>
			</div>
			<div class="ff-policy-draft-notice" role="note">
				<?php esc_html_e( 'Working draft — items marked [CLIENT TO CONFIRM] require final details before publication.', 'foxfire-child' ); ?>
			</div>
		</header>

		<nav class="ff-policy-toc" aria-label="<?php esc_attr_e( 'Privacy Policy sections', 'foxfire-child' ); ?>">
			<p class="ff-policy-toc__label"><?php esc_html_e( 'Contents', 'foxfire-child' ); ?></p>
			<ol class="ff-policy-toc__list">
				<li><a href="#pp-intro"><?php esc_html_e( 'Introduction', 'foxfire-child' ); ?></a></li>
				<li><a href="#pp-collect"><?php esc_html_e( 'Information We Collect', 'foxfire-child' ); ?></a></li>
				<li><a href="#pp-how-collected"><?php esc_html_e( 'How Information Is Collected', 'foxfire-child' ); ?></a></li>
				<li><a href="#pp-use"><?php esc_html_e( 'How We Use Your Information', 'foxfire-child' ); ?></a></li>
				<li><a href="#pp-sharing"><?php esc_html_e( 'Information Sharing', 'foxfire-child' ); ?></a></li>
				<li><a href="#pp-payments"><?php esc_html_e( 'Payments', 'foxfire-child' ); ?></a></li>
				<li><a href="#pp-cookies"><?php esc_html_e( 'Cookies', 'foxfire-child' ); ?></a></li>
				<li><a href="#pp-retention"><?php esc_html_e( 'Data Retention', 'foxfire-child' ); ?></a></li>
				<li><a href="#pp-security"><?php esc_html_e( 'Security', 'foxfire-child' ); ?></a></li>
				<li><a href="#pp-rights"><?php esc_html_e( 'Your Privacy Rights', 'foxfire-child' ); ?></a></li>
				<li><a href="#pp-children"><?php esc_html_e( "Children's Privacy", 'foxfire-child' ); ?></a></li>
				<li><a href="#pp-changes"><?php esc_html_e( 'Policy Changes', 'foxfire-child' ); ?></a></li>
				<li><a href="#pp-contact"><?php esc_html_e( 'Contact Us', 'foxfire-child' ); ?></a></li>
			</ol>
		</nav>

		<div class="ff-policy-sections">

			<section id="pp-intro" class="ff-policy-section">
				<h2 class="ff-policy-section__heading"><?php esc_html_e( '1. Introduction', 'foxfire-child' ); ?></h2>
				<p><?php esc_html_e( 'This Privacy Policy explains how Foxfire Peptides ("Foxfire Peptides," "we," "us," or "our") collects, uses, stores, and shares information when you visit or make a purchase through our website at FoxfirePeptides.com (the "Site").', 'foxfire-child' ); ?></p>
				<p><?php esc_html_e( 'By using the Site, you agree to the collection and use of information as described in this Policy. If you do not agree with these practices, please do not use the Site.', 'foxfire-child' ); ?></p>
				<div class="ff-policy-placeholder-block">
					<strong><?php esc_html_e( '[CLIENT TO CONFIRM]', 'foxfire-child' ); ?></strong>
					<ul>
						<li><?php esc_html_e( 'Legal entity name (e.g., "Foxfire Peptides LLC")', 'foxfire-child' ); ?></li>
						<li><?php esc_html_e( 'Official business address', 'foxfire-child' ); ?></li>
						<li><?php esc_html_e( 'Privacy inquiry email address', 'foxfire-child' ); ?></li>
					</ul>
				</div>
			</section>

			<section id="pp-collect" class="ff-policy-section">
				<h2 class="ff-policy-section__heading"><?php esc_html_e( '2. Information We Collect', 'foxfire-child' ); ?></h2>
				<p><?php esc_html_e( 'We may collect the following categories of personal information:', 'foxfire-child' ); ?></p>
				<h3 class="ff-policy-subheading"><?php esc_html_e( 'Account and Order Information', 'foxfire-child' ); ?></h3>
				<ul>
					<li><?php esc_html_e( 'Name', 'foxfire-child' ); ?></li>
					<li><?php esc_html_e( 'Email address', 'foxfire-child' ); ?></li>
					<li><?php esc_html_e( 'Phone number', 'foxfire-child' ); ?></li>
					<li><?php esc_html_e( 'Billing address', 'foxfire-child' ); ?></li>
					<li><?php esc_html_e( 'Shipping address', 'foxfire-child' ); ?></li>
					<li><?php esc_html_e( 'Account login credentials (username and hashed password)', 'foxfire-child' ); ?></li>
					<li><?php esc_html_e( 'Order history, including products purchased, quantities, and order amounts', 'foxfire-child' ); ?></li>
				</ul>
				<h3 class="ff-policy-subheading"><?php esc_html_e( 'Payment Information', 'foxfire-child' ); ?></h3>
				<p><?php esc_html_e( 'Payment transactions are processed by third-party payment providers. We do not store full credit or debit card numbers on our servers. We may receive limited confirmation details from payment providers for reference purposes.', 'foxfire-child' ); ?></p>
				<h3 class="ff-policy-subheading"><?php esc_html_e( 'Technical and Usage Information', 'foxfire-child' ); ?></h3>
				<ul>
					<li><?php esc_html_e( 'IP address', 'foxfire-child' ); ?></li>
					<li><?php esc_html_e( 'Browser type and version', 'foxfire-child' ); ?></li>
					<li><?php esc_html_e( 'Device type and operating system', 'foxfire-child' ); ?></li>
					<li><?php esc_html_e( 'Pages viewed and navigation actions on the Site', 'foxfire-child' ); ?></li>
					<li><?php esc_html_e( 'Referring website or source', 'foxfire-child' ); ?></li>
					<li><?php esc_html_e( 'Session and cookie identifiers', 'foxfire-child' ); ?></li>
				</ul>
				<h3 class="ff-policy-subheading"><?php esc_html_e( 'Communications', 'foxfire-child' ); ?></h3>
				<p><?php esc_html_e( 'If you contact us through our contact form, email, or customer support, we collect the content of those communications and your contact details.', 'foxfire-child' ); ?></p>
			</section>

			<section id="pp-how-collected" class="ff-policy-section">
				<h2 class="ff-policy-section__heading"><?php esc_html_e( '3. How Information Is Collected', 'foxfire-child' ); ?></h2>
				<p><?php esc_html_e( 'We collect information through the following means:', 'foxfire-child' ); ?></p>
				<ul>
					<li><strong><?php esc_html_e( 'Account Registration:', 'foxfire-child' ); ?></strong> <?php esc_html_e( 'When you create a customer account on our Site.', 'foxfire-child' ); ?></li>
					<li><strong><?php esc_html_e( 'Checkout:', 'foxfire-child' ); ?></strong> <?php esc_html_e( 'When you place an order, whether as a registered customer or as a guest.', 'foxfire-child' ); ?></li>
					<li><strong><?php esc_html_e( 'Contact Forms:', 'foxfire-child' ); ?></strong> <?php esc_html_e( 'When you submit an inquiry through our contact page or customer support channels.', 'foxfire-child' ); ?></li>
					<li><strong><?php esc_html_e( 'Cookies and Similar Technologies:', 'foxfire-child' ); ?></strong> <?php esc_html_e( 'Automatically when you browse the Site. See Section 7 (Cookies) for details.', 'foxfire-child' ); ?></li>
					<li><strong><?php esc_html_e( 'Payment Providers:', 'foxfire-child' ); ?></strong> <?php esc_html_e( 'When a payment transaction is processed through a third-party service.', 'foxfire-child' ); ?></li>
				</ul>
			</section>

			<section id="pp-use" class="ff-policy-section">
				<h2 class="ff-policy-section__heading"><?php esc_html_e( '4. How We Use Your Information', 'foxfire-child' ); ?></h2>
				<p><?php esc_html_e( 'We use the information we collect to:', 'foxfire-child' ); ?></p>
				<ul>
					<li><?php esc_html_e( 'Process, fulfill, and manage your orders', 'foxfire-child' ); ?></li>
					<li><?php esc_html_e( 'Create and maintain your customer account', 'foxfire-child' ); ?></li>
					<li><?php esc_html_e( 'Process and verify payments through our payment providers', 'foxfire-child' ); ?></li>
					<li><?php esc_html_e( 'Send transactional emails such as order confirmations, shipping updates, and account notices', 'foxfire-child' ); ?></li>
					<li><?php esc_html_e( 'Provide shipping and order tracking information', 'foxfire-child' ); ?></li>
					<li><?php esc_html_e( 'Respond to your customer support inquiries', 'foxfire-child' ); ?></li>
					<li><?php esc_html_e( 'Detect, prevent, and investigate fraudulent or unauthorized activity', 'foxfire-child' ); ?></li>
					<li><?php esc_html_e( 'Maintain website security and functionality', 'foxfire-child' ); ?></li>
					<li><?php esc_html_e( 'Maintain business and legal records as required', 'foxfire-child' ); ?></li>
					<li><?php esc_html_e( 'Improve and optimize the Site experience', 'foxfire-child' ); ?></li>
				</ul>
				<p><?php esc_html_e( 'We do not sell your personal information to third parties for their own marketing or advertising purposes.', 'foxfire-child' ); ?></p>
			</section>

			<section id="pp-sharing" class="ff-policy-section">
				<h2 class="ff-policy-section__heading"><?php esc_html_e( '5. Information Sharing', 'foxfire-child' ); ?></h2>
				<p><?php esc_html_e( 'We do not sell your personal information. We share information only as necessary to operate the Site and fulfill orders, with the following categories of third parties:', 'foxfire-child' ); ?></p>
				<ul>
					<li><strong><?php esc_html_e( 'Payment Processors:', 'foxfire-child' ); ?></strong> <?php esc_html_e( 'To securely process order payments. These providers have their own privacy policies governing the data they receive.', 'foxfire-child' ); ?></li>
					<li><strong><?php esc_html_e( 'Hosting Provider:', 'foxfire-child' ); ?></strong> <?php esc_html_e( 'Our website and customer data are hosted on servers provided by Hostinger.', 'foxfire-child' ); ?></li>
					<li><strong><?php esc_html_e( 'Shipping and Carrier Services:', 'foxfire-child' ); ?></strong> <?php esc_html_e( 'We share your name and shipping address with carriers to fulfill and deliver your order.', 'foxfire-child' ); ?></li>
					<li><strong><?php esc_html_e( 'Email Service Providers:', 'foxfire-child' ); ?></strong> <?php esc_html_e( 'To send transactional and operational emails related to your orders and account.', 'foxfire-child' ); ?></li>
					<li><strong><?php esc_html_e( 'WordPress / WooCommerce Extensions:', 'foxfire-child' ); ?></strong> <?php esc_html_e( 'Established plugins and extensions that operate core store functionality.', 'foxfire-child' ); ?></li>
					<li><strong><?php esc_html_e( 'Legal and Regulatory Authorities:', 'foxfire-child' ); ?></strong> <?php esc_html_e( 'Where required by law, court order, or regulatory authority.', 'foxfire-child' ); ?></li>
				</ul>
				<p><?php esc_html_e( 'This list will be updated to reflect the actual third-party services in use once the final plugin and service stack is confirmed.', 'foxfire-child' ); ?></p>
			</section>

			<section id="pp-payments" class="ff-policy-section">
				<h2 class="ff-policy-section__heading"><?php esc_html_e( '6. Payments', 'foxfire-child' ); ?></h2>
				<p><?php esc_html_e( 'Payment card transactions are processed by third-party payment providers. We do not store full payment card numbers on our own servers. The payment providers we use have their own terms of service and privacy policies.', 'foxfire-child' ); ?></p>
				<p><?php esc_html_e( 'For orders placed using alternative payment methods such as Wise, Zelle, or GCash, payment information is transmitted directly through those third-party services and is subject to their respective terms and privacy policies.', 'foxfire-child' ); ?></p>
				<div class="ff-policy-placeholder-block">
					<strong><?php esc_html_e( '[CLIENT TO CONFIRM]', 'foxfire-child' ); ?></strong>
					<p><?php esc_html_e( 'Final payment gateway(s) to be named once confirmed and integrated.', 'foxfire-child' ); ?></p>
				</div>
			</section>

			<section id="pp-cookies" class="ff-policy-section">
				<h2 class="ff-policy-section__heading"><?php esc_html_e( '7. Cookies', 'foxfire-child' ); ?></h2>
				<p><?php esc_html_e( 'Our Site uses cookies and similar technologies to support essential functionality. A cookie is a small text file stored on your device by your browser.', 'foxfire-child' ); ?></p>
				<h3 class="ff-policy-subheading"><?php esc_html_e( 'Essential / Functional Cookies', 'foxfire-child' ); ?></h3>
				<p><?php esc_html_e( 'These cookies are necessary for the Site to function and cannot be disabled without affecting core features:', 'foxfire-child' ); ?></p>
				<ul>
					<li><?php esc_html_e( 'Shopping cart session cookies — to maintain your cart across pages', 'foxfire-child' ); ?></li>
					<li><?php esc_html_e( 'Customer login session cookies — to keep you signed in', 'foxfire-child' ); ?></li>
					<li><?php esc_html_e( 'Security / anti-CSRF cookies — to protect form submissions', 'foxfire-child' ); ?></li>
				</ul>
				<p><?php esc_html_e( 'We do not currently use advertising, marketing, or cross-site tracking cookies. If analytics tools are added in the future, this section will be updated accordingly.', 'foxfire-child' ); ?></p>
				<p><?php esc_html_e( 'Most browsers allow you to manage or delete cookies through browser settings. Disabling essential cookies may prevent parts of the Site from functioning correctly.', 'foxfire-child' ); ?></p>
			</section>

			<section id="pp-retention" class="ff-policy-section">
				<h2 class="ff-policy-section__heading"><?php esc_html_e( '8. Data Retention', 'foxfire-child' ); ?></h2>
				<p><?php esc_html_e( 'We retain personal information for as long as reasonably necessary to fulfill the purposes described in this Policy, unless a longer retention period is required by law.', 'foxfire-child' ); ?></p>
				<ul>
					<li><strong><?php esc_html_e( 'Order records:', 'foxfire-child' ); ?></strong> <span class="ff-policy-placeholder"><?php esc_html_e( '[RETENTION PERIOD — CLIENT TO CONFIRM]', 'foxfire-child' ); ?></span></li>
					<li><strong><?php esc_html_e( 'Customer accounts:', 'foxfire-child' ); ?></strong> <span class="ff-policy-placeholder"><?php esc_html_e( '[RETENTION PERIOD — CLIENT TO CONFIRM]', 'foxfire-child' ); ?></span></li>
					<li><strong><?php esc_html_e( 'Support communications:', 'foxfire-child' ); ?></strong> <span class="ff-policy-placeholder"><?php esc_html_e( '[RETENTION PERIOD — CLIENT TO CONFIRM]', 'foxfire-child' ); ?></span></li>
				</ul>
				<p><?php esc_html_e( 'You may request deletion of your personal data at any time by contacting us. We will process requests in accordance with applicable law and our retention obligations.', 'foxfire-child' ); ?></p>
			</section>

			<section id="pp-security" class="ff-policy-section">
				<h2 class="ff-policy-section__heading"><?php esc_html_e( '9. Security', 'foxfire-child' ); ?></h2>
				<p><?php esc_html_e( 'We use reasonable technical and organizational measures designed to protect the personal information we hold from unauthorized access, disclosure, alteration, or destruction. These measures include SSL/TLS encryption for data transmitted to and from the Site.', 'foxfire-child' ); ?></p>
				<p><?php esc_html_e( 'No method of transmission over the Internet or electronic storage is 100% secure. While we work to protect your information, we cannot guarantee absolute security.', 'foxfire-child' ); ?></p>
			</section>

			<section id="pp-rights" class="ff-policy-section">
				<h2 class="ff-policy-section__heading"><?php esc_html_e( '10. Your Privacy Rights', 'foxfire-child' ); ?></h2>
				<p><?php esc_html_e( 'Depending on where you live, you may have certain rights regarding your personal information. These may include the right to:', 'foxfire-child' ); ?></p>
				<ul>
					<li><?php esc_html_e( 'Know what personal information we hold about you', 'foxfire-child' ); ?></li>
					<li><?php esc_html_e( 'Request correction of inaccurate personal information', 'foxfire-child' ); ?></li>
					<li><?php esc_html_e( 'Request deletion of your personal information', 'foxfire-child' ); ?></li>
					<li><?php esc_html_e( 'Request a portable copy of your personal information', 'foxfire-child' ); ?></li>
					<li><?php esc_html_e( 'Opt out of the sale or sharing of your personal information (where applicable)', 'foxfire-child' ); ?></li>
				</ul>
				<p><?php printf( esc_html__( 'To submit a privacy rights request, please contact us using the details in Section 13, or visit our %1$sContact page%2$s.', 'foxfire-child' ), '<a href="' . esc_url( $contact_url ) . '">', '</a>' ); ?></p>
				<div class="ff-policy-placeholder-block">
					<strong><?php esc_html_e( '[CLIENT TO CONFIRM]', 'foxfire-child' ); ?></strong>
					<p><?php esc_html_e( 'Whether CCPA/CPRA or similar state/regional privacy laws apply. If applicable, specific required notices and opt-out links must be confirmed with legal counsel.', 'foxfire-child' ); ?></p>
				</div>
			</section>

			<section id="pp-children" class="ff-policy-section">
				<h2 class="ff-policy-section__heading"><?php esc_html_e( "11. Children's Privacy", 'foxfire-child' ); ?></h2>
				<div class="ff-policy-placeholder-block">
					<strong><?php esc_html_e( '[CLIENT TO CONFIRM — MINIMUM AGE REQUIRED]', 'foxfire-child' ); ?></strong>
					<p><?php esc_html_e( 'The Site is intended for individuals who meet the minimum age requirement set in our Terms and Conditions. We do not knowingly collect personal information from individuals below that minimum age. The minimum age must be approved by the client before publication.', 'foxfire-child' ); ?></p>
				</div>
			</section>

			<section id="pp-changes" class="ff-policy-section">
				<h2 class="ff-policy-section__heading"><?php esc_html_e( '12. Policy Changes', 'foxfire-child' ); ?></h2>
				<p><?php esc_html_e( 'We may update this Privacy Policy from time to time to reflect changes in our practices, the Site, or applicable law. When we make changes, we will update the "Last updated" date at the top of this page. We encourage you to review this Policy periodically.', 'foxfire-child' ); ?></p>
				<p><?php esc_html_e( 'Your continued use of the Site after any changes to this Policy constitutes acceptance of the updated terms.', 'foxfire-child' ); ?></p>
			</section>

			<section id="pp-contact" class="ff-policy-section">
				<h2 class="ff-policy-section__heading"><?php esc_html_e( '13. Contact Us', 'foxfire-child' ); ?></h2>
				<p><?php esc_html_e( 'If you have questions, concerns, or requests regarding this Privacy Policy or your personal information, please contact us:', 'foxfire-child' ); ?></p>
				<div class="ff-policy-contact-block">
					<p>
						<strong>Foxfire Peptides</strong><br>
						<span class="ff-policy-placeholder"><?php esc_html_e( '[LEGAL ENTITY NAME — CLIENT TO CONFIRM]', 'foxfire-child' ); ?></span><br>
						<span class="ff-policy-placeholder"><?php esc_html_e( '[BUSINESS ADDRESS — CLIENT TO CONFIRM]', 'foxfire-child' ); ?></span><br>
						<?php esc_html_e( 'Website:', 'foxfire-child' ); ?> <a href="<?php echo esc_url( home_url( '/' ) ); ?>">FoxfirePeptides.com</a><br>
						<?php esc_html_e( 'Email:', 'foxfire-child' ); ?> <span class="ff-policy-placeholder"><?php esc_html_e( '[PRIVACY CONTACT EMAIL — CLIENT TO CONFIRM]', 'foxfire-child' ); ?></span>
					</p>
				</div>
				<p><?php printf( esc_html__( 'You may also use our %1$sContact page%2$s to reach us.', 'foxfire-child' ), '<a href="' . esc_url( $contact_url ) . '">', '</a>' ); ?></p>
			</section>

		</div>

		<nav class="ff-policy-related-links" aria-label="<?php esc_attr_e( 'Related legal pages', 'foxfire-child' ); ?>">
			<p class="ff-policy-related-links__label"><?php esc_html_e( 'Related', 'foxfire-child' ); ?></p>
			<ul class="ff-policy-related-links__list">
				<li><a href="<?php echo esc_url( $terms_url ); ?>"><?php esc_html_e( 'Terms &amp; Conditions', 'foxfire-child' ); ?></a></li>
				<li><a href="<?php echo esc_url( $refund_url ); ?>"><?php esc_html_e( 'Refund &amp; Returns Policy', 'foxfire-child' ); ?></a></li>
			</ul>
		</nav>

	</div>
</main>

<?php get_footer(); ?>
