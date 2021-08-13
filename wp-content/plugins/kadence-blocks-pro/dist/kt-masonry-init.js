jQuery( document ).ready( function( $ ) {
	// Init Masonry
	$( '.kt-post-grid-layout-masonry-wrap' ).each( function() {
		var masRtl = true;
		if ( $( 'html[dir="rtl"]' ).length ) {
			masRtl = false;
		}
		$( this ).masonry( {
			itemSelector: '.kt-post-masonry-item',
			isOriginLeft: masRtl,
		} );
		$( this ).find( '.kt-post-masonry-item' ).each( function( i ) {
			$( this ).delay( i * 75 ).addClass( 'kt-post-masonry-trigger-animation' );
		} );
	} );
} );
