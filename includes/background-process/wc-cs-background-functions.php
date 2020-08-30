<?php

defined( 'ABSPATH' ) || exit ;

/**
 * Check the heartbeat for credits.
 * 
 * Based upon the rules, we create the credits to the user or update the credit line to the users based upon the admin option.
 * 
 * @param object $item
 */
function wc_cs_credits_heartbeat( $item ) {

	if ( ! WC_CS_Credit_Line_Rules::exists() ) {
		return ;
	}

	try {
		$active_users = array() ;

		if ( _wc_cs()->is_on_auto_approval() ) {

			/*
			  |--------------------------------------------------------------------------
			  | Check with all WP Users on Auto Approval
			  |--------------------------------------------------------------------------
			 */
			$wp_users = get_users( array(
				'fields'      => 'ids',
				'count_total' => false
					) ) ;

			foreach ( $wp_users as $user_id ) {
				$credits = _wc_cs_get_credits_from_user_id( $user_id ) ;

				if ( $credits ) {
					if ( $credits->has_status( 'trash' ) ) {
						continue ;
					}

					$credits->maybe_apply_lt_2pt0_compatibility( true ) ;

					if ( $credits->is_manual() || ! $credits->has_status( array( 'pending', 'on_hold', 'active' ) ) ) {
						continue ;
					}
				}

				$rule_applied   = $credits ? $credits->get_rule_applied() : null ;
				$satisfied_rule = WC_CS_Credit_Line_Rules::get_satisfied_rule( $user_id, $rule_applied ) ;

				if ( false === $satisfied_rule ) {
					continue ;
				}

				if ( 'new' === $satisfied_rule->status ) {
					$credits_created_via_rule = false ;

					/*
					  |--------------------------------------------------------------------------
					  | Apply new rule
					  |--------------------------------------------------------------------------
					  |
					  | Create new credits post to the user if doesn't exist.
					  |
					 */

					if ( ! $credits ) {
						$credits_created_via_rule = _wc_cs_create_credits( array_merge( array(
							'user_id'     => $user_id,
							'created_via' => 'rule'
										), _wc_cs_prepare_userdata( $user_id ) ) ) ;

						if ( is_wp_error( $credits_created_via_rule ) ) {
							continue ;
						}

						$credits = $credits_created_via_rule ;
					}

					if ( ! $credits->can_apply_rule() ) {
						continue ;
					}

					if ( $credits_created_via_rule ) {
						remove_action( 'wc_cs_credits_usage_limit_increased_notification', array( 'WC_CS_Email_Credit_Limit_Updated', 'trigger' ) ) ;
						remove_action( 'wc_cs_credits_usage_limit_decreased_notification', array( 'WC_CS_Email_Credit_Limit_Updated', 'trigger' ) ) ;
						$applied = $credits->apply_rule( $satisfied_rule ) ;
						add_action( 'wc_cs_credits_usage_limit_increased_notification', array( 'WC_CS_Email_Credit_Limit_Updated', 'trigger' ) ) ;
						add_action( 'wc_cs_credits_usage_limit_decreased_notification', array( 'WC_CS_Email_Credit_Limit_Updated', 'trigger' ) ) ;

						if ( ! $applied ) {
							continue ;
						}

						do_action( 'wc_cs_credits_created_via_rule_and_activated', $credits ) ;
					} else {
						if ( ! $credits->apply_rule( $satisfied_rule ) ) {
							continue ;
						}
					}
				}

				/*
				  |--------------------------------------------------------------------------
				  | Monitor Active No. of Users
				  |--------------------------------------------------------------------------
				  |
				  | Save in cache.
				  |
				 */

				if ( ! isset( $active_users[ $satisfied_rule->id ] ) ) {
					$active_users[ $satisfied_rule->id ] = 0 ;
				}

				$active_users[ $satisfied_rule->id ] ++ ;

				WC_CS_Credit_Line_Rules::set_no_of_users( $satisfied_rule->id, $active_users[ $satisfied_rule->id ] ) ;
			}
		} else {
			/*
			  |--------------------------------------------------------------------------
			  | Check with all Credits available posts on App Based Approval
			  |--------------------------------------------------------------------------
			 */
			$all_credits = _wc_cs_get_all_credits() ;

			if ( ! empty( $all_credits ) ) {
				foreach ( $all_credits as $credits_id ) {
					$credits = _wc_cs_get_credits( $credits_id ) ;

					if ( ! $credits || $credits->has_status( 'trash' ) ) {
						continue ;
					}

					$credits->maybe_apply_lt_2pt0_compatibility( true ) ;

					if ( $credits->is_manual() || ! $credits->has_status( array( 'pending', 'on_hold', 'active' ) ) ) {
						continue ;
					}

					$rule_applied   = $credits ? $credits->get_rule_applied() : null ;
					$satisfied_rule = WC_CS_Credit_Line_Rules::get_satisfied_rule( $credits->get_user_id(), $rule_applied ) ;

					if ( false === $satisfied_rule ) {
						continue ;
					}

					if ( 'new' === $satisfied_rule->status ) {

						if ( ! $credits->can_apply_rule() ) {
							continue ;
						}

						/*
						  |--------------------------------------------------------------------------
						  | Apply new rule
						  |--------------------------------------------------------------------------
						 */
						if ( ! $credits->apply_rule( $satisfied_rule ) ) {
							continue ;
						}
					}

					/*
					  |--------------------------------------------------------------------------
					  | Monitor Active No. of Users
					  |--------------------------------------------------------------------------
					  |
					  | Save in cache.
					  |
					 */

					if ( ! isset( $active_users[ $satisfied_rule->id ] ) ) {
						$active_users[ $satisfied_rule->id ] = 0 ;
					}

					$active_users[ $satisfied_rule->id ] ++ ;

					WC_CS_Credit_Line_Rules::set_no_of_users( $satisfied_rule->id, $active_users[ $satisfied_rule->id ] ) ;
				}
			}
		}

		/*
		  |--------------------------------------------------------------------------
		  | Update the Rules from Cache
		  |--------------------------------------------------------------------------
		 */
		WC_CS_Credit_Line_Rules::update() ;
	} catch ( Exception $e ) {
		return ;
	}
}

