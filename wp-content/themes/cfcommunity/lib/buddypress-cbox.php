<?php
/**
 * Commons In A Box Theme: BuddyPress setup
 */

// abort if bp not active
if ( false == function_exists( 'bp_is_member' ) ) {
	// return to calling script
	return;
}
/**
 * Change Default Avatar Size
 */
if ( !defined( 'BP_AVATAR_THUMB_WIDTH' ) ) {
	define( 'BP_AVATAR_THUMB_WIDTH', 80 );
}

if ( !defined( 'BP_AVATAR_THUMB_HEIGHT' ) ) {
	define( 'BP_AVATAR_THUMB_HEIGHT', 80 );
}

if ( !defined( 'BP_AVATAR_FULL_WIDTH' ) ) {
	define( 'BP_AVATAR_FULL_WIDTH', 300 );
}

if ( !defined( 'BP_AVATAR_FULL_HEIGHT' ) ) {
	define( 'BP_AVATAR_FULL_HEIGHT', 300 );
}

//
// Actions
//

/**
 * Automagically set up sidebars
 */
function cfc_theme_magic_sidebars()
{
	// load requirements
	require_once 'classes/cbox-widget-setter.php';
	require_once 'buddypress/bp-sidebars.php';

	// auto sidebar population
	cfc_theme_populate_sidebars();
}
add_action( 'infinity_dashboard_activated', 'cfc_theme_magic_sidebars' );

/**
 * Automagically set up menus
 */
function cfc_theme_magic_menus()
{
	// load requirements
	require_once 'buddypress/bp-menus.php';

	// add our default sub-menu
	cfc_theme_add_default_sub_menu();
}
add_action( 'get_header', 'cfc_theme_magic_menus' );

/**
 * Register custom cbox widgets
 */
function cfc_theme_register_widgets()
{
	// load requirements
	require_once 'buddypress/bp-widgets.php';

	// register it
	return register_widget( "cfc_BP_Blogs_Recent_Posts_Widget" );
}
add_action( 'widgets_init', 'cfc_theme_register_widgets' );

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
 * Add New Product Modal
 */
function cfc_theme_add_product_modal()
{
	if ( bp_is_activity_component() && bp_is_directory() ):
		get_template_part( 'buddypress/parts/add-product' );
	endif;
}
add_action( 'wp_footer', 'cfc_theme_add_product_modal' );

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
 * Add BuddyPress Login Modal
 */
function cfc_bp_login_modal()
{
	get_template_part( 'buddypress/parts/bp-login-modal' );
}
add_action( 'wp_footer', 'cfc_bp_login_modal' );

/**
 * Add a filter for every displayed user navigation item
 */
function cfc_theme_member_navigation_filter_setup()
{
	// call helper function in core
	infinity_bp_nav_inject_options_setup();
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
 * need to reinstate it so {@link infinity_bp_nav_inject_options_filter()}
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


/**
 * Temporarily fix the "New Topic" button when using bbPress with BP.
 *
 * @todo Remove this when bbPress addresses this.
 */
function cfc_fix_bbp_new_topic_button() {
	// if groups isn't active, stop now!
	if ( ! bp_is_active( 'groups' ) )
		return;

	// if bbPress 2 isn't enabled, stop now!
	if ( ! function_exists( 'bbpress' ) )
		return;

	// remove the 'New Topic' button
	// this is done because the 'bp_get_group_new_topic_button' filter doesn't
	// work properly
	remove_action( 'bp_group_header_actions', 'bp_group_new_topic_button' );

	// version of bp_is_group_forum() that works with bbPress 2
	$is_group_forum = bp_is_single_item() && bp_is_groups_component() && bp_is_current_action( 'forum' );

	// If these conditions are met, this button should not be displayed
	if ( ! is_user_logged_in() || ! $is_group_forum || bp_is_group_forum_topic()|| bp_group_is_user_banned() )
		return false;

	// create function to output new topic button
	$new_button = create_function( '', "
		// do not show in sidebar
		if ( did_action( 'open_sidebar' ) )
			return;

		// render the button
		bp_button( array(
			'id'                => 'new_topic',
			'component'         => 'groups',
			'must_be_logged_in' => true,
			'block_self'        => true,
			'wrapper_class'     => 'group-button',
			'link_href'         => '#new-post',    // anchor modified
			'link_class'        => 'group-button', // removed a link_class here
			'link_id'           => 'new-topic-button',
			'link_text'         => __( 'New Topic', 'buddypress' ),
			'link_title'        => __( 'New Topic', 'buddypress' ),
		) );
	" );

	// add our customized 'New Topic' button
	add_action( 'bp_group_header_actions', $new_button );

}
add_action( 'bp_actions', 'cfc_fix_bbp_new_topic_button' );

/**
 * Make sure BuddyPress items that are attached to 'bp_head' are added to CBOX
 * Theme.
 *
 * 'bp_head' is a hook that is hardcoded in bp-default's header.php.  So we
 * add the same hook here attached to the 'wp_head' action.
 *
 * This hook is used by BP to add activity item feeds.  Other plugins like
 * BuddyPress Courseware also uses this hook.
 */
function cfc_add_bp_head() {
	do_action( 'bp_head' );
}
add_action( 'wp_head', 'cfc_add_bp_head' );


/**
 * Populate the $groups_template global for use outside the loop
 *
 * We build the group navigation outside the groups loop. In order to use BP's
 * group template functions while building the nav, we must have the template
 * global populated. In this function, we fill in any missing data, based on
 * the current group.
 *
 * This issue should be fixed more elegantly upstream in BuddyPress, ideally
 * by making the template functions fall back on the current group when the
 * loop global is not populated.
 *
 * @see cbox-theme#155
 */
function cfc_populate_group_global() {
	global $groups_template;

	if ( bp_is_group() && isset( $groups_template->groups[0]->group_id ) && empty( $groups_template->groups[0]->name ) ) {
		$current_group = groups_get_current_group();

		// Fill in all missing properties
		foreach ( $current_group as $cur_key => $cur_value ) {
			if ( ! isset( $groups_template->groups[0]->{$cur_key} ) ) {
				$groups_template->groups[0]->{$cur_key} = $cur_value;
			}
		}
	}
}


//
// Helpers
//

if ( false == function_exists( 'is_activity_page' ) ) {
	/**
	 * Activity Stream Conditional
	 */
	function is_activity_page() {
		return ( bp_is_activity_component() && !bp_is_user() );
	}
}
