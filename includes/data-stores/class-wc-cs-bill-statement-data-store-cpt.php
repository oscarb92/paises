<?php

defined( 'ABSPATH' ) || exit ;

/**
 * Bill Statement Data Store CPT
 * 
 * @class WC_CS_Bill_Statement_Data_Store_CPT
 * @package Class
 */
class WC_CS_Bill_Statement_Data_Store_CPT {

	/**
	 * Data stored in post keys, but not considered "meta" for the bill statement.
	 *
	 * @var array
	 */
	protected $post_keys = array(
		'user_id',
		'credits_id',
		'status',
		'hash'
			) ;

	/**
	 * Method to create a new ID in the database from the new changes.
	 * 
	 * @param  WC_CS_Bill_Statement $bill_statement
	 */
	public function create( &$bill_statement ) {
		if ( ! $bill_statement->get_user_id() ) {
			throw new Exception( esc_html__( 'Invalid user ID to create bill statement.', 'credits-for-woocommerce' ) ) ;
		}

		if ( ! $bill_statement->get_credits_id() ) {
			throw new Exception( esc_html__( 'Invalid credits ID to create bill statement.', 'credits-for-woocommerce' ) ) ;
		}

		if ( ! $bill_statement->get_hash( 'edit' ) ) {
			throw new Exception( esc_html__( 'Invalid hash value to create bill statement.', 'credits-for-woocommerce' ) ) ;
		}

		$bill_statement->set_version( WC_CS_VERSION ) ;

		$id = wp_insert_post( array(
			'post_type'     => $bill_statement->get_object_type(),
			'post_status'   => $this->get_post_status( $bill_statement ),
			'ping_status'   => 'closed',
			'post_parent'   => $bill_statement->get_credits_id( 'edit' ),
			'post_author'   => $bill_statement->get_user_id( 'edit' ),
			'post_password' => $bill_statement->get_hash( 'edit' ),
			'post_title'    => esc_html__( 'Bill Statement', 'credits-for-woocommerce' ),
				), true ) ;

		if ( ! $id || is_wp_error( $id ) ) {
			throw new Exception( esc_html__( 'Invalid bill statement ID.', 'credits-for-woocommerce' ) ) ;
		}

		$bill_statement->set_id( $id ) ;
		$bill_statement->apply_changes() ;
		$this->save_meta( $bill_statement, $bill_statement->get_data() ) ; // Make sure the data gets saved after changes applied.
	}

	/**
	 * Method to read data from the database.
	 * 
	 * @param  WC_CS_Bill_Statement $bill_statement
	 */
	public function read( &$bill_statement ) {
		if ( ! $bill_statement->get_id() ) {
			throw new Exception( esc_html__( 'Invalid bill statement ID.', 'credits-for-woocommerce' ) ) ;
		}

		$bill_statement->set_defaults() ;
		$post = get_post( $bill_statement->get_id() ) ;

		if ( ! $post || $post->post_type !== $bill_statement->get_object_type() ) {
			throw new Exception( esc_html__( 'Invalid bill statement.', 'credits-for-woocommerce' ) ) ;
		}

		$bill_statement->set_props( array(
			'user_id'    => $post->post_author,
			'credits_id' => $post->post_parent,
			'status'     => $post->post_status,
			'hash'       => $post->post_password,
		) ) ;
		$this->read_meta( $bill_statement ) ;
		$bill_statement->apply_changes() ;
	}

	/**
	 * Method to update changes in the database.
	 * 
	 * @param  WC_CS_Bill_Statement $bill_statement
	 */
	public function update( &$bill_statement ) {
		if ( ! $bill_statement->get_id() ) {
			throw new Exception( esc_html__( 'Invalid bill statement ID to update.', 'credits-for-woocommerce' ) ) ;
		}

		if ( ! $bill_statement->get_user_id() ) {
			throw new Exception( esc_html__( 'Invalid user ID to update bill statement.', 'credits-for-woocommerce' ) ) ;
		}

		if ( ! $bill_statement->get_credits_id() ) {
			throw new Exception( esc_html__( 'Invalid credits ID to update bill statement.', 'credits-for-woocommerce' ) ) ;
		}

		if ( ! $bill_statement->get_hash( 'edit' ) ) {
			throw new Exception( esc_html__( 'Invalid hash value to update bill statement.', 'credits-for-woocommerce' ) ) ;
		}

		$bill_statement->set_version( WC_CS_VERSION ) ;

		$post_data = array(
			'post_status'   => $this->get_post_status( $bill_statement ),
			'post_author'   => $bill_statement->get_user_id( 'edit' ),
			'post_parent'   => $bill_statement->get_credits_id( 'edit' ),
			'post_password' => $bill_statement->get_hash( 'edit' ),
				) ;

		if ( doing_action( 'save_post' ) ) {
			$GLOBALS[ 'wpdb' ]->update( $GLOBALS[ 'wpdb' ]->posts, $post_data, array( 'ID' => $bill_statement->get_id() ) ) ;
			clean_post_cache( $bill_statement->get_id() ) ;
		} else {
			wp_update_post( array_merge( array( 'ID' => $bill_statement->get_id() ), $post_data ) ) ;
		}

		$this->save_meta( $bill_statement, $bill_statement->get_changes() ) ;
		$bill_statement->apply_changes() ;
	}

	/**
	 * Delete an object, set the ID to 0.
	 *
	 * @param  WC_CS_Bill_Statement $bill_statement
	 * @param  bool $force Should the ID be deleted permanently.
	 */
	public function delete( &$bill_statement, $force = false ) {
		if ( $force ) {
			wp_delete_post( $bill_statement->get_id() ) ;
			$bill_statement->set_id( 0 ) ;
			$bill_statement->set_defaults() ;
		} else {
			wp_trash_post( $bill_statement->get_id() ) ;
			$bill_statement->set_status( 'trash' ) ;
		}
	}

	/**
	 * Get the status to save to the post object.
	 *
	 * @param  WC_CS_Bill_Statement $bill_statement
	 * @return string
	 */
	protected function get_post_status( $bill_statement ) {
		$post_status = $bill_statement->get_status( 'edit' ) ;

		if ( ! $post_status ) {
			$post_status = apply_filters( 'wc_cs_default_bill statement_status', 'publish' ) ;
		}

		return $post_status ;
	}

	/**
	 * Read meta data from the database.
	 *
	 * @param  WC_CS_Bill_Statement $bill_statement
	 */
	protected function read_meta( &$bill_statement ) {
		foreach ( $bill_statement->get_data_keys() as $prop ) {
			if ( in_array( $prop, $this->post_keys ) ) {
				continue ;
			}

			$setter = "set_$prop" ;
			if ( is_callable( array( $bill_statement, $setter ) ) ) {
				$bill_statement->{$setter}( get_post_meta( $bill_statement->get_id(), "_{$prop}", true ) ) ;
			}
		}
	}

	/**
	 * Save meta data in the database.
	 * 
	 * @param  WC_CS_Bill_Statement $bill_statement
	 * @param array $props_to_update
	 */
	protected function save_meta( &$bill_statement, $props_to_update ) {
		if ( empty( $props_to_update ) ) {
			return ;
		}

		foreach ( $props_to_update as $prop => $value ) {
			if ( in_array( $prop, $this->post_keys ) ) {
				continue ;
			}

			update_post_meta( $bill_statement->get_id(), "_{$prop}", $value ) ;
		}
	}

}
