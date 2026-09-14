<?php
/** Standalone source/template tests with simulated WordPress APIs, not live acceptance. */
define( 'ABSPATH', __DIR__ . '/../' );
$options = array();
function __( $value, $domain = '' ) { return $value; }
function add_action( ...$args ) {}
function add_filter( ...$args ) {}
function get_option( $key, $fallback = false ) { global $options; return $options[ $key ] ?? $fallback; }
function update_option( $key, $value, ...$args ) { global $options; $options[ $key ] = $value; }
function add_option( $key, $value, ...$args ) { global $options; if ( ! array_key_exists( $key, $options ) ) $options[ $key ] = $value; }
function wp_get_environment_type() { return 'local'; }
function do_action( ...$args ) {}
function get_header() {}
function get_footer() {}
function esc_html( $value ) { return htmlspecialchars( $value, ENT_QUOTES, 'UTF-8' ); }
function esc_attr( $value ) { return esc_html( $value ); }
function esc_url( $value ) { return esc_attr( $value ); }
function esc_html_e( $value, $domain = '' ) { echo esc_html( $value ); }
function esc_attr_e( $value, $domain = '' ) { echo esc_attr( $value ); }
function absint( $value ) { return abs( (int) $value ); }
function foxfire_get_shop_url() { return 'http://localhost:8080/shop/'; }
function foxfire_get_page_url( $slug, $fallback ) { return 'http://localhost:8080/' . $slug . '/'; }
function get_stylesheet_directory_uri() { return 'http://localhost:8080/wp-content/themes/foxfire-child'; }
function wp_attachment_is_image( $id ) { return in_array( $id, array( 200, 201 ), true ); }
function wp_get_attachment_image( $id, $size, $icon, $attributes ) {
	$html = '<img src="http://localhost:8080/uploads/image-' . $id . '.jpg" srcset="image-' . $id . '-480.jpg 480w">';
	$attrs = '';
	foreach ( $attributes as $key => $value ) $attrs .= ' ' . $key . '="' . esc_attr( $value ) . '"';
	return str_replace( '>', $attrs . '>', $html );
}
class WP_CLI {
	static function error( $message ) { throw new RuntimeException( $message ); }
	static function success( $message ) {}
}
require ABSPATH . 'wp-content/plugins/foxfire-operations/includes/content-admin.php';
function foxfire_get_managed_content( $key, $fallback ) { return foxfire_operations_get_public_content( $key, $fallback ); }
function check( $condition, $label ) {
	if ( ! $condition ) throw new RuntimeException( 'FAIL: ' . $label );
	echo 'PASS: ' . $label . PHP_EOL;
}
function render_about() {
	ob_start();
	include ABSPATH . 'wp-content/themes/foxfire-child/page-about.php';
	return ob_get_clean();
}
$html = render_about();
$schema = foxfire_operations_public_content_schema();
foreach ( array( 'about_hero_title', 'about_hero_intro', 'about_commitment_text', 'about_people_text', 'about_story_title', 'about_story_one', 'about_story_two', 'about_values_title', 'about_values_intro', 'about_cta_title', 'about_cta_description', 'about_primary_cta', 'about_secondary_cta' ) as $key ) {
	check( str_contains( $html, esc_html( $schema[ $key ]['default'] ) ), 'Approved default rendered: ' . $key );
}
check( str_contains( $html, 'Transparent &amp; Straightforward' ) && str_contains( $html, 'Real People Behind Foxfire' ), 'Reviewed collage headings retained' );
check( substr_count( $html, 'class="ff-stat-block"' ) === 4 && substr_count( $html, 'class="ff-value-card"' ) === 4, 'Both four-column values sections retained' );
check( ! str_contains( $html, 'purchasing simple' ) && ! str_contains( $html, 'ff-about-founder' ), 'Reviewed story wording and layout replace later redesign' );
check( str_contains( $html, 'href="http://localhost:8080/shop/"' ) && str_contains( $html, 'href="http://localhost:8080/testing-coa/"' ), 'CTA links use existing Shop and Testing routes' );
foreach ( array( 'portrait', 'product', 'team' ) as $slot ) {
	check( $schema[ 'about_image_' . $slot ]['type'] === 'image' && str_contains( $html, 'about-hero-' . $slot . '.webp' ), 'Independent Media field and placeholder: ' . $slot );
	check( is_file( ABSPATH . 'wp-content/themes/foxfire-child/assets/images/about-hero-' . $slot . '.webp' ) && is_file( ABSPATH . 'wp-content/themes/foxfire-child/assets/images/about-hero-' . $slot . '-480.webp' ), 'Responsive placeholder files exist: ' . $slot );
}
$options['foxfire_public_content'] = array( 'about_image_product' => '200', 'about_image_product_alt' => '"><script>alert(1)</script>' );
$custom = render_about();
check( str_contains( $custom, 'image-200.jpg' ) && ! str_contains( $custom, 'about-hero-product.webp' ) && str_contains( $custom, 'about-hero-portrait.webp' ), 'Media replacement changes only selected collage image' );
check( ! str_contains( $custom, '<script>' ) && str_contains( $custom, '&lt;script&gt;' ), 'Managed image description is escaped' );
$options['foxfire_public_content']['about_image_product'] = '201';
check( str_contains( render_about(), 'image-201.jpg' ) && ! str_contains( render_about(), 'image-200.jpg' ), 'Subsequent Media replacement updates markup' );
$options['foxfire_public_content']['about_image_product'] = '999';
check( str_contains( render_about(), 'about-hero-product.webp' ), 'Invalid/deleted attachment safely falls back' );
$options['foxfire_public_content']['about_image_product'] = '0';
check( str_contains( render_about(), 'about-hero-product.webp' ), 'Remove selection restores placeholder' );
$options['foxfire_public_content'] = array( 'support_email' => 'info@foxfirepeptides.com', 'home_hero_title' => 'Keep homepage', 'about_founder_name' => 'Keep archived founder', 'about_image_portrait' => '200', 'about_hero_title' => 'Old heading' );
require ABSPATH . 'scripts/apply-about-review.php';
check( $options['foxfire_public_content']['support_email'] === 'info@foxfirepeptides.com' && $options['foxfire_public_content']['home_hero_title'] === 'Keep homepage' && $options['foxfire_public_content']['about_founder_name'] === 'Keep archived founder' && $options['foxfire_public_content']['about_image_portrait'] === '200', 'Local copy update preserves unrelated content, archived founder and images' );
check( $options['foxfire_about_review_backup_v1']['about_hero_title'] === 'Old heading' && str_contains( render_about(), 'Built on Quality, Trust &amp; Community.' ), 'Saved old heading replaced; prior value backed up' );
require ABSPATH . 'scripts/apply-about-review.php';
check( $options['foxfire_about_review_backup_v1']['about_hero_title'] === 'Old heading', 'Repeated copy update retains original backup' );
$css = file_get_contents( ABSPATH . 'wp-content/themes/foxfire-child/assets/css/woocommerce.css' );
$start = strpos( $css, 'A. About Us Page' );
$end = strpos( $css, 'B. Contact Us Page', $start );
$about_css = substr( $css, $start, $end - $start );
foreach ( array( '#FF5800', '#AFF769', '#15171A', '#FFFFFF' ) as $color ) check( str_contains( $about_css, $color ), 'Scoped brand token: ' . $color );
check( str_contains( $about_css, 'a:focus-visible' ) && str_contains( $about_css, 'flex-wrap: wrap' ), 'CTA focus and long-label wrapping defined' );
echo "Standalone checks passed. Live WordPress admin, browser layout and caching still require Docker." . PHP_EOL;
