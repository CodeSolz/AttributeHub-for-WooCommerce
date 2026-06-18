/* global attributehubAdmin, jQuery, Swal */
( function ( $, attributehubAdmin ) {
	'use strict';

	function ahLoading( title ) {
		Swal.fire( {
			title:             title || 'Please wait…',
			html:              '<span style="font-size:13px;color:#64748b;">This will only take a moment.</span>',
			allowOutsideClick: false,
			allowEscapeKey:    false,
			showConfirmButton: false,
			customClass:       { popup: 'ah-swal-popup' },
			didOpen: function () { Swal.showLoading(); },
		} );
	}

	function ahAlert( opts ) {
		return Swal.fire( Object.assign( {
			customClass:        { popup: 'ah-swal-popup' },
			confirmButtonColor: '#6d28d9',
		}, opts ) );
	}

	function ahSuccessReload( message ) {
		return Swal.fire( {
			toast:             true,
			position:          'top-end',
			icon:              'success',
			title:             message || 'Done!',
			showConfirmButton: false,
			timer:             2000,
			timerProgressBar:  true,
			customClass:       { popup: 'ah-swal-popup' },
		} ).then( function () {
			window.location.reload();
		} );
	}

	// Bulk-map selected rows
	$( '.ah-bulk-map-btn' ).on( 'click', function () {
		var taxonomy = $( this ).data( 'taxonomy' );
		var masterId = $( '#ah-bulk-master-select' ).val();
		var termIds  = [];

		$( '.ah-row-check:checked' ).each( function () {
			termIds.push( $( this ).val() );
		} );

		if ( ! masterId ) {
			ahAlert( { icon: 'warning', title: 'No Master Selected', text: 'Please select a master group before bulk mapping.' } );
			return;
		}

		if ( ! termIds.length ) {
			ahAlert( { icon: 'warning', title: 'No Values Selected', text: 'Please select at least one attribute value to map.' } );
			return;
		}

		ahLoading( 'Mapping ' + termIds.length + ' value' + ( termIds.length > 1 ? 's' : '' ) + '…' );

		$.post( attributehubAdmin.ajaxUrl, {
			action:          'attributehub_ajax',
			attributehub_method:       'MappingEditorPage@bulk_map',
			attributehub_nonce:        attributehubAdmin.nonce,
			taxonomy:        taxonomy,
			master_group_id: masterId,
			term_ids:        termIds,
		} ).done( function ( r ) {
			if ( r.success ) {
				ahSuccessReload( ( r.data && r.data.message ) || 'Values mapped successfully!' );
			} else {
				ahAlert( { icon: 'error', title: 'Bulk Map Failed', text: ( r.data && r.data.message ) || 'Could not bulk map the selected values.' } );
			}
		} ).fail( function () {
			ahAlert( { icon: 'error', title: 'Server Error', text: 'Bulk map request failed. Please try again.' } );
		} );
	} );

	// Select all / deselect all
	$( '#ah-select-all' ).on( 'change', function () {
		var checked = $( this ).is( ':checked' );
		$( '.ah-row-check:visible' ).prop( 'checked', checked );
	} );

	// Populate bulk master select on page load
	$( document ).ready( function () {
		var $sel = $( '#ah-bulk-master-select' );
		if ( ! $sel.length ) { return; }

		$.post( attributehubAdmin.ajaxUrl, {
			action:    'attributehub_ajax',
			attributehub_method: 'MappingEditorPage@get_masters',
			attributehub_nonce:  attributehubAdmin.nonce,
			taxonomy:  $( '.ah-run-scan-btn' ).data( 'taxonomy' ),
		} ).done( function ( r ) {
			if ( r.success && Array.isArray( r.data ) ) {
				$.each( r.data, function ( i, m ) {
					$sel.append( $( '<option>' ).val( m.id ).text( m.label ) );
				} );
			}
		} );
	} );

}( jQuery, window.attributehubAdmin || {} ) );
