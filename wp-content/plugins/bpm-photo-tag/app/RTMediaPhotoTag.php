<?php

/**
 * Description of BPMediaPhotoTag
 *
 * @author saurabh
 */
class RTMediaPhotoTag {

	/**
	 *
	 */
	function __construct() {
		if (!class_exists('RTMedia')) return;

		$this->model = new RTMediaModel();
		remove_filter( 'rtmedia_action_buttons_before_delete', array( $this, 'tag_button' ));
		add_action ( 'rtmedia_actions_without_lightbox', array( $this, 'tag_button' ) );
        add_action( 'rtmedia_action_buttons_after_media', array( $this, 'tag_button' ));
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts_styles' ), 99 );
		add_action( 'wp_ajax_rtmedia_get_taggable', array( $this, 'get_taggables' ) );
		add_action( 'wp_ajax_rtmedia_save_tags', array( $this, 'save' ) );
		add_action( 'wp_ajax_rtmedia_delete_tag', array( $this, 'delete' ) );
		add_filter( 'rtmedia_single_content_filter', array( $this, 'display' ), 10, 2 );
		require_once(RTMEDIA_PHOTO_TAGGING_PATH.'app/RTMediaTagNotifications.php');
		$this->notification = new RTMediaTagNotifications();
	}

	function enqueue_scripts_styles() {
		if( ! wp_script_is( 'bp-jquery-autocomplete' ) ) {
			wp_enqueue_script( 'jquery-ui-autocomplete' );
		}
		wp_enqueue_script(
				'rtmedia-tag', RTMEDIA_PHOTO_TAGGING_URL . 'assets/bp-media-tag.js', '', '1.0'
		);
		wp_enqueue_style(
				'rtmedia-tag', RTMEDIA_PHOTO_TAGGING_URL . 'assets/bp-media-tag.css', '', '1.0'
		);
		$this->localize();
	}



	function localize() {
		$array = array(
			'tag_txt' => __( 'Tag Photo', 'rtm-photo-tagging' ),
			'tag_done_txt' => __( 'Finished Tagging', 'rtm-photo-tagging' ),
			'tag_nonce' => wp_create_nonce( 'rtm-photo-tagging-nonce' )
		);
		wp_localize_script( 'rtmedia-tag', 'rtm_photo_tag_str', $array );
	}

	function set_media($media_id, $is_attach_id=false){
		//echo $media_id;
		if($is_attach_id===false){
			//echo 'na';
			$media_r = $this->model->get(array('id'=>$media_id));
		}else{
			//echo 'a';
			$media_r = $this->model->get(array('media_id'=>$media_id));
			//var_dump($media_r);
		}
		//var_dump($media_r);
		if($media_r){
			return $media_r[0];
		}
	}

	function tag_button( $action_buttons ) {
		global $rtmedia_query;

		if ( isset($rtmedia_query->action_query->id) ) {

			$media = $this->set_media($rtmedia_query->action_query->id,false);

			if($media->context=='group' || $media->media_type != "photo") return $action_buttons;
			$usercantag = $this->can_tag($media->media_id);



			if ( $usercantag != false ) {
				$action_buttons = '<button class="button item-button bp-secondary-action bp-media-tag-button rtmedia-action-buttons"><i class="rtmicon-tag"></i><span>'
						 . __( 'Tag Photo', 'rtm-photo-tagging' ) . '</span></button>';
                                if($action_buttons)
                                    echo $action_buttons ;
                                //echo "<li>" . $action_buttons . "</li>";
                                
			}
		}

		return $action_buttons;
	}

	function can_tag($media_id) {
		$media=$this->set_media($media_id,true);

		//var_dump($media);

		$privacy = 0;

		if( is_user_logged_in()){
			$privacy = 20;
		}

		if(get_current_user_id()==$media->media_author){
			$privacy = 60;
		}

		if(class_exists('BuddyPress')){
			if(bp_is_active('friends')){
				$friends = new RTMediaFriends();
				if(in_array( $media->media_author ,$friends->get_friends_cache(get_current_user_id()))){
					$privacy = 40;
				}
			}
		}


		if($privacy>=40){
			return true;
		}


		return false;
	}

	function get_taggables() {


		$media_id = $_GET[ 'media_id' ];

		$media = $this->set_media($media_id);

		$tags = $this->get_existing( $media->media_id );
		$tagged_ids = array( );
		if ( is_array( $tags ) ) {
			$tagged_ids = array_keys( $tags );
		}

		$limit = (int) $_GET[ 'limit' ] ? $_GET[ 'limit' ] : apply_filters( 'bp_autocomplete_max_results', 10 );

		if ( bp_is_active( 'friends' ) ) {
			$users = friends_search_friends( $_GET[ 'q' ], bp_loggedin_user_id(), $limit, 1 );

			if ( ! empty( $users[ 'friends' ] ) ) {
				$users = $users[ 'friends' ];
			}
			$user_ids = apply_filters( 'bp_friends_autocomplete_ids', $users, $_GET[ 'q' ], $limit );
			$user_ids[ ] = bp_loggedin_user_id();
		}

		$user_ids = array_diff( $user_ids, $tagged_ids );
		if ( ! empty( $user_ids ) ) {
			foreach ( $user_ids as $user_id ) {
				// Note that the final line break acts as a delimiter for the
				// autocomplete javascript and thus should not be removed
				$item[ $user_id ][ 'tagger' ] = bp_loggedin_user_id();
				$item[ $user_id ][ 'tagged' ] = $user_id;
				$item[ $user_id ][ 'tagged_url' ] = bp_core_get_userlink( $user_id );
				$item[ $user_id ][ 'label' ] = bp_core_fetch_avatar(
								array(
									'item_id' => $user_id,
									'type' => 'thumb',
									'width' => 32,
									'height' => 32,
									'alt' => bp_core_get_user_displayname( $user_id ) )
						)
						. ' &nbsp;'
						. bp_core_get_user_displayname( $user_id ) . "\n";
			}
			echo json_encode( $item );
			die();
		}
	}

