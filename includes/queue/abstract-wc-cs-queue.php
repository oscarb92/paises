<?php

defined( 'ABSPATH' ) || exit ;

/**
 * Job Queue.
 * 
 * @class WC_CS_Queue
 * @package Class
 */
abstract class WC_CS_Queue extends WC_CS_Background_Process {

	/**
	 * Parent ID of Scheduled Jobs.
	 * 
	 * @var int
	 */
	protected $parent_id = 0 ;

	/**
	 * Scheduled job names.
	 * 
	 * @var array
	 */
	protected $jobs = array() ;

	/**
	 * Points to the WPBD reference.
	 * 
	 * @var WPDB 
	 */
	protected $wpdb_ref ;

	/**
	 * Post Type for Job Scheduler
	 */
	const SCHEDULER_POST_TYPE = 'wc_cs_scheduled_jobs' ;

	/**
	 * Construct WC_CS_Queue
	 */
	public function __construct() {
		global $wpdb ;

		$this->wpdb_ref = &$wpdb ;
		parent::__construct() ;
	}

	/**
	 * Get the parent ID of scheduled jobs.
	 * 
	 * @return int
	 */
	public function get_parent_id() {
		return $this->parent_id ;
	}

	/**
	 * Get scheduled job names.
	 * 
	 * @return array()
	 */
	public function get_jobs() {
		return $this->jobs ;
	}

	/**
	 * Are jobs available, prepare to dispatch.
	 *
	 * @return bool
	 */
	public function are_jobs_available() {
		$this->get_jobs() ;

		return ! empty( $this->jobs ) ? true : false ;
	}

	/**
	 * Whether job is valid.
	 *
	 * @return bool
	 */
	public function is_job_valid( $job_name ) {
		if ( ! empty( $job_name ) && in_array( $job_name, ( array ) $this->get_jobs() ) ) {
			return true ;
		}

		return false ;
	}

	/**
	 * Get scheduled jobs to dispatch.
	 * 
	 * @return array()
	 */
	public function get_available_queue() {
		return $this->get_jobs() ;
	}

	/**
	 * Is queue item valid.
	 * 
	 * @return bool
	 */
	public function is_queue_valid( $queue ) {

		if ( ! parent::is_queue_valid( $queue ) ) {
			return false ;
		}

		if ( ! $this->is_job_valid( $queue->name ) ) {
			return false ;
		}

		return true ;
	}

	/**
	 * Check whether the job scheduled for the respective parent.
	 * 
	 * @return bool|int
	 */
	public function exists( $job_name, $scheduled_on = null ) {
		if ( ! $this->is_job_valid( $job_name ) ) {
			return false ;
		}

		if ( ! $scheduled_on ) {
			$job_id = absint( $this->wpdb_ref->get_var(
							$this->wpdb_ref->prepare(
									"SELECT DISTINCT ID FROM {$this->wpdb_ref->posts} WHERE post_type=%s AND post_status='publish' AND post_author='1' AND post_parent=%s AND post_excerpt=%s AND post_title=%s LIMIT 1"
									, self::SCHEDULER_POST_TYPE
									, esc_sql( $this->get_parent_id() )
									, esc_sql( $this->get_group_name() )
									, esc_sql( sanitize_title( $job_name ) )
					) ) ) ;
		} else {
			$job_id = absint( $this->wpdb_ref->get_var(
							$this->wpdb_ref->prepare(
									"SELECT DISTINCT ID FROM {$this->wpdb_ref->posts} INNER JOIN {$this->wpdb_ref->postmeta} AS pm ON (ID = pm.post_id AND pm.meta_key='_scheduled_on' AND pm.meta_value=%s) WHERE post_type=%s AND post_status='publish' AND post_author='1' AND post_parent=%s AND post_excerpt=%s AND post_title=%s LIMIT 1"
									, esc_sql( $scheduled_on )
									, self::SCHEDULER_POST_TYPE
									, esc_sql( $this->get_parent_id() )
									, esc_sql( $this->get_group_name() )
									, esc_sql( sanitize_title( $job_name ) )
					) ) ) ;
		}

		return $job_id ;
	}

	/**
	 * Set the parent ID of scheduled jobs.
	 */
	public function set_parent_id( $id ) {
		$this->parent_id = 0 ;

		if ( ! $id || ! is_numeric( $id ) ) {
			return ;
		}

		$this->parent_id = absint( $id ) ;
	}

	/**
	 * Filter the meta input arg while the post args being prepared.
	 * 
	 * @return bool True to allow the given arg.
	 */
	private function filter_args( $arg ) {
		if ( is_bool( $arg ) || is_numeric( $arg ) ) {
			return true ;
		}

		if ( is_object( $arg ) ) {
			return false ;
		}

		return ! empty( $arg ) ;
	}

