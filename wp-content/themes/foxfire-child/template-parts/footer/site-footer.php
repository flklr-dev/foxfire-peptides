<?php
/**
 * Site footer — charcoal shell with secondary links (Chunk 1D).
 *
 * @package Foxfire_Child
 */

defined( 'ABSPATH' ) || exit;

$shop_url    = foxfire_get_shop_url();
$testing_url = foxfire_get_page_url( 'testing-coa', '/testing-coa/' );
$about_url   = foxfire_get_page_url( 'about', '/about/' );
$contact_url = foxfire_get_page_url( 'contact', '/contact/' );
$faq_url     = foxfire_get_page_url( 'faq', '/faq/' );
$terms_url   = foxfire_get_page_url( 'terms-and-conditions', '/terms-and-conditions/' );
$privacy_url = foxfire_get_page_url( 'privacy-policy', '/privacy-policy/' );
$refund_url  = foxfire_get_page_url( 'refund-and-returns-policy', '/refund-and-returns-policy/' );
$shipping_url = foxfire_get_page_url( 'shipping-policy', '/shipping-policy/' );
$year        = (string) gmdate( 'Y' );
$footer_tagline = foxfire_get_managed_content( 'footer_tagline', __( 'Quality research peptides with transparent testing information.', 'foxfire-child' ) );
$research_notice = foxfire_get_managed_content( 'footer_research_notice', __( 'For research use only. Not for human consumption.', 'foxfire-child' ) );
$support_email = foxfire_get_managed_content( 'support_email', 'support@foxfirepeptides.com' );
$support_phone = foxfire_get_managed_content( 'support_phone', '' );
$support_phone_uri = preg_replace( '/[^0-9+]/', '', $support_phone );
$contact_hours = foxfire_get_managed_content( 'contact_hours', __( 'Mon – Fri, 9 AM – 5 PM EST', 'foxfire-child' ) );
?>

<div class="ff-site-footer">
	<div class="ff-site-footer__inner">
		<div class="ff-site-footer__brand">
			<p class="ff-site-footer__name"><?php bloginfo( 'name' ); ?></p>
			<p class="ff-site-footer__tagline">
				<?php echo esc_html( $footer_tagline ); ?>
			</p>
		</div>

		<nav class="ff-site-footer__nav" aria-label="<?php esc_attr_e( 'Footer', 'foxfire-child' ); ?>">
			<ul class="ff-site-footer__links">
				<li><a href="<?php echo esc_url( $shop_url ); ?>"><?php esc_html_e( 'Shop', 'foxfire-child' ); ?></a></li>
				<li><a href="<?php echo esc_url( $testing_url ); ?>"><?php esc_html_e( 'Testing/COA', 'foxfire-child' ); ?></a></li>
				<li><a href="<?php echo esc_url( $about_url ); ?>"><?php esc_html_e( 'About', 'foxfire-child' ); ?></a></li>
				<li><a href="<?php echo esc_url( $faq_url ); ?>"><?php esc_html_e( 'FAQ', 'foxfire-child' ); ?></a></li>
				<li><a href="<?php echo esc_url( $contact_url ); ?>"><?php esc_html_e( 'Contact', 'foxfire-child' ); ?></a></li>
			</ul>
		</nav>

		<div class="ff-site-footer__policies">
			<p class="ff-site-footer__section-label"><?php esc_html_e( 'Legal', 'foxfire-child' ); ?></p>
			<ul class="ff-site-footer__links">
				<li>
					<a href="<?php echo esc_url( $terms_url ); ?>">
						<?php esc_html_e( 'Terms & Conditions', 'foxfire-child' ); ?>
					</a>
				</li>
				<li>
					<a href="<?php echo esc_url( $privacy_url ); ?>">
						<?php esc_html_e( 'Privacy Policy', 'foxfire-child' ); ?>
					</a>
				</li>
				<li>
					<a href="<?php echo esc_url( $refund_url ); ?>">
						<?php esc_html_e( 'Refund & Returns', 'foxfire-child' ); ?>
					</a>
				</li>
				<li>
					<a href="<?php echo esc_url( $shipping_url ); ?>">
						<?php esc_html_e( 'Shipping Policy', 'foxfire-child' ); ?>
					</a>
				</li>
			</ul>
		</div>

		<div class="ff-site-footer__contact">
			<p class="ff-site-footer__section-label"><?php esc_html_e( 'Contact', 'foxfire-child' ); ?></p>
			<p class="ff-site-footer__contact-link">
				<a href="mailto:<?php echo esc_attr( $support_email ); ?>"><?php echo esc_html( $support_email ); ?></a>
			</p>
			<?php if ( '' !== $support_phone && '' !== $support_phone_uri ) : ?>
				<p class="ff-site-footer__contact-link"><a href="tel:<?php echo esc_attr( $support_phone_uri ); ?>"><?php echo esc_html( $support_phone ); ?></a></p>
			<?php endif; ?>
			<p class="ff-site-footer__contact-placeholder"><?php echo esc_html( $contact_hours ); ?></p>
			<p class="ff-site-footer__contact-link"><a href="<?php echo esc_url( $contact_url ); ?>"><?php esc_html_e( 'Contact form', 'foxfire-child' ); ?></a></p>
		</div>
	</div>

	<div class="ff-site-footer__bottom">
		<p class="ff-site-footer__copyright">
			<?php
			printf(
				/* translators: 1: year, 2: site name */
				esc_html__( '© %1$s %2$s. All rights reserved.', 'foxfire-child' ),
				esc_html( $year ),
				esc_html( get_bloginfo( 'name' ) )
			);
			?>
		</p>
		<p class="ff-site-footer__research-note">
			<?php echo esc_html( $research_notice ); ?>
		</p>
	</div>
</div>
