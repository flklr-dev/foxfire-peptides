<?php
/**
 * Secure order, fulfillment, shipment tracking, and operational queue tools.
 *
 * @package Foxfire_Operations
 */

defined( 'ABSPATH' ) || exit;

/**
 * Register the customer-facing Shipped order status.
 *
 * @return void
 */
function foxfire_operations_register_shipped_status() {
	register_post_status(
		'wc-shipped',
		array(
			'label'                     => _x( 'Shipped', 'Order status', 'foxfire-operations' ),
			'public'                    => true,
			'exclude_from_search'       => false,
			'show_in_admin_all_list'    => true,
			'show_in_admin_status_list' => true,
			/* translators: %s: number of shipped orders. */
			'label_count'               => _n_noop( 'Shipped <span class="count">(%s)</span>', 'Shipped <span class="count">(%s)</span>', 'foxfire-operations' ),
		)
	);
}
add_action( 'init', 'foxfire_operations_register_shipped_status', 9 );

/**
 * Insert Shipped after Processing in WooCommerce status selectors.
 *
 * @param array<string, string> $statuses Existing statuses.
 * @return array<string, string>
 */
function foxfire_operations_add_shipped_status( $statuses ) {
	$updated = array();

	foreach ( $statuses as $key => $label ) {
		$updated[ $key ] = $label;
		if ( 'wc-processing' === $key ) {
			$updated['wc-shipped'] = _x( 'Shipped', 'Order status', 'foxfire-operations' );
		}
	}

	if ( ! isset( $updated['wc-shipped'] ) ) {
		$updated['wc-shipped'] = _x( 'Shipped', 'Order status', 'foxfire-operations' );
	}

	return $updated;
}
add_filter( 'wc_order_statuses', 'foxfire_operations_add_shipped_status' );

/**
 * Treat shipped orders as paid wherever WooCommerce checks paid statuses.
 *
 * @param string[] $statuses Paid status slugs without the wc- prefix.
 * @return string[]
 */
function foxfire_operations_add_shipped_to_paid_statuses( $statuses ) {
	$statuses[] = 'shipped';

	return array_values( array_unique( $statuses ) );
}
add_filter( 'woocommerce_order_is_paid_statuses', 'foxfire_operations_add_shipped_to_paid_statuses' );

/**
 * Register protected legacy post-meta definitions. WooCommerce CRUD remains the
 * storage API so the same implementation works when HPOS is enabled later.
 *
 * @return void
 */
function foxfire_operations_register_order_meta() {
	$definitions = array(
		'_foxfire_shipping_carrier' => 'sanitize_text_field',
		'_foxfire_tracking_number'  => 'sanitize_text_field',
		'_foxfire_tracking_url'     => 'esc_url_raw',
		'_foxfire_shipped_date_gmt' => 'sanitize_text_field',
		'_foxfire_needs_follow_up'  => 'sanitize_key',
	);

	foreach ( $definitions as $meta_key => $sanitize_callback ) {
		register_post_meta(
			'shop_order',
			$meta_key,
			array(
				'type'              => 'string',
				'single'            => true,
				'show_in_rest'      => false,
				'sanitize_callback' => $sanitize_callback,
				'auth_callback'     => static function () {
					return current_user_can( 'edit_shop_orders' );
				},
			)
		);
	}
}
add_action( 'init', 'foxfire_operations_register_order_meta', 10 );

/**
 * Return a safely truncated single-line operational value.
 *
 * @param mixed $value  Submitted value.
 * @param int   $length Maximum length.
 * @return string
 */
function foxfire_operations_sanitize_order_text( $value, $length ) {
	$value = sanitize_text_field( (string) $value );

	return function_exists( 'mb_substr' ) ? mb_substr( $value, 0, $length ) : substr( $value, 0, $length );
}

/**
 * Normalize a site-local datetime-local value to a GMT database timestamp.
 *
 * @param string $value Datetime-local value.
 * @return string Empty when invalid.
 */
