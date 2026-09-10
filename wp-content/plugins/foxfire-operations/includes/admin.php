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
	$is_content_page   = str_ends_with( $hook_suffix, '_page_foxfire-site-content' );
	$is_home_products  = str_ends_with( $hook_suffix, '_page_foxfire-homepage-products' );
	$is_store_config   = str_ends_with( $hook_suffix, '_page_foxfire-store-configuration' );
	$is_product_screen = $screen && 'product' === $screen->post_type && in_array( $screen->base, array( 'post', 'edit' ), true );
	$is_coupon_screen  = $screen && 'shop_coupon' === $screen->post_type && in_array( $screen->base, array( 'post', 'edit' ), true );
	$is_order_screen   = $screen && ( 'shop_order' === $screen->post_type || ( function_exists( 'wc_get_page_screen_id' ) && wc_get_page_screen_id( 'shop-order' ) === $screen->id ) );

	if ( ! $is_operations_hub && ! $is_order_queue && ! $is_content_page && ! $is_home_products && ! $is_store_config && ! $is_product_screen && ! $is_coupon_screen && ! $is_order_screen ) {
		return;
	}

	$asset_path = FOXFIRE_OPERATIONS_DIR . 'assets/admin.css';
	wp_enqueue_style(
		'foxfire-operations-admin',
		FOXFIRE_OPERATIONS_URL . 'assets/admin.css',
		array(),
		file_exists( $asset_path ) ? (string) filemtime( $asset_path ) : FOXFIRE_OPERATIONS_VERSION
	);

	if ( $is_home_products ) {
		$homepage_script_path = FOXFIRE_OPERATIONS_DIR . 'assets/homepage-products.js';
		wp_enqueue_script(
			'foxfire-operations-homepage-products',
			FOXFIRE_OPERATIONS_URL . 'assets/homepage-products.js',
			array( 'jquery', 'jquery-ui-sortable' ),
			file_exists( $homepage_script_path ) ? (string) filemtime( $homepage_script_path ) : FOXFIRE_OPERATIONS_VERSION,
			true
		);
	}

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
			'label'       => __( 'Homepage Products', 'foxfire-operations' ),
			'description' => __( 'Choose and order the products promoted on the homepage without changing the page design.', 'foxfire-operations' ),
			'url'         => admin_url( 'admin.php?page=foxfire-homepage-products' ),
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
			'description' => __( 'Update approved homepage, About, Contact, FAQ, footer, and policy content without accessing theme code.', 'foxfire-operations' ),
			'url'         => admin_url( 'admin.php?page=foxfire-site-content' ),
			'capability'  => FOXFIRE_OPERATIONS_CAPABILITY,
		),
	);

	if ( current_user_can( FOXFIRE_OPERATIONS_SENSITIVE_CAPABILITY ) ) {
		$links[] = array(
			'label'       => __( 'Store Configuration', 'foxfire-operations' ),
			'description' => __( 'Review saved shipping zones, rates, payment methods, and manual-payment readiness before changing WooCommerce settings.', 'foxfire-operations' ),
			'url'         => admin_url( 'admin.php?page=foxfire-store-configuration' ),
			'capability'  => FOXFIRE_OPERATIONS_SENSITIVE_CAPABILITY,
		);
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
	$auth_hardened  = false !== has_filter( 'authenticate', 'foxfire_operations_throttle_login' )
		&& false !== has_filter( 'authenticate', 'foxfire_operations_normalize_login_error' )
		&& false !== has_action( 'wp_loaded', 'foxfire_operations_process_customer_lost_password' )
		&& false !== has_filter( 'auth_cookie_expiration', 'foxfire_operations_privileged_auth_cookie_expiration' )
		&& HOUR_IN_SECONDS === (int) apply_filters( 'password_reset_expiration', DAY_IN_SECONDS );
	$manager_role   = get_role( 'shop_manager' );
	$admin_role     = get_role( 'administrator' );
	$manager_drift  = array();

	if ( $manager_role instanceof WP_Role ) {
		foreach ( foxfire_operations_forbidden_manager_capabilities() as $capability ) {
			if ( $manager_role->has_cap( $capability ) ) {
				$manager_drift[] = $capability;
			}
		}
	}

	$manager_safe = $manager_role instanceof WP_Role
		&& $manager_role->has_cap( FOXFIRE_OPERATIONS_CAPABILITY )
		&& ! $manager_role->has_cap( FOXFIRE_OPERATIONS_SENSITIVE_CAPABILITY )
		&& empty( $manager_drift );
	$admin_safe = $admin_role instanceof WP_Role
		&& $admin_role->has_cap( FOXFIRE_OPERATIONS_CAPABILITY )
		&& $admin_role->has_cap( FOXFIRE_OPERATIONS_SENSITIVE_CAPABILITY );

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
			'label'  => __( 'Shop Manager least privilege', 'foxfire-operations' ),
			'status' => $manager_safe ? 'pass' : 'fail',
			'detail' => $manager_safe
				? __( 'Routine operators have Foxfire commerce access without global settings, users, plugins, themes, posts, or unrestricted page editing.', 'foxfire-operations' )
				: __( 'Repair the Shop Manager role before assigning it to staff.', 'foxfire-operations' ),
		),
		array(
			'label'  => __( 'Administrator control boundary', 'foxfire-operations' ),
			'status' => $admin_safe ? 'pass' : 'fail',
			'detail' => $admin_safe
				? __( 'Sensitive shipping and payment configuration remains Administrator-only.', 'foxfire-operations' )
				: __( 'Repair the Administrator Foxfire capabilities before changing store-wide configuration.', 'foxfire-operations' ),
		),
		array(
			'label'  => __( 'Customer authentication abuse protection', 'foxfire-operations' ),
			'status' => $auth_hardened ? 'pass' : 'fail',
			'detail' => $auth_hardened
				? __( 'Login and password-reset requests use neutral responses, application throttling, one-hour reset links, and shorter privileged sessions.', 'foxfire-operations' )
				: __( 'Restore the Foxfire customer authentication protections before accepting public traffic.', 'foxfire-operations' ),
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
		<table class="widefat striped ff-ops-table ff-ops-links-table">
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
		<table class="widefat striped ff-ops-table ff-ops-security-table">
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