/**
 * Create the bill statement.
 * 
 * @param object $item
 */
function wc_cs_credits_create_bill_statement( $item ) {
	$credits = _wc_cs_get_credits( $item->credits ) ;

	if ( ! $credits || $credits->has_status( 'trash' ) ) {
		return ;
	}

	try {
		/*
		  |--------------------------------------------------------------------------
		  | Bill generation health check
		  |--------------------------------------------------------------------------
		  |
		  | Check the buffer time repeatedly when generating the bill. By default, 3 days in seconds.
		  | It might be used when the WP-Cron system gets disturbed for some reasons then make sure if we need to postpone this bill to next month.
		  |
		 */
		$buffer_time    = absint( apply_filters( 'wc_cs_get_bill_statement_creation_buffer_time', 259200 ) ) ;
		$current_date   = _wc_cs_get_time( 'timestamp' ) ;
		$statement_date = _wc_cs_maybe_strtotime( $credits->get_next_bill_date( 'edit' ) ) ;

		if ( $current_date > ( $statement_date + $buffer_time ) ) {
			_wc_cs_cancel_job_from_queue( 'credits', 'remind_payment_due', $credits->get_id() ) ;
			_wc_cs_cancel_job_from_queue( 'credits', 'charge_late_payment', $credits->get_id() ) ;

			$credits->set_props( array(
				'status'         => 'overdue',
				'next_bill_date' => _wc_cs_calculate_billing_date( $credits->get_billing_day( 'edit' ) ),
			) ) ;
			$credits->save() ;

			_wc_cs_push_job_to_queue( 'credits', 'create_bill_statement', $credits->get_next_bill_date( 'edit' ), $credits->get_id() ) ;
			return ;
		}

		/*
		  |--------------------------------------------------------------------------
		  | Start the bill generation process
		  |--------------------------------------------------------------------------
		 */

		/* Create the bill statement process */
		$bill_statement = new WC_CS_Bill_Statement() ;
		$hash           = _wc_cs_generate_statement_hash( $statement_date ) ;

		$bill_statement->set_props( array(
			'credits_id'        => $credits->get_id(),
			'user_id'           => $credits->get_user_id(),
			'hash'              => $hash[ 'value' ],
			'date_created'      => $statement_date,
			'total_outstanding' => $credits->get_total_outstanding_amount( 'edit' ),
			'prev_amount_due'   => $credits->get_unpaid_previous_bill_amount( 'edit' ),
			'from_date'         => _wc_cs_get_time( 'timestamp', array( 'time' => '+1 day', 'base' => ( '' === $credits->get_last_billed_date() ? $credits->get_approved_date() : $credits->get_last_billed_date() ) ) ),
			'to_date'           => $statement_date,
		) ) ;

		// Collect the unbilled transactions for this bill statement
		$unbilled = $credits->get_transactions( 'unbilled' ) ;
		if ( ! empty( $unbilled ) ) {
			$other_debits = 0 ;

			foreach ( $unbilled as $txn ) {
				$other_debits += $txn->get_debited( 'edit' ) ;
				$txn->set_props( array(
					'status'      => 'billed',
					'billed_date' => $bill_statement->get_date_created( 'edit' ),
					'key'         => $bill_statement->get_hash( 'edit' ),
				) ) ;
				$txn->save() ;
			}

			$bill_statement->set_other_debits( $other_debits ) ;
		}

		if ( $bill_statement->get_total_outstanding( 'edit' ) > 0 ) {
			$bill_statement->set_due_date( _wc_cs_calculate_due_date( $bill_statement->get_date_created( 'edit' ), $credits->get_due_day( 'edit' ), $credits->get_due_duration_by( 'edit' ) ) ) ;
		}

		/* End of bill statement creation process */
		$bill_statement->save() ;

		/* Assign the bill created to the credits */
		$statements                              = $credits->get_statements() ;
		$statements[ $bill_statement->get_id() ] = $hash[ 'index' ] ;

		$credits->set_props( array(
			'last_billed_status'    => $bill_statement->get_total_outstanding( 'edit' ) > 0 ? 'unpaid' : 'paid',
			'last_billed_date'      => $bill_statement->get_date_created( 'edit' ),
			'last_billed_due_date'  => $bill_statement->get_due_date( 'edit' ),
			'last_billed_amount'    => $bill_statement->get_total_outstanding( 'edit' ),
			'last_payment_order_id' => 0,
			'statements'            => $statements,
			'next_bill_date'        => _wc_cs_calculate_billing_date( $credits->get_billing_day( 'edit' ) ),
		) ) ;

		/* End of assigning the bill created to the credits */
		$credits->save() ;

		/* Start scheduling the repayment process */
		if ( '' !== $credits->get_last_billed_due_date( 'edit' ) ) {
			if ( ! _wc_cs_job_exists_in_queue( 'credits', 'charge_late_payment', $credits->get_last_billed_due_date( 'edit' ), $credits->get_id() ) ) {
				_wc_cs_push_job_to_queue( 'credits', 'charge_late_payment', $credits->get_last_billed_due_date( 'edit' ), $credits->get_id() ) ;
			}

			$days_to_remind_payment_due  = array_map( 'trim', explode( ',', get_option( WC_CS_PREFIX . 'remind_payment_due_after' ) ) ) ;
			$dates_to_remind_payment_due = _wc_cs_get_dates( $current_date, $credits->get_last_billed_due_date( 'edit' ), $days_to_remind_payment_due ) ;

			if ( empty( $dates_to_remind_payment_due ) ) {
				return ;
			}

			foreach ( $dates_to_remind_payment_due as $count => $remind_date ) {
				if ( ! _wc_cs_job_exists_in_queue( 'credits', 'remind_payment_due', $remind_date, $credits->get_id() ) ) {
					_wc_cs_push_job_to_queue( 'credits', 'remind_payment_due', $remind_date, $credits->get_id(), array( 'reminder count' => _wc_cs_get_number_suffix( 1 + $count ) ) ) ;
				}
			}
		}
		/* End of scheduling the repayment process */

		/*
		  |--------------------------------------------------------------------------
		  | End of bill generation process
		  |--------------------------------------------------------------------------
		 */
		do_action( 'wc_cs_bill_generated', $credits, $bill_statement ) ;

		/* Schedule next bill generation process */
		_wc_cs_push_job_to_queue( 'credits', 'create_bill_statement', $credits->get_next_bill_date( 'edit' ), $credits->get_id() ) ;
	} catch ( Exception $e ) {
		return ;
	}
}

