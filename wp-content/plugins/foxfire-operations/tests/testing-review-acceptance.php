<?php
/** Local-only COA review tests. Fixtures never modify existing products/reports. */
defined( 'ABSPATH' ) || exit;
if ( ! foxfire_operations_local_mail_is_enabled() ) throw new RuntimeException( 'Run only in the isolated local environment.' );
$check = static function ( bool $passed, string $label ): void {
	if ( ! $passed ) throw new RuntimeException( 'FAIL: ' . $label );
	WP_CLI::log( 'PASS: ' . $label );
};
$product_id = 0;
$attachment_ids = array();
$old_post = $GLOBALS['post'] ?? null;
$old_server = $_SERVER;
$_SERVER['SERVER_NAME'] = 'localhost';
$_SERVER['REQUEST_METHOD'] = 'GET';
$find = static function ( int $id ): array {
	foreach ( foxfire_get_coa_catalog_items() as $item ) if ( $item['id'] === $id ) return $item;
	throw new RuntimeException( 'Fixture missing from directory.' );
};
try {
	$summary_field = acf_get_field( 'field_foxfire_testing_notes' );
	$status_field = acf_get_field( 'field_foxfire_testing_summary' );
	$check( 'textarea' === $summary_field['type'] && 'Testing Summary' === $summary_field['label'], 'Separate admin-editable testing summary exists' );
	$check( 'select' === $status_field['type'] && 'Testing Status' === $status_field['label'], 'Existing status choices and saved field remain compatible' );
	$check( true === apply_filters( 'acf/validate_value/name=foxfire_testing_notes', true, 'Factual batch documentation.' ) && true !== apply_filters( 'acf/validate_value/name=foxfire_testing_notes', true, str_repeat( 'x', 501 ) ), 'Summary server validation accepts text and rejects overlong values' );
	$check( 'Plain text' === foxfire_operations_sanitize_testing_notes( '<b>Plain text</b>' ), 'Summary strips markup' );
	$check( true !== apply_filters( 'acf/validate_value/name=foxfire_testing_summary', true, 'Made up status' ), 'Status validation rejects unapproved workflow values' );
	$product = new WC_Product_Simple();
	$product->set_name( 'LOCAL COA REVIEW ' . wp_generate_password( 8, false ) );
	$product->set_status( 'publish' );
	$product->set_regular_price( '1' );
	$product->set_sku( 'LOCAL-COA-' . wp_generate_password( 8, false ) );
	$product_id = $product->save();
	update_field( 'field_foxfire_batch_lot', 'LOCAL-BATCH-A', $product_id );
	update_field( 'field_foxfire_testing_notes', 'Local fixture: supporting analytical documentation.', $product_id );
	update_field( 'field_foxfire_testing_summary', 'Information Available', $product_id );
	$item = $find( $product_id );
	$check( 'LOCAL-BATCH-A' === $item['batch_lot'], 'Adding a batch updates the correct product directory row' );
	$check( 'Local fixture: supporting analytical documentation.' === $item['testing_summary'] && 'Information Available' === $item['status'], 'Summary and admin-selected status appear independently' );
	$check( ! $item['has_coa'] && '' === $item['coa_url'], 'Missing document never creates a View COA link' );
	update_field( 'field_foxfire_batch_lot', 'LOCAL-BATCH-B', $product_id );
	update_field( 'field_foxfire_testing_notes', 'Updated local batch summary.', $product_id );
	update_field( 'field_foxfire_testing_summary', 'Archived', $product_id );
	$item = $find( $product_id );
	$check( 'LOCAL-BATCH-B' === $item['batch_lot'] && 'Updated local batch summary.' === $item['testing_summary'] && 'Archived' === $item['status'], 'Editing batch, summary and status updates the directory' );
	update_field( 'field_foxfire_testing_summary', 'Report Available', $product_id );
	$check( 'Awaiting Document' === $find( $product_id )['status'], 'Report Available cannot imply an available link when no report exists' );
	update_field( 'field_foxfire_coa_url', home_url( '/testing-coa/' ), $product_id );
	$check( ! $find( $product_id )['has_coa'], 'General directory URL is not treated as a COA report' );
	$check( '' === foxfire_operations_document_url( 'javascript:alert(1)' ), 'Unsafe document URL schemes are rejected' );
	update_field( 'field_foxfire_coa_url', 'https://example.test/local-report-a.pdf', $product_id );
	$item = $find( $product_id );
	$check( $item['has_coa'] && 'Report Available' === $item['status'] && 'https://example.test/local-report-a.pdf' === $item['coa_url'], 'Direct report URL associates with the correct product/batch' );
	foreach ( array( 'a', 'b' ) as $suffix ) {
		$attachment_ids[] = wp_insert_attachment( array( 'post_title' => 'Local COA attachment ' . $suffix, 'post_mime_type' => 'application/pdf', 'post_status' => 'inherit' ), false, $product_id );
		update_post_meta( end( $attachment_ids ), '_wp_attached_file', 'local-coa-fixture-' . $product_id . '-' . $suffix . '.pdf' );
	}
	update_field( 'field_foxfire_coa_file', $attachment_ids[0], $product_id );
	$check( wp_get_attachment_url( $attachment_ids[0] ) === $find( $product_id )['coa_url'], 'Media selection takes precedence over report URL' );
	update_field( 'field_foxfire_coa_file', $attachment_ids[1], $product_id );
	$check( wp_get_attachment_url( $attachment_ids[1] ) === $find( $product_id )['coa_url'], 'Replacing selected Media attachment updates the live report link' );
	$GLOBALS['post'] = get_page_by_path( 'testing-coa' );
	setup_postdata( $GLOBALS['post'] );
	ob_start();
	include get_stylesheet_directory() . '/page-testing-coa.php';
	$html = ob_get_clean();
	$check( str_contains( $html, 'Testing &amp; COA Documentation' ) && str_contains( $html, 'Quality &amp; Batch Verification' ), 'Main heading and eyebrow follow client wording' );
	$check( str_contains( $html, 'Access available third-party testing and batch-specific documentation for Foxfire research compounds.' ), 'Hero description follows exact client wording' );
	foreach ( array( 'View available product and batch/lot identification.', 'Review available third-party testing information for each batch.', 'View available Certificates of Analysis and supporting laboratory documentation.' ) as $copy ) $check( str_contains( $html, $copy ), 'Information block: ' . $copy );
	$check( str_contains( $html, 'View COA' ) && str_contains( $html, esc_url( wp_get_attachment_url( $attachment_ids[1] ) ) ) && str_contains( $html, 'noopener noreferrer' ), 'Available document renders a clear safe View COA link' );
	$check( 5 === preg_match_all( '/<th scope="col"/', $html ) && str_contains( $html, 'data-ff-coa-filter' ), 'Searchable directory retains all five columns' );
	$check( str_contains( $html, 'Laboratory Research Use Only' ), 'Bottom research-use section remains' );
	update_field( 'field_foxfire_coa_file', '', $product_id );
	$check( 'https://example.test/local-report-a.pdf' === $find( $product_id )['coa_url'], 'Clearing uploaded selection restores the configured direct URL' );
	update_field( 'field_foxfire_coa_url', '', $product_id );
	update_field( 'field_foxfire_batch_lot', '', $product_id );
	update_field( 'field_foxfire_testing_notes', '', $product_id );
	update_field( 'field_foxfire_testing_summary', '', $product_id );
	$item = $find( $product_id );
	$check( 'Not provided' === $item['batch_lot'] && 'Not provided' === $item['testing_summary'] && 'Awaiting Document' === $item['status'] && ! $item['has_coa'], 'Clearing batch, summary, status and both report sources removes their displayed data safely' );
} finally {
	foreach ( $attachment_ids as $id ) wp_delete_attachment( $id, true );
	if ( $product_id ) wp_delete_post( $product_id, true );
	wp_reset_postdata();
	$GLOBALS['post'] = $old_post;
	$_SERVER = $old_server;
}
WP_CLI::success( 'Testing review passed. Only disposable fixture records were removed; no existing products or reports were changed. Attachment-link tests do not validate real COA document contents.' );
