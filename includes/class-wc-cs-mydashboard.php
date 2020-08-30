<?php

defined( 'ABSPATH' ) || exit ;

/**
 * Handle Credits Dashboard page
 * 
 * @class WC_CS_MyDashboard
 * @package Class
 */
class WC_CS_MyDashboard {

	/**
	 * The single instance of the class.
	 */
	protected static $instance = null ;

	/**
	 * Credits.
	 * 
	 * @var WC_CS_Credits 
	 */
	protected static $credits ;

	/**
	 * Is current user accessible to dashboard page ?
	 * 
	 * @var bool 
	 */
	protected static $is_accessible ;

	/**
	 * Get the shortcode for the dashboard.
	 * 
	 * @var string 
	 */
	protected $dashboard_page = 'credits_dashboard' ;

	/**
	 * Get our available query vars.
	 *  
	 * @var array 
	 */
	protected $query_vars = array() ;

	/**
	 * Create instance for WC_CS_MyDashboard.
	 */
	public static function instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self() ;
		}
		return self::$instance ;
	}

	/**
	 * Construct WC_CS_MyDashboard.
	 */
	public function __construct() {
		$this->query_vars = array(
			'wc-cs-make-repayment'     => get_option( WC_CS_PREFIX . 'make_payment_label' ),
			'wc-cs-view-unbilled-txns' => get_option( WC_CS_PREFIX . 'view_unbilled_transactions_label' ),
			'wc-cs-view-statements'    => get_option( WC_CS_PREFIX . 'view_statements_label' ),
				) ;
	}

	/**
	 * Init WC_CS_MyDashboard.
	 */
	public function init() {
		add_filter( 'wc_cs_query_vars', array( $this, 'add_query_vars' ) ) ;
		add_filter( 'wc_cs_get_current_endpoint_title', array( $this, 'get_current_endpoint_title' ), 10, 2 ) ;
		add_shortcode( $this->dashboard_page, array( $this, 'my_dashboard' ) ) ;
	}

	/**
	 * Credits.
	 * 
	 * @var WC_CS_Credits 
	 */
	public function get_credits() {
		global $current_user ;

		if ( ! is_null( self::$credits ) ) {
			return self::$credits ;
		}

		self::$credits = _wc_cs_get_credits_from_user_id( $current_user->ID ) ;

		return self::$credits ;
	}

	/**
	 * Is current user accessible to dashboard page ?
	 * 
	 * @var bool 
	 */
	public function is_accessible() {
		global $current_user ;

		if ( is_bool( self::$is_accessible ) ) {
			return self::$is_accessible ;
		}

		self::$is_accessible = _wc_cs()->is_on_auto_approval() && ! $this->is_credits_user() ? false : true ;

		if ( ! self::$is_accessible ) {
			return self::$is_accessible ;
		}

		switch ( get_option( WC_CS_PREFIX . 'display_credit_form_for' ) ) {
			case 'include-users':
				self::$is_accessible = ( in_array( $current_user->ID, ( array ) get_option( WC_CS_PREFIX . 'get_included_users_to_display_credit_form' ) ) ) ? true : false ;
				break ;
			case 'exclude-users':
				self::$is_accessible = ( ! in_array( $current_user->ID, ( array ) get_option( WC_CS_PREFIX . 'get_excluded_users_to_display_credit_form' ) ) ) ? true : false ;
				break ;
			case 'include-userroles':
				self::$is_accessible = ( isset( $current_user->roles[ 0 ] ) && in_array( $current_user->roles[ 0 ], ( array ) get_option( WC_CS_PREFIX . 'get_included_userroles_to_display_credit_form' ) ) ) ? true : false ;
				break ;
			case 'exclude-userroles':
				self::$is_accessible = ( isset( $current_user->roles[ 0 ] ) && ! in_array( $current_user->roles[ 0 ], ( array ) get_option( WC_CS_PREFIX . 'get_excluded_userroles_to_display_credit_form' ) ) ) ? true : false ;
				break ;
			default:
				self::$is_accessible = true ;
		}

		if ( self::$is_accessible && is_user_logged_in() ) {
			switch ( get_option( WC_CS_PREFIX . 'allow_users_site_activity_to_display_credit_form_with' ) ) {
				case 'min-no-of-orders-placed':
					$total_orders_placed     = _wc_cs_get_total_orders_placed_by_user( $current_user->ID ) ;
					$no_of_min_orders_placed = absint( get_option( WC_CS_PREFIX . 'allow_users_site_activity_with_min_orders_placed', '0' ) ) ;

					if ( $total_orders_placed < $no_of_min_orders_placed ) {
						self::$is_accessible = false ;
						return self::$is_accessible ;
					}
					break ;
				case 'min-amt-spent-on-site':
					$total_amount_spent = _wc_cs_get_total_amount_spent_by_user( $current_user->ID ) ;
					$min_amt_spent      = wc_format_decimal( get_option( WC_CS_PREFIX . 'allow_users_site_activity_with_min_amt_spent', '0' ), 2 ) ;

					if ( $total_amount_spent < $min_amt_spent ) {
						self::$is_accessible = false ;
						return self::$is_accessible ;
					}
					break ;
			}
		}

		return self::$is_accessible ;
	}

	/**
	 * Is current user a credits user ?
	 * 
	 * @var bool 
	 */
	public function is_credits_user() {
		if ( ! $this->get_credits() || is_wp_error( $this->get_credits() ) ) {
			return false ;
		}

		return true ;
	}

	/**
	 * Is credits user active ?
	 * 
	 * @var bool 
	 */
	public function is_active() {
		return $this->is_credits_user() && $this->get_credits()->has_status( 'active' ) ;
	}

	/**
	 * Can current user make the repayment after the bill gets generated ?
	 * 
	 * @var bool 
	 */
	public function can_user_make_repayment() {
		if ( ! $this->is_credits_user() ) {
			return false ;
		}

		if ( 'unpaid' !== $this->get_credits()->get_last_billed_status( 'edit' ) ) {
			return false ;
		}

		if ( did_action( 'woocommerce_after_register_post_type' ) && $this->get_credits()->get_last_payment_order_id( 'edit' ) > 0 ) {
			$last_payment_order = wc_get_order( $this->get_credits()->get_last_payment_order_id( 'edit' ) ) ;

			if ( $last_payment_order && $last_payment_order->has_status( array( 'pending', 'on-hold' ) ) ) {
				return false ;
			}
		}

		return true ;
	}

	/**
	 * Flush the cache on demand.
	 */
	public function clear_cache() {
		self::$credits = null ;
	}

	/**
	 * Get dashboard endpoints.
	 *
	 * @return array
	 */
	public function get_endpoints() {
		if ( ! $this->can_user_make_repayment() ) {
			unset( $this->query_vars[ 'wc-cs-make-repayment' ] ) ;
		}

		return apply_filters( 'wc_cs_get_endpoints', $this->query_vars ) ;
	}

	/**
	 * Get current dashboard endpoint URL.
	 *
	 * @return string
	 */
	public function get_current_endpoint_url() {
		if ( is_null( _wc_cs()->query ) || ! _wc_cs()->query->get_current_endpoint() ) {
			return $this->get_endpoint_url( 'dashboard' ) ;
		}

		return $this->get_endpoint_url( _wc_cs()->query->get_current_endpoint() ) ;
	}

	/**
	 * Get dashboard endpoint URL.
	 *
	 * @param string $endpoint Endpoint.
	 * @return string
	 */
	public function get_endpoint_url( $endpoint ) {
		if ( 'dashboard' === $endpoint ) {
			return wc_get_page_permalink( $this->dashboard_page ) ;
		}

		return wc_get_endpoint_url( $endpoint, '', wc_get_page_permalink( $this->dashboard_page ) ) ;
	}

	/**
	 * Shortcode Wrapper.
	 *
	 * @param string[] $function Callback function.
	 * @param array    $atts     Attributes. Default to empty array.
	 *
	 * @return string
	 */
	public function shortcode_wrapper( $function, $atts = array() ) {
		ob_start() ;

		echo '<div class="credits-for-woocommerce">' ;
		call_user_func( $function, $atts ) ;
		echo '</div>' ;

		return ob_get_clean() ;
	}

	/**
	 * My Dashboard page shortcode.
	 *
	 * @param array $atts Attributes.
	 * @return string
	 */
	public function my_dashboard( $atts ) {
		return $this->shortcode_wrapper( array( $this, 'output' ), $atts ) ;
	}

	/**
	 * Add dashboard query vars.
	 *
	 * @return array
	 */
	public function add_query_vars( $query_vars ) {
		foreach ( array_keys( $this->get_endpoints() ) as $var ) {
			if ( $var ) {
				$query_vars[ $var ] = $var ;
			}
		}

		return $query_vars ;
	}

	/**
	 * Add dashboard query vars endpoint title
	 *
	 * @return string
	 */
	public function get_current_endpoint_title( $title, $endpoint ) {
		$endpoints = $this->get_endpoints() ;

		if ( isset( $endpoints[ $endpoint ] ) ) {
			$title = $endpoints[ $endpoint ] ;
		}

		return $title ;
	}

	/**
	 * Output the shortcode.
	 *
	 * @param array $atts Shortcode attributes.
	 */
	public function output( $atts ) {
		global $wp, $current_user, $wc_credits ;

		// Check cart class is loaded or abort.
		if ( is_null( WC()->cart ) ) {
			return ;
		}

		// Print notices.
		wc_print_notices() ;

		if ( ! $this->is_accessible() ) {

			wc_print_notice( esc_html( get_option( WC_CS_PREFIX . 'invalid_user_dashboard_access_message' ) ), 'error' ) ;
		} else if ( ! $this->is_credits_user() ) {

			// Enqueue scripts.
			wp_enqueue_script( 'wc-country-select' ) ;
			wp_enqueue_script( 'wc-address-i18n' ) ;

			_wc_cs_get_template( 'application-form/form.php', array(
				'form'   => $this,
				'fields' => WC_CS_Form_Fields::get_available_fields(),
					)
			) ;
		} else {
			$wc_credits = $this->get_credits() ;

			switch ( $wc_credits->get_status() ) {
				case 'pending':
				case 'under_review':
					wc_print_notice( esc_html( get_option( WC_CS_PREFIX . 'credit_app_submitted_message' ) ), 'notice' ) ;
					break ;
				case 'rejected':
					wc_print_notice( esc_html( get_option( WC_CS_PREFIX . 'credit_app_rejected_message' ) ), 'error' ) ;
					break ;
				case 'on_hold':
					wc_print_notice( esc_html( get_option( WC_CS_PREFIX . 'credit_app_onhold_message' ) ), 'notice' ) ;
					break ;
				default:
					if ( ! empty( $wp->query_vars ) ) {
						foreach ( $wp->query_vars as $key => $value ) {
							// Ignore pagename param.
							if ( 'pagename' === $key ) {
								continue ;
							}

							if ( has_action( 'wc_cs_credits_dashboard_' . $key . '_endpoint' ) ) {
								do_action( 'wc_cs_credits_dashboard_' . $key . '_endpoint', $value ) ;
								return ;
							}
						}
					}

					ob_start() ;

					/**
					 * My Dashboard page.
					 */
					_wc_cs_get_template( 'mydashboard/dashboard.php', array(
						'dashboard' => $this,
							)
					) ;

					ob_end_flush() ;
					break ;
			}
		}
	}

}
