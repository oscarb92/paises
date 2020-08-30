<?php

/**
 * Abstract Credits for Woocommerce Settings Page
 * 
 * @abstract WC_CS_Abstract_Settings
 */
abstract class WC_CS_Abstract_Settings {

	/**
	 * Page id.
	 * 
	 * @var string 
	 */
	protected $id = '' ;

	/**
	 * Setting page label.
	 * 
	 * @var string
	 */
	protected $label = '' ;

	/**
	 * Get settings.
	 * 
	 * @var array
	 */
	public $settings = array() ;

	/**
	 * Get sections.
	 * 
	 * @var array
	 */
	public $sections = array() ;

	/**
	 * Get plugin prefix.
	 * 
	 * @var string
	 */
	protected $prefix = WC_CS_PREFIX ;

	/**
	 * Get custom field type[].
	 * 
	 * @var array
	 */
	protected $custom_fields = array() ;

	/**
	 * Get custom settings field prefix.
	 * 
	 * @var string
	 */
	protected $custom_field_prefix = 'woocommerce_admin_field_' ;

	/**
	 * Init setting page.
	 */
	protected function init() {
		global $current_tab ;

		add_filter( 'wc_cs_settings_tabs_array', array( $this, 'add_settings_page' ) ) ;
		add_action( 'wc_cs_settings_' . $this->id, array( $this, 'output' ) ) ;
		add_action( 'wc_cs_sections_' . $this->id, array( $this, 'output_sections' ) ) ;
		add_action( 'wc_cs_add_options_' . $this->id, array( $this, 'add_options' ) ) ;
		add_action( 'wc_cs_update_options_' . $this->id, array( $this, 'save' ) ) ;
		add_action( 'wc_cs_reset_options_' . $this->id, array( $this, 'reset' ) ) ;

		foreach ( $this->custom_fields as $type ) {
			if ( $current_tab === $this->id ) {
				add_action( $this->get_custom_field_hook( $type ), array( $this, $type ) ) ;
			}
		}
	}

	/**
	 * Get settings page ID.
	 *
	 * @return string
	 */
	public function get_id() {
		return $this->id ;
	}

	/**
	 * Get settings page label.
	 *
	 * @return string
	 */
	public function get_label() {
		return $this->label ;
	}

	/**
	 * Get settings array.
	 *
	 * @return array
	 */
	public function get_settings() {
		return $this->settings ;
	}

	/**
	 * Get settings array.
	 *
	 * @return array
	 */
	public function get_sections() {
		return $this->sections ;
	}

	/**
	 * Get custom field type hook
	 * 
	 * @param string $type
	 * @return string
	 */
	public function get_custom_field_hook( $type ) {
		return $this->custom_field_prefix . $this->get_custom_field_type( $type ) ;
	}

	/**
	 * Get custom field type
	 * 
	 * @param string $type
	 * @return string
	 */
	public function get_custom_field_type( $type ) {
		return "{$this->prefix}_{$type}" ;
	}

	/**
	 * Add this page to settings.
	 *
	 * @param array $pages
	 * @return array
	 */
	public function add_settings_page( $pages ) {
		$pages[ $this->id ] = $this->label ;

		return $pages ;
	}

	/**
	 * Output the settings.
	 */
	public function output() {
		woocommerce_admin_fields( $this->settings ) ;
	}

	/**
	 * Output sections.
	 */
	public function output_sections() {
		global $current_section ;

		if ( empty( $this->sections ) || 1 === count( $this->sections ) ) {
			return ;
		}

		echo '<ul class="subsubsub">' ;

		$array_keys = array_keys( $this->sections ) ;

		foreach ( $this->sections as $id => $label ) {
			echo '<li><a href="' . esc_url( admin_url( 'admin.php?page=wc_cs_settings&tab=' . $this->id . '&section=' . sanitize_title( $id ) ) ) . '" class="' . esc_attr( $current_section == $id ? 'current' : ''  ) . '">' . esc_html( $label ) . '</a> ' . esc_html( end( $array_keys ) == $id ? '' : '|'  ) . ' </li>' ;
		}

		echo '</ul><br class="clear" />' ;
	}

	/**
	 * Looping through each settings fields and save the option once.
	 */
	public function add_options( $reset_all = false ) {

		if ( $reset_all && is_callable( array( $this, 'custom_types_delete_options' ) ) ) {
			$this->custom_types_delete_options() ;
		}
		if ( is_callable( array( $this, 'custom_types_add_options' ) ) ) {
			$this->custom_types_add_options() ;
		}

		foreach ( $this->settings as $setting ) {
			if ( isset( $setting[ 'id' ], $setting[ 'default' ] ) ) {
				if ( $reset_all ) {
					delete_option( $setting[ 'id' ] ) ;
				}
				add_option( $setting[ 'id' ], $setting[ 'default' ] ) ;
			}
		}
	}

	/**
	 * Save settings.
	 */
	public function save( $posted ) {
		woocommerce_update_options( $this->settings ) ;

		if ( is_callable( array( $this, 'custom_types_delete_options' ) ) ) {
			$this->custom_types_delete_options( $posted ) ;
		}
		if ( is_callable( array( $this, 'custom_types_save' ) ) ) {
			$this->custom_types_save( $posted ) ;
		}

		update_option( 'wc_cs_flush_rewrite_rules', 1 ) ;
	}

	/**
	 * Reset settings.
	 */
	public function reset( $posted ) {

		if ( is_callable( array( $this, 'custom_types_delete_options' ) ) ) {
			$this->custom_types_delete_options( $posted ) ;
		}
		if ( is_callable( array( $this, 'custom_types_add_options' ) ) ) {
			$this->custom_types_add_options( $posted ) ;
		}

		foreach ( $this->settings as $setting ) {
			if ( isset( $setting[ 'id' ], $setting[ 'default' ] ) ) {
				delete_option( $setting[ 'id' ] ) ;
				add_option( $setting[ 'id' ], $setting[ 'default' ] ) ;
			}
		}

		update_option( 'wc_cs_flush_rewrite_rules', 1 ) ;
	}

}
