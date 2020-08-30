<?php

defined( 'ABSPATH' ) || exit ;

/**
 * Handle Credits for Woocommerce Ajax Event.
 * 
 * @class WC_CS_Ajax
 * @package Class
 */
class WC_CS_Ajax {

	/**
	 * Init WC_CS_Ajax.
	 */
	public static function init() {
		//Get Ajax Events.
		$prefix      = WC_CS_PREFIX ;
		$ajax_events = array(
			'create_virtual_product'     => false,
			'get_repayment_day_of_month' => false,
			'check_site_activity'        => false,
			'view_statement'             => false,
			'save_before_approval'       => false,
			'save_after_approval'        => false,
			'edit_rule'                  => false,
			'save_rule'                  => false,
			'remove_rule'                => false
				) ;

		foreach ( $ajax_events as $ajax_event => $nopriv ) {
			add_action( "wp_ajax_{$prefix}{$ajax_event}", __CLASS__ . "::{$ajax_event}" ) ;

			if ( $nopriv ) {
				add_action( "wp_ajax_nopriv_{$prefix}{$ajax_event}", __CLASS__ . "::{$ajax_event}" ) ;
			}
		}
	}

	/**
	 * Create the virtual product
	 */
	public static function create_virtual_product() {
		check_ajax_referer( 'wc-cs-create-virtual-product', 'security' ) ;

		try {
			if ( ! isset( $_POST[ 'product_title' ] ) ) {
				throw new Exception( __( 'Invalid response', 'credits-for-woocommerce' ) ) ;
			}

			if ( empty( $_POST[ 'product_title' ] ) ) {
				throw new Exception( __( 'Product title should not be empty.', 'credits-for-woocommerce' ) ) ;
			}

			$product = new WC_Product_Simple() ;
			$errors  = $product->set_props( array(
				'name'               => sanitize_title( wp_unslash( $_POST[ 'product_title' ] ) ),
				'status'             => 'publish',
				'virtual'            => true,
				'downloadable'       => false,
				'catalog_visibility' => 'hidden',
				'regular_price'      => '0',
				'price'              => '0',
				'sale_price'         => '',
				'reviews_allowed'    => false,
				'featured'           => false,
				'sold_individually'  => true,
				'manage_stock'       => false,
				'stock_quantity'     => null,
				'stock_status'       => 'instock',
				'backorders'         => 'no',
				'total_sales'        => '0',
					) ) ;

			if ( is_wp_error( $errors ) ) {
				throw new Exception( $errors->get_error_message() ) ;
			}

			$product->save() ;

			wp_send_json_success( array(
				'product_id'   => $product->get_id(),
				'product_name' => wp_kses_post( $product->get_formatted_name() ),
			) ) ;
		} catch ( Exception $e ) {
			wp_send_json_error( array( 'error' => esc_html( $e->getMessage() ) ) ) ;
		}
	}

