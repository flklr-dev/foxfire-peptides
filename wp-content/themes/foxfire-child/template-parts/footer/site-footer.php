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
$shipping_url = foxfire_get_page_url( 'shipping-policy', '/shipping-policy/' );
$research_url = $terms_url . '#tc-ruo';
$research_page = get_page_by_path( 'research-use-policy', OBJECT, 'page' );
if ( $research_page instanceof WP_Post && 'publish' === $research_page->post_status ) {
	$research_url = get_permalink( $research_page );
}
$account_url = foxfire_get_wc_page_url( 'myaccount' );
$orders_url = function_exists( 'wc_get_endpoint_url' ) ? wc_get_endpoint_url( 'orders', '', $account_url ) : $account_url;
$year        = (string) gmdate( 'Y' );
$footer_tagline = foxfire_get_managed_content( 'footer_tagline', __( 'Research compounds with transparent testing, clear documentation, and straightforward ordering.', 'foxfire-child' ) );
$research_notice = foxfire_get_managed_content( 'footer_research_notice', __( 'For laboratory research use only. Not for human consumption.', 'foxfire-child' ) );
?>

<div class="ff-site-footer">
	<div class="ff-site-footer__inner">
		<div class="ff-site-footer__brand">
			<p class="ff-site-footer__name"><?php bloginfo( 'name' ); ?></p>
			<p class="ff-site-footer__tagline">
				<?php echo esc_html( $footer_tagline ); ?>
			</p>
		</div>

		<nav class="ff-site-footer__nav" aria-labelledby="ff-footer-shop-heading">
			<h2 id="ff-footer-shop-heading" class="ff-site-footer__section-label"><?php esc_html_e( 'Shop', 'foxfire-child' ); ?></h2>
			<ul class="ff-site-footer__links">
				<li><a href="<?php echo esc_url( $shop_url ); ?>"><?php esc_html_e( 'Research Compounds', 'foxfire-child' ); ?></a></li>
				<li><a href="<?php echo esc_url( $testing_url ); ?>"><?php esc_html_e( 'Testing & COAs', 'foxfire-child' ); ?></a></li>
			</ul>
		</nav>

		<nav class="ff-site-footer__information" aria-labelledby="ff-footer-information-heading">
			<h2 id="ff-footer-information-heading" class="ff-site-footer__section-label"><?php esc_html_e( 'Information', 'foxfire-child' ); ?></h2>
			<ul class="ff-site-footer__links">
				<li><a href="<?php echo esc_url( $about_url ); ?>"><?php esc_html_e( 'About Foxfire', 'foxfire-child' ); ?></a></li>
				<li><a href="<?php echo esc_url( $faq_url ); ?>"><?php esc_html_e( 'FAQ', 'foxfire-child' ); ?></a></li>
				<li><a href="<?php echo esc_url( $contact_url ); ?>"><?php esc_html_e( 'Contact', 'foxfire-child' ); ?></a></li>
				<li><a href="<?php echo esc_url( $shipping_url ); ?>"><?php esc_html_e( 'Shipping & Returns', 'foxfire-child' ); ?></a></li>
				<li><a href="<?php echo esc_url( $privacy_url ); ?>"><?php esc_html_e( 'Privacy Policy', 'foxfire-child' ); ?></a></li>
				<li><a href="<?php echo esc_url( $terms_url ); ?>"><?php esc_html_e( 'Terms & Conditions', 'foxfire-child' ); ?></a></li>
				<li><a href="<?php echo esc_url( $research_url ); ?>"><?php esc_html_e( 'Research Use Policy', 'foxfire-child' ); ?></a></li>
			</ul>
		</nav>

		<nav class="ff-site-footer__account" aria-labelledby="ff-footer-account-heading">
			<h2 id="ff-footer-account-heading" class="ff-site-footer__section-label"><?php esc_html_e( 'Account', 'foxfire-child' ); ?></h2>
			<ul class="ff-site-footer__links">
				<li><a href="<?php echo esc_url( $account_url ); ?>"><?php esc_html_e( 'My Account', 'foxfire-child' ); ?></a></li>
				<li><a href="<?php echo esc_url( $orders_url ); ?>"><?php esc_html_e( 'Order History', 'foxfire-child' ); ?></a></li>
			</ul>
		</nav>
	</div>

	<div class="ff-site-footer__bottom">
		<p class="ff-site-footer__research-note">
			<?php echo esc_html( $research_notice ); ?>
		</p>
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
	</div>
</div>
