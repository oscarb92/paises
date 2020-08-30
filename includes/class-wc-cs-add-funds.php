<?php

defined( 'ABSPATH' ) || exit ;

/**
 * Adding Funds to Admin Account Handler
 * 
 * @class WC_CS_Add_Funds
 * @package Class
 */
class WC_CS_Add_Funds {

	/**
	 * The single instance of the class.
	 */
	protected static $instance = null ;

	protected static $current_user_is_eligible ;

	protected static $product_to_add_funds ;

	protected static $min_funds_to_add ;

	protected static $max_funds_to_add ;

	/**
	 * Create instance for WC_CS_Add_Funds.
	 */
	public static function instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self() ;
		}
		return self::$instance ;
	}

	/**
	 * Init WC_CS_Add_Funds.
	 */
	public function init() {
		add_filter( 'woocommerce_add_to_cart_validation', array( $this, 'validate_add_to_cart' ), 99, 4 ) ;
		add_filter( 'woocommerce_coupon_is_valid', array( $this, 'restrict_coupon_usage' ), 99 ) ;
		add_filter( 'woocommerce_is_sold_individually', array( $this, 'set_as_sold_individually' ), 99, 2 ) ;
		add_filter( 'woocommerce_product_is_visible', array( $this, 'set_as_invisible' ), 99, 2 ) ;
		add_filter( 'woocommerce_cart_needs_shipping', array( $this, 'do_not_allow_shipping' ), 99 ) ;
		add_action( 'woocommerce_before_calculate_totals', array( $this, 'check_cart_items' ) ) ;
		add_action( 'woocommerce_before_calculate_totals', array( $this, 'set_cart_total' ), 99 ) ;
		add_filter( 'woocommerce_cart_total', array( $this, 'get_funds_to_add_html' ), 99, 2 ) ;
		add_action( 'woocommerce_checkout_create_order_line_item', array( $this, 'add_order_item_meta' ), 10, 3 ) ;

		add_action( 'woocommerce_before_cart_table', array( $this, 'add_notice' ) ) ;
		add_action( 'woocommerce_checkout_before_customer_details', array( $this, 'add_notice' ) ) ;
	}

	public function current_user_is_eligible() {
		if ( is_bool( self::$current_user_is_eligible ) ) {
			return self::$current_user_is_eligible ;
		}

		self::$current_user_is_eligible = false ;
		if ( is_user_logged_in() && get_current_user_id() === absint( get_option( WC_CS_PREFIX . 'get_funds_addition_user' ) ) ) {
			self::$current_user_is_eligible = true ;
		}
		return self::$current_user_is_eligible ;
	}

	public function validate_before_add( $amount ) {
		if ( ! is_numeric( $amount ) || $amount <= 0 ) {
			throw new Exception( esc_html( get_option( WC_CS_PREFIX . 'invalid_amount_to_add_funds_message' ) ) ) ;
		}

		$min_amt_to_add_funds = $this->get_min_to_add() ;
		$max_amt_to_add_funds = $this->get_max_to_add() ;

		if ( $min_amt_to_add_funds > 0 && $max_amt_to_add_funds > 0 && $amount < $min_amt_to_add_funds && $amount > $max_amt_to_add_funds ) {
			/* translators: 1: minimum amount to add funds 2: maxmimum amount to add funds */
			throw new Exception( str_replace( array( '[min_amnt_to_add_funds]', '[max_amnt_to_add_funds]' ), array( $min_amt_to_add_funds, $max_amt_to_add_funds ), get_option( WC_CS_PREFIX . 'funds_addition_range_message' ) ) ) ;
		} else if ( $min_amt_to_add_funds > 0 && $amount < $min_amt_to_add_funds ) {
			/* translators: 1: minimum amount to add funds */
			throw new Exception( str_replace( '[min_amnt_to_add_funds]', $min_amt_to_add_funds, get_option( WC_CS_PREFIX . 'min_amount_for_funds_addition_message' ) ) ) ;
		} else if ( $max_amt_to_add_funds > 0 && $amount > $max_amt_to_add_funds ) {
			/* translators: 1: maxmimum amount to add funds */
			throw new Exception( str_replace( '[max_amnt_to_add_funds]', $max_amt_to_add_funds, get_option( WC_CS_PREFIX . 'max_amount_for_funds_addition_message' ) ) ) ;
		}

		return true ;
	}

	public function in_progress() {
		if ( ! is_null( WC()->cart ) ) {
			foreach ( WC()->cart->cart_contents as $cart_item ) {
				if ( isset( $cart_item[ WC_CS_PREFIX . 'funds_addition' ] ) ) {
					return $cart_item[ WC_CS_PREFIX . 'funds_addition' ] ;
				}
			}
		}

		return null ;
	}

	public function get_product() {
		if ( is_numeric( self::$product_to_add_funds ) ) {
			return self::$product_to_add_funds ;
		}

		self::$product_to_add_funds = absint( get_option( WC_CS_PREFIX . 'get_selected_product_to_add_funds' ) ) ;
		return self::$product_to_add_funds ;
	}

	public function get_min_to_add() {
		if ( is_numeric( self::$min_funds_to_add ) ) {
			return self::$min_funds_to_add ;
		}

		self::$min_funds_to_add = wc_format_decimal( get_option( WC_CS_PREFIX . 'min_funds_to_add_per_txn' ), 2 ) ;
		return self::$min_funds_to_add ;
	}

	public function get_max_to_add() {
		if ( is_numeric( self::$max_funds_to_add ) ) {
			return self::$max_funds_to_add ;
		}

		self::$max_funds_to_add = wc_format_decimal( get_option( WC_CS_PREFIX . 'max_funds_to_add_per_txn' ), 2 ) ;
		return self::$max_funds_to_add ;
	}

	public function validate_add_to_cart( $bool, $product_id, $qty, $variation_id = '' ) {
		$add_cart_product = $variation_id ? $variation_id : $product_id ;

		if ( $add_cart_product != $this->get_product() && $this->in_progress() ) {
			wc_add_notice( esc_html( get_option( WC_CS_PREFIX . 'funds_addition_in_cart_message' ) ), 'error' ) ;
			return false ;
		}
		return $bool ;
	}

	public function restrict_coupon_usage( $bool ) {
		if ( $this->in_progress() ) {
			throw new Exception( esc_html( get_option( WC_CS_PREFIX . 'funds_addition_coupon_usage_message' ) ), 100 ) ;
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

		if ( $this->current_user_is_eligible() ) {
			return ;
		}

		WC()->cart->empty_cart() ;
	}

	public function add_notice() {
		if ( $this->in_progress() ) {
			wc_print_notice( esc_html( get_option( WC_CS_PREFIX . 'complete_funds_addition_message' ) ), 'success' ) ;
		}
	}

	public function set_cart_total() {

		foreach ( WC()->cart->cart_contents as $cart_item ) {
			if ( isset( $cart_item[ WC_CS_PREFIX . 'funds_addition' ] ) && is_numeric( $cart_item[ WC_CS_PREFIX . 'funds_addition' ][ 'amount' ] ) ) {
				$cart_item[ 'data' ]->set_price( $cart_item[ WC_CS_PREFIX . 'funds_addition' ][ 'amount' ] ) ;
			}
		}
	}

	public function get_funds_to_add_html( $total ) {
		$adding_funds = $this->in_progress() ;

		if ( $adding_funds ) {
			/* translators: 1: adding funds amount */
			$total = str_replace( '[funds_addition_amount]', wc_price( $adding_funds[ 'amount' ] ), get_option( WC_CS_PREFIX . 'funds_addition_amount_message' ) ) ;
		}

		return $total ;
	}

	public function add_order_item_meta( $item, $cart_item_key, $cart_item ) {
		if ( empty( $cart_item[ WC_CS_PREFIX . 'funds_addition' ] ) ) {
			return ;
		}

		$item->delete_meta_data( WC_CS_PREFIX . 'funds_addition' ) ;
		$item->add_meta_data( WC_CS_PREFIX . 'funds_addition', $cart_item[ WC_CS_PREFIX . 'funds_addition' ] ) ;
		$item->save() ;
	}

}
