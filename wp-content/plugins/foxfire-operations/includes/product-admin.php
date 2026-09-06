<?php
/**
 * Product operations fields, validation, previews, and list-table tools.
 *
 * @package Foxfire_Operations
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register the product operations fields with Advanced Custom Fields.
 *
 * @return void
 */
function foxfire_operations_register_product_fields() {
	if ( ! function_exists( 'acf_add_local_field_group' ) ) {
		return;
	}

	acf_add_local_field_group(
		array(
			'key'      => 'group_foxfire_product',
			'title'    => __( 'Foxfire Product Operations', 'foxfire-operations' ),
			'fields'   => array(
				array(
					'key'   => 'field_foxfire_store_setup_tab',
					'label' => __( 'Store Setup', 'foxfire-operations' ),
					'name'  => '',
					'type'  => 'tab',
				),
				array(
					'key'       => 'field_foxfire_native_product_guide',
					'label'     => __( 'WooCommerce product controls', 'foxfire-operations' ),
					'name'      => '',
					'type'      => 'message',
					'message'   => __( 'Use Product data for SKU, regular/sale price, and inventory. For products sold in different strengths, use Attributes and Variations and give every variation its own price, SKU, and stock value. Use Catalog visibility to mark a product as featured.', 'foxfire-operations' ),
					'new_lines' => 'wpautop',
					'esc_html'  => 1,
				),
				array(
					'key'   => 'field_foxfire_batch_tab',
					'label' => __( 'Batch & COA', 'foxfire-operations' ),
					'name'  => '',
					'type'  => 'tab',
				),
				array(
					'key'           => 'field_foxfire_batch_lot',
					'label'         => __( 'Batch / Lot Number', 'foxfire-operations' ),
					'name'          => 'foxfire_batch_lot',
					'type'          => 'text',
					'instructions'  => __( 'Enter the identifier printed on the product packaging. Maximum 80 characters.', 'foxfire-operations' ),
					'maxlength'     => 80,
					'wrapper'       => array( 'width' => 50 ),
				),
				array(
					'key'           => 'field_foxfire_testing_summary',
					'label'         => __( 'Testing Status', 'foxfire-operations' ),
					'name'          => 'foxfire_testing_summary',
					'type'          => 'select',
					'instructions'  => __( 'Use only a factual workflow status; this does not make a quality or regulatory claim.', 'foxfire-operations' ),
					'choices'       => array(
						'Information Pending'   => __( 'Information Pending', 'foxfire-operations' ),
						'Information Available' => __( 'Information Available', 'foxfire-operations' ),
						'Report Available'      => __( 'Report Available', 'foxfire-operations' ),
						'On File'               => __( 'On File', 'foxfire-operations' ),
						'Archived'              => __( 'Archived', 'foxfire-operations' ),
					),
					'allow_null'    => 1,
					'ui'            => 1,
					'return_format' => 'value',
					'wrapper'       => array( 'width' => 50 ),
				),
				array(
					'key'          => 'field_foxfire_coa_url',
					'label'        => __( 'COA Report URL', 'foxfire-operations' ),
					'name'         => 'foxfire_coa_url',
					'type'         => 'url',
					'instructions' => __( 'Link directly to the batch report when possible. A link to the general testing directory is shown as a placeholder in the Products list.', 'foxfire-operations' ),
					'wrapper'      => array( 'width' => 50 ),
				),
				array(
					'key'           => 'field_foxfire_coa_file',
					'label'         => __( 'COA Report File', 'foxfire-operations' ),
					'name'          => 'foxfire_coa_file',
					'type'          => 'file',
					'instructions'  => __( 'Upload a PDF or an image of the report. This takes precedence over the URL on the storefront.', 'foxfire-operations' ),
					'return_format' => 'url',
					'library'       => 'all',
					'mime_types'    => 'pdf,jpg,jpeg,png,webp',
					'wrapper'       => array( 'width' => 50 ),
				),
				array(
					'key'          => 'field_foxfire_coa_label',
					'label'        => __( 'COA Link Label', 'foxfire-operations' ),
					'name'         => 'foxfire_coa_label',
					'type'         => 'text',
					'instructions' => __( 'Example: View Batch COA. Maximum 80 characters.', 'foxfire-operations' ),
					'placeholder'  => __( 'View Batch COA', 'foxfire-operations' ),
					'maxlength'    => 80,
				),
				array(
					'key'   => 'field_foxfire_tier_tab',
					'label' => __( 'Quantity Pricing', 'foxfire-operations' ),
					'name'  => '',
					'type'  => 'tab',
				),
				array(
					'key'           => 'field_foxfire_tier_enable',
					'label'         => __( 'Enable Quantity Pricing', 'foxfire-operations' ),
					'name'          => 'foxfire_tier_enable',
					'type'          => 'true_false',
					'instructions'  => __( 'Applies the configured discount when the cart line reaches the qualifying quantity.', 'foxfire-operations' ),
					'default_value' => 1,
					'ui'            => 1,
				),
				array(
					'key'               => 'field_foxfire_tier_3_discount',
					'label'             => __( '3+ Unit Discount (%)', 'foxfire-operations' ),
					'name'              => 'foxfire_tier_3_discount',
					'type'              => 'number',
					'min'               => 0,
					'max'               => 50,
					'step'              => 0.01,
					'append'            => '%',
					'wrapper'           => array( 'width' => 50 ),
					'conditional_logic' => array(
						array(
							array(
								'field'    => 'field_foxfire_tier_enable',
								'operator' => '==',
								'value'    => '1',
							),
						),
					),
				),
				array(
					'key'               => 'field_foxfire_tier_5_discount',
					'label'             => __( '5+ Unit Discount (%)', 'foxfire-operations' ),
					'name'              => 'foxfire_tier_5_discount',
					'type'              => 'number',
					'instructions'      => __( 'Must be equal to or greater than the 3+ unit discount.', 'foxfire-operations' ),
					'min'               => 0,
					'max'               => 50,
					'step'              => 0.01,
					'append'            => '%',
					'wrapper'           => array( 'width' => 50 ),
					'conditional_logic' => array(
						array(
							array(
								'field'    => 'field_foxfire_tier_enable',
								'operator' => '==',
								'value'    => '1',
							),
						),
					),
				),
				array(
					'key'          => 'field_foxfire_pricing_rule',
					'label'        => __( 'Pricing order', 'foxfire-operations' ),
					'name'         => '',
					'type'         => 'message',
					'message'      => __( 'Quantity discounts apply to the current WooCommerce price (including an active sale price). Coupons are applied afterward by WooCommerce.', 'foxfire-operations' ),
					'new_lines'    => 'wpautop',
					'esc_html'     => 1,
				),
			),
			'location' => array(
				array(
					array(
						'param'    => 'post_type',
						'operator' => '==',
						'value'    => 'product',
					),
				),
			),
			'position' => 'normal',
			'style'    => 'default',
			'active'   => true,
		)
	);
}
add_action( 'acf/init', 'foxfire_operations_register_product_fields' );

