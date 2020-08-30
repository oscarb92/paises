<?php

defined( 'ABSPATH' ) || exit ;

if ( ! class_exists( 'WC_CS_Email_Payment_Success', false ) ) {

	/**
	 * Payment Success Email.
	 * 
	 * An email will be sent to the user when the due amount paid success.
	 * 
	 * @class WC_CS_Email_Payment_Success
	 * @package class
	 * @extends WC_CS_Abstract_Email
	 */
	class WC_CS_Email_Payment_Success extends WC_CS_Abstract_Email {

		/**
		 * Constructor.
		 * 
		 */
		public function __construct() {
			$this->id             = WC_CS_PREFIX . 'payment_success' ;
			$this->customer_email = true ;
			$this->title          = __( 'Payment Success', 'credits-for-woocommerce' ) ;
			$this->description    = __( 'Payment Success emails are sent to customers when the user pays their bill', 'credits-for-woocommerce' ) ;

			$this->template_html  = 'emails/payment-success.php' ;
			$this->template_plain = 'emails/plain/payment-success.php' ;
			$this->placeholders   = array(
				'{order_date}'   => '',
				'{order_number}' => '',
					) ;

			$this->subject = __( '[{site_title}] - Payment Success', 'credits-for-woocommerce' ) ;
			$this->heading = __( 'Payment Success', 'credits-for-woocommerce' ) ;

			// Triggers for this email.
			add_action( 'wc_cs_payment_success_notification', array( $this, 'trigger' ), 10, 3 ) ;

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
		 * @param WC_CS_Credits $credits
		 * @param WC_Order $order
		 */
		public function trigger( $credits_txn, $credits, $order ) {
			$this->object                           = $order ;
			$this->credits                          = $credits ;
			$this->credits_txn                      = $credits_txn ;
			$this->user                             = new WP_User( $credits->get_user_id() ) ;
			$this->recipient                        = $credits->get_user_email() ;
			$this->placeholders[ '{order_date}' ]   = wc_format_datetime( $this->object->get_date_created() ) ;
			$this->placeholders[ '{order_number}' ] = $this->object->get_order_number() ;

			$this->maybe_trigger() ;
		}

	}

}

return new WC_CS_Email_Payment_Success() ;

