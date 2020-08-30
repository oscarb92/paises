<?php

defined( 'ABSPATH' ) || exit ;

/**
 * Abstract Background Process
 * 
 * @abstract WC_CS_Background_Process
 */
abstract class WC_CS_Background_Process {

	/**
	 * Prefix
	 * 
	 * @var string
	 */
	protected $prefix = 'wc_cs_' ;

	/**
	 * Prepare the parent group name
	 * 
	 * @var string 
	 */
	protected $group_name = '' ;

	/**
	 * Cron Interval in Seconds.
	 * 
	 * @var int
	 */
	protected $cron_interval = 300 ;

	/**
	 * Cron hook identifier
	 *
	 * @var mixed
	 */
	protected $cron_hook_identifier ;

	/**
	 * Cron interval identifier
	 *
	 * @var mixed
	 */
	protected $cron_interval_identifier ;

	/**
	 * Get the queue items which are about to dispatch.
	 * 
	 * @var array
	 */
	protected $queue ;

	/**
	 * Construct WC_CS_Background_Process
	 */
	public function __construct() {
		if ( '' === $this->get_group_name() ) {
			return ;
		}

		$this->cron_hook_identifier     = $this->prefix . $this->get_group_name() . '_background_process' ;
		$this->cron_interval_identifier = $this->prefix . $this->get_group_name() . '_cron_interval' ;

		add_filter( 'cron_schedules', array( $this, 'schedule_cron_healthcheck' ) ) ;
		add_action( $this->cron_hook_identifier, array( $this, 'run' ) ) ;
	}

	/**
	 * Get the parent group name.
	 * 
	 * @return string
	 */
	public function get_group_name() {
		return $this->group_name ;
	}

	/**
	 * Get the Cron Interval in Seconds.
	 * 
	 * @var int
	 */
	public function get_cron_interval() {
		return ( int ) $this->cron_interval ;
	}

	/**
	 * Get the current time. 
	 * By default, it is in WP local time
	 * 
	 * @var int
	 */
	public function get_current_time() {
		return _wc_cs_get_time( 'timestamp' ) ;
	}

	/**
	 * Get the queue items which are about to dispatch.
	 * 
	 * @return array()
	 */
	public function get_queue() {
		return $this->queue ;
	}

	/**
	 * Get the queue jobs which are available.
	 * 
	 * @return array()
	 */
	public function get_available_queue() {
		return array() ;
	}

	/**
	 * Get the parent group name.
	 * 
	 * @return string
	 */
	public function set_group_name( $group_name ) {
		$this->group_name = '' ;

		if ( $group_name ) {
			$this->group_name = sanitize_key( $group_name ) ;
		}
	}

	/**
	 * Prepare the hook name.
	 * 
	 * @return string
	 */
	protected function prepare_dispatch_hook( $queue_name ) {
		$hook = sanitize_key( $this->prefix . $this->get_group_name() . $queue_name ) ;

		return str_replace( '-', '_', $hook ) ;
	}

	/**
	 * Prepare the hooked function name.
	 * 
	 * @return string
	 */
	protected function prepare_dispatch_callable( $queue_name ) {
		$callable = sanitize_key( $this->prefix . $this->get_group_name() . '_' . $queue_name ) ;

		return str_replace( '-', '_', $callable ) ;
	}

	/**
	 * Is queue empty
	 *
	 * @return bool
	 */
	public function is_queue_empty() {
		return ( ! is_array( $this->queue ) || empty( $this->queue ) ) ? true : false ;
	}

	/**
	 * Is queue item valid.
	 * 
	 * @return bool
	 */
	public function is_queue_valid( $item ) {
		if ( ! is_object( $item ) || empty( $item->ID ) || empty( $item->name ) ) {
			return false ;
		}

		if ( empty( $item->scheduled_on ) || ! is_numeric( $item->scheduled_on ) ) {
			return false ;
		}

		return true ;
	}

	/**
	 * Confirm before dispatch.
	 *
	 * @return bool
	 */
	public function can_dispatch() {
		$group_name      = $this->get_group_name() ;
		$available_queue = $this->get_available_queue() ;

		return ( ! empty( $group_name ) && ! empty( $available_queue ) ) ? true : false ;
	}

