<?php

defined( 'ABSPATH' ) || exit ;

/**
 * Retrieve all credits.
 * 
 * @return array
 */
function _wc_cs_get_all_credits() {
	global $wpdb ;

	$wpdb_ref    = &$wpdb ;
	$all_credits = $wpdb_ref->get_col(
			$wpdb_ref->prepare( "SELECT DISTINCT ID FROM {$wpdb_ref->posts}
                            WHERE post_type='%s' AND post_status IN ('" . implode( "','", array_map( 'esc_sql', array_keys( _wc_cs_get_credits_statuses() ) ) ) . "')"
					, 'wc_cs_credits'
			) ) ;

	return $all_credits ;
}

/**
 * Create the credits to the user.
 * 
 * @param array $args
 * @return \WC_CS_Credits
 */
function _wc_cs_create_credits( $args = array() ) {
	$args = wp_parse_args( $args, array(
		'id'      => 0,
		'user_id' => 0,
		'status'  => 'pending',
			) ) ;

	do_action( 'wc_cs_before_credits_created', $args ) ;

	$credits = new WC_CS_Credits( $args[ 'id' ] ) ;

	unset( $args[ 'id' ] ) ;
	$credits->set_props( $args ) ;

	$result = $credits->save() ;

	if ( is_wp_error( $result ) ) {
		return $result ;
	}

	do_action( 'wc_cs_credits_created', $credits, $args ) ;

	if ( '' !== $credits->get_created_via( 'edit' ) ) {
		do_action( 'wc_cs_credits_created_via_' . $credits->get_created_via( 'edit' ), $credits, $args ) ;
	}

	return $credits ;
}

/**
 * Create the credits transaction to the user.
 * 
 * @param array $args
 * @return \WC_CS_Credits_Transaction
 */
function _wc_cs_create_credits_txn( $args = array() ) {
	$args = wp_parse_args( $args, array(
		'id'         => 0,
		'credits_id' => 0,
		'user_id'    => 0,
		'status'     => 'unbilled',
		'type'       => '',
		'activity'   => __( 'Unknown', 'credits-for-woocommerce' ),
			) ) ;

	do_action( 'wc_cs_before_credits_txn_created', $args ) ;

	$credits_txn = new WC_CS_Credits_Transaction( $args[ 'id' ] ) ;

	unset( $args[ 'id' ] ) ;
	$credits_txn->set_props( $args ) ;

	$result = $credits_txn->save() ;

	if ( is_wp_error( $result ) ) {
		return $result ;
	}

	do_action( 'wc_cs_credits_txn_created', $credits_txn, $args ) ;

	return $credits_txn ;
}

/**
 * Get the credits.
 * 
 * @param WC_CS_Credits $credits
 * @param bool $wp_error
 * @return bool|\WC_CS_Credits
 */
function _wc_cs_get_credits( $credits, $wp_error = false ) {
	if ( ! $credits ) {
		return false ;
	}

	try {
		$credits = new WC_CS_Credits( $credits ) ;
	} catch ( Exception $e ) {
		return $wp_error ? new WP_Error( 'error', $e->getMessage() ) : false ;
	}

	return $credits ;
}

/**
 * Get the credits transaction.
 * 
 * @param WC_CS_Credits_Transaction $credits_txn
 * @param bool $wp_error
 * @return bool|\WC_CS_Credits_Transaction
 */
function _wc_cs_get_credits_txn( $credits_txn, $wp_error = false ) {
	if ( ! $credits_txn ) {
		return false ;
	}

	try {
		$credits_txn = new WC_CS_Credits_Transaction( $credits_txn ) ;
	} catch ( Exception $e ) {
		return $wp_error ? new WP_Error( 'error', $e->getMessage() ) : false ;
	}

	return $credits_txn ;
}

/**
 * Get the credits statuses.
 * 
 * @return array
 */
