<?php

/**
 * Our Templates
 *
 * Functions for the templating system.
 */
defined( 'ABSPATH' ) || exit ;

/**
 * My dashboard > Make repayment template.
 */
function _wc_cs_make_repayment() {

	_wc_cs_get_template( 'mydashboard/make-repayment.php', array(
		'dashboard' => _wc_cs()->dashboard,
			)
	) ;
}

/**
 * My dashboard > View unbilled Transactions template.
 */
function _wc_cs_view_unbilled_txns() {

	_wc_cs_get_template( 'mydashboard/view-unbilled-txns.php', array(
		'dashboard' => _wc_cs()->dashboard,
			)
	) ;
}

/**
 * My dashboard > View previous statements template.
 */
function _wc_cs_view_previous_statements() {

	_wc_cs_get_template( 'mydashboard/view-statements.php', array(
		'dashboard' => _wc_cs()->dashboard,
			)
	) ;
}

/**
 * My Account > Add Funds template.
 */
function _wc_cs_view_add_funds() {

	_wc_cs_get_template( 'myaccount/add-funds-form.php', array(
		'min_amt' => _wc_cs()->funds_addition->get_min_to_add() > 0 ? _wc_cs()->funds_addition->get_min_to_add() : '0.01',
		'max_amt' => _wc_cs()->funds_addition->get_max_to_add() > 0 ? _wc_cs()->funds_addition->get_max_to_add() : '',
	) ) ;
}
