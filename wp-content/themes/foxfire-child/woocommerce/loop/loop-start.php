<?php
/**
 * Product Loop Start — Foxfire override.
 *
 * Adds data-ff-product-grid attribute for JS targeting.
 *
 * @package Foxfire_Child
 */

defined( 'ABSPATH' ) || exit;

$columns = absint( wc_get_loop_prop( 'columns', 3 ) );
?>
<ul class="products columns-<?php echo esc_attr( $columns ); ?>" data-ff-product-grid>
