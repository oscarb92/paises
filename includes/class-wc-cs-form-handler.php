<?php

/**
 * Handle frontend forms.
 */
defined( 'ABSPATH' ) || exit ;

/**
 * WC_CS_Form_Handler class.
 */
class WC_CS_Form_Handler {

	/**
	 * Hook in methods.
	 */
	public static function init() {
		add_action( 'template_redirect', array( __CLASS__, 'guest_redirection' ), 20 ) ;
		add_action( 'wp_loaded', array( __CLASS__, 'add_funds' ), 20 ) ;
		add_action( 'wp_loaded', array( __CLASS__, 'credit_app_submitted' ), 20 ) ;
		add_action( 'wp_loaded', array( __CLASS__, 'do_dashboard_action' ), 20 ) ;
	}

	/**
	 * Force redirect guest to login url if they landing on dashboard.
	 */
	public static function guest_redirection() {
		if ( ! _wc_cs_is_dashboard() ) {
			return ;
		}

		if ( ! is_user_logged_in() ) {
			wp_safe_redirect( wc_get_page_permalink( 'myaccount' ) ) ;
			exit ;
		}
	}

	/**
	 * Add Funds
	 */
	public static function add_funds() {
		if ( ! isset( $_REQUEST[ 'add_funds' ] ) || ! isset( $_REQUEST[ WC_CS_PREFIX . 'nonce' ] ) || ! isset( $_REQUEST[ 'fund_amount' ] ) ) {
			return ;
		}

		if ( ! wp_verify_nonce( sanitize_key( wp_unslash( $_REQUEST[ WC_CS_PREFIX . 'nonce' ] ) ), 'wc-cs-view-add-funds' ) ) {
			return ;
		}

		if ( ! _wc_cs()->funds_addition->current_user_is_eligible() ) {
			wc_add_notice( esc_html( get_option( WC_CS_PREFIX . 'cannot_add_funds_message' ) ), 'error' ) ;
			wp_safe_redirect( wc_get_page_permalink( 'myaccount' ) ) ;
			exit ;
		}

		if ( _wc_cs()->funds_addition->in_progress() ) {
			wc_add_notice( esc_html( get_option( WC_CS_PREFIX . 'funds_addition_in_cart_message' ) ), 'error' ) ;
			wp_safe_redirect( wc_get_checkout_url() ) ;
			exit ;
		}

		try {
			$amount = wc_format_decimal( sanitize_text_field( wp_unslash( $_REQUEST[ 'fund_amount' ] ) ), 2 ) ;

			_wc_cs()->funds_addition->validate_before_add( $amount ) ;

			if ( ! WC()->cart->is_empty() ) {
				WC()->cart->empty_cart() ;
				wc_add_notice( esc_html( get_option( WC_CS_PREFIX . 'previous_cart_removed_while_adding_funds_message' ) ), 'error' ) ;
			}

			$was_added_to_cart = WC()->cart->add_to_cart( _wc_cs()->funds_addition->get_product(), 1, 0, array(), array(
				WC_CS_PREFIX . 'funds_addition' => array(
					'amount' => $amount
				) ) ) ;

			if ( false === $was_added_to_cart ) {
				throw new Exception( esc_html( get_option( WC_CS_PREFIX . 'while_preparing_to_add_funds_message' ) ) ) ;
			}

			//redirect to checkout page
			wp_safe_redirect( wc_get_checkout_url() ) ;
			exit ;
		} catch ( Exception $e ) {
			wc_add_notice( $e->getMessage(), 'error' ) ;
		}
	}

