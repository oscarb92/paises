<?php

defined( 'ABSPATH' ) || exit ;

/**
 * Abstract Credits
 * 
 * @class WC_CS_Abstract_Credits
 * @package Class
 */
abstract class WC_CS_Abstract_Credits extends WC_CS_Data {

	/**
	 * This is the name of this object type.
	 *
	 * @var string
	 */
	protected $object_type = 'wc_cs_credits' ;

	/**
	 * Which data store to load.
	 *
	 * @var string
	 */
	protected $data_store_name = 'credits-cpt' ;

	/**
	 * Core data for this object. Name value pairs (name + default value).
	 *
	 * @var array
	 */
	protected $data = array(
		'user_id'                     => 0,
		'status'                      => '',
		'date_created'                => '',
		'version'                     => '',
		'approved_credits'            => 0,
		'available_credits'           => 0,
		'approved_date'               => '',
		'billing_day'                 => 1,
		'due_day'                     => 20,
		'due_duration_by'             => 'this-month',
		'last_billed_status'          => '',
		'last_billed_date'            => '',
		'last_billed_due_date'        => '',
		'last_billed_amount'          => 0,
		'last_payment_date'           => '',
		'last_payment_order_id'       => 0,
		'unpaid_previous_bill_amount' => 0,
		'total_outstanding_amount'    => 0,
		'statements'                  => array(),
		'next_bill_date'              => '',
		'rule_applied'                => '',
		'created_via'                 => '',
		'type'                        => 'auto',
			) ;

	/**
	 * Construct.
	 */
	public function __construct( $credits = 0 ) {
		parent::__construct( $credits ) ;

		if ( is_numeric( $credits ) && $credits > 0 ) {
			$this->set_id( $credits ) ;
		} elseif ( $credits instanceof self ) {
			$this->set_id( $credits->get_id() ) ;
		} elseif ( ! empty( $credits->ID ) ) {
			$this->set_id( $credits->ID ) ;
		}

		$this->load_data_store() ;
		$this->maybe_read() ;
	}

	/**
	 * Load the data store for the credits.
	 */
	protected function load_data_store() {
		$this->data_store = new WC_CS_Credits_Data_Store_CPT() ;
	}

	/**
	 * Maybe read the data from the data store for the credits.
	 */
	protected function maybe_read() {
		if ( ! $this->data_store ) {
			return ;
		}

		if ( $this->get_id() > 0 ) {
			$this->data_store->read( $this ) ;
		}
	}

	/**
	 * Get all valid statuses for this credits.
	 *
	 * @return array Internal status keys e.g. WC_CS_PREFIX. 'pending'
	 */
	public function get_valid_statuses() {
		return array_keys( _wc_cs_get_credits_statuses() ) ;
	}

	/**
	 * Get the user ID.
	 *
	 * @param  string $context View or edit context.
	 * @return int
	 */
	public function get_user_id( $context = 'view' ) {
		return $this->get_prop( 'user_id', $context ) ;
	}

	/**
	 * Return the credits status without WC_CS_PREFIX internal prefix.
	 *
	 * @param  string $context View or edit context.
	 * @return string
	 */
	public function get_status( $context = 'view' ) {
		$status = $this->get_prop( 'status', $context ) ;

		if ( 'view' === $context && empty( $status ) ) {
			// In view context, return the default status if no status has been set.
			$status = 'pending' ;
		}

		return $status ;
	}

	/**
	 * Get date created.
	 *
	 * @param  string $context View or edit context.
	 * @return string
	 */
	public function get_date_created( $context = 'view' ) {
		return $this->get_prop( 'date_created', $context ) ;
	}

	/**
	 * Get credits version.
	 *
	 * @param  string $context View or edit context.
	 * @return string
	 */
	public function get_version( $context = 'view' ) {
		return $this->get_prop( 'version', $context ) ;
	}

	/**
	 * Get approved credits.
	 *
	 * @param  string $context View or edit context.
	 * @return string
	 */
	public function get_approved_credits( $context = 'view' ) {
		$approved_credits = $this->get_prop( 'approved_credits', $context ) ;

		if ( 'view' === $context ) {
			return wc_price( $approved_credits ) ;
		}

		return $approved_credits ;
	}

	/**
	 * Get available credits.
	 *
	 * @param  string $context View or edit context.
	 * @return string
	 */
	public function get_available_credits( $context = 'view' ) {
		$available_credits = $this->get_prop( 'available_credits', $context ) ;

		if ( 'view' === $context ) {
			return wc_price( $available_credits ) ;
		}

		return $available_credits ;
	}

