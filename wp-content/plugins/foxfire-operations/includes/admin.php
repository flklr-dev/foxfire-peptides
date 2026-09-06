<?php
/**
 * Foxfire Operations admin hub.
 *
 * @package Foxfire_Operations
 */

defined( 'ABSPATH' ) || exit;

/**
 * Register the operations hub for authorized store staff.
 */
function foxfire_operations_register_admin_menu(): void {
	add_menu_page(
		__( 'Foxfire Operations', 'foxfire-operations' ),
		__( 'Foxfire Ops', 'foxfire-operations' ),
		FOXFIRE_OPERATIONS_CAPABILITY,
		'foxfire-operations',
		'foxfire_operations_render_admin_page',
		'dashicons-store',
		55.4
	);
}
add_action( 'admin_menu', 'foxfire_operations_register_admin_menu', 30 );

/**
 * Enqueue operations assets only on relevant admin screens.
 *
 * @param string $hook_suffix Current admin page hook.
 */
function foxfire_operations_enqueue_admin_assets( string $hook_suffix ): void {
	$screen            = get_current_screen();
	$is_operations_hub = 'toplevel_page_foxfire-operations' === $hook_suffix;
	$is_order_queue    = str_ends_with( $hook_suffix, '_page_foxfire-order-queue' );
	$is_product_screen = $screen && 'product' === $screen->post_type && in_array( $screen->base, array( 'post', 'edit' ), true );
	$is_coupon_screen  = $screen && 'shop_coupon' === $screen->post_type && in_array( $screen->base, array( 'post', 'edit' ), true );
	$is_order_screen   = $screen && ( 'shop_order' === $screen->post_type || ( function_exists( 'wc_get_page_screen_id' ) && wc_get_page_screen_id( 'shop-order' ) === $screen->id ) );

	if ( ! $is_operations_hub && ! $is_order_queue && ! $is_product_screen && ! $is_coupon_screen && ! $is_order_screen ) {
		return;
	}

	$asset_path = FOXFIRE_OPERATIONS_DIR . 'assets/admin.css';
	wp_enqueue_style(
		'foxfire-operations-admin',
		FOXFIRE_OPERATIONS_URL . 'assets/admin.css',
		array(),
		file_exists( $asset_path ) ? (string) filemtime( $asset_path ) : FOXFIRE_OPERATIONS_VERSION
	);

	if ( ! $is_product_screen || 'post' !== $screen->base ) {
		return;
	}

	$script_path = FOXFIRE_OPERATIONS_DIR . 'assets/product-admin.js';
	wp_enqueue_script(
		'foxfire-operations-product-admin',
		FOXFIRE_OPERATIONS_URL . 'assets/product-admin.js',
		array(),
		file_exists( $script_path ) ? (string) filemtime( $script_path ) : FOXFIRE_OPERATIONS_VERSION,
		true
	);
	wp_localize_script(
		'foxfire-operations-product-admin',
		'FoxfireProductAdmin',
		array(
			'currency'       => get_woocommerce_currency(),
			'currencySymbol' => get_woocommerce_currency_symbol(),
			'decimals'       => wc_get_price_decimals(),
		)
	);
}
add_action( 'admin_enqueue_scripts', 'foxfire_operations_enqueue_admin_assets' );

/**
 * Return operational links based on the user's actual capabilities.
 *
 * @return array<int, array{label:string, description:string, url:string, capability:string}>
 */
