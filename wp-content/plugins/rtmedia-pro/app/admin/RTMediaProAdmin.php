<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of RTMediaProAdmin
 *
 * @author ritz
 */
class RTMediaProAdmin {
	public function __construct() {
		add_action( 'wp_ajax_rtmedia_shortcode_editor', array( 'RTMediaEditorLoader', 'content' ) );
		add_action( 'admin_enqueue_scripts', array( &$this, 'rtmedia_pro_admin_script' ) );
		add_filter( 'rtmedia_general_content_default_values', array( $this, 'rtmedia_general_content_add_default_value' ), 10, 1 );
		add_filter( 'rtmedia_general_content_add_itmes', array( $this, 'rtmedia_general_content_add_options' ), 10, 2 );
		add_filter( 'rtmedia_display_content_add_itmes', array( $this, 'rtmedia_general_content_single_view_options' ), 10, 2 );
		add_filter( 'rtmedia_album_control_setting', array( $this, 'rtmedia_album_control_setting' ), 10, 2 );
		add_filter( 'rtmedia_general_content_groups', array( $this, 'general_content_groups' ), 10, 1 );
		add_filter( 'rtmedia_check_enable_disable_like', array( $this, 'rtmedia_check_enable_diable_like' ), 10, 1 );
		$this->rtmedia_pro_check_for_like_button_site_option();
		add_action( 'init', array( &$this, 'rtmedia_pro_short_code_button' ) );
		//add_action('rtmedia_admin_page_insert', array($rtmedia_support, 'service_selector'),10);
		add_action( "rtmedia_type_settings_before_heading", array( $this, "add_media_type_settings_before_heading" ), 10 );
		add_action( "rtmedia_type_settings_before_body", array( $this, "add_media_type_settings_before_body" ), 10 );
		add_action( "rtmedia_type_settings_after_heading", array( $this, "add_media_type_settings_after_heading" ), 10 );
		add_action( "rtmedia_type_settings_after_body", array( $this, "add_media_type_settings_after_body" ), 10, 2 );
		add_filter( "rtmedia_migration_content_filter", array( $this, "rtmedia_migration_add_gd_migration" ), 10, 1 );
		add_filter( "rtmedia_admin_ui_handler_filter", array( $this, "rtmedia_pro_admin_ui_vertical_tabs" ), 1 );
		add_filter( "rtmedia_admin_tab_content_handler", array( $this, "rtmedia_pro_admin_vertical_tabs_content" ), 1 );
		add_filter( "rtmedia_display_content_groups", array( $this, 'rtmedia_display_content_groups' ), 10, 1 );
		add_filter( "rtmedia_add_settings_sub_tabs", array( $this, "rtmedia_add_wp_setting_tab" ), 40, 1 );
		add_action( 'rtmedia_media_type_setting_message', array( $this, 'add_media_type_setting_setting' ) );
		add_action( 'admin_notices', array( $this, 'rtmedia_pro_admin_notices' ) );
	}

	function rtmedia_pro_admin_notices() {
		$this->rtmedia_version_notice();
	}

	function rtmedia_version_notice() {
		if( defined( 'RTMEDIA_VERSION' ) && RTMEDIA_VERSION < '3.6.13' ) {
			?>
				<div class="error">
					<p>This version of rtMedia-PRO requires rtMedia version 3.6.13 or higher. Please update rtMedia.</p>
				</div>
			<?php
		}
	}

	function add_media_type_setting_setting() {
	?>
		<span style="float: right">0 = Unlimited</span>
	<?php
	}

	static function render_wordpress_content( $options ) {
		$render = array();

		return $render;
	}

