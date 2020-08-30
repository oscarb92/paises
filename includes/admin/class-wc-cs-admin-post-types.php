<?php
defined( 'ABSPATH' ) || exit ;

/**
 * Post Types Admin
 * 
 * @class WC_CS_Admin_Post_Types
 * @package Class
 */
class WC_CS_Admin_Post_Types {

	/**
	 * Get our post types.
	 * 
	 * @var array 
	 */
	protected static $custom_post_types = array(
		'wc_cs_credits'        => 'credits',
		'wc_cs_adminfunds_txn' => 'admin_funds_txn',
		'wc_cs_vrtualfundstxn' => 'virtual_funds_txn',
		'wc_cs_scheduled_jobs' => 'scheduled_jobs',
			) ;

	/**
	 * Init WC_CS_Admin_Post_Types.
	 */
	public static function init() {

		add_action( 'admin_notices', __CLASS__ . '::output_notices', 99999 ) ;

		foreach ( self::$custom_post_types as $post_type => $meant_for ) {
			add_filter( "manage_{$post_type}_posts_columns", __CLASS__ . "::define_{$meant_for}_columns" ) ;
			add_filter( "manage_edit-{$post_type}_sortable_columns", __CLASS__ . '::define_sortable_columns' ) ;
			add_filter( "bulk_actions-edit-{$post_type}", __CLASS__ . '::define_bulk_actions' ) ;
			add_action( "manage_{$post_type}_posts_custom_column", __CLASS__ . "::render_{$meant_for}_columns", 10, 2 ) ;
		}

		add_filter( 'post_row_actions', __CLASS__ . '::row_actions', 99, 2 ) ;
		add_filter( 'request', __CLASS__ . '::request_query' ) ;
		add_action( 'admin_init', __CLASS__ . '::admin_action' ) ;
		add_filter( 'get_search_query', __CLASS__ . '::search_label' ) ;
		add_filter( 'query_vars', __CLASS__ . '::add_custom_query_var' ) ;
		add_action( 'parse_query', __CLASS__ . '::search_custom_fields' ) ;
		add_action( 'restrict_manage_posts', __CLASS__ . '::restrict_manage_posts' ) ;
	}

	/**
	 * Render our admin notices.
	 */
	public static function output_notices() {
		global $post_type ;

		switch ( $post_type ) {
			case 'wc_cs_adminfunds_txn':
				/* translators: 1: class name 2: label 3: available funds */
				printf( '<div id="message" class="%1$s notice"><p><strong>%2$s</strong></p><p>%3$s</p></div>', esc_attr(  ! WC_CS_Admin_Funds::get_available_funds( 'edit' ) ) ? 'error' : 'updated', esc_attr__( 'Available Funds', 'credits-for-woocommerce' ), wp_kses_post( WC_CS_Admin_Funds::get_available_funds() ) ) ;
				break ;
			case 'wc_cs_vrtualfundstxn':
				//                printf( '<div id="message" class="updated notice"><p><strong>%2$s</strong></p><p>%3$s</p></div>' ) ;
				break ;
			case 'wc_cs_scheduled_jobs':
				if ( isset( $_GET[ 'wc-cs-notice' ] ) ) {
					switch ( $_GET[ 'wc-cs-notice' ] ) {
						case 'job-ran-success':
							if ( isset( $_GET[ 'job-name' ] ) ) {
								echo '<div id="message" class="updated is-dismissible"><p>' . esc_html__( 'Successfully executed: ', 'credits-for-woocommerce' ) . '<code>' . esc_html( sanitize_text_field( wp_unslash( $_GET[ 'job-name' ] ) ) ) . '</code></p></div>' ;
							}
							break ;
						case 'job-ran-failed':
							if ( isset( $_GET[ 'job-name' ] ) ) {
								echo '<div id="message" class="error is-dismissible"><p>' . esc_html__( 'Failed to execute: ', 'credits-for-woocommerce' ) . '<code>' . esc_html( sanitize_text_field( wp_unslash( $_GET[ 'job-name' ] ) ) ) . '</code></p></div>' ;
							}
							break ;
					}
				}
				break ;
		}
	}