	function get_existing( $media_id ) {
		$tags = get_post_meta( $media_id, 'bp-media-user-tags', true );
		return $tags;
	}

	function display( $content, $media ) {
		if ( $media->media_type != 'photo' )
			return $content;
		$media_id = $media->media_id;
		$tags = $this->tags( $media_id );
		$new_content = '<div class="tagcontainer"><form name="rtmedia-tag-form" id="rtmedia-tag-form">';
		$new_content .= $content;
		$new_content .= $tags;
		$new_content .= '</form></div>';
		return $new_content;
	}

	function tags( $media_id = false ) {
		if ( $media_id == false )
			return;
		$tags = $this->get_existing( $media_id );

		$tag = '';
		if ( is_array( $tags ) ) {
			foreach ( $tags as $tagged => &$taginfo ) {
    			if(get_user_by('id', $tagged) === false ){
                	$this-> delete_tag($tagged,$media_id);
                    continue ;
                }
				$tag .='<div class="bp-media-tag" style="top: ' . $taginfo[ 'top' ] . '%; left: ' . $taginfo[ 'left' ] . '%;">
				<div class="tagbox">';
				if ( $this->can_delete( $media_id,$tagged, $taginfo[ 'tagger' ] ) ) {
					$tag .= '<a href="#" class="close"></a>';
				}
				$tag .= '<input type="hidden" class="bp-media-tagatr" name="bp-media-tagger[' . $tagged . ']" value="' . $taginfo[ 'tagger' ] . '">
								<input type="hidden" class="bp-media-tagatr" name="bp-media-tag-top[' . $tagged . ']" value="' . $taginfo[ 'top' ] . '">
								<input type="hidden" class="bp-media-tagatr" name="bp-media-tag-left[' . $tagged . ']" value="' . $taginfo[ 'left' ] . '">

							</div>
							<div class="tagged-user"><i class="bp-media-notch"></i>' . bp_core_get_userlink( $tagged ) . '</div>
								</div>';
			}
		}

		return $tag;
	}

	function save() {
		$taggers = $_GET[ 'bp-media-tagger' ];
		$tagtops = $_GET[ 'bp-media-tag-top' ];
		$taglefts = $_GET[ 'bp-media-tag-left' ];
		$media_id = $_GET[ 'media_id' ];
		$media = $this->set_media($media_id);

		$tag = array( );
		foreach ( $taggers as $taggedid => $tagger ) {
			$tag[ $taggedid ][ 'tagger' ] = $tagger;
			$tag[ $taggedid ][ 'left' ] = $taglefts[ $taggedid ];
			$tag[ $taggedid ][ 'top' ] = $tagtops[ $taggedid ];
			$this->notification->notify( $media_id, $taggedid,$tagger );
		}
		update_post_meta( $media->media_id, 'bp-media-user-tags', $tag );

		echo json_encode( $tag );
		die();
	}

	function can_delete( $media_id,$tagged_id, $tagger ) {
		global $bp;
		$can_delete = false;

		if ( $this->can_tag($media_id) ) {
			if ( $bp->loggedin_user->id == $tagged_id || $bp->loggedin_user->id == $tagger ) {
				$can_delete = true;
			}
		}
		return $can_delete;
	}

	function delete() {
		$taggers = $_GET[ 'bp-media-tagger' ];
		$media_id = $_GET[ 'media_id' ];
		$tag_nonce = $_GET[ 'tag_nonce' ];
		if ( ! wp_verify_nonce( $tag_nonce, 'rtm-photo-tagging-nonce' ) )
			die( '-1' );

		foreach ( $taggers as $taggedid => $tagger ) {
			$media = $this->set_media($media_id);
			if ( $this->can_delete($media->media_id, $taggedid, $tagger ) ) {
				$this->delete_tag( $taggedid, $media_id );
			}
		}
		die( '1' );
	}

	function delete_tag( $tag_id, $media_id ) {
		$media = $this->set_media($media_id,false);
		$tags = get_post_meta( $media->media_id, 'bp-media-user-tags', true );
		unset( $tags[ $tag_id ] );
		update_post_meta( $media->media_id, 'bp-media-user-tags', $tags );
	}

}
