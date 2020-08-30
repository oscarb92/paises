/* global wc_cs_frontend */

jQuery( function( $ ) {
	'use strict' ;

	// wc_cs_frontend is required to continue, ensure the object exists
	if ( typeof wc_cs_frontend === 'undefined' ) {
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
				message : null ,
				overlayCSS : {
					background : '#fff' ,
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

	var appForm = {
		form : $( '.wc-cs-mydashboard' ) ,
		init : function() {
			if ( 0 === this.form.length ) {
				return false ;
			}

			if ( this.form.find( '.wc-cs-file-attachments' ).length ) {
				this.form.on( 'click' , '.wc-cs-file-attachments a.wc-cs-add-file' , this.addFile ) ;
				this.form.on( 'click' , '.wc-cs-file-attachments a.wc-cs-delete-file' , this.deleteFile ) ;
			}
		} ,
		addFile : function( evt ) {
			var $this = $( evt.currentTarget ) , $row ;

			$row = $( '<tr>\n\
                       <td class="file-attach"><input type="file" class="file-attach-input" name="file_attachments[]"/></td>\n\
                       <td class="file-delete" width="1%"><a href="#" class="wc-cs-delete-file"><span class="dashicons dashicons-dismiss"></span></a></td>\n\
                    </tr>' ) ;

			$this.closest( '.wc-cs-file-attachments' ).find( 'tbody' ).append( $row ) ;
			return false ;
		} ,
		deleteFile : function( evt ) {
			$( evt.currentTarget ).closest( 'tr' ).remove() ;
			return false ;
		} ,
	} ;

	var dashboard = {
		wrapper : $( 'div.wc-cs-mydashboard-content' ) ,
		init : function() {
			if ( 0 === this.wrapper.length ) {
				return false ;
			}

			if ( this.wrapper.find( '.wc-cs-mydashboard-view-statements' ).length ) {
				this.wrapper.find( '.wc-cs-mydashboard-view-statements' ).on( 'click' , 'button.view-statements' , this.viewStatements ) ;
			}
		} ,
		viewStatements : function( evt ) {
			evt.preventDefault() ;
			block( dashboard.wrapper ) ;

			$.ajax( {
				type : 'POST' ,
				url : wc_cs_frontend.ajax_url ,
				dataType : 'json' ,
				data : {
					action : '_wc_cs_view_statement' ,
					security : wc_cs_frontend.view_statement_nonce ,
					credits_id : wc_cs_frontend.credits_id ,
					is_admin : 'no' ,
					data : dashboard.wrapper.find( ':input[name]' ).serialize() ,
				} ,
				success : function( response ) {
					if ( response.data.redirect ) {
						window.open( formatUrl( response.data.redirect ) , '_blank' ) ;
					} else {
						window.alert( response.data.error ) ;
					}
				} ,
				complete : function() {
					unblock( dashboard.wrapper ) ;
				}
			} ) ;
		} ,
	} ;

	appForm.init() ;
	dashboard.init() ;
} ) ;