	/**
	 * Get credits approved date.
	 *
	 * @param  string $context View or edit context.
	 * @return string
	 */
	public function get_approved_date( $context = 'view' ) {
		return $this->get_prop( 'approved_date', $context ) ;
	}

	/**
	 * Get the day of billing to set the next billing date.
	 *
	 * @param  string $context View or edit context.
	 * @return int
	 */
	public function get_billing_day( $context = 'view' ) {
		return $this->get_prop( 'billing_day', $context ) ;
	}

	/**
	 * Get the day of due to set the due date.
	 *
	 * @param  string $context View or edit context.
	 * @return int
	 */
	public function get_due_day( $context = 'view' ) {
		return $this->get_prop( 'due_day', $context ) ;
	}

	/**
	 * Get the due duration gap to set the due date either by this-month|next-month.
	 *
	 * @param  string $context View or edit context.
	 * @return string
	 */
	public function get_due_duration_by( $context = 'view' ) {
		return $this->get_prop( 'due_duration_by', $context ) ;
	}

	/**
	 * Return the last billed status
	 *
	 * @param  string $context View or edit context.
	 * @return string
	 */
	public function get_last_billed_status( $context = 'view' ) {
		return $this->get_prop( 'last_billed_status', $context ) ;
	}

	/**
	 * Get last billed date.
	 *
	 * @param  string $context View or edit context.
	 * @return string
	 */
	public function get_last_billed_date( $context = 'view' ) {
		return $this->get_prop( 'last_billed_date', $context ) ;
	}

	/**
	 * Get last billed amount due.
	 *
	 * @param  string $context View or edit context.
	 * @return string
	 */
	public function get_last_billed_amount( $context = 'view' ) {
		$last_billed_amount = $this->get_prop( 'last_billed_amount', $context ) ;

		if ( 'view' === $context ) {
			return wc_price( $last_billed_amount ) ;
		}

		return $last_billed_amount ;
	}

	/**
	 * Get last billed due date.
	 *
	 * @param  string $context View or edit context.
	 * @return string
	 */
	public function get_last_billed_due_date( $context = 'view' ) {
		return $this->get_prop( 'last_billed_due_date', $context ) ;
	}

	/**
	 * Get statements.
	 *
	 * @param  string $context View or edit context.
	 * @return array
	 */
	public function get_statements( $context = 'view' ) {
		return $this->get_prop( 'statements', $context ) ;
	}

	/**
	 * Get last payment date.
	 *
	 * @param  string $context View or edit context.
	 * @return string
	 */
	public function get_last_payment_date( $context = 'view' ) {
		return $this->get_prop( 'last_payment_date', $context ) ;
	}

	/**
	 * Get the last payment order ID.
	 *
	 * @param  string $context View or edit context.
	 * @return int
	 */
	public function get_last_payment_order_id( $context = 'view' ) {
		return $this->get_prop( 'last_payment_order_id', $context ) ;
	}

	/**
	 * Get next bill date.
	 *
	 * @param  string $context View or edit context.
	 * @return string
	 */
	public function get_next_bill_date( $context = 'view' ) {
		return $this->get_prop( 'next_bill_date', $context ) ;
	}

	/**
	 * Get total outstanding amount.
	 *
	 * @param  string $context View or edit context.
	 * @return string
	 */
	public function get_total_outstanding_amount( $context = 'view' ) {
		$total_outstanding_amount = $this->get_prop( 'total_outstanding_amount', $context ) ;

		if ( 'view' === $context ) {
			return wc_price( $total_outstanding_amount ) ;
		}

		return $total_outstanding_amount ;
	}

	/**
	 * Get unpaid previous bill amount.
	 *
	 * @param  string $context View or edit context.
	 * @return string
	 */
	public function get_unpaid_previous_bill_amount( $context = 'view' ) {
		$unpaid_previous_bill_amount = $this->get_prop( 'unpaid_previous_bill_amount', $context ) ;

		if ( 'view' === $context ) {
			return wc_price( $unpaid_previous_bill_amount ) ;
		}

		return $unpaid_previous_bill_amount ;
	}

	/**
	 * Get rule applied.
	 *
	 * @param  string $context View or edit context.
	 * @return int
	 */
	public function get_rule_applied( $context = 'view' ) {
		return $this->get_prop( 'rule_applied', $context ) ;
	}

	/**
	 * Return the credits/credit line type.
	 *
	 * @param  string $context View or edit context.
	 * @return string
	 */
	public function get_type( $context = 'view' ) {
		return $this->get_prop( 'type', $context ) ;
	}

