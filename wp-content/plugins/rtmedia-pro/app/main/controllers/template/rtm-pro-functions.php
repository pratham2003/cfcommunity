<?php

add_filter( "rtm_is_album_create_enable", "is_album_create_enable", 10, 1 );
function is_album_create_enable( $return ) {
	global $rtmedia;
	if ( ( isset ( $rtmedia->options[ "general_enableCreateAlbums" ] ) && $rtmedia->options[ "general_enableCreateAlbums" ] == "0" ) ){
		$return = false;
	}

	return $return;
}

add_filter( "rtm_display_create_album_button", "display_create_album_button", 10, 2 );

function display_create_album_button( $display, $user_id ) {
	if ( $display ){
		$counts = get_user_meta( $user_id, 'rtmedia_counts_' . get_current_blog_id(), true );
		global $rtmedia;
		if ( isset( $rtmedia->options[ "general_albumsPerUser" ] ) && ( $rtmedia->options[ "general_albumsPerUser" ] > 0 ) && is_array( $counts ) ){
			$total_album_count = 0;
			foreach ( $counts as $privacy_level ) {
				if ( isset( $privacy_level->album ) ){
					$total_album_count += $privacy_level->album;
				}
			}
			$allow_album = $rtmedia->options[ "general_albumsPerUser" ];
			if ( $allow_album <= $total_album_count ){
				$display = false;
			}
		}
	}

	return $display;
}

add_filter( "rtmedia_modify_upload_params", "modify_upload_params", 10, 1 );
function modify_upload_params( $params ) {
	global $rtmedia;
	$options      = $rtmedia->options;
	$upload_limit = array();
	if ( isset( $options[ 'allowedTypes_photo_upload_limit' ] ) && $options[ 'allowedTypes_photo_upload_limit' ] > 0 ){
		$upload_limit[ 'photo_max_file_size' ] = $options[ 'allowedTypes_photo_upload_limit' ];
	}
	if ( isset( $options[ 'allowedTypes_video_upload_limit' ] ) && $options[ 'allowedTypes_video_upload_limit' ] > 0 ){
		$upload_limit[ 'video_max_file_size' ] = $options[ 'allowedTypes_video_upload_limit' ];
	}
	if ( isset( $options[ 'allowedTypes_music_upload_limit' ] ) && $options[ 'allowedTypes_music_upload_limit' ] > 0 ){
		$upload_limit[ 'music_max_file_size' ] = $options[ 'allowedTypes_music_upload_limit' ];
	}

	if ( $upload_limit != "" ){
		$params[ 'rtmedia_pro_max_file_size' ] = json_encode( $upload_limit );
	}
	$params[ 'rtmedia_pro_filters' ] = apply_filters( 'allowed_media_type_settings', $rtmedia->allowed_types );

	$temp_size_array = array();
	foreach ( $params[ 'rtmedia_pro_filters' ] as $type => $value ) {
		if ( ! empty( $value[ "extn" ] ) ){
			foreach ( $value[ "extn" ] as $extn ) {
				$temp_size_array[ $extn ] = array( "type" => $type, 'size' => isset( $options[ 'allowedTypes_' . $type . '_upload_limit' ] ) ? floatval( $options[ 'allowedTypes_' . $type . '_upload_limit' ] ) : 0 );
			}
		}
	}
	$params[ "upload_size" ] = $temp_size_array;

	return $params;
}

function rtmedia_plupload_file_size_msg( $file_size ) {
	global $rtmedia;
	$options      = $rtmedia->options;
	$upload_limit = "";
	if ( isset( $options[ 'allowedTypes_photo_upload_limit' ] ) && $options[ 'allowedTypes_photo_upload_limit' ] > 0 ){
		$upload_limit .= __( "Photos: ", "rtmedia" ) . $options[ 'allowedTypes_photo_upload_limit' ] . " MB";
	}
	if ( isset( $options[ 'allowedTypes_video_upload_limit' ] ) && $options[ 'allowedTypes_video_upload_limit' ] > 0 ){
		if ( $upload_limit != "" ){
			$upload_limit .= ", ";
		}
		$upload_limit .= __( "Video: ", "rtmedia" ) . $options[ 'allowedTypes_video_upload_limit' ] . " MB";
	}
	if ( isset( $options[ 'allowedTypes_music_upload_limit' ] ) && $options[ 'allowedTypes_music_upload_limit' ] > 0 ){
		if ( $upload_limit != "" ){
			$upload_limit .= ", ";
		}
		$upload_limit .= __( "Music: ", "rtmedia" ) . $options[ 'allowedTypes_music_upload_limit' ] . " MB";
	}
	if ( $upload_limit != "" ){
		return $upload_limit;
	} else {
		return $file_size;
	}
}

add_filter( "rtmedia_plupload_file_size_msg", "rtmedia_plupload_file_size_msg", 99, 1 );
/*
 * Checks if playlists are enabled from rtmedia settings
 */
function is_rtmedia_playlist_enable() {
	global $rtmedia;
	if ( isset ( $rtmedia->options[ "general_enablePlaylist" ] ) && $rtmedia->options[ "general_enablePlaylist" ] != "0" ){
		return true;
	}

	return false;
}

/*
 * Checks if Document support is enabled from rtmedia settings
 */
function is_rtmedia_upload_document_enabled() {
	global $rtmedia;
	if ( isset ( $rtmedia->options[ "allowedTypes_document_enabled" ] ) && $rtmedia->options[ "allowedTypes_document_enabled" ] != "0" ){
		return true;
	}

	return false;
}

/*
 * Checks if Document support is enabled from rtmedia settings
 */
function is_rtmedia_upload_other_enabled() {
	global $rtmedia;
	if ( isset ( $rtmedia->options[ "allowedTypes_other_enabled" ] ) && $rtmedia->options[ "allowedTypes_other_enabled" ] != "0" ){
		return true;
	}

	return false;
}

/*
 * Check if the current media is a document
 */
function is_rtmedia_document() {
	global $rtmedia_query;
	if ( $rtmedia_query->media_query[ 'media_type' ] == 'document' ){
		return true;
	} else {
		return false;
	}
}

/*
 * Check if the current media is a document
 */
function is_rtmedia_other_file_type() {
	global $rtmedia_query;
	if ( $rtmedia_query->media_query[ 'media_type' ] == 'other' ){
		return true;
	} else {
		return false;
	}
}

add_filter( 'rtmedia_set_media_type_filter', 'rtmedia_pro_set_media_type', 10, 2 );
function rtmedia_pro_set_media_type( $media_type, $file_object ) {

	if ( isset( $file_object ) && $file_object != '' ){
		$is_document = is_rtmedia_document_extension( $file_object );
		if ( $is_document ){
			return 'document';
		} else {
			return 'other';
		}
	}

	return $media_type;
}

// checks if the extension of the file belongs to the document media type
function is_rtmedia_document_extension( $file_object ) {
	if ( isset( $file_object[ 0 ][ 'file' ] ) && $file_object[ 0 ][ 'file' ] != "" && is_rtmedia_upload_document_enabled() ){
		$extn = pathinfo( $file_object[ 0 ][ 'file' ] );
		$extn = $extn[ 'extension' ];
		global $document_extensions;
		if ( isset( $document_extensions ) && in_array( $extn, $document_extensions ) ){
			return true;
		}
	}

	return false;

}


/**
 *
 * @return boolean
 */
function is_rtmedia_playlist_gallery() {
	global $rtmedia_query;
	if ( $rtmedia_query ){
		return $rtmedia_query->is_playlist_gallery();
	} else {
		return false;
	}
}

/**
 *
 * @return boolean
 */
function is_rtmedia_playlist() {
	global $rtmedia_query;
	if ( $rtmedia_query ){
		return $rtmedia_query->is_playlist();
	} else {
		return false;
	}
}

//disabled view counts from original place
// add_action("rtmedia_view_media_counts","view_media_counts", 10, 1);
function view_media_counts( $view_obj ) {
	global $rtmedia;
	$options = $rtmedia->options;
	if ( isset( $options[ 'general_viewcount' ] ) && ( $options[ 'general_viewcount' ] == "1" ) ){
		$media_id   = $view_obj->action_query->id;
		$action     = $view_obj->action;
		$curr_count = get_rtmedia_meta( $media_id, $action );
		if ( $curr_count == "" || sizeof( $curr_count ) == 0 ){
			$count = 0;
		} else {
			$count = $curr_count;
		}
		echo '<label class="rtmedia-pro-view-counts">' . __( 'Media Views: ' . $count ) . '</label>';
	}
}

//show media views below user details in new-lightbox-UI
add_action( "rtmedia_actions_before_description", "rtmedia_get_media_view_counts", 10, 1 );
function rtmedia_get_media_view_counts( $media_id = false ) {
	global $rtmedia;
	$options = $rtmedia->options;
	if ( isset( $options[ 'general_viewcount' ] ) && ( $options[ 'general_viewcount' ] == "1" ) ){
		$counts = $plural = "";
		$counts = get_rtmedia_meta( $media_id, 'view' );
		if ( $counts == "" || sizeof( $counts ) == 0 || $counts == 0 ){
			$counts = 1;
		}
		$view = __( " Views", 'rtmedia' );
		if ( $counts == 1 ){
			$view = __( ' View', 'rtmedia' );
		}
		$counts = $counts . $view;

		echo "<div class='rtmedia-media-views'><span>" . $counts . "</span> </div>";
	}
}


