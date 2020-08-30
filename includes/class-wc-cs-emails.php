<?php

defined( 'ABSPATH' ) || exit ;

/**
 * Emails class.
 * 
 * @class WC_CS_Emails
 * @package Class
 */
class WC_CS_Emails {

	/**
	 * Email notification classes
	 *
	 * @var WC_Email[]
	 */
	protected $emails = array() ;

	/**
	 * Available email notification classes to load
	 * 
	 * @var WC_Email::id => WC_Email class
	 */
	protected $email_classes = array(
		'funds_addition_success'  => 'WC_CS_Email_Funds_Addition_Success',
		'funds_low'               => 'WC_CS_Email_Funds_Low',
		'credit_app_submitted'    => 'WC_CS_Email_Credit_Application_Submitted',
		'credit_app_under_review' => 'WC_CS_Email_Credit_Application_Under_Review',
		'credit_app_rejected'     => 'WC_CS_Email_Credit_Application_Rejected',
		'credit_app_active'       => 'WC_CS_Email_Credit_Application_Active',
		'auto_credits_approval'   => 'WC_CS_Email_Auto_Credits_Approval',
		'credit_limit_updated'    => 'WC_CS_Email_Credit_Limit_Updated',
		'account_on_hold'         => 'WC_CS_Email_Account_On_Hold',
		'payment_overdue'         => 'WC_CS_Email_Payment_Overdue',
		'bill_generated'          => 'WC_CS_Email_Bill_Generated',
		'payment_success'         => 'WC_CS_Email_Payment_Success',
		'user_funds_credited'     => 'WC_CS_Email_User_Funds_Credited',
		'user_funds_debited'      => 'WC_CS_Email_User_Funds_Debited',
		'payment_reminder'        => 'WC_CS_Email_Payment_Reminder',
			) ;

	/**
	 * The single instance of the class
	 *
	 * @var WC_CS_Emails
	 */
	protected static $_instance = null ;

	/**
	 * Main WC_CS_Emails Instance.
	 * Ensures only one instance of WC_CS_Emails is loaded or can be loaded.
	 * 
	 * @return WC_CS_Emails Main instance
	 */
	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self() ;
		}
		return self::$_instance ;
	}

	/**
	 * Init the email class hooks in all emails that can be sent.
	 */
	public function init() {
		add_filter( 'woocommerce_email_classes', array( $this, 'add_email_classes' ) ) ;
		add_filter( 'woocommerce_template_directory', array( $this, 'set_template_directory' ), 10, 2 ) ;
		add_action( 'admin_init', array( $this, 'hide_plain_text_template' ) ) ;

		self::init_notifications() ;
	}

	/**
	 * Hook in all our emails to notify.
	 */
	public static function init_notifications() {
		$email_actions = apply_filters( 'wc_cs_email_actions', array(
			'wc_cs_admin_funds_added',
			'wc_cs_admin_funds_low',
			'wc_cs_credits_created',
			'wc_cs_credits_created_via_rule_and_activated',
			'wc_cs_credits_status_active',
			'wc_cs_credits_status_rejected',
			'wc_cs_credits_status_on_hold',
			'wc_cs_credits_status_under_review',
			'wc_cs_credits_status_overdue',
			'wc_cs_credits_usage_limit_increased',
			'wc_cs_bill_generated',
			'wc_cs_payment_success',
			'wc_cs_credits_txn_amount_status_credited',
			'wc_cs_credits_txn_amount_status_debited',
			'wc_cs_remind_payment_due',
			'wc_cs_late_fee_charged'
				) ) ;

		foreach ( $email_actions as $action ) {
			add_action( $action, array( __CLASS__, 'send_notification' ), 10, 10 ) ;
		}
	}

	/**
	 * Init the WC mailer instance and call the notifications for the current filter.
	 *
	 * @param array $args Email args (default: []).
	 */
	public static function send_notification( $args = array() ) {
		try {
			WC()->mailer() ;
			$args = func_get_args() ;
			do_action_ref_array( current_filter() . '_notification', $args ) ;
		} catch ( Exception $e ) {
			return ;
		}
	}

	/**
	 * Load our email classes.
	 * 
	 * @param array $emails
	 */
	public function add_email_classes( $emails ) {
		if ( ! empty( $this->emails ) ) {
			return $emails + $this->emails ;
		}

		// Include email classes.
		include_once 'abstracts/abstract-wc-cs-email.php' ;

		foreach ( $this->email_classes as $id => $class ) {
			$file_name = 'class-' . strtolower( str_replace( '_', '-', $class ) ) ;
			$path      = WC_CS_DIR . "includes/emails/{$file_name}.php" ;

			if ( is_readable( $path ) ) {
				$this->emails[ $class ] = include( $path ) ;
			}
		}

		return $emails + $this->emails ;
	}

	/**
	 * Hide Template - Plain text
	 */
	public function hide_plain_text_template() {
		if ( ! isset( $_GET[ 'section' ] ) ) {
			return ;
		}

		WC()->mailer() ;

		if ( in_array( $_GET[ 'section' ], array_map( 'strtolower', array_keys( $this->emails ) ) ) ) {
			echo '<style>div.template_plain{display:none;}</style>' ;
		}
	}

	/**
	 * Set our email templates directory.
	 * 
	 * @param string $template_directory
	 * @param string $template
	 * @return string
	 */
	public function set_template_directory( $template_directory, $template ) {
		$templates = array_map( array( $this, 'get_template_name' ), array_keys( $this->email_classes ) ) ;

		foreach ( $templates as $name ) {
			if ( in_array( $template, array(
						"emails/{$name}.php",
						"emails/plain/{$name}.php",
					) )
			) {
				return untrailingslashit( WC_CS_BASENAME_DIR ) ;
			}
		}

		return $template_directory ;
	}

	/**
	 * Get the template name from email ID
	 */
	public function get_template_name( $id ) {
		return str_replace( '_', '-', $id ) ;
	}

	/**
	 * Are emails available ?
	 *
	 * @return WC_Email class
	 */
	public function available() {
		WC()->mailer() ;

		return ! empty( $this->emails ) ? true : false ;
	}

	/**
	 * Return the email class
	 *
	 * @param string $id
	 * @return null|WC_Email class name
	 */
	public function get_email_class( $id ) {
		$id = strtolower( $id ) ;

		if ( false !== stripos( $id, WC_CS_PREFIX ) ) {
			$id = ltrim( $id, WC_CS_PREFIX ) ;
		}

		return isset( $this->email_classes[ $id ] ) ? $this->email_classes[ $id ] : null ;
	}

	/**
	 * Return the emails
	 *
	 * @return WC_Email[]
	 */
	public function get_emails() {
		WC()->mailer() ;

		return $this->emails ;
	}

	/**
	 * Return the email
	 *
	 * @param string $id
	 * @return WC_Email
	 */
	public function get_email( $id ) {
		WC()->mailer() ;

		$class = $this->get_email_class( $id ) ;

		return isset( $this->emails[ $class ] ) ? $this->emails[ $class ] : null ;
	}

}
