<?php
/** Apply the supplied legal policies to existing localhost pages only. */
defined( 'ABSPATH' ) || exit;
if ( 'local' !== wp_get_environment_type() ) WP_CLI::error( 'This script applies localhost updates only.' );
$targets = array(
	'terms' => array( 'terms-and-conditions', 'page-terms-and-conditions.php' ),
	'privacy' => array( 'privacy-policy', 'page-privacy-policy.php' ),
	'refund' => array( 'refund-and-returns-policy', 'page-refund-and-returns.php' ),
	'shipping' => array( 'shipping-policy', 'page-shipping-policy.php' ),
);
$pages = $backup = array();
foreach ( $targets as $key => list( $slug, $template ) ) {
	$page = get_page_by_path( $slug );
	if ( ! $page instanceof WP_Post || 'publish' !== $page->post_status ) WP_CLI::error( 'Expected published policy page missing: ' . $slug );
	$pages[ $key ] = $page;
	$backup[ $key ] = array( 'ID' => $page->ID, 'content' => $page->post_content, 'template' => get_page_template_slug( $page->ID ), 'editor' => get_post_meta( $page->ID, '_foxfire_use_editor_content', true ), 'effective' => get_post_meta( $page->ID, '_foxfire_policy_effective_date', true ), 'updated' => get_post_meta( $page->ID, '_foxfire_policy_updated_date', true ) );
}
add_option( 'foxfire_legal_review_backup_v1', $backup, '', false );
foreach ( $targets as $key => list( $slug, $template ) ) {
	$page = $pages[ $key ];
	$policy = foxfire_legal_policies()[ $key ];
	$content = foxfire_legal_editor_content( $key );
	if ( $content !== $page->post_content ) {
		wp_save_post_revision( $page->ID );
		$result = wp_update_post( wp_slash( array( 'ID' => $page->ID, 'post_content' => $content ) ), true );
		if ( is_wp_error( $result ) ) WP_CLI::error( $result->get_error_message() );
	}
	update_post_meta( $page->ID, '_wp_page_template', $template );
	update_post_meta( $page->ID, '_foxfire_use_editor_content', 'yes' );
	$effective = 'terms' === $key ? wp_date( 'F j, Y' ) : $policy['effective'];
	update_post_meta( $page->ID, '_foxfire_policy_effective_date', $effective );
	update_post_meta( $page->ID, '_foxfire_policy_updated_date', 'terms' === $key ? $effective : $policy['updated'] );
	clean_post_cache( $page->ID );
	WP_CLI::log( 'Updated ' . $policy['title'] . ': ' . get_permalink( $page->ID ) );
}
do_action( 'litespeed_purge_all' );
WP_CLI::success( 'Four client policies are public locally and editable in WordPress. All policies use info@. Payment/email safety settings are unchanged.' );
