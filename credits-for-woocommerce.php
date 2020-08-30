<?php

/**
 * Plugin Name: Credits for WooCommerce
 * Description: Customers can request credits which when approved by the admin can be used to make purchases right away. Used credits can be repaid later.
 * Version: 2.0
 * Author: FantasticPlugins
 * Author URI: http://fantasticplugins.com
 * Text Domain: credits-for-woocommerce
 * Domain Path: /languages
 * Woo: 5193955:e6bd211c49fefac2a35d7ede9f58e9fe
 * Tested up to: 5.4.2
 * WC tested up to: 4.2.2
 * WC requires at least: 3.0
 * Copyright: © 2019 FantasticPlugins
 * License: GNU General Public License v3.0
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 */
defined( 'ABSPATH' ) || exit ;

/**
 * Initiate Plugin Core class.
 * 
 * @class WC_Credit_System
 * @package Class
 */
final class WC_Credit_System {

	/**
	 * Plugin version.
	 * 
	 * @var string 
	 */
	public $version = '2.0' ;

	/**
	 * Plugin prefix.
	 * 
	 * @var string 
	 */
	public $prefix = '_wc_cs_' ;

	/**
	 * Get Query instance.
	 * 
	 * @var WC_CS_Query 
	 */
	public $query ;

	/**
	 * Collect the queued jobs.
	 * 
	 * @var WC_CS_Queue[] 
	 */
	public $queue = array() ;

	/**
	 * Is on Auto approval mode ?
	 * 
	 * @var bool 
	 */
	protected $is_on_auto_approval ;

	/**
	 * Is funding source via real money ?
	 * 
	 * @var bool 
	 */
	protected $funding_via_real_money ;

	/**
	 * The single instance of the class.
	 */
	protected static $instance = null ;

	/**
	 * WC_Credit_System constructor.
	 */
	public function __construct() {
		$this->init_plugin_dependencies() ;

		if ( true !== $this->plugin_dependencies_met() ) {
			return ; // Return to stop the existing function to be call 
		}

		$this->define_constants() ;
		$this->include_files() ;
		$this->init_hooks() ;
	}

	/**
	 * Cloning is forbidden.
	 */
	public function __clone() {
		_doing_it_wrong( __FUNCTION__, esc_html__( 'Cloning is forbidden.', 'credits-for-woocommerce' ), '1.1' ) ;
	}

	/**
	 * Unserializing instances of this class is forbidden.
	 */
	public function __wakeup() {
		_doing_it_wrong( __FUNCTION__, esc_html__( 'Unserializing instances of this class is forbidden.', 'credits-for-woocommerce' ), '1.1' ) ;
	}

	/**
	 * Auto-load in-accessible properties on demand.
	 *
	 * @param mixed $key Key name.
	 * @return mixed
	 */
	public function __get( $key ) {
		if ( in_array( $key, array( 'funds_addition', 'repayment', 'dashboard', 'gateways', 'mailer' ), true ) ) {
			return $this->$key() ;
		}
	}