	public static function wordpress_content() {
		global $rtmedia;
		$options        = $rtmedia->options;
		$render_options = self::render_wordpress_content( $options );
		$render_options = apply_filters( "rtmedia_wordpress_content_add_itmes", $render_options, $options );
		$general_group  = array();
		$general_group  = apply_filters( "rtmedia_wordpress_content_groups", $general_group );
		ksort( $general_group );
		$html = '';
		if ( ! ( sizeof( $render_options ) > 0 && sizeof( $general_group ) > 0 ) ){
			return;
		}
		foreach ( $general_group as $key => $value ) {
			?>
			<div class="postbox metabox-holder">
				<h3 class="hndle"><span><?php echo $value; ?></span></h3>
				<?php
				foreach ( $render_options as $tab => $option ) {

					if ( ! isset( $option[ 'group' ] ) ){
						$option[ 'group' ] = "90";
					}

					if ( $option[ 'group' ] != $key ){
						continue;
					}
					?>
					<div class="row section">
						<div class="columns large-6">
							<?php echo $option[ 'title' ]; ?>
						</div>
						<div class="columns large-6">
							<?php call_user_func( $option[ 'callback' ], $option[ 'args' ] ); ?>
							<span data-tooltip class="has-tip"
								  title="<?php echo ( isset( $option[ 'args' ][ 'desc' ] ) ) ? $option[ 'args' ][ 'desc' ] : "NA"; ?>"><i
									class="rtmicon-info-circle"></i></span>
						</div>
					</div>
					<?php
					if ( isset( $option[ 'after_content' ] ) ){
						?>
						<div class="row">
							<div class="columns large-12">
								<p class="rtmedia-info rtmedia-admin-notice">
									<?php echo $option[ 'after_content' ]; ?>
								</p>
							</div>
						</div>
					<?php
					}
					?>
				<?php
				}
				?>
			</div>
		<?php
		}
	}

	function rtmedia_add_wp_setting_tab( $sub_tabs ) {
		$sub_tabs[ 10 ] = array(
			'href' => '#rtmedia-wordpress', 'icon' => 'rtmicon-cogs', 'title' => __( 'WordPress', 'rtmedia' ), 'name' => __( 'WordPress', 'rtmedia' ), 'callback' => array( $this, 'wordpress_content' )
		);

		return $sub_tabs;
	}

	function rtmedia_display_content_groups( $general_group ) {
		$general_group[ 20 ] = "Miscellaneous";

		return $general_group;
	}

	function general_content_groups( $general_group ) {
		$general_group[ 20 ] = "Playlist Feature";

		return $general_group;
	}

	function rtmedia_pro_admin_vertical_tabs_content( $rtmedia_admin_tab_content_handler ) {
		$rtmedia_admin_tab_content_handler = "<div class='tabs-content vertical'>";

		return $rtmedia_admin_tab_content_handler;
	}

	function rtmedia_pro_admin_ui_vertical_tabs( $rtmedia_admin_ui_handler ) {
		$rtmedia_admin_ui_handler = "<div class='clearfix rtm-settings-tab-container rtmedia-pro'><dl class='tabs vertical' data-tab>";

		return $rtmedia_admin_ui_handler;
	}

	function add_media_type_settings_before_heading() {
		?>
		<div class="row"> <div class="columns large-9"> <div class="row">
	<?php
	}

function add_media_type_settings_before_body() {
	?>
	<div class="columns large-9">
	<div class="row">
<?php
}

function add_media_type_settings_after_heading() {
	?>
	</div>
	</div>
	<div class="columns large-3">
		<h4 data-tooltip class="has-tip" title="<?php echo __( "Limit file size for media.", "rtmedia" ); ?>">
			<abbr><?php echo __( "Upload Limit(MB)", "rtmedia" ); ?></abbr></h4>
	</div>
	</div>
<?php
}

	function add_media_type_settings_after_body( $key, $section ) {
		?>
		</div>
		</div>
		<div class="columns large-3">
			<?php
			if ( ! isset( $section[ 'upload' ] ) ){
				$section[ 'upload' ] = 0;
			}
			$args = array( 'key' => 'allowedTypes_' . $key . '_upload_limit', 'value' => $section[ 'upload' ], 'class' => array( 'rtmedia-setting-text-box' ) );
			RTMediaFormHandler::number( $args );
			?>
		</div>
	<?php
	}

