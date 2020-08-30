<?php

defined( 'ABSPATH' ) || exit ;

/**
 * Handle Admin menus, post types and settings.
 * 
 * @class WC_CS_Admin_Settings
 * @package Class
 */
class WC_CS_Admin_Settings {

	/**
	 * Setting pages.
	 *
	 * @var array
	 */
	private static $settings = array() ;

	/**
	 * Init WC_CS_Admin_Settings.
	 */
	public static function init() {
		add_action( 'wc_cs_reset_options', __CLASS__ . '::reset_options' ) ;
		add_filter( 'woocommerce_account_settings', __CLASS__ . '::add_wc_account_settings' ) ;
	}

	/**
	 * Include the settings page classes.
	 */
	public static function get_settings_pages() {
		if ( empty( self::$settings ) ) {

			self::$settings[] = include( 'settings-page/class-wc-cs-admin-settings-general.php' ) ;
			self::$settings[] = include( 'settings-page/class-wc-cs-admin-settings-rules.php' ) ;
			self::$settings[] = include( 'settings-page/class-wc-cs-admin-settings-advanced.php' ) ;
			self::$settings[] = include( 'settings-page/class-wc-cs-admin-settings-frontend-form.php' ) ;
			self::$settings[] = include( 'settings-page/class-wc-cs-admin-settings-message.php' ) ;
		}

		return self::$settings ;
	}

	/**
	 * Settings page.
	 *
	 * Handles the display of the main Credits for Woocommerce settings page in admin.
	 */
	public static function output() {
		global $current_section, $current_tab ;

		do_action( 'wc_cs_settings_start' ) ;

		$current_tab     = ( empty( $_GET[ 'tab' ] ) ) ? 'general' : urldecode( sanitize_text_field( $_GET[ 'tab' ] ) ) ;
		$current_section = ( empty( $_REQUEST[ 'section' ] ) ) ? '' : urldecode( sanitize_text_field( $_REQUEST[ 'section' ] ) ) ;

		// Include settings pages
		self::get_settings_pages() ;

		do_action( 'wc_cs_add_options_' . $current_tab ) ;
		do_action( 'wc_cs_add_options' ) ;

		if ( $current_section ) {
			do_action( 'wc_cs_add_options_' . $current_tab . '_' . $current_section ) ;
		}

		if ( ! empty( $_POST[ 'save' ] ) ) {
			if ( empty( $_REQUEST[ WC_CS_PREFIX . 'nonce' ] ) || ! wp_verify_nonce( sanitize_key( $_REQUEST[ WC_CS_PREFIX . 'nonce' ] ), 'wc-cs-settings' ) ) {
				die( esc_html__( 'Action failed. Please refresh the page and retry.', 'credits-for-woocommerce' ) ) ;
			}

			// Save settings if data has been posted
			do_action( 'wc_cs_update_options_' . $current_tab, $_POST ) ;
			do_action( 'wc_cs_update_options', $_POST ) ;

			if ( $current_section ) {
				do_action( 'wc_cs_update_options_' . $current_tab . '_' . $current_section, $_POST ) ;
			}

			wp_safe_redirect( esc_url_raw( add_query_arg( array( 'saved' => 'true' ) ) ) ) ;
			exit ;
		}
		if ( ! empty( $_POST[ 'reset' ] ) || ! empty( $_POST[ 'reset_all' ] ) ) {
			if ( empty( $_REQUEST[ WC_CS_PREFIX . 'nonce' ] ) || ! wp_verify_nonce( sanitize_key( $_REQUEST[ WC_CS_PREFIX . 'nonce' ] ), 'wc-cs-reset-settings' ) ) {
				die( esc_html__( 'Action failed. Please refresh the page and retry.', 'credits-for-woocommerce' ) ) ;
			}

			do_action( 'wc_cs_reset_options_' . $current_tab, $_POST ) ;

			if ( ! empty( $_POST[ 'reset_all' ] ) ) {
				do_action( 'wc_cs_reset_options', $_POST ) ;
			}

			if ( $current_section ) {
				do_action( 'wc_cs_reset_options_' . $current_tab . '_' . $current_section, $_POST ) ;
			}

			wp_safe_redirect( esc_url_raw( add_query_arg( array( 'saved' => 'true' ) ) ) ) ;
			exit ;
		}
		// Get any returned messages
		$error   = ( empty( $_GET[ 'wc_error' ] ) ) ? '' : urldecode( stripslashes( sanitize_title( $_GET[ 'wc_error' ] ) ) ) ;
		$message = ( empty( $_GET[ 'wc_message' ] ) ) ? '' : urldecode( stripslashes( sanitize_title( $_GET[ 'wc_message' ] ) ) ) ;

		if ( $error || $message ) {
			if ( $error ) {
				echo '<div id="message" class="error fade"><p><strong>' . esc_html( $error ) . '</strong></p></div>' ;
			} else {
				echo '<div id="message" class="updated fade"><p><strong>' . esc_html( $message ) . '</strong></p></div>' ;
			}
		} elseif ( ! empty( $_GET[ 'saved' ] ) ) {
			echo '<div id="message" class="updated fade"><p><strong>' . esc_html__( 'Your settings have been saved.', 'credits-for-woocommerce' ) . '</strong></p></div>' ;
		}

		include 'views/html-admin-settings.php' ;
	}

	/**
	 * Default options.
	 *
	 * Sets up the default options used on the settings page.
	 */
	public static function save_default_options( $reset_all = false ) {

		if ( empty( self::$settings ) ) {
			self::get_settings_pages() ;
		}

		foreach ( self::$settings as $tab ) {
			if ( ! isset( $tab->settings ) || ! is_array( $tab->settings ) ) {
				continue ;
			}

			$tab->add_options( $reset_all ) ;
		}
	}

	/**
	 * Reset All settings
	 */
	public static function reset_options() {

		self::save_default_options( true ) ;
	}

	/**
	 * Add privacy setings under WooCommerce Privacy
	 *
	 * @param array $settings
	 * @return array
	 */
	public static function add_wc_account_settings( $settings ) {
		$original_settings = $settings ;

		if ( ! empty( $original_settings ) ) {
			$new_settings = array() ;

			foreach ( $original_settings as $pos => $setting ) {
				if ( ! isset( $setting[ 'id' ] ) ) {
					continue ;
				}

				switch ( $setting[ 'id' ] ) {
					case 'woocommerce_erasure_request_removes_order_data':
						$new_settings[ $pos + 1 ] = array(
							'title'         => esc_html__( 'Account erasure requests', 'credits-for-woocommerce' ),
							'desc'          => esc_html__( 'Remove personal data from Credits for Woocommerce', 'credits-for-woocommerce' ),
							/* Translators: %s URL to erasure request screen. */
							'desc_tip'      => sprintf( __( 'When handling an <a href="%s">account erasure request</a>, should personal data within Credits for Woocommerce be retained or removed?', 'credits-for-woocommerce' ), esc_url( admin_url( 'tools.php?page=remove_personal_data' ) ) ),
							'id'            => WC_CS_PREFIX . 'erasure_request_removes_user_data',
							'type'          => 'checkbox',
							'default'       => 'no',
							'checkboxgroup' => '',
							'autoload'      => false,
								) ;
						break ;
				}
			}
			if ( ! empty( $new_settings ) ) {
				foreach ( $new_settings as $pos => $new_setting ) {
					array_splice( $settings, $pos, 0, array( $new_setting ) ) ;
				}
			}
		}
		return $settings ;
	}

}

WC_CS_Admin_Settings::init() ;
