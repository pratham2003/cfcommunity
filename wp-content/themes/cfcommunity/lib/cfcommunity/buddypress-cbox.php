<?php
/**
 * Commons In A Box Theme: BuddyPress setup
 */

// abort if bp not active
if ( false == function_exists( 'bp_is_member' ) ) {
	// return to calling script
	return;
}

function custom_bbpress_maybe_load_mentions_scripts( $retval = false ) {
    if ( function_exists( 'bbpress' ) && is_bbpress() ) {
        $retval = true;
    }

    return $retval;
}
add_filter( 'bp_activity_maybe_load_mentions_scripts', 'custom_bbpress_maybe_load_mentions_scripts' );

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
 * Add Activity Tabs on the Stream Directory
 */
function cbox_theme_activity_tabs()
{
	if ( bp_is_activity_component() && bp_is_directory() ):
		get_template_part( 'buddypress/parts/activity-tabs' );
	endif;
}
add_action( 'open_sidebar', 'cbox_theme_activity_tabs' );


/**
 * Add Group Navigation Items to Group Pages
 */
function cbox_theme_group_navigation()
{
	if ( bp_is_group() ) :
		cbox_populate_group_global();
		get_template_part( 'buddypress/parts/group-navigation' );
	endif;
}
add_action( 'open_sidebar', 'cbox_theme_group_navigation' );

/**
 * Add Member Navigation to Member Pages
 */
function cbox_theme_member_navigation()
{
	if ( bp_is_user() ) :
		get_template_part( 'buddypress/parts/member-navigation' );
	endif;
}
add_action( 'open_sidebar', 'cbox_theme_member_navigation' );

/**
 * Add a filter for every displayed user navigation item
 */
function cbox_theme_member_navigation_filter_setup()
{
	// call helper function in core
	infinity_bp_nav_inject_options_setup();
}
add_action( 'bp_setup_nav', 'cbox_theme_member_navigation_filter_setup', 999 );



function setup_cover_profile_nav(){
    global $bp;
    $profile_link = bp_loggedin_user_domain() . $bp->profile->slug . '/';
    $args = array(
                'name' => 'Profile Cover',
                'slug' => 'change-cover',
                'parent_url' => $profile_link,
                'parent_slug' => $bp->profile->slug,
                'screen_function' => 'screen_change_cover',
                'user_has_access'   => ( bp_is_my_profile() || is_super_admin() ),
                'position' => 40
            );
    bp_core_new_subnav_item($args);
}
add_action( 'bp_setup_nav', 'setup_cover_profile_nav' );

function screen_change_cover(){
    global $bp;
    add_action( 'bp_template_title', 'page_title');
    add_action( 'bp_template_content', 'page_content');
    bp_core_load_template( apply_filters( 'bp_core_template_plugin', 'members/single/plugins' ) );
}

function page_title(){
        echo 'Add/Update Your Profile Cover Image';
}

function page_content(){?>

        <div id="profile-cover-uploader">
        <?php
        rtmedia_uploader();
        ?>
        </div>
        <span class="small-text">**Note: The above uploader will allow you to upload multiple images.  If you select multiple images, a random image will be set as your profile cover.</span>

        <?php
}

function set_featured_after_upload ( $media_id, $file_object, $uploaded ) {
    global $bp;
    if ( $bp->current_action == 'change-cover' ) {
        update_user_meta ( bp_loggedin_user_id(), 'rtmedia_featured_media', $media_id[0] );
    }
}

/**
 * Filter the options nav on a user's profile only.
 *
 * We want to remove the options nav on user pages because Infinity does a
 * neat job in nesting child items under the parent nav menu.
 */
function cbox_theme_remove_user_options_nav() {
	global $bp;

	$bp->cbox_theme = new stdClass;
	$bp->cbox_theme->removed_nav_items = array();

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
					// @see cbox_theme_reinstate_user_options_nav()
					$bp->cbox_theme->removed_nav_items[] = $options_nav_item;

					add_filter(
						'bp_get_options_nav_' . $options_nav_item,
						'__return_false'
					);
				}

				break;
		}
	}
}
add_action( 'bp_before_member_body', 'cbox_theme_remove_user_options_nav' );

/**
 * Reinstate the options nav on a user's profile.
 *
 * {@link cbox_theme_remove_user_options_nav()} removes the options nav, but we
 * need to reinstate it so {@link infinity_bp_nav_inject_options_filter()}
 * can do its nesting thang in the sidebar.
 *
 * The sidebar gets rendered after the regular options nav, which is why
 * we have to do this.
 */
