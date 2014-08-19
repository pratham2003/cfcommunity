<?php

/**
 * Created by PhpStorm.
 * User: ritz
 * Date: 25/6/14
 * Time: 8:10 PM
 */
class RTMediaProFavListInteraction extends RTMediaUserInteraction {

	/**
	 *
	 */
	function __construct() {
		$defaults = array(
			'action'     => 'add-to-favlist', 'label' => __( 'Add to FavList', 'rtmedia' ), 'privacy' => 20, //60,
			'repeatable' => true, 'icon_class' => 'rtmicon-heart'
		);
		parent::__construct( $defaults );
		//removed default filter for placement of the button and added new filter
		remove_filter( 'rtmedia_action_buttons_before_delete', array( $this, 'button_filter' ) );
		add_filter( 'rtmedia_addons_action_buttons', array( $this, 'button_filter' ) );
		add_filter( 'rtmedia_author_media_options', array( $this, 'button_filter' ), 12, 1 );
		add_action( 'rtmedia_actions_before_description', array( $this, "rtmedia_favlist_form_holder" ), 30, 1 );
	}

	function rtmedia_favlist_form_holder() {
		if ( isset( $this->media->media_type ) && is_rtmedia_favlist_enable() ){
			echo "<div id='rtmp-favlist-form' style='clear:both'></div>";
		}
	}

	function button_filter( $buttons ) {
		if ( empty( $this->media ) ){
			$this->init();
		}
		$buttons[ ] = $this->render();

		return $buttons;
	}

	function process() {
		if ( isset( $_POST[ 'action' ] ) && $_POST[ 'action' ] != "" ){
			$action = $_POST[ 'action' ];
		} else {
			return false;
		}

		if ( isset( $_POST[ 'favlist_id' ] ) && $_POST[ 'favlist_id' ] != "" ){
			$favlist_id = $_POST[ 'favlist_id' ];
		}

		if ( isset( $action ) && $action == "get_form" ){
			$current_favlists = $this->get_user_favlist_form();
			$output           = $current_favlists;

			return $output;
		}
		if ( $action == "add" && $favlist_id != "" ){
			$rtmfavlist = new RTMediaProFavList();
			$status     = true;
			//check if new favlist is to be created and then media is to be added
			if ( $favlist_id == "-1" && $_POST[ 'favlist_name' ] != "" ){
				$current_user_id = get_current_user_id();
				$favlist_id      = $rtmfavlist->add( $_POST[ 'favlist_name' ], $current_user_id, true, false, 'profile', $current_user_id, $_POST[ 'privacy' ] );
				if ( ! $rtmfavlist ){
					$status = false;
				}
			}

			$return           = array();
			$return[ "next" ] = "error";

			if ( $status === true ){
				//add the media to the favlist
				$add = $this->add_to_favlist( $favlist_id, $this->media->id );
				if ( $add ){
					$return[ "next" ] = "success";
				}
			}

			if ( isset( $_REQUEST[ "json" ] ) && $_REQUEST[ "json" ] == "true" ){
				echo json_encode( $return );
				die();
			} else {
				wp_safe_redirect( $_SERVER[ "HTTP_REFERER" ] );
				exit();
			}
		}
		die();
	}

	function add_to_favlist( $favlist_id, $media_id ) {
		$set_favlist_meta = $this->add_media_to_favlist_meta( $favlist_id, $media_id );
		$media_meta       = $this->add_favlist_to_media_meta( $media_id, $favlist_id );
		if ( $set_favlist_meta && $media_meta ){
			return true;
		}

		return false;
	}

	function add_media_to_favlist_meta( $favlist_id, $media_id ) {
		if ( isset( $favlist_id ) && $favlist_id != "" && isset( $media_id ) && $media_id != "" ){
			$media_list = maybe_unserialize( get_rtmedia_meta( $favlist_id, 'media_ids' ) );
			if ( ! empty( $media_list ) ){
				if ( ! in_array( $media_id, $media_list ) ){
					$media_list = array_merge( $media_list, array( $media_id ) );
				} else {
					return true;
				}
			} else {
				$media_list = array( $media_id );
			}
			$update = update_rtmedia_meta( $favlist_id, "media_ids", $media_list );
			if ( $update ){
				return true;
			}

			return false;
		}
	}

