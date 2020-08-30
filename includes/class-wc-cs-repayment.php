<?php

defined( 'ABSPATH' ) || exit ;

/**
 * Repayment.
 * 
 * @class WC_CS_Repayment
 * @package Class
 */
class WC_CS_Repayment {

	/**
	 * The single instance of the class.
	 */
	protected static $instance = null ;

	protected static $product_to_repay ;

	/**
	 * Create instance for WC_CS_Repayment.
	 */
	public static function instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self() ;
		}
		return self::$instance ;
	}

	/**
	 * Init WC_CS_Repayment.
	 */
	public function init() {
		add_filter( 'woocommerce_add_to_cart_validation', array( $this, 'validate_add_to_cart' ), 99, 4 ) ;
		add_filter( 'woocommerce_coupon_is_valid', array( $this, 'restrict_coupon_usage' ), 99 ) ;
		add_filter( 'woocommerce_is_sold_individually', array( $this, 'set_as_sold_individually' ), 99, 2 ) ;
		add_filter( 'woocommerce_product_is_visible', array( $this, 'set_as_invisible' ), 99, 2 ) ;
		add_filter( 'woocommerce_product_is_taxable', array( $this, 'set_as_non_taxable' ), 99, 2 ) ;
		add_filter( 'woocommerce_cart_needs_shipping', array( $this, 'do_not_allow_shipping' ), 99 ) ;
		add_action( 'woocommerce_before_calculate_totals', array( $this, 'check_cart_items' ) ) ;
		add_action( 'woocommerce_before_calculate_totals', array( $this, 'set_cart_total' ), 99 ) ;
		add_action( 'woocommerce_checkout_create_order_line_item', array( $this, 'add_order_item_meta' ), 10, 3 ) ;

		add_action( 'woocommerce_before_cart_table', array( $this, 'add_notice' ) ) ;
		add_action( 'woocommerce_checkout_before_customer_details', array( $this, 'add_notice' ) ) ;
	}

	public function in_progress() {
		if ( ! is_null( WC()->cart ) ) {
			foreach ( WC()->cart->cart_contents as $cart_item ) {
				if ( isset( $cart_item[ WC_CS_PREFIX . 'repayment' ] ) ) {
					return $cart_item[ WC_CS_PREFIX . 'repayment' ] ;
				}
			}
		}

		return null ;
	}

	public function get_product() {
		if ( is_numeric( self::$product_to_repay ) ) {
			return self::$product_to_repay ;
		}

		self::$product_to_repay = absint( get_option( WC_CS_PREFIX . 'get_selected_product_for_repayment' ) ) ;
		return self::$product_to_repay ;
	}

	public function validate_add_to_cart( $bool, $product_id, $qty, $variation_id = '' ) {
		$add_cart_product = $variation_id ? $variation_id : $product_id ;

		if ( $add_cart_product != $this->get_product() && $this->in_progress() ) {
			wc_add_notice( esc_html( get_option( WC_CS_PREFIX . 'repayment_in_cart_message' ) ), 'error' ) ;
			return false ;
		}
		return $bool ;
	}

	public function restrict_coupon_usage( $bool ) {
		if ( $this->in_progress() ) {
			throw new Exception( esc_html( get_option( WC_CS_PREFIX . 'coupon_error_notice_for_repayment' ) ), 100 ) ;
		}

		return $bool ;
	}

	public function set_as_sold_individually( $bool, $product ) {
		if ( $this->in_progress() && $this->get_product() === $product->get_id() ) {
			$bool = true ;
		}

		return $bool ;
	}

	public function set_as_invisible( $bool, $product_id ) {
		if ( $this->in_progress() && $this->get_product() === $product_id ) {
			$bool = false ;
		}

		return $bool ;
	}

	public function set_as_non_taxable( $bool, $product ) {
		if ( $this->in_progress() && $this->get_product() === $product->get_id() ) {
			$bool = false ;
		}

		return $bool ;
	}

	public function do_not_allow_shipping( $bool ) {
		if ( $this->in_progress() ) {
			$bool = false ;
		}

		return $bool ;
	}

	public function check_cart_items() {
		if ( ! $this->in_progress() ) {
			return ;
		}

		if ( ! _wc_cs()->dashboard->can_user_make_repayment() ) {
			WC()->cart->empty_cart() ;
			return ;
		}

		foreach ( WC()->cart->cart_contents as $item_key => $cart_item ) {
			if ( isset( $cart_item[ WC_CS_PREFIX . 'repayment' ] ) ) {
				WC()->cart->cart_contents[ $item_key ][ WC_CS_PREFIX . 'repayment' ] = array(
					'credits' => _wc_cs()->dashboard->get_credits()->get_id(),
					'amount'  => _wc_cs()->dashboard->get_credits()->get_last_billed_amount( 'edit' )
						) ;
				return ;
			}
		}
	}

	public function add_notice() {
		if ( $this->in_progress() ) {
			wc_print_notice( esc_html( get_option( WC_CS_PREFIX . 'complete_repayment_message' ) ), 'success' ) ;
		}
	}

	public function set_cart_total() {

		foreach ( WC()->cart->cart_contents as $cart_item ) {
			if ( isset( $cart_item[ WC_CS_PREFIX . 'repayment' ] ) && is_numeric( $cart_item[ WC_CS_PREFIX . 'repayment' ][ 'amount' ] ) ) {
				$cart_item[ 'data' ]->set_price( $cart_item[ WC_CS_PREFIX . 'repayment' ][ 'amount' ] ) ;
			}
		}
	}

	public function add_order_item_meta( $item, $cart_item_key, $cart_item ) {
		if ( empty( $cart_item[ WC_CS_PREFIX . 'repayment' ] ) ) {
			return ;
		}

		$item->delete_meta_data( WC_CS_PREFIX . 'repayment' ) ;
		$item->add_meta_data( WC_CS_PREFIX . 'repayment', $cart_item[ WC_CS_PREFIX . 'repayment' ] ) ;
		$item->save() ;
	}

}
