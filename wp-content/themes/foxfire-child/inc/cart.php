<?php
/**
 * Cart page customizations & AJAX Cart Drawer — Chunk 1J.
 *
 * @package Foxfire_Child
 */

defined( 'ABSPATH' ) || exit;

/**
 * Suppress Storefront default breadcrumbs and page title on cart page.
 * Unhook cross-sells from inside the sidebar collaterals.
 */
function foxfire_cart_cleanup_layout(): void {
	if ( is_cart() ) {
		remove_action( 'storefront_before_content', 'woocommerce_breadcrumb', 10 );
		remove_action( 'woocommerce_before_main_content', 'woocommerce_breadcrumb', 20 );
		remove_action( 'storefront_page', 'storefront_page_header', 10 );

		// Remove cross-sells from the sidebar totals column
		remove_action( 'woocommerce_cart_collaterals', 'woocommerce_cross_sell_display' );
	}
}
add_action( 'wp', 'foxfire_cart_cleanup_layout', 10 );

/**
 * Render cross-sells cleanly below the 2-column cart grid (full width).
 */
function foxfire_render_cart_cross_sells(): void {
	if ( is_cart() && is_object( WC()->cart ) && ! WC()->cart->is_empty() ) {
		echo '<div class="ff-cart-cross-sells-wrap">';
		woocommerce_cross_sell_display( 4, 4 );
		echo '</div>';
	}
}
add_action( 'woocommerce_after_cart', 'foxfire_render_cart_cross_sells', 15 );

/**
 * Render the slide-over cart drawer in the footer.
 */
function foxfire_render_cart_drawer_footer(): void {
	get_template_part( 'template-parts/cart/cart-drawer' );
}
add_action( 'wp_footer', 'foxfire_render_cart_drawer_footer', 20 );

/**
 * Enqueue cart drawer and cart page scripts.
 */
function foxfire_cart_enqueue_scripts(): void {
	$version = defined( 'FOXFIRE_CHILD_VERSION' ) ? FOXFIRE_CHILD_VERSION : '1.2.0';

	// Drawer script (all pages)
	$drawer_js_path = FOXFIRE_CHILD_DIR . '/assets/js/cart-drawer.js';
	wp_enqueue_script(
		'foxfire-cart-drawer',
		FOXFIRE_CHILD_URI . '/assets/js/cart-drawer.js',
		array( 'jquery' ),
		file_exists( $drawer_js_path ) ? (string) filemtime( $drawer_js_path ) : $version,
		true
	);

	wp_localize_script(
		'foxfire-cart-drawer',
		'wc_cart_drawer_params',
		array(
			'ajax_url' => admin_url( 'admin-ajax.php' ),
			'nonce'    => wp_create_nonce( 'foxfire_cart_drawer_nonce' ),
		)
	);

	// Full Cart Page script (only on /cart/)
	if ( is_cart() ) {
		$cart_js_path = FOXFIRE_CHILD_DIR . '/assets/js/cart.js';
		wp_enqueue_script(
			'foxfire-cart-js',
			FOXFIRE_CHILD_URI . '/assets/js/cart.js',
			array( 'jquery' ),
			file_exists( $cart_js_path ) ? (string) filemtime( $cart_js_path ) : $version,
			true
		);
	}
}
add_action( 'wp_enqueue_scripts', 'foxfire_cart_enqueue_scripts', 30 );

/**
 * Helper to render the complete drawer HTML for AJAX returns.
 */
function foxfire_get_cart_drawer_html(): string {
	ob_start();
	get_template_part( 'template-parts/cart/cart-drawer' );
	$html = (string) ob_get_clean();

	// If the template outputs the overlay as a sibling, extract only the <aside> tag for fragment replacement
	if ( preg_match( '/<aside\s+id="ff-cart-drawer"[\s\S]*?<\/aside>/i', $html, $matches ) ) {
		return $matches[0];
	}

	return $html;
}

/**
 * Add cart drawer fragment to standard WooCommerce AJAX cart fragments.
 *
 * @param array<string, string> $fragments WooCommerce cart fragments.
 * @return array<string, string>
 */
function foxfire_cart_fragments( array $fragments ): array {
	$fragments['#ff-cart-drawer'] = foxfire_get_cart_drawer_html();

	$cart_count = is_object( WC()->cart ) ? WC()->cart->get_cart_contents_count() : 0;
	$fragments['.ff-header-cart-count'] = sprintf(
		'<span class="ff-header-cart-count%s">%s</span>',
		$cart_count > 0 ? '' : ' ff-header-cart-count--empty',
		esc_html( (string) $cart_count )
	);

	return $fragments;
}
add_filter( 'woocommerce_add_to_cart_fragments', 'foxfire_cart_fragments', 20 );

/**
 * AJAX handler for updating drawer item quantity.
 */
function foxfire_ajax_update_drawer_item(): void {
	check_ajax_referer( 'foxfire_cart_drawer_nonce', 'nonce' );

	$cart_key = isset( $_POST['cart_key'] ) ? sanitize_text_field( wp_unslash( $_POST['cart_key'] ) ) : '';
	$quantity = isset( $_POST['quantity'] ) ? (int) $_POST['quantity'] : 0;

	if ( empty( $cart_key ) || ! isset( WC()->cart->get_cart()[ $cart_key ] ) ) {
		wp_send_json_error( array( 'message' => __( 'Item not found in cart.', 'foxfire-child' ) ) );
	}

	if ( $quantity <= 0 ) {
		WC()->cart->remove_cart_item( $cart_key );
	} else {
		WC()->cart->set_quantity( $cart_key, $quantity );
	}

	WC()->cart->calculate_totals();

	wp_send_json_success(
		array(
			'drawer_html' => foxfire_get_cart_drawer_html(),
			'cart_count'  => WC()->cart->get_cart_contents_count(),
			'cart_total'  => WC()->cart->get_cart_subtotal(),
		)
	);
}
add_action( 'wp_ajax_foxfire_update_drawer_item', 'foxfire_ajax_update_drawer_item' );
add_action( 'wp_ajax_nopriv_foxfire_update_drawer_item', 'foxfire_ajax_update_drawer_item' );

/**
 * AJAX handler for removing an item from the drawer.
 */
function foxfire_ajax_remove_drawer_item(): void {
	check_ajax_referer( 'foxfire_cart_drawer_nonce', 'nonce' );

	$cart_key = isset( $_POST['cart_key'] ) ? sanitize_text_field( wp_unslash( $_POST['cart_key'] ) ) : '';

	if ( empty( $cart_key ) || ! isset( WC()->cart->get_cart()[ $cart_key ] ) ) {
		wp_send_json_error( array( 'message' => __( 'Item not found in cart.', 'foxfire-child' ) ) );
	}

	WC()->cart->remove_cart_item( $cart_key );
	WC()->cart->calculate_totals();

	wp_send_json_success(
		array(
			'drawer_html' => foxfire_get_cart_drawer_html(),
			'cart_count'  => WC()->cart->get_cart_contents_count(),
			'cart_total'  => WC()->cart->get_cart_subtotal(),
		)
	);
}
add_action( 'wp_ajax_foxfire_remove_drawer_item', 'foxfire_ajax_remove_drawer_item' );
add_action( 'wp_ajax_nopriv_foxfire_remove_drawer_item', 'foxfire_ajax_remove_drawer_item' );