add_action( 'rtmedia_playlist_gallery_actions', 'rtmedia_create_playlist', 10 );
function rtmedia_create_playlist() {
	if ( ! is_rtmedia_playlist_enable() ){
		return;
	}
	$return = true;
	$return = apply_filters( "rtm_is_playlist_create_enable", $return );
	if ( ! $return ){
		return;
	}

	global $rtmedia_query;
	$user_id     = get_current_user_id();
	$display     = false;
	$playlist_el = "";
	if ( isset ( $rtmedia_query->query[ 'context' ] ) && in_array( $rtmedia_query->query[ 'context' ], array( 'profile', 'group' ) ) ){
		switch ( $rtmedia_query->query[ 'context' ] ) {
			case 'profile':
				if ( $rtmedia_query->query[ 'context_id' ] == $user_id ){
					$display    = true;
					$display    = apply_filters( "rtm_display_create_playlist_button", $display, $user_id );
					$provacyObj = new RTMediaPrivacy();
					$privacy_el = $provacyObj->select_privacy_ui( false );
					if ( $privacy_el ){
						$privacy_el = "<label> " . __( 'Privacy :', 'rtmedia' ) . " </label>" . $privacy_el;
					} else {
						$privacy_el = "";
					}
				}
				break;
			case 'group':
				$group_id = $rtmedia_query->query[ 'context_id' ];
				if ( can_user_create_playlist_in_group() ){
					$display    = true;
					$privacy_el = "<input type='hidden' name='privacy' value='0'>";
				}
				break;
		}
	}

	if ( $display === true ){
		?>
		<div id="rtmedia-media-options">
			<a href="#new-playlist-modal" class="rtmedia-modal-link"
			   title="<?php _e( "Add New Playlist", "rtmedia" ); ?>"><i
					class="rtmicon-plus-circle"></i><?php _e( "Add Playlist", "rtmedia" ); ?></a>
		</div>
		<div id="new-playlist-modal" class="rtmedia-popup mfp-hide">
			<div id="rtm-modal-container">
				<h2 class="rtm-modal-title"><?php _e( "Create New Playlist", "rtmedia" ); ?></h2>

				<p>
					<label for='rtmedia_playlist_name'><?php _e( 'Title :', 'rtmedia' ); ?></label><input type="text"
																										  id="rtmedia_playlist_name"
																										  value=""
																										  class='rtm-input-medium'/>
				</p>

				<div>
					<input type="hidden" id="rtmedia_playlist_context"
						   value="<?php echo $rtmedia_query->query[ 'context' ]; ?>">
					<input type="hidden" id="rtmedia_playlist_context_id"
						   value="<?php echo $rtmedia_query->query[ 'context_id' ]; ?>">
					<?php echo $privacy_el ?>
					<button type="button"
							id="rtmedia_create_new_playlist"><?php _e( "Create Playlist", "rtmedia" ); ?></button>
				</div>
			</div>
		</div>
	<?php
	}
}

/**
 *
 * @param type $group_id
 * @param type $user_id
 *
 * @return boolean
 */
function can_user_create_playlist_in_group( $group_id = false, $user_id = false ) {
	if ( $group_id == false ){
		$group    = groups_get_current_group();
		$group_id = $group->id;
	}
	$upload_level = groups_get_groupmeta( $group_id, "rtmp_create_playlist_control_level" );
	if ( empty ( $upload_level ) ){
		$upload_level = "all";
	}

	$user_id      = get_current_user_id();
	$display_flag = false;
	if ( groups_is_user_member( $user_id, $group_id ) ){
		if ( $upload_level == "admin" ){
			if ( groups_is_user_admin( $user_id, $group_id ) > 0 ){
				$display_flag = true;
			}
		} else {
			if ( $upload_level == "moderators" ){
				if ( groups_is_user_mod( $user_id, $group_id ) || groups_is_user_admin( $user_id, $group_id ) ){
					$display_flag = true;
				}
			} else {
				$display_flag = true;
			}
		}
	}

	return $display_flag;
}


function rtm_playlist_creation_settings_groups_edit() {
	$current_playlist_level = groups_get_groupmeta( bp_get_current_group_id(), 'rtmp_create_playlist_control_level' );
	if ( empty ( $current_playlist_level ) ){
		$current_level = "all";
	}
	?>
	<h4><?php _e( "Playlist Creation Control", 'rtmedia' ); ?></h4>
	<p><?php _e( "Who can create Playlists in this group?", 'rtmedia' ); ?></p>
	<div class="radio">
		<label>
			<input name="rtmp_playlist_creation_control" type="radio" id="rtmp_media_group_level_all"
				   value="all" <?php checked( $current_playlist_level, 'all', true ) ?> >
			<strong><?php _e( "All Group Members", 'rtmedia' ); ?></strong>
		</label>
		<label>
			<input name="rtmp_playlist_creation_control" type="radio" id="rtmp_media_group_level_moderators"
				   value="moderators" <?php checked( $current_playlist_level, 'moderators', true ) ?> >
			<strong><?php _e( "Group Admins and Mods only", 'rtmedia' ); ?></strong>
		</label>
		<label>
			<input name="rtmp_playlist_creation_control" type="radio" id="rtmp_media_group_level_admin"
				   value="admin" <?php checked( $current_playlist_level, 'admin', true ) ?> >
			<strong><?php _e( "Group Admin only", 'rtmedia' ); ?></strong>
		</label>
	</div>
	<hr>
<?php
}

function rtm_playlist_creation_settings_create_group() {
	?>
	<hr>
	<h4><?php _e( "Playlist Creation Control", 'rtmedia' ); ?></h4>
	<p><?php _e( "Who can create Playlists in this group?", 'rtmedia' ); ?></p>
	<div class="radio">
		<label>
			<input name="rtmp_playlist_creation_control" type="radio" id="rtmp_media_group_level_all" value="all"
				   checked='checked'>
			<strong><?php _e( "All Group Members", 'rtmedia' ); ?></strong>
		</label>
		<label>
			<input name="rtmp_playlist_creation_control" type="radio" id="rtmp_media_group_level_moderators"
				   value="moderators">
			<strong><?php _e( "Group Admins and Mods only", 'rtmedia' ); ?></strong>
		</label>
		<label>
			<input name="rtmp_playlist_creation_control" type="radio" id="rtmp_media_group_level_admin" value="admin">
			<strong><?php _e( "Group Admin only", 'rtmedia' ); ?></strong>
		</label>
	</div>
<?php
}

function rtm_create_save_group_media_settings( $settings ) {
	if ( isset( $settings[ 'rtmp_playlist_creation_control' ] ) && $settings[ 'rtmp_playlist_creation_control' ] != "" ){
		$success = groups_update_groupmeta( bp_get_current_group_id(), 'rtmp_create_playlist_control_level', $settings[ 'rtmp_playlist_creation_control' ] );
	}
}

function rtm_edit_save_group_media_settings( $settings ) {
	if ( isset( $settings[ 'rtmp_playlist_creation_control' ] ) && $settings[ 'rtmp_playlist_creation_control' ] != "" ){
		$success = groups_update_groupmeta( bp_get_current_group_id(), 'rtmp_create_playlist_control_level', $settings[ 'rtmp_playlist_creation_control' ] );
	}
}

global $rtmedia;
$options = $rtmedia->options;
if ( isset ( $rtmedia->options[ "general_enablePlaylist" ] ) && $rtmedia->options[ "general_enablePlaylist" ] == 1 ){ // playlist is enabled){
	add_action( 'rtmedia_playlist_creation_settings_create_group', 'rtm_playlist_creation_settings_create_group' );
	add_action( 'rtmedia_create_save_group_media_settings', 'rtm_create_save_group_media_settings' );
	add_action( 'rtmedia_edit_save_group_media_settings', 'rtm_edit_save_group_media_settings' );
	add_filter( 'rtmedia_group_media_extension', 'bp_group_rtmedia_extension_filter', 10, 1 ); // if playlist is enabled, return true.
	add_action( 'rtmedia_playlist_creation_settings_groups_edit', 'rtm_playlist_creation_settings_groups_edit' );
}

function bp_group_rtmedia_extension_filter( $extension ) {
	$extension = true;

	return $extension;
}

function get_playlist_media_list_table() {
	$rtmedia_playlist = new RTMediaProPlaylist();
	$table            = $rtmedia_playlist->edit_playlists_media_list( rtmedia_type() );

	if ( $table ){
		echo $table;
	} else {
		echo "<i class='rtmicon-attention'></i>" . __( ' Currently there are no media in this playlist.' );
	}
}

