<?php

//
// Actions
//

/**
 * Add Activity Tabs on the Stream Directory
 */
function cfc_theme_activity_tabs()
{
    if ( bp_is_activity_component() && bp_is_directory() ):
        get_template_part( 'buddypress/parts/activity-tabs' );
    endif;
}
add_action( 'open_sidebar', 'cfc_theme_activity_tabs' );


/**
 * Add Group Navigation Items to Group Pages
 */
function cfc_theme_group_navigation()
{
    if ( bp_is_group() ) :
        cfc_populate_group_global();
        get_template_part( 'buddypress/parts/group-navigation' );
    endif;
}
add_action( 'open_sidebar', 'cfc_theme_group_navigation' );

/**
 * Add Activity Tour
 */
function cfc_activity_tour()
{
    if ( bp_is_activity_component() && bp_is_directory() && is_user_logged_in() ) :
        get_template_part( 'buddypress/parts/activity-tour' );
    endif;
}
add_action('wp_footer','cfc_activity_tour',10000);

/**
 * Add Activity Tour
 */
function cfc_profile_edit_tour()
{
    if ( bp_is_profile_edit() ) :
        get_template_part( 'buddypress/parts/profile-tour' );
    endif;
}
//add_action('wp_footer','cfc_profile_edit_tour',10000);

/**
 * Add Member Navigation to Member Pages
 */
function cfc_theme_member_navigation()
{
    if ( bp_is_user() ) :
        get_template_part( 'buddypress/parts/member-navigation' );
    endif;
}
add_action( 'open_sidebar', 'cfc_theme_member_navigation' );

/**
 * Add Recent Photos Widget
 */
function cfc_media_widget()
{
    if ( bp_is_user() || bp_is_user() && !is_rtmedia_album() || bp_is_user() && !is_rtmedia_album_gallery() || bp_is_user() && !is_rtmedia_single() ) :
        get_template_part( 'rtmedia/recent-photos' );
    endif;
}
//add_action( 'open_sidebar', 'cfc_media_widget' );

/**
 * Fix maximum photos in profile widget
 */
add_filter( 'rtmedia_per_page_media', 'limit_widget_media_size');
function limit_widget_media_size( $admin_per_page ) {
   if ( is_page( bp_is_user() ) ){  
        $widget_per_page = 9;
        return $widget_per_page;
    }
       return $admin_per_page;
}
?>