<?php
/**
 * Cart page customizations & AJAX Cart Drawer — Chunk 1J.
 *
 * @package Foxfire_Child
 */

defined( 'ABSPATH' ) || exit;

/**
 * Return free shipping minimum threshold ($150.00) across cart, checkout, and shipping rates.
 */
function foxfire_get_free_shipping_threshold(): float {
	return 150.00;
}

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
 * Completely suppress default WooCommerce "Added to cart" message banners across the entire store.
 * The minimalist Center HUD Toast handles confirmation feedback instead.
 */
add_filter( 'wc_add_to_cart_message_html', '__return_false', 99 );

/**
 * Filter out any notices that contain "added to your cart" or "View cart".
 */
function foxfire_suppress_add_to_cart_notices( $message ) {
	if ( is_string( $message ) && ( false !== stripos( $message, 'added to your cart' ) || false !== stripos( $message, 'wc-forward' ) ) ) {
		return '';
	}
	return $message;
}
add_filter( 'woocommerce_add_message', 'foxfire_suppress_add_to_cart_notices', 99 );
add_filter( 'woocommerce_add_success', 'foxfire_suppress_add_to_cart_notices', 99 );

/**
 * Clear any queued 'added to cart' messages from WooCommerce session on cart and checkout pages.
 */
function foxfire_clear_queued_cart_notices(): void {
	if ( function_exists( 'wc_get_notices' ) && function_exists( 'wc_set_notices' ) ) {
		$all_notices = wc_get_notices();
		if ( ! empty( $all_notices['success'] ) ) {
			foreach ( $all_notices['success'] as $key => $notice ) {
				$text = is_array( $notice ) ? ( $notice['notice'] ?? '' ) : (string) $notice;
				if ( false !== stripos( $text, 'added to your cart' ) || false !== stripos( $text, 'wc-forward' ) ) {
					unset( $all_notices['success'][ $key ] );
				}
			}
			$all_notices['success'] = array_values( $all_notices['success'] );
			wc_set_notices( $all_notices );
		}
	}
}
add_action( 'template_redirect', 'foxfire_clear_queued_cart_notices', 5 );
add_action( 'woocommerce_before_cart', 'foxfire_clear_queued_cart_notices', 1 );

/**
 * Customize Cross-Sells section heading.
 */
function foxfire_cross_sells_heading(): string {
	return __( 'You May Be Interested In', 'foxfire-child' );
}
add_filter( 'woocommerce_product_cross_sells_products_heading', 'foxfire_cross_sells_heading' );

/**
 * Ensure "You May Be Interested In" always returns 3 products by backfilling
 * if the products in the cart have fewer than 3 cross-sells assigned.
 *
 * @param int[] $cross_sells Array of cross-sell product IDs.
 * @return int[]
 */
function foxfire_ensure_minimum_cross_sells( array $cross_sells ): array {
	$limit = 3;
	if ( count( $cross_sells ) >= $limit ) {
		return array_slice( $cross_sells, 0, $limit );
	}

	$cart_product_ids = array();
	if ( function_exists( 'WC' ) && is_object( WC()->cart ) ) {
		foreach ( WC()->cart->get_cart() as $cart_item ) {
			$cart_product_ids[] = (int) $cart_item['product_id'];
			if ( ! empty( $cart_item['variation_id'] ) ) {
				$cart_product_ids[] = (int) $cart_item['variation_id'];
			}
		}
	}

	$needed  = $limit - count( $cross_sells );
	$exclude = array_unique( array_merge( $cart_product_ids, $cross_sells ) );

	$backfill = get_posts(
		array(
			'post_type'      => 'product',
			'post_status'    => 'publish',
			'posts_per_page' => $needed,
			'post__not_in'   => $exclude,
			'fields'         => 'ids',
			'orderby'        => 'rand',
		)
	);

	if ( ! empty( $backfill ) ) {
		$cross_sells = array_merge( $cross_sells, array_map( 'intval', $backfill ) );
	}

	return $cross_sells;
}
add_filter( 'woocommerce_cart_crosssell_ids', 'foxfire_ensure_minimum_cross_sells', 20, 1 );

/**
 * Render cross-sells cleanly below the 2-column cart grid (full width, 3 columns).
 */
function foxfire_render_cart_cross_sells(): void {
	if ( is_cart() && is_object( WC()->cart ) && ! WC()->cart->is_empty() ) {
		echo '<div class="ff-cart-cross-sells-wrap">';
		woocommerce_cross_sell_display( 3, 3 );
		echo '</div>';
	}
}
add_action( 'woocommerce_after_cart', 'foxfire_render_cart_cross_sells', 15 );

/**
 * Render the added-to-cart confirmation modal in the footer.
 */
function foxfire_render_atc_modal_footer(): void {
	get_template_part( 'template-parts/cart/added-to-cart-modal' );
}
add_action( 'wp_footer', 'foxfire_render_atc_modal_footer', 20 );

/**
 * Enqueue Added-to-Cart modal and cart page scripts.
 */