	/**
	 * Define which credits columns to show on this screen.
	 *
	 * @param array $columns Existing columns.
	 * @return array
	 */
	public static function define_credits_columns( $columns ) {
		$columns = array(
			'cb'                       => $columns[ 'cb' ],
			'user'                     => __( 'User', 'credits-for-woocommerce' ),
			'status'                   => __( 'Status', 'credits-for-woocommerce' ),
			'approved_credits'         => __( 'Approved Credits', 'credits-for-woocommerce' ),
			'total_outstanding_amount' => __( 'Total Outstanding', 'credits-for-woocommerce' ),
			'available_credits'        => __( 'Available Credits', 'credits-for-woocommerce' ),
			'actions'                  => __( 'Actions', 'credits-for-woocommerce' ),
				) ;
		return $columns ;
	}

	/**
	 * Define which admin funds transaction columns to show on this screen.
	 *
	 * @param array $columns Existing columns.
	 * @return array
	 */
	public static function define_admin_funds_txn_columns( $columns ) {
		$columns = array(
			'cb'         => $columns[ 'cb' ],
			'activity'   => __( 'Activity', 'credits-for-woocommerce' ),
			'user_email' => __( 'User Email', 'credits-for-woocommerce' ),
			'credit'     => __( 'Credit', 'credits-for-woocommerce' ),
			'debit'      => __( 'Debit', 'credits-for-woocommerce' ),
			'balance'    => __( 'Balance', 'credits-for-woocommerce' ),
			'date'       => __( 'Date', 'credits-for-woocommerce' ),
				) ;
		return $columns ;
	}

	/**
	 * Define which virtual funds transaction columns to show on this screen.
	 *
	 * @param array $columns Existing columns.
	 * @return array
	 */
	public static function define_virtual_funds_txn_columns( $columns ) {
		$columns = array(
			'cb'         => $columns[ 'cb' ],
			'activity'   => __( 'Activity', 'credits-for-woocommerce' ),
			'user_email' => __( 'User Email', 'credits-for-woocommerce' ),
			'credit'     => __( 'Credit', 'credits-for-woocommerce' ),
			'balance'    => __( 'Balance', 'credits-for-woocommerce' ),
			'date'       => __( 'Date', 'credits-for-woocommerce' )
				) ;
		return $columns ;
	}

	/**
	 * Define which scheduled job columns to show on this screen.
	 *
	 * @param array $columns Existing columns.
	 * @return array
	 */
	public static function define_scheduled_jobs_columns( $columns ) {
		$columns = array(
			'cb'         => $columns[ 'cb' ],
			'job'        => __( 'Job', 'credits-for-woocommerce' ),
			'next_run'   => __( 'Next Run', 'credits-for-woocommerce' ),
			'recurrence' => __( 'Recurrence', 'credits-for-woocommerce' ),
			'args'       => __( 'Arguments', 'credits-for-woocommerce' ),
			'group'      => __( 'Group', 'credits-for-woocommerce' ),
			'relation'   => __( 'Relation', 'credits-for-woocommerce' ),
				) ;
		return $columns ;
	}

	/**
	 * Define which columns are sortable.
	 *
	 * @param array $columns Existing columns.
	 * @return array
	 */
	public static function define_sortable_columns( $columns ) {
		global $current_screen ;

		if ( ! isset( $current_screen->post_type ) ) {
			return $columns ;
		}

		$columns = array() ;
		switch ( $current_screen->post_type ) {
			case 'wc_cs_adminfunds_txn':
				$columns = array(
					'user_email' => 'user_email',
					'credit'     => 'credit',
					'debit'      => 'debit',
					'balance'    => 'balance',
					'date'       => 'date',
						) ;
				break ;
			case 'wc_cs_vrtualfundstxn':
				$columns = array(
					'user_email' => 'user_email',
					'credit'     => 'credit',
					'balance'    => 'balance',
					'date'       => 'date',
						) ;
				break ;
			case 'wc_cs_credits':
				$columns = array(
					'user'              => 'user_email',
					'approved_credits'  => 'approved_credits',
					'available_credits' => 'available_credits',
						) ;
				break ;
			case 'wc_cs_scheduled_jobs':
				$columns = array(
					'job'      => 'post_title',
					'next_run' => 'next_run',
					'group'    => 'post_excerpt',
						) ;
				break ;
		}

		return wp_parse_args( $columns, $columns ) ;
	}

	/**
	 * Define bulk actions.
	 *
	 * @param array $actions Existing actions.
	 * @return array
	 */
	public static function define_bulk_actions( $actions ) {
		global $current_screen ;

		switch ( $current_screen->post_type ) {
			case 'wc_cs_adminfunds_txn':
			case 'wc_cs_vrtualfundstxn':
			case 'wc_cs_credits':
			case 'wc_cs_scheduled_jobs':
				$actions             = array() ;
				$actions[ 'delete' ] = __( 'Delete' ) ;
				break ;
		}

		return $actions ;
	}

