<?php

defined( 'ABSPATH' ) || exit ;

/**
 * Credits Transaction Data Store CPT
 * 
 * @class WC_CS_Credits_Transaction_Data_Store_CPT
 * @package Class
 */
class WC_CS_Credits_Transaction_Data_Store_CPT extends Abstract_WC_CS_Transaction_Data_Store_CPT {

	/**
	 * Data stored in post keys, but not considered "meta" for the transaction.
	 *
	 * @var array
	 */
	protected $post_keys = array(
		'user_id',
		'credits_id',
		'status',
		'type',
		'activity',
		'key'
			) ;

	/**
	 * Method to create a new ID in the database from the new changes.
	 * 
	 * @param  WC_CS_Credits_Transaction $credits_txn
	 */
	public function create( &$credits_txn ) {
		if ( ! $credits_txn->get_credits_id() ) {
			throw new Exception( esc_html__( 'Invalid credits ID to create transaction.', 'credits-for-woocommerce' ) ) ;
		}

		parent::create( $credits_txn ) ;
	}

	/**
	 * Method to read data from the database.
	 * 
	 * @param  WC_CS_Credits_Transaction $credits_txn
	 */
	public function read( &$credits_txn ) {
		if ( ! $credits_txn->get_id() ) {
			throw new Exception( esc_html__( 'Invalid transaction ID.', 'credits-for-woocommerce' ) ) ;
		}

		$credits_txn->set_defaults() ;
		$post = get_post( $credits_txn->get_id() ) ;

		if ( ! $post || $post->post_type !== $credits_txn->get_object_type() ) {
			throw new Exception( esc_html__( 'Invalid transaction.', 'credits-for-woocommerce' ) ) ;
		}

		$credits_txn->set_props( array(
			'user_id'    => $post->post_author,
			'credits_id' => $post->post_parent,
			'status'     => $post->post_status,
			'type'       => $post->post_excerpt,
			'activity'   => $post->post_content,
			'key'        => $post->post_password,
		) ) ;
		$this->read_meta( $credits_txn ) ;
		$credits_txn->apply_changes() ;
	}

	/**
	 * Method to update changes in the database.
	 * 
	 * @param  WC_CS_Credits_Transaction $credits_txn
	 */
	public function update( &$credits_txn ) {
		if ( ! $credits_txn->get_credits_id() ) {
			throw new Exception( esc_html__( 'Invalid credits ID to update transaction.', 'credits-for-woocommerce' ) ) ;
		}

		parent::update( $credits_txn ) ;
	}

	/**
	 * Get the status to save to the post object.
	 *
	 * @param  WC_CS_Credits_Transaction $credits_txn
	 * @return string
	 */
	protected function get_post_status( $credits_txn ) {
		$post_status = parent::get_post_status( $credits_txn ) ;

		if ( ! $post_status ) {
			$post_status = apply_filters( 'wc_cs_default_credits_transaction_status', 'unbilled' ) ;
		}

		if ( in_array( WC_CS_PREFIX . $post_status, $credits_txn->get_valid_statuses() ) ) {
			$post_status = WC_CS_PREFIX . $post_status ;
		}

		return $post_status ;
	}

	/**
	 * Get the title to save to the post object.
	 *
	 * @param  object $txn
	 * @return string
	 */
	protected function get_post_title( $txn ) {
		return esc_html__( 'Credits Transaction', 'credits-for-woocommerce' ) ;
	}

	/**
	 * Get the parent ID for the post to save to the post object.
	 *
	 * @param  object $txn
	 * @return string
	 */
	protected function get_post_parent( $txn ) {
		return $txn->get_credits_id( 'edit' ) ;
	}

}