	function rtmedia_general_content_add_default_value( $defaults ) {
		$defaults[ 'general_enableLikes' ]                      = 0;
		$defaults[ 'general_enableRatings' ]                    = 0;
		$defaults[ 'general_enableCreateAlbums' ]               = 0;
		$defaults[ 'general_albumsPerUser' ]                    = 0;
		$defaults[ 'general_enableDownloads' ]                  = 0;
		$defaults[ 'general_viewcount' ]                        = 0;
		$defaults[ 'moderation_enableModeration' ]              = 0;
		$defaults[ 'moderation_removeContentAfterReports' ]     = 0;
		$defaults[ 'moderation_adminEmails' ]                   = "";
		$defaults[ 'moderation_emailNotificationFreq' ]         = "daily";
		$defaults[ 'allowedTypes_photo_upload_limit' ]          = 0;
		$defaults[ 'allowedTypes_video_upload_limit' ]          = 0;
		$defaults[ 'allowedTypes_music_upload_limit' ]          = 0;
		$defaults[ 'allowedTypes_playlist_enabled' ]            = 1;
		$defaults[ 'allowedTypes_playlist_featured' ]           = 0;
		$defaults[ 'general_enablePlaylist' ]                   = 0;
		$defaults[ 'allowedTypes_document_upload_limit' ]       = 0;
		$defaults[ 'allowedTypes_document_enabled' ]            = 0;
		$defaults[ 'allowedTypes_document_featured' ]           = 0;
		$defaults[ 'allowedTypes_other_upload_limit' ]          = 0;
		$defaults[ 'allowedTypes_other_enabled' ]               = 0;
		$defaults[ 'allowedTypes_other_featured' ]              = 0;
		$defaults[ 'rtmedia_other_file_extensions' ]            = '';
		$defaults[ 'rtmedia_enablebbpress' ]                    = 0;
		$defaults[ 'rtmedia_bbpress_attachment_view' ]          = 'thumb_image';
		$defaults[ 'general_enableCommentForm' ]                = 0;
		$defaults[ 'general_comment_form_attachment_view' ]     = 'thumb_image';
		$defaults[ 'general_enable_anonymous_comment' ]         = 0;
		$defaults[ 'rtmedia_enable_feed' ]                      = 0;
		$defaults[ 'rtmedia_media_per_feed' ]                   = 30;
		$defaults[ 'rtmedia_enable_wp_album' ]                  = 0;
		$defaults[ 'rtmedia_wp_album_slug' ]                    = 'album';
		$defaults[ 'general_enable_music_playlist_view' ]       = 0;
		$defaults[ 'general_enable_user_likes' ]                = 0;
		$defaults[ 'general_enable_favlist' ]                   = 0;
		$defaults[ 'user_storage_limit_daily' ]                 = 0;
		$defaults[ 'user_storage_limit_monthly' ]               = 0;
		$defaults[ 'user_storage_limit_lifetime' ]              = 0;
		$defaults[ 'user_files_limit_daily' ]                   = 0;
		$defaults[ 'user_files_limit_monthly' ]                 = 0;
		$defaults[ 'user_files_limit_lifetime' ]                = 0;
		$defaults[ 'general_enable_document_other_table_view' ] = 0;
		$defaults[ 'general_enable_google_docs' ]               = 1;
		$defaults[ 'general_enable_upload_terms' ]              = 0;
		$defaults[ 'general_upload_terms_page_link' ]           = '';
		$defaults[ 'general_enable_url_upload' ]           		= 0;
		$defaults[ 'general_enable_media_share' ]           	= 0;
		global $rtmedia;
		if ( isset( $rtmedia->options[ 'rtmedia_other_file_extensions' ] ) && $rtmedia->options[ 'rtmedia_other_file_extensions' ] != '' ){
			$defaults[ 'rtmedia_other_file_extensions' ] = $rtmedia->options[ 'rtmedia_other_file_extensions' ];
		}

		return $defaults;
	}

	function rtmedia_pro_admin_script() {
		wp_enqueue_script( 'rtmedia-pro-admin', RTMEDIA_PRO_URL . "app/assets/js/admin.js", array( 'jquery' ), RTMEDIA_PRO_VERSION, true );
		wp_localize_script( 'rtmedia-pro-admin', 'rtmedia_loading_file', admin_url( "/images/loading.gif" ) );
		wp_localize_script( 'rtmedia-pro-admin', 'rtmedia_pro_url', RTMEDIA_PRO_URL );
		wp_localize_script( 'rtmedia-pro-admin', 'rtmedia_pro_ajax_url', admin_url( 'admin-ajax.php' ) );
		wp_enqueue_style( 'rtmedia-pro-rating-simple', RTMEDIA_PRO_URL . "app/assets/css/settings.css", '', RTMEDIA_PRO_VERSION );
		wp_localize_script( 'rtmedia-pro-admin', 'rtmedia_empty_extension_msg', __( 'Please provide some extensions for the Other file type.' ) );
		wp_localize_script( 'rtmedia-pro-admin', 'rtmedia_invalid_extension_msg', __( 'Please provide extensions seperated by commas. Ex: ' ) . "extn1,extn2,extn3" );
	}

