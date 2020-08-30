<?php

defined( 'ABSPATH' ) || exit ;

if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' ) ;
}

/**
 * List of Unbilled/Billed Transactions 
 * 
 * @class WC_CS_Txns_List_Table
 * @package Class
 */
class WC_CS_Txns_List_Table extends WP_List_Table {

	/**
	 * The total list of items.
	 *
	 * @var array
	 */
	private $total_items ;

	/**
	 * The current list of items per page.
	 *
	 * @var int
	 */
	private $perpage = 5 ;

	/**
	 * Offset
	 * 
	 * @var int
	 */
	private $offset ;

	/**
	 * Are we going to use post type items
	 * 
	 * @var string
	 */
	private $post_type = 'wc_cs_credits_txn' ;

	/**
	 * Current status to view on page load.
	 * 
	 * @var string
	 */
	private $current_status = WC_CS_PREFIX . 'unbilled' ;

	/**
	 * Points to the WPBD reference.
	 * 
	 * @var WPDB 
	 */
	protected $wpdb_ref ;

	/**
	 * Initialize the table.
	 */
	public function __construct() {
		global $wpdb ;

		$this->wpdb_ref = &$wpdb ;
		parent::__construct(
				array(
					'singular' => 'transaction',
					'plural'   => 'transactions',
					'ajax'     => false,
				)
		) ;
	}

	/**
	 * No items found text.
	 */
	public function no_items() {
		esc_html_e( 'No transactions found.', 'credits-for-woocommerce' ) ;
	}

	/**
	 * Get a list of columns. The format is:
	 * 'internal-name' => 'Title'
	 *
	 * @return array
	 */
	public function get_columns() {
		$columns = array(
			'activity' => esc_html__( 'Activity', 'credits-for-woocommerce' ),
			'credit'   => esc_html__( 'Credit', 'credits-for-woocommerce' ),
			'debit'    => esc_html__( 'Debit', 'credits-for-woocommerce' ),
			'date'     => esc_html__( 'Date', 'credits-for-woocommerce' ),
				) ;

		return $columns ;
	}

	/**
	 * Get a list of sortable columns. The format is:
	 * 'internal-name' => 'orderby'
	 * or
	 * 'internal-name' => array( 'orderby', true )
	 *
	 * The second format will make the initial sorting order to be descending
	 *
	 * @return array
	 */
	protected function get_sortable_columns() {
		return array(
			'credit' => array( 'credit', true ),
			'debit'  => array( 'debit', true ),
			'date'   => array( 'date', true ),
				) ;
	}

	/**
	 * Get column value.
	 *
	 * @param int $id WC_CS_Credits_Transaction post ID.
	 * @param string  $column_name Column name.
	 * @return string
	 */
	protected function column_default( $id, $column_name ) {
		$credits_txn = _wc_cs_get_credits_txn( $id ) ;

		switch ( $column_name ) {
			case 'activity':
				echo wp_kses_post( $credits_txn->get_activity() ) ;
				break ;
			case 'credit':
				echo wp_kses_post( $credits_txn->get_credited() ) ;
				break ;
			case 'debit':
				echo wp_kses_post( $credits_txn->get_debited() ) ;
				break ;
			case 'date':
				echo wp_kses_post( _wc_cs_format_datetime( $credits_txn->get_date_created() ) ) ;
				break ;
		}
	}

	/**
	 * Get the list of views available on this table. The format is:
	 * 'id' => link
	 *
	 * @return array
	 */
	protected function get_views() {
		global $thecredits ;

		$status_links = array() ;
		foreach ( _wc_cs_get_credits_txn_statuses() as $status_name => $status_label ) {
			$status_count = $this->wpdb_ref->get_var( $this->wpdb_ref->prepare( "SELECT COUNT(DISTINCT ID) FROM {$this->wpdb_ref->posts} WHERE post_type=%s AND post_author=%s AND post_parent=%s AND post_status=%s", esc_sql( $this->post_type ), esc_sql( $thecredits->get_user_id() ), esc_sql( $thecredits->get_id() ), esc_sql( $status_name ) ) ) ;

			if ( ! $status_count ) {
				if ( WC_CS_PREFIX . 'unbilled' === $status_name ) {
					$this->current_status = WC_CS_PREFIX . 'billed' ;
				}
			}

			if ( isset( $_REQUEST[ 'status' ] ) ) {
				$this->current_status = '' ;

				if ( sanitize_key( wp_unslash( $_REQUEST[ 'status' ] ) ) === $status_name ) {
					$this->current_status = $status_name ;
				}
			}

			$class = '' ;
			if ( $status_name === $this->current_status ) {
				$class = ' class="current"' ;
			}

			$label = array(
				'singular' => sprintf( '%s <span class="count">(%s)</span>', esc_html( $status_label ), $status_count ),
				'plural'   => sprintf( '%s <span class="count">(%s)</span>', esc_html( $status_label ), $status_count ),
				'context'  => '',
				'domain'   => 'credits-for-woocommerce',
					) ;

			$status_links[ $status_name ] = sprintf( "<a href='%s' %s>%s</a>", admin_url( "post.php?post={$thecredits->get_id()}&action=edit&status={$status_name}" ), $class, translate_nooped_plural( $label, $status_count ) ) ;
		}

		return $status_links ;
	}

