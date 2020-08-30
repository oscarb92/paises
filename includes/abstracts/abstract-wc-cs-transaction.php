<?php

defined( 'ABSPATH' ) || exit ;

/**
 * Abstract Transaction
 * 
 * @class WC_CS_Abstract_Transaction
 * @package Class
 */
abstract class WC_CS_Abstract_Transaction extends WC_CS_Data {

	/**
	 * Which data store to load.
	 *
	 * @var string
	 */
	protected $data_store_name ;

	/**
	 * Core data for this object. Name value pairs (name + default value).
	 *
	 * @var array
	 */
	protected $data = array(
		'user_id'      => 0,
		'status'       => '',
		'type'         => '',
		'date_created' => '',
		'version'      => '',
		'key'          => '',
		'activity'     => '',
		'credited'     => 0,
		'debited'      => 0,
		'balance'      => 0,
			) ;

	/**
	 * Construct.
	 */
	public function __construct( $txn = 0 ) {
		parent::__construct( $txn ) ;

		if ( is_numeric( $txn ) && $txn > 0 ) {
			$this->set_id( $txn ) ;
		} elseif ( $txn instanceof self ) {
			$this->set_id( $txn->get_id() ) ;
		} elseif ( ! empty( $txn->ID ) ) {
			$this->set_id( $txn->ID ) ;
		}

		$this->load_data_store() ;
		$this->maybe_read() ;
	}

	/**
	 * Load the data store for the transaction.
	 */
	protected function load_data_store() {
		wc_doing_it_wrong( __METHOD__, __( 'This method must be over-ridden in a sub-class to load data store.', 'credits-for-woocommerce' ), '1.0' ) ;
	}

	/**
	 * Maybe read the data from the data store for the transaction.
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
	 * Get all valid statuses for this transaction.
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
	 * Return the transaction status without WC_CS_PREFIX internal prefix.
	 *
	 * @param  string $context View or edit context.
	 * @return string
	 */
	public function get_status( $context = 'view' ) {
		return $this->get_prop( 'status', $context ) ;
	}

	/**
	 * Return the transaction type.
	 *
	 * @param  string $context View or edit context.
	 * @return string
	 */
	public function get_type( $context = 'view' ) {
		return $this->get_prop( 'type', $context ) ;
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
	 * Get transaction version.
	 *
	 * @param  string $context View or edit context.
	 * @return string
	 */
	public function get_version( $context = 'view' ) {
		return $this->get_prop( 'version', $context ) ;
	}

	/**
	 * Get activity.
	 *
	 * @param  string $context View or edit context.
	 * @return string
	 */
	public function get_activity( $context = 'view' ) {
		return $this->get_prop( 'activity', $context ) ;
	}

	/**
	 * Get credited.
	 *
	 * @param  string $context View or edit context.
	 * @return string
	 */
	public function get_credited( $context = 'view' ) {
		$credited = $this->get_prop( 'credited', $context ) ;

		if ( 'view' === $context ) {
			return wc_price( $credited ) ;
		}

		return $credited ;
	}

	/**
	 * Get debited.
	 *
	 * @param  string $context View or edit context.
	 * @return string
	 */
	public function get_debited( $context = 'view' ) {
		$debited = $this->get_prop( 'debited', $context ) ;

		if ( 'view' === $context ) {
			return wc_price( $debited ) ;
		}

		return $debited ;
	}

	/**
	 * Get balance.
	 *
	 * @param  string $context View or edit context.
	 * @return mixed
	 */
	public function get_balance( $context = 'view' ) {
		$balance = $this->get_prop( 'balance', $context ) ;

		if ( 'view' === $context ) {
			return wc_price( $balance ) ;
		}

		return $balance ;
	}

	/**
	 * Return the transaction key.
	 *
	 * @param  string $context View or edit context.
	 * @return string
	 */
	public function get_key( $context = 'view' ) {
		return $this->get_prop( 'key', $context ) ;
	}

	/**
	 * Checks the transaction status against a passed in status.
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
	 * Set transaction status.
	 *
	 * @param string $new_status Status to change the transaction to. 
	 * @throws Exception may be thrown if value is invalid.
	 */
	public function set_status( $new_status ) {
		$this->set_prop( 'status', $new_status ) ;
	}

	/**
	 * Set the transaction type.
	 *
	 * @param  string $type
	 * @throws Exception may be thrown if value is invalid.
	 */
	public function set_type( $type ) {
		$this->set_prop( 'type', sanitize_title( $type ) ) ;
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
	 * @param  string|int $date based on WordPress site timezone.
	 * @throws Exception may be thrown if value is invalid.
	 */
	public function set_date_created( $date ) {
		$this->set_prop( 'date_created', $date ) ;
	}

	/**
	 * Set transaction version.
	 *
	 * @param string $value Value to set.
	 * @throws Exception may be thrown if value is invalid.
	 */
	public function set_version( $value ) {
		$this->set_prop( 'version', $value ) ;
	}

	/**
	 * Set activity.
	 *
	 * @param string $value Value to set.
	 * @throws Exception may be thrown if value is invalid.
	 */
	public function set_activity( $value ) {
		$this->set_prop( 'activity', wp_kses_post( wp_unslash( $value ) ) ) ;
	}

	/**
	 * Set credited.
	 *
	 * @param string $value Value to set.
	 * @throws Exception may be thrown if value is invalid.
	 */
	public function set_credited( $value ) {
		$this->set_prop( 'credited', wc_format_decimal( $value ) ) ;
	}

	/**
	 * Set debited.
	 *
	 * @param string $value Value to set.
	 * @throws Exception may be thrown if value is invalid.
	 */
	public function set_debited( $value ) {
		$this->set_prop( 'debited', wc_format_decimal( $value ) ) ;
	}

	/**
	 * Set balance.
	 *
	 * @param string $value Value to set.
	 * @throws Exception may be thrown if value is invalid.
	 */
	public function set_balance( $value ) {
		$this->set_prop( 'balance', wc_format_decimal( $value ) ) ;
	}

	/**
	 * Set the transaction key.
	 *
	 * @param  string $key
	 * @throws Exception may be thrown if value is invalid.
	 */
	public function set_key( $key ) {
		$this->set_prop( 'key', $key ) ;
	}

}
