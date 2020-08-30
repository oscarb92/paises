<?php

defined( 'ABSPATH' ) || exit ;

/**
 * Credits
 * 
 * @class WC_CS_Credits
 * @package Class
 */
class WC_CS_Credits extends WC_CS_Abstract_Credits {

	/**
	 * Stores data about status changes so relevant hooks can be fired.
	 *
	 * @var bool|array
	 */
	protected $status_transition = false ;

	/**
	 * Stores data about credits changes so relevant hooks can be fired.
	 *
	 * @var bool|array
	 */
	protected $credits_transition = false ;

	/**
	 * Credits transactions will be stored here, sometimes before they persist in the DB.
	 *
	 * @var array
	 */
	protected $transactions = array() ;

	/**
	 * Extra data for this object. Name value pairs.
	 * Used to add additional information to an inherited class.
	 *
	 * @var array
	 */
	protected $extra_data = array(
		'attachments' => array(),
		'address'     => array(
			'user_first_name' => '',
			'user_last_name'  => '',
			'user_company'    => '',
			'user_address_1'  => '',
			'user_address_2'  => '',
			'user_city'       => '',
			'user_state'      => '',
			'user_postcode'   => '',
			'user_country'    => '',
			'user_email'      => '',
			'user_phone'      => ''
		),
		'orders'      => array(
			'total_orders_placed_by_user'      => 0,
			'total_amount_spent_by_user'       => 0,
			'highest_order_value_by_user'      => 0,
			'lowest_order_value_by_user'       => 0,
			'avg_monthly_amount_spent_by_user' => 0,
			'avg_yearly_amount_spent_by_user'  => 0
		)
			) ;

	/*
	  |--------------------------------------------------------------------------
	  | Helper Methods
	  |--------------------------------------------------------------------------
	 */

	/**
	 * Maybe read the data from the data store for the credits.
	 */
	protected function maybe_read() {
		parent::maybe_read() ;

		// Reset transition variables.
		$this->status_transition  = false ;
		$this->credits_transition = false ;
	}

	/**
	 * Return the array of transactions within this credits.
	 * 
	 * @param string $type unbilled|billed
	 * @param string $hash Hash value of the bill statement generated. Want to retrive the specific bill statement ?
	 * @return array
	 */
	public function get_transactions( $type, $hash = '' ) {
		if ( ! $this->get_id() ) { // Credits must exist.
			return array() ;
		}

		if ( '' !== $hash ) {
			if ( ! isset( $this->transactions[ $type ][ $hash ] ) ) {
				$this->transactions[ $type ][ $hash ] = $this->data_store->read_transactions( $this, $type, $hash ) ;
			}

			return $this->transactions[ $type ][ $hash ] ;
		}

		if ( ! isset( $this->transactions[ $type ] ) ) {
			$this->transactions[ $type ] = $this->data_store->read_transactions( $this, $type ) ;
		}

		return $this->transactions[ $type ] ;
	}

	/**
	 * Get the bill statement by date.
	 * 
	 * @param string $date
	 * @return WC_CS_Bill_Statement|false
	 */
	public function get_bill_statement_by_date( $date ) {
		if ( ! $this->get_id() || ! $date ) {
			return false ;
		}

		$hash_index        = _wc_cs_get_time( 'Ym', array( 'time' => $date ) ) ;
		$bill_statement_id = array_search( $hash_index, $this->get_statements() ) ;

		try {
			$bill_statement = $bill_statement_id ? new WC_CS_Bill_Statement( $bill_statement_id ) : false ;
		} catch ( Exception $e ) {
			return false ;
		}

		return $bill_statement ;
	}