	/**
	 * Get created via.
	 *
	 * @param  string $context View or edit context.
	 * @return string
	 */
	public function get_created_via( $context = 'view' ) {
		return $this->get_prop( 'created_via', $context ) ;
	}

	/**
	 * Checks the credits status against a passed in status.
	 *
	 * @param array|string $status Status to check.
	 * @return bool
	 */
	public function has_status( $status ) {
		if ( is_array( $status ) ) {
			return in_array( $this->get_status(), $status ) || in_array( $this->get_status(), $status ) ;
		}

		return $status === $this->get_status() || $status === $this->get_status() ;
	}

	/**
	 * Set credits status.
	 *
	 * @param string $new_status Status to change the credits to. No internal WC_CS_PREFIX is required.
	 * @throws Exception may be thrown if value is invalid.
	 * @return array details of change
	 */
	public function set_status( $new_status ) {
		$old_status = $this->get_status() ;
		$new_status = WC_CS_PREFIX === substr( $new_status, 0, 7 ) ? substr( $new_status, 7 ) : $new_status ;

		if ( 'trash' !== $new_status && ! in_array( WC_CS_PREFIX . $new_status, $this->get_valid_statuses() ) ) {
			$new_status = 'pending' ;
		}

		$this->set_prop( 'status', $new_status ) ;

		return array(
			'from' => $old_status,
			'to'   => $new_status,
				) ;
	}

	/**
	 * Set the user ID.
	 *
	 * @param int $value
	 * @throws Exception may be thrown if value is invalid.
	 */
	public function set_user_id( $value ) {
		$this->set_prop( 'user_id', absint( $value ) ) ;
	}

	/**
	 * Set date created.
	 *
	 * @param  string $date based on WordPress site timezone.
	 * @throws Exception may be thrown if value is invalid.
	 */
	public function set_date_created( $date ) {
		$this->set_prop( 'date_created', $date ) ;
	}

	/**
	 * Set credits version.
	 *
	 * @param string $value Value to set.
	 * @throws Exception may be thrown if value is invalid.
	 */
	public function set_version( $value ) {
		$this->set_prop( 'version', $value ) ;
	}

	/**
	 * Set approved credits.
	 *
	 * @param  string|int|float $value
	 * @throws Exception may be thrown if value is invalid.
	 * @return array details of change
	 */
	public function set_approved_credits( $value ) {
		if ( ! is_numeric( $value ) ) {
			throw new Exception( esc_html__( 'Invalid approved credits.', 'credits-for-woocommerce' ) ) ;
		}

		$old_usage = $this->get_approved_credits( 'edit' ) ;
		$new_usage = wc_format_decimal( $value ) ;

		$this->set_prop( 'approved_credits', $new_usage ) ;

		return array(
			'old_usage' => $old_usage,
			'new_usage' => $new_usage,
				) ;
	}

	/**
	 * Set available credits.
	 *
	 * @param  string|int|float $value
	 * @throws Exception may be thrown if value is invalid.
	 * @return array details of change
	 */
	public function set_available_credits( $value ) {
		if ( ! is_numeric( $value ) ) {
			throw new Exception( esc_html__( 'Invalid available credits.', 'credits-for-woocommerce' ) ) ;
		}

		$old_amount = $this->get_available_credits( 'edit' ) ;
		$new_amount = wc_format_decimal( $value ) ;

		$this->set_prop( 'available_credits', $new_amount ) ;

		return array(
			'old_amount' => $old_amount,
			'new_amount' => $new_amount,
				) ;
	}

	/**
	 * Set credits approved date.
	 *
	 * @param  string $date based on WordPress site timezone.
	 * @throws Exception may be thrown if value is invalid.
	 */
	public function set_approved_date( $date ) {
		$this->set_prop( 'approved_date', $date ) ;
	}

	/**
	 * Set the day of billing to set the next billing date.
	 *
	 * @param  string|int $value
	 * @throws Exception may be thrown if value is invalid.
	 */
	public function set_billing_day( $value ) {
		if ( ! is_numeric( $value ) || $value < 1 || $value > 27 ) {
			throw new Exception( __( 'Invalid billing day of month.', 'credits-for-woocommerce' ) ) ;
		}

		$this->set_prop( 'billing_day', absint( $value ) ) ;
	}

	/**
	 * Set the day of due to set the due date.
	 * 
	 * @param string|int $value
	 * @throws Exception may be thrown if value is invalid.
	 */
	public function set_due_day( $value ) {
		if ( ! is_numeric( $value ) || $value < 1 || $value > 28 ) {
			throw new Exception( __( 'Invalid due day of month.', 'credits-for-woocommerce' ) ) ;
		}

		$this->set_prop( 'due_day', absint( $value ) ) ;
	}

