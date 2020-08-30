<?php

defined( 'ABSPATH' ) || exit ;

if ( ! class_exists( 'WC_CS_Email_Account_On_Hold', false ) ) {

	/**
	 * Account On-hold Email.
	 * 
	 * An email will be sent to the user when the status has been put on-hold.
	 * 
	 * @class WC_CS_Email_Account_On_Hold
	 * @package class
	 * @extends WC_CS_Abstract_Email
	 */
	class WC_CS_Email_Account_On_Hold extends WC_CS_Abstract_Email {

		/**
		 * Constructor.
		 * 
		 */
		public function __construct() {
			$this->id             = WC_CS_PREFIX . 'account_on_hold' ;
			$this->customer_email = true ;
			$this->title          = __( 'Account On-Hold', 'credits-for-woocommerce' ) ;
			$this->description    = __( 'Account On-hold emails are sent to customers whenever users credit account has been put on-hold', 'credits-for-woocommerce' ) ;

			$this->template_html  = 'emails/account-on-hold.php' ;
			$this->template_plain = 'emails/plain/account-on-hold.php' ;

			$this->subject = __( '[{site_title}] - Credit Limit On-Hold by Admin', 'credits-for-woocommerce' ) ;
			$this->heading = __( 'Credit Limit On-Hold by Admin', 'credits-for-woocommerce' ) ;

			// Triggers for this email.
			add_action( 'wc_cs_credits_status_on_hold_notification', array( $this, 'trigger' ) ) ;

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
			$content_args[ 'user_nicename' ] = stripslashes( $this->user->user_nicename ) ;
			$content_args[ 'user_login' ]    = stripslashes( $this->user->user_login ) ;

			return $content_args ;
		}

		/**
		 * Trigger the sending of this email.
		 *
		 * @param WC_CS_Credits $credits
		 */
		public function trigger( $credits ) {
			$this->credits   = $credits ;
			$this->user      = new WP_User( $credits->get_user_id() ) ;
			$this->recipient = $credits->get_user_email() ;

			$this->maybe_trigger() ;
		}

	}

}

return new WC_CS_Email_Account_On_Hold() ;

