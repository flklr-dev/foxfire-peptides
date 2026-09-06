<?php
/**
 * Template Name: Terms & Conditions
 *
 * Draft Terms and Conditions for Foxfire Peptides WooCommerce store.
 * Structured per POLICY_WRITING_GUIDE.md §4.
 * All [CLIENT TO CONFIRM] markers must be completed before launch.
 *
 * IMPORTANT: Research Use Only (RUO) language in Section 3 must be
 * reviewed and approved by the client and/or legal counsel before publication.
 *
 * DRAFT — For client and legal review. Not attorney-reviewed.
 *
 * @package Foxfire_Child
 */

defined( 'ABSPATH' ) || exit;

get_header();

$effective_date = 'September 2026';
$last_updated   = 'September 4, 2026';
$contact_url    = foxfire_get_page_url( 'contact', '/contact/' );
$privacy_url    = foxfire_get_page_url( 'privacy-policy', '/privacy-policy/' );
$refund_url     = foxfire_get_page_url( 'refund-and-returns-policy', '/refund-and-returns-policy/' );
?>
<main id="main-content" class="ff-policy-page" tabindex="-1">
	<div class="ff-policy-page__inner">

		<header class="ff-policy-header">
			<p class="ff-policy-header__label"><?php esc_html_e( 'Legal', 'foxfire-child' ); ?></p>
			<h1 class="ff-policy-header__title"><?php esc_html_e( 'Terms &amp; Conditions', 'foxfire-child' ); ?></h1>
			<div class="ff-policy-header__meta">
				<span class="ff-policy-meta-item"><?php printf( esc_html__( 'Effective: %s', 'foxfire-child' ), esc_html( $effective_date ) ); ?></span>
				<span class="ff-policy-meta-sep" aria-hidden="true">&middot;</span>
				<span class="ff-policy-meta-item"><?php printf( esc_html__( 'Last updated: %s', 'foxfire-child' ), esc_html( $last_updated ) ); ?></span>
			</div>
			<div class="ff-policy-draft-notice" role="note">
				<?php esc_html_e( 'Working draft — items marked [CLIENT TO CONFIRM] require final details and legal review before publication.', 'foxfire-child' ); ?>
			</div>
		</header>

		<nav class="ff-policy-toc" aria-label="<?php esc_attr_e( 'Terms and Conditions sections', 'foxfire-child' ); ?>">
			<p class="ff-policy-toc__label"><?php esc_html_e( 'Contents', 'foxfire-child' ); ?></p>
			<ol class="ff-policy-toc__list">
				<li><a href="#tc-acceptance"><?php esc_html_e( 'Acceptance of Terms', 'foxfire-child' ); ?></a></li>
				<li><a href="#tc-eligibility"><?php esc_html_e( 'Eligibility', 'foxfire-child' ); ?></a></li>
				<li><a href="#tc-ruo"><?php esc_html_e( 'Research Use Only', 'foxfire-child' ); ?></a></li>
				<li><a href="#tc-prohibited"><?php esc_html_e( 'Prohibited Uses', 'foxfire-child' ); ?></a></li>
				<li><a href="#tc-products"><?php esc_html_e( 'Product Information', 'foxfire-child' ); ?></a></li>
				<li><a href="#tc-orders"><?php esc_html_e( 'Orders and Acceptance', 'foxfire-child' ); ?></a></li>
				<li><a href="#tc-pricing"><?php esc_html_e( 'Pricing and Payment', 'foxfire-child' ); ?></a></li>
				<li><a href="#tc-shipping"><?php esc_html_e( 'Shipping', 'foxfire-child' ); ?></a></li>
				<li><a href="#tc-returns"><?php esc_html_e( 'Returns and Refunds', 'foxfire-child' ); ?></a></li>
				<li><a href="#tc-accounts"><?php esc_html_e( 'Customer Accounts', 'foxfire-child' ); ?></a></li>
				<li><a href="#tc-ip"><?php esc_html_e( 'Intellectual Property', 'foxfire-child' ); ?></a></li>
				<li><a href="#tc-third-party"><?php esc_html_e( 'Third-Party Services', 'foxfire-child' ); ?></a></li>
				<li><a href="#tc-disclaimers"><?php esc_html_e( 'Disclaimers', 'foxfire-child' ); ?></a></li>
				<li><a href="#tc-liability"><?php esc_html_e( 'Limitation of Liability', 'foxfire-child' ); ?></a></li>
				<li><a href="#tc-indemnification"><?php esc_html_e( 'Indemnification', 'foxfire-child' ); ?></a></li>
				<li><a href="#tc-governing-law"><?php esc_html_e( 'Governing Law', 'foxfire-child' ); ?></a></li>
				<li><a href="#tc-changes"><?php esc_html_e( 'Changes to Terms', 'foxfire-child' ); ?></a></li>
				<li><a href="#tc-contact"><?php esc_html_e( 'Contact', 'foxfire-child' ); ?></a></li>
			</ol>
		</nav>

		<div class="ff-policy-sections">

			<section id="tc-acceptance" class="ff-policy-section">
				<h2 class="ff-policy-section__heading"><?php esc_html_e( '1. Acceptance of Terms', 'foxfire-child' ); ?></h2>
				<p><?php esc_html_e( 'By accessing the Foxfire Peptides website at FoxfirePeptides.com (the "Site"), creating a customer account, or placing an order, you agree to be bound by these Terms and Conditions ("Terms"), our Privacy Policy, and our Refund and Returns Policy, each of which are incorporated into these Terms by reference.', 'foxfire-child' ); ?></p>
				<p><?php esc_html_e( 'If you do not agree with these Terms, you must not use the Site or place an order.', 'foxfire-child' ); ?></p>
				<p><?php esc_html_e( 'These Terms constitute a legally binding agreement between you and Foxfire Peptides.', 'foxfire-child' ); ?></p>
				<div class="ff-policy-placeholder-block">
					<strong><?php esc_html_e( '[CLIENT TO CONFIRM]', 'foxfire-child' ); ?></strong>
					<p><?php esc_html_e( 'Legal entity name (e.g., "Foxfire Peptides LLC").', 'foxfire-child' ); ?></p>
				</div>
			</section>

			<section id="tc-eligibility" class="ff-policy-section">
				<h2 class="ff-policy-section__heading"><?php esc_html_e( '2. Eligibility', 'foxfire-child' ); ?></h2>
				<div class="ff-policy-placeholder-block">
					<strong><?php esc_html_e( '[CLIENT TO CONFIRM — MINIMUM AGE AND ELIGIBILITY REQUIREMENTS]', 'foxfire-child' ); ?></strong>
					<p><?php esc_html_e( 'To use the Site and place orders, you must meet the minimum age requirement approved by Foxfire Peptides. You must also have the legal capacity to enter into binding contracts in your jurisdiction. By using the Site, you represent and warrant that you meet these requirements. The minimum age and any additional eligibility criteria must be confirmed by the client and legal counsel before publication.', 'foxfire-child' ); ?></p>
				</div>
			</section>

			<section id="tc-ruo" class="ff-policy-section">
				<h2 class="ff-policy-section__heading"><?php esc_html_e( '3. Research Use Only', 'foxfire-child' ); ?></h2>
				<div class="ff-policy-ruo-block">
					<p><?php esc_html_e( 'All products sold by Foxfire Peptides are supplied exclusively for legitimate laboratory and scientific research purposes ("Research Use Only" or "RUO").', 'foxfire-child' ); ?></p>
					<p><?php esc_html_e( 'Products available through this Site:', 'foxfire-child' ); ?></p>
					<ul>
						<li><?php esc_html_e( 'Are not intended for human consumption, therapeutic use, diagnostic use, veterinary use, or any non-research application', 'foxfire-child' ); ?></li>
						<li><?php esc_html_e( 'Have not been evaluated by the U.S. Food and Drug Administration (FDA) or any equivalent regulatory authority for safety or efficacy in humans or animals', 'foxfire-child' ); ?></li>
						<li><?php esc_html_e( 'Are not FDA-approved drugs, medical devices, or dietary supplements', 'foxfire-child' ); ?></li>
					</ul>
					<p><?php esc_html_e( 'By placing an order, you represent and warrant that you are a qualified researcher, scientist, or represent a legitimate research institution, and that products will be used solely for lawful research purposes.', 'foxfire-child' ); ?></p>
				</div>
				<div class="ff-policy-placeholder-block">
					<strong><?php esc_html_e( '[CLIENT TO CONFIRM]', 'foxfire-child' ); ?></strong>
					<p><?php esc_html_e( 'This RUO wording must be reviewed and approved by the client and, if required, by legal counsel before publication. Do not publish without explicit client approval.', 'foxfire-child' ); ?></p>
				</div>
			</section>

			<section id="tc-prohibited" class="ff-policy-section">
				<h2 class="ff-policy-section__heading"><?php esc_html_e( '4. Prohibited Uses', 'foxfire-child' ); ?></h2>
				<p><?php esc_html_e( 'You agree not to use products purchased from Foxfire Peptides for:', 'foxfire-child' ); ?></p>
				<ul>
					<li><?php esc_html_e( 'Any human consumption, therapeutic, or diagnostic purpose', 'foxfire-child' ); ?></li>
					<li><?php esc_html_e( 'Any veterinary or animal-use purpose', 'foxfire-child' ); ?></li>
					<li><?php esc_html_e( 'Any purpose that violates applicable law or regulation', 'foxfire-child' ); ?></li>
					<li><?php esc_html_e( 'Any unauthorized resale or redistribution', 'foxfire-child' ); ?></li>
				</ul>
				<div class="ff-policy-placeholder-block">
					<strong><?php esc_html_e( '[CLIENT TO CONFIRM]', 'foxfire-child' ); ?></strong>
					<p><?php esc_html_e( 'This list of prohibited uses must be reviewed and approved by the client and/or legal counsel. Additional restrictions may apply depending on applicable law and jurisdictions served.', 'foxfire-child' ); ?></p>
				</div>
			</section>

			<section id="tc-products" class="ff-policy-section">
				<h2 class="ff-policy-section__heading"><?php esc_html_e( '5. Product Information', 'foxfire-child' ); ?></h2>
				<p><?php esc_html_e( 'We strive to provide accurate product descriptions, images, and specifications. However, we do not warrant that product descriptions or other content on the Site are free of errors.', 'foxfire-child' ); ?></p>
				<p><?php esc_html_e( 'Product availability is subject to stock levels at the time of ordering. We reserve the right to limit quantities or discontinue products at any time.', 'foxfire-child' ); ?></p>
				<p><?php esc_html_e( 'Where available, Certificates of Analysis (COAs) are provided for informational reference on a per-batch basis. COA documentation may vary by product and lot. We make no warranty that identical COA documentation will be available for every product or every batch at all times.', 'foxfire-child' ); ?></p>
				<p><?php esc_html_e( 'Product images are representative only and may not depict exact packaging or presentation.', 'foxfire-child' ); ?></p>
			</section>

			<section id="tc-orders" class="ff-policy-section">
				<h2 class="ff-policy-section__heading"><?php esc_html_e( '6. Orders and Acceptance', 'foxfire-child' ); ?></h2>
				<p><?php esc_html_e( 'Submitting an order through the Site constitutes an offer to purchase. Your order is not confirmed until you receive an order confirmation from us.', 'foxfire-child' ); ?></p>
				<p><?php esc_html_e( 'We reserve the right to refuse, cancel, or limit any order at our sole discretion, including in cases of:', 'foxfire-child' ); ?></p>
				<ul>
					<li><?php esc_html_e( 'Suspected fraudulent, unauthorized, or abusive activity', 'foxfire-child' ); ?></li>
					<li><?php esc_html_e( 'Pricing errors on the Site', 'foxfire-child' ); ?></li>
					<li><?php esc_html_e( 'Insufficient product inventory to fulfill the order', 'foxfire-child' ); ?></li>
					<li><?php esc_html_e( 'Failure to verify payment', 'foxfire-child' ); ?></li>
					<li><?php esc_html_e( 'Violation of these Terms or applicable law', 'foxfire-child' ); ?></li>
				</ul>
				<p><?php esc_html_e( 'If your order is cancelled after payment has been received, you will be notified and a refund will be issued through the original payment method where possible.', 'foxfire-child' ); ?></p>
			</section>

			<section id="tc-pricing" class="ff-policy-section">
				<h2 class="ff-policy-section__heading"><?php esc_html_e( '7. Pricing and Payment', 'foxfire-child' ); ?></h2>
				<p><?php esc_html_e( 'All prices are displayed in US Dollars (USD) unless otherwise stated. Prices are subject to change without notice. The price applicable to your order is the price displayed at the time you place the order.', 'foxfire-child' ); ?></p>
				<p><?php esc_html_e( 'Applicable taxes, if any, will be added at checkout. Tax obligations vary by jurisdiction.', 'foxfire-child' ); ?></p>
				<p><?php esc_html_e( 'By placing an order, you represent that you are the authorized user of the payment method you provide and that the payment information is accurate. Payment must be received before your order is processed or shipped.', 'foxfire-child' ); ?></p>
				<p><?php esc_html_e( 'Third-party payment providers (such as those used for Wise, Zelle, or GCash transactions) have their own terms and conditions that apply to your use of their services. We accept no liability for issues arising from third-party payment platforms.', 'foxfire-child' ); ?></p>
				<div class="ff-policy-placeholder-block">
					<strong><?php esc_html_e( '[CLIENT TO CONFIRM]', 'foxfire-child' ); ?></strong>
					<p><?php esc_html_e( 'Final accepted payment methods and any specific payment-related requirements to be confirmed once gateway integration is complete.', 'foxfire-child' ); ?></p>
				</div>
			</section>

			<section id="tc-shipping" class="ff-policy-section">
				<h2 class="ff-policy-section__heading"><?php esc_html_e( '8. Shipping', 'foxfire-child' ); ?></h2>
				<p>
					<?php
					printf(
						/* translators: %1$s link opening tag, %2$s link closing tag */
						esc_html__( 'Shipping terms, carriers, costs, estimated delivery times, and available regions are governed by our %1$sShipping Policy%2$s. The Shipping Policy is incorporated into these Terms by reference.', 'foxfire-child' ),
						'<a href="' . esc_url( home_url( '/shipping-policy/' ) ) . '">',
						'</a>'
					);
					?>
				</p>
				<div class="ff-policy-placeholder-block">
					<strong><?php esc_html_e( '[CLIENT TO CONFIRM]', 'foxfire-child' ); ?></strong>
					<p><?php esc_html_e( 'Shipping carriers, rates, timeframes, and regions to be confirmed. Shipping Policy content will be finalized in Chunk 1O.', 'foxfire-child' ); ?></p>
				</div>
			</section>

			<section id="tc-returns" class="ff-policy-section">
				<h2 class="ff-policy-section__heading"><?php esc_html_e( '9. Returns and Refunds', 'foxfire-child' ); ?></h2>
				<p>
					<?php
					printf(
						/* translators: %1$s link opening tag, %2$s link closing tag */
						esc_html__( 'Return, refund, and cancellation terms are governed by our %1$sRefund and Returns Policy%2$s, which is incorporated into these Terms by reference. In the event of any conflict between these Terms and the Refund and Returns Policy, the Refund and Returns Policy will govern with respect to returns and refunds.', 'foxfire-child' ),
						'<a href="' . esc_url( $refund_url ) . '">',
						'</a>'
					);
					?>
				</p>
			</section>

			<section id="tc-accounts" class="ff-policy-section">
				<h2 class="ff-policy-section__heading"><?php esc_html_e( '10. Customer Accounts', 'foxfire-child' ); ?></h2>
				<p><?php esc_html_e( 'To access certain features of the Site, including order history and COA lookups, you may create a customer account. You agree to:', 'foxfire-child' ); ?></p>
				<ul>
					<li><?php esc_html_e( 'Provide accurate, current, and complete information during registration and keep it up to date', 'foxfire-child' ); ?></li>
					<li><?php esc_html_e( 'Maintain the confidentiality of your account credentials', 'foxfire-child' ); ?></li>
					<li><?php esc_html_e( 'Notify us promptly if you suspect unauthorized use of your account', 'foxfire-child' ); ?></li>
					<li><?php esc_html_e( 'Accept full responsibility for all activities conducted through your account', 'foxfire-child' ); ?></li>
				</ul>
				<p><?php esc_html_e( 'We reserve the right to suspend or terminate accounts that violate these Terms, are associated with fraudulent activity, or have been inactive for an extended period.', 'foxfire-child' ); ?></p>
			</section>

			<section id="tc-ip" class="ff-policy-section">
				<h2 class="ff-policy-section__heading"><?php esc_html_e( '11. Intellectual Property', 'foxfire-child' ); ?></h2>
				<p><?php esc_html_e( 'All content on the Site, including but not limited to the Foxfire Peptides brand name, logo, product imagery, graphic elements, original written content, and software, is owned by or licensed to Foxfire Peptides and is protected by applicable intellectual property law.', 'foxfire-child' ); ?></p>
				<p><?php esc_html_e( 'You may not reproduce, distribute, modify, create derivative works of, publicly display, or otherwise exploit any content from the Site without our express prior written permission.', 'foxfire-child' ); ?></p>
			</section>

			<section id="tc-third-party" class="ff-policy-section">
				<h2 class="ff-policy-section__heading"><?php esc_html_e( '12. Third-Party Services', 'foxfire-child' ); ?></h2>
				<p><?php esc_html_e( 'The Site may integrate or link to third-party services, including payment processors, shipping carriers, and analytics or infrastructure providers. These third-party services operate under their own terms of service and privacy policies.', 'foxfire-child' ); ?></p>
				<p><?php esc_html_e( 'We are not responsible for the practices, content, or availability of third-party services. Your use of any third-party service is at your own risk and subject to their terms.', 'foxfire-child' ); ?></p>
			</section>

			<section id="tc-disclaimers" class="ff-policy-section">
				<h2 class="ff-policy-section__heading"><?php esc_html_e( '13. Disclaimers', 'foxfire-child' ); ?></h2>
				<p><?php esc_html_e( 'The Site and all products and content are provided "as is" and "as available" without warranties of any kind, either express or implied, to the fullest extent permitted by applicable law.', 'foxfire-child' ); ?></p>
				<p><?php esc_html_e( 'We make no warranty that the Site will operate uninterrupted or error-free, that defects will be corrected, or that the Site or its servers are free of viruses or other harmful components.', 'foxfire-child' ); ?></p>
				<p><?php esc_html_e( 'Products are provided for research use only. We make no representation or warranty regarding the fitness of any product for a particular research purpose.', 'foxfire-child' ); ?></p>
			</section>

			<section id="tc-liability" class="ff-policy-section">
				<h2 class="ff-policy-section__heading"><?php esc_html_e( '14. Limitation of Liability', 'foxfire-child' ); ?></h2>
				<p><?php esc_html_e( 'To the fullest extent permitted by applicable law, Foxfire Peptides and its owners, officers, employees, agents, and affiliates shall not be liable for any indirect, incidental, special, consequential, or punitive damages arising from or in connection with your use of the Site, any products purchased through the Site, or any breach of these Terms.', 'foxfire-child' ); ?></p>
				<p><?php esc_html_e( 'In no event shall our total liability to you for all claims arising out of or related to these Terms or your use of the Site exceed the amount actually paid by you for the specific order giving rise to the claim.', 'foxfire-child' ); ?></p>
				<div class="ff-policy-placeholder-block">
					<strong><?php esc_html_e( '[CLIENT TO CONFIRM]', 'foxfire-child' ); ?></strong>
					<p><?php esc_html_e( 'This limitation of liability clause must be reviewed by the client and/or legal counsel. The liability cap and specific exclusions should be confirmed before publication.', 'foxfire-child' ); ?></p>
				</div>
			</section>

			<section id="tc-indemnification" class="ff-policy-section">
				<h2 class="ff-policy-section__heading"><?php esc_html_e( '15. Indemnification', 'foxfire-child' ); ?></h2>
				<p><?php esc_html_e( 'You agree to indemnify, defend, and hold harmless Foxfire Peptides and its owners, officers, employees, and agents from and against any claims, liabilities, damages, losses, and expenses (including reasonable attorneys\' fees) arising out of or in connection with: (a) your use of the Site or products; (b) your breach of these Terms; (c) your violation of any applicable law or third-party right; or (d) your misuse of any product purchased through the Site.', 'foxfire-child' ); ?></p>
				<div class="ff-policy-placeholder-block">
					<strong><?php esc_html_e( '[CLIENT TO CONFIRM]', 'foxfire-child' ); ?></strong>
					<p><?php esc_html_e( 'Indemnification scope and language to be reviewed by client and/or legal counsel before publication.', 'foxfire-child' ); ?></p>
				</div>
			</section>

			<section id="tc-governing-law" class="ff-policy-section">
				<h2 class="ff-policy-section__heading"><?php esc_html_e( '16. Governing Law and Jurisdiction', 'foxfire-child' ); ?></h2>
				<div class="ff-policy-placeholder-block ff-policy-placeholder-block--critical">
					<strong><?php esc_html_e( '[CLIENT / ATTORNEY TO PROVIDE — DO NOT PUBLISH WITHOUT THIS]', 'foxfire-child' ); ?></strong>
					<p><?php esc_html_e( 'The governing law, state/country of jurisdiction, and dispute resolution method must be provided by the client and/or their legal counsel. This section cannot be invented. Examples: "Laws of the State of [STATE], USA" or another jurisdiction. Dispute resolution preference (e.g., binding arbitration, courts) must also be specified.', 'foxfire-child' ); ?></p>
				</div>
			</section>

			<section id="tc-changes" class="ff-policy-section">
				<h2 class="ff-policy-section__heading"><?php esc_html_e( '17. Changes to Terms', 'foxfire-child' ); ?></h2>
				<p><?php esc_html_e( 'We reserve the right to update or modify these Terms at any time. Changes will be effective when posted to the Site with an updated "Last updated" date. We encourage you to review these Terms periodically.', 'foxfire-child' ); ?></p>
				<p><?php esc_html_e( 'Your continued use of the Site after any changes to these Terms constitutes your acceptance of the updated Terms.', 'foxfire-child' ); ?></p>
			</section>

			<section id="tc-contact" class="ff-policy-section">
				<h2 class="ff-policy-section__heading"><?php esc_html_e( '18. Contact', 'foxfire-child' ); ?></h2>
				<p><?php esc_html_e( 'If you have questions about these Terms, please contact us:', 'foxfire-child' ); ?></p>
				<div class="ff-policy-contact-block">
					<p>
						<strong>Foxfire Peptides</strong><br>
						<span class="ff-policy-placeholder"><?php esc_html_e( '[LEGAL ENTITY NAME — CLIENT TO CONFIRM]', 'foxfire-child' ); ?></span><br>
						<span class="ff-policy-placeholder"><?php esc_html_e( '[BUSINESS ADDRESS — CLIENT TO CONFIRM]', 'foxfire-child' ); ?></span><br>
						<?php esc_html_e( 'Website:', 'foxfire-child' ); ?> <a href="<?php echo esc_url( home_url( '/' ) ); ?>">FoxfirePeptides.com</a><br>
						<?php esc_html_e( 'Email:', 'foxfire-child' ); ?> <span class="ff-policy-placeholder"><?php esc_html_e( '[LEGAL CONTACT EMAIL — CLIENT TO CONFIRM]', 'foxfire-child' ); ?></span>
					</p>
				</div>
				<p><?php printf( esc_html__( 'You may also use our %1$sContact page%2$s to reach us.', 'foxfire-child' ), '<a href="' . esc_url( $contact_url ) . '">', '</a>' ); ?></p>
			</section>

		</div>

		<nav class="ff-policy-related-links" aria-label="<?php esc_attr_e( 'Related legal pages', 'foxfire-child' ); ?>">
			<p class="ff-policy-related-links__label"><?php esc_html_e( 'Related', 'foxfire-child' ); ?></p>
			<ul class="ff-policy-related-links__list">
				<li><a href="<?php echo esc_url( $privacy_url ); ?>"><?php esc_html_e( 'Privacy Policy', 'foxfire-child' ); ?></a></li>
				<li><a href="<?php echo esc_url( $refund_url ); ?>"><?php esc_html_e( 'Refund &amp; Returns Policy', 'foxfire-child' ); ?></a></li>
			</ul>
		</nav>

	</div>
</main>

<?php get_footer(); ?>
