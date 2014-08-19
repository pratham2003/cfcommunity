<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of RTMediaCommentForm
 *
 * @author ritz
 */
class RTMediaProCommentForm {
	public function __construct() {
		add_filter( 'rtmedia_wordpress_content_groups', array( $this, 'general_content_groups' ), 10, 1 );
		add_filter( 'rtmedia_wordpress_content_add_itmes', array( $this, 'rtmedia_general_content_add_options' ), 10, 2 );
		add_action( 'init', array( $this, 'init' ) );
	}

	function rtmedia_general_content_add_options( $render_options, $options ) {
		$render_options[ 'general_enableCommentForm' ]            = array(
			'title'    => __( 'Enable attachments in comments', 'rtmedia' ), 'callback' => array( 'RTMediaFormHandler', 'checkbox' ), 'args' => array(
				'key' => 'general_enableCommentForm', 'value' => $options[ 'general_enableCommentForm' ], 'desc' => __( 'Option to upload/attach media/files to WordPress comments using comment form on front-end', 'rtmedia' ), 'class' => array( 'rtm_enable_comment_form' )
			), 'group' => "30"
		);
		$render_options[ 'general_enable_anonymous_comment' ]     = array(
			'title'    => __( 'Enable for non-logged in users also', 'rtmedia' ), 'callback' => array( 'RTMediaFormHandler', 'checkbox' ), 'args' => array(
				'key' => 'general_enable_anonymous_comment', 'value' => $options[ 'general_enable_anonymous_comment' ], 'desc' => __( 'It is NOT recommended to use this option. This will allow non-logged in user to upload files using comment form. If you use this option, make sure you haven\'t enabled any sensitive <a href=\'#rtmedia-types\'>file type extension</a>.', 'rtmedia' ), 'class' => array( 'rtm_enable_anonymous_comment' )
			), 'group' => "30"
		);
		$radios                                                   = array();
		$radios[ 'thumb_image' ]                                  = "<strong>With Thumbnail + file name</strong>";
		$radios[ 'thumb_title' ]                                  = "<strong>Without Thumbnail (only file name)</strong>";
		$render_options[ 'general_comment_form_attachment_view' ] = array(
			'title'    => __( "Display attachments in comments as", "rtmedia" ), 'callback' => array( "RTMediaFormHandler", "radio" ), 'args' => array(
				'key' => 'general_comment_form_attachment_view', 'radios' => $radios, 'default' => $options[ 'general_comment_form_attachment_view' ], 'class' => array( 'rtm_comment_default_view' ), 'desc' => __( 'Control how you want to display uploaded media files in comment listing', 'rtmedia' )
			), 'group' => "30"
		);

		return $render_options;
	}

	function general_content_groups( $general_group ) {
		$general_group[ 30 ] = "Attachment Support For WordPress Comments";

		return $general_group;
	}

	function init() {
		global $rtmedia;
		$options = $rtmedia->options;
		if ( isset( $options[ 'general_enableCommentForm' ] ) && $options[ 'general_enableCommentForm' ] != "0" ){
			if ( get_current_user_id() || ( isset( $options[ 'general_enable_anonymous_comment' ] ) && $options[ 'general_enable_anonymous_comment' ] != "0" ) ){
				add_filter( "comment_form_field_comment", array( $this, 'add_upload_shortcode' ), 9999, 1 );
				//add_action("comment_form",array($this,'add_upload_shortcode'), 99);
				add_filter( "comment_post", array( $this, 'attach_comment_media' ), 99, 1 );
			}
			add_filter( "comment_text", array( $this, 'show_comment_attachments' ), 99, 2 );
			add_filter( 'rtmedia_before_delete_media_redirect', array( $this, 'rtmedia_before_delete_media_redirect_wp_comment' ), 10, 1 );
		}
	}

	function rtmedia_before_delete_media_redirect_wp_comment( $redirect_url ) {
		global $rtmedia_query;

		if ( $rtmedia_query->media[ 0 ]->context == 'comment' ){
			$comment = get_comment( $rtmedia_query->media[ 0 ]->context_id );

			$redirect_url = get_post_permalink( $comment->comment_post_ID );
		}

		return $redirect_url;
	}

