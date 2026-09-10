<?php
/**
 * Template Name: About Us
 *
 * About Us page for Foxfire Peptides.
 * Structure and layout inspired by modern editorial bento benchmark.
 * Brand tone: Professional, trustworthy, approachable, quality-focused, and community-oriented.
 * Styled strictly per DESIGN.md and client brand copy requirements.
 *
 * @package Foxfire_Child
 */

defined( 'ABSPATH' ) || exit;

get_header();

$shop_url      = foxfire_get_shop_url();
$testing_url   = foxfire_get_page_url( 'testing-coa', '/testing-coa/' );
$hero_portrait = get_stylesheet_directory_uri() . '/assets/images/about-hero-portrait.webp';
$hero_product  = get_stylesheet_directory_uri() . '/assets/images/about-hero-product.webp';
$hero_team     = get_stylesheet_directory_uri() . '/assets/images/about-hero-team.webp';
$hero_portrait_sm = get_stylesheet_directory_uri() . '/assets/images/about-hero-portrait-480.webp';
$hero_product_sm  = get_stylesheet_directory_uri() . '/assets/images/about-hero-product-480.webp';
$hero_team_sm     = get_stylesheet_directory_uri() . '/assets/images/about-hero-team-480.webp';
?>
<main id="main-content" class="ff-about-page" tabindex="-1">

	<!-- ====================================================================
	     SECTION 1: HERO & ASYMMETRIC BENTO COLLAGE GRID
	     ==================================================================== -->
	<section class="ff-about-hero-section">
		<div class="ff-about-hero-container">

			<!-- Hero Centered Header -->
			<header class="ff-about-header">
				<h1 class="ff-about-header__title"><?php echo esc_html( foxfire_get_managed_content( 'about_hero_title', __( 'Built on Quality, Trust & Community', 'foxfire-child' ) ) ); ?></h1>
				<p class="ff-about-header__subtitle">
					<?php echo esc_html( foxfire_get_managed_content( 'about_hero_intro', __( 'Foxfire Peptides is focused on creating a straightforward, transparent experience for research customers. We believe quality products, accessible information, and dependable service are the foundation of lasting relationships.', 'foxfire-child' ) ) ); ?>
				</p>
			</header>

			<!-- 3-Column Bento Grid -->
			<div class="ff-about-bento-grid">

				<!-- Column 1 (Left): Tall Portrait Image -->
				<div class="ff-bento-col ff-bento-col--tall">
					<div class="ff-bento-card ff-bento-card--image-tall">
						<img
							src="<?php echo esc_url( $hero_portrait ); ?>"
							srcset="<?php echo esc_attr( $hero_portrait_sm . ' 480w, ' . $hero_portrait . ' 800w' ); ?>"
							sizes="(max-width: 767px) calc(100vw - 40px), 33vw"
							alt="<?php esc_attr_e( 'Foxfire Peptides team member in a bright, modern studio workspace', 'foxfire-child' ); ?>"
							class="ff-bento-img"
							width="800"
							height="1067"
							loading="eager"
							fetchpriority="high"
							decoding="async"
						/>
					</div>
				</div>

				<!-- Column 2 (Middle): Orange Pillar Card + Product Image -->
				<div class="ff-bento-col ff-bento-col--middle">
					<!-- Orange Brand Callout Card -->
					<div class="ff-bento-card ff-bento-card--stat-orange">
						<div class="ff-bento-card__content">
							<span class="ff-bento-card__eyebrow"><?php esc_html_e( 'Our Commitment', 'foxfire-child' ); ?></span>
							<h2 class="ff-bento-card__heading"><?php esc_html_e( 'Transparent & Straightforward', 'foxfire-child' ); ?></h2>
							<p class="ff-bento-card__text"><?php esc_html_e( 'Accessible documentation, dependable service, and clear communication every step of the way.', 'foxfire-child' ); ?></p>
						</div>
					</div>

					<!-- Product Image Card -->
					<div class="ff-bento-card ff-bento-card--image-landscape">
						<img
							src="<?php echo esc_url( $hero_product ); ?>"
							srcset="<?php echo esc_attr( $hero_product_sm . ' 480w, ' . $hero_product . ' 800w' ); ?>"
							sizes="(max-width: 767px) calc(100vw - 40px), 33vw"
							alt="<?php esc_attr_e( 'Clean Foxfire Peptides research vials in natural studio light', 'foxfire-child' ); ?>"
							class="ff-bento-img"
							width="800"
							height="600"
							loading="eager"
							decoding="async"
						/>
					</div>
				</div>

				<!-- Column 3 (Right): Team Image + Charcoal Brand Card -->
				<div class="ff-bento-col ff-bento-col--right">
					<!-- Team Collaboration Image Card -->
					<div class="ff-bento-card ff-bento-card--image-landscape">
						<img
							src="<?php echo esc_url( $hero_team ); ?>"
							srcset="<?php echo esc_attr( $hero_team_sm . ' 480w, ' . $hero_team . ' 800w' ); ?>"
							sizes="(max-width: 767px) calc(100vw - 40px), 33vw"
							alt="<?php esc_attr_e( 'Friendly team members collaborating and supporting researchers', 'foxfire-child' ); ?>"
							class="ff-bento-img"
							width="800"
							height="600"
							loading="eager"
							decoding="async"
						/>
					</div>

					<!-- Charcoal Brand Callout Card -->
					<div class="ff-bento-card ff-bento-card--stat-dark">
						<div class="ff-bento-card__content">
							<span class="ff-bento-card__eyebrow"><?php esc_html_e( 'Who We Are', 'foxfire-child' ); ?></span>
							<h2 class="ff-bento-card__heading"><?php esc_html_e( 'Real People Behind Foxfire', 'foxfire-child' ); ?></h2>
							<p class="ff-bento-card__text"><?php esc_html_e( 'Dedicated to building lasting relationships with the research community through genuine care.', 'foxfire-child' ); ?></p>
						</div>
					</div>
				</div>

			</div>
		</div>
	</section>

	<!-- ====================================================================
	     SECTION 2: A MORE PERSONAL APPROACH & OUR APPROACH PILLARS
	     ==================================================================== -->
	<section class="ff-about-story-section" aria-labelledby="about-story-title">
		<div class="ff-about-story-container">

			<!-- Split 2-Column Story Row -->
			<div class="ff-about-story-row">
				<div class="ff-about-story-col ff-about-story-col--heading">
					<h2 id="about-story-title" class="ff-about-story__heading">
						<?php echo esc_html( foxfire_get_managed_content( 'about_story_title', __( 'A More Personal Approach', 'foxfire-child' ) ) ); ?>
					</h2>
				</div>
				<div class="ff-about-story-col ff-about-story-col--body">
					<p>
						<?php echo esc_html( foxfire_get_managed_content( 'about_story_one', __( 'We want Foxfire to feel different from an anonymous online storefront. Our goal is to make purchasing simple, information easy to find, and communication clear throughout the customer experience.', 'foxfire-child' ) ) ); ?>
					</p>
					<p>
						<?php echo esc_html( foxfire_get_managed_content( 'about_story_two', __( 'We believe that lasting trust is earned through everyday consistency. By focusing on clear product details, easy access to testing records where available, and responsive support whenever questions arise, we are building a brand researchers can count on for the long haul.', 'foxfire-child' ) ) ); ?>
					</p>
				</div>
			</div>

			<!-- 4-Pillar Horizontal Row (Our Approach) -->
			<div class="ff-about-stats-bar">
				<div class="ff-stat-block">
					<span class="ff-stat-block__value"><?php esc_html_e( 'Quality', 'foxfire-child' ); ?></span>
					<span class="ff-stat-block__desc"><?php esc_html_e( 'Clear product and testing information where available.', 'foxfire-child' ); ?></span>
				</div>
				<div class="ff-stat-divider" aria-hidden="true"></div>
				<div class="ff-stat-block">
					<span class="ff-stat-block__value"><?php esc_html_e( 'Transparency', 'foxfire-child' ); ?></span>
					<span class="ff-stat-block__desc"><?php esc_html_e( 'Easy access to batch and COA documentation.', 'foxfire-child' ); ?></span>
				</div>
				<div class="ff-stat-divider" aria-hidden="true"></div>
				<div class="ff-stat-block">
					<span class="ff-stat-block__value"><?php esc_html_e( 'Trust', 'foxfire-child' ); ?></span>
					<span class="ff-stat-block__desc"><?php esc_html_e( 'Straightforward communication and a simple buying experience.', 'foxfire-child' ); ?></span>
				</div>
				<div class="ff-stat-divider" aria-hidden="true"></div>
				<div class="ff-stat-block">
					<span class="ff-stat-block__value"><?php esc_html_e( 'Community', 'foxfire-child' ); ?></span>
					<span class="ff-stat-block__desc"><?php esc_html_e( 'Building long-term relationships with the people we serve.', 'foxfire-child' ); ?></span>
				</div>
			</div>

		</div>
	</section>

	<!-- ====================================================================
	     SECTION 3: OUR CORE VALUES / GUIDING PRINCIPLES
	     ==================================================================== -->
	<section class="ff-about-values-section" aria-labelledby="about-values-title">
		<div class="ff-about-values-container">

			<!-- Centered Section Header -->
			<header class="ff-about-values-header">
				<h2 id="about-values-title" class="ff-about-values-header__title"><?php echo esc_html( foxfire_get_managed_content( 'about_values_title', __( 'Our Core Values', 'foxfire-child' ) ) ); ?></h2>
				<p class="ff-about-values-header__subtitle">
					<?php echo esc_html( foxfire_get_managed_content( 'about_values_intro', __( 'Simple standards that guide how we treat our customers, curate our products, and support the community.', 'foxfire-child' ) ) ); ?>
				</p>
			</header>

			<!-- 4-Column Horizontal Values Row -->
			<div class="ff-about-values-grid">

				<!-- Value 1: Quality Mindset -->
				<div class="ff-value-card">
					<div class="ff-value-card__icon" aria-hidden="true">
						<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
							<path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path>
						</svg>
					</div>
					<h3 class="ff-value-card__title"><?php esc_html_e( 'Quality Mindset', 'foxfire-child' ); ?></h3>
					<p class="ff-value-card__desc">
						<?php esc_html_e( 'Careful attention to detail in everything we offer, accompanied by clear product specifications and testing data where available.', 'foxfire-child' ); ?>
					</p>
				</div>

				<!-- Value 2: Open Transparency -->
				<div class="ff-value-card">
					<div class="ff-value-card__icon" aria-hidden="true">
						<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
							<path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
							<polyline points="14 2 14 8 20 8"></polyline>
							<line x1="16" y1="13" x2="8" y2="13"></line>
							<line x1="16" y1="17" x2="8" y2="17"></line>
							<polyline points="10 9 9 9 8 9"></polyline>
						</svg>
					</div>
					<h3 class="ff-value-card__title"><?php esc_html_e( 'Open Transparency', 'foxfire-child' ); ?></h3>
					<p class="ff-value-card__desc">
						<?php esc_html_e( 'Easy, direct access to batch testing reports and certificates of analysis so you can order with complete clarity.', 'foxfire-child' ); ?>
					</p>
				</div>

				<!-- Value 3: Approachable Support -->
				<div class="ff-value-card">
					<div class="ff-value-card__icon" aria-hidden="true">
						<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
							<path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path>
						</svg>
					</div>
					<h3 class="ff-value-card__title"><?php esc_html_e( 'Approachable Support', 'foxfire-child' ); ?></h3>
					<p class="ff-value-card__desc">
						<?php esc_html_e( 'Friendly, responsive assistance from real people who are genuinely happy to help answer questions.', 'foxfire-child' ); ?>
					</p>
				</div>

				<!-- Value 4: Long-Term Relationships -->
				<div class="ff-value-card">
					<div class="ff-value-card__icon" aria-hidden="true">
						<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
							<path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
							<circle cx="9" cy="7" r="4"></circle>
							<path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
							<path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
						</svg>
					</div>
					<h3 class="ff-value-card__title"><?php esc_html_e( 'Community & Trust', 'foxfire-child' ); ?></h3>
					<p class="ff-value-card__desc">
						<?php esc_html_e( 'Focused on building lasting relationships with the people we serve, earning your trust through dependable service.', 'foxfire-child' ); ?>
					</p>
				</div>

			</div>
		</div>
	</section>

	<!-- ====================================================================
	     SECTION 4: CALL TO ACTION
	     ==================================================================== -->
	<section class="ff-about-cta-section" aria-labelledby="about-cta-title">
		<div class="ff-about-cta-container">
			<div class="ff-about-cta-box">
				<div class="ff-about-cta-box__text">
					<h2 id="about-cta-title" class="ff-about-cta-box__title"><?php echo esc_html( foxfire_get_managed_content( 'about_cta_title', __( 'Explore Foxfire Peptides', 'foxfire-child' ) ) ); ?></h2>
					<p class="ff-about-cta-box__desc">
						<?php echo esc_html( foxfire_get_managed_content( 'about_cta_description', __( 'Browse our catalog of research compounds or check out available batch documentation and certificates of analysis.', 'foxfire-child' ) ) ); ?>
					</p>
				</div>
				<div class="ff-about-cta-box__actions">
					<a href="<?php echo esc_url( $shop_url ); ?>" class="button button--primary ff-btn-orange">
						<?php esc_html_e( 'Explore Products', 'foxfire-child' ); ?>
					</a>
					<a href="<?php echo esc_url( $testing_url ); ?>" class="button button--secondary ff-btn-outline">
						<?php esc_html_e( 'View Testing & COAs', 'foxfire-child' ); ?>
					</a>
				</div>
			</div>
		</div>
	</section>

</main>
<?php
get_footer();
