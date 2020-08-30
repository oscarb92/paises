<?php

defined( 'ABSPATH' ) || exit ;

if ( ! class_exists( 'WC_CS_Email_Credit_Limit_Updated', false ) ) {

	/**
	 * Credit Limit Updated Email.
	 * 
	 * An email will be sent to the user when the credit limit is updated based upon credit line rules.
	 * 
	 * @class WC_CS_Email_Credit_Limit_Updated
	 * @package class
	 * @extends WC_CS_Abstract_Email
	 */
	class WC_CS_Email_Credit_Limit_Updated extends WC_CS_Abstract_Email {

		/**
		 * Constructor.
		 */
		public function __construct() {
			$this->id             = WC_CS_PREFIX . 'credit_limit_updated' ;
			$this->customer_email = true ;
			$this->title          = __( 'Credit Limit Updated', 'credits-for-woocommerce' ) ;
			$this->description    = __( 'Credit Limit Updated emails are sent to customers when the credit limit is updated based upon credit line rules', 'credits-for-woocommerce' ) ;

			$this->template_html  = 'emails/credit-limit-updated.php' ;
			$this->template_plain = 'emails/plain/credit-limit-updated.php' ;

			$this->subject = __( '[{site_title}] - Credit Limit Updated', 'credits-for-woocommerce' ) ;
			$this->heading = __( 'Credit Line Funds Updated', 'credits-for-woocommerce' ) ;

			// Triggers for this email.
			add_action( 'wc_cs_credits_usage_limit_increased_notification', array( $this, 'trigger' ) ) ;
			add_action( 'wc_cs_credits_usage_limit_decreased_notification', array( $this, 'trigger' ) ) ;

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

return new WC_CS_Email_Credit_Limit_Updated() ;
