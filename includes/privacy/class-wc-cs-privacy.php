<?php
defined( 'ABSPATH' ) || exit ;

/**
 * Privacy/GDPR related functionality which ties into WordPress functionality.
 * 
 * @class WC_CS_Privacy
 * @package Class
 */
class WC_CS_Privacy {

	/**
	 * This is a list of exporters.
	 *
	 * @var array
	 */
	protected static $exporters = array() ;

	/**
	 * This is a list of erasers.
	 *
	 * @var array
	 */
	protected static $erasers = array() ;

	/**
	 * Limit background process to number of batches to avoid timeouts
	 *
	 * @var int 
	 */
	protected static $batch_limit = 10 ;

	/**
	 * Force erase personal data from user.
	 *
	 * @var bool 
	 */
	protected static $force_erase_personal_data = false ;

	/**
	 * Init WC_CS_Privacy.
	 */
	public static function init() {
		self::$force_erase_personal_data = 'yes' === get_option( WC_CS_PREFIX . 'erasure_request_removes_user_data', 'no' ) ;

		add_action( 'admin_init', __CLASS__ . '::add_privacy_message' ) ;

		self::add_exporter( 'wc_cs-credits-users-data', __( 'Credits Users Data', 'credits-for-woocommerce' ), __CLASS__ . '::credits_users_data_exporter' ) ;
		self::add_eraser( 'wc_cs-credits-users-data', __( 'Credits Users Data', 'credits-for-woocommerce' ), __CLASS__ . '::credits_users_data_eraser' ) ;

		add_filter( 'wp_privacy_personal_data_exporters', __CLASS__ . '::register_exporters', 6 ) ;
		add_filter( 'wp_privacy_personal_data_erasers', __CLASS__ . '::register_erasers' ) ;
	}

	/**
	 * Get plugin name
	 * 
	 * @return string
	 */
	public static function get_plugin_name() {
		$plugin = get_plugin_data( WC_CS_FILE ) ;
		return $plugin[ 'Name' ] ;
	}

	/**
	 * Adds the privacy message on WC Credits privacy page.
	 */
	public static function add_privacy_message() {
		if ( function_exists( 'wp_add_privacy_policy_content' ) ) {
			$content = self::get_privacy_message() ;

			if ( $content ) {
				wp_add_privacy_policy_content( self::get_plugin_name(), $content ) ;
			}
		}
	}

	/**
	 * Integrate this exporter implementation within the WordPress core exporters.
	 *
	 * @param array $exporters List of exporter callbacks.
	 * @return array
	 */
	public static function register_exporters( $exporters = array() ) {
		foreach ( self::$exporters as $id => $exporter ) {
			$exporters[ $id ] = $exporter ;
		}
		return $exporters ;
	}

	/**
	 * Integrate this eraser implementation within the WordPress core erasers.
	 *
	 * @param array $erasers List of eraser callbacks.
	 * @return array
	 */
	public static function register_erasers( $erasers = array() ) {
		foreach ( self::$erasers as $id => $eraser ) {
			$erasers[ $id ] = $eraser ;
		}
		return $erasers ;
	}

	/**
	 * Add exporter to list of exporters.
	 *
	 * @param string $id       ID of the Exporter.
	 * @param string $name     Exporter name.
	 * @param string $callback Exporter callback.
	 */
	public static function add_exporter( $id, $name, $callback ) {
		self::$exporters[ $id ] = array(
			'exporter_friendly_name' => $name,
			'callback'               => $callback,
				) ;
		return self::$exporters ;
	}

	/**
	 * Add eraser to list of erasers.
	 *
	 * @param string $id       ID of the Eraser.
	 * @param string $name     Exporter name.
	 * @param string $callback Exporter callback.
	 */
	public static function add_eraser( $id, $name, $callback ) {
		self::$erasers[ $id ] = array(
			'eraser_friendly_name' => $name,
			'callback'             => $callback,
				) ;
		return self::$erasers ;
	}

