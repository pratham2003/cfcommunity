<?php
/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of RTMediaProPlaylist
 *
 * @author Pushpak Patel <pushpak.patel@rtcamp.com>
 */
class RTMediaProPlaylist {

    /**
     *
     * @var type
     *
     * Media object associated with the playlist. It works as an interface
     * for the actions specific the media from this playlist
     */
    var $media;

    /**
     *
     */
    public function __construct () {

        if(is_rtmedia_playlist_enable()) {
            $this->media = new RTMediaMedia();
            if(!defined ('RTMEDIA_PLAYLIST_SLUG')) {
                define ( 'RTMEDIA_PLAYLIST_SLUG', apply_filters('rtmedia_playlist_slug','playlist') );
            }
            add_action ( 'init', array( &$this, 'register_post_types' ), 12 );
            add_filter('rtmedia_allowed_types', array( $this, "add_allowed_types"), 10,1);
            add_filter('rtmedia_template_filter', array( $this, "rtmedia_pro_template_filter"), 10, 1);
            add_filter( 'rtmedia_located_template', array( $this,'rtmedia_locate_playlist_template'), 10, 4);
            add_filter('rtmedia_backbone_template_filter', array( $this, "rtmedia_pro_backbone_template_filter"), 10, 1);
            add_filter( 'rtmedia_single_content_filter', array($this, 'rtmedia_single_playlist_content'), 10, 2 );
            add_action('wp_ajax_rtmedia_create_playlist', array( $this , 'create_new_playlist' ), 10);
            add_action('wp_ajax_rtmedia_remove_media_from_playlist', array( $this , 'rtmedia_remove_media_from_playlist' ), 10);
            add_action( 'rtmedia_after_delete_media' , array( $this , 'delete_playlist_datas'), 10, 1);
//            add_filter( 'rtmedia_query_media_type_filter' , array( $this, 'rtmedia_add_playlist_media_type'), 10, 1); //do not show playlists in all media tab
        }
		// exclude playlist counts from counts in media nav
		add_filter( 'rtmedia_media_count_exclude_type', array( $this, 'rtmedia_media_count_exclude_type_playlist' ), 10, 1 );
    }

	function rtmedia_media_count_exclude_type_playlist( $types ){
		$types[] = 'playlist';
		return $types;
	}

    function rtmedia_add_playlist_media_type ( $media_type ) {
        if( isset( $media_type ) && $media_type != "" ) {
            $media_type['value'] = array_merge ( $media_type['value'] , array('playlist') );
        }
        return $media_type;
    }
    /*
     * Removes media from the playlist
     */
    function rtmedia_remove_media_from_playlist () {

        if(
            isset( $_POST['action'] ) && $_POST['action'] == "rtmedia_remove_media_from_playlist"
            && isset($_POST['media_id']) && $_POST['media_id'] != ""
            && isset($_POST['playlist_id']) && $_POST['playlist_id'] != ""
            ) {

            $playlist_id = $_POST['playlist_id'];
            $media_id = $_POST['media_id'];
            $playlist_meta = $this->remove_music_from_playlist_meta( $media_id , $playlist_id );
            $music_meta = $this->remove_playlist_from_music_meta( $media_id , $playlist_id );
            if( $music_meta && $playlist_meta) { echo "true"; die();}
        }
        echo "false"; die();
    }

    /*
     * Removes the media from the playlist (meta field)
     * @param expects the media_id of the media to be removed
     * @param expects the playlist_id of the playlist from which the media is to be removed
     */
    function remove_music_from_playlist_meta ( $media_id, $playlist_id ) {
        if( isset($media_id) && $media_id != "" && isset($playlist_id) && $playlist_id != "") {

            $media_list = get_rtmedia_meta( $playlist_id , 'media_ids');
            $media_key = array_search ( $media_id, $media_list );
            if( $media_list != "" && ( $media_key = array_search (  $media_id, $media_list ) ) !== false) {
                unset( $media_list[ $media_key ] );//remove media id from the playlist meta

                if( !empty ($media_list)) {
                    $update_meta = update_rtmedia_meta( $playlist_id , "media_ids", $media_list );
                } else{
                    $update_meta = delete_rtmedia_meta ( $playlist_id , "media_ids");
                }
                if( $update_meta ) { return true;}
            }
        }
        return false;
    }

