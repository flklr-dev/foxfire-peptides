<?php
/** Customer-owned order documents, independent of the active theme. */
defined( 'ABSPATH' ) || exit;

add_filter( 'woocommerce_get_query_vars', static function ( array $vars ): array {
	$vars['batch-coa'] = 'batch-coa';
	return $vars;
} );

/** Install the new endpoint once; never flush on every request. */
add_action( 'init', static function (): void {
	if ( class_exists( 'WooCommerce' ) && '1' !== get_option( 'foxfire_customer_documents_endpoint_version' ) ) {
		flush_rewrite_rules( false );
		update_option( 'foxfire_customer_documents_endpoint_version', '1', false );
	}
}, 99 );

/** Reject directory placeholders and unsafe URL schemes. Reports are public assets. */
function foxfire_operations_document_url( string $url ): string {
	$url = esc_url_raw( trim( $url ), array( 'https', 'http' ) );
	if ( ! $url || untrailingslashit( (string) wp_parse_url( $url, PHP_URL_PATH ) ) === untrailingslashit( (string) wp_parse_url( home_url( '/testing-coa/' ), PHP_URL_PATH ) ) ) {
		return '';
	}
	return $url;
}

/** Read the same product fields as the storefront, using the parent for strengths. */
function foxfire_operations_product_document( int $product_id ): array {
	$product = wc_get_product( $product_id );
	if ( ! $product ) {
		return array( 'batch' => '', 'url' => '' );
	}
	$product_id = $product->get_parent_id() ?: $product_id;
	$file = function_exists( 'get_field' ) ? get_field( 'foxfire_coa_file', $product_id ) : get_post_meta( $product_id, 'foxfire_coa_file', true );
	if ( is_numeric( $file ) ) {
		$file = wp_get_attachment_url( (int) $file );
	} elseif ( is_array( $file ) ) {
		$file = $file['url'] ?? '';
	}
	return array(
		'batch' => sanitize_text_field( (string) get_post_meta( $product_id, 'foxfire_batch_lot', true ) ),
		'url' => foxfire_operations_document_url( is_string( $file ) && $file ? $file : (string) get_post_meta( $product_id, 'foxfire_coa_url', true ) ),
	);
}

/** Preserve the purchase-time batch/document rather than relabeling historic orders. */
function foxfire_operations_snapshot_order_document( $item, $cart_item_key, $values, $order ): void {
	$document = foxfire_operations_product_document( (int) ( ( $values['variation_id'] ?? 0 ) ?: ( $values['product_id'] ?? 0 ) ) );
	$item->add_meta_data( '_foxfire_document_snapshot', '1', true );
	if ( $document['batch'] ) {
		$item->add_meta_data( 'Batch / Lot', $document['batch'], true );
	}
	if ( $document['url'] ) {
		$item->add_meta_data( 'COA Report URL', $document['url'], true );
	}
}
add_action( 'woocommerce_checkout_create_order_line_item', 'foxfire_operations_snapshot_order_document', 20, 4 );

/** Saved reports remain stable; missing reports can resolve from the same current batch. */
function foxfire_operations_order_item_document( $item ): array {
	$saved_batch = sanitize_text_field( (string) $item->get_meta( 'Batch / Lot', true ) );
	$saved_url = foxfire_operations_document_url( (string) $item->get_meta( 'COA Report URL', true ) );
	$current = foxfire_operations_product_document( (int) ( $item->get_variation_id() ?: $item->get_product_id() ) );
	$recorded = '1' === $item->get_meta( '_foxfire_document_snapshot', true ) || $saved_batch || $saved_url;
	if ( $recorded ) {
		return array( 'batch' => $saved_batch, 'url' => $saved_url ?: ( $saved_batch && $saved_batch === $current['batch'] ? $current['url'] : '' ), 'recorded' => true );
	}
	// Legacy orders have no evidence of their shipped batch: clearly label current references.
	return $current + array( 'recorded' => false );
}

/** Explicit ownership guard, including direct calls from another integration. */
function foxfire_operations_customer_owns_order( $order ): bool {
	return $order instanceof WC_Order && get_current_user_id() > 0 && (int) $order->get_customer_id() === get_current_user_id();
}