function foxfire_cart_enqueue_scripts(): void {
	$version = defined( 'FOXFIRE_CHILD_VERSION' ) ? FOXFIRE_CHILD_VERSION : '1.2.0';

	// Added-to-Cart Modal script (all pages)
	$modal_js_path = FOXFIRE_CHILD_DIR . '/assets/js/atc-modal.js';
	wp_enqueue_script(
		'foxfire-atc-modal',
		FOXFIRE_CHILD_URI . '/assets/js/atc-modal.js',
		array( 'jquery' ),
		file_exists( $modal_js_path ) ? (string) filemtime( $modal_js_path ) : $version,
		true
	);

	wp_localize_script(
		'foxfire-atc-modal',
		'foxfire_atc_params',
		array(
			'ajax_url' => admin_url( 'admin-ajax.php' ),
			'nonce'    => wp_create_nonce( 'foxfire_atc_nonce' ),
			'cart_url' => wc_get_cart_url(),
			'i18n'     => array(
				'adding' => __( 'Adding...', 'foxfire-child' ),
				'added'  => __( 'Added to Cart', 'foxfire-child' ),
				'error'  => __( 'Could not add to cart. Please select an option.', 'foxfire-child' ),
			),
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
 * AJAX handler for Single Product Page (PDP) Add-to-Cart.
 * Supports Simple & Variable products, custom quantity tiers, and returns product card info for the center modal.
 */
function foxfire_ajax_add_to_cart_pdp(): void {
	check_ajax_referer( 'foxfire_atc_nonce', 'nonce' );

	$product_id   = isset( $_POST['product_id'] ) ? absint( $_POST['product_id'] ) : 0;
	$quantity     = isset( $_POST['quantity'] ) ? max( 1, absint( $_POST['quantity'] ) ) : 1;
	$variation_id = isset( $_POST['variation_id'] ) ? absint( $_POST['variation_id'] ) : 0;
	$variations   = array();

	// Extract variation attributes from POST
	foreach ( $_POST as $key => $val ) {
		if ( 0 === strpos( $key, 'attribute_' ) ) {
			$variations[ sanitize_text_field( wp_unslash( $key ) ) ] = sanitize_text_field( wp_unslash( $val ) );
		}
	}

	if ( ! $product_id ) {
		wp_send_json_error( array( 'message' => __( 'Invalid product ID.', 'foxfire-child' ) ) );
	}

	$passed_validation = apply_filters( 'woocommerce_add_to_cart_validation', true, $product_id, $quantity, $variation_id, $variations );

	if ( ! $passed_validation ) {
		wp_send_json_error( array( 'message' => __( 'Product could not be added to cart.', 'foxfire-child' ) ) );
	}

	$cart_item_key = WC()->cart->add_to_cart( $product_id, $quantity, $variation_id, $variations );

	if ( ! $cart_item_key ) {
		wp_send_json_error( array( 'message' => __( 'Could not add item to cart. Please check stock.', 'foxfire-child' ) ) );
	}

	WC()->cart->calculate_totals();

	// Retrieve product for modal presentation
	$target_product = $variation_id ? wc_get_product( $variation_id ) : wc_get_product( $product_id );
	$parent_product = wc_get_product( $product_id );

	$title = $parent_product ? $parent_product->get_name() : '';
	$thumb = $target_product ? $target_product->get_image( 'thumbnail' ) : ( $parent_product ? $parent_product->get_image( 'thumbnail' ) : '' );

	// Format meta (e.g. Strength: 5mg • Qty: 3 Vials)
	$meta_parts = array();
	if ( ! empty( $variations ) ) {
		foreach ( $variations as $attr_name => $attr_val ) {
			$taxonomy = str_replace( 'attribute_', '', $attr_name );
			$term     = get_term_by( 'slug', $attr_val, $taxonomy );
			$label    = wc_attribute_label( $taxonomy );
			$val_text = $term ? $term->name : $attr_val;
			$meta_parts[] = sprintf( '%s: %s', esc_html( $label ), esc_html( $val_text ) );
		}
	}
	$qty_label = ( 1 === $quantity ) ? __( '1 Vial', 'foxfire-child' ) : sprintf( __( '%d Vials', 'foxfire-child' ), $quantity );
	$meta_parts[] = sprintf( __( 'Qty: %s', 'foxfire-child' ), $qty_label );

	// Calculate line item price
	$cart_item = WC()->cart->get_cart_item( $cart_item_key );
	$price_html = '';
	if ( ! empty( $cart_item ) && isset( $cart_item['data'] ) ) {
		$unit_price = (float) $cart_item['data']->get_price();
		$price_html = wc_price( $unit_price * $quantity );
	} elseif ( $target_product ) {
		$price_html = $target_product->get_price_html();
	}

	$cart_count = WC()->cart->get_cart_contents_count();
	$cart_count_text = ( 1 === $cart_count ) ? __( '1 item', 'foxfire-child' ) : sprintf( __( '%d items', 'foxfire-child' ), $cart_count );

	wp_send_json_success(
		array(
			'product_name'  => esc_html( $title ),
			'product_thumb' => $thumb,
			'product_meta'  => implode( ' • ', $meta_parts ),
			'product_price' => $price_html,
			'cart_count'    => $cart_count_text,
			'cart_subtotal' => WC()->cart->get_cart_subtotal(),
			'fragments'     => apply_filters( 'woocommerce_add_to_cart_fragments', array() ),
		)
	);
}
add_action( 'wp_ajax_foxfire_ajax_add_to_cart_pdp', 'foxfire_ajax_add_to_cart_pdp' );
add_action( 'wp_ajax_nopriv_foxfire_ajax_add_to_cart_pdp', 'foxfire_ajax_add_to_cart_pdp' );