    /*
     * disassociates the playlist from the music meta field.
     * @param expects the media_id of the media from which the playlist is to be disassociated
     * @param expects the playlist_id of the playlist from which the media is to be removed
     */
    function remove_playlist_from_music_meta ( $media_id, $playlist_id ) {
        if( isset($media_id) && $media_id != "" && isset($playlist_id) && $playlist_id != "") {
            $playlist_list = get_rtmedia_meta( $media_id , 'playlists');

            if( $playlist_list != "" && ( $playlist_key = array_search (  $playlist_id , $playlist_list ) ) !== false) {
                unset( $playlist_list[ $playlist_key ] );//remove playlist id from the music meta

                if( !empty( $playlist_list ))
                    $update_meta = update_rtmedia_meta( $media_id , "playlists", $playlist_list );
                else
                    $update_meta = delete_rtmedia_meta ( $media_id , "playlists" );

                if( $update_meta ) { return true;}
            }
        }
        return false;
    }

    //show list of media under the playlist edit template
    function edit_playlists_media_list ( $media_type ) {
        if( isset( $media_type ) && $media_type == "playlist") {
            global $rtmedia_media;

            $media_list = get_rtmedia_meta( $rtmedia_media->id , 'media_ids');

            if( $media_list != "") {
                $model = new RTMediaModel();
                $media_table = "<div class='rtmedia-playlist-media-table'>"
                        . "<table id='" . $rtmedia_media->id . "' class='rtmedia-playlist-media-list'>"
                        . "<thead><tr><th>" . __('Title') . "</th><th colspan='2'>" . __('Actions') . "</th></tr></thead>"
                        . "<tbody>";
                foreach( $media_list as $media_id) {
                   $media = $model->get( array ('id' => $media_id ) );
                   $list = '';
                   if($media) {
                       $link = get_rtmedia_permalink( $media[0]->id );
                       $list .= "<tr>"
                               	. "<td class='rtm-edit-media-list-title'><a href='". $link ."' target='_blank' title='" . __('View this media') . "' ><i class='rtmicon-music'></i> " . $media[0]->media_title . "</a></td>"
                               	. "<td class='rtm-edit-media-list-edit'><a href='". $link ."/edit' target='_blank' title='" . __('Edit this media') . "' ><i class='rtmicon-edit'></i>" . __('Edit') . "</a></td>"
					   			. "<td class='rtm-edit-media-list-delete'><span id='" . $media[0]->id . "' class='rtmedia-remove-media-from-playlist' title='" . __('Remove media from this playlist', 'rtmedia') . "' ><i class='rtmicon-times-circle' ></i>Remove</span></td>"
                               	. "</tr>";
                   }

                   if($list == '') {
                       return false;
                   }
                   $media_table .= $list;
                }
                $media_table .= "</tbody></table></div>";

                return $media_table;
            }

        }
    }

    /*
     * deletes the playlist meta and also the entry of the current playlist from rtmedia(music) meta after the playlist is deleted.
     */

    function delete_playlist_datas ( $id ) {

        if( $id != "") {

            if( is_rtmedia_playlist()) { // if playlist is being deleted
                $playlist_meta  = get_rtmedia_meta( $id , 'media_ids' );

                $delete_meta = delete_rtmedia_meta( $id , 'media_ids' );

                if( $playlist_meta != "") {
                    foreach ( $playlist_meta as $key => $media_id ) {

                      $remove_meta =  $this->remove_playlist_from_music_meta ( $media_id, $id );
                    }
                }

            } else {

                $playlists = get_rtmedia_meta( $id , 'playlists' );
                if( !empty( $playlists)){ //if media being deleted was there in any playlist, then remove that media from playlist meta
                    foreach( $playlists as $playlist_id ) {
                        $this->remove_music_from_playlist_meta ( $id, $playlist_id );
                    }
                    //delete media meta with key 'playlists'
                    $delete_meta = delete_rtmedia_meta( $id , 'playlists' );
                }
            }
        }
    }

    /*
     * function to create new playlist
     */
    function create_new_playlist () {
        if( isset($_POST['name']) && $_POST['name'] != "") {
           $playlist_id =  $this->add( $_POST['name'] , get_current_user_id (), true, false, $_POST['context'] , $_POST['context_id'], $_POST['privacy']);

           if($playlist_id) {
               echo $playlist_id;
           } else {
               echo false;
           }
           wp_die();
        }
    }

