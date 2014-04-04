<?php
/* This fixes the BP_ENABLE_MULTIBLOG avatar problem */
function nfm_bp_avtar_upload_path_correct($path){
    if ( bp_core_is_multisite() ){
     //   $path = ABSPATH . get_blog_option( BP_ROOT_BLOG, 'upload_path' );
		$path = ABSPATH . 'wp-content/uploads/';
    }
    return $path;
}
add_filter('bp_core_avatar_upload_path', 'nfm_bp_avtar_upload_path_correct', 1);
 
function nfm_bp_avatar_upload_url_correct($url){
    if ( bp_core_is_multisite() ){
        $url = get_blog_option( BP_ROOT_BLOG, 'siteurl' ) . "/wp-content/uploads";
    }
    return $url;
}
add_filter('bp_core_avatar_url', 'nfm_bp_avatar_upload_url_correct', 1);
?>