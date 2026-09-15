<?php
/** Apply approved catalog category wording and canonical slugs locally. */
defined( 'ABSPATH' ) || exit;
if ( 'local' !== wp_get_environment_type() ) {
	WP_CLI::error( 'This review script is restricted to the local environment.' );
}

$categories = array(
	'glp-1-agonists'    => array(
		'slug'        => 'metabolic-research',
		'name'        => 'Metabolic Research',
		'description' => 'Compounds for laboratory metabolic research.',
	),
	'recovery-healing'  => array(
		'slug'        => 'regenerative-research',
		'name'        => 'Regenerative Research',
		'description' => 'Compounds for laboratory regenerative research.',
	),
	'support-compounds' => array(
		'slug'        => 'specialty-research',
		'name'        => 'Specialty Research',
		'description' => 'Specialized compounds for laboratory research.',
	),
);

foreach ( $categories as $legacy_slug => $category ) {
	$term = get_term_by( 'slug', $category['slug'], 'product_cat' );
	if ( ! $term instanceof WP_Term ) {
		$term = get_term_by( 'slug', $legacy_slug, 'product_cat' );
	}
	if ( ! $term instanceof WP_Term ) {
		WP_CLI::error( 'Missing catalog category: ' . $legacy_slug );
	}

	$term_id            = (int) $term->term_id;
	$assigned_before    = get_objects_in_term( $term_id, 'product_cat' );
	$assigned_before    = is_wp_error( $assigned_before ) ? array() : array_map( 'intval', $assigned_before );
	$result = wp_update_term( $term->term_id, 'product_cat', $category );
	if ( is_wp_error( $result ) ) {
		WP_CLI::error( $result->get_error_message() );
	}
	$assigned_after = get_objects_in_term( $term_id, 'product_cat' );
	$assigned_after = is_wp_error( $assigned_after ) ? array() : array_map( 'intval', $assigned_after );
	sort( $assigned_before );
	sort( $assigned_after );
	if ( $assigned_before !== $assigned_after ) {
		WP_CLI::error( 'Product assignments changed unexpectedly for category: ' . $category['name'] );
	}

	WP_CLI::success(
		sprintf(
			'Category updated: %1$s (%2$s -> %3$s). Term ID %4$d and product assignments preserved.',
			$category['name'],
			$legacy_slug,
			$category['slug'],
			$term_id
		)
	);
}