	/**
	 * Render individual credits columns.
	 *
	 * @param string $column Column ID to render.
	 * @param int    $post_id Post ID.
	 */
	public static function render_credits_columns( $column, $post_id ) {
		$credits = _wc_cs_get_credits( $post_id ) ;

		switch ( $column ) {
			case 'user':
				echo wp_kses_post( $credits->get_user_details_html() ) ;
				break ;
			case 'status':
				echo esc_html( _wc_cs_get_credits_status_name( $credits->get_status() ) ) ;
				break ;
			case 'approved_credits':
				echo wp_kses_post( $credits->get_approved_credits() ) ;
				break ;
			case 'total_outstanding_amount':
				echo wp_kses_post( ( ( '' !== $credits->get_last_billed_status() || $credits->get_total_outstanding_amount( 'edit' ) > 0 ) ? $credits->get_total_outstanding_amount() : '-' ) ) ;
				break ;
			case 'available_credits':
				echo wp_kses_post( $credits->get_available_credits() ) ;
				break ;
			case 'actions':
				printf( '<a href="%1$s">%2$s</a>', esc_url( admin_url( "post.php?post={$credits->get_id()}&action=edit" ) ), esc_html__( 'View more', 'credits-for-woocommerce' ) ) ;
				break ;
		}
	}

	/**
	 * Render individual admin funds transaction columns.
	 *
	 * @param string $column Column ID to render.
	 * @param int    $post_id Post ID.
	 */
	public static function render_admin_funds_txn_columns( $column, $post_id ) {
		$funds_txn = new WC_CS_Admin_Funds_Transaction( $post_id ) ;

		switch ( $column ) {
			case 'activity':
				echo wp_kses_post( $funds_txn->get_activity() ) ;
				break ;
			case 'user_email':
				echo '' === $funds_txn->get_user_email() ? '--' : wp_kses_post( $funds_txn->get_user_email() ) ;
				break ;
			case 'credit':
				echo wp_kses_post( $funds_txn->get_credited() ) ;
				break ;
			case 'debit':
				echo wp_kses_post( $funds_txn->get_debited() ) ;
				break ;
			case 'balance':
				echo wp_kses_post( $funds_txn->get_balance() ) ;
				break ;
		}
	}

	/**
	 * Render individual virtual funds transaction columns.
	 *
	 * @param string $column Column ID to render.
	 * @param int    $post_id Post ID.
	 */
	public static function render_virtual_funds_txn_columns( $column, $post_id ) {
		$funds_txn = new WC_CS_Virtual_Funds_Transaction( $post_id ) ;

		switch ( $column ) {
			case 'activity':
				echo wp_kses_post( $funds_txn->get_activity() ) ;
				break ;
			case 'user_email':
				echo '' === $funds_txn->get_user_email() ? '--' : wp_kses_post( $funds_txn->get_user_email() ) ;
				break ;
			case 'credit':
				echo wp_kses_post( $funds_txn->get_credited() ) ;
				break ;
			case 'balance':
				echo wp_kses_post( $funds_txn->get_balance() ) ;
				break ;
		}
	}

	/**
	 * Render individual scheduled job columns.
	 *
	 * @param string $column Column ID to render.
	 * @param int    $post_id Post ID.
	 */
	public static function render_scheduled_jobs_columns( $column, $post_id ) {
		$job      = get_post( $post_id ) ;
		$job_meta = get_post_meta( $post_id ) ;

		switch ( $column ) {
			case 'job':
				echo esc_html( $job->post_title ) ;
				break ;
			case 'next_run':
				$scheduled_on  = isset( $job_meta[ '_scheduled_on' ][ 0 ] ) ? $job_meta[ '_scheduled_on' ][ 0 ] : '' ;
				echo empty( $scheduled_on ) ? '--' : wp_kses_post( _wc_cs_prepare_datetime( $scheduled_on ) . nl2br( "\n[" . _wc_cs_get_human_time_diff( $scheduled_on ) . ']' ) ) ;
				break ;
			case 'recurrence':
				$job_schedules = _wc_cs_get_recurring_job_schedules() ;
				echo isset( $job_schedules[ $job_meta[ '_recurrence' ][ 0 ] ] ) ? esc_html( $job_schedules[ $job_meta[ '_recurrence' ][ 0 ] ][ 'display' ] ) : 'Non-repeating' ;
				break ;
			case 'args':
				echo wp_kses_post( $job->post_content ) ;
				break ;
			case 'group':
				echo esc_html( $job->post_excerpt ) ;
				break ;
			case 'relation':
				if ( $job->post_parent ) {
					if ( 'wc_cs_credits' === get_post_type( $job->post_parent ) ) {
						printf( '<a href="%1$s" target="_blank">#%2$s</a> Parent of #%3$s', esc_url( admin_url( "post.php?post={$job->post_parent}&action=edit" ) ), esc_html( $job->post_parent ), esc_html( $job->ID ) ) ;
					} else {
						echo wp_kses_post( "#{$job->post_parent} Parent of #{$job->ID}" ) ;
					}
				} else {
					echo wp_kses_post( "#{$job->ID}" ) ;
				}
				break ;
		}
	}

