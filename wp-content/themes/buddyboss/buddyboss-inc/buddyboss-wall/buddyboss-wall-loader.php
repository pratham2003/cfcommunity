<?php

/**
 * buddyboss_wall is a BuddyPress plugin combining user activity feeds into a Facebook-like wall.
 *
 * Since 3.0, we use the BP components API
 *
 * @package WordPress
 * @subpackage BuddyBoss
 * @since 3.0
 * @credits		Brajesh Singh for his tutorial on in profile posting
 *
 * TODO: Move functions below main class into proper files
 * denoting their utility. For example:
 * Filters should be in buddyboss-wall-filters.php
 * Actions should be in buddyboss-wall-actions.php
 * Class should be in buddyboss-wall-classes.php
*/

class BuddyBoss_Wall extends BP_Component
{
	/**
	 * BUDDYPRESS ACTIVITIES
	 *
	 * @since BuddyBoss 1.5
	 */
	public $activities;
	public $activity_count = 0;
	public $filter_qs = false;

	/**
	 * OPTIONS
	 *
	 * @since BuddyBoss 1.5
	 */
	private $options;

	/**
	 * STORAGE
	 *
	 * @since BuddyBoss 2.0
	 */
	public $cache;

	/**
	 * LIKES
	 *
	 * @since BuddyBoss 2.0
	 */
	public $likes_store = array();

	/**
	 * INITIALIZE CLASS
	 *
	 * @since BuddyBoss 1.5
	 */
	public function __construct( $options = null )
	{
		parent::start(
			'wall',
			__( 'Wall' , 'buddyboss' ),
			dirname( __FILE__ )
		);
	}

	/**
	 * SETUP BUDDYPRESS GLOBAL OPTIONS
	 *
	 * @since	BuddyBoss 2.0
	 */
	function setup_globals()
	{
		global $activity_template;

		// DEFAULT CONFIGURATION OPTIONS
		$this->options = array(
			"POST_IN_WIRE_OPTIONS"	=> array(),
			"UPDATE_MENUS"		    	=> TRUE,
			"PERSONAL_TAB_NAME"		  => __( 'Wall' , 'buddyboss' ),
			"FEED_TAB_NAME"		      => __( 'News Feed' , 'buddyboss' ),
			"FAV_TAB_NAME"			    => __( 'My Likes' , 'buddyboss' ),
			"MENU_NAME"				      => __( 'Wall' , 'buddyboss' )
		);

		// Log
		buddyboss_log( $this->options );

		// Update menu text
		if (isset($this->options['UPDATE_MENUS']) && $this->options['UPDATE_MENUS'] == true)
		{
			add_action( 'wp_before_admin_bar_render', array($this, 'update_wp_menus'), 99 );
			add_action( 'bp_setup_nav', array($this, 'update_bp_menus'), 98 );
			add_action( 'bp_setup_nav', array($this, 'bbg_remove_activity_friends_subnav'), 99 );
			add_filter( 'bp_get_displayed_user_nav_activity', array($this, 'bbg_replace_activity_link') );
		}

		// Add body class
		add_filter( 'body_class', array($this, 'add_body_class') );

		// Caching
		$this->cache = get_transient('bbwall_cacher');
		add_action( 'wp_shutdown', array($this, 'shutdown') );

		// Actions/filters
		add_action( 'template_redirect', array( $this, 'newsfeed_logout_redirect_url' ) );

		parent::setup_globals();
	}

	/**
	 * SAVES CACHE @ WP SHUTDOWN
	 *
	 * @since BuddyBoss 1.5
	 */
	function shutdown()
	{
		set_transient('bbwall_cacher', $this->cache);
	}

	/**
	 * GET OPTION
	 *
	 * @since BuddyBoss 1.5
	 */
	function get_option($name)
	{
		if (isset($this->options[$name])) return $this->options[$name];
		return false;
	}

	/**
	 * Add active wall class
	 *
	 * @since BuddyBoss 2.0
	 */
	function add_body_class( $classes )
	{
		$classes[] = 'buddyboss-active-wall';
		return $classes;
	}

	/**
	 * RENAME ACTIVITY LINK ON PROFILE SIDEBAR MENU
	 *
	 * @since BuddyBoss 1.5
	 */
	function bbg_replace_activity_link($v)
	{
		return str_replace('Activity', $this->options["MENU_NAME"], $v);
	}