function foxfire_operations_normalize_shipped_date( $value ) {
	$value = sanitize_text_field( $value );
	if ( '' === $value ) {
		return '';
	}

	$date   = DateTimeImmutable::createFromFormat( '!Y-m-d\TH:i', $value, wp_timezone() );
	$errors = DateTimeImmutable::getLastErrors();
	if ( ! $date || ( is_array( $errors ) && ( $errors['warning_count'] > 0 || $errors['error_count'] > 0 ) ) ) {
		return '';
	}

	return $date->setTimezone( new DateTimeZone( 'UTC' ) )->format( 'Y-m-d H:i:s' );
}

/**
 * Format a stored GMT shipment date for the admin datetime-local control.
 *
 * @param string $value GMT database timestamp.
 * @return string
 */
function foxfire_operations_format_shipped_date_input( $value ) {
	if ( '' === $value ) {
		return '';
	}

	return get_date_from_gmt( $value, 'Y-m-d\TH:i' );
}

/**
 * Resolve either order-screen object format to a WC_Order.
 *
 * @param WP_Post|WC_Order|mixed $object Screen object.
 * @return WC_Order|false
 */
function foxfire_operations_resolve_order( $object ) {
	if ( $object instanceof WC_Order ) {
		return $object;
	}

	return $object instanceof WP_Post ? wc_get_order( $object->ID ) : false;
}

/**
 * Add the shipment panel to both legacy and HPOS order screens.
 *
 * @return void
 */
function foxfire_operations_add_order_metabox() {
	if ( ! current_user_can( 'edit_shop_orders' ) ) {
		return;
	}

	$screens = array( 'shop_order' );
	if ( function_exists( 'wc_get_page_screen_id' ) ) {
		$screens[] = wc_get_page_screen_id( 'shop-order' );
	}

	foreach ( array_unique( $screens ) as $screen ) {
		add_meta_box(
			'foxfire-order-fulfillment',
			__( 'Foxfire Fulfillment', 'foxfire-operations' ),
			'foxfire_operations_render_order_metabox',
			$screen,
			'side',
			'high'
		);
	}
}
add_action( 'add_meta_boxes', 'foxfire_operations_add_order_metabox', 20 );

/**
 * Render secure order tracking fields.
 *
 * @param WP_Post|WC_Order $object Current order object.
 * @return void
 */
function foxfire_operations_render_order_metabox( $object ) {
	$order = foxfire_operations_resolve_order( $object );
	if ( ! $order || ! current_user_can( 'edit_shop_orders' ) ) {
		return;
	}

	$carrier        = $order->get_meta( '_foxfire_shipping_carrier', true );
	$tracking       = $order->get_meta( '_foxfire_tracking_number', true );
	$tracking_url   = $order->get_meta( '_foxfire_tracking_url', true );
	$shipped_date   = foxfire_operations_format_shipped_date_input( $order->get_meta( '_foxfire_shipped_date_gmt', true ) );
	$needs_followup = 'yes' === $order->get_meta( '_foxfire_needs_follow_up', true );

	wp_nonce_field( 'foxfire_save_order_fulfillment_' . $order->get_id(), 'foxfire_order_fulfillment_nonce' );
	?>
	<div class="foxfire-order-fields">
		<p>
			<label for="foxfire_shipping_carrier"><strong><?php esc_html_e( 'Carrier', 'foxfire-operations' ); ?></strong></label>
			<input type="text" class="widefat" id="foxfire_shipping_carrier" name="foxfire_shipping_carrier" maxlength="80" value="<?php echo esc_attr( $carrier ); ?>" autocomplete="off">
		</p>
		<p>
			<label for="foxfire_tracking_number"><strong><?php esc_html_e( 'Tracking number', 'foxfire-operations' ); ?></strong></label>
			<input type="text" class="widefat" id="foxfire_tracking_number" name="foxfire_tracking_number" maxlength="120" value="<?php echo esc_attr( $tracking ); ?>" autocomplete="off">
		</p>
		<p>
			<label for="foxfire_tracking_url"><strong><?php esc_html_e( 'Tracking URL', 'foxfire-operations' ); ?></strong></label>
			<input type="url" class="widefat" id="foxfire_tracking_url" name="foxfire_tracking_url" maxlength="2048" value="<?php echo esc_attr( $tracking_url ); ?>" placeholder="https://">
		</p>
		<p>
			<label for="foxfire_shipped_date"><strong><?php esc_html_e( 'Shipped date', 'foxfire-operations' ); ?></strong></label>
			<input type="datetime-local" class="widefat" id="foxfire_shipped_date" name="foxfire_shipped_date" value="<?php echo esc_attr( $shipped_date ); ?>">
		</p>
		<p>
			<label><input type="checkbox" name="foxfire_needs_follow_up" value="yes" <?php checked( $needs_followup ); ?>> <?php esc_html_e( 'Needs staff follow-up', 'foxfire-operations' ); ?></label>
		</p>
		<p class="description"><?php esc_html_e( 'Save these fields, choose Shipped in the order status control, and update the order. The customer shipment email is sent only when the status changes to Shipped.', 'foxfire-operations' ); ?></p>
	</div>
	<?php
}

