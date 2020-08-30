<?php

defined( 'ABSPATH' ) || exit ;

if ( ! class_exists( 'WC_CS_Email_Payment_Overdue', false ) ) {

	/**
	 * Payment Overdue Email.
	 * 
	 * An email will be sent to the user when he has overdue payment.
	 * 
	 * @class WC_CS_Email_Payment_Overdue
	 * @package class
	 * @extends WC_CS_Abstract_Email
	 */
	class WC_CS_Email_Payment_Overdue extends WC_CS_Abstract_Email {

		/**
		 * Constructor.
		 * 
		 */
		public function __construct() {
			$this->id             = WC_CS_PREFIX . 'payment_overdue' ;
			$this->customer_email = true ;
			$this->title          = __( 'Payment Overdue', 'credits-for-woocommerce' ) ;
			$this->description    = __( 'Payment Overdue emails are sent to customers if bill is not paid within the due date', 'credits-for-woocommerce' ) ;

			$this->template_html  = 'emails/payment-overdue.php' ;
			$this->template_plain = 'emails/plain/payment-overdue.php' ;

			$this->subject = __( '[{site_title}] - Payment Overdue', 'credits-for-woocommerce' ) ;
			$this->heading = __( 'Payment Overdue', 'credits-for-woocommerce' ) ;

			// Triggers for this email.
			add_action( 'wc_cs_credits_status_overdue_notification', array( $this, 'trigger' ) ) ;
			add_action( 'wc_cs_late_fee_charged_notification', array( $this, 'trigger' ), 10, 2 ) ;

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
			$content_args[ 'credits_txn' ]    = $this->credits_txn ;
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
		public function trigger( $credits, $credits_txn = null ) {
			$this->credits     = $credits ;
			$this->credits_txn = $credits_txn ;
			$this->user        = new WP_User( $credits->get_user_id() ) ;
			$this->recipient   = $credits->get_user_email() ;

			$this->maybe_trigger() ;
		}

	}

}

return new WC_CS_Email_Payment_Overdue() ;