	/**
	 * Set the due duration gap to set the due date either by this-month|next-month.
	 * 
	 * @param string $value
	 * @throws Exception may be thrown if value is invalid.
	 */
	public function set_due_duration_by( $value ) {
		$this->set_prop( 'due_duration_by', $value ) ;
	}

	/**
	 * Set last billed status.
	 *
	 * @param string $status
	 */
	public function set_last_billed_status( $status ) {
		$this->set_prop( 'last_billed_status', $status ) ;
	}

	/**
	 * Set last billed date.
	 *
	 * @param  string $value based on WordPress site timezone.
	 * @throws Exception may be thrown if value is invalid.
	 */
	public function set_last_billed_date( $value ) {
		$this->set_prop( 'last_billed_date', $value ) ;
	}

	/**
	 * Set last billed amount due.
	 *
	 * @param  string|float|int $value
	 * @throws Exception may be thrown if value is invalid.
	 */
	public function set_last_billed_amount( $value ) {
		if ( ! is_numeric( $value ) ) {
			throw new Exception( esc_html__( 'Invalid last billed amount due.', 'credits-for-woocommerce' ) ) ;
		}

		$this->set_prop( 'last_billed_amount', wc_format_decimal( $value ) ) ;
	}

	/**
	 * Set last billed due date.
	 *
	 * @param  string $value based on WordPress site timezone.
	 * @throws Exception may be thrown if value is invalid.
	 */
	public function set_last_billed_due_date( $value ) {
		$this->set_prop( 'last_billed_due_date', $value ) ;
	}

	/**
	 * Set last payment date.
	 *
	 * @param  string $value based on WordPress site timezone.
	 * @throws Exception may be thrown if value is invalid.
	 */
	public function set_last_payment_date( $value ) {
		$this->set_prop( 'last_payment_date', $value ) ;
	}

	/**
	 * Set the last payment order ID.
	 *
	 * @param string $value Value to set.
	 */
	public function set_last_payment_order_id( $value ) {
		$this->set_prop( 'last_payment_order_id', absint( $value ) ) ;
	}

	/**
	 * Set next bill date.
	 *
	 * @param  string $value based on WordPress site timezone.
	 * @throws Exception may be thrown if value is invalid.
	 */
	public function set_next_bill_date( $value ) {
		$this->set_prop( 'next_bill_date', $value ) ;
	}

	/**
	 * Set total outstanding amount.
	 *
	 * @param  string|float|int $value
	 * @throws Exception may be thrown if value is invalid.
	 */
	public function set_total_outstanding_amount( $value ) {
		if ( ! is_numeric( $value ) ) {
			throw new Exception( esc_html__( 'Invalid total outstanding amount.', 'credits-for-woocommerce' ) ) ;
		}

		$this->set_prop( 'total_outstanding_amount', wc_format_decimal( $value ) ) ;
	}

	/**
	 * Set unpaid previous bill amount.
	 *
	 * @param  string|float|int $value
	 * @throws Exception may be thrown if value is invalid.
	 */
	public function set_unpaid_previous_bill_amount( $value ) {
		if ( ! is_numeric( $value ) ) {
			throw new Exception( esc_html__( 'Invalid unpaid previous bill amount.', 'credits-for-woocommerce' ) ) ;
		}

		$this->set_prop( 'unpaid_previous_bill_amount', wc_format_decimal( $value ) ) ;
	}

	/**
	 * Set statements.
	 *
	 * @param array $data
	 */
	public function set_statements( $data ) {
		$this->set_prop( 'statements', $data ) ;
	}

	/**
	 * Set rule applied.
	 *
	 * @param int $value Value to set.
	 * @throws Exception may be thrown if value is invalid.
	 */
	public function set_rule_applied( $value ) {
		$this->set_prop( 'rule_applied', is_numeric( $value ) ? absint( $value ) : ''  ) ;
	}

	/**
	 * Set created via.
	 *
	 * @param  string $value
	 * @throws Exception may be thrown if value is invalid.
	 */
	public function set_created_via( $value ) {
		$this->set_prop( 'created_via', sanitize_title( $value ) ) ;
	}

	/**
	 * Set the credits/credit line type.
	 *
	 * @param  string $type
	 * @throws Exception may be thrown if value is invalid.
	 */
	public function set_type( $type ) {
		$this->set_prop( 'type', sanitize_title( $type ) ) ;
	}

}
