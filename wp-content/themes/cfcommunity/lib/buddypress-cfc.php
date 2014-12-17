<?php
/**
 * Commons In A Box Theme: BuddyPress setup
 */

// abort if bp not active
if ( false == function_exists( 'bp_is_member' ) ) {
	// return to calling script
	return;
}

function bp_profile_homepage()
//Redirect logged in users from homepage to activity
{
	global $bp;
	if( is_user_logged_in() && bp_is_front_page() && !get_user_meta( $user->ID, 'last_activity', true ) )
	{
		wp_redirect( network_home_url( $bp->activity->root_slug ), 301 );
	}
}
add_action('wp','bp_profile_homepage');

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
 * @since cfc 2.0
 */
if ( !function_exists('cfc_addgravatar') ) {
	function cfc_addgravatar( $avatar_defaults ) {
		$myavatar = get_bloginfo('template_directory') . '/assets/img/avatar-member.jpg';
		$avatar_defaults[$myavatar] = 'cfc Man';
		return $avatar_defaults;
	}
	add_filter( 'avatar_defaults', 'cfc_addgravatar' );
}

/**
 * Replace default group avatar
 *
 * @since cfc 1.0
 */
function cfc_default_group_avatar($avatar)
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
add_filter( 'bp_get_group_avatar', 'cfc_default_group_avatar');
add_filter( 'bp_get_new_group_avatar', 'cfc_default_group_avatar' );

?>