<?php
/**
 * Secure homepage product merchandising controls.
 *
 * Product selection belongs to the operations layer so a visual theme change
 * does not discard the client's saved merchandising order.
 *
 * @package Foxfire_Operations
 */

defined( 'ABSPATH' ) || exit;

const FOXFIRE_OPERATIONS_HOMEPAGE_PRODUCTS_OPTION       = 'foxfire_homepage_product_ids';
const FOXFIRE_OPERATIONS_HOMEPAGE_PRODUCTS_AUDIT_OPTION = 'foxfire_homepage_product_ids_audit';
const FOXFIRE_OPERATIONS_HOMEPAGE_PRODUCTS_LIMIT        = 8;

/** Determine whether the current operator may manage homepage products. */
function foxfire_operations_can_manage_homepage_products(): bool {
	return current_user_can( FOXFIRE_OPERATIONS_CAPABILITY ) && current_user_can( 'edit_products' );
}

/**
 * Normalize the saved option without trusting its storage shape.
 *
 * @param mixed $value Raw option value.
 * @return int[]
 */
function foxfire_operations_normalize_homepage_product_ids( $value ): array {
	if ( ! is_array( $value ) ) {
		return array();
	}

	$product_ids = array();
	foreach ( $value as $raw_product_id ) {
		if ( ! is_int( $raw_product_id ) && ! is_string( $raw_product_id ) ) {
			continue;
		}

		$product_id = absint( $raw_product_id );
		if ( $product_id <= 0 || in_array( $product_id, $product_ids, true ) ) {
			continue;
		}

		$product_ids[] = $product_id;
		if ( count( $product_ids ) >= FOXFIRE_OPERATIONS_HOMEPAGE_PRODUCTS_LIMIT ) {
			break;
		}
	}

	return $product_ids;
}

/**
 * Return the saved order, optionally excluding products that cannot currently
 * be offered on the public storefront.
 *
 * @param bool $available_only Whether to return only public, purchasable, in-stock products.
 * @return int[]
 */
function foxfire_operations_get_homepage_product_ids( bool $available_only = true ): array {
	$product_ids = foxfire_operations_normalize_homepage_product_ids(
		get_option( FOXFIRE_OPERATIONS_HOMEPAGE_PRODUCTS_OPTION, array() )
	);

	if ( ! $available_only || ! function_exists( 'wc_get_product' ) ) {
		return $product_ids;
	}

	return array_values(
		array_filter(
			$product_ids,
			static function ( int $product_id ): bool {
				if ( 'product' !== get_post_type( $product_id ) || 'publish' !== get_post_status( $product_id ) ) {
					return false;
				}

				$product = wc_get_product( $product_id );
				return $product instanceof WC_Product
					&& 'hidden' !== $product->get_catalog_visibility()
					&& $product->is_in_stock()
					&& $product->is_purchasable();
			}
		)
	);
}

/**
 * Validate a submitted ordered product list without partially saving it.
 *
 * @param mixed $value Raw submitted value.
 * @return int[]|WP_Error
 */
function foxfire_operations_validate_homepage_product_ids( $value ) {
	if ( ! is_array( $value ) ) {
		return new WP_Error( 'invalid-payload', __( 'The product selection was invalid.', 'foxfire-operations' ) );
	}

	$product_ids = array();
	foreach ( $value as $raw_product_id ) {
		if ( '' === $raw_product_id || null === $raw_product_id ) {
			continue;
		}

		if (
			( ! is_int( $raw_product_id ) && ! is_string( $raw_product_id ) )
			|| ! preg_match( '/^[1-9][0-9]*$/', (string) $raw_product_id )
		) {
			return new WP_Error( 'invalid-product', __( 'One or more selected products were invalid.', 'foxfire-operations' ) );
		}

		$product_id = absint( $raw_product_id );
		if ( in_array( $product_id, $product_ids, true ) ) {
			return new WP_Error( 'duplicate-product', __( 'Each homepage product may be selected only once.', 'foxfire-operations' ) );
		}

		$product_ids[] = $product_id;
		if ( count( $product_ids ) > FOXFIRE_OPERATIONS_HOMEPAGE_PRODUCTS_LIMIT ) {
			return new WP_Error( 'too-many-products', __( 'Select no more than eight homepage products.', 'foxfire-operations' ) );
		}
	}

	foreach ( $product_ids as $product_id ) {
		if (
			'product' !== get_post_type( $product_id )
			|| 'publish' !== get_post_status( $product_id )
			|| ! current_user_can( 'edit_post', $product_id )
		) {
			return new WP_Error( 'invalid-product', __( 'Only published products you may edit can be selected.', 'foxfire-operations' ) );
		}
	}

	return $product_ids;
}

