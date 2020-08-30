<?php

defined( 'ABSPATH' ) || exit ;

/**
 * Handle enqueues.
 * 
 * @class WC_CS_Enqueues
 * @package Class
 */
class WC_CS_Enqueues {

	/**
	 * Init WC_CS_Enqueues.
	 */
	public static function init() {
		add_action( 'admin_enqueue_scripts', __CLASS__ . '::admin_script', 11 ) ;
		add_action( 'admin_enqueue_scripts', __CLASS__ . '::admin_style', 11 ) ;
		add_action( 'wp_enqueue_scripts', __CLASS__ . '::frontend_script' ) ;
		add_action( 'wp_enqueue_scripts', __CLASS__ . '::frontend_style' ) ;
		add_filter( 'woocommerce_screen_ids', __CLASS__ . '::load_woocommerce_enqueues', 1 ) ;
	}

	/**
	 * Register and enqueue a script for use.
	 *
	 * @uses   wp_enqueue_script()
	 * @param  string   $handle
	 * @param  string   $path
	 * @param  array   $localize_data
	 * @param  string[] $deps
	 * @param  string   $version
	 * @param  boolean  $in_footer
	 */
	public static function enqueue_script( $handle, $path = '', $localize_data = array(), $deps = array( 'jquery' ), $version = WC_CS_VERSION, $in_footer = false ) {
		wp_register_script( $handle, $path, $deps, $version, $in_footer ) ;

		$name = str_replace( '-', '_', $handle ) ;
		wp_localize_script( $handle, $name, $localize_data ) ;
		wp_enqueue_script( $handle ) ;
	}

	/**
	 * Register and enqueue a styles for use.
	 *
	 * @uses   wp_enqueue_style()
	 * @param  string   $handle
	 * @param  string   $path
	 * @param  string[] $deps
	 * @param  string   $version
	 * @param  string   $media
	 * @param  boolean  $has_rtl
	 */
	public static function enqueue_style( $handle, $path = '', $deps = array(), $version = WC_CS_VERSION, $media = 'all', $has_rtl = false ) {
		wp_register_style( $handle, $path, $deps, $version, $media, $has_rtl ) ;
		wp_enqueue_style( $handle ) ;
	}

	/**
	 * Return asset URL.
	 *
	 * @param string $path
	 * @return string
	 */
	public static function get_asset_url( $path ) {
		return WC_CS_URL . "/assets/{$path}" ;
	}

	/**
	 * Enqueue jQuery UI events
	 */
	public static function enqueue_jQuery_ui() {
		self::enqueue_script( 'wc-cs-jquery-ui', self::get_asset_url( 'js/jquery-ui/jquery-ui.js' ) ) ;
		self::enqueue_style( 'wc-cs-jquery-ui', self::get_asset_url( 'css/jquery-ui.css' ) ) ;
	}

	/**
	 * Enqueue WC Multiselect field
	 */
	public static function enqueue_wc_multiselect() {
		wp_enqueue_script( 'wc-enhanced-select' ) ;
	}

	/**
	 * Enqueue Jquery tipTip
	 */
	public static function enqueue_jquery_tiptip() {
		self::enqueue_script( 'wc-cs-jquery-tiptip-lib', self::get_asset_url( 'js/jquery-tiptip/jquery.tipTip.js' ) ) ;
		self::enqueue_script( 'wc-cs-jquery-tiptip', self::get_asset_url( 'js/jquery-tiptip/wc-cs-my-tipTip.js' ) ) ;
		self::enqueue_style( 'wc-cs-jquery-tiptip', self::get_asset_url( 'css/wc-cs-jquery.tipTip.css' ) ) ;
	}

