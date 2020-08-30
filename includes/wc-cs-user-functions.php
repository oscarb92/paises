<?php

defined( 'ABSPATH' ) || exit ;

/**
 * Get the credits for the given user.
 * 
 * @param int $user_id
 * @return WC_CS_Credits
 */
function _wc_cs_get_credits_from_user_id( $user_id ) {
	global $wpdb ;

	$wpdb_ref   = &$wpdb ;
	$credits_id = absint( $wpdb_ref->get_var(
					$wpdb_ref->prepare( "SELECT DISTINCT ID FROM {$wpdb_ref->posts}
                            WHERE post_type='wc_cs_credits' AND post_author=%s AND post_status IN ('" . implode( "','", array_map( 'esc_sql', array_keys( _wc_cs_get_credits_statuses() ) ) ) . "') LIMIT 1"
							, esc_sql( $user_id )
			) ) ) ;

	return _wc_cs_get_credits( $credits_id ) ;
}

/**
 * Retrieve the total orders placed count by the given user.
 * 
 * @param int $user_id
 * @return int
 */
function _wc_cs_get_total_orders_placed_by_user( $user_id ) {
	global $wpdb ;

	$wpdb_ref = &$wpdb ;
	return absint( $wpdb_ref->get_var(
					$wpdb_ref->prepare( "SELECT COUNT(DISTINCT ID) FROM {$wpdb_ref->posts} as p
                            INNER JOIN {$wpdb_ref->postmeta} pm ON (p.ID = pm.post_id AND pm.meta_key='_customer_user' AND pm.meta_value=%s)
                            LEFT JOIN {$wpdb_ref->postmeta} pm2 ON (p.ID = pm2.post_id AND (pm2.meta_key=%s OR pm2.meta_key=%s))
                            WHERE pm2.meta_key IS NULL AND p.post_type='shop_order' AND p.post_status IN ( 'wc-" . implode( "','wc-", array_map( 'esc_sql', wc_get_is_paid_statuses() ) ) . "' )"
							, esc_sql( $user_id )
							, esc_sql( WC_CS_PREFIX . 'funds_added' )
							, esc_sql( WC_CS_PREFIX . 'repayment' )
			) ) ) ;
}

/**
 * Retrieve the total amount spent by the given user.
 * 
 * @param int $user_id
 * @return float
 */
function _wc_cs_get_total_amount_spent_by_user( $user_id ) {
	global $wpdb ;

	$wpdb_ref = &$wpdb ;
	return wc_format_decimal( $wpdb_ref->get_var(
					$wpdb_ref->prepare( "SELECT SUM(pm.meta_value) FROM {$wpdb_ref->posts} as p
                            INNER JOIN {$wpdb_ref->postmeta} pm ON (p.ID = pm.post_id)
                            INNER JOIN {$wpdb_ref->postmeta} pm2 ON (p.ID = pm2.post_id)
                            LEFT JOIN {$wpdb_ref->postmeta} pm3 ON (p.ID = pm3.post_id AND (pm3.meta_key=%s OR pm3.meta_key=%s))
                            WHERE pm3.meta_key IS NULL AND pm2.meta_key='_customer_user' AND pm2.meta_value=%s AND p.post_type='shop_order' AND p.post_status IN ( 'wc-" . implode( "','wc-", array_map( 'esc_sql', wc_get_is_paid_statuses() ) ) . "' ) 
                            AND pm.meta_key='_order_total'"
							, esc_sql( WC_CS_PREFIX . 'funds_added' )
							, esc_sql( WC_CS_PREFIX . 'repayment' )
							, esc_sql( $user_id )
			) ), 2 ) ;
}

/**
 * Retrieve the highest amount spent on order by the given user.
 * 
 * @param int $user_id
 * @return float
 */
