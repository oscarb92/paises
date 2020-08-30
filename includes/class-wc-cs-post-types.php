<?php

defined( 'ABSPATH' ) || exit ;

/**
 * Post Types
 * 
 * Registers post types
 * 
 * @class WC_CS_Post_Types
 * @package Class
 */
class WC_CS_Post_Types {

	/**
	 * Init WC_CS_Post_Types.
	 */
	public static function init() {
		add_action( 'init', __CLASS__ . '::register_post_types' ) ;
		add_action( 'init', __CLASS__ . '::register_post_status' ) ;
	}

	/**
	 * Register our custom post types.
	 */
	public static function register_post_types() {

		register_post_type( 'wc_cs_credits', array(
			'labels'          => array(
				'name'               => __( 'Credits', 'credits-for-woocommerce' ),
				'singular_name'      => _x( 'Credits', 'wc_cs_credits post type singular name', 'credits-for-woocommerce' ),
				'menu_name'          => _x( 'Credits', 'Admin menu name', 'credits-for-woocommerce' ),
				'add_new'            => __( 'Add credits', 'credits-for-woocommerce' ),
				'add_new_item'       => __( 'Add new credits', 'credits-for-woocommerce' ),
				'new_item'           => __( 'New credits', 'credits-for-woocommerce' ),
				'edit_item'          => __( 'Edit credits', 'credits-for-woocommerce' ),
				'view_item'          => __( 'View credits', 'credits-for-woocommerce' ),
				'search_items'       => __( 'Search credits', 'credits-for-woocommerce' ),
				'not_found'          => __( 'No credits found.', 'credits-for-woocommerce' ),
				'not_found_in_trash' => __( 'No credits found in Trash.', 'credits-for-woocommerce' )
			),
			'description'     => __( 'This is where store credits are stored.', 'credits-for-woocommerce' ),
			'public'          => false,
			'show_ui'         => true,
			'capability_type' => 'wc_cs_credits',
			'show_in_menu'    => 'credits-for-woocommerce',
			'rewrite'         => false,
			'has_archive'     => false,
			'supports'        => false,
			'capabilities'    => array(
				'edit_post'          => 'manage_woocommerce',
				'edit_posts'         => 'manage_woocommerce',
				'edit_others_posts'  => 'manage_woocommerce',
				'publish_posts'      => 'manage_woocommerce',
				'read_post'          => 'manage_woocommerce',
				'read_private_posts' => 'manage_woocommerce',
				'delete_post'        => 'manage_woocommerce',
				'delete_posts'       => true,
				'create_posts'       => 'do_not_allow'
			)
		) ) ;

		register_post_type( 'wc_cs_credits_txn', array(
			'labels'       => array(
				'name'      => __( 'Credits Transactions', 'credits-for-woocommerce' ),
				'menu_name' => _x( 'Credits Transactions', 'Admin menu name', 'credits-for-woocommerce' ),
			),
			'description'  => __( 'This is where store credits transactions are stored.', 'credits-for-woocommerce' ),
			'public'       => false,
			'show_ui'      => false,
			'show_in_menu' => false,
			'rewrite'      => false,
			'has_archive'  => false,
			'supports'     => false,
			'capabilities' => array(
				'edit_post'          => 'manage_woocommerce',
				'edit_posts'         => 'manage_woocommerce',
				'edit_others_posts'  => 'manage_woocommerce',
				'publish_posts'      => 'manage_woocommerce',
				'read_post'          => 'manage_woocommerce',
				'read_private_posts' => 'manage_woocommerce',
				'delete_post'        => 'manage_woocommerce',
				'delete_posts'       => true,
				'create_posts'       => 'do_not_allow'
			),
		) ) ;

		register_post_type( 'wc_cs_bill_statement', array(
			'labels'       => array(
				'name'      => __( 'Billing Statement', 'credits-for-woocommerce' ),
				'menu_name' => _x( 'Billing Statements', 'Admin menu name', 'credits-for-woocommerce' ),
			),
			'description'  => __( 'This is where store billing statements are stored.', 'credits-for-woocommerce' ),
			'public'       => false,
			'show_ui'      => false,
			'show_in_menu' => false,
			'rewrite'      => false,
			'has_archive'  => false,
			'supports'     => false,
			'capabilities' => array(
				'edit_post'          => 'manage_woocommerce',
				'edit_posts'         => 'manage_woocommerce',
				'edit_others_posts'  => 'manage_woocommerce',
				'publish_posts'      => 'manage_woocommerce',
				'read_post'          => 'manage_woocommerce',
				'read_private_posts' => 'manage_woocommerce',
				'delete_post'        => 'manage_woocommerce',
				'delete_posts'       => true,
				'create_posts'       => 'do_not_allow'
			),
		) ) ;

		register_post_type( 'wc_cs_adminfunds_txn', array(
			'labels'       => array(
				'name'               => __( 'Admin Funds', 'credits-for-woocommerce' ),
				'menu_name'          => _x( 'Admin Funds', 'Admin menu name', 'credits-for-woocommerce' ),
				'search_items'       => __( 'Search admin funds', 'credits-for-woocommerce' ),
				'not_found'          => __( 'No admin funds found.', 'credits-for-woocommerce' ),
				'not_found_in_trash' => __( 'No admin funds found in Trash.', 'credits-for-woocommerce' )
			),
			'description'  => __( 'This is where admin funds transactions are stored.', 'credits-for-woocommerce' ),
			'public'       => false,
			'show_ui'      => _wc_cs()->funding_via_real_money() ? true : false,
			'show_in_menu' => 'credits-for-woocommerce',
			'rewrite'      => false,
			'has_archive'  => false,
			'supports'     => false,
			'capabilities' => array(
				'edit_post'          => 'manage_woocommerce',
				'edit_posts'         => 'manage_woocommerce',
				'edit_others_posts'  => 'manage_woocommerce',
				'publish_posts'      => 'manage_woocommerce',
				'read_post'          => 'manage_woocommerce',
				'read_private_posts' => 'manage_woocommerce',
				'delete_post'        => 'manage_woocommerce',
				'delete_posts'       => true,
				'create_posts'       => 'do_not_allow'
			),
		) ) ;

		register_post_type( 'wc_cs_vrtualfundstxn', array(
			'labels'       => array(
				'name'               => __( 'Virtual Funds', 'credits-for-woocommerce' ),
				'menu_name'          => _x( 'Virtual Funds', 'Admin menu name', 'credits-for-woocommerce' ),
				'search_items'       => __( 'Search virtual funds', 'credits-for-woocommerce' ),
				'not_found'          => __( 'No virtual funds found.', 'credits-for-woocommerce' ),
				'not_found_in_trash' => __( 'No virtual funds found in Trash.', 'credits-for-woocommerce' )
			),
			'description'  => __( 'This is where virtual funds transactions are stored.', 'credits-for-woocommerce' ),
			'public'       => false,
			'show_ui'      => _wc_cs()->funding_via_real_money() ? false : true,
			'show_in_menu' => 'credits-for-woocommerce',
			'rewrite'      => false,
			'has_archive'  => false,
			'supports'     => false,
			'capabilities' => array(
				'edit_post'          => 'manage_woocommerce',
				'edit_posts'         => 'manage_woocommerce',
				'edit_others_posts'  => 'manage_woocommerce',
				'publish_posts'      => 'manage_woocommerce',
				'read_post'          => 'manage_woocommerce',
				'read_private_posts' => 'manage_woocommerce',
				'delete_post'        => 'manage_woocommerce',
				'delete_posts'       => true,
				'create_posts'       => 'do_not_allow'
			),
		) ) ;

		register_post_type( 'wc_cs_scheduled_jobs', array(
			'labels'              => array(
				'name'         => __( 'Scheduled Jobs', 'credits-for-woocommerce' ),
				'menu_name'    => _x( 'Scheduled Jobs', 'Admin menu name', 'credits-for-woocommerce' ),
				'search_items' => __( 'Search scheduled jobs', 'credits-for-woocommerce' ),
				'not_found'    => __( 'No scheduled jobs found.', 'credits-for-woocommerce' ),
			),
			'description'         => __( 'This is where scheduled jobs are stored.', 'credits-for-woocommerce' ),
			'public'              => false,
			'capability_type'     => 'post',
			'show_ui'             => apply_filters( 'wc_cs_show_scheduled_jobs_post_type_ui', false ),
			'publicly_queryable'  => false,
			'exclude_from_search' => true,
			'show_in_menu'        => 'credits-for-woocommerce',
			'hierarchical'        => false,
			'show_in_nav_menus'   => false,
			'rewrite'             => false,
			'query_var'           => false,
			'supports'            => false,
			'has_archive'         => false,
			'capabilities'        => array(
				'edit_post'          => 'manage_options',
				'edit_posts'         => 'manage_options',
				'edit_others_posts'  => 'manage_options',
				'publish_posts'      => 'manage_options',
				'read_post'          => 'manage_options',
				'read_private_posts' => 'manage_options',
				'delete_post'        => 'manage_options',
				'delete_posts'       => true,
				'create_posts'       => 'do_not_allow'
			),
		) ) ;
	}

