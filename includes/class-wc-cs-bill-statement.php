<?php

defined( 'ABSPATH' ) || exit ;

/**
 * Bill Statement Generated
 * 
 * @class WC_CS_Bill_Statement
 * @package Class
 */
class WC_CS_Bill_Statement extends WC_CS_Data {

	/**
	 * This is the name of this object type.
	 *
	 * @var string
	 */
	protected $object_type = 'wc_cs_bill_statement' ;

	/**
	 * Which data store to load.
	 *
	 * @var string
	 */
	protected $data_store_name = 'bill-statement-cpt' ;

	/**
	 * Core data for this object. Name value pairs (name + default value).
	 *
	 * @var array
	 */
	protected $data = array(
		'user_id'           => 0,
		'credits_id'        => 0,
		'status'            => 'publish',
		'date_created'      => '',
		'version'           => '',
		'hash'              => '',
		'due_date'          => '',
		'total_outstanding' => 0,
		'prev_amount_due'   => 0,
		'other_debits'      => 0,
		'from_date'         => '',
		'to_date'           => '',
			) ;

	/**
	 * Construct.
	 */
	public function __construct( $bill_statement = 0 ) {
		parent::__construct( $bill_statement ) ;

		if ( is_numeric( $bill_statement ) && $bill_statement > 0 ) {
			$this->set_id( $bill_statement ) ;
		} elseif ( $bill_statement instanceof self ) {
			$this->set_id( $bill_statement->get_id() ) ;
		} elseif ( ! empty( $bill_statement->ID ) ) {
			$this->set_id( $bill_statement->ID ) ;
		}

		$this->load_data_store() ;
		$this->maybe_read() ;
	}

	/**
	 * Load the data store for the bill statement.
	 */
	protected function load_data_store() {
		$this->data_store = new WC_CS_Bill_Statement_Data_Store_CPT() ;
	}

