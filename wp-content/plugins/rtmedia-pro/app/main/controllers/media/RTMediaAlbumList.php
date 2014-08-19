<?php

/*
 * Adds the submenu page in Media to display the list of Albums
 * @author Umesh Kumar (.1) <umeshsingla05@gmail.com>
 *
 */

class RTMediaAlbumList {

	function __construct() {
		//	add_filter("rtmedia_add_settings_sub_tabs", array($this,"rtmedia_pro_add_settings_tab"), 1);
		add_filter( "rtmedia_wordpress_content_groups", array( $this, "rtmedia_pro_general_content_groups" ), 10, 1 );
		add_filter( "rtmedia_wordpress_content_add_itmes", array( $this, 'wp_albums_content' ), 10, 2 );
		add_action( 'admin_init', array( $this, 'load_uploader' ) );
		add_filter( 'rtmedia_album_rewrite_slug', array( $this, 'rtmedia_album_rewrite_slug' ), 10, 1 );
		add_filter( 'rtmedia_return_is_template', array( $this, 'rtmedia_return_is_template' ), 10, 2 );
		add_action( 'add_meta_boxes_rtmedia_album', array( $this, 'rtmedia_add_uploader_metaboxes' ) );
		add_action( 'save_post', array( $this, 'save_rtmedia_album' ) );
		add_filter( 'rtmedia_albums_args', array( $this, 'rtmedia_albums_args' ), 10, 1 );
		add_filter( 'rtmedia_query_filter', array( $this, 'rtmedia_query_filter' ), 10, 1 );
		add_filter( 'rtmedia_gallery_title', array( $this, 'rtmedia_gallery_title' ), 10, 1 );
		add_filter( 'wp_title', array( $this, 'rtmedia_set_title' ), 99999, 2 );
		add_filter( "views_edit-rtmedia_album", array( $this, 'rtmedia_album_unset_custom_post_status' ) );
		add_action( 'init', array( $this, 'pre_get_posts' ) );
		// For displaying media galler when editing album
		add_action( 'wp_ajax_rtm_edit_wp_album_gallery', array( $this, 'rtm_edit_wp_album_gallery' ) );
		// Saving the changes after media update when editing album
		add_action( 'wp_ajax_rtm_wp_gallery_media_update', array( $this, 'rtm_edit_wp_album_media_save' ) );
		// Deleting the selected media when editing album
		add_action( 'wp_ajax_rtm_wp_gallery_media_delete', array( $this, 'rtm_edit_wp_album_media_delete' ) );
		// join query filter for archieve page content
		add_filter( 'posts_join', array( $this, 'rtm_album_archieve_posts_join' ), 10, 2 );
	}

	function rtm_album_archieve_posts_join( $join, $that ) {
        global $wpdb;
        
        if( isset( $that ) && isset( $that->query['post_type'] ) && $that->query['post_type'] == 'rtmedia_album' ) {
            $rtmedia_model_obj = new RTMediaModel();
            $join_table = $rtmedia_model_obj->table_name;
            $table_name = $wpdb->posts;
            $join .= " INNER JOIN {$join_table} ON ( {$join_table}.media_id = {$table_name}.id AND ( {$join_table}.context = 'dashboard' ) ) ";
        }
        
		return $join;
	}
        
	// Function for deleting media when editing album
	function rtm_edit_wp_album_media_delete() {
		$media = new RTMediaMedia();
		$ids = $_POST[ 'selected' ];
		foreach ( $ids as $id ) {
			$media->delete( $id );
		}
		wp_die();
	}

	// Saving the changes after media update when editing album
	function rtm_edit_wp_album_media_save() {
		$data_array = array( 'media_title', 'description' );
		$data = rtmedia_sanitize_object( $_POST, $data_array );
		$media = new RTMediaMedia();
		$state = $media->update( $_POST[ 'id' ], $data, $_POST[ 'media_id' ] );
		wp_die();
	}

