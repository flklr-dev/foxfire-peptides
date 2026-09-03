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
$hero_img    = FOXFIRE_CHILD_URI . '/assets/images/hero-peptides.png';
$categories  = function_exists( 'foxfire_get_homepage_categories' ) ? foxfire_get_homepage_categories() : array();
$featured_q  = function_exists( 'foxfire_get_homepage_products' ) ? foxfire_get_homepage_products( 8 ) : null;
?>

<div class="ff-homepage">
	<!-- 1. Hero Section -->
	<section class="ff-home-hero" aria-labelledby="ff-hero-title">
		<div class="ff-home-hero__inner">
			<div class="ff-home-hero__content ff-reveal">
				<p class="ff-home-hero__eyebrow">
					<?php esc_html_e( 'Analytical Research Standards', 'foxfire-child' ); ?>
				</p>

				<h1 id="ff-hero-title" class="ff-home-hero__title">
					<?php esc_html_e( 'Quality Research Peptides With Transparent Testing', 'foxfire-child' ); ?>
				</h1>

				<p class="ff-home-hero__lead">
					<?php esc_html_e( 'Laboratory research compounds with batch-specific Certificates of Analysis (COAs) available for every compound. Straightforward purchasing with 1, 3, and 5 vial options.', 'foxfire-child' ); ?>
				</p>

				<div class="ff-home-hero__actions">
					<a href="<?php echo esc_url( $shop_url ); ?>" class="ff-home-hero__cta-primary">
						<?php esc_html_e( 'Shop Now', 'foxfire-child' ); ?>
					</a>
					<a href="<?php echo esc_url( $testing_url ); ?>" class="ff-home-hero__cta-secondary">
						<?php esc_html_e( 'View Testing & COAs', 'foxfire-child' ); ?>
					</a>
				</div>

				<ul class="ff-home-hero__bullets">
					<li>
						<span class="ff-home-hero__check" aria-hidden="true">✓</span>
						<?php esc_html_e( 'Independently Tested', 'foxfire-child' ); ?>
					</li>
					<li>
						<span class="ff-home-hero__check" aria-hidden="true">✓</span>
						<?php esc_html_e( 'Batch COA Reports', 'foxfire-child' ); ?>
					</li>
					<li>
						<span class="ff-home-hero__check" aria-hidden="true">✓</span>
						<?php esc_html_e( 'Fast, Secure Dispatch', 'foxfire-child' ); ?>
					</li>
				</ul>
			</div>

			<div class="ff-home-hero__media ff-reveal ff-reveal--delay-1">
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
					<h3 class="ff-trust-band__title"><?php esc_html_e( 'Third-Party Tested', 'foxfire-child' ); ?></h3>
					<p class="ff-trust-band__desc"><?php esc_html_e( 'Independent laboratory analytical testing on each batch.', 'foxfire-child' ); ?></p>
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
					<h3 class="ff-trust-band__title"><?php esc_html_e( 'Batch-Matched COAs', 'foxfire-child' ); ?></h3>
					<p class="ff-trust-band__desc"><?php esc_html_e( 'Publicly accessible certificates matching every vial lot.', 'foxfire-child' ); ?></p>
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
					<h3 class="ff-trust-band__title"><?php esc_html_e( 'Fast, Secure Shipping', 'foxfire-child' ); ?></h3>
					<p class="ff-trust-band__desc"><?php esc_html_e( 'Discreet, temperature-stable packaging & prompt fulfillment.', 'foxfire-child' ); ?></p>
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
					<h3 class="ff-trust-band__title"><?php esc_html_e( 'Research Compliant', 'foxfire-child' ); ?></h3>
					<p class="ff-trust-band__desc"><?php esc_html_e( 'Strictly for in-vitro and laboratory research use.', 'foxfire-child' ); ?></p>
				</div>
			</div>
		</div>
	</section>

	<!-- 3. Featured peptides -->
	<section class="ff-featured-section" aria-labelledby="ff-featured-heading">
		<div class="ff-section-header ff-section-header--with-link ff-reveal">
			<div>
				<h2 id="ff-featured-heading" class="ff-section-header__title">
					<?php esc_html_e( 'Featured Research Compounds', 'foxfire-child' ); ?>
				</h2>
			</div>
			<a href="<?php echo esc_url( $shop_url ); ?>" class="ff-section-header__link">
				<?php esc_html_e( 'View full catalog', 'foxfire-child' ); ?>
				<span aria-hidden="true">→</span>
			</a>
		</div>

		<?php if ( $featured_q instanceof WP_Query && $featured_q->have_posts() ) : ?>
			<div class="woocommerce ff-reveal ff-reveal--delay-1">
				<ul class="products ff-featured-grid">
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
			<p class="ff-featured-section__empty"><?php esc_html_e( 'Products will appear here once published.', 'foxfire-child' ); ?></p>
		<?php endif; ?>
	</section>

	<!-- 4. Research Category Cards -->
	<?php if ( ! empty( $categories ) ) : ?>
		<section class="ff-categories-section" aria-labelledby="ff-categories-heading">
			<div class="ff-section-header ff-reveal">
				<h2 id="ff-categories-heading" class="ff-section-header__title">
					<?php esc_html_e( 'Browse by Research Category', 'foxfire-child' ); ?>
				</h2>
			</div>

			<div class="ff-category-grid">
				<?php
				$cat_delay = 1;
				foreach ( $categories as $cat ) :
					$delay_class = 'ff-reveal--delay-' . min( $cat_delay, 4 );
					$cat_delay++;
					?>
					<a href="<?php echo esc_url( $cat['link'] ); ?>" class="ff-category-card ff-reveal <?php echo esc_attr( $delay_class ); ?>">
						<div class="ff-category-card__icon" aria-hidden="true">
							<?php echo $cat['icon']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
						</div>
						<h3 class="ff-category-card__title"><?php echo esc_html( $cat['name'] ); ?></h3>
						<p class="ff-category-card__count">
							<?php
							printf(
								/* translators: %d: number of products */
								esc_html( _n( '%d product available', '%d products available', $cat['count'], 'foxfire-child' ) ),
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
				<?php esc_html_e( 'Every batch has a matching laboratory report.', 'foxfire-child' ); ?>
			</h2>
			<p class="ff-home-coa-banner__desc">
				<?php esc_html_e( 'Look up your batch number in our directory to view independent testing documentation.', 'foxfire-child' ); ?>
			</p>
		</div>

		<div class="ff-home-coa-banner__actions">
			<a href="<?php echo esc_url( $testing_url ); ?>" class="ff-home-coa-banner__cta">
				<span><?php esc_html_e( 'Access Batch Directory', 'foxfire-child' ); ?></span>
				<span aria-hidden="true">→</span>
			</a>
		</div>
	</section>

	<!-- 6. Quality & Verification Approach -->
	<section class="ff-home-standards" aria-labelledby="ff-standards-heading">
		<div class="ff-home-standards__inner">
			<div class="ff-home-standards__header ff-reveal">
				<span class="ff-home-standards__eyebrow"><?php esc_html_e( 'Quality Approach', 'foxfire-child' ); ?></span>
				<h2 id="ff-standards-heading" class="ff-home-standards__title">
					<?php esc_html_e( 'Independent Testing & Simple Shopping', 'foxfire-child' ); ?>
				</h2>
			</div>

			<div class="ff-standards-grid">
				<div class="ff-standard-card ff-reveal ff-reveal--delay-1">
					<span class="ff-standard-card__step" aria-hidden="true">1</span>
					<h3 class="ff-standard-card__title"><?php esc_html_e( 'Independent Verification', 'foxfire-child' ); ?></h3>
					<p class="ff-standard-card__text">
						<?php esc_html_e( 'Every compound batch is tested by third-party analytical laboratories to confirm quality.', 'foxfire-child' ); ?>
					</p>
				</div>

				<div class="ff-standard-card ff-reveal ff-reveal--delay-2">
					<span class="ff-standard-card__step" aria-hidden="true">2</span>
					<h3 class="ff-standard-card__title"><?php esc_html_e( 'Batch Identification', 'foxfire-child' ); ?></h3>
					<p class="ff-standard-card__text">
						<?php esc_html_e( 'Printed batch lot codes on every vial allow instant lookup of matching documentation.', 'foxfire-child' ); ?>
					</p>
				</div>

				<div class="ff-standard-card ff-reveal ff-reveal--delay-3">
					<span class="ff-standard-card__step" aria-hidden="true">3</span>
					<h3 class="ff-standard-card__title"><?php esc_html_e( 'Multi-Vial Savings', 'foxfire-child' ); ?></h3>
					<p class="ff-standard-card__text">
						<?php esc_html_e( 'Choose 1, 3, or 5 vials on a single page with automatic quantity savings and accurate stock deduction.', 'foxfire-child' ); ?>
					</p>
				</div>
			</div>
		</div>
	</section>

	<!-- 7. Frequently Asked Questions -->
	<section class="ff-home-faq ff-reveal" aria-labelledby="ff-home-faq-heading">
		<div class="ff-home-faq__inner">
			<div class="ff-home-faq__header">
				<span class="ff-home-faq__eyebrow"><?php esc_html_e( 'Help & Information', 'foxfire-child' ); ?></span>
				<h2 id="ff-home-faq-heading" class="ff-home-faq__title">
					<?php esc_html_e( 'Frequently Asked Questions', 'foxfire-child' ); ?>
				</h2>
			</div>

			<div class="ff-home-faq__list">
				<!-- Q1 -->
				<details class="ff-home-faq__item" open>
					<summary class="ff-home-faq__question">
						<span class="ff-home-faq__q-text"><?php esc_html_e( 'How do I place an order?', 'foxfire-child' ); ?></span>
						<span class="ff-home-faq__icon" aria-hidden="true">
							<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"/></svg>
						</span>
					</summary>
					<div class="ff-home-faq__answer">
						<p><?php esc_html_e( 'Browse our products, select the available strength and quantity, add the item to your cart, and complete checkout using your preferred payment method.', 'foxfire-child' ); ?></p>
					</div>
				</details>

				<!-- Q2 -->
				<details class="ff-home-faq__item">
					<summary class="ff-home-faq__question">
						<span class="ff-home-faq__q-text"><?php esc_html_e( 'Can I buy 1, 3, or 5 vials?', 'foxfire-child' ); ?></span>
						<span class="ff-home-faq__icon" aria-hidden="true">
							<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"/></svg>
						</span>
					</summary>
					<div class="ff-home-faq__answer">
						<p><?php esc_html_e( 'Yes. Available quantity options are shown directly on each product page. Some products may offer 1, 3, and 5 vial options, while others may have different availability.', 'foxfire-child' ); ?></p>
					</div>
				</details>

				<!-- Q3 -->
				<details class="ff-home-faq__item">
					<summary class="ff-home-faq__question">
						<span class="ff-home-faq__q-text"><?php esc_html_e( 'Do larger vial quantities receive a discount?', 'foxfire-child' ); ?></span>
						<span class="ff-home-faq__icon" aria-hidden="true">
							<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"/></svg>
						</span>
					</summary>
					<div class="ff-home-faq__answer">
						<p><?php esc_html_e( 'Some products may offer savings when purchasing larger quantities. Available pricing and discounts are shown on the product page.', 'foxfire-child' ); ?></p>
					</div>
				</details>

				<!-- Q4 -->
				<details class="ff-home-faq__item">
					<summary class="ff-home-faq__question">
						<span class="ff-home-faq__q-text"><?php esc_html_e( 'Where can I find testing and COA information?', 'foxfire-child' ); ?></span>
						<span class="ff-home-faq__icon" aria-hidden="true">
							<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"/></svg>
						</span>
					</summary>
					<div class="ff-home-faq__answer">
						<p><?php esc_html_e( 'Available batch and testing information can be found on our Testing/COA page. You can also access the corresponding COA report when available.', 'foxfire-child' ); ?></p>
					</div>
				</details>

				<!-- Q5 -->
				<details class="ff-home-faq__item">
					<summary class="ff-home-faq__question">
						<span class="ff-home-faq__q-text"><?php esc_html_e( "How do I find my product's batch or lot number?", 'foxfire-child' ); ?></span>
						<span class="ff-home-faq__icon" aria-hidden="true">
							<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"/></svg>
						</span>
					</summary>
					<div class="ff-home-faq__answer">
						<p><?php esc_html_e( "The batch or lot number can be found on the product packaging or vial. You can use that information to look up available documentation in our Testing/COA directory.", 'foxfire-child' ); ?></p>
					</div>
				</details>

				<!-- Q6 -->
				<details class="ff-home-faq__item">
					<summary class="ff-home-faq__question">
						<span class="ff-home-faq__q-text"><?php esc_html_e( 'What payment methods are available?', 'foxfire-child' ); ?></span>
						<span class="ff-home-faq__icon" aria-hidden="true">
							<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"/></svg>
						</span>
					</summary>
					<div class="ff-home-faq__answer">
						<p><?php esc_html_e( 'Foxfire plans to support payment options including Wise, Zelle, and GCash, subject to availability. Clear payment instructions will be provided during checkout.', 'foxfire-child' ); ?></p>
					</div>
				</details>

				<!-- Q7 -->
				<details class="ff-home-faq__item">
					<summary class="ff-home-faq__question">
						<span class="ff-home-faq__q-text"><?php esc_html_e( 'Is an account required to order?', 'foxfire-child' ); ?></span>
						<span class="ff-home-faq__icon" aria-hidden="true">
							<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"/></svg>
						</span>
					</summary>
					<div class="ff-home-faq__answer">
						<p><?php esc_html_e( 'You can browse our products freely. Creating an account gives you access to your orders and other account features.', 'foxfire-child' ); ?></p>
					</div>
				</details>

				<!-- Q8 -->
				<details class="ff-home-faq__item">
					<summary class="ff-home-faq__question">
						<span class="ff-home-faq__q-text"><?php esc_html_e( 'Are these products for human consumption?', 'foxfire-child' ); ?></span>
						<span class="ff-home-faq__icon" aria-hidden="true">
							<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"/></svg>
						</span>
					</summary>
					<div class="ff-home-faq__answer">
						<p><?php esc_html_e( 'Foxfire Peptides provides these products for research-use purposes only. Foxfire does not provide medical guidance, dosing advice, or administration recommendations.', 'foxfire-child' ); ?></p>
					</div>
				</details>
			</div>
		</div>
	</section>

	<!-- 8. Pre-footer CTA -->
	<section class="ff-home-cta-card ff-reveal" aria-labelledby="ff-cta-card-heading">
		<div class="ff-home-cta-card__inner">
			<div class="ff-home-cta-card__content">
				<span class="ff-home-cta-card__eyebrow"><?php esc_html_e( 'Order Online', 'foxfire-child' ); ?></span>
				<h2 id="ff-cta-card-heading" class="ff-home-cta-card__title">
					<?php esc_html_e( 'Ready to Order Research Peptides?', 'foxfire-child' ); ?>
				</h2>
				<p class="ff-home-cta-card__desc">
					<?php esc_html_e( 'Explore our catalog of research peptides with batch-specific Certificates of Analysis available for every sequence.', 'foxfire-child' ); ?>
				</p>
			</div>

			<div class="ff-home-cta-card__actions">
				<a href="<?php echo esc_url( $shop_url ); ?>" class="ff-home-cta-card__btn-primary">
					<?php esc_html_e( 'Shop Catalog', 'foxfire-child' ); ?>
				</a>
				<a href="<?php echo esc_url( $testing_url ); ?>" class="ff-home-cta-card__btn-secondary">
					<span><?php esc_html_e( 'Look Up Batch COAs', 'foxfire-child' ); ?></span>
					<span class="ff-btn-arrow" aria-hidden="true">↗</span>
				</a>
			</div>
		</div>
	</section>
</div>

<?php
get_footer();
