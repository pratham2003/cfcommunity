<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of RTMediaProBlockUsers
 *
 * @author ritz
 */
class RTMediaProBlockUsers {

    public function __construct() {
	$this->moderation_admin_page_hooks();
	add_action( 'wp_ajax_rtmedia_block_user',array($this, 'block_user'));
	add_action( 'wp_ajax_rtmedia_unblock_user',array($this, 'unblock_user'));
	add_filter( 'authenticate', array($this, 'restrict_blocked_user_login'), 30, 3);
    }

    function restrict_blocked_user_login( $user, $username, $password ) {
	if(!(empty($username) || empty($password))) {
	    $rtmedia_site_option = maybe_unserialize(rtmedia_get_site_option("rtmedia-blocked-users"));
	    $queryUser = get_user_by('login', $username);
	    if($rtmedia_site_option != "" && in_array($queryUser->ID, $rtmedia_site_option)) {
		$user = new WP_Error('authentication_failed', __('<strong>Authentication ERROR</strong>: You have been blocked by admin.', 'rtmedia'));
	    }
	}
	return $user;
    }

    function block_user() {
	$curr_user = wp_get_current_user();
	if(isset($_POST['author_id']) && $_POST['author_id'] != "" && $_POST['author_id'] != $curr_user->data->ID) {
	    $rtmedia_site_option = maybe_unserialize(rtmedia_get_site_option("rtmedia-blocked-users"));
	    if($rtmedia_site_option== "") {
		$rtmedia_site_option = array();
		$rtmedia_site_option[] = $_POST['author_id'];
	    } else {
		if(!in_array($_POST['author_id'], $rtmedia_site_option)) {
		    $rtmedia_site_option[] = $_POST['author_id'];
		}
	    }
	    rtmedia_update_site_option("rtmedia-blocked-users", $rtmedia_site_option);
	    echo "true";
	    wp_die();
	}
	echo "false";
	wp_die();
    }

    function unblock_user() {
	if(isset($_POST['author_id']) && $_POST['author_id'] != "") {
	    $rtmedia_site_option = maybe_unserialize(rtmedia_get_site_option("rtmedia-blocked-users"));
	    if(($key = array_search($_POST['author_id'], $rtmedia_site_option)) !== false) {
		unset($rtmedia_site_option[$key]);
	    }
	    rtmedia_update_site_option("rtmedia-blocked-users", $rtmedia_site_option);
	    echo "true";
	    wp_die();
	}
	echo "false";
	wp_die();
    }

    function moderation_admin_page_hooks() {
	add_filter("rtmedia_filter_admin_pages_array",array($this,"rtmedia_add_admin_page_array"), 11, 1);
	add_action ( 'admin_menu', array( $this, 'add_block_user_menu' ), 100 );
    }

    function rtmedia_add_admin_page_array($admin_pages) {
	$admin_pages[] = "rtmedia_page_rtmedia-blocked-users";
	return $admin_pages;
    }

    function add_block_user_menu() {
	add_submenu_page ( 'rtmedia-settings', __ ( 'Blocked Users', 'rtmedia' ), __ ( 'Blocked Users ', 'rtmedia' ), 'manage_options', 'rtmedia-blocked-users', array( $this, 'blocked_users_page' ) );
    }

    function blocked_users_page() {
	$rtmedia_site_option = maybe_unserialize(rtmedia_get_site_option("rtmedia-blocked-users"));
	echo "<h2>rtMedia: Blocked users</h2>";
	if($rtmedia_site_option == "" || sizeof($rtmedia_site_option) == 0) {
    ?>
	<p>There isn't any Blocked users.</p>
    <?php
	} else {
    ?>
	<div class="rtmedia-block-user-div wrap">
	    <table class="widefat">
		<tr>
		    <th>User</th>
		    <th>Action</th>
		</tr>
	<?php
	    foreach($rtmedia_site_option as $key => $user_id) {
		$user_info = get_userdata($user_id);
		$user_link = get_rtmedia_user_link ( $user_id );
	?>
		<tr>
		    <td><a href='<?php echo $user_link ?>' title='<?php echo $user_info->data->display_name; ?>'><?php echo $user_info->data->display_name; ?></a></td>
		    <td><a href='<?php echo $user_link ?>' title='<?php echo $user_info->data->display_name; ?>'>View</a> | <a href='#' id='user-<?php echo $user_id; ?>' onclick="rtmedia_unblock_user(this)">Unblock</a></td>
		</tr>
	<?php
	    }
	?>
	    </table>
	</div>
    <?php
	}
    }
}
