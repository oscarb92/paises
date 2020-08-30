<?php

defined( 'ABSPATH' ) || exit ;

/**
 * Virtual Funds Transaction Data Store CPT
 * 
 * @class WC_CS_Virtual_Funds_Transaction_Data_Store_CPT
 * @package Class
 */
class WC_CS_Virtual_Funds_Transaction_Data_Store_CPT extends Abstract_WC_CS_Transaction_Data_Store_CPT {

	/**
	 * Get the status to save to the post object.
	 *
	 * @param  WC_CS_Virtual_Funds_Transaction $txn
	 * @return string
	 */
	protected function get_post_status( $txn ) {
		$post_status = parent::get_post_status( $txn ) ;

		if ( ! $post_status ) {
			$post_status = apply_filters( 'wc_cs_default_virtual_funds_transaction_status', 'publish' ) ;
		}

		return $post_status ;
	}

	/**
	 * Get the title to save to the post object.
	 *
	 * @param  object $txn
	 * @return string
	 */
	protected function get_post_title( $txn ) {
		return esc_html__( 'Virtual Funds Transaction', 'credits-for-woocommerce' ) ;
	}

}