	/**
	 * Add privacy policy content for the privacy policy page.
	 */
	public static function get_privacy_message() {
		ob_start() ;
		?>
		<p><?php esc_html_e( 'This includes the basics of what personal data your store may be collecting, storing and sharing. Depending on what settings are enabled and which additional plugins are used, the specific information shared by your store will vary.', 'credits-for-woocommerce' ) ; ?></p>
		<h2><?php esc_html_e( 'What the Plugin does', 'credits-for-woocommerce' ) ; ?></h2>
		<p><?php esc_html_e( 'Using this plugin, you can offer credit limit to your customers for making purchases on your site. Your customers can repay the used credit limit on a later date.', 'credits-for-woocommerce' ) ; ?></p>
		<h2><?php esc_html_e( 'What we collect and share', 'credits-for-woocommerce' ) ; ?></h2>
		<h2><?php esc_html_e( 'First Name, Last Name, Company Name, Address, Phone Number, Email', 'credits-for-woocommerce' ) ; ?></h2>
		<ul>
			<li><?php esc_html_e( '- Collecting personal details about the user. ', 'credits-for-woocommerce' ) ; ?></li>
		</ul>
		<h2><?php esc_html_e( 'User ID', 'credits-for-woocommerce' ) ; ?></h2>
		<ul>
			<li><?php esc_html_e( '- Identifying the User', 'credits-for-woocommerce' ) ; ?></li>            
		</ul>
		<?php
		return apply_filters( 'wc_cs_privacy_policy_content', ob_get_clean() ) ;
	}

	/**
	 * Finds and exports data which could be used to identify a person from WC Credits data associated with an email address.
	 *
	 * Users data are exported in blocks of 10 to avoid timeouts.
	 *
	 * @param string $email_address The user email address.
	 * @param int    $page  Page.
	 * @return array An array of personal data in name value pairs
	 */
	public static function credits_users_data_exporter( $email_address, $page ) {
		$done           = false ;
		$data_to_export = array() ;
		$user           = get_user_by( 'email', $email_address ) ; // Check if user has an ID in the DB to load stored personal data.

		if ( $user instanceof WP_User ) {
			$credits_ids = get_posts( array(
				'post_type'   => 'wc_cs_credits',
				'post_status' => 'all',
				'numberposts' => self::$batch_limit,
				'page'        => absint( $page ),
				'fields'      => 'ids',
				'meta_key'    => '_user_email',
				'meta_value'  => $email_address,
					) ) ;

			if ( 0 < count( $credits_ids ) ) {
				foreach ( $credits_ids as $id ) {
					$credits         = _wc_cs_get_credits( $id ) ;
					$data_to_export[] = array(
						'group_id'    => 'wc_cs_credits',
						'group_label' => __( 'Credits for Woocommerce', 'credits-for-woocommerce' ),
						'item_id'     => "credits-{$id}",
						'data'        => self::get_personal_data( $credits ),
							) ;
				}

				$done = 10 > count( $credits_ids ) ;
			} else {
				$done = true ;
			}
		}

		return array(
			'data' => $data_to_export,
			'done' => $done,
				) ;
	}

	/**
	 * Finds and erases data which could be used to identify a person from WC Credits data assocated with an email address.
	 *
	 * Users data are erased in blocks of 10 to avoid timeouts.
	 *
	 * @param string $email_address The user email address.
	 * @param int    $page  Page.
	 * @return array An array of personal data in name value pairs
	 */
	public static function credits_users_data_eraser( $email_address, $page ) {
		$user     = get_user_by( 'email', $email_address ) ; // Check if user has an ID in the DB to load stored personal data.
		$response = array(
			'items_removed'  => false,
			'items_retained' => false,
			'messages'       => array(),
			'done'           => true,
				) ;

		if ( $user instanceof WP_User ) {
			$credits_ids = get_posts( array(
				'post_type'   => 'wc_cs_credits',
				'post_status' => 'all',
				'numberposts' => self::$batch_limit,
				'page'        => absint( $page ),
				'fields'      => 'ids',
				'meta_key'    => '_user_email',
				'meta_value'  => $email_address,
					) ) ;

			if ( 0 < count( $credits_ids ) ) {
				foreach ( $credits_ids as $id ) {
					$credits = _wc_cs_get_credits( $id ) ;

					if ( apply_filters( 'wc_cs_privacy_erase_personal_data', self::$force_erase_personal_data, $credits ) ) {
						self::remove_personal_data( $credits ) ;

						/* Translators: %s Credits ID. */
						$response[ 'messages' ][]    = sprintf( __( 'Removed personal data from the credits %s.', 'credits-for-woocommerce' ), $id ) ;
						$response[ 'items_removed' ] = true ;
					} else {
						/* Translators: %s Credits ID. */
						$response[ 'messages' ][]     = sprintf( __( 'Personal data within the credits %s has been retained.', 'credits-for-woocommerce' ), $id ) ;
						$response[ 'items_retained' ] = true ;
					}
				}

				$response[ 'done' ] = 10 > count( $credits_ids ) ;
			} else {
				$response[ 'done' ] = true ;
			}
		}
		return $response ;
	}