    /*
     * Enqueues the stylesheet for the playlist addon of mediaelement.js
     */
    function add_playlist_style() {
        echo "<link rel='stylesheet' id='admin-bar-css'  href='" . RTMEDIA_PRO_URL . "lib/playlist/mep-feature-playlist.css?ver=" . RTMEDIA_PRO_VERSION . "' type='text/css' media='all' />";
    }

    /*
     * Filters the content for the playlist single page
     * @params accepts the current html
     * #params rtmedia_media variable
     */
    function rtmedia_single_playlist_content ( $html, $rtmedia_media ) {

        if( is_rtmedia_playlist () ) {
            $medialist = maybe_unserialize( get_rtmedia_meta( $rtmedia_media->id , 'media_ids' , true ) );
            $source = "";
            if( isset( $medialist ) && $medialist != "") {
                foreach ( $medialist as $key=>$value ) {
                    $media_id = rtmedia_media_id( $value );
                    $url = wp_get_attachment_url( $media_id );
                    $permalink = get_rtmedia_permalink($value);
                    $title = get_rtmedia_title($value);
                    if( $url ) {
                        $source .= '<source src="' . $url . '"  type="audio/mp3" data-permalink="' . $permalink . '" title="' . $title . '"/>';
                    }
                }

            }

            if( $source != "") {
                //$this->add_playlist_scripts();

                $html = '<div id="rtmedia-playlist-view">'
                        . '<audio controls="controls" class="rtmp-media-playlist" id="bp_media_audio_' . $rtmedia_media->id . '">';
                $html .= $source;
                $html .= "</audio></div>";
	    } else {
                add_filter( 'rtmedia_render_media_rate', array( $this, 'do_not_render_rate_empty_playlist' ) );
                $html = "<span>" . __('Oops. No media found for this playlist.') . "</span>";
                return $html;
            }

        }

        return $html;
    }
    
    function do_not_render_rate_empty_playlist( $flag ) {
        return true;
    }

    /*
     * filter the context for the playlist template
     * @param expects the current $context
     */
    function rtmedia_pro_context_filter ( $context ) {

       if( is_rtmedia_playlist_gallery() || is_rtmedia_playlist () ) {
          $context = false;
       }

       return $context;
    }

    /*
     * filter the template for the playlist template
     * @param expects the current $template
     */
    function rtmedia_pro_template_filter ( $template ) {
       if( is_rtmedia_playlist_gallery() ) {
           $template = "playlist-gallery";
       } elseif ( is_rtmedia_playlist ()){
           //$template = 'playlist-single';
           global $rtmedia_query;
           if ( isset ( $rtmedia_query->media_query ) && $rtmedia_query->action_query->action == 'edit' ) {
                if ( isset ( $rtmedia_query->media_query[ 'media_author' ] ) && (get_current_user_id () == $rtmedia_query->media_query[ 'media_author' ]) ) {
						add_filter( 'rtmedia_edit_media_album_select','rtm_do_not_load_album_select_playlist_edit', 10, 1 );
						add_filter( 'rtmedia_edit_media_attribute_select','rtm_do_not_load_album_select_playlist_edit', 10, 1 );
                        $template = 'playlist-single-edit';
                    }
           }
       }
       if( is_page() || is_single() ) { // for post/pages where media gallery is shown using gallery shortcode and media type is set as "playlist"
           global $rtmedia_query;
            if( isset($rtmedia_query->media_query) && $rtmedia_query->media_query['media_type'] == 'playlist') {
            $template = "playlist-gallery";
            }
       }

       return $template;
    }

	function rtm_do_not_load_album_select_playlist_edit( $flag ) {
		return false;
	}

    /*
     * filter the location of the template to locate the playlist templates located under templates/media folder
     * @param expects the current Location, $url, $ogpath, and current $template name
     */
    function rtmedia_locate_playlist_template ( $located , $url , $ogpath, $template_name ){

        if( isset( $template_name ) && in_array( $template_name, array( 'playlist-gallery.php', 'playlist-single.php', 'playlist-single-edit.php', 'playlist-gallery-item.php' )) ) {
            if ( $url ) {
                $located = trailingslashit ( RTMEDIA_PRO_URL ) . $ogpath . $template_name;
            } else {
                $located = trailingslashit ( RTMEDIA_PRO_PATH ) . $ogpath . $template_name;
            }
        }
        return $located;
    }

