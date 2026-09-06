<?php
/**
 * Template Name: Shipping Policy
 *
 * Draft Shipping Policy for Foxfire Peptides WooCommerce store.
 * Structured per standard e-commerce and research compound guidelines.
 * All [CLIENT TO CONFIRM] markers require client input before publication.
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
$refund_url     = foxfire_get_page_url( 'refund-and-returns-policy', '/refund-and-returns-policy/' );
$terms_url      = foxfire_get_page_url( 'terms-and-conditions', '/terms-and-conditions/' );
?>
<main id="main-content" class="ff-policy-page" tabindex="-1">
	<div class="ff-policy-page__inner">

		<header class="ff-policy-header">
			<p class="ff-policy-header__label"><?php esc_html_e( 'Legal', 'foxfire-child' ); ?></p>
			<h1 class="ff-policy-header__title"><?php esc_html_e( 'Shipping Policy', 'foxfire-child' ); ?></h1>
			<div class="ff-policy-header__meta">
				<span class="ff-policy-meta-item"><?php printf( esc_html__( 'Effective: %s', 'foxfire-child' ), esc_html( $effective_date ) ); ?></span>
				<span class="ff-policy-meta-sep" aria-hidden="true">&middot;</span>
				<span class="ff-policy-meta-item"><?php printf( esc_html__( 'Last updated: %s', 'foxfire-child' ), esc_html( $last_updated ) ); ?></span>
			</div>
			<div class="ff-policy-draft-notice" role="note">
				<?php esc_html_e( 'Working draft — Carrier rates, fulfillment cutoff times, and delivery policies marked [CLIENT TO CONFIRM] require client decision before publication. Do not publish without client approval.', 'foxfire-child' ); ?>
			</div>
		</header>

		<nav class="ff-policy-toc" aria-label="<?php esc_attr_e( 'Shipping Policy sections', 'foxfire-child' ); ?>">
			<p class="ff-policy-toc__label"><?php esc_html_e( 'Contents', 'foxfire-child' ); ?></p>
			<ol class="ff-policy-toc__list">
				<li><a href="#sp-overview"><?php esc_html_e( 'Overview &amp; Scope', 'foxfire-child' ); ?></a></li>
				<li><a href="#sp-processing"><?php esc_html_e( 'Order Processing &amp; Handling', 'foxfire-child' ); ?></a></li>
				<li><a href="#sp-methods"><?php esc_html_e( 'Shipping Methods &amp; Transit Times', 'foxfire-child' ); ?></a></li>
				<li><a href="#sp-rates"><?php esc_html_e( 'Shipping Rates &amp; Free Shipping', 'foxfire-child' ); ?></a></li>
				<li><a href="#sp-tracking"><?php esc_html_e( 'Tracking &amp; Delivery Confirmation', 'foxfire-child' ); ?></a></li>
				<li><a href="#sp-address"><?php esc_html_e( 'Shipping Address Accuracy', 'foxfire-child' ); ?></a></li>
				<li><a href="#sp-undeliverable"><?php esc_html_e( 'Undeliverable &amp; Returned Packages', 'foxfire-child' ); ?></a></li>
				<li><a href="#sp-lost-delayed"><?php esc_html_e( 'Lost, Delayed, or Missing Shipments', 'foxfire-child' ); ?></a></li>
				<li><a href="#sp-damaged"><?php esc_html_e( 'Damaged Shipments', 'foxfire-child' ); ?></a></li>
				<li><a href="#sp-packaging"><?php esc_html_e( 'Packaging &amp; Product Stability', 'foxfire-child' ); ?></a></li>
				<li><a href="#sp-geographic"><?php esc_html_e( 'Geographic Restrictions', 'foxfire-child' ); ?></a></li>
				<li><a href="#sp-contact"><?php esc_html_e( 'Shipping Support &amp; Inquiries', 'foxfire-child' ); ?></a></li>
			</ol>
		</nav>

		<!-- 1. Overview -->
		<section id="sp-overview" class="ff-policy-section">
			<h2><?php esc_html_e( '1. Overview &amp; Scope', 'foxfire-child' ); ?></h2>
			<p>
				<?php esc_html_e( 'This Shipping Policy outlines the terms and conditions governing the shipment and delivery of research compounds ordered from Foxfire Peptides via foxfirepeptides.com. By completing an order, you agree to the handling and shipping practices described herein.', 'foxfire-child' ); ?>
			</p>
			<p>
				<?php esc_html_e( 'All compounds offered by Foxfire Peptides are supplied strictly for laboratory research and analytical purposes in accordance with our Terms &amp; Conditions.', 'foxfire-child' ); ?>
			</p>
		</section>

		<!-- 2. Order Processing -->
		<section id="sp-processing" class="ff-policy-section">
			<h2><?php esc_html_e( '2. Order Processing &amp; Handling', 'foxfire-child' ); ?></h2>
			<p>
				<?php esc_html_e( 'Orders are processed Monday through Friday, excluding standard national and postal holidays.', 'foxfire-child' ); ?>
			</p>
			<ul>
				<li>
					<strong><?php esc_html_e( 'Handling Time:', 'foxfire-child' ); ?></strong>
					<?php esc_html_e( 'Most in-stock orders are prepared and dispatched within ', 'foxfire-child' ); ?>
					<span class="ff-policy-placeholder">[CLIENT TO CONFIRM: e.g., 1–2 business days]</span>
					<?php esc_html_e( ' of confirmed payment.', 'foxfire-child' ); ?>
				</li>
				<li>
					<strong><?php esc_html_e( 'Daily Cutoff Time:', 'foxfire-child' ); ?></strong>
					<?php esc_html_e( 'Orders placed before ', 'foxfire-child' ); ?>
					<span class="ff-policy-placeholder">[CLIENT TO CONFIRM: e.g., 2:00 PM EST]</span>
					<?php esc_html_e( ' on a business day typically begin fulfillment the same day. Orders placed after the daily cutoff, over the weekend, or during recognized holidays begin processing on the following business day.', 'foxfire-child' ); ?>
				</li>
				<li>
					<strong><?php esc_html_e( 'Payment Verification:', 'foxfire-child' ); ?></strong>
					<?php esc_html_e( 'Orders paid via manual methods (e.g., Wise, Zelle, or GCash) will begin processing once payment confirmation has been verified by our team.', 'foxfire-child' ); ?>
				</li>
			</ul>
		</section>

		<!-- 3. Methods & Transit Times -->
		<section id="sp-methods" class="ff-policy-section">
			<h2><?php esc_html_e( '3. Shipping Methods &amp; Transit Times', 'foxfire-child' ); ?></h2>
			<p>
				<?php esc_html_e( 'We partner with reputable commercial carriers to ensure swift and secure transit:', 'foxfire-child' ); ?>
			</p>
			<ul>
				<li>
					<strong><?php esc_html_e( 'Standard Domestic Shipping:', 'foxfire-child' ); ?></strong>
					<span class="ff-policy-placeholder">[CLIENT TO CONFIRM: e.g., USPS Ground Advantage / Priority Mail (approx. 2–5 business days)]</span>
				</li>
				<li>
					<strong><?php esc_html_e( 'Expedited Domestic Shipping:', 'foxfire-child' ); ?></strong>
					<span class="ff-policy-placeholder">[CLIENT TO CONFIRM: e.g., UPS 2nd Day Air / USPS Priority Express (approx. 1–2 business days, where available)]</span>
				</li>
			</ul>
			<p>
				<?php esc_html_e( 'Please note that transit times are carrier estimates and are not legally binding guarantees. Severe weather events, carrier network congestion, natural disasters, or remote delivery locations may cause occasional transit delays outside of our control.', 'foxfire-child' ); ?>
			</p>
		</section>

		<!-- 4. Rates & Free Shipping -->
		<section id="sp-rates" class="ff-policy-section">
			<h2><?php esc_html_e( '4. Shipping Rates &amp; Free Shipping', 'foxfire-child' ); ?></h2>
			<p>
				<?php esc_html_e( 'Shipping fees are calculated during checkout based on destination and selected shipping speed:', 'foxfire-child' ); ?>
			</p>
			<ul>
				<li>
					<strong><?php esc_html_e( 'Standard Flat Rate:', 'foxfire-child' ); ?></strong>
					<?php esc_html_e( 'Standard shipping is billed at a flat rate of ', 'foxfire-child' ); ?>
					<span class="ff-policy-placeholder">[CLIENT TO CONFIRM: e.g., $9.95 / $12.00]</span>.
				</li>
				<li>
					<strong><?php esc_html_e( 'Free Shipping Qualification:', 'foxfire-child' ); ?></strong>
					<?php esc_html_e( 'Foxfire Peptides offers free standard shipping on all qualifying orders over ', 'foxfire-child' ); ?>
					<span class="ff-policy-placeholder">[CLIENT TO CONFIRM: e.g., $150.00 / $200.00]</span>
					<?php esc_html_e( ' (subtotal calculated after any promotional discounts and before applicable sales taxes).', 'foxfire-child' ); ?>
				</li>
			</ul>
		</section>

		<!-- 5. Tracking -->
		<section id="sp-tracking" class="ff-policy-section">
			<h2><?php esc_html_e( '5. Tracking &amp; Delivery Confirmation', 'foxfire-child' ); ?></h2>
			<p>
				<?php esc_html_e( 'Once your package is packed and labeled for dispatch, an automated shipment confirmation email containing your carrier tracking number is sent to the email address provided at checkout.', 'foxfire-child' ); ?>
			</p>
			<p>
				<?php esc_html_e( 'Please allow up to 24 hours after receiving your tracking link for carrier scan updates to appear in the tracking portal. Registered account holders can also review tracking information directly in their Customer Account Hub.', 'foxfire-child' ); ?>
			</p>
		</section>

		<!-- 6. Address Accuracy -->
		<section id="sp-address" class="ff-policy-section">
			<h2><?php esc_html_e( '6. Shipping Address Accuracy', 'foxfire-child' ); ?></h2>
			<p>
				<?php esc_html_e( 'Customers are solely responsible for ensuring the accuracy and completeness of their delivery address (including apartment, suite, or unit numbers) prior to placing an order.', 'foxfire-child' ); ?>
			</p>
			<p>
				<?php esc_html_e( 'If you detect an error in your shipping address, please contact support immediately. While we will make every reasonable effort to adjust destination details before package handover, address modifications cannot be guaranteed once order processing has commenced and are impossible after dispatch.', 'foxfire-child' ); ?>
			</p>
		</section>

		<!-- 7. Undeliverable & Returned -->
		<section id="sp-undeliverable" class="ff-policy-section">
			<h2><?php esc_html_e( '7. Undeliverable &amp; Returned Packages', 'foxfire-child' ); ?></h2>
			<p>
				<?php esc_html_e( 'If a parcel is returned to Foxfire Peptides by the carrier due to an incomplete, inaccurate, or outdated address provided by the customer, or because the package was refused or unclaimed:', 'foxfire-child' ); ?>
			</p>
			<ul>
				<li>
					<?php esc_html_e( 'The customer will be contacted upon receipt of the returned shipment.', 'foxfire-child' ); ?>
				</li>
				<li>
					<span class="ff-policy-placeholder">[CLIENT TO CONFIRM: Customer is responsible for return-to-sender carrier fees and reshipment postage before the order is re-dispatched, OR order is refunded minus actual shipping expenses].</span>
				</li>
			</ul>
		</section>

		<!-- 8. Lost or Delayed -->
		<section id="sp-lost-delayed" class="ff-policy-section">
			<h2><?php esc_html_e( '8. Lost, Delayed, or Missing Shipments', 'foxfire-child' ); ?></h2>
			<p>
				<?php esc_html_e( 'While rare, shipment anomalies can occur during carrier handling. If your package appears stuck in transit or is marked delivered but not received:', 'foxfire-child' ); ?>
			</p>
			<ul>
				<li>
					<strong><?php esc_html_e( 'In-Transit Inactivity:', 'foxfire-child' ); ?></strong>
					<?php esc_html_e( 'If your tracking number shows no movement for more than ', 'foxfire-child' ); ?>
					<span class="ff-policy-placeholder">[CLIENT TO CONFIRM: e.g., 7 consecutive business days]</span>,
					<?php esc_html_e( ' contact support so we can initiate a trace investigation with the shipping carrier.', 'foxfire-child' ); ?>
				</li>
				<li>
					<strong><?php esc_html_e( 'Marked Delivered but Not Found:', 'foxfire-child' ); ?></strong>
					<?php esc_html_e( 'Carriers occasionally mark packages delivered up to 24 hours prior to physical drop-off. Please check with household members, parcel lockers, mailrooms, and neighboring areas. If still missing after 24 hours, notify our support team.', 'foxfire-child' ); ?>
				</li>
				<li>
					<strong><?php esc_html_e( 'Resolution Policy:', 'foxfire-child' ); ?></strong>
					<span class="ff-policy-placeholder">[CLIENT TO CONFIRM: Replacement shipment issued after carrier investigation concludes / Carrier claim procedure].</span>
				</li>
			</ul>
		</section>

		<!-- 9. Damaged Shipments -->
		<section id="sp-damaged" class="ff-policy-section">
			<h2><?php esc_html_e( '9. Damaged Shipments', 'foxfire-child' ); ?></h2>
			<p>
				<?php esc_html_e( 'If your parcel arrives with visible exterior damage or damaged contents:', 'foxfire-child' ); ?>
			</p>
			<ol>
				<li><?php esc_html_e( 'Retain all original packaging materials, exterior boxes, cushioning, and shipping labels.', 'foxfire-child' ); ?></li>
				<li><?php esc_html_e( 'Take clear, well-lit photographs documenting the parcel condition, outer label, and any compromised vials.', 'foxfire-child' ); ?></li>
				<li>
					<?php esc_html_e( 'Contact customer support within ', 'foxfire-child' ); ?>
					<span class="ff-policy-placeholder">[CLIENT TO CONFIRM: e.g., 48 hours / 3 business days]</span>
					<?php esc_html_e( ' of carrier delivery timestamp.', 'foxfire-child' ); ?>
				</li>
			</ol>
			<p>
				<?php printf(
					/* translators: %s: Refund and returns policy link */
					esc_html__( 'Please consult our %s for full guidelines regarding damage verification, replacements, and store credit.', 'foxfire-child' ),
					sprintf( '<a href="%s">%s</a>', esc_url( $refund_url ), esc_html__( 'Refund &amp; Returns Policy', 'foxfire-child' ) )
				); ?>
			</p>
		</section>

		<!-- 10. Packaging & Stability -->
		<section id="sp-packaging" class="ff-policy-section">
			<h2><?php esc_html_e( '10. Packaging &amp; Product Stability', 'foxfire-child' ); ?></h2>
			<p>
				<?php esc_html_e( 'Foxfire Peptides packages all orders with strict attention to compound integrity and customer privacy:', 'foxfire-child' ); ?>
			</p>
			<ul>
				<li>
					<strong><?php esc_html_e( 'Discreet Packaging:', 'foxfire-child' ); ?></strong>
					<?php esc_html_e( 'All orders are dispatched in plain, sturdy exterior mailers or cartons with neutral sender information. There are no markings or labels indicating the specific compound contents on the exterior of the parcel.', 'foxfire-child' ); ?>
				</li>
				<li>
					<strong><?php esc_html_e( 'Transit Stability:', 'foxfire-child' ); ?></strong>
					<?php esc_html_e( 'Our research peptides are synthesized and distributed in lyophilized (freeze-dried) powder form, which exhibits high stability at ambient temperatures during standard commercial transit.', 'foxfire-child' ); ?>
				</li>
				<li>
					<strong><?php esc_html_e( 'Receiving Storage:', 'foxfire-child' ); ?></strong>
					<?php esc_html_e( 'Upon receipt, research materials should be inspected and immediately stored in accordance with product specifications (typically stored in a dark, dry environment between 2°C to 8°C for short term, or -20°C for extended research storage).', 'foxfire-child' ); ?>
				</li>
			</ul>
		</section>

		<!-- 11. Geographic Restrictions -->
		<section id="sp-geographic" class="ff-policy-section">
			<h2><?php esc_html_e( '11. Geographic Restrictions', 'foxfire-child' ); ?></h2>
			<p>
				<?php esc_html_e( 'Foxfire Peptides currently services the following geographical areas:', 'foxfire-child' ); ?>
			</p>
			<ul>
				<li>
					<strong><?php esc_html_e( 'Domestic Coverage:', 'foxfire-child' ); ?></strong>
					<span class="ff-policy-placeholder">[CLIENT TO CONFIRM: Shipping to all 50 US States, including Alaska, Hawaii, and US Territories / Any state restrictions].</span>
				</li>
				<li>
					<strong><?php esc_html_e( 'Military / P.O. Boxes:', 'foxfire-child' ); ?></strong>
					<span class="ff-policy-placeholder">[CLIENT TO CONFIRM: APO/FPO and P.O. Box delivery permitted via USPS].</span>
				</li>
				<li>
					<strong><?php esc_html_e( 'International Shipping:', 'foxfire-child' ); ?></strong>
					<span class="ff-policy-placeholder">[CLIENT TO CONFIRM: Currently Domestic US only, OR International shipping terms, customs declarations, and customer import duties responsibilities].</span>
				</li>
			</ul>
		</section>

		<!-- 12. Contact -->
		<section id="sp-contact" class="ff-policy-section">
			<h2><?php esc_html_e( '12. Shipping Support &amp; Inquiries', 'foxfire-child' ); ?></h2>
			<p>
				<?php esc_html_e( 'For assistance regarding transit status, tracking inquiries, or delivery updates, please contact our support team:', 'foxfire-child' ); ?>
			</p>
			<ul class="ff-policy-contact-list">
				<li>
					<strong><?php esc_html_e( 'Email:', 'foxfire-child' ); ?></strong>
					<span class="ff-policy-placeholder">[CLIENT TO CONFIRM: e.g., shipping@foxfirepeptides.com / support@foxfirepeptides.com]</span>
				</li>
				<li>
					<strong><?php esc_html_e( 'Support Hours:', 'foxfire-child' ); ?></strong>
					<span class="ff-policy-placeholder">[CLIENT TO CONFIRM: Monday – Friday, 9:00 AM – 5:00 PM EST]</span>
				</li>
				<li>
					<strong><?php printf(
						/* translators: %s: Contact page link */
						esc_html__( 'Online Contact: %s', 'foxfire-child' ),
						sprintf( '<a href="%s">%s</a>', esc_url( $contact_url ), esc_html__( 'Contact Form', 'foxfire-child' ) )
					); ?></strong>
				</li>
			</ul>
		</section>

	</div>
</main>
<?php
get_footer();
