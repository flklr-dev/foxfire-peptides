<?php
/**
 * The template for displaying the front page / homepage (Chunk 1H).
 *
 * @package Foxfire_Child
 */

defined( 'ABSPATH' ) || exit;

get_header();

$shop_url    = function_exists( 'foxfire_get_shop_url' ) ? foxfire_get_shop_url() : home_url( '/shop/' );
$testing_url = function_exists( 'foxfire_get_page_url' ) ? foxfire_get_page_url( 'testing-coa', '/testing-coa/' ) : home_url( '/testing-coa/' );
$about_url   = function_exists( 'foxfire_get_page_url' ) ? foxfire_get_page_url( 'about', '/about/' ) : home_url( '/about/' );
$hero_img    = FOXFIRE_CHILD_URI . '/assets/images/hero-peptides.png';
$categories  = function_exists( 'foxfire_get_homepage_categories' ) ? foxfire_get_homepage_categories() : array();
$featured_q  = function_exists( 'foxfire_get_homepage_products' ) ? foxfire_get_homepage_products( 4 ) : null;
?>

<div class="ff-homepage">
	<!-- 1. Hero Section -->
	<section class="ff-home-hero" aria-labelledby="ff-hero-title">
		<div class="ff-home-hero__inner">
			<div class="ff-home-hero__content">
				<p class="ff-home-hero__eyebrow">
					<?php esc_html_e( 'Analytical Research Standards', 'foxfire-child' ); ?>
				</p>

				<h1 id="ff-hero-title" class="ff-home-hero__title">
					<?php esc_html_e( 'High-Purity Research Peptides With Transparent Testing', 'foxfire-child' ); ?>
				</h1>

				<p class="ff-home-hero__lead">
					<?php esc_html_e( 'Lab-grade research compounds verified by independent third-party laboratories. Batch-specific Certificates of Analysis available for every sequence.', 'foxfire-child' ); ?>
				</p>

				<div class="ff-home-hero__actions">
					<a href="<?php echo esc_url( $shop_url ); ?>" class="ff-home-hero__cta-primary">
						<?php esc_html_e( 'Shop Peptides', 'foxfire-child' ); ?>
					</a>
					<a href="<?php echo esc_url( $testing_url ); ?>" class="ff-home-hero__cta-secondary">
						<?php esc_html_e( 'View Testing & COAs', 'foxfire-child' ); ?>
					</a>
				</div>

				<ul class="ff-home-hero__bullets">
					<li>
						<span class="ff-home-hero__check" aria-hidden="true">✓</span>
						<?php esc_html_e( 'HPLC & MS Verified', 'foxfire-child' ); ?>
					</li>
					<li>
						<span class="ff-home-hero__check" aria-hidden="true">✓</span>
						<?php esc_html_e( 'Public COA Library', 'foxfire-child' ); ?>
					</li>
					<li>
						<span class="ff-home-hero__check" aria-hidden="true">✓</span>
						<?php esc_html_e( 'Secure Dispatch', 'foxfire-child' ); ?>
					</li>
				</ul>
			</div>

			<div class="ff-home-hero__media">
				<div class="ff-home-hero__image-card">
					<img
						src="<?php echo esc_url( $hero_img ); ?>"
						alt="<?php esc_attr_e( 'Foxfire Peptides sterile laboratory research vials with Certificate of Analysis testing', 'foxfire-child' ); ?>"
						width="1200"
						height="675"
						loading="eager"
					/>
				</div>
			</div>
		</div>
	</section>

	<!-- 2. Trust Value Band (Shared flat band with vertical dividers) -->
	<section class="ff-trust-band" aria-label="<?php esc_attr_e( 'Trust and Quality Commitments', 'foxfire-child' ); ?>">
		<div class="ff-trust-band__grid">
			<div class="ff-trust-band__col">
				<span class="ff-trust-band__icon" aria-hidden="true">
					<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
						<path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
					</svg>
				</span>
				<div class="ff-trust-band__content">
					<h3 class="ff-trust-band__title"><?php esc_html_e( 'Third-Party Tested', 'foxfire-child' ); ?></h3>
					<p class="ff-trust-band__desc"><?php esc_html_e( 'HPLC & Mass Spectrometry purity testing on every batch.', 'foxfire-child' ); ?></p>
				</div>
			</div>

			<div class="ff-trust-band__col">
				<span class="ff-trust-band__icon" aria-hidden="true">
					<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
						<path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
						<polyline points="14 2 14 8 20 8"/>
						<line x1="16" y1="13" x2="8" y2="13"/>
						<line x1="16" y1="17" x2="8" y2="17"/>
					</svg>
				</span>
				<div class="ff-trust-band__content">
					<h3 class="ff-trust-band__title"><?php esc_html_e( 'Batch-Matched COAs', 'foxfire-child' ); ?></h3>
					<p class="ff-trust-band__desc"><?php esc_html_e( 'Publicly accessible certificates matching every vial label.', 'foxfire-child' ); ?></p>
				</div>
			</div>

			<div class="ff-trust-band__col">
				<span class="ff-trust-band__icon" aria-hidden="true">
					<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
						<rect width="20" height="14" x="2" y="5" rx="2"/>
						<line x1="2" y1="10" x2="22" y2="10"/>
					</svg>
				</span>
				<div class="ff-trust-band__content">
					<h3 class="ff-trust-band__title"><?php esc_html_e( 'Fast, Secure Shipping', 'foxfire-child' ); ?></h3>
					<p class="ff-trust-band__desc"><?php esc_html_e( 'Discreet, temperature-stable packaging & fast dispatch.', 'foxfire-child' ); ?></p>
				</div>
			</div>

			<div class="ff-trust-band__col">
				<span class="ff-trust-band__icon" aria-hidden="true">
					<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
						<circle cx="12" cy="12" r="10"/>
						<line x1="12" y1="16" x2="12" y2="12"/>
						<line x1="12" y1="8" x2="12.01" y2="8"/>
					</svg>
				</span>
				<div class="ff-trust-band__content">
					<h3 class="ff-trust-band__title"><?php esc_html_e( 'Research Compliant', 'foxfire-child' ); ?></h3>
					<p class="ff-trust-band__desc"><?php esc_html_e( 'Strictly for in-vitro and laboratory research use.', 'foxfire-child' ); ?></p>
				</div>
			</div>
		</div>
	</section>

	<!-- 3. Featured Research Compounds -->
	<section class="ff-featured-section" aria-labelledby="ff-featured-heading">
		<div class="ff-section-header ff-section-header--with-link">
			<div>
				<span class="ff-section-header__eyebrow"><?php esc_html_e( 'Catalog Showcase', 'foxfire-child' ); ?></span>
				<h2 id="ff-featured-heading" class="ff-section-header__title">
					<?php esc_html_e( 'Featured Research Compounds', 'foxfire-child' ); ?>
				</h2>
			</div>
			<a href="<?php echo esc_url( $shop_url ); ?>" class="ff-section-header__link">
				<?php esc_html_e( 'View All Compounds', 'foxfire-child' ); ?>
				<span aria-hidden="true">→</span>
			</a>
		</div>

		<?php if ( $featured_q instanceof WP_Query && $featured_q->have_posts() ) : ?>
			<div class="woocommerce">
				<ul class="products">
					<?php
					while ( $featured_q->have_posts() ) :
						$featured_q->the_post();
						wc_get_template_part( 'content', 'product' );
					endwhile;
					wp_reset_postdata();
					?>
				</ul>
			</div>
		<?php else : ?>
			<p class="ff-featured-section__empty"><?php esc_html_e( 'Products loading...', 'foxfire-child' ); ?></p>
		<?php endif; ?>
	</section>

	<!-- 4. Research Category Cards -->
	<?php if ( ! empty( $categories ) ) : ?>
		<section class="ff-categories-section" aria-labelledby="ff-categories-heading">
			<div class="ff-section-header">
				<span class="ff-section-header__eyebrow"><?php esc_html_e( 'Classification', 'foxfire-child' ); ?></span>
				<h2 id="ff-categories-heading" class="ff-section-header__title">
					<?php esc_html_e( 'Browse by Research Area', 'foxfire-child' ); ?>
				</h2>
			</div>

			<div class="ff-category-grid">
				<?php foreach ( $categories as $cat ) : ?>
					<a href="<?php echo esc_url( $cat['link'] ); ?>" class="ff-category-card">
						<div class="ff-category-card__icon" aria-hidden="true">
							<?php echo $cat['icon']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
						</div>
						<h3 class="ff-category-card__title"><?php echo esc_html( $cat['name'] ); ?></h3>
						<p class="ff-category-card__count">
							<?php
							printf(
								/* translators: %d: number of products */
								esc_html( _n( '%d Compound Available', '%d Compounds Available', $cat['count'], 'foxfire-child' ) ),
								(int) $cat['count']
							);
							?>
						</p>
						<span class="ff-category-card__arrow" aria-hidden="true">
							<?php esc_html_e( 'Explore category', 'foxfire-child' ); ?> →
						</span>
					</a>
				<?php endforeach; ?>
			</div>
		</section>
	<?php endif; ?>

	<!-- 5. Testing & COA Callout Banner -->
	<section class="ff-home-coa-banner" aria-labelledby="ff-coa-banner-heading">
		<div>
			<div class="ff-home-coa-banner__header">
				<span class="ff-home-coa-banner__icon" aria-hidden="true">
					<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
						<path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
						<path d="m9 12 2 2 4-4"/>
					</svg>
				</span>
				<span class="ff-home-coa-banner__badge"><?php esc_html_e( 'Quality Transparency', 'foxfire-child' ); ?></span>
			</div>
			<h2 id="ff-coa-banner-heading" class="ff-home-coa-banner__title">
				<?php esc_html_e( 'Batch Traceability in Every Vial. Verified by Independent Labs.', 'foxfire-child' ); ?>
			</h2>
			<p class="ff-home-coa-banner__desc">
				<?php esc_html_e( 'Analytical testing data is made publicly available for research verification. Review chromatography profiles and mass spectrometry documentation.', 'foxfire-child' ); ?>
			</p>
		</div>

		<div style="text-align: right;">
			<a href="<?php echo esc_url( $testing_url ); ?>" class="ff-home-coa-banner__cta">
				<span><?php esc_html_e( 'Access Testing / COA Portal', 'foxfire-child' ); ?></span>
				<span aria-hidden="true">→</span>
			</a>
		</div>
	</section>

	<!-- 6. Research Standards & Commitments -->
	<section class="ff-home-standards" aria-labelledby="ff-standards-heading">
		<div class="ff-section-header">
			<span class="ff-section-header__eyebrow"><?php esc_html_e( 'Our Standard', 'foxfire-child' ); ?></span>
			<h2 id="ff-standards-heading" class="ff-section-header__title">
				<?php esc_html_e( 'Commitment to Scientific Integrity', 'foxfire-child' ); ?>
			</h2>
		</div>

		<div class="ff-standards-grid">
			<div class="ff-standard-card">
				<h3 class="ff-standard-card__title"><?php esc_html_e( 'Precision Synthesis', 'foxfire-child' ); ?></h3>
				<p class="ff-standard-card__text">
					<?php esc_html_e( 'Solid-phase peptide synthesis monitored through rigid quality control to ensure strict sequence purity.', 'foxfire-child' ); ?>
				</p>
			</div>

			<div class="ff-standard-card">
				<h3 class="ff-standard-card__title"><?php esc_html_e( 'Lyophilization & Storage', 'foxfire-child' ); ?></h3>
				<p class="ff-standard-card__text">
					<?php esc_html_e( 'Vacuum freeze-dried into sterile glass vials for maximum shelf life and structural stability when stored at -20°C.', 'foxfire-child' ); ?>
				</p>
			</div>

			<div class="ff-standard-card">
				<h3 class="ff-standard-card__title"><?php esc_html_e( 'Researcher Support', 'foxfire-child' ); ?></h3>
				<p class="ff-standard-card__text">
					<?php esc_html_e( 'Responsive technical assistance regarding compound specifications, lot verification, and order logistics.', 'foxfire-child' ); ?>
				</p>
			</div>
		</div>
	</section>
</div>

<?php
get_footer();
