<?php

defined( 'ABSPATH' ) || exit ;

/**
 * Check whether the queue is available.
 * 
 * @param WC_CS_Background_Process::$group_name $name
 * @return bool true on Success
 */
function _wc_cs_is_queue_available( $name ) {
	if ( is_null( _wc_cs()->queue ) ) {
		return false ;
	}

	if ( ! $name || ! isset( _wc_cs()->queue[ $name ] ) ) {
		return false ;
	}

	return true ;
}

/**
 * Return the queue if available.
 * 
 * @param WC_CS_Background_Process::$group_name $name
 * @return bool|WC_CS_Queue false on Failure
 */
function _wc_cs_get_queue( $name ) {
	if ( ! _wc_cs_is_queue_available( $name ) ) {
		return false ;
	}

	return _wc_cs()->queue[ $name ] ;
}

/**
 * Format the given datetime in strtotime.
 * 
 * @param string|int $date
 * @return int
 */
function _wc_cs_format_queue_schedule_time( $date ) {
	return _wc_cs_maybe_strtotime( $date ) ;
}

/**
 * Check whether the job scheduled under the group for given datetime in the queue.
 * 
 * @param WC_CS_Background_Process::$group_name $name
 * @param string $job_name Should be unique and present in array of WC_CS_Queue::$jobs
 * @param string|int $scheduled_on
 * @param int $parent_id Parent ID of the group. Want to check the job scheduled under the Parent ID ?
 * @return bool|int false on Failure
 */
function _wc_cs_job_exists_in_queue( $name, $job_name, $scheduled_on = null, $parent_id = 0 ) {
	if ( ! _wc_cs_is_queue_available( $name ) ) {
		return false ;
	}

	_wc_cs()->queue[ $name ]->set_parent_id( $parent_id ) ;

	if ( ! $scheduled_on ) {
		return _wc_cs()->queue[ $name ]->exists( $job_name ) ;
	} else {
		return _wc_cs()->queue[ $name ]->exists( $job_name, _wc_cs_format_queue_schedule_time( $scheduled_on ) ) ;
	}
}

/**
 * Push/Schedule the job under the group in the queue.
 * 
 * @param WC_CS_Background_Process::$group_name $name Should be unique
 * @param string $job_name Should be unique and present in array of WC_CS_Queue::$jobs
 * @param string|int $schedule_on
 * @param int $parent_id Parent ID of the group. Want to schedule the job under the Parent ID ? Which will be easy for mapping the jobs you have scheduled for.
 * @param array $args Additional arguments which are passed on to the job hook's callback function
 * @return bool|int false on Failure
 */
function _wc_cs_push_job_to_queue( $name, $job_name, $schedule_on, $parent_id = 0, $args = array() ) {
	if ( ! _wc_cs_is_queue_available( $name ) ) {
		return false ;
	}

	_wc_cs()->queue[ $name ]->set_parent_id( $parent_id ) ;

	return _wc_cs()->queue[ $name ]->push( $job_name, _wc_cs_format_queue_schedule_time( $schedule_on ), null, $args ) ;
}

/**
 * Push/Schedule the recurring job under the group in the queue.
 *
 * @param WC_CS_Background_Process::$group_name $name Should be unique
 * @param string $job_name Should be unique and present in array of WC_CS_Queue::$jobs
 * @param string|int $schedule_on
 * @param string $recurrence How often the job should subsequently recur. See _wc_cs_get_recurring_job_schedules() for accepted values.
 * @param array $args Additional arguments which are passed on to the job hook's callback function
 * @return bool|int false on Failure
 */
function _wc_cs_push_recurring_job_to_queue( $name, $job_name, $schedule_on, $recurrence, $args = array() ) {
	if ( ! _wc_cs_is_queue_available( $name ) ) {
		return false ;
	}

	_wc_cs()->queue[ $name ]->set_parent_id( 0 ) ;

	return _wc_cs()->queue[ $name ]->push( $job_name, _wc_cs_format_queue_schedule_time( $schedule_on ), $recurrence, $args ) ;
}

