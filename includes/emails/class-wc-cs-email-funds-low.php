<?php

defined( 'ABSPATH' ) || exit ;

if ( ! class_exists( 'WC_CS_Email_Funds_Low', false ) ) {

	/**
	 * Funds Low Email.
	 * 
	 * An email will be sent to the admin when the funds are low.
	 * 
	 * @class WC_CS_Email_Funds_Low
	 * @package class
	 * @extends WC_CS_Abstract_Email
	 */
	class WC_CS_Email_Funds_Low extends WC_CS_Abstract_Email {

		/**
		 * Constructor.
		 * 
		 */
		public function __construct() {
			$this->id          = WC_CS_PREFIX . 'funds_low' ;
			$this->title       = __( 'Funds Low - Admin', 'credits-for-woocommerce' ) ;
			$this->description = __( 'Funds Low - Admin emails are sent to chosen recipient(s) when available funds are below the given threshold', 'credits-for-woocommerce' ) ;

			$this->template_html  = 'emails/funds-low.php' ;
			$this->template_plain = 'emails/plain/funds-low.php' ;

			$this->subject = __( '[{site_title}] - Admin Funds Low', 'credits-for-woocommerce' ) ;
			$this->heading = __( 'Admin Funds Low', 'credits-for-woocommerce' ) ;

			$this->supports = array( 'recipient' ) ;

			// Triggers for this email.
			add_action( 'wc_cs_admin_funds_low_notification', array( $this, 'trigger' ) ) ;

			// Call parent constructor
			parent::__construct() ;
		}

		/**
		 * Get content args.
		 *
		 * @return array
		 */
		public function get_content_args() {
			$content_args                = parent::get_content_args() ;
			$content_args[ 'funds_txn' ] = $this->funds_txn ;

			return $content_args ;
		}

		/**
		 * Trigger the sending of this email.
		 *
		 * @param WC_CS_Admin_Funds_Transaction $funds_txn
		 */
		public function trigger( $funds_txn ) {
			$this->funds_txn  = $funds_txn ;
			$funds_added_user = get_user_by( 'ID', absint( get_option( WC_CS_PREFIX . 'get_funds_addition_user' ) ) ) ;

			if ( $funds_added_user ) {
				$this->recipient = $funds_added_user->user_email ;
			}

			$this->maybe_trigger() ;
		}

	}

}

return new WC_CS_Email_Funds_Low() ;