function foxfire_operations_render_order_documents( $order ): void {
	if ( ! foxfire_operations_customer_owns_order( $order ) ) {
		return;
	}
	?>
	<section class="ff-customer-documents ff-view-order-unified-card" aria-label="<?php esc_attr_e( 'Batch / COA documents', 'foxfire-operations' ); ?>">
		<div class="ff-view-order-items-section">
			<h2 class="ff-view-order-section-title"><?php esc_html_e( 'Batch / COA Documents', 'foxfire-operations' ); ?></h2>
			<?php foreach ( $order->get_items() as $item ) : $document = foxfire_operations_order_item_document( $item ); ?>
				<div class="ff-customer-document-row">
					<strong><?php echo esc_html( $item->get_name() ); ?></strong>
					<p><?php echo esc_html( $document['recorded'] ? __( 'Order batch:', 'foxfire-operations' ) : __( 'Current product batch (not a recorded shipment batch):', 'foxfire-operations' ) ); ?> <?php echo esc_html( $document['batch'] ?: __( 'Not provided', 'foxfire-operations' ) ); ?></p>
					<?php if ( $document['url'] ) : ?>
						<a class="ff-customer-document-link" href="<?php echo esc_url( $document['url'] ); ?>" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'View COA Report', 'foxfire-operations' ); ?></a>
					<?php else : ?>
						<p><?php esc_html_e( 'A report is not available for this batch yet.', 'foxfire-operations' ); ?></p>
					<?php endif; ?>
				</div>
			<?php endforeach; ?>
		</div>
	</section>
	<?php
}
add_action( 'woocommerce_order_details_after_order_table', 'foxfire_operations_render_order_documents', 25 );

/** Paginated owned orders only; no email-based lookup or other customer data. */
function foxfire_operations_render_customer_documents(): void {
	if ( ! is_user_logged_in() ) {
		return;
	}
	$page = max( 1, absint( $_GET['coa-page'] ?? 1 ) );
	$result = wc_get_orders( array( 'customer_id' => get_current_user_id(), 'limit' => 10, 'page' => $page, 'paginate' => true, 'orderby' => 'date', 'order' => 'DESC', 'status' => array_keys( wc_get_order_statuses() ) ) );
	echo '<div class="ff-section-title-wrap"><h1 class="ff-section-title">' . esc_html__( 'Batch / COA Lookup', 'foxfire-operations' ) . '</h1>';
	echo '<p class="ff-section-subtext">' . esc_html__( 'Batch-specific documents are displayed when available. Always match the batch number shown on your product label with the corresponding COA before reviewing documentation.', 'foxfire-operations' ) . '</p></div>';
	foreach ( $result->orders as $order ) {
		echo '<h2 class="ff-view-order-section-title"><a href="' . esc_url( $order->get_view_order_url() ) . '">' . esc_html( sprintf( __( 'Order #%s', 'foxfire-operations' ), $order->get_order_number() ) ) . '</a></h2>';
		foxfire_operations_render_order_documents( $order );
	}
	if ( ! $result->orders ) {
		echo '<p>' . esc_html__( 'No order documents to display yet.', 'foxfire-operations' ) . '</p>';
	}
	if ( $page > 1 ) {
		echo '<a class="ff-view-order-back-btn" href="' . esc_url( add_query_arg( 'coa-page', $page - 1, wc_get_account_endpoint_url( 'batch-coa' ) ) ) . '">' . esc_html__( 'Previous', 'foxfire-operations' ) . '</a> ';
	}
	if ( $page < $result->max_num_pages ) {
		echo '<a class="ff-view-order-back-btn" href="' . esc_url( add_query_arg( 'coa-page', $page + 1, wc_get_account_endpoint_url( 'batch-coa' ) ) ) . '">' . esc_html__( 'Next', 'foxfire-operations' ) . '</a>';
	}
}
add_action( 'woocommerce_account_batch-coa_endpoint', 'foxfire_operations_render_customer_documents' );

/** Allow document corrections on fulfilled orders without unlocking prices or inventory. */
add_action( 'add_meta_boxes', static function (): void {
	if ( ! current_user_can( 'edit_shop_orders' ) ) { return; }
	$screens = array( 'shop_order' );
	if ( function_exists( 'wc_get_page_screen_id' ) ) { $screens[] = wc_get_page_screen_id( 'shop-order' ); }
	foreach ( array_unique( $screens ) as $screen ) {
		add_meta_box( 'foxfire-order-documents', __( 'Foxfire Batch / COA Documents', 'foxfire-operations' ), 'foxfire_operations_render_order_documents_admin', $screen, 'normal', 'default' );
	}
}, 20 );