	/**
	 * Get the repayment day of month
	 */
	public static function get_repayment_day_of_month() {
		check_ajax_referer( 'wc-cs-get-repayment-day-of-month', 'security' ) ;

		try {
			if ( isset( $_POST[ 'credits_id' ] ) ) {
				if ( ! isset( $_POST[ 'data' ] ) || ! isset( $_POST[ 'template' ] ) ) {
					throw new Exception( __( 'Invalid response', 'credits-for-woocommerce' ) ) ;
				}

				$credits = _wc_cs_get_credits( absint( wp_unslash( $_POST[ 'credits_id' ] ) ) ) ;

				if ( ! $credits ) {
					throw new Exception( __( 'Invalid credits', 'credits-for-woocommerce' ) ) ;
				}

				$data                    = wp_parse_args( sanitize_text_field( wp_unslash( $_POST[ 'data' ] ) ) ) ;
				$template                = sanitize_title( wp_unslash( $_POST[ 'template' ] ) ) ;
				$selected_billing_day    = absint( get_option( WC_CS_PREFIX . 'get_billing_day_of_month' ) ) ;
				$selected_due_day        = absint( get_option( WC_CS_PREFIX . 'get_due_day_of_month' ) ) ;
				$repayment_month         = get_option( WC_CS_PREFIX . 'get_repayment_month' ) ;
				$use_global_due_date     = isset( $data[ 'use_global_due_date' ] ) ? $data[ 'use_global_due_date' ] : '' ;
				$use_global_billing_date = isset( $data[ 'use_global_billing_date' ] ) ? $data[ 'use_global_billing_date' ] : '' ;
				$modify_due_date         = 'after-approval' === $template ? ( isset( $data[ 'modify_due_date' ] ) ? $data[ 'modify_due_date' ] : '' ) : 'yes' ;
				$modify_billing_date     = 'after-approval' === $template ? ( isset( $data[ 'modify_billing_date' ] ) ? $data[ 'modify_billing_date' ] : '' ) : 'yes' ;

				if ( 'yes' === $modify_billing_date && 'yes' !== $use_global_billing_date && ! empty( $data[ 'billing_day_of_month' ] ) ) {
					$selected_billing_day = absint( $data[ 'billing_day_of_month' ] ) ;
				}

				if ( 'yes' === $modify_due_date && 'yes' !== $use_global_due_date && ! empty( $data[ 'due_day_of_month' ] ) ) {
					$repayment_month  = $data[ 'get_repayment_month' ] ;
					$selected_due_day = absint( $data[ 'due_day_of_month' ] ) ;
				}

				$due_days_excluded     = array( $selected_billing_day ) ;
				$billing_days_excluded = array( $selected_due_day ) ;

				if ( 'next-month' === $repayment_month ) {
					$due_start_day_in_month = 1 ;
					$due_days_in_month      = $selected_billing_day - 1 ;
				} else {
					$due_start_day_in_month = 1 + $selected_billing_day ;
					$due_days_in_month      = 28 ;
				}

				ob_start() ;
				if ( 'before-approval' === $template ) {
					$credits->set_status( $data[ 'request_status' ] ) ;
					$credits->set_approved_credits( $data[ 'new_credits_limit' ] ) ;

					include 'admin/meta-boxes/views/html-before-approval.php' ;
				} else {
					include 'admin/meta-boxes/views/html-after-approval.php' ;
				}
				$html = ob_get_clean() ;
			} else {
				$selected_billing_day = ! empty( $_POST[ 'billing_day' ] ) ? absint( wp_unslash( $_POST[ 'billing_day' ] ) ) : 1 ;
				$repayment_month      = ! empty( $_POST[ 'repayment_month' ] ) ? wc_clean( wp_unslash( $_POST[ 'repayment_month' ] ) ) : 'this-month' ;
				$selected_due_day     = '' ;
				$due_days_excluded    = array( $selected_billing_day ) ;

				if ( 'next-month' === $repayment_month ) {
					$due_start_day_in_month = 1 ;
					$due_days_in_month      = $selected_billing_day - 1 ;
				} else {
					$due_start_day_in_month = 1 + $selected_billing_day ;
					$due_days_in_month      = 28 ;
				}

				ob_start() ;
				include 'admin/settings-page/views/html-repayment-date.php' ;
				$html = ob_get_clean() ;
			}

			wp_send_json_success( array(
				'html' => $html,
			) ) ;
		} catch ( Exception $e ) {
			wp_send_json_error( array( 'error' => esc_html( $e->getMessage() ) ) ) ;
		}
	}

	/**
	 * Check the user's site activity
	 */
	public static function check_site_activity() {
		check_ajax_referer( 'wc-cs-check-site-activity', 'security' ) ;

		try {
			if ( ! isset( $_POST[ 'credits_id' ] ) ) {
				throw new Exception( __( 'Invalid credits', 'credits-for-woocommerce' ) ) ;
			}

			$credits = _wc_cs_get_credits( absint( wp_unslash( $_POST[ 'credits_id' ] ) ) ) ;

			if ( ! $credits ) {
				throw new Exception( __( 'Invalid credits', 'credits-for-woocommerce' ) ) ;
			}

			$user = get_user_by( 'ID', $credits->get_user_id() ) ;
			$credits->read_user_history() ;

			ob_start() ;
			include 'admin/meta-boxes/views/html-site-activity.php' ;
			$html = ob_get_clean() ;

			wp_send_json_success( array(
				'html' => $html,
			) ) ;
		} catch ( Exception $e ) {
			wp_send_json_error( array( 'error' => esc_html( $e->getMessage() ) ) ) ;
		}
	}

