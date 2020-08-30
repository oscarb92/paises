<?php

defined( 'ABSPATH' ) || exit ;

if ( ! class_exists( 'WC_CS_Email_Credit_Application_Under_Review', false ) ) {

	/**
	 * Credit Application Under Review Email.
	 * 
	 * An email will be sent to the user when the credit app is under review.
	 * 
	 * @class WC_CS_Email_Credit_Application_Under_Review
	 * @package class
	 * @extends WC_CS_Abstract_Email
	 */
	class WC_CS_Email_Credit_Application_Under_Review extends WC_CS_Abstract_Email {

		/**
		 * Constructor.
		 * 
		 */
		public function __construct() {
			$this->id             = WC_CS_PREFIX . 'credit_app_under_review' ;
			$this->customer_email = true ;
			$this->title          = __( 'Credit Application Under Review', 'credits-for-woocommerce' ) ;
			$this->description    = __( 'Credit Application Under Review emails are sent to customers when the admin updates the status of the credit application to "Under Review"', 'credits-for-woocommerce' ) ;

			$this->template_html  = 'emails/credit-app-under-review.php' ;
			$this->template_plain = 'emails/plain/credit-app-under-review.php' ;

			$this->subject = __( '[{site_title}] - Credit Application Under Review', 'credits-for-woocommerce' ) ;
			$this->heading = __( 'Credit Application Under Review', 'credits-for-woocommerce' ) ;

			// Triggers for this email.
			add_action( 'wc_cs_credits_status_under_review_notification', array( $this, 'trigger' ) ) ;

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

return new WC_CS_Email_Credit_Application_Under_Review() ;