	function rtmedia_pro_short_code_add_buttons( $plugin_array ) {
		$plugin_array[ 'rtmedia_pro_short_code' ] = RTMEDIA_PRO_URL . '/app/assets/js/rtmedia_pro_short_codes.js';

		return $plugin_array;
	}

	function rtmedia_pro_short_code_register_buttons( $buttons ) {
		array_push( $buttons, 'rtmedia_pro_short_code' );

		return $buttons;
	}

	function rtmedia_pro_short_code_button() {
		add_filter( "mce_external_plugins", array( &$this, "rtmedia_pro_short_code_add_buttons" ) );
		add_filter( 'mce_buttons', array( &$this, 'rtmedia_pro_short_code_register_buttons' ) );
	}

	function rtmedia_album_control_setting( $render_options, $options ) {
		$render_options[ 'general_enableCreateAlbums' ] = array(
			'title'    => __( 'Allow user to create new albums', 'rtmedia' ), 'callback' => array( 'RTMediaFormHandler', 'checkbox' ), 'args' => array(
				'key' => 'general_enableCreateAlbums', 'value' => $options[ 'general_enableCreateAlbums' ], 'desc' => __( 'You can either allow user to create new albums themselves using this setting and/or provide list of default albums.', 'rtmedia' ), 'class' => array( 'rtmedia-album-setting' )
			), 'group' => "20"
		);
		$render_options[ 'general_albumsPerUser' ]      = array(
			'title'    => __( 'Limit number of albums per user', 'rtmedia' ), 'callback' => array( 'RTMediaFormHandler', 'number' ), 'args' => array(
				'key' => 'general_albumsPerUser', 'value' => $options[ 'general_albumsPerUser' ], 'desc' => __( 'Number of albums a user can create. This limit will applied to all users. <em>0</em> means unlimited.', 'rtmedia' ), 'class' => array( 'rtmedia-setting-text-box', 'rtmedia-album-setting' ), 'min' => 0
			), 'group' => "20"
		);

		return $render_options;
	}

	function rtmedia_general_content_add_options( $render_options, $options ) {
		$render_options[ 'general_enablePlaylist' ] = array(
			'title'    => __( 'Enable playlists', 'rtmedia' ), 'callback' => array( 'RTMediaFormHandler', 'checkbox' ), 'args' => array(
				'key' => 'general_enablePlaylist', 'value' => $options[ 'general_enablePlaylist' ], 'desc' => __( 'Allow users to create playlists and add their favourite music to playlists. Playlist can be then downloaded in iTunes (m3u) format.', 'rtmedia' )
			), 'group' => 20
		);

		return $render_options;
	}

