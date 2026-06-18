/* global attributehubAdmin, jQuery, Swal */
( function ( $, attributehubAdmin ) {
	'use strict';

	// =========================================================================
	// SweetAlert2 helpers
	// =========================================================================

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

	function ahToast( opts ) {
		return Swal.fire( Object.assign( {
			toast:             true,
			position:          'top-end',
			showConfirmButton: false,
			timer:             2200,
			timerProgressBar:  true,
			customClass:       { popup: 'ah-swal-popup' },
		}, opts ) );
	}

	function ahConfirm( opts ) {
		return Swal.fire( Object.assign( {
			showCancelButton:   true,
			confirmButtonColor: '#d33',
			cancelButtonColor:  '#6b7280',
			customClass:        { popup: 'ah-swal-popup' },
		}, opts ) );
	}

	function ahSuccess( message ) {
		return ahToast( { icon: 'success', title: message || 'Done!' } );
	}

	// =========================================================================
	// AJAX helper
	// =========================================================================

	function ahAjax( method, data ) {
		return $.post( attributehubAdmin.ajaxUrl, Object.assign( {
			action:    'attributehub_ajax',
			attributehub_method: method,
			attributehub_nonce:  attributehubAdmin.nonce,
		}, data ) );
	}

	// =========================================================================
	// HTML escape helpers
	// =========================================================================

	function escHtml( s ) {
		return String( s )
			.replace( /&/g, '&amp;' )
			.replace( /</g, '&lt;' )
			.replace( />/g, '&gt;' );
	}

	function escAttr( s ) {
		return escHtml( s ).replace( /"/g, '&quot;' );
	}

	// =========================================================================
	// Master Labels page — DOM helpers
	// =========================================================================

	function buildMasterRow( master, taxonomy ) {
		var mapHref  = ( attributehubAdmin.adminUrl || '' ) + '?page=attributehub-mappings&taxonomy=' + encodeURIComponent( taxonomy ) + '&master=' + master.id;
		var desc     = master.description
			? '<br><span class="ah-text-sm ah-muted">' + escHtml( master.description ) + '</span>'
			: '';
		var isHidden = parseInt( master.is_hidden, 10 );
		var visiBadge = isHidden
			? '<span class="ah-badge ah-badge--hidden">&#128683; Hidden</span>'
			: '<span class="ah-badge ah-badge--success">&#128065;&#65039; Visible</span>';

		return '<tr data-id="' + master.id + '">' +
			'<td style="padding-left:16px;cursor:grab;">' +
				'<span class="dashicons dashicons-menu" style="color:var(--ah-gray-400);vertical-align:middle;"></span>' +
			'</td>' +
			'<td>' +
				'<span class="ah-cell-primary">' + escHtml( master.label ) + '</span>' + desc +
			'</td>' +
			'<td><code class="ah-code-chip">' + escHtml( master.slug ) + '</code></td>' +
			'<td><span class="ah-badge ah-badge--muted">None yet</span></td>' +
			'<td>' + visiBadge + '</td>' +
			'<td style="white-space:nowrap;">' +
				'<button class="ah-btn ah-btn--secondary ah-btn--sm ah-edit-master-btn" type="button"' +
					' data-id="' + master.id + '"' +
					' data-label="' + escAttr( master.label ) + '"' +
					' data-slug="' + escAttr( master.slug ) + '"' +
					' data-desc="' + escAttr( master.description || '' ) + '"' +
					' data-hidden="' + ( master.is_hidden || 0 ) + '">Edit</button>' +
				'<a href="' + escAttr( mapHref ) + '" class="ah-btn ah-btn--secondary ah-btn--sm" style="margin-left:4px;">Map</a>' +
				'<button class="ah-btn ah-btn--danger ah-btn--sm ah-delete-master-btn" type="button"' +
					' data-id="' + master.id + '"' +
					' data-label="' + escAttr( master.label ) + '"' +
					' data-taxonomy="' + escAttr( taxonomy ) + '"' +
					' style="margin-left:4px;">Delete</button>' +
			'</td>' +
		'</tr>';
	}

	function ensureMastersTable( taxonomy ) {
		if ( $( '#ah-masters-sortable' ).length ) { return; }

		var $table = $( [
			'<table class="ah-table" id="ah-masters-sortable" data-taxonomy="' + escAttr( taxonomy ) + '">',
			'<thead><tr>',
			'<th style="width:32px;padding-left:16px;"></th>',
			'<th>Label</th><th>Slug</th><th>Mapped Values</th><th>Visibility</th><th>Actions</th>',
			'</tr></thead>',
			'<tbody></tbody>',
			'</table>',
		].join( '' ) );

		$( '.ah-table-wrap' ).html( $table );
		initSortable();
	}

	function checkMastersEmpty() {
		if ( ! $( '#ah-masters-sortable tbody tr' ).length ) {
			$( '.ah-table-wrap' ).html(
				'<div class="ah-empty-state" style="padding:48px 24px;">' +
				'<span class="ah-empty-icon dashicons dashicons-tag"></span>' +
				'<p>No master labels yet for this attribute. Add one above to get started.</p>' +
				'</div>'
			);
		}
	}

	function initSortable() {
		if ( $.fn.sortable && $( '#ah-masters-sortable tbody' ).length ) {
			$( '#ah-masters-sortable tbody' ).sortable( {
				handle: 'td:first-child',
				axis:   'y',
				update: function () {
					var ids      = [];
					var taxonomy = $( '#ah-masters-sortable' ).data( 'taxonomy' );
					$( '#ah-masters-sortable tbody tr' ).each( function () {
						ids.push( $( this ).data( 'id' ) );
					} );
					ahAjax( 'MasterDirectoryPage@reorder_masters', { taxonomy: taxonomy, ids: ids } );
				},
			} );
		}
	}

	function updateMasterRow( master ) {
		var $row    = $( '#ah-masters-sortable tbody tr[data-id="' + master.id + '"]' );
		var desc    = master.description
			? '<br><span class="ah-text-sm ah-muted">' + escHtml( master.description ) + '</span>'
			: '';
		var isHidden = parseInt( master.is_hidden, 10 );

		$row.find( 'td:nth-child(2)' ).html( '<span class="ah-cell-primary">' + escHtml( master.label ) + '</span>' + desc );
		$row.find( 'td:nth-child(3)' ).html( '<code class="ah-code-chip">' + escHtml( master.slug ) + '</code>' );
		$row.find( 'td:nth-child(5)' ).html(
			isHidden
				? '<span class="ah-badge ah-badge--hidden">&#128683; Hidden</span>'
				: '<span class="ah-badge ah-badge--success">&#128065;&#65039; Visible</span>'
		);
		$row.find( '.ah-edit-master-btn' )
			.attr( 'data-label',  escAttr( master.label ) )
			.attr( 'data-slug',   escAttr( master.slug ) )
			.attr( 'data-desc',   escAttr( master.description || '' ) )
			.attr( 'data-hidden', master.is_hidden || 0 );
		$row.find( '.ah-delete-master-btn' )
			.attr( 'data-label', escAttr( master.label ) );
	}

	// =========================================================================
	// Mapping Editor — DOM helpers
	// =========================================================================

	function buildPoolChip( termId, taxonomy, name, count ) {
		return $(
			'<div class="ah-term-chip ah-draggable" draggable="true"' +
				' data-term-id="' + termId + '"' +
				' data-taxonomy="' + escAttr( taxonomy ) + '"' +
				' title="ID: ' + termId + '">' +
			'<span class="ah-chip-label">' + escHtml( name ) + '</span>' +
			'<span class="ah-chip-count">' + escHtml( String( count ) ) + '</span>' +
			'</div>'
		);
	}

	function buildMappedChip( termId, taxonomy, name, count ) {
		return $(
			'<div class="ah-mapped-chip"' +
				' data-term-id="' + termId + '"' +
				' data-taxonomy="' + escAttr( taxonomy ) + '">' +
			'<span>' + escHtml( name ) + '</span>' +
			'<span class="ah-chip-count">' + escHtml( String( count ) ) + '</span>' +
			'<button class="ah-unmap-btn" type="button"' +
				' data-term-id="' + termId + '"' +
				' data-taxonomy="' + escAttr( taxonomy ) + '"' +
				' title="Unmap">&times;</button>' +
			'</div>'
		);
	}

	function ensurePool() {
		if ( $( '#ah-unmapped-pool' ).length ) { return; }
		var $body = $( '.ah-editor-layout .ah-panel' ).first().find( '.ah-panel-body' );
		$body.find( '.ah-empty-state' ).remove();
		$body.append( '<div class="ah-chips-list" id="ah-unmapped-pool"></div>' );
	}

	function updatePoolBadge() {
		var count = $( '#ah-unmapped-pool .ah-term-chip' ).length;
		$( '.ah-editor-layout .ah-panel' ).first().find( '.ah-panel-badge' ).text( count );
	}

	// =========================================================================
	// Taxonomy switcher
	// =========================================================================

	$( '#ah-taxonomy-select' ).on( 'change', function () {
		var url = new URL( window.location.href );
		url.searchParams.set( 'taxonomy', $( this ).val() );
		window.location.href = url.toString();
	} );

	// =========================================================================
	// Dashboard: Scan All
	// =========================================================================

	$( '.ah-scan-all-btn' ).on( 'click', function () {
		var $btn = $( this );
		$btn.prop( 'disabled', true );
		$( '.ah-scan-status' ).text( '' );
		ahLoading( 'Scanning all attributes…' );

		ahAjax( 'DashboardPage@ajax_scan', {} )
			.done( function ( r ) {
				if ( r.success ) {
					ahToast( { icon: 'success', title: r.data.message || 'Scan complete!' } )
						.then( function () { window.location.reload(); } );
				} else {
					ahAlert( { icon: 'error', title: 'Scan Failed', text: ( r.data && r.data.message ) || 'Scan failed.' } );
				}
			} )
			.fail( function () {
				ahAlert( { icon: 'error', title: 'Server Error', text: 'Scan request could not be completed.' } );
			} )
			.always( function () { $btn.prop( 'disabled', false ); } );
	} );

	// =========================================================================
	// Scanner: Run Scan (uses its own progress bar — no loading popup)
	// =========================================================================

	$( '.ah-run-scan-btn' ).on( 'click', function () {
		var $btn  = $( this );
		var $wrap = $( '.ah-scan-progress-wrap' );
		var $bar  = $( '.ah-scan-progress-bar' );

		$btn.prop( 'disabled', true );
		$wrap.show();

		var progress = 0;
		var ticker   = setInterval( function () {
			progress = Math.min( progress + Math.random() * 10, 90 );
			$bar.css( 'width', progress + '%' );
		}, 300 );

		ahAjax( 'ScannerPage@run_scan', { taxonomy: $btn.data( 'taxonomy' ) } )
			.done( function () {
				clearInterval( ticker );
				$bar.css( 'width', '100%' );
				setTimeout( function () { window.location.reload(); }, 500 );
			} )
			.fail( function () {
				clearInterval( ticker );
				ahAlert( { icon: 'error', title: 'Scan Failed', text: 'Scan request failed. Please try again.' } );
				$btn.prop( 'disabled', false );
				$wrap.hide();
			} );
	} );

	// Scanner filter tabs
	$( document ).on( 'click', '.ah-filter-tab', function ( e ) {
		e.preventDefault();
		var filter = $( this ).data( 'filter' );
		$( '.ah-filter-tab' ).removeClass( 'active' );
		$( this ).addClass( 'active' );

		$( '.ah-result-row' ).each( function () {
			var $row    = $( this );
			var issue   = $row.data( 'issue' );
			var mapped  = parseInt( $row.data( 'mapped' ), 10 );
			var visible = ( 'all' === filter ) ||
				( 'unmapped' === filter && ! mapped ) ||
				( 'duplicate' === filter && 'duplicate' === issue ) ||
				( 'ugly' === filter && 'ugly' === issue );
			$row.toggle( visible );
		} );
	} );

	// =========================================================================
	// Scanner: Map Now modal
	// =========================================================================

	$( document ).on( 'click', '.ah-map-now-btn', function () {
		var $btn  = $( this );
		var modal = $( '#ah-map-modal' );

		modal.find( '#ah-modal-raw-value' ).text( $btn.data( 'raw' ) );
		modal.data( 'term-id', $btn.data( 'term-id' ) );
		modal.data( 'taxonomy', $btn.data( 'taxonomy' ) );

		ahAjax( 'MappingEditorPage@get_masters', { taxonomy: $btn.data( 'taxonomy' ) } )
			.done( function ( r ) {
				if ( r.success ) {
					var $sel = modal.find( '#ah-modal-master-select' ).empty();
					$sel.append( '<option value="">' + ( attributehubAdmin.strings.selectMaster || 'Select existing master…' ) + '</option>' );
					$.each( r.data, function ( i, m ) {
						$sel.append( $( '<option>' ).val( m.id ).text( m.label ) );
					} );
				}
			} );

		modal.show();
	} );

	$( '#ah-modal-cancel, #ah-modal-cancel-2' ).on( 'click', function () {
		$( '#ah-map-modal' ).hide();
	} );

	$( '#ah-modal-save' ).on( 'click', function () {
		var modal    = $( '#ah-map-modal' );
		var termId   = modal.data( 'term-id' );
		var taxonomy = modal.data( 'taxonomy' );
		var masterId = modal.find( '#ah-modal-master-select' ).val();
		var newLabel = modal.find( '#ah-modal-new-master' ).val().trim();

		if ( ! masterId && ! newLabel ) {
			ahAlert( { icon: 'warning', title: 'Selection Required', text: 'Please select an existing master or enter a new label.' } );
			return;
		}

		modal.hide();
		ahLoading( 'Saving mapping…' );

		ahAjax( 'MappingEditorPage@map_term', {
			term_id:         termId,
			taxonomy:        taxonomy,
			master_group_id: masterId,
			new_label:       newLabel,
		} ).done( function ( r ) {
			if ( r.success ) {
				ahSuccess( 'Mapping saved!' ).then( function () { window.location.reload(); } );
			} else {
				ahAlert( { icon: 'error', title: 'Mapping Failed', text: ( r.data && r.data.message ) || 'Failed to save mapping.' } );
			}
		} ).fail( function () {
			ahAlert( { icon: 'error', title: 'Server Error', text: 'Could not save mapping. Please try again.' } );
		} );
	} );

	// =========================================================================
	// Master Directory: toggle form
	// =========================================================================

	$( '.ah-add-master-btn' ).on( 'click', function ( e ) {
		e.preventDefault();
		$( '#ah-add-master-form' ).slideToggle( 200 );
	} );

	$( '#ah-cancel-master-btn' ).on( 'click', function () {
		$( '#ah-add-master-form' ).slideUp( 200 );
	} );

	// Auto-slug from label
	$( '#ah-new-master-label' ).on( 'input', function () {
		$( '#ah-new-master-slug' ).val(
			$( this ).val().toLowerCase().replace( /[^a-z0-9]+/g, '-' ).replace( /^-|-$/g, '' )
		);
	} );

	// =========================================================================
	// Master Directory: Create — inject row, no reload
	// =========================================================================

	$( '#ah-save-master-btn' ).on( 'click', function () {
		var $btn     = $( this );
		var label    = $( '#ah-new-master-label' ).val().trim();
		var taxonomy = $btn.data( 'taxonomy' );

		if ( ! label ) {
			ahAlert( { icon: 'warning', title: 'Label Required', text: 'Please enter a display label before saving.' } );
			$( '#ah-new-master-label' ).focus();
			return;
		}

		$btn.prop( 'disabled', true );
		ahLoading( 'Creating master label…' );

		ahAjax( 'MasterDirectoryPage@create_master', {
			taxonomy:    taxonomy,
			label:       label,
			slug:        $( '#ah-new-master-slug' ).val(),
			description: $( '#ah-new-master-desc' ).val(),
			is_hidden:   $( '#ah-new-master-hidden-cb' ).is( ':checked' ) ? 1 : 0,
		} ).done( function ( r ) {
			$btn.prop( 'disabled', false );

			if ( r.success && r.data && r.data.master ) {
				var master = r.data.master;

				// Build table if this is the first entry
				ensureMastersTable( taxonomy );

				// Inject new row
				var $row = $( buildMasterRow( master, taxonomy ) ).hide();
				$( '#ah-masters-sortable tbody' ).append( $row );
				$row.fadeIn( 300 );

				// Reset form
				$( '#ah-new-master-label, #ah-new-master-slug, #ah-new-master-desc' ).val( '' );
				$( '#ah-new-master-hidden-cb' ).prop( 'checked', false );
				$( '#ah-add-master-form' ).slideUp( 200 );

				Swal.close();
				ahSuccess( 'Master label "' + master.label + '" created!' );
			} else {
				ahAlert( { icon: 'error', title: 'Could Not Save', text: ( r.data && r.data.message ) || 'Failed to create master label.' } );
			}
		} ).fail( function ( xhr ) {
			$btn.prop( 'disabled', false );
			ahAlert( { icon: 'error', title: 'Server Error', text: 'Request failed (HTTP ' + xhr.status + ').' } );
		} );
	} );

	// =========================================================================
	// Master Directory: Edit modal open
	// =========================================================================

	$( document ).on( 'click', '.ah-edit-master-btn', function () {
		var $btn = $( this );
		$( '#ah-edit-master-id' ).val( $btn.data( 'id' ) );
		$( '#ah-edit-master-label' ).val( $btn.data( 'label' ) );
		$( '#ah-edit-master-slug' ).val( $btn.data( 'slug' ) );
		$( '#ah-edit-master-desc' ).val( $btn.data( 'desc' ) );
		$( '#ah-edit-master-hidden-cb' ).prop( 'checked', !! parseInt( $btn.data( 'hidden' ), 10 ) );
		$( '#ah-edit-master-modal' ).show();
	} );

	// =========================================================================
	// Master Directory: Update — patch row in-place, no reload
	// =========================================================================

	$( '#ah-update-master-btn' ).on( 'click', function () {
		var $btn = $( this );
		$btn.prop( 'disabled', true );
		$( '#ah-edit-master-modal' ).hide();
		ahLoading( 'Updating master label…' );

		ahAjax( 'MasterDirectoryPage@update_master', {
			taxonomy:    $btn.data( 'taxonomy' ),
			id:          $( '#ah-edit-master-id' ).val(),
			label:       $( '#ah-edit-master-label' ).val(),
			slug:        $( '#ah-edit-master-slug' ).val(),
			description: $( '#ah-edit-master-desc' ).val(),
			is_hidden:   $( '#ah-edit-master-hidden-cb' ).is( ':checked' ) ? 1 : 0,
		} ).done( function ( r ) {
			$btn.prop( 'disabled', false );

			if ( r.success && r.data && r.data.master ) {
				updateMasterRow( r.data.master );
				Swal.close();
				ahSuccess( 'Master label updated!' );
			} else {
				$( '#ah-edit-master-modal' ).show();
				ahAlert( { icon: 'error', title: 'Update Failed', text: ( r.data && r.data.message ) || 'Failed to update master label.' } );
			}
		} ).fail( function () {
			$btn.prop( 'disabled', false );
			$( '#ah-edit-master-modal' ).show();
			ahAlert( { icon: 'error', title: 'Server Error', text: 'Please try again.' } );
		} );
	} );

	// =========================================================================
	// Master Directory: Delete — remove row, no reload
	// =========================================================================

	$( document ).on( 'click', '.ah-delete-master-btn', function () {
		var $btn  = $( this );
		var label = $btn.data( 'label' );

		ahConfirm( {
			icon:              'warning',
			title:             'Delete "' + label + '"?',
			text:              'All mapped values will become unmapped. This cannot be undone.',
			confirmButtonText: 'Yes, delete it',
		} ).then( function ( result ) {
			if ( ! result.isConfirmed ) { return; }

			ahLoading( 'Deleting master label…' );

			ahAjax( 'MasterDirectoryPage@delete_master', {
				id:       $btn.data( 'id' ),
				taxonomy: $btn.data( 'taxonomy' ),
			} ).done( function ( r ) {
				if ( r.success ) {
					var $row = $btn.closest( 'tr' );
					$row.fadeOut( 300, function () {
						$row.remove();
						checkMastersEmpty();
					} );
					Swal.close();
					ahSuccess( '"' + label + '" deleted.' );
				} else {
					ahAlert( { icon: 'error', title: 'Delete Failed', text: ( r.data && r.data.message ) || 'Delete failed.' } );
				}
			} ).fail( function () {
				ahAlert( { icon: 'error', title: 'Server Error', text: 'Please try again.' } );
			} );
		} );
	} );

	// =========================================================================
	// Master Directory: Sortable reorder
	// =========================================================================

	initSortable();

	// =========================================================================
	// Mapping Editor: Drag-and-drop — move chip DOM, no reload
	// =========================================================================

	var $dragItem = null;

	$( document ).on( 'dragstart', '.ah-draggable', function ( e ) {
		$dragItem = $( this );
		e.originalEvent.dataTransfer.effectAllowed = 'move';
		$( this ).addClass( 'ah-dragging' );
	} );

	$( document ).on( 'dragend', '.ah-draggable', function () {
		$( this ).removeClass( 'ah-dragging' );
	} );

	$( document ).on( 'dragover', '.ah-master-dropzone', function ( e ) {
		e.preventDefault();
		$( this ).addClass( 'ah-drop-active' );
	} );

	$( document ).on( 'dragleave', '.ah-master-dropzone', function () {
		$( this ).removeClass( 'ah-drop-active' );
	} );

	$( document ).on( 'drop', '.ah-master-dropzone', function ( e ) {
		e.preventDefault();
		$( this ).removeClass( 'ah-drop-active' );
		if ( ! $dragItem ) { return; }

		var $zone    = $( this );
		var masterId = $zone.data( 'master-id' );
		var termId   = $dragItem.data( 'term-id' );
		var taxonomy = $dragItem.data( 'taxonomy' );
		var name     = $dragItem.find( '.ah-chip-label' ).text();
		var count    = $dragItem.find( '.ah-chip-count' ).text();

		ahLoading( 'Saving mapping…' );

		ahAjax( 'MappingEditorPage@map_term', {
			term_id:         termId,
			taxonomy:        taxonomy,
			master_group_id: masterId,
		} ).done( function ( r ) {
			if ( r.success ) {
				// Remove from pool
				$dragItem.remove();
				updatePoolBadge();

				// Add to master zone
				var $list = $zone.find( '.ah-mapped-list' );
				$list.find( '.ah-drop-hint' ).remove();
				$list.append( buildMappedChip( termId, taxonomy, name, count ) );

				Swal.close();
				ahSuccess( '"' + name + '" mapped!' );
			} else {
				ahAlert( { icon: 'error', title: 'Mapping Failed', text: ( r.data && r.data.message ) || 'Could not save mapping.' } );
			}
		} ).fail( function () {
			ahAlert( { icon: 'error', title: 'Server Error', text: 'Mapping request failed. Please try again.' } );
		} );
	} );

	// =========================================================================
	// Mapping Editor: Unmap single — move chip back to pool, no reload
	// =========================================================================

	$( document ).on( 'click', '.ah-unmap-btn', function ( e ) {
		e.stopPropagation();
		var $mappedChip = $( this ).closest( '.ah-mapped-chip' );
		var termId      = $mappedChip.data( 'term-id' );
		var taxonomy    = $mappedChip.data( 'taxonomy' );
		var name        = $mappedChip.find( 'span:first' ).text();
		var count       = $mappedChip.find( '.ah-chip-count' ).text();

		ahLoading( 'Removing mapping…' );

		ahAjax( 'MappingEditorPage@unmap_term', { term_id: termId, taxonomy: taxonomy } )
			.done( function ( r ) {
				if ( r.success ) {
					var $list = $mappedChip.closest( '.ah-mapped-list' );
					$mappedChip.remove();

					// Show drop hint if zone is now empty
					if ( ! $list.find( '.ah-mapped-chip' ).length ) {
						$list.html( '<div class="ah-drop-hint">Drop values here</div>' );
					}

					// Add chip back to pool
					ensurePool();
					$( '#ah-unmapped-pool' ).append( buildPoolChip( termId, taxonomy, name, count ) );
					updatePoolBadge();

					Swal.close();
					ahSuccess( '"' + name + '" unmapped.' );
				} else {
					ahAlert( { icon: 'error', title: 'Failed', text: ( r.data && r.data.message ) || 'Could not remove mapping.' } );
				}
			} )
			.fail( function () {
				ahAlert( { icon: 'error', title: 'Server Error', text: 'Please try again.' } );
			} );
	} );

	// =========================================================================
	// Mapping Editor: Unmap all — move all chips back to pool, no reload
	// =========================================================================

	$( document ).on( 'click', '.ah-unmap-all-btn', function () {
		var $zone    = $( this ).closest( '.ah-master-zone' );
		var masterId = $zone.data( 'master-id' );
		var taxonomy = $( this ).data( 'taxonomy' );
		var count    = $zone.find( '.ah-mapped-chip' ).length;

		if ( ! count ) {
			ahAlert( { icon: 'info', title: 'Nothing to unmap', text: 'This master group has no mapped values.' } );
			return;
		}

		ahConfirm( {
			icon:              'warning',
			title:             'Remove all mappings?',
			text:              count + ' value' + ( count > 1 ? 's' : '' ) + ' will become unmapped.',
			confirmButtonText: 'Yes, remove all',
		} ).then( function ( result ) {
			if ( ! result.isConfirmed ) { return; }

			ahLoading( 'Removing all mappings…' );

			ahAjax( 'MappingEditorPage@unmap_all', { master_id: masterId, taxonomy: taxonomy } )
				.done( function ( r ) {
					if ( r.success ) {
						ensurePool();
						var $pool = $( '#ah-unmapped-pool' );

						// Move each chip to pool
						$zone.find( '.ah-mapped-chip' ).each( function () {
							var $chip = $( this );
							var tId   = $chip.data( 'term-id' );
							var name  = $chip.find( 'span:first' ).text();
							var cnt   = $chip.find( '.ah-chip-count' ).text();
							$pool.append( buildPoolChip( tId, taxonomy, name, cnt ) );
						} );

						$zone.find( '.ah-mapped-list' ).html( '<div class="ah-drop-hint">Drop values here</div>' );
						updatePoolBadge();

						Swal.close();
						ahSuccess( 'All mappings removed.' );
					} else {
						ahAlert( { icon: 'error', title: 'Failed', text: ( r.data && r.data.message ) || 'Could not remove mappings.' } );
					}
				} )
				.fail( function () {
					ahAlert( { icon: 'error', title: 'Server Error', text: 'Please try again.' } );
				} );
		} );
	} );

	// =========================================================================
	// Mapping Editor: Search unmapped pool
	// =========================================================================

	$( '#ah-search-unmapped' ).on( 'input', function () {
		var q = $( this ).val().toLowerCase();
		$( '#ah-unmapped-pool .ah-term-chip, #ah-unmapped-pool .ah-draggable' ).each( function () {
			var text = $( this ).find( '.ah-chip-label, .ah-term-name' ).text().toLowerCase();
			$( this ).toggle( text.includes( q ) );
		} );
	} );

	// CSV Export
	$( '.ah-export-csv-btn' ).on( 'click', function () {
		var taxonomy = $( this ).data( 'taxonomy' );
		window.location.href = attributehubAdmin.ajaxUrl +
			'?action=attributehub_ajax&attributehub_method=MappingEditorPage%40export_csv&taxonomy=' +
			encodeURIComponent( taxonomy ) + '&attributehub_nonce=' + attributehubAdmin.nonce;
	} );

	// =========================================================================
	// Settings: Flush Cache
	// =========================================================================

	$( '#ah-flush-cache-btn' ).on( 'click', function () {
		var $btn = $( this );
		$btn.prop( 'disabled', true );
		ahLoading( 'Clearing cache…' );

		ahAjax( 'SettingsPage@flush_cache', {} )
			.done( function ( r ) {
				$btn.prop( 'disabled', false );
				if ( r.success ) {
					ahAlert( { icon: 'success', title: 'Cache Cleared', text: ( r.data && r.data.message ) || 'All caches have been cleared.' } );
				} else {
					ahAlert( { icon: 'error', title: 'Error', text: ( r.data && r.data.message ) || 'Failed to clear cache.' } );
				}
			} )
			.fail( function () {
				$btn.prop( 'disabled', false );
				ahAlert( { icon: 'error', title: 'Server Error', text: 'Cache flush request failed.' } );
			} );
	} );

	// =========================================================================
	// Settings: Save form via AJAX
	// =========================================================================

	$( '.ah-settings-form' ).on( 'submit', function ( e ) {
		e.preventDefault();
		var $form = $( this );
		var $btn  = $form.find( '[type=submit]' );

		$btn.prop( 'disabled', true );
		ahLoading( 'Saving settings…' );

		// Serialize form fields and append AJAX routing params.
		// Note: unchecked checkboxes are absent — the PHP handler forces them to false
		// for the active tab (see SettingsPage::save_settings_ajax).
		var data = $form.serializeArray();
		data.push( { name: 'action',              value: 'attributehub_ajax' } );
		data.push( { name: 'attributehub_method', value: 'SettingsPage@save_settings_ajax' } );
		data.push( { name: 'attributehub_nonce',  value: attributehubAdmin.nonce } );

		$.post( attributehubAdmin.ajaxUrl, $.param( data ) )
			.done( function ( r ) {
				$btn.prop( 'disabled', false );
				Swal.close();
				if ( r.success ) {
					ahSuccess( ( r.data && r.data.message ) || 'Settings saved!' );
				} else {
					ahAlert( { icon: 'error', title: 'Could not save', text: ( r.data && r.data.message ) || 'Failed to save settings.' } );
				}
			} )
			.fail( function () {
				$btn.prop( 'disabled', false );
				ahAlert( { icon: 'error', title: 'Server Error', text: 'Settings could not be saved. Please try again.' } );
			} );
	} );

	// =========================================================================
	// Modal close on backdrop click / close button
	// =========================================================================

	$( '.ah-modal' ).on( 'click', function ( e ) {
		if ( $( e.target ).hasClass( 'ah-modal' ) ) { $( this ).hide(); }
	} );

	$( '.ah-modal-close' ).on( 'click', function () {
		$( this ).closest( '.ah-modal' ).hide();
	} );

}( jQuery, window.attributehubAdmin || {} ) );