/**
 * Return the array of jobs scheduled under the group in the queue.
 * 
 * @param WC_CS_Background_Process::$group_name $name
 * @return bool|array false on Failure
 */
function _wc_cs_get_jobs_from_queue( $name ) {
	if ( ! _wc_cs_is_queue_available( $name ) ) {
		return false ;
	}

	return _wc_cs()->queue[ $name ]->get_queue() ;
}

/**
 * Return the array of job's scheduled datetime under the job name meant for the group in the queue.
 * 
 * @param WC_CS_Background_Process::$group_name $name
 * @param string $job_name If present in array of WC_CS_Queue::$jobs
 * @param int $parent_id Parent ID of the group. Want to get the job's scheduled datetime under the Parent ID ?
 * @param bool $single Want to return single job's scheduled datetime from the array ?
 * @return bool|array false on Failure
 */
function _wc_cs_get_job_scheduled( $name, $job_name, $parent_id = 0, $single = true ) {
	if ( ! _wc_cs_is_queue_available( $name ) ) {
		return false ;
	}

	_wc_cs()->queue[ $name ]->set_parent_id( $parent_id ) ;

	$scheduled = _wc_cs()->queue[ $name ]->get( $job_name ) ;

	if ( $single && is_array( $scheduled ) ) {
		reset( $scheduled ) ;
		$scheduled = current( $scheduled ) ;
	}

	return $scheduled ;
}

/**
 * Cancel the job under the group from the queue.
 * 
 * @param WC_CS_Background_Process::$group_name $name
 * @param string $job_name If present in array of WC_CS_Queue::$jobs
 * @param int $parent_id Parent ID of the group. Want to cancel the job under the Parent ID ?
 * @return bool false on Failure
 */
function _wc_cs_cancel_job_from_queue( $name, $job_name, $parent_id = 0 ) {
	if ( ! _wc_cs_is_queue_available( $name ) ) {
		return false ;
	}

	_wc_cs()->queue[ $name ]->set_parent_id( $parent_id ) ;

	return _wc_cs()->queue[ $name ]->delete( $job_name ) ;
}

/**
 * Cancel every jobs under the group from the queue.
 * 
 * @param WC_CS_Background_Process::$group_name $name
 * @param int $parent_id Parent ID of the group. Want to cancel the jobs under the Parent ID ?
 * @return bool false on Failure
 */
function _wc_cs_cancel_all_jobs_from_queue( $name, $parent_id = 0 ) {
	if ( ! _wc_cs_is_queue_available( $name ) ) {
		return false ;
	}

	_wc_cs()->queue[ $name ]->set_parent_id( $parent_id ) ;

	return _wc_cs()->queue[ $name ]->delete_all() ;
}

/**
 * Cancel the queue process of the group on demand.
 * 
 * @param WC_CS_Background_Process::$group_name $name
 * @return bool false on Failure
 */
function _wc_cs_cancel_queue_process( $name ) {
	if ( ! _wc_cs_is_queue_available( $name ) ) {
		return false ;
	}

	_wc_cs()->queue[ $name ]->cancel_process() ;
}

/**
 * Cancel every queue process on demand.
 * 
 * @return bool false on Failure
 */
function _wc_cs_cancel_all_queue_process() {
	if ( empty( _wc_cs()->queue ) ) {
		return ;
	}

	array_map( '_wc_cs_cancel_queue_process', array_keys( _wc_cs()->queue ) ) ;
}

/**
 * Retrieve supported recurrence job schedules.
 * 
 * @return array
 */
function _wc_cs_get_recurring_job_schedules() {
	$schedules = array(
		'5mins' => array(
			'interval' => 300,
			'display'  => 'Every 5 Minutes',
		),
			) ;

	return array_merge( apply_filters( '_wc_cs_get_recurring_job_schedules', array() ), $schedules ) ;
}
