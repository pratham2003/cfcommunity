<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of RTMediaProBBPress
 *
 * @author ritz
 */
class RTMediaProBBPress {

	public function __construct( $init = true ) {
		if ( $init ){
			if ( class_exists( 'bbPress' ) ){
				add_filter( "rtmedia_add_settings_sub_tabs", array( $this, "rtmedia_pro_add_bbpress_tab" ), 30, 1 );
				$this->init();
			}
		}
	}

	public function init() {
		global $rtmedia;
		$options = $rtmedia->options;
		if ( isset( $options[ 'rtmedia_enablebbpress' ] ) && $options[ 'rtmedia_enablebbpress' ] != "0" ){
			add_action( 'bbp_template_before_single_topic', array( $this, 'rtmedia_init_global_media' ), 99 );
			add_action( 'wp_enqueue_scripts', array( $this, 'wp_enqueue_scripts' ), 1000 );
			add_action( 'bbp_theme_before_reply_form_submit_wrapper', array( $this, 'add_uploader' ), 99 );
			add_action( 'bbp_theme_before_topic_form_submit_wrapper', array( $this, 'add_uploader' ), 99 );

			add_action( 'bbp_edit_reply', array( $this, 'save_reply' ), 20, 5 );
			add_action( 'bbp_new_reply', array( $this, 'save_reply' ), 20, 5 );
			add_action( 'bbp_edit_topic', array( $this, 'save_topic' ), 20, 4 );
			add_action( 'bbp_new_topic', array( $this, 'save_topic' ), 20, 4 );

			add_filter( 'bbp_get_reply_content', array( $this, 'embed_attachments' ), 200, 2 );
			add_filter( 'bbp_get_topic_content', array( $this, 'embed_attachments' ), 200, 2 );

			add_action( 'bbp_deleted_topic', array( $this, 'delete_topic_attachments' ), 99 ,1 );
			add_action( 'bbp_deleted_reply', array( $this, 'delete_reply_attachments' ), 99 ,1 );
                        
                        add_filter( 'rtmedia_before_delete_media_redirect', array( $this, 'rtmedia_before_delete_media_redirect_bbpress' ), 10, 1 );
		}
	}
        
        function rtmedia_before_delete_media_redirect_bbpress( $redirect_url ) {
            global $rtmedia_query;
                        
            $post = get_post( $rtmedia_query->media[ 0 ]->context_id );
            
            if( $rtmedia_query->media[ 0 ]->context == 'forum' || ( ( $rtmedia_query->media[ 0 ]->context == 'topic' || $rtmedia_query->media[ 0 ]->context == 'bp_group' ) && bbp_is_topic( $rtmedia_query->media[ 0 ]->context_id ) ) ) {
                $redirect_url = bbp_get_topic_permalink( $rtmedia_query->media[ 0 ]->context_id );
            } else if( ( $rtmedia_query->media[ 0 ]->context == 'reply' || $rtmedia_query->media[ 0 ]->context == 'bp_group' || $rtmedia_query->media[ 0 ]->context == 'topic' ) && bbp_is_reply( $rtmedia_query->media[ 0 ]->context_id ) ) {
                $redirect_url = bbp_get_topic_permalink( $post->post_parent );
            }           
            
            return $redirect_url;
        }

	function delete_topic_attachments( $topic_id ) {
		$media_model = new RTMediaModel();
		$media_media = new RTMediaMedia();
		$media_list = $media_model->get( array( 'context' => 'topic', 'context_id' => $topic_id ) );
		foreach( $media_list as $media ){
			$media_media->delete( $media->id );
		}
	}
	function delete_reply_attachments( $reply_id ) {
		$media_model = new RTMediaModel();
		$media_media = new RTMediaMedia();
		$media_list = $media_model->get( array( 'context' => 'reply', 'context_id' => $reply_id ) );
		foreach( $media_list as $media ){
			$media_media->delete( $media->id );
		}
	}

