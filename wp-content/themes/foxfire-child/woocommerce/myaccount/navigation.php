<?php
/**
 * My Account navigation — Chunk 1L.
 *
 * @package Foxfire_Child
 */

defined( 'ABSPATH' ) || exit;

$current_user = wp_get_current_user();
$user_id      = $current_user->ID;
$initials     = function_exists( 'foxfire_get_user_initials' ) ? foxfire_get_user_initials( $user_id ) : 'F';
$display_name = ! empty( $current_user->display_name ) ? $current_user->display_name : $current_user->user_login;

do_action( 'woocommerce_before_account_navigation' );
?>

<nav class="ff-account-nav" aria-label="<?php esc_attr_e( 'Account Navigation', 'foxfire-child' ); ?>">
	
	<!-- Single Unified Sidebar Container -->
	<div class="ff-account-sidebar-box">
		<!-- Customer Identity Header -->
		<div class="ff-account-user-card">
			<div class="ff-account-avatar">
				<span><?php echo esc_html( $initials ); ?></span>
			</div>
			<div class="ff-account-user-info">
				<strong class="ff-account-user-name"><?php echo esc_html( $display_name ); ?></strong>
				<span class="ff-account-user-email"><?php echo esc_html( $current_user->user_email ); ?></span>
			</div>
		</div>

		<!-- Navigation Menu Items -->
		<ul class="ff-account-menu">
			<?php
			foreach ( wc_get_account_menu_items() as $endpoint => $label ) :
				$url       = ( 'batch-coa' === $endpoint ) ? home_url( '/testing-coa/' ) : wc_get_account_endpoint_url( $endpoint );
				$classes   = wc_get_account_menu_item_classes( $endpoint );
				$is_active = false !== strpos( $classes, 'is-active' );
				?>
				<li class="ff-account-menu-item ff-account-menu-item--<?php echo esc_attr( $endpoint ); ?> <?php echo $is_active ? 'is-active' : ''; ?>">
					<a href="<?php echo esc_url( $url ); ?>" class="ff-account-menu-link">
						<span class="ff-account-menu-icon" aria-hidden="true">
							<?php if ( 'orders' === $endpoint ) : ?>
								<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m7.5 4.27 9 5.15"/><path d="M21 8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16Z"/><path d="m3.3 7 8.7 5 8.7-5"/><path d="M12 22V12"/></svg>
							<?php elseif ( 'edit-address' === $endpoint ) : ?>
								<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 10c0 4.993-5.539 10.193-7.399 11.799a1 1 0 0 1-1.202 0C9.539 20.193 4 14.993 4 10a8 8 0 0 1 16 0"/><circle cx="12" cy="10" r="3"/></svg>
							<?php elseif ( 'edit-account' === $endpoint ) : ?>
								<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
							<?php elseif ( 'batch-coa' === $endpoint ) : ?>
								<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2v4a2 2 0 0 0 2 2h4"/><path d="M4 22h16a2 2 0 0 0 2-2V7.5L14.5 2H6a2 2 0 0 0-2 2v4"/><path d="m3 15 2 2 4-4"/></svg>
							<?php elseif ( 'customer-logout' === $endpoint ) : ?>
								<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" x2="9" y1="12" y2="12"/></svg>
							<?php else : ?>
								<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
							<?php endif; ?>
						</span>
						<span class="ff-account-menu-text"><?php echo esc_html( $label ); ?></span>
					</a>
				</li>
			<?php endforeach; ?>
		</ul>
	</div>

</nav>

<?php do_action( 'woocommerce_after_account_navigation' ); ?>
