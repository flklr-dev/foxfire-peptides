<?php
/**
 * Disabled-by-default, instruction-based manual payment gateways.
 *
 * @package Foxfire_Operations
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'WC_Payment_Gateway' ) ) {
	return;
}

/** Shared secure behavior for instruction-based payment methods. */
abstract class Foxfire_Operations_Manual_Gateway extends WC_Payment_Gateway {
	/** Customer-facing instructions saved in WooCommerce settings. */
	public $instructions = '';

	/** Configure a provider-specific gateway instance. */
	protected function foxfire_initialize( string $provider_slug, string $provider_label ): void {
		$this->id                 = 'foxfire_manual_' . $provider_slug;
		$this->method_title       = sprintf( /* translators: %s: payment provider name. */ __( '%s Manual Payment', 'foxfire-operations' ), $provider_label );
		$this->method_description = sprintf( /* translators: %s: payment provider name. */ __( 'Instruction-based %s payment. Disabled until an Administrator confirms and saves complete customer instructions.', 'foxfire-operations' ), $provider_label );
		$this->has_fields         = false;
		$this->supports           = array( 'products' );

		$this->init_form_fields();
		$this->init_settings();

		$this->enabled      = $this->get_option( 'enabled', 'no' );
		$this->title        = $this->get_option( 'title', $provider_label );
		$this->description  = $this->get_option( 'description', '' );
		$this->instructions = $this->get_option( 'instructions', '' );

		add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );
		add_action( 'woocommerce_thankyou_' . $this->id, array( $this, 'thankyou_page' ) );
		add_action( 'woocommerce_email_before_order_table', array( $this, 'email_instructions' ), 10, 4 );
	}

	/** Define the safe fields visible in WooCommerce payment settings. */
	public function init_form_fields(): void {
		$this->form_fields = array(
			'enabled'      => array(
				'title'       => __( 'Enable/Disable', 'foxfire-operations' ),
				'type'        => 'checkbox',
				'label'       => __( 'Enable this confirmed manual payment method', 'foxfire-operations' ),
				'default'     => 'no',
				'description' => __( 'Leave disabled until the client owns the account and the complete workflow has been tested.', 'foxfire-operations' ),
			),
			'title'        => array(
				'title'       => __( 'Checkout title', 'foxfire-operations' ),
				'type'        => 'text',
				'description' => __( 'Short customer-facing payment-method name. Plain text only.', 'foxfire-operations' ),
				'desc_tip'    => true,
				'default'     => $this->method_title,
			),
			'description'  => array(
				'title'       => __( 'Checkout description', 'foxfire-operations' ),
				'type'        => 'textarea',
				'description' => __( 'Shown while the customer selects this method. Plain text only.', 'foxfire-operations' ),
				'default'     => '',
			),
			'instructions' => array(
				'title'       => __( 'Payment instructions', 'foxfire-operations' ),
				'type'        => 'textarea',
				'description' => __( 'Shown after checkout and in the customer email. Plain text only. Include the order-reference requirement, verification expectation, and support route. Never include a password, API secret, recovery code, or private key.', 'foxfire-operations' ),
				'default'     => '',
			),
		);
	}

	/** Limit and sanitize single-line settings. */
	public function validate_text_field( $key, $value ): string {
		unset( $key );
		$value = sanitize_text_field( stripslashes( (string) $value ) );
		return foxfire_operations_limit_content_length( $value, 120 );
	}

	/** Limit and sanitize multiline customer instructions without accepting HTML. */
	public function validate_textarea_field( $key, $value ): string {
		unset( $key );
		$value = sanitize_textarea_field( stripslashes( (string) $value ) );
		return foxfire_operations_limit_content_length( $value, 1200 );
	}

	/** Protect settings even if this method is called outside the normal WC screen. */
	public function process_admin_options(): bool {
		if ( ! current_user_can( FOXFIRE_OPERATIONS_SENSITIVE_CAPABILITY ) ) {
			wp_die(
				esc_html__( 'Administrator permission is required to change payment instructions.', 'foxfire-operations' ),
				esc_html__( 'Access denied', 'foxfire-operations' ),
				array( 'response' => 403 )
			);
		}

		check_admin_referer( 'woocommerce-settings' );
		$saved = parent::process_admin_options();
		if ( $saved ) {
			update_option(
				'foxfire_payment_settings_audit_' . $this->id,
				array( 'user_id' => get_current_user_id(), 'updated_gmt' => gmdate( 'Y-m-d H:i:s' ) ),
				false
			);
		}

		return $saved;
	}

	/** Hide enabled-but-incomplete methods instead of exposing an empty workflow. */
	public function is_available(): bool {
		return parent::is_available() && '' !== trim( (string) $this->instructions );
	}

	/** Place an instruction-based order on hold for staff payment verification. */
	public function process_payment( $order_id ): array {
		$order = wc_get_order( $order_id );
		if ( ! $order instanceof WC_Order ) {
			return array( 'result' => 'failure' );
		}

		$order->update_status( 'on-hold', __( 'Awaiting verification of the selected manual payment method.', 'foxfire-operations' ) );

		if ( WC()->cart ) {
			WC()->cart->empty_cart();
		}

		return array(
			'result'   => 'success',
			'redirect' => $this->get_return_url( $order ),
		);
	}

	/** Render saved instructions on the order confirmation page. */
	public function thankyou_page(): void {
		if ( '' !== trim( (string) $this->instructions ) ) {
			echo '<section class="woocommerce-order-details foxfire-manual-payment-instructions"><h2>' . esc_html__( 'Payment instructions', 'foxfire-operations' ) . '</h2>';
			echo wp_kses_post( wpautop( esc_html( (string) $this->instructions ) ) );
			echo '</section>';
		}
	}

	/** Render the same saved instructions in eligible customer emails. */
	public function email_instructions( $order, bool $sent_to_admin, bool $plain_text, $email ): void {
		unset( $email );
		if ( $sent_to_admin || ! $order instanceof WC_Order || $this->id !== $order->get_payment_method() || ! $order->has_status( array( 'on-hold', 'pending' ) ) || '' === trim( (string) $this->instructions ) ) {
			return;
		}

		if ( $plain_text ) {
			echo "\n" . esc_html__( 'Payment instructions', 'foxfire-operations' ) . "\n" . sanitize_textarea_field( (string) $this->instructions ) . "\n"; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Plain-text email output; the setting is sanitized on save and again here.
			return;
		}

		echo '<h2>' . esc_html__( 'Payment instructions', 'foxfire-operations' ) . '</h2>';
		echo wp_kses_post( wpautop( esc_html( (string) $this->instructions ) ) );
	}
}

/** Wise manual payment option. */
final class Foxfire_Operations_Gateway_Wise extends Foxfire_Operations_Manual_Gateway {
	public function __construct() {
		$this->foxfire_initialize( 'wise', 'Wise' );
	}
}

/** Zelle manual payment option. */
final class Foxfire_Operations_Gateway_Zelle extends Foxfire_Operations_Manual_Gateway {
	public function __construct() {
		$this->foxfire_initialize( 'zelle', 'Zelle' );
	}
}

/** GCash manual payment option. */
final class Foxfire_Operations_Gateway_GCash extends Foxfire_Operations_Manual_Gateway {
	public function __construct() {
		$this->foxfire_initialize( 'gcash', 'GCash' );
	}
}
