<?php

defined( 'ABSPATH' ) || exit ;

/**
 * Virtual Funds Transaction
 * 
 * @class WC_CS_Virtual_Funds_Transaction
 * @package Class
 */
class WC_CS_Virtual_Funds_Transaction extends WC_CS_Abstract_Transaction {

	/**
	 * This is the name of this object type.
	 *
	 * @var string
	 */
	protected $object_type = 'wc_cs_vrtualfundstxn' ;

	/**
	 * Which data store to load.
	 *
	 * @var string
	 */
	protected $data_store_name = 'virtual-funds-transaction-cpt' ;

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
		'user_email' => ''
			) ;

	/**
	 * Load the data store for the virtual funds transaction.
	 */
	protected function load_data_store() {
		$this->data_store = new WC_CS_Virtual_Funds_Transaction_Data_Store_CPT() ;
	}

	/**
	 * Maybe read the data from the data store for the transaction.
	 */
	protected function maybe_read() {
		parent::maybe_read() ;

		// Reset transition variable.
		$this->amount_transition_status = false ;
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

		do_action( 'wc_cs_virtual_funds_txn_amount_status_' . $amount_transition_status, $this ) ;
	}

	/*
	  |--------------------------------------------------------------------------
	  | Getters
	  |--------------------------------------------------------------------------
	 */

	/**
	 * Get user email.
	 *
	 * @param  string $context What the value is for. Valid values are 'view' and 'edit'.
	 * @return string
	 */
	public function get_user_email( $context = 'view' ) {
		return $this->get_prop( 'user_email', $context ) ;
	}

	/*
	  |--------------------------------------------------------------------------
	  | Setters
	  |--------------------------------------------------------------------------
	 */

	/**
	 * Set user email.
	 *
	 * @param string $value email.
	 */
	public function set_user_email( $value ) {
		if ( $value && ! is_email( $value ) ) {
			throw new Exception( esc_html__( 'Invalid user email address.', 'credits-for-woocommerce' ) ) ;
		}

		$this->set_prop( 'user_email', sanitize_email( $value ) ) ;
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

	/**
	 * Save data to the database.
	 *
	 * @return int transaction ID|WP_Error
	 */
	public function save() {
		$result = parent::save() ;

		if ( ! is_wp_error( $result ) ) {
			$this->amount_transition_status() ;
		} else {
			// Reset transition variables.
			$this->amount_transition_status = false ;
		}

		return $result ;
	}

}
