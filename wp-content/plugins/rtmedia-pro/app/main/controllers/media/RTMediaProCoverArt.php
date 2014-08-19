<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of RTMediaCoverArt
 *
 * @author saurabh
 */
class RTMediaProCoverArt extends RTMediaUserInteraction{

	/**
	 *
	 */
	function __construct() {
		$defaults = array(
                    'action' => 'cover',
                    'label' => __('Set as Album Cover'),
                    'plural' => '',
                    'undo_label' => __('Unset as Album Cover'),
                    'privacy' => 60, //60,
                    'countable' => false,
                    'single' => false,
                    'repeatable' => false,
                    'undoable' => true,
                    'icon_class' => 'rtmicon-picture-o'
		);
		parent::__construct($defaults);
                add_filter ( 'show_custom_album_cover', array( $this, 'show_album_cover' ) , 10, 3);
                //removed default filter for placement of the button and added new filter
                remove_filter('rtmedia_action_buttons_before_delete', array($this,'button_filter'));
                add_filter ( 'rtmedia_addons_action_buttons', array( $this, 'button_filter' ) );
                add_filter ( 'rtmedia_author_media_options', array( $this, 'button_filter' ), 12, 1 );

	}

        function button_filter($buttons){

		if(empty($this->media)){
			$this->init();
		}
		$buttons[] = $this->render();
		return $buttons;
	}

	function process(){
            //$action = $this->action_query->action;
            $user_id = $this->interactor;
            $media_id = $this->media->media_id;
            $album_id = $this->media->album_id;
            $return = array();
            if ( $this->label == "Set as Album Cover") {
                    $return["next"] = $this->undo_label;
            } else {
                    $return["next"] = $this->label;
            }

            $global_albums = RTMediaAlbum::get_globals();

            if(in_array( $album_id, $global_albums)){ //global album

                $global_albums_cover = array();
                $global_albums_cover = maybe_unserialize(get_user_meta( get_current_user_id(), 'global_albums_cover_art' , true));

                foreach($global_albums as $key => $value) {
                    if($value == $album_id)
                        $global_albums_cover[$value] = $media_id;
                }
                update_user_meta( $user_id, 'global_albums_cover_art', $global_albums_cover);
            }
            else {
                $update_data = array( 'cover_art' => $media_id );
                $where_columns = array( 'id' =>  $album_id );
                $update = $this->model->update($update_data, $where_columns);
            }
	    global $rtmedia_points_media_id;
	    $rtmedia_points_media_id = $this->media->id;
	    do_action("rtmedia_pro_after_set_album_cover", $this);
            if(isset($_REQUEST["json"]) && $_REQUEST["json"]=="true"){
                    echo json_encode($return);
                    die();
                }
                else{
                    wp_safe_redirect ($_SERVER["HTTP_REFERER"]);
                    exit();
                }

	}

        function before_render() {
                $globa_id = RTMediaAlbum::get_default();
                $global_albums = RTMediaAlbum::get_globals();

                if(isset($this->media->media_type) && $this->media->media_type != "photo") {
                    return false;
                }

                if(isset($this->media->album_id ) && $this->media->album_id > 0){

                    $album = ($this->model->get(array('media_id'=>$globa_id)));
                    if($album && isset($album[0])){
                        if($album[0]->id == $this->media->album_id){
                            return false;
                        }
                    }
                    $album = ($this->model->get(array('id'=>$this->media->album_id)));
                    if($album && isset($album[0])){
                        if($album[0]->media_author != $this->interactor ){

                            if(!in_array( $this->media->album_id, $global_albums)){ //global album
                                return false;
                            }
                        }
                    }
                }

                if(isset($this->media->media_id) && isset($this->media->media_author) && $this->media->media_author != '' && $this->media->media_id != '') {
                    if($this->media->media_author != $this->interactor) {
                                return false;
                    }
                }

                $album = $this->model->get(array('id'=>$this->media->album_id));
		if(sizeof($album) > 0) {
		    $album = $album[0];
		    $album_cover = $album->cover_art;
		    if(isset($album_cover) && $album_cover != '' && $album_cover == $this->media->media_id) {
			return false;
		    }
		}

                if(in_array( $this->media->album_id, $global_albums)){ //global album
                   $global_cover_arts = maybe_unserialize(get_user_meta($this->interactor, 'global_albums_cover_art', true));
                   if( $global_cover_arts !="" && in_array( $this->media->media_id , $global_cover_arts )) {
                       return false;
                   }
                }
        }

        function show_album_cover($thumb_id, $media_type , $album_id) {
            //code for specific album cover art of global albums for specific users
            $global_albums =  RTMediaAlbum::get_globals();
	    global $rtmedia_query;
             if($media_type == 'album' && in_array( $album_id, $global_albums) && is_user_logged_in()) {
		if( isset( $rtmedia_query ) && isset( $rtmedia_query->query['context'] ) && $rtmedia_query->query['context'] != "group" ) {
		    $global_cover_arts = maybe_unserialize( get_user_meta( $rtmedia_query->query['context_id'], 'global_albums_cover_art' , true));
		    if(isset($global_cover_arts[$album_id]) && $global_cover_arts[$album_id] != ""){
			if(wp_get_attachment_image_src($global_cover_arts[$album_id])){
			     return $global_cover_arts[$album_id];
			}
		    } else {
			return false;
		    }
		}
             }
             return $thumb_id;
        }

}