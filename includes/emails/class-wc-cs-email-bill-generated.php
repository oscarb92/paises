<?php

defined( 'ABSPATH' ) || exit ;

if ( ! class_exists( 'WC_CS_Email_Bill_Generated', false ) ) {

	/**
	 * Bill Generated Email.
	 * 
	 * An email will be sent to the user when the bill is generated.
	 * 
	 * @class WC_CS_Email_Bill_Generated
	 * @package Class
	 * @extends WC_CS_Abstract_Email
	 */
	class WC_CS_Email_Bill_Generated extends WC_CS_Abstract_Email {

		/**
		 * Constructor.
		 * 
		 */
		public function __construct() {
			$this->id             = WC_CS_PREFIX . 'bill_generated' ;
			$this->customer_email = true ;
			$this->title          = __( 'Bill Generated', 'credits-for-woocommerce' ) ;
			$this->description    = __( 'Bill Generated emails are sent to customers when bill is generated for the user', 'credits-for-woocommerce' ) ;

			$this->template_html  = 'emails/bill-generated.php' ;
			$this->template_plain = 'emails/plain/bill-generated.php' ;

			$this->subject = __( '[{site_title}] - Bill Generated', 'credits-for-woocommerce' ) ;
			$this->heading = __( 'Bill Generated', 'credits-for-woocommerce' ) ;

			// Triggers for this email.
			add_action( 'wc_cs_bill_generated_notification', array( $this, 'trigger' ), 10, 2 ) ;

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
			$content_args[ 'bill_statement' ] = $this->bill_statement ;
			$content_args[ 'user_nicename' ]  = stripslashes( $this->user->user_nicename ) ;
			$content_args[ 'user_login' ]     = stripslashes( $this->user->user_login ) ;

			return $content_args ;
		}

		/**
		 * Trigger the sending of this email.
		 *
		 * @param WC_CS_Credits $credits
		 * @param WC_CS_Bill_Statement $bill_statement
		 */
		public function trigger( $credits, $bill_statement ) {
			$this->credits        = $credits ;
			$this->bill_statement = $bill_statement ;
			$this->user           = new WP_User( $credits->get_user_id() ) ;
			$this->recipient      = $credits->get_user_email() ;

			$this->maybe_trigger() ;
		}

	}

}

return new WC_CS_Email_Bill_Generated() ;
