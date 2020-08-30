/* global ajaxurl, wc_cs_admin_settings_general, wp */

jQuery( function( $ ) {
    'use strict' ;

    var is_blocked = function( $node ) {
        return $node.is( '.processing' ) || $node.parents( '.processing' ).length ;
    } ;

    /**
     * Block a node visually for processing.
     *
     * @param {JQuery Object} $node
     */
    var block = function( $node ) {
        $.blockUI.defaults.overlayCSS.cursor = 'wait' ;

        if ( ! is_blocked( $node ) ) {
            $node.addClass( 'processing' ).block( {
                message : null,
                overlayCSS : {
                    background : '#fff',
                    opacity : 0.6
                }
            } ) ;
        }
    } ;

    /**
     * Unblock a node after processing is complete.
     *
     * @param {JQuery Object} $node
     */
    var unblock = function( $node ) {
        $node.removeClass( 'processing' ).unblock() ;
    } ;

    $( '#_wc_cs_disabled_payment_gateways_for_funds_addition,#_wc_cs_disabled_payment_gateways_for_repayment, #_wc_cs_get_included_userroles_to_display_credit_form, #_wc_cs_get_excluded_userroles_to_display_credit_form' ).select2() ;

    if ( 'yes' !== wc_cs_admin_settings_general.funding_via_real_money ) {
        $( '#_wc_cs_credit_line_approval_mode' ).change( function() {
            if ( 'auto-approval' === this.value ) {
                $( '#_wc_cs_display_credit_form_for' ).closest( 'table' ).hide().prev( 'h2' ).hide() ;
            } else {
                $( '#_wc_cs_display_credit_form_for' ).closest( 'table' ).show().prev( 'h2' ).show() ;
            }
        } ).change() ;
    }

    $( '#_wc_cs_funds_addition_product_type' ).change( function() {
        if ( 'new-product' === this.value ) {
            $( '#_wc_cs_create_product_to_add_funds' ).closest( 'tr' ).show() ;
            $( '#_wc_cs_get_selected_product_to_add_funds' ).closest( 'tr' ).hide() ;
        } else {
            $( '#_wc_cs_create_product_to_add_funds' ).closest( 'tr' ).hide() ;
            $( '#_wc_cs_get_selected_product_to_add_funds' ).closest( 'tr' ).show() ;
        }
    } ).change() ;

    $( '#_wc_cs_repayment_product_type' ).change( function() {
        if ( 'new-product' === this.value ) {
            $( '#_wc_cs_create_product_for_repayment' ).closest( 'tr' ).show() ;
            $( '#_wc_cs_get_selected_product_for_repayment' ).closest( 'tr' ).hide() ;
        } else {
            $( '#_wc_cs_create_product_for_repayment' ).closest( 'tr' ).hide() ;
            $( '#_wc_cs_get_selected_product_for_repayment' ).closest( 'tr' ).show() ;
        }
    } ).change() ;

    $( '#_wc_cs_allow_users_site_activity_to_display_credit_form_with' ).change( function() {
        $( '#_wc_cs_allow_users_site_activity_with_min_orders_placed' ).closest( 'tr' ).hide() ;
        $( '#_wc_cs_allow_users_site_activity_with_min_amt_spent' ).closest( 'tr' ).hide() ;

        if ( 'min-no-of-orders-placed' === this.value ) {
            $( '#_wc_cs_allow_users_site_activity_with_min_orders_placed' ).closest( 'tr' ).show() ;
        } else if ( 'min-amt-spent-on-site' === this.value ) {
            $( '#_wc_cs_allow_users_site_activity_with_min_amt_spent' ).closest( 'tr' ).show() ;
        }
    } ).change() ;

    $( '#_wc_cs_display_credit_form_for' ).change( function() {
        $( '#_wc_cs_get_included_users_to_display_credit_form' ).closest( 'tr' ).hide() ;
        $( '#_wc_cs_get_excluded_users_to_display_credit_form' ).closest( 'tr' ).hide() ;
        $( '#_wc_cs_get_included_userroles_to_display_credit_form' ).closest( 'tr' ).hide() ;
        $( '#_wc_cs_get_excluded_userroles_to_display_credit_form' ).closest( 'tr' ).hide() ;

        if ( 'include-users' === this.value || 'exclude-users' === this.value ) {
            if ( 'include-users' === this.value ) {
                $( '#_wc_cs_get_included_users_to_display_credit_form' ).closest( 'tr' ).show() ;
            } else {
                $( '#_wc_cs_get_excluded_users_to_display_credit_form' ).closest( 'tr' ).show() ;
            }
        } else if ( 'include-userroles' === this.value || 'exclude-userroles' === this.value ) {
            if ( 'include-userroles' === this.value ) {
                $( '#_wc_cs_get_included_userroles_to_display_credit_form' ).closest( 'tr' ).show() ;
            } else {
                $( '#_wc_cs_get_excluded_userroles_to_display_credit_form' ).closest( 'tr' ).show() ;
            }
        }
    } ).change() ;

    $( '#_wc_cs_charge_interest_for_credit_usage' ).change( function() {
        if ( this.checked ) {
            $( '#_wc_cs_credit_usage_interest_type' ).closest( 'tr' ).show() ;
            $( '#_wc_cs_credit_usage_interest_value' ).closest( 'tr' ).show() ;
        } else {
            $( '#_wc_cs_credit_usage_interest_type' ).closest( 'tr' ).hide() ;
            $( '#_wc_cs_credit_usage_interest_value' ).closest( 'tr' ).hide() ;
        }
    } ).change() ;

    $( '#_wc_cs_charge_late_payment_fee' ).change( function() {
        if ( this.checked ) {
            $( '#_wc_cs_late_payment_fee_type' ).closest( 'tr' ).show() ;
            $( '#_wc_cs_late_payment_fee_value' ).closest( 'tr' ).show() ;
        } else {
            $( '#_wc_cs_late_payment_fee_type' ).closest( 'tr' ).hide() ;
            $( '#_wc_cs_late_payment_fee_value' ).closest( 'tr' ).hide() ;
        }
    } ).change() ;

    $( '._wc_cs_create_product' ).click( function( evt ) {
        evt.preventDefault() ;

        var $product_title = $( evt.currentTarget ).closest( 'td' ).find( '#product_title' ).val(),
                $field_type_selector = $( evt.currentTarget ).data( 'field_type_selector' ),
                $display_field_id = $( evt.currentTarget ).data( 'display_field_id' ) ;

        $( '.spinner' ).addClass( 'is-active' ).show() ;

        $.ajax( {
            type : 'POST',
            url : ajaxurl,
            data : {
                action : '_wc_cs_create_virtual_product',
                security : wc_cs_admin_settings_general.create_virtual_product_nonce,
                product_title : $product_title,
            },
            success : function( response ) {
                if ( response.success ) {
                    $( evt.currentTarget ).closest( 'tr' ).hide() ;
                    $( '#' + $field_type_selector ).val( 'old-product' ) ;
                    $( '#' + $display_field_id ).closest( 'tr' ).show() ;
                    $( '#' + $display_field_id ).append( $( '<option></option>' ).attr( 'value', response.data.product_id ).text( response.data.product_name ).prop( 'selected', true ) ) ;
                } else {
                    if ( response.data.error ) {
                        window.alert( response.data.error ) ;
                    } else {
                        window.alert( wc_cs_admin_settings_general.i18n_went_wrong ) ;
                    }
                }
            },
            complete : function() {
                $( '.spinner' ).removeClass( 'is-active' ).hide() ;
            }
        } ) ;
    } ) ;

    var repayment = {
        wrapper : $( 'table.repayment-date-settings-wrapper' ),
        init : function() {
            $( document ).on( 'change', 'select#_wc_cs_get_repayment_month', this.getRepaymentDayOfMonth ) ;
            $( document ).on( 'change', 'select#_wc_cs_get_billing_day_of_month', this.getRepaymentDayOfMonth ) ;
        },
        getRepaymentDayOfMonth : function( evt ) {
            evt.preventDefault() ;
            block( repayment.wrapper ) ;

            $.ajax( {
                type : 'POST',
                url : ajaxurl,
                data : {
                    action : '_wc_cs_get_repayment_day_of_month',
                    security : wc_cs_admin_settings_general.get_repayment_nonce,
                    billing_day : $( '#_wc_cs_get_billing_day_of_month' ).val(),
                    repayment_month : $( '#_wc_cs_get_repayment_month' ).val(),
                },
                success : function( response ) {
                    if ( response.success ) {
                        repayment.wrapper.find( 'tbody' ).empty() ;
                        repayment.wrapper.find( 'tbody' ).append( response.data.html ) ;
                    } else {
                        if ( response.data.error ) {
                            window.alert( response.data.error ) ;
                        } else {
                            window.alert( wc_cs_admin_settings_general.i18n_went_wrong ) ;
                        }
                    }
                },
                complete : function() {
                    unblock( repayment.wrapper ) ;
                }
            } ) ;
        },
    } ;

    repayment.init() ;

    var wp_media = {
        file_frame : null,
        logoWrapper : $( '.header-logo-wrapper' ),
        init : function() {
            if ( this.logoWrapper.find( '#logo_attachment_id' ).val() !== '' ) {
                this.logoWrapper.find( '#upload_logo' ).val( 'Change logo' ) ;
            }

            this.logoWrapper.on( 'click', '#upload_logo', this.upload ) ;
            this.logoWrapper.on( 'click', '#delete_logo', this.delete ) ;
        },
        createMediaFrame : function( $el ) {
            wp_media.file_frame = wp.media.frames.file_frame = wp.media( {
                title : $el.data( 'choose' ),
                button : {
                    text : wp_media.logoWrapper.find( '#logo_attachment_id' ).val() !== '' ? $el.val() : $el.data( 'add' )
                },
                states : [
                    new wp.media.controller.Library( {
                        title : $el.data( 'choose' ),
                        library : wp.media.query( { type : 'image' } ),
                        filterable : 'all',
                        editable : true,
                        suggestedWidth : '90',
                        suggestedHeight : '60'
                    } )
                ]
            } ) ;
        },
        onSelect : function() {
            // We set multiple to false so only get one image from the uploader
            var attachment = wp_media.file_frame.state().get( 'selection' ).first().toJSON() ;

            if ( attachment.type !== 'image' ) {
                return false ;
            }

            if ( attachment.id && attachment.url ) {
                wp_media.logoWrapper.find( '.logo-preview' ).show() ;
                wp_media.logoWrapper.find( '.logo-attachment' ).hide() ;

                // Do something with attachment.id and/or attachment.url here
                wp_media.logoWrapper.find( '.logo-preview' ).attr( 'src', attachment.url ).attr( 'width', '200' ).attr( 'height', '100' ) ;
                wp_media.logoWrapper.find( '#logo_attachment_id' ).val( attachment.id ) ;
            } else {
                wp_media.logoWrapper.find( '.logo-preview' ).hide() ;
                wp_media.logoWrapper.find( '.logo-attachment' ).show() ;
            }
        },
        upload : function( evt ) {
            evt.preventDefault() ;

            var $el = $( this ) ;

            // Create the media frame.
            wp_media.createMediaFrame( $el ) ;

            // When an image is selected, run a callback.
            wp_media.file_frame.on( 'select', wp_media.onSelect ) ;

            // Finally, open the modal
            wp_media.file_frame.open() ;
        },
        delete : function( evt ) {
            evt.preventDefault() ;
            wp_media.logoWrapper.find( '#logo_attachment_id' ).val( '' ) ;
            wp_media.logoWrapper.find( '.logo-preview' ).attr( 'style', 'display: none;' ) ;
            wp_media.logoWrapper.find( '.logo-attachment' ).empty() ;
            $( this ).hide() ;
        },
    } ;

    wp_media.init() ;

} ) ;
