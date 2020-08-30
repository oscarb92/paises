<?php

defined( 'ABSPATH' ) || exit ;

/**
 * Virtual Funds Handler
 * 
 * @class WC_CS_Virtual_Funds
 * @package Class
 */
class WC_CS_Virtual_Funds {

	/**
	 * Get the Transaction.
	 * 
	 * @param WC_CS_Virtual_Funds_Transaction $txn
	 * @param bool $wp_error
	 * @return boolean|\WC_CS_Virtual_Funds_Transaction
	 */
	public static function get_txn( $txn, $wp_error = false ) {
		if ( ! $txn ) {
			return false ;
		}

		try {
			$txn = new WC_CS_Virtual_Funds_Transaction( $txn ) ;
		} catch ( Exception $e ) {
			return $wp_error ? new WP_Error( 'error', $e->getMessage() ) : false ;
		}

		return $txn ;
	}

	/**
	 * Create the transaction.
	 * 
	 * @param array $args
	 * @return \WC_CS_Virtual_Funds_Transaction
	 */
	public static function create_txn( $args = array() ) {
		$args = wp_parse_args( $args, array(
			'id'       => 0,
			'user_id'  => 1,
			'status'   => 'publish',
			'type'     => '',
			'activity' => __( 'Unknown', 'credits-for-woocommerce' ),
			'key'      => _wc_cs_generate_key( 'vft_' ),
				) ) ;

		do_action( 'wc_cs_before_virtual_funds_txn_created', $args ) ;

		$txn = new WC_CS_Virtual_Funds_Transaction( $args[ 'id' ] ) ;

		unset( $args[ 'id' ] ) ;
		$txn->set_props( $args ) ;

		$result = $txn->save() ;

		if ( is_wp_error( $result ) ) {
			return $result ;
		}

		do_action( 'wc_cs_virtual_funds_txn_created', $txn, $args ) ;

		return $txn ;
	}

}
