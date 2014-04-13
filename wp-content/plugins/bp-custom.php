<?php
/* This fixes the BP_ENABLE_MULTIBLOG avatar problem */
function nfm_bp_avtar_upload_path_correct($path){
    if ( is_multisite() ){
     //   $path = ABSPATH . get_blog_option( BP_ROOT_BLOG, 'upload_path' );
		$path = ABSPATH . 'wp-content/uploads/';
    }
    return $path;
}
add_filter('bp_core_avatar_upload_path', 'nfm_bp_avtar_upload_path_correct', 1);
 
function nfm_bp_avatar_upload_url_correct($url){
    if ( is_multisite() ){
        $url = get_blog_option( BP_ROOT_BLOG, 'siteurl' ) . "/wp-content/uploads";
    }
    return $url;
}
add_filter('bp_core_avatar_url', 'nfm_bp_avatar_upload_url_correct', 1);

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