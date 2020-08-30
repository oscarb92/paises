<?php

defined( 'ABSPATH' ) || exit ;

if ( ! class_exists( 'WC_CS_Email_Funds_Addition_Success', false ) ) {

	/**
	 * Admin Funds Addition Success Email.
	 * 
	 * An email will be sent to the admin when the funds are added.
	 * 
	 * @class WC_CS_Email_Funds_Addition_Success
	 * @package class
	 * @extends WC_CS_Abstract_Email
	 */
	class WC_CS_Email_Funds_Addition_Success extends WC_CS_Abstract_Email {

		/**
		 * Constructor.
		 * 
		 */
		public function __construct() {
			$this->id          = WC_CS_PREFIX . 'funds_addition_success' ;
			$this->title       = __( 'Funds Addition Success - Admin', 'credits-for-woocommerce' ) ;
			$this->description = __( 'Funds Addition Success - Admin emails are sent to chosen recipient(s) when funds are added successfully to admin account', 'credits-for-woocommerce' ) ;

			$this->template_html  = 'emails/funds-addition-success.php' ;
			$this->template_plain = 'emails/plain/funds-addition-success.php' ;
			$this->placeholders   = array(
				'{order_date}'   => '',
				'{order_number}' => '',
					) ;

			$this->subject = __( '[{site_title}] - Funds Addition Success', 'credits-for-woocommerce' ) ;
			$this->heading = __( 'Funds Addition Success', 'credits-for-woocommerce' ) ;

			$this->supports = array( 'recipient' ) ;

			// Triggers for this email.
			add_action( 'wc_cs_admin_funds_added_notification', array( $this, 'trigger' ), 10, 2 ) ;

			// Call parent constructor
			parent::__construct() ;
		}

		/**
		 * Get content args.
		 *
		 * @return array
		 */
		public function get_content_args() {
			$content_args                = parent::get_content_args() ;
			$content_args[ 'funds_txn' ] = $this->funds_txn ;

			return $content_args ;
		}

		/**
		 * Trigger the sending of this email.
		 *
		 * @param WC_CS_Admin_Funds_Transaction $funds_txn
		 * @param WC_Order $order
		 */
		public function trigger( $funds_txn, $order ) {
			$this->object                           = $order ;
			$this->funds_txn                        = $funds_txn ;
			$this->placeholders[ '{order_date}' ]   = wc_format_datetime( $this->object->get_date_created() ) ;
			$this->placeholders[ '{order_number}' ] = $this->object->get_order_number() ;

			$funds_added_user = get_user_by( 'ID', absint( get_option( WC_CS_PREFIX . 'get_funds_addition_user' ) ) ) ;

			if ( $funds_added_user ) {
				$this->recipient = $funds_added_user->user_email ;
			}

			$this->maybe_trigger() ;
		}

	}

}

return new WC_CS_Email_Funds_Addition_Success() ;