	// For displaying media galler when editing album
	function rtm_edit_wp_album_gallery() {
		$media_model = new RTMediaModel();
		// Getting the album media using context id
		$rtmedia_context_content = $media_model->get( array( 'context_id' => $_REQUEST[ 'context_id' ], 'context' => 'dashboard' ) );

		$content = '<div id="rtmedia-edit-wp-gallery-content">';
		$content .=  '<ul>';

		for( $c = 0; $c < sizeof( $rtmedia_context_content ); $c++) {
			$post_data = get_post( $rtmedia_context_content[ $c ]->media_id );

			$content .= '<li id="' . $rtmedia_context_content[ $c ]->id . '">';
			$content .= '<input type="hidden" class="rtm_media_title" value="' . $rtmedia_context_content[ $c ]->media_title . '" />';
			$content .= '<input type="hidden" class="rtm_media_description" value="' . $post_data->post_content . '" />';
			$content .= '<input type="hidden" class="rtm_id" value="' . $rtmedia_context_content[ $c ]->id . '" />';
			$content .= '<input type="hidden" class="rtm_media_id" value="' . $rtmedia_context_content[ $c ]->media_id . '" />';
			$content .= '<div class="rtmedia-attachment-preview">';
			$content .= '<img src="' . rtmedia_image( 'rt_media_thumbnail', $rtmedia_context_content[ $c ]->id, false ) . '" alt="' . $rtmedia_context_content[ $c ]->media_title . '" title="' . $rtmedia_context_content[ $c ]->media_title . '" />';
			$content .= '<br />';
			$content .= '<label title="' . $rtmedia_context_content[ $c ]->media_title . '">' . substr( $rtmedia_context_content[ $c ]->media_title, 0, 20 ) . '</label>';
			$content .= '</div>';
			$content .= '</li>';
		}

		$content .= "</ul>";
		$content .= '<div class="media-sidebar">';
		$content .= '<div class="rtmedia-attachment-detail">';
		$content .= '<h3 class="rtmedia-attachment-display-none">Attachment Details</h3>';
		$content .= '<div class="rtmedia-attachment-delete-success">Media Deleted Successfully.</div>';
        $content .= '<div class="rtmedia-attachment-save-success">Media Updated Successfully.</div>';
		$content .= '<label class="setting rtmedia-attachment-display-none">';
		$content .= '<span>Title</span>';
		$content .= '<input type="text" id="rtmedia_attachment_title" />';
		$content .= '</label>';
		$content .= '<label class="setting rtmedia-attachment-display-none">';
		$content .= '<span>Description</span>';
		$content .= '<textarea id="rtmedia_attachment_description"></textarea>';
		$content .= '</label>';
		$content .= '<div class="clear"></div>';
		$content .= '<div id="rtmedia_attachment_button_div">';
		$content .= '<a class="rtmedia-attachment-display-none rtmedia-delete-attachment button-primary button-danger button-large">Delete Permanently</a>';
		$content .= '<a href="#" class="rtmedia-attachment-display-none button media-button button-primary button-large save-button">Save</a>';
		$content .= '</div>';
		$content .= '</div>';
		$content .= '</div>';
		$content .= '</div>';

		echo $content;

		wp_die();
	}

	function pre_get_posts() {
		add_action( 'pre_get_posts', array( $this, 'media_album_pre_get_posts' ) );
	}

	function media_album_pre_get_posts( $query ) {
		if ( is_admin() && isset( $query->query ) && $query->query[ 'post_type' ] == "rtmedia_album" ){
			if ( ! isset( $_GET[ 'post_status' ] ) ){
				$query->set( 'post_status', 'publish' );
			}
		}

		return $query;
	}

	function rtmedia_album_unset_custom_post_status( $views ) {
		unset( $views[ 'hidden' ] );
		unset( $views[ 'all' ] );

		return $views;
	}

	function rtmedia_set_title( $title, $sep ) {
		global $wp_query;
		if ( isset( $wp_query->query[ 'post_type' ] ) && $wp_query->query[ 'post_type' ] == "rtmedia_album" && isset( $wp_query->post ) && isset( $wp_query->post->ID ) ){
			if ( isset( $wp_query->queried_object ) && isset( $wp_query->queried_object->post_title ) ){
				$title = $wp_query->queried_object->post_title . ( $title != "" ? " " . $sep . " " . $title : "" );
			}
		}

		return $title;
	}

