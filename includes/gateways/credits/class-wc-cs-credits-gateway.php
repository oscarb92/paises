<?php

defined( 'ABSPATH' ) || exit ;

/**
 * Register new Payment Gateway id of Credits.
 * 
 * @class WC_CS_Credits_Gateway
 * @package Class
 */
class WC_CS_Credits_Gateway extends WC_Payment_Gateway {

	/**
	 * WC_CS_Credits_Gateway constructor.
	 */
	public function __construct() {
		$this->id                 = WC_CS_PREFIX . 'credits' ;
		$this->method_title       = __( 'Credits', 'credits-for-woocommerce' ) ;
		$this->method_description = __( 'Pay with Credits.', 'credits-for-woocommerce' ) ;
		$this->has_fields         = true ;
		$this->init_form_fields() ;
		$this->init_settings() ;
		$this->enabled            = $this->get_option( 'enabled' ) ;
		$this->title              = $this->get_option( 'title' ) ;
		$this->description        = $this->get_option( 'description' ) ;
		$this->supports           = array(
			'refunds',
			WC_CS_PREFIX . 'credits'
				) ;

		add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) ) ;
		add_action( 'admin_notices', array( $this, 'gateway_dependencies_notice' ) ) ;
	}

	/**
	 * Admin Settings
	 */
	public function init_form_fields() {
		$this->form_fields = array(
			'enabled'     => array(
				'title'   => __( 'Enable/Disable', 'credits-for-woocommerce' ),
				'type'    => 'checkbox',
				'label'   => __( 'Credits', 'credits-for-woocommerce' ),
				'default' => 'no'
			),
			'title'       => array(
				'title'       => __( 'Title:', 'credits-for-woocommerce' ),
				'type'        => 'text',
				'description' => __( 'This controls the title which the user see during checkout.', 'credits-for-woocommerce' ),
				'default'     => __( 'Credits', 'credits-for-woocommerce' ),
			),
			'description' => array(
				'title'    => __( 'Description', 'credits-for-woocommerce' ),
				'type'     => 'textarea',
				'default'  => 'Pay using your Credits.',
				'desc_tip' => true,
			),
				) ;
	}

	/**
	 * Output a admin notice when gateway dependencies not met.
	 */
	public function gateway_dependencies_notice() {
		if ( 'yes' === $this->enabled ) {
			printf( '<div class="notice notice-warning"><p>%s</p></div>', esc_html__( 'Credits Payment Gateway will not be displayed in checkout page if the user doesn\'t have enough credits in their account.', 'credits-for-woocommerce' ) ) ;
		}
	}

	/**
	 * Can the order be refunded ?
	 *
	 * @param  WC_Order $order
	 * @return bool
	 */
	public function can_refund_order( $order ) {
		if ( ! $order ) {
			return false ;
		}

		if ( 'yes' !== WC_CS_Order_Manager::get_order_txn_status( $order->get_id(), 'payment_made' ) ) {
			return false ;
		}

		if ( ! WC_CS_Order_Manager::get_meta( $order->get_id(), 'credits_id' ) ) {
			return false ;
		}

		return $order ;
	}

	/**
	 * Check our gateway is available for use.
	 *
	 * @return bool
	 */
	public function is_available() {

		// Check cart class is loaded or abort.
		if ( is_null( WC()->cart ) ) {
			return apply_filters( 'wc_cs_is_credits_available_by_default', parent::is_available() ) ;
		}

		if ( ! _wc_cs()->dashboard->is_active() ) {
			return false ;
		}

		if ( _wc_cs()->dashboard->get_credits()->get_available_credits( 'edit' ) <= 0 ) {
			return false ;
		}

		if ( $this->get_order_total() < wc_format_decimal( floatval( get_option( WC_CS_PREFIX . 'min_cart_total_to_use_credits', '0' ) ) ) ) {
			return false ;
		}

		if ( $this->get_order_total() > _wc_cs()->dashboard->get_credits()->get_available_credits( 'edit' ) ) {
			return false ;
		}

		if ( _wc_cs()->funds_addition->in_progress() || _wc_cs()->repayment->in_progress() ) {
			return false ;
		}

		return true ;
	}

	/**
	 * Process Payment.
	 * 
	 * @param int $order_id
	 * @return array
	 * @throws Exception
	 */
	public function process_payment( $order_id ) {

		try {
			$order = wc_get_order( $order_id ) ;

			if ( ! $order ) {
				throw new Exception( __( 'Something went wrong !!', 'credits-for-woocommerce' ) ) ;
			}

			$order->update_status( get_option( WC_CS_PREFIX . 'credits_gateway_successful_order_status' ) ) ;

			// Reduce stock levels
			wc_reduce_stock_levels( $order ) ;

			// Remove cart
			WC()->cart->empty_cart() ;

			return array(
				'result'   => 'success',
				'redirect' => $this->get_return_url( $order )
					) ;
		} catch ( Exception $e ) {
			if ( ! empty( $e ) ) {
				wc_add_notice( $e->getMessage(), 'error' ) ;
			}
		}

		// If we reached this point then there were errors
		return array(
			'result'   => 'failure',
			'redirect' => $this->get_return_url( $order )
				) ;
	}

	/**
	 * Process a refund if supported.
	 */
	public function process_refund( $order_id, $amount = null, $reason = null ) {

		try {
			$order = wc_get_order( $order_id ) ;

			if ( ! $order ) {
				throw new Exception( __( 'Refund failed: Invalid order', 'credits-for-woocommerce' ) ) ;
			}

			if ( ! $this->can_refund_order( $order ) ) {
				throw new Exception( __( 'Refund failed: Something went wrong while initiating refund', 'credits-for-woocommerce' ) ) ;
			}

			$credits = _wc_cs_get_credits( WC_CS_Order_Manager::get_meta( $order->get_id(), 'credits_id' ) ) ;

			if ( ! $credits ) {
				throw new Exception( __( 'Refund failed: Couldn\'t find any credits for this order', 'credits-for-woocommerce' ) ) ;
			}

			$refund_amount = wc_format_decimal( $amount ) ;

			$credits->set_props( array(
				'total_outstanding_amount' => $credits->get_total_outstanding_amount( 'edit' ) - $refund_amount,
				'available_credits'        => $credits->get_available_credits( 'edit' ) + $refund_amount,
			) ) ;
			$credits->save() ;

			$credits_txn = _wc_cs_create_credits_txn( array(
				'credits_id' => $credits->get_id(),
				'user_id'    => $credits->get_user_id(),
				/* translators: 1: Refund Order ID, 2: Refunded amount, 3: Reason */
				'activity'   => sprintf( __( 'Refund for Order #%1$s', 'credits-for-woocommerce' ), $order_id ),
				'credited'   => $refund_amount,
				'order_id'   => $order_id,
				'balance'    => $credits->get_available_credits( 'edit' ),
				'type'       => 'refund-credits',
					) ) ;

			if ( is_wp_error( $credits_txn ) ) {
				throw new Exception( __( 'Refund failed: Couldn\'t process refund', 'credits-for-woocommerce' ) ) ;
			}

			$order->add_order_note(
					/* translators: 1: Refund amount, 2: Refund credits ID */
					sprintf( __( 'Refunded %1$s - Refund for credits ID: %2$s', 'credits-for-woocommerce' ), wc_price( $refund_amount, array( 'currency' => $order->get_currency() ) ), $credits->get_id() )
			) ;
		} catch ( Exception $e ) {
			return new WP_Error( 'wc-cs-credits-refund-error', $e->getMessage() ) ;
		}
		return true ;
	}

}

return new WC_CS_Credits_Gateway() ;