	/**
	 * Set row actions.
	 *
	 * @param array   $actions Array of actions.
	 * @param WP_Post $post Current post object.
	 * @return array
	 */
	public static function row_actions( $actions, $post ) {
		switch ( $post->post_type ) {
			case 'wc_cs_adminfunds_txn':
			case 'wc_cs_vrtualfundstxn':
				$actions              = array() ;
				break ;
			case 'wc_cs_credits':
				$actions              = array() ;
				$actions[ 'delete' ]  = sprintf( '<a href="%s" class="submitdelete" aria-label="Delete">%s</a>', get_delete_post_link( $post->ID, '', true ), __( 'Delete' ) ) ;
				break ;
			case 'wc_cs_scheduled_jobs':
				$actions              = array() ;
				$actions[ 'run-job' ] = sprintf( '<a href="%s" aria-label="Run Now">%s</a>', wp_nonce_url( add_query_arg( array( 'action' => 'run-job', 'group' => $post->post_excerpt, 'parent' => $post->post_parent ), admin_url( "edit.php?post_type={$post->post_type}&job_id={$post->ID}" ) ), "run-job-{$post->post_excerpt}-{$post->ID}", WC_CS_PREFIX . 'nonce' ), __( 'Run Now' ) ) ;
				$actions[ 'delete' ]  = sprintf( '<a href="%s" class="submitdelete" aria-label="Delete Job">%s</a>', get_delete_post_link( $post->ID, '', true ), __( 'Delete' ) ) ;
				break ;
		}
		return $actions ;
	}

	/**
	 * Handle any filters.
	 *
	 * @param array $query_vars Query vars.
	 * @return array
	 */
	public static function request_query( $query_vars ) {
		global $typenow ;

		if ( ! in_array( $typenow, array_keys( self::$custom_post_types ) ) ) {
			return $query_vars ;
		}

		//Sorting
		if ( empty( $query_vars[ 'orderby' ] ) ) {
			$query_vars[ 'orderby' ] = 'ID' ;
		}

		if ( empty( $query_vars[ 'order' ] ) ) {
			$query_vars[ 'order' ] = 'DESC' ;
		}

		$meta_query_array = array() ;
		// Filter the credits by the posted credit line type.
		if ( ! empty( $_GET[ 'wc_cs_credit_line_type' ] ) ) {
			$meta_query_array[] = array(
				'key'     => '_type',
				'value'   => sanitize_title( wp_unslash( $_GET[ 'wc_cs_credit_line_type' ] ) ),
				'compare' => '=',
					) ;
		}

		// Filter the credits by the posted created via.
		if ( ! empty( $_GET[ 'wc_cs_created_via' ] ) ) {
			$meta_query_array[] = array(
				'key'     => '_created_via',
				'value'   => sanitize_title( wp_unslash( $_GET[ 'wc_cs_created_via' ] ) ),
				'compare' => '=',
					) ;
		}

		if ( ! empty( $meta_query_array ) ) {
			$query_vars[ 'meta_query' ] = array( $meta_query_array ) ;
		}

		if ( ! empty( $query_vars[ 'orderby' ] ) ) {
			switch ( $query_vars[ 'orderby' ] ) {
				case 'next_run':
					$query_vars[ 'meta_key' ]  = '_scheduled_on' ;
					$query_vars[ 'meta_type' ] = 'DATETIME' ;
					$query_vars[ 'orderby' ]   = 'meta_value' ;
					break ;
				case 'user_email':
					$query_vars[ 'meta_key' ]  = "_{$query_vars[ 'orderby' ]}" ;
					$query_vars[ 'orderby' ]   = 'meta_value' ;
					break ;
				case 'credit':
				case 'debit':
				case 'balance':
				case 'approved_credits':
				case 'available_credits':
					$query_vars[ 'meta_key' ]  = "_{$query_vars[ 'orderby' ]}" ;
					$query_vars[ 'orderby' ]   = 'meta_value_num' ;
					break ;
			}
		}

		return $query_vars ;
	}

