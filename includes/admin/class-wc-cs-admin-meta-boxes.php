<?php

defined( 'ABSPATH' ) || exit ;

/**
 * Handle Admin metaboxes.
 * 
 * @class WC_CS_Admin_Metaboxes
 * @package Class
 */
class WC_CS_Admin_Metaboxes {

	/**
	 * WC_CS_Admin_Metaboxes constructor.
	 */
	public function __construct() {
		add_action( 'add_meta_boxes', array( $this, 'add_meta_boxes' ) ) ;
		add_action( 'add_meta_boxes', array( $this, 'remove_meta_boxes' ) ) ;
		add_action( 'admin_init', array( $this, 'check_user_history' ) ) ;
	}

	/**
	 * Add Metaboxes.
	 */
	public function add_meta_boxes() {
		add_meta_box( WC_CS_PREFIX . 'basic_details', __( 'Basic Details', 'credits-for-woocommerce' ), 'WC_CS_Meta_Box_Basic_Details::output', 'wc_cs_credits', 'normal', 'high' ) ;
		add_meta_box( WC_CS_PREFIX . 'site_activity', __( 'Site Activity', 'credits-for-woocommerce' ), 'WC_CS_Meta_Box_Site_Activity::output', 'wc_cs_credits', 'normal', 'low' ) ;
		add_meta_box( WC_CS_PREFIX . 'view_statements', __( 'View Statements', 'credits-for-woocommerce' ), 'WC_CS_Meta_Box_View_Statements::output', 'wc_cs_credits', 'side', 'high' ) ;

		if ( array_key_exists( get_post_status(), _wc_cs_get_after_approval_credits_statuses() ) ) {
			add_meta_box( WC_CS_PREFIX . 'after_approval', __( 'Credits', 'credits-for-woocommerce' ), 'WC_CS_Meta_Box_After_Approval::output', 'wc_cs_credits', 'normal', 'low' ) ;
		} else {
			add_meta_box( WC_CS_PREFIX . 'before_approval', __( 'Credits', 'credits-for-woocommerce' ), 'WC_CS_Meta_Box_Before_Approval::output', 'wc_cs_credits', 'normal', 'low' ) ;
		}

		add_meta_box( WC_CS_PREFIX . 'txns', __( 'Transactions', 'credits-for-woocommerce' ), 'WC_CS_Meta_Box_Transactions::output', 'wc_cs_credits', 'normal', 'low' ) ;
	}

	/**
	 * Remove Metaboxes.
	 */
	public function remove_meta_boxes() {
		remove_meta_box( 'commentsdiv', 'wc_cs_credits', 'normal' ) ;
		remove_meta_box( 'submitdiv', 'wc_cs_credits', 'side' ) ;
	}

	/**
	 * Check the user history.
	 */
	public static function check_user_history() {
		if ( ! isset( $_GET[ 'action' ] ) || ! isset( $_GET[ 'post' ] ) ) {
			return ;
		}

		if ( 'edit' === $_GET[ 'action' ] ) {
			$post_id = absint( wp_unslash( $_GET[ 'post' ] ) ) ;
			$credits = _wc_cs_get_credits( $post_id ) ;

			if ( $credits && 0 === $credits->get_total_orders_placed_by_user( 'edit' ) ) {
				$credits->read_user_history() ;
			}
		}
	}

}

new WC_CS_Admin_Metaboxes() ;
