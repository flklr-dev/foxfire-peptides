<?php
/** Apply only the approved Shipping email correction to existing local editor copy. */
defined( 'ABSPATH' ) || exit;
if ( 'local' !== wp_get_environment_type() ) WP_CLI::error( 'Local updates only.' );
$page = get_page_by_path( 'shipping-policy' );
if ( ! $page instanceof WP_Post ) WP_CLI::error( 'Shipping Policy page missing.' );
$content = str_replace( 'support@foxfirepeptides.com', 'info@foxfirepeptides.com', $page->post_content );
if ( $content !== $page->post_content ) {
    wp_save_post_revision( $page->ID );
    $result = wp_update_post( wp_slash( array( 'ID' => $page->ID, 'post_content' => $content ) ), true );
    if ( is_wp_error( $result ) ) WP_CLI::error( $result->get_error_message() );
    clean_post_cache( $page->ID );
    do_action( 'litespeed_purge_all' );
}
WP_CLI::success( 'Shipping Policy uses info@foxfirepeptides.com. Other content and payment/email settings are unchanged.' );