	/**
	 * Returns the user details in html format.
	 * 
	 * @param bool $tips
	 * @return string
	 */
	public function get_user_details_html( $tips = true ) {
		if ( ! $this->get_id() ) { // Credits must exist.
			return '' ;
		}

		$user_data = array(
			'first_name' => '&nbsp;',
			'last_name'  => ",\n",
			'email'      => ",\n",
			'company'    => ",\n",
			'address_1'  => ",\n",
			'address_2'  => ",\n",
			'city'       => ',&nbsp;',
			'state'      => ',&nbsp;',
			'country'    => ",&nbsp;\n",
			'postcode'   => ",\n",
			'phone'      => '',
				) ;

		$out = '<p>' ;
		foreach ( $user_data as $internal_prop => $rep ) {
			$getter = "get_user_$internal_prop" ;

			if ( ! is_callable( array( $this, $getter ) ) ) {
				continue ;
			}

			if ( '' !== $this->{$getter}() ) {
				$out .= $this->{$getter}() ;
				$out .= $rep ;
			}
		}
		$out .= '</p>' ;

		if ( $tips ) {
			/* translators: 1: class name 2: data-tip 3: email */
			return sprintf( __( '<a href="#" class="%1$s" data-tip="%2$s">%3$s</a>' ), esc_attr( WC_CS_PREFIX . 'tips' ), esc_attr( nl2br( $out ) ), esc_html( $this->get_user_email() ) ) ;
		}

		return nl2br( $out ) ;
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

		try {
			do_action( 'wc_cs_credits_status_' . $status_transition[ 'to' ], $this ) ;

			if ( ! empty( $status_transition[ 'from' ] ) ) {
				do_action( 'wc_cs_credits_status_' . $status_transition[ 'from' ] . '_to_' . $status_transition[ 'to' ], $this ) ;
				do_action( 'wc_cs_credits_status_changed', $status_transition[ 'from' ], $status_transition[ 'to' ], $this ) ;
			}
		} catch ( Exception $e ) {
			$this->handle_exception( $e, sprintf( 'Status transition of credits #%d errored!', $this->get_id() ) ) ;
		}
	}

	/**
	 * Handle the credits transition.
	 */
	protected function credits_transition() {
		$credits_transition = $this->credits_transition ;

		// Reset credits transition variable.
		$this->credits_transition = false ;

		if ( ! $credits_transition ) {
			return ;
		}

		try {
			if ( ! empty( $credits_transition[ 'usage_limit' ] ) ) {
				do_action( 'wc_cs_credits_usage_limit_' . $credits_transition[ 'usage_limit' ], $this ) ;
			}

			if ( ! empty( $credits_transition[ 'amount_status' ] ) ) {
				do_action( 'wc_cs_credits_amount_status_' . $credits_transition[ 'amount_status' ], $this ) ;
			}
		} catch ( Exception $e ) {
			$this->handle_exception( $e, sprintf( 'Amount transition of credits #%d errored!', $this->get_id() ) ) ;
		}
	}

	/**
	 * Updates status of credits immediately.
	 *
	 * @uses WC_CS_Credits::set_status()
	 * @param string $new_status  Status to change the credits to. No internal WC_CS_PREFIX prefix is required.
	 * @return bool
	 */
	public function update_status( $new_status ) {
		if ( ! $this->get_id() ) { // Credits must exist.
			return false ;
		}

		try {
			$this->set_status( $new_status ) ;

			// Check with the new status.
			switch ( $this->get_status() ) {
				case 'active':
					if ( '' === $this->get_approved_date( 'edit' ) ) {
						$this->set_approved_date( _wc_cs_get_time( 'timestamp' ) ) ;
					}

					if ( '' === $this->get_next_bill_date( 'edit' ) ) {
						$this->set_billing_day( get_option( WC_CS_PREFIX . 'get_billing_day_of_month' ) ) ;
						$this->set_due_day( get_option( WC_CS_PREFIX . 'get_due_day_of_month' ) ) ;
						$this->set_due_duration_by( get_option( WC_CS_PREFIX . 'get_repayment_month' ) ) ;
						$this->set_next_bill_date( _wc_cs_calculate_billing_date( $this->get_billing_day( 'edit' ), true ) ) ;
					}

					if ( ! _wc_cs_job_exists_in_queue( 'credits', 'create_bill_statement', $this->get_next_bill_date( 'edit' ), $this->get_id() ) ) {
						_wc_cs_push_job_to_queue( 'credits', 'create_bill_statement', $this->get_next_bill_date( 'edit' ), $this->get_id() ) ;
					}
					break ;
			}

			$this->save() ;
		} catch ( Exception $e ) {
			$this->handle_exception( $e, sprintf( 'Error updating status for credits #%d', $this->get_id() ) ) ;
			return false ;
		}
		return true ;
	}