	/**
	 * Maybe read the data from the data store for the bill statement.
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
	 * Get all valid statuses for this bill statement.
	 *
	 * @return array
	 */
	public function get_valid_statuses() {
		return array( 'publish', 'trash' ) ;
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
	 * Get the credits ID.
	 *
	 * @param  string $context View or edit context.
	 * @return int
	 */
	public function get_credits_id( $context = 'view' ) {
		return $this->get_prop( 'credits_id', $context ) ;
	}

	/**
	 * Return the bill statement status.
	 *
	 * @param  string $context View or edit context.
	 * @return string
	 */
	public function get_status( $context = 'view' ) {
		return $this->get_prop( 'status', $context ) ;
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
	 * Get bill statement version.
	 *
	 * @param  string $context View or edit context.
	 * @return string
	 */
	public function get_version( $context = 'view' ) {
		return $this->get_prop( 'version', $context ) ;
	}

	/**
	 * Get the hash value.
	 *
	 * @param  string $context View or edit context.
	 * @return string
	 */
	public function get_hash( $context = 'view' ) {
		return $this->get_prop( 'hash', $context ) ;
	}

	/**
	 * Get due date.
	 *
	 * @param  string $context View or edit context.
	 * @return string
	 */
	public function get_due_date( $context = 'view' ) {
		return $this->get_prop( 'due_date', $context ) ;
	}

	/**
	 * Get total outstanding.
	 *
	 * @param  string $context View or edit context.
	 * @return mixed
	 */
	public function get_total_outstanding( $context = 'view' ) {
		$total_outstanding = $this->get_prop( 'total_outstanding', $context ) ;

		if ( 'view' === $context ) {
			return wc_price( $total_outstanding ) ;
		}

		return $total_outstanding ;
	}

	/**
	 * Get previous amount due.
	 *
	 * @param  string $context View or edit context.
	 * @return mixed
	 */
	public function get_prev_amount_due( $context = 'view' ) {
		$prev_amount_due = $this->get_prop( 'prev_amount_due', $context ) ;

		if ( 'view' === $context ) {
			return wc_price( $prev_amount_due ) ;
		}

		return $prev_amount_due ;
	}

	/**
	 * Get other debits.
	 *
	 * @param  string $context View or edit context.
	 * @return mixed
	 */
	public function get_other_debits( $context = 'view' ) {
		$other_debits = $this->get_prop( 'other_debits', $context ) ;

		if ( 'view' === $context ) {
			return wc_price( $other_debits ) ;
		}

		return $other_debits ;
	}

	/**
	 * Get from date.
	 *
	 * @param  string $context View or edit context.
	 * @return string
	 */
	public function get_from_date( $context = 'view' ) {
		return $this->get_prop( 'from_date', $context ) ;
	}

	/**
	 * Get to date.
	 *
	 * @param  string $context View or edit context.
	 * @return string
	 */
	public function get_to_date( $context = 'view' ) {
		return $this->get_prop( 'to_date', $context ) ;
	}

	/**
	 * Checks the bill statement status against a passed in status.
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
	 * Set bill statement status.
	 *
	 * @param string $new_status Status to change the bill statement to. 
	 * @throws Exception may be thrown if value is invalid.
	 */
	public function set_status( $new_status ) {
		$this->set_prop( 'status', $new_status ) ;
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
	 * Set the credits ID.
	 *
	 * @param int $value
	 * @throws Exception may be thrown if value is invalid.
	 */
	public function set_credits_id( $value ) {
		$this->set_prop( 'credits_id', absint( $value ) ) ;
	}

	/**
	 * Set date created.
	 *
	 * @param  string|int $date based on WordPress site timezone.
	 * @throws Exception may be thrown if value is invalid.
	 */
	public function set_date_created( $date ) {
		$this->set_prop( 'date_created', $date ) ;
	}

	/**
	 * Set bill statement version.
	 *
	 * @param string $value Value to set.
	 * @throws Exception may be thrown if value is invalid.
	 */
	public function set_version( $value ) {
		$this->set_prop( 'version', $value ) ;
	}

	/**
	 * Set the bill statement hash value.
	 *
	 * @param  string $hash
	 * @throws Exception may be thrown if value is invalid.
	 */
	public function set_hash( $hash ) {
		if ( ! $hash || ! is_string( $hash ) ) {
			throw new Exception( __( 'Invalid hash value to read bill statement.', 'credits-for-woocommerce' ) ) ;
		}
		
		$this->set_prop( 'hash', $hash ) ;
	}

	/**
	 * Set due date.
	 *
	 * @param  string|int $date based on WordPress site timezone.
	 * @throws Exception may be thrown if value is invalid.
	 */
	public function set_due_date( $date ) {
		$this->set_prop( 'due_date', $date ) ;
	}

	/**
	 * Set total outstanding.
	 *
	 * @param string $value Value to set.
	 * @throws Exception may be thrown if value is invalid.
	 */
	public function set_total_outstanding( $value ) {
		$this->set_prop( 'total_outstanding', wc_format_decimal( $value ) ) ;
	}

	/**
	 * Set previous amount due.
	 *
	 * @param string $value Value to set.
	 * @throws Exception may be thrown if value is invalid.
	 */
	public function set_prev_amount_due( $value ) {
		$this->set_prop( 'prev_amount_due', wc_format_decimal( $value ) ) ;
	}

	/**
	 * Set other_debits.
	 *
	 * @param string $value Value to set.
	 * @throws Exception may be thrown if value is invalid.
	 */
	public function set_other_debits( $value ) {
		$this->set_prop( 'other_debits', wc_format_decimal( $value ) ) ;
	}

	/**
	 * Set from date.
	 *
	 * @param  string|int $date based on WordPress site timezone.
	 * @throws Exception may be thrown if value is invalid.
	 */
	public function set_from_date( $date ) {
		$this->set_prop( 'from_date', $date ) ;
	}

	/**
	 * Set to date.
	 *
	 * @param  string|int $date based on WordPress site timezone.
	 * @throws Exception may be thrown if value is invalid.
	 */
	public function set_to_date( $date ) {
		$this->set_prop( 'to_date', $date ) ;
	}

}