	/**
	 * REMOVE TABS FROM PROFILE HEADER
	 *
	 * @since BuddyBoss 1.5
	 */
	function bbg_remove_activity_friends_subnav() {

		global $bp;

		bp_core_remove_subnav_item( 'activity', 'friends' );
		bp_core_remove_subnav_item( 'activity', 'mentions' );
		bp_core_remove_subnav_item( 'activity', 'groups' );

		if ( !bp_is_my_profile() )
			bp_core_remove_subnav_item( 'activity', 'favorites' );
	}

	/**
	 * RENAME MENU TABS ON PROFILE
	 */
	function update_bp_menus()
	{
		buddyboss_log('Updating Menus');
		global $bp;

		$domain = (!empty($bp->displayed_user->id)) ? $bp->displayed_user->domain : $bp->loggedin_user->domain;

		$profile_link = $domain . $bp->activity->slug . '/';

		// RENAME PERSONAL/WALL TAB
		bp_core_new_subnav_item( array(
			'name' => $this->options["PERSONAL_TAB_NAME"],
			'slug' => 'just-me',
			'parent_url' => $profile_link,
			'parent_slug' => $bp->activity->slug,
			'screen_function' =>
			'bp_activity_screen_my_activity' ,
			"position" => 10
		) );

		// ADD NEWS FEED TAB
		if ( bp_is_my_profile() )
		{
			bp_core_new_subnav_item( array(
				'name' => $this->options["FEED_TAB_NAME"],
				'slug' => 'news-feed',
				'parent_url' => $profile_link,
				'parent_slug' => $bp->activity->slug,
				'screen_function' =>
				'bp_activity_screen_my_activity' ,
				"position" => 11
			) );
		}

		// RENAME FAVORITES TAB
		bp_core_new_subnav_item( array(
			'name' => $this->options["FAV_TAB_NAME"],
			'slug' => 'favorites',
			'parent_url' => $profile_link,
			'parent_slug' => $bp->activity->slug,
			'screen_function' => 'bp_activity_screen_favorites',
			'position' => 12
		) );
	}

	/**
	 * REDIRECT LOGOUT FROM NEWSFEED
	 * @since BuddyBoss 3.0
	 */
	function newsfeed_logout_redirect_url()
	{
		global $bp;

		$action = $bp->current_action;

		if ( $action == 'news-feed' )
		{
			add_filter( 'logout_url', array( $this, 'set_newsfeed_logout_url' ) );
		}
	}
	function set_newsfeed_logout_url( $logout_url )
	{
		global $bp;

		$parts = explode( 'redirect_to', $logout_url );

		if ( count( $parts ) > 1 )
		{
			$domain = (!empty($bp->displayed_user->id)) ? $bp->displayed_user->domain : $bp->loggedin_user->domain;

			$profile_link = $domain . $bp->activity->slug . '/';

			$logout_url = $parts[0] . '&redirect_to=' . urlencode( $profile_link );
		}

		return $logout_url;
	}