	/**
	 * Update credit limit.
	 * 
	 * @param float $new_limit_raw
	 * @param bool $force_save
	 * @return bool
	 */
	public function update_credit_limit( $new_limit_raw, $force_save = false ) {
		if ( ! $this->get_id() ) {
			return false ;
		}

		try {
			$new_limit = $new_limit_raw - $this->get_approved_credits( 'edit' ) ;

			if ( _wc_cs()->funding_via_real_money() ) {
				$this->set_props( array(
					'approved_credits'  => $new_limit_raw,
					'available_credits' => $this->get_available_credits( 'edit' ) + $new_limit
				) ) ;

				if ( $new_limit < 0 ) {
					WC_CS_Admin_Funds::credit( $new_limit ) ;
					WC_CS_Admin_Funds::create_txn( array(
						'credited'   => $new_limit,
						'activity'   => __( 'Credit Limit Updated', 'credits-for-woocommerce' ),
						'type'       => 'credit-limit-decreased',
						'user_email' => $this->get_user_email()
					) ) ;
				} else {
					WC_CS_Admin_Funds::debit( $new_limit ) ;
					WC_CS_Admin_Funds::create_txn( array(
						'debited'    => $new_limit,
						'activity'   => '' === $this->get_approved_date( 'edit' ) ? __( 'Credit Limit Approved', 'credits-for-woocommerce' ) : __( 'Credit Limit Updated', 'credits-for-woocommerce' ),
						'type'       => '' === $this->get_approved_date( 'edit' ) ? 'credits-approved' : 'credit-limit-increased',
						'user_email' => $this->get_user_email()
					) ) ;
				}
			} else {
				$this->set_props( array(
					'approved_credits'  => $new_limit_raw,
					'available_credits' => $this->get_available_credits( 'edit' ) + $new_limit
				) ) ;

				if ( $new_limit > 0 ) {
					WC_CS_Virtual_Funds::create_txn( array(
						'credited'   => $new_limit,
						'balance'    => $this->get_available_credits( 'edit' ),
						'activity'   => '' === $this->get_approved_date( 'edit' ) ? __( 'Credit Limit Approved', 'credits-for-woocommerce' ) : __( 'Credit Limit Updated', 'credits-for-woocommerce' ),
						'type'       => '' === $this->get_approved_date( 'edit' ) ? 'credits-approved' : 'credit-limit-increased',
						'user_email' => $this->get_user_email()
					) ) ;
				}
			}

			if ( $force_save ) {
				$this->save() ;
			}
		} catch ( Exception $e ) {
			return false ;
		}

		return true ;
	}

	/**
	 * Apply rule to the credits user.
	 * 
	 * @param object $rule
	 * @return bool
	 */
	public function apply_rule( $rule ) {
		if ( ! $this->get_id() || $this->is_manual() ) {
			return false ;
		}

		try {
			$new_limit_raw = wc_format_decimal( $rule->credit_limit, 2 ) ;

			if ( $new_limit_raw <= 0 ) {
				throw new Exception( sprintf( 'Rule: %s - Invalid credit limit to apply for the user for credits #%d', $rule->name, $this->get_id() ) ) ;
			}

			if ( _wc_cs()->funding_via_real_money() ) {
				$new_limit = $new_limit_raw - $this->get_approved_credits( 'edit' ) ;

				if ( $new_limit > WC_CS_Admin_Funds::get_available_funds( 'edit' ) ) {
					throw new Exception( sprintf( 'Rule: %s - Insufficient funds to apply credit limit for the user for credits #%d', $rule->name, $this->get_id() ) ) ;
				}
			}

			$this->set_type( 'auto' ) ;
			$this->set_rule_applied( $rule->id ) ;
			$this->update_credit_limit( $new_limit_raw ) ;

			if ( ! $this->has_status( 'active' ) ) {
				$this->update_status( 'active' ) ;
			}

			$this->save() ;
		} catch ( Exception $e ) {
			$this->set_rule_applied( '' ) ;

			if ( ! $this->has_status( 'on_hold' ) ) {
				$this->set_status( 'on_hold' ) ;
			}

			$this->save() ;
			$this->handle_exception( $e, ( $e->getMessage() ? $e->getMessage() : sprintf( 'Error applying rule for credits #%d', $this->get_id() ) ) ) ;
			return false ;
		}
		return true ;
	}

