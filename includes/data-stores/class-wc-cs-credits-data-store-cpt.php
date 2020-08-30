<?php

defined( 'ABSPATH' ) || exit ;

/**
 * Credits Data Store CPT
 * 
 * @class WC_CS_Credits_Data_Store_CPT
 * @package Class
 */
class WC_CS_Credits_Data_Store_CPT {

	/**
	 * Data stored in post keys, but not considered "meta" for the credits.
	 *
	 * @var array
	 */
	protected $post_keys = array(
		'user_id',
		'status',
			) ;

	/**
	 * Method to create a new ID in the database from the new changes.
	 * 
	 * @param  WC_CS_Credits $credits
	 */
	public function create( &$credits ) {
		if ( ! $credits->get_user_id() ) {
			throw new Exception( esc_html__( 'Invalid user ID to create credits.', 'credits-for-woocommerce' ) ) ;
		}

		$credits->set_version( WC_CS_VERSION ) ;
		$credits->set_date_created( _wc_cs_get_time( 'timestamp' ) ) ;

		$id = wp_insert_post( array(
			'post_type'   => $credits->get_object_type(),
			'post_status' => $this->get_post_status( $credits ),
			'ping_status' => 'closed',
			'post_parent' => 0,
			'post_author' => $credits->get_user_id( 'edit' ),
			'post_title'  => esc_html__( 'Credits', 'credits-for-woocommerce' ),
				), true ) ;

		if ( ! $id || is_wp_error( $id ) ) {
			throw new Exception( esc_html__( 'Invalid Credits ID.', 'credits-for-woocommerce' ) ) ;
		}

		$credits->set_id( $id ) ;
		$credits->apply_changes() ;
		$this->save_meta( $credits, $credits->get_data() ) ; // Make sure the data gets saved after changes applied.
	}

	/**
	 * Method to read data from the database.
	 * 
	 * @param  WC_CS_Credits $credits
	 */
	public function read( &$credits ) {
		if ( ! $credits->get_id() ) {
			throw new Exception( esc_html__( 'Invalid Credits ID.', 'credits-for-woocommerce' ) ) ;
		}

		$credits->set_defaults() ;
		$post = get_post( $credits->get_id() ) ;

		if ( ! $post || $post->post_type !== $credits->get_object_type() ) {
			throw new Exception( esc_html__( 'Invalid credits.', 'credits-for-woocommerce' ) ) ;
		}

		$credits->set_props( array(
			'user_id' => $post->post_author,
			'status'  => $post->post_status,
		) ) ;
		$this->read_meta( $credits ) ;
		$credits->apply_changes() ;
	}

	/**
	 * Method to update changes in the database.
	 * 
	 * @param  WC_CS_Credits $credits
	 */
	public function update( &$credits ) {
		if ( ! $credits->get_id() ) {
			throw new Exception( esc_html__( 'Invalid Credits ID to update.', 'credits-for-woocommerce' ) ) ;
		}

		if ( ! $credits->get_user_id() ) {
			throw new Exception( esc_html__( 'Invalid user ID to update Credits.', 'credits-for-woocommerce' ) ) ;
		}

		$credits->set_version( WC_CS_VERSION ) ;

		$post_data = array(
			'post_status' => $this->get_post_status( $credits ),
			'post_author' => $credits->get_user_id( 'edit' ),
			'post_parent' => 0,
				) ;

		if ( doing_action( 'save_post' ) ) {
			$GLOBALS[ 'wpdb' ]->update( $GLOBALS[ 'wpdb' ]->posts, $post_data, array( 'ID' => $credits->get_id() ) ) ;
			clean_post_cache( $credits->get_id() ) ;
		} else {
			wp_update_post( array_merge( array( 'ID' => $credits->get_id() ), $post_data ) ) ;
		}

		$this->save_meta( $credits, $credits->get_changes() ) ;
		$credits->apply_changes() ;
	}