function foxfire_operations_get_admin_links(): array {
	$links = array(
		array(
			'label'       => __( 'Order Queue', 'foxfire-operations' ),
			'description' => __( 'Work through orders awaiting payment, fulfillment, shipment, or staff follow-up.', 'foxfire-operations' ),
			'url'         => admin_url( 'admin.php?page=foxfire-order-queue' ),
			'capability'  => 'edit_shop_orders',
		),
		array(
			'label'       => __( 'All Orders', 'foxfire-operations' ),
			'description' => __( 'Review payment state, customer details, notes, fulfillment, refunds, and order status.', 'foxfire-operations' ),
			'url'         => admin_url( 'admin.php?page=wc-orders' ),
			'capability'  => 'edit_shop_orders',
		),
		array(
			'label'       => __( 'Products & Inventory', 'foxfire-operations' ),
			'description' => __( 'Manage products, variations, prices, stock, batch details, and COAs.', 'foxfire-operations' ),
			'url'         => admin_url( 'edit.php?post_type=product' ),
			'capability'  => 'edit_products',
		),
		array(
			'label'       => __( 'Coupons', 'foxfire-operations' ),
			'description' => __( 'Create and manage approved store promotions and usage restrictions.', 'foxfire-operations' ),
			'url'         => admin_url( 'edit.php?post_type=shop_coupon' ),
			'capability'  => 'edit_shop_coupons',
		),
		array(
			'label'       => __( 'Customers', 'foxfire-operations' ),
			'description' => __( 'Find customer records and review their commerce history.', 'foxfire-operations' ),
			'url'         => admin_url( 'admin.php?page=wc-admin&path=/customers' ),
			'capability'  => 'manage_woocommerce',
		),
		array(
			'label'       => __( 'Analytics', 'foxfire-operations' ),
			'description' => __( 'Review store performance and operational reports.', 'foxfire-operations' ),
			'url'         => admin_url( 'admin.php?page=wc-admin&path=/analytics/overview' ),
			'capability'  => 'view_woocommerce_reports',
		),
		array(
			'label'       => __( 'Site Content', 'foxfire-operations' ),
			'description' => __( 'Edit approved pages and policy content without accessing theme code.', 'foxfire-operations' ),
			'url'         => admin_url( 'edit.php?post_type=page' ),
			'capability'  => 'edit_pages',
		),
	);

	if ( current_user_can( FOXFIRE_OPERATIONS_SENSITIVE_CAPABILITY ) ) {
		$links[] = array(
			'label'       => __( 'Shipping Settings', 'foxfire-operations' ),
			'description' => __( 'Administrator-only zones, methods, rates, restrictions, and thresholds.', 'foxfire-operations' ),
			'url'         => admin_url( 'admin.php?page=wc-settings&tab=shipping' ),
			'capability'  => FOXFIRE_OPERATIONS_SENSITIVE_CAPABILITY,
		);
		$links[] = array(
			'label'       => __( 'Payment Settings', 'foxfire-operations' ),
			'description' => __( 'Administrator-only gateways, credentials, and manual-payment configuration.', 'foxfire-operations' ),
			'url'         => admin_url( 'admin.php?page=wc-settings&tab=checkout' ),
			'capability'  => FOXFIRE_OPERATIONS_SENSITIVE_CAPABILITY,
		);
	}

	return array_values(
		array_filter(
			$links,
			static fn( array $link ): bool => current_user_can( $link['capability'] )
		)
	);
}

/**
 * Build non-sensitive environment checks for the operations page.
 *
 * @return array<int, array{label:string, status:string, detail:string}>
 */
function foxfire_operations_get_security_checks(): array {
	$is_local       = in_array( wp_get_environment_type(), array( 'local', 'development' ), true );
	$is_https_ready = is_ssl() || $is_local;
	$debug_safe     = ! defined( 'WP_DEBUG' ) || ! WP_DEBUG || $is_local;

	return array(
		array(
			'label'  => __( 'Encrypted admin traffic', 'foxfire-operations' ),
			'status' => $is_https_ready ? 'pass' : 'fail',
			'detail' => $is_https_ready
				? __( 'HTTPS is active, or this is an approved local environment.', 'foxfire-operations' )
				: __( 'HTTPS is required before production administration.', 'foxfire-operations' ),
		),
		array(
			'label'  => __( 'Dashboard file editor', 'foxfire-operations' ),
			'status' => defined( 'DISALLOW_FILE_EDIT' ) && DISALLOW_FILE_EDIT ? 'pass' : 'fail',
			'detail' => defined( 'DISALLOW_FILE_EDIT' ) && DISALLOW_FILE_EDIT
				? __( 'Theme and plugin source editing is disabled in wp-admin.', 'foxfire-operations' )
				: __( 'Disable the built-in theme and plugin source editor.', 'foxfire-operations' ),
		),
		array(
			'label'  => __( 'Production debug display', 'foxfire-operations' ),
			'status' => $debug_safe ? 'pass' : 'fail',
			'detail' => $debug_safe
				? __( 'Debug mode is off, or this is an approved local environment.', 'foxfire-operations' )
				: __( 'Disable WP_DEBUG on production to avoid information disclosure.', 'foxfire-operations' ),
		),
	);
}

