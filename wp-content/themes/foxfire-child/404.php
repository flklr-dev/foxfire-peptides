<?php
/**
 * Custom 404 Error Template — Foxfire Peptides.
 *
 * Clean, branded 404 experience with search bar and "You May Also Like" recommendation grid.
 *
 * @package Foxfire_Child
 */

get_header(); ?>

<div id="primary" class="content-area ff-404-page">
	<main id="main" class="site-main" role="main">
		<div class="ff-404-container">
			
			<div class="ff-404-hero">
				<header class="page-header">
					<h1 class="ff-404-title"><?php esc_html_e( 'Oops! That page can’t be found.', 'foxfire-child' ); ?></h1>
				</header>

				<p class="ff-404-text">
					<?php esc_html_e( 'Nothing was found at this location. Try searching, or check out the links below.', 'foxfire-child' ); ?>
				</p>

				<div class="ff-404-search">
					<form role="search" method="get" class="ff-404-search__form" action="<?php echo esc_url( home_url( '/' ) ); ?>">
						<label class="screen-reader-text" for="ff-404-search-field"><?php esc_html_e( 'Search for:', 'foxfire-child' ); ?></label>
						<input
							type="search"
							id="ff-404-search-field"
							class="ff-404-search__input"
							placeholder="<?php esc_attr_e( 'Search research compounds...', 'foxfire-child' ); ?>"
							value="<?php echo get_search_query(); ?>"
							name="s"
						/>
						<input type="hidden" name="post_type" value="product" />
						<button type="submit" class="ff-404-search__submit">
							<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
								<circle cx="11" cy="11" r="8"></circle>
								<line x1="21" y1="21" x2="16.65" y2="16.65"></line>
							</svg>
							<span><?php esc_html_e( 'Search', 'foxfire-child' ); ?></span>
						</button>
					</form>
				</div>

				<div class="ff-404-actions">
					<a href="<?php echo esc_url( foxfire_get_shop_url() ); ?>" class="ff-404-btn ff-404-btn--primary">
						<?php esc_html_e( 'Browse Shop Catalog', 'foxfire-child' ); ?>
					</a>
					<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="ff-404-btn ff-404-btn--outline">
						<?php esc_html_e( 'Return to Home', 'foxfire-child' ); ?>
					</a>
				</div>
			</div>

			<?php if ( function_exists( 'storefront_is_woocommerce_activated' ) && storefront_is_woocommerce_activated() ) : ?>
				<section class="ff-404-recommendations" aria-label="<?php esc_attr_e( 'You May Also Like', 'foxfire-child' ); ?>">
					<div class="ff-404-recommendations__header">
						<h2 class="ff-404-recommendations__title"><?php esc_html_e( 'You May Also Like', 'foxfire-child' ); ?></h2>
					</div>
					<?php echo do_shortcode( '[products limit="4" columns="4" orderby="popularity"]' ); ?>
				</section>
			<?php endif; ?>

		</div>
	</main>
</div>

<?php
get_footer();
