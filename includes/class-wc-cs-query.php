<?php

defined( 'ABSPATH' ) || exit ;

/**
 * Contains the query functions for Credits for Woocommerce
 * 
 * @class WC_CS_Query
 * @package Class
 */
class WC_CS_Query {

	/**
	 * Query vars to add to wp
	 *
	 * @var array 
	 */
	public $query_vars = array() ;

	/**
	 * Constructor for the query class. Hooks in methods.
	 */
	public function __construct() {

		add_action( 'init', array( $this, 'init_query_vars' ), -1 ) ;
		add_action( 'init', array( $this, 'add_endpoints' ) ) ;

		if ( ! is_admin() ) {
			add_filter( 'query_vars', array( $this, 'add_query_vars' ), 0 ) ;
			add_action( 'pre_get_posts', array( $this, 'pre_get_posts' ) ) ;
			add_filter( 'the_title', array( $this, 'set_page_endpoint_title' ) ) ;
		}
	}

	/**
	 * Init query vars by loading options.
	 */
	public function init_query_vars() {
		// Query vars to add to WP.
		$this->query_vars = apply_filters( 'wc_cs_query_vars', array() ) ;
	}

	/**
	 * Get query vars.
	 *
	 * @return array
	 */
	public function get_query_vars() {
		return $this->query_vars ;
	}

	/**
	 * Add endpoints for query vars.
	 */
	public function add_endpoints() {
		$mask = $this->get_endpoints_mask() ;

		foreach ( $this->get_query_vars() as $key => $var ) {
			if ( ! empty( $var ) ) {
				add_rewrite_endpoint( $var, $mask ) ;
			}
		}

		$do_flush = get_option( 'wc_cs_flush_rewrite_rules', 1 ) ;

		if ( $do_flush && ! empty( $this->query_vars ) ) {
			update_option( 'wc_cs_flush_rewrite_rules', 0 ) ;
			flush_rewrite_rules() ;
		}
	}

	/**
	 * Add query vars.
	 *
	 * @param array $vars
	 * @return array
	 */
	public function add_query_vars( $vars ) {
		foreach ( $this->get_query_vars() as $key => $var ) {
			$vars[] = $key ;
		}
		return $vars ;
	}

	/**
	 * Get current active query var.
	 *
	 * @return string
	 */
	public function get_current_endpoint() {
		global $wp ;

		foreach ( $this->get_query_vars() as $key => $var ) {
			if ( isset( $wp->query_vars[ $key ] ) ) {
				return $key ;
			}
		}
		return '' ;
	}

	/**
	 * Replace a page title with the endpoint title.
	 *
	 * @param  string $title
	 * @return string
	 */
	public function set_page_endpoint_title( $title ) {
		global $wp_query ;

		$endpoint = $this->get_current_endpoint() ;

		if ( ! is_null( $wp_query ) && ! is_admin() && is_main_query() && in_the_loop() && is_page() && '' !== $endpoint ) {
			$endpoint_title = $this->get_endpoint_title( $endpoint ) ;

			if ( $endpoint_title ) {
				$title = $endpoint_title ;
			}

			remove_filter( 'the_title', array( $this, 'set_page_endpoint_title' ) ) ;
		}

		return $title ;
	}

	/**
	 * Get page title for an endpoint.
	 * 
	 * @param  string
	 * @return string
	 */
	public function get_endpoint_title( $endpoint ) {
		return apply_filters( 'wc_cs_get_current_endpoint_title', '', $endpoint ) ;
	}

	/**
	 * Endpoint mask describing the places the endpoint should be added.
	 *
	 * @return int
	 */
	public function get_endpoints_mask() {
		if ( 'page' === get_option( 'show_on_front' ) ) {
			$page_on_front     = get_option( 'page_on_front' ) ;
			$myaccount_page_id = get_option( 'woocommerce_myaccount_page_id' ) ;

			if ( in_array( $page_on_front, array( $myaccount_page_id ) ) ) {
				return EP_ROOT | EP_PAGES ;
			}
		}

		return EP_PAGES ;
	}

	/**
	 * Are we currently on the front page?
	 *
	 * @param object $q
	 *
	 * @return bool
	 */
	private function is_showing_page_on_front( $q ) {
		return $q->is_home() && 'page' === get_option( 'show_on_front' ) ;
	}

	/**
	 * Is the front page a page we define?
	 *
	 * @param int $page_id
	 *
	 * @return bool
	 */
	private function page_on_front_is( $page_id ) {
		return absint( get_option( 'page_on_front' ) ) === absint( $page_id ) ;
	}

	/**
	 * Hook into pre_get_posts to do the main product query.
	 *
	 * @param object $q query object
	 */
	public function pre_get_posts( $q ) {
		// We only want to affect the main query
		if ( ! $q->is_main_query() ) {
			return ;
		}

		// Fix for endpoints on the homepage
		if ( $this->is_showing_page_on_front( $q ) && ! $this->page_on_front_is( $q->get( 'page_id' ) ) ) {
			$_query = wp_parse_args( $q->query ) ;
			if ( ! empty( $_query ) && array_intersect( array_keys( $_query ), array_keys( $this->query_vars ) ) ) {
				$q->is_page     = true ;
				$q->is_home     = false ;
				$q->is_singular = true ;
				$q->set( 'page_id', ( int ) get_option( 'page_on_front' ) ) ;
				add_filter( 'redirect_canonical', '__return_false' ) ;
			}
		}
	}

}