/**
 * Sanitize short product operations text values before ACF stores them.
 *
 * @param mixed $value Submitted value.
 * @return string
 */
function foxfire_operations_sanitize_short_text( $value ) {
	$value = sanitize_text_field( (string) $value );

	return function_exists( 'mb_substr' ) ? mb_substr( $value, 0, 80 ) : substr( $value, 0, 80 );
}
add_filter( 'acf/update_value/name=foxfire_batch_lot', 'foxfire_operations_sanitize_short_text' );
add_filter( 'acf/update_value/name=foxfire_coa_label', 'foxfire_operations_sanitize_short_text' );

/**
 * Sanitize the external COA URL.
 *
 * @param mixed $value Submitted value.
 * @return string
 */
function foxfire_operations_sanitize_coa_url( $value ) {
	return esc_url_raw( (string) $value, array( 'http', 'https' ) );
}
add_filter( 'acf/update_value/name=foxfire_coa_url', 'foxfire_operations_sanitize_coa_url' );

/**
 * Normalize tier switches.
 *
 * @param mixed $value Submitted value.
 * @return int
 */
function foxfire_operations_sanitize_tier_switch( $value ) {
	return empty( $value ) ? 0 : 1;
}
add_filter( 'acf/update_value/name=foxfire_tier_enable', 'foxfire_operations_sanitize_tier_switch' );