	/**
	 * Prepare the post args for the job to schedule.
	 * 
	 * @param string $job_name required. The name of the job hook to execute. 
	 * @param int $job_time required. The time you want the job to run, uses local time in WP. This must be in a valid date/time string.
	 * @param string $recurrence How often the job should subsequently recur. See _wc_cs_get_recurring_job_schedules() for accepted values.
	 * @param array $args optional. Arguments to pass to the job function(s)
	 * @return array
	 */
	private function prepare_post_args( $job_name, $job_time, $recurrence, $args ) {
		$post_args = array(
			'post_type'    => self::SCHEDULER_POST_TYPE,
			'post_author'  => 1,
			'post_status'  => 'publish',
			'ping_status'  => 'closed',
			'post_parent'  => $this->get_parent_id(),
			'post_title'   => sanitize_title( $job_name ),
			'post_excerpt' => sanitize_key( $this->get_group_name() ),
			'meta_input'   => array(
				'_scheduled_on' => absint( $job_time ),
				'_recurrence'   => $recurrence ? sanitize_key( $recurrence ) : null,
				'_args'         => null,
			) ) ;

		$args                                 = array_filter( ( array ) $args, array( $this, 'filter_args' ) ) ;
		$post_args[ 'meta_input' ][ '_args' ] = $args ;
		$post_args[ 'post_content' ]          = ! empty( $args ) ? var_export( $args, true ) : '' ; // To display

		return $post_args ;
	}

	/**
	 * Schedule the job for the respective parent.
	 * 
	 * @param string $job_name required. The name of the job to execute. 
	 * @param int $job_time required. The time you want the job to run, uses local time in WP. This must be in a valid date/time string.
	 * @param string $recurrence How often the job should subsequently recur. See _wc_cs_get_recurring_job_schedules() for accepted values.
	 * @param array $args optional. Arguments to the job.
	 * @return int
	 */
	public function push( $job_name, $job_time, $recurrence = null, $args = array() ) {
		if ( ! $this->is_job_valid( $job_name ) ) {
			return 0 ;
		}

		if ( ! $job_time || ! is_int( $job_time ) ) {
			return 0 ;
		}

		$post_args = $this->prepare_post_args( $job_name, $job_time, $recurrence, $args ) ;
		$this->set_parent_id( $post_args[ 'post_parent' ] ) ;
		$job_id    = wp_insert_post( $post_args, true ) ;

		return $job_id ;
	}

	/**
	 * Get the scheduled job for the respective parent.
	 * 
	 * @return bool|array
	 */
	public function get( $job_name ) {
		if ( ! $this->is_job_valid( $job_name ) ) {
			return false ;
		}

		$scheduled = $this->wpdb_ref->get_col(
				$this->wpdb_ref->prepare(
						"SELECT pm.meta_value FROM {$this->wpdb_ref->postmeta} AS pm 
                        INNER JOIN {$this->wpdb_ref->posts} AS p ON (p.ID = pm.post_id AND p.post_type=%s AND p.post_status='publish' AND p.post_author='1' AND p.post_parent=%s AND p.post_excerpt=%s AND p.post_title=%s) 
                        WHERE pm.meta_key='_scheduled_on'"
						, self::SCHEDULER_POST_TYPE
						, esc_sql( $this->get_parent_id() )
						, esc_sql( $this->get_group_name() )
						, esc_sql( sanitize_title( $job_name ) )
				) ) ;

		$scheduled = ! empty( $scheduled ) ? array_map( 'absint', ( array ) $scheduled ) : false ;

		return $scheduled ;
	}

	/**
	 * Unschedule the given job belongs to the group if available.
	 * 
	 * @param string $job_name
	 */
	public function delete( $job_name ) {
		if ( ! $this->is_job_valid( $job_name ) ) {
			return false ;
		}

		$this->wpdb_ref->get_col(
				$this->wpdb_ref->prepare(
						"DELETE FROM {$this->wpdb_ref->posts} WHERE post_type=%s AND post_status='publish' AND post_author='1' AND post_parent=%s AND post_excerpt=%s AND post_title=%s"
						, self::SCHEDULER_POST_TYPE
						, esc_sql( $this->get_parent_id() )
						, esc_sql( $this->get_group_name() )
						, esc_sql( sanitize_title( $job_name ) )
		) ) ;
	}

	/**
	 * Unschedule all jobs belongs to the group if available.
	 */
	public function delete_all() {
		$this->wpdb_ref->get_col(
				$this->wpdb_ref->prepare(
						"DELETE FROM {$this->wpdb_ref->posts} WHERE post_type=%s AND post_status='publish' AND post_author='1' AND post_parent=%s AND post_excerpt=%s"
						, self::SCHEDULER_POST_TYPE
						, esc_sql( $this->get_parent_id() )
						, esc_sql( $this->get_group_name() )
		) ) ;
	}

}