	/**
	 * RENAME WORDPRESS MENU ITEMS
	 *
	 * @since BuddyBoss 2.0
	 */
	function update_wp_menus()
	{
		global $wp_admin_bar, $bp;

		$domain = $bp->loggedin_user->domain;

		$profile_link = $domain . $bp->activity->slug . '/';

		$activity_link = trailingslashit( $domain . $bp->activity->slug );

		// ADD ITEMS
		if ( is_user_logged_in() )
		{
			// REMOVE ITEMS
			$wp_admin_bar->remove_menu('my-account-activity-mentions');
			$wp_admin_bar->remove_menu('my-account-activity-personal');
			$wp_admin_bar->remove_menu('my-account-activity-favorites');
			$wp_admin_bar->remove_menu('my-account-activity-friends');
			$wp_admin_bar->remove_menu('my-account-activity-groups');

			// Change menus item to link to wall
			$user_info = $wp_admin_bar->get_node( 'user-info' );
			if ( ! is_object( $user_info ) ) $user_info = new stdClass();
			$user_info->href = trailingslashit( $activity_link );
			$wp_admin_bar->add_node( $user_info );

			$my_acct = $wp_admin_bar->get_node( 'my-account' );
			if ( ! is_object( $my_acct ) ) $my_acct = new stdClass();
			$my_acct->href = trailingslashit( $activity_link );
			$wp_admin_bar->add_node( $my_acct );


			// Change 'Activity' to 'Wall'
			$wp_admin_bar->add_menu( array(
				'parent' => 'my-account-buddypress',
				'id'     => 'my-account-' . $bp->activity->id,
				'title'  => $this->options["PERSONAL_TAB_NAME"],
				'href'   => trailingslashit( $activity_link )
			) );

			// Personal/Wall
			$wp_admin_bar->add_menu( array(
				'parent' => 'my-account-' . $bp->activity->id,
				'id'     => 'my-account-' . $bp->activity->id . '-wall',
				'title'  => $this->options["PERSONAL_TAB_NAME"],
				'href'   => trailingslashit( $activity_link )
			) );

			// News Feed
			$wp_admin_bar->add_menu( array(
				'parent' => 'my-account-' . $bp->activity->id,
				'id'     => 'my-account-' . $bp->activity->id . '-feed',
				'title'  => $this->options["FEED_TAB_NAME"],
				'href'   => trailingslashit( $activity_link . 'news-feed' )
			) );

			// Favorites
			$wp_admin_bar->add_menu( array(
				'parent' => 'my-account-' . $bp->activity->id,
				'id'     => 'my-account-' . $bp->activity->id . '-favorites',
				'title'  => $this->options["FAV_TAB_NAME"],
				'href'   => trailingslashit( $activity_link . 'favorites' )
			) );
		}
	}

	/**
	 * WRAPPER FUNCTION, WILL BE DEPRECATED
	 */
	function is_friend($id)
	{
		return buddyboss_is_my_friend($id);
	}

	/**
	 * GET WALL ACTIVITES
	 */
	function get_wall_activities( $page = 0, $per_page=20 )
	{
		global $bp, $wpdb, $buddyboss_ajax_qs;

		$min = ($page>0)? ($page-1) * $per_page : 0;
		$max = ($page+1) * $per_page;
		$per_page = bp_get_activity_per_page();
		buddyboss_log(" per page $per_page");

		if (isset($bp->loggedin_user) && isset($bp->loggedin_user->id) && $bp->displayed_user->id == $bp->loggedin_user->id)
		{
			$myprofile = true;
		}
		else {
			$myprofile = false;
		}
		$wpdb->show_errors = BUDDYBOSS_DEBUG;
		$user_id = $bp->displayed_user->id;

		buddyboss_log("Looking at $user_id" );
		$user_filter = $bp->displayed_user->domain;

		//buddyboss_log($friend_id_list);
		$table = bp_core_get_table_prefix() . 'bp_activity';
		$table2 = bp_core_get_table_prefix() . 'bp_activity_meta';

		// Default WHERE
		$where = "WHERE ( $table.user_id = $user_id AND $table.type!='activity_comment' AND $table.type!='friends' )";

		// Add @mentions
		$mentions_modifier = "OR ( $table.content LIKE '%$user_filter%' AND $table.type!='activity_comment' ) ";

		// If we have a filter enabled, let's handle that
		$ajax_qs = ! empty( $buddyboss_ajax_qs )
						 ? wp_parse_args( $buddyboss_ajax_qs )
						 : false;

		if ( is_array( $ajax_qs ) && isset( $ajax_qs['action'] ) )
		{
			// Clear the @mentions modifier
			$mentions_modifier = '';

			$filter_qs = $ajax_qs['action'];

			// Check for commas and adjust
			if ( strpos( $filter_qs, ',' ) )
			{
				$filters = explode( ',', $filter_qs );
			}
			else {
				$filters = (array)$filter_qs;
			}

			// Clean each filter
			$filters_clean = array();

			foreach( $filters as $filter )
			{
				$filters_clean[] = $wpdb->escape( $filter );
			}

			$filter_sql = "AND ( $table.type='" . implode( "' OR $table.type='", $filters_clean ) . "' )";

			$where = "WHERE ( $table.user_id = $user_id $filter_sql )";
		}

		// var_dump( $where, $ajax_qs );
		// var_dump( $mentions_modifier );

		$qry = "SELECT DISTINCT $table.id FROM $table LEFT JOIN $table2 ON $table.id=$table2.activity_id
		$where
		$mentions_modifier
		ORDER BY date_recorded DESC LIMIT $min, 40";

		// echo $qry;

		$activities = $wpdb->get_results( $qry, ARRAY_A );
		//var_dump($wpdb->print_error());
		buddyboss_log($qry);

		buddyboss_log($activities);


		if ( empty( $activities ) ) return null;

		$tmp = array();

		foreach ($activities as $a)
		{
			$tmp[] = $a["id"];

		}

		$activity_list = implode(",", $tmp);
		return $activity_list;
	}