	/**
	 * Read the user purchase history.
	 */
	public function read_user_history() {
		$this->set_props( array(
			'total_orders_placed_by_user'      => _wc_cs_get_total_orders_placed_by_user( $this->get_user_id() ),
			'total_amount_spent_by_user'       => _wc_cs_get_total_amount_spent_by_user( $this->get_user_id() ),
			'highest_order_value_by_user'      => _wc_cs_get_highest_order_value_by_user( $this->get_user_id() ),
			'lowest_order_value_by_user'       => _wc_cs_get_lowest_order_value_by_user( $this->get_user_id() ),
			'avg_monthly_amount_spent_by_user' => _wc_cs_get_avg_monthly_amount_spent_by_user( $this->get_user_id() ),
			'avg_yearly_amount_spent_by_user'  => _wc_cs_get_avg_yearly_amount_spent_by_user( $this->get_user_id() ),
		) ) ;
		$this->save() ;
	}

	/**
	 * Maybe apply less than 2.0 compatibility tweaks.
	 * 
	 * @param bool $force_save
	 */
	public function maybe_apply_lt_2pt0_compatibility( $force_save = false ) {
		if ( '' === $this->get_created_via() ) {
			$this->set_created_via( 'app' ) ;
			$this->set_type( 'manual' ) ;
		}

		if ( $force_save && $this->has_changes() ) {
			$this->save() ;
		}
	}

	/**
	 * Log an error about this credits is exception is encountered.
	 *
	 * @param Exception $e Exception object.
	 * @param string    $message Message regarding exception thrown.
	 */
	protected function handle_exception( $e, $message = 'Error' ) {
		if ( function_exists( 'wc_get_logger' ) ) {
			wc_get_logger()->error( $message, array(
				'credits' => $this,
				'error'   => $e,
				'source'  => 'credits-for-wc'
					)
			) ;
		}
	}

	/**
	 * Save data to the database.
	 *
	 * @return int credits ID|WP_Error
	 */
	public function save() {
		$result = parent::save() ;

		if ( ! is_wp_error( $result ) ) {
			$this->status_transition() ;
			$this->credits_transition() ;
		} else {
			// Reset transition variables.
			$this->status_transition  = false ;
			$this->credits_transition = false ;
		}

		return $result ;
	}

	/*
	  |--------------------------------------------------------------------------
	  | Conditionals
	  |--------------------------------------------------------------------------
	  |
	  | Checks if a condition is true or false.
	  |
	 */

	/**
	 * Check if credits/credit line adding is manual.
	 * 
	 * @return bool
	 */
	public function is_manual() {
		return apply_filters( 'wc_cs_credits_is_manual', ( '' === $this->get_type() || 'manual' === $this->get_type() ), $this ) ;
	}

	/**
	 * Check if credits has been created via app, rule or in another way.
	 * app - Credits created after the application is submitted.
	 * rule - Credits created based upon the rules satisfaction.
	 * 
	 * @param string $modus Way of creating the credits to user.
	 * @return bool
	 */
	public function is_created_via( $modus ) {
		if ( '' === $this->get_created_via() ) {
			$created_via = 'app' ;
		} else {
			$created_via = $this->get_created_via() ;
		}

		return apply_filters( 'wc_cs_credits_is_created_via', $modus === $created_via, $this, $modus ) ;
	}