/** Register the merchandising page under Foxfire Operations. */
function foxfire_operations_register_homepage_products_page(): void {
	add_submenu_page(
		'foxfire-operations',
		__( 'Homepage Products', 'foxfire-operations' ),
		__( 'Homepage Products', 'foxfire-operations' ),
		FOXFIRE_OPERATIONS_CAPABILITY,
		'foxfire-homepage-products',
		'foxfire_operations_render_homepage_products_page'
	);
}
add_action( 'admin_menu', 'foxfire_operations_register_homepage_products_page', 41 );

/**
 * Return published products the current operator may place on the homepage.
 *
 * @return array<int, string> Product ID to admin label.
 */
function foxfire_operations_get_homepage_product_choices(): array {
	if ( ! function_exists( 'wc_get_product' ) ) {
		return array();
	}

	$query = new WP_Query(
		array(
			'post_type'              => 'product',
			'post_status'            => 'publish',
			'posts_per_page'         => -1,
			'orderby'                => 'title',
			'order'                  => 'ASC',
			'fields'                 => 'ids',
			'no_found_rows'          => true,
			'ignore_sticky_posts'    => true,
			'update_post_meta_cache' => false,
			'update_post_term_cache' => false,
		)
	);

	$choices = array();
	foreach ( $query->posts as $product_id ) {
		$product_id = absint( $product_id );
		if ( $product_id <= 0 || ! current_user_can( 'edit_post', $product_id ) ) {
			continue;
		}

		$product = wc_get_product( $product_id );
		if ( ! $product instanceof WC_Product ) {
			continue;
		}

		$stock_label = $product->is_in_stock() ? __( 'In stock', 'foxfire-operations' ) : __( 'Out of stock', 'foxfire-operations' );
		$sku          = $product->get_sku();
		$label        = $product->get_name();
		if ( '' !== $sku ) {
			$label .= ' — ' . sprintf( __( 'SKU: %s', 'foxfire-operations' ), $sku );
		}
		$choices[ $product_id ] = sprintf( '%1$s — %2$s (#%3$d)', $label, $stock_label, $product_id );
	}

	return $choices;
}

