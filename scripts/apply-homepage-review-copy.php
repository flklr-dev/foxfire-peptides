<?php
/** Apply client review items 7–10 to local managed copy, preserving other data. */
defined( 'ABSPATH' ) || exit;
if ( 'local' !== wp_get_environment_type() ) {
	WP_CLI::error( 'This review script is restricted to the local environment.' );
}

$content = get_option( 'foxfire_public_content', array() );
$content = is_array( $content ) ? $content : array();
$updates = array(
	'home_coa_title' => "Know What's Behind Every Vial.",
	'home_coa_description' => 'Access available third-party testing and batch-specific documentation for Foxfire research compounds. Search by product, batch, or lot number to find the available COA.',
	'home_closing_title' => 'Ready to Explore Foxfire?',
	'home_closing_description' => 'Browse our research compounds, review available testing documentation, and find the products that fit your research needs.',
	'faq_question_2' => 'Can I order more than one vial?',
	'faq_answer_2' => 'Yes. Multiple-vial quantities are available on select products. Available quantity options and pricing are shown directly on each product page.',
	'footer_tagline' => 'Research compounds with transparent testing, clear documentation, and straightforward ordering.',
	'footer_research_notice' => 'For laboratory research use only. Not for human consumption.',
);
if ( false === get_option( 'foxfire_homepage_review_copy_20260913_backup', false ) ) {
	add_option( 'foxfire_homepage_review_copy_20260913_backup', array(
		'content' => array_intersect_key( $content, $updates ),
		'keys' => array_keys( $updates ),
	), '', false );
}
update_option( 'foxfire_public_content', array_merge( $content, $updates ), false );
WP_CLI::success( 'Updated only the requested testing, FAQ, final CTA, and footer copy; other managed fields preserved.' );
