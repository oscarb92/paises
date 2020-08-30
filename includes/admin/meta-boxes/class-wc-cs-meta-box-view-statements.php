<?php

defined( 'ABSPATH' ) || exit ;

/**
 * View Statements.
 * 
 * @class WC_CS_Meta_Box_View_Statements
 * @package Class
 */
class WC_CS_Meta_Box_View_Statements {

	/**
	 * Output the metabox.
	 *
	 * @param WP_Post $post
	 */
	public static function output( $post ) {
		global $post, $thecredits ;

		if ( ! is_object( $thecredits ) ) {
			$thecredits = _wc_cs_get_credits( $post->ID ) ;
		}

		$credits      = $thecredits ;
		$credits_post = $post ;

		include 'views/html-view-statements.php' ;
	}

}