function foxfire_operations_render_order_documents_admin( $object ): void {
	$order = foxfire_operations_resolve_order( $object );
	if ( ! $order || ! current_user_can( 'edit_shop_orders' ) ) { return; }
	wp_nonce_field( 'foxfire_save_order_documents_' . $order->get_id(), 'foxfire_order_documents_nonce' );
	echo '<p>' . esc_html__( 'Upload the report in Media and copy its File URL, then associate it with the product below. Use only the batch actually supplied. Update the order to save. These fields remain editable for shipped/completed orders without changing quantities, prices or order status. Current product reports are managed in Products → Edit → Foxfire Product Operations → Batch & COA. Keep old files available for historical orders.', 'foxfire-operations' ) . '</p>';
	foreach ( $order->get_items() as $id => $item ) {
		?>
		<fieldset style="margin:16px 0;padding:12px;border:1px solid #ddd">
			<legend><strong><?php echo esc_html( $item->get_name() ); ?></strong></legend>
			<p><label for="ff-order-batch-<?php echo (int) $id; ?>"><?php esc_html_e( 'Batch / Lot', 'foxfire-operations' ); ?></label><br><input class="widefat" id="ff-order-batch-<?php echo (int) $id; ?>" name="foxfire_order_documents[<?php echo (int) $id; ?>][batch]" maxlength="80" value="<?php echo esc_attr( $item->get_meta( 'Batch / Lot', true ) ); ?>"></p>
			<p><label for="ff-order-report-<?php echo (int) $id; ?>"><?php esc_html_e( 'COA Report URL', 'foxfire-operations' ); ?></label><br><input class="widefat" type="url" id="ff-order-report-<?php echo (int) $id; ?>" name="foxfire_order_documents[<?php echo (int) $id; ?>][url]" maxlength="2048" value="<?php echo esc_attr( $item->get_meta( 'COA Report URL', true ) ); ?>"></p>
		</fieldset>
		<?php
	}
}

/** Validate all submitted rows before saving any; customer data cannot select another item. */
function foxfire_operations_save_order_documents( $order_id, $object ): void {
	if ( ! current_user_can( 'edit_shop_orders' ) ) { return; }
	$nonce = isset( $_POST['foxfire_order_documents_nonce'] ) && is_string( $_POST['foxfire_order_documents_nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['foxfire_order_documents_nonce'] ) ) : '';
	if ( ! wp_verify_nonce( $nonce, 'foxfire_save_order_documents_' . $order_id ) || ! isset( $_POST['foxfire_order_documents'] ) ) { return; }
	$order = wc_get_order( $order_id );
	$rows = wp_unslash( $_POST['foxfire_order_documents'] );
	if ( ! $order || ! is_array( $rows ) ) { return; }
	$items = $order->get_items(); $updates = array();
	foreach ( $rows as $id => $row ) {
		if ( ! isset( $items[$id] ) || ! is_array( $row ) || ! isset( $row['batch'], $row['url'] ) || ! is_string( $row['batch'] ) || ! is_string( $row['url'] ) ) { return; }
		$url = foxfire_operations_document_url( $row['url'] );
		if ( strlen( $row['batch'] ) > 80 || strlen( $row['url'] ) > 2048 || ( trim( $row['url'] ) && ! $url ) ) {
			if ( class_exists( 'WC_Admin_Meta_Boxes' ) ) { WC_Admin_Meta_Boxes::add_error( __( 'Batch / COA documents were not saved. Use a batch up to 80 characters and a direct HTTP/HTTPS report URL, not the testing directory.', 'foxfire-operations' ) ); }
			return;
		}
		$updates[$id] = array( 'batch' => sanitize_text_field( $row['batch'] ), 'url' => $url );
	}
	foreach ( $updates as $id => $row ) {
		$items[$id]->update_meta_data( '_foxfire_document_snapshot', '1' );
		$items[$id]->update_meta_data( 'Batch / Lot', $row['batch'] );
		$items[$id]->update_meta_data( 'COA Report URL', $row['url'] );
		$items[$id]->save();
	}
}
add_action( 'woocommerce_process_shop_order_meta', 'foxfire_operations_save_order_documents', 44, 2 );