	/**
	 * Fire our actions perfomed in admin screen.
	 */
	public static function admin_action() {
		if ( ! isset( $_GET[ 'action' ] ) || ! isset( $_GET[ WC_CS_PREFIX . 'nonce' ] ) ) {
			return ;
		}

		$action = sanitize_title( wp_unslash( $_GET[ 'action' ] ) ) ;
		$nonce  = sanitize_key( wp_unslash( $_GET[ WC_CS_PREFIX . 'nonce' ] ) ) ;

		if ( 'run-job' === $action ) {
			if ( ! isset( $_GET[ 'job_id' ] ) || ! isset( $_GET[ 'group' ] ) || ! isset( $_GET[ 'post_type' ] ) ) {
				return ;
			}

			$job_id    = absint( wp_unslash( $_GET[ 'job_id' ] ) ) ;
			$group     = sanitize_title( wp_unslash( $_GET[ 'group' ] ) ) ;
			$post_type = sanitize_title( wp_unslash( $_GET[ 'post_type' ] ) ) ;

			if ( ! wp_verify_nonce( $nonce, "{$action}-{$group}-{$job_id}" ) ) {
				return ;
			}

			$queue_group = _wc_cs_get_queue( $group ) ;

			if ( ! $queue_group ) {
				return ;
			}

			$queue = $queue_group->get_queue() ;

			if ( empty( $queue ) ) {
				return ;
			}

			$item   = $queue_group->prepare_queue_item( $job_id ) ;
			$status = 'failed' ;

			if ( $queue_group->is_queue_valid( $item ) ) {
				$queue_group->handle( $item ) ;
				$status = 'success' ;
			}

			wp_safe_redirect( esc_url_raw( admin_url( "edit.php?post_type={$post_type}&wc-cs-notice=job-ran-{$status}&job-name={$item->name}" ) ) ) ;
			exit ;
		}

		if ( 'view-statement' === $action ) {
			if ( ! wp_verify_nonce( $nonce, 'wc-cs-view-statement' ) ) {
				return ;
			}

			if ( ! isset( $_GET[ 'post' ] ) || ! isset( $_GET[ 'statement-key' ] ) || empty( $_GET[ 'statement-key' ] ) ) {
				return ;
			}

			$credits = _wc_cs_get_credits( absint( wp_unslash( $_GET[ 'post' ] ) ) ) ;

			if ( $credits ) {
				$html = new WC_CS_Bill_Statement_HTML( null, sanitize_key( wp_unslash( $_GET[ 'statement-key' ] ) ), $credits ) ;
				$html->set_logoAttachment( get_option( WC_CS_PREFIX . 'get_header_logo_attachment' ) ) ;
				$html->generate() ;
			}
		}
	}

	/**
	 * Change the label when searching index.
	 *
	 * @param mixed $query Current search query.
	 * @return string
	 */
	public static function search_label( $query ) {
		global $pagenow, $typenow ;

		if ( 'edit.php' !== $pagenow || ! in_array( $typenow, array_keys( self::$custom_post_types ) ) || ! get_query_var( "{$typenow}_search" ) || ! isset( $_GET[ 's' ] ) ) { // WPCS: input var ok.
			return $query ;
		}

		return wc_clean( wp_unslash( $_GET[ 's' ] ) ) ; // WPCS: input var ok, sanitization ok.
	}

	/**
	 * Query vars for custom searches.
	 *
	 * @param mixed $public_query_vars Array of query vars.
	 * @return array
	 */
	public static function add_custom_query_var( $public_query_vars ) {
		return array_merge( $public_query_vars, array_map( function( $type ) {
					return "{$type}_search" ;
		}, array_keys( self::$custom_post_types ) ) ) ;
	}