	function show_comment_attachments( $comment_text, $comment = '' ) {
		global $rtmedia;
		$options            = $rtmedia->options;
		$media_model        = new RTMediaModel();
		$attachment_content = "";
		if ( isset( $comment->comment_ID ) && $comment->comment_ID != "" ){
			$attached_medias = $media_model->get( array( 'context' => 'comment', 'context_id' => $comment->comment_ID ) );
			if ( is_array( $attached_medias ) && sizeof( $attached_medias ) > 0 ){
				$author = $attached_medias[ 0 ]->media_author;
				$attachment_content .= "<div class='rtm-comment-content rt-clear'>";
				$attachment_content .= "<hr>";
				$attachment_content .= "<h6>Attachments:</h6>";
				if ( $author ){
					$attachment_content .= "<ul class='rtm-comment-container'>";
				} else {
					$attachment_content .= "<ul class='rtm-comment-container-non-loggedin-user'>";
				}

				foreach ( $attached_medias as $attached_media ) {
					$rtm_id      = $attached_media->id;
					$media_title = $attached_media->media_title;
					if ( isset( $options[ 'general_comment_form_attachment_view' ] ) && $options[ 'general_comment_form_attachment_view' ] == "thumb_title" ){
						$attachment_content .= " <li class='rtm-comment-title-view'> <div> ";
						if ( $author ){
							$attachment_content .= " <a href='" . get_rtmedia_permalink( $rtm_id ) . "' target='_blank' class='rtm-comment-attachment'> ";
						} else {
							$attachment_content .= " <a href='" . wp_get_attachment_url( $attached_media->media_id ) . "' target='_blank' class='rtm-comment-attachment'> ";
						}
						$attachment_content .= " <p>" . __( $media_title ) . " </p>";
						$attachment_content .= " </a> </div> </li>";
					} else {
						$attachment_content .= " <li class='rtm-comment-thumb-view'> <div> ";
						if ( $author ){
							$attachment_content .= " <a href='" . get_rtmedia_permalink( $rtm_id ) . "' target='_blank' class='rtm-comment-attachment' title='" . __( $media_title ) . "' > ";
						} else {
							$attachment_content .= " <a href='" . wp_get_attachment_url( $attached_media->media_id ) . "' target='_blank' class='rtm-comment-attachment' title='" . __( $media_title ) . "' > ";
						}
						$attachment_content .= " <img src='" . rtmedia_image( "rt_media_thumbnail", $rtm_id, false ) . "' alt='" . __( $media_title ) . "' > ";
						$attachment_content .= " <p>" . __( $media_title ) . " </p>";
						$attachment_content .= " </a> </div> </li>";
					}
				}
				$attachment_content .= " </ul> ";
				$attachment_content .= " </div> ";
			}
		}

		return $comment_text . $attachment_content;
	}

	function add_upload_shortcode( $args ) {
		$comment_form_attachment = "";
		$allow_upload            = apply_filters( 'rtmedia_allow_uploader_view', true, 'comment_form' );
		if ( $allow_upload ){
			$comment_form_attachment = ' <p class="bbp-attachments-form">' . RTMediaUploadShortcode::pre_render( array( "rtmedia_simple_file_upload" => true, "rtmedia_upload_without_form" => true, "rtmedia_upload_allow_multiple" => true, "allow_anonymous" => true, "privacy" => "0" ) ) . ' </p>';
		} else {
			$comment_form_attachment = "<div class='rtmedia-upload-not-allowed'>" . apply_filters( 'rtmedia_upload_not_allowed_message', __( 'You are not allowed to upload/attach media.', 'rtmedia' ), 'comment_form' ) . "</div>";
		}

		return $args . $comment_form_attachment;
	}

	function attach_comment_media( $comment_content ) {
		if ( isset( $_FILES[ 'rtmedia_file_multiple' ] ) && sizeof( $_FILES[ 'rtmedia_file_multiple' ] ) > 0 ){
			$_POST[ 'context_id' ] = $comment_content;
			$_POST[ 'context' ]    = "comment";
			$rtmedia_upload        = new RTMediaUploadEndpoint();
			add_filter( 'rtmedia_filter_upload_dir', array( $this, 'change_upload_dir' ), 10, 1 );
			if ( isset( $_FILES[ 'rtmedia_file_multiple' ] ) && isset( $_FILES[ 'rtmedia_file_multiple' ][ 'name' ] ) && isset( $_FILES[ 'rtmedia_file_multiple' ][ 'name' ][ 0 ] ) && $_FILES[ 'rtmedia_file_multiple' ][ 'name' ][ 0 ] != '' ){
				foreach ( $_FILES[ 'rtmedia_file_multiple' ][ 'error' ] as $key => $val ) {
					$file_name                = $_FILES[ 'rtmedia_file_multiple' ][ 'name' ][ $key ];
					$file                     = array(
						'name' => $file_name, 'type' => $_FILES[ 'rtmedia_file_multiple' ][ 'type' ][ $key ], 'size' => $_FILES[ 'rtmedia_file_multiple' ][ 'size' ][ $key ], 'tmp_name' => $_FILES[ 'rtmedia_file_multiple' ][ 'tmp_name' ][ $key ], 'error' => $_FILES[ 'rtmedia_file_multiple' ][ 'error' ][ $key ]
					);
					$_FILES[ 'rtmedia_file' ] = $file;
					$media                    = $rtmedia_upload->template_redirect( false );
				}
			}
		}

		return $comment_content;
	}

	function change_upload_dir( $upload_dir ) {
		$upload_dir[ 'path' ] = trailingslashit( $upload_dir[ 'basedir' ] ) . 'rtMedia/comments/' . $_POST[ 'comment_post_ID' ] . $upload_dir[ 'subdir' ];
		$upload_dir[ 'url' ]  = trailingslashit( $upload_dir[ 'basedir' ] ) . 'rtMedia/comments/' . $_POST[ 'comment_post_ID' ] . $upload_dir[ 'subdir' ];

		return $upload_dir;
	}
}