/**
 * Normalize tier percentages to the supported range.
 *
 * @param mixed $value Submitted value.
 * @return string
 */
function foxfire_operations_sanitize_tier_discount( $value ) {
	if ( '' === $value || null === $value ) {
		return '';
	}

	return (string) min( 50, max( 0, (float) wc_format_decimal( $value ) ) );
}
add_filter( 'acf/update_value/name=foxfire_tier_3_discount', 'foxfire_operations_sanitize_tier_discount' );
add_filter( 'acf/update_value/name=foxfire_tier_5_discount', 'foxfire_operations_sanitize_tier_discount' );

/**
 * Preserve monotonic tier pricing for programmatic ACF updates as well as UI saves.
 *
 * @param mixed $value   Sanitized 5+ discount.
 * @param mixed $post_id ACF post ID.
 * @return string
 */
function foxfire_operations_enforce_tier_order( $value, $post_id ) {
	if ( '' === $value || null === $value ) {
		return '';
	}

	$product_id = absint( $post_id );
	$tier_3     = $product_id ? get_post_meta( $product_id, 'foxfire_tier_3_discount', true ) : '';

	return is_numeric( $tier_3 ) ? (string) max( (float) $value, (float) $tier_3 ) : (string) $value;
}
add_filter( 'acf/update_value/name=foxfire_tier_5_discount', 'foxfire_operations_enforce_tier_order', 20, 2 );

/**
 * Normalize testing status even when data is updated programmatically.
 *
 * @param mixed $value Submitted value.
 * @return string
 */
function foxfire_operations_sanitize_testing_status( $value ) {
	$value   = sanitize_text_field( (string) $value );
	$allowed = array( 'Information Pending', 'Information Available', 'Report Available', 'On File', 'Archived' );

	return in_array( $value, $allowed, true ) ? $value : '';
}
add_filter( 'acf/update_value/name=foxfire_testing_summary', 'foxfire_operations_sanitize_testing_status' );

/**
 * Validate allowed testing workflow statuses.
 *
 * @param bool|string $valid Existing validation result.
 * @param mixed       $value Submitted value.
 * @return bool|string
 */
function foxfire_operations_validate_testing_status( $valid, $value ) {
	if ( true !== $valid || '' === $value || null === $value ) {
		return $valid;
	}

	$allowed = array( 'Information Pending', 'Information Available', 'Report Available', 'On File', 'Archived' );

	return in_array( (string) $value, $allowed, true )
		? $valid
		: __( 'Choose one of the available testing workflow statuses.', 'foxfire-operations' );
}
add_filter( 'acf/validate_value/name=foxfire_testing_summary', 'foxfire_operations_validate_testing_status', 10, 2 );

/**
 * Validate a tier percentage without silently accepting an unsafe value.
 *
 * @param bool|string $valid Existing validation result.
 * @param mixed       $value Submitted value.
 * @return bool|string
 */
function foxfire_operations_validate_tier_discount( $valid, $value ) {
	if ( true !== $valid || '' === $value || null === $value ) {
		return $valid;
	}

	if ( ! is_numeric( $value ) || (float) $value < 0 || (float) $value > 50 ) {
		return __( 'Enter a discount from 0 to 50 percent.', 'foxfire-operations' );
	}

	return $valid;
}
add_filter( 'acf/validate_value/name=foxfire_tier_3_discount', 'foxfire_operations_validate_tier_discount', 10, 2 );
add_filter( 'acf/validate_value/name=foxfire_tier_5_discount', 'foxfire_operations_validate_tier_discount', 10, 2 );

/**
 * Require the 5+ discount to be no lower than the 3+ discount.
 *
 * @param bool|string $valid Existing validation result.
 * @param mixed       $value Submitted 5+ value.
 * @return bool|string
 */