/**
 * Remind the users about the payment due.
 * 
 * @param object $item
 */
function wc_cs_credits_remind_payment_due( $item ) {
	$credits = _wc_cs_get_credits( $item->credits ) ;

	if ( ! $credits || $credits->has_status( 'trash' ) || 'unpaid' !== $credits->get_last_billed_status( 'édit' ) ) {
		return ;
	}

	do_action( 'wc_cs_remind_payment_due', $credits ) ;
}

/**
 * Charge the late fee and put on Overdue.
 * 
 * @param object $item
 */
function wc_cs_credits_charge_late_payment( $item ) {
	$credits = _wc_cs_get_credits( $item->credits ) ;

	if ( ! $credits || $credits->has_status( 'trash' ) ) {
		return ;
	}

	try {
		if ( 'unpaid' !== $credits->get_last_billed_status( 'édit' ) ) {
			return ;
		}

		//Modify the last bill amount if any refund is given after the bill is generated
		$amount_to_pay = min( $credits->get_total_outstanding_amount( 'edit' ), $credits->get_last_billed_amount( 'édit' ) ) ;

		// If no total outstanding amount is available for the user then Activate the credits
		if ( $amount_to_pay <= 0 ) {
			$credits->set_props( array(
				'status'                      => 'active',
				'last_billed_status'          => 'paid',
				'last_billed_amount'          => 0,
				'unpaid_previous_bill_amount' => 0,
			) ) ;
			$credits->save() ;
			return ;
		}

		// Charge the late payment
		$late_fee = _wc_cs_maybe_calculate_late_payment_fee( $amount_to_pay ) ;

		// If no late fee is set to charge by the admin then set as Overdue
		if ( $late_fee <= 0 ) {
			$credits->set_props( array(
				'status'                      => 'overdue',
				'last_billed_status'          => 'unbilled',
				'last_billed_amount'          => $amount_to_pay,
				'unpaid_previous_bill_amount' => $amount_to_pay,
			) ) ;
			$credits->save() ;
			return ;
		}

		/*
		  |--------------------------------------------------------------------------
		  | Start the late fee charging process
		  |--------------------------------------------------------------------------
		 */

		$credits_txn = _wc_cs_create_credits_txn( array(
			'credits_id' => $credits->get_id(),
			'user_id'    => $credits->get_user_id(),
			'activity'   => __( 'Late Fee', 'credits-for-woocommerce' ),
			'debited'    => $late_fee,
			'balance'    => $credits->get_available_credits( 'edit' ),
			'type'       => 'late-fee-debits',
				) ) ;

		_wc_cs_add_profit_gained( $late_fee ) ;

		do_action( 'wc_cs_late_fee_charged', $credits, $credits_txn ) ;

		$credits->set_props( array(
			'status'                      => 'overdue',
			'last_billed_status'          => 'unbilled',
			'last_billed_amount'          => $amount_to_pay,
			'unpaid_previous_bill_amount' => $amount_to_pay,
			'total_outstanding_amount'    => $credits->get_total_outstanding_amount( 'edit' ) + $late_fee,
			'available_credits'           => $credits->get_available_credits( 'edit' ) - $late_fee,
		) ) ;

		/*
		  |--------------------------------------------------------------------------
		  | End of late fee charging process
		  |--------------------------------------------------------------------------
		 */
		$credits->save() ;
	} catch ( Exception $e ) {
		return ;
	}
}

/**
 * Removes schedules belonging to a deleted post.
 *
 * @param mixed $id ID of post being deleted.
 */
function wc_cs_credits_delete_post( $id ) {
	if ( ! $id ) {
		return ;
	}

	if ( 'wc_cs_credits' === get_post_type( $id ) ) {
		_wc_cs_cancel_all_jobs_from_queue( 'credits', $id ) ;
	}
}

add_action( 'delete_post', 'wc_cs_credits_delete_post' ) ;