	/**
	 * Prepare the queue item.
	 * 
	 * @return array()
	 */
	public function prepare_queue_item( $q_id ) {
		$item               = new stdClass() ;
		$item->ID           = $q_id ;
		$item->group        = $this->get_group_name() ;
		$item->name         = null ;
		$item->scheduled_on = null ;
		$item->args         = null ;

		return $item ;
	}

	/**
	 * Schedule cron healthcheck
	 *
	 * @param mixed $schedules Schedules.
	 * @return mixed
	 */
	public function schedule_cron_healthcheck( $schedules ) {
		$schedules[ $this->cron_interval_identifier ] = array(
			'interval' => $this->get_cron_interval(),
			/* translators: 1: cron interval time */
			'display'  => sprintf( __( 'Every %1$d Minutes', 'credits-for-woocommerce' ), $this->get_cron_interval() / 60 )
				) ;

		return $schedules ;
	}

	/**
	 * Schedule event
	 */
	protected function schedule_event() {

		//may be preventing the recurrence Cron interval not to be greater than $this->get_cron_interval()
		if ( ( wp_next_scheduled( $this->cron_hook_identifier ) - _wc_cs_get_time( 'timestamp', array( 'gmt' => true ) ) ) > $this->get_cron_interval() ) {
			$this->cancel_process() ;
		}

		//Schedule recurrence Cron job
		if ( ! wp_next_scheduled( $this->cron_hook_identifier ) ) {
			wp_schedule_event( _wc_cs_get_time( 'timestamp', array( 'gmt' => true ) ) + $this->get_cron_interval(), $this->cron_interval_identifier, $this->cron_hook_identifier ) ;
		}
	}

	/**
	 * Dispatch the queue items.
	 */
	public function dispatch() {
		if ( ! $this->can_dispatch() ) {
			$this->cancel_process() ;
			return ;
		}

		// Schedule the cron healthcheck.
		$this->schedule_event() ;

		foreach ( $this->get_available_queue() as $queue_name ) {
			$callable_name = $this->prepare_dispatch_callable( $queue_name ) ;

			if ( ! function_exists( $callable_name ) ) {
				continue ;
			}

			add_action( $this->prepare_dispatch_hook( $queue_name ), $callable_name, 10, 2 ) ;
		}
	}

	/**
	 * Handle the individual queue item.
	 */
	public function handle( $item ) {
		do_action( $this->prepare_dispatch_hook( $item->name ), $item ) ;

		if ( did_action( $this->prepare_dispatch_hook( $item->name ) ) ) {
			$this->complete( $item ) ;
		} else {
			$this->failure( $item ) ;
		}
	}

	/**
	 * Run.
	 *
	 * Pass each queue item to the task handler, while remaining
	 * within server memory and time limit constraints.
	 */
	public function run() {
		$this->get_queue() ;

		if ( $this->is_queue_empty() ) {
			$this->cancel_process() ;
			return ;
		}

		foreach ( $this->queue as $id ) {
			$item = $this->prepare_queue_item( $id ) ;

			if ( ! $this->is_queue_valid( $item ) ) {
				$this->invalid( $item ) ;
				continue ;
			}

			if ( $this->get_current_time() >= absint( $item->scheduled_on ) ) {
				$this->handle( $item ) ;
			}
		}
	}

	/**
	 * Job is done with success.
	 *
	 * Override this method to perform any actions required on each
	 * queue item. 
	 *
	 * @param mixed $item Corresponding queue data
	 */
	protected function complete( $item ) {
		
	}

	/**
	 * Fails to do the job.
	 *
	 * Override this method to perform any actions required on each
	 * queue item.
	 *
	 * @param mixed $item Corresponding queue data
	 */
	protected function failure( $item ) {
		
	}

	/**
	 * Invalid job.
	 *
	 * Override this method to perform any actions required on each
	 * queue item.
	 *
	 * @param mixed $item Corresponding queue data
	 */
	protected function invalid( $item ) {
		
	}

	/**
	 * Cancel Process
	 *
	 * Clear cronjob.
	 */
	public function cancel_process() {
		unset( $this->queue ) ;
		wp_clear_scheduled_hook( $this->cron_hook_identifier ) ;
	}

}
