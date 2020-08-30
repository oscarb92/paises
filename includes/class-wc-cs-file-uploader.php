<?php

defined( 'ABSPATH' ) || exit ;

/**
 * WC_CS_File_Uploader class.
 */
class WC_CS_File_Uploader {

	/**
	 * Base directory folder name 
	 * 
	 * @var string 
	 */
	protected $base_folder_name ;

	/**
	 * Allow to upload files in the base directory.
	 * 
	 * @var string 
	 */
	protected $base_directory ;

	/**
	 * Allow to upload files in the separate directory rather in the base.
	 * 
	 * @var string 
	 */
	protected $sub_directory ;

	/**
	 * Constructor.
	 */
	public function __construct( $sub_dirname = null ) {
		$this->prepare_base_folder_name() ;
		$this->get_base_directory() ;
		$this->maybe_make_directory( $sub_dirname ) ;
	}

	/**
	 * Get the base directory of our plugin uploads
	 * 
	 * @return string
	 */
	public function get_base_directory() {
		$upload_dir           = wp_upload_dir() ;
		$this->base_directory = $upload_dir[ 'basedir' ] . '/' . $this->base_folder_name ;

		return $this->base_directory ;
	}

	/**
	 * Get the current directory to upload our files
	 * 
	 * @return string
	 */
	public function get_upload_directory() {
		$this->get_base_directory() ;

		return $this->prepare_upload_directory() ;
	}

	/**
	 * Prepare the folder name to create our plugin base directory
	 * 
	 * @return string
	 */
	protected function prepare_base_folder_name() {
		$plugin                 = get_plugin_data( WC_CS_FILE ) ;
		$this->base_folder_name = strtolower( sanitize_file_name( $plugin[ 'Name' ] ) ) . '-uploads' ;

		return $this->base_folder_name ;
	}

	/**
	 * Prepare the current directory to upload our files
	 */
	protected function prepare_upload_directory() {
		if ( $this->sub_directory ) {
			if ( file_exists( $this->sub_directory ) && is_dir( $this->sub_directory ) ) {
				return $this->sub_directory ;
			}
		} else {
			if ( file_exists( $this->base_directory ) && is_dir( $this->base_directory ) ) {
				return $this->base_directory ;
			}
		}

		return '' ;
	}

	/**
	 * Maybe make the sub directory for the base to upload our files
	 */
	protected function maybe_make_sub_directory( $sub_dirname ) {
		if ( empty( $sub_dirname ) || ! is_string( $sub_dirname ) ) {
			$this->sub_directory = null ;
			return ;
		}

		$this->sub_directory = $this->base_directory . '/' . strtolower( sanitize_file_name( $sub_dirname ) ) ;

		if ( ! file_exists( $this->sub_directory ) && ! is_dir( $this->sub_directory ) ) {
			wp_mkdir_p( $this->sub_directory ) ;
		}
	}

	/**
	 * Maybe make the directory to upload our files.
	 */
	protected function maybe_make_directory( $sub_dirname = null ) {
		if ( ! file_exists( $this->base_directory ) && ! is_dir( $this->base_directory ) ) {
			wp_mkdir_p( $this->base_directory ) ;
		}

		$this->maybe_make_sub_directory( $sub_dirname ) ;
	}

	/**
	 * Prepare the file path from the given filename.
	 */
	protected function prepare_file_path( $file_name ) {
		$file_name = sanitize_file_name( $file_name ) ;

		return $this->get_upload_directory() . '/' . $file_name ;
	}

	/**
	 * Upload the files to server.
	 * 
	 * @param array $files
	 * @return array
	 */
	public function upload_files( $files ) {
		if ( empty( $files[ 'tmp_name' ] ) ) {
			return array() ;
		}

		$uploaded = array() ;
		if ( is_array( $files[ 'tmp_name' ] ) ) {
			$file_count = count( $files[ 'tmp_name' ] ) ;

			for ( $i = 0 ; $i < $file_count ; $i ++ ) {
				if ( empty( $files[ 'tmp_name' ][ $i ] ) ) {
					continue ;
				}

				$file_path = $this->prepare_file_path( $files[ 'name' ][ $i ] ) ;

				if ( move_uploaded_file( $files[ 'tmp_name' ][ $i ], $file_path ) ) {
					if ( ! file_exists( $file_path ) ) {
						return $uploaded ;
					}

					$uploaded[ $files[ 'name' ][ $i ] ] = $file_path ;
				}
			}
		} else {
			$file_path = $this->prepare_file_path( $files[ 'name' ] ) ;

			if ( move_uploaded_file( $files[ 'tmp_name' ], $file_path ) ) {
				if ( ! file_exists( $file_path ) ) {
					return $uploaded ;
				}

				$uploaded[ $files[ 'name' ] ] = $file_path ;
			}
		}

		return $uploaded ;
	}

	/**
	 * Add the files to WP media.
	 * 
	 * @param string $file_path Should be the path to a file in the upload directory.
	 * @param int $reference_id The ID of the post this attachment is for.
	 * @return int Attachment ID
	 */
	public function add_to_library( $file_path, $reference_id = 0 ) {
		// Check the type of file.
		$filetype = wp_check_filetype( basename( $file_path ), null ) ;

		// Get the path to the upload directory.
		$upload_dir = wp_upload_dir() ;
		$attachment = array(
			'guid'           => $upload_dir[ 'url' ] . '/' . basename( $file_path ),
			'post_mime_type' => $filetype[ 'type' ],
			'post_title'     => preg_replace( '/\.[^.]+$/', '', basename( $file_path ) ),
			'post_content'   => '',
			'post_status'    => 'inherit'
				) ;

		// Insert the attachment.
		$attach_id = wp_insert_attachment( $attachment, $file_path, $reference_id ) ;

		// Make sure that this file is included, as wp_generate_attachment_metadata() depends on it.
		require_once( ABSPATH . 'wp-admin/includes/image.php' ) ;

		// Generate the metadata for the attachment, and update the database record.
		$attach_data = wp_generate_attachment_metadata( $attach_id, $file_path ) ;
		wp_update_attachment_metadata( $attach_id, $attach_data ) ;

		if ( $reference_id ) {
			set_post_thumbnail( $reference_id, $attach_id ) ;
		}

		return $attach_id ;
	}

}
