<?php

defined( 'ABSPATH' ) || exit ;

/**
 * Abstract WC CS Data Class
 *
 * Implemented by classes using the CRUD(s) pattern.
 *
 * @class WC_CS_Data
 * @package Class
 */
abstract class WC_CS_Data {

	/**
	 * ID for this object.
	 *
	 * @var int
	 */
	protected $id = 0 ;

	/**
	 * Core data for this object. Name value pairs (name + default value).
	 *
	 * @var array
	 */
	protected $data = array() ;

	/**
	 * Core data changes for this object.
	 *
	 * @var array
	 */
	protected $changes = array() ;

	/**
	 * This is the name of this object type.
	 *
	 * @var string
	 */
	protected $object_type = 'data' ;

	/**
	 * Extra data for this object. Name value pairs.
	 * Used to add additional information to an inherited class.
	 *
	 * @var array
	 */
	protected $extra_data = array() ;

	/**
	 * Set default_data from data on construct so we can reset data if needed.
	 *
	 * @var array
	 */
	protected $default_data = array() ;

	/**
	 * Contains a reference to the data store for this class.
	 *
	 * @var object
	 */
	protected $data_store ;

	/**
	 * Default constructor.
	 *
	 * @param int|object $read ID.
	 */
	public function __construct( $read = 0 ) {
		$this->data         = array_merge( $this->data, $this->extra_data ) ;
		$this->default_data = $this->data ;
	}

	/**
	 * Only store the object ID to avoid serializing the data object instance.
	 *
	 * @return array
	 */
	public function __sleep() {
		return array( 'id' ) ;
	}

	/**
	 * Reestablish the constructor with the object ID.
	 *
	 * If the object no longer exists, remove the ID.
	 */
	public function __wakeup() {
		try {
			$this->__construct( absint( $this->get_id() ) ) ;
		} catch ( Exception $e ) {
			$this->set_id( 0 ) ;
		}
	}

	/**
	 * Change data to JSON format.
	 *
	 * @return string Data in JSON format.
	 */
	public function __toString() {
		return wp_json_encode( $this->get_data() ) ;
	}

	/**
	 * Returns the unique ID for this object.
	 *
	 * @return int
	 */
	public function get_id() {
		return $this->id ;
	}

	/**
	 * Get internal type.
	 *
	 * @return string
	 */
	public function get_object_type() {
		return $this->object_type ;
	}

	/**
	 * Get the data store.
	 *
	 * @return object
	 */
	public function get_data_store() {
		return $this->data_store ;
	}

	/**
	 * Returns all data for this object.
	 *
	 * @return array
	 */
	public function get_data() {
		return array_merge( array( 'id' => $this->get_id() ), $this->data ) ;
	}

	/**
	 * Return data changes only.
	 *
	 * @return array
	 */
	public function get_changes() {
		return $this->changes ;
	}

	/**
	 * Returns array of expected data keys for this object.
	 *
	 * @return array
	 */
	public function get_data_keys() {
		return array_keys( $this->data ) ;
	}

	/**
	 * Returns array of all "extra" data keys for an object.
	 *
	 * @return array
	 */
	public function get_extra_data_keys() {
		return array_keys( $this->extra_data ) ;
	}

	/**
	 * Prefix for action and filter hooks on data.
	 *
	 * @return string
	 */
	protected function get_hook_prefix() {
		return 'wc_cs_' . $this->object_type . '_get_' ;
	}

	/**
	 * Gets a prop for a getter method.
	 *
	 * Gets the value from either current pending changes, or the data itself.
	 * Context controls what happens to the value before it's returned.
	 *
	 * @param  string $prop Name of prop to get.
	 * @param  string $context What the value is for. Valid values are view and edit.
	 * @return mixed
	 */
	protected function get_prop( $prop, $context = 'view' ) {
		$value = null ;

		if ( $this->data_has( $prop ) ) {
			$value = $this->changes_in( $prop ) ? $this->changes[ $prop ] : $this->data[ $prop ] ;

			if ( 'view' === $context ) {
				$value = apply_filters( $this->get_hook_prefix() . $prop, $value, $this ) ;
			}
		}

		return $value ;
	}

	/**
	 * Gets a default prop.
	 *
	 * @param  string $prop Name of prop to get.
	 * @return mixed
	 */
	protected function get_default_prop( $prop ) {
		$value = null ;

		if ( $this->data_has( $prop ) && array_key_exists( $prop, $this->default_data ) ) {
			$value = $this->default_data[ $prop ] ;
		}

		return $value ;
	}

	/**
	 * Check if any changes has been made for this object.
	 * 
	 * @return bool
	 */
	public function has_changes() {
		return ! empty( $this->changes ) ? true : false ;
	}

