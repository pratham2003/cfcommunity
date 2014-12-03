/**
 * navigation.js
 *
 * Handles toggling the navigation menu for small screens.
 */
( function() {
    var container, button, menu;

    header    = document.getElementById( 'masthead' );
    container = document.getElementById( 'site-navigation' );

    if ( ! container )
        return;

    button = header.getElementsByTagName( 'h1' )[0];
    arrow  = container.getElementsByTagName( 'i' )[0];
    icon   = button.getElementsByTagName( 'i' )[0];

    if ( 'undefined' === typeof button ) {
        return;
    }

    menu = container.getElementsByTagName( 'ul' )[0];

    // Hide menu toggle button if menu is empty and return early.
    if ( 'undefined' === typeof menu ) {
        button.style.display = 'none';
        return;
    }

    if ( -1 === menu.className.indexOf( 'nav-menu' ) ) {
        menu.className += ' nav-menu';
    }

    navigationState = function() {
        if ( -1 !== container.className.indexOf( 'toggled' ) ) {
          container.className = container.className.replace( ' toggled', '' );
          header.className = header.className.replace( ' toggled', '' );
          icon.className = icon.className.replace( 'icon-alto-iconfont_Close', 'icon-alto-iconfont_Menu' );
        } else {
            container.className += ' toggled';
          header.className += ' toggled';
          icon.className = icon.className.replace( 'icon-alto-iconfont_Menu', 'icon-alto-iconfont_Close' );
        }
    };

    button.onclick = navigationState;
    arrow.onclick = navigationState;
    
} )();