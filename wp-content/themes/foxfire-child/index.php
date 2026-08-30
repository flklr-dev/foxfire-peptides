<?php
/**
 * Main template file override for Foxfire Child Theme.
 *
 * @package Foxfire_Child
 */

defined( 'ABSPATH' ) || exit;

if ( is_front_page() || is_home() ) {
	require_once FOXFIRE_CHILD_DIR . '/front-page.php';
} else {
	get_header();
	?>
	<div id="primary" class="content-area">
		<main id="main" class="site-main" role="main">
			<?php
			if ( have_posts() ) :
				while ( have_posts() ) :
					the_post();
					the_content();
				endwhile;
			endif;
			?>
		</main>
	</div>
	<?php
	get_footer();
}
