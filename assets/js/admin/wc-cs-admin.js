/* global wc_cs_admin, ajaxurl */

jQuery( function( $ ) {
    'use strict' ;

    // wc_cs_admin is required to continue, ensure the object exists
    if ( typeof wc_cs_admin === 'undefined' ) {
        return false ;
    }

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

    var formatUrl = function( url ) {
        if ( - 1 === url.indexOf( 'https://' ) || - 1 === url.indexOf( 'http://' ) ) {
            return url ;
        } else {
            return decodeURI( url ) ;
        }
    }

    var admin = {
        siteActivityWrapper : $( '#_wc_cs_site_activity' ),
        viewStatementsWrapper : $( '#_wc_cs_view_statements' ),
        beforeApprovalWrapper : $( '#_wc_cs_before_approval' ),
        afterApprovalWrapper : $( '#_wc_cs_after_approval' ),
        init : function() {

            if ( this.siteActivityWrapper.length ) {
                this.siteActivityWrapper.on( 'click', 'button.check-site-activity', this.checkSiteActivity ) ;
            }

            if ( this.viewStatementsWrapper.length ) {
                this.viewStatementsWrapper.on( 'click', 'button.view-statements', this.viewStatements ) ;
            }

            if ( this.beforeApprovalWrapper.length ) {
                this.beforeApproval.init() ;
            }

            if ( this.afterApprovalWrapper.length ) {
                this.afterApproval.init() ;
            }
        },
        viewStatements : function( evt ) {
            evt.preventDefault() ;
            block( admin.viewStatementsWrapper ) ;

            $.ajax( {
                type : 'POST',
                url : ajaxurl,
                dataType : 'json',
                data : {
                    action : '_wc_cs_view_statement',
                    security : wc_cs_admin.view_statement_nonce,
                    credits_id : wc_cs_admin.credits_id,
                    is_admin : 'yes',
                    data : admin.viewStatementsWrapper.find( ':input[name]' ).serialize(),
                },
                success : function( response ) {
                    if ( response.data.redirect ) {
                        window.open( formatUrl( response.data.redirect ), '_blank' ) ;
                    } else {
                        window.alert( response.data.error ) ;
                    }
                },
                complete : function() {
                    unblock( admin.viewStatementsWrapper ) ;
                }
            } ) ;
        },
        checkSiteActivity : function( evt ) {
            evt.preventDefault() ;
            block( admin.siteActivityWrapper ) ;

            $.ajax( {
                type : 'POST',
                url : ajaxurl,
                dataType : 'json',
                data : {
                    action : '_wc_cs_check_site_activity',
                    security : wc_cs_admin.check_site_activity_nonce,
                    credits_id : wc_cs_admin.credits_id,
                },
                success : function( response ) {
                    if ( response.success ) {
                        admin.siteActivityWrapper.find( '.inside' ).empty() ;
                        admin.siteActivityWrapper.find( '.inside' ).append( response.data.html ) ;
                    } else {
                        window.alert( response.data.error ) ;
                    }
                },
                complete : function() {
                    unblock( admin.siteActivityWrapper ) ;
                }
            } ) ;
        },
        beforeApproval : {
            resetRepaymentSelector : true,
            init : function() {
                this.populate() ;

                admin.beforeApprovalWrapper
                        .on( 'change', '#new_due_date select', this.getRepaymentDayOfMonthSelector )
                        .on( 'change', '#new_billing_date select', this.getRepaymentDayOfMonthSelector )
                        .on( 'change', '#request_status select', this.toggleStatus )
                        .on( 'change', '#use_global_billing_date input', this.toggleUseGlobalBilling )
                        .on( 'change', '#use_global_due_date input', this.toggleUseGlobalDue )
                        .on( 'click', '#new_credits_limit .edit-credits', this.editEligibleCredits )
                        .on( 'click', 'button.save-before-approval', this.save ) ;
            },
            populate : function() {
                admin.beforeApproval.resetRepaymentSelector = true ;

                this.toggleStatus() ;
            },
            toggleStatus : function( evt ) {
                if ( 'object' === typeof evt ) {
                    evt.stopImmediatePropagation() ;
                }

                if ( '_wc_cs_active' === $( '#request_status select' ).val() ) {
                    $( '#new_credits_limit,#use_global_billing_date,#use_global_due_date' ).show() ;

                    admin.beforeApproval.toggleUseGlobalBilling() ;
                    admin.beforeApproval.toggleUseGlobalDue() ;
                } else {
                    $( '#new_credits_limit,#use_global_billing_date,#use_global_due_date,#new_billing_date,#new_due_date' ).hide() ;
                }
            },
            toggleUseGlobalBilling : function( evt ) {
                if ( 'object' === typeof evt ) {
                    evt.stopImmediatePropagation() ;
                    admin.beforeApproval.resetRepaymentSelector = false ;
                }

                if ( $( '#use_global_billing_date input' ).is( ':checked' ) ) {
                    $( '#new_billing_date' ).hide() ;
                } else {
                    $( '#new_billing_date' ).show() ;
                }

                if ( false === admin.beforeApproval.resetRepaymentSelector ) {
                    admin.beforeApproval.getRepaymentDayOfMonthSelector() ;
                }
            },
            toggleUseGlobalDue : function( evt ) {
                if ( 'object' === typeof evt ) {
                    evt.stopImmediatePropagation() ;
                    admin.beforeApproval.resetRepaymentSelector = false ;
                }

                if ( $( '#use_global_due_date input' ).is( ':checked' ) ) {
                    $( '#new_due_date' ).hide() ;
                } else {
                    $( '#new_due_date' ).show() ;
                }

                if ( false === admin.beforeApproval.resetRepaymentSelector ) {
                    admin.beforeApproval.getRepaymentDayOfMonthSelector() ;
                }
            },
            editEligibleCredits : function( evt ) {
                evt.preventDefault() ;
                if ( window.confirm( wc_cs_admin.i18n_confirm_credit_limit_changes ) ) {
                    $( evt.currentTarget ).closest( 'tr' ).find( 'input' ).removeAttr( 'readonly', 'readonly' ) ;
                    $( evt.currentTarget ).hide() ;
                }
                return false ;
            },
            getRepaymentDayOfMonthSelector : function( evt ) {
                if ( 'object' === typeof evt ) {
                    evt.stopImmediatePropagation() ;
                }

                block( admin.beforeApprovalWrapper ) ;

                $.ajax( {
                    type : 'POST',
                    url : ajaxurl,
                    data : {
                        action : '_wc_cs_get_repayment_day_of_month',
                        security : wc_cs_admin.get_repayment_nonce,
                        credits_id : wc_cs_admin.credits_id,
                        template : 'before-approval',
                        data : admin.beforeApprovalWrapper.find( ':input[name]' ).serialize(),
                    },
                    success : function( response ) {
                        if ( response.success ) {
                            admin.beforeApprovalWrapper.find( '.inside' ).empty() ;
                            admin.beforeApprovalWrapper.find( '.inside' ).append( response.data.html ) ;
                            admin.beforeApproval.populate() ;
                        } else {
                            if ( response.data.error ) {
                                window.alert( response.data.error ) ;
                            } else {
                                window.alert( wc_cs_admin.i18n_went_wrong ) ;
                            }
                        }
                    },
                    complete : function() {
                        unblock( admin.beforeApprovalWrapper ) ;
                    }
                } ) ;
            },
            save : function( evt ) {
                evt.preventDefault() ;
                block( admin.beforeApprovalWrapper ) ;

                $.ajax( {
                    type : 'POST',
                    url : ajaxurl,
                    data : {
                        action : '_wc_cs_save_before_approval',
                        security : wc_cs_admin.save_before_approval_nonce,
                        credits_id : wc_cs_admin.credits_id,
                        data : admin.beforeApprovalWrapper.find( ':input[name]' ).serialize(),
                    },
                    success : function( response ) {
                        if ( response.success ) {
                            admin.beforeApprovalWrapper.find( '.inside' ).empty() ;
                            admin.beforeApprovalWrapper.find( '.inside' ).append( response.data.html ) ;
                            admin.beforeApproval.populate() ;

                            if ( response.data.refresh ) {
                                window.location.reload( true ) ;
                            } else {
                                unblock( admin.beforeApprovalWrapper ) ;
                            }
                        } else {
                            window.alert( response.data.error ) ;
                            unblock( admin.beforeApprovalWrapper ) ;
                        }
                    }
                } ) ;
            },
        },
        afterApproval : {
            resetRepaymentSelector : true,
            init : function() {
                this.populate() ;

                admin.afterApprovalWrapper
                        .on( 'change', '#new_due_date select', this.getRepaymentDayOfMonthSelector )
                        .on( 'change', '#new_billing_date select', this.getRepaymentDayOfMonthSelector )
                        .on( 'change', '#modify_credits_limit input', this.toggleModifyLimit )
                        .on( 'change', '#modify_billing_date input', this.toggleModifyBilling )
                        .on( 'change', '#modify_due_date input', this.toggleModifyDue )
                        .on( 'change', '#use_global_billing_date input', this.toggleUseGlobalBilling )
                        .on( 'change', '#use_global_due_date input', this.toggleUseGlobalDue )
                        .on( 'click', 'button.save-after-approval', this.save ) ;
            },
            populate : function() {
                admin.afterApprovalWrapper.resetRepaymentSelector = true ;

                this.toggleModifyLimit() ;
                this.toggleModifyBilling() ;
                this.toggleModifyDue() ;
            },
            toggleModifyLimit : function( evt ) {
                if ( 'object' === typeof evt ) {
                    evt.stopImmediatePropagation() ;

                    if ( $( evt.currentTarget ).is( ':checked' ) && ! window.confirm( wc_cs_admin.i18n_confirm_credit_limit_changes ) ) {
                        $( evt.currentTarget ).prop( 'checked', false ) ;
                        return false ;
                    }
                }

                if ( $( '#modify_credits_limit input' ).is( ':checked' ) ) {
                    $( '#new_credits_limit' ).show() ;
                } else {
                    $( '#new_credits_limit' ).hide() ;
                }
            },
            toggleModifyBilling : function( evt ) {
                if ( 'object' === typeof evt ) {
                    evt.stopImmediatePropagation() ;
                    admin.afterApprovalWrapper.resetRepaymentSelector = false ;
                }

                if ( $( '#modify_billing_date input' ).is( ':checked' ) ) {
                    $( '#use_global_billing_date' ).show() ;

                    if ( $( '#use_global_billing_date input' ).is( ':checked' ) ) {
                        $( '#new_billing_date' ).hide() ;
                    } else {
                        $( '#new_billing_date' ).show() ;
                    }
                } else {
                    $( '#use_global_billing_date,#new_billing_date' ).hide() ;
                }

                if ( false === admin.afterApprovalWrapper.resetRepaymentSelector ) {
                    admin.afterApproval.getRepaymentDayOfMonthSelector() ;
                }
            },
            toggleModifyDue : function( evt ) {
                if ( 'object' === typeof evt ) {
                    evt.stopImmediatePropagation() ;
                    admin.afterApprovalWrapper.resetRepaymentSelector = false ;
                }

                if ( $( '#modify_due_date input' ).is( ':checked' ) ) {
                    $( '#use_global_due_date' ).show() ;

                    if ( $( '#use_global_due_date input' ).is( ':checked' ) ) {
                        $( '#new_due_date' ).hide() ;
                    } else {
                        $( '#new_due_date' ).show() ;
                    }
                } else {
                    $( '#use_global_due_date,#new_due_date' ).hide() ;
                }

                if ( false === admin.afterApprovalWrapper.resetRepaymentSelector ) {
                    admin.afterApproval.getRepaymentDayOfMonthSelector() ;
                }
            },
            toggleUseGlobalBilling : function( evt ) {
                if ( 'object' === typeof evt ) {
                    evt.stopImmediatePropagation() ;
                    admin.afterApprovalWrapper.resetRepaymentSelector = false ;
                }

                if ( $( '#use_global_billing_date input' ).is( ':checked' ) ) {
                    $( '#new_billing_date' ).hide() ;
                } else {
                    $( '#new_billing_date' ).show() ;
                }

                if ( false === admin.afterApprovalWrapper.resetRepaymentSelector ) {
                    admin.afterApproval.getRepaymentDayOfMonthSelector() ;
                }
            },
            toggleUseGlobalDue : function( evt ) {
                if ( 'object' === typeof evt ) {
                    evt.stopImmediatePropagation() ;
                    admin.afterApprovalWrapper.resetRepaymentSelector = false ;
                }

                if ( $( '#use_global_due_date input' ).is( ':checked' ) ) {
                    $( '#new_due_date' ).hide() ;
                } else {
                    $( '#new_due_date' ).show() ;
                }

                if ( false === admin.afterApprovalWrapper.resetRepaymentSelector ) {
                    admin.afterApproval.getRepaymentDayOfMonthSelector() ;
                }
            },
            getRepaymentDayOfMonthSelector : function( evt ) {
                if ( 'object' === typeof evt ) {
                    evt.stopImmediatePropagation() ;
                }

                block( admin.afterApprovalWrapper ) ;

                $.ajax( {
                    type : 'POST',
                    url : ajaxurl,
                    data : {
                        action : '_wc_cs_get_repayment_day_of_month',
                        security : wc_cs_admin.get_repayment_nonce,
                        credits_id : wc_cs_admin.credits_id,
                        template : 'after-approval',
                        data : admin.afterApprovalWrapper.find( ':input[name]' ).serialize(),
                    },
                    success : function( response ) {
                        if ( response.success ) {
                            admin.afterApprovalWrapper.find( '.inside' ).empty() ;
                            admin.afterApprovalWrapper.find( '.inside' ).append( response.data.html ) ;
                            admin.afterApproval.populate() ;
                        } else {
                            if ( response.data.error ) {
                                window.alert( response.data.error ) ;
                            } else {
                                window.alert( wc_cs_admin.i18n_went_wrong ) ;
                            }
                        }
                    },
                    complete : function() {
                        unblock( admin.afterApprovalWrapper ) ;
                    }
                } ) ;
            },
            save : function( evt ) {
                evt.preventDefault() ;
                block( admin.afterApprovalWrapper ) ;

                $.ajax( {
                    type : 'POST',
                    url : ajaxurl,
                    data : {
                        action : '_wc_cs_save_after_approval',
                        security : wc_cs_admin.save_after_approval_nonce,
                        credits_id : wc_cs_admin.credits_id,
                        data : admin.afterApprovalWrapper.find( ':input[name]' ).serialize(),
                    },
                    success : function( response ) {
                        if ( response.success ) {
                            admin.afterApprovalWrapper.find( '.inside' ).empty() ;
                            admin.afterApprovalWrapper.find( '.inside' ).append( response.data.html ) ;
                            admin.afterApproval.populate() ;

                            if ( response.data.refresh ) {
                                window.location.reload( true ) ;
                            } else {
                                unblock( admin.afterApprovalWrapper ) ;
                            }
                        } else {
                            window.alert( response.data.error ) ;
                            unblock( admin.afterApprovalWrapper ) ;
                        }
                    }
                } ) ;
            },
        },
    } ;

    admin.init() ;
} ) ;
