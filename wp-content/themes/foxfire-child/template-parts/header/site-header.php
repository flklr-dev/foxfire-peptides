<?php
/**
 * Site header — logo, primary nav, account icon, foxed cart button.
 *
 * @package Foxfire_Child
 */

defined( 'ABSPATH' ) || exit;

$shop_url     = foxfire_get_shop_url();
$categories   = foxfire_get_nav_product_categories();
$cart_url     = foxfire_get_wc_page_url( 'cart' );
$account_url  = foxfire_get_wc_page_url( 'myaccount' );
$testing_url  = foxfire_get_page_url( 'testing-coa', '/testing-coa/' );
$about_url    = foxfire_get_page_url( 'about', '/about/' );
$contact_url  = foxfire_get_page_url( 'contact', '/contact/' );
$cart_count   = foxfire_get_cart_count();
$nav_panel_id = 'ff-primary-nav';
?>

<a class="skip-link screen-reader-text" href="#main-content">
	<?php esc_html_e( 'Skip to content', 'foxfire-child' ); ?>
</a>

<div class="ff-site-header">
	<div class="ff-site-header__inner">
		<!-- Brand Logo -->
		<div class="ff-site-header__brand">
			<?php if ( has_custom_logo() ) : ?>
				<div class="ff-site-header__logo">
					<?php the_custom_logo(); ?>
				</div>
			<?php else : ?>
				<a class="ff-site-header__logo-text" href="<?php echo esc_url( home_url( '/' ) ); ?>">
					<span class="ff-site-header__logo-mark" aria-hidden="true">FF</span>
					<span class="ff-site-header__logo-name"><?php bloginfo( 'name' ); ?></span>
				</a>
			<?php endif; ?>
		</div>

		<!-- Mobile Menu Toggle -->
		<button
			type="button"
			class="ff-nav-toggle"
			aria-expanded="false"
			aria-controls="<?php echo esc_attr( $nav_panel_id ); ?>"
			data-ff-nav-toggle
		>
			<span class="ff-nav-toggle__icon" aria-hidden="true"></span>
			<span class="ff-nav-toggle__label"><?php esc_html_e( 'Menu', 'foxfire-child' ); ?></span>
		</button>

		<!-- Main Nav Wrap -->
		<div class="ff-site-header__nav-wrap" data-ff-nav-panel>
			<nav
				id="<?php echo esc_attr( $nav_panel_id ); ?>"
				class="ff-primary-nav"
				aria-label="<?php esc_attr_e( 'Primary', 'foxfire-child' ); ?>"
			>
				<ul class="ff-primary-nav__list">
					<li class="ff-primary-nav__item ff-primary-nav__item--has-dropdown">
						<div class="ff-primary-nav__shop-row">
							<a class="ff-primary-nav__link" href="<?php echo esc_url( $shop_url ); ?>">
								<?php esc_html_e( 'Shop', 'foxfire-child' ); ?>
							</a>
							<button
								type="button"
								class="ff-primary-nav__submenu-toggle"
								aria-expanded="false"
								aria-controls="ff-shop-categories"
								data-ff-submenu-toggle
							>
								<span class="screen-reader-text"><?php esc_html_e( 'Show product categories', 'foxfire-child' ); ?></span>
								<span class="ff-primary-nav__submenu-icon" aria-hidden="true"></span>
							</button>
						</div>
						<ul id="ff-shop-categories" class="ff-primary-nav__dropdown" data-ff-submenu>
							<?php if ( ! empty( $categories ) ) : ?>
								<?php foreach ( $categories as $category ) : ?>
									<?php if ( ! $category instanceof WP_Term ) : ?>
										<?php continue; ?>
									<?php endif; ?>
									<li>
										<a href="<?php echo esc_url( get_term_link( $category ) ); ?>">
											<?php echo esc_html( foxfire_get_category_display_name( $category->name ) ); ?>
										</a>
									</li>
								<?php endforeach; ?>
							<?php else : ?>
								<li class="ff-primary-nav__dropdown-note">
									<span><?php esc_html_e( 'Categories coming soon', 'foxfire-child' ); ?></span>
								</li>
							<?php endif; ?>
						</ul>
					</li>

					<li class="ff-primary-nav__item">
						<a class="ff-primary-nav__link" href="<?php echo esc_url( $testing_url ); ?>">
							<?php esc_html_e( 'Testing/COA', 'foxfire-child' ); ?>
						</a>
					</li>

					<li class="ff-primary-nav__item">
						<a class="ff-primary-nav__link" href="<?php echo esc_url( $about_url ); ?>">
							<?php esc_html_e( 'About', 'foxfire-child' ); ?>
						</a>
					</li>

					<li class="ff-primary-nav__item">
						<a class="ff-primary-nav__link" href="<?php echo esc_url( $contact_url ); ?>">
							<?php esc_html_e( 'Contact', 'foxfire-child' ); ?>
						</a>
					</li>

					<!-- Mobile Drawer Only Links -->
					<li class="ff-primary-nav__item ff-primary-nav__item--mobile-only">
						<a class="ff-primary-nav__link" href="<?php echo esc_url( $account_url ); ?>">
							<?php esc_html_e( 'Account', 'foxfire-child' ); ?>
						</a>
					</li>

					<li class="ff-primary-nav__item ff-primary-nav__item--mobile-only">
						<a class="ff-primary-nav__link" href="<?php echo esc_url( $cart_url ); ?>">
							<span><?php esc_html_e( 'Cart', 'foxfire-child' ); ?></span>
							<?php foxfire_render_cart_count_badge(); ?>
						</a>
					</li>
				</ul>
			</nav>

			<!-- Header Actions (Account Icon + Solid Fox Orange Cart Button) -->
			<div class="ff-site-header__actions">
				<a
					class="ff-header-account-btn"
					href="<?php echo esc_url( $account_url ); ?>"
					aria-label="<?php esc_attr_e( 'My Account', 'foxfire-child' ); ?>"
					title="<?php esc_attr_e( 'My Account', 'foxfire-child' ); ?>"
				>
					<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
						<path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"></path>
						<circle cx="12" cy="7" r="4"></circle>
					</svg>
				</a>

				<a
					class="ff-header-cart-btn ff-header-cart-btn--primary"
					href="<?php echo esc_url( $cart_url ); ?>"
					aria-label="<?php esc_attr_e( 'View shopping cart', 'foxfire-child' ); ?>"
				>
					<span class="ff-header-cart-btn__icon" aria-hidden="true">
						<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
							<circle cx="9" cy="21" r="1"></circle>
							<circle cx="20" cy="21" r="1"></circle>
							<path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path>
						</svg>
					</span>
					<span class="ff-header-cart-btn__label"><?php esc_html_e( 'Cart', 'foxfire-child' ); ?></span>
					<?php foxfire_render_cart_count_badge(); ?>
				</a>
			</div>
		</div>
	</div>
</div>
