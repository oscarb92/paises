<?php

/**
 * Advanced Tab.
 * 
 * @class WC_CS_Settings_Advanced
 * @package Class
 */
class WC_CS_Settings_Advanced extends WC_CS_Abstract_Settings {

	/**
	 * WC_CS_Settings_Advanced constructor.
	 */
	public function __construct() {

		$this->id       = 'advanced' ;
		$this->label    = __( 'Advanced', 'credits-for-woocommerce' ) ;
		$this->settings = $this->get_settings() ;
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
				'name' => __( 'Advanced Settings', 'credits-for-woocommerce' ),
				'type' => 'title',
				'id'   => $this->prefix . 'advanced_settings',
			),
			array(
				'name'     => __( 'Credit Line has to be Given to Users', 'credits-for-woocommerce' ),
				'id'       => $this->prefix . 'credit_line_funding_source',
				'type'     => 'select',
				'options'  => array(
					'from-admin'   => __( 'From Admin Funds', 'credits-for-woocommerce' ),
					'from-virtual' => __( 'From Virtual Funds', 'credits-for-woocommerce' ),
				),
				'default'  => 'from-virtual',
				'desc_tip' => __( 'If "From Admin Funds" is selected, admin has to first add funds to their account. If "From Virtual Funds" is selected, admin doesn\'t need to add funds to their account.', 'credits-for-woocommerce' ),
			),
			array(
				'name'    => __( 'Orders placed through Credits Payment Gateway will go to', 'credits-for-woocommerce' ),
				'id'      => $this->prefix . 'credits_gateway_successful_order_status',
				'type'    => 'select',
				'options' => _wc_cs_get_order_statuses_for_credits(),
				'default' => 'wc-processing',
			),
			array(
				'name'     => __( 'Minimum Cart Total to Use Credits Payment Gateway', 'credits-for-woocommerce' ),
				'id'       => $this->prefix . 'min_cart_total_to_use_credits',
				'type'     => 'text',
				'default'  => '',
				'desc_tip' => __( 'If the user\'s cart total is less than the amount set in this option, the user won\'t be able to use their credits to place the order.', 'credits-for-woocommerce' ),
			),
			array(
				'name'     => __( 'Custom CSS', 'credits-for-woocommerce' ),
				'id'       => $this->prefix . 'inline_style',
				'type'     => 'textarea',
				'default'  => '',
				'desc'     => '',
				'desc_tip' => true,
			),
			array( 'type' => 'sectionend', 'id' => $this->prefix . 'advanced_settings' ),
				) ) ;
	}

}

return new WC_CS_Settings_Advanced() ;
