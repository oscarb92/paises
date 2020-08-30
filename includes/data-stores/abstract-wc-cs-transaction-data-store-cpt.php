<?php

defined( 'ABSPATH' ) || exit ;

/**
 * Abstract Transaction Data Store: Stored in CPT.
 */
abstract class Abstract_WC_CS_Transaction_Data_Store_CPT {

	/**
	 * Data stored in post keys, but not considered "meta" for the transaction.
	 *
	 * @var array
	 */
	protected $post_keys = array(
		'user_id',
		'status',
		'type',
		'activity',
		'key'
			) ;

	/**
	 * Method to create a new ID in the database from the new changes.
	 * 
	 * @param object $txn
	 */
	public function create( &$txn ) {
		if ( ! $txn->get_user_id() ) {
			throw new Exception( esc_html__( 'Invalid user ID to create transaction.', 'credits-for-woocommerce' ) ) ;
		}

		$txn->set_version( WC_CS_VERSION ) ;
		$txn->set_date_created( _wc_cs_get_time( 'timestamp' ) ) ;

		$id = wp_insert_post( array(
			'post_type'     => $txn->get_object_type(),
			'post_status'   => $this->get_post_status( $txn ),
			'ping_status'   => 'closed',
			'post_parent'   => $this->get_post_parent( $txn ),
			'post_author'   => $txn->get_user_id( 'edit' ),
			'post_excerpt'  => $txn->get_type( 'edit' ),
			'post_content'  => $txn->get_activity( 'edit' ),
			'post_password' => $txn->get_key( 'edit' ),
			'post_title'    => $this->get_post_title( $txn ),
				), true ) ;

		if ( ! $id || is_wp_error( $id ) ) {
			throw new Exception( esc_html__( 'Invalid transaction ID.', 'credits-for-woocommerce' ) ) ;
		}

		$txn->set_id( $id ) ;
		$txn->apply_changes() ;
		$this->save_meta( $txn, $txn->get_data() ) ; // Make sure the data gets saved after changes applied.
	}

	/**
	 * Method to read data from the database.
	 * 
	 * @param  object $txn
	 */
	public function read( &$txn ) {
		if ( ! $txn->get_id() ) {
			throw new Exception( esc_html__( 'Invalid transaction ID.', 'credits-for-woocommerce' ) ) ;
		}

		$txn->set_defaults() ;
		$post = get_post( $txn->get_id() ) ;

		if ( ! $post || $post->post_type !== $txn->get_object_type() ) {
			throw new Exception( esc_html__( 'Invalid transaction.', 'credits-for-woocommerce' ) ) ;
		}

		$txn->set_props( array(
			'user_id'  => $post->post_author,
			'status'   => $post->post_status,
			'type'     => $post->post_excerpt,
			'activity' => $post->post_content,
			'key'      => $post->post_password,
		) ) ;
		$this->read_meta( $txn ) ;
		$txn->apply_changes() ;
	}

	/**
	 * Method to update changes in the database.
	 * 
	 * @param  object $txn
	 */
	public function update( &$txn ) {
		if ( ! $txn->get_id() ) {
			throw new Exception( esc_html__( 'Invalid transaction ID to update.', 'credits-for-woocommerce' ) ) ;
		}

		if ( ! $txn->get_user_id() ) {
			throw new Exception( esc_html__( 'Invalid user ID to update transaction.', 'credits-for-woocommerce' ) ) ;
		}

		$txn->set_version( WC_CS_VERSION ) ;

		$post_data = array(
			'post_status'   => $this->get_post_status( $txn ),
			'post_author'   => $txn->get_user_id( 'edit' ),
			'post_parent'   => $this->get_post_parent( $txn ),
			'post_excerpt'  => $txn->get_type( 'edit' ),
			'post_content'  => $txn->get_activity( 'edit' ),
			'post_password' => $txn->get_key( 'edit' ),
				) ;

		if ( doing_action( 'save_post' ) ) {
			$GLOBALS[ 'wpdb' ]->update( $GLOBALS[ 'wpdb' ]->posts, $post_data, array( 'ID' => $txn->get_id() ) ) ;
			clean_post_cache( $txn->get_id() ) ;
		} else {
			wp_update_post( array_merge( array( 'ID' => $txn->get_id() ), $post_data ) ) ;
		}

		$this->save_meta( $txn, $txn->get_changes() ) ;
		$txn->apply_changes() ;
	}

	/**
	 * Delete an object, set the ID to 0.
	 *
	 * @param  object $txn
	 * @param  bool $force Should the ID be deleted permanently.
	 */
	public function delete( &$txn, $force = false ) {
		if ( $force ) {
			wp_delete_post( $txn->get_id() ) ;
			$txn->set_id( 0 ) ;
			$txn->set_defaults() ;
		} else {
			wp_trash_post( $txn->get_id() ) ;
			$txn->set_status( 'trash' ) ;
		}
	}

	/**
	 * Get the status to save to the post object.
	 *
	 * @param  object $txn
	 * @return string
	 */
	protected function get_post_status( $txn ) {
		return $txn->get_status( 'edit' ) ;
	}

	/**
	 * Get the title to save to the post object.
	 *
	 * @param  object $txn
	 * @return string
	 */
	protected function get_post_title( $txn ) {
		return esc_html__( 'Transaction', 'credits-for-woocommerce' ) ;
	}

	/**
	 * Get the parent ID for the post to save to the post object.
	 *
	 * @param  object $txn
	 * @return string
	 */
	protected function get_post_parent( $txn ) {
		return 0 ;
	}

	/**
	 * Read meta data from the database.
	 *
	 * @param  object $txn
	 */
	protected function read_meta( &$txn ) {
		foreach ( $txn->get_data_keys() as $prop ) {
			if ( in_array( $prop, $this->post_keys ) ) {
				continue ;
			}

			$setter = "set_$prop" ;
			if ( is_callable( array( $txn, $setter ) ) ) {
				$txn->{$setter}( get_post_meta( $txn->get_id(), "_{$prop}", true ) ) ;
			}
		}
	}

	/**
	 * Save meta data in the database.
	 * 
	 * @param  object $txn
	 * @param array $props_to_update
	 */
	protected function save_meta( &$txn, $props_to_update ) {
		if ( empty( $props_to_update ) ) {
			return ;
		}

		foreach ( $props_to_update as $prop => $value ) {
			if ( in_array( $prop, $this->post_keys ) ) {
				continue ;
			}

			update_post_meta( $txn->get_id(), "_{$prop}", $value ) ;
		}
	}

}
