<?php

/**
 * Don't load this file directly!
 */
if ( ! defined( 'ABSPATH' ) ){
	exit;
}

class RTMediaPro {

	public function __construct() {
		$this->load_translation();
		if ( ! ( defined( "DOING_AJAX" ) && DOING_AJAX && $_REQUEST[ "action" ] == "imgedit-preview" ) ){
			add_action( 'widgets_init', array( &$this, 'rtmedia_pro_widgets' ) );
		}
		include( RTMEDIA_PRO_PATH . 'app/main/controllers/template/rtm-pro-functions.php' );
		// add_action ( 'wp_enqueue_scripts', array( &$this,'rtmedia_pro_sidebar_widget_stylesheet' ));
		add_action( 'wp_enqueue_scripts', array( &$this, 'enqueue_scripts_styles' ), 999 );
		$this->check_updates();
		add_action( 'init', array( $this, 'rtmedia_pro_do_upgrade' ) );
		//add_action( 'plugins_loaded', array( $this, 'load_translation' ), 10 );
		new RTMediaProAdmin();
		add_action( 'wp_head', array( $this, 'custom_style_for_image_size' ) );
	}

	function custom_style_for_image_size() {
		global $rtmedia;
		?>
		<style type="text/css">
			.rtmedia-container .rtmedia-list .rtmedia-list-item .rtmedia-gallery-item-actions {
				width: 100%;
				z-index: 99;
			}
		</style>
		<?php
	}

	// load rtMedia-PRO translations
	function load_translation() {
		load_plugin_textdomain( 'rtmedia', false, basename( RTMEDIA_PRO_PATH ) . '/languages/' );
	}

	function rtm_db_upgrade_fix_media_privacy() {
		// fix forum media privacy
		global $wpdb;
		$rtmedia_pro_bbpress_privacy = rtmedia_get_site_option( "rtmedia_pro_fix_bbpress_privacy" );
		if ( ! $rtmedia_pro_bbpress_privacy ){
			if ( class_exists( 'bbPress' ) ){
				$rtmedia_model = new RTMediaModel();
				$sql_forum     = " SELECT distinct context_id from $rtmedia_model->table_name where $rtmedia_model->table_name.context = 'reply' AND $rtmedia_model->table_name.blog_id = '" . get_current_blog_id() . "' ";
				$upate_sql     = " UPDATE $rtmedia_model->table_name set $rtmedia_model->table_name.privacy = '0' where $rtmedia_model->table_name.context = 'topic' or $rtmedia_model->table_name.context = 'reply' AND $rtmedia_model->table_name.blog_id = '" . get_current_blog_id() . "' ";
				$wpdb->query( $upate_sql );
				$res = $wpdb->get_results( $sql_forum );
				if ( $res && sizeof( $res ) > 0 ){
					foreach ( $res as $media ) {
						$reply_id = $media->context_id;
						$topic_id = bbp_get_reply_topic_id( $reply_id );
						$forum_id = bbp_get_topic_forum_id( $topic_id );
						if ( bbp_is_forum_hidden( $forum_id ) || bbp_is_forum_private( $forum_id ) ){
							$upate_sql_topic = " UPDATE $rtmedia_model->table_name set $rtmedia_model->table_name.privacy = '20' where $rtmedia_model->table_name.context = 'forum' and $rtmedia_model->table_name.context_id = '$topic_id' AND $rtmedia_model->table_name.blog_id = '" . get_current_blog_id() . "' ";
							$wpdb->query( $upate_sql_topic );
							$upate_sql_reply = " UPDATE $rtmedia_model->table_name set $rtmedia_model->table_name.privacy = '20' where $rtmedia_model->table_name.context = 'topic' and $rtmedia_model->table_name.context_id = '$reply_id' AND $rtmedia_model->table_name.blog_id = '" . get_current_blog_id() . "' ";
							$wpdb->query( $upate_sql_reply );
						}
					}
				}
			}
			rtmedia_update_site_option( 'rtmedia_pro_fix_bbpress_privacy', 'true' );
		}
	}

	// fix media context for  bbPress attachments
	function rtm_db_upgrade_fix_bbpress_media_context() {
		$rtmedia_pro_bbpress_privacy = rtmedia_get_site_option( "rtmedia_pro_fix_bbpress_context" );
		if ( ! $rtmedia_pro_bbpress_privacy ){
			if ( class_exists( 'bbPress' ) ){
				$rtmedia_model = new RTMediaModel();
				$rtmedia_model->update( array( 'context' => 'reply' ), array( 'context' => 'topic' ) );
				$rtmedia_model->update( array( 'context' => 'topic' ), array( 'context' => 'forum' ) );
			}
			rtmedia_update_site_option( 'rtmedia_pro_fix_bbpress_context', 'true' );
		}
	}