	/**
	 * Can the rule be applied ?
	 * 
	 * @return bool
	 */
	public function can_apply_rule() {
		return ( $this->is_created_via( 'rule' ) || ( $this->is_created_via( 'app' ) && '' !== $this->get_approved_date( 'edit' ) ) ) ? true : false ;
	}

	/*
	  |--------------------------------------------------------------------------
	  | Getters
	  |--------------------------------------------------------------------------
	 */

	/**
	 * Get the attachments
	 *
	 * @param  string $context What the value is for. Valid values are 'view' and 'edit'.
	 * @return int[]
	 */
	public function get_attachments( $context = 'view' ) {
		return $this->get_prop( 'attachments', $context ) ;
	}

	/**
	 * Gets a user prop for a getter method.
	 *
	 * @param  string $prop Name of prop to get.
	 * @param  string $purpose Name of purpose to get. address|orders
	 * @param  string $context What the value is for. Valid values are 'view' and 'edit'.
	 * @return mixed
	 */
	protected function get_user_prop( $prop, $purpose, $context = 'view' ) {
		$value = '' ;

		switch ( $purpose ) {
			case 'address':
				$prop = "user_{$prop}" ;
				break ;
			case 'orders':
				$prop = "{$prop}_by_user" ;
				break ;
		}

		if ( $this->data_has( $purpose ) && array_key_exists( $prop, $this->data[ $purpose ] ) ) {
			$value = $this->changes_in( $purpose ) && array_key_exists( $prop, $this->changes[ $purpose ] ) ? $this->changes[ $purpose ][ $prop ] : $this->data[ $purpose ][ $prop ] ;

			if ( 'view' === $context ) {
				$value = apply_filters( $this->get_hook_prefix() . $purpose . '_' . $prop, $value, $this ) ;
			}
		}

		return $value ;
	}

	/**
	 * Returns the address in raw.
	 * Note: Merges raw data with get_prop data so changes are returned too.
	 *
	 * @return array
	 */
	public function get_address() {
		return apply_filters( 'wc_cs_get_user_address', array_merge( $this->data[ 'address' ], $this->get_prop( 'address', 'view' ) ), $this ) ;
	}

	/**
	 * Get user first name.
	 *
	 * @param  string $context What the value is for. Valid values are 'view' and 'edit'.
	 * @return string
	 */
	public function get_user_first_name( $context = 'view' ) {
		return $this->get_user_prop( 'first_name', 'address', $context ) ;
	}

	/**
	 * Get user last name.
	 *
	 * @param  string $context What the value is for. Valid values are 'view' and 'edit'.
	 * @return string
	 */
	public function get_user_last_name( $context = 'view' ) {
		return $this->get_user_prop( 'last_name', 'address', $context ) ;
	}

	/**
	 * Get user company.
	 *
	 * @param  string $context What the value is for. Valid values are 'view' and 'edit'.
	 * @return string
	 */
	public function get_user_company( $context = 'view' ) {
		return $this->get_user_prop( 'company', 'address', $context ) ;
	}

	/**
	 * Get user address_1.
	 *
	 * @param  string $context What the value is for. Valid values are 'view' and 'edit'.
	 * @return string
	 */
	public function get_user_address_1( $context = 'view' ) {
		return $this->get_user_prop( 'address_1', 'address', $context ) ;
	}

	/**
	 * Get user address_2.
	 *
	 * @param  string $context What the value is for. Valid values are 'view' and 'edit'.
	 * @return string $value
	 */
	public function get_user_address_2( $context = 'view' ) {
		return $this->get_user_prop( 'address_2', 'address', $context ) ;
	}

	/**
	 * Get user city.
	 *
	 * @param  string $context What the value is for. Valid values are 'view' and 'edit'.
	 * @return string $value
	 */
	public function get_user_city( $context = 'view' ) {
		return $this->get_user_prop( 'city', 'address', $context ) ;
	}