    /*
     * filter the Backbone template for the playlist gallery template
     * @param expects the current $template
     */
    function rtmedia_pro_backbone_template_filter( $template ) {
        if( is_rtmedia_playlist_gallery() ) {
            $template = "playlist-gallery-item";
        }
        return $template;
    }

    /**
     * filters the allowed media types and adds "playist" as allowed media type.
     * @params accepts the array of currently allowed media types
     */
    function add_allowed_types ( $allowed_types ) {

        $playlist_type = array(
        'playlist' => array(
            'name' => 'playlist',
            'plural' => 'playlist',
            'label' => __('Playlist', 'rtmedia'),
            'plural_label' => __('Playlists', 'rtmedia'),
            'extn' => '',
            'thumbnail' => RTMEDIA_PRO_URL . 'app/assets/img/playlist-icon.png',
	    'settings_visibility' => false ),
        );

        if (!defined('RTMEDIA_PLAYLIST_PLURAL_LABEL')) {
	    define('RTMEDIA_PLAYLIST_PLURAL_LABEL', __('Playlists'));
	}
	if (!defined('RTMEDIA_PLAYLIST_LABEL')) {
                define('RTMEDIA_PLAYLIST_LABEL', $playlist_type['playlist']['label']);
        }
        $allowed_types = array_merge ( $allowed_types , $playlist_type );

        return $allowed_types;
    }

    /**
     * Register Custom Post Types required by rtMediaPro
     */
    function register_post_types () {
        /* Set up Playlist labels */
        $playlist_labels = array(
            'name' => __ ( 'Playlists', 'rtmedia' ),
            'singular_name' => __ ( 'Playlist', 'rtmedia' ),
            'add_new' => __ ( 'Create', 'rtmedia' ),
            'add_new_item' => __ ( 'Create Playlist', 'rtmedia' ),
            'edit_item' => __ ( 'Edit Playlist', 'rtmedia' ),
            'new_item' => __ ( 'New Playlist', 'rtmedia' ),
            'all_items' => __ ( 'All Playlist', 'rtmedia' ),
            'view_item' => __ ( 'View Playlist', 'rtmedia' ),
            'search_items' => __ ( 'Search Playlist', 'rtmedia' ),
            'not_found' => __ ( 'No Playlist found', 'rtmedia' ),
            'not_found_in_trash' => __ ( 'No Playlist found in trash', 'rtmedia' ),
            'parent_item_colon' => '',
            'menu_name' => __ ( 'Playlists', 'rtmedia' )
        );

        /* Set up Playlist post type arguments */
        $playlist_args = array(
            'labels' => $playlist_labels,
            'public' => false,
            'publicly_queryable' => false,
            'show_ui' => false,
            'show_in_menu' => false,
            'query_var' => false,
            'capability_type' => 'post',
            'has_archive' => false,
            'hierarchical' => false,
            'menu_position' => null,
            'rewrite' => false,
            'supports' => array(
                'title',
                'author',
                'thumbnail',
                'excerpt',
                'comments'
            )
        );

        /* register Playlist post type */
        register_post_type ( 'rtmedia_playlist', $playlist_args );
    }

    /**
     * Method verifies the nonce passed while performing any CRUD operations
     * on the playlist.
     *
     * @param type $mode
     * @return boolean
     */
    function verify_nonce ( $mode ) {

        $nonce = $_REQUEST[ "rtmedia_{$mode}_playlist_nonce" ];
        $mode = $_REQUEST[ 'mode' ];
        if ( wp_verify_nonce ( $nonce, 'rtmedia_' . $mode ) )
            return true;
        else
            return false;
    }

    /**
     * returns user_id of the current logged in user in wordpress
     *
     * @global type $current_user
     * @return type
     */
    function get_current_author () {

        return get_current_user_id ();
    }

