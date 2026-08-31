<?php
/**
 * My Account page layout — Chunk 1L.
 *
 * @package Foxfire_Child
 */

defined( 'ABSPATH' ) || exit;
?>

<div class="ff-account-page">
	<div class="ff-account-layout">
		
		<!-- Sidebar Navigation -->
		<aside class="ff-account-sidebar">
			<?php do_action( 'woocommerce_account_navigation' ); ?>
		</aside>

		<!-- Main Content Area -->
		<main class="ff-account-content">
			<?php
			/**
			 * My Account content.
			 *
			 * @since 2.6.0
			 */
			do_action( 'woocommerce_account_content' );
			?>
		</main>

	</div>
</div>
