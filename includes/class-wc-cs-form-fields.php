<?php

defined( 'ABSPATH' ) || exit ;

/**
 * Handle our Form Fields by simply inheriting WC Checkout Form Fields.
 * 
 * @class WC_CS_Form_Fields
 * @package Class
 */
class WC_CS_Form_Fields {

	/**
	 * Get the fields.
	 * 
	 * @var array 
	 */
	protected static $fields ;

	/**
	 * Get the available fields for usage.
	 * 
	 * @var array 
	 */
	protected static $available_fields ;

	/**
	 * Init hooks.
	 */
	public static function init() {
		add_filter( 'woocommerce_form_field_wc_cs_file_attachments', __CLASS__ . '::prepare_file_attachments_fieldset', 10, 3 ) ;
	}

	/**
	 * Get an array of form fields.
	 *
	 * @return array
	 */
	public static function get_fields() {
		if ( is_array( self::$fields ) ) {
			return self::$fields ;
		}

		if ( is_null( WC()->countries ) ) {
			return array() ;
		}

		$current_user      = wp_get_current_user() ;
		$country           = self::get_value( 'country' ) ;
		$country           = empty( $country ) ? WC()->countries->get_base_country() : $country ;
		$allowed_countries = WC()->countries->get_allowed_countries() ;

		if ( ! array_key_exists( $country, $allowed_countries ) ) {
			$country = current( array_keys( $allowed_countries ) ) ;
		}

		$fields                       = WC()->countries->get_address_fields( $country, 'billing_' ) ;
		$fields[ 'file_attachments' ] = array(
			'label'    => __( 'Attach a File', 'credits-for-woocommerce' ),
			'required' => true,
			'type'     => 'wc_cs_file_attachments',
			'class'    => array( 'form-row-wide' ),
			'validate' => array( 'wc-cs-file' ),
				) ;

		self::$fields = array() ;
		foreach ( $fields as $field_key => $value ) {
			if ( in_array( $field_key, array(
						'billing_first_name',
						'billing_last_name',
						'billing_company',
						'billing_country',
						'billing_address_1',
						'billing_address_2',
						'billing_city',
						'billing_state',
						'billing_postcode',
						'billing_phone',
						'billing_email',
						'file_attachments',
					) )
			) {
				self::$fields[ $field_key ] = $value ;

				switch ( $field_key ) {
					case 'billing_email':
						self::$fields[ $field_key ][ 'required' ]          = true ;
						self::$fields[ $field_key ][ 'default_required' ]  = true ;
						self::$fields[ $field_key ][ 'default' ]           = esc_attr( $current_user->user_email ) ;
						self::$fields[ $field_key ][ 'custom_attributes' ] = array( 'readonly' => 'readonly' ) ;
						break ;
					default:
						self::$fields[ $field_key ][ 'required' ]          = 'yes' === get_option( WC_CS_PREFIX . $field_key . '_is_mandatory' ) ? true : false ;
				}
			}
		}

		return self::$fields ;
	}

	/**
	 * Get an array of available form fields.
	 *
	 * @return array
	 */
	public static function get_available_fields() {
		if ( is_array( self::$available_fields ) ) {
			return self::$available_fields ;
		}

		foreach ( self::get_fields() as $field_key => $value ) {
			if ( 'yes' === get_option( WC_CS_PREFIX . $field_key . '_enabled' ) ) {
				self::$available_fields[ $field_key ] = $value ;
			}

			if ( 'billing_address_2' === $field_key && array_key_exists( 'billing_address_1', self::$available_fields ) ) {
				self::$available_fields[ $field_key ]               = $value ;
				self::$available_fields[ $field_key ][ 'required' ] = false ;
			}
		}

		return self::$available_fields ;
	}

