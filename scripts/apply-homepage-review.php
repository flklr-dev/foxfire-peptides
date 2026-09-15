<?php
/** Apply the first client homepage review to the local WordPress data only.
 * Run with WP-CLI eval-file. Existing category IDs and assignments stay intact.
 */
defined( 'ABSPATH' ) || exit;
if ( 'local' !== wp_get_environment_type() ) {
	WP_CLI::error( 'This review script is restricted to the local environment.' );
}

$content = get_option( 'foxfire_public_content', array() );
$content = is_array( $content ) ? $content : array();
$updates = array(
	'home_hero_title' => 'Research Compounds. Transparent Testing. Real Accountability.',
	'home_hero_lead' => 'Third-party testing, clear batch documentation, and straightforward access to the information behind every Foxfire product.',
	'home_primary_cta' => 'SHOP RESEARCH COMPOUNDS',
	'home_secondary_cta' => 'VIEW TESTING & COAs',
	'home_long_road_title' => 'Built for the Long Road',
	'home_long_road_description' => 'Finding a vendor is easy. Finding one you trust enough to stay with is different. Foxfire is built around consistency, transparency, accessible testing, and real communication—because we believe the right relationship should matter beyond the next order.',
	'home_long_road_cta' => 'LEARN MORE ABOUT FOXFIRE',
);
if ( ! get_option( 'foxfire_homepage_review_20260913_backup' ) ) {
	add_option( 'foxfire_homepage_review_20260913_backup', array(
		'content' => array_intersect_key( $content, $updates ),
		'category' => get_term_by( 'slug', 'regenerative-research', 'product_cat' ) ?: get_term_by( 'slug', 'recovery-healing', 'product_cat' ),
	), '', false );
}
update_option( 'foxfire_public_content', array_merge( $content, $updates ) );
$category = get_term_by( 'slug', 'regenerative-research', 'product_cat' );
if ( ! $category ) {
	$category = get_term_by( 'slug', 'recovery-healing', 'product_cat' );
}
if ( ! $category ) {
	$category = get_term_by( 'name', 'Regenerative Research', 'product_cat' );
}
if ( $category instanceof WP_Term ) {
	$result = wp_update_term( $category->term_id, 'product_cat', array(
		'name' => 'Regenerative Research',
		'slug' => 'regenerative-research',
		'description' => 'Compounds for laboratory regenerative research.',
	) );
	if ( is_wp_error( $result ) ) {
		WP_CLI::error( $result->get_error_message() );
	}
	WP_CLI::success( 'Updated the category name and canonical slug; product assignments preserved.' );
} else {
	WP_CLI::warning( 'No matching category found; no categories were created or removed.' );
}
WP_CLI::success( 'Updated the approved homepage copy fields; other managed content preserved.' );
