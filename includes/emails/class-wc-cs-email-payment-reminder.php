<?php

defined( 'ABSPATH' ) || exit ;

if ( ! class_exists( 'WC_CS_Email_Payment_Reminder', false ) ) {

	/**
	 * Payment Reminder Email.
	 * 
	 * An email will be sent to the user to remind about the payment due.
	 * 
	 * @class WC_CS_Email_Payment_Reminder
	 * @package class
	 * @extends WC_CS_Abstract_Email
	 */
	class WC_CS_Email_Payment_Reminder extends WC_CS_Abstract_Email {

		/**
		 * Constructor.
		 * 
		 */
		public function __construct() {
			$this->id             = WC_CS_PREFIX . 'payment_reminder' ;
			$this->customer_email = true ;
			$this->title          = __( 'Payment Reminder', 'credits-for-woocommerce' ) ;
			$this->description    = __( 'Payment Reminder emails are sent to customers before due date', 'credits-for-woocommerce' ) ;

			$this->template_html  = 'emails/payment-reminder.php' ;
			$this->template_plain = 'emails/plain/payment-reminder.php' ;

			$this->subject = __( '[{site_title}] - Payment Reminder', 'credits-for-woocommerce' ) ;
			$this->heading = __( 'Payment Reminder', 'credits-for-woocommerce' ) ;

			// Triggers for this email.
			add_action( 'wc_cs_remind_payment_due_notification', array( $this, 'trigger' ) ) ;

			// Call parent constructor
			parent::__construct() ;
		}

		/**
		 * Get content args.
		 *
		 * @return array
		 */
		public function get_content_args() {
			$content_args                     = parent::get_content_args() ;
			$content_args[ 'credits' ]        = $this->credits ;
			$content_args[ 'bill_statement' ] = $this->credits->get_bill_statement_by_date( $this->credits->get_last_billed_date() ) ;
			$content_args[ 'user_nicename' ]  = stripslashes( $this->user->user_nicename ) ;
			$content_args[ 'user_login' ]     = stripslashes( $this->user->user_login ) ;

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

return new WC_CS_Email_Payment_Reminder() ;
