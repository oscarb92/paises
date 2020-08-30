/* global wc_cs_jquery_tiptip */

jQuery( function ( $ ) {
	// wc_cs_jquery_tiptip is required to continue, ensure the object exists
	if ( typeof wc_cs_jquery_tiptip === 'undefined' ) {
		return false ;
	}

	$( '._wc_cs_tips' ).tipTip( {
		'attribute' : 'data-tip' ,
		'fadeIn' : 50 ,
		'fadeOut' : 50 ,
		'delay' : 200
	} ) ;
} ) ;
