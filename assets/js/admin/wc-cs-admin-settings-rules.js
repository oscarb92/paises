/* global ajaxurl, wc_cs_admin_settings_rules, wp */

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

    var rules = {
        wrapper : $( '.wc_cs_rules_wrapper' ),

        init : function() {
            this.wrapper
                    .on( 'click', 'table.wc_cs_rules a.add', this.addRule )
                    .on( 'click', 'table.wc_cs_rules a.apply_all', this.applyAllRules )
                    .on( 'click', 'table.wc_cs_rules a.remove', this.removeRule )
                    .on( 'click', 'table.wc_cs_rules a.edit', this.backbone.rule.edit ) ;

            $( document.body )
                    .on( 'wc_backbone_modal_loaded', this.backbone.init )
                    .on( 'wc_backbone_modal_response', this.backbone.response ) ;
        },
        addRule : function( e ) {
            e.preventDefault() ;

            $( this ).WCBackboneModal( {
                template : 'wc-cs-modal-preview-rule',
                variable : {
                    rule_id : 'new',
                    data : {
                        no_of_users : 0
                    },
                    criteria_options_groups : ''
                }
            } ) ;
            return false ;
        },
        applyAllRules : function( e ) {
            e.preventDefault() ;

        },
        removeRule : function( e ) {
            e.preventDefault() ;

            if ( window.confirm( wc_cs_admin_settings_rules.i18n_confirm_before_rule_deletion ) ) {
                block( rules.wrapper ) ;

                $.ajax( {
                    type : 'POST',
                    url : ajaxurl,
                    dataType : 'json',
                    data : {
                        action : '_wc_cs_remove_rule',
                        security : wc_cs_admin_settings_rules.remove_rule_nonce,
                        rule_id : $( e.currentTarget ).data( 'rule_id' )
                    },
                    success : function( response ) {
                        if ( response.success ) {
                            rules.wrapper.find( 'tbody' ).empty() ;
                            rules.wrapper.find( 'tbody' ).append( response.data.html ) ;
                        } else {
                            window.alert( response.data.error ) ;
                        }
                    },
                    complete : function() {
                        unblock( rules.wrapper ) ;
                    }
                } ) ;
            }
            return false ;
        },
        backbone : {

            init : function( e, target ) {
                if ( 'wc-cs-modal-preview-rule' === target ) {
                    rules.backbone.rule.init( e ) ;
                }
            },
            response : function( e, target, data ) {
                if ( 'wc-cs-modal-preview-rule' === target ) {
                    rules.backbone.rule.save() ;
                }
            },
            rule : {

                init : function( e ) {
                    if ( 0 === $( e.currentTarget ).find( '.wc_cs_rule_criteria_options_group' ).length ) {
                        $( e.currentTarget )
                                .find( '.criteria_options_groups_inside' ).append( $( e.currentTarget ).find( '.wc_cs_rule_criteria_wrapper' ).data( 'group' ) ) ;
                    }

                    $( e.currentTarget )
                            .on( 'click', 'a.add_and_options', this.addAndOptions )
                            .on( 'click', 'a.add_or_group', this.addORGroup )
                            .on( 'click', 'a.remove_options', this.removeOptions ) ;

                    $( '.wc_cs_rule_criteria_wrapper' )
                            .on( 'wc_cs_rule_criteria_options_group_added', this.initGroup )
                            .on( 'wc_cs_rule_criteria_options_row_added', this.initRow )

                            .on( 'wc_cs_rule_criteria_options_group_removed', this.resetGroup )
                            .on( 'wc_cs_rule_criteria_options_row_removed', this.resetRow )

                            .on( 'wc_cs_rule_criteria_options_group_init', this.updateGroup )
                            .on( 'wc_cs_rule_criteria_options_group_reset', this.updateGroup )

                            .on( 'wc_cs_rule_criteria_options_row_init', this.updateRow )
                            .on( 'wc_cs_rule_criteria_options_row_reset', this.updateRow )

                            .on( 'change', '.wc_cs_rule_criteria_options_group #action', this.actionSelected )
                            .find( '.wc_cs_rule_criteria_options_group #action' ).change() ;
                },
                actionSelected : function( e ) {
                    e.preventDefault() ;

                    switch ( $( e.currentTarget ).val() ) {
                        case 'user_registered_for':
                            $( e.currentTarget )
                                    .closest( '.wc_cs_rule_criteria_options_row' ).find( '#user_role,#orders_amount,#orders_count' ).hide()
                                    .closest( '.wc_cs_rule_criteria_options_row' ).find( '#registered_period_group' ).show() ;
                            break ;
                        case 'user_total_orders_amt_less_than_r_eql_to':
                        case 'user_total_orders_amt_more_than_r_eql_to':
                            $( e.currentTarget )
                                    .closest( '.wc_cs_rule_criteria_options_row' ).find( '#user_role,#registered_period_group,#orders_count' ).hide()
                                    .closest( '.wc_cs_rule_criteria_options_row' ).find( '#orders_amount' ).show() ;
                            break ;
                        case 'user_placed_orders_count_less_than_r_eql_to':
                        case 'user_placed_orders_count_more_than_r_eql_to':
                            $( e.currentTarget )
                                    .closest( '.wc_cs_rule_criteria_options_row' ).find( '#user_role,#registered_period_group,#orders_amount' ).hide()
                                    .closest( '.wc_cs_rule_criteria_options_row' ).find( '#orders_count' ).show() ;
                            break ;
                        default:
                            $( e.currentTarget )
                                    .closest( '.wc_cs_rule_criteria_options_row' ).find( '#registered_period_group,#orders_amount,#orders_count' ).hide()
                                    .closest( '.wc_cs_rule_criteria_options_row' ).find( '#user_role' ).show() ;

                    }
                    return false ;
                },
                addAndOptions : function( e ) {
                    e.stopImmediatePropagation() ;
                    var row = $( e.currentTarget ).closest( '.wc_cs_rule_criteria_wrapper' ).data( 'row' ) ;

                    $( e.currentTarget ).closest( '.wc_cs_rule_criteria_options_row' ).after( row ) ;
                    $( '.wc_cs_rule_criteria_wrapper' ).trigger( 'wc_cs_rule_criteria_options_row_added', e ) ;
                    return false ;
                },
                addORGroup : function( e ) {
                    e.stopImmediatePropagation() ;
                    var group = $( e.currentTarget ).closest( '.wc_cs_rule_criteria_wrapper' ).data( 'group' ) ;

                    $( e.currentTarget ).closest( '.wc_cs_rule_criteria_options_group' ).after( group ) ;
                    $( '.wc_cs_rule_criteria_wrapper' ).trigger( 'wc_cs_rule_criteria_options_group_added', e ) ;
                    return false ;
                },
                initGroup : function( e, event ) {
                    $( event.currentTarget ).closest( '.criteria_options_groups_inside' ).find( '.wc_cs_rule_criteria_options_group' ).each( function( groupID ) {
                        $( this ).attr( 'data-group', groupID ) ;
                        $( '.wc_cs_rule_criteria_wrapper' ).trigger( 'wc_cs_rule_criteria_options_group_init', [ this, groupID ] ) ;
                    } ) ;
                    return false ;
                },
                initRow : function( e, event ) {
                    $( event.currentTarget ).closest( '.wc_cs_rule_criteria_options_group' ).find( '.wc_cs_rule_criteria_options_row' ).each( function( rowID ) {
                        $( this ).attr( 'data-row', rowID ) ;
                        $( '.wc_cs_rule_criteria_wrapper' ).trigger( 'wc_cs_rule_criteria_options_row_init', [ this, rowID ] ) ;
                    } ) ;
                    return false ;
                },
                resetGroup : function() {
                    $( '.wc_cs_rule_criteria_wrapper' ).find( '.wc_cs_rule_criteria_options_group' ).each( function( groupID ) {
                        $( this ).attr( 'data-group', groupID ) ;
                        $( '.wc_cs_rule_criteria_wrapper' ).trigger( 'wc_cs_rule_criteria_options_group_reset', [ this, groupID ] ) ;
                    } ) ;
                    return false ;
                },
                resetRow : function( e, evtGroup ) {
                    $( evtGroup ).find( '.wc_cs_rule_criteria_options_row' ).each( function( rowID ) {
                        $( this ).attr( 'data-row', rowID ) ;
                        $( '.wc_cs_rule_criteria_wrapper' ).trigger( 'wc_cs_rule_criteria_options_row_reset', [ this, rowID ] ) ;
                    } ) ;
                    return false ;
                },
                updateGroup : function( e, evtGroup, groupID ) {
                    $( evtGroup ).find( '.wc_cs_rule_criteria_options_row' ).each( function() {
                        var rowID = $( this ).data( 'row' ) ;

                        $( this ).find( 'select,input' ).each( function() {
                            $( this ).attr( 'name', "criteria[" + groupID + "][" + rowID + "][" + $( this ).attr( 'id' ) + "]" ) ;
                        } ) ;
                    } ) ;
                    return false ;
                },
                updateRow : function( e, evtRow, rowID ) {
                    var groupID = $( evtRow ).closest( '.wc_cs_rule_criteria_options_group' ).data( 'group' ) ;

                    $( evtRow ).find( 'select,input' ).each( function() {
                        $( this ).attr( 'name', "criteria[" + groupID + "][" + rowID + "][" + $( this ).attr( 'id' ) + "]" ) ;
                    } ) ;
                    return false ;
                },
                removeOptions : function( e ) {
                    e.stopImmediatePropagation() ;

                    if ( 1 !== $( e.currentTarget ).closest( '.wc_cs_rule_criteria_wrapper' ).find( '.wc_cs_rule_criteria_options_row' ).length ) {
                        if ( 1 === $( e.currentTarget ).closest( '.wc_cs_rule_criteria_options_group' ).find( '.wc_cs_rule_criteria_options_row' ).length ) {
                            $( e.currentTarget ).closest( '.wc_cs_rule_criteria_options_group' ).remove() ;
                            $( '.wc_cs_rule_criteria_wrapper' ).trigger( 'wc_cs_rule_criteria_options_group_removed' ) ;
                        } else {
                            var group = $( e.currentTarget ).closest( '.wc_cs_rule_criteria_options_group' ) ;

                            $( e.currentTarget ).closest( '.wc_cs_rule_criteria_options_row' ).remove() ;
                            $( '.wc_cs_rule_criteria_wrapper' ).trigger( 'wc_cs_rule_criteria_options_row_removed', group ) ;
                        }
                    }
                    return false ;
                },
                edit : function( e ) {
                    e.preventDefault() ;
                    block( rules.wrapper ) ;

                    $.ajax( {
                        type : 'GET',
                        url : ajaxurl,
                        dataType : 'json',
                        data : {
                            action : '_wc_cs_edit_rule',
                            security : wc_cs_admin_settings_rules.edit_rule_nonce,
                            rule_id : $( e.currentTarget ).data( 'rule_id' )
                        },
                        success : function( response ) {
                            if ( response.success ) {
                                $( this ).WCBackboneModal( {
                                    template : 'wc-cs-modal-preview-rule',
                                    variable : response.data
                                } ) ;
                            } else {
                                window.alert( response.data.error ) ;
                            }
                        },
                        complete : function() {
                            unblock( rules.wrapper ) ;
                        }
                    } ) ;
                },
                save : function() {
                    var creditLimit = $( '.wc_cs_credit_line_rule_wrapper input[name="credit_limit"]' ).val() ;

                    if ( ! $.isNumeric( creditLimit ) || parseFloat( creditLimit ) <= 0 ) {
                        window.alert( wc_cs_admin_settings_rules.i18n_credit_limit_invalid ) ;
                        return false ;
                    }

                    block( rules.wrapper ) ;

                    $.ajax( {
                        type : 'POST',
                        url : ajaxurl,
                        dataType : 'json',
                        data : {
                            action : '_wc_cs_save_rule',
                            security : wc_cs_admin_settings_rules.save_rule_nonce,
                            data : $( '.wc_cs_credit_line_rule_wrapper :input[name]' ).serialize()
                        },
                        success : function( response ) {
                            if ( response.success ) {
                                rules.wrapper.find( 'tbody' ).empty() ;
                                rules.wrapper.find( 'tbody' ).append( response.data.html ) ;
                            } else {
                                window.alert( response.data.error ) ;
                            }
                        },
                        complete : function() {
                            unblock( rules.wrapper ) ;
                        }
                    } ) ;
                }
            },
        },
    } ;

    rules.init() ;

} ) ;