	/**
	 * GET FEED ACTIVITES
	 */
	function get_feed_activities( $page = 0, $per_page = 20 )
	{
		global $bp, $wpdb, $buddyboss_ajax_qs;

		$min = ($page>0)? ($page-1) * $per_page : 0;
		$max = ($page+1) * $per_page;
		$per_page = bp_get_activity_per_page();

		buddyboss_log( "per page: $per_page" );

		if ( isset( $bp->loggedin_user ) && isset( $bp->loggedin_user->id )
				 && $bp->displayed_user->id == $bp->loggedin_user->id )
		{
			$myprofile = true;
		}
		else {
			$myprofile = false;
		}

		$wpdb->show_errors = BUDDYBOSS_DEBUG;

		$user_id = $bp->displayed_user->id;

		$user_name = $bp->displayed_user->userdata->user_login;

		$filter = $bp->displayed_user->domain;

		buddyboss_log( "Looking at $user_id" );

		// Get friend's user IDs
		$user_ids = friends_get_friend_user_ids(	$user_id, false, false );

		// Add logged in user to news feed results
		// $user_ids[] = $user_id;

		$user_list = implode( ',', $user_ids );

		buddyboss_log( $friend_id_list );
		$table = bp_core_get_table_prefix() . 'bp_activity';
		$table2 = bp_core_get_table_prefix() . 'bp_activity_meta';

		// Default WHERE
		$where = "WHERE ( $table.user_id IN ($user_list) AND $table.type != 'activity_comment' )";

		// Add when user joined a group
		$group_modifier = "OR ( $table.user_id = $user_id AND $table.component = 'groups' ) ";

		// If we have a filter enabled, let's handle that
		$ajax_qs = ! empty( $buddyboss_ajax_qs )
						 ? wp_parse_args( $buddyboss_ajax_qs )
						 : false;

		if ( is_array( $ajax_qs ) && isset( $ajax_qs['action'] ) )
		{
			// Clear group modifier
			$group_modifier = '';

			$filter_qs = $ajax_qs['action'];

			// Check for commas and adjust
			if ( strpos( $filter_qs, ',' ) )
			{
				$filters = explode( ',', $filter_qs );
			}
			else {
				$filters = (array)$filter_qs;
			}

			// Clean each filter
			$filters_clean = array();

			foreach( $filters as $filter )
			{
				$filters_clean[] = $wpdb->escape( $filter );
			}

			$filter_sql = "AND ( $table.type='" . implode( "' OR $table.type='", $filters_clean ) . "' )";

			$where = "WHERE ( $table.user_id IN ($user_list) $filter_sql )";
		}

		// var_dump( $where, $ajax_qs );
		// var_dump( $group_modifier );

		$qry = "SELECT DISTINCT $table.id FROM $table LEFT JOIN $table2 ON $table.id = $table2.activity_id
		$where
		$group_modifier
		ORDER BY date_recorded DESC LIMIT $min, 40";

		$qry_res = $wpdb->get_results( $qry, ARRAY_A );

		// echo $qry;

		buddyboss_log( $qry );

		// $qry2 = "SELECT DISTINCT $table.id FROM $table LEFT JOIN $table2 ON $table.id=$table2.activity_id
		// WHERE ( $table.user_id = $user_id AND $table.type!='activity_comment' AND $table.type!='friends' )
		// $friends_modifier
		// $group_modifier
		// $friendships
		// $mentions_modifier
		// ORDER BY date_recorded DESC LIMIT $min, 40";

		// $qry2_res = $wpdb->get_results( $qry, ARRAY_A );

		// buddyboss_log( $qry2 );

		// $activities = array_merge( $qry_res, $qry2_res );

		$activities = $qry_res;

		buddyboss_log( $activities );

		if ( empty( $activities ) ) return null;

		$tmp = array();

		foreach ($activities as $a)
		{
			$tmp[] = $a["id"];
		}

		$activity_list = implode(",", $tmp);

		return $activity_list;
	}

