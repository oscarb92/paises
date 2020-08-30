<?php

defined( 'ABSPATH' ) || exit ;

if ( ! class_exists( 'WC_CS_Email_Credit_Application_Active', false ) ) {

	/**
	 * Credit Application Active Email.
	 * 
	 * An email will be sent to the user when the credit application is active.
	 * 
	 * @class WC_CS_Email_Credit_Application_Active
	 * @package class
	 * @extends WC_CS_Abstract_Email
	 */
	class WC_CS_Email_Credit_Application_Active extends WC_CS_Abstract_Email {

		/**
		 * Constructor.
		 */
		public function __construct() {
			$this->id             = WC_CS_PREFIX . 'credit_app_active' ;
			$this->customer_email = true ;
			$this->title          = __( 'Credit Application Active', 'credits-for-woocommerce' ) ;
			$this->description    = __( 'Credit Application Active emails are sent to customers when their submitted request is approved by the admin', 'credits-for-woocommerce' ) ;

			$this->template_html  = 'emails/credit-app-active.php' ;
			$this->template_plain = 'emails/plain/credit-app-active.php' ;

			$this->subject = __( '[{site_title}] - Credit Application Active', 'credits-for-woocommerce' ) ;
			$this->heading = __( 'Credit Application Active', 'credits-for-woocommerce' ) ;

			// Triggers for this email.
			add_action( 'wc_cs_credits_status_active_notification', array( $this, 'trigger' ) ) ;

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

return new WC_CS_Email_Credit_Application_Active() ;
