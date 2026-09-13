<?php
/** Editable quantity options using native WooCommerce controls (no ACF Pro required). */
defined( 'ABSPATH' ) || exit;

function foxfire_operations_quantity_tab( $tabs ) {
	$tabs['foxfire_quantity'] = array( 'label' => __( 'Quantity Options', 'foxfire-operations' ), 'target' => 'foxfire_quantity_data', 'class' => array( 'show_if_simple', 'show_if_variable' ), 'priority' => 65 );
	return $tabs;
}
add_filter( 'woocommerce_product_data_tabs', 'foxfire_operations_quantity_tab' );

/** Shared accessible row editor; variation rows can inherit the parent rule. */
function foxfire_operations_quantity_editor( $name, $options, $variation = false ) {
	echo '<div class="ff-quantity-editor" style="overflow-x:auto" data-variation="' . ( $variation ? '1' : '0' ) . '">';
	echo '<table class="widefat"><thead><tr><th>' . esc_html__( 'Vials', 'foxfire-operations' ) . '</th><th>' . esc_html__( 'Pricing type', 'foxfire-operations' ) . '</th><th>' . esc_html__( 'Value', 'foxfire-operations' ) . '</th><th>' . esc_html__( 'Action', 'foxfire-operations' ) . '</th></tr></thead><tbody>';
	$index = 0;
	foreach ( $options as $quantity => $rule ) {
		foxfire_operations_quantity_editor_row( $name, $index++, $quantity, $rule, $variation );
	}
	echo '</tbody></table>';
	if ( ! $variation ) {
		echo '<p><button type="button" class="button ff-quantity-add">' . esc_html__( 'Add quantity option', 'foxfire-operations' ) . '</button></p>';
		echo '<template>';
		foxfire_operations_quantity_editor_row( $name, '__INDEX__', '', array( 'mode' => 'discount', 'value' => '0' ), false );
		echo '</template>';
	}
	echo '</div>';
}

function foxfire_operations_quantity_editor_row( $name, $index, $quantity, $rule, $variation ) {
	$prefix = $name . '[' . $index . ']';
	echo '<tr><td><input style="width:75px;float:none" type="number" min="1" max="10000" step="1" aria-label="' . esc_attr__( 'Vial quantity', 'foxfire-operations' ) . '" name="' . esc_attr( $prefix . '[quantity]' ) . '" value="' . esc_attr( $quantity ) . '"' . ( $variation ? ' readonly' : '' ) . '></td>';
	echo '<td><select style="float:none;width:auto" aria-label="' . esc_attr__( 'Quantity pricing type', 'foxfire-operations' ) . '" name="' . esc_attr( $prefix . '[mode]' ) . '">';
	$modes = array( 'discount' => __( 'Discount (%)', 'foxfire-operations' ), 'total' => sprintf( __( 'Total price (%s)', 'foxfire-operations' ), get_woocommerce_currency() ) );
	if ( $variation ) {
		$modes = array( 'inherit' => __( 'Use product rule', 'foxfire-operations' ) ) + $modes;
	}
	foreach ( $modes as $mode => $label ) {
		echo '<option value="' . esc_attr( $mode ) . '"' . selected( $rule['mode'], $mode, false ) . '>' . esc_html( $label ) . '</option>';
	}
	echo '</select></td><td><input style="width:100px;float:none" type="number" min="0" max="' . ( 'discount' === $rule['mode'] ? '100' : '1000000' ) . '" step="any" aria-label="' . esc_attr__( 'Discount percentage or total price', 'foxfire-operations' ) . '" name="' . esc_attr( $prefix . '[value]' ) . '" value="' . esc_attr( $rule['value'] ) . '"></td><td>';
	if ( ! $variation ) {
		echo '<button type="button" class="button ff-quantity-remove">' . esc_html__( 'Remove', 'foxfire-operations' ) . '</button>';
	}
	echo '</td></tr>';
}

function foxfire_operations_quantity_panel() {
	global $product_object;
	if ( ! $product_object instanceof WC_Product || ! current_user_can( 'edit_post', $product_object->get_id() ) ) {
		return;
	}
	echo '<div id="foxfire_quantity_data" class="panel woocommerce_options_panel hidden"><div style="padding:16px">';
	wp_nonce_field( 'foxfire_quantity_save', '_foxfire_quantity_nonce' );
	echo '<label><input type="checkbox" name="foxfire_quantity_enabled" value="1"' . checked( foxfire_operations_tier_pricing_enabled( $product_object->get_id() ), true, false ) . '> ' . esc_html__( 'Enable quantity options and pricing', 'foxfire-operations' ) . '</label>';
	echo '<p>' . esc_html__( 'Choose 1 to 12 quantity buttons for this product. Discount uses the active price of the selected strength. Total price is the complete price for that row’s vial quantity, not the price of one vial. A 0% discount uses the normal price. Enter prices using the same tax basis as the product’s regular price.', 'foxfire-operations' ) . '</p>';
	echo '<p>' . esc_html__( 'The highest qualifying quantity rule also applies when quantities change in the cart. Stock is counted in individual vials. Coupons apply afterward. For different fixed prices per strength, set overrides under Variations. Save/Update to apply changes and refresh the pricing preview.', 'foxfire-operations' ) . '</p>';
	foxfire_operations_quantity_editor( 'foxfire_quantity_rows', foxfire_operations_get_quantity_options( $product_object->get_id() ) );
	echo '</div></div>';
}
add_action( 'woocommerce_product_data_panels', 'foxfire_operations_quantity_panel' );

