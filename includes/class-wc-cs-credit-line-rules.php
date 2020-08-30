<?php

defined( 'ABSPATH' ) || exit ;

/**
 * Credit Line Rules Handler
 * 
 * @class WC_CS_Credit_Line_Rules
 * @package Class
 */
class WC_CS_Credit_Line_Rules {

	/**
	 * Retrieve the rules.
	 * 
	 * @var array 
	 */
	protected static $rules ;

	/**
	 * Retrieve the active users count.
	 * 
	 * @var array 
	 */
	protected static $active_users = array() ;

	/**
	 * Retrieve the array of rules.
	 * 
	 * @return array
	 */
	public static function get_rules() {
		if ( is_null( self::$rules ) ) {
			self::$rules = get_option( WC_CS_PREFIX . 'credit_line_rules', array() ) ;
		}

		return self::$rules ;
	}

	/**
	 * Check whether the rules exists.
	 * 
	 * @return bool
	 */
	public static function exists() {
		return ! empty( self::get_rules() ) ;
	}

	/**
	 * Set active number of users count cache.
	 * 
	 * @param int $rule_id
	 * @param int $count
	 */
	public static function set_no_of_users( $rule_id, $count ) {
		self::$active_users[ $rule_id ] = absint( $count ) ;
	}

	/**
	 * Update the rules from cache.
	 */
	public static function update() {
		self::get_rules() ;

		if ( ! empty( self::$rules ) ) {
			foreach ( self::$rules as $rule_id => $rule ) {
				if ( isset( self::$active_users[ $rule_id ] ) ) {
					self::$rules[ $rule_id ][ 'no_of_users' ] = self::$active_users[ $rule_id ] ;
				} else {
					self::$rules[ $rule_id ][ 'no_of_users' ] = 0 ;
				}
			}

			update_option( WC_CS_PREFIX . 'credit_line_rules', self::$rules ) ;
		}
	}

	/**
	 * Get the rule which is satisfied to the user.
	 * 
	 * @param int $user_id
	 * @param int $rule_applied
	 * @return array
	 */
	public static function get_satisfied_rule( $user_id, $rule_applied = null ) {
		$satisfied_rule      = false ;
		$applied_rule_exists = array_key_exists( $rule_applied, self::get_rules() ) ? true : false ;

		foreach ( self::get_rules() as $rule_id => $rule ) {
			if ( $applied_rule_exists ) {
				if ( $rule_id >= $rule_applied ) {
					$satisfied_rule         = ( object ) $rule ;
					$satisfied_rule->id     = $rule_id ;
					$satisfied_rule->status = 'already_applied' ;
					break ;
				}
			}

			if ( empty( $rule[ 'criteria' ] ) || 0 === absint( $rule[ 'priority' ] ) ) {
				continue ;
			}

			foreach ( $rule[ 'criteria' ] as $group ) {
				if ( empty( $group ) ) {
					continue ;
				}

				$criteria_satisfied = 0 ;
				foreach ( $group as $criteria ) {
					if ( empty( $criteria[ 'action' ] ) ) {
						continue ;
					}

					switch ( $criteria[ 'action' ] ) {
						case 'user_as':
							$user_data = get_userdata( $user_id ) ;

							if ( $user_data && in_array( $criteria[ 'user_role' ], $user_data->roles ) ) {
								$criteria_satisfied ++ ;
							}
							break ;
						case 'user_not_as':
							$user_data = get_userdata( $user_id ) ;

							if ( $user_data && ! in_array( $criteria[ 'user_role' ], $user_data->roles ) ) {
								$criteria_satisfied ++ ;
							}
							break ;
						case 'user_registered_for':
							$user_data = get_userdata( $user_id ) ;

							if ( $user_data ) {
								$registered_period_compare  = isset( $criteria[ 'registered_period_compare' ] ) ? $criteria[ 'registered_period_compare' ] : 'less-than' ;
								$registered_period_interval = isset( $criteria[ 'registered_period_interval' ] ) ? absint( $criteria[ 'registered_period_interval' ] ) : '1' ;
								$registered_period          = isset( $criteria[ 'registered_period' ] ) ? trim( $criteria[ 'registered_period' ] ) : 'days' ;
								$current_time               = _wc_cs_get_time( 'timestamp' ) ;
								$user_registered_time_till  = _wc_cs_get_time( 'timestamp', array( 'time' => "+{$registered_period_interval} {$registered_period}", 'base' => $user_data->user_registered ) ) ;

								if ( 'more-than' === $registered_period_compare ) {
									if ( $current_time > $user_registered_time_till ) {
										$criteria_satisfied ++ ;
									}
								} else {
									if ( $current_time < $user_registered_time_till ) {
										$criteria_satisfied ++ ;
									}
								}
							}
							break ;
						case 'user_total_orders_amt_less_than_r_eql_to':
							$total_orders_amount = _wc_cs_get_total_amount_spent_by_user( $user_id ) ;

							if ( isset( $criteria[ 'orders_amount' ] ) && floatval( $total_orders_amount ) <= floatval( $criteria[ 'orders_amount' ] ) ) {
								$criteria_satisfied ++ ;
							}
							break ;
						case 'user_total_orders_amt_more_than_r_eql_to':
							$total_orders_amount = _wc_cs_get_total_amount_spent_by_user( $user_id ) ;

							if ( isset( $criteria[ 'orders_amount' ] ) && floatval( $total_orders_amount ) >= floatval( $criteria[ 'orders_amount' ] ) ) {
								$criteria_satisfied ++ ;
							}
							break ;
						case 'user_placed_orders_count_less_than_r_eql_to':
							$total_orders = _wc_cs_get_total_orders_placed_by_user( $user_id ) ;

							if ( isset( $criteria[ 'orders_count' ] ) && $total_orders <= absint( $criteria[ 'orders_count' ] ) ) {
								$criteria_satisfied ++ ;
							}
							break ;
						case 'user_placed_orders_count_more_than_r_eql_to':
							$total_orders = _wc_cs_get_total_orders_placed_by_user( $user_id ) ;

							if ( isset( $criteria[ 'orders_count' ] ) && $total_orders >= absint( $criteria[ 'orders_count' ] ) ) {
								$criteria_satisfied ++ ;
							}
							break ;
					}
				}

				if ( $criteria_satisfied > 0 && count( $group ) === $criteria_satisfied ) {
					$satisfied_rule         = ( object ) $rule ;
					$satisfied_rule->id     = $rule_id ;
					$satisfied_rule->status = 'new' ;
					break 2;
				}
			}
		}

		return $satisfied_rule ;
	}

	/**
	 * Get the credit limit which is eligible to the user.
	 * 
	 * @param int $user_id
	 * @param int $rule_applied
	 * @return float
	 */
	public static function get_eligible_credit_limit_by_user( $user_id, $rule_applied = null ) {
		$satisfied_rule = self::get_satisfied_rule( $user_id, $rule_applied ) ;

		if ( false === $satisfied_rule ) {
			return 0 ;
		}

		return floatval( $satisfied_rule->credit_limit ) ;
	}

}