add_action( "rtmedia_add_album_privacy", "add_album_privacy", 10 );
function add_album_privacy( $screen = false ) { //$screen for identifying from where the do_action is applied
	if ( function_exists( "bp_is_group" ) ){
		if ( bp_is_group() ){
			return;
		}
	}
	$rtmediaprivacy = new RTMediaPrivacy( false );
	$rtmediaprivacy = $rtmediaprivacy->select_privacy_ui( false, "rtmedia_select_album_privacy" );
	if ( $rtmediaprivacy ){
		if ( $screen == 'album-edit' ){
			echo "<div class='rtmedia-edit-privacy'><label>" . __( 'Privacy : ', 'rtmedia' ) . "</label>" . $rtmediaprivacy . "</div>";
		} else {
			echo "<label for='rtmedia_select_album_privacy'>" . __( 'Privacy : ', 'rtmedia' ) . "</label>" . $rtmediaprivacy;
		}
	}
}

add_filter( "rtmedia-get-album-where-query", "change_get_album_query", 10, 2 );
function change_get_album_query( $where, $table_name ) {
	$rtmedia_query = new RTMediaQuery( false );
	$where         = $rtmedia_query->privacy_filter( $where, $table_name );

	return $where;
}

add_filter( "rtmedia_before_save_album_attributes", "change_album_attributes_before_save", 10, 2 );
function change_album_attributes_before_save( $attributes, $post_array ) {
	if ( isset( $post_array[ 'privacy' ] ) && $post_array[ 'privacy' ] != "" ){
		$attributes[ 'privacy' ] = $post_array[ 'privacy' ];
	}

	return $attributes;
}

add_filter( "rtmedia-model-join-query", "join_query_album_privacy", 10, 2 );
function join_query_album_privacy( $join, $table_name ) {
	if ( is_rt_admin() ){
		return $join;
	}
	$join_table = $table_name . ' as ap';

	$join .= " LEFT JOIN {$join_table} ON ( ap.id = {$table_name}.album_id )";

	return $join;
}

add_filter( "rtmedia-model-where-query", "where_query_album_privacy", 10, 3 );
function where_query_album_privacy( $where, $table_name, $join ) {
	$join_table = $table_name . ' as ap';
	if ( ! strpos( $join, $join_table ) ){
		return $where;
	}
	if ( is_user_logged_in() ){
		$user = get_current_user_id();
	} else {
		$user = 0;
	}
	$friends_obj = new RTMediaFriends();
	$where .= " AND ( ap.privacy is NULL OR ap.privacy<=0 ";
	if ( $user ){
		$where .= " OR ( ap.privacy=20)";
		$where .= " OR ( ap.media_author={$user} AND ap.privacy>=40)";
		if ( class_exists( 'BuddyPress' ) ){
			if ( bp_is_active( 'friends' ) ){
				$friends = $friends_obj->get_friends_cache( $user );
				$where .= " OR ( ap.privacy=40 AND ap.media_author IN ('" . implode( "','", $friends ) . "') )";
			}
		}
	}
	$where .= " ) ";

	return $where;
}

add_filter( 'bp_activity_get_user_join_filter', 'rtmedia_pro_album_activity_privacy', 20, 6 );
function rtmedia_pro_album_activity_privacy( $sql, $select_sql, $from_sql, $where_sql, $sort, $pag_sql = '' ) {
	if ( is_rt_admin() ){
		return $sql;
	}

	$sql   = '';
	$where = '';
	global $bp, $wpdb;
	$rtmedia_model = new RTMediaModel();
	if ( is_user_logged_in() ){
		$user = get_current_user_id();
	} else {
		$user = 0;
	}

	// privacy
	$where .= " ( ";
	// media privacy
	$where .= "( ( m.max_privacy is NULL OR m.max_privacy <= 0)";

	if ( $user ){
		$where .= "OR ( ";
		$where .= " ( m.max_privacy=20 ) ";
		$where .= " OR (a.user_id={$user} AND m.max_privacy >= 40 ) ";
		if ( class_exists( 'BuddyPress' ) ){
			if ( bp_is_active( 'friends' ) ){
				$friendship = new RTMediaFriends();
				$friends    = $friendship->get_friends_cache( $user );
				if ( isset( $friends ) && ! empty ( $friends ) != "" ){
					$where .= " OR (m.max_privacy=40 AND a.user_id IN ('" . implode( "','", $friends ) . "') ) ";
				}
			}
		}
		$where .= " ) ";
	}
	$where .= " ) ";
	// E.O. media privacy

	//check for album privacy
	$where .= " AND ( (ap.privacy is NULL OR ap.privacy <= 0)";
	$where .= "OR ( ( ap.privacy=20 ) ";
	$where .= " OR ( a.user_id={$user} AND ap.privacy >= 40 ) ";
	if ( class_exists( 'BuddyPress' ) ){
		if ( bp_is_active( 'friends' ) ){
			$friendship = new RTMediaFriends();
			$friends    = $friendship->get_friends_cache( $user );
			if ( isset( $friends ) && ! empty ( $friends ) != "" ){
				$where .= " OR ( ap.privacy=40 AND a.user_id IN ( '" . implode( "','", $friends ) . "') )";
			}
		}
	}
	$where .= ") )";
	// E.O. check for album privacy

	$where .= " ) ";
	// E.O. privacy
	if ( function_exists( "bp_core_get_table_prefix" ) ){
		$bp_prefix = bp_core_get_table_prefix();
	} else {
		$bp_prefix = "";
	}

	if ( strpos( $select_sql, "SELECT DISTINCT" ) === false ){
		$select_sql = str_replace( "SELECT", "SELECT DISTINCT", $select_sql );
	}

	$media_table = "SELECT *, max( privacy ) as max_privacy from {$rtmedia_model->table_name} group by activity_id";

	$from_sql  = " FROM {$bp->activity->table_name} a LEFT JOIN {$wpdb->users} u ON a.user_id = u.ID LEFT JOIN ( $media_table ) m ON ( a.id = m.activity_id and m.blog_id = '" . get_current_blog_id() . "' ) LEFT JOIN {$rtmedia_model->table_name} ap ON ( ap.id = m.album_id and ap.blog_id = '" . get_current_blog_id() . "' ) ";
	$where_sql = $where_sql . " AND (NOT EXISTS (SELECT m.activity_id FROM {$bp_prefix}bp_activity_meta m WHERE m.meta_key='rtmedia_privacy' AND m.activity_id=a.id) OR ( {$where} ) )";
	$newsql    = "{$select_sql} {$from_sql} {$where_sql} ORDER BY a.date_recorded {$sort} {$pag_sql}";

	return $newsql;
}

// remove premium upgrade tab from admin
remove_filter( "media_add_tabs", "rtmedia_admin_premium_tab", 99, 1 );

// "View Original" button besides the download button
add_action( 'rtmedia_action_buttons_after_media', 'rtmedia_view_original_button', 11 );
function rtmedia_view_original_button( $media_id = false ) {
	if ( ! $media_id ){
		$media_id = rtmedia_id();
	}

	$mediamodel = new RTMediaModel();
	$media      = $mediamodel->get( array( 'id' => $media_id ) );

	global $rtmedia;
	$options = $rtmedia->options;
	//if download is allowed then only show this button and media_type is not playlist
	if ( is_user_logged_in() && isset( $options[ 'general_enableDownloads' ] ) && $options[ 'general_enableDownloads' ] == "1" && ( isset( $media[ 0 ]->media_type ) && $media[ 0 ]->media_type != 'playlist' ) ){
		$url    = wp_get_attachment_url( rtmedia_media_id( $media_id ) );
		$button = "";
		if ( $url ){
			$button = "<form action='" . $url . "' method='link'>";
			$button .= '<button type="submit" class="rtmedia-action-buttons button rtmedia-vew-original"><i class="rtmicon-eye"></i>' . __( 'View Original', 'rtmedia' ) . '</button>';
			$button .= "</form>";
			echo $button;
		}
	}

}

/**
 * Fetches details of users who liked media
 *
 * @param type $media_id
 *
 * @return Users list Object
 */

global $rtmedia;
// if likes are enabled from backend, then only load content of "who liked"
if ( isset( $rtmedia->options ) && isset( $rtmedia->options[ 'general_enableLikes' ] ) && $rtmedia->options[ 'general_enableLikes' ] == 1 ){
	add_action( 'wp_footer', 'rtmpro_like_wrapper_div' );
	add_action( 'wp_ajax_rtm_media_likes', 'rtm_media_likes_callback' );
	add_action( 'wp_ajax_nopriv_rtm_media_likes', 'rtm_media_likes_callback' );
	add_filter( 'rtmedia_action_buttons_after_delete', 'rtm_media_like_stats_button', 10, 1 );
}


function rtm_pro_fetch_media_like_stats( $media_id ) {
	if ( empty( $media_id ) ){
		return false;
	}
	$rtmediainteractionmodel = new RTMediaInteractionModel();
	$media_like_cols         = array(
		'media_id' => $media_id, 'action' => 'like', 'value' => 1
	);
	$media_likes             = $rtmediainteractionmodel->get( $media_like_cols, false, false, 'action_date' );
	if ( count( $media_likes ) == 0 ){
		return false;
	}

	return $media_likes;
}

