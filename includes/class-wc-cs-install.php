<?php

defined( 'ABSPATH' ) || exit ;

/**
 * Installation related functions and actions.
 * 
 * @class WC_CS_Install
 * @package Class
 */
class WC_CS_Install {

	/**
	 * Init Install.
	 */
	public static function init() {
		add_action( 'init', array( __CLASS__, 'check_version' ), 9 ) ;
		add_filter( 'plugin_action_links_' . WC_CS_BASENAME, array( __CLASS__, 'plugin_action_links' ) ) ;
	}

	/**
	 * Check WC CS version and run updater
	 */
	public static function check_version() {
		if ( get_option( WC_CS_PREFIX . 'version' ) !== WC_CS_VERSION ) {
			self::install() ;
			do_action( 'wc_cs_updated' ) ;
		}
	}

	/**
	 * Install WC CS.
	 */
	public static function install() {
		if ( ! defined( 'WC_CS_INSTALLING' ) ) {
			define( 'WC_CS_INSTALLING', true ) ;
		}

		self::create_options() ;
		self::create_pages() ;
		self::update_wc_cs_version() ;

		do_action( 'wc_cs_installed' ) ;
	}

	/**
	 * Is this a brand new WC CS install?
	 * A brand new install has no version yet.
	 *
	 * @return bool
	 */
	private static function is_new_install() {
		return is_null( get_option( WC_CS_PREFIX . 'version', null ) ) ;
	}

	/**
	 * Update WC CS version to current.
	 */
	private static function update_wc_cs_version() {
		delete_option( WC_CS_PREFIX . 'version' ) ;
		add_option( WC_CS_PREFIX . 'version', WC_CS_VERSION ) ;
	}

	/**
	 * Default options.
	 *
	 * Sets up the default options used on the settings page.
	 */
	private static function create_options() {
		// Include settings so that we can run through defaults.
		include_once dirname( __FILE__ ) . '/admin/class-wc-cs-admin-settings.php' ;
		WC_CS_Admin_Settings::save_default_options() ;
	}

	/**
	 * Create pages that the plugin relies on, storing page IDs in variables.
	 */
	public static function create_pages() {
		include_once dirname( __FILE__ ) . '/admin/wc-cs-admin-functions.php' ;

		$pages = apply_filters( 'wc_cs_create_pages', array(
			'credits_dashboard' => array(
				'name'    => _x( 'credits-dashboard', 'Page slug', 'credits-for-woocommerce' ),
				'title'   => _x( 'Credits Dashboard', 'Page title', 'credits-for-woocommerce' ),
				'content' => '<!-- wp:shortcode -->[credits_dashboard]<!-- /wp:shortcode -->',
			),
				) ) ;

		foreach ( $pages as $key => $page ) {
			_wc_cs_create_page( esc_sql( $page[ 'name' ] ), 'woocommerce_' . $key . '_page_id', $page[ 'title' ], $page[ 'content' ] ) ;
		}
	}

	/**
	 * Show action links on the plugin screen.
	 *
	 * @param	mixed $links Plugin Action links
	 * @return	array
	 */
	public static function plugin_action_links( $links ) {
		$setting_page_link = '<a  href="' . admin_url( 'admin.php?page=wc_cs_settings' ) . '">' . esc_html__( 'Settings', 'credits-for-woocommerce' ) . '</a>' ;
		array_unshift( $links, $setting_page_link ) ;
		return $links ;
	}

}

WC_CS_Install::init() ;