	/**
	 * Get user state.
	 *
	 * @param  string $context What the value is for. Valid values are 'view' and 'edit'.
	 * @return string
	 */
	public function get_user_state( $context = 'view' ) {
		return $this->get_user_prop( 'state', 'address', $context ) ;
	}

	/**
	 * Get user postcode.
	 *
	 * @param  string $context What the value is for. Valid values are 'view' and 'edit'.
	 * @return string
	 */
	public function get_user_postcode( $context = 'view' ) {
		return $this->get_user_prop( 'postcode', 'address', $context ) ;
	}

	/**
	 * Get user country.
	 *
	 * @param  string $context What the value is for. Valid values are 'view' and 'edit'.
	 * @return string
	 */
	public function get_user_country( $context = 'view' ) {
		return $this->get_user_prop( 'country', 'address', $context ) ;
	}

	/**
	 * Get user email.
	 *
	 * @param  string $context What the value is for. Valid values are 'view' and 'edit'.
	 * @return string
	 */
	public function get_user_email( $context = 'view' ) {
		return $this->get_user_prop( 'email', 'address', $context ) ;
	}

	/**
	 * Get user phone.
	 *
	 * @param  string $context What the value is for. Valid values are 'view' and 'edit'.
	 * @return string
	 */
	public function get_user_phone( $context = 'view' ) {
		return $this->get_user_prop( 'phone', 'address', $context ) ;
	}

	/**
	 * Get total orders placed by the user after it has been saved.
	 *
	 * @param  string $context What the value is for. Valid values are 'view' and 'edit'.
	 * @return string
	 */
	public function get_total_orders_placed_by_user( $context = 'view' ) {
		return $this->get_user_prop( 'total_orders_placed', 'orders', $context ) ;
	}

	/**
	 * Get total amount spent for the orders by the user after it has been saved.
	 *
	 * @param  string $context What the value is for. Valid values are 'view' and 'edit'.
	 * @return string
	 */
	public function get_total_amount_spent_by_user( $context = 'view' ) {
		$total_amount_spent = $this->get_user_prop( 'total_amount_spent', 'orders', $context ) ;

		if ( 'view' === $context ) {
			return wc_price( $total_amount_spent ) ;
		}

		return $total_amount_spent ;
	}

	/**
	 * Get highest amount spent for the order by the user after it has been saved.
	 *
	 * @param  string $context What the value is for. Valid values are 'view' and 'edit'.
	 * @return string
	 */
	public function get_highest_order_value_by_user( $context = 'view' ) {
		$highest_order_value = $this->get_user_prop( 'highest_order_value', 'orders', $context ) ;

		if ( 'view' === $context ) {
			return wc_price( $highest_order_value ) ;
		}

		return $highest_order_value ;
	}

	/**
	 * Get lowest amount spent for the order by the user after it has been saved.
	 *
	 * @param  string $context What the value is for. Valid values are 'view' and 'edit'.
	 * @return string
	 */
	public function get_lowest_order_value_by_user( $context = 'view' ) {
		$lowest_order_value = $this->get_user_prop( 'lowest_order_value', 'orders', $context ) ;

		if ( 'view' === $context ) {
			return wc_price( $lowest_order_value ) ;
		}

		return $lowest_order_value ;
	}

	/**
	 * Get average monthly amount spent for the orders by the user after it has been saved.
	 *
	 * @param  string $context What the value is for. Valid values are 'view' and 'edit'.
	 * @return string
	 */
	public function get_avg_monthly_amount_spent_by_user( $context = 'view' ) {
		$avg_monthly_amount_spent = $this->get_user_prop( 'avg_monthly_amount_spent', 'orders', $context ) ;

		if ( 'view' === $context ) {
			return wc_price( $avg_monthly_amount_spent ) ;
		}

		return $avg_monthly_amount_spent ;
	}

