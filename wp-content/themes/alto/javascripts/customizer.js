/**
 * Theme Customizer enhancements for a better user experience.
 *
 * Contains handlers to make Theme Customizer preview reload changes asynchronously.
 */

( function( $ ) {

    // Helper Function for Shifting Colors

    function lightenDarkenColor(col, amt) {

        var usePound = false;

        if (col[0] == "#") {
            col = col.slice(1);
            usePound = true;
        }

        var num = parseInt(col,16);

        var r = (num >> 16) + amt;

        if (r > 255) r = 255;
        else if  (r < 0) r = 0;

        var b = ((num >> 8) & 0x00FF) + amt;

        if (b > 255) b = 255;
        else if  (b < 0) b = 0;

        var g = (num & 0x0000FF) + amt;

        if (g > 255) g = 255;
        else if (g < 0) g = 0;

        return (usePound?"#":"") + (g | (b << 8) | (r << 16)).toString(16);

    }


    // Site title and description.
    wp.customize( 'blogname', function( value ) {
        value.bind( function( to ) {
            $( '.site-title a' ).text( to );
        } );
    } );
    wp.customize( 'blogdescription', function( value ) {
        value.bind( function( to ) {
            $( '.site-description' ).text( to );
        } );
    } );

    // Background Color
    wp.customize( 'background_color', function( value ) {
        value.bind( function( to ) {
            $( '.block-header h3, .sidebar .widget_instagram .control-cap .cap-overflow' ).css( 'background-color', to );
        } );
    } );

    // Accent Color
    wp.customize( 'alto_accent_color', function( value ) {
        value.bind( function( to ) {

            // Buttons
            $( '.btn, button:not(.search-submit), input[type="submit"], #infinite-handle, #comment-submit' ).css({
                background: to,
                '-webkit-box-shadow': '0 1px 0 ' + lightenDarkenColor( to,-20 ),
                '-moz-box-shadow': '0 1px 0 ' + lightenDarkenColor( to,-20 ),
                '-o-box-shadow': '0 1px 0 ' + lightenDarkenColor( to,-20 ),
                '-ms-box-shadow': '0 1px 0 ' + lightenDarkenColor( to,-20 ),
                'box-shadow': '0 1px 0 ' + lightenDarkenColor( to,-20 )
            });

            // Tags
            $( '.sidebar .widget_tag_cloud .tagcloud a' ).css( 'color', to );

            // Sidebar Links
            $( '.sidebar ul li a, .sidebar ol li a, .sidebar .widget_calendar #wp-calendar tbody a, .sidebar .widget_calendar #wp-calendar tfoot a' ).css( 'color', to );
            $( '.sidebar .widget_text .textwidget a' ).css( 'color',to );

            // Entry Content
            $( '#page .entry-content .category-title a, #page .entry-title .category-title a, #page .entry-content h1 a:hover, #page .more-link, #page .site-footer a' ).css( 'color', to );
            $( '#page .entry-content blockquote:not(.pull-quote)' ).css( 'border-left-color', to );

            // Entry Meta
            $( '.tags a, .edit-link a' ).css( 'color', to );
            $( '.alt-post .entry-body .entry-meta.desktop h5 a, .alt-post .entry-body .entry-meta.mobile h5 a' ).css( 'color', to );

            // Comments
            $( '.comments-area .comment-list .comment.bypostauthor > .comment-body > .comment-meta > .comment-author > .avatar' ).css( 'border-color', to );

            $( '.comments-area .comment-list .comment-reply-link' ).css( 'color', to );

            $( '.comments-area .comment-list .comment-awaiting-moderation' ).css( 'color', to );

            $( '.comments-area .comment-list .comment-respond .comment-reply-title a').css( 'color', to );

            // Navigation
            $( '.site-branding .menu-toggle .icon-alto-iconfont_Close' ).css( 'color', to );
            $( '.main-navigation.toggled .menu .current-menu-item > a, .main-navigation.toggled .menu .current_page_item > a' ).css( 'color', to );

            if ( document.body.clientWidth > 804 ) {
                $( '.main-navigation .menu > li:hover > a, .main-navigation.toggled .menu > li:hover > a' ).css( 'color', to );
                $( '.main-navigation .menu > .menu-item-has-children:hover:after, .main-navigation.toggled .menu > .menu-item-has-children:hover:after, .main-navigation .menu > .page_item_has_children:hover:after, .main-navigation.toggled .menu > .page_item_has_children:hover:after').css({
                    width: 0,
                    height: 0,
                    borderLeft: '3px solid transparent',
                    borderRight: '3px solid transparent',
                    borderTop: '3px solid ' + to
                } );
                $( '.main-navigation .menu-item-has-children .sub-menu > li:hover > a, .main-navigation.toggled .menu-item-has-children .sub-menu > li:hover > a, .main-navigation .page_item_has_children .children > li:hover > a, .main-navigation.toggled .page_item_has_children .children > li:hover > a' ).css( 'background', to );
            }

            $(window).resize(function(){

                if ( document.body.clientWidth > 804 ) {
                    $( '.main-navigation .menu > li:hover > a, .main-navigation.toggled .menu > li:hover > a' ).css( 'color', to );
                    $( '.main-navigation .menu > .menu-item-has-children:hover:after, .main-navigation.toggled .menu > .menu-item-has-children:hover:after, .main-navigation .menu > .page_item_has_children:hover:after, .main-navigation.toggled .menu > .page_item_has_children:hover:after').css({
                        width: 0,
                        height: 0,
                        borderLeft: '3px solid transparent',
                        borderRight: '3px solid transparent',
                        borderTop: '3px solid ' + to
                    } );
                    $( '.main-navigation .menu-item-has-children .sub-menu > li:hover > a, .main-navigation.toggled .menu-item-has-children .sub-menu > li:hover > a, .main-navigation .page_item_has_children .children > li:hover > a, .main-navigation.toggled .page_item_has_children .children > li:hover > a' ).css( 'background', to );
                }

            });

            // Sharing Module
            $( '.alto-sharing .alto-sharing-more .close a' ).css( 'color', to );

        });

    } );

} )( jQuery );