/** Save the ordered selection through a protected POST-only action. */
function foxfire_operations_save_homepage_products(): void {
	if ( ! foxfire_operations_can_manage_homepage_products() ) {
		wp_die(
			esc_html__( 'You do not have permission to manage homepage products.', 'foxfire-operations' ),
			esc_html__( 'Access denied', 'foxfire-operations' ),
			array( 'response' => 403 )
		);
	}

	$request_method = isset( $_SERVER['REQUEST_METHOD'] ) ? strtoupper( sanitize_text_field( wp_unslash( $_SERVER['REQUEST_METHOD'] ) ) ) : '';
	if ( 'POST' !== $request_method ) {
		wp_die(
			esc_html__( 'Homepage products can be saved only with a POST request.', 'foxfire-operations' ),
			esc_html__( 'Invalid request', 'foxfire-operations' ),
			array( 'response' => 405 )
		);
	}

	check_admin_referer( 'foxfire_save_homepage_products', 'foxfire_homepage_products_nonce' );

	$posted = isset( $_POST['foxfire_homepage_product_ids'] ) ? wp_unslash( $_POST['foxfire_homepage_product_ids'] ) : array();
	$result = foxfire_operations_validate_homepage_product_ids( $posted );
	$status = 'updated';

	if ( is_wp_error( $result ) ) {
		$status = $result->get_error_code();
	} else {
		update_option( FOXFIRE_OPERATIONS_HOMEPAGE_PRODUCTS_OPTION, $result, false );
		update_option(
			FOXFIRE_OPERATIONS_HOMEPAGE_PRODUCTS_AUDIT_OPTION,
			array(
				'user_id'     => get_current_user_id(),
				'updated_gmt' => gmdate( 'Y-m-d H:i:s' ),
				'count'       => count( $result ),
			),
			false
		);
	}

	wp_safe_redirect(
		add_query_arg(
			'foxfire_homepage_status',
			$status,
			admin_url( 'admin.php?page=foxfire-homepage-products' )
		)
	);
	exit;
}
add_action( 'admin_post_foxfire_save_homepage_products', 'foxfire_operations_save_homepage_products' );

