<?php
/**
 * Changes default BuddyPress behavior through filters. Has some overlap with bp-custom.php
 */

function cfc_bp_profile_homepage()
//Redirect logged in users from homepage to activity
{
    global $bp;
    global $blog_id;
    if( is_user_logged_in() && bp_is_front_page() && ! $blog_id == 4 && !get_user_meta( $user->ID, 'last_activity', true ) )
    {
        wp_redirect( network_home_url( $bp->activity->root_slug ), 301 );
    }
}
add_action('wp','cfc_bp_profile_homepage');

/**
 * Add a filter for every displayed user navigation item
 */
function cfc_theme_member_navigation_filter_setup()
{
    // call helper function in core
    cfc_bp_nav_inject_options_setup();
}
add_action( 'bp_setup_nav', 'cfc_theme_member_navigation_filter_setup', 999 );


/**
 * Filter the options nav on a user's profile only.
 *
 * We want to remove the options nav on user pages because Infinity does a
 * neat job in nesting child items under the parent nav menu.
 */
function cfc_theme_remove_user_options_nav() {
    global $bp;

    $bp->cfc_theme = new stdClass;
    $bp->cfc_theme->removed_nav_items = array();

    // loop all nav components
    foreach ( (array) $bp->bp_options_nav as $component => $nav_item ) {

        switch ( $component ) {
            // remove everything by default
            // in the future, we could do this on a component-by-component basis
            // but we probably won't have to do this.
            default :
                // get all 'css_id' values as the options nav filter relies on this
                $options_nav = wp_list_pluck( $nav_item, 'css_id' );

                foreach ( $options_nav as $options_nav_item ) {
                    // we're temporarily saving what is removed so we can reinstate it later
                    // @see cfc_theme_reinstate_user_options_nav()
                    $bp->cfc_theme->removed_nav_items[] = $options_nav_item;

                    add_filter(
                        'bp_get_options_nav_' . $options_nav_item,
                        '__return_false'
                    );
                }

                break;
        }
    }
}
add_action( 'bp_before_member_body', 'cfc_theme_remove_user_options_nav' );

/**
 * Reinstate the options nav on a user's profile.
 *
 * {@link cfc_theme_remove_user_options_nav()} removes the options nav, but we
 * need to reinstate it so {@link cfc_bp_nav_inject_options_filter()}
 * can do its nesting thang in the sidebar.
 *
 * The sidebar gets rendered after the regular options nav, which is why
 * we have to do this.
 */
function cfc_theme_reinstate_user_options_nav() {
    global $bp;

    if ( empty( $bp->cfc_theme->removed_nav_items ) ) {
        return;
    }

    foreach ( (array) $bp->cfc_theme->removed_nav_items as $options_nav_item ) {
        remove_filter(
            'bp_get_options_nav_' . $options_nav_item,
            '__return_false'
        );
    }
}
add_action( 'bp_after_member_body', 'cfc_theme_reinstate_user_options_nav' );
?>