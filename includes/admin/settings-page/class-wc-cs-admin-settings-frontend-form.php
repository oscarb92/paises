<?php

/**
 * Frontend Form Tab.
 * 
 * @class WC_CS_Settings_Frontend_Form
 * @package Class
 */
class WC_CS_Settings_Frontend_Form extends WC_CS_Abstract_Settings {

	/**
	 * WC_CS_Settings_Frontend_Form constructor.
	 */
	public function __construct() {

		$this->id            = 'frontend_form' ;
		$this->label         = __( 'Frontend Form', 'credits-for-woocommerce' ) ;
		$this->custom_fields = array(
			'get_form_fields',
				) ;
		$this->settings      = $this->get_settings() ;
		$this->init() ;
	}

	/**
	 * Get settings array.
	 *
	 * @return array
	 */
	public function get_settings() {
		global $current_section ;

		return apply_filters( 'wc_cs_get_' . $this->id . '_settings', array(
			array(
				'name' => __( 'Frontend Form', 'credits-for-woocommerce' ),
				'type' => 'title',
				'id'   => $this->prefix . 'frontend_form_settings'
			),
			array( 'type' => $this->get_custom_field_type( 'get_form_fields' ) ),
			array( 'type' => 'sectionend', 'id' => $this->prefix . 'frontend_form_settings' ),
				) ) ;
	}

	/**
	 * Custom type field.
	 */
	public function get_form_fields() {
		echo '<table class="widefat striped credits-application-form-fields" data-sort="false">' ;
		include 'views/html-application-form-fields.php' ;
		echo '</table>' ;
	}

	/**
	 * Delete the custom options.
	 */
	public function custom_types_delete_options( $posted = null ) {
		foreach ( WC_CS_Form_Fields::get_fields() as $field_key => $value ) {
			if ( $field_key ) {
				delete_option( "$this->prefix{$field_key}_enabled" ) ;
				delete_option( "$this->prefix{$field_key}_is_mandatory" ) ;
			}
		}
	}

	/**
	 * Save custom settings.
	 */
	public function custom_types_save( $posted ) {
		foreach ( WC_CS_Form_Fields::get_fields() as $field_key => $value ) {
			update_option( "$this->prefix{$field_key}_enabled", isset( $posted[ "$this->prefix{$field_key}_enabled" ] ) ? wc_clean( $posted[ "$this->prefix{$field_key}_enabled" ] ) : 'no'  ) ;
			update_option( "$this->prefix{$field_key}_is_mandatory", isset( $posted[ "$this->prefix{$field_key}_is_mandatory" ] ) ? wc_clean( $posted[ "$this->prefix{$field_key}_is_mandatory" ] ) : 'no'  ) ;
		}
	}

	/**
	 * Save the custom options once.
	 */
	public function custom_types_add_options( $posted = null ) {
		foreach ( WC_CS_Form_Fields::get_fields() as $field_key => $value ) {
			add_option( "$this->prefix{$field_key}_enabled", 'yes' ) ;
			add_option( "$this->prefix{$field_key}_is_mandatory", 'yes' ) ;
		}
	}

}

return new WC_CS_Settings_Frontend_Form() ;