function foxfire_operations_validate_tier_order( $valid, $value ) {
	if ( true !== $valid || '' === $value || null === $value ) {
		return $valid;
	}

	$acf_input = isset( $_POST['acf'] ) && is_array( $_POST['acf'] )
		? wp_unslash( $_POST['acf'] ) // phpcs:ignore WordPress.Security.NonceVerification.Missing -- This validation filter does not mutate data; ACF verifies its form nonce before saving.
		: array();
	$tier_3    = is_array( $acf_input ) && isset( $acf_input['field_foxfire_tier_3_discount'] )
		? $acf_input['field_foxfire_tier_3_discount']
		: '';

	if ( '' !== $tier_3 && is_numeric( $tier_3 ) && (float) $value < (float) $tier_3 ) {
		return __( 'The 5+ unit discount must be equal to or greater than the 3+ unit discount.', 'foxfire-operations' );
	}

	return $valid;
}
add_filter( 'acf/validate_value/name=foxfire_tier_5_discount', 'foxfire_operations_validate_tier_order', 20, 2 );

/**
 * Validate COA report URLs independently of browser-side field validation.
 *
 * @param bool|string $valid Existing validation result.
 * @param mixed       $value Submitted value.
 * @return bool|string
 */
function foxfire_operations_validate_coa_url( $valid, $value ) {
	if ( true !== $valid || '' === $value || null === $value ) {
		return $valid;
	}

	$url    = (string) $value;
	$scheme = wp_parse_url( $url, PHP_URL_SCHEME );
	if ( strlen( $url ) > 2048 || ! in_array( strtolower( (string) $scheme ), array( 'http', 'https' ), true ) ) {
		return __( 'Enter a valid HTTP or HTTPS report URL no longer than 2,048 characters.', 'foxfire-operations' );
	}

	return $valid;
}
add_filter( 'acf/validate_value/name=foxfire_coa_url', 'foxfire_operations_validate_coa_url', 10, 2 );

/**
 * Validate uploaded COA file types server-side.
 *
 * @param bool|string $valid Existing validation result.
 * @param mixed       $value Attachment ID or empty value.
 * @return bool|string
 */
function foxfire_operations_validate_coa_file( $valid, $value ) {
	if ( true !== $valid || empty( $value ) ) {
		return $valid;
	}

	$attachment_id = is_array( $value ) && isset( $value['ID'] ) ? absint( $value['ID'] ) : absint( $value );
	$allowed       = array( 'application/pdf', 'image/jpeg', 'image/png', 'image/webp' );
	$mime_type     = $attachment_id ? get_post_mime_type( $attachment_id ) : '';

	return in_array( $mime_type, $allowed, true )
		? $valid
		: __( 'Upload a PDF, JPEG, PNG, or WebP report file.', 'foxfire-operations' );
}
add_filter( 'acf/validate_value/name=foxfire_coa_file', 'foxfire_operations_validate_coa_file', 10, 2 );

/**
 * Identify a general testing-directory link rather than a direct report.
 *
 * @param string $url COA URL.
 * @return bool
 */
function foxfire_operations_is_coa_placeholder_url( $url ) {
	$path = wp_parse_url( $url, PHP_URL_PATH );

	return is_string( $path ) && false !== strpos( trailingslashit( strtolower( $path ) ), '/testing-coa/' );
}

/**
 * Resolve the product's operational COA state.
 *
 * @param int $product_id Product ID.
 * @return string direct, placeholder, or missing.
 */
function foxfire_operations_get_coa_state( $product_id ) {
	$file = (string) get_post_meta( $product_id, 'foxfire_coa_file', true );
	$url  = (string) get_post_meta( $product_id, 'foxfire_coa_url', true );

	if ( '' !== $file || ( '' !== $url && ! foxfire_operations_is_coa_placeholder_url( $url ) ) ) {
		return 'direct';
	}

	return '' !== $url ? 'placeholder' : 'missing';
}

/**
 * Add operational columns to the Products screen.
 *
 * @param array $columns Existing columns.
 * @return array
 */