/**
 * Queue a short per-user validation message for the redirect after order save.
 *
 * @param string $message Safe translated message.
 * @return void
 */
function foxfire_operations_queue_order_error( $message ) {
	$user_id = get_current_user_id();
	if ( ! $user_id ) {
		return;
	}

	$errors   = get_transient( 'foxfire_order_errors_' . $user_id );
	$errors   = is_array( $errors ) ? $errors : array();
	$errors[] = sanitize_text_field( $message );
	set_transient( 'foxfire_order_errors_' . $user_id, array_unique( $errors ), MINUTE_IN_SECONDS );
}

/**
 * Display and consume order-field validation messages.
 *
 * @return void
 */
function foxfire_operations_display_order_errors() {
	$user_id = get_current_user_id();
	$errors  = $user_id ? get_transient( 'foxfire_order_errors_' . $user_id ) : false;
	if ( ! is_array( $errors ) || empty( $errors ) ) {
		return;
	}

	delete_transient( 'foxfire_order_errors_' . $user_id );
	foreach ( $errors as $error ) {
		echo '<div class="notice notice-error is-dismissible"><p>' . esc_html( $error ) . '</p></div>';
	}
}
add_action( 'admin_notices', 'foxfire_operations_display_order_errors' );

/**
 * Save fulfillment fields before WooCommerce processes the status transition.
 *
 * @param int              $order_id Order ID.
 * @param WP_Post|WC_Order $object   Order object supplied by the active data store.
 * @return void
 */