	/**
	 * Credit Application Submitted
	 */
	public static function credit_app_submitted() {
		if ( ! isset( $_REQUEST[ 'credit_app_submitted' ] ) || ! isset( $_REQUEST[ WC_CS_PREFIX . 'nonce' ] ) ) {
			return ;
		}

		if ( ! wp_verify_nonce( sanitize_key( wp_unslash( $_REQUEST[ WC_CS_PREFIX . 'nonce' ] ) ), 'wc-cs-credit-app-submited' ) ) {
			return ;
		}

		if ( _wc_cs()->dashboard->is_credits_user() ) {
			return ;
		}

		try {
			$errors = new WP_Error() ;
			$posted = array_merge( ( array ) $_REQUEST, ( array ) $_FILES ) ;

			// Validate posted data before proceeding.
			WC_CS_Form_Fields::validate_posted_data( $posted, $errors ) ;

			$messages = $errors->get_error_messages() ;

			if ( $messages ) {
				throw new Exception( implode( '<br>', $messages ) ) ;
			}

			$credits = _wc_cs_create_credits( array(
				'created_via' => 'app',
				'user_id'     => get_current_user_id()
					) ) ;

			if ( is_wp_error( $credits ) ) {
				throw new Exception( $credits->get_error_message() ) ;
			}

			foreach ( WC_CS_Form_Fields::get_available_fields() as $field_key => $field ) {
				if ( ! isset( $posted[ $field_key ] ) ) {
					continue ;
				}

				switch ( $field_key ) {
					case 'file_attachments':
						$uploader    = new WC_CS_File_Uploader( 'user documents' ) ;
						$uploaded    = $uploader->upload_files( $posted[ 'file_attachments' ] ) ;
						$attachments = array() ;

						if ( ! empty( $uploaded ) ) {
							foreach ( $uploaded as $file_path ) {
								$attachments[] = $uploader->add_to_library( $file_path, $credits->get_id() ) ;
							}
						}

						$credits->set_attachments( $attachments ) ;
						break ;
					default:
						$setter = 'set_user_' . str_replace( 'billing_', '', $field_key ) ;

						if ( is_callable( array( $credits, $setter ) ) ) {
							$credits->{$setter}( wc_clean( wp_unslash( $posted[ $field_key ] ) ) ) ;
						}
				}
			}

			$credits->save() ;
			_wc_cs()->dashboard->clear_cache() ;

			do_action( 'wc_cs_credits_application_submitted', $credits->get_id() ) ;
		} catch ( Exception $e ) {
			wc_add_notice( $e->getMessage(), 'error' ) ;
		}
	}

	/**
	 * Do some dashboard action
	 */
	public static function do_dashboard_action() {
		if ( ! isset( $_REQUEST[ WC_CS_PREFIX . 'nonce' ] ) ) {
			return ;
		}

		if ( ! empty( $_REQUEST[ 'do-repayment' ] ) && wp_verify_nonce( sanitize_key( wp_unslash( $_REQUEST[ WC_CS_PREFIX . 'nonce' ] ) ), 'wc-cs-do-repayment' ) ) {
			if ( ! _wc_cs()->dashboard->can_user_make_repayment() ) {
				wc_add_notice( esc_html( get_option( WC_CS_PREFIX . 'cannot_make_repayment_message' ) ), 'error' ) ;
				wp_safe_redirect( wc_get_page_permalink( 'myaccount' ) ) ;
				exit ;
			}

			if ( _wc_cs()->repayment->in_progress() ) {
				wc_add_notice( esc_html( get_option( WC_CS_PREFIX . 'repayment_in_cart_message' ) ), 'error' ) ;
				wp_safe_redirect( wc_get_checkout_url() ) ;
				exit ;
			}

			try {
				if ( ! WC()->cart->is_empty() ) {
					WC()->cart->empty_cart() ;
					wc_add_notice( esc_html( get_option( WC_CS_PREFIX . 'repayment_remove_cart_notice_label' ) ), 'error' ) ;
				}

				$was_added_to_cart = WC()->cart->add_to_cart( _wc_cs()->repayment->get_product(), 1, 0, array(), array(
					WC_CS_PREFIX . 'repayment' => array(
						'credits' => _wc_cs()->dashboard->get_credits()->get_id(),
						'amount'  => _wc_cs()->dashboard->get_credits()->get_last_billed_amount( 'edit' )
					) ) ) ;

				if ( false === $was_added_to_cart ) {
					throw new Exception( esc_html( get_option( WC_CS_PREFIX . 'while_preparing_to_repay_message' ) ) ) ;
				}

				//redirect to checkout page
				wp_safe_redirect( wc_get_checkout_url() ) ;
				exit ;
			} catch ( Exception $e ) {
				wc_add_notice( $e->getMessage(), 'error' ) ;
			}
		}

		if ( ! empty( $_REQUEST[ 'view-statement' ] ) && wp_verify_nonce( sanitize_key( wp_unslash( $_REQUEST[ WC_CS_PREFIX . 'nonce' ] ) ), 'wc-cs-view-statement' ) ) {
			try {
				$html = new WC_CS_Bill_Statement_HTML( null, sanitize_key( wp_unslash( $_REQUEST[ 'view-statement' ] ) ), _wc_cs()->dashboard->get_credits() ) ;
				$html->set_logoAttachment( get_option( WC_CS_PREFIX . 'get_header_logo_attachment' ) ) ;
				$html->generate() ;
			} catch ( Exception $e ) {
				wc_add_notice( $e->getMessage(), 'error' ) ;
			}
		}
	}

}

WC_CS_Form_Handler::init() ;
