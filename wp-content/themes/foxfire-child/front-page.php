<?php
/**
 * The template for displaying the front page / homepage (Chunk 1H).
 *
 * Clean, fast, white-forward shopping experience with transparent batch testing
 * per PRD §1-§5 and DESIGN.md §1-§7.
 *
 * @package Foxfire_Child
 */

defined( 'ABSPATH' ) || exit;

get_header();

$shop_url    = function_exists( 'foxfire_get_shop_url' ) ? foxfire_get_shop_url() : home_url( '/shop/' );
$testing_url = function_exists( 'foxfire_get_page_url' ) ? foxfire_get_page_url( 'testing-coa', '/testing-coa/' ) : home_url( '/testing-coa/' );
$hero_img    = FOXFIRE_CHILD_URI . '/assets/images/hero-peptides.webp';
$hero_img_sm = FOXFIRE_CHILD_URI . '/assets/images/hero-peptides-480.webp';
$featured_q  = function_exists( 'foxfire_get_homepage_products' ) ? foxfire_get_homepage_products( 4 ) : null;
$managed_faqs = foxfire_get_homepage_faqs();
?>

<div class="ff-homepage">
	<!-- 1. Hero Section -->
	<section class="ff-home-hero" aria-labelledby="ff-hero-title">
		<div class="ff-home-hero__inner">
			<div class="ff-home-hero__content ff-reveal">
				<p class="ff-home-hero__eyebrow">
					<?php echo esc_html( foxfire_get_managed_content( 'home_hero_eyebrow', __( 'Analytical Research Standards', 'foxfire-child' ) ) ); ?>
				</p>

				<h1 id="ff-hero-title" class="ff-home-hero__title">
					<?php echo esc_html( foxfire_get_managed_content( 'home_hero_title', __( 'Research Compounds. Transparent Testing. Real Accountability.', 'foxfire-child' ) ) ); ?>
				</h1>

				<p class="ff-home-hero__lead">
					<?php echo esc_html( foxfire_get_managed_content( 'home_hero_lead', __( 'Third-party testing, clear batch documentation, and straightforward access to the information behind every Foxfire product.', 'foxfire-child' ) ) ); ?>
				</p>

				<div class="ff-home-hero__actions">
					<a href="<?php echo esc_url( $shop_url ); ?>" class="ff-home-hero__cta-primary">
						<?php echo esc_html( foxfire_get_managed_content( 'home_primary_cta', __( 'SHOP RESEARCH COMPOUNDS', 'foxfire-child' ) ) ); ?>
					</a>
					<a href="<?php echo esc_url( $testing_url ); ?>" class="ff-home-hero__cta-secondary">
						<?php echo esc_html( foxfire_get_managed_content( 'home_secondary_cta', __( 'VIEW TESTING & COAs', 'foxfire-child' ) ) ); ?>
					</a>
				</div>

				<ul class="ff-home-hero__bullets">
					<li>
						<span class="ff-home-hero__check" aria-hidden="true">✓</span>
						<?php esc_html_e( 'Batch & COA Access', 'foxfire-child' ); ?>
					</li>
					<li>
						<span class="ff-home-hero__check" aria-hidden="true">✓</span>
						<?php esc_html_e( 'Simple Ordering', 'foxfire-child' ); ?>
					</li>
					<li>
						<span class="ff-home-hero__check" aria-hidden="true">✓</span>
						<?php esc_html_e( 'Research Use Only', 'foxfire-child' ); ?>
					</li>
				</ul>
				<p class="ff-home-hero__research-note"><?php esc_html_e( 'For laboratory research use only. Not for human consumption.', 'foxfire-child' ); ?></p>
			</div>

			<div class="ff-home-hero__media ff-reveal ff-reveal--delay-1">
				<div class="ff-home-hero__image-card">
					<img
						src="<?php echo esc_url( $hero_img ); ?>"
						srcset="<?php echo esc_attr( $hero_img_sm . ' 480w, ' . $hero_img . ' 851w' ); ?>"
						sizes="(max-width: 767px) calc(100vw - 48px), (max-width: 1200px) 45vw, 520px"
						alt="<?php esc_attr_e( 'Foxfire Peptides laboratory research vials with Certificate of Analysis testing', 'foxfire-child' ); ?>"
						width="851"
						height="733"
						loading="eager"
						fetchpriority="high"
						decoding="async"
					/>
				</div>
			</div>
		</div>
	</section>

	<!-- 2. Trust Value Band -->
	<section class="ff-trust-band ff-reveal" aria-label="<?php esc_attr_e( 'Trust and Quality Commitments', 'foxfire-child' ); ?>">
		<div class="ff-trust-band__grid">
			<div class="ff-trust-band__col ff-reveal ff-reveal--delay-1">
				<span class="ff-trust-band__icon" aria-hidden="true">
					<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
						<path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
					</svg>
				</span>
				<div class="ff-trust-band__content">
					<h2 class="ff-trust-band__title"><?php esc_html_e( 'Testing Information', 'foxfire-child' ); ?></h2>
					<p class="ff-trust-band__desc"><?php esc_html_e( 'Access available third-party testing and documentation.', 'foxfire-child' ); ?></p>
				</div>
			</div>

			<div class="ff-trust-band__col ff-reveal ff-reveal--delay-2">
				<span class="ff-trust-band__icon" aria-hidden="true">
					<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
						<path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
						<polyline points="14 2 14 8 20 8"/>
						<line x1="16" y1="13" x2="8" y2="13"/>
						<line x1="16" y1="17" x2="8" y2="17"/>
					</svg>
				</span>
				<div class="ff-trust-band__content">
					<h2 class="ff-trust-band__title"><?php esc_html_e( 'Batch & COA Access', 'foxfire-child' ); ?></h2>
					<p class="ff-trust-band__desc"><?php esc_html_e( 'Find available laboratory reports by batch or lot number.', 'foxfire-child' ); ?></p>
				</div>
			</div>

			<div class="ff-trust-band__col ff-reveal ff-reveal--delay-3">
				<span class="ff-trust-band__icon" aria-hidden="true">
					<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
						<rect width="20" height="14" x="2" y="5" rx="2"/>
						<line x1="2" y1="10" x2="22" y2="10"/>
					</svg>
				</span>
				<div class="ff-trust-band__content">
					<h2 class="ff-trust-band__title"><?php esc_html_e( 'Simple Ordering', 'foxfire-child' ); ?></h2>
					<p class="ff-trust-band__desc"><?php esc_html_e( 'Straightforward product selection and checkout.', 'foxfire-child' ); ?></p>
				</div>
			</div>

			<div class="ff-trust-band__col ff-reveal ff-reveal--delay-4">
				<span class="ff-trust-band__icon" aria-hidden="true">
					<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
						<circle cx="12" cy="12" r="10"/>
						<line x1="12" y1="16" x2="12" y2="12"/>
						<line x1="12" y1="8" x2="12.01" y2="8"/>
					</svg>
				</span>
				<div class="ff-trust-band__content">
					<h2 class="ff-trust-band__title"><?php esc_html_e( 'Research Use Only', 'foxfire-child' ); ?></h2>
					<p class="ff-trust-band__desc"><?php esc_html_e( 'Products intended strictly for laboratory research. Not for human consumption.', 'foxfire-child' ); ?></p>
				</div>
			</div>
		</div>
	</section>

	<!-- 3. Quality & Verification Approach -->
	<section class="ff-home-standards" aria-labelledby="ff-standards-heading">
		<div class="ff-home-standards__inner">
			<div class="ff-home-standards__header ff-reveal">
				<span class="ff-home-standards__eyebrow"><?php esc_html_e( 'Quality Approach', 'foxfire-child' ); ?></span>
				<h2 id="ff-standards-heading" class="ff-home-standards__title">
					<?php esc_html_e( 'More Than a Storefront.', 'foxfire-child' ); ?>
				</h2>
				<p class="ff-home-standards__description">
					<?php esc_html_e( 'Foxfire was built around a simple idea: make research products straightforward, make testing easy to find, and put a real person behind the company.', 'foxfire-child' ); ?>
				</p>
			</div>

			<div class="ff-standards-grid">
				<div class="ff-standard-card ff-reveal ff-reveal--delay-1">
					<span class="ff-standard-card__step" aria-hidden="true">1</span>
					<h3 class="ff-standard-card__title"><?php esc_html_e( 'Testing Information', 'foxfire-child' ); ?></h3>
					<p class="ff-standard-card__text">
						<?php esc_html_e( 'Review available third-party testing and batch documentation for Foxfire research compounds.', 'foxfire-child' ); ?>
					</p>
				</div>

				<div class="ff-standard-card ff-reveal ff-reveal--delay-2">
					<span class="ff-standard-card__step" aria-hidden="true">2</span>
					<h3 class="ff-standard-card__title"><?php esc_html_e( 'Batch Identification', 'foxfire-child' ); ?></h3>
					<p class="ff-standard-card__text">
						<?php esc_html_e( 'Clearly marked batch numbers make it easy to match each product with its available testing documentation.', 'foxfire-child' ); ?>
					</p>
				</div>

				<div class="ff-standard-card ff-reveal ff-reveal--delay-3">
					<span class="ff-standard-card__step" aria-hidden="true">3</span>
					<h3 class="ff-standard-card__title"><?php esc_html_e( 'Straightforward Ordering', 'foxfire-child' ); ?></h3>
					<p class="ff-standard-card__text">
						<?php esc_html_e( 'Choose available quantities directly from the product page with clear pricing and no unnecessary complexity.', 'foxfire-child' ); ?>
					</p>
				</div>
			</div>
		</div>
	</section>

	<!-- 4. Featured peptides -->
	<section class="ff-featured-section" aria-labelledby="ff-featured-heading">
		<div class="ff-section-header ff-section-header--with-link ff-reveal">
			<div>
				<h2 id="ff-featured-heading" class="ff-section-header__title">
					<?php esc_html_e( 'Featured Research Compounds', 'foxfire-child' ); ?>
				</h2>
				<p class="ff-section-header__description">
					<?php esc_html_e( 'Explore select Foxfire research compounds with available batch and testing documentation.', 'foxfire-child' ); ?>
				</p>
			</div>
			<a href="<?php echo esc_url( $shop_url ); ?>" class="ff-section-header__link">
				<?php esc_html_e( 'View Full Catalog', 'foxfire-child' ); ?>
				<span aria-hidden="true">→</span>
			</a>
		</div>

		<?php if ( $featured_q instanceof WP_Query && $featured_q->have_posts() ) : ?>
			<div class="woocommerce ff-reveal ff-reveal--delay-1">
				<ul class="products ff-featured-grid">
					<?php
					// Reuse catalog cards with a homepage-only product-detail action.
					$previous_showcase = wc_get_loop_prop( 'foxfire_homepage_showcase', false );
					wc_set_loop_prop( 'foxfire_homepage_showcase', true );
					while ( $featured_q->have_posts() ) :
						$featured_q->the_post();
						wc_get_template_part( 'content', 'product' );
					endwhile;
					wc_set_loop_prop( 'foxfire_homepage_showcase', $previous_showcase );
					wp_reset_postdata();
					?>
				</ul>
			</div>
		<?php else : ?>
			<p class="ff-featured-section__empty"><?php esc_html_e( 'Products will appear here once published.', 'foxfire-child' ); ?></p>
		<?php endif; ?>
	</section>

	<!-- 5. Testing & COA Callout Banner -->
	<section class="ff-home-coa-banner ff-reveal" aria-labelledby="ff-coa-banner-heading">
		<div>
			<div class="ff-home-coa-banner__header">
				<span class="ff-home-coa-banner__icon" aria-hidden="true">
					<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
						<path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
						<path d="m9 12 2 2 4-4"/>
					</svg>
				</span>
				<span class="ff-home-coa-banner__badge"><?php esc_html_e( 'Testing & COAs', 'foxfire-child' ); ?></span>
			</div>
			<h2 id="ff-coa-banner-heading" class="ff-home-coa-banner__title">
				<?php echo esc_html( foxfire_get_managed_content( 'home_coa_title', __( "Know What's Behind Every Vial.", 'foxfire-child' ) ) ); ?>
			</h2>
			<p class="ff-home-coa-banner__desc">
				<?php echo esc_html( foxfire_get_managed_content( 'home_coa_description', __( 'Access available third-party testing and batch-specific documentation for Foxfire research compounds. Search by product, batch, or lot number to find the available COA.', 'foxfire-child' ) ) ); ?>
			</p>
		</div>

		<div class="ff-home-coa-banner__actions">
			<a href="<?php echo esc_url( $testing_url ); ?>" class="ff-home-coa-banner__cta">
				<span><?php esc_html_e( 'VIEW TESTING & COAs', 'foxfire-child' ); ?></span>
				<span aria-hidden="true">→</span>
			</a>
		</div>
	</section>

	<!-- 6. Frequently Asked Questions -->
	<section class="ff-home-faq ff-reveal" aria-labelledby="ff-home-faq-heading">
		<div class="ff-home-faq__inner">
			<div class="ff-home-faq__header">
				<span class="ff-home-faq__eyebrow"><?php esc_html_e( 'Help & Information', 'foxfire-child' ); ?></span>
				<h2 id="ff-home-faq-heading" class="ff-home-faq__title">
					<?php esc_html_e( 'Frequently Asked Questions', 'foxfire-child' ); ?>
				</h2>
			</div>

			<div class="ff-home-faq__list">
				<?php if ( ! empty( $managed_faqs ) ) : ?>
					<?php foreach ( $managed_faqs as $faq_position => $faq ) : ?>
						<details class="ff-home-faq__item" <?php echo 0 === $faq_position ? 'open' : ''; ?>>
							<summary class="ff-home-faq__question">
								<span class="ff-home-faq__q-text"><?php echo esc_html( $faq['question'] ); ?></span>
								<span class="ff-home-faq__icon" aria-hidden="true">
									<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"/></svg>
								</span>
							</summary>
							<div class="ff-home-faq__answer"><p><?php echo esc_html( $faq['answer'] ); ?></p></div>
						</details>
					<?php endforeach; ?>
				<?php endif; ?>
			</div>
		</div>
	</section>

	<!-- 7. Pre-footer CTA -->
	<section class="ff-site-cta ff-reveal" aria-labelledby="ff-home-closing-cta-heading">
		<div class="ff-site-cta__content">
			<p class="ff-site-cta__eyebrow"><?php esc_html_e( 'Order Online', 'foxfire-child' ); ?></p>
			<h2 id="ff-home-closing-cta-heading" class="ff-site-cta__title">
					<?php echo esc_html( foxfire_get_managed_content( 'home_closing_title', __( 'Ready to Explore Foxfire?', 'foxfire-child' ) ) ); ?>
			</h2>
			<p class="ff-site-cta__description">
					<?php echo esc_html( foxfire_get_managed_content( 'home_closing_description', __( 'Browse our research compounds, review available testing documentation, and find the products that fit your research needs.', 'foxfire-child' ) ) ); ?>
			</p>
		</div>

		<div class="ff-site-cta__actions">
				<a href="<?php echo esc_url( $shop_url ); ?>" class="ff-site-cta__button ff-site-cta__button--primary">
					<?php esc_html_e( 'BROWSE RESEARCH COMPOUNDS', 'foxfire-child' ); ?>
				</a>
		</div>
	</section>
</div>

<?php
get_footer();