function rtm_media_likes_callback() {
	global $wpdb;
	if ( ! empty( $rtmedia_media ) ){
		$media_id = $rtmedia_media->id;
	} else {
		$media_id = ! empty( $_POST[ 'media_id' ] ) ? $_POST[ 'media_id' ] : '';
	}

	if ( empty( $media_id ) ){
		return;
	}
	$like_list    = '';
	$user_details = rtm_pro_fetch_media_like_stats( $media_id );
	if ( ! $user_details ){
		return '<li>' . _e( 'no likes', 'rtmedia' ) . '</li>';
		die();
	}
	$mysql_time = $wpdb->get_var( 'select CURRENT_TIMESTAMP()' );
	foreach ( $user_details as $detail ) {
		global $wpdb;
		$user_data = get_userdata( $detail->user_id );

		$user_name    = ! empty( $user_data ) ? $user_data->data->display_name : '';
		$user_profile = '';
		if ( class_exists( 'BuddyPress' ) ){
			$user_profile = bp_core_get_user_domain( $detail->user_id );
		} else {
			$user_profile = site_url() . '/author/' . $user_data->data->user_login;
		}
		$user_avatar = rtmedia_author_profile_pic( '', false, $detail->user_id );
		$like_time   = human_time_diff( strtotime( $detail->action_date ), strtotime( $mysql_time ) );
		$like_list .= '<li class="like-user">
           <div class="like-user-avatar"><a href="' . $user_profile . '">' . $user_avatar . '</a></div>
           <div class="like-desc">
               <a href="' . $user_profile . '">' . $user_name . '</a> ' . __( 'liked this ', 'rtmedia' ) . '<span class="user-like-time">' . $like_time . __( ' ago', 'rtmedia' ) . '</span>
           </div>

                </li>';
	}
	echo $like_list;
	die( 1 );
}

//Append like info wrapper after div
function rtmpro_like_wrapper_div() {
	if ( is_rtmedia_single() || is_rtmedia_gallery() ){
		?>
		<div class="media-likes-wrapper">
		<div class="media-likes">
			<h3><?php _e( 'People Who Like This', 'rtmedia' ); ?><span class="close"
																	   title="<?php _e( 'Close', 'rtmedia' ); ?>">x</span>
			</h3>
			<img class="loading-gif" src="<?php echo admin_url( "/images/loading.gif" ); ?>"
				 alt="<?php _e( 'Loading...', 'rtmedia' ); ?>"/>
		</div>
		</div><?php
	}
}


function rtm_media_like_stats_button( $actions ) {
	global $rtmedia_media;
	if ( isset( $rtmedia_media->id ) ){
		$actions[ ] = '<input class="current-media-item" type="hidden" value="' . $rtmedia_media->id . '" />';
	}

	return $actions;
}

function rtmedia_valid_file_type_size( $valid, $file ) {
	if ( $valid ){
		global $rtmedia;
		$options         = $rtmedia->options;
		$allowed_types   = $rtmedia->allowed_types;
		$file_extn_array = explode( '.', $file[ 'name' ] );
		$file_extn       = end( $file_extn_array );
		if ( $file_extn && $file_extn != "" ){
			$media_type = "";
			foreach ( $allowed_types as $type ) {
				if ( isset( $type[ 'extn' ] ) && $type[ 'extn' ] != "" && in_array( $file_extn, $type[ 'extn' ] ) ){
					$media_type = $type[ 'name' ];
					break;
				}
			}
			if ( isset( $options[ 'allowedTypes_' . $media_type . '_upload_limit' ] ) && $options[ 'allowedTypes_' . $media_type . '_upload_limit' ] > 0 ){
				$file_size = $file[ 'size' ] / ( 1024 * 1024 );
				if ( $file_size > $options[ 'allowedTypes_' . $media_type . '_upload_limit' ] ){
					$valid = false;
				}
			}
		}
	}

	return $valid;
}

add_filter( 'rtmedia_valid_type_check', 'rtmedia_valid_file_type_size', 99, 2 );

function rtmedia_flush_rewrite_rule( $flag ) {
	return true;
}

add_filter( 'rtmedia_flush_rewrite_rule', 'rtmedia_flush_rewrite_rule', 10, 1 );


// add edit/delete buttons in media gallery besides thumbnails // commented currently.. please remove comment afterwords to enable
add_action( 'rtmedia_before_item', 'add_action_buttons_before_media_thumbnail', 11 );
function add_action_buttons_before_media_thumbnail() {
	// add edit and delete links on single media
	global $rtmedia_media, $rtmedia_backbone;
	?>
	<?php
		if ( is_user_logged_in() ){
			if ( $rtmedia_backbone[ 'backbone' ] ){
				echo "<%= media_actions %>";
			} else {
				if( isset( $rtmedia_media ) && isset( $rtmedia_media->media_author ) && $rtmedia_media->media_author == get_current_user_id() ){
					?>
					<div class='rtmedia-gallery-item-actions'>
						<a href="<?php rtmedia_permalink(); ?>edit" class='no-popup' target='_blank' title='<?php _e( 'Edit this media', 'rtmedia-pro' ); ?>'>
							<i class='rtmicon-edit'></i><?php _e( 'Edit', 'rtmedia-pro' ); ?>
						</a>
						<a href="#" class="no-popup rtmp-delete-media" title='<?php _e( 'Delete this media', 'rtmedia-pro' ); ?>'>
							<i class='rtmicon-trash-o'></i><?php _e( 'Delete', 'rtmedia-pro' ); ?>
						</a>
					</div>
				<?php
				}
			}
	 	}
	?>
<?php
}

function rtmedia_media_actions_backbone( $media_array ){
	if( $media_array->media_author == get_current_user_id() ){
		$media_array->media_actions = "<div class='rtmedia-gallery-item-actions'><a href='" . $media_array->rt_permalink . "edit' class='no-popup' target='_blank' title='" . __( 'Edit this media', 'rtmedia-pro' ) ."'><i class='rtmicon-edit'></i>" . __( 'Edit', 'rtmedia-pro' ) ."</a><a href='#' class='no-popup rtmp-delete-media' title='" . __( 'Delete this media', 'rtmedia-pro' ) . "' ><i class='rtmicon-trash-o'></i>" . __( 'Delete', 'rtmedia-pro' ) ."</a></div>";
	} else {
		$media_array->media_actions = "";
	}
	return $media_array;
}

// In load more of media all the data render through backbone template and so we need to avail it in backbone variable
add_filter( 'rtmedia_media_array_backbone', 'rtmedia_media_actions_backbone', 10, 1 );

// add a custom class to media gallery UL if the user on his profile which will be used to show the action buttons on the media gallery item
add_filter( 'rtmedia_gallery_class_filter', "add_class_to_rtmedia_gallery", 11, 1 );
function add_class_to_rtmedia_gallery( $classes ) {
	global $rtmedia_query;
	$user_id = get_current_user_id();
	if ( isset( $rtmedia_query->query[ 'context' ] ) && $rtmedia_query->query[ 'context' ] == 'profile' && isset( $rtmedia_query->query[ 'context_id' ] ) && $rtmedia_query->query[ 'context_id' ] == $user_id ){
		$classes .= " rtm-pro-allow-action";
	}
	if ( isset( $rtmedia_query->query[ 'context' ] ) && $rtmedia_query->query[ 'context' ] == 'group' ){
		$group_id = $rtmedia_query->query[ 'context_id' ];
		if ( groups_is_user_mod( $user_id, $group_id ) || groups_is_user_admin( $user_id, $group_id ) ){
			$classes .= " rtm-pro-allow-action";
		}
	}

	return $classes;

}

//delete media from media gallery when user is
add_action( 'wp_ajax_rtmedia_delete_user_media', 'rtmedia_delete_user_media' );
function rtmedia_delete_user_media() {
	//check for valid post data
	if ( isset( $_POST ) && isset( $_POST[ 'action' ] ) && $_POST[ 'action' ] == 'rtmedia_delete_user_media' && isset( $_POST[ 'media_id' ] ) && $_POST[ 'media_id' ] != "" && is_user_logged_in() ){

		$media = new RTMediaMedia();
		$model = new RTMediaModel();

		$media_id = $_POST[ 'media_id' ];
		//check if the media to be delete exists and the current user is the media_author
		$curr_media = $model->get( array( 'id' => $media_id, 'media_author' => get_current_user_id() ) );

		if ( $curr_media ){
			//delete the media if media is found
			$delete = $media->delete( $media_id );
			echo '1';
			die();

		}
	}
	//return 0 if valid data is not recieved
	echo "0";
	die();

}