	/**
	 * Search custom fields as well as content.
	 *
	 * @param WP_Query $wp Query object.
	 */
	public static function search_custom_fields( $wp ) {
		global $pagenow, $wpdb ;

		if ( 'edit.php' !== $pagenow || empty( $wp->query_vars[ 's' ] ) || ! in_array( $wp->query_vars[ 'post_type' ], array_keys( self::$custom_post_types ) ) || ! isset( $_GET[ 's' ] ) ) { // WPCS: input var ok.
			return ;
		}

		$wpdb_ref      = &$wpdb ;
		$term          = str_replace( '#', '', wc_clean( wp_unslash( $wp->query_vars[ 's' ] ) ) ) ;
		$post_ids      = array() ;
		$search_fields = array() ;

		switch ( $wp->query_vars[ 'post_type' ] ) {
			case 'wc_cs_credits':
				$search_fields = array(
					'_last_payment_order_id',
					'_due_duration_by',
					'_last_billed_status',
					'_billing_day',
					'_due_day',
					'_user_address_index'
						) ;
				break ;
			case 'wc_cs_adminfunds_txn':
				$search_fields = array(
					'_user_email',
					'_order_id'
						) ;
				break ;
			case 'wc_cs_vrtualfundstxn':
				$search_fields = array(
					'_user_email'
						) ;
				break ;
		}

		if ( empty( $search_fields ) ) {
			return ;
		}

		if ( is_numeric( $term ) ) {
			$post_ids = array_unique(
					array_merge( array( absint( $term ) ), $wpdb_ref->get_col(
									$wpdb_ref->prepare(
											"SELECT DISTINCT p1.post_id FROM {$wpdb_ref->postmeta} p1 WHERE p1.meta_value LIKE %s AND p1.meta_key IN ('" . implode( "','", array_map( 'esc_sql', $search_fields ) ) . "')", '%' . $wpdb_ref->esc_like( wc_clean( $term ) ) . '%'
									)
							)
					) ) ;
		} else {
			$post_ids = array_unique(
					array_merge(
							$post_ids, $wpdb_ref->get_col(
									$wpdb_ref->prepare(
											"SELECT DISTINCT p1.post_id FROM {$wpdb_ref->postmeta} p1 WHERE p1.meta_value LIKE %s AND p1.meta_key IN ('" . implode( "','", array_map( 'esc_sql', $search_fields ) ) . "')", '%' . $wpdb_ref->esc_like( wc_clean( $term ) ) . '%'
									)
							)
					) ) ;
		}

		if ( ! empty( $post_ids ) ) {
			// Remove "s" - we don't want to search our post name.
			unset( $wp->query_vars[ 's' ] ) ;

			// so we know we're doing this.
			$wp->query_vars[ "{$wp->query_vars[ 'post_type' ]}_search" ] = true ;

			// Search by found posts.
			$wp->query_vars[ 'post__in' ] = array_merge( $post_ids, array( 0 ) ) ;
		}
	}

	/**
	 * See if we should render search filters or not.
	 */
	public static function restrict_manage_posts() {
		global $typenow ;

		if ( 'wc_cs_credits' === $typenow ) {
			$credit_line_type_selected = ! empty( $_GET[ 'wc_cs_credit_line_type' ] ) ? sanitize_title( wp_unslash( $_GET[ 'wc_cs_credit_line_type' ] ) ) : '' ;
			$created_via_selected      = ! empty( $_GET[ 'wc_cs_created_via' ] ) ? sanitize_title( wp_unslash( $_GET[ 'wc_cs_created_via' ] ) ) : '' ;
			?>
			<select name="wc_cs_credit_line_type">
				<option value=""><?php esc_html_e( 'Automatic/Manual Credit Line Users', 'credits-for-woocommerce' ) ; ?></option>
				<option value="auto" <?php selected( $credit_line_type_selected, 'auto' ) ; ?>><?php esc_html_e( 'Automatic Credit Line Users', 'credits-for-woocommerce' ) ; ?></option>
				<option value="manual" <?php selected( $credit_line_type_selected, 'manual' ) ; ?>><?php esc_html_e( 'Manual Credit Line Users', 'credits-for-woocommerce' ) ; ?></option>
			</select>
			<select name="wc_cs_created_via">
				<option value=""><?php esc_html_e( 'Created via Application/Rule', 'credits-for-woocommerce' ) ; ?></option>
				<option value="app" <?php selected( $created_via_selected, 'app' ) ; ?>><?php esc_html_e( 'Created via Application', 'credits-for-woocommerce' ) ; ?></option>
				<option value="rule" <?php selected( $created_via_selected, 'rule' ) ; ?>><?php esc_html_e( 'Created via Rule', 'credits-for-woocommerce' ) ; ?></option>
			</select>            
			<?php
		}
	}

}

WC_CS_Admin_Post_Types::init() ;
