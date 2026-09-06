<?php
/**
 * Global shell — header, footer, navigation (Chunk 1D).
 *
 * @package Foxfire_Child
 */

defined( 'ABSPATH' ) || exit;

/**
 * Register theme supports used by the shell.
 */
function foxfire_shell_setup(): void {
	add_theme_support(
		'custom-logo',
		array(
			'height'      => 80,
			'width'       => 240,
			'flex-height' => true,
			'flex-width'  => true,
		)
	);
}
add_action( 'after_setup_theme', 'foxfire_shell_setup', 30 );

/**
 * Replace Storefront default header/footer with Foxfire shell.
 */
function foxfire_shell_replace_storefront_masthead(): void {
	$header_hooks = array(
		'storefront_header_container'                 => 0,
		'storefront_skip_links'                       => 5,
		'storefront_social_icons'                     => 10,
		'storefront_site_branding'                    => 20,
		'storefront_secondary_navigation'             => 30,
		'storefront_product_search'                   => 40,
		'storefront_header_container_close'           => 41,
		'storefront_primary_navigation_wrapper'       => 42,
		'storefront_primary_navigation'               => 50,
		'storefront_header_cart'                      => 60,
		'storefront_primary_navigation_wrapper_close' => 68,
	);

	foreach ( $header_hooks as $callback => $priority ) {
		remove_action( 'storefront_header', $callback, $priority );
	}

	add_action( 'storefront_header', 'foxfire_render_site_header', 10 );

	remove_action( 'storefront_footer', 'storefront_footer_widgets', 10 );
	remove_action( 'storefront_footer', 'storefront_credit', 20 );
	remove_action( 'storefront_footer', 'storefront_handheld_footer_bar', 999 );
	add_action( 'storefront_footer', 'foxfire_render_site_footer', 10 );
}
add_action( 'wp', 'foxfire_shell_replace_storefront_masthead' );

/**
 * Enqueue shell assets.
 */
function foxfire_shell_enqueue_assets(): void {
	$shell_css = FOXFIRE_CHILD_DIR . '/assets/css/shell.css';
	$shell_js  = FOXFIRE_CHILD_DIR . '/assets/js/shell.js';

	wp_enqueue_style(
		'foxfire-shell',
		FOXFIRE_CHILD_URI . '/assets/css/shell.css',
		array( 'foxfire-base' ),
		file_exists( $shell_css ) ? (string) filemtime( $shell_css ) : FOXFIRE_CHILD_VERSION
	);

	wp_enqueue_script(
		'foxfire-shell',
		FOXFIRE_CHILD_URI . '/assets/js/shell.js',
		array(),
		file_exists( $shell_js ) ? (string) filemtime( $shell_js ) : FOXFIRE_CHILD_VERSION,
		true
	);
}
add_action( 'wp_enqueue_scripts', 'foxfire_shell_enqueue_assets', 30 );

/**
 * Resolve a published page URL by slug with a safe fallback.
 */
function foxfire_get_page_url( string $slug, string $fallback_path = '' ): string {
	$slugs_to_check = array( $slug );
	if ( 'about' === $slug ) {
		$slugs_to_check[] = 'about-us';
	} elseif ( 'about-us' === $slug ) {
		$slugs_to_check[] = 'about';
	} elseif ( 'contact' === $slug ) {
		$slugs_to_check[] = 'contact-us';
	} elseif ( 'contact-us' === $slug ) {
		$slugs_to_check[] = 'contact';
	}

	foreach ( $slugs_to_check as $s ) {
		$page = get_page_by_path( $s, OBJECT, 'page' );
		if ( $page instanceof WP_Post && 'publish' === $page->post_status ) {
			return get_permalink( $page );
		}
	}

	if ( '' !== $fallback_path ) {
		return home_url( $fallback_path );
	}

	return home_url( '/' . trim( $slug, '/' ) . '/' );
}