//show create album and upload buttons to non-logged in users and prompt to login/register when clicked.
add_filter( 'rtmedia_gallery_actions', 'rtmedia_add_album_button_for_non_logged', 15 );
function rtmedia_add_album_button_for_non_logged( $options ) {
	if ( ! is_user_logged_in() ){
		global $rtmedia;
		if ( isset( $rtmedia->options[ 'general_enableAlbums' ] ) && $rtmedia->options[ 'general_enableAlbums' ] == 1 && isset( $rtmedia->options[ 'general_enableCreateAlbums' ] ) && $rtmedia->options[ 'general_enableCreateAlbums' ] == 1 ){
			$options[ ] = '<span><a href="#rtmedia-login-register-modal" class="rtmedia-modal-link" title="' . __( 'Add new Album', 'rtmedia' ) . '" id="rtmedia-login-register-modal"><i class="rtmicon-plus-circle"></i>' . __( 'Add Album' ) . '</span> ';
		}
	}

	return $options;
}

add_action( 'rtmedia_album_gallery_actions', 'rtmedia_add_upload_album_button', 99 );
add_action( 'rtmedia_media_gallery_actions', 'rtmedia_add_upload_album_button', 99 );
function rtmedia_add_upload_album_button() {
	if ( ! is_user_logged_in() ){
		echo '<span><a href="#rtmedia-login-register-modal" class="primary rtmedia-upload-media-link rtmedia-modal-link" id="rtmedia-login-register-modal" title="' . __( 'Upload Media', 'rtmedia' ) . '"><i class="rtmicon-upload"></i>' . __( 'Upload' ) . '</a></span>';
	}
}

add_action( 'rtmedia_before_media_gallery', 'rtmedia_login_register_modal' );
add_action( 'rtmedia_before_album_gallery', 'rtmedia_login_register_modal' );
function rtmedia_login_register_modal() {

	if ( ! is_user_logged_in() ){
		?>
		<div class="rtmedia-popup mfp-hide rtm-modal" id="rtmedia-login-register-modal">
			<div id="rtm-modal-container">
				<h2 class="rtm-modal-title"><?php _e( 'Please login', 'rtmedia' ); ?></h2>

				<p><?php _e( "You need to be logged in to upload Media or to create Album.", 'rtmedia' ); ?></p>

				<p>
					<?php echo __( 'Click ' ) . '<a href="' . wp_login_url() . '" title="' . __( 'Login', 'rtmedia' ) . '">' . __( 'HERE', 'rtmedia' ) . '</a>' . __( ' to login.', 'rtmedia' ); ?>
				</p>
			</div>
		</div>
	<?php
	}
}

// custom thumbnail input in media single edit page
function rtmedia_media_custom_thumbnail_content( $media_type ) {
	$allow_custom_thumb_media = array( 'photo' );
	$obj_encoding             = new RTMediaEncoding( true );
	if ( $obj_encoding->api_key ){
		$allow_custom_thumb_media[ ] = ( 'video' );
	}
	if ( ! in_array( $media_type, $allow_custom_thumb_media ) ){
		?>
		<div class="content" id="custom_thumb">
			<div class="rtmedia-custom-thumbnail-wrapper">
				<p><label class="rtmedia-custom-thumbnail-label"><?php _e( 'Current Thumbnail', 'rtmedia' ) ?>:</label>
				</p>

				<div>
					<img class="rtmedia-custom-thumbnail-image" src='<?php rtmedia_image(); ?>'/>
				</div>
				<div>
					<input type="file" name="rtmedia_media_custom_thumbnail" id="rtmedia_media_custom_thumbnail"
						   class="rtmedia_media_custom_thumbnail"/>
				</div>
			</div>
		</div>
	<?php
	}
}


add_action( 'rtmedia_add_edit_tab_content', 'rtmedia_media_custom_thumbnail_content', 30, 1 );

function rtmedia_media_custom_thumbnail_title( $media_type ) {
	$allow_custom_thumb_media = array( 'photo', 'album' );
	$obj_encoding             = new RTMediaEncoding( true );
	if ( $obj_encoding->api_key ){
		$allow_custom_thumb_media[ ] = ( 'video' );
	}
	if ( ! in_array( $media_type, $allow_custom_thumb_media ) ){
		?>
		<dd><a href="#custom_thumb"><i class="rtmicon-picture-o"></i><?php _e( 'Media Thumbnail', 'rtmedia' ); ?></a>
		</dd>
	<?php
	}
}

add_action( 'rtmedia_add_edit_tab_title', 'rtmedia_media_custom_thumbnail_title', 30, 1 );

// filter media update state in case of file is not allowed or of big size for custom thumbnail
function rtmedia_single_edit_state_custom_thumbnail( $state ) {
	return false;
}

// message when custom thubmnail file is not allowed
function rtmedia_update_media_message_custom_thumbnail( $message ) {
	global $rtmedia;
	$options             = $rtmedia->options;
	$photo_allowed_types = $rtmedia->allowed_types[ 'photo' ][ 'extn' ];
	$image_extn          = pathinfo( $_FILES[ 'rtmedia_media_custom_thumbnail' ][ 'name' ], PATHINFO_EXTENSION );
	if ( ! in_array( $image_extn, $photo_allowed_types ) ){
		$message = __( 'This file type is not allowed', 'rtmedia' );
	} else {
		$message = __( 'Max file size is: ', 'rtmedia' ) . $options[ 'allowedTypes_photo_upload_limit' ] . " MB";
	}

	return $message;
}


// save custom thumbnail for media
function rtmedia_media_save_custom_thumbnail( $id ) {
	global $rtmedia;
	$options             = $rtmedia->options;
	$photo_allowed_types = $rtmedia->allowed_types[ 'photo' ][ 'extn' ];
	$media_type          = rtmedia_type( $id );

	// check for file exist in post object or not
	$allow_custom_thumb_media = array( 'photo', 'album' );
	$obj_encoding             = new RTMediaEncoding( true );
	if ( $obj_encoding->api_key ){
		$allow_custom_thumb_media[ ] = ( 'video' );
	}
	if ( isset( $_FILES[ 'rtmedia_media_custom_thumbnail' ] ) && isset( $_FILES[ 'rtmedia_media_custom_thumbnail' ][ 'name' ] ) && strlen( $_FILES[ 'rtmedia_media_custom_thumbnail' ][ 'name' ] ) > 0 && ! in_array( $media_type, $allow_custom_thumb_media ) ){

		$image_extn = pathinfo( $_FILES[ 'rtmedia_media_custom_thumbnail' ][ 'name' ], PATHINFO_EXTENSION );

		// check for allowed types and max file size
		if ( ! isset( $options[ 'allowedTypes_photo_upload_limit' ] ) || ( isset( $options[ 'allowedTypes_photo_upload_limit' ] ) && ( $options[ 'allowedTypes_photo_upload_limit' ] <= 0 || ( ( $_FILES[ 'rtmedia_media_custom_thumbnail' ][ 'size' ] / ( 1024 * 1024 ) ) <= $options[ 'allowedTypes_photo_upload_limit' ] ) ) && in_array( $image_extn, $photo_allowed_types ) ) ){
			include_once ABSPATH . 'wp-admin/includes/media.php';
			include_once ABSPATH . 'wp-admin/includes/file.php';
			include_once ABSPATH . 'wp-admin/includes/image.php';
			$uploadedfile     = $_FILES[ 'rtmedia_media_custom_thumbnail' ];
			$upload_overrides = array( 'test_form' => false );
			$thumb_file       = wp_handle_upload( $uploadedfile, $upload_overrides );
			$file_name        = $thumb_file[ 'url' ];
			$thumb_file_size  = image_make_intermediate_size( $thumb_file[ 'file' ], intval( $options[ "defaultSizes_photo_thumbnail_width" ] ), intval( $options[ "defaultSizes_photo_thumbnail_height" ] ), true );
			if ( $thumb_file_size ){
				$file_name = explode( "/", $file_name );
				unset( $file_name[ sizeof( $file_name ) - 1 ] );
				$file_name[ ] = $thumb_file_size[ 'file' ];
				$file_name    = implode( "/", $file_name );
			}
			$rtmedia_model = new RTMediaModel();
			$rtmedia_model->update( array( 'cover_art' => $file_name ), array( 'id' => $id ) );
		} else {
			add_filter( 'rtmedia_single_edit_state', 'rtmedia_single_edit_state_custom_thumbnail', 10, 1 );
			add_filter( 'rtmedia_update_media_message', 'rtmedia_update_media_message_custom_thumbnail', 10, 1 );
		}
	}
}

// add action after update media
add_action( 'rtmedia_after_update_media', 'rtmedia_media_save_custom_thumbnail', 30, 1 );


// Check if Media is disabled for the current group or not for PER GROUP MEDIA feature. Media tab wont appear if media is disabled for current group
add_filter( 'rtmedia_media_enabled_for_current_group', 'rtmedia_media_enabled_for_current_group', 10, 1 );
function rtmedia_media_enabled_for_current_group( $media_enabled ) {
	global $bp;
	if ( function_exists( 'bp_is_group' ) && bp_is_group() ){
		//is media disabled for this group , then return false
		$is_media_enabled = groups_get_groupmeta( bp_get_current_group_id(), 'rtmedia_group_media_enabled' );
		if ( isset( $is_media_enabled ) && $is_media_enabled == '0' ){
			global $wp_query;
			$wp_query->is_404 = true;
			$media_enabled    = false;
		}
	}

	return $media_enabled;
}