	function rtmedia_gallery_title( $title ) {
		global $wp_query;
		if ( isset( $wp_query->query[ 'post_type' ] ) && $wp_query->query[ 'post_type' ] == "rtmedia_album" && isset( $wp_query->post ) && isset( $wp_query->post->ID ) ){
			if ( isset( $wp_query->queried_object ) && isset( $wp_query->queried_object->post_title ) ){
				$title = $wp_query->queried_object->post_title;
			}
		}

		return $title;
	}

	function rtmedia_query_filter( $args ) {
		global $wp_query;
		if ( isset( $wp_query->query[ 'post_type' ] ) && $wp_query->query[ 'post_type' ] == "rtmedia_album" && isset( $wp_query->post ) && isset( $wp_query->post->ID ) ){
			$args[ 'context' ]    = "dashboard";
			$args[ 'context_id' ] = $wp_query->post->ID;
		}

		return $args;
	}

	function rtmedia_albums_args( $album_args ) {
		global $rtmedia;
		$options = $rtmedia->options;
		if ( isset( $options[ 'rtmedia_enable_wp_album' ] ) && $options[ 'rtmedia_enable_wp_album' ] != "0" ){
			$album_args[ 'show_ui' ]            = true;
			$album_args[ 'public' ]             = true;
			$album_args[ 'publicly_queryable' ] = true;
			$album_args[ 'show_in_menu' ]       = true;
			$album_args[ 'has_archive' ]       	= true;
		}

		return $album_args;
	}

	function save_rtmedia_album() {
		if ( isset( $_POST[ 'post_type' ] ) && $_POST[ 'post_type' ] == "rtmedia_album" ){
			$rtmedia_model = new RTMediaModel();
			$album_object  = $rtmedia_model->get( array( 'media_id' => $_POST[ 'post_ID' ] ) );
			$rtmedia_album = new RTMediaAlbum();
			if ( ! $album_object || sizeof( $album_object ) == "0" ){
				$rtmedia_album->add( $_POST[ 'post_title' ], false, false, $_POST[ 'post_ID' ], 'dashboard' );
			} else {
				$album_id = $album_object[ 0 ]->id;
				$status   = $rtmedia_model->update( array( 'media_title' => $_POST[ 'post_title' ] ), array( 'id' => $album_id ) );
			}
		}
	}

	function rtmedia_add_uploader_metaboxes() {
		if ( rtmedia_is_edit_page( 'edit' ) ){
			add_meta_box( 'add_meta_box', 'Uploader', array( $this, 'add_meta_box' ) );
		}
	}

	function add_meta_box() {
		?>
                <div id="wp-content-media-buttons" class="wp-media-buttons" style="float: right;">
                    <a href="#" id="rtmedia-edit-wp-gallery-button" class="button add_media" data-editor="content" title="Edit Media"><span class="wp-media-buttons-icon"></span> Edit Media</a>
                </div>
                <div class="clear"></div>
		<div id="rtmedia-uploader-form">                    
			<?php echo RTMediaUploadShortcode::pre_render( array( 'is_up_shortcode' => true, "album_id" => $_REQUEST[ 'post' ], "context" => 'dashboard', "context_id" => $_REQUEST[ 'post' ], "privacy" => "0" ) ); ?>
		</div>
	<?php
	}


	function rtmedia_return_is_template( $return, $slug ) {
		global $wp_query, $wpdb;
		if ( $slug == "upload" || is_archive() ){
			return $return;
		} elseif( isset( $wp_query->query[ 'post_type' ] ) && $wp_query->query[ 'post_type' ] == "rtmedia_album" ){
			return true;
		} else {
			return $return;
		}
	}

	function rtmedia_album_rewrite_slug( $slug ) {
		global $rtmedia;
		$options = $rtmedia->options;
		if ( isset( $options[ 'rtmedia_enable_wp_album' ] ) && $options[ 'rtmedia_enable_wp_album' ] != "0" && ( $options[ 'rtmedia_wp_album_slug' ] != "" || $options[ 'rtmedia_wp_album_slug' ] != "0" ) ){
			return $options[ 'rtmedia_wp_album_slug' ];
		}

		return $slug;
	}