	function add_favlist_to_media_meta( $media_id, $favlist_id ) {
		if ( isset( $favlist_id ) && $favlist_id != "" && isset( $media_id ) && $media_id != "" ){
			$media_meta = maybe_unserialize( get_rtmedia_meta( $media_id, 'favlists' ) );
			if ( $media_meta ){
				if ( ! in_array( $favlist_id, $media_meta ) ){
					$media_meta = array_merge( $media_meta, array( $favlist_id ) );
				}
				$music_meta = update_rtmedia_meta( $media_id, 'favlists', $media_meta );
			} else {
				$music_meta = add_rtmedia_meta( $media_id, 'favlists', array( $favlist_id ) );
			}
			if ( $music_meta ){
				return true;
			}

			return false;
		}
	}

	function get_user_favlist_form() {
		$favlists                = new RTMediaProFavlist();
		$profile_favlists        = $favlists->get_profile_favlists( get_current_user_id() );
		$current_medias_favlists = get_rtmedia_meta( $this->media->id, "favlists" );
		if ( $profile_favlists != "" ){

			$privacyObj = new RTMediaPrivacy();
			$privacy_el = $privacyObj->select_privacy_ui( false, 'rtSelectPrivacy' );
			if ( $privacy_el ){
				$privacy_el = "<label>" . __( 'Privacy: ', 'rtmedia' ) . "</label>" . $privacy_el;
			} else {
				$privacy_el = "";
			}

			$link = trailingslashit( get_rtmedia_permalink( $this->media->id ) ) . $this->action . '/';
			$list = '<form action="' . $link . '" name="" method="post" class="rtm-add-to-favlist-form">' . '<span class="rtm-favlist-span" id="rtm-favlist-select"><label for="rtm_favlist_id">' . __( 'Select FavList: ', 'rtmedia' ) . '</label><select name="rtm_favlist_id" id="rtm-favlist-list" class="">' . '<option value="0">' . __( 'Select FavList', 'rtmedia' ) . '</option>' . "<option value='-1'>" . __( 'Create New FavList', 'rtmedia' ) . "</option>";

			$profile_list = '';
			foreach ( $profile_favlists as $favlist ) {
				if ( $current_medias_favlists == "" || ( $current_medias_favlists != "" && ! in_array( $favlist->id, $current_medias_favlists ) ) ){
					$profile_list .= "<option value='" . $favlist->id . "'>";
					$profile_list .= $favlist->media_title . "</option>";
				}
			}
			if ( $profile_list != "" ){
				$list .= "<optgroup value='profile' label='" . __( 'Your FavLists', 'rtmedia' ) . "'>" . $profile_list . "</optgroup>";
			}

			$list .= "</select></span>" . "<input type='hidden' name='action' value='add'/>" . "<div id='rtm-new-favlist-container' style='display:none'>" . "<span class='favlist-span'><label for='rtm_favlist_name'>" . __( 'Title: ', 'rtmedia' ) . "</label><input type='text' name='rtm_favlist_name' id='rtm_favlist_name'/></span>" . "<span class='favlist-span'>" . $privacy_el . "</span>" . "</div>" . "<input type='submit' class='add-to-rtmp-favlist' value='" . __( 'Add', 'rtmedia' ) . "'/>" . "<button id='rtm-favlist-cancel' style='display:none'>" . __( 'Cancel', 'rtmedia' ) . "</form>";

			return $list;
		}
	}

	function is_favorited() {
		return false;
	}

	function before_render() {
		global $rtmedia;
		$options = $rtmedia->options;
		if ( isset( $options[ 'general_enable_favlist' ] ) && $options[ 'general_enable_favlist' ] == "0" ){
			return false;
		} else {
			return true;
		}
	}
}