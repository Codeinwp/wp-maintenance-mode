/* global jQuery */

jQuery( function( $ ) {
	/**
	 * DISMISS NOTICES
	 *
	 * @since 2.0.4
	 */
	$( '.wpmm_notices' ).on( 'click', '.notice-dismiss', function() {
		const noticeKey = $( this ).parent().data( 'key' );
		const noticeNonce = $( this ).parent().data( 'nonce' );
		$.post( ajaxurl, {
			action: 'wpmm_dismiss_notices',
			notice_key: noticeKey,
			_nonce: noticeNonce,
		}, function( response ) {
			if ( ! response.success ) {
				return false;
			}
		}, 'json' );
	} );
} );