/** Render the accessible ordered-product editor. */
function foxfire_operations_render_homepage_products_page(): void {
	if ( ! foxfire_operations_can_manage_homepage_products() ) {
		wp_die(
			esc_html__( 'You do not have permission to manage homepage products.', 'foxfire-operations' ),
			esc_html__( 'Access denied', 'foxfire-operations' ),
			array( 'response' => 403 )
		);
	}

	$choices     = foxfire_operations_get_homepage_product_choices();
	$product_ids = foxfire_operations_get_homepage_product_ids( false );
	$product_ids = array_pad( $product_ids, FOXFIRE_OPERATIONS_HOMEPAGE_PRODUCTS_LIMIT, 0 );
	$status      = isset( $_GET['foxfire_homepage_status'] ) ? sanitize_key( wp_unslash( $_GET['foxfire_homepage_status'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	$audit       = get_option( FOXFIRE_OPERATIONS_HOMEPAGE_PRODUCTS_AUDIT_OPTION, array() );
	$error_messages = array(
		'invalid-payload'   => __( 'Nothing was saved because the submitted selection was invalid.', 'foxfire-operations' ),
		'invalid-product'   => __( 'Nothing was saved. Select only published products you are allowed to edit.', 'foxfire-operations' ),
		'duplicate-product' => __( 'Nothing was saved because the same product was selected more than once.', 'foxfire-operations' ),
		'too-many-products' => __( 'Nothing was saved because more than eight products were submitted.', 'foxfire-operations' ),
	);
	?>
	<div class="wrap ff-ops-wrap ff-homepage-products">
		<h1><?php esc_html_e( 'Homepage Products', 'foxfire-operations' ); ?></h1>
		<p class="ff-ops-intro"><?php esc_html_e( 'Arrange up to eight published products in priority order. The homepage shows the first four available products; additional selections are backup priorities. This changes merchandising only, not prices, stock or product details.', 'foxfire-operations' ); ?></p>

		<?php if ( 'updated' === $status ) : ?>
			<div class="notice notice-success is-dismissible"><p><?php esc_html_e( 'Homepage product priorities saved.', 'foxfire-operations' ); ?></p></div>
		<?php elseif ( isset( $error_messages[ $status ] ) ) : ?>
			<div class="notice notice-error"><p><?php echo esc_html( $error_messages[ $status ] ); ?></p></div>
		<?php endif; ?>

		<div class="notice notice-info inline"><p><?php esc_html_e( 'If no products are selected—or every saved product later becomes unavailable—the storefront falls back to available WooCommerce featured products and then recent products.', 'foxfire-operations' ); ?></p></div>

		<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" class="ff-homepage-products__form">
			<input type="hidden" name="action" value="foxfire_save_homepage_products">
			<?php wp_nonce_field( 'foxfire_save_homepage_products', 'foxfire_homepage_products_nonce' ); ?>

			<p>
				<label for="foxfire-homepage-product-search"><strong><?php esc_html_e( 'Filter product choices', 'foxfire-operations' ); ?></strong></label><br>
				<input type="search" class="regular-text" id="foxfire-homepage-product-search" placeholder="<?php esc_attr_e( 'Search by name, SKU, or product ID', 'foxfire-operations' ); ?>" autocomplete="off">
			</p>

			<table class="widefat striped ff-ops-table ff-homepage-products__table">
				<thead><tr>
					<th scope="col"><?php esc_html_e( 'Order', 'foxfire-operations' ); ?></th>
					<th scope="col"><?php esc_html_e( 'Product', 'foxfire-operations' ); ?></th>
					<th scope="col"><?php esc_html_e( 'Reorder', 'foxfire-operations' ); ?></th>
				</tr></thead>
				<tbody id="foxfire-homepage-product-rows">
					<?php foreach ( $product_ids as $index => $selected_product_id ) : ?>
						<tr data-foxfire-homepage-product-row>
							<th scope="row"><span class="ff-homepage-products__position"><?php echo esc_html( (string) ( $index + 1 ) ); ?></span></th>
							<td>
								<label class="screen-reader-text" for="foxfire-homepage-product-<?php echo esc_attr( (string) $index ); ?>"><?php printf( esc_html__( 'Homepage product at position %d', 'foxfire-operations' ), esc_html( (string) ( $index + 1 ) ) ); ?></label>
								<select class="widefat ff-homepage-products__select" id="foxfire-homepage-product-<?php echo esc_attr( (string) $index ); ?>" name="foxfire_homepage_product_ids[]">
									<option value=""><?php esc_html_e( '— No product selected —', 'foxfire-operations' ); ?></option>
									<?php if ( $selected_product_id > 0 && ! isset( $choices[ $selected_product_id ] ) ) : ?>
										<option value="<?php echo esc_attr( (string) $selected_product_id ); ?>" selected><?php printf( esc_html__( 'Unavailable product (#%d) — remove or replace', 'foxfire-operations' ), esc_html( (string) $selected_product_id ) ); ?></option>
									<?php endif; ?>
									<?php foreach ( $choices as $product_id => $label ) : ?>
										<option value="<?php echo esc_attr( (string) $product_id ); ?>" <?php selected( $selected_product_id, $product_id ); ?>><?php echo esc_html( $label ); ?></option>
									<?php endforeach; ?>
								</select>
							</td>
							<td class="ff-homepage-products__actions">
								<button type="button" class="button ff-homepage-products__drag" aria-label="<?php esc_attr_e( 'Drag to reorder this position', 'foxfire-operations' ); ?>" title="<?php esc_attr_e( 'Drag to reorder', 'foxfire-operations' ); ?>">↕</button>
								<button type="button" class="button ff-homepage-products__up"><?php esc_html_e( 'Up', 'foxfire-operations' ); ?></button>
								<button type="button" class="button ff-homepage-products__down"><?php esc_html_e( 'Down', 'foxfire-operations' ); ?></button>
							</td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>

			<p class="description" id="foxfire-homepage-products-help"><?php esc_html_e( 'Drag rows or use the Up and Down buttons. Duplicate products are rejected. Unpublished, hidden, out-of-stock, or non-purchasable products are automatically omitted from the public homepage.', 'foxfire-operations' ); ?></p>
			<?php submit_button( __( 'Save homepage products', 'foxfire-operations' ) ); ?>
		</form>

		<?php if ( is_array( $audit ) && ! empty( $audit['updated_gmt'] ) ) : ?>
			<p class="description"><?php printf( esc_html__( 'Last saved: %s UTC.', 'foxfire-operations' ), esc_html( (string) $audit['updated_gmt'] ) ); ?></p>
		<?php endif; ?>
	</div>
	<?php
}
