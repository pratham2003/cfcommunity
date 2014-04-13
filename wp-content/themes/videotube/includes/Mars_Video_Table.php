<?php
/**
 * VideoTube Video Table Custom
 * Add LIKES AND VIEWED column in VIDEO table.
 *
 * @author 		Toan Nguyen
 * @category 	Core
 * @version     1.0.0
 */
if( !defined('ABSPATH') ) exit;
if( !class_exists('Mars_Video_Table') ){
	class Mars_Video_Table {
		function __construct() {
			add_filter('manage_edit-video_columns' , array($this,'cpt_columns'));
			add_action( "manage_video_posts_custom_column", array($this,'modify_column'), 10, 2 );
		}
		function cpt_columns($columns){
			$new_columns = array(
				'source'	=>	__('Source','mars'),
				'likes'	=>	__('Likes','mars'),
				'views'	=>	__('Views','mars')
			);
		    return array_merge($columns, $new_columns);			
		}
		function modify_column($column, $post_id){
			switch ($column) {
				case 'likes':
					print mars_get_like_count($post_id);
				break;
				case 'views':
					print mars_get_count_viewed();				
				break;
				case 'source':
					if (get_post_meta($post_id, 'video_url', true) != ''){
						print '<a href="'.get_post_meta($post_id, 'video_url', true).'">'.get_post_meta($post_id,'real_video_source',true).'</a>';
					}
					elseif( get_post_meta($post_id, 'video_file', true) != '' ){
						_e('Hosted','mars');
					}
					elseif( get_post_meta($post_id, 'video_frame', true) !='' ){
						_e('iFrame/Object','mars');
					}
				break;
			}	
		}	
	}
	new Mars_Video_Table();
}