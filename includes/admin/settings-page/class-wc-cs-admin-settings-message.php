<?php

/**
 * Messages and Localization Tab.
 * 
 * @class WC_CS_Settings_Messages
 * @package Class
 */
class WC_CS_Settings_Messages extends WC_CS_Abstract_Settings {

	/**
	 * WC_CS_Settings_Messages constructor.
	 */
	public function __construct() {

		$this->id       = 'messages' ;
		$this->label    = __( 'Messages and Localization', 'credits-for-woocommerce' ) ;
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
				'name' => __( 'Credits Dashboard Localization', 'credits-for-woocommerce' ),
				'type' => 'title',
				'id'   => $this->prefix . 'credits_dashboard_localization_settings'
			),
			array(
				'name'    => __( 'Approved Credits Label', 'credits-for-woocommerce' ),
				'id'      => $this->prefix . 'approved_credits_label',
				'type'    => 'text',
				'default' => __( 'Approved Credits', 'credits-for-woocommerce' ),
			),
			array(
				'name'    => __( 'Available Credits Label', 'credits-for-woocommerce' ),
				'id'      => $this->prefix . 'available_credits_label',
				'type'    => 'text',
				'default' => __( 'Available Credits', 'credits-for-woocommerce' ),
			),
			array(
				'name'    => __( 'Total Outstanding Label', 'credits-for-woocommerce' ),
				'id'      => $this->prefix . 'total_outstanding_amount_label',
				'type'    => 'text',
				'default' => __( 'Total Outstanding', 'credits-for-woocommerce' ),
			),
			array(
				'name'    => __( 'Last Bill Amount Label', 'credits-for-woocommerce' ),
				'id'      => $this->prefix . 'last_bill_amount_label',
				'type'    => 'text',
				'default' => __( 'Last Bill Amount', 'credits-for-woocommerce' ),
			),
			array(
				'name'    => __( 'Last Bill Date Label', 'credits-for-woocommerce' ),
				'id'      => $this->prefix . 'last_bill_date_label',
				'type'    => 'text',
				'default' => __( 'Last Bill Date', 'credits-for-woocommerce' ),
			),
			array(
				'name'    => __( 'Due Date Label', 'credits-for-woocommerce' ),
				'id'      => $this->prefix . 'due_date_label',
				'type'    => 'text',
				'default' => __( 'Due Date Label', 'credits-for-woocommerce' ),
			),
			array(
				'name'    => __( 'Last Payment Date Label', 'credits-for-woocommerce' ),
				'id'      => $this->prefix . 'last_payment_date_label',
				'type'    => 'text',
				'default' => __( 'Last Payment Date', 'credits-for-woocommerce' ),
			),
			array(
				'name'    => __( 'Next Billing Date Label', 'credits-for-woocommerce' ),
				'id'      => $this->prefix . 'next_billing_date_label',
				'type'    => 'text',
				'default' => __( 'Next Billing Date', 'credits-for-woocommerce' ),
			),
			array(
				'name'    => __( 'Make Payment Label', 'credits-for-woocommerce' ),
				'id'      => $this->prefix . 'make_payment_label',
				'type'    => 'text',
				'default' => __( 'Make Payment', 'credits-for-woocommerce' ),
			),
			array(
				'name'    => __( 'View Unbilled Transactions Label', 'credits-for-woocommerce' ),
				'id'      => $this->prefix . 'view_unbilled_transactions_label',
				'type'    => 'text',
				'default' => __( 'View Unbilled Transactions', 'credits-for-woocommerce' ),
			),
			array(
				'name'    => __( 'View Statements Label', 'credits-for-woocommerce' ),
				'id'      => $this->prefix . 'view_statements_label',
				'type'    => 'text',
				'default' => __( 'View Statements ', 'credits-for-woocommerce' ),
			),
			array(
				'name'    => __( 'Select Period Label', 'credits-for-woocommerce' ),
				'id'      => $this->prefix . 'select_period_label',
				'type'    => 'text',
				'default' => __( 'Select Period', 'credits-for-woocommerce' ),
			),
			array(
				'name'    => __( 'View Statements Link Label', 'credits-for-woocommerce' ),
				'id'      => $this->prefix . 'view_statements_link_label',
				'type'    => 'text',
				'default' => __( 'View Statement', 'credits-for-woocommerce' ),
			),
			array(
				'name'    => __( 'Statement Date Label', 'credits-for-woocommerce' ),
				'id'      => $this->prefix . 'statement_date_label',
				'type'    => 'text',
				'default' => __( 'Statement Date', 'credits-for-woocommerce' ),
			),
			array(
				'name'    => __( 'Due Date Label', 'credits-for-woocommerce' ),
				'id'      => $this->prefix . 'due_date_label',
				'type'    => 'text',
				'default' => __( 'Due Date', 'credits-for-woocommerce' ),
			),
			array(
				'name'    => __( 'Payable Amount Label', 'credits-for-woocommerce' ),
				'id'      => $this->prefix . 'payable_amount_label',
				'type'    => 'text',
				'default' => __( 'Payable Amount', 'credits-for-woocommerce' ),
			),
			array(
				'name'    => __( 'Pay Label', 'credits-for-woocommerce' ),
				'id'      => $this->prefix . 'pay_label',
				'type'    => 'text',
				'default' => __( 'Pay', 'credits-for-woocommerce' ),
			),
			array( 'type' => 'sectionend', 'id' => $this->prefix . 'credits_dashboard_localization_settings' ),
			array(
				'name' => __( 'Credits Dashboard Messages', 'credits-for-woocommerce' ),
				'type' => 'title',
				'id'   => $this->prefix . 'credits_dashboard_messages_settings'
			),
			array(
				'name'    => __( 'Credit Application Submitted Acknowledgement', 'credits-for-woocommerce' ),
				'id'      => $this->prefix . 'credit_app_submitted_message',
				'type'    => 'textarea',
				'default' => __( 'Your Credit application has been submitted. It will take a few days for the Site Admin to process your Request. Please check back after a few days.', 'credits-for-woocommerce' ),
			),
			array(
				'name'    => __( 'Credit Application On-Hold Message', 'credits-for-woocommerce' ),
				'id'      => $this->prefix . 'credit_app_onhold_message',
				'type'    => 'textarea',
				'default' => __( 'Your Credit Application is currently being reviewed.', 'credits-for-woocommerce' ),
			),
			array(
				'name'    => __( 'Credit Application Rejected Message', 'credits-for-woocommerce' ),
				'id'      => $this->prefix . 'credit_app_rejected_message',
				'type'    => 'textarea',
				'default' => __( 'Your Credit Application has been Rejected.', 'credits-for-woocommerce' ),
			),
			array(
				'name'    => __( 'Admin Funds Addition Notice in Cart and Checkout Page', 'credits-for-woocommerce' ),
				'id'      => $this->prefix . 'complete_funds_addition_message',
				'type'    => 'textarea',
				'default' => __( 'Complete the Payment to add Funds to Admin Account.', 'credits-for-woocommerce' ),
			),
			array(
				'name'    => __( 'Credit Line Availibility Notice After Receiving Credits', 'credits-for-woocommerce' ),
				'id'      => $this->prefix . 'after_receiving_credits_notice',
				'type'    => 'textarea',
				'default' => __( 'Use the Available Credit Line to make purchases and Pay Later.', 'credits-for-woocommerce' ),
			),
			array(
				'name'    => __( 'Credit Line Availibility Notice Before Receiving Credits', 'credits-for-woocommerce' ),
				'id'      => $this->prefix . 'before_receiving_credits_notice',
				'type'    => 'textarea',
				/* translators: 1: application form page url */
				'default' => sprintf( __( 'You can purchase via a credit line and repay the used credits later. Please submit an <a href="%s">application</a> to get started.', 'credits-for-woocommerce' ), _wc_cs()->dashboard->get_current_endpoint_url() ),
			),
			array(
				'name'    => __( 'Credits Application/Dashboard Page Restricted Message', 'credits-for-woocommerce' ),
				'id'      => $this->prefix . 'invalid_user_dashboard_access_message',
				'type'    => 'textarea',
				'default' => __( 'Sorry, you are not allowed to access this page.', 'credits-for-woocommerce' ),
			),
			array(
				'name'    => __( 'Credit Repayment Notice in Cart and Checkout Page', 'credits-for-woocommerce' ),
				'id'      => $this->prefix . 'complete_repayment_message',
				'type'    => 'textarea',
				'default' => __( 'Complete the Repayment for your Credits.', 'credits-for-woocommerce' ),
			),
			array(
				'name'    => __( 'Add Funds Restricted Error Message', 'credits-for-woocommerce' ),
				'id'      => $this->prefix . 'cannot_add_funds_message',
				'type'    => 'textarea',
				'default' => __( 'Sorry, you are not allowed to add funds to Admin\'s account.', 'credits-for-woocommerce' ),
			),
			array(
				'name'    => __( 'Add Funds Product in Cart Error Message', 'credits-for-woocommerce' ),
				'id'      => $this->prefix . 'funds_addition_in_cart_message',
				'type'    => 'textarea',
				'default' => __( 'You have already prepared to add funds in the cart. Either complete your payment to add funds or clear the cart and try again !!.', 'credits-for-woocommerce' ),
			),
			array(
				'name'    => __( 'Previous Cart Removal while adding Funds Addition Product to Cart Error Message', 'credits-for-woocommerce' ),
				'id'      => $this->prefix . 'previous_cart_removed_while_adding_funds_message',
				'type'    => 'textarea',
				'default' => __( 'You cannot add funds when other products are in Cart hence your previous cart is removed.', 'credits-for-woocommerce' ),
			),
			array(
				'name'    => __( 'Repayment Error Message', 'credits-for-woocommerce' ),
				'id'      => $this->prefix . 'cannot_make_repayment_message',
				'type'    => 'textarea',
				'default' => __( 'Sorry you are not allowed to make repayment.', 'credits-for-woocommerce' ),
			),
			array(
				'name'    => __( 'Repayment Product in Cart Error Message', 'credits-for-woocommerce' ),
				'id'      => $this->prefix . 'repayment_in_cart_message',
				'type'    => 'textarea',
				'default' => __( 'You have already prepared to make repayment in the cart. Either complete your payment to make repayment or clear the cart and try again !!', 'credits-for-woocommerce' ),
			),
			array(
				'name'    => __( 'Add Funds Error Message in Form Handler', 'credits-for-woocommerce' ),
				'id'      => $this->prefix . 'while_preparing_to_add_funds_message',
				'type'    => 'textarea',
				'default' => __( 'Something went wrong while preparing to add funds.', 'credits-for-woocommerce' ),
			),
			array(
				'name'    => __( 'Repayment Error Message in Form Handler', 'credits-for-woocommerce' ),
				'id'      => $this->prefix . 'while_preparing_to_repay_message',
				'type'    => 'textarea',
				'default' => __( 'Something went wrong while preparing to make repayment.', 'credits-for-woocommerce' ),
			),
			array(
				'name'    => __( 'Invalid Amount Error Message for Add Funds', 'credits-for-woocommerce' ),
				'id'      => $this->prefix . 'invalid_amount_to_add_funds_message',
				'type'    => 'textarea',
				'default' => __( 'Invalid Amount', 'credits-for-woocommerce' ),
			),
			array(
				'name'    => __( 'Funds Addition Coupon Usage Error Message', 'credits-for-woocommerce' ),
				'id'      => $this->prefix . 'funds_addition_coupon_usage_message',
				'type'    => 'textarea',
				'default' => __( 'Coupon is not applicable for funds addition.', 'credits-for-woocommerce' ),
			),
			array(
				'name'    => __( 'Funds Addition Amount Message', 'credits-for-woocommerce' ),
				'id'      => $this->prefix . 'funds_addition_amount_message',
				'type'    => 'textarea',
				'default' => __( '[funds_addition_amount] will be added to Admin\'s account.', 'credits-for-woocommerce' ),
			),
			array(
				'name'    => __( 'Funds Addition Range Error Message', 'credits-for-woocommerce' ),
				'id'      => $this->prefix . 'funds_addition_range_message',
				'type'    => 'textarea',
				'default' => __( 'Funds Addition Amount should be between [min_amnt_to_add_funds] and [max_amnt_to_add_funds]', 'credits-for-woocommerce' ),
			),
			array(
				'name'    => __( 'Minimum Amount for Funds Addition Message', 'credits-for-woocommerce' ),
				'id'      => $this->prefix . 'min_amount_for_funds_addition_message',
				'type'    => 'textarea',
				'default' => __( 'Funds Addition Minimum Amount should be more than [min_amnt_to_add_funds]', 'credits-for-woocommerce' ),
			),
			array(
				'name'    => __( 'Maximum Amount for Funds Addition Message', 'credits-for-woocommerce' ),
				'id'      => $this->prefix . 'max_amount_for_funds_addition_message',
				'type'    => 'textarea',
				'default' => __( 'Funds Addition Maximum Amount should be less than [max_amnt_to_add_funds]', 'credits-for-woocommerce' ),
			),
			array( 'type' => 'sectionend', 'id' => $this->prefix . 'credits_dashboard_messages_settings' ),
				) ) ;
	}

}

return new WC_CS_Settings_Messages() ;