/**
 * Render the read-only operations hub.
 */
function foxfire_operations_render_admin_page(): void {
	if ( ! current_user_can( FOXFIRE_OPERATIONS_CAPABILITY ) ) {
		wp_die(
			esc_html__( 'You do not have permission to access Foxfire Operations.', 'foxfire-operations' ),
			esc_html__( 'Access denied', 'foxfire-operations' ),
			array( 'response' => 403 )
		);
	}

	$links           = foxfire_operations_get_admin_links();
	$security_checks = foxfire_operations_get_security_checks();
	$is_admin        = current_user_can( FOXFIRE_OPERATIONS_SENSITIVE_CAPABILITY );
	?>
	<div class="wrap ff-ops-wrap">
		<h1><?php esc_html_e( 'Foxfire Operations', 'foxfire-operations' ); ?></h1>
		<p class="ff-ops-intro">
			<?php esc_html_e( 'Use this page as the starting point for day-to-day store work. Access is determined by your assigned role.', 'foxfire-operations' ); ?>
		</p>

		<div class="notice notice-info inline">
			<p>
				<strong><?php echo esc_html( $is_admin ? __( 'Administrator access', 'foxfire-operations' ) : __( 'Shop Manager access', 'foxfire-operations' ) ); ?></strong>
				&mdash;
				<?php echo esc_html( $is_admin ? __( 'Sensitive shipping and payment settings are available.', 'foxfire-operations' ) : __( 'Sensitive settings, credentials, plugins, themes, and user roles are restricted.', 'foxfire-operations' ) ); ?>
			</p>
		</div>

		<h2><?php esc_html_e( 'Store operations', 'foxfire-operations' ); ?></h2>
		<table class="widefat striped ff-ops-table">
			<thead>
				<tr>
					<th scope="col"><?php esc_html_e( 'Area', 'foxfire-operations' ); ?></th>
					<th scope="col"><?php esc_html_e( 'What you can manage', 'foxfire-operations' ); ?></th>
					<th scope="col"><?php esc_html_e( 'Open', 'foxfire-operations' ); ?></th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ( $links as $link ) : ?>
					<tr>
						<th scope="row"><?php echo esc_html( $link['label'] ); ?></th>
						<td><?php echo esc_html( $link['description'] ); ?></td>
						<td><a class="button" href="<?php echo esc_url( $link['url'] ); ?>"><?php esc_html_e( 'Open', 'foxfire-operations' ); ?></a></td>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>

		<h2><?php esc_html_e( 'Security baseline', 'foxfire-operations' ); ?></h2>
		<table class="widefat striped ff-ops-table">
			<tbody>
				<?php foreach ( $security_checks as $check ) : ?>
					<tr>
						<th scope="row"><?php echo esc_html( $check['label'] ); ?></th>
						<td><span class="ff-ops-status ff-ops-status--<?php echo esc_attr( $check['status'] ); ?>"><?php echo esc_html( 'pass' === $check['status'] ? __( 'Pass', 'foxfire-operations' ) : __( 'Action required', 'foxfire-operations' ) ); ?></span></td>
						<td><?php echo esc_html( $check['detail'] ); ?></td>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
	</div>
	<?php
}
