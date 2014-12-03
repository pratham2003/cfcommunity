/**
 * Sharing
 * Controls interaction/classing for the sharing module.
 */

( function( $ ) {

  // Open Facebook & Twitter links in a new window

  $(".alto-sharing-list").on("click", ".alto-share-facebook a, .alto-share-twitter a", function(e){
    e.preventDefault();
    var url = $(this).attr('href');
    window.open( url, 'Share on Facebook', 'height=315,width=600,top=100,left=100', false );
  });

  // On doc ready, for each sharing module, find it's popover and then append it to the more list item...because, PHP.
  var shareModule  = $( '.alto-sharing' );
  shareModule.each(function(){
    var $this        = $(this);
    var more         = $this.find( '.alto-sharing-more' );
    var moreClone    = more.clone();
    var moreListItem = $this.find( '.alto-share-more' );
    more.remove();
    moreListItem.append(moreClone);
  });

  // Reveal/hide popover on click
  $(".alto-share-more").on( "click", function(e){
    e.preventDefault();
    $this = $(this);
    $parent = $this.closest( '.alto-sharing' );
    $parent.find( '.alto-sharing-more' ).fadeIn();
    $this.toggleClass( 'toggled' );
  });

  // Close on click window "close"
  $(".close").on( "click", "i", function(e){
    e.preventDefault();
    e.stopPropagation();
    $parent = $(this).closest( '.alto-sharing-more' );
    $parent.parent().find( '.alto-share-more' ).removeClass( 'toggled' );
    $parent.fadeOut();
  });

} )( jQuery );