	/**
	 * To view the user's statement
	 */
	public static function view_statement() {
		check_ajax_referer( 'wc-cs-view-statement', 'security' ) ;

		try {
			if ( ! isset( $_POST[ 'credits_id' ] ) || ! isset( $_POST[ 'data' ] ) || ! isset( $_POST[ 'is_admin' ] ) ) {
				throw new Exception( __( 'Invalid response', 'credits-for-woocommerce' ) ) ;
			}

			$credits = _wc_cs_get_credits( absint( wp_unslash( $_POST[ 'credits_id' ] ) ) ) ;

			if ( ! $credits ) {
				throw new Exception( __( 'Invalid credits', 'credits-for-woocommerce' ) ) ;
			}

			$data = wp_parse_args( sanitize_text_field( wp_unslash( $_POST[ 'data' ] ) ) ) ;

			if ( empty( $data[ 'selected_month' ] ) || empty( $data[ 'selected_year' ] ) ) {
				throw new Exception( __( 'Invalid period selected', 'credits-for-woocommerce' ) ) ;
			}

			$bill_statement = $credits->get_bill_statement_by_date( trim( $data[ 'selected_year' ] . '-' . $data[ 'selected_month' ] . '-01' ) ) ;

			if ( ! $bill_statement ) {
				throw new Exception( __( 'No statements found for the selected period.', 'credits-for-woocommerce' ) ) ;
			}

			if ( 'yes' === sanitize_key( $_POST[ 'is_admin' ] ) ) {
				$redirectUrl = add_query_arg( array( 'action' => 'view-statement', 'statement-key' => $bill_statement->get_hash(), WC_CS_PREFIX . 'nonce' => wp_create_nonce( 'wc-cs-view-statement' ) ), admin_url( "post.php?post={$credits->get_id()}&action=edit" ) ) ;
			} else {
				$redirectUrl = add_query_arg( array( 'view-statement' => $bill_statement->get_hash(), WC_CS_PREFIX . 'nonce' => wp_create_nonce( 'wc-cs-view-statement' ) ), _wc_cs()->dashboard->get_current_endpoint_url() ) ;
			}

			wp_send_json_success( array(
				'redirect' => $redirectUrl,
			) ) ;
		} catch ( Exception $e ) {
			wp_send_json_error( array( 'error' => esc_html( $e->getMessage() ) ) ) ;
		}
	}

