<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
/**
 * Description of RTMediaProPlaylistInteraction
 *
 * @author PushpakPatel < pushpak.patel@rtcamp.com >
 */
class RTMediaProPlaylistInteraction extends RTMediaUserInteraction{

	function __construct() {

                if( is_rtmedia_playlist_enable()) {
                    $defaults = array(
                    'action' => 'add-to-playlist',
                    'label' => __('Add to Playlist', 'rtmedia'),
                    'privacy' => 20,
		    'repeatable' => true,
                    'icon_class' => 'rtmicon-plus'

                    );
                    parent::__construct($defaults);
                    //removed default filter for placement of the button and added new filter
                    remove_filter('rtmedia_action_buttons_before_delete', array($this,'button_filter'));
                    add_filter ( 'rtmedia_addons_action_buttons', array( $this, 'button_filter' ) );
                    add_filter ( 'rtmedia_author_media_options', array( $this, 'button_filter' ), 14);
                    add_action('rtmedia_actions_before_description', array( $this, "rtmedia_playlist_form_holder"), 30);
                }
	}
        function add_to_playlist_button(){

        }

        function rtmedia_playlist_form_holder() {
            if(isset($this->media->media_type) && $this->media->media_type == "music" && is_rtmedia_playlist_enable ()) {
                
                echo "<div id='rtmp-playlist-form' style='clear:both'></div>";
            }
        }

        function process() {

           if(isset($_POST['action']) && $_POST['action'] != "")
               $action = $_POST['action'];
           else
               return false;

           if( isset($_POST['playlist_id']) && $_POST['playlist_id'] != "")
               $playlist_id = $_POST['playlist_id'];

            if( isset( $action ) && $action =="get_form" ) {

                $current_playlists =  $this->get_user_playlist_form ( );
                $output = $current_playlists;

                return $output ;
            }

            //add media the playlist
            if( $action == "add" && $playlist_id != "") {
                $rtmplaylist = new RTMediaProPlaylist;
                $status = true;
                //check if new playlist is to be created and then media is to be added
                if( $playlist_id == "-1" && $_POST['playlist_name'] != "" ) {

                    $playlist_id = $rtmplaylist->add( $_POST['playlist_name'], get_current_user_id (), true, false, false, false, $_POST['privacy'] );
                    if(!$rtmplaylist) { $status = false ; }
                }

                $return = array();
                $return["next"] = "error";

                if( $status === true ){
                    //add the media to the playlist
                    $add = $this->add_to_playlist( $playlist_id , $this->media->id);
                    if( $add ) {
                        $return["next"] = "success";
                    }
                }

                if(isset($_REQUEST["json"]) && $_REQUEST["json"]=="true"){
                    echo json_encode($return);
                    die();
                }
                else{
                    wp_safe_redirect ($_SERVER["HTTP_REFERER"]);
                    exit();
                }
            }

        }



        /**
        * Add media to Playlist.
        *
        * @param type $playlist_id
        * @param type $media_id
        * @return boolean
        */
        function add_to_playlist ( $playlist_id, $media_id ) {

            $set_playlist_meta = $this->add_music_to_playlist_meta ( $playlist_id , $media_id );
            $music_meta = $this->add_playlist_to_media_meta ( $media_id , $playlist_id );

            if( $set_playlist_meta && $music_meta ) {
                return true;
            }
            return false;
        }

        /**
        * Adds playlist to media's(music) meta.
        *
        * @param type $media_id
        * @param type $playlist_id
        * @return boolean
        */
        function add_playlist_to_media_meta ( $media_id , $playlist_id ) {

            if( isset( $playlist_id ) && $playlist_id != "" && isset( $media_id ) && $media_id != "") {

                $media_meta = maybe_unserialize( get_rtmedia_meta( $media_id , 'playlists' ) );
                if( $media_meta ) {
                    if( !in_array( $playlist_id , $media_meta )) {
                        $media_meta = array_merge ( $media_meta , array( $playlist_id ) );
                    }
                    $music_meta = update_rtmedia_meta ( $media_id , 'playlists' , $media_meta );

                }else {
                    $music_meta = add_rtmedia_meta ( $media_id , 'playlists' , array( $playlist_id ) );
                }

                if( $music_meta ) {
                    return true;
                }
                return false;
            }
        }

        /**
        * Adds music to Playist's meta.
        *
        * @param type $playlist_id
        * @param type $media_id
        * @return boolean
        */
        function add_music_to_playlist_meta ( $playlist_id , $media_id ) {

            if( isset( $playlist_id ) && $playlist_id != "" && isset( $media_id ) && $media_id != "") {

                $media_list = maybe_unserialize(get_rtmedia_meta( $playlist_id , 'media_ids' ) );

                if( !empty($media_list) ) {

                    if( !in_array( $media_id , $media_list ) ) {
                       $media_list =  array_merge( $media_list, array($media_id) );
                    } else {
                        return true;
                    }

                } else {
                    $media_list = array($media_id);
                }

                $update = update_rtmedia_meta( $playlist_id , "media_ids" , $media_list );

                if($update) {
                    return true;
                }
                return false;
            }
        }