	function rtmedia_pro_add_bbpress_tab( $sub_tabs ) {
		$sub_tabs[ 15 ] = array(
			'href' => '#rtmedia-bbpress', 'icon' => 'rtmicon-user', 'title' => __( 'rtMedia For bbPress', 'rtmedia' ), 'name' => __( 'bbPress', 'rtmedia' ), 'callback' => array( $this, 'rtmedia_bbpress_content' )
		);

		return $sub_tabs;
	}

	function rtmedia_bbpress_content() {
		global $rtmedia;
		$options                                             = $rtmedia->options;
		$render_options                                      = array();
		$render_options[ 'rtmedia_enablebbpress' ]           = array(
			'title' => __( 'Enable attachments in topic/reply', 'rtmedia' ), 'callback' => array( 'RTMediaFormHandler', 'checkbox' ), 'args' => array(
				'key' => 'rtmedia_enablebbpress', 'value' => $options[ 'rtmedia_enablebbpress' ], 'desc' => __( 'Option to upload/attach media/files to bbPress topic/reply on front-end.', 'rtmedia' ), 'class' => array( 'rtm_enable_bbpress' )
			)
		);
		$radios                                              = array();
		$radios[ 'thumb_image' ]                             = "<strong>With Thumbnail + file name</strong>";
		$radios[ 'thumb_title' ]                             = "<strong>Without Thumbnail (only file name)</strong>";
		$render_options[ 'rtmedia_bbpress_attachment_view' ] = array(
			'title' => __( "Display attachments in topic/reply as", "rtmedia" ), 'callback' => array( "RTMediaFormHandler", "radio" ), 'args' => array(
				'key' => 'rtmedia_bbpress_attachment_view', 'radios' => $radios, 'default' => $options[ 'rtmedia_bbpress_attachment_view' ], 'class' => array( 'rtm_bbpress_default_view' ), 'desc' => __( 'Control how you want to display uploaded/attached media files in reply.', 'rtmedia' )
			),
		);
		?>
		<div class="postbox metabox-holder">
			<h3 class="hndle"><span>Attachment Support for bbPress</span></h3>
			<?php
			foreach ( $render_options as $key => $option ) {
				?>
				<div class="row section">
					<div class="columns large-6">
						<?php echo $option[ 'title' ]; ?>
					</div>
					<div class="columns large-6">
						<?php call_user_func( $option[ 'callback' ], $option[ 'args' ] ); ?>
						<span data-tooltip class="has-tip"
							  title="<?php echo ( isset( $option[ 'args' ][ 'desc' ] ) ) ? $option[ 'args' ][ 'desc' ] : "NA"; ?>"><i
								class="rtmicon-info-circle rtmicon-fw"></i></span>
					</div>
					<div class="clearfix"></div>
				</div>
				<div class="clearfix"></div>
			<?php
			}

			?>
			<?php
			if ( class_exists( "gdbbPressAttachments" ) ){
				?>
				<p class='rtmedia-info rtmedia-admin-notice' id="rtmedia_gd_bbp_attachement_notice">
					<?php _e( 'Looks like you have GD bbPress Attachment installed. Click' ) . " "; ?>
					<a href="<?php echo get_admin_url(); ?>admin.php?page=rtmedia-migration-bbpress"><?php _e( 'here' ); ?></a>
					<?php " " . _e( 'to migrate attachments from GD bbPress Attachment to rtMedia.' ); ?>
				</p>
			<?php
			}
			?>
			<p class='rtmedia-info rtmedia-admin-notice' id="rtmedia_bbp_attachment_allow_other">
				<?php _e( 'You can ' ); ?>
				<a href="#rtmedia-types"><?php _e( 'manage file types/extension from here' ); ?></a>
			</p>
		</div>
	<?php

	}

	public function rtmedia_init_global_media() {
		//	global $wpdb;
		//	global $rtm_attached_media;
		//	global $rtmedia_media;
		//	$rtm_attached_media = "";
		//	$media_model = new RTMediaModel();
		//	if(function_exists("bbp_get_topic_id")) {
		//	    $topic_id = bbp_get_topic_id();
		//	    if($topic_id != "" && $topic_id != '0') {
		//		$attachment_query_post = "select * from (
		//					    select *
		//					    from $wpdb->posts
		//					    where post_parent = '$topic_id' and post_type = 'attachment' and guid <> ''
		//
		//					    union
		//
		//					    select c.*
		//					    from $wpdb->posts p, $wpdb->posts c
		//					    where p.post_parent = '$topic_id' and p.ID = c.post_parent and p.post_type = 'reply' and p.post_status = 'publish' and c.post_type = 'attachment'
		//					    and c.guid <> ''
		//					) as a
		//					order by a.ID
		//			.		";
		//		$attachments_post = $wpdb->get_results( $attachment_query_post );
		//		$rtm_attached_media = $attachments_post;
		//	    }
		//	}
	}