	function rtmedia_general_content_single_view_options( $render_options, $options ) {
		$render_options[ 'general_enableLikes' ]     = array(
			'title'    => __( 'Enable likes for media', 'rtmedia' ), 'callback' => array( 'RTMediaFormHandler', 'checkbox' ), 'args' => array(
				'key' => 'general_enableLikes', 'value' => $options[ 'general_enableLikes' ], 'desc' => __( 'You may want to disable like feature if you had enabled rating feature.', 'rtmedia' )
			), 'group' => "10"
		);
		$render_options[ 'general_enableRatings' ]   = array(
			'title'    => __( 'Enable 5 star rating for media', 'rtmedia' ), 'callback' => array( 'RTMediaFormHandler', 'checkbox' ), 'args' => array(
				'key' => 'general_enableRatings', 'value' => $options[ 'general_enableRatings' ], 'desc' => __( 'Allow user to rate media.', 'rtmedia' )
			), 'group' => "10"
		);
		$render_options[ 'general_enableDownloads' ] = array(
			'title'    => __( 'Enable download button', 'rtmedia' ), 'callback' => array( 'RTMediaFormHandler', 'checkbox' ), 'args' => array(
				'key' => 'general_enableDownloads', 'value' => $options[ 'general_enableDownloads' ], 'desc' => __( 'Display download button under media to allow users to download media.', 'rtmedia' )
			), 'group' => "10"
		);
		//        $render_options['general_enableAlbums']['group'] = "20";
		$render_options[ 'general_viewcount' ]                  = array(
			'title'    => __( 'Enable view count', 'rtmedia' ), 'callback' => array( 'RTMediaFormHandler', 'checkbox' ), 'args' => array(
				'key' => 'general_viewcount', 'value' => $options[ 'general_viewcount' ], 'desc' => __( 'You may want to show total views of the media to users.', 'rtmedia' )
			), 'group' => "10"
		);
		$render_options[ 'general_enable_music_playlist_view' ] = array(
			'title' => __( 'Display music in playlist style (only for music tab)', 'rtmedia' ), 'callback' => array( 'RTMediaFormHandler', 'checkbox' ), 'args' => array(
				'key' => 'general_enable_music_playlist_view', 'value' => $options[ 'general_enable_music_playlist_view' ], 'desc' => __( 'On music tab, rather than opening lightbox/single-page and listening to songs one-by-one, all songs can be played in playlist style.', 'rtmedia' )
			)
		);

		$render_options[ 'general_enable_document_other_table_view' ] = array(
			'title' => __( 'Display documents and other files in table style (only for Document and Others tab)', 'rtmedia' ), 'callback' => array( 'RTMediaFormHandler', 'checkbox' ), 'args' => array(
				'key' => 'general_enable_document_other_table_view', 'value' => $options[ 'general_enable_document_other_table_view' ], 'desc' => __( 'In Document and Others tab, all files can be displayed in tabular format.', 'rtmedia' )
			)
		);
		$render_options[ 'general_enable_google_docs' ]               = array(
			'title' => __( 'Enable Google Docs for documents and files.', 'rtmedia' ), 'callback' => array( 'RTMediaFormHandler', 'checkbox' ), 'args' => array(
				'key' => 'general_enable_google_docs', 'value' => $options[ 'general_enable_google_docs' ], 'desc' => __( 'In lightbox media view, display the docs, pdf, excel and other office documents in Google Docs. ', 'rtmedia' )
			)
		);

		return $render_options;
	}

	function rtmedia_check_enable_diable_ratting( $enable_ratting ) {
		global $rtmedia;
		$options = $rtmedia->options;
		if ( isset( $options[ 'general_enableRatings' ] ) && ( $options[ 'general_enableRatings' ] == "1" ) ){
			return true;
		} else {
			return false;
		}
	}

	function rtmedia_check_enable_diable_like( $enable_like ) {
		global $rtmedia;
		$options = $rtmedia->options;
		if ( isset( $options[ 'general_enableLikes' ] ) && ( $options[ 'general_enableLikes' ] == "1" ) ){
			return true;
		} else {
			return false;
		}
	}

	function rtmedia_pro_check_for_like_button_site_option() {
		$rtmedia_options = rtmedia_get_site_option( 'rtmedia-options' );
		if ( function_exists( "bp_get_option" ) ){
			if ( is_array( $rtmedia_options ) ){
				if ( ! isset( $rtmedia_options[ 'general_enableLikes' ] ) || $rtmedia_options[ 'general_enableLikes' ] == "" ){
					$rtmedia_options[ 'general_enableLikes' ] = "0";
				}
				if ( ! isset( $rtmedia_options[ 'general_enableRatings' ] ) || $rtmedia_options[ 'general_enableRatings' ] == "" ){
					$rtmedia_options[ 'general_enableRatings' ] = "0";
				}
				rtmedia_update_site_option( "rtmedia-options", $rtmedia_options );
			}
		}
	}

	function rtmedia_migration_add_gd_migration( $content ) {
		global $rtmedia;
		$options            = $rtmedia->options;
		$pending_gd_migrate = get_site_option( "rtm-gd-migration-pending-count" );
		if ( ( $pending_gd_migrate > 0 ) && isset( $options[ 'rtmedia_enablebbpress' ] ) && $options[ 'rtmedia_enablebbpress' ] != "0" ){
			$content = " ";
			$content .= '<div class="rtmedia-gd-migration-support">';
			$content .= ' <p>' . __( 'Click', 'rtmedia' ) . ' <a href="' . get_admin_url() . 'admin.php?page=rtmedia-migration-bbpress">' . __( 'here', 'rtmedia' ) . '</a>' . __( 'here to migrate attachments from GD bbPress Attachment to rtMedia.', 'rtmedia' ) . '</p>';
			$content .= '</div>';
		}

		return $content;
	}
}
