<?php

defined( 'ABSPATH' ) || exit ;

/**
 * Before Approval.
 * 
 * @class WC_CS_Meta_Box_Before_Approval
 * @package Class
 */
class WC_CS_Meta_Box_Before_Approval {

	/**
	 * Populate data.
	 * 
	 * @return array
	 */
	public static function populate_data() {
		$data                              = array() ;
		$data[ 'selected_billing_day' ]    = absint( get_option( WC_CS_PREFIX . 'get_billing_day_of_month' ) ) ;
		$data[ 'selected_due_day' ]        = absint( get_option( WC_CS_PREFIX . 'get_due_day_of_month' ) ) ;
		$data[ 'repayment_month' ]         = get_option( WC_CS_PREFIX . 'get_repayment_month' ) ;
		$data[ 'due_days_excluded' ]       = array( $data[ 'selected_billing_day' ] ) ;
		$data[ 'billing_days_excluded' ]   = array( $data[ 'selected_due_day' ] ) ;
		$data[ 'use_global_due_date' ]     = 'yes' ;
		$data[ 'use_global_billing_date' ] = 'yes' ;

		if ( 'next-month' === $data[ 'repayment_month' ] ) {
			$data[ 'due_start_day_in_month' ] = 1 ;
			$data[ 'due_days_in_month' ]      = $data[ 'selected_billing_day' ] - 1 ;
		} else {
			$data[ 'due_start_day_in_month' ] = 1 + $data[ 'selected_billing_day' ] ;
			$data[ 'due_days_in_month' ]      = 28 ;
		}

		return $data ;
	}

	/**
	 * Output the metabox.
	 *
	 * @param WP_Post $post
	 */
	public static function output( $post ) {
		global $post, $thecredits ;

		if ( ! is_object( $thecredits ) ) {
			$thecredits = _wc_cs_get_credits( $post->ID ) ;
		}

		$credits      = $thecredits ;
		$credits_post = $post ;

		extract( self::populate_data() ) ;

		include 'views/html-before-approval.php' ;
	}

}