/**
 * Shop page URL.
 */
function foxfire_get_shop_url(): string {
	if ( function_exists( 'wc_get_page_permalink' ) ) {
		$shop_url = wc_get_page_permalink( 'shop' );

		if ( is_string( $shop_url ) && '' !== $shop_url ) {
			return $shop_url;
		}
	}

	return foxfire_get_page_url( 'shop', '/shop/' );
}

/**
 * WooCommerce endpoint URL helper.
 */
function foxfire_get_wc_page_url( string $page ): string {
	if ( function_exists( 'wc_get_page_permalink' ) ) {
		$url = wc_get_page_permalink( $page );

		if ( is_string( $url ) && '' !== $url ) {
			return $url;
		}
	}

	$slug_map = array(
		'cart'       => 'cart',
		'checkout'   => 'checkout',
		'myaccount'  => 'my-account',
	);

	$slug = $slug_map[ $page ] ?? $page;

	return foxfire_get_page_url( $slug, '/' . $slug . '/' );
}

/**
 * Product categories for the Shop dropdown.
 *
 * @return WP_Term[]
 */
function foxfire_get_nav_product_categories(): array {
	if ( ! taxonomy_exists( 'product_cat' ) ) {
		return array();
	}

	$terms = get_terms(
		array(
			'taxonomy'   => 'product_cat',
			'hide_empty' => true,
			'parent'     => 0,
			'orderby'    => 'name',
			'order'      => 'ASC',
		)
	);

	if ( is_wp_error( $terms ) || ! is_array( $terms ) ) {
		return array();
	}

	$default_slug = 'uncategorized';

	return array_values(
		array_filter(
			$terms,
			static function ( $term ) use ( $default_slug ): bool {
				return $term instanceof WP_Term && $default_slug !== $term->slug;
			}
		)
	);
}

/**
 * Display name for a product category.
 *
 * Placeholder taxonomy names carry a "(TBD)" suffix for internal tracking
 * (PRD §5.2); that marker must not surface in customer-facing navigation.
 */
function foxfire_get_category_display_name( string $name ): string {
	return trim( str_replace( '(TBD)', '', $name ) );
}

/**
 * Cart item count for header badge.
 */
function foxfire_get_cart_count(): int {
	if ( ! function_exists( 'WC' ) || ! WC()->cart ) {
		return 0;
	}

	return (int) WC()->cart->get_cart_contents_count();
}

/**
 * Keep header cart count in sync after AJAX add-to-cart.
 *
 * @param array<string, string> $fragments Cart fragments.
 * @return array<string, string>
 */
function foxfire_cart_count_fragment( array $fragments ): array {
	ob_start();
	foxfire_render_cart_count_badge();
	$fragments['.ff-cart-count'] = (string) ob_get_clean();

	return $fragments;
}
add_filter( 'woocommerce_add_to_cart_fragments', 'foxfire_cart_count_fragment' );

/**
 * Output cart count badge markup.
 */
function foxfire_render_cart_count_badge(): void {
	$count = foxfire_get_cart_count();
	?>
	<span class="ff-cart-count" aria-label="<?php echo esc_attr( sprintf( _n( '%d item in cart', '%d items in cart', $count, 'foxfire-child' ), $count ) ); ?>">
		<?php echo esc_html( (string) $count ); ?>
	</span>
	<?php
}

/**
 * Render site header.
 */
function foxfire_render_site_header(): void {
	get_template_part( 'template-parts/header/site-header' );
}

/**
 * Render site footer.
 */
function foxfire_render_site_footer(): void {
	get_template_part( 'template-parts/footer/site-footer' );
}

/**
 * Mark the main content landmark for skip-link targeting.
 */
function foxfire_shell_content_landmark(): void {
	echo '<div id="main-content" class="ff-main-content-landmark" tabindex="-1"></div>';
}
add_action( 'storefront_content_top', 'foxfire_shell_content_landmark', 1 );
