<?php

defined( 'ABSPATH' ) || exit ;

/**
 * Credits Transaction
 * 
 * @class WC_CS_Credits_Transaction
 * @package Class
 */
class WC_CS_Credits_Transaction extends WC_CS_Abstract_Transaction {

	/**
	 * This is the name of this object type.
	 *
	 * @var string
	 */
	protected $object_type = 'wc_cs_credits_txn' ;

	/**
	 * Which data store to load.
	 *
	 * @var string
	 */
	protected $data_store_name = 'credits-transaction-cpt' ;

	/**
	 * Stores data about status changes so relevant hooks can be fired.
	 *
	 * @var bool|string|array
	 */
	protected $status_transition = false ;

	/**
	 * Stores data about amount status changes so relevant hooks can be fired.
	 *
	 * @var bool|string|array
	 */
	protected $amount_transition_status = false ;

	/**
	 * Extra data for this object. Name value pairs.
	 * Used to add additional information to an inherited class.
	 *
	 * @var array
	 */
	protected $extra_data = array(
		'credits_id'  => 0,
		'order_id'    => 0,
		'billed_date' => '',
			) ;

	/*
	  |--------------------------------------------------------------------------
	  | Helper Methods
	  |--------------------------------------------------------------------------
	 */

	/**
	 * Load the data store for the credits transaction.
	 */
	protected function load_data_store() {
		$this->data_store = new WC_CS_Credits_Transaction_Data_Store_CPT() ;
	}

	/**
	 * Maybe read the data from the data store for the transaction.
	 */
	protected function maybe_read() {
		parent::maybe_read() ;

		// Reset transition variable.
		$this->status_transition        = false ;
		$this->amount_transition_status = false ;
	}

	/**
	 * Get all valid statuses for this transaction.
	 *
	 * @return array Internal status keys e.g. WC_CS_PREFIX. 'unbilled'
	 */
	public function get_valid_statuses() {
		return array_keys( _wc_cs_get_credits_txn_statuses() ) ;
	}

	/**
	 * Handle the status transition.
	 */
	protected function status_transition() {
		$status_transition = $this->status_transition ;

		// Reset status transition variable.
		$this->status_transition = false ;

		if ( ! $status_transition ) {
			return ;
		}

		do_action( 'wc_cs_credits_txn_status_' . $status_transition[ 'to' ], $this ) ;

		if ( ! empty( $status_transition[ 'from' ] ) ) {
			do_action( 'wc_cs_credits_txn_status_' . $status_transition[ 'from' ] . '_to_' . $status_transition[ 'to' ], $this ) ;
			do_action( 'wc_cs_credits_txn_status_changed', $status_transition[ 'from' ], $status_transition[ 'to' ], $this ) ;
		}
	}

	/**
	 * Handle the amount transition.
	 */
	protected function amount_transition_status() {
		$amount_transition_status = $this->amount_transition_status ;

		// Reset status transition variable.
		$this->amount_transition_status = false ;

		if ( ! $amount_transition_status ) {
			return ;
		}

		do_action( 'wc_cs_credits_txn_amount_status_' . $amount_transition_status, $this ) ;
	}

	/**
	 * Save data to the database.
	 *
	 * @return int transaction ID|WP_Error
	 */
	public function save() {
		$result = parent::save() ;

		if ( ! is_wp_error( $result ) ) {
			$this->status_transition() ;
			$this->amount_transition_status() ;
		} else {
			// Reset transition variables.
			$this->status_transition        = false ;
			$this->amount_transition_status = false ;
		}

		return $result ;
	}

	/*
	  |--------------------------------------------------------------------------
	  | Getters
	  |--------------------------------------------------------------------------
	 */

	/**
	 * Return the transaction status without WC_CS_PREFIX internal prefix.
	 *
	 * @param  string $context View or edit context.
	 * @return string
	 */
	public function get_status( $context = 'view' ) {
		$status = parent::get_status( $context ) ;

		if ( 'view' === $context && empty( $status ) ) {
			// In view context, return the default status if no status has been set.
			$status = 'unbilled' ;
		}

		return $status ;
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
	 * Get the order ID.
	 *
	 * @param  string $context View or edit context.
	 * @return int
	 */
	public function get_order_id( $context = 'view' ) {
		return $this->get_prop( 'order_id', $context ) ;
	}

	/**
	 * Get billed date.
	 *
	 * @param  string $context View or edit context.
	 * @return int
	 */
	public function get_billed_date( $context = 'view' ) {
		return $this->get_prop( 'billed_date', $context ) ;
	}

	/*
	  |--------------------------------------------------------------------------
	  | Setters
	  |--------------------------------------------------------------------------
	 */

	/**
	 * Set transaction status.
	 *
	 * @param string $new_status Status to change the transaction to. No internal WC_CS_PREFIX is required.
	 * @throws Exception may be thrown if value is invalid.
	 */
	public function set_status( $new_status ) {
		$old_status = $this->get_status() ;
		$new_status = WC_CS_PREFIX === substr( $new_status, 0, 7 ) ? substr( $new_status, 7 ) : $new_status ;

		if ( 'trash' !== $new_status && ! in_array( WC_CS_PREFIX . $new_status, $this->get_valid_statuses() ) ) {
			$new_status = 'unbilled' ;
		}

		$this->set_prop( 'status', $new_status ) ;

		if ( $old_status !== $new_status ) {
			$this->status_transition = array(
				'from' => $old_status,
				'to'   => $new_status,
					) ;
		}
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
	 * Set the order ID.
	 *
	 * @param string $value Value to set.
	 * @throws Exception may be thrown if value is invalid.
	 */
	public function set_order_id( $value ) {
		$this->set_prop( 'order_id', absint( $value ) ) ;
	}

	/**
	 * Set the billed date.
	 *
	 * @param string $value Value to set.
	 */
	public function set_billed_date( $value ) {
		$this->set_prop( 'billed_date', $value ) ;
	}

	/**
	 * Set credited.
	 *
	 * @param string $value Value to set.
	 * @throws Exception may be thrown if value is invalid.
	 */
	public function set_credited( $value ) {
		parent::set_credited( $value ) ;
		$this->amount_transition_status = 'credited' ;
	}

	/**
	 * Set debited.
	 *
	 * @param string $value Value to set.
	 * @throws Exception may be thrown if value is invalid.
	 */
	public function set_debited( $value ) {
		parent::set_debited( $value ) ;
		$this->amount_transition_status = 'debited' ;
	}

}
