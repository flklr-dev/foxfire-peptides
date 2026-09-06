<?php
/**
 * Customer shipped-order transactional email.
 *
 * @package Foxfire_Operations
 */

defined( 'ABSPATH' ) || exit;

/**
 * Shipment notification sent once when an order enters Shipped status.
 */
class Foxfire_Email_Customer_Shipped_Order extends WC_Email {
	/**
	 * Configure the email and its trigger.
	 */
	public function __construct() {
		$this->id             = 'foxfire_customer_shipped_order';
		$this->customer_email = true;
		$this->title          = __( 'Shipped order', 'foxfire-operations' );
		$this->description    = __( 'Sent to the customer when an order is marked Shipped.', 'foxfire-operations' );
		$this->email_group    = 'order-updates';
		$this->template_html  = 'emails/customer-shipped-order.php';
		$this->template_plain = 'emails/plain/customer-shipped-order.php';
		$this->template_base  = FOXFIRE_OPERATIONS_DIR . 'templates/';
		$this->placeholders   = array(
			'{order_date}'   => '',
			'{order_number}' => '',
		);

		add_action( 'woocommerce_order_status_shipped_notification', array( $this, 'trigger' ), 10, 2 );

		parent::__construct();
	}

	/**
	 * Trigger the customer email.
	 *
	 * @param int            $order_id Order ID.
	 * @param WC_Order|false $order    Order object.
	 * @return void
	 */
	public function trigger( $order_id, $order = false ) {
		$this->setup_locale();

		if ( $order_id && ! $order instanceof WC_Order ) {
			$order = wc_get_order( $order_id );
		}

		if ( $order instanceof WC_Order ) {
			$this->object                         = $order;
			$this->recipient                      = $order->get_billing_email();
			$this->placeholders['{order_date}']   = wc_format_datetime( $order->get_date_created() );
			$this->placeholders['{order_number}'] = $order->get_order_number();
		}

		$this->send_notification();
		$this->restore_locale();
	}

	/**
	 * Default subject.
	 *
	 * @return string
	 */
	public function get_default_subject() {
		return __( 'Your {site_title} order #{order_number} has shipped', 'foxfire-operations' );
	}

	/**
	 * Default heading.
	 *
	 * @return string
	 */
	public function get_default_heading() {
		return __( 'Your order is on the way', 'foxfire-operations' );
	}

	/**
	 * HTML content.
	 *
	 * @return string
	 */
	public function get_content_html() {
		return wc_get_template_html(
			$this->template_html,
			array(
				'order'              => $this->object,
				'email_heading'      => $this->get_heading(),
				'additional_content' => $this->get_additional_content(),
				'sent_to_admin'      => false,
				'plain_text'         => false,
				'email'              => $this,
			),
			'',
			$this->template_base
		);
	}

	/**
	 * Plain content.
	 *
	 * @return string
	 */
	public function get_content_plain() {
		return wc_get_template_html(
			$this->template_plain,
			array(
				'order'              => $this->object,
				'email_heading'      => $this->get_heading(),
				'additional_content' => $this->get_additional_content(),
				'sent_to_admin'      => false,
				'plain_text'         => true,
				'email'              => $this,
			),
			'',
			$this->template_base
		);
	}

	/**
	 * Default footer content.
	 *
	 * @return string
	 */
	public function get_default_additional_content() {
		return __( 'Tracking updates can take time to appear after the carrier receives the package.', 'foxfire-operations' );
	}
}