function foxfire_operations_save_quantity_panel( $product ) {
	$nonce = isset( $_POST['_foxfire_quantity_nonce'] ) && is_scalar( $_POST['_foxfire_quantity_nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['_foxfire_quantity_nonce'] ) ) : '';
	if ( ! wp_verify_nonce( $nonce, 'foxfire_quantity_save' ) || ! current_user_can( 'edit_post', $product->get_id() ) || ! isset( $_POST['foxfire_quantity_rows'] ) ) {
		return;
	}
	$options = foxfire_operations_validate_quantity_options( wp_unslash( $_POST['foxfire_quantity_rows'] ) );
	if ( is_wp_error( $options ) ) {
		WC_Admin_Meta_Boxes::add_error( $options->get_error_message() . ' ' . __( 'Previous quantity settings were kept.', 'foxfire-operations' ) );
		return;
	}
	$product->update_meta_data( '_foxfire_quantity_options', $options );
	$product->update_meta_data( 'foxfire_tier_enable', isset( $_POST['foxfire_quantity_enabled'] ) ? '1' : '0' );
}
add_action( 'woocommerce_admin_process_product_object', 'foxfire_operations_save_quantity_panel' );

function foxfire_operations_variation_quantity_editor( $loop, $variation_data, $variation ) {
	if ( ! current_user_can( 'edit_post', $variation->post_parent ) ) {
		return;
	}
	$options = foxfire_operations_get_quantity_options( $variation->post_parent );
	$overrides = get_post_meta( $variation->ID, '_foxfire_quantity_options', true );
	foreach ( $options as $quantity => &$rule ) {
		$rule = isset( $overrides[ $quantity ] ) && is_array( $overrides[ $quantity ] ) ? $overrides[ $quantity ] : array( 'mode' => 'inherit', 'value' => '0' );
	}
	unset( $rule );
	echo '<div class="form-row form-row-full"><h4>' . esc_html__( 'Quantity price overrides', 'foxfire-operations' ) . '</h4><p>' . esc_html__( 'Optional prices for this strength only. Use product rule inherits the Quantity Options tab. Save the product after changing the available quantities, then reopen this variation to edit its prices.', 'foxfire-operations' ) . '</p>';
	echo '<input type="hidden" name="foxfire_quantity_variation_nonce[' . esc_attr( $loop ) . ']" value="' . esc_attr( wp_create_nonce( 'foxfire_quantity_variation_' . $variation->ID ) ) . '">';
	foxfire_operations_quantity_editor( 'foxfire_quantity_variation[' . $loop . ']', $options, true );
	echo '</div>';
}
add_action( 'woocommerce_product_after_variable_attributes', 'foxfire_operations_variation_quantity_editor', 10, 3 );

function foxfire_operations_save_variation_quantity( $variation_id, $loop ) {
	$nonce = $_POST['foxfire_quantity_variation_nonce'][ $loop ] ?? '';
	if ( ! is_scalar( $nonce ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $nonce ) ), 'foxfire_quantity_variation_' . $variation_id ) || ! current_user_can( 'edit_post', wp_get_post_parent_id( $variation_id ) ) || ! isset( $_POST['foxfire_quantity_variation'][ $loop ] ) ) {
		return;
	}
	$rows = wp_unslash( $_POST['foxfire_quantity_variation'][ $loop ] );
	if ( ! is_array( $rows ) || count( $rows ) > 12 ) {
		return;
	}
	$options = foxfire_operations_get_quantity_options( wp_get_post_parent_id( $variation_id ) );
	$custom = array();
	$seen = array();
	foreach ( $rows as $row ) {
		if ( ! is_array( $row ) || ! isset( $row['mode'], $row['quantity'] ) || ! is_scalar( $row['quantity'] ) || ! isset( $options[ $row['quantity'] ] ) ) {
			WC_Admin_Meta_Boxes::add_error( __( 'Invalid variation quantity. Previous overrides were kept.', 'foxfire-operations' ) );
			return;
		}
		$quantity = filter_var( $row['quantity'], FILTER_VALIDATE_INT, array( 'options' => array( 'min_range' => 1, 'max_range' => 10000 ) ) );
		if ( ! $quantity || isset( $seen[ $quantity ] ) ) {
			WC_Admin_Meta_Boxes::add_error( __( 'Variation quantities must be unique whole numbers. Previous overrides were kept.', 'foxfire-operations' ) );
			return;
		}
		$seen[ $quantity ] = true;
		if ( 'inherit' !== $row['mode'] ) {
			$custom[] = $row;
		}
	}
	$validated = $custom ? foxfire_operations_validate_quantity_options( $custom ) : array();
	if ( is_wp_error( $validated ) ) {
		WC_Admin_Meta_Boxes::add_error( $validated->get_error_message() );
		return;
	}
	update_post_meta( $variation_id, '_foxfire_quantity_options', $validated );
	wc_delete_product_transients( wp_get_post_parent_id( $variation_id ) );
}
add_action( 'woocommerce_save_product_variation', 'foxfire_operations_save_variation_quantity', 10, 2 );

// Retain legacy values for compatibility, but present only one authoritative editor.
foreach ( array( 'foxfire_tier_enable', 'foxfire_tier_3_discount', 'foxfire_tier_5_discount' ) as $legacy_field ) {
	add_filter( 'acf/prepare_field/name=' . $legacy_field, '__return_false' );
}