	/**
	 * Register our custom post statuses
	 */
	public static function register_post_status() {
		$our_statuses = array(
			WC_CS_PREFIX . 'pending'      => array(
				'label'                     => _x( 'Pending', 'credits status name', 'credits-for-woocommerce' ),
				'public'                    => true,
				'exclude_from_search'       => false,
				'show_in_admin_all_list'    => true,
				'show_in_admin_status_list' => true,
				/* translators: %s: status name */
				'label_count'               => _n_noop( 'Pending <span class="count">(%s)</span>', 'Pending <span class="count">(%s)</span>' ),
			),
			WC_CS_PREFIX . 'active'       => array(
				'label'                     => _x( 'Active', 'credits status name', 'credits-for-woocommerce' ),
				'public'                    => true,
				'exclude_from_search'       => false,
				'show_in_admin_all_list'    => true,
				'show_in_admin_status_list' => true,
				/* translators: %s: status name */
				'label_count'               => _n_noop( 'Active <span class="count">(%s)</span>', 'Active <span class="count">(%s)</span>' ),
			),
			WC_CS_PREFIX . 'rejected'     => array(
				'label'                     => _x( 'Rejected', 'credits status name', 'credits-for-woocommerce' ),
				'public'                    => true,
				'exclude_from_search'       => false,
				'show_in_admin_all_list'    => true,
				'show_in_admin_status_list' => true,
				/* translators: %s: status name */
				'label_count'               => _n_noop( 'Rejected <span class="count">(%s)</span>', 'Rejected <span class="count">(%s)</span>' ),
			),
			WC_CS_PREFIX . 'on_hold'      => array(
				'label'                     => _x( 'On-Hold', 'credits status name', 'credits-for-woocommerce' ),
				'public'                    => true,
				'exclude_from_search'       => false,
				'show_in_admin_all_list'    => true,
				'show_in_admin_status_list' => true,
				/* translators: %s: status name */
				'label_count'               => _n_noop( 'On-Hold <span class="count">(%s)</span>', 'On-Hold <span class="count">(%s)</span>' ),
			),
			WC_CS_PREFIX . 'overdue'      => array(
				'label'                     => _x( 'Overdue', 'credits status name', 'credits-for-woocommerce' ),
				'public'                    => true,
				'exclude_from_search'       => false,
				'show_in_admin_all_list'    => true,
				'show_in_admin_status_list' => true,
				/* translators: %s: status name */
				'label_count'               => _n_noop( 'Overdue <span class="count">(%s)</span>', 'Overdue <span class="count">(%s)</span>' ),
			),
			WC_CS_PREFIX . 'under_review' => array(
				'label'                     => _x( 'Under Review', 'credits status name', 'credits-for-woocommerce' ),
				'public'                    => true,
				'exclude_from_search'       => false,
				'show_in_admin_all_list'    => true,
				'show_in_admin_status_list' => true,
				/* translators: %s: status name */
				'label_count'               => _n_noop( 'Under Review <span class="count">(%s)</span>', 'Under Review <span class="count">(%s)</span>' ),
			),
			WC_CS_PREFIX . 'unbilled'     => array(
				'label'                     => _x( 'Unbilled', 'credits transaction status name', 'credits-for-woocommerce' ),
				'public'                    => false,
				'exclude_from_search'       => false,
				'show_in_admin_all_list'    => true,
				'show_in_admin_status_list' => true,
				/* translators: %s: status name */
				'label_count'               => _n_noop( 'Unbilled <span class="count">(%s)</span>', 'Unbilled <span class="count">(%s)</span>' ),
			),
			WC_CS_PREFIX . 'billed'       => array(
				'label'                     => _x( 'Billed', 'credits transaction status name', 'credits-for-woocommerce' ),
				'public'                    => false,
				'exclude_from_search'       => false,
				'show_in_admin_all_list'    => true,
				'show_in_admin_status_list' => true,
				/* translators: %s: status name */
				'label_count'               => _n_noop( 'Billed <span class="count">(%s)</span>', 'Billed <span class="count">(%s)</span>' ),
			),
				) ;

		foreach ( $our_statuses as $status => $status_display_name ) {
			register_post_status( $status, $status_display_name ) ;
		}
	}

}

WC_CS_Post_Types::init() ;
