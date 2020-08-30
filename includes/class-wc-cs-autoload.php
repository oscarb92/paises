<?php

defined( 'ABSPATH' ) || exit ;

/**
 * Credits for Woocommerce Autoloader.
 */
class WC_CS_Autoloader {

	/**
	 * Path to the includes directory.
	 *
	 * @var string
	 */
	private $include_path = '' ;

	/**
	 * Construct WC_CS_Autoloader
	 */
	public function __construct() {
		$this->include_path = WC_CS_DIR . 'includes/' ;

		spl_autoload_register( array( $this, 'autoload' ) ) ;
	}

	/**
	 * Auto-load our classes on demand to reduce memory consumption.
	 *
	 * @param string $class Class name.
	 */
	public function autoload( $class ) {
		$class = strtolower( $class ) ;

		//Make sure our classes are going to load
		if ( 0 !== strpos( $class, 'wc_cs_' ) ) {
			return ;
		}

		$file = 'class-' . str_replace( '_', '-', $class ) . '.php' ; //Retrieve file name from class name
		$path = $this->include_path . $file ;

		if ( false !== strpos( $class, '_queue' ) ) {
			$path = $this->include_path . 'queue/' . $file ;
		} else if ( false !== strpos( $class, 'meta_box_' ) ) {
			$path = $this->include_path . 'admin/meta-boxes/' . $file ;
		} elseif ( false !== strpos( $class, '_html' ) ) {
			$path = $this->include_path . 'documents/' . $file ;
		}

		//Include a class file.
		if ( $path && is_readable( $path ) ) {
			include_once $path ;
		}
	}

}

new WC_CS_Autoloader() ;
