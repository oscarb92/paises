<?php

defined( 'ABSPATH' ) || exit ;

if ( ! class_exists( 'WC_CS_Email_User_Funds_Credited', false ) ) {

	/**
	 * User Funds Credited Email.
	 * 
	 * An email will be sent to the user when the amount is credited to the user.
	 * 
	 * @class WC_CS_Email_User_Funds_Credited
	 * @package class
	 * @extends WC_CS_Abstract_Email
	 */
	class WC_CS_Email_User_Funds_Credited extends WC_CS_Abstract_Email {

		/**
		 * Constructor.
		 * 
		 */
		public function __construct() {
			$this->id             = WC_CS_PREFIX . 'user_funds_credited' ;
			$this->customer_email = true ;
			$this->title          = __( 'Funds Credited - User', 'credits-for-woocommerce' ) ;
			$this->description    = __( 'Funds Credited - User emails are sent to customers when any funds are credited to their account', 'credits-for-woocommerce' ) ;

			$this->template_html  = 'emails/user-funds-credited.php' ;
			$this->template_plain = 'emails/plain/user-funds-credited.php' ;

			$this->subject = __( '[{site_title}] - Credit Limit Funds Credited', 'credits-for-woocommerce' ) ;
			$this->heading = __( 'Credit Limit Funds Credited', 'credits-for-woocommerce' ) ;

			// Triggers for this email.
			add_action( 'wc_cs_credits_txn_amount_status_credited_notification', array( $this, 'trigger' ) ) ;

			// Call parent constructor
			parent::__construct() ;
		}

		/**
		 * Get content args.
		 *
		 * @return array
		 */
		public function get_content_args() {
			$content_args                    = parent::get_content_args() ;
			$content_args[ 'credits' ]       = $this->credits ;
			$content_args[ 'credits_txn' ]   = $this->credits_txn ;
			$content_args[ 'user_nicename' ] = stripslashes( $this->user->user_nicename ) ;
			$content_args[ 'user_login' ]    = stripslashes( $this->user->user_login ) ;

			return $content_args ;
		}

		/**
		 * Trigger the sending of this email.
		 *
		 * @param WC_CS_Credits_Transaction $credits_txn
		 */
		public function trigger( $credits_txn ) {
			$this->credits     = _wc_cs_get_credits( $credits_txn->get_credits_id() ) ;
			$this->credits_txn = $credits_txn ;
			$this->user        = new WP_User( $this->credits->get_user_id() ) ;
			$this->recipient   = $this->credits->get_user_email() ;

			$this->maybe_trigger() ;
		}

	}

}

return new WC_CS_Email_User_Funds_Credited() ;