	/**
	 * Delete an object, set the ID to 0.
	 *
	 * @param  WC_CS_Credits $credits
	 * @param  bool $force Should the ID be deleted permanently.
	 */
	public function delete( &$credits, $force = false ) {
		if ( $force ) {
			wp_delete_post( $credits->get_id() ) ;
			$credits->set_id( 0 ) ;
			$credits->set_defaults() ;
		} else {
			wp_trash_post( $credits->get_id() ) ;
			$credits->set_status( 'trash' ) ;
		}
	}

	/**
	 * Get the status to save to the post object.
	 *
	 * @param  WC_CS_Credits $credits
	 * @return string
	 */
	protected function get_post_status( $credits ) {
		$post_status = $credits->get_status( 'edit' ) ;

		if ( ! $post_status ) {
			$post_status = apply_filters( 'wc_cs_default_credits_status', 'pending' ) ;
		}

		if ( in_array( WC_CS_PREFIX . $post_status, $credits->get_valid_statuses() ) ) {
			$post_status = WC_CS_PREFIX . $post_status ;
		}

		return $post_status ;
	}

	/**
	 * Read meta data from the database.
	 *
	 * @param  WC_CS_Credits $credits
	 */
	protected function read_meta( &$credits ) {
		foreach ( $credits->get_data() as $prop => $value ) {
			if ( in_array( $prop, $this->post_keys ) ) {
				continue ;
			}

			switch ( $prop ) {
				case 'address':
				case 'orders':
					foreach ( $value as $internal_prop => $internal_value ) {
						$setter = "set_$internal_prop" ;
						if ( is_callable( array( $credits, $setter ) ) ) {
							$credits->{$setter}( get_post_meta( $credits->get_id(), "_{$internal_prop}", true ) ) ;
						}
					}
					break ;
				default:
					$setter = "set_$prop" ;
					if ( is_callable( array( $credits, $setter ) ) ) {
						$credits->{$setter}( get_post_meta( $credits->get_id(), "_{$prop}", true ) ) ;
					}
			}
		}
	}

	/**
	 * Save meta data in the database.
	 * 
	 * @param  WC_CS_Credits $credits
	 * @param array $props_to_update
	 */
	protected function save_meta( &$credits, $props_to_update ) {
		if ( empty( $props_to_update ) ) {
			return ;
		}

		foreach ( $props_to_update as $prop => $value ) {
			if ( in_array( $prop, $this->post_keys ) ) {
				continue ;
			}

			switch ( $prop ) {
				case 'address':
				case 'orders':
					foreach ( $value as $internal_prop => $internal_value ) {
						update_post_meta( $credits->get_id(), "_{$internal_prop}", $internal_value ) ;
					}
					break ;
				default:
					update_post_meta( $credits->get_id(), "_{$prop}", $value ) ;
			}
		}

		// If address changed, store concatenated version to make searches faster.
		if ( array_key_exists( 'address', $props_to_update ) || ! metadata_exists( 'post', $credits->get_id(), '_user_address_index' ) ) {
			update_post_meta( $credits->get_id(), '_user_address_index', implode( ' ', $credits->get_address() ) ) ;
		}
	}

	/**
	 * Read credits transactions of a specific type from the database for this credits.
	 *
	 * @param WC_CS_Credits $credits
	 * @param string $type Credits transactions type.
	 * @param string $hash Hash value of the bill statement generated. Want to retrive the specific bill statement ?
	 * @return array
	 */
	public function read_transactions( $credits, $type, $hash = '' ) {
		global $wpdb ;

		$wpdb_ref     = &$wpdb ;
		$transactions = $wpdb_ref->get_col(
				$wpdb_ref->prepare(
						"SELECT DISTINCT ID FROM {$wpdb_ref->posts} WHERE post_type='wc_cs_credits_txn' AND post_author=%s AND post_parent=%s AND post_status=%s AND post_password=%s ORDER BY post_date DESC"
						, esc_sql( $credits->get_user_id() )
						, esc_sql( $credits->get_id() )
						, esc_sql( WC_CS_PREFIX . $type )
						, esc_sql( $hash )
				) ) ;

		if ( empty( $transactions ) ) {
			return array() ;
		}

		return array_filter( array_map( '_wc_cs_get_credits_txn', ( array ) $transactions ) ) ;
	}

}