	/**
	 * Retrieve likes for current activity (within activity loop)
	 *
	 * @since 2.0
	 */
	function has_likes( $actid = null )
	{
		if ( $actid === null ) $actid = bp_get_activity_id();

		return bp_activity_get_meta( $actid, 'favorite_count' );
	}

} // end of BUDDYBOSS_WALL class



/**
 * ACTIVATION AND DEACTIVATION CODE
 */
function buddyboss_wall_on_activate()
{
	return 'The Profile Wall was activated successfully';
}


/**
 * Deregister the BuddyBoss Wall Component
 *
 * @since BuddyBoss 2.0
 */
function buddyboss_wall_on_deactivate()
{
	return '';
}

/**
 * Format @mention notifications to redirect to the wall
 * @param  [type] $notification [description]
 * @return [type]              [description]
 */
function buddyboss_format_mention_notification( $notification, $at_mention_link, $total_items, $activity_id, $poster_user_id )
{
	global $wp_admin_bar, $bp;

	$domain = $bp->loggedin_user->domain;
	$activity_link = trailingslashit( $domain . $bp->activity->slug );
	$at_mention_link  = bp_loggedin_user_domain() . bp_get_activity_slug() . '/mentions/';
	$at_mention_title = sprintf( __( '@%s Mentions', 'buddyboss' ), bp_get_loggedin_user_username() );

	if ( (int) $total_items > 1 ) {
		$text = sprintf( __( 'You have %1$d new mentions', 'buddyboss' ), (int) $total_items );
	} else {
		$user_fullname = bp_core_get_user_displayname( $poster_user_id );
		$text =  sprintf( __( '%1$s mentioned you', 'buddyboss' ), $user_fullname );
	}

	if ( is_array( $notification ) )
	{
		$notification['link'] = $activity_link;
	}
	else {
		$notification = '<a href="' . $activity_link . '" title="' . $at_mention_title . '">' . $text . '</a>';
	}

	return $notification;
}

/* FILTERS */
if ( BUDDYBOSS_WALL_ENABLED )
{
	add_action( 'bp_before_activity_comment', 'wall_add_likes_comments' );
	add_filter( 'bp_get_activity_action', 'wall_read_filter' );
	add_filter( 'bp_activity_after_save', 'wall_input_filter' );
	add_filter( 'bp_ajax_querystring', 'wall_qs_filter', 111 );
	add_filter( 'bp_activity_multiple_at_mentions_notification', 'buddyboss_format_mention_notification', 10, 5 );
	add_filter( 'bp_activity_single_at_mentions_notification', 'buddyboss_format_mention_notification', 10, 5 );
	add_action( 'bp_activity_screen_my_activity', 'bp_activity_reset_my_new_mentions' );
}

/**
 * This adds how many people liked an item
 *
 * @since BuddyBoss 2.0
 */
function wall_add_like_action()
{
	global $buddyboss_wall;

	if ( isset($_POST['action']) && $_POST['action'] == 'new_activity_comment' )
		return false;

	$actid = (int) bp_get_activity_id();

	if ( $actid === 0 )
		return false;

	if ( isset( $buddyboss_wall->likes_store[$actid] ) )
		return false;

	$count = (int) bp_activity_get_meta( $actid, 'favorite_count' );

	$buddyboss_wall->likes_store[$actid] = 1;

	if ( $count === 0 )
		return false;

	$subject = ($count == 1) ? __( 'person' , 'buddyboss' ) : __( 'people' , 'buddyboss' );
	$verb = ($count > 1) ? __( 'like' , 'buddyboss' ) : __( 'likes' , 'buddyboss' );
	$this_txt = __( 'this' , 'buddyboss' );

	$like_html = sprintf( '<li class="activity-like-count">%d %s %s %s.</li>', $count, $subject, $verb, $this_txt );

	echo $like_html;

}

/**
 * This adds how many people liked an item
 *
 * @since BuddyBoss 2.0
 */