function _wc_cs_get_highest_order_value_by_user( $user_id ) {
	global $wpdb ;

	$wpdb_ref = &$wpdb ;
	return wc_format_decimal( $wpdb_ref->get_var(
					$wpdb_ref->prepare( "SELECT pm.meta_value FROM {$wpdb_ref->posts} as p
                            INNER JOIN {$wpdb_ref->postmeta} pm ON (p.ID = pm.post_id)
                            INNER JOIN {$wpdb_ref->postmeta} pm2 ON (p.ID = pm2.post_id)
                            LEFT JOIN {$wpdb_ref->postmeta} pm3 ON (p.ID = pm3.post_id AND (pm3.meta_key=%s OR pm3.meta_key=%s))
                            WHERE pm3.meta_key IS NULL AND pm2.meta_key='_customer_user' AND pm2.meta_value=%s AND p.post_type='shop_order' AND p.post_status IN ( 'wc-" . implode( "','wc-", array_map( 'esc_sql', wc_get_is_paid_statuses() ) ) . "' ) 
                            AND pm.meta_key='_order_total' ORDER BY CAST(pm.meta_value AS DECIMAL) DESC"
							, esc_sql( WC_CS_PREFIX . 'funds_added' )
							, esc_sql( WC_CS_PREFIX . 'repayment' )
							, esc_sql( $user_id )
			) ), 2 ) ;
}

/**
 * Retrieve the lowest amount spent on order by the given user.
 * 
 * @param int $user_id
 * @return float
 */
function _wc_cs_get_lowest_order_value_by_user( $user_id ) {
	global $wpdb ;

	$wpdb_ref = &$wpdb ;
	return wc_format_decimal( $wpdb_ref->get_var(
					$wpdb_ref->prepare( "SELECT pm.meta_value FROM {$wpdb_ref->posts} as p
                            INNER JOIN {$wpdb_ref->postmeta} pm ON (p.ID = pm.post_id)
                            INNER JOIN {$wpdb_ref->postmeta} pm2 ON (p.ID = pm2.post_id)
                            LEFT JOIN {$wpdb_ref->postmeta} pm3 ON (p.ID = pm3.post_id AND (pm3.meta_key=%s OR pm3.meta_key=%s))
                            WHERE pm3.meta_key IS NULL AND pm2.meta_key='_customer_user' AND pm2.meta_value=%s AND p.post_type='shop_order' AND p.post_status IN ( 'wc-" . implode( "','wc-", array_map( 'esc_sql', wc_get_is_paid_statuses() ) ) . "' ) 
                            AND pm.meta_key='_order_total' ORDER BY CAST(pm.meta_value AS DECIMAL) ASC"
							, esc_sql( WC_CS_PREFIX . 'funds_added' )
							, esc_sql( WC_CS_PREFIX . 'repayment' )
							, esc_sql( $user_id )
			) ), 2 ) ;
}

/**
 * Retrieve the average monthly amount spent on order by the given user.
 * 
 * @param int $user_id
 * @return float
 */
function _wc_cs_get_avg_monthly_amount_spent_by_user( $user_id ) {
	global $wpdb ;

	$wpdb_ref = &$wpdb ;
	return wc_format_decimal( $wpdb_ref->get_var(
					$wpdb_ref->prepare( "SELECT AVG(pm.meta_value) FROM {$wpdb_ref->posts} as p
                            INNER JOIN {$wpdb_ref->postmeta} pm ON (p.ID = pm.post_id)
                            INNER JOIN {$wpdb_ref->postmeta} pm2 ON (p.ID = pm2.post_id)
                            LEFT JOIN {$wpdb_ref->postmeta} pm3 ON (p.ID = pm3.post_id AND (pm3.meta_key=%s OR pm3.meta_key=%s))
                            WHERE pm3.meta_key IS NULL AND pm2.meta_key='_customer_user' AND pm2.meta_value=%s AND p.post_type='shop_order' AND p.post_status IN ( 'wc-" . implode( "','wc-", array_map( 'esc_sql', wc_get_is_paid_statuses() ) ) . "' ) 
                            AND pm.meta_key='_order_total' GROUP BY MONTH(p.post_date) ORDER BY CAST(pm.meta_value AS DECIMAL) ASC"
							, esc_sql( WC_CS_PREFIX . 'funds_added' )
							, esc_sql( WC_CS_PREFIX . 'repayment' )
							, esc_sql( $user_id )
			) ), 2 ) ;
}

/**
 * Retrieve the average yearly amount spent on order by the given user.
 * 
 * @param int $user_id
 * @return float
 */