	/**
	 * Get personal data (key/value pairs) for the User.
	 *
	 * @param object $credits
	 * @return array
	 */
	public static function get_personal_data( $credits ) {
		$personal_data   = array() ;
		$props_to_export = apply_filters( 'wc_cs_privacy_export_personal_data_props', array(
			'user_first_name' => __( 'User First Name', 'credits-for-woocommerce' ),
			'user_last_name'  => __( 'User last Name', 'credits-for-woocommerce' ),
			'user_company'    => __( 'User Company', 'credits-for-woocommerce' ),
			'user_address_1'  => __( 'User Address 1', 'credits-for-woocommerce' ),
			'user_address_2'  => __( 'User Address 2', 'credits-for-woocommerce' ),
			'user_city'       => __( 'User City', 'credits-for-woocommerce' ),
			'user_state'      => __( 'User State', 'credits-for-woocommerce' ),
			'user_postcode'   => __( 'User Postcode', 'credits-for-woocommerce' ),
			'user_country'    => __( 'User Country', 'credits-for-woocommerce' ),
			'user_email'      => __( 'User Email Address', 'credits-for-woocommerce' ),
			'user_phone'      => __( 'User Phone', 'credits-for-woocommerce' ),
				), $credits ) ;

		foreach ( $props_to_export as $prop => $name ) {
			$value  = '' ;
			$getter = "get_$prop" ;
			if ( is_callable( array( $credits, $getter ) ) ) {
				$value = $credits->{$getter}() ;
			}

			$value = apply_filters( 'wc_cs_privacy_export_personal_data_prop', $value, $prop, $credits ) ;

			if ( '' !== $value ) {
				$personal_data[] = array(
					'name'  => $name,
					'value' => $value,
						) ;
			}
		}

		/**
		 * Allow extensions to register their own personal data for the export.
		 *
		 * @param array $personal_data Array of name value pairs to expose in the export.
		 * @param object $credits
		 */
		$personal_data = apply_filters( 'wc_cs_privacy_export_personal_data', $personal_data, $credits ) ;

		return $personal_data ;
	}

	/**
	 * Remove personal data specific to the User.
	 * 
	 * @param object $credits
	 */
	public static function remove_personal_data( $credits ) {
		$anonymized_data = array() ;

		/**
		 * Allow extensions to remove their own personal data first, so user data is still available.
		 */
		do_action( 'wc_cs_privacy_before_remove_personal_data', $credits ) ;

		/**
		 * Expose props and data types we'll be anonymizing.
		 */
		$props_to_remove = apply_filters( 'wc_cs_privacy_remove_personal_data_props', array(
			'user_first_name' => 'text',
			'user_last_name'  => 'text',
			'user_company'    => 'text',
			'user_address_1'  => 'text',
			'user_address_2'  => 'text',
			'user_city'       => 'text',
			'user_postcode'   => 'text',
			'user_state'      => 'address_state',
			'user_country'    => 'address_country',
			'user_email'      => 'email',
			'user_phone'      => 'phone',
				), $credits ) ;

		if ( ! empty( $props_to_remove ) && is_array( $props_to_remove ) ) {
			foreach ( $props_to_remove as $prop => $data_type ) {
				$value  = '' ;
				$getter = "get_$prop" ;
				if ( is_callable( array( $credits, $getter ) ) ) {
					$value = $credits->{$getter}() ;
				}

				// If the value is empty, it does not need to be anonymized.
				if ( '' === $value || empty( $data_type ) ) {
					continue ;
				}

				$anon_value = function_exists( 'wp_privacy_anonymize_data' ) ? wp_privacy_anonymize_data( $data_type, $value ) : '' ;

				/**
				 * Expose a way to control the anonymized value of a prop via 3rd party code.
				 */
				$anonymized_data[ $prop ] = apply_filters( 'wc_cs_privacy_remove_personal_data_prop_value', $anon_value, $prop, $value, $data_type, $credits ) ;
			}
		}

		// Set all new props and persist the new data to the database.
		foreach ( $anonymized_data as $prop => $anon_value ) {
			if ( $anon_value ) {
				update_post_meta( $credits->get_id(), "_{$prop}", $anon_value ) ;
			} else {
				delete_post_meta( $credits->get_id(), "_{$prop}" ) ;
			}
		}

		update_post_meta( $credits->get_id(), '_anonymized', 'yes' ) ;

		/**
		 * Allow extensions to remove their own personal data.
		 */
		do_action( 'wc_cs_privacy_remove_personal_data', $credits ) ;
	}

}

WC_CS_Privacy::init() ;