	function rtm_db_upgrade_fix_table_collation() {
		global $wpdb;
		$attribute_model = new RTMediaAttributesModel();
		$table_name = $attribute_model->table_name;
		$update_media_sql = "ALTER TABLE ".$table_name." CONVERT TO CHARACTER SET utf8 COLLATE utf8_general_ci";
		$wpdb->query($update_media_sql);
	}

	function rtmedia_pro_do_upgrade() {
		if ( class_exists( 'RTDBUpdate' ) ){
			$update = new RTDBUpdate( false, RTMEDIA_PRO_PATH . "index.php", RTMEDIA_PRO_PATH . "app/schema/", true );
			if ( ! defined( 'RTMEDIA_PRO_VERSION' ) ){
				define( 'RTMEDIA_PRO_VERSION', $update->db_version );
			}

			if ( $update->check_upgrade() ){

				// fix album post status hidden
				global $wpdb;
				$rtmedia_model = new RTMediaModel();
				$update_sql    = "UPDATE $wpdb->posts wp join $rtmedia_model->table_name m on ( m.media_id = wp.ID ) SET wp.post_status = 'hidden' WHERE wp.post_type = 'rtmedia_album' and ( m.context <> 'dashboard' OR m.context is NULL ) ";
				$wpdb->query( $update_sql );

				add_action( 'rt_db_upgrade', array( $this, 'rtm_db_upgrade_fix_bbpress_media_context' ) );
				add_action( 'rt_db_upgrade', array( $this, 'rtm_db_upgrade_fix_media_privacy' ) );
				add_action( 'rt_db_upgrade', array( $this, 'rtm_db_upgrade_fix_table_collation' ) );
				$update->do_upgrade();
				remove_action( 'rt_db_upgrade', array( $this, 'rtm_db_upgrade_fix_bbpress_media_context' ) );
				remove_action( 'rt_db_upgrade', array( $this, 'rtm_db_upgrade_fix_media_privacy' ) );
				remove_action( 'rt_db_upgrade', array( $this, 'rtm_db_upgrade_fix_table_collation' ) );
			}
		}
	}

	function check_updates() {
		if ( ! class_exists( 'rtPluginUpdateCheckerNew' ) ){
			include RTMEDIA_PRO_PATH . 'lib/update-checker/rtPluginUpdateCheckerNew.php';
		}
		new rtPluginUpdateCheckerNew ( 'https://rtcamp.com/plugin-update-checker/', trailingslashit( RTMEDIA_PRO_PATH ) . 'index.php', 'rtmedia-pro', 12 );
	}


	function rtmedia_pro_widgets() {
		register_widget( 'RTMediaUploaderWidget' );
		register_widget( 'RTMediaGalleryWidget' );
	}


