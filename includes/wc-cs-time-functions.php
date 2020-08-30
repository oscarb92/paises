<?php

defined( 'ABSPATH' ) || exit ;

/**
 * Parse about any English textual datetime description into a Unix timestamp
 * 
 * @param string $date
 * @param int|string $base
 * @return int
 */
function _wc_cs_maybe_strtotime( $date, $base = null ) {
	if ( ! $date ) {
		return time() ;
	}

	if ( is_numeric( $date ) ) {
		return absint( $date ) ;
	}

	if ( is_string( $date ) ) {
		if ( $base ) {
			$base = _wc_cs_maybe_strtotime( $base ) ;
		}

		return $base ? strtotime( $date, $base ) : strtotime( $date ) ;
	}
	return time() ;
}

/**
 * Prepare the given time in GMT/UTC date/time
 * 
 * @param int $time
 * @param string $format
 * @return string
 */
function _wc_cs_prepare_datetime( $time = null, $format = 'Y-m-d H:i:s' ) {
	if ( $time ) {
		$time = _wc_cs_maybe_strtotime( $time ) ;
		return gmdate( $format, $time ) ;
	}

	return gmdate( $format ) ;
}

/**
 * Get the time formatted in GMT/UTC 0 or +/- offset
 * 
 * @param string $type Type of time to retrieve. Accepts 'mysql', 'timestamp', or PHP date format string (e.g. 'Y-m-d').
 * @param array $args Accepted values are [
 *              'time' => Optional. A valid date/time string. If null then it returns the current time
 *              'base' => Optional. The timestamp which is used as a base for the calculation of relative dates
 *              'gmt'  => Optional. By default it will consider the WP offset
 *              ]
 * @return mixed
 */
function _wc_cs_get_time( $type = 'mysql', $args = array() ) {
	$args = wp_parse_args( $args, array(
		'time' => null,
		'base' => null,
		'gmt'  => false
			) ) ;

	$time = _wc_cs_maybe_strtotime( $args[ 'time' ], $args[ 'base' ] ) ;

	if ( 'mysql' === $type || 'timestamp' === $type ) {
		$format = 'Y-m-d H:i:s' ;
	} else {
		$format = $type ;
	}

	$time = ( $args[ 'gmt' ] ) ? _wc_cs_prepare_datetime( $time, $format ) : _wc_cs_prepare_datetime( ( $time + ( get_option( 'gmt_offset' ) * HOUR_IN_SECONDS ) ), $format ) ;

	if ( 'timestamp' === $type ) {
		$time = _wc_cs_maybe_strtotime( $time ) ;
	}

	return $time ;
}

/**
 * Format the date to output.
 * 
 * @param mixed $time A valid date/time string
 * @param bool $allow_time Need HH:MM:SS ?
 * @param bool $maybe_human Need human time in short period ?
 * @return string GMT/UTC +/- offset
 */
function _wc_cs_format_datetime( $time, $allow_time = true, $maybe_human = false ) {
	if ( ! $time ) {
		return '-' ;
	}

	$wp_date_format = '' !== get_option( 'date_format' ) ? get_option( 'date_format' ) : 'F j, Y' ;
	$wp_time_format = '' !== get_option( 'time_format' ) ? get_option( 'time_format' ) : 'g:i a' ;
	$time           = _wc_cs_maybe_strtotime( $time ) ;
	$time_diff      = _wc_cs_get_time( 'timestamp' ) - $time ;

	if ( $maybe_human && $time_diff > 0 && $time_diff < DAY_IN_SECONDS ) {
		/* translators: 1: time */
		$displ_time = sprintf( __( '%1$s ago', 'credits-for-woocommerce' ), human_time_diff( $time, _wc_cs_get_time( 'timestamp' ) ) ) ;
	} else {
		$displ_time = $allow_time ? date_i18n( "{$wp_date_format} {$wp_time_format}", $time ) : date_i18n( "{$wp_date_format}", $time ) ;
	}

	return $displ_time ;
}

/**
 * Get the human time difference for any time to output.
 * 
 * @param mixed $time A valid date/time string
 * @return string
 */
function _wc_cs_get_human_time_diff( $time ) {
	if ( ! $time ) {
		return '-' ;
	}

	if ( is_string( $time ) ) {
		if ( _wc_cs_maybe_strtotime( $time ) < _wc_cs_get_time( 'timestamp' ) ) {
			$time = null ;
		}
	} elseif ( is_numeric( $time ) ) {
		if ( absint( $time ) < _wc_cs_get_time( 'timestamp' ) ) {
			$time = null ;
		}
	}

	if ( $time ) {
		$fromDate = new DateTime( _wc_cs_prepare_datetime( _wc_cs_get_time( 'timestamp' ) ) ) ;
		$toDate   = new DateTime( _wc_cs_prepare_datetime( $time ) ) ;
		return $toDate->diff( $fromDate )->format( '<b>%a</b> day(s), <b>%H</b> hour(s), <b>%I</b> minute(s), <b>%S</b> second(s)' ) ;
	}

	return 'now' ;
}

/**
 * Check whether the threshold day gets crossed.
 * 
 * @param int $billing_day Day of month.
 * @return bool
 */
function _wc_cs_billing_day_threshold_crossed( $billing_day ) {
	$threshold_day = absint( get_option( WC_CS_PREFIX . 'get_threshold_day_of_month' ) ) ;
	$current_day   = _wc_cs_prepare_datetime( _wc_cs_get_time( 'timestamp' ), 'd' ) ;

	if ( $threshold_day === $billing_day || $current_day < $threshold_day ) {
		return false ;
	}

	return true ;
}