	public function wp_enqueue_scripts() {
		if ( bbp_get_forum_id() > 0 || bbp_get_reply_id() > 0 || bbp_get_topic_id() > 0 ){
			wp_localize_script( 'rtmedia-pro-main', 'rtmedia_bbpress_page', 'true' );
		}
	}

	function add_uploader() {
		$allow_upload = apply_filters( 'rtmedia_allow_uploader_view', true, 'bbpress' );
		if ( $allow_upload ){
			?>
			<p class="bbp-attachments-form">
				<?php echo RTMediaUploadShortcode::pre_render( array( "rtmedia_simple_file_upload" => true, "rtmedia_upload_without_form" => true, "rtmedia_upload_allow_multiple" => true, "privacy" => "0" ) ); ?>
			</p>
		<?php
		} else {
			echo "<div class='rtmedia-upload-not-allowed'>" . apply_filters( 'rtmedia_upload_not_allowed_message', __( 'You are not allowed to upload/attach media.', 'rtmedia' ), 'bbpress' ) . "</div>";
		}
	}

	function change_upload_dir( $upload_dir ) {
		$upload_dir[ 'path' ] = trailingslashit( $upload_dir[ 'basedir' ] ) . 'rtMedia/topics/' . $_POST[ 'context_id' ] . $upload_dir[ 'subdir' ];
		$upload_dir[ 'url' ]  = trailingslashit( $upload_dir[ 'baseurl' ] ) . 'rtMedia/topics/' . $_POST[ 'context_id' ] . $upload_dir[ 'subdir' ];

		return $upload_dir;
	}

	public function save_topic( $topic_id, $forum_id, $anonymous_data, $topic_author ) {
		$this->save_reply( 0, $topic_id, $forum_id, $anonymous_data, $topic_author );
	}

	public function save_reply( $reply_id, $topic_id, $forum_id, $anonymous_data, $reply_author ) {
		global $rtmedia_forum_id;
		$rtmedia_forum_id = $forum_id;
		$rtmedia_upload   = new RTMediaUploadEndpoint();
		// set reply id as context
		$_POST[ 'context_id' ] = $reply_id;
		if ( $reply_id == 0 ){
                    $_POST[ 'context_id' ] = $topic_id;
                    $_POST[ 'context' ] = 'topic';
                } else {
                    $_POST[ 'context' ] = 'reply';
                }
		if ( bbp_is_forum_hidden( $forum_id ) || bbp_is_forum_private( $forum_id ) ){
			$_POST[ 'privacy' ] = "20";
		}
		add_filter( 'rtmedia_filter_upload_dir', array( $this, 'change_upload_dir' ), 10, 1 );
		if ( isset( $_FILES[ 'rtmedia_file_multiple' ] ) && isset( $_FILES[ 'rtmedia_file_multiple' ][ 'name' ] ) && isset( $_FILES[ 'rtmedia_file_multiple' ][ 'name' ][ 0 ] ) && $_FILES[ 'rtmedia_file_multiple' ][ 'name' ][ 0 ] != '' ){
			if ( isset( $_FILES[ 'rtmedia_file_multiple' ] ) && sizeof( $_FILES[ 'rtmedia_file_multiple' ] ) > 0 ){
				foreach ( $_FILES[ 'rtmedia_file_multiple' ][ 'error' ] as $key => $val ) {
					add_action( 'rtmedia_after_add_media', array( $this, 'rtmedia_after_add_media' ), 10, 3 );
					$file_name                = $_FILES[ 'rtmedia_file_multiple' ][ 'name' ][ $key ];
					$file                     = array(
						'name' => $file_name, 'type' => $_FILES[ 'rtmedia_file_multiple' ][ 'type' ][ $key ], 'size' => $_FILES[ 'rtmedia_file_multiple' ][ 'size' ][ $key ], 'tmp_name' => $_FILES[ 'rtmedia_file_multiple' ][ 'tmp_name' ][ $key ], 'error' => $_FILES[ 'rtmedia_file_multiple' ][ 'error' ][ $key ]
					);
					$_FILES[ 'rtmedia_file' ] = $file;
					$media                    = $rtmedia_upload->template_redirect( false );
				}
			}
		}
	}

