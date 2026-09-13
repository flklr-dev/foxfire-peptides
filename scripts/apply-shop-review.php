<?php
/** Apply approved catalog category labels locally without changing URLs or assignments. */
defined( 'ABSPATH' ) || exit;
if ( 'local' !== wp_get_environment_type() ) {
	WP_CLI::error( 'This review script is restricted to the local environment.' );
}

foreach ( array( 'recovery-healing' => 'Peptide Research', 'glp-1-agonists' => 'GLP-1 Research' ) as $slug => $name ) {
	$term = get_term_by( 'slug', $slug, 'product_cat' );
	if ( ! $term instanceof WP_Term ) {
		WP_CLI::error( 'Missing catalog category: ' . $slug );
	}
	$result = wp_update_term( $term->term_id, 'product_cat', array( 'name' => $name ) );
	if ( is_wp_error( $result ) ) {
		WP_CLI::error( $result->get_error_message() );
	}
	WP_CLI::success( 'Category label updated: ' . $name . '. URL and product assignments preserved.' );
}