	function rtmedia_pro_add_settings_tab( $sub_tabs ) {
		$sub_tabs[ ] = array(
			'href' => '#rtmedia-wp-albums', 'icon' => 'rtmicon-picture-o', 'title' => __( 'rtMedia WordPress Albums', 'rtmedia' ), 'name' => __( 'WordPress Albums', 'rtmedia' ), 'callback' => array( 'RTMediaAlbumList', 'wp_albums_content' )
		);

		return $sub_tabs;
	}

	function rtmedia_pro_general_content_groups( $general_group ) {
		$general_group[ 80 ] = __( 'Sitewide Gallery Section', 'rtmedia' );

		return $general_group;
	}

	function wp_albums_content( $render_options, $options ) {
		$render_options[ 'rtmedia_enable_wp_album' ] = array(
			'title'    => __( 'Enable sitewide gallery section', 'rtmedia' ), 'callback' => array( 'RTMediaFormHandler', 'checkbox' ), 'args' => array(
				'key' => 'rtmedia_enable_wp_album', 'value' => $options[ 'rtmedia_enable_wp_album' ], 'desc' => __( 'This will enable sitewide gallery feature. Gallery is collection of albums and can be manage from WordPress dashboard, like a custom post type.', 'rtmedia' )
			), 'group' => "80"
		);
		$create_gallery_page_url                     = get_bloginfo( 'url' ) . '/wp-admin/edit.php?post_type=rtmedia_album';
		$render_options[ 'rtmedia_wp_album_slug' ]   = array(
			'title'            => __( 'Slug for sitewide gallery section', 'rtmedia' ), 'callback' => array( 'RTMediaFormHandler', 'textbox' ), 'args' => array(
				'key' => 'rtmedia_wp_album_slug', 'value' => $options[ 'rtmedia_wp_album_slug' ], 'desc' => __( 'It is not recommended to change this setting again and again. This will be act as a base for all albums created from dashboard.', 'rtmedia' )
			), 'after_content' => __( 'You can', 'rtmedia') . ' <a href=\'' . $create_gallery_page_url . '\'>' . __( 'manage sitewide galleries from here', 'rtmedia') . '.</a>', 'group' => "80"
		);

		return $render_options;
	}