// Show the group meta for media enabled/disabled option in group creation step under the media settings
add_action( 'rtmedia_group_media_control_create', 'rtmedia_group_media_settings_option' );
function rtmedia_group_media_settings_option() {
	?>
	<h4><?php _e( 'Media Control', 'rtmedia' ); ?></h4>
	<div class="radio">
		<label>
			<input name="rt_group_media_enabled" type="checkbox" id="rt_group_media_enabled" checked="checked"
				   value="1">
			<strong><?php _e( 'Enable Media for this Group.', 'rtmedia' ); ?></strong>
		</label>
	</div>
<?php
}

//save the media control settings when group created
add_action( 'rtmedia_create_save_group_media_settings', 'rtmedia_save_group_media_control', 12, 1 );
add_action( 'rtmedia_edit_save_group_media_settings', 'rtmedia_save_group_media_control', 12, 1 );
function rtmedia_save_group_media_control( $settings ) {
	global $bp;
	$group_id = '';
	if ( ! function_exists( 'groups_update_groupmeta' ) ){
		return;
	}

	if ( isset( $bp->groups->new_group_id ) && $bp->groups->new_group_id != '' ){
		$group_id = $bp->groups->new_group_id;

	} else {
		$group_id = bp_get_current_group_id();
	}
	$media_enabled = '1';
	if ( ! isset( $settings[ 'rt_group_media_enabled' ] ) ){
		$media_enabled = '0';
	}
	$test = groups_update_groupmeta( $group_id, 'rtmedia_group_media_enabled', $media_enabled );
}

// Show the group meta for media enabled/disabled option in group creation step under the media settings
add_action( 'rtmedia_group_media_control_edit', 'rtmedia_group_media_settings_option_edit' );
function rtmedia_group_media_settings_option_edit() {
	if ( ! ( function_exists( 'bp_is_group' ) && bp_is_group() ) ){
		return;
	}

	$is_media_enabled = groups_get_groupmeta( bp_get_current_group_id(), 'rtmedia_group_media_enabled' );
	$checked          = 'checked="checked"';
	if ( $is_media_enabled == '0' ){
		$checked = '';
	}
	?>
	<h4><?php _e( 'Media Control', 'rtmedia' ); ?></h4>
	<p class="radio">
		<label>
			<input name="rt_group_media_enabled" type="checkbox" id="rt_group_media_enabled" <?php echo $checked; ?>
				   value="1">
			<strong><?php _e( 'Enable Media for this Group.', 'rtmedia' ); ?></strong>
		</label>
	</p>
<?php
}

//prevent uploads and album creation when media is disabled for the group
add_filter( 'rtm_can_user_upload_in_group', 'rtmedia_disable_album_and_upload_in_group', 12, 1 );
add_filter( 'can_user_create_album_in_group', 'rtmedia_disable_album_and_upload_in_group' );
function rtmedia_disable_album_and_upload_in_group( $flag ) {
	if ( ! ( function_exists( 'bp_is_group' ) && bp_is_group() ) ){
		return $flag;
	}

	$is_media_enabled = groups_get_groupmeta( bp_get_current_group_id(), 'rtmedia_group_media_enabled' );

	if ( $is_media_enabled == '0' ){
		$flag = false;
	}

	return $flag;
}

/*
 * Playlist view for "Music" tab in BuddyPress profile/group
 */
//change the selected 'madia-gallery" template with 'music-gallery' template if option enabled in rtMedia Settings
add_filter( 'rtmedia_template_filter', 'rtmedia_music_gallery_template', 12, 1 );
function rtmedia_music_gallery_template( $template ) {
	if ( rtmp_is_music_tab() && is_music_playlist_view_enabled() ){
		$template = 'music-gallery';
	}

	return $template;
}

function rtmp_is_music_tab() {
	global $rtmedia_interaction;
	if ( isset( $rtmedia_interaction->context->type ) && in_array( $rtmedia_interaction->context->type, array( 'profile', 'group' ) ) && isset( $rtmedia_interaction->routes[ 'media' ]->query_vars[ 0 ] ) && $rtmedia_interaction->routes[ 'media' ]->query_vars[ 0 ] == 'music' ){
		return true;
	}

	return false;
}

//change the selected 'madia-gallery" template with 'music-gallery' template if option enabled in rtMedia Settings
add_filter( 'rtmedia_template_filter', 'rtmedia_document_gallery_template', 12, 1 );
function rtmedia_document_gallery_template( $template ) {
	if ( rtmp_is_document_tab() && is_document_table_view_enabled() ){
		$template = 'document-list';
	}

	return $template;
}

// check if document tab or not
function rtmp_is_document_tab() {
	global $rtmedia_interaction;
	if ( isset( $rtmedia_interaction->context->type ) && in_array( $rtmedia_interaction->context->type, array( 'profile', 'group' ) ) && isset( $rtmedia_interaction->routes[ 'media' ]->query_vars[ 0 ] ) && $rtmedia_interaction->routes[ 'media' ]->query_vars[ 0 ] == 'document' ){
		return true;
	}

	return false;
}

//change the selected 'media-gallery" template with 'document-list' template if option enabled in rtMedia Settings
add_filter( 'rtmedia_template_filter', 'rtmedia_other_gallery_template', 12, 1 );
function rtmedia_other_gallery_template( $template ) {
	if ( rtmp_is_other_tab() && is_document_table_view_enabled() ){
		$template = 'document-list';
	}

	return $template;
}

// check if document tab or not
function rtmp_is_other_tab() {
	global $rtmedia_interaction;
	if ( isset( $rtmedia_interaction->context->type ) && in_array( $rtmedia_interaction->context->type, array( 'profile', 'group' ) ) && isset( $rtmedia_interaction->routes[ 'media' ]->query_vars[ 0 ] ) && $rtmedia_interaction->routes[ 'media' ]->query_vars[ 0 ] == 'other' ){
		return true;
	}

	return false;
}

//filter the location of the template to locate the music-gallery templates located under templates/media folder
add_filter( 'rtmedia_located_template', 'rtmedia_locate_music_gallery_template', 10, 4 );
function rtmedia_locate_music_gallery_template( $located, $url, $ogpath, $template_name ) {

	if ( isset( $template_name ) && $template_name == 'music-gallery.php' ){
		if ( $url ){
			$located = trailingslashit( RTMEDIA_PRO_URL ) . $ogpath . $template_name;
		} else {
			$located = trailingslashit( RTMEDIA_PRO_PATH ) . $ogpath . $template_name;
		}
	}

	return $located;
}

add_filter( 'rtmedia_located_template', 'rtmedia_locate_document_gallery_template', 10, 4 );
function rtmedia_locate_document_gallery_template( $located, $url, $ogpath, $template_name ) {

	if ( isset( $template_name ) && $template_name == 'document-list.php' ){
		if ( $url ){
			$located = trailingslashit( RTMEDIA_PRO_URL ) . $ogpath . $template_name;
		} else {
			$located = trailingslashit( RTMEDIA_PRO_PATH ) . $ogpath . $template_name;
		}
	}

	return $located;
}

//add filter to change the LIMIT in rtMedia Query to get all the available music media
add_filter( 'rtmedia-model-limit-query', 'rtmedia_remove_limit_for_music_gallery', 12, 3 );
function rtmedia_remove_limit_for_music_gallery( $limit, $offser, $per_page ) {
	if ( rtmp_is_music_tab() && is_music_playlist_view_enabled() ){
		$limit = '';
	} else {
		if ( rtmp_is_document_tab() && is_document_table_view_enabled() ){
			$limit = '';
		}
	}

	return $limit;
}

add_filter( 'rtmedia_action_query_in_populate_media', 'rtmedia_set_per_page_media_document_list', 99, 2 );

function rtmedia_set_per_page_media_document_list( $action_query, $total_count ) {
	if ( rtmp_is_document_tab() && is_document_table_view_enabled() ){
		$action_query->per_page_media = $total_count;
	}
	return $action_query;
}

// remove filter of limit applied above
add_action( 'bp_before_member_header', 'remove_query_limit_filter' );
function remove_query_limit_filter() {
	if ( rtmp_is_music_tab() && is_music_playlist_view_enabled() ){
		remove_filter( 'rtmedia-model-limit-query', 'rtmedia_remove_limit_for_music_gallery', 12, 3 );
	}
	if ( rtmp_is_document_tab() && is_document_table_view_enabled() ){
		remove_filter( 'rtmedia-model-limit-query', 'rtmedia_remove_limit_for_music_gallery', 12, 3 );
	}
}

/*
 * Checks if playlist view is enabled for media in Music tab
 */
function is_music_playlist_view_enabled() {
	global $rtmedia;
	if ( isset ( $rtmedia->options[ "general_enable_music_playlist_view" ] ) && $rtmedia->options[ "general_enable_music_playlist_view" ] != "0" ){
		return true;
	}

	return false;
}

/*
 * Checks if playlist view is enabled for media in Music tab
 */