	/**
	 * Save before credits app approved to the user
	 */
	public static function save_before_approval() {
		check_ajax_referer( 'wc-cs-save-before-approval', 'security' ) ;

		try {
			if ( ! isset( $_POST[ 'credits_id' ] ) || ! isset( $_POST[ 'data' ] ) ) {
				throw new Exception( __( 'Invalid response', 'credits-for-woocommerce' ) ) ;
			}

			$credits = _wc_cs_get_credits( absint( wp_unslash( $_POST[ 'credits_id' ] ) ) ) ;

			if ( ! $credits ) {
				throw new Exception( __( 'Invalid credits', 'credits-for-woocommerce' ) ) ;
			}

			$data = wp_parse_args( sanitize_text_field( wp_unslash( $_POST[ 'data' ] ) ) ) ;

			if ( WC_CS_PREFIX . 'active' === $data[ 'request_status' ] ) {
				if ( ! is_numeric( $data[ 'new_credits_limit' ] ) ) {
					throw new Exception( __( 'Invalid credits', 'credits-for-woocommerce' ) ) ;
				}

				$approved_limit = wc_format_decimal( $data[ 'new_credits_limit' ], 2 ) ;

				if ( $approved_limit <= 0 ) {
					throw new Exception( __( 'Credit Limit must be greater than 0 if you want to update the status to Active.', 'credits-for-woocommerce' ) ) ;
				}

				if ( _wc_cs()->funding_via_real_money() && $approved_limit > WC_CS_Admin_Funds::get_available_funds( 'edit' ) ) {
					/* translators: 1: available funds */
					throw new Exception( sprintf( __( 'Credit limit should not be greater than %1$s.', 'credits-for-woocommerce' ), WC_CS_Admin_Funds::get_available_funds( 'edit' ) ) ) ;
				}

				$billing_day     = get_option( WC_CS_PREFIX . 'get_billing_day_of_month' ) ;
				$due_day         = get_option( WC_CS_PREFIX . 'get_due_day_of_month' ) ;
				$due_duration_by = get_option( WC_CS_PREFIX . 'get_repayment_month' ) ;

				if ( 'yes' !== $data[ 'use_global_billing_date' ] ) {
					$billing_day = $data[ 'billing_day_of_month' ] ;
				}

				if ( 'yes' !== $data[ 'use_global_due_date' ] ) {
					if ( empty( $data[ 'due_day_of_month' ] ) ) {
						throw new Exception( __( 'Due date is invalid.', 'credits-for-woocommerce' ) ) ;
					}

					$due_day         = $data[ 'due_day_of_month' ] ;
					$due_duration_by = $data[ 'get_repayment_month' ] ;
				}

				$credits->set_props( array(
					'type'              => isset( $data[ 'eligible_limit' ] ) && $data[ 'eligible_limit' ] === $data[ 'new_credits_limit' ] ? 'auto' : 'manual',
					'approved_date'     => _wc_cs_get_time( 'timestamp' ),
					'approved_credits'  => $approved_limit,
					'available_credits' => $approved_limit,
					'billing_day'       => $billing_day,
					'due_day'           => $due_day,
					'due_duration_by'   => $due_duration_by,
					'next_bill_date'    => _wc_cs_calculate_billing_date( $data[ 'billing_day_of_month' ], true ),
				) ) ;

				if ( _wc_cs()->funding_via_real_money() ) {
					WC_CS_Admin_Funds::debit( $approved_limit ) ;
					WC_CS_Admin_Funds::create_txn( array(
						'activity'   => __( 'Credit Limit Approved', 'credits-for-woocommerce' ),
						'debited'    => $approved_limit,
						'type'       => 'credits-approved',
						'user_email' => $credits->get_user_email()
					) ) ;
				} else {
					WC_CS_Virtual_Funds::create_txn( array(
						'activity'   => __( 'Credit Limit Approved', 'credits-for-woocommerce' ),
						'credited'   => $approved_limit,
						'balance'    => $credits->get_available_credits( 'edit' ),
						'type'       => 'credits-approved',
						'user_email' => $credits->get_user_email()
					) ) ;
				}
			}

			$credits->maybe_apply_lt_2pt0_compatibility() ;
			$credits->set_status( $data[ 'request_status' ] ) ;
			$credits->save() ;

			if ( $credits->has_status( 'active' ) ) {
				if ( ! _wc_cs_job_exists_in_queue( 'credits', 'create_bill_statement', $credits->get_next_bill_date( 'edit' ), $credits->get_id() ) ) {
					_wc_cs_push_job_to_queue( 'credits', 'create_bill_statement', $credits->get_next_bill_date( 'edit' ), $credits->get_id() ) ;
				}
			}

			//populate data.
			extract( WC_CS_Meta_Box_Before_Approval::populate_data() ) ;

			ob_start() ;
			include 'admin/meta-boxes/views/html-before-approval.php' ;
			$html = ob_get_clean() ;

			wp_send_json_success( array(
				'html'    => $html,
				'refresh' => $credits->has_status( 'active' ) ? true : false
			) ) ;
		} catch ( Exception $e ) {
			wp_send_json_error( array( 'error' => esc_html( $e->getMessage() ) ) ) ;
		}
	}