function foxfire_operations_save_order_fulfillment( $order_id, $object ) {
	unset( $object );

	if ( ! current_user_can( 'edit_shop_orders' ) ) {
		return;
	}

	$nonce = isset( $_POST['foxfire_order_fulfillment_nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['foxfire_order_fulfillment_nonce'] ) ) : '';
	if ( ! wp_verify_nonce( $nonce, 'foxfire_save_order_fulfillment_' . $order_id ) ) {
		return;
	}

	$order = wc_get_order( $order_id );
	if ( ! $order ) {
		return;
	}

	$old_values = array(
		'carrier'  => (string) $order->get_meta( '_foxfire_shipping_carrier', true ),
		'tracking' => (string) $order->get_meta( '_foxfire_tracking_number', true ),
		'url'      => (string) $order->get_meta( '_foxfire_tracking_url', true ),
		'date'     => (string) $order->get_meta( '_foxfire_shipped_date_gmt', true ),
		'followup' => (string) $order->get_meta( '_foxfire_needs_follow_up', true ),
	);

	$carrier     = isset( $_POST['foxfire_shipping_carrier'] ) ? foxfire_operations_sanitize_order_text( wp_unslash( $_POST['foxfire_shipping_carrier'] ), 80 ) : '';
	$tracking    = isset( $_POST['foxfire_tracking_number'] ) ? foxfire_operations_sanitize_order_text( wp_unslash( $_POST['foxfire_tracking_number'] ), 120 ) : '';
	$raw_url     = isset( $_POST['foxfire_tracking_url'] ) ? trim( (string) wp_unslash( $_POST['foxfire_tracking_url'] ) ) : '';
	$tracking_url = esc_url_raw( $raw_url, array( 'http', 'https' ) );
	$raw_date    = isset( $_POST['foxfire_shipped_date'] ) ? (string) wp_unslash( $_POST['foxfire_shipped_date'] ) : '';
	$shipped_gmt = foxfire_operations_normalize_shipped_date( $raw_date );
	$followup    = isset( $_POST['foxfire_needs_follow_up'] ) && 'yes' === sanitize_key( wp_unslash( $_POST['foxfire_needs_follow_up'] ) ) ? 'yes' : 'no';

	$order->update_meta_data( '_foxfire_shipping_carrier', $carrier );
	$order->update_meta_data( '_foxfire_tracking_number', $tracking );
	$order->update_meta_data( '_foxfire_needs_follow_up', $followup );

	if ( '' !== $raw_url && ( '' === $tracking_url || strlen( $raw_url ) > 2048 ) ) {
		foxfire_operations_queue_order_error( __( 'Tracking URL was not saved. Enter a valid HTTP or HTTPS URL no longer than 2,048 characters.', 'foxfire-operations' ) );
	} else {
		$order->update_meta_data( '_foxfire_tracking_url', $tracking_url );
	}

	if ( '' !== $raw_date && '' === $shipped_gmt ) {
		foxfire_operations_queue_order_error( __( 'Shipped date was not saved because it was invalid.', 'foxfire-operations' ) );
	} else {
		$order->update_meta_data( '_foxfire_shipped_date_gmt', $shipped_gmt );
	}

	$order->save_meta_data();

	$new_values = array(
		'carrier'  => $carrier,
		'tracking' => $tracking,
		'url'      => '' !== $raw_url && '' === $tracking_url ? $old_values['url'] : $tracking_url,
		'date'     => '' !== $raw_date && '' === $shipped_gmt ? $old_values['date'] : $shipped_gmt,
		'followup' => $followup,
	);

	if ( $old_values !== $new_values ) {
		$user = wp_get_current_user();
		/* translators: %s: staff display name. */
		$order->add_order_note( sprintf( __( 'Shipment details updated by %s.', 'foxfire-operations' ), $user->display_name ), false, true );
	}
}
add_action( 'woocommerce_process_shop_order_meta', 'foxfire_operations_save_order_fulfillment', 45, 2 );

/**
 * Stamp the first shipped date before the shipped notification is dispatched.
 *
 * @param int      $order_id Order ID.
 * @param WC_Order $order    Order object.
 * @return void
 */
function foxfire_operations_stamp_shipped_date( $order_id, $order ) {
	if ( ! $order instanceof WC_Order ) {
		$order = wc_get_order( $order_id );
	}
	if ( ! $order || $order->get_meta( '_foxfire_shipped_date_gmt', true ) ) {
		return;
	}

	$order->update_meta_data( '_foxfire_shipped_date_gmt', gmdate( 'Y-m-d H:i:s' ) );
	$order->save_meta_data();
}
add_action( 'woocommerce_order_status_shipped', 'foxfire_operations_stamp_shipped_date', 5, 2 );

/**
 * Add the shipped transition to WooCommerce's transactional email dispatcher.
 *
 * @param string[] $actions Existing email-trigger actions.
 * @return string[]
 */
function foxfire_operations_add_shipped_email_action( $actions ) {
	$actions[] = 'woocommerce_order_status_shipped';

	return array_values( array_unique( $actions ) );
}
add_filter( 'woocommerce_email_actions', 'foxfire_operations_add_shipped_email_action' );

/**
 * Register the customer shipment email.
 *
 * @param array<string, WC_Email> $emails Existing email classes.
 * @return array<string, WC_Email>
 */
function foxfire_operations_register_shipped_email( $emails ) {
	require_once FOXFIRE_OPERATIONS_DIR . 'includes/class-foxfire-email-customer-shipped-order.php';
	$emails['Foxfire_Email_Customer_Shipped_Order'] = new Foxfire_Email_Customer_Shipped_Order();

	return $emails;
}
add_filter( 'woocommerce_email_classes', 'foxfire_operations_register_shipped_email' );

/**
 * Return public shipment details for an order.
 *
 * @param WC_Order $order Order object.
 * @return array{carrier:string, tracking:string, url:string, date:string, has_data:bool}
 */
function foxfire_operations_get_tracking_details( $order ) {
	$carrier      = (string) $order->get_meta( '_foxfire_shipping_carrier', true );
	$tracking     = (string) $order->get_meta( '_foxfire_tracking_number', true );
	$url          = esc_url_raw( (string) $order->get_meta( '_foxfire_tracking_url', true ), array( 'http', 'https' ) );
	$shipped_gmt  = (string) $order->get_meta( '_foxfire_shipped_date_gmt', true );
	$shipped_date = '' !== $shipped_gmt ? get_date_from_gmt( $shipped_gmt, wc_date_format() ) : '';

	return array(
		'carrier'  => $carrier,
		'tracking' => $tracking,
		'url'      => $url,
		'date'     => $shipped_date,
		'has_data' => '' !== $carrier || '' !== $tracking || '' !== $url || '' !== $shipped_date,
	);
}

/**
 * Render customer tracking details after the account order card.
 *
 * @param WC_Order $order Order object.
 * @return void
 */
function foxfire_operations_render_account_tracking( $order ) {
	if ( ! $order instanceof WC_Order || ! is_user_logged_in() ) {
		return;
	}

	$order_user_id = (int) $order->get_user_id();
	if ( ! current_user_can( 'edit_shop_orders' ) && ( ! $order_user_id || get_current_user_id() !== $order_user_id ) ) {
		return;
	}

	$details = foxfire_operations_get_tracking_details( $order );
	if ( ! $details['has_data'] ) {
		return;
	}
	?>
	<section class="foxfire-shipment-tracking" aria-labelledby="foxfire-shipment-tracking-title">
		<h2 id="foxfire-shipment-tracking-title"><?php esc_html_e( 'Shipment tracking', 'foxfire-operations' ); ?></h2>
		<dl>
			<?php if ( $details['carrier'] ) : ?><div><dt><?php esc_html_e( 'Carrier', 'foxfire-operations' ); ?></dt><dd><?php echo esc_html( $details['carrier'] ); ?></dd></div><?php endif; ?>
			<?php if ( $details['tracking'] ) : ?><div><dt><?php esc_html_e( 'Tracking number', 'foxfire-operations' ); ?></dt><dd><?php echo esc_html( $details['tracking'] ); ?></dd></div><?php endif; ?>
			<?php if ( $details['date'] ) : ?><div><dt><?php esc_html_e( 'Shipped', 'foxfire-operations' ); ?></dt><dd><?php echo esc_html( $details['date'] ); ?></dd></div><?php endif; ?>
		</dl>
		<?php if ( $details['url'] ) : ?>
			<p><a class="button" href="<?php echo esc_url( $details['url'] ); ?>" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'Track shipment', 'foxfire-operations' ); ?></a></p>
		<?php endif; ?>
	</section>
	<?php
}
add_action( 'woocommerce_order_details_after_order_table', 'foxfire_operations_render_account_tracking', 20 );

/**
 * Add tracking to customer transactional emails, including the shipped email.
 *
 * @param WC_Order $order         Order object.
 * @param bool     $sent_to_admin Whether this is an administrator email.
 * @param bool     $plain_text    Whether this is a plain-text email.
 * @param WC_Email $email         Email object.
 * @return void
 */
function foxfire_operations_render_email_tracking( $order, $sent_to_admin, $plain_text, $email ) {
	unset( $email );
	if ( $sent_to_admin || ! $order instanceof WC_Order ) {
		return;
	}

	$details = foxfire_operations_get_tracking_details( $order );
	if ( ! $details['has_data'] ) {
		return;
	}

	if ( $plain_text ) {
		echo "\n" . esc_html__( 'SHIPMENT TRACKING', 'foxfire-operations' ) . "\n";
		if ( $details['carrier'] ) {
			echo esc_html__( 'Carrier:', 'foxfire-operations' ) . ' ' . esc_html( $details['carrier'] ) . "\n";
		}
		if ( $details['tracking'] ) {
			echo esc_html__( 'Tracking number:', 'foxfire-operations' ) . ' ' . esc_html( $details['tracking'] ) . "\n";
		}
		if ( $details['date'] ) {
			echo esc_html__( 'Shipped:', 'foxfire-operations' ) . ' ' . esc_html( $details['date'] ) . "\n";
		}
		if ( $details['url'] ) {
			echo esc_url( $details['url'] ) . "\n";
		}
		return;
	}

	echo '<h2>' . esc_html__( 'Shipment tracking', 'foxfire-operations' ) . '</h2>';
	echo '<table cellspacing="0" cellpadding="6" style="width:100%;border:1px solid #e5e5e5" border="1">';
	if ( $details['carrier'] ) {
		echo '<tr><th scope="row" style="text-align:left">' . esc_html__( 'Carrier', 'foxfire-operations' ) . '</th><td>' . esc_html( $details['carrier'] ) . '</td></tr>';
	}
	if ( $details['tracking'] ) {
		echo '<tr><th scope="row" style="text-align:left">' . esc_html__( 'Tracking number', 'foxfire-operations' ) . '</th><td>' . esc_html( $details['tracking'] ) . '</td></tr>';
	}
	if ( $details['date'] ) {
		echo '<tr><th scope="row" style="text-align:left">' . esc_html__( 'Shipped', 'foxfire-operations' ) . '</th><td>' . esc_html( $details['date'] ) . '</td></tr>';
	}
	if ( $details['url'] ) {
		echo '<tr><th scope="row" style="text-align:left">' . esc_html__( 'Tracking link', 'foxfire-operations' ) . '</th><td><a href="' . esc_url( $details['url'] ) . '">' . esc_html__( 'Track shipment', 'foxfire-operations' ) . '</a></td></tr>';
	}
	echo '</table>';
}
add_action( 'woocommerce_email_after_order_table', 'foxfire_operations_render_email_tracking', 20, 4 );

/**
 * Register the operational queue below the Foxfire Ops menu.
 *
 * @return void
 */
function foxfire_operations_register_order_queue_page() {
	add_submenu_page(
		'foxfire-operations',
		__( 'Order Queue', 'foxfire-operations' ),
		__( 'Order Queue', 'foxfire-operations' ),
		FOXFIRE_OPERATIONS_CAPABILITY,
		'foxfire-order-queue',
		'foxfire_operations_render_order_queue_page'
	);
}
add_action( 'admin_menu', 'foxfire_operations_register_order_queue_page', 40 );

/**
 * Operational queue definitions.
 *
 * @return array<string, array{label:string,status?:string[],followup?:bool}>
 */
function foxfire_operations_order_queues() {
	return array(
		'active'           => array( 'label' => __( 'All active', 'foxfire-operations' ), 'status' => array( 'pending', 'on-hold', 'processing', 'shipped', 'failed' ) ),
		'awaiting-payment' => array( 'label' => __( 'Awaiting payment', 'foxfire-operations' ), 'status' => array( 'pending', 'on-hold' ) ),
		'fulfillment'      => array( 'label' => __( 'Awaiting fulfillment', 'foxfire-operations' ), 'status' => array( 'processing' ) ),
		'in-transit'       => array( 'label' => __( 'Shipped / in transit', 'foxfire-operations' ), 'status' => array( 'shipped' ) ),
		'follow-up'        => array( 'label' => __( 'Needs follow-up', 'foxfire-operations' ), 'followup' => true ),
	);
}

/**
 * Render the read-only operational order queue.
 *
 * @return void
 */
function foxfire_operations_render_order_queue_page() {
	if ( ! current_user_can( FOXFIRE_OPERATIONS_CAPABILITY ) || ! current_user_can( 'edit_shop_orders' ) ) {
		wp_die( esc_html__( 'You do not have permission to view the order queue.', 'foxfire-operations' ), esc_html__( 'Access denied', 'foxfire-operations' ), array( 'response' => 403 ) );
	}

	$queues       = foxfire_operations_order_queues();
	$selected     = isset( $_GET['queue'] ) ? sanitize_key( wp_unslash( $_GET['queue'] ) ) : 'active'; // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only operational filter.
	$selected     = isset( $queues[ $selected ] ) ? $selected : 'active';
	$current_page = isset( $_GET['paged'] ) ? max( 1, absint( $_GET['paged'] ) ) : 1; // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only pagination.
	$query_args   = array(
		'limit'    => 25,
		'page'     => $current_page,
		'paginate' => true,
		'orderby'  => 'date',
		'order'    => 'DESC',
	);

	if ( ! empty( $queues[ $selected ]['status'] ) ) {
		$query_args['status'] = $queues[ $selected ]['status'];
	}
	if ( ! empty( $queues[ $selected ]['followup'] ) ) {
		$query_args['meta_key']   = '_foxfire_needs_follow_up';
		$query_args['meta_value'] = 'yes';
	}

	$results = wc_get_orders( $query_args );
	?>
	<div class="wrap ff-ops-wrap ff-order-queue">
		<h1><?php esc_html_e( 'Order Queue', 'foxfire-operations' ); ?></h1>
		<p class="ff-ops-intro"><?php esc_html_e( 'Use statuses for workflow and the follow-up flag for exceptions. Open an order to add notes, shipment details, resend supported emails, or record a refund.', 'foxfire-operations' ); ?></p>
		<nav class="nav-tab-wrapper" aria-label="<?php esc_attr_e( 'Order queue filters', 'foxfire-operations' ); ?>">
			<?php foreach ( $queues as $key => $queue ) : ?>
				<a class="nav-tab <?php echo $selected === $key ? 'nav-tab-active' : ''; ?>" href="<?php echo esc_url( add_query_arg( array( 'page' => 'foxfire-order-queue', 'queue' => $key ), admin_url( 'admin.php' ) ) ); ?>"><?php echo esc_html( $queue['label'] ); ?></a>
			<?php endforeach; ?>
		</nav>

		<table class="widefat striped ff-ops-table ff-order-queue__table">
			<thead><tr><th><?php esc_html_e( 'Order', 'foxfire-operations' ); ?></th><th><?php esc_html_e( 'Date', 'foxfire-operations' ); ?></th><th><?php esc_html_e( 'Customer', 'foxfire-operations' ); ?></th><th><?php esc_html_e( 'Status', 'foxfire-operations' ); ?></th><th><?php esc_html_e( 'Tracking', 'foxfire-operations' ); ?></th><th><?php esc_html_e( 'Total', 'foxfire-operations' ); ?></th><th><?php esc_html_e( 'Open', 'foxfire-operations' ); ?></th></tr></thead>
			<tbody>
			<?php if ( empty( $results->orders ) ) : ?>
				<tr><td colspan="7"><?php esc_html_e( 'No orders are currently in this queue.', 'foxfire-operations' ); ?></td></tr>
			<?php else : ?>
				<?php foreach ( $results->orders as $order ) : ?>
					<?php
					$details      = foxfire_operations_get_tracking_details( $order );
					$customer     = trim( $order->get_formatted_billing_full_name() );
					$tracking_text = $details['tracking'] ? $details['tracking'] : ( $details['carrier'] ? $details['carrier'] : __( 'Not added', 'foxfire-operations' ) );
					?>
					<tr>
						<th scope="row">#<?php echo esc_html( $order->get_order_number() ); ?></th>
						<td><?php echo esc_html( $order->get_date_created() ? wc_format_datetime( $order->get_date_created() ) : '—' ); ?></td>
						<td><?php echo esc_html( $customer ? $customer : __( 'Guest', 'foxfire-operations' ) ); ?></td>
						<td><?php echo esc_html( wc_get_order_status_name( $order->get_status() ) ); ?><?php if ( 'yes' === $order->get_meta( '_foxfire_needs_follow_up', true ) ) : ?><br><strong class="foxfire-ops-status--missing"><?php esc_html_e( 'Follow-up', 'foxfire-operations' ); ?></strong><?php endif; ?></td>
						<td><?php echo esc_html( $tracking_text ); ?></td>
						<td><?php echo wp_kses_post( $order->get_formatted_order_total() ); ?></td>
						<td><a class="button" href="<?php echo esc_url( $order->get_edit_order_url() ); ?>"><?php esc_html_e( 'Open', 'foxfire-operations' ); ?></a></td>
					</tr>
				<?php endforeach; ?>
			<?php endif; ?>
			</tbody>
		</table>

		<?php if ( $results->max_num_pages > 1 ) : ?>
			<div class="tablenav"><div class="tablenav-pages">
				<?php echo wp_kses_post( paginate_links( array( 'base' => add_query_arg( array( 'page' => 'foxfire-order-queue', 'queue' => $selected, 'paged' => '%#%' ), admin_url( 'admin.php' ) ), 'current' => $current_page, 'total' => $results->max_num_pages ) ) ); ?>
			</div></div>
		<?php endif; ?>
	</div>
	<?php
}
