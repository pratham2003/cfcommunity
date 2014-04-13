<?php
/**
 * VideoTube Submit Video
 * Add [msubmit] shortcode to create the Submit Video page.
 * @author 		Toan Nguyen
 * @category 	Core
 * @version     1.0.0
 */
if( !defined('ABSPATH') ) exit;
if( !class_exists('Mars_ShortcodeSubmitVideo') ){
	class Mars_ShortcodeSubmitVideo {
		function __construct() {
			add_action('init', array($this,'add_shortcodes'));
			add_action('wp_ajax_mars_submit_video', array($this,'action_form'));
			add_action('wp_ajax_nopriv_mars_submit_video', array($this,'action_form'));
		}
		function add_shortcodes(){
			add_shortcode('videotube_upload', array($this,'videotube_upload'));
		}
		function videotube_upload( $attr, $content ){
			global $videotube;
			global $post;
			$html = null;
			$submit_roles = isset( $videotube['submit_roles'] ) ? (array)$videotube['submit_roles'] : 'author';
			if( count( $submit_roles ) == 1 ){
				$submit_roles = (array)$submit_roles;
			}	
			//print_r($submit_roles);
			### 0 is not allow guest, 1 is only register.
			$submit_permission = isset( $videotube['submit_permission'] ) ? $videotube['submit_permission'] : 0;
			$user_id = get_current_user_id();
			$current_user_role = mars_get_user_role( $user_id );
			### Check if Admin does not allow Visitor submit the video.
			if( $submit_permission == 0 && !$user_id ){
				$html .= '
					<div class="alert alert-warning">'.sprintf( __('Please %s/%s to continue.','mars') ,'<a href="'.wp_login_url( get_permalink() ).'">'.__('Login','mars').'</a>','<a href="'.wp_registration_url().'">'.__('Register','mars').'</a>').'</div>
				';				
			}
			//elseif( $submit_permission == 0 && !in_array( $current_user_role, $submit_roles) && $current_user_role != 'administrator'){
			elseif( $submit_permission == 0 && !in_array( $current_user_role, $submit_roles)){
				$html .= '
					<div class="alert alert-warning">'.__('You don\'t have the right permission to access this feature.','mars').'</div>
				';		
			}
			else{
				$args = array(
					'show_option_none'   => __('None','mars'),
					'orderby'            => 'ID', 
					'order'              => 'ASC',
					'show_count'         => 0,
					'hide_empty'         => 1, 
					'child_of'           => 0,
					'echo'               => 0,
					'selected'           => 0,
					'hierarchical'       => 0, 
					'name'               => 'cat',
					'id'                 => '',
					'class'              => 'form-control',
					'depth'              => 0,
					'tab_index'          => 0,
					'taxonomy'           => 'categories',
					'hide_if_empty'      => false,
				);				
				$html .= '
					<form role="form" action="" method="post" id="mars-submit-video-form">
					  <div class="form-group post_title">
					    <label for="post_title">'.__('Video Title','mars').'</label>
					    <input type="text" class="form-control" name="post_title" id="post_title">
					    <span class="help-block"></span>
					  </div>
					  <div class="form-group video_url">
					    <label for="video_url">'.__('Video Link','mars').'</label>
					    <input type="text" class="form-control" name="video_url" id="video_url" placeholder="Example: http://www.youtube.com/watch?v=X6pQ-pNSnRE">
					    <span class="help-block"></span>
					  </div>
					  <div class="form-group post_content">
					    <label for="post_content">'.__('Video Description','mars').'</label>';
						if( $videotube['submit_editor'] == 1 ){
							$html .= mars_get_editor('', 'post_content', 'post_content');	
						}
						else{
							$html .= '<textarea name="post_content" id="post_content" class="form-control" rows="3"></textarea>';
						}
					  $html .= '<span class="help-block"></span>';
					  $html .= '</div>
					  <div class="form-group">
					    <label for="key">'.__('Video Tag','mars').'</label>
					    <input type="text" class="form-control" name="key" id="key">
					  </div>
					  <div class="form-group">
					    <label for="category">'.__('Category','mars').'</label>';				
					    $html .= wp_dropdown_categories($args);
					  $html .= '</div>';
					  ### Recaptcha
					  if( $videotube['submit_captcha'] ){
					  	$error = null;
					  	if( !function_exists('recaptcha_check_answer') ){
					  		$html .= '<div class="alert alert-danger">'.__('ERROR: Please install recaptchalib Class plugin.','mars').'</div>';
					  	}
					  	elseif( empty( $videotube['public_key'] ) ){
					  		$html .= '<div class="alert alert-danger">'.__('ERROR: Please contact your Administrator for providing Public Key to continue.','mars').'</div>';
					  	}
					  	elseif( empty( $videotube['private_key'] ) ){
					  		$html .= '<div class="alert alert-danger">'.__('ERROR: Please contact your Administrator for providing Private Key to continue.','mars').'</div>';
					  	}
					  	else{
						  	$html .= '<div class="form-group recaptcha_response_field">';
						  		$html .= recaptcha_get_html($videotube['public_key'], $error);
						  		$html .= '<span class="help-block"></span>';
						  	$html .= '</div>';
					  	}
					  }
					  $html .= '<button type="submit" class="btn btn-default">Submit</button>
					  <input type="hidden" name="current_page" value="'.$post->ID.'">
					  <input type="hidden" name="action" value="mars_submit_video">
					  '.wp_nonce_field('submit_video_act','submit_video',true,false).'
					  
					</form>
				';
				
			}
			return do_shortcode( $html );
		}
		function action_form(){
			global $videotube;
			$post_title = wp_filter_nohtml_kses( $_POST['post_title'] );
			$video_url = wp_filter_nohtml_kses( $_POST['video_url'] );
			$post_content = wp_filter_nohtml_kses( $_POST['post_content'] );
			$key = wp_filter_nohtml_kses( $_POST['key'] );
			$cat = wp_filter_nohtml_kses( $_POST['cat'] );
			$user_id = get_current_blog_id() ? get_current_blog_id() : $videotube['submit_assigned_user'];
			$post_status = $videotube['submit_status'] ? $videotube['submit_status'] : 'pending'; 
			
			if( !$post_title ){
				echo json_encode(array(
					'resp'	=>	'error',
					'message'	=>	__('Video Title is required','mars'),
					'element_id'	=>	'post_title'
				));exit;
			}
			if( !$video_url ){
				echo json_encode(array(
					'resp'	=>	'error',
					'message'	=>	__('Video Link is required','mars'),
					'element_id'	=>	'video_url'
				));exit;				
			}
			if( !$post_content ){
				echo json_encode(array(
					'resp'	=>	'error',
					'message'	=>	__('Video Description is required','mars'),
					'element_id'	=>	'post_content'
				));exit;					
			}
			
			### Check recaptcha.
			if( $videotube['submit_captcha'] == 1 ){
				if( !function_exists('recaptcha_check_answer') ){
			  		require_once 'recaptchalib.php';
			  	}				
				# the response from reCAPTCHA
				$resp = null;
				# the error code from reCAPTCHA, if any
				$error = null;				
				if( !$_POST["recaptcha_response_field"] ){
					echo json_encode(array(
						'resp'	=>	'error',
						'message'	=>	__('Recaptcha is required','mars'),
						'element_id'	=>	'recaptcha_response_field'
					));exit;					
				}
				$resp = recaptcha_check_answer ($videotube['private_key'],
                                        $_SERVER["REMOTE_ADDR"],
                                        $_POST["recaptcha_challenge_field"],
                                        $_POST["recaptcha_response_field"]);
                if (!$resp->is_valid){
					echo json_encode(array(
						'resp'	=>	'error',
						'message'	=>	$error = $resp->error,
						'element_id'	=>	'recaptcha_response_field'
					));exit;
                }
			}
			
			$postarr = array(
				'post_title'	=>	$post_title,
				'post_content'	=>	$post_content,
				'post_type'	=>	'video',
				'post_author'	=>	$user_id,
				'post_status'	=>	$post_status,
				'comment_status'	=>	'open'
			);
			$post_id = wp_insert_post($postarr, true);
			
			if ( is_wp_error( $post_id ) ){
				echo json_encode(array(
					'resp'	=>	'error',
					'message'	=>	__('Error: Unable to create Video. Please try again.','mars')
				));exit;
			}
			
			###  update meta
			update_post_meta($post_id, 'video_url', $video_url);
			### update term
			if( $key ){
				wp_set_post_terms($post_id, $key,'key',true);	
			}
			if( $cat ){
				wp_set_post_terms($post_id, $cat,'categories',true);	
			}
			do_action('mars_save_post', $post_id);
			if( $post_status != 'publish' ){
				$redirect_to = $videotube['submit_redirect_to'] ? get_permalink( $videotube['submit_redirect_to'] ) : NULL;
				if( !$redirect_to ){
					echo json_encode(array(
						'resp'	=>	'success',
						'message'	=>	__('Congratulation, Your submit is waiting for approval.','mars'),
						'post_id'	=>	$post_id,
					));exit;
				}
				else{
					echo json_encode(array(
						'resp'	=>	'success',
						'message'	=>	__('Congratulation, Your submit is waiting for approval.','mars'),
						'post_id'	=>	$post_id,
						'redirect_to'	=>	$redirect_to
					));exit;					
				}
			}
			else{
				echo json_encode(array(
					'resp'	=>	'publish',
					'message'	=>	__('Congratulation, Your submit is published.','mars'),
					'post_id'	=>	$post_id,
					'redirect_to'	=>	get_permalink( $post_id )
				));exit;				
			}
		}
	}
	new Mars_ShortcodeSubmitVideo();
}