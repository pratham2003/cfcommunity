;(function ( $, window, document, undefined ) {

  // undefined is used here as the undefined global variable in ECMAScript 3 is
  // mutable (ie. it can be changed by someone else). undefined isn't really being
  // passed in so we can ensure the value of it is truly undefined. In ES5, undefined
  // can no longer be modified.

  // window and document are passed through as local variable rather than global
  // as this (slightly) quickens the resolution process and can be more efficiently
  // minified (especially when both are regularly referenced in your plugin).

  // Create the defaults once
  var pluginName = "instagramCycle",
          defaults = {
          propertyName: "value"
  };

  // The actual plugin constructor
  function Plugin ( element, options ) {
          this.element = element;
          // jQuery has an extend method which merges the contents of two or
          // more objects, storing the result in the first object. The first object
          // is generally empty as we don't want to alter the default options for
          // future instances of the plugin
          this.settings = $.extend( {}, defaults, options );
          this._defaults = defaults;
         this._name = pluginName;
          this.init();
  }

  Plugin.prototype = {
          init: function () {

              // Setup some default variables.
              var $this           = this,
                      widget      = $( $this.element ),
                      toggle      = widget.find( '.instagram-cycle-toggle' ),
                      pane        = widget.find( '.pane' ),
                      paneList    = widget.find( '.pane.current' ).find( '.image-list' );

              // Make sure heights are playing friendly with absolute positioning. 
              $(window).bind( 'load', function(){
                  var firstItemH  = $( paneList[0] ).find( 'li:first-child' ).outerHeight(true),
                      lastItemH   = $( paneList[0] ).find( 'li:last-child' ).height(),
                      panes       = pane.length,
                      calcHeight  = ( firstItemH + lastItemH );
                  $windowElem = $(window);
                  if ( $windowElem.width() > 479 && $windowElem.width() < 805 ) {
                      widget.css( 'height', lastItemH ); 
                  } else {
                      widget.css( 'height', calcHeight ); 
                  }
                  widget.find( '.placeholder' ).css( 'height', calcHeight );
                  widget.find( '.placeholder' ).find( 'li' ).each(function(){
                      $(this).css( 'height', lastItemH );
                  });
              });

              // Handle height on resize, too.
              $(window).resize(function(){ 
                  var paneList    = widget.find( '.pane.current' ).find( '.image-list' ),
                      firstItemH  = $( paneList[0] ).find( 'li:first-child' ).outerHeight(true),
                      lastItemH   = $( paneList[0] ).find( 'li:last-child' ).height(),
                      panes       = pane.length,
                      calcHeight  = ( firstItemH + lastItemH );
                      $windowElem = $(window);

                  if ( $windowElem.width() > 479 && $windowElem.width() < 805 ) {
                      widget.css( 'height', lastItemH ); 
                  } else {
                      widget.css( 'height', ( firstItemH + lastItemH ) ); 
                  }

                  widget.find( '.placeholder' ).css( 'height', calcHeight );
                  widget.find( '.placeholder' ).find( 'li' ).each(function(){
                      $(this).css( 'height', lastItemH );
                  });
              });

              // Setup the click event that makes this dog and pony show run.
              toggle.on('click', function(){
                  var currentPane = widget.find( '.pane.current' );
                  $this.fadeImagesOut( currentPane,$this,toggle );
                  $(this).addClass( 'running' );
              });

          },
          fadeImagesOut: function ( pane,context,toggle ) {

              // Wrap function in a to-be-canceled interval to get "cycled" effect.
              var outCycle = setInterval(function(){

                  // Setup fade out variables.
                  var $list      = pane.find( '.image-list' ),
                          $available = $list.find( 'li.unused' ),
                          $rand      = Math.floor( Math.random() * $available.length );

                  // Perform fade out cycle on current set.
                  $list.find( 'li.unused:eq(' + $rand + ')' ).removeClass( 'unused' ).addClass( 'used' ).find( 'img' ).fadeTo('slow', 0);

                  // If we're out of available images to cycle, let's move on to step two.
                  if ( $available.length < 1 ) {

                      // Clear the interval out and demote the current pane.
                      clearInterval(outCycle);

                      // Handle the next pane's interaction.
                      var $nextPane = pane.next();
                      if ( $nextPane.length == 0 ) {
                          $nextPane = pane.parent().children().first();
                      }
                      context.fadeImagesIn( $nextPane,toggle );

                      setTimeout(function(){
                          pane.removeClass( 'current' );
                          $nextPane.addClass( 'current' );
                      }, 300);

                  } // end if ( $available.length < 1 )

              }, 60);
          },
          fadeImagesIn: function( pane,toggle ) {

              // Wrap function in a to-be-canceled interval to get "cycled" effect.
              var inCycle = setInterval(function(){

                  // Setup fade in variables.
                  var $list      = pane.find( '.image-list' ),
                          $available = $list.find( 'li.used' ),
                          $rand      = Math.floor( Math.random() * $available.length );

                  // Perform initial fade out cycle on current set
                  $list.find( 'li.used:eq(' + $rand + ')' ).removeClass( 'used' ).addClass( 'unused' ).find( 'img' ).fadeTo('slow', 1);

                  // If we're out of available images to cycle, let's move on to step two.
                  if ( $available.length < 1 ) {
                      clearInterval(inCycle);
                      toggle.removeClass( 'running' );
                  } // end if ( $available.length < 1 )

              }, 60);
          }
  };
  // A really lightweight plugin wrapper around the constructor,
  // preventing against multiple instantiations
  $.fn[ pluginName ] = function ( options ) {
          this.each(function() {
                  if ( !$.data( this, "plugin_" + pluginName ) ) {
                          $.data( this, "plugin_" + pluginName, new Plugin( this, options ) );
                  }
          });

          // chain jQuery functions
          return this;
  };

})( jQuery, window, document );

// Initialize Instagram plugin 
jQuery(".instagram-cycle").instagramCycle();