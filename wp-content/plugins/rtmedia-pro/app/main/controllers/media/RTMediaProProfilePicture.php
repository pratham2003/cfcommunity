<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of RTMediaProProfileImage
 *
 * @author Pushpak
 */
class RTMediaProProfilePicture extends RTMediaUserInteraction{

	function __construct() {
		$defaults = array(
		'action' => 'profile-picture',
		'label' => __('Set as Profile Pic'),
		'plural' => '',
		'undo_label' => 'set',
		'privacy' => 60, //60,
		'countable' => false,
		'single' => false,
		'repeatable' => false,
		'undoable' => false,
                'icon_class' => 'rtmicon-user'
		);
                add_filter('get_avatar', array($this,'show_user_avatar'), 10, 5);
		parent::__construct($defaults);
                add_action("bp_before_profile_content",array($this,"buddypress_before_profile_content"));
                add_action("bp_after_profile_content",array($this, "buddypress_after_profile_content"));
                //removed default filter for placement of the button and added new filter
                remove_filter('rtmedia_action_buttons_before_delete', array($this,'button_filter'));
                add_filter ( 'rtmedia_addons_action_buttons', array( $this, 'button_filter' ) );
                add_filter ( 'rtmedia_author_media_options', array( $this, 'button_filter' ), 13,1 );
	}

        /*
         * Shows the image that was set as profile picture as the user avatar when buddypress is not active.
         */
        function show_user_avatar ($avatar, $id_or_email, $size, $default, $alt) {

            if(!function_exists("bp_is_active") || !bp_is_active( 'xprofile' )) { //buddypress is not active or xprofile is not enabled in buddypress

                global $pagenow;
                if ( 'options-discussion.php' == $pagenow ) // Do not filter if inside WordPress options page
                        return $avatar;

                if ( is_object( $id_or_email ) ) { // If passed an object, assume $user->user_id
                        $id = $id_or_email->user_id;

                } else if ( is_numeric( $id_or_email ) ) {// If passed a number, assume it was a $user_id
                        $id = $id_or_email;

                } elseif ( is_string($id_or_email ) && ( $user_by_email = get_user_by( 'email', $id_or_email ) ) ) {// If passed a string and that string returns a user, get the $id
                        $id = $user_by_email->ID;
                }

                if ( empty( $id ) ) {// If somehow $id hasn't been assigned, return the result of get_avatar
                        return !empty( $avatar ) ? $avatar : $default;
                }

                $current_avatar = get_user_meta( $id , 'profile_picture' , true);
                if(isset($current_avatar) && $current_avatar != "") {
                    $avatar = wp_get_attachment_image_src ($current_avatar);
                    if($avatar) {
                        $avatar = "<img src='".$avatar[0]."' alt='".$alt."' width='".$size."' height='".$size."' style='width:" . $size . "px;height:" . $size . "px' >";
                    }
                }
            }

            return $avatar;
          }

         /*
         * Enqueue required scripts for image cropping
         */
        function enqueue_crop_scripts() {
            wp_enqueue_style( 'jcrop' );
            wp_enqueue_script( 'jcrop', array( 'jquery' ) );
            bp_core_add_cropper_inline_js();
            bp_core_add_cropper_inline_css();
        }