function foxfire_operations_product_columns( $columns ) {
	$updated = array();

	foreach ( $columns as $key => $label ) {
		$updated[ $key ] = $label;
		if ( 'sku' === $key ) {
			$updated['foxfire_batch'] = __( 'Batch', 'foxfire-operations' );
			$updated['foxfire_coa']   = __( 'COA', 'foxfire-operations' );
			$updated['foxfire_tiers'] = __( 'Qty pricing', 'foxfire-operations' );
		}
	}

	return $updated;
}
add_filter( 'manage_edit-product_columns', 'foxfire_operations_product_columns', 20 );

/**
 * Render product operational columns.
 *
 * @param string $column     Column key.
 * @param int    $product_id Product ID.
 * @return void
 */
function foxfire_operations_render_product_column( $column, $product_id ) {
	if ( 'foxfire_batch' === $column ) {
		$batch = get_post_meta( $product_id, 'foxfire_batch_lot', true );
		echo '' !== $batch ? esc_html( $batch ) : '<span class="foxfire-ops-muted">&mdash;</span>';
		return;
	}

	if ( 'foxfire_coa' === $column ) {
		$state  = foxfire_operations_get_coa_state( $product_id );
		$labels = array(
			'direct'      => __( 'Report ready', 'foxfire-operations' ),
			'placeholder' => __( 'Placeholder', 'foxfire-operations' ),
			'missing'     => __( 'Missing', 'foxfire-operations' ),
		);
		printf(
			'<span class="foxfire-ops-status foxfire-ops-status--%1$s">%2$s</span>',
			esc_attr( $state ),
			esc_html( $labels[ $state ] )
		);
		return;
	}

	if ( 'foxfire_tiers' === $column ) {
		if ( ! foxfire_operations_tier_pricing_enabled( $product_id ) ) {
			echo '<span class="foxfire-ops-muted">' . esc_html__( 'Disabled', 'foxfire-operations' ) . '</span>';
			return;
		}

		$discounts = foxfire_operations_get_tier_discounts( $product_id );
		printf(
			'<span class="foxfire-ops-tier">3+: %1$s%%<br>5+: %2$s%%</span>',
			esc_html( wc_format_localized_decimal( $discounts[3] ) ),
			esc_html( wc_format_localized_decimal( $discounts[5] ) )
		);
	}
}
add_action( 'manage_product_posts_custom_column', 'foxfire_operations_render_product_column', 10, 2 );

/**
 * Add operational filters to the Products screen.
 *
 * @param string $post_type Current post type.
 * @return void
 */