function wall_add_likes_comments()
{
	echo get_wall_add_likes_comments( bp_get_activity_id() );
}
function get_wall_add_likes_comments( $actid )
{
	global $bp;
	static $ran = array();

	// Only get likes for parent comment items, this can be done else where
	// but let's take care if it at the source of the action
	if ( isset( $_POST['action'] ) && $_POST['action'] == 'new_activity_comment' )
		return;

	// Only run once
	if ( isset( $ran[$actid] ) && $ran[$actid] === true )
		return;

	$ran[$actid] = true;

	$actid = (int) $actid;

	if ( $actid === 0 )
		return false;

	$count = (int) bp_activity_get_meta( $actid, 'favorite_count' );

	if ( $count === 0 )
		return false;

	$subject = ($count == 1) ? __( 'person' , 'buddyboss' ) : __( 'people' , 'buddyboss' );
	$verb = ($count > 1) ? __( 'like' , 'buddyboss' ) : __( 'likes' , 'buddyboss' );
	$count_txt = number_format_i18n( $count ) . ' ';

	// Change wording to YOU like this if the user is logged in and likes this item
	if ( is_user_logged_in() )
	{
		$user_id = $bp->loggedin_user->id;

		$favorite_activity_entries = bp_get_user_meta( $user_id, 'bp_favorite_activities', true );

		if ( !empty( $favorite_activity_entries ) && in_array( $actid, $favorite_activity_entries ) )
		{
			$count_txt = '';
			$verb = __( 'like' , 'buddyboss' );

			// If count is 1, only you like this
			if ( $count == 1 )
			{
				$subject = __( 'You', 'buddyboss' );
			}
			// If count is > 1, you and $count-1 other people like this
			else {
				$others = $count-1;
				$subject = ( $others == 1 )
								 ? __( sprintf( 'You and %d other person', number_format_i18n( $others ) ), 'buddyboss' )
								 : __( sprintf( 'You and %d other people', number_format_i18n( $others ) ), 'buddyboss' );
			}
		}
	}

	$like_html = "<li class=\"activity-like-count\">$count_txt" .
							 "$subject $verb " . __( 'this' , 'buddyboss' ) . ".</li>";

	$wrap_in_ul = false;

	// If we don't have a current activity ID we're not in the loop and
	// this is and AJAX request
	if ( ! bp_get_activity_id() )
	{
		if ( bp_has_activities( 'include=' . $actid ) ): while ( bp_activities() ): bp_the_activity();

		// If there are no comments we need to wrap this in a UL
		if ( ! bp_activity_get_comment_count() )
		{
			$wrap_in_ul = true;
		}

		endwhile; endif;
	}

	// If we do have an activity ID we can get comment count easily
	else {

		// If there are no comments we need to wrap this in a UL
		if ( ! bp_activity_get_comment_count() )
		{
			$wrap_in_ul = true;
		}
	}


	if ( $wrap_in_ul )
		$like_html = '<ul>' . $like_html . '</ul>';

	return $like_html;
}

/**
 * This filters wall actions, when reading an item it will convert it to use wall markup
 *
 * @since BuddyBoss 2.0
 */
function wall_read_filter( $action )
{
	global $activities_template;

	$curr_id = $activities_template->current_activity;

	$act_id = $activities_template->activities[$curr_id]->id;

	$bbwall_action = bp_activity_get_meta( $act_id, 'buddyboss_wall_action' );

	// file_put_contents( ABSPATH . 'lglg.txt', print_r( $bbwall_action, true )."\n", FILE_APPEND );
	// file_put_contents( ABSPATH . 'lglg.txt', print_r( $curr_id, true )."\n", FILE_APPEND );
	// file_put_contents( ABSPATH . 'lglg.txt', print_r( $act_id, true )."\n\n", FILE_APPEND );

	if ( $bbwall_action )
	{
		$with_meta = $bbwall_action . ' <a class="activity-time-since"><span class="time-since">' . bp_core_time_since( bp_get_activity_date_recorded() ) . '</span></a>';

		if ( $with_meta )
			return $with_meta;

		return $bbwall_action;
	}

	return $action;
}

/**
 * This will save wall related data to the activity meta table when a new wall post happens
 *
 * @since BuddyBoss 2.0
 */
