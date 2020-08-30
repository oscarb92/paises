<?php

defined( 'ABSPATH' ) || exit ;

include_once('wc-cs-conditional-functions.php') ;
include_once('wc-cs-time-functions.php') ;
include_once('wc-cs-credits-functions.php') ;
include_once('wc-cs-user-functions.php') ;
include_once('wc-cs-account-functions.php') ;
include_once('wc-cs-template-functions.php') ;
include_once('queue/wc-cs-queue-functions.php') ;

/**
 * Get our templates.
 *
 * @param string $template_name
 * @param array $args (default: array())
 * @param string $template_path (default: 'WC_CS_BASENAME_DIR')
 * @param string $default_path (default: WC_CS_TEMPLATE_PATH)
 */
function _wc_cs_get_template( $template_name, $args = array() ) {
	if ( ! $template_name ) {
		return ;
	}

	wc_get_template( $template_name, $args, WC_CS_BASENAME_DIR, WC_CS_TEMPLATE_PATH ) ;
}

/**
 * Return the valid order statuses to complete the order placed via Credits.
 * 
 * @return array
 */
function _wc_cs_get_order_statuses_for_credits() {
	$stauses         = wc_get_order_statuses() ;
	$invalid_stauses = array( 'pending', 'on-hold', 'cancelled', 'refunded', 'failed' ) ;

	foreach ( $invalid_stauses as $status ) {
		unset( $stauses[ 'wc-' . $status ] ) ;
	}

	return $stauses ;
}

/**
 * Generates a random key.
 * 
 * @param string $prefix
 * @param int $length
 * @return string
 */
function _wc_cs_generate_key( $prefix = WC_CS_PREFIX, $length = 13 ) {
	if ( ! $prefix || ! is_string( $prefix ) ) {
		$prefix = '' ;
	}

	$length = absint( $length ) ;
	$key    = $prefix ;
	$key    .= wp_generate_password( $length, false ) ;

	return $key ;
}

/**
 * Get the type in which the array is sorted by.
 * 
 * @param array $array
 * @return boolean|string
 */
function _wc_cs_array_sorted_by( $array ) {
	$o_array = $array ;

	$asc = $o_array ;
	sort( $asc ) ;
	if ( $o_array === $asc ) {
		return 'asc' ;
	}

	$desc = $o_array ;
	rsort( $desc ) ;
	if ( $o_array === $desc ) {
		return 'desc' ;
	}

	return false ;
}

/**
 * Get Number Suffix to Display.
 * 
 * @param int $number
 * @return string
 */
function _wc_cs_get_number_suffix( $number ) {
	// Special case 'teenth'
	if ( ( $number / 10 ) % 10 != 1 ) {
		// Handle 1st, 2nd, 3rd
		switch ( $number % 10 ) {
			case 1:
				return $number . 'st' ;
			case 2:
				return $number . 'nd' ;
			case 3:
				return $number . 'rd' ;
		}
	}
	// Everything else is 'nth'
	return $number . 'th' ;
}

/**
 * Calculate the interest rate for the given amount. 
 * 
 * @param float|int $amount
 * @return int|string
 */
function _wc_cs_maybe_calculate_interest( $amount ) {
	if ( 'yes' !== get_option( WC_CS_PREFIX . 'charge_interest_for_credit_usage' ) ) {
		return 0 ;
	}

	if ( ! $amount || ! is_numeric( $amount ) ) {
		return 0 ;
	}

	$amount         = wc_format_decimal( $amount, 2 ) ;
	$interest_value = wc_format_decimal( get_option( WC_CS_PREFIX . 'credit_usage_interest_value', '0' ), 2 ) ;

	if ( 'percent' === get_option( WC_CS_PREFIX . 'credit_usage_interest_type' ) ) {
		$interest = ( $amount / 100 ) * $interest_value ;
	} else {
		$interest = $interest_value ;
	}

	return wc_format_decimal( $interest, 2 ) ;
}

/**
 * Calculate the late payment fee for the given amount. 
 * 
 * @param float|int $amount
 * @return int|string
 */
function _wc_cs_maybe_calculate_late_payment_fee( $amount ) {
	if ( 'yes' !== get_option( WC_CS_PREFIX . 'charge_late_payment_fee' ) ) {
		return 0 ;
	}

	if ( ! $amount || ! is_numeric( $amount ) ) {
		return 0 ;
	}

	$amount         = wc_format_decimal( $amount, 2 ) ;
	$late_fee_value = wc_format_decimal( get_option( WC_CS_PREFIX . 'late_payment_fee_value', '0' ), 2 ) ;

	if ( 'percent' === get_option( WC_CS_PREFIX . 'late_payment_fee_type' ) ) {
		$late_fee = ( $amount / 100 ) * $late_fee_value ;
	} else {
		$late_fee = $late_fee_value ;
	}

	return wc_format_decimal( $late_fee, 2 ) ;
}

/**
 * Return the available payment gateways.
 * 
 * @return array
 */
function _wc_cs_get_valid_payment_gateways() {
	$valid              = array() ;
	$available_gateways = WC()->payment_gateways->get_available_payment_gateways() ;

	foreach ( $available_gateways as $key => $gateway ) {
		if ( $gateway->supports( WC_CS_PREFIX . 'credits' ) ) {
			continue ;
		}

		$valid[ $key ] = $gateway->get_title() ;
	}

	return $valid ;
}

/**
 * Add the profit gained by Admin.
 * Use for Interest charges, Late fee charges etc.
 * 
 * @param float $amount
 * @return true on Success
 */
function _wc_cs_add_profit_gained( $amount ) {
	if ( ! is_numeric( $amount ) ) {
		return null ;
	}

	$total_profit_gained = wc_format_decimal( get_option( WC_CS_PREFIX . 'total_profit_gained', '0' ), 2 ) ;
	$newly_gained_profit = wc_format_decimal( wp_unslash( $amount ), 2 ) ;

	if ( $newly_gained_profit < 0 ) {
		$newly_gained_profit *= -1 ;
	}

	update_option( WC_CS_PREFIX . 'total_profit_gained', ( $total_profit_gained + $newly_gained_profit ) ) ;
	return true ;
}