    /**
     * Adds a new playlist
     *
     * @global type $rtmedia_interaction
     * @param type $title
     * @param type $author_id
     * @param type $new
     * @param type $post_id
     * @return type
     */
    function add ( $title = '', $author_id = false, $new = true, $post_id = false, $context = false, $context_id = false , $privacy = 20 ) {

        /* action to perform any task before adding the playlist */
        do_action ( 'rtmedia_before_add_playlist' );

        $author_id = $author_id ? $author_id : $this->get_current_author ();

        /* Playlist Details which will be passed to Database query to add the Playlist */
        $post_vars = array(
            'post_title' => (empty ( $title )) ? 'Untitled Playlist' : $title,
            'post_type' => 'rtmedia_playlist',
            'post_author' => $author_id,
            'post_status' => 'publish'
        );

        /* Check whether to create a new playlist in wp_post table
         * This is the case when a user creates a playlist of his own. We need to
         * create a separte post in wp_post which will work as parent for
         * all the media uploaded to that playlist
         *
         *  */
        if ( $new )
            $playlist_id = wp_insert_post ( $post_vars );
        /**
         * if user uploads any media directly to a post or a page or any custom
         * post then the context in which the user is uploading a media becomes
         * an playlist in itself. We do not need to create a separate playlist in this
         * case.
         */
        else
            $playlist_id = $post_id;

        $current_playlist = get_post ( $playlist_id, ARRAY_A );
        global $rtmedia_query;
        if ( $context === false ) {
            $context = (isset ( $rtmedia_query->query[ 'context' ] )) ? $rtmedia_query->query[ 'context' ] : NULL;
        }
        if ( $context_id === false ) {

            if( $context == 'profile' ){
                // if context is profile, the current user id should be the context_id
                $context_id = get_current_user_id();
            }else {
                $context_id = (isset ( $rtmedia_query->query[ 'context_id' ] )) ? $rtmedia_query->query[ 'context_id' ] : NULL;
            }

        }
        // add in the media since playlist is also a media
        //defaults
        global $rtmedia_interaction;
        $attributes = array(
            'blog_id' => get_current_blog_id (),
            'media_id' => $playlist_id,
            'album_id' => NULL,
            'media_title' => $current_playlist[ 'post_title' ],
            'media_author' => $current_playlist[ 'post_author' ],
            'media_type' => 'playlist',
            'context' => $context,
            'context_id' => $context_id,
            'activity_id' => NULL,
            'privacy' => $privacy
        );


	$model = new RTMediaModel();
        $rtmedia_id = $model->insert ( $attributes );//create function for insert_playlist and use here
	$rtMediaNav = new RTMediaNav();
	$media_count = $rtMediaNav->refresh_counts ( $context_id, array( "context" => $context, 'media_author' => $context_id ) );
        /* action to perform any task after adding the playlist */
	global $rtmedia_points_media_id;
	$rtmedia_points_media_id = $rtmedia_id;
        do_action ( 'rtmedia_after_add_playlist', $this );

        return $rtmedia_id;
    }

    /**
     * Function to get number of playlist of specific user
     *
     * @param type $user_id
     * @return number of playlists of the user
     */
    function get_playlist_count ( $user_id ) {

        if( isset( $user_id ) && $user_id != "") {

            global $wpdb;
            $where = get_posts_by_author_sql( "rtmedia_playlist", true, $user_id );
            $count = $wpdb->get_var( "SELECT COUNT(*) FROM $wpdb->posts $where" );
            return apply_filters( 'get_user_playlist_count', $count, $user_id );
        }
    }

    /**
     * Function to get the list playlist of specific user [context = profile]
     *
     * @param type $user_id
     * @return List of playlists of the user
     */
    function get_profile_playlists ( $user_id ) {
        if( isset( $user_id ) && $user_id != "") {

            $model = new RTMediaModel();
            $playlists = $model->get( array ('media_type' => 'playlist', 'media_author' => $user_id, 'context' => 'profile' ) );
            return apply_filters( 'get_user_playlists', $playlists, $user_id );
        }
    }

    /**
     * Function to get the list playlist of specific user [context = group]
     *
     * @param type $user_id
     * @return List of playlists of the user
     */
    function get_group_playlists ( ) {
            $model = new RTMediaModel();
            $playlists = $model->get( array ('media_type' => 'playlist', 'context' => 'group' ) );
            return apply_filters( 'get_user_playlists', $playlists );
    }

    /**
     * Helper function to set number of queries in pagination
     *
     * @param int $per_page
     * @param type $table_name
     * @return int
     */
    function set_queries_per_page ( $per_page, $table_name ) {

        $per_page = 1;
        return $per_page;
    }

}
