<?php
/**
 * My Account Dashboard — Chunk 1L.
 *
 * @package Foxfire_Child
 */

defined( 'ABSPATH' ) || exit;

$current_user = wp_get_current_user();
$user_id      = $current_user->ID;
$first_name   = ! empty( $current_user->first_name ) ? $current_user->first_name : $current_user->display_name;
$latest_order = function_exists( 'foxfire_get_customer_latest_order' ) ? foxfire_get_customer_latest_order( $user_id ) : null;
$shipping_addr = get_user_meta( $user_id, 'shipping_address_1', true );
$shipping_city = get_user_meta( $user_id, 'shipping_city', true );
$shipping_state = get_user_meta( $user_id, 'shipping_state', true );
?>

<div class="ff-account-dashboard">

	<!-- Welcome Header Card -->
	<div class="ff-dashboard-welcome-card">
		<div class="ff-dashboard-welcome-header">
			<div>
				<span class="ff-dashboard-eyebrow"><?php esc_html_e( 'Customer Portal', 'foxfire-child' ); ?></span>
				<h1 class="ff-dashboard-title">
					<?php
					printf(
						/* translators: %s: customer first name */
						esc_html__( 'Welcome back, %s!', 'foxfire-child' ),
						esc_html( $first_name )
					);
					?>
				</h1>
				<p class="ff-dashboard-desc">
					<?php esc_html_e( 'Manage your research orders, update shipping addresses, and lookup batch purity certificates from one central dashboard.', 'foxfire-child' ); ?>
				</p>
			</div>
		</div>
	</div>

	<!-- Dashboard Action Cards Grid -->
	<div class="ff-dashboard-grid">

		<!-- Card 1: Latest Order Snapshot -->
		<div class="ff-dashboard-card">
			<div class="ff-dashboard-card__header">
				<div class="ff-dashboard-card__icon" aria-hidden="true">
					<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m7.5 4.27 9 5.15"/><path d="M21 8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16Z"/><path d="m3.3 7 8.7 5 8.7-5"/><path d="M12 22V12"/></svg>
				</div>
				<h2 class="ff-dashboard-card__title"><?php esc_html_e( 'Recent Orders', 'foxfire-child' ); ?></h2>
			</div>
			<div class="ff-dashboard-card__body">
				<?php if ( $latest_order ) : ?>
					<div class="ff-dashboard-order-meta">
						<strong>#<?php echo esc_html( $latest_order->get_order_number() ); ?></strong>
						<span class="ff-order-badge ff-order-badge--<?php echo esc_attr( $latest_order->get_status() ); ?>">
							<?php echo esc_html( wc_get_order_status_name( $latest_order->get_status() ) ); ?>
						</span>
					</div>
					<p class="ff-dashboard-order-sub">
						<?php echo esc_html( wc_format_datetime( $latest_order->get_date_created() ) ); ?> &bull; 
						<strong><?php echo wp_kses_post( $latest_order->get_formatted_order_total() ); ?></strong>
					</p>
					<a href="<?php echo esc_url( $latest_order->get_view_order_url() ); ?>" class="ff-btn ff-btn--outline ff-btn--sm">
						<?php esc_html_e( 'View Order Details', 'foxfire-child' ); ?> &rarr;
					</a>
				<?php else : ?>
					<p class="ff-dashboard-card__empty"><?php esc_html_e( 'You have not placed any research orders yet.', 'foxfire-child' ); ?></p>
					<a href="<?php echo esc_url( function_exists( 'foxfire_get_shop_url' ) ? foxfire_get_shop_url() : wc_get_page_permalink( 'shop' ) ); ?>" class="ff-btn ff-btn--primary ff-btn--sm">
						<?php esc_html_e( 'Explore Compounds', 'foxfire-child' ); ?> &rarr;
					</a>
				<?php endif; ?>
			</div>
			<div class="ff-dashboard-card__footer">
				<a href="<?php echo esc_url( wc_get_account_endpoint_url( 'orders' ) ); ?>" class="ff-card-footer-link">
					<?php esc_html_e( 'View All Orders', 'foxfire-child' ); ?> &rarr;
				</a>
			</div>
		</div>

		<!-- Card 2: Saved Shipping Address -->
		<div class="ff-dashboard-card">
			<div class="ff-dashboard-card__header">
				<div class="ff-dashboard-card__icon" aria-hidden="true">
					<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 10c0 4.993-5.539 10.193-7.399 11.799a1 1 0 0 1-1.202 0C9.539 20.193 4 14.993 4 10a8 8 0 0 1 16 0"/><circle cx="12" cy="10" r="3"/></svg>
				</div>
				<h2 class="ff-dashboard-card__title"><?php esc_html_e( 'Shipping Address', 'foxfire-child' ); ?></h2>
			</div>
			<div class="ff-dashboard-card__body">
				<?php if ( ! empty( $shipping_addr ) ) : ?>
					<p class="ff-dashboard-address-preview">
						<?php echo esc_html( $shipping_addr ); ?><br/>
						<?php echo esc_html( $shipping_city . ( $shipping_state ? ', ' . $shipping_state : '' ) ); ?>
					</p>
					<a href="<?php echo esc_url( wc_get_endpoint_url( 'edit-address', 'shipping', wc_get_page_permalink( 'myaccount' ) ) ); ?>" class="ff-btn ff-btn--outline ff-btn--sm">
						<?php esc_html_e( 'Edit Shipping Address', 'foxfire-child' ); ?>
					</a>
				<?php else : ?>
					<p class="ff-dashboard-card__empty"><?php esc_html_e( 'No default shipping address on file.', 'foxfire-child' ); ?></p>
					<a href="<?php echo esc_url( wc_get_endpoint_url( 'edit-address', 'shipping', wc_get_page_permalink( 'myaccount' ) ) ); ?>" class="ff-btn ff-btn--outline ff-btn--sm">
						<?php esc_html_e( 'Add Address', 'foxfire-child' ); ?>
					</a>
				<?php endif; ?>
			</div>
			<div class="ff-dashboard-card__footer">
				<a href="<?php echo esc_url( wc_get_account_endpoint_url( 'edit-address' ) ); ?>" class="ff-card-footer-link">
					<?php esc_html_e( 'Manage Address Book', 'foxfire-child' ); ?> &rarr;
				</a>
			</div>
		</div>

		<!-- Card 3: Batch Purity & COA Verification -->
		<div class="ff-dashboard-card">
			<div class="ff-dashboard-card__header">
				<div class="ff-dashboard-card__icon ff-dashboard-card__icon--orange" aria-hidden="true">
					<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2v4a2 2 0 0 0 2 2h4"/><path d="M4 22h16a2 2 0 0 0 2-2V7.5L14.5 2H6a2 2 0 0 0-2 2v4"/><path d="m3 15 2 2 4-4"/></svg>
				</div>
				<h2 class="ff-dashboard-card__title"><?php esc_html_e( 'Testing & Batch COAs', 'foxfire-child' ); ?></h2>
			</div>
			<div class="ff-dashboard-card__body">
				<p class="ff-dashboard-card__desc">
					<?php esc_html_e( 'Match batch lot numbers from your past orders with HPLC purity & Mass Spectrometry lab reports.', 'foxfire-child' ); ?>
				</p>
				<a href="<?php echo esc_url( home_url( '/testing-coa/' ) ); ?>" class="ff-btn ff-btn--primary ff-btn--sm">
					<?php esc_html_e( 'Lookup Batch COAs', 'foxfire-child' ); ?> &rarr;
				</a>
			</div>
			<div class="ff-dashboard-card__footer">
				<a href="<?php echo esc_url( home_url( '/testing-coa/' ) ); ?>" class="ff-card-footer-link">
					<?php esc_html_e( 'View Testing Standard', 'foxfire-child' ); ?> &rarr;
				</a>
			</div>
		</div>

	</div>

	<?php
	/**
	 * My Account dashboard.
	 *
	 * @since 2.6.0
	 */
	do_action( 'woocommerce_account_dashboard' );
	?>

</div>