function _wc_cs_get_credits_statuses() {
	$credits_statuses = array(
		WC_CS_PREFIX . 'pending'      => __( 'Pending', 'credits-for-woocommerce' ),
		WC_CS_PREFIX . 'active'       => __( 'Active', 'credits-for-woocommerce' ),
		WC_CS_PREFIX . 'rejected'     => __( 'Rejected', 'credits-for-woocommerce' ),
		WC_CS_PREFIX . 'on_hold'      => __( 'On-Hold', 'credits-for-woocommerce' ),
		WC_CS_PREFIX . 'overdue'      => __( 'Overdue', 'credits-for-woocommerce' ),
		WC_CS_PREFIX . 'under_review' => __( 'Under Review', 'credits-for-woocommerce' ),
			) ;

	return $credits_statuses ;
}

/**
 * Get the credits transaction statuses.
 * 
 * @return array
 */
function _wc_cs_get_credits_txn_statuses() {
	$credits_txn_statuses = array(
		WC_CS_PREFIX . 'unbilled' => __( 'Unbilled', 'credits-for-woocommerce' ),
		WC_CS_PREFIX . 'billed'   => __( 'Billed', 'credits-for-woocommerce' ),
			) ;

	return $credits_txn_statuses ;
}

/**
 * Get the credits statuses before credits approved to the user.
 * 
 * @return array
 */
function _wc_cs_get_before_approval_credits_statuses() {
	$credits_statuses = _wc_cs_get_credits_statuses() ;
	$invalid_statuses = array( 'on_hold', 'overdue' ) ;

	foreach ( $invalid_statuses as $status ) {
		unset( $credits_statuses[ WC_CS_PREFIX . $status ] ) ;
	}
	return $credits_statuses ;
}

/**
 * Get the credits statuses after credits approved to the user.
 * 
 * @return array
 */
function _wc_cs_get_after_approval_credits_statuses() {
	$credits_statuses = _wc_cs_get_credits_statuses() ;
	$invalid_statuses = array( 'pending', 'rejected', 'under_review' ) ;

	foreach ( $invalid_statuses as $status ) {
		unset( $credits_statuses[ WC_CS_PREFIX . $status ] ) ;
	}
	return $credits_statuses ;
}

/**
 * Get the credits status name.
 * 
 * @param string $status
 * @return string
 */
function _wc_cs_get_credits_status_name( $status ) {
	$statuses = _wc_cs_get_credits_statuses() ;
	$status   = WC_CS_PREFIX === substr( $status, 0, 7 ) ? substr( $status, 7 ) : $status ;
	$status   = isset( $statuses[ WC_CS_PREFIX . $status ] ) ? $statuses[ WC_CS_PREFIX . $status ] : $status ;
	return $status ;
}

/**
 * Generate the bill statement hash.
 * 
 * @param string $statement_date
 * @return array
 */
function _wc_cs_generate_statement_hash( $statement_date ) {
	$Ym     = _wc_cs_get_time( 'Ym', array( 'time' => $statement_date ) ) ;
	$prefix = "$Ym" ;
	$prefix = "st{$prefix}_" ;
	$hash   = _wc_cs_generate_key( $prefix ) ;

	return array(
		'index' => $Ym,
		'value' => $hash,
			) ;
}

/**
 * Finds an bill statement ID based on an statement hash.
 * 
 * @param string $hash An statement hash has generated by.
 * @return int The ID of an bill statement, or 0 if the bill statement could not be found.
 */
function _wc_cs_get_bill_statement_id_by_hash( $hash ) {
	global $wpdb ;

	$wpdb_ref          = &$wpdb ;
	$bill_statement_id = $wpdb_ref->get_var(
			$wpdb_ref->prepare(
					"SELECT DISTINCT ID FROM {$wpdb_ref->posts} WHERE post_type='wc_cs_bill_statement' AND post_password=%s"
					, esc_sql( $hash )
			) ) ;

	return absint( $bill_statement_id ) ;
}
