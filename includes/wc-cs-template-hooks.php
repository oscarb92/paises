<?php

/**
 * Our Template Hooks
 *
 * Action/filter hooks used for Our functions/templates.
 */
defined( 'ABSPATH' ) || exit ;

/**
 * My Dashboard.
 */
add_action( 'wc_cs_credits_dashboard_wc-cs-make-repayment_endpoint', '_wc_cs_make_repayment' ) ;
add_action( 'wc_cs_credits_dashboard_wc-cs-view-unbilled-txns_endpoint', '_wc_cs_view_unbilled_txns' ) ;
add_action( 'wc_cs_credits_dashboard_wc-cs-view-statements_endpoint', '_wc_cs_view_previous_statements' ) ;

/**
 * My Account.
 */
add_filter( 'wc_cs_query_vars', '_wc_cs_add_account_query_vars' ) ;
add_filter( 'wc_cs_get_current_endpoint_title', '_wc_cs_get_account_current_endpoint_title', 10, 2 ) ;
add_action( 'woocommerce_account_wc-cs-add-funds_endpoint', '_wc_cs_view_add_funds' ) ;
add_filter( 'woocommerce_account_menu_items', '_wc_cs_add_account_menu_items' ) ;
add_action( 'woocommerce_account_dashboard', '_wc_cs_add_account_credits_notices' ) ;