function wall_input_filter( &$activity )
{
	global $bp, $buddyboss_wall;

	$user = $bp->loggedin_user;
	$tgt  = $bp->displayed_user;
	$new_action = false;

	// If we're on an activity page (user's own profile or a friends), check for a target ID
	if ( $bp->current_action == 'just-me' && (!isset($tgt->id) || $tgt->id == 0) ) return;

	// It's either an @ mention, status update, or forum post.
	if ( ($bp->current_action == 'just-me' && $user->id == $tgt->id) || $bp->current_action == 'forum' )
	{
		if (!empty($activity->content))
		{
			$mentioned = bp_activity_find_mentions($activity->content);
			$uids = array();
			$usernames = array();

			// Get all the mentions and store valid usernames in a new array
			foreach( (array)$mentioned as $mention ) {
				if ( bp_is_username_compatibility_mode() )
					$user_id = username_exists( $mention );
				else
					$user_id = bp_core_get_userid_from_nicename( $mention );

				if ( empty( $user_id ) )
					continue;

				$uids[] = $user_id;
				$usernames[] = $mention;
			}

			$len = count($uids);
			$mentions_action = '';

			// It's mentioning one person
			if($len == 1)
			{
				$user_id =
				$tgt = bp_core_get_core_userdata( (int) $uids[0] );
				$user_url  = '<a href="'.$user->domain.'">'.$user->fullname.'</a>';
				$tgt_url  = '<a href="'.bp_core_get_userlink( $uids[0], false, true ).'">@'.$tgt->user_login.'</a>';

				$mentions_action = " " . __( 'mentioned' , 'buddyboss' ) ." ". $tgt_url;
			}

			// It's mentioning multiple people
			elseif($len > 1)
			{
				$user_url  = '<a href="'.$user->domain.'">'.$user->fullname.'</a>';
				$un = '@'.join(',@', $usernames);
				$mentions_action = $user_url. " " . __( 'mentioned' , 'buddyboss' ) ." ".$len." " . __( 'people' , 'buddyboss' );
			}

			// If it's a forum post let's define some forum topic text
			if ( $bp->current_action == 'forum' )
			{
				$new_action = str_replace( ' replied to the forum topic', $mentions_action.' in the forum topic', $activity->action);
			}

			// If it's a plublic message let's define that text as well
			elseif ($len > 0) {
				$new_action = $user_url. " " .$mentions_action.' ' . __( 'in a public message' , 'buddyboss' );
			}

			// Otherwise it's a normal status update
			else {
				$new_action = false;
			}

		}
	}

	// It's a normal wall post because the displayed ID doesn't match the logged in ID
	// And we're on activity page
	elseif ( $bp->current_action == 'just-me' && $user->id != $tgt->id ) {
		$user_url  = '<a href="'.$user->domain.'">'.$user->fullname.'</a>';
		$tgt_url  = '<a href="'.$tgt->domain.'">'.$tgt->fullname.'\'s</a>';

		// if a user is on his own page it is an update
		$new_action = sprintf( __( '%s wrote on %s Wall', 'buddyboss' ), $user_url , $tgt_url );
	}

	if ( $new_action )
	{
		bp_activity_update_meta( $activity->id, 'buddyboss_wall_action', $new_action );
	}

}

function cancel_bp_has_activities()
{
	return false;
}
function wall_qs_filter( $qs )
{
	global $bp, $buddyboss_wall, $buddyboss_ajax_qs;

	$buddyboss_ajax_qs = $qs;

	$action = $bp->current_action;

	if ( $action != "just-me" && $action != "news-feed" )
	{
		// if we're on a different page than wall pass qs as is
		return $qs;
	}

	// else modify it to include wall activities

	// see if we have a page string
	$page = 1;
	if ( preg_match("/page=\d+/", $qs, $m) )
		$page = intval(str_replace("page=", "", $m[0])); // if so grab the number

	$activities = $action == 'just-me'
							? $buddyboss_wall->get_wall_activities( $page ) // load wall activities for this page
							: $buddyboss_wall->get_feed_activities( $page ); // load feed activities for this page

	if ( ! $activities )
	{
		add_filter( 'bp_has_activities', 'cancel_bp_has_activities' );
	}

	$nqs = "include=$activities";

	return $nqs;
}

/**
 * Check if the current profile a user is on is a friend or not
 *
 * @since BuddyBoss 2.0
 */
function buddyboss_is_my_friend( $id=null )
{
	global $bp;
	if ( $id === null ) $id = $bp->displayed_user->id;
	return 'is_friend' == BP_Friends_Friendship::check_is_friend( $bp->loggedin_user->id, $id );
}