function is_document_table_view_enabled() {
	global $rtmedia;
	if ( isset ( $rtmedia->options[ "general_enable_document_other_table_view" ] ) && $rtmedia->options[ "general_enable_document_other_table_view" ] != "0" ){
		return true;
	}

	return false;
}

//add_action ( 'rtmedia_before_item', 'rtmedia_bulk_edit_item_select' );
function rtmedia_bulk_edit_item_select() {
	global $rtmedia_query, $rtmedia_backbone;
	if ( $rtmedia_backbone[ 'backbone' ] ){
		echo '<input type="checkbox" name="selected[]" class="rtmedia-item-selector bulk-action" value="<%= id %>"/>';
	} else {
		if ( isset( $rtmedia_query->query[ 'context' ] ) && $rtmedia_query->query[ 'context' ] == 'profile' ){
			echo '<input type="checkbox" class="rtmedia-item-selector bulk-action" name="selected[]" value="' . rtmedia_id() . '"/>';
		}
	}
}

// add bulk edit option under the option dropdown in gallery
add_filter( 'rtmedia_gallery_actions', 'rtmedia_bulk_edit_option', 14, 1 );
function rtmedia_bulk_edit_option( $options ) {

	global $rtmedia_query;
	if ( rtmp_is_music_tab() && is_music_playlist_view_enabled() ){
		return $options;
	}
	if ( isset ( $rtmedia_query->media_query[ 'media_author' ] ) && get_current_user_id() == $rtmedia_query->media_query[ 'media_author' ] && is_rtmedia_gallery() ){
		$options[ ] = '<a href="#" class="rtmedia-bulk-edit" title="' . __( 'Bulk edit media', 'rtmedia' ) . '"><i class="rtmicon-edit"></i>' . __( 'Bulk Edit' ) . '</a>';
	}

	return $options;

}


add_action( 'rtmedia_after_media_gallery', 'rtmedia_after_media_gallery_template' );
function rtmedia_after_media_gallery_template() {
	global $rtmedia_query;
	if ( isset( $rtmedia_query->is_gallery_shortcode ) && $rtmedia_query->is_gallery_shortcode ){
		return;
	}
	if ( isset( $rtmedia_query->media_query[ 'media_author' ] ) && get_current_user_id() == $rtmedia_query->media_query[ 'media_author' ] && is_rtmedia_gallery() ){
		echo "</form>";
	}
}

add_action( 'rtmedia_after_media_gallery_title', 'rtmedia_bulk_edit_container' );
function rtmedia_bulk_edit_container() {
	global $rtmedia_query;
	if ( isset( $rtmedia_query->is_gallery_shortcode ) && $rtmedia_query->is_gallery_shortcode ){
		return;
	}
	if ( isset ( $rtmedia_query->media_query[ 'media_author' ] ) && get_current_user_id() == $rtmedia_query->media_query[ 'media_author' ] && is_rtmedia_gallery() ){
		?>
		<form class='bulk-edit-form' id='bulk-edit-form' action='' method='POST'>
		<div class="rtmedia-bulk-edit-options">

			<button type='button' class="select-all" title="<?php _e( 'Select All Visible', 'rtmedia' ); ?>"><i
					class='rtmicon-square-o'></i></button>
			<input type="hidden" name="bulk-action" value=""/>
			<button type="button" class="rtmedia-bulk-move"
					title="<?php _e( 'Move selected media to album' ); ?>"><?php _e( 'Move' ); ?></button>
			<button type="button" class="rtmedia-bulk-delete-selected rtm-alert-btn"
					title="<?php _e( 'Delete the selected medias' ); ?>"><?php _e( 'Delete' ); ?></button>
			<?php if ( is_rtmedia_privacy_enable() && is_rtmedia_privacy_user_overide() ){ ?>
				<button type="button" class="rtmedia-change-privacy"
						title="<?php _e( 'Change the privacy of the selected Media' ); ?>">
					<?php _e( 'Change Privacy' ); ?>
				</button>
			<?php } ?>

			<?php wp_nonce_field( 'rtmedia_bulk_delete_nonce', 'rtmedia_bulk_delete_nonce' ); ?>
			<?php RTMediaMedia::media_nonce_generator( $rtmedia_query->media_query[ 'media_author' ] ); ?>

			<?php do_action( 'rtmedia_add_bulk_edit_buttons' ) ?>

			<button type="button" class="bulk-edit-cancel"><?php _e( 'Cancel Bulk Editing', 'rtmedia' ); ?></button>
		</div>
		<!--// Album selection container for moving media-->
		<div class="rtmedia-bulk-move-container rtm-dashed-border">
			<?php _e( 'Move selected media to the album : ', 'rtmedia' ); ?>
			<?php
			echo '<select name="album" class="rtmedia-user-album-list">';
			//$global_albums = rtmedia_global_album_list ();
			// $options = "<optgroup label='".__("Global Albums","rtmedia")." ' value = 'global'>$global_albums</optgroup>";
			$options = '';
			$profile_albums = rtmedia_user_album_list();
			if ( $profile_albums ){
				$options .= $profile_albums;
			}
			echo $options;
			echo '</select>';
			?>
			<input type="button" class="rtmedia-bulk-move-selected" name="move-selected"
				   value="<?php _e( 'Move', 'rtmedia' ); ?>"/>
		</div>

		<?php if ( is_rtmedia_privacy_enable() && is_rtmedia_privacy_user_overide() ){ ?>
			<div class="rtmedia-bulk-privacy-container rtm-dashed-border">
				<?php _e( 'Change privacy of the selected media to : ', 'rtmedia' ); ?>
				<?php
				$privacy = new RTMediaPrivacy();
				$privacy = $privacy->select_privacy_ui();
				?>

				<input type="button" class="rtmedia-change-privacy-selected" name="change-privacy-selected"
					   value="<?php _e( 'Save', 'rtmedia' ); ?>"/>
			</div>
		<?php } ?>

		<?php do_action( 'rtmedia_add_bulk_edit_content' ) ?>

		<p class="rtmedia-bulk-action-message"></p>

		<?php // <form> tag closes after the media gallery

	}
}

/*
 * Change the privacy of the selected medias when using bulk edit option
 * $selected_ids is array of ids
 * $new_privacy is the value for the new privacy of the media
 */
function rtmedia_bulk_edit_change_privacy( $selected_ids, $new_privacy ) {

	if ( ! ( isset( $selected_ids ) && is_array( $selected_ids ) && isset( $new_privacy ) && $new_privacy != "" ) ){
		return false;
	}

	$model = new RTMediaModel();
	$media = new RTMediaMedia();

	foreach ( $selected_ids as $media_id ) {
		$media_details = $model->get_media( array( 'id' => $media_id, 'media_author' => get_current_user_id() ), false, false );

		if ( $media_details ){
			$media->update( $media_details[ 0 ]->id, array( 'privacy' => $new_privacy ), $media_details[ 0 ]->media_id );
		}
	}

	return true;

}

/*
 * Change the album of the selected medias when using bulk edit option
 * $selected_ids is array of ids
 * $new_album is the new album id
 */
function rtmedia_bulk_edit_change_album( $selected_ids, $new_album ) {

	if ( ! ( isset( $selected_ids ) && is_array( $selected_ids ) && isset( $new_album ) && $new_album != "" ) ){
		return false;
	}

	$model = new RTMediaModel();
	$media = new RTMediaMedia();

	$album_move_details = $model->get_media( array( 'id' => $new_album, 'media_type' => 'album' ), false, false );

	if ( $album_move_details ){ // if album exists

		foreach ( $selected_ids as $media_id ) {
			$media_details = $model->get_media( array( 'id' => $media_id ), false, false );
			// if media exists and media is not album/playlist, then proceed with changing the album of the media
			if ( $media_details && isset( $media_details[ 0 ]->media_type ) && ! in_array( $media_details[ 0 ]->media_type, array( 'playlist', 'album' ) ) ){
				$post_array[ 'ID' ]          = $media_details[ 0 ]->media_id;
				$post_array[ 'post_parent' ] = $album_move_details[ 0 ]->media_id;
				wp_update_post( $post_array );
				$media->update( $media_details[ 0 ]->id, array( 'album_id' => $album_move_details[ 0 ]->id ), $media_details[ 0 ]->media_id );
			}
		}

		return true;

	} else { // if album with provided id is not found
		return false;
	}
}

/*
 * Handling the bukl edit requests
 */