        /**
        * get those playlists of user in which the current media is not associated
        *
        * @param type $title
        * @return boolean
        */
        function get_user_playlist_form () {

            $playlists = new RTMediaProPlaylist();
            $profile_playlists = $playlists->get_profile_playlists( get_current_user_id() );
            $group_playlists = $playlists->get_group_playlists();
            $current_medias_playlists = get_rtmedia_meta( $this->media->id , "playlists" );
            if( $profile_playlists != "" || $group_playlists != "") {

                $provacyObj = new RTMediaPrivacy();
                $privacy_el = $provacyObj->select_privacy_ui( false , 'rtSelectPrivacy' );
                if( $privacy_el ) {
                    $privacy_el = "<label>" . __('Privacy: ', 'rtmedia') . "</label>" . $privacy_el ;
                } else {
                    $privacy_el ="";
                }

                $link = trailingslashit(get_rtmedia_permalink($this->media->id)).$this->action.'/';
                $list = '<form action="' . $link . '" name="" method="post" class="add-to-playlist-form">'
                        . '<span class="playlist-span" id="playlist-select"><label for="playlist_id">' . __('Select Playlist: ', 'rtmedia') . '</label><select name="playlist_id" id="playlist-list" class="">'
                        . '<option value="0">' . __('Select Playlist', 'rtmedia') . '</option>'
                        . "<option value='-1'>" . __('Create New Playlist', 'rtmedia') . "</option>";
                if( $profile_playlists != "" ) {
                    $profile_list = '';
                    foreach($profile_playlists as $playlist) {
                        if( $current_medias_playlists == "" || ( $current_medias_playlists != "" && !in_array($playlist->id , $current_medias_playlists )) ) {
                            $profile_list .= "<option value='" . $playlist->id . "'>";
                            $profile_list .= $playlist->media_title . "</option>";
                        }
                    }
                    if( $profile_list != "" ) {
                        $list .= "<optgroup value='profile' label='" . __('Your Playlists' , 'rtmedia') . "'>" . $profile_list . "</optgroup>";
                    }
                }
                $group_list = "";
                if( $group_playlists != "" && function_exists('bp_is_active')) { //if group playlist are there and if buddypress is active, show the group playlists
                    foreach($group_playlists as $playlist) {
                        if( $current_medias_playlists == "" || ( $current_medias_playlists != "" && !in_array($playlist->id , $current_medias_playlists )) ) {
                            $group_list .= "<option value='" . $playlist->id . "'>";
                            $group_list .= $playlist->media_title . "</option>";
                        }
                    }
                    if( $group_list != "" ) {
                            $group_list = '<optgroup value="group" label="' . __('Group Playlists', 'rtmedia') . '">' . $group_list . '</optgroup>';
                            $list .= $group_list;
                    }
                }


                $list .= "</select></span>"
                        . "<input type='hidden' name='action' value='add'/>"
                        . "<div id='new-playlist-container' style='display:none'>"
                        . "<span class='playlist-span'><label for='playlist_name'>" . __('Title: ', 'rtmedia') . "</label><input type='text' name='playlist_name' id='playlist_name'/></span>"
                        . "<span class='playlist-span'>" . $privacy_el . "</span>"
                        . "</div>"
                        . "<input type='submit' class='add-to-rtmp-playlist' value='" . __('Add', 'rtmedia') . "'/>"
                        . "<button id='playlist-cancel' style='display:none'>" . __('Cancel', 'rtmedia')
                        . "</form>";

                return $list;
            }
        }

        function render() {

            $before_render = $this->before_render();
            if($before_render === false )
                return false;

            if($this->is_visible()){
                $link = trailingslashit(get_rtmedia_permalink($this->media->id)) . $this->action.'/';

		$disabled = '';
		if(!$this->is_clickable()){
			$disabled = ' disabled';
		}

                $button_start = $button_end = $button = $icon = "";
                if( isset( $this->icon_class ) && $this->icon_class != "" ) {
                            $icon = "<i class='" . $this->icon_class . "'></i>";
                        }
                $button_start .='<form action="'. $link .'">';
                $button .= '<button type="button" id="rtmedia-action-button-'.$this->media->id.'" class="rtmedia-'.$this->action
					.' rtmedia-action-buttons button'.$disabled.'">' . $icon .$this->label.'</button>';
                $button = apply_filters( 'rtmedia_' . $this->action . '_button_filter', $button);
                $button_end = '</form>';
            }

            return $button_start . $button . $button_end;

        }

        function before_render() {

            if(isset($this->media->media_type) && $this->media->media_type != "music") {
                return false;
            }
            return true;
        }

}