        /*
         * restricts the default buddypress template to load
         */
        function buddypress_before_profile_content(){
            if(isset($_REQUEST["rtmpsetpp"]) && isset($_POST['rtmp_media_id'])){
                ob_start();
            }
        }
        /*
         * Show the image editor for cropping the image to set as profile picture
         */
        function buddypress_after_profile_content(){
            if(isset($_REQUEST["rtmpsetpp"]) && isset($_POST['rtmp_media_id'])){
                $data = ob_get_clean();
                global $bp;

                if(isset($_POST['rtmp_media_id']) && $_POST['rtmp_media_id'] != '') {
                    $media_id = $_POST['rtmp_media_id'];
                    $bp->avatar_admin->original['file'] = $this->copy_profile_picture ($media_id);
                }
                require_once( ABSPATH . '/wp-admin/includes/image.php' );
                $editor = wp_get_image_editor( $bp->avatar_admin->original['file'] );
                $bp = buddypress();
    $args = array(
                    'object'     => 'user', // user OR group OR blog OR custom type (if you use filters)
            );
            extract( $args, EXTR_SKIP );
            $item_id = bp_displayed_user_id();


            if ( empty( $avatar_dir ) ) {
                    if ( 'user' == $object )
                            $avatar_dir = 'avatars';
                    else if ( 'group' == $object )
                            $avatar_dir = 'group-avatars';
                    else if ( 'blog' == $object )
                            $avatar_dir = 'blog-avatars';

                    $avatar_dir = apply_filters( 'bp_core_avatar_dir', $avatar_dir, $object );

                    if ( !$avatar_dir ) return false;
	}

                if ( ! is_wp_error( $editor ) ) {

                    $editor->set_quality( 100 );
                    $resized = $editor->resize( bp_core_avatar_original_max_width(), bp_core_avatar_original_max_width(), false );

                    if ( ! is_wp_error( $resized ) ) {
                        $thumb = $editor->save( $editor->generate_filename() );
                    } else {
                        $error = $resized;
                    }
                    if ( isset($error) && false === $error && is_wp_error( $thumb ) ) { // Check for thumbnail creation errors
                            $error = $thumb;
                    }
                    if ( isset($error) && false !== $error ) { // Thumbnail is good so proceed
                            $bp->avatar_admin->resized = $thumb;
                    }
                }

                if ( ! isset( $bp->avatar_admin->image ) )
                        $bp->avatar_admin->image = new stdClass();

                if ( empty ( $bp -> avatar_admin -> resized ) ) {
                $bp -> avatar_admin -> image -> dir = str_replace ( bp_core_avatar_upload_path () , '' , $bp -> avatar_admin -> original[ 'file' ] ) ;
            }
            else {
                $bp -> avatar_admin -> image -> dir = str_replace ( bp_core_avatar_upload_path () , '' , $bp -> avatar_admin -> resized[ 'path' ] ) ;
                @unlink ( $bp -> avatar_admin -> original[ 'file' ] ) ;
            }

            // Check for WP_Error on what should be an image
            if ( is_wp_error ( $bp -> avatar_admin -> image -> dir ) ) {
                bp_core_add_message ( sprintf ( __ ( 'Upload failed! Error was: %s' , 'buddypress' ) , $bp -> avatar_admin -> image -> dir -> get_error_message () ) , 'error' ) ;
                return false ;
            }

	// Set the url value for the image
	$bp->avatar_admin->image->url = bp_core_avatar_url() . $bp->avatar_admin->image->dir;

                $this->enqueue_crop_scripts(); //enqueue the required scripts
                if ( ! isset( $bp->avatar_admin ) ) {
                        $bp->avatar_admin = new stdClass();
                }
                $bp->avatar_admin->step = 'crop-image';
                include ( RTMEDIA_PRO_PATH . '/templates/profile-picture-edit.php' ) ;
             }
        }

        /*
         * Copies the current media to the user's folder under /uploads/avatars/
         * @param accepts media_id
         */
        function copy_profile_picture ($media_id) {

        $args = array(
		'object'     => 'user', // user OR group OR blog OR custom type (if you use filters)
	);
	extract( $args, EXTR_SKIP );
        $item_id = bp_displayed_user_id();


	if ( empty( $avatar_dir ) ) {
		if ( 'user' == $object )
			$avatar_dir = 'avatars';
		else if ( 'group' == $object )
			$avatar_dir = 'group-avatars';
		else if ( 'blog' == $object )
			$avatar_dir = 'blog-avatars';

		$avatar_dir = apply_filters( 'bp_core_avatar_dir', $avatar_dir, $object );

		if ( !$avatar_dir ) return false;
	}

	$avatar_folder_dir = apply_filters( 'bp_core_avatar_folder_dir', bp_core_avatar_upload_path() . '/' . $avatar_dir . '/' . $item_id, $item_id, $object, $avatar_dir );
        wp_mkdir_p($avatar_folder_dir);
            if(isset($media_id) && $media_id != '') {
                 $image_url = wp_get_attachment_image_src($media_id);
                 $image_url = get_attached_file($media_id);

                 if($image_url) {
                     $filename = basename($image_url) ;
                     $new_image = trailingslashit($avatar_folder_dir) . $filename ;
                     $copy  = copy($image_url, $new_image);
                     if($copy) {
                         return $new_image;
                     }else {
                         return false;
                     }
                 }
            }
        }

