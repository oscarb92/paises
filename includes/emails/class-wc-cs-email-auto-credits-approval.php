<?php

defined( 'ABSPATH' ) || exit ;

if ( ! class_exists( 'WC_CS_Email_Auto_Credits_Approval', false ) ) {

	/**
	 * Auto Credits Approval Email.
	 * 
	 * An email will be sent to the user when their credits approved automatically based upon credit line rules.
	 * 
	 * @class WC_CS_Email_Auto_Credits_Approval
	 * @package class
	 * @extends WC_CS_Abstract_Email
	 */
	class WC_CS_Email_Auto_Credits_Approval extends WC_CS_Abstract_Email {

		/**
		 * Constructor.
		 */
		public function __construct() {
			$this->id             = WC_CS_PREFIX . 'auto_credits_approval' ;
			$this->customer_email = true ;
			$this->title          = __( 'Automatic Credits Approval', 'credits-for-woocommerce' ) ;
			$this->description    = __( 'Automatic Credits Approval emails are sent to customers when their credits approved automatically based upon credit line rules', 'credits-for-woocommerce' ) ;

			$this->template_html  = 'emails/auto-credits-approval.php' ;
			$this->template_plain = 'emails/plain/auto-credits-approval.php' ;

			$this->subject = __( '[{site_title}] - Credit Line Funds Approved', 'credits-for-woocommerce' ) ;
			$this->heading = __( 'Credit Line Funds Approved', 'credits-for-woocommerce' ) ;

			// Triggers for this email.
			add_action( 'wc_cs_credits_created_via_rule_and_activated_notification', array( $this, 'trigger' ) ) ;

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

return new WC_CS_Email_Auto_Credits_Approval() ;
