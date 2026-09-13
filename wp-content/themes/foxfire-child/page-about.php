<?php
/**
 * About page template.
 *
 * @package Foxfire_Peptides
 */

defined( 'ABSPATH' ) || exit;

get_header();

$shop_url    = function_exists( 'foxfire_get_shop_url' ) ? foxfire_get_shop_url() : home_url( '/shop/' );
$testing_url = home_url( '/testing-coa/' );
$founder_enabled  = 'yes' === foxfire_get_managed_content( 'about_founder_enabled', 'yes' );
$founder_image_id = absint( foxfire_get_managed_content( 'about_founder_image', '0' ) );
$founder_image    = '';

if ( $founder_image_id ) {
	$founder_image = wp_get_attachment_image(
		$founder_image_id,
		'large',
		false,
		array(
			'class'   => 'ff-about-founder__image',
			'alt'     => foxfire_get_managed_content( 'about_founder_image_alt', 'Jay, founder of Foxfire Peptides' ),
			'loading' => 'lazy',
		)
	);
}

if ( ! $founder_image ) {
	$template_image_path     = get_stylesheet_directory() . '/assets/images/founder-jay.webp';
	$template_image_fallback = get_stylesheet_directory() . '/assets/images/founder-jay.jpg';

	if ( file_exists( $template_image_path ) || file_exists( $template_image_fallback ) ) {
		$image_src = file_exists( $template_image_path )
			? FOXFIRE_CHILD_URI . '/assets/images/founder-jay.webp'
			: FOXFIRE_CHILD_URI . '/assets/images/founder-jay.jpg';
		$image_sm  = FOXFIRE_CHILD_URI . '/assets/images/founder-jay-480.webp';
		$alt_text  = foxfire_get_managed_content( 'about_founder_image_alt', 'Jay, founder of Foxfire Peptides' );

		$founder_image = sprintf(
			'<img src="%1$s" srcset="%2$s 480w, %1$s 896w" sizes="(max-width: 640px) 100vw, 450px" width="896" height="1200" class="ff-about-founder__image" alt="%3$s" loading="lazy" />',
			esc_url( $image_src ),
			esc_url( $image_sm ),
			esc_attr( $alt_text )
		);
	}
}
?>

