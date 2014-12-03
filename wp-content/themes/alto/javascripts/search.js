/**
 * Search Input
 * Controls interaction/classing for search input widget.
 */

( function( $ ) {

    // On focus, add a helper class for styling. 
    $( '.search-form' ).on( 'focus', '.search-field', function() {
        $parent = $( this ).closest( '.search-form' );
        $parent.find( '.search-cap' ).addClass( 'focused' );
        $parent.find( '.search-cap i' ).removeClass( 'grey' ).addClass( 'white' );
    } );

    // On blur, strip helper class.
    $( '.search-form' ).on( 'blur', '.search-field', function() {
        $value = $( this ).val().length;
        $parent = $( this ).closest( '.search-form' );
        // Check if a value still exists, if so, leave style intact. Otherwise, drop it. 
        $parent.find( '.search-cap' ).removeClass( 'focused' );
        $parent.find( '.search-cap i' ).removeClass( 'white' ).addClass( 'grey' );
    } );

} )( jQuery );