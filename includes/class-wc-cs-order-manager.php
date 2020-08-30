<?php

defined( 'ABSPATH' ) || exit ;

/**
 * Order Manager.
 * 
 * @class WC_CS_Order_Manager
 * @package Class
 */
class WC_CS_Order_Manager {

	/**
	 * Data stored in meta keys, prefixed with WC_CS_PREFIX
	 *
	 * @var array
	 */
	protected static $internal_meta_keys = array(
		'funds_added',
		'payment_made',
		'repayment',
		'credits_id',
		'funds_txn_id',
		'purchase_debits_txn_id',
		'interest_debits_txn_id',
		'payment_credits_txn_id'
			) ;

	/**
	 * Init WC_CS_Order_Manager.
	 */
	public static function init() {
		add_action( 'woocommerce_order_status_completed', __CLASS__ . '::maybe_add_funds_to_admin_account', 99 ) ;
		add_action( 'woocommerce_order_status_processing', __CLASS__ . '::maybe_add_funds_to_admin_account', 99 ) ;
		add_action( 'woocommerce_order_status_changed', __CLASS__ . '::maybe_create_unbilled_txn', 99 ) ;
		add_action( 'woocommerce_order_status_changed', __CLASS__ . '::maybe_credit_after_repayment', 99, 3 ) ;
	}

	/**
	 * Retrieve our order type.
	 * 
	 * @param WC_Order $order
	 * @param string $type
	 * @return mixed
	 */
	public static function get_order_type( $order, $type ) {
		foreach ( $order->get_items() as $item ) {
			if ( ! empty( $item[ WC_CS_PREFIX . $type ] ) ) {
				return $item[ WC_CS_PREFIX . $type ] ;
			}
		}

		return false ;
	}

	/**
	 * Retrieve the order transaction status.
	 * 
	 * @param int $order_id
	 * @param string $type
	 * @return string
	 */
	public static function get_order_txn_status( $order_id, $type ) {
		return self::get_meta( $order_id, $type ) ;
	}

	/**
	 * Retrieve our order meta.
	 * 
	 * @param int $order_id
	 * @param string $internal_meta_key
	 * @return string
	 */
	public static function get_meta( $order_id, $internal_meta_key ) {
		if ( ! in_array( $internal_meta_key, self::$internal_meta_keys ) ) {
			return null ;
		}

		return get_post_meta( $order_id, WC_CS_PREFIX . $internal_meta_key, true ) ;
	}

	/**
	 * Add our order meta.
	 * 
	 * @param int $order_id
	 * @param string $internal_meta_key
	 * @param mixed $value
	 */
	public static function add_meta( $order_id, $internal_meta_key, $value ) {
		if ( ! in_array( $internal_meta_key, self::$internal_meta_keys ) ) {
			return null ;
		}

		add_post_meta( $order_id, WC_CS_PREFIX . $internal_meta_key, $value ) ;
	}

	/**
	 * Add funds to admin account.
	 * 
	 * @param int $order_id
	 */
	public static function maybe_add_funds_to_admin_account( $order_id ) {
		$order = wc_get_order( $order_id ) ;

		if ( ! $order || $order->get_total() <= 0 || WC_CS_PREFIX . 'credits' === $order->get_payment_method() || 1 !== count( $order->get_items() ) ) {
			return ;
		}

		if ( 'done' === self::get_order_txn_status( $order_id, 'funds_added' ) ) {
			return ;
		}

		$funds_addition = self::get_order_type( $order, 'funds_addition' ) ;

		if ( ! $funds_addition ) {
			return ;
		}

		do_action( 'wc_cs_before_funds_added', $order ) ;

		WC_CS_Admin_Funds::credit( $funds_addition[ 'amount' ] ) ;
		$funds_txn = WC_CS_Admin_Funds::create_txn( array(
					/* translators: 1: order id */
					'activity' => __( 'Funds Addition', 'credits-for-woocommerce' ),
					'credited' => $funds_addition[ 'amount' ],
					'order_id' => $order_id,
					'type'     => 'funds-added',
				) ) ;

		if ( is_wp_error( $funds_txn ) ) {
			return ;
		}

		WC_CS_Admin_Funds::add_investment( $funds_txn->get_credited( 'edit' ) ) ;

		self::add_meta( $order_id, 'funds_added', 'done' ) ;
		self::add_meta( $order_id, 'funds_txn_id', $funds_txn->get_id() ) ;

		do_action( 'wc_cs_admin_funds_added', $funds_txn, $order ) ;
	}

