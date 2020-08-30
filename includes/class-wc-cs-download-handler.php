<?php

/**
 * Handle digital downloads.
 */
defined( 'ABSPATH' ) || exit ;

/**
 * Download handler class.
 * 
 * @class WC_CS_Download_Handler
 * @package Class
 */
class WC_CS_Download_Handler {

	/**
	 * Hook in methods.
	 */
	public static function init() {
		add_action( 'init', array( __CLASS__, 'download_file' ) ) ;
	}

	/**
	 * Check if we need to download a file and check validity.
	 */
	public static function download_file() {
		if ( ! isset( $_GET[ WC_CS_PREFIX . 'nonce' ] ) || ! isset( $_GET[ 'download_file' ] ) ) {
			return ;
		}

		if ( ! wp_verify_nonce( sanitize_key( wp_unslash( $_GET[ WC_CS_PREFIX . 'nonce' ] ) ), 'wc-cs-download-user-docs' ) ) {
			return ;
		}

		$file_path = wp_get_attachment_url( sanitize_text_field( wp_unslash( $_GET[ 'download_file' ] ) ) ) ;
		self::download( $file_path ) ;
	}

	/**
	 * Download a file - hook into init function.
	 *
	 * @param string  $file_path  URL to file.
	 */
	public static function download( $file_path ) {
		if ( ! $file_path ) {
			self::download_error( __( 'No file defined', 'credits-for-woocommerce' ) ) ;
		}

		$filename = basename( $file_path ) ;

		if ( strstr( $filename, '?' ) ) {
			$filename = current( explode( '?', $filename ) ) ;
		}

		$filename             = apply_filters( 'wc_cs_file_download_filename', $filename ) ;
		$file_download_method = apply_filters( 'wc_cs_file_download_method', 'force' ) ;

		// Add action to prevent issues in IE.
		add_action( 'nocache_headers', array( __CLASS__, 'ie_nocache_headers_fix' ) ) ;

		// Trigger download via one of the methods.
		do_action( 'woocommerce_download_file_' . $file_download_method, $file_path, $filename ) ;
	}

	/**
	 * Filter headers for IE to fix issues over SSL.
	 *
	 * IE bug prevents download via SSL when Cache Control and Pragma no-cache headers set.
	 *
	 * @param array $headers HTTP headers.
	 * @return array
	 */
	public static function ie_nocache_headers_fix( $headers ) {
		if ( is_ssl() && ! empty( $GLOBALS[ 'is_IE' ] ) ) {
			$headers[ 'Cache-Control' ] = 'private' ;
			unset( $headers[ 'Pragma' ] ) ;
		}
		return $headers ;
	}

	/**
	 * Die with an error message if the download fails.
	 *
	 * @param string  $message Error message.
	 * @param string  $title   Error title.
	 * @param integer $status  Error status.
	 */
	private static function download_error( $message, $title = '', $status = 404 ) {
		wp_die( esc_html( $message ), esc_html( $title ), array( 'response' => esc_html( $status ) ) ) ;
	}

}

WC_CS_Download_Handler::init() ;
