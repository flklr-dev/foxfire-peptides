<?php
/** Reversible, localhost-only display data; never include its data/file in migration. */
defined( 'ABSPATH' ) || exit;
if ( 'local' !== wp_get_environment_type() || ! in_array( wp_parse_url( home_url(), PHP_URL_HOST ), array( 'localhost', '127.0.0.1' ), true ) ) {
	WP_CLI::error( 'COA preview is restricted to localhost.' );
}
$option = 'foxfire_local_coa_preview_v1';
$state = get_option( $option, array() );
$mode = $args[0] ?? 'seed';
$keys = array( 'foxfire_batch_lot', 'foxfire_testing_notes', 'foxfire_testing_summary', 'foxfire_coa_file', 'foxfire_coa_url', 'foxfire_coa_label', '_foxfire_local_coa_demo' );
$read = static function ( int $id ) use ( $keys ): array {
	$result = array();
	foreach ( $keys as $key ) {
		$result[$key] = get_post_meta( $id, $key, false );
		if ( ! str_starts_with( $key, '_' ) ) $result['_' . $key] = get_post_meta( $id, '_' . $key, false );
	}
	return $result;
};
$purge = static function ( int $id ): void {
	wc_delete_product_transients( $id );
	clean_post_cache( $id );
	do_action( 'litespeed_purge_post', $id );
};
if ( 'restore' === $mode ) {
	if ( ! $state || ! empty( $state['restored'] ) ) WP_CLI::error( 'No active local COA preview to restore.' );
	foreach ( $state['products'] as $id => $saved ) {
		if ( $read( (int) $id ) !== $saved['preview'] ) WP_CLI::error( 'Product ' . $id . ' has changed since preview setup. Review those edits before restoring; nothing overwritten.' );
	}
	foreach ( $state['products'] as $id => $saved ) {
		foreach ( $saved['original'] as $key => $values ) {
			delete_post_meta( $id, $key );
			foreach ( $values as $value ) add_post_meta( $id, $key, $value );
		}
		$purge( (int) $id );
	}
	$state['restored'] = true;
	update_option( $option, $state, false );
	do_action( 'litespeed_purge_all' );
	WP_CLI::success( 'Original local fields restored. Sample attachment retained to avoid breaking any local order references; exclude it from migration.' );
	return;
}
if ( 'seed' !== $mode ) WP_CLI::error( 'Use seed or restore.' );
if ( $state && empty( $state['restored'] ) ) {
	WP_CLI::success( 'Local demo already active; not overwriting edits or creating duplicate reports.' );
	return;
}
$examples = array(
	'5-amino-1mq' => array( 'Report Available', 'Batch report linked' ),
	'bpc-157' => array( 'Information Available', 'Batch details recorded' ),
	'cagrilintide-cagri' => array( 'Information Pending', 'Testing details pending' ),
	'ghk-cu' => array( 'On File', 'Documentation on file' ),
	'klow' => array( 'Archived', 'Archived batch report' ),
);
$products = array();
foreach ( $examples as $slug => $example ) {
	$post = get_page_by_path( $slug, OBJECT, 'product' );
	if ( ! $post || 'publish' !== $post->post_status ) WP_CLI::error( 'Missing published product: ' . $slug . '. Nothing changed.' );
	$products[$post->ID] = array( 'original' => $read( $post->ID ), 'status' => $example[0], 'notes' => $example[1] );
}
$path = '/foxfire-preview/foxfire-local-demo-coa.pdf';
if ( ! is_readable( $path ) ) WP_CLI::error( 'Mount output/pdf at /foxfire-preview:ro with the labeled sample PDF first. Nothing changed.' );
$attachment_id = (int) ( $state['attachment_id'] ?? 0 );
if ( ! $attachment_id || ! get_post( $attachment_id ) ) {
	$upload = wp_upload_bits( 'foxfire-local-demo-coa.pdf', null, file_get_contents( $path ) );
	if ( $upload['error'] ) WP_CLI::error( $upload['error'] );
	$attachment_id = wp_insert_attachment( array( 'post_title' => 'LOCAL DEMO ONLY - Sample COA (not real test results)', 'post_content' => 'LOCAL DEMO. Exclude from staging/production migration.', 'post_mime_type' => 'application/pdf', 'post_status' => 'inherit' ), $upload['file'], 0, true );
	if ( is_wp_error( $attachment_id ) ) WP_CLI::error( $attachment_id->get_error_message() );
	update_post_meta( $attachment_id, '_foxfire_local_coa_demo', '1' );
}
$state = array( 'attachment_id' => $attachment_id, 'products' => $products, 'restored' => false );
update_option( $option, $state, false );
foreach ( $products as $id => $example ) {
	update_field( 'field_foxfire_batch_lot', 'DEMO-LOCAL-' . $id, $id );
	update_field( 'field_foxfire_testing_notes', $example['notes'], $id );
	update_field( 'field_foxfire_testing_summary', $example['status'], $id );
	update_field( 'field_foxfire_coa_file', in_array( $example['status'], array( 'Report Available', 'On File', 'Archived' ), true ) ? $attachment_id : '', $id );
	update_field( 'field_foxfire_coa_url', '', $id );
	update_field( 'field_foxfire_coa_label', 'View COA (local sample)', $id );
	update_post_meta( $id, '_foxfire_local_coa_demo', '1' );
	$state['products'][$id]['preview'] = $read( (int) $id );
	update_option( $option, $state, false );
	$purge( (int) $id );
	WP_CLI::log( get_the_title( $id ) . ': ' . $example['status'] . ' - LOCAL DEMO' );
}
do_action( 'litespeed_purge_all' );
WP_CLI::success( 'Five existing local products now demonstrate different workflows; catalog count, prices, stock and historic orders are unchanged. Restore before export.' );