	/**
	 * Validates the posted data based on field properties.
	 *
	 * @param  array    $data   An array of posted data.
	 * @param  WP_Error $errors Validation error.
	 */
	public static function validate_posted_data( &$data, &$errors ) {

		foreach ( self::get_available_fields() as $field_key => $field ) {
			if ( ! isset( $field[ 'label' ] ) || ! $field[ 'required' ] ) {
				continue ;
			}

			switch ( $field_key ) {
				case 'file_attachments':
					if ( ! isset( $data[ $field_key ][ 'name' ][ 0 ] ) || empty( $data[ $field_key ][ 'name' ][ 0 ] ) ) {
						/* translators: 1: field name */
						$errors->add( 'required-field', sprintf( esc_html__( '%1$s is a required field. Please attach atleast one of your identity document.', 'credits-for-woocommerce' ), '<strong>' . esc_html( $field[ 'label' ] ) . '</strong>' ) ) ;
					}
					break ;
				default:
					if ( ! isset( $data[ $field_key ] ) || '' === $data[ $field_key ] ) {
						/* translators: 1: field name */
						$errors->add( 'required-field', sprintf( esc_html__( '%1$s is a required field.', 'credits-for-woocommerce' ), '<strong>' . esc_html( $field[ 'label' ] ) . '</strong>' ) ) ;
					}
			}
		}
	}

	/**
	 * Gets the value from POST. Sets the default values in form fields.
	 *
	 * @param string $input Name of the input we want to grab data for. e.g. first_name.
	 * @return string The default value.
	 */
	public static function get_value( $input ) {
		$posted = array_merge( ( array ) $_REQUEST, ( array ) $_FILES ) ;

		if ( ! isset( $posted[ WC_CS_PREFIX . 'nonce' ] ) || ! wp_verify_nonce( sanitize_key( wp_unslash( $posted[ WC_CS_PREFIX . 'nonce' ] ) ), 'wc-cs-credit-app-submited' ) ) {
			return null ;
		}

		if ( ! empty( $posted[ $input ] ) ) {
			return wc_clean( wp_unslash( $posted[ $input ] ) ) ;
		}

		return null ;
	}

	/**
	 * Enqueue WC scripts on demand.
	 */
	public static function enqueues() {
		wp_enqueue_script( 'wc-country-select' ) ;
		wp_enqueue_script( 'wc-address-i18n' ) ;
	}

	/**
	 * Prepare the fieldset.
	 * 
	 * @param string $field_body
	 * @param array $args
	 * @param string $wrapper_class
	 * @return string
	 */
	protected static function prepare_fieldset( $field_body, $args, $wrapper_class = '' ) {
		if ( $args[ 'required' ] ) {
			$required = '&nbsp;<abbr class="required" title="' . esc_attr__( 'required', 'woocommerce' ) . '">*</abbr>' ;
		} else {
			$required = '&nbsp;<span class="optional">(' . esc_html__( 'optional', 'woocommerce' ) . ')</span>' ;
		}

		if ( '' !== $wrapper_class ) {
			$field_container = '<div class="' . $wrapper_class . '"><p class="form-row %1$s" id="%2$s">%3$s</p></div>' ;
		} else {
			$field_container = '<p class="form-row %1$s" id="%2$s">%3$s</p>' ;
		}

		$field_html = '' ;

		if ( $args[ 'label' ] ) {
			$field_html = '<label for="' . esc_attr( $args[ 'id' ] ) . '" class="' . esc_attr( implode( ' ', $args[ 'label_class' ] ) ) . '">' . $args[ 'label' ] . $required . '</label>' ;
		}

		$field_html .= $field_body ;

		$container_class = esc_attr( implode( ' ', $args[ 'class' ] ) ) ;
		$container_id    = esc_attr( $args[ 'id' ] ) . '_field' ;
		$field           = sprintf( $field_container, $container_class, $container_id, $field_html ) ;

		return $field ;
	}

	/**
	 * Prepare and get the file attachments fieldset.
	 */
	public static function prepare_file_attachments_fieldset( $field, $key, $args ) {
		$field_body = '<a href="#" class="woocommerce-button button wc-cs-add-file">' . esc_html__( 'Add Attachment', 'credits-for-woocommerce' ) . '</a>' ;
		$field_body .= '<table><tbody></tbody></table>' ;

		return self::prepare_fieldset( $field_body, $args, 'wc-cs-file-attachments' ) ;
	}

}
