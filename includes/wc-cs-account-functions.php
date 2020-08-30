<?php

defined( 'ABSPATH' ) || exit ;

/**
 * Add our menus in My Account menu items.
 *
 * @param array $items
 * @return array
 */
function _wc_cs_add_account_menu_items( $items ) {
	if ( ! _wc_cs()->funds_addition->current_user_is_eligible() ) {
		return $items ;
	}

	$menu     = array( 'wc-cs-add-funds' => esc_html__( 'Add Funds', 'credits-for-woocommerce' ) ) ;
	$position = 2 ;
	$items    = array_slice( $items, 0, $position ) + $menu + array_slice( $items, $position, count( $items ) - 1 ) ;
	return $items ;
}

/**
 * Add our query vars.
 *
 * @return array
 */
function _wc_cs_add_account_query_vars( $query_vars ) {
	if ( ! _wc_cs()->funds_addition->current_user_is_eligible() ) {
		return $query_vars ;
	}

	$query_vars[ 'wc-cs-add-funds' ] = 'wc-cs-add-funds' ;
	return $query_vars ;
}

/**
 * Add our query vars endpoint title
 *
 * @return string
 */
function _wc_cs_get_account_current_endpoint_title( $title, $endpoint ) {
	if ( ! _wc_cs()->funds_addition->current_user_is_eligible() ) {
		return $title ;
	}

	switch ( $endpoint ) {
		case 'wc-cs-add-funds':
			$title = __( 'Add Funds', 'credits-for-woocommerce' ) ;
			break ;
	}

	return $title ;
}

/**
 * Add credits notices to user.
 */
function _wc_cs_add_account_credits_notices() {
	$after_receiving_notice = trim( get_option( WC_CS_PREFIX . 'after_receiving_credits_notice' ) ) ;

	if ( '' !== $after_receiving_notice ) {
		if ( _wc_cs()->dashboard->is_active() ) {
			wc_print_notice( $after_receiving_notice, 'notice' ) ;
		}
	}

	$before_receiving_notice = trim( get_option( WC_CS_PREFIX . 'before_receiving_credits_notice' ) ) ;

	if ( '' !== $before_receiving_notice ) {
		if ( ! _wc_cs()->is_on_auto_approval() && ! _wc_cs()->dashboard->is_credits_user() ) {
			wc_print_notice( $before_receiving_notice, 'notice' ) ;
		}
	}
}
