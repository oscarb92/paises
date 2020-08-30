<?php

defined( 'ABSPATH' ) || exit ;

/**
 * Credits for Woocommerce Admin
 * 
 * @class WC_CS_Admin
 * @package Class
 */
class WC_CS_Admin {

	/**
	 * Init WC_CS_Admin.
	 */
	public static function init() {
		add_action( 'init', __CLASS__ . '::includes' ) ;
		add_action( 'admin_menu', __CLASS__ . '::admin_menus' ) ;
	}

	/**
	 * Include any classes we need within admin.
	 */
	public static function includes() {
		include_once('wc-cs-admin-functions.php') ;
		include_once('class-wc-cs-admin-post-types.php') ;
		include_once('class-wc-cs-admin-meta-boxes.php') ;
		include_once('class-wc-cs-admin-settings.php') ;
	}

	/**
	 * Add admin menu pages.
	 */
	public static function admin_menus() {
		add_menu_page( __( 'Credits', 'credits-for-woocommerce' ), __( 'Credits', 'credits-for-woocommerce' ), 'manage_woocommerce', 'credits-for-woocommerce', null, WC_CS_URL . '/assets/images/dash-icon.png', '56.5.1' ) ;
		add_submenu_page( 'credits-for-woocommerce', __( 'Settings', 'credits-for-woocommerce' ), __( 'Settings', 'credits-for-woocommerce' ), 'manage_woocommerce', 'wc_cs_settings', 'WC_CS_Admin_Settings::output' ) ;
	}

}

WC_CS_Admin::init() ;