	/**
	 * Check if data contains given prop for this object.
	 * 
	 * @param string $prop Name of prop to check.
	 * @return bool
	 */
	public function data_has( $prop ) {
		return array_key_exists( $prop, $this->data ) ? true : false ;
	}

	/**
	 * Check if changes contains given prop for this object.
	 * 
	 * @param string $prop Name of prop to check.
	 * @return bool
	 */
	public function changes_in( $prop ) {
		return array_key_exists( $prop, $this->changes ) ? true : false ;
	}

	/**
	 * Save should create or update based on object existence.
	 *
	 * @return int|WP_Error
	 */
	public function save() {
		if ( ! $this->data_store ) {
			return $this->get_id() ;
		}

		$errors = false ;
		try {
			/**
			 * Be ready before saving to the DB.
			 */
			$this->before_save() ;

			if ( $this->get_id() ) {
				$this->data_store->update( $this ) ;
			} else {
				$this->data_store->create( $this ) ;
			}

			/**
			 * Data saved successful to the DB.
			 */
			$this->saved() ;
		} catch ( Exception $e ) {
			if ( ! $errors ) {
				$errors = new WP_Error() ;
			}
			$errors->add( 'wc-cs-invalid-data', $e->getMessage() ) ;
		}

		if ( $errors ) {
			/**
			 * At this point we ended up with errors.
			 * 
			 * Maybe some of the data gets saved.
			 */
			return $this->save_error( $errors ) ;
		}

		/**
		 * When we reached here, data saved with no error.
		 */
		return $this->get_id() ;
	}

	/**
	 * Delete an object, set the ID to 0.
	 *
	 * @param  bool $force Should the ID be deleted permanently.
	 */
	public function delete( $force = false ) {
		if ( $this->data_store ) {
			$this->data_store->delete( $this, $force ) ;
			$this->set_id( 0 ) ;
			return true ;
		}
	}

	/**
	 * Set ID.
	 *
	 * @param int $id ID.
	 */
	public function set_id( $id ) {
		$this->id = absint( $id ) ;
	}

	/**
	 * Set all props to default values.
	 */
	public function set_defaults() {
		$this->data    = $this->default_data ;
		$this->changes = array() ;
	}

	/**
	 * Set a collection of props in one go.
	 *
	 * @param array $props Key value pairs to set. Key is the prop and should map to a setter function name.
	 */
	public function set_props( $props ) {
		if ( ! is_array( $props ) ) {
			return ;
		}

		$errors = false ;
		foreach ( $props as $prop => $value ) {
			try {
				if ( is_null( $value ) ) {
					continue ;
				}

				$setter = "set_$prop" ;

				if ( is_callable( array( $this, $setter ) ) ) {
					$this->{$setter}( $value ) ;
				}
			} catch ( Exception $e ) {
				if ( ! $errors ) {
					$errors = new WP_Error() ;
				}
				$errors->add( 'wc-cs-invalid-data', $e->getMessage() ) ;
			}
		}

		return $errors ? $errors : true ;
	}

	/**
	 * Sets a prop for a setter method.
	 *
	 * This stores changes in a special array so we can track what needs saving
	 * the DB later.
	 *
	 * @param string $prop Name of prop to set.
	 * @param mixed  $value Value of the prop.
	 */
	protected function set_prop( $prop, $value ) {
		if ( $this->data_has( $prop ) ) {
			if ( $value !== $this->data[ $prop ] || $this->changes_in( $prop ) ) {
				$this->changes[ $prop ] = $value ;
			} else {
				$this->data[ $prop ] = $value ;
			}
		}
	}

	/**
	 * Merge changes with data and clear.
	 */
	public function apply_changes() {
		$this->data    = array_replace_recursive( $this->data, $this->changes ) ;
		$this->changes = array() ;
	}

	/**
	 * Do something before saving to the DB.
	 */
	protected function before_save() {
		/**
		 * Trigger action before saving to the DB. Allows you to adjust object props before save.
		 */
		do_action( 'wc_cs_before_' . $this->object_type . '_object_save', $this, $this->data_store ) ;
	}

	/**
	 * Do something after saving to the DB.
	 */
	protected function saved() {
		/**
		 * Trigger action after saving to the DB.
		 */
		do_action( 'wc_cs_after_' . $this->object_type . '_object_save', $this, $this->data_store ) ;
	}

	/**
	 * Returns error when the data saving to the DB is unsuccessful.
	 * 
	 * @param WP_Error $error
	 * @return WP_Error
	 */
	protected function save_error( &$error ) {
		return $error ;
	}

}
