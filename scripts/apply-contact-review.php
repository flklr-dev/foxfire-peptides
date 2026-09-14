<?php
/** Apply the client's Contact copy locally; preserve all unrelated content. */
defined( 'ABSPATH' ) || exit;
if ( 'local' !== wp_get_environment_type() ) WP_CLI::error( 'Local review updates only.' );
$content = get_option( 'foxfire_public_content', array() );
if ( ! is_array( $content ) ) WP_CLI::error( 'Unexpected public-content storage; nothing changed.' );
$content['support_email'] = 'info@foxfirepeptides.com';
$content['contact_hours'] = 'Messages Accepted 24/7';
$content['contact_response_time'] = 'We’ll respond as soon as possible';
$content['contact_direct_description'] = 'Send us a message anytime for assistance with inquiries, documentation, or orders. We’ll respond as soon as possible.';
// Narrow, approved email replacement in other saved public copy only.
foreach ( $content as &$value ) {
	if ( is_string( $value ) ) $value = str_replace( 'support@foxfirepeptides.com', 'info@foxfirepeptides.com', $value );
}
unset( $value );
update_option( 'foxfire_public_content', $content, false );
foreach ( get_posts( array( 'post_type' => array( 'page', 'post' ), 'post_status' => 'any', 'numberposts' => -1 ) ) as $page ) {
	if ( str_contains( $page->post_content, 'support@foxfirepeptides.com' ) ) {
		$result = wp_update_post( array( 'ID' => $page->ID, 'post_content' => wp_slash( str_replace( 'support@foxfirepeptides.com', 'info@foxfirepeptides.com', $page->post_content ) ) ), true );
		if ( is_wp_error( $result ) ) WP_CLI::error( $result->get_error_message() );
	}
}
WP_CLI::success( 'Contact email and approved availability updated locally; mail transport, payment settings and unrelated content unchanged.' );