	function load_uploader() {
		$edit_page      = rtmedia_is_edit_page( 'edit' );
		$curr_post_type = false;
		if ( isset( $_REQUEST[ 'post' ] ) ){
			$curr_post_type = get_post_type( $_REQUEST[ 'post' ] );
		}
		if ( $curr_post_type && $curr_post_type == "rtmedia_album" && $edit_page ){
			$template_url = add_query_arg( array( "action" => 'rtmedia_get_template', "template" => apply_filters( 'rtmedia_backbone_template_filter', "media-gallery-item" ) ), admin_url( "admin-ajax.php" ) );
			$url = trailingslashit( get_site_url() ). "upload/";
			$params = array(
				'url'             => $url, 'runtimes' => 'html5,silverlight,flash,html4', 'browse_button' => 'rtMedia-upload-button', 'container' => 'rtmedia-upload-container', 'drop_element' => 'drag-drop-area', 'filters' => apply_filters( 'rtmedia_plupload_files_filter', array( array( 'title' => "Media Files", 'extensions' => get_rtmedia_allowed_upload_type() ) ) ), 'max_file_size' => min( array( ini_get( 'upload_max_filesize' ), ini_get( 'post_max_size' ) ) ), 'multipart' => true, 'urlstream_upload' => true, 'flash_swf_url' => includes_url( 'js/plupload/plupload.flash.swf' ), 'silverlight_xap_url' => includes_url( 'js/plupload/plupload.silverlight.xap' ), 'file_data_name' => 'rtmedia_file', // key passed to $_FILE.
				'multi_selection' => true, 'multipart_params' => apply_filters( 'rtmedia-multi-params', array( 'redirect' => 'no', 'action' => 'wp_handle_upload', '_wp_http_referer' => $_SERVER[ 'REQUEST_URI' ], 'mode' => 'file_upload', 'rtmedia_upload_nonce' => RTMediaUploadView::upload_nonce_generator( false, true ) ) ), 'max_file_size_msg' => apply_filters( "rtmedia_plupload_file_size_msg", min( array( ini_get( 'upload_max_filesize' ), ini_get( 'post_max_size' ) ) ) )
			);
			if ( wp_is_mobile() ){
				$params[ 'multi_selection' ] = false;
			}

			$params = apply_filters( "rtmedia_modify_upload_params", $params );
			wp_enqueue_script( 'plupload-all' );
			wp_enqueue_script( 'rtmedia-backbone', RTMEDIA_URL . 'app/assets/js/rtMedia.backbone.js', array( 'plupload', 'backbone' ), false, true );
			wp_enqueue_script( 'rtmedia-main', RTMEDIA_URL . 'app/assets/js/rtMedia.js', array( 'jquery', 'wp-mediaelement' ), RTMEDIA_VERSION );
			wp_enqueue_script( 'rtmedia-pro-main', RTMEDIA_PRO_URL . "app/assets/js/main.js", '', RTMEDIA_PRO_VERSION, true );
			wp_enqueue_style( 'rtmedia-main', RTMEDIA_URL . 'app/assets/css/main.css', '', RTMEDIA_VERSION );
			wp_enqueue_style( 'rtmedia-pro-main', RTMEDIA_PRO_URL . 'app/assets/css/main.css', '', RTMEDIA_PRO_VERSION );
			wp_localize_script( 'rtmedia-main', 'rtmedia_media_slug', RTMEDIA_MEDIA_SLUG );
			wp_localize_script( 'rtmedia-main', 'rtmedia_max_file_msg', __( 'Max file Limit', "rtmedia" ) );
			wp_localize_script( 'rtmedia-main', 'rtmedia_drop_media_msg', __( 'Drop files here', "rtmedia" ) );
			wp_localize_script( 'rtmedia-main', 'rtMedia_plupload_config', $params );
			wp_localize_script( 'rtmedia-main', 'template_url', $template_url );
			wp_localize_script( 'rtmedia-main', 'rtmedia_allowed_file_formats', __( 'Allowed File Formats', "rtmedia" ) );
			wp_localize_script( 'rtmedia-pro-main', 'rtmedia_pro_max_file_size', __( 'Max file size is.', "rtmedia" ) );
			wp_localize_script( 'rtmedia-main', 'rtmedia_add_more_files_msg', __( 'Add more files', "rtmedia" ) );
			wp_localize_script( 'rtmedia-main', 'rtmedia_waiting_msg', __( 'Waiting', "rtmedia" ) );
			wp_localize_script( 'rtmedia-main', 'rtmedia_uploaded_msg', __( 'Uploaded', "rtmedia" ) );
			wp_localize_script( 'rtmedia-main', 'rtmedia_uploading_msg', __( 'Uploading', "rtmedia" ) );
			wp_localize_script( 'rtmedia-main', 'rtmedia_upload_failed_msg', __( 'Failed', "rtmedia" ) );
			wp_localize_script( 'rtmedia-main', 'rtmedia_close', __( 'Close', "rtmedia" ) );
			wp_localize_script( 'rtmedia-main', 'rtmedia_edit', __( 'Edit', "rtmedia" ) );
			wp_localize_script( 'rtmedia-main', 'rtmedia_delete', __( 'Delete', "rtmedia" ) );
			wp_localize_script( 'rtmedia-main', 'rtmedia_edit_media', __( 'Edit Media', "rtmedia" ) );
			wp_localize_script( 'rtmedia-main', 'rtmedia_remove_from_queue', __( 'Remove from queue', "rtmedia" ) );
			wp_localize_script( 'rtmedia-main', 'rtmedia_add_more_files_msg', __( 'Add more files', "rtmedia" ) );
			wp_localize_script( 'rtmedia-main', 'rtmedia_file_extension_error_msg', __( 'File not supported', "rtmedia" ) );
			wp_localize_script( 'rtmedia-main', 'rtmedia_more', __( 'more', "rtmedia" ) );
			wp_localize_script( 'rtmedia-main', 'rtmedia_less', __( 'less', "rtmedia" ) );
			wp_localize_script( 'rtmedia-main', 'rtm_wp_version', get_bloginfo( 'version' ) );
			wp_localize_script( 'rtmedia-backbone', 'rMedia_loading_media', RTMEDIA_URL . "app/assets/img/boxspinner.gif" );
			wp_localize_script('rtmedia-main', 'rtmedia_delete_uploaded_media', __('This media is uploaded. Are you sure you want to delete this media?',"rtmedia"));
		}
	}
}