function _wc_cs_get_avg_yearly_amount_spent_by_user( $user_id ) {
	global $wpdb ;

	$wpdb_ref = &$wpdb ;
	return wc_format_decimal( $wpdb_ref->get_var(
					$wpdb_ref->prepare( "SELECT AVG(pm.meta_value) FROM {$wpdb_ref->posts} as p
                            INNER JOIN {$wpdb_ref->postmeta} pm ON (p.ID = pm.post_id)
                            INNER JOIN {$wpdb_ref->postmeta} pm2 ON (p.ID = pm2.post_id)
                            LEFT JOIN {$wpdb_ref->postmeta} pm3 ON (p.ID = pm3.post_id AND (pm3.meta_key=%s OR pm3.meta_key=%s))
                            WHERE pm3.meta_key IS NULL AND pm2.meta_key='_customer_user' AND pm2.meta_value=%s AND p.post_type='shop_order' AND p.post_status IN ( 'wc-" . implode( "','wc-", array_map( 'esc_sql', wc_get_is_paid_statuses() ) ) . "' ) 
                            AND pm.meta_key='_order_total' GROUP BY YEAR(p.post_date) ORDER BY CAST(pm.meta_value AS DECIMAL) ASC"
							, esc_sql( WC_CS_PREFIX . 'funds_added' )
							, esc_sql( WC_CS_PREFIX . 'repayment' )
							, esc_sql( $user_id )
			) ), 2 ) ;
}

/**
 * Get WP User roles
 * 
 * @global WP_Roles $wp_roles
 * @param bool $include_guest
 * @return array
 */
function _wc_cs_get_user_roles( $include_guest = false ) {
	global $wp_roles ;
	$user_role_key  = array() ;
	$user_role_name = array() ;

	foreach ( $wp_roles->roles as $_user_role_key => $user_role ) {
		$user_role_key[]  = $_user_role_key ;
		$user_role_name[] = $user_role[ 'name' ] ;
	}

	$user_roles = array_combine( ( array ) $user_role_key, ( array ) $user_role_name ) ;

	if ( $include_guest ) {
		$user_roles = array_merge( $user_roles, array( 'guest' => 'Guest' ) ) ;
	}

	return $user_roles ;
}

/**
 * Return array of user data.
 * 
 * @param int $user_id
 * @return array
 */
function _wc_cs_prepare_userdata( $user_id ) {
	$user = get_userdata( $user_id ) ;

	if ( ! $user ) {
		return array() ;
	}

	$user_meta          = get_user_meta( $user_id ) ;
	$billing_first_name = isset( $user_meta[ 'billing_first_name' ][ 0 ] ) ? $user_meta[ 'billing_first_name' ][ 0 ] : '' ;
	$billing_last_name  = isset( $user_meta[ 'billing_last_name' ][ 0 ] ) ? $user_meta[ 'billing_last_name' ][ 0 ] : '' ;

	if ( empty( $billing_first_name ) ) {
		$billing_first_name = isset( $user_meta[ 'first_name' ][ 0 ] ) ? $user_meta[ 'first_name' ][ 0 ] : '' ;
	}

	if ( empty( $billing_last_name ) ) {
		$billing_last_name = isset( $user_meta[ 'last_name' ][ 0 ] ) ? $user_meta[ 'last_name' ][ 0 ] : '' ;
	}

	$userdata = array(
		'user_first_name' => $billing_first_name,
		'user_last_name'  => $billing_last_name,
		'user_company'    => isset( $user_meta[ 'billing_company' ][ 0 ] ) ? $user_meta[ 'billing_company' ][ 0 ] : '',
		'user_country'    => isset( $user_meta[ 'billing_country' ][ 0 ] ) ? $user_meta[ 'billing_country' ][ 0 ] : '',
		'user_address_1'  => isset( $user_meta[ 'billing_address_1' ][ 0 ] ) ? $user_meta[ 'billing_address_1' ][ 0 ] : '',
		'user_address_2'  => isset( $user_meta[ 'billing_address_2' ][ 0 ] ) ? $user_meta[ 'billing_address_2' ][ 0 ] : '',
		'user_city'       => isset( $user_meta[ 'billing_city' ][ 0 ] ) ? $user_meta[ 'billing_city' ][ 0 ] : '',
		'user_state'      => isset( $user_meta[ 'billing_state' ][ 0 ] ) ? $user_meta[ 'billing_state' ][ 0 ] : '',
		'user_postcode'   => isset( $user_meta[ 'billing_postcode' ][ 0 ] ) ? $user_meta[ 'billing_postcode' ][ 0 ] : '',
		'user_phone'      => isset( $user_meta[ 'billing_phone' ][ 0 ] ) ? $user_meta[ 'billing_phone' ][ 0 ] : '',
		'user_email'      => isset( $user_meta[ 'billing_email' ][ 0 ] ) ? $user_meta[ 'billing_email' ][ 0 ] : $user->user_email,
			) ;

	return $userdata ;
}