function foxfire_operations_product_filters( $post_type ) {
	if ( 'product' !== $post_type || ! current_user_can( 'edit_products' ) ) {
		return;
	}

	$coa_filter  = isset( $_GET['foxfire_coa_filter'] ) ? sanitize_key( wp_unslash( $_GET['foxfire_coa_filter'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only list-table filter.
	$tier_filter = isset( $_GET['foxfire_tier_filter'] ) ? sanitize_key( wp_unslash( $_GET['foxfire_tier_filter'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only list-table filter.

	echo '<label class="screen-reader-text" for="foxfire_coa_filter">' . esc_html__( 'Filter by COA status', 'foxfire-operations' ) . '</label>';
	echo '<select name="foxfire_coa_filter" id="foxfire_coa_filter">';
	foreach ( array( '' => __( 'All COA statuses', 'foxfire-operations' ), 'direct' => __( 'COA: report ready', 'foxfire-operations' ), 'placeholder' => __( 'COA: placeholder', 'foxfire-operations' ), 'missing' => __( 'COA: missing', 'foxfire-operations' ) ) as $value => $label ) {
		printf( '<option value="%1$s"%2$s>%3$s</option>', esc_attr( $value ), selected( $coa_filter, $value, false ), esc_html( $label ) );
	}
	echo '</select>';

	echo '<label class="screen-reader-text" for="foxfire_tier_filter">' . esc_html__( 'Filter by quantity pricing', 'foxfire-operations' ) . '</label>';
	echo '<select name="foxfire_tier_filter" id="foxfire_tier_filter">';
	foreach ( array( '' => __( 'All quantity pricing', 'foxfire-operations' ), 'enabled' => __( 'Quantity pricing enabled', 'foxfire-operations' ), 'disabled' => __( 'Quantity pricing disabled', 'foxfire-operations' ) ) as $value => $label ) {
		printf( '<option value="%1$s"%2$s>%3$s</option>', esc_attr( $value ), selected( $tier_filter, $value, false ), esc_html( $label ) );
	}
	echo '</select>';
}
add_action( 'restrict_manage_posts', 'foxfire_operations_product_filters' );

/**
 * Apply operational filters to the main Products query.
 *
 * @param WP_Query $query Current query.
 * @return void
 */
function foxfire_operations_filter_products_query( $query ) {
	if ( ! is_admin() || ! $query->is_main_query() || 'product' !== $query->get( 'post_type' ) || ! current_user_can( 'edit_products' ) ) {
		return;
	}

	$coa_filter  = isset( $_GET['foxfire_coa_filter'] ) ? sanitize_key( wp_unslash( $_GET['foxfire_coa_filter'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only list-table filter.
	$tier_filter = isset( $_GET['foxfire_tier_filter'] ) ? sanitize_key( wp_unslash( $_GET['foxfire_tier_filter'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only list-table filter.
	$conditions  = array();

	if ( 'direct' === $coa_filter ) {
		$conditions[] = array(
			'relation' => 'OR',
			array( 'key' => 'foxfire_coa_file', 'value' => '', 'compare' => '!=' ),
			array(
				'relation' => 'AND',
				array( 'key' => 'foxfire_coa_url', 'value' => '', 'compare' => '!=' ),
				array( 'key' => 'foxfire_coa_url', 'value' => '/testing-coa', 'compare' => 'NOT LIKE' ),
			),
		);
	} elseif ( 'placeholder' === $coa_filter ) {
		$conditions[] = array(
			'relation' => 'AND',
			array( 'key' => 'foxfire_coa_url', 'value' => '/testing-coa', 'compare' => 'LIKE' ),
			array(
				'relation' => 'OR',
				array( 'key' => 'foxfire_coa_file', 'compare' => 'NOT EXISTS' ),
				array( 'key' => 'foxfire_coa_file', 'value' => '', 'compare' => '=' ),
			),
		);
	} elseif ( 'missing' === $coa_filter ) {
		$conditions[] = array(
			'relation' => 'AND',
			array(
				'relation' => 'OR',
				array( 'key' => 'foxfire_coa_url', 'compare' => 'NOT EXISTS' ),
				array( 'key' => 'foxfire_coa_url', 'value' => '', 'compare' => '=' ),
			),
			array(
				'relation' => 'OR',
				array( 'key' => 'foxfire_coa_file', 'compare' => 'NOT EXISTS' ),
				array( 'key' => 'foxfire_coa_file', 'value' => '', 'compare' => '=' ),
			),
		);
	}

	if ( 'enabled' === $tier_filter ) {
		$conditions[] = array(
			'relation' => 'OR',
			array( 'key' => 'foxfire_tier_enable', 'compare' => 'NOT EXISTS' ),
			array( 'key' => 'foxfire_tier_enable', 'value' => '0', 'compare' => '!=' ),
		);
	} elseif ( 'disabled' === $tier_filter ) {
		$conditions[] = array( 'key' => 'foxfire_tier_enable', 'value' => '0', 'compare' => '=' );
	}

	if ( empty( $conditions ) ) {
		return;
	}

	$existing_meta_query = $query->get( 'meta_query' );
	if ( ! empty( $existing_meta_query ) ) {
		$conditions = array_merge( array( $existing_meta_query ), $conditions );
	}

	$query->set( 'meta_query', array_merge( array( 'relation' => 'AND' ), $conditions ) );
}
add_action( 'pre_get_posts', 'foxfire_operations_filter_products_query' );

/**
 * Add a read-only quantity-pricing preview to product edit screens.
 *
 * @return void
 */
function foxfire_operations_add_pricing_preview_metabox() {
	if ( current_user_can( 'edit_products' ) ) {
		add_meta_box(
			'foxfire-quantity-pricing-preview',
			__( 'Quantity Pricing Preview', 'foxfire-operations' ),
			'foxfire_operations_render_pricing_preview_metabox',
			'product',
			'normal',
			'default'
		);
	}
}
add_action( 'add_meta_boxes_product', 'foxfire_operations_add_pricing_preview_metabox' );

/**
 * Render quantity prices for the product and its variations.
 *
 * @param WP_Post $post Product post.
 * @return void
 */
function foxfire_operations_render_pricing_preview_metabox( $post ) {
	$product = wc_get_product( $post->ID );
	if ( ! $product ) {
		echo '<p>' . esc_html__( 'Save the product once to generate a pricing preview.', 'foxfire-operations' ) . '</p>';
		return;
	}

	$rows = array();
	if ( $product->is_type( 'variable' ) ) {
		foreach ( array_slice( $product->get_children(), 0, 50 ) as $variation_id ) {
			$variation = wc_get_product( $variation_id );
			if ( $variation && '' !== $variation->get_price( 'edit' ) ) {
				$rows[] = array(
					'label' => wp_strip_all_tags( wc_get_formatted_variation( $variation, true, false, true ) ),
					'price' => (float) $variation->get_price( 'edit' ),
				);
			}
		}
	} elseif ( '' !== $product->get_price( 'edit' ) ) {
		$rows[] = array(
			'label' => __( 'Product', 'foxfire-operations' ),
			'price' => (float) $product->get_price( 'edit' ),
		);
	}

	if ( empty( $rows ) ) {
		echo '<p>' . esc_html__( 'Add and save a WooCommerce price to see the tier preview.', 'foxfire-operations' ) . '</p>';
		return;
	}

	$discounts = foxfire_operations_get_tier_discounts( $post->ID );
	$enabled   = foxfire_operations_tier_pricing_enabled( $post->ID );

	echo '<div class="foxfire-pricing-preview" data-product-type="' . esc_attr( $product->get_type() ) . '">';
	echo '<p>' . esc_html__( 'Preview uses the current active WooCommerce price. An active sale price is discounted first; coupons apply afterward.', 'foxfire-operations' ) . '</p>';
	echo '<div class="foxfire-pricing-preview__scroll"><table class="widefat striped"><thead><tr>';
	echo '<th>' . esc_html__( 'Item', 'foxfire-operations' ) . '</th><th>' . esc_html__( '1-unit total', 'foxfire-operations' ) . '</th><th>' . esc_html__( '3-unit total', 'foxfire-operations' ) . '</th><th>' . esc_html__( '5-unit total', 'foxfire-operations' ) . '</th>';
	echo '</tr></thead><tbody>';
	foreach ( $rows as $row ) {
		$tier_3_price = $enabled ? foxfire_operations_calculate_tier_unit_price( $row['price'], 3, $discounts ) : $row['price'];
		$tier_5_price = $enabled ? foxfire_operations_calculate_tier_unit_price( $row['price'], 5, $discounts ) : $row['price'];
		printf(
			'<tr data-base-price="%1$s"><th scope="row">%2$s</th><td data-tier="1">%3$s</td><td data-tier="3">%4$s</td><td data-tier="5">%5$s</td></tr>',
			esc_attr( $row['price'] ),
			esc_html( $row['label'] ),
			wp_kses_post( wc_price( $row['price'] ) ),
			wp_kses_post( wc_price( $tier_3_price * 3 ) ),
			wp_kses_post( wc_price( $tier_5_price * 5 ) )
		);
	}
	echo '</tbody></table></div></div>';
}

/**
 * Warn administrators if the field-management dependency is unavailable.
 *
 * @return void
 */
function foxfire_operations_acf_dependency_notice() {
	if ( function_exists( 'acf_add_local_field_group' ) || ! current_user_can( 'activate_plugins' ) ) {
		return;
	}

	echo '<div class="notice notice-error"><p>';
	echo esc_html__( 'Foxfire Operations requires Advanced Custom Fields for product batch, COA, and quantity-pricing controls.', 'foxfire-operations' );
	echo '</p></div>';
}
add_action( 'admin_notices', 'foxfire_operations_acf_dependency_notice' );
