<?php
/** Apply only the approved About copy locally; leave other pages/assets untouched. */
defined( 'ABSPATH' ) || exit;
if ( 'local' !== wp_get_environment_type() ) WP_CLI::error( 'Local review updates only.' );
$content = get_option( 'foxfire_public_content', array() );
if ( ! is_array( $content ) ) WP_CLI::error( 'Unexpected public-content storage; nothing changed.' );
$approved = array(
	'about_hero_title' => 'Built on Quality, Trust & Community.',
	'about_hero_intro' => 'Foxfire Peptides is focused on creating a straightforward, transparent experience for the research community. We believe clear product information, accessible testing documentation, and dependable service are the foundation of lasting relationships.',
	'about_story_title' => 'A More Personal Approach',
	'about_story_one' => 'We want Foxfire to feel different from an anonymous online storefront. Our goal is to make ordering simple, information easy to find, and communication clear throughout the customer experience.',
	'about_story_two' => 'We believe that lasting trust is earned through everyday consistency. By focusing on clear product details, easy access to testing records where available, and responsive support whenever questions arise, we are building a brand researchers can count on for the long haul.',
	'about_values_title' => 'Our Core Values',
	'about_values_intro' => 'Simple standards that guide how we treat our customers, curate our products, and support the community.',
	'about_cta_title' => 'Explore Foxfire',
	'about_cta_description' => 'Browse our research compounds or review available batch documentation and Certificates of Analysis.',
	'about_primary_cta' => 'BROWSE RESEARCH COMPOUNDS',
	'about_secondary_cta' => 'VIEW TESTING & COAs',
);
$schema = foxfire_operations_public_content_schema();
foreach ( array( 'about_commitment_text', 'about_people_text' ) as $key ) {
	$approved[ $key ] = $schema[ $key ]['default'];
}
// Retain the pre-review copy once for recovery; do not alter stored image choices.
add_option( 'foxfire_about_review_backup_v1', array_intersect_key( $content, $approved ), '', false );
foreach ( $approved as $key => $value ) $content[ $key ] = $value;
update_option( 'foxfire_public_content', $content, false );
do_action( 'litespeed_purge_all' );
WP_CLI::success( 'Approved About wording applied locally. Other content, image selections, archived founder copy, payments and email are unchanged.' );