	/**
	 * Prepare the current list of items.
	 * 
	 * @return array
	 */
	private function prepare_current_page_items() {
		$join              = $this->get_join() ;
		$where             = $this->get_where() ;
		$orderby           = $this->get_orderby() ;
		$this->total_items = $this->wpdb_ref->get_var( $this->wpdb_ref->prepare( "SELECT COUNT(DISTINCT ID) FROM {$this->wpdb_ref->posts} AS p $join WHERE 1=%d $where", '1' ) ) ;
		$this->items       = $this->wpdb_ref->get_col( $this->wpdb_ref->prepare( "SELECT DISTINCT ID FROM {$this->wpdb_ref->posts} AS p $join WHERE 1=1 $where $orderby LIMIT %d,%d", $this->offset, $this->perpage ) ) ;
	}

	/**
	 * Get the JOIN clause of the query.
	 */
	private function get_join() {
		$join = '' ;
		if ( ! empty( $_REQUEST[ 's' ] ) || ! empty( $_REQUEST[ 'orderby' ] ) ) {
			$join = " INNER JOIN {$this->wpdb_ref->postmeta} pm ON ( pm.post_id = p.ID )" ;
		}

		return $join ;
	}

	/**
	 * Get the WHERE clause of the query.
	 */
	private function get_where() {
		global $thecredits ;

		$where  = '' ;
		$status = "('" . sanitize_title( $this->current_status ) . "')" ;

		if ( ! empty( $_REQUEST[ 'status' ] ) ) {
			$status = "('" . sanitize_title( $_REQUEST[ 'status' ] ) . "')" ;
		}

		$where .= " AND post_type='{$this->post_type}' AND post_author='{$thecredits->get_user_id()}' AND post_parent='{$thecredits->get_id()}' AND post_status IN $status" ;
		return $where ;
	}

	/**
	 * Get the ORDER BY clause of the query.
	 */
	private function get_orderby() {
		$order = 'DESC' ;
		if ( ! empty( $_REQUEST[ 'order' ] ) && is_string( $_REQUEST[ 'order' ] ) ) {
			if ( 'ASC' === strtoupper( sanitize_title( wp_unslash( $_REQUEST[ 'order' ] ) ) ) ) {
				$order = 'ASC' ;
			}
		}

		switch ( ! empty( $_REQUEST[ 'orderby' ] ) ? sanitize_title( wp_unslash( $_REQUEST[ 'orderby' ] ) ) : 'ID' ) {
			case 'credit':
				$orderby = " AND pm.meta_key='_credited' ORDER BY pm.meta_value $order" ;
				break ;
			case 'debit':
				$orderby = " AND pm.meta_key='_debited' ORDER BY pm.meta_value $order" ;
				break ;
			case 'date':
				$orderby = " ORDER BY post_date $order" ;
				break ;
			default:
				$orderby = " ORDER BY ID $order" ;
		}

		return $orderby ;
	}

	/**
	 * Prepare list items.
	 */
	public function prepare_items() {
		/**
		 * Get the current page number
		 */
		$current_page = absint( $this->get_pagenum() ) ;

		/**
		 * Offset the list of items
		 */
		$this->offset = ( $current_page - 1 ) * $this->perpage ;

		/**
		 * Init column headers.
		 */
		$this->_column_headers = array( $this->get_columns(), array(), $this->get_sortable_columns() ) ;

		/**
		 * Get the current list of items.
		 */
		$this->prepare_current_page_items() ;

		/**
		 * Sets all the necessary pagination arguments
		 */
		$this->set_pagination_args( array(
			'total_items' => $this->total_items,
			'per_page'    => $this->perpage,
		) ) ;
	}

	/**
	 * Output the list of table.
	 */
	public function output() {
		echo '<form method="post" id="wc_cs_credits_transactions">' ;

		$this->views() ;
		$this->prepare_items() ;
		$this->search_box( __( 'Search transactions', 'credits-for-woocommerce' ), 'txn_search' ) ;
		$this->display() ;

		echo '</form>' ;
	}

}