	/**
	 * Save after credits app approved to the user
	 */
	public static function save_after_approval() {
		check_ajax_referer( 'wc-cs-save-after-approval', 'security' ) ;

		try {
			if ( ! isset( $_POST[ 'credits_id' ] ) || ! isset( $_POST[ 'data' ] ) ) {
				throw new Exception( __( 'Invalid response', 'credits-for-woocommerce' ) ) ;
			}

			$credits = _wc_cs_get_credits( absint( wp_unslash( $_POST[ 'credits_id' ] ) ) ) ;

			if ( ! $credits ) {
				throw new Exception( __( 'Invalid credits', 'credits-for-woocommerce' ) ) ;
			}

			$data = wp_parse_args( sanitize_text_field( wp_unslash( $_POST[ 'data' ] ) ) ) ;

			if ( '' === $credits->get_next_bill_date( 'edit' ) ) {
				$credits->set_billing_day( get_option( WC_CS_PREFIX . 'get_billing_day_of_month' ) ) ;
				$credits->set_due_day( get_option( WC_CS_PREFIX . 'get_due_day_of_month' ) ) ;
				$credits->set_due_duration_by( get_option( WC_CS_PREFIX . 'get_repayment_month' ) ) ;
				$credits->set_next_bill_date( _wc_cs_calculate_billing_date( $credits->get_billing_day( 'edit' ) ) ) ;
			}

			if ( isset( $data[ 'modify_credits_limit' ] ) && 'yes' === $data[ 'modify_credits_limit' ] ) {
				if ( ! is_numeric( $data[ 'new_credits_limit' ] ) ) {
					throw new Exception( __( 'Invalid credits', 'credits-for-woocommerce' ) ) ;
				}

				$new_limit_raw = wc_format_decimal( $data[ 'new_credits_limit' ], 2 ) ;

				if ( $new_limit_raw <= 0 ) {
					throw new Exception( __( 'Credit limit should be greater than 0.', 'credits-for-woocommerce' ) ) ;
				}

				if ( _wc_cs()->funding_via_real_money() ) {
					$new_limit = $new_limit_raw - $credits->get_approved_credits( 'edit' ) ;

					if ( $new_limit > WC_CS_Admin_Funds::get_available_funds( 'edit' ) ) {
						/* translators: 1: Available funds */
						throw new Exception( sprintf( __( 'Credit limit should not be greater than %1$s.', 'credits-for-woocommerce' ), WC_CS_Admin_Funds::get_available_funds( 'edit' ) ) ) ;
					}
				}

				$credits->set_type( 'manual' ) ;
				$credits->update_credit_limit( $new_limit_raw ) ;
			}

			if ( isset( $data[ 'modify_billing_date' ] ) && 'yes' === $data[ 'modify_billing_date' ] ) {
				if ( ! empty( $data[ 'use_global_billing_date' ] ) && 'yes' === $data[ 'use_global_billing_date' ] ) {
					$credits->set_billing_day( get_option( WC_CS_PREFIX . 'get_billing_day_of_month' ) ) ;
				} else {
					$credits->set_billing_day( $data[ 'billing_day_of_month' ] ) ;
				}

				$credits->set_next_bill_date( _wc_cs_calculate_billing_date( $credits->get_billing_day( 'edit' ) ) ) ;
			}

			if ( isset( $data[ 'modify_due_date' ] ) && 'yes' === $data[ 'modify_due_date' ] ) {
				if ( ! empty( $data[ 'use_global_due_date' ] ) && 'yes' === $data[ 'use_global_due_date' ] ) {
					$credits->set_due_day( get_option( WC_CS_PREFIX . 'get_due_day_of_month' ) ) ;
					$credits->set_due_duration_by( get_option( WC_CS_PREFIX . 'get_repayment_month' ) ) ;
				} else {
					if ( empty( $data[ 'due_day_of_month' ] ) ) {
						throw new Exception( __( 'Due date is invalid.', 'credits-for-woocommerce' ) ) ;
					}

					$credits->set_due_day( $data[ 'due_day_of_month' ] ) ;
					$credits->set_due_duration_by( $data[ 'get_repayment_month' ] ) ;
				}
			}

			$credits->maybe_apply_lt_2pt0_compatibility() ;
			$credits->set_status( $data[ 'request_status' ] ) ;
			$credits->save() ;

			_wc_cs_cancel_job_from_queue( 'credits', 'create_bill_statement', $credits->get_id() ) ;

			if ( ! _wc_cs_job_exists_in_queue( 'credits', 'create_bill_statement', $credits->get_next_bill_date( 'edit' ), $credits->get_id() ) ) {
				_wc_cs_push_job_to_queue( 'credits', 'create_bill_statement', $credits->get_next_bill_date( 'edit' ), $credits->get_id() ) ;
			}

			//populate data.
			extract( WC_CS_Meta_Box_After_Approval::populate_data() ) ;

			ob_start() ;
			include 'admin/meta-boxes/views/html-after-approval.php' ;
			$html = ob_get_clean() ;

			wp_send_json_success( array(
				'html' => $html,
			) ) ;
		} catch ( Exception $e ) {
			wp_send_json_error( array( 'error' => esc_html( $e->getMessage() ) ) ) ;
		}
	}