function cbox_theme_reinstate_user_options_nav() {
	global $bp;

	if ( empty( $bp->cbox_theme->removed_nav_items ) ) {
		return;
	}

	foreach ( (array) $bp->cbox_theme->removed_nav_items as $options_nav_item ) {
		remove_filter(
			'bp_get_options_nav_' . $options_nav_item,
			'__return_false'
		);
	}
}
add_action( 'bp_after_member_body', 'cbox_theme_reinstate_user_options_nav' );



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
function cbox_add_bp_head() {
	do_action( 'bp_head' );
}
add_action( 'wp_head', 'cbox_add_bp_head' );


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
function cbox_populate_group_global() {
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

/**
 * Create the Notifications menu for the BuddyBar.
 *
 * @since BuddyPress (1.9.0)
 */
function cf_notifications_buddybar_menu() {

    if ( ! is_user_logged_in() ) {
        return false;
    }

    echo '<li class="dropdown menu-groups notification-nav" id="bp-adminbar-notifications-menu"><a data-toggle="dropdown" class="dropdown-toggle has-submenu" href="' . esc_url( bp_loggedin_user_domain() ) . '">';
    _e( '', 'buddypress' );

    if ( $notification_count = bp_notifications_get_unread_notification_count( bp_loggedin_user_id() ) ) : ?>
        <span id="notification-counter"><?php echo bp_core_number_format( $notification_count ); ?></span>
    <?php
    endif;

    echo '</a>';
    echo '<ul class="dropdown-menu">';

    if ( $notifications = bp_notifications_get_notifications_for_user( bp_loggedin_user_id() ) ) {
        $counter = 0;
        for ( $i = 0, $count = count( $notifications ); $i < $count; ++$i ) {
            $alt = ( 0 == $counter % 2 ) ? ' class="alt"' : ''; ?>

            <li<?php echo $alt ?>><?php echo $notifications[$i] ?></li>

            <?php $counter++;
        }
    } else { ?>

        <li><a href="<?php echo esc_url( bp_loggedin_user_domain() ); ?>"><?php _e( 'No new notifications.', 'buddypress' ); ?></a></li>

    <?php
    }

    echo '</ul>';
    echo '</li>';
}

/**
 * Output the My Account BuddyBar menu.
 *
 * @return bool|null Returns false on failure.
 */
function cf_adminbar_account_menu() {
	global $bp;

	if ( !$bp->bp_nav || !is_user_logged_in() )
		return false;

	echo '<ul class="dropdown-menu">';

	// Loop through each navigation item
	$counter = 0;
	foreach( (array) $bp->bp_nav as $nav_item ) {
		$alt = ( 0 == $counter % 2 ) ? ' class="alt"' : '';

		if ( -1 == $nav_item['position'] )
			continue;

		echo '<li' . $alt . '>';
		echo '<a id="bp-admin-' . $nav_item['css_id'] . '" href="' . $nav_item['link'] . '">' . $nav_item['name'] . '</a>';

		if ( isset( $bp->bp_options_nav[$nav_item['slug']] ) && is_array( $bp->bp_options_nav[$nav_item['slug']] ) ) {
			echo '<ul class="dropdown-menu">';
			$sub_counter = 0;

			foreach( (array) $bp->bp_options_nav[$nav_item['slug']] as $subnav_item ) {
				$link = $subnav_item['link'];
				$name = $subnav_item['name'];

				if ( bp_displayed_user_domain() )
					$link = str_replace( bp_displayed_user_domain(), bp_loggedin_user_domain(), $subnav_item['link'] );

				if ( isset( $bp->displayed_user->userdata->user_login ) )
					$name = str_replace( $bp->displayed_user->userdata->user_login, $bp->loggedin_user->userdata->user_login, $subnav_item['name'] );

				$alt = ( 0 == $sub_counter % 2 ) ? ' class="alt"' : '';
				echo '<li' . $alt . '><a id="bp-admin-' . $subnav_item['css_id'] . '" href="' . $link . '">' . $name . '</a></li>';
				$sub_counter++;
			}
			echo '</ul>';
		}

		echo '</li>';

		$counter++;
	}

	$alt = ( 0 == $counter % 2 ) ? ' class="alt"' : '';

	echo '<li' . $alt . '><a id="bp-admin-logout" class="logout" href="' . wp_logout_url( home_url() ) . '">' . __( 'Log Out', 'buddypress' ) . '</a></li>';
	echo '</ul>';
}

/**
 * Replace default member avatar
 *
 * @since BuddyBoss 2.0
 */
if ( !function_exists('buddyboss_addgravatar') ) {
	function buddyboss_addgravatar( $avatar_defaults ) {
		$myavatar = get_bloginfo('template_directory') . '/assets/img/avatar-member.jpg';
		$avatar_defaults[$myavatar] = 'BuddyBoss Man';
		return $avatar_defaults;
	}
	add_filter( 'avatar_defaults', 'buddyboss_addgravatar' );
}

/**
 * Replace default group avatar
 *
 * @since BuddyBoss 1.0
 */
function buddyboss_default_group_avatar($avatar)
{
	global $bp, $groups_template;
	if ( strpos($avatar,'group-avatars') )
	{
		return $avatar;
	}
	else {
		$custom_avatar = get_stylesheet_directory_uri() .'/assets/img/avatar-group.jpg';

		if ( $bp->current_action == "" )
		{
			return '<img width="'.BP_AVATAR_THUMB_WIDTH.'" height="'.BP_AVATAR_THUMB_HEIGHT.'" src="'.$custom_avatar.'" class="avatar" alt="' . esc_attr( $groups_template->group->name ) . '" />';
		}
		else {
			return '<img width="'.BP_AVATAR_FULL_WIDTH.'" height="'.BP_AVATAR_FULL_HEIGHT.'" src="'.$custom_avatar.'" class="avatar" alt="' . esc_attr( $groups_template->group->name ) . '" />';
		}
	}
}
add_filter( 'bp_get_group_avatar', 'buddyboss_default_group_avatar');
add_filter( 'bp_get_new_group_avatar', 'buddyboss_default_group_avatar' );



function setup_coverphoto () {
    if ( !is_admin() ) {
         add_action( 'bp_xprofile_setup_nav', 'setup_cover_profile_nav' );
         add_action( 'rtmedia_after_add_media', 'set_featured_after_upload' );
    }
}
add_action('wp', 'setup_coverphoto');
?>