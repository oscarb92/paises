<?php

defined( 'ABSPATH' ) || exit ;

/**
 * Credits Background Process
 * 
 * @class WC_CS_Credits_Queue
 */
class WC_CS_Credits_Queue extends WC_CS_Queue {

	/**
	 * Prepare the group name before the jobs gets scheduled.
	 * 
	 * @var string 
	 */
	protected $group_name = 'credits' ;

	/**
	 * Scheduled job names for the respective author to dispatch.
	 * 
	 * @var array
	 */
	protected $jobs = array(
		'heartbeat',
		'create_bill_statement',
		'remind_payment_due',
		'charge_late_payment',
			) ;

	/**
	 * Prepare the queue item.
	 * 
	 * @return array()
	 */
	public function prepare_queue_item( $q_id ) {
		$item = parent::prepare_queue_item( $q_id ) ;
		$job  = get_post( $item->ID ) ;

		if ( $job ) {
			$job_data           = get_post_meta( $item->ID ) ;
			$item->name         = $job->post_title ;
			$item->credits      = $job->post_parent ;
			$item->scheduled_on = _wc_cs_maybe_strtotime( $job_data[ '_scheduled_on' ][ 0 ] ) ;
			$item->recurrence   = isset( $job_data[ '_recurrence' ][ 0 ] ) ? $job_data[ '_recurrence' ][ 0 ] : null ;
			$item->args         = maybe_unserialize( $job_data[ '_args' ][ 0 ] ) ;
		}

		return $item ;
	}

	/**
	 * Get the queue items which are about to dispatch.
	 * 
	 * @return array()
	 */
	public function get_queue() {
		$queue = $this->wpdb_ref->get_col(
				$this->wpdb_ref->prepare(
						"SELECT DISTINCT ID FROM {$this->wpdb_ref->posts} WHERE post_type=%s AND post_status='publish' AND post_author='1' AND post_excerpt=%s AND post_title IN ('" . implode( "','", array_map( 'esc_sql', $this->get_jobs() ) ) . "')"
						, self::SCHEDULER_POST_TYPE
						, esc_sql( $this->get_group_name() )
				) ) ;

		$this->queue = ! is_array( $queue ) ? array() : $queue ;

		return $this->queue ;
	}

	/**
	 * Fire after the Job is done.
	 */
	protected function complete( $item ) {
		wp_delete_post( $item->ID, true ) ;
		$item_id = array_search( $item->ID, $this->queue ) ;

		if ( false !== $item_id ) {
			unset( $this->queue[ $item_id ] ) ;
		}
	}

}
