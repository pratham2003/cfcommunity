( function( $ ) {

  $(window).resize(function(){

    windowWidth = $(window).width();
    navigation  = $('#masthead, #site-navigation');
    isToggled   = navigation.hasClass( 'toggled' );

    if ( windowWidth > 804 && isToggled ) {
      navigation.removeClass('toggled');
      $('.menu-toggle').find('i').removeClass('icon-alto-iconfont_Close').addClass('icon-alto-iconfont_Menu');
    }

  });

} )( jQuery );