<?php

defined( 'ABSPATH' ) || exit ;

/**
 * Admin Funds Handler
 * 
 * @class WC_CS_Admin_Funds
 * @package Class
 */
class WC_CS_Admin_Funds {

	/**
	 * Retrieve the low funds threshold.
	 * 
	 * @var float 
	 */
	protected static $low_funds_threshold ;

	/**
	 * Get the Transaction.
	 * 
	 * @param WC_CS_Admin_Funds_Transaction $txn
	 * @param bool $wp_error
	 * @return \WC_CS_Admin_Funds_Transaction|boolean
	 */
	public static function get_txn( $txn, $wp_error = false ) {
		if ( ! $txn ) {
			return false ;
		}

		try {
			$txn = new WC_CS_Admin_Funds_Transaction( $txn ) ;
		} catch ( Exception $e ) {
			return $wp_error ? new WP_Error( 'error', $e->getMessage() ) : false ;
		}

		return $txn ;
	}

	/**
	 * Retrieve the low funds threshold.
	 * 
	 * @return float
	 */
	public static function get_low_funds_threshold() {
		if ( is_numeric( self::$low_funds_threshold ) ) {
			return self::$low_funds_threshold ;
		}

		self::$low_funds_threshold = wc_format_decimal( get_option( WC_CS_PREFIX . 'low_funds_threshold', '0' ), 2 ) ;
		return self::$low_funds_threshold ;
	}

	/**
	 * Retrieve the available funds.
	 * 
	 * @param string $context View or edit context.
	 * @return float
	 */
	public static function get_available_funds( $context = 'view' ) {
		$available_funds = wc_format_decimal( get_option( WC_CS_PREFIX . 'available_funds', '0' ), 2 ) ;

		if ( 'view' === $context ) {
			return wc_price( $available_funds ) ;
		}

		return $available_funds ;
	}

	/**
	 * Retrieve the total investment by Admin.
	 * 
	 * @param string $context View or edit context.
	 * @return float
	 */
	public static function get_total_investment( $context = 'view' ) {
		$total_investment = wc_format_decimal( get_option( WC_CS_PREFIX . 'total_investment', '0' ), 2 ) ;

		if ( 'view' === $context ) {
			return wc_price( $total_investment ) ;
		}

		return $total_investment ;
	}

	/**
	 * Credit the funds.
	 * 
	 * @param float $amount
	 */
	public static function credit( $amount ) {
		if ( ! is_numeric( $amount ) ) {
			return null ;
		}

		$available_funds = self::get_available_funds( 'edit' ) ;
		$credit_amount   = wc_format_decimal( wp_unslash( $amount ), 2 ) ;

		if ( $credit_amount < 0 ) {
			$credit_amount *= -1 ;
		}

		update_option( WC_CS_PREFIX . 'available_funds', ( $available_funds + $credit_amount ) ) ;
	}

	/**
	 * Debit the funds.
	 * 
	 * @param float $amount
	 */
	public static function debit( $amount ) {
		if ( ! is_numeric( $amount ) ) {
			return null ;
		}

		$available_funds = self::get_available_funds( 'edit' ) ;
		$debit_amount    = wc_format_decimal( wp_unslash( $amount ), 2 ) ;

		update_option( WC_CS_PREFIX . 'available_funds', ( $available_funds - $debit_amount ) ) ;
	}

	/**
	 * Add the funds to record the total investment done by Admin.
	 * 
	 * @param float $amount
	 */
	public static function add_investment( $amount ) {
		if ( ! is_numeric( $amount ) ) {
			return null ;
		}

		$total_investment = self::get_total_investment( 'edit' ) ;
		$new_investment   = wc_format_decimal( wp_unslash( $amount ), 2 ) ;

		update_option( WC_CS_PREFIX . 'total_investment', ( $total_investment + $new_investment ) ) ;
	}

	/**
	 * Create the transaction.
	 * 
	 * @param array $args
	 * @return \WC_CS_Admin_Funds_Transaction
	 */
	public static function create_txn( $args = array() ) {
		$args = wp_parse_args( $args, array(
			'id'       => 0,
			'user_id'  => 1,
			'status'   => 'publish',
			'type'     => '',
			'activity' => __( 'Unknown', 'credits-for-woocommerce' ),
			'balance'  => self::get_available_funds( 'edit' ),
			'key'      => _wc_cs_generate_key( 'aft_' ),
				) ) ;

		do_action( 'wc_cs_before_funds_txn_created', $args ) ;

		$txn = new WC_CS_Admin_Funds_Transaction( $args[ 'id' ] ) ;

		unset( $args[ 'id' ] ) ;
		$txn->set_props( $args ) ;

		$result = $txn->save() ;

		if ( is_wp_error( $result ) ) {
			return $result ;
		}

		if ( $txn->get_balance( 'edit' ) <= self::get_low_funds_threshold() ) {
			do_action( 'wc_cs_admin_funds_low', $txn ) ;
		}

		do_action( 'wc_cs_funds_txn_created', $txn, $args ) ;

		return $txn ;
	}

}
