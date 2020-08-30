<?php

defined( 'ABSPATH' ) || exit ;

/**
 * Site Activity.
 * 
 * @class WC_CS_Meta_Box_Site_Activity
 * @package Class
 */
class WC_CS_Meta_Box_Site_Activity {

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
		$user         = get_user_by( 'ID', $credits->get_user_id() ) ;

		include 'views/html-site-activity.php' ;
	}

}
