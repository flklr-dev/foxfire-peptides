<?php
/** Apply the first client homepage review to the local WordPress data only.
 * Run with WP-CLI eval-file. Existing category IDs, slugs and assignments stay intact.
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
);
if ( ! get_option( 'foxfire_homepage_review_20260913_backup' ) ) {
	add_option( 'foxfire_homepage_review_20260913_backup', array(
		'content' => array_intersect_key( $content, $updates ),
		'category' => get_term_by( 'slug', 'recovery-healing', 'product_cat' ),
	), '', false );
}
update_option( 'foxfire_public_content', array_merge( $content, $updates ) );
$category = get_term_by( 'slug', 'recovery-healing', 'product_cat' );
if ( ! $category ) {
	$category = get_term_by( 'name', 'Recovery & Healing', 'product_cat' );
}
if ( $category instanceof WP_Term ) {
	$result = wp_update_term( $category->term_id, 'product_cat', array(
		'name' => 'Peptide Research',
		'description' => 'Compounds for laboratory peptide research.',
	) );
	if ( is_wp_error( $result ) ) {
		WP_CLI::error( $result->get_error_message() );
	}
	WP_CLI::success( 'Renamed category; URL and product assignments preserved.' );
} else {
	WP_CLI::warning( 'No matching category found; no categories were created or removed.' );
}
WP_CLI::success( 'Updated the four homepage copy fields; other managed content preserved.' );