	function rtmedia_after_add_media( $media_ids, $file_object, $uploaded ) {
		global $rtmedia_forum_id;
		$rtmedia_model = new RTMediaModel();
		if ( ! is_rtmedia_privacy_enable() ){
			if ( bbp_is_forum_hidden( $rtmedia_forum_id ) || bbp_is_forum_private( $rtmedia_forum_id ) ){
				$privacy = "20";
			} else {
				$privacy = "0";
			}
			$rtmedia_model->update( array( 'privacy' => $privacy ), array( 'id' => $media_ids[ 0 ] ) );
		}
	}

	public function embed_attachments( $content, $topic_id ) {
		global $wpdb;
		global $rtmedia;
		$options = $rtmedia->options;
		if ( $topic_id != "" && $topic_id != "0" ){
			$attachment_query_post = "  select *
		    from $wpdb->posts
		    where post_parent = '$topic_id' and post_type = 'attachment' and guid <> ''";
			$attachments           = $wpdb->get_results( $attachment_query_post );
			if ( is_array( $attachments ) && sizeof( $attachments ) > 0 ){
				$content .= "<hr>";
				$content .= "<h6>" . __( 'Attachments', 'rtmedia') .": </h6>";
				$content .= "<ul class='rtm-bbp-container'>";
				foreach ( $attachments as $attachment ) {
					$attachment_content = " ";
					$rtm_id             = rtmedia_id( $attachment->ID );
					if ( $rtm_id ){
						$media_title = get_rtmedia_title( $rtm_id );
						if ( isset( $options[ 'rtmedia_bbpress_attachment_view' ] ) && $options[ 'rtmedia_bbpress_attachment_view' ] == "thumb_title" ){
							$attachment_content .= " <li class='rtm-bbp-title-view'> <div> ";
							$attachment_content .= " <a href='" . get_rtmedia_permalink( $rtm_id ) . "' target='_blank' class='rtm-bbp-attachment'> ";
							$attachment_content .= " <p>" . __( $media_title ) . " </p>";
							$attachment_content .= " </a> </div> </li>";
						} else {
							$attachment_content .= " <li class='rtm-bbp-thumb-view'> <div> ";
							$attachment_content .= " <a href='" . get_rtmedia_permalink( $rtm_id ) . "' target='_blank' class='rtm-bbp-attachment' title='" . __( $media_title ) . "' > ";
							$attachment_content .= " <img src='" . rtmedia_image( "rt_media_thumbnail", $rtm_id, false ) . "' alt='" . __( $media_title ) . "' > ";
							$attachment_content .= " <p>" . __( $media_title ) . " </p>";
							$attachment_content .= " </a> </div> </li>";
						}
					} else {
						if ( isset( $options[ 'rtmedia_bbpress_attachment_view' ] ) && $options[ 'rtmedia_bbpress_attachment_view' ] == "thumb_title" ){
							$attachment_content .= " <li class='rtm-bbp-title-view'> <div> ";
						} else {
							$attachment_content .= " <li class='rtm-bbp-thumb-view'> <div> ";
						}
						$attachment_content .= " <p>" . __( 'This attachment can not be displayed.', 'rtmedia' ) . " </p>";
						$attachment_content .= " </div> </li>";
					}
					$content .= apply_filters( 'rtmedia_bbpress_individual_attachment', $attachment_content, $attachment );
				}
				$content .= " </ul> ";
				$content = apply_filters( 'rtmedia_bbpress_attachment_view_filter', $content, $attachments );
			}

		}

		return $content;
	}
}