<main id="main-content" class="site-main ff-about-page" tabindex="-1">
	<section class="ff-about-hero" aria-labelledby="ff-about-title">
		<div class="ff-about-container">
			<div class="ff-about-hero__copy">
				<p class="ff-about-eyebrow"><?php echo esc_html( foxfire_get_managed_content( 'about_hero_eyebrow', 'About Foxfire' ) ); ?></p>
				<h1 id="ff-about-title" class="ff-about-hero__title"><?php echo esc_html( foxfire_get_managed_content( 'about_hero_title', 'The Road Is Better Together.' ) ); ?></h1>
				<p class="ff-about-hero__lead"><?php echo esc_html( foxfire_get_managed_content( 'about_hero_intro', 'Foxfire Peptides is being built as a focused, human-led peptide company—with clear product information, a recognizable founder, and genuine relationships at its heart.' ) ); ?></p>
				<p class="ff-about-hero__values" aria-label="Foxfire brand values">
					<span><?php esc_html_e( 'Quality', 'foxfire-peptides' ); ?></span>
					<span class="ff-about-hero__separator" aria-hidden="true">•</span>
					<span><?php esc_html_e( 'Transparency', 'foxfire-peptides' ); ?></span>
					<span class="ff-about-hero__separator" aria-hidden="true">•</span>
					<span><?php esc_html_e( 'Community', 'foxfire-peptides' ); ?></span>
				</p>
			</div>
		</div>
	</section>

	<section class="ff-about-story" aria-labelledby="ff-about-story-title">
		<div class="ff-about-container ff-about-story__grid">
			<div class="ff-about-story__copy">
				<p class="ff-about-eyebrow"><?php esc_html_e( 'Why Foxfire', 'foxfire-peptides' ); ?></p>
				<h2 id="ff-about-story-title" class="ff-about-section-title"><?php echo esc_html( foxfire_get_managed_content( 'about_story_title', 'A Focused, More Personal Approach' ) ); ?></h2>
				<p><?php echo esc_html( foxfire_get_managed_content( 'about_story_one', 'Foxfire is designed around a focused catalog rather than an overwhelming warehouse of options. The goal is to make it simple to find a product, understand the available strengths, and move through checkout without unnecessary friction.' ) ); ?></p>
				<p><?php echo esc_html( foxfire_get_managed_content( 'about_story_two', 'As the company grows, the commitment stays the same: communicate clearly, make available documentation easy to find, and create an experience customers can navigate with confidence.' ) ); ?></p>
			</div>

			<aside class="ff-about-story-card">
				<p class="ff-about-story-card__label"><?php echo esc_html( foxfire_get_managed_content( 'about_story_callout_title', 'Focused by design' ) ); ?></p>
				<p><?php echo esc_html( foxfire_get_managed_content( 'about_story_callout_text', 'A smaller, intentional catalog keeps the experience straightforward—from product discovery to testing information and account support.' ) ); ?></p>
				<ul>
					<li><?php esc_html_e( 'Simple shopping on every screen', 'foxfire-peptides' ); ?></li>
					<li><?php esc_html_e( 'Clear product and batch information', 'foxfire-peptides' ); ?></li>
					<li><?php esc_html_e( 'A recognizable, human-led brand', 'foxfire-peptides' ); ?></li>
				</ul>
			</aside>
		</div>
	</section>

	<?php if ( $founder_enabled ) : ?>
		<section class="ff-about-founder" aria-labelledby="ff-about-founder-title">
			<div class="ff-about-container ff-about-founder__grid">
				<div class="ff-about-founder__media">
					<?php if ( $founder_image ) : ?>
						<?php
						echo wp_kses(
							$founder_image,
							array(
								'img' => array(
									'src'      => true,
									'srcset'   => true,
									'sizes'    => true,
									'alt'      => true,
									'class'    => true,
									'width'    => true,
									'height'   => true,
									'loading'  => true,
									'decoding' => true,
								),
							)
						);
						?>
					<?php else : ?>
						<div class="ff-about-founder__placeholder" role="img" aria-label="Founder photograph will be added before launch">
							<span class="ff-about-founder__placeholder-mark" aria-hidden="true">FF</span>
							<span><?php esc_html_e( 'Human-led by design', 'foxfire-peptides' ); ?></span>
						</div>
					<?php endif; ?>
				</div>

				<div class="ff-about-founder__copy">
					<p class="ff-about-eyebrow"><?php echo esc_html( foxfire_get_managed_content( 'about_founder_eyebrow', 'Meet the Founder' ) ); ?></p>
					<h2 id="ff-about-founder-title" class="ff-about-section-title"><?php echo esc_html( foxfire_get_managed_content( 'about_founder_name', 'Meet Jay' ) ); ?></h2>
					<p class="ff-about-founder__role"><?php echo esc_html( foxfire_get_managed_content( 'about_founder_role', 'Founder of Foxfire Peptides' ) ); ?></p>
					<p><?php echo esc_html( foxfire_get_managed_content( 'about_founder_story_one', 'Foxfire is being built as a human-led company rather than another anonymous peptide storefront. Jay plans to be publicly connected to the brand and accountable for the experience it creates.' ) ); ?></p>
					<p><?php echo esc_html( foxfire_get_managed_content( 'about_founder_story_two', 'The aim is straightforward: keep products easy to shop, make available information easy to find, and build lasting relationships through clear communication and dependable service.' ) ); ?></p>
					<blockquote><?php echo esc_html( foxfire_get_managed_content( 'about_founder_quote', 'The Road Is Better Together.' ) ); ?></blockquote>
				</div>
			</div>
		</section>
	<?php endif; ?>

	<section class="ff-about-values" aria-labelledby="ff-about-values-title">
		<div class="ff-about-container">
			<div class="ff-about-section-header">
				<p class="ff-about-eyebrow"><?php esc_html_e( 'What Guides Us', 'foxfire-peptides' ); ?></p>
				<h2 id="ff-about-values-title" class="ff-about-section-title"><?php echo esc_html( foxfire_get_managed_content( 'about_values_title', 'Built Around Three Commitments' ) ); ?></h2>
				<p class="ff-about-section-intro"><?php echo esc_html( foxfire_get_managed_content( 'about_values_intro', 'Three principles shape the store, the information we share, and the relationships we want to build.' ) ); ?></p>
			</div>

			<div class="ff-about-values__grid">
				<article class="ff-about-value-card">
					<span class="ff-about-value-card__index" aria-hidden="true">01</span>
					<h3><?php echo esc_html( foxfire_get_managed_content( 'about_value_quality_title', 'Quality' ) ); ?></h3>
					<p><?php echo esc_html( foxfire_get_managed_content( 'about_value_quality_text', 'A focused catalog, carefully presented product information, and a commitment to a dependable customer experience.' ) ); ?></p>
				</article>
				<article class="ff-about-value-card">
					<span class="ff-about-value-card__index" aria-hidden="true">02</span>
					<h3><?php echo esc_html( foxfire_get_managed_content( 'about_value_transparency_title', 'Transparency' ) ); ?></h3>
					<p><?php echo esc_html( foxfire_get_managed_content( 'about_value_transparency_text', 'Available batch and testing information should be easy to locate and straightforward to review.' ) ); ?></p>
				</article>
				<article class="ff-about-value-card">
					<span class="ff-about-value-card__index" aria-hidden="true">03</span>
					<h3><?php echo esc_html( foxfire_get_managed_content( 'about_value_community_title', 'Community' ) ); ?></h3>
					<p><?php echo esc_html( foxfire_get_managed_content( 'about_value_community_text', 'Foxfire is intended to grow through genuine relationships, responsive support, and shared trust.' ) ); ?></p>
				</article>
			</div>
		</div>
	</section>

	<section class="ff-about-coa" aria-labelledby="ff-about-coa-title">
		<div class="ff-about-container">
			<div class="ff-about-coa__panel">
				<div class="ff-about-coa__icon" aria-hidden="true">
					<svg viewBox="0 0 48 48" role="presentation" focusable="false">
						<path d="M16 5h16v10l8 15a8 8 0 0 1-7 12H15a8 8 0 0 1-7-12l8-15V5Z" />
						<path d="M14 29h20M18 13h12" />
					</svg>
				</div>
				<div class="ff-about-coa__copy">
					<p class="ff-about-eyebrow"><?php echo esc_html( foxfire_get_managed_content( 'about_coa_eyebrow', 'Testing & Documentation' ) ); ?></p>
					<h2 id="ff-about-coa-title"><?php echo esc_html( foxfire_get_managed_content( 'about_coa_title', 'Know What’s Behind the Vial.' ) ); ?></h2>
					<p><?php echo esc_html( foxfire_get_managed_content( 'about_coa_description', 'Review available testing status, batch and lot information, and COA documents in one clear directory.' ) ); ?></p>
				</div>
				<a class="ff-about-button ff-about-button--light" href="<?php echo esc_url( $testing_url ); ?>"><?php esc_html_e( 'Explore Testing & COAs', 'foxfire-peptides' ); ?></a>
			</div>
		</div>
	</section>

	<section class="ff-about-closing" aria-labelledby="ff-about-closing-title">
		<div class="ff-about-container">
			<div class="ff-about-community">
				<p class="ff-about-eyebrow"><?php esc_html_e( 'Community', 'foxfire-peptides' ); ?></p>
				<h2 id="ff-about-closing-title" class="ff-about-section-title"><?php echo esc_html( foxfire_get_managed_content( 'about_community_title', 'The Road Is Better Together.' ) ); ?></h2>
				<p><?php echo esc_html( foxfire_get_managed_content( 'about_community_description', 'Foxfire is more than a catalog. It is a brand being built in the open—with a founder customers can recognize and a community that helps shape what comes next.' ) ); ?></p>
			</div>

			<div class="ff-site-cta" aria-labelledby="ff-about-closing-cta-heading">
				<div class="ff-site-cta__content">
					<p class="ff-site-cta__eyebrow"><?php esc_html_e( 'Explore Foxfire', 'foxfire-peptides' ); ?></p>
					<h2 id="ff-about-closing-cta-heading" class="ff-site-cta__title"><?php echo esc_html( foxfire_get_managed_content( 'about_cta_title', 'Start With What Matters Most' ) ); ?></h2>
					<p class="ff-site-cta__description"><?php echo esc_html( foxfire_get_managed_content( 'about_cta_description', 'Explore the focused catalog or review available testing documentation.' ) ); ?></p>
				</div>
				<div class="ff-site-cta__actions">
					<a class="ff-site-cta__button ff-site-cta__button--primary" href="<?php echo esc_url( $shop_url ); ?>"><?php echo esc_html( foxfire_get_managed_content( 'about_primary_cta', 'Shop Products' ) ); ?></a>
					<a class="ff-site-cta__button ff-site-cta__button--secondary" href="<?php echo esc_url( $testing_url ); ?>"><?php echo esc_html( foxfire_get_managed_content( 'about_secondary_cta', 'View Testing & COAs' ) ); ?><span class="ff-btn-arrow" aria-hidden="true">↗</span></a>
				</div>
			</div>
		</div>
	</section>
</main>

<?php
get_footer();