	/**
	 * Main WC_Credit_System Instance.
	 * Ensures only one instance of WC_Credit_System is loaded or can be loaded.
	 * 
	 * @return WC_Credit_System - Main instance.
	 */
	public static function instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self() ;
		}
		return self::$instance ;
	}

	/**
	 * Init plugin dependencies.
	 */
	private function init_plugin_dependencies() {
		include_once( ABSPATH . 'wp-admin/includes/plugin.php' ) ; //Prevent fatal error by load the files when you might call init hook.
		add_action( 'init', array( $this, 'prevent_header_sent_problem' ), 1 ) ;
		add_action( 'admin_notices', array( $this, 'plugin_dependencies_notice' ) ) ;
	}

	/**
	 * Prevent header problem while plugin activates.
	 */
	public function prevent_header_sent_problem() {
		ob_start() ;
	}

	/**
	 * Check whether the plugin dependencies met.
	 * 
	 * @return bool|string True on Success
	 */
	private function plugin_dependencies_met( $return_dep_notice = false ) {
		$return = false ;

		if ( is_multisite() && is_plugin_active_for_network( 'woocommerce/woocommerce.php' ) && is_plugin_active( 'woocommerce/woocommerce.php' ) ) {
			$is_wc_active = true ;
		} else if ( is_plugin_active( 'woocommerce/woocommerce.php' ) ) {
			$is_wc_active = true ;
		} else {
			$is_wc_active = false ;
		}

		// WC check.
		if ( ! $is_wc_active ) {
			if ( $return_dep_notice ) {
				$return = 'Credits for Woocommerce Plugin requires WooCommerce Plugin should be Active !!!' ;
			}

			return $return ;
		}

		return true ;
	}

	/**
	 * Output a admin notice when plugin dependencies not met.
	 */
	public function plugin_dependencies_notice() {
		$return = $this->plugin_dependencies_met( true ) ;

		if ( true !== $return && current_user_can( 'activate_plugins' ) ) {
			$dependency_notice = $return ;
			printf( '<div class="error"><p>%s</p></div>', wp_kses_post( $dependency_notice ) ) ;
		}
	}

	/**
	 * Define constants.
	 */
	private function define_constants() {
		$this->define( 'WC_CS_FILE', __FILE__ ) ;
		$this->define( 'WC_CS_DIR', plugin_dir_path( WC_CS_FILE ) ) ;
		$this->define( 'WC_CS_BASENAME', plugin_basename( WC_CS_FILE ) ) ;
		$this->define( 'WC_CS_BASENAME_DIR', trailingslashit( dirname( WC_CS_BASENAME ) ) ) ;
		$this->define( 'WC_CS_TEMPLATE_PATH', WC_CS_DIR . 'templates/' ) ;
		$this->define( 'WC_CS_URL', untrailingslashit( plugins_url( '/', WC_CS_FILE ) ) ) ;
		$this->define( 'WC_CS_VERSION', $this->version ) ;
		$this->define( 'WC_CS_PREFIX', $this->prefix ) ;
	}

	/**
	 * Define constant if not already set.
	 *
	 * @param string      $name  Constant name.
	 * @param string|bool $value Constant value.
	 */
	private function define( $name, $value ) {
		if ( ! defined( $name ) ) {
			define( $name, $value ) ;
		}
	}

	/**
	 * Is frontend request ?
	 *
	 * @return bool
	 */
	private function is_frontend() {
		return ( ! is_admin() || defined( 'DOING_AJAX' ) ) && ! defined( 'DOING_CRON' ) ;
	}

	/**
	 * Check if our plugin core is enabled globally
	 */
	public function core_enabled() {
		return ( bool ) apply_filters( 'wc_cs_plugin_core_enabled', true ) ;
	}

	/**
	 * Check if the credits approval mode is on Auto.
	 * 
	 * @return bool
	 */
	public function is_on_auto_approval() {
		if ( ! is_bool( $this->is_on_auto_approval ) ) {
			$this->is_on_auto_approval = 'auto-approval' === get_option( WC_CS_PREFIX . 'credit_line_approval_mode', 'app-approval' ) ? true : false ;
		}

		return $this->is_on_auto_approval ;
	}

	/**
	 * Check if the credits funding source via real money.
	 * 
	 * @return bool
	 */
	public function funding_via_real_money() {
		if ( ! is_bool( $this->funding_via_real_money ) ) {
			$this->funding_via_real_money = 'from-admin' === get_option( WC_CS_PREFIX . 'credit_line_funding_source', 'from-virtual' ) ? true : false ;
		}

		return $this->funding_via_real_money ;
	}

	/**
	 * Include required core files used in admin and on the frontend.
	 */
	private function include_files() {
		//Class autoloader.
		include_once('includes/class-wc-cs-autoload.php') ;

		//Abstract classes.
		include_once('includes/abstracts/abstract-wc-cs-settings.php') ;
		include_once('includes/abstracts/abstract-wc-cs-data.php') ;
		include_once('includes/abstracts/abstract-wc-cs-credits.php') ;
		include_once('includes/abstracts/abstract-wc-cs-transaction.php') ;

		/**
		 * Data stores - used to store and retrieve CRUD object data from the database.
		 */
		include_once('includes/data-stores/abstract-wc-cs-transaction-data-store-cpt.php') ;
		include_once('includes/data-stores/class-wc-cs-credits-data-store-cpt.php') ;
		include_once('includes/data-stores/class-wc-cs-credits-transaction-data-store-cpt.php') ;
		include_once('includes/data-stores/class-wc-cs-bill-statement-data-store-cpt.php') ;
		include_once('includes/data-stores/class-wc-cs-admin-funds-transaction-data-store-cpt.php') ;
		include_once('includes/data-stores/class-wc-cs-virtual-funds-transaction-data-store-cpt.php') ;

		//Core functions.
		include_once('includes/wc-cs-core-functions.php') ;

		//Core classes.
		include_once('includes/class-wc-cs-post-types.php') ;
		include_once('includes/class-wc-cs-install.php') ;
		include_once('includes/class-wc-cs-download-handler.php') ;
		include_once('includes/class-wc-cs-ajax.php') ;
		include_once('includes/class-wc-cs-enqueues.php') ;
		include_once('includes/privacy/class-wc-cs-privacy.php') ;
		include_once('includes/class-wc-cs-order-manager.php') ;

		if ( is_admin() ) {
			include_once('includes/admin/class-wc-cs-admin.php') ;
		}

		if ( $this->is_frontend() ) {
			$this->frontend_includes() ;
		}

		$this->gateways->init() ;
		$this->query = new WC_CS_Query() ;
	}

	/**
	 * Include required frontend files.
	 */
	private function frontend_includes() {
		if ( ! $this->core_enabled() ) {
			return ;
		}

		$this->funds_addition->init() ;
		$this->repayment->init() ;
		$this->dashboard->init() ;

		include_once('includes/wc-cs-template-hooks.php') ;
		include_once('includes/class-wc-cs-form-handler.php') ;
	}

	/**
	 * Hook into actions and filters.
	 */
	private function init_hooks() {
		register_activation_hook( WC_CS_FILE, array( 'WC_CS_Install', 'install' ) ) ;
		register_deactivation_hook( WC_CS_FILE, array( $this, 'upon_deactivation' ) ) ;
		add_action( 'plugins_loaded', array( $this, 'on_plugins_loaded' ), 5 ) ;
		add_action( 'init', array( $this, 'init' ), 5 ) ;
	}

	/**
	 * Fire upon deactivating Credits for Woocommerce
	 */
	public function upon_deactivation() {
		update_option( 'wc_cs_flush_rewrite_rules', 1 ) ;
		_wc_cs_cancel_all_queue_process() ;
	}

	/**
	 * When WP has loaded all plugins, trigger the `wc_cs_loaded` hook.
	 */
	public function on_plugins_loaded() {
		$this->load_plugin_textdomain() ;

		WC_CS_Form_Fields::init() ;

		do_action( 'wc_cs_loaded' ) ;
	}

	/**
	 * Load Localization files.
	 */
	public function load_plugin_textdomain() {
		if ( function_exists( 'determine_locale' ) ) {
			$locale = determine_locale() ;
		} else {
			$locale = is_admin() ? get_user_locale() : get_locale() ;
		}

		$locale = apply_filters( 'plugin_locale', $locale, 'credits-for-woocommerce' ) ;

		unload_textdomain( 'credits-for-woocommerce' ) ;
		load_textdomain( 'credits-for-woocommerce', WP_LANG_DIR . '/credits-for-woocommerce/credits-for-woocommerce-' . $locale . '.mo' ) ;
		load_plugin_textdomain( 'credits-for-woocommerce', false, WC_CS_BASENAME_DIR . 'languages' ) ;
	}

	/**
	 * Init WC_Credit_System when WordPress Initializes. 
	 */
	public function init() {
		do_action( 'before_wc_cs_init' ) ;

		$this->mailer->init() ; //Load mailer
		$this->init_background_process() ;

		// Init recurring job.
		if ( ! _wc_cs_job_exists_in_queue( 'credits', 'heartbeat' ) ) {
			_wc_cs_push_recurring_job_to_queue( 'credits', 'heartbeat', _wc_cs_get_time( 'timestamp', array( 'time' => '+5 minutes' ) ), '5mins' ) ;
		}

		do_action( 'wc_cs_init' ) ;
	}

	/**
	 * Init the background process.
	 */
	protected function init_background_process() {
		include_once('includes/background-process/wc-cs-background-functions.php') ;
		include_once('includes/background-process/abstract-wc-cs-background-process.php') ;
		include_once('includes/queue/abstract-wc-cs-queue.php') ;

		$queues = apply_filters( 'wc_cs_queue_classes', array(
			'credits' => 'WC_CS_Credits_Queue'
				) ) ;

		foreach ( $queues as $name => $class_name ) {
			if ( ! class_exists( $class_name ) ) {
				continue ;
			}

			$this->queue[ $name ] = new $class_name() ;
			$this->queue[ $name ]->dispatch() ;
		}
	}

	/**
	 * Include classes for plugin support.
	 */
	private function other_plugin_support_includes() {
		
	}

	/**
	 * Get Funds Addition class.
	 *
	 * @return WC_CS_Add_Funds
	 */
	public function funds_addition() {
		return WC_CS_Add_Funds::instance() ;
	}

	/**
	 * Get Repayment class.
	 *
	 * @return WC_CS_Repayment
	 */
	public function repayment() {
		return WC_CS_Repayment::instance() ;
	}

	/**
	 * Get Dashboard class.
	 *
	 * @return WC_CS_MyDashboard
	 */
	public function dashboard() {
		return WC_CS_MyDashboard::instance() ;
	}

	/**
	 * Get gateways class.
	 *
	 * @return WC_CS_Payment_Gateways
	 */
	public function gateways() {
		return WC_CS_Payment_Gateways::instance() ;
	}

	/**
	 * Email Class.
	 *
	 * @return WC_CS_Emails
	 */
	public function mailer() {
		return WC_CS_Emails::instance() ;
	}

}

/**
 * Main instance of WC_Credit_System.
 * Returns the main instance of WC_Credit_System.
 *
 * @return WC_Credit_System
 */
function _wc_cs() {
	return WC_Credit_System::instance() ;
}

/**
 * Run Credits for Woocommerce
 */
_wc_cs() ;