	/**
	 * Create the unbilled transaction when the customer uses their credits to purchase the order.
	 * 
	 * @param int $order_id
	 */
	public static function maybe_create_unbilled_txn( $order_id ) {
		$order = wc_get_order( $order_id ) ;

		if ( ! $order || $order->get_total() <= 0 || WC_CS_PREFIX . 'credits' !== $order->get_payment_method() ) {
			return ;
		}

		if ( 'yes' === self::get_order_txn_status( $order_id, 'payment_made' ) ) {
			return ;
		}

		$credits = _wc_cs_get_credits_from_user_id( $order->get_customer_id() ) ;

		if ( ! $credits ) {
			return ;
		}

		$interest_charged = _wc_cs_maybe_calculate_interest( $order->get_total() ) ;
		$limit_used       = $order->get_total() + $interest_charged ;

		$credits->set_props( array(
			'total_outstanding_amount' => $credits->get_total_outstanding_amount( 'edit' ) + $limit_used,
			'available_credits'        => $credits->get_available_credits( 'edit' ) - $limit_used,
		) ) ;
		$credits->save() ;

		$credits_txn = _wc_cs_create_credits_txn( array(
			'credits_id' => $credits->get_id(),
			'user_id'    => $credits->get_user_id(),
			/* translators: 1: order ID */
			'activity'   => sprintf( __( 'Payment for Order #%1$s', 'credits-for-woocommerce' ), $order_id ),
			'debited'    => $order->get_total(),
			'order_id'   => $order_id,
			'balance'    => $credits->get_available_credits( 'edit' ),
			'type'       => 'purchase-debits',
				) ) ;

		self::add_meta( $order_id, 'purchase_debits_txn_id', $credits_txn->get_id() ) ;

		if ( $interest_charged ) {
			$credits_txn = _wc_cs_create_credits_txn( array(
				'credits_id' => $credits->get_id(),
				'user_id'    => $credits->get_user_id(),
				/* translators: 1: order ID */
				'activity'   => sprintf( __( 'Interest for the Payment of Order #%1$s', 'credits-for-woocommerce' ), $order_id ),
				'debited'    => $interest_charged,
				'order_id'   => $order_id,
				'balance'    => $credits->get_available_credits( 'edit' ),
				'type'       => 'interest-debits',
					) ) ;

			self::add_meta( $order_id, 'interest_debits_txn_id', $credits_txn->get_id() ) ;
			_wc_cs_add_profit_gained( $interest_charged ) ;
		}

		self::add_meta( $order_id, 'payment_made', 'yes' ) ;
		self::add_meta( $order_id, 'credits_id', $credits->get_id() ) ;
	}

	/**
	 * Customer paid their amount and credit limit is credited back to the user.
	 * 
	 * @param int $order_id
	 * @param string $from_status
	 * @param string $to_status
	 */
	public static function maybe_credit_after_repayment( $order_id, $from_status, $to_status ) {
		$order = wc_get_order( $order_id ) ;

		if ( ! $order || $order->get_total() <= 0 || WC_CS_PREFIX . 'credits' === $order->get_payment_method() || 1 !== count( $order->get_items() ) ) {
			return ;
		}

		if ( 'done' === self::get_order_txn_status( $order_id, 'repayment' ) ) {
			return ;
		}

		$repayment = self::get_order_type( $order, 'repayment' ) ;

		if ( ! $repayment ) {
			return ;
		}

		$credits = _wc_cs_get_credits( $repayment[ 'credits' ] ) ;

		if ( ! $credits ) {
			return ;
		}

		switch ( $to_status ) {
			case 'pending':
			case 'on-hold':
				$credits->set_last_payment_order_id( $order_id ) ;
				$credits->save() ;
				break ;
			case 'failed':
			case 'cancelled':
				$credits->set_last_payment_order_id( 0 ) ;
				$credits->save() ;
				break ;
			case 'processing':
			case 'completed':
				$credits->set_props( array(
					'status'                      => 'active',
					'last_billed_status'          => 'paid',
					'total_outstanding_amount'    => max( 0, $credits->get_total_outstanding_amount( 'edit' ) - $repayment[ 'amount' ] ),
					'unpaid_previous_bill_amount' => 0,
					'available_credits'           => $credits->get_available_credits( 'edit' ) + $repayment[ 'amount' ],
					'last_payment_date'           => _wc_cs_get_time( 'timestamp' ),
					'last_payment_order_id'       => $order_id,
				) ) ;
				$credits->save() ;

				$credits_txn = _wc_cs_create_credits_txn( array(
					'credits_id' => $credits->get_id(),
					'user_id'    => $credits->get_user_id(),
					/* translators: 1: order ID */
					'activity'   => __( 'Credits Repayment', 'credits-for-woocommerce' ),
					'credited'   => $repayment[ 'amount' ],
					'order_id'   => $order_id,
					'balance'    => $credits->get_available_credits( 'edit' ),
					'type'       => 'payment-credits',
						) ) ;

				self::add_meta( $order_id, 'repayment', 'done' ) ;
				self::add_meta( $order_id, 'payment_credits_txn_id', $credits_txn->get_id() ) ;
				self::add_meta( $order_id, 'credits_id', $credits->get_id() ) ;

				// Make sure to clear the pending payment queue once the full repayment is done.
				_wc_cs_cancel_job_from_queue( 'credits', 'charge_late_payment', $credits->get_id() ) ;
				_wc_cs_cancel_job_from_queue( 'credits', 'remind_payment_due', $credits->get_id() ) ;

				// Make sure to create the bill statement since it may unscheduled for some reason.
				if ( ! _wc_cs_job_exists_in_queue( 'credits', 'create_bill_statement', $credits->get_next_bill_date( 'edit' ), $credits->get_id() ) ) {
					_wc_cs_push_job_to_queue( 'credits', 'create_bill_statement', $credits->get_next_bill_date( 'edit' ), $credits->get_id() ) ;
				}

				do_action( 'wc_cs_payment_success', $credits_txn, $credits, $order ) ;
				break ;
		}
	}

}

WC_CS_Order_Manager::init() ;