/**
 * Calculate the bill date.
 * 
 * @param int $billing_day Day of month.
 * @param bool $check_threshold True on demand.
 * @return int
 */
function _wc_cs_calculate_billing_date( $billing_day = null, $check_threshold = false ) {
	$billing_day = is_numeric( $billing_day ) ? absint( $billing_day ) : absint( get_option( WC_CS_PREFIX . 'get_billing_day_of_month' ) ) ;

	if ( $check_threshold && _wc_cs_billing_day_threshold_crossed( $billing_day ) ) {
		$base_time = _wc_cs_get_time( 'timestamp', array( 'time' => 'second month' ) ) ;
	} else {
		$base_time = _wc_cs_get_time( 'timestamp', array( 'time' => 'next month' ) ) ;
	}

	$m            = _wc_cs_prepare_datetime( $base_time, 'm' ) ;
	$y            = _wc_cs_prepare_datetime( $base_time, 'Y' ) ;
	$billing_date = _wc_cs_maybe_strtotime( "{$y}/{$m}/{$billing_day} 23:59:59" ) ;

	return $billing_date ;
}

/**
 * Calculate the due date.
 * 
 * @param string|int $billing_date
 * @param int $due_day Day of month.
 * @param string $repayment_month Want this month|next month ?
 * @return int
 */
function _wc_cs_calculate_due_date( $billing_date, $due_day = null, $repayment_month = 'this-month' ) {
	$due_day = is_numeric( $due_day ) ? absint( $due_day ) : absint( get_option( WC_CS_PREFIX . 'get_due_day_of_month' ) ) ;

	if ( 'next-month' === $repayment_month ) {
		$base_time = _wc_cs_get_time( 'timestamp', array( 'time' => 'next month', 'base' => $billing_date ) ) ;
	} else {
		$base_time = _wc_cs_get_time( 'timestamp', array( 'time' => 'this month', 'base' => $billing_date ) ) ;
	}

	$m        = _wc_cs_prepare_datetime( $base_time, 'm' ) ;
	$y        = _wc_cs_prepare_datetime( $base_time, 'Y' ) ;
	$due_date = _wc_cs_maybe_strtotime( "{$y}/{$m}/{$due_day} 23:59:59" ) ;

	return $due_date ;
}

/**
 * Retrieve the array of dates between the given dates.
 * 
 * @param mixed $start_time A valid date/time string
 * @param mixed $end_time A valid date/time string
 * @param array $days_count Should be either array( '1','2','3',... ) or array( ...,'3','2','1' )
 * @param string $default_sortby asc|desc
 * @return array
 */
function _wc_cs_get_dates( $start_time, $end_time, $days_count, $default_sortby = 'asc' ) {
	$dates = array() ;

	if ( empty( $days_count ) || ! is_array( $days_count ) ) {
		return $dates ;
	}

	$start_time = _wc_cs_maybe_strtotime( $start_time ) ;
	$end_time   = _wc_cs_maybe_strtotime( $end_time ) ;
	$sortby     = 1 === count( $days_count ) ? $default_sortby : _wc_cs_array_sorted_by( $days_count ) ;

	foreach ( $days_count as $day_count ) {
		$day_count = absint( $day_count ) ;

		if ( $day_count ) {
			if ( 'asc' === $sortby ) {
				$datetime = _wc_cs_get_time( 'timestamp', array( 'time' => "+{$day_count} days", 'base' => $start_time ) ) ;

				if ( $datetime <= $end_time ) {
					$dates[] = $datetime ;
				}
			} else {
				$datetime = _wc_cs_get_time( 'timestamp', array( 'time' => "-{$day_count} days", 'base' => $end_time ) ) ;

				if ( $datetime >= $start_time ) {
					$dates[] = $datetime ;
				}
			}
		}
	}

	if ( $dates ) {
		$dates = array_unique( $dates ) ;
		sort( $dates ) ;
	}

	return $dates ;
}

/**
 * Return the available years to display since 2019.
 * 
 * @return array
 */
function _wc_cs_get_years() {
	$current_year = _wc_cs_get_time( 'Y' ) ;
	$years        = array() ;

	for ( $year = 2019 ; $year <= $current_year ; $year ++ ) {
		$years[] = $year ;
	}

	return $years ;
}

/**
 * Return the available months to display.
 * 
 * @return array
 */
function _wc_cs_get_months() {
	return array(
		'01' => __( 'January', 'credits-for-woocommerce' ),
		'02' => __( 'February', 'credits-for-woocommerce' ),
		'03' => __( 'March', 'credits-for-woocommerce' ),
		'04' => __( 'April', 'credits-for-woocommerce' ),
		'05' => __( 'May', 'credits-for-woocommerce' ),
		'06' => __( 'June', 'credits-for-woocommerce' ),
		'07' => __( 'July', 'credits-for-woocommerce' ),
		'08' => __( 'August', 'credits-for-woocommerce' ),
		'09' => __( 'September', 'credits-for-woocommerce' ),
		'10' => __( 'October', 'credits-for-woocommerce' ),
		'11' => __( 'November', 'credits-for-woocommerce' ),
		'12' => __( 'December', 'credits-for-woocommerce' ),
			) ;
}