	/**
	 * Get average yearly amount spent for the orders by the user after it has been saved.
	 *
	 * @param  string $context What the value is for. Valid values are 'view' and 'edit'.
	 * @return string
	 */
	public function get_avg_yearly_amount_spent_by_user( $context = 'view' ) {
		$avg_yearly_amount_spent = $this->get_user_prop( 'avg_yearly_amount_spent', 'orders', $context ) ;

		if ( 'view' === $context ) {
			return wc_price( $avg_yearly_amount_spent ) ;
		}

		return $avg_yearly_amount_spent ;
	}

	/*
	  |--------------------------------------------------------------------------
	  | Setters
	  |--------------------------------------------------------------------------
	 */

	/**
	 * Set credits status.
	 *
	 * @param string $new_status Status to change the credits to. No internal WC_CS_PREFIX is required.
	 * @throws Exception may be thrown if value is invalid.
	 * @return array details of change
	 */
	public function set_status( $new_status ) {
		$result = parent::set_status( $new_status ) ;

		if ( $result[ 'from' ] !== $result[ 'to' ] ) {
			$this->status_transition = $result ;
		}

		return $result ;
	}

	/**
	 * Set approved credits.
	 *
	 * @param  string|int|float $value
	 * @throws Exception may be thrown if value is invalid.
	 * @return array details of change
	 */
	public function set_approved_credits( $value ) {
		$result = parent::set_approved_credits( $value ) ;

		if ( wc_format_decimal( $result[ 'old_usage' ] ) !== wc_format_decimal( $result[ 'new_usage' ] ) ) {
			if ( ! is_array( $this->credits_transition ) ) {
				$this->credits_transition = array() ;
			}

			$this->credits_transition[ 'usage_limit' ] = $result[ 'new_usage' ] > $result[ 'old_usage' ] ? 'increased' : 'decreased' ;
		}

		return $result ;
	}

	/**
	 * Set available credits.
	 *
	 * @param  string|int|float $value
	 * @throws Exception may be thrown if value is invalid.
	 * @return array details of change
	 */
	public function set_available_credits( $value ) {
		$result = parent::set_available_credits( $value ) ;

		if ( wc_format_decimal( $result[ 'old_amount' ] ) !== wc_format_decimal( $result[ 'new_amount' ] ) ) {
			if ( ! is_array( $this->credits_transition ) ) {
				$this->credits_transition = array() ;
			}

			$this->credits_transition[ 'amount_status' ] = $result[ 'new_amount' ] > $result[ 'old_amount' ] ? 'credited' : 'debited' ;
		}

		return $result ;
	}

	/**
	 * Set the attachments.
	 *
	 * @param string $value Value of the prop.
	 * @throws Exception may be thrown if value is invalid.
	 */
	public function set_attachments( $value ) {
		if ( ! is_array( $value ) ) {
			throw new Exception( esc_html__( 'Invalid attachments found.', 'credits-for-woocommerce' ) ) ;
		}

		$this->set_prop( 'attachments', $value ) ;
	}

	/**
	 * Sets a user prop for a setter method.
	 *
	 * @param string $prop    Name of prop to set.
	 * @param string $purpose Name of purpose to set. address|orders
	 * @param mixed  $value   Value of the prop.
	 */
	protected function set_user_prop( $prop, $purpose, $value ) {
		switch ( $purpose ) {
			case 'address':
				$prop = "user_{$prop}" ;
				break ;
			case 'orders':
				$prop = "{$prop}_by_user" ;
				break ;
		}

		if ( $this->data_has( $purpose ) && array_key_exists( $prop, $this->data[ $purpose ] ) ) {
			if ( $value !== $this->data[ $purpose ][ $prop ] || ( $this->changes_in( $purpose ) && array_key_exists( $prop, $this->changes[ $purpose ] ) ) ) {
				$this->changes[ $purpose ][ $prop ] = $value ;
			} else {
				$this->data[ $purpose ][ $prop ] = $value ;
			}
		}
	}

	/**
	 * Set user first_name.
	 *
	 * @param string $value first name.
	 */
	public function set_user_first_name( $value ) {
		$this->set_user_prop( 'first_name', 'address', $value ) ;
	}