// AJAX update posting
// Credt: POST IN WIRE by Brajesh Singh
function buddyboss_wall_post_update()
{
	global $bp;

	// Check the nonce
	check_admin_referer( 'post_update', '_wpnonce_post_update' );

	if ( !is_user_logged_in() ) {
		echo '-1';
		return false;
	}

	if ( empty( $_POST['content'] ) ) {
		echo '-1<div id="message" class="error"><p>' . __( 'Please enter some content to post.', 'buddyboss' ) . '</p></div>';
		return false;
	}

	$activity_id = false;

	if ( empty( $_POST['object'] ) && function_exists( 'bp_activity_post_update' ) )
	{
		if ( ! bp_is_my_profile() && bp_is_user() )
		{
			$content = "@". bp_get_displayed_user_username()." ".$_POST['content'];
		}
		else {
			$content = $_POST['content'];
		}

		$activity_id = bp_activity_post_update( array( 'content' => $content ) );
	}
	elseif ( $_POST['object'] == 'groups' )
	{
		if ( !empty( $_POST['item_id'] ) && function_exists( 'groups_post_update' ) )
		{
			$activity_id = groups_post_update( array( 'content' => $_POST['content'], 'group_id' => $_POST['item_id'] ) );
		}
	}
	else {
		$activity_id = apply_filters( 'bp_activity_custom_update', $_POST['object'], $_POST['item_id'], $_POST['content'] );
	}

	if ( ! $activity_id )
	{
		echo '-1<div id="message" class="error"><p>' . __( 'There was a problem posting your update, please try again.', 'buddyboss' ) . '</p></div>';
		return false;
	}

	if ( bp_has_activities ( 'include=' . $activity_id ) ) : ?>
	<?php while ( bp_activities() ) : bp_the_activity(); ?>
	<?php bp_get_template_part( 'activity/entry' ) ?>
	<?php endwhile; ?>
	<?php endif;
}


/**
 * Mark an activity as a favourite via a POST request.
 *
 * @return string HTML
 * @since BuddyBoss 3.0
 */
function buddyboss_mark_activity_favorite()
{
	// Bail if not a POST action
	if ( 'POST' !== strtoupper( $_SERVER['REQUEST_METHOD'] ) )
		return;

	if ( bp_activity_add_user_favorite( $_POST['id'] ) )
		$resp['but_text'] = __( 'Unlike', 'buddyboss' );
	else
		$resp['but_text'] = __( 'Like', 'buddyboss' );

	$resp['num_likes'] = get_wall_add_likes_comments( (int)$_POST['id'] );
	$resp['like_count'] = (int) bp_activity_get_meta( (int)$_POST['id'], 'favorite_count' );

	echo json_encode( $resp );

	exit;
}


/**
 * Un-favourite an activity via a POST request.
 *
 * @return string HTML
 * @since BuddyBoss 3.0
 */
function buddyboss_unmark_activity_favorite() {
	// Bail if not a POST action
	if ( 'POST' !== strtoupper( $_SERVER['REQUEST_METHOD'] ) )
		return;

	if ( bp_activity_remove_user_favorite( $_POST['id'] ) )
		$resp['but_text'] = __( 'Like', 'buddyboss' );
	else
		$resp['but_text'] = __( 'Unlike', 'buddyboss' );

	$resp['num_likes'] = get_wall_add_likes_comments( (int)$_POST['id'] );
	$resp['like_count'] = (int) bp_activity_get_meta( (int)$_POST['id'], 'favorite_count' );

	echo json_encode( $resp );

	exit;
}

function buddyboss_remove_original_update_functions()
{
	/* actions */
	if ( BUDDYBOSS_WALL_ENABLED )
	{
		// Remove actions related to posting and likes
		remove_action( 'wp_ajax_post_update', 'bp_dtheme_post_update' );
		remove_action( 'wp_ajax_post_update', 'bp_legacy_theme_post_update' );
		remove_action( 'wp_ajax_activity_mark_fav',   'bp_legacy_theme_mark_activity_favorite' );
		remove_action( 'wp_ajax_activity_mark_unfav', 'bp_legacy_theme_unmark_activity_favorite' );

		// Add our custom actions to handle posting and likes
		add_action( 'wp_ajax_activity_mark_unfav', 'buddyboss_unmark_activity_favorite' );
		add_action( 'wp_ajax_activity_mark_fav', 'buddyboss_mark_activity_favorite' );
		add_action( 'wp_ajax_post_update', 'buddyboss_wall_post_update' );
	}
}
add_action( 'after_setup_theme', 'buddyboss_remove_original_update_functions', 9999 );
?>