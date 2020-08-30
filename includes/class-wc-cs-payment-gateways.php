<?php

defined( 'ABSPATH' ) || exit ;

/**
 * Load Payment Gateways
 * 
 * @class WC_CS_Payment_Gateways
 * @package Class
 */
class WC_CS_Payment_Gateways {

	/**
	 * Get our payment gateways to load in to the WC checkout
	 *
	 * @var array 
	 */
	protected static $load_gateways = array() ;

	/**
	 * Get the disabled payment gateways in checkout to check it for funds addition
	 *
	 * @var array 
	 */
	protected static $disabled_payment_gateways_for_funds_addition ;

	/**
	 * Get the disabled payment gateways in checkout to check it for repayment
	 *
	 * @var array 
	 */
	protected static $disabled_payment_gateways_for_repayment ;

	/**
	 * The single instance of the class.
	 */
	protected static $instance = null ;

	/**
	 * Create instance for WC_CS_Payment_Gateways.
	 */
	public static function instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self() ;
		}
		return self::$instance ;
	}

	/**
	 * Init WC_CS_Payment_Gateways.
	 */
	public function init() {
		add_action( 'plugins_loaded', array( $this, 'load_payment_gateways' ), 20 ) ;
		add_filter( 'woocommerce_payment_gateways', array( $this, 'add_payment_gateways' ) ) ;
		add_filter( 'woocommerce_available_payment_gateways', array( $this, 'set_payment_gateways' ), 999 ) ;
	}

	/**
	 * Get the disabled payment gateways in checkout to check it for funds addition
	 *
	 * @return array 
	 */
	public function get_disabled_payment_gateways_for_funds_addition() {
		if ( is_array( self::$disabled_payment_gateways_for_funds_addition ) ) {
			return self::$disabled_payment_gateways_for_funds_addition ;
		}
		self::$disabled_payment_gateways_for_funds_addition = get_option( WC_CS_PREFIX . 'disabled_payment_gateways_for_funds_addition', array() ) ;

		return self::$disabled_payment_gateways_for_funds_addition ;
	}

	/**
	 * Get the disabled payment gateways in checkout to check it for repayment
	 *
	 * @return array 
	 */
	public function get_disabled_payment_gateways_for_repayment() {
		if ( is_array( self::$disabled_payment_gateways_for_repayment ) ) {
			return self::$disabled_payment_gateways_for_repayment ;
		}
		self::$disabled_payment_gateways_for_repayment = get_option( WC_CS_PREFIX . 'disabled_payment_gateways_for_repayment', array() ) ;

		return self::$disabled_payment_gateways_for_repayment ;
	}

	/**
	 * Get payment gateways to load in to the WC checkout
	 */
	public function load_payment_gateways() {
		if ( ! class_exists( 'WC_Payment_Gateway' ) ) {
			return ;
		}

		self::$load_gateways[] = include_once('gateways/credits/class-wc-cs-credits-gateway.php') ;
	}

	/**
	 * Add payment gateways awaiting to load
	 * 
	 * @param object $gateways
	 * @return array
	 */
	public function add_payment_gateways( $gateways ) {
		if ( empty( self::$load_gateways ) ) {
			return $gateways ;
		}

		foreach ( self::$load_gateways as $gateway ) {
			$gateways[] = $gateway ;
		}
		return $gateways ;
	}

	/**
	 * Check whether specific payment gateway is needed in checkout
	 * 
	 * @param WC_Payment_Gateway $gateway
	 * @return bool
	 */
	public function need_payment_gateway( $gateway ) {
		$need = true ;

		if ( _wc_cs()->funds_addition->in_progress() ) {
			// This is high priority to disable any payment gateways
			if ( in_array( $gateway->id, ( array ) self::get_disabled_payment_gateways_for_funds_addition() ) ) {
				$need = false ;
			}
		}

		if ( _wc_cs()->repayment->in_progress() ) {
			// This is high priority to disable any payment gateways
			if ( in_array( $gateway->id, ( array ) self::get_disabled_payment_gateways_for_repayment() ) ) {
				$need = false ;
			}
		}

		return apply_filters( 'wc_cs_need_payment_gateway', $need, $gateway ) ;
	}

	/**
	 * Handle payment gateways in checkout
	 * 
	 * @param array $_available_gateways
	 * @return array
	 */
	public function set_payment_gateways( $_available_gateways ) {
		if ( is_admin() ) {
			return $_available_gateways ;
		}

		foreach ( $_available_gateways as $gateway_name => $gateway ) {
			if ( ! isset( $gateway->id ) ) {
				continue ;
			}

			if ( ! self::need_payment_gateway( $gateway ) ) {
				unset( $_available_gateways[ $gateway_name ] ) ;
			}
		}
		return $_available_gateways ;
	}

}
