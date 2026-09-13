<?php
/** Local, read-only regression checks for the client homepage review. */

defined( 'ABSPATH' ) || exit;

if ( 'local' !== wp_get_environment_type() ) {
	throw new RuntimeException( 'Run homepage review checks only in the local environment.' );
}

$check = static function ( bool $passed, string $message ): void {
	if ( ! $passed ) {
		throw new RuntimeException( $message );
	}
	WP_CLI::log( 'PASS: ' . $message );
};

$original_user_id = get_current_user_id();
$original_showcase = wc_get_loop_prop( 'foxfire_homepage_showcase', false );
$administrators = get_users( array( 'role' => 'administrator', 'number' => 1, 'fields' => 'ID' ) );
$check( ! empty( $administrators ), 'An existing local administrator is available for permission checks' );
wp_set_current_user( (int) $administrators[0] );

$candidates = wc_get_products( array( 'status' => 'publish', 'stock_status' => 'instock', 'limit' => -1, 'return' => 'ids' ) );
$candidate_ids = foxfire_homepage_filter_available_product_ids( $candidates, 8 );
$check( count( $candidate_ids ) >= 4, 'At least four public purchasable products are available' );
$priority_ids = array_reverse( $candidate_ids );
$simulate_selection = static function () use ( $priority_ids ): array {
	return $priority_ids;
};

// Simulate a changed admin selection in this process only; never write options.
add_filter( 'pre_option_foxfire_homepage_product_ids', $simulate_selection );
try {
	$check( foxfire_operations_can_manage_homepage_products(), 'Administrators can manage homepage product priorities' );
	$validated = foxfire_operations_validate_homepage_product_ids( $priority_ids );
	$check( ! is_wp_error( $validated ) && $priority_ids === $validated, 'Admin product selections preserve the chosen order' );
	$check( is_wp_error( foxfire_operations_validate_homepage_product_ids( array( $priority_ids[0], $priority_ids[0] ) ) ), 'Duplicate selections are rejected' );

	$query = foxfire_get_homepage_products( 4 );
	$query_ids = array_map( 'intval', wp_list_pluck( $query->posts, 'ID' ) );
	$check( array_slice( $priority_ids, 0, 4 ) === $query_ids, 'Homepage shows only the first four eligible administrator-selected products' );

	ob_start();
	foxfire_operations_render_homepage_products_page();
	$admin_html = ob_get_clean();
	$check( str_contains( $admin_html, 'name="foxfire_homepage_products_nonce"' ) && str_contains( $admin_html, 'name="foxfire_homepage_product_ids[]"' ), 'The admin editor includes product selectors and CSRF protection' );

	wc_set_loop_prop( 'foxfire_homepage_showcase', true );
	ob_start();
	while ( $query->have_posts() ) {
		$query->the_post();
		wc_get_template_part( 'content', 'product' );
	}
	$homepage_cards = ob_get_clean();
	$check( 4 === substr_count( $homepage_cards, 'class="button ff-product-card__view-product"' ), 'All four homepage buttons are View Product links' );
	$check( ! str_contains( $homepage_cards, 'ajax_add_to_cart' ) && ! str_contains( $homepage_cards, '?add-to-cart=' ), 'Homepage buttons cannot trigger an add-to-cart action' );
	foreach ( $query_ids as $product_id ) {
		$check( str_contains( $homepage_cards, 'href="' . esc_url( get_permalink( $product_id ) ) . '" class="button ff-product-card__view-product"' ), 'A featured button links to its existing individual product page' );
	}

	$query->rewind_posts();
	$query->the_post();
	wc_set_loop_prop( 'foxfire_homepage_showcase', false );
	ob_start();
	wc_get_template_part( 'content', 'product' );
	$catalog_card = ob_get_clean();
	$check( ! str_contains( $catalog_card, 'ff-product-card__view-product' ) && str_contains( $catalog_card, 'add_to_cart_button' ), 'Other product loops retain their original WooCommerce actions outside showcase contexts' );
} finally {
	remove_filter( 'pre_option_foxfire_homepage_product_ids', $simulate_selection );
	wc_set_loop_prop( 'foxfire_homepage_showcase', $original_showcase );
	wp_reset_postdata();
	wp_set_current_user( $original_user_id );
}

WP_CLI::success( 'Homepage review checks passed without changing saved products, users, or options.' );

$faqs = foxfire_get_homepage_faqs();
$check( 7 === count( $faqs ), 'The current homepage contains seven FAQ questions' );
$check( 'Can I order more than one vial?' === $faqs[1]['question'], 'The homepage uses the revised multi-vial question' );
$check( 'Yes. Multiple-vial quantities are available on select products. Available quantity options and pricing are shown directly on each product page.' === $faqs[1]['answer'], 'The homepage uses the client-supplied multi-vial answer' );
$check( ! in_array( 'Is an account required to order?', array_column( $faqs, 'question' ), true ), 'The account-required question is excluded from the homepage' );

$simulate_empty_content = static function (): array { return array(); };
add_filter( 'pre_option_foxfire_public_content', $simulate_empty_content );
try {
	$check( 7 === count( foxfire_get_homepage_faqs() ), 'New installations also use seven default homepage FAQs' );
} finally {
	remove_filter( 'pre_option_foxfire_public_content', $simulate_empty_content );
}

$simulate_cleared_faqs = static function (): array {
	$values = array();
	for ( $index = 1; $index <= 8; $index++ ) {
		$values[ 'faq_question_' . $index ] = '';
		$values[ 'faq_answer_' . $index ] = '';
	}
	return $values;
};
add_filter( 'pre_option_foxfire_public_content', $simulate_cleared_faqs );
try {
	$check( array() === foxfire_get_homepage_faqs(), 'Cleared admin FAQ fields stay hidden instead of restoring old questions' );
} finally {
	remove_filter( 'pre_option_foxfire_public_content', $simulate_cleared_faqs );
}

ob_start();
get_template_part( 'template-parts/footer/site-footer' );
$footer_html = ob_get_clean();
$check( 3 === substr_count( $footer_html, '<nav ' ), 'Footer has the three requested navigation groups' );
$check( str_contains( $footer_html, esc_url( wc_get_endpoint_url( 'orders', '', wc_get_page_permalink( 'myaccount' ) ) ) ), 'Order History links to the actual WooCommerce orders endpoint' );
$check( strpos( $footer_html, 'ff-site-footer__research-note' ) < strpos( $footer_html, 'ff-site-footer__copyright' ), 'The research-use notice appears before the copyright' );
WP_CLI::success( 'FAQ and footer regression checks passed without changing saved data.' );