add_action( 'wp_ajax_rtmedia_bulk_edit', 'rtmedia_bulk_edit_handler' );
function rtmedia_bulk_edit_handler() {

	$return = 0;
	if ( isset( $_POST ) && is_user_logged_in() && isset( $_POST[ 'medias' ] ) && ! empty ( $_POST[ 'medias' ] ) && is_array( $_POST[ 'medias' ] ) && isset( $_POST[ 'nonce' ] ) && wp_verify_nonce( $_POST[ 'nonce' ], 'rtmedia_' . get_current_user_id() ) && isset( $_POST[ 'media_action' ] ) ){

		$selected_ids = $_POST[ 'medias' ];
		$media_action = $_POST[ 'media_action' ];

		if ( $media_action == 'change_privacy' && isset( $_POST[ 'privacy' ] ) && $_POST[ 'privacy' ] != '' ){

			if ( rtmedia_bulk_edit_change_privacy( $selected_ids, $_POST[ 'privacy' ] ) ){
				$return = 1;
			}
		} else {
			if ( $media_action == 'change_album' && isset( $_POST[ 'album_id' ] ) && $_POST[ 'album_id' ] != '' ){

				if ( rtmedia_bulk_edit_change_album( $selected_ids, $_POST[ 'album_id' ] ) ){
					$return = 1;
				}
			} else {
				if ( $media_action == 'change_attributes' && isset( $_POST[ 'media_attributes' ] ) && $_POST[ 'media_attributes' ] != '' ){
					$rtmedia_attributes = new RTMediaProAttributes( false );
					if ( $rtmedia_attributes->rtmedia_bulk_edit_change_attributes( $selected_ids, $_POST[ 'media_attributes' ] ) ){
						$return = 1;
					}
				}
			}
		}

	}
	echo $return;
	die();
}

// change the document files list upload date format
function rtmedia_pro_document_other_files_list_date( $rtmedia_id = false ) {
	if ( ! $rtmedia_id ){
		global $rtmedia_media;
		$rtmedia_id = $rtmedia_media->id;
	}
	$media     = get_post( rtmedia_media_id( $rtmedia_id ) );
	$date_time = '';
	if ( isset( $media->post_date_gmt ) && $media->post_date_gmt != '' ){
		$date_time = date( "d-m-Y", strtotime( $media->post_date_gmt ) );
	}

	return apply_filters( 'rtmedia_pro_document_other_files_list_date_filter', $date_time );
}

function rtmedia_pro_sanitize_taxonomy_name( $taxonomy ) {
	$taxonomy = strtolower( stripslashes( strip_tags( $taxonomy ) ) );
	$taxonomy = preg_replace( '/&.+?;/', '', $taxonomy ); // Kill entities
	$taxonomy = str_replace( array( '.', '\'', '"' ), '', $taxonomy ); // Kill quotes and full stops.
	$taxonomy = str_replace( array( ' ', '_' ), '-', $taxonomy ); // Replace spaces and underscores.

	return $taxonomy;
}

function rtmedia_pro_attribute_taxonomy_name( $name ) {
	return 'rt_' . rtmedia_pro_sanitize_taxonomy_name( $name );
}

function rtmedia_pro_post_type_name( $name ) {
	return 'rt_' . rtmedia_pro_sanitize_taxonomy_name( $name );
}

add_action( 'rtmedia_bp_before_activity_posted', 'rtmedia_bp_activity_url_preview', 10, 4 );
function rtmedia_bp_activity_url_preview( $updated_content, $user_id, $activity_id ) {
	global $wpdb, $bp;
	if ( isset( $_POST[ 'rtmp_link_url' ] ) && $_POST[ 'rtmp_link_url' ] != '' ){

		//		$updated_content .= $wpdb->get_var( "select content from  {$bp->activity->table_name} where  id= $activity_id" );
		$updated_content .= '<div class="rtmp_final_link">';
		$updated_content .= '<div class="rtmp_link_preview_container">';
		$updated_content .= '<a href="' . $_POST[ 'rtmp_link_url' ] . '"><img src="' . $_POST[ 'rtmp_link_img' ] . '" /></a>';
		$updated_content .= '</div>';
		$updated_content .= '<div class="rtmp_link_contents">';
		$updated_content .= '<span class="rtmp_link_preview_title"><a href="' . $_POST[ 'rtmp_link_url' ] . '">' . $_POST[ 'rtmp_link_title' ] . '</a></span>';
		$updated_content .= '<span class="rtmp_link_preview_body">' . $_POST[ 'rtmp_link_description' ] . '</span>';
		$updated_content .= '</div>';
		$updated_content .= '</div>';
		$updated_content .= '<br/>';

		bp_activity_update_meta( $activity_id, "bp_activity_text", $updated_content );
		$wpdb->update( $bp->activity->table_name, array( "type" => "rtmedia_update", "content" => $updated_content ), array( "id" => $activity_id ) );
	}
}

add_action( 'wp_ajax_rtm_url_parser', 'rtm_url_parser_callback' );
add_action( 'wp_ajax_nopriv_rtm_url_parser', 'rtm_url_parser_callback' );

function rtm_url_parser_callback(){
    // booting

    require_once RTMEDIA_PRO_PATH . 'lib/php-metaparser/Curler.class.php';
    require_once RTMEDIA_PRO_PATH . 'lib/php-metaparser/MetaParser.class.php';

    // curling
    if( class_exists( 'Curler' ) && class_exists( 'MetaParser' ) ){
        $url = $_POST[ 'url' ];
        $curler = new Curler();
        $body = $curler->get($url);
        $parser = new MetaParser($body, $url);
        $result = $parser->getDetails();

        $json_data = array();

        if( ! empty( $result[ 'openGraph' ] ) && !empty( $result[ 'openGraph' ][ 'title' ] ) ){
            $json_data['title'] = $result[ 'openGraph' ][ 'title' ];
        }else{
            $json_data['title'] = $result['title'];
        }

        if( !empty( $result[ 'openGraph' ] ) && !empty( $result[ 'openGraph' ][ 'description' ] ) ){
            $json_data['description'] = $result[ 'openGraph' ][ 'description' ];
        }else{
            if( $result[ 'meta' ][ 'description' ] == false ){
                $json_data['description'] = '';
            }else{
                $json_data['description'] = $result[ 'meta' ][ 'description' ];
            }

        }

        $images = array();

        if( ! empty( $result['openGraph'] ) && !empty( $result['openGraph']['imagePath'] ) ){
            if( getimagesize( $result['openGraph']['imagePath'] ) ){
                array_push( $images, $result['openGraph']['imagePath'] );
            }
        }else {
            foreach( $result[ 'images' ] as $image ){
                if( getimagesize( $image ) ){
                    array_push( $images, $image );
                }
            }
        }
        $json_data['images'] = $images;

        echo json_encode($json_data);

    }
    die();
}

function is_rtmedia_favlist_enable() {
	global $rtmedia;
	if ( isset ( $rtmedia->options[ "general_enable_favlist" ] ) && $rtmedia->options[ "general_enable_favlist" ] != "0" ){
		return true;
	}

	return false;
}

function is_rtmedia_favlist_gallery() {
	global $rtmedia_query;
	if ( isset ( $rtmedia_query->action_query->media_type ) && $rtmedia_query->action_query->media_type == 'favlist' ){
		return true;
	}
	return false;
}

/**
 *
 * @return boolean
 */
function is_rtmedia_favlist() {
	global $rtmedia_query;
	if ( isset ( $rtmedia_query->query[ 'media_type' ] ) && $rtmedia_query->query[ 'media_type' ] == 'favlist' ){
		return true;
	}
	return false;
}


// add favlist option in favlist gallery
add_action( 'rtmedia_favlist_gallery_actions', 'rtm_create_favlist_option' );

function rtm_create_favlist_option() {
	if ( ! is_rtmedia_favlist_enable() ){
		return;
	}

	global $rtmedia_query;
	$user_id     = get_current_user_id();

	$provacyObj = new RTMediaPrivacy();
	$privacy_el = $provacyObj->select_privacy_ui( false );
	if ( $privacy_el ){
		$privacy_el = "<label> " . __( 'Privacy :', 'rtmedia' ) . " </label>" . $privacy_el;
	} else {
		$privacy_el = "";
	}
?>
	<div id="rtmedia-media-options">
		<a href="#new-favlist-modal" class="rtmedia-modal-link" title="<?php _e( "Add New FavList", "rtmedia" ); ?>">
			<i class="rtmicon-plus-circle"></i><?php _e( "Add FavList", "rtmedia" ); ?>
		</a>
	</div>
	<div id="new-favlist-modal" class="rtmedia-popup mfp-hide">
		<div id="rtm-modal-container">
			<h2 class="rtm-modal-title"><?php _e( "Create New FavList", "rtmedia" ); ?></h2>
			<p>
				<label for='rtmedia_favlist_name'><?php _e( 'Title :', 'rtmedia' ); ?></label>
				<input type="text" id="rtmedia_favlist_name" name="rtmedia_favlist_name" value="" class='rtm-input-medium'/>
			</p>

			<div>
				<?php echo $privacy_el ?>
				<button type="button" id="rtmedia_create_new_favlist"><?php _e( "Create FavList", "rtmedia" ); ?></button>
			</div>
		</div>
	</div>
<?php
}

function get_favlist_media_list_table() {
	$rtmedia_favlist = new RTMediaProFavList();
	$table = $rtmedia_favlist->edit_favlists_media_list( rtmedia_type() );
	if ( $table ){
		echo $table;
	} else {
		echo "<i class='rtmicon-attention'></i>" . __( ' Currently there isn\'t any media in this FavList.' );
	}
}