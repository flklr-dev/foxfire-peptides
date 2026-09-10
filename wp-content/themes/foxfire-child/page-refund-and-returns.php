<?php
/**
 * Template Name: Refund & Returns Policy
 *
 * Draft refund and returns policy for Foxfire Peptides WooCommerce store.
 * Structured per POLICY_WRITING_GUIDE.md §5.
 * All [CLIENT TO CONFIRM] markers require client input before publication.
 *
 * CRITICAL: The return/refund model (all-sales-final vs. limited exceptions)
 * MUST be chosen by the client. No refund model is assumed here.
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
$privacy_url    = foxfire_get_page_url( 'privacy-policy', '/privacy-policy/' );
?>
<main id="main-content" class="ff-policy-page" tabindex="-1">
	<div class="ff-policy-page__inner">

		<header class="ff-policy-header">
			<p class="ff-policy-header__label"><?php esc_html_e( 'Legal', 'foxfire-child' ); ?></p>
			<h1 class="ff-policy-header__title"><?php esc_html_e( 'Refund &amp; Returns Policy', 'foxfire-child' ); ?></h1>
			<div class="ff-policy-header__meta">
				<span class="ff-policy-meta-item"><?php printf( esc_html__( 'Effective: %s', 'foxfire-child' ), esc_html( $effective_date ) ); ?></span>
				<span class="ff-policy-meta-sep" aria-hidden="true">&middot;</span>
				<span class="ff-policy-meta-item"><?php printf( esc_html__( 'Last updated: %s', 'foxfire-child' ), esc_html( $last_updated ) ); ?></span>
			</div>
			<div class="ff-policy-draft-notice" role="note">
				<?php esc_html_e( 'Working draft — ALL return and refund terms marked [CLIENT TO CONFIRM] require the client\'s decision before publication. Do not publish without client and legal approval.', 'foxfire-child' ); ?>
			</div>
		</header>

		<nav class="ff-policy-toc" aria-label="<?php esc_attr_e( 'Refund and Returns Policy sections', 'foxfire-child' ); ?>">
			<p class="ff-policy-toc__label"><?php esc_html_e( 'Contents', 'foxfire-child' ); ?></p>
			<ol class="ff-policy-toc__list">
				<li><a href="#rr-overview"><?php esc_html_e( 'Overview', 'foxfire-child' ); ?></a></li>
				<li><a href="#rr-returns"><?php esc_html_e( 'Returns', 'foxfire-child' ); ?></a></li>
				<li><a href="#rr-eligible"><?php esc_html_e( 'Eligible Issues', 'foxfire-child' ); ?></a></li>
				<li><a href="#rr-window"><?php esc_html_e( 'Reporting Window', 'foxfire-child' ); ?></a></li>
				<li><a href="#rr-evidence"><?php esc_html_e( 'Evidence Required', 'foxfire-child' ); ?></a></li>
				<li><a href="#rr-damaged"><?php esc_html_e( 'Damaged or Incorrect Orders', 'foxfire-child' ); ?></a></li>
				<li><a href="#rr-lost"><?php esc_html_e( 'Lost Shipments', 'foxfire-child' ); ?></a></li>
				<li><a href="#rr-cancellation"><?php esc_html_e( 'Cancellations', 'foxfire-child' ); ?></a></li>
				<li><a href="#rr-non-refundable"><?php esc_html_e( 'Non-Refundable Situations', 'foxfire-child' ); ?></a></li>
				<li><a href="#rr-method"><?php esc_html_e( 'Refund Method', 'foxfire-child' ); ?></a></li>
				<li><a href="#rr-statutory"><?php esc_html_e( 'Statutory Rights', 'foxfire-child' ); ?></a></li>
				<li><a href="#rr-contact"><?php esc_html_e( 'Contact Us', 'foxfire-child' ); ?></a></li>
			</ol>
		</nav>

		<div class="ff-policy-sections">

			<section id="rr-overview" class="ff-policy-section">
				<h2 class="ff-policy-section__heading"><?php esc_html_e( '1. Overview', 'foxfire-child' ); ?></h2>
				<p><?php esc_html_e( 'This Refund and Returns Policy explains the terms under which Foxfire Peptides handles requests for returns, refunds, and cancellations for orders placed at FoxfirePeptides.com.', 'foxfire-child' ); ?></p>
				<p><?php esc_html_e( 'Because our products are research-grade compounds supplied for laboratory purposes, all requests are reviewed on a case-by-case basis in accordance with the terms below.', 'foxfire-child' ); ?></p>
				<p>
					<?php
					printf(
						/* translators: %1$s link opening tag, %2$s link closing tag */
						esc_html__( 'This Policy should be read together with our %1$sTerms and Conditions%2$s.', 'foxfire-child' ),
						'<a href="' . esc_url( $terms_url ) . '">',
						'</a>'
					);
					?>
				</p>
			</section>

			<section id="rr-returns" class="ff-policy-section">
				<h2 class="ff-policy-section__heading"><?php esc_html_e( '2. Returns', 'foxfire-child' ); ?></h2>
				<div class="ff-policy-placeholder-block ff-policy-placeholder-block--critical">
					<strong><?php esc_html_e( '[CLIENT TO CONFIRM — RETURN POLICY CHOICE REQUIRED]', 'foxfire-child' ); ?></strong>
					<p><?php esc_html_e( 'The client must choose one of the following return policy models before publication:', 'foxfire-child' ); ?></p>
					<ul>
						<li><strong><?php esc_html_e( 'Option A — All Sales Final with Exceptions:', 'foxfire-child' ); ?></strong> <?php esc_html_e( 'No physical returns are accepted after shipment. Returns are only considered for verified fulfillment errors, damaged shipments, or missing items. This is the most common model in the research chemical and peptide supply industry.', 'foxfire-child' ); ?></li>
						<li><strong><?php esc_html_e( 'Option B — Limited Returns Window:', 'foxfire-child' ); ?></strong> <?php esc_html_e( 'Returns may be accepted within a defined window under specific conditions confirmed by the client and legal counsel.', 'foxfire-child' ); ?></li>
						<li><strong><?php esc_html_e( 'Option C — Client-Defined Policy:', 'foxfire-child' ); ?></strong> <?php esc_html_e( 'Other terms as defined and approved by the client.', 'foxfire-child' ); ?></li>
					</ul>
					<p><?php esc_html_e( 'The specific language and conditions of the chosen model will replace this placeholder block before the page is published.', 'foxfire-child' ); ?></p>
				</div>
			</section>

			<section id="rr-eligible" class="ff-policy-section">
				<h2 class="ff-policy-section__heading"><?php esc_html_e( '3. Eligible Issues', 'foxfire-child' ); ?></h2>
				<p><?php esc_html_e( 'The following are potential eligible issues that may qualify for review, subject to client confirmation:', 'foxfire-child' ); ?></p>
				<ul>
					<li><?php esc_html_e( 'Wrong product received (fulfillment error on our part)', 'foxfire-child' ); ?></li>
					<li><?php esc_html_e( 'Shipment damaged in transit with carrier documentation', 'foxfire-child' ); ?></li>
					<li><?php esc_html_e( 'Item(s) missing from order', 'foxfire-child' ); ?></li>
					<li><?php esc_html_e( 'Verified fulfillment error', 'foxfire-child' ); ?></li>
					<li><?php esc_html_e( 'Lost shipment (subject to carrier investigation)', 'foxfire-child' ); ?></li>
				</ul>
				<div class="ff-policy-placeholder-block">
					<strong><?php esc_html_e( '[CLIENT TO CONFIRM]', 'foxfire-child' ); ?></strong>
					<p><?php esc_html_e( 'This list of eligible issues requires client approval. Additional conditions or exclusions may apply.', 'foxfire-child' ); ?></p>
				</div>
			</section>

			<section id="rr-window" class="ff-policy-section">
				<h2 class="ff-policy-section__heading"><?php esc_html_e( '4. Reporting Window', 'foxfire-child' ); ?></h2>
				<div class="ff-policy-placeholder-block ff-policy-placeholder-block--critical">
					<strong><?php esc_html_e( '[CLIENT TO CONFIRM — REPORTING WINDOW REQUIRED]', 'foxfire-child' ); ?></strong>
					<p><?php esc_html_e( 'The client must specify the number of days within which a customer must report an eligible issue from the date of delivery. Common industry practice ranges from 24 hours to 14 days, but there is no universal standard. Do not publish a timeframe without client approval.', 'foxfire-child' ); ?></p>
				</div>
				<p><?php esc_html_e( 'Issues reported outside the approved window may not be eligible for a refund or replacement.', 'foxfire-child' ); ?></p>
			</section>

			<section id="rr-evidence" class="ff-policy-section">
				<h2 class="ff-policy-section__heading"><?php esc_html_e( '5. Evidence Required', 'foxfire-child' ); ?></h2>
				<p><?php esc_html_e( 'To initiate a claim for a damaged, incorrect, or missing order, you may be required to provide:', 'foxfire-child' ); ?></p>
				<ul>
					<li><?php esc_html_e( 'Your Foxfire Peptides order number', 'foxfire-child' ); ?></li>
					<li><?php esc_html_e( 'Clear photographs of the received product, packaging, and any visible damage', 'foxfire-child' ); ?></li>
					<li><?php esc_html_e( 'A written description of the issue', 'foxfire-child' ); ?></li>
					<li><?php esc_html_e( 'Tracking information for the shipment', 'foxfire-child' ); ?></li>
				</ul>
				<div class="ff-policy-placeholder-block">
					<strong><?php esc_html_e( '[CLIENT TO CONFIRM]', 'foxfire-child' ); ?></strong>
					<p><?php esc_html_e( 'Exact documentation requirements to be approved by the client. Additional information may be requested during the review process.', 'foxfire-child' ); ?></p>
				</div>
			</section>

			<section id="rr-damaged" class="ff-policy-section">
				<h2 class="ff-policy-section__heading"><?php esc_html_e( '6. Damaged or Incorrect Orders', 'foxfire-child' ); ?></h2>
				<p><?php esc_html_e( 'If you receive a damaged or incorrect item, please contact our support team within the reporting window specified above. Include your order number and the required evidence described in Section 5.', 'foxfire-child' ); ?></p>
				<p><?php esc_html_e( 'We will review your claim and notify you of our determination. Approved claims may be resolved through:', 'foxfire-child' ); ?></p>
				<ul>
					<li><?php esc_html_e( 'Replacement of the affected item(s)', 'foxfire-child' ); ?></li>
					<li><?php esc_html_e( 'Reshipment of the order', 'foxfire-child' ); ?></li>
					<li><?php esc_html_e( 'Store credit', 'foxfire-child' ); ?></li>
					<li><?php esc_html_e( 'Refund to the original payment method', 'foxfire-child' ); ?></li>
				</ul>
				<div class="ff-policy-placeholder-block">
					<strong><?php esc_html_e( '[CLIENT TO CONFIRM]', 'foxfire-child' ); ?></strong>
					<p><?php esc_html_e( 'The available resolution options and order of preference must be confirmed by the client. Not all options above may apply.', 'foxfire-child' ); ?></p>
				</div>
			</section>

			<section id="rr-lost" class="ff-policy-section">
				<h2 class="ff-policy-section__heading"><?php esc_html_e( '7. Lost Shipments', 'foxfire-child' ); ?></h2>
				<p><?php esc_html_e( 'If your shipment tracking shows a delay or the package has not arrived within the expected timeframe, please contact us so we can initiate an investigation with the carrier.', 'foxfire-child' ); ?></p>
				<p><?php esc_html_e( 'Resolution of lost shipment claims is subject to the outcome of the carrier investigation and applicable carrier policies.', 'foxfire-child' ); ?></p>
				<div class="ff-policy-placeholder-block">
					<strong><?php esc_html_e( '[CLIENT TO CONFIRM]', 'foxfire-child' ); ?></strong>
					<p><?php esc_html_e( 'Lost shipment resolution (replacement, refund, or other action) to be confirmed once shipping carrier(s) and policies are finalized in Chunk 1O.', 'foxfire-child' ); ?></p>
				</div>
			</section>

			<section id="rr-cancellation" class="ff-policy-section">
				<h2 class="ff-policy-section__heading"><?php esc_html_e( '8. Cancellations', 'foxfire-child' ); ?></h2>
				<div class="ff-policy-placeholder-block ff-policy-placeholder-block--critical">
					<strong><?php esc_html_e( '[CLIENT TO CONFIRM — CANCELLATION RULES REQUIRED]', 'foxfire-child' ); ?></strong>
					<p><?php esc_html_e( 'The client must specify cancellation rules for each stage:', 'foxfire-child' ); ?></p>
					<ul>
						<li><?php esc_html_e( 'Before payment is received', 'foxfire-child' ); ?></li>
						<li><?php esc_html_e( 'After payment is received but before processing begins', 'foxfire-child' ); ?></li>
						<li><?php esc_html_e( 'After processing has begun but before shipment', 'foxfire-child' ); ?></li>
						<li><?php esc_html_e( 'After the order has been shipped', 'foxfire-child' ); ?></li>
					</ul>
					<p><?php esc_html_e( 'Do not publish cancellation rules without explicit client confirmation for each stage.', 'foxfire-child' ); ?></p>
				</div>
			</section>

			<section id="rr-non-refundable" class="ff-policy-section">
				<h2 class="ff-policy-section__heading"><?php esc_html_e( '9. Non-Refundable Situations', 'foxfire-child' ); ?></h2>
				<div class="ff-policy-placeholder-block">
					<strong><?php esc_html_e( '[CLIENT TO CONFIRM — NON-REFUNDABLE EXCLUSIONS REQUIRED]', 'foxfire-child' ); ?></strong>
					<p><?php esc_html_e( 'The following situations are commonly excluded by research-use-only product sellers, but each must be individually confirmed by the client before being included in this policy:', 'foxfire-child' ); ?></p>
					<ul>
						<li><?php esc_html_e( 'Change of mind after order placement', 'foxfire-child' ); ?></li>
						<li><?php esc_html_e( 'Incorrect shipping address entered by the customer', 'foxfire-child' ); ?></li>
						<li><?php esc_html_e( 'Product damaged due to improper storage or handling after delivery', 'foxfire-child' ); ?></li>
						<li><?php esc_html_e( 'Opened, used, or altered product', 'foxfire-child' ); ?></li>
						<li><?php esc_html_e( 'Customer refusal of delivery', 'foxfire-child' ); ?></li>
					</ul>
					<p><?php esc_html_e( 'This list will be replaced with the client-approved exclusions before publication.', 'foxfire-child' ); ?></p>
				</div>
			</section>

			<section id="rr-method" class="ff-policy-section">
				<h2 class="ff-policy-section__heading"><?php esc_html_e( '10. Refund Method', 'foxfire-child' ); ?></h2>
				<p><?php esc_html_e( 'Where a refund is approved, we will return the applicable amount through the original payment method used for the order, where technically possible and operationally feasible.', 'foxfire-child' ); ?></p>
				<div class="ff-policy-placeholder-block">
					<strong><?php esc_html_e( '[CLIENT TO CONFIRM]', 'foxfire-child' ); ?></strong>
					<p><?php esc_html_e( 'The refund process for alternative payment methods (Wise, Zelle, GCash) must be defined once the payment workflow is confirmed and integrated (Chunk 1N). Timing and processing periods for refunds to be confirmed.', 'foxfire-child' ); ?></p>
				</div>
			</section>

			<section id="rr-statutory" class="ff-policy-section">
				<h2 class="ff-policy-section__heading"><?php esc_html_e( '11. Statutory Rights', 'foxfire-child' ); ?></h2>
				<p><?php esc_html_e( 'Nothing in this Policy is intended to limit or exclude any rights you may have under applicable consumer protection law that cannot legally be waived or excluded. Where statutory rights apply, those rights are preserved in addition to the terms set out in this Policy.', 'foxfire-child' ); ?></p>
			</section>

			<section id="rr-contact" class="ff-policy-section">
				<h2 class="ff-policy-section__heading"><?php esc_html_e( '12. Contact Us', 'foxfire-child' ); ?></h2>
				<p><?php esc_html_e( 'To report an issue, request a refund review, or ask a question about an order, please contact our support team:', 'foxfire-child' ); ?></p>
				<div class="ff-policy-contact-block">
					<p>
						<strong>Foxfire Peptides</strong><br>
						<?php esc_html_e( 'Website:', 'foxfire-child' ); ?> <a href="<?php echo esc_url( home_url( '/' ) ); ?>">FoxfirePeptides.com</a><br>
						<?php esc_html_e( 'Email:', 'foxfire-child' ); ?> <span class="ff-policy-placeholder"><?php esc_html_e( '[SUPPORT CONTACT EMAIL — CLIENT TO CONFIRM]', 'foxfire-child' ); ?></span>
					</p>
				</div>
				<p><?php printf( esc_html__( 'You may also reach us through our %1$sContact page%2$s.', 'foxfire-child' ), '<a href="' . esc_url( $contact_url ) . '">', '</a>' ); ?></p>
				<p><?php esc_html_e( 'Please include your order number and a description of the issue when contacting us to ensure a prompt response.', 'foxfire-child' ); ?></p>
			</section>

		</div>

		<nav class="ff-policy-related-links" aria-label="<?php esc_attr_e( 'Related legal pages', 'foxfire-child' ); ?>">
			<p class="ff-policy-related-links__label"><?php esc_html_e( 'Related', 'foxfire-child' ); ?></p>
			<ul class="ff-policy-related-links__list">
				<li><a href="<?php echo esc_url( $terms_url ); ?>"><?php esc_html_e( 'Terms &amp; Conditions', 'foxfire-child' ); ?></a></li>
				<li><a href="<?php echo esc_url( $privacy_url ); ?>"><?php esc_html_e( 'Privacy Policy', 'foxfire-child' ); ?></a></li>
			</ul>
		</nav>

	</div>
</main>

<?php get_footer(); ?>
