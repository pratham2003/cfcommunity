<?php
//Block certain activity types from being added
function bp_activity_dont_save( $activity_object ) {
$exclude = array(
        'updated_profile',
        'new_member',
        'new_avatar',
        'friendship_created',
        'joined_group'
    );

// if the activity type is empty, it stops BuddyPress BP_Activity_Activity::save() function
if( in_array( $activity_object->type, $exclude ) )
$activity_object->type = false;

}
add_action('bp_activity_before_save', 'bp_activity_dont_save', 10, 1 );

function myprofile_shortcode() {
  $myprofileurl = bp_get_loggedin_user_link() ;
  return $myprofileurl;
}
add_shortcode('myprofileurl', 'myprofile_shortcode');

//Auto accept invitations
define( 'WELCOME_PACK_AUTOACCEPT_INVITATIONS', true );

// add custom post type business to the activity stream
add_filter ( 'bp_blogs_record_post_post_types', 'activity_publish_custom_post_types',1,1 );
function activity_publish_custom_post_types( $post_types ) {
$post_types[] = 'video';
return $post_types;
}

add_filter('bp_blogs_activity_new_post_action', 'record_cpt_activity_action', 1, 3);
function record_cpt_activity_action( $activity_action, $post, $post_permalink ) {
global $bp;
if( $post->post_type == 'video' ) {

$activity_action = sprintf( __( '%1$s added the video %2$s to the <a href="http://videos.cfcommunity.net">CF Video Library</a>', 'buddypress' ), bp_core_get_userlink( (int)$post->post_author ), '<a href="' . $post_permalink . '">' . $post->post_title . '</a>', get_blog_option($blog_id, 'blogname') );

}
return $activity_action;
}
?>