	function enqueue_scripts_styles() {
		// Dont enqueue main.css if default styles is checked false in rtmedia settings
		global $rtmedia;
		if ( ! ( isset( $rtmedia->options ) && isset( $rtmedia->options[ 'styles_enabled' ] ) && $rtmedia->options[ 'styles_enabled' ] == 0 ) ){
			wp_enqueue_style( 'rtmedia-pro-main', RTMEDIA_PRO_URL . "app/assets/css/main.css", '', RTMEDIA_PRO_VERSION );
			wp_register_style( 'rtmedia-pro-popular-photos-css', trailingslashit( RTMEDIA_PRO_URL ) . 'app/assets/css/rtmedia-pro-popular-photos-widget.css' );
			wp_enqueue_style( 'rtmedia-pro-popular-photos-css' );
		}
		if ( ( rtmp_is_music_tab() && is_music_playlist_view_enabled() ) || ( rtmp_is_document_tab() && is_document_table_view_enabled() ) || ( rtmp_is_other_tab() && is_document_table_view_enabled() ) ){ // if it is music tab && Playlist view for music is enabled or if it is document tab and table view is enabled
			wp_localize_script( 'rtmedia-main', 'rtmedia_gallery_reload_on_upload', '0' );
		}
		wp_enqueue_script( 'rtmedia-pro-rating', RTMEDIA_PRO_URL . "lib/rating-simple/rating_simple.js", '', RTMEDIA_PRO_VERSION, true );
		wp_enqueue_style( 'rtmedia-pro-rating-simple', RTMEDIA_PRO_URL . "lib/rating-simple/rating_simple.css", '', RTMEDIA_PRO_VERSION );
		wp_enqueue_script( 'rtmedia-pro-main', RTMEDIA_PRO_URL . "app/assets/js/main.js", array( 'jquery', 'rtmedia-pro-rating' ), RTMEDIA_PRO_VERSION, true );
		wp_localize_script( 'rtmedia-pro-rating', 'rt_user_logged_in', ( is_user_logged_in() ) ? "1" : "0" );
		wp_localize_script( 'rtmedia-pro-main', 'rtmedia_pro_url', RTMEDIA_PRO_URL );
		wp_enqueue_script( 'rtmedia-pro-most-rated-photos-widget', RTMEDIA_PRO_URL . "app/assets/js/rtmedia_pro_most_rated_photos_widget.js" );
		wp_enqueue_script( 'rtmedia-pro-playlist', RTMEDIA_PRO_URL . "lib/playlist/mep-feature-playlist.js", '', RTMEDIA_PRO_VERSION, true );
		wp_enqueue_style( 'rtmedia-pro-playlist', RTMEDIA_PRO_URL . "lib/playlist/mep-feature-playlist.css", '', RTMEDIA_PRO_VERSION );
		// javascript messages
		wp_localize_script( 'rtmedia-pro-main', 'rtmedia_empty_playlist_name_msg', __( 'Please provide a name for the playlist.' ) );
		wp_localize_script( 'rtmedia-pro-main', 'rtmedia_playlist_created_msg', __( 'playlist created successfully.' ) );
		wp_localize_script( 'rtmedia-pro-main', 'rtmedia_playlist_delete_confirmation', __( 'Are you sure you want to remove this media from this playlist?' ) );
		wp_localize_script( 'rtmedia-pro-main', 'rtmedia_playlist_media_added_msg', __( 'Media successfully added to the playlist.' ) );
		wp_localize_script( 'rtmedia-pro-main', 'rtmedia_select_playlist_msg', __( 'Please select a playlist or create a new one and then proceed.' ) );
		wp_localize_script( 'rtmedia-pro-main', 'rtmedia_playlist_creation_error_msg', __( 'Something went wrong While creating the Playlist. Please try again.' ) );
		wp_localize_script( 'rtmedia-pro-main', 'rtmedia_pro_max_file_size', __( 'Max file size limit :' ) );
		wp_localize_script( 'rtmedia-pro-main', 'rtmedia_file_type_msg', __( 'File type not allowed.' ) );
		wp_localize_script( 'rtmedia-pro-main', 'rtmedia_media_no_likes', __( 'No likes for the media' ) );
		wp_localize_script( 'rtmedia-pro-main', 'rtmedia_media_who_liked', __( 'Click to see who liked this media' ) );
		wp_localize_script( 'rtmedia-pro-main', 'rtmedia_file_not_allowed_singular', __( 'Following file is not allowed and can\'t be attached' ) );
		wp_localize_script( 'rtmedia-pro-main', 'rtmedia_file_not_allowed_plural', __( 'Following files are not allowed and can\'t be attached' ) );
		wp_localize_script( 'rtmedia-pro-main', 'rtmedia_file_not_deleted', __( 'Something went wrong while deleting the media. Please try again' ) );
		wp_localize_script( 'rtmedia-pro-main', 'rtmedia_pro_user_domain', trailingslashit( get_rtmedia_user_link( get_current_user_id() ) ) . RTMEDIA_MEDIA_SLUG );
		$upload_limit_messages                          = array();
		$upload_limit_messages[ 'size' ][ 'daily' ]     = __( 'You have exceeded daily quota for file size limit.', 'rtmedia' );
		$upload_limit_messages[ 'size' ][ 'monthly' ]   = __( 'You have exceeded monthly quota for file size limit.', 'rtmedia' );
		$upload_limit_messages[ 'size' ][ 'lifetime' ]  = __( 'You have exceeded lifetime quota for file size limit.', 'rtmedia' );
		$upload_limit_messages[ 'files' ][ 'daily' ]    = __( 'You have exceeded daily quota to upload files.', 'rtmedia' );
		$upload_limit_messages[ 'files' ][ 'monthly' ]  = __( 'You have exceeded monthly quota to upload files.', 'rtmedia' );
		$upload_limit_messages[ 'files' ][ 'lifetime' ] = __( 'You have exceeded lifetime quota to upload files.', 'rtmedia' );
		wp_localize_script( 'rtmedia-pro-main', 'rtmedia_pro_upload_limit_messages', apply_filters( 'rtmedia_pro_upload_limit_messages', $upload_limit_messages ) );
		wp_localize_script( 'rtmedia-pro-main', 'rtmedia_check_terms_message', apply_filters( 'rtmedia_pro_check_terms_message', 'Please check terms and conditions.' ) );
		wp_localize_script( 'rtmedia-pro-main', 'rtmedia_empty_favlist_name_msg', __( 'Please provide a name for the FavList.' ) );
		wp_localize_script( 'rtmedia-pro-main', 'rtmedia_favlist_created_msg', __( 'FavList created successfully.' ) );
		wp_localize_script( 'rtmedia-pro-main', 'rtmedia_favlist_delete_confirmation', __( 'Are you sure you want to remove this media from this FavList?' ) );
		wp_localize_script( 'rtmedia-pro-main', 'rtmedia_favlist_media_added_msg', __( 'Media successfully added to the FavList' ) );
		wp_localize_script( 'rtmedia-pro-main', 'rtmedia_select_favlist_msg', __( 'Please select a FavList or create a new one and then proceed.' ) );
		wp_localize_script( 'rtmedia-pro-main', 'rtmedia_favlist_creation_error_msg', __( 'Something went wrong While creating the FavList. Please try again.' ) );

	}
}