	/**
	 * Set user last_name.
	 *
	 * @param string $value last name.
	 */
	public function set_user_last_name( $value ) {
		$this->set_user_prop( 'last_name', 'address', $value ) ;
	}

	/**
	 * Set user company.
	 *
	 * @param string $value company.
	 */
	public function set_user_company( $value ) {
		$this->set_user_prop( 'company', 'address', $value ) ;
	}

	/**
	 * Set user address_1.
	 *
	 * @param string $value address line 1.
	 */
	public function set_user_address_1( $value ) {
		$this->set_user_prop( 'address_1', 'address', $value ) ;
	}

	/**
	 * Set user address_2.
	 *
	 * @param string $value address line 2.
	 */
	public function set_user_address_2( $value ) {
		$this->set_user_prop( 'address_2', 'address', $value ) ;
	}

	/**
	 * Set user city.
	 *
	 * @param string $value city.
	 */
	public function set_user_city( $value ) {
		$this->set_user_prop( 'city', 'address', $value ) ;
	}

	/**
	 * Set user state.
	 *
	 * @param string $value state.
	 */
	public function set_user_state( $value ) {
		$this->set_user_prop( 'state', 'address', $value ) ;
	}

	/**
	 * Set user postcode.
	 *
	 * @param string $value postcode.
	 */
	public function set_user_postcode( $value ) {
		$this->set_user_prop( 'postcode', 'address', $value ) ;
	}

	/**
	 * Set user country.
	 *
	 * @param string $value country.
	 */
	public function set_user_country( $value ) {
		$this->set_user_prop( 'country', 'address', $value ) ;
	}

	/**
	 * Set user email.
	 *
	 * @param string $value email.
	 */
	public function set_user_email( $value ) {
		if ( $value && ! is_email( $value ) ) {
			throw new Exception( esc_html__( 'Invalid user email address.', 'credits-for-woocommerce' ) ) ;
		}

		$this->set_user_prop( 'email', 'address', sanitize_email( $value ) ) ;
	}

	/**
	 * Set user phone.
	 *
	 * @param string $value phone.
	 */
	public function set_user_phone( $value ) {
		$this->set_user_prop( 'phone', 'address', $value ) ;
	}

	/**
	 * Set total orders placed by the user after it has been saved.
	 *
	 * @param string
	 */
	public function set_total_orders_placed_by_user( $value ) {
		$this->set_user_prop( 'total_orders_placed', 'orders', absint( $value ) ) ;
	}

	/**
	 * Get total amount spent for the orders by the user after it has been saved.
	 *
	 * @param string
	 */
	public function set_total_amount_spent_by_user( $value ) {
		$this->set_user_prop( 'total_amount_spent', 'orders', wc_format_decimal( $value ) ) ;
	}

	/**
	 * Get highest amount spent for the order by the user after it has been saved.
	 *
	 * @param string
	 */
	public function set_highest_order_value_by_user( $value ) {
		$this->set_user_prop( 'highest_order_value', 'orders', wc_format_decimal( $value ) ) ;
	}

	/**
	 * Get lowest amount spent for the order by the user after it has been saved.
	 *
	 * @param string
	 */
	public function set_lowest_order_value_by_user( $value ) {
		$this->set_user_prop( 'lowest_order_value', 'orders', wc_format_decimal( $value ) ) ;
	}

	/**
	 * Get average monthly amount spent for the orders by the user after it has been saved.
	 *
	 * @param string
	 */
	public function set_avg_monthly_amount_spent_by_user( $value ) {
		$this->set_user_prop( 'avg_monthly_amount_spent', 'orders', wc_format_decimal( $value ) ) ;
	}

	/**
	 * Get average yearly amount spent for the orders by the user after it has been saved.
	 *
	 * @param string
	 */
	public function set_avg_yearly_amount_spent_by_user( $value ) {
		$this->set_user_prop( 'avg_yearly_amount_spent', 'orders', wc_format_decimal( $value ) ) ;
	}

}