	/**
	 * Edit the credit line rule.
	 */
	public static function edit_rule() {
		check_ajax_referer( 'wc-cs-edit-rule', 'security' ) ;

		try {
			if ( ! isset( $_GET[ 'rule_id' ] ) ) {
				throw new Exception( __( 'Invalid rule', 'credits-for-woocommerce' ) ) ;
			}

			$rule_id      = absint( wp_unslash( $_GET[ 'rule_id' ] ) ) ;
			$credit_rules = get_option( WC_CS_PREFIX . 'credit_line_rules', array() ) ;
			$data         = isset( $credit_rules[ $rule_id ] ) ? $credit_rules[ $rule_id ] : array() ;

			if ( ! empty( $data[ 'criteria' ] ) ) {
				ob_start() ;
				foreach ( $data[ 'criteria' ] as $group_id => $group ) {
					include 'admin/settings-page/views/html-add-criteria-options-group.php' ;
				}
				$html = ob_get_clean() ;
			} else {
				$html = '' ;
			}

			wp_send_json_success( array(
				'rule_id'                 => $rule_id,
				'data'                    => $data,
				'criteria_options_groups' => $html
			) ) ;
		} catch ( Exception $e ) {
			wp_send_json_error( array( 'error' => esc_html( $e->getMessage() ) ) ) ;
		}
	}

	/**
	 * Save the credit line rule.
	 */
	public static function save_rule() {
		check_ajax_referer( 'wc-cs-save-rule', 'security' ) ;

		try {
			$posted   = $_POST ;
			$raw_data = isset( $posted[ 'data' ] ) ? wp_parse_args( wp_unslash( $posted[ 'data' ] ) ) : array() ;
			$new_rule = wp_parse_args( $raw_data, array(
				'name'         => '',
				'priority'     => '',
				'credit_limit' => 0,
				'no_of_users'  => 0,
				'criteria'     => array(),
					) ) ;

			$credit_rules = get_option( WC_CS_PREFIX . 'credit_line_rules', array() ) ;

			unset( $new_rule[ 'rule_id' ] ) ;
			if ( ! empty( $new_rule[ 'name' ] ) ) {
				if ( isset( $credit_rules[ $raw_data[ 'rule_id' ] ] ) ) {
					$credit_rules[ $raw_data[ 'rule_id' ] ] = $new_rule ;
				} else {
					$credit_rules[] = $new_rule ;
				}

				$credit_rules = array_values( array_filter( $credit_rules ) ) ;
				update_option( WC_CS_PREFIX . 'credit_line_rules', $credit_rules ) ;
			}

			ob_start() ;
			include 'admin/settings-page/views/html-rules.php' ;
			$html = ob_get_clean() ;

			wp_send_json_success( array(
				'html' => $html,
			) ) ;
		} catch ( Exception $e ) {
			wp_send_json_error( array( 'error' => esc_html( $e->getMessage() ) ) ) ;
		}
	}

	/**
	 * Remove the credit line rule.
	 */
	public static function remove_rule() {
		check_ajax_referer( 'wc-cs-remove-rule', 'security' ) ;

		try {
			if ( ! isset( $_POST[ 'rule_id' ] ) ) {
				throw new Exception( __( 'Invalid rule', 'credits-for-woocommerce' ) ) ;
			}

			$rule_id      = absint( wp_unslash( $_POST[ 'rule_id' ] ) ) ;
			$credit_rules = get_option( WC_CS_PREFIX . 'credit_line_rules', array() ) ;

			if ( isset( $credit_rules[ $rule_id ] ) ) {
				unset( $credit_rules[ $rule_id ] ) ;

				$credit_rules = array_values( array_filter( $credit_rules ) ) ;
				update_option( WC_CS_PREFIX . 'credit_line_rules', $credit_rules ) ;
			}

			ob_start() ;
			include 'admin/settings-page/views/html-rules.php' ;
			$html = ob_get_clean() ;

			wp_send_json_success( array(
				'html' => $html,
			) ) ;
		} catch ( Exception $e ) {
			wp_send_json_error( array( 'error' => esc_html( $e->getMessage() ) ) ) ;
		}
	}

}

WC_CS_Ajax::init() ;