	function process(){

            global $rtmedia_query;

            $rtmediainteraction = new RTMediaInteractionModel();
            $user_id = $this->interactor;
            $media_id = $this->media->media_id;

            $return = array();

            if ( $this->label == "Set as Profile Pic") {
                    $return["next"] = $this->undo_label;
            } else {
                    $return["next"] = $this->label;
            }


            if( isset($user_id) && isset($media_id) && $media_id != '' && $user_id != '') {
                update_user_meta( $user_id, 'profile_picture', $media_id);
            }
	    global $rtmedia_points_media_id;
	    $rtmedia_points_media_id = $this->media->id;
	    do_action("rtmedia_pro_after_set_profile_pic", $this);
            if(isset($_REQUEST["json"]) && $_REQUEST["json"]=="true"){
                    echo json_encode($return);
                    die();
                }
                else{
                    wp_safe_redirect ($_SERVER["HTTP_REFERER"]);
                    exit();
                }

	}


        function render() {

             $before_render = $this->before_render();
                if($before_render === false )
                    return false;
		$button = '';
		if($this->is_visible()){
                    $link = trailingslashit(get_rtmedia_permalink($this->media->id)).
                                    $this->action.'/';
                    $disabled = '';
                    if(!$this->is_clickable()){
                            $disabled = ' disabled';
                    }
                }
                $button = $this->show_profile_picture_form($link, $disabled);
		return $button;
        }

        /*
         * Show the profile picture form with "set as profile picture" button
         */
        function show_profile_picture_form($link, $disabled = ''){

            $button_start = $button = $button_end = $icon = "";
            if( isset( $this->icon_class ) && $this->icon_class != "" ) {
                            $icon = "<i class='" . $this->icon_class . "'></i>";
                        }
            if(function_exists('bp_is_active') && bp_is_active( 'xprofile' )) { // if buddypress-xprofile is enabled
                $username = wp_get_current_user();
                $username = $username->user_login;
                $link  = bp_loggedin_user_domain() . "/profile/change-avatar/?rtmpsetpp=1";

                $button_start = '<form action="' . $link . '" method="post" id="avatar-upload-form" class="standard-form" enctype="multipart/form-data">';
                $button_start .= wp_nonce_field( 'bp_avatar_upload' );

                $button_end = '<input type="hidden" name="action" id="action" value="bp_avatar_upload" />';
                $button_end .= '<input type="hidden" name="rtmp_media_id" value="'.$this->media->media_id.'" />';
                $button_end .= "</form>";

                $button .= '<button type="submit" name="upload" id="upload" class="rtmedia-action-buttons rtmedia-'. $this->action .'">' . $icon . $this->label . "</button>";
            } else {
                $button_start = '<form action="'. $link .'">';
                $button = '<button type="submit" id="rtmedia-action-button-'
					.$this->media->id.'" class="rtmedia-action-buttons button'.$disabled.' rtmedia-'. $this->action .' rtmedia-set-profile-picture">'
					.$this->label.'</button>';
                $button_end = '</form>';
            }
            $button = apply_filters( 'rtmedia_' . $this->action . '_button_filter', $button);

            return $button_start .$button . $button_end;
        }

        function before_render() {
                $globa_id = RTMediaAlbum::get_default();

                if(isset($this->media->media_type) && $this->media->media_type != "photo") {
                    return false;
                }

                if(isset($this->media->media_id) && isset($this->media->media_author) && $this->media->media_author != '' && $this->media->media_id != '') {
                    if($this->media->media_author != $this->interactor) {
                                return false;
                    }
                }
        }

}
