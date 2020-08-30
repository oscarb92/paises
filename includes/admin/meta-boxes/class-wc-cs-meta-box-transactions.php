<?php

defined( 'ABSPATH' ) || exit ;

/**
 * Transactions.
 * 
 * @class WC_CS_Meta_Box_Transactions
 * @package Class
 */
class WC_CS_Meta_Box_Transactions {

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

		include_once(WC_CS_DIR . 'includes/admin/wp-list-table/class-wc-cs-txns-list-table.php') ;
		$txns_table_list = new WC_CS_Txns_List_Table() ;
		$txns_table_list->output() ;
	}

}