	/**
	 * Perform script localization in backend.
	 */
	public static function admin_script() {
		global $post ;

		$screen    = get_current_screen() ;
		$screen_id = $screen ? $screen->id : '' ;

		//Admin Page.
		if ( in_array( get_post_type(), _wc_cs_get_screen_ids() ) ) {
			self::enqueue_script( 'wc-cs-admin', self::get_asset_url( 'js/admin/wc-cs-admin.js' ), array(
				'credits_id'                        => isset( $post->ID ) ? $post->ID : '',
				'check_site_activity_nonce'         => wp_create_nonce( 'wc-cs-check-site-activity' ),
				'view_statement_nonce'              => wp_create_nonce( 'wc-cs-view-statement' ),
				'save_before_approval_nonce'        => wp_create_nonce( 'wc-cs-save-before-approval' ),
				'save_after_approval_nonce'         => wp_create_nonce( 'wc-cs-save-after-approval' ),
				'get_repayment_nonce'               => wp_create_nonce( 'wc-cs-get-repayment-day-of-month' ),
				'i18n_confirm_credit_limit_changes' => esc_attr__( 'Are you sure you want to update the credit limit? If edited, the credit limit for this user will not be updated automatically based on rules in the future.', 'credits-for-woocommerce' ),
				'i18n_went_wrong'                   => esc_attr__( 'Something went wrong!!', 'credits-for-woocommerce' ),
			) ) ;
			self::enqueue_jquery_tiptip() ;
			// Disable WP Auto Save on Edit Page.
			wp_dequeue_script( 'autosave' ) ;
		}

		if ( in_array( $screen_id, _wc_cs_get_screen_ids() ) ) {
			self::enqueue_script( 'wc-cs-admin-settings-general', self::get_asset_url( 'js/admin/wc-cs-admin-settings-general.js' ), array(
				'create_virtual_product_nonce' => wp_create_nonce( 'wc-cs-create-virtual-product' ),
				'get_repayment_nonce'          => wp_create_nonce( 'wc-cs-get-repayment-day-of-month' ),
				'funding_via_real_money'       => _wc_cs()->funding_via_real_money() ? 'yes' : '',
				'i18n_went_wrong'              => esc_attr__( 'Something went wrong!!', 'credits-for-woocommerce' ),
			) ) ;
			self::enqueue_script( 'wc-cs-admin-settings-rules', self::get_asset_url( 'js/admin/wc-cs-admin-settings-rules.js' ), array(
				'save_rule_nonce'                   => wp_create_nonce( 'wc-cs-save-rule' ),
				'edit_rule_nonce'                   => wp_create_nonce( 'wc-cs-edit-rule' ),
				'remove_rule_nonce'                 => wp_create_nonce( 'wc-cs-remove-rule' ),
				'i18n_confirm_before_rule_deletion' => esc_attr__( 'Are you sure you want to remove rule? This cannot be undone.', 'credits-for-woocommerce' ),
				'i18n_credit_limit_invalid'         => esc_attr__( 'Please enter the valid credit limit !!', 'credits-for-woocommerce' )
					), array( 'wc-backbone-modal' ) ) ;
			self::enqueue_jQuery_ui() ;
			self::enqueue_wc_multiselect() ;
			wp_enqueue_media() ;
		}
	}

	/**
	 * Load style in backend.
	 */
	public static function admin_style() {
		$screen    = get_current_screen() ;
		$screen_id = $screen ? $screen->id : '' ;

		if ( in_array( get_post_type(), _wc_cs_get_screen_ids() ) || in_array( $screen_id, _wc_cs_get_screen_ids() ) ) {
			self::enqueue_style( 'wc-cs-admin', self::get_asset_url( 'css/wc-cs-admin.css' ) ) ;
		}
	}

	/**
	 * Perform script localization in frontend.
	 */
	public static function frontend_script() {
		if ( _wc_cs_is_dashboard() ) {
			self::enqueue_script( 'wc-cs-frontend', self::get_asset_url( 'js/frontend/wc-cs-frontend.js' ), array(
				'ajax_url'             => admin_url( 'admin-ajax.php' ),
				'credits_id'           => _wc_cs()->dashboard->is_credits_user() ? _wc_cs()->dashboard->get_credits()->get_id() : 0,
				'view_statement_nonce' => wp_create_nonce( 'wc-cs-view-statement' ),
			) ) ;
		}
	}

	/**
	 * Load style in frontend.
	 */
	public static function frontend_style() {
		if ( _wc_cs_is_dashboard() ) {
			self::enqueue_style( 'wc-cs-frontend-inline' ) ;
			self::enqueue_style( 'wc-cs-frontend', self::get_asset_url( 'css/wc-cs-frontend.css' ), array( 'dashicons' ) ) ;

			$css = get_option( WC_CS_PREFIX . 'inline_style' ) ;

			if ( '' !== $css ) {
				wp_add_inline_style( 'wc-cs-frontend-inline', $css ) ;
			}
		}
	}

	/**
	 * Load WooCommerce enqueues.
	 * 
	 * @param array $screen_ids
	 * @return array
	 */
	public static function load_woocommerce_enqueues( $screen_ids ) {
		$screen    = get_current_screen() ;
		$screen_id = $screen ? $screen->id : '' ;

		if ( function_exists( '_wc_cs_get_screen_ids' ) && in_array( $screen_id, _wc_cs_get_screen_ids() ) ) {
			$screen_ids[] = $screen_id ;
		}

		return $screen_ids ;
	}

}

WC_CS_Enqueues::init() ;
