<?php

defined( 'ABSPATH' ) || exit ;

if ( ! class_exists( 'WC_CS_Email_Credit_Application_Rejected', false ) ) {

	/**
	 * Credit Application Rejected Email.
	 * 
	 * An email will be sent to the user when the credit application is rejected.
	 * 
	 * @class WC_CS_Email_Credit_Application_Rejected
	 * @package class
	 * @extends WC_CS_Abstract_Email
	 */
	class WC_CS_Email_Credit_Application_Rejected extends WC_CS_Abstract_Email {

		/**
		 * Constructor.
		 * 
		 */
		public function __construct() {
			$this->id             = WC_CS_PREFIX . 'credit_app_rejected' ;
			$this->customer_email = true ;
			$this->title          = __( 'Credit Application Rejected', 'credits-for-woocommerce' ) ;
			$this->description    = __( 'Credit Application Rejected emails are sent to customers when their submitted request is rejected by the admin', 'credits-for-woocommerce' ) ;

			$this->template_html  = 'emails/credit-app-rejected.php' ;
			$this->template_plain = 'emails/plain/credit-app-rejected.php' ;

			$this->subject = __( '[{site_title}] - Credit Application Rejected', 'credits-for-woocommerce' ) ;
			$this->heading = __( 'Credit Application Rejected', 'credits-for-woocommerce' ) ;

			// Triggers for this email.
			add_action( 'wc_cs_credits_status_rejected_notification', array( $this, 'trigger' ) ) ;

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

return new WC_CS_Email_Credit_Application_Rejected() ;
