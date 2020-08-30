<?php

/**
 * General Tab.
 * 
 * @class WC_CS_Settings_General
 * @package Class
 */
class WC_CS_Settings_General extends WC_CS_Abstract_Settings {

	/**
	 * WC_CS_Settings_General constructor.
	 */
	public function __construct() {

		$this->id            = 'general' ;
		$this->label         = __( 'General', 'credits-for-woocommerce' ) ;
		$this->custom_fields = array(
			'get_shortcodes_and_its_usage',
			'get_funds_addition_user',
			'create_funds_addition_product',
			'create_repayment_product',
			'choose_funds_addition_product',
			'choose_repayment_product',
			'get_included_users_to_display_credit_form',
			'get_excluded_users_to_display_credit_form',
			'get_billing_settings',
			'get_repayment_settings',
			'get_header_logo',
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

		$settings = array(
			array( 'type' => $this->get_custom_field_type( 'get_shortcodes_and_its_usage' ) ),
			array(
				'name' => __( 'General Settings', 'credits-for-woocommerce' ),
				'type' => 'title',
				'id'   => $this->prefix . 'general_settings'
			),
			array(
				'name'     => __( 'Credit Line Approval Mode', 'credits-for-woocommerce' ),
				'id'       => $this->prefix . 'credit_line_approval_mode',
				'type'     => 'select',
				'options'  => array(
					'auto-approval' => __( 'Auto Approval', 'credits-for-woocommerce' ),
					'app-approval'  => __( 'Application Based Approval', 'credits-for-woocommerce' )
				),
				'default'  => 'app-approval',
				'desc_tip' => __( 'Auto Approval: Users will be automatically assigned a credit limit. Application Based Approval: Users wil have to submit an application to receive a credit limit', 'credits-for-woocommerce' )
			) ) ;

		if ( _wc_cs()->funding_via_real_money() ) {
			$settings = array_merge( $settings, array(
				array( 'type' => $this->get_custom_field_type( 'get_funds_addition_user' ) ),
				array(
					'name'     => __( 'Funds Addition Product Type', 'credits-for-woocommerce' ),
					'id'       => $this->prefix . 'funds_addition_product_type',
					'type'     => 'select',
					'options'  => array(
						'new-product' => __( 'New Product', 'credits-for-woocommerce' ),
						'old-product' => __( 'Old Product', 'credits-for-woocommerce' )
					),
					'default'  => 'new-product',
					'desc_tip' => __( 'Funds addition to admin account should go through WooCommerce product purchase. For that, a product will be needed. This option controls the product selection for that purpose', 'credits-for-woocommerce' ),
				),
				array( 'type' => $this->get_custom_field_type( 'create_funds_addition_product' ) ),
				array( 'type' => $this->get_custom_field_type( 'choose_funds_addition_product' ) ),
				array(
					'name'              => __( 'Min Amount for Funds Addition Per Transaction', 'credits-for-woocommerce' ),
					'id'                => $this->prefix . 'min_funds_to_add_per_txn',
					'type'              => 'number',
					'default'           => '',
					'desc_tip'          => __( 'The funds added to the admin\'s account should not be less than the value specified in this option.', 'credits-for-woocommerce' ),
					'custom_attributes' => array(
						'min'  => '0.01',
						'step' => '0.01',
					),
				),
				array(
					'name'              => __( 'Max Amount for Funds Addition Per Transaction', 'credits-for-woocommerce' ),
					'id'                => $this->prefix . 'max_funds_to_add_per_txn',
					'type'              => 'number',
					'default'           => '',
					'desc_tip'          => __( 'The funds added to the admin\'s account should not be more than the value specified in this option.', 'credits-for-woocommerce' ),
					'custom_attributes' => array(
						'min'  => '0.01',
						'step' => '0.01',
					),
				),
				array(
					'name'              => __( 'Low Funds Threshold', 'credits-for-woocommerce' ),
					'id'                => $this->prefix . 'low_funds_threshold',
					'type'              => 'number',
					'default'           => '',
					'desc_tip'          => __( 'Admin will be notified by email when the available admin funds reaches below the the threshold set in this option. The receivers and the email content can be customized in the email section.', 'credits-for-woocommerce' ),
					'custom_attributes' => array(
						'min'  => '0.01',
						'step' => '0.01',
					),
				),
				array(
					'name'     => __( 'Disable Payment Gateways for Funds Addition', 'credits-for-woocommerce' ),
					'id'       => $this->prefix . 'disabled_payment_gateways_for_funds_addition',
					'type'     => 'multiselect',
					'options'  => _wc_cs_get_valid_payment_gateways(),
					'default'  => array(),
					'desc_tip' => __( 'Select the payment gateways which you want to hide at checkout when funds addition product is in cart', 'credits-for-woocommerce' ),
				) ) ) ;
		}

		$settings = array_merge( $settings, array(
			array( 'type' => 'sectionend', 'id' => $this->prefix . 'general_settings' ),
			array(
				'name' => __( 'User Restrictions', 'credits-for-woocommerce' ),
				'type' => 'title',
				'id'   => $this->prefix . 'user_restrictions'
			),
			array(
				'name'     => __( 'Display Credit Form for', 'credits-for-woocommerce' ),
				'id'       => $this->prefix . 'display_credit_form_for',
				'type'     => 'select',
				'options'  => array(
					'all-users'         => __( 'All Users', 'credits-for-woocommerce' ),
					'include-users'     => __( 'Include Users', 'credits-for-woocommerce' ),
					'exclude-users'     => __( 'Exclude Users', 'credits-for-woocommerce' ),
					'include-userroles' => __( 'Include User Roles', 'credits-for-woocommerce' ),
					'exclude-userroles' => __( 'Exclude User Roles', 'credits-for-woocommerce' ),
				),
				'default'  => 'all-users',
				'desc_tip' => __( 'The form for availaing credit from the site will be displayed only to the users selected in this option.', 'credits-for-woocommerce' ),
			),
			array( 'type' => $this->get_custom_field_type( 'get_included_users_to_display_credit_form' ) ),
			array( 'type' => $this->get_custom_field_type( 'get_excluded_users_to_display_credit_form' ) ),
			array(
				'name'    => __( 'Include Userrole', 'credits-for-woocommerce' ),
				'id'      => $this->prefix . 'get_included_userroles_to_display_credit_form',
				'type'    => 'multiselect',
				'options' => _wc_cs_get_user_roles(),
				'default' => array(),
			),
			array(
				'name'    => __( 'Exclude Userrole', 'credits-for-woocommerce' ),
				'id'      => $this->prefix . 'get_excluded_userroles_to_display_credit_form',
				'type'    => 'multiselect',
				'options' => _wc_cs_get_user_roles(),
				'default' => array(),
			),
			array(
				'name'     => __( 'Site Activity of User', 'credits-for-woocommerce' ),
				'id'       => $this->prefix . 'allow_users_site_activity_to_display_credit_form_with',
				'type'     => 'select',
				'options'  => array(
					'no-restrictions'         => __( 'No Restrictions', 'credits-for-woocommerce' ),
					'min-no-of-orders-placed' => __( 'Min No. of Orders Placed', 'credits-for-woocommerce' ),
					'min-amt-spent-on-site'   => __( 'Min Amount Spent on Site', 'credits-for-woocommerce' ),
				),
				'default'  => 'no-restrictions',
				'desc_tip' => __( 'Credit availing form can be restricted to users based on their previous site activity.', 'credits-for-woocommerce' ),
			),
			array(
				'name'              => __( 'Min No.Of Orders Placed', 'credits-for-woocommerce' ),
				'id'                => $this->prefix . 'allow_users_site_activity_with_min_orders_placed',
				'type'              => 'number',
				'default'           => '',
				'custom_attributes' => array(
					'min' => '0',
				),
			),
			array(
				'name'              => __( 'Min Amount Spent on Site', 'credits-for-woocommerce' ),
				'id'                => $this->prefix . 'allow_users_site_activity_with_min_amt_spent',
				'type'              => 'number',
				'default'           => '',
				'custom_attributes' => array(
					'min' => '0',
				),
			),
			array( 'type' => 'sectionend', 'id' => $this->prefix . 'user_restrictions' ),
			array(
				'name' => __( 'Billing Date Settings', 'credits-for-woocommerce' ),
				'type' => 'title',
				'id'   => $this->prefix . 'billing_date_settings'
			),
			array( 'type' => $this->get_custom_field_type( 'get_billing_settings' ) ),
			array( 'type' => 'sectionend', 'id' => $this->prefix . 'billing_date_settings' ),
			array(
				'name' => __( 'Repayment Date Settings', 'credits-for-woocommerce' ),
				'type' => 'title',
				'id'   => $this->prefix . 'repayment_date_settings'
			),
			array( 'type' => $this->get_custom_field_type( 'get_repayment_settings' ) ),
			array( 'type' => 'sectionend', 'id' => $this->prefix . 'repayment_date_settings' ),
			array(
				'name' => __( 'Fee Settings', 'credits-for-woocommerce' ),
				'type' => 'title',
				'id'   => $this->prefix . 'fee_settings'
			),
			array(
				'name'    => __( 'Charge Interest for Credit Usage', 'credits-for-woocommerce' ),
				'id'      => $this->prefix . 'charge_interest_for_credit_usage',
				'type'    => 'checkbox',
				'default' => 'no',
				'desc'    => __( 'If enabled, interest can be charged for the credits usage. Whenever a user uses their credits to place an order, the interest fee for credits usage will be automatically debited from the user\'s credits. ', 'credits-for-woocommerce' ),
			),
			array(
				'name'    => __( 'Interest Type', 'credits-for-woocommerce' ),
				'id'      => $this->prefix . 'credit_usage_interest_type',
				'type'    => 'select',
				'default' => 'fixed',
				'options' => array(
					'fixed'   => __( 'Fixed', 'credits-for-woocommerce' ),
					'percent' => __( 'Percentage', 'credits-for-woocommerce' ),
				),
			),
			array(
				'name'    => __( 'Interest', 'credits-for-woocommerce' ),
				'id'      => $this->prefix . 'credit_usage_interest_value',
				'type'    => 'text',
				'default' => '',
			),
			array(
				'name'    => __( 'Charge Late Payment Fee', 'credits-for-woocommerce' ),
				'id'      => $this->prefix . 'charge_late_payment_fee',
				'type'    => 'checkbox',
				'default' => 'no',
				'desc'    => __( 'If enabled, a late payment fee can be charged from the users if payment was not made before the due date. The late payment fee will be automatically debited from the user\'s available credits and the bill amount along with late fee has to be paid in the next bill.', 'credits-for-woocommerce' ),
			),
			array(
				'name'    => __( 'Late Fee Type', 'credits-for-woocommerce' ),
				'id'      => $this->prefix . 'late_payment_fee_type',
				'type'    => 'select',
				'default' => 'fixed',
				'options' => array(
					'fixed'   => __( 'Fixed', 'credits-for-woocommerce' ),
					'percent' => __( 'Percentage', 'credits-for-woocommerce' ),
				),
			),
			array(
				'name'    => __( 'Late Fee', 'credits-for-woocommerce' ),
				'id'      => $this->prefix . 'late_payment_fee_value',
				'type'    => 'text',
				'default' => '',
			),
			array( 'type' => 'sectionend', 'id' => $this->prefix . 'fee_settings' ),
			array(
				'name' => __( 'Email Notification Settings', 'credits-for-woocommerce' ),
				'type' => 'title',
				'id'   => $this->prefix . 'email_notify_settings'
			),
			array(
				'name'    => __( 'Send Payment Notification Emails', 'credits-for-woocommerce' ),
				'id'      => $this->prefix . 'remind_payment_due_after',
				'type'    => 'text',
				'default' => '1,2,3',
				'desc'    => __( 'days after bill generated date', 'credits-for-woocommerce' ),
			),
			array( 'type' => 'sectionend', 'id' => $this->prefix . 'email_notify_settings' ),
			array(
				'name' => __( 'Repayment Settings', 'credits-for-woocommerce' ),
				'type' => 'title',
				'id'   => $this->prefix . 'repayment_settings'
			),
			array(
				'name'     => __( 'Repayment Product Type', 'credits-for-woocommerce' ),
				'id'       => $this->prefix . 'repayment_product_type',
				'type'     => 'select',
				'options'  => array(
					'new-product' => __( 'New Product', 'credits-for-woocommerce' ),
					'old-product' => __( 'Old Product', 'credits-for-woocommerce' )
				),
				'default'  => 'new-product',
				'desc_tip' => __( 'Repayment for Generated Bill has to be done through WooCommerce checkout. Hence, a product has to be assigned for repayment. Select whether you want to create a new product or assign a previously created one.', 'credits-for-woocommerce' ),
			),
			array( 'type' => $this->get_custom_field_type( 'create_repayment_product' ) ),
			array( 'type' => $this->get_custom_field_type( 'choose_repayment_product' ) ),
			array(
				'name'     => __( 'Disable Payment Gateways for Repayment', 'credits-for-woocommerce' ),
				'id'       => $this->prefix . 'disabled_payment_gateways_for_repayment',
				'type'     => 'multiselect',
				'options'  => _wc_cs_get_valid_payment_gateways(),
				'default'  => array(),
				'desc_tip' => __( 'Select the payment gateways which you want to hide at checkout when repayment product is in cart', 'credits-for-woocommerce' ),
			),
			array( 'type' => 'sectionend', 'id' => $this->prefix . 'repayment_settings' ),
			array(
				'name' => __( 'Credits Dashboard Settings', 'credits-for-woocommerce' ),
				'type' => 'title',
				'id'   => $this->prefix . 'credits_dashboard_settings'
			),
			array(
				'name'     => __( 'Credits Dashboard Page', 'credits-for-woocommerce' ),
				'id'       => 'woocommerce_credits_dashboard_page_id',
				'type'     => 'single_select_page',
				'class'    => 'wc-enhanced-select-nostd',
				'css'      => 'min-width:300px;',
				'default'  => wc_get_page_id( 'credits_dashboard' ),
				/* translators: 1: credits dashboard shortcode */
				'desc'     => sprintf( __( 'Page contents: [%1$s]', 'credits-for-woocommerce' ), 'credits_dashboard' ),
				'desc_tip' => true,
			),
			array( 'type' => 'sectionend', 'id' => $this->prefix . 'credits_dashboard_settings' ),
			array(
				'name' => __( 'Statement Settings', 'credits-for-woocommerce' ),
				'type' => 'title',
				'id'   => $this->prefix . 'statement_settings'
			),
			array( 'type' => $this->get_custom_field_type( 'get_header_logo' ) ),
			array( 'type' => 'sectionend', 'id' => $this->prefix . 'statement_settings' ),
				) ) ;

		return apply_filters( 'wc_cs_get_' . $this->id . '_settings', $settings ) ;
	}

	/**
	 * Custom type field.
	 */
	public function get_shortcodes_and_its_usage() {
		$shortcodes = array(
			'[credits_dashboard]' => __( 'Shortcode to display the Credits Dashboard.', 'credits-for-woocommerce' ),
				) ;
		?>
		<table class="widefat">
			<thead>
				<tr>
					<th><?php esc_html_e( 'Shortcode', 'credits-for-woocommerce' ) ; ?></th>
					<th><?php esc_html_e( 'Purpose', 'credits-for-woocommerce' ) ; ?></th>
				</tr>
			</thead>
			<tbody>                
				<?php foreach ( $shortcodes as $shortcode => $purpose ) : ?>
					<tr>
						<td><?php echo esc_html( $shortcode ) ; ?></td>
						<td><?php echo esc_html( $purpose ) ; ?></td>
					</tr>
				<?php endforeach ; ?>
			</tbody>
		</table>
		<?php
	}

	/**
	 * Custom type field.
	 */
	public function get_funds_addition_user() {
		?>
		<tr valign="top">
			<th scope="row" class="titledesc">
				<label for="get_funds_addition_user"><?php esc_html_e( 'User to Add Funds', 'credits-for-woocommerce' ) ; ?>
					<span class="woocommerce-help-tip" data-tip="<?php esc_html_e( 'Select the user(usually an admin) who is responsible for adding funds to the admin account. Admin can give funds to users only if there is enough balance in admin account.', 'credits-for-woocommerce' ) ; ?>"></span>
				</label>
			</th>
			<td class="forminp forminp-select">
				<?php
				_wc_cs_search_field( array(
					'class'       => 'wc-customer-search',
					'id'          => $this->prefix . 'get_funds_addition_user',
					'type'        => 'customer',
					'multiple'    => false,
					'placeholder' => __( 'Search for a user&hellip;', 'credits-for-woocommerce' ),
					'options'     => get_option( $this->prefix . 'get_funds_addition_user' )
				) ) ;
				?>
			</td>
		</tr>
		<?php
	}

	/**
	 * Custom type field.
	 */
	public function get_included_users_to_display_credit_form() {
		?>
		<tr valign="top">
			<th scope="row" class="titledesc">
				<label for="get_included_users_to_display_credit_form"><?php esc_html_e( 'Include User(s)', 'credits-for-woocommerce' ) ; ?></label>
			</th>
			<td class="forminp forminp-select">
				<?php
				_wc_cs_search_field( array(
					'class'       => 'wc-customer-search',
					'id'          => $this->prefix . 'get_included_users_to_display_credit_form',
					'type'        => 'customer',
					'multiple'    => true,
					'placeholder' => __( 'Search for a user&hellip;', 'credits-for-woocommerce' ),
					'options'     => get_option( $this->prefix . 'get_included_users_to_display_credit_form' )
				) ) ;
				?>
			</td>
		</tr>
		<?php
	}

	/**
	 * Custom type field.
	 */
	public function get_excluded_users_to_display_credit_form() {
		?>
		<tr valign="top">
			<th scope="row" class="titledesc">
				<label for="get_excluded_users_to_display_credit_form"><?php esc_html_e( 'Exclude User(s)', 'credits-for-woocommerce' ) ; ?></label>
			</th>
			<td class="forminp forminp-select">
				<?php
				_wc_cs_search_field( array(
					'class'       => 'wc-customer-search',
					'id'          => $this->prefix . 'get_excluded_users_to_display_credit_form',
					'type'        => 'customer',
					'multiple'    => true,
					'placeholder' => __( 'Search for a user&hellip;', 'credits-for-woocommerce' ),
					'options'     => get_option( $this->prefix . 'get_excluded_users_to_display_credit_form' )
				) ) ;
				?>
			</td>
		</tr>
		<?php
	}

	/**
	 * Custom type field.
	 */
	public function choose_funds_addition_product() {
		?>
		<tr valign="top">
			<th scope="row" class="titledesc">
				<label for="get_selected_product_to_add_funds"><?php esc_html_e( 'Select Product', 'credits-for-woocommerce' ) ; ?></label>
			</th>
			<td class="forminp forminp-select">
				<?php
				_wc_cs_search_field( array(
					'class'       => 'wc-product-search',
					'id'          => $this->prefix . 'get_selected_product_to_add_funds',
					'type'        => 'product',
					'multiple'    => false,
					'placeholder' => __( 'Search for a product&hellip;', 'credits-for-woocommerce' ),
					'options'     => get_option( $this->prefix . 'get_selected_product_to_add_funds' )
				) ) ;
				?>
			</td>
		</tr>
		<?php
	}

	/**
	 * Custom type field.
	 */
	public function choose_repayment_product() {
		?>
		<tr valign="top">
			<th scope="row" class="titledesc">
				<label for="get_selected_product_for_repayment"><?php esc_html_e( 'Select Product', 'credits-for-woocommerce' ) ; ?></label>
			</th>
			<td class="forminp forminp-select">
				<?php
				_wc_cs_search_field( array(
					'class'       => 'wc-product-search',
					'id'          => $this->prefix . 'get_selected_product_for_repayment',
					'type'        => 'product',
					'multiple'    => false,
					'placeholder' => __( 'Search for a product&hellip;', 'credits-for-woocommerce' ),
					'options'     => get_option( $this->prefix . 'get_selected_product_for_repayment' )
				) ) ;
				?>
			</td>
		</tr>
		<?php
	}

	/**
	 * Custom type field.
	 */
	public function create_funds_addition_product() {
		$slug                = 'to_add_funds' ;
		$field_type_selector = $this->prefix . 'funds_addition_product_type' ;
		$product_title       = __( 'Fund Addition', 'credits-for-woocommerce' ) ;
		echo '<tr valign="top" class="create_funds_addition_product">' ;
		include 'views/html-create-product.php' ;
		echo '</tr>' ;
	}

	/**
	 * Custom type field.
	 */
	public function create_repayment_product() {
		$slug                = 'for_repayment' ;
		$field_type_selector = $this->prefix . 'repayment_product_type' ;
		$product_title       = __( 'Repayment', 'credits-for-woocommerce' ) ;
		echo '<tr valign="top" class="create_repayment_product">' ;
		include 'views/html-create-product.php' ;
		echo '</tr>' ;
	}

	/**
	 * Custom type field.
	 */
	public function get_billing_settings() {
		$selected_billing_day         = absint( get_option( $this->prefix . 'get_billing_day_of_month' ) ) ;
		$selected_threshold_day       = absint( get_option( $this->prefix . 'get_threshold_day_of_month' ) ) ;
		$billing_start_day_in_month   = 1 ;
		$billing_days_in_month        = 27 ;
		$billing_days_excluded        = array() ;
		$threshold_start_day_in_month = 1 ;
		$threshold_days_in_month      = 28 ;
		$threshold_days_excluded      = array() ;
		echo '<table class="form-table billing-date-settings-wrapper"><tbody>' ;
		include 'views/html-billing-date.php' ;
		echo '</tbody></table>' ;
	}

	/**
	 * Custom type field.
	 */
	public function get_repayment_settings() {
		$selected_billing_day = absint( get_option( $this->prefix . 'get_billing_day_of_month' ) ) ;
		$selected_due_day     = absint( get_option( $this->prefix . 'get_due_day_of_month' ) ) ;
		$repayment_month      = get_option( $this->prefix . 'get_repayment_month' ) ;
		$due_days_excluded    = array( $selected_billing_day ) ;

		if ( 'next-month' === $repayment_month ) {
			$due_start_day_in_month = 1 ;
			$due_days_in_month      = $selected_billing_day - 1 ;
		} else {
			$due_start_day_in_month = 1 + $selected_billing_day ;
			$due_days_in_month      = 28 ;
		}
		echo '<table class="form-table repayment-date-settings-wrapper"><tbody>' ;
		include 'views/html-repayment-date.php' ;
		echo '</tbody></table>' ;
	}

	/**
	 * Custom type field.
	 */
	public function get_header_logo() {
		$attachment_id = get_option( $this->prefix . 'get_header_logo_attachment' ) ;
		?>
		<tr valign="top">
			<th scope="row" class="titledesc">
				<label for="get_header_logo_attachment"><?php esc_html_e( 'Statement Header Logo', 'credits-for-woocommerce' ) ; ?>
					<span class="woocommerce-help-tip" data-tip="<?php esc_html_e( 'Selected logo will be displayed in statement header.', 'credits-for-woocommerce' ) ; ?>"></span>
				</label>
			</th>
			<td class="forminp forminp-select">
				<div class="header-logo-wrapper">
					<span class="logo-attachment" style="padding:0px 2px"><?php echo wp_get_attachment_image( $attachment_id, array( 200, 100 ) ) ; ?></span>
					<img class="logo-preview" width="200" height="100" style="display: none;">

					<input type="hidden" name="_wc_cs_get_header_logo_attachment" id="logo_attachment_id" value="<?php echo esc_attr( $attachment_id ) ; ?>">
					<button class="button" id="upload_logo" data-choose="<?php esc_attr_e( 'Choose a Logo', 'credits-for-woocommerce' ) ; ?>" data-add="<?php esc_attr_e( 'Add Logo', 'credits-for-woocommerce' ) ; ?>"><?php esc_html_e( 'Upload Logo', 'credits-for-woocommerce' ) ; ?></button>
					<?php if ( $attachment_id ) : ?>
						<button class="button" id="delete_logo"><?php esc_html_e( 'Delete Logo', 'credits-for-woocommerce' ) ; ?></button>
					<?php endif ; ?>
				</div>
			</td>
		</tr>
		<?php
	}

	/**
	 * Delete the custom options.
	 */
	public function custom_types_delete_options( $posted = null ) {
		delete_option( $this->prefix . 'get_funds_addition_user' ) ;
		delete_option( $this->prefix . 'get_selected_product_to_add_funds' ) ;
		delete_option( $this->prefix . 'get_selected_product_for_repayment' ) ;
		delete_option( $this->prefix . 'get_included_users_to_display_credit_form' ) ;
		delete_option( $this->prefix . 'get_excluded_users_to_display_credit_form' ) ;
		delete_option( $this->prefix . 'get_billing_day_of_month' ) ;
		delete_option( $this->prefix . 'get_due_day_of_month' ) ;
		delete_option( $this->prefix . 'get_repayment_month' ) ;
		delete_option( $this->prefix . 'get_threshold_day_of_month' ) ;
		delete_option( $this->prefix . 'get_header_logo_attachment' ) ;
	}

	/**
	 * Save custom settings.
	 */
	public function custom_types_save( $posted ) {

		$metakeys = array(
			'get_funds_addition_user'                   => 'string',
			'get_selected_product_to_add_funds'         => 'string',
			'get_selected_product_for_repayment'        => 'string',
			'get_included_users_to_display_credit_form' => 'multisearch',
			'get_excluded_users_to_display_credit_form' => 'multisearch',
			'get_billing_day_of_month'                  => 'string',
			'get_due_day_of_month'                      => 'string',
			'get_threshold_day_of_month'                => 'string',
			'get_repayment_month'                       => 'string',
			'get_header_logo_attachment'                => 'img',
				) ;

		foreach ( $metakeys as $key => $type ) {
			if ( ! isset( $posted[ "{$this->prefix }{$key}" ] ) ) {
				continue ;
			}

			$posted_value = wc_clean( $posted[ "{$this->prefix }{$key}" ] ) ;

			if ( 'multisearch' === $type ) {
				if ( ! is_array( $posted_value ) ) {
					$value = array_filter( array_map( 'absint', explode( ',', $posted_value ) ) ) ;
				} else {
					$value = $posted_value ;
				}
			} else {
				$value = $posted_value ;
			}

			update_option( "{$this->prefix }{$key}", wc_clean( $value ) ) ;
		}
	}

	/**
	 * Save the custom options once.
	 */
	public function custom_types_add_options( $posted = null ) {
		$admin = get_user_by( 'email', get_option( 'admin_email' ) ) ;

		add_option( $this->prefix . 'get_funds_addition_user', $admin ? $admin->ID : ''  ) ;
		add_option( $this->prefix . 'get_selected_product_to_add_funds', '' ) ;
		add_option( $this->prefix . 'get_selected_product_for_repayment', '' ) ;
		add_option( $this->prefix . 'get_included_users_to_display_credit_form', array() ) ;
		add_option( $this->prefix . 'get_excluded_users_to_display_credit_form', array() ) ;
		add_option( $this->prefix . 'get_billing_day_of_month', '1' ) ;
		add_option( $this->prefix . 'get_due_day_of_month', '20' ) ;
		add_option( $this->prefix . 'get_repayment_month', 'this-month' ) ;
		add_option( $this->prefix . 'get_threshold_day_of_month', '15' ) ;
	}

}

return new WC_CS_Settings_General() ;
