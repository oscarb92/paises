<?php

defined( 'ABSPATH' ) || exit ;

/**
 * Check the currently installed WC version
 * 
 * @param string $comparison_opr The possible operators are: <, lt, <=, le, >, gt, >=, ge, ==, =, eq, !=, <>, ne respectively.
 * This parameter is case-sensitive, values should be lowercase
 * @param string $version
 * @return boolean
 */
function _wc_cs_is_wc_version( $comparison_opr, $version ) {
	if ( defined( 'WC_VERSION' ) && version_compare( WC_VERSION, $version, $comparison_opr ) ) {
		return true ;
	}
	return false ;
}

/**
 * Returns true when viewing an dashboard page.
 *
 * @return bool
 */
function _wc_cs_is_dashboard() {
	global $post ;

	return is_singular() && is_a( $post, 'WP_Post' ) && has_shortcode( $post->post_content, 'credits_dashboard' ) ;
}
