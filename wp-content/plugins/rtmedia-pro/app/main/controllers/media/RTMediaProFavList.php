<?php
/**
 * Created by PhpStorm.
 * User: ritz
 * Date: 25/6/14
 * Time: 6:40 PM
 */

/**
 *
 * @author ritz
 */
class RTMediaProFavList {

	function __construct() {
		if ( is_rtmedia_favlist_enable() ){

			if ( ! defined( 'RTMEDIA_FAVLIST_PLURAL_LABEL' ) ){
				define( 'RTMEDIA_FAVLIST_PLURAL_LABEL', 'FavList' );
			}
			if ( ! defined( 'RTMEDIA_FAVLIST_LABEL' ) ){
				define( 'RTMEDIA_FAVLIST_LABEL', 'FavList' );
			}
			if ( ! defined( 'RTMEDIA_FAVLIST_SLUG' ) ){
				define( 'RTMEDIA_FAVLIST_SLUG', 'favlist' );
			}

			add_action( 'init', array( &$this, 'register_post_types' ), 12 );
			add_filter( 'rtmedia_allowed_types', array( $this, "add_allowed_types" ), 10, 1 );
			add_filter( 'rtmedia_template_filter', array( $this, "rtmedia_pro_template_filter" ), 10, 1 );
			add_filter( 'rtmedia_located_template', array( $this, 'rtmedia_locate_favlist_template' ), 10, 4 );
			add_filter( 'rtmedia_backbone_template_filter', array( $this, "rtmedia_pro_backbone_template_filter" ), 10, 1 );
			add_action( 'wp_ajax_rtmedia_create_favlist', array( $this, 'create_new_favlist' ), 10 );
			add_action( 'wp_ajax_rtmedia_remove_media_from_favlist', array( $this, 'rtmedia_remove_media_from_favlist' ), 10 );
			add_action( 'rtmedia_after_delete_media', array( $this, 'delete_favlist_data' ), 10, 1 );
			add_action( "rtmedia_set_query", array( $this, "rtmedia_set_query_filters" ), 99 );
			add_filter( "rtmedia_allowed_query", array( $this, "rtmedia_allowed_favlist_parameter_in_query" ), 99 );
		}
		// add on/off switch for user favlist page
		add_filter( 'rtmedia_general_content_add_itmes', array( $this, 'rtmedia_general_content_add_favlist_option' ), 10, 2 );
		// add new group in Other settings
		add_filter( 'rtmedia_general_content_groups', array( $this, 'general_content_add_favlist_group' ), 10, 1 );
		// exclude favlist counts from counts in media nav
		add_filter( 'rtmedia_media_count_exclude_type', array( $this, 'rtmedia_media_count_exclude_type_favlist' ), 10, 1 );
	}

	function rtmedia_media_count_exclude_type_favlist( $types ){
		$types[] = 'favlist';
		return $types;
	}

	function rtmedia_allowed_favlist_parameter_in_query( $param = array() ) {
		$param[] = 'favlist_id';
		return $param;
	}

	function edit_favlists_media_list( $media_type ) {
		if ( isset( $media_type ) && $media_type == "favlist" ){
			global $rtmedia_media;
			$media_list = get_rtmedia_meta( $rtmedia_media->id, 'media_ids' );
			if ( $media_list != "" ){
				$model       = new RTMediaModel();
				$media_table = "<div class='rtmedia-favlist-media-table'>" . "<table id='" . $rtmedia_media->id . "' class='rtmedia-favlist-media-list'>" . "<thead><tr><th>" . __( 'Title' ) . "</th><th colspan='2'>" . __( 'Actions' ) . "</th></tr></thead>" . "<tbody>";
				$count       = 1;
				foreach ( $media_list as $media_id ) {
					$media = $model->get( array( 'id' => $media_id ) );
					$list  = '';
					if ( $media ){
						$link = trailingslashit( get_rtmedia_permalink( $media[ 0 ]->id ) );
						$list .= "<tr>" . "<td class='rtm-edit-media-list-title'>" . $count ++ . ". <a href='" . $link . "' target='_blank' title='" . __( 'View this media' ) . "' >" . $media[ 0 ]->media_title . "</a></td>" . "<td class='rtm-edit-media-list-edit'><a href='" . $link . "edit' target='_blank' title='" . __( 'Edit this media' ) . "' ><i class='rtmicon-edit rtmicon-fw'></i>" . __( 'Edit' ) . "</a></td>" . "<td class='rtm-edit-media-list-delete'><span id='" . $media[ 0 ]->id . "' class='rtmedia-remove-media-from-favlist' title='" . __( 'Remove media from this favlist', 'rtmedia' ) . "' ><i class='rtmicon-times-circle' ></i>Remove</span></td>" . "</tr>";
					}

					if ( $list == '' ){
						continue;
					}
					$media_table .= $list;
				}
				$media_table .= "</tbody></table></div>";

				return $media_table;
			}

		}
	}

	function rtmedia_set_query_filters() {
		// unset media_author from query
		add_filter( 'rtmedia_media_query', array( $this, 'modify_media_query' ), 10, 3 );
		// join with interaction table to get user's liked media

		// remove interaction join filter
		add_action( 'bp_before_member_header', array( $this, 'remove_favlist_where_filter' ) );
	}

	function rtmedia_gallery_title( $title ) {
		global $media_query_clone_favlist;
		$favlist_id = $media_query_clone_favlist[ 'id' ];
		$title      = get_rtmedia_title( $favlist_id );

		return $title;
	}

	function rtmedia_modify_wp_title( $title, $default, $sep ) {
		global $media_query_clone_favlist;
		$favlist_id  = $media_query_clone_favlist[ 'id' ];
		$title_array = explode( $sep, $title );
		remove_filter( 'rtmedia-model-where-query', array( $this, 'rtmedia_model_where_query_favlist' ), 10, 2 );
		$title_array[ 0 ] = ucfirst( get_rtmedia_title( $favlist_id ) );
		$title            = implode( " " . $sep . " ", $title_array );
		add_filter( 'rtmedia-model-where-query', array( $this, 'rtmedia_model_where_query_favlist' ), 10, 2 );

		return $title;
	}

	function modify_media_query( $media_query, $action_query, $query ) {
		global $rtmedia_query;
		global $media_query_clone_favlist; // store media_query for reference
		$media_query_clone_favlist = $media_query;
		if( isset( $media_query['favlist_id'] ) && intval( $media_query['favlist_id'] ) > 0 ){
			add_filter( 'rtmedia-model-where-query', array( $this, 'rtmedia_model_shortcode_where_query_favlist' ), 10, 3 );
			add_action( 'rtmedia_before_media_gallery', array( $this, 'remove_rtmedia_model_shortcode_where_query_favlist' ), 10, 3 );
			unset( $media_query['favlist_id'] );

			// unset from global query so that multiple gallery shortcode can work
			if( isset( $rtmedia_query->query ) && isset( $rtmedia_query->query['favlist_id'] ) ) {
				unset( $rtmedia_query->query['favlist_id'] );
			}
			if ( isset( $media_query[ 'context_id' ] ) ){
				unset( $media_query[ 'context_id' ] );
			}
			if ( isset( $media_query[ 'context' ] ) ){
				unset( $media_query[ 'context' ] );
			}
		}
		if ( is_array( $media_query ) && isset( $media_query[ 'media_type' ] ) && $media_query[ 'media_type' ] == 'favlist' ){
			if ( ! ( isset( $action_query->action ) && $action_query->action == "edit" ) ){
				if ( isset( $media_query[ 'context_id' ] ) ){
					unset( $media_query[ 'context_id' ] );
				}
				if ( isset( $media_query[ 'id' ] ) ){		// in favlist gallery only show favlist of that user only...while in media gallery of a favlist remove check for media_author
					if ( isset( $media_query[ 'media_author' ] ) ){
						unset( $media_query[ 'media_author' ] );
					}
				}
				if ( isset( $media_query[ 'id' ] ) ){
					// set title
					add_filter( 'rtmedia_wp_title', array( $this, 'rtmedia_modify_wp_title' ), 10, 3 );
					// modify template title
					add_filter( 'rtmedia_gallery_title', array( $this, 'rtmedia_gallery_title' ) );
					// filter where query for favlist
					add_filter( 'rtmedia-model-where-query', array( $this, 'rtmedia_model_where_query_favlist' ), 10, 3 );
					$media_query[ 'media_type' ] = apply_filters( 'rtmedia_query_media_type_filter', array( 'compare' => 'IN', 'value' => array( 'music', 'video', 'photo' ) ) );
					unset( $media_query[ 'id' ] );
				}
			} else {
				add_filter( 'rtmedia_edit_media_album_select', array( $this, 'rtm_do_not_load_album_select_favlist_edit' ), 10, 1 );
				add_filter( 'rtmedia_edit_media_attribute_select', array( $this, 'rtm_do_not_load_album_select_favlist_edit' ), 10, 1 );
			}
		}

		return $media_query;
	}

	function rtm_do_not_load_album_select_favlist_edit() {
		return false;
	}

	function rtmedia_model_shortcode_where_query_favlist( $where, $table_name, $join ) {
		global $media_query_clone_favlist;
		$favlist_id     = $media_query_clone_favlist[ 'favlist_id' ];
		$media_model    = new RTMediaModel();
		$media_id_array = maybe_unserialize( get_rtmedia_meta( $favlist_id, 'media_ids' ) );
		if ( is_array( $media_id_array ) && sizeof( $media_id_array ) > 0 ){
			$media_id_string = implode( ',', $media_id_array );
		} else {
			$media_id_string = '0';
		}
		$where .= " AND {$table_name}.id in (" . $media_id_string . ") ";
		unset( $media_model );

		return $where;
	}

	function remove_rtmedia_model_shortcode_where_query_favlist() {
		remove_filter( 'rtmedia-model-where-query', array( $this, 'rtmedia_model_shortcode_where_query_favlist' ), 10, 3 );
	}

	function rtmedia_model_where_query_favlist( $where, $table_name, $join ) {
		global $media_query_clone_favlist;
		$favlist_id     = $media_query_clone_favlist[ 'id' ];
		$media_model    = new RTMediaModel();
		$media_id_array = maybe_unserialize( get_rtmedia_meta( $favlist_id, 'media_ids' ) );
		if ( is_array( $media_id_array ) && sizeof( $media_id_array ) > 0 ){
			$media_id_string = implode( ',', $media_id_array );
		} else {
			$media_id_string = '0';
		}
		$where .= " AND {$table_name}.id in (" . $media_id_string . ") ";
		unset( $media_model );

		return $where;
	}

	function remove_favlist_where_filter() {
		remove_filter( 'rtmedia-model-where-query', array( $this, 'rtmedia_model_where_query_favlist' ), 10, 3 );
	}

	function rtmedia_general_content_add_favlist_option( $render_options, $options ) {
		$render_options[ 'general_enable_favlist' ] = array(
			'title'    => __( 'Enable FavList', 'rtmedia' ), 'callback' => array( 'RTMediaFormHandler', 'checkbox' ), 'args' => array(
				'key' => 'general_enable_favlist', 'value' => $options[ 'general_enable_favlist' ], 'desc' => __( 'Allow users to create their own favorite media list.', 'rtmedia' )
			), 'group' => 25
		);

		return $render_options;
	}

	function general_content_add_favlist_group( $general_group ) {
		$general_group[ 25 ] = "User's Favorite Media List";

		return $general_group;
	}

	function delete_favlist_data( $id ) {
		if ( $id != "" ){
			if ( is_rtmedia_favlist() ){ // if favlist is being deleted
				$favlist_meta = maybe_unserialize( get_rtmedia_meta( $id, 'media_ids' ) );
				$delete_meta  = delete_rtmedia_meta( $id, 'media_ids' );
				if ( $favlist_meta != "" ){
					foreach ( $favlist_meta as $key => $media_id ) {
						$remove_meta = $this->remove_favlist_from_media_meta( $media_id, $id );
					}
				}
			} else {
				$favlists = maybe_unserialize( get_rtmedia_meta( $id, 'favlists' ) );
				if ( ! empty( $favlists ) ){ //if media being deleted was there in any favlist, then remove that media from favlist meta
					foreach ( $favlists as $favlist_id ) {
						$this->remove_media_from_favlist_meta( $id, $favlist_id );
					}
					//delete media meta with key 'favlists'
					$delete_meta = delete_rtmedia_meta( $id, 'favlists' );
				}
			}
		}
	}

	function rtmedia_remove_media_from_favlist() {
		if ( isset( $_POST[ 'action' ] ) && $_POST[ 'action' ] == "rtmedia_remove_media_from_favlist" && isset( $_POST[ 'media_id' ] ) && $_POST[ 'media_id' ] != "" && isset( $_POST[ 'favlist_id' ] ) && $_POST[ 'favlist_id' ] != ""
		){

			$favlist_id   = $_POST[ 'favlist_id' ];
			$media_id     = $_POST[ 'media_id' ];
			$favlist_meta = $this->remove_media_from_favlist_meta( $media_id, $favlist_id );
			$media_meta   = $this->remove_favlist_from_media_meta( $media_id, $favlist_id );
			if ( $media_meta && $favlist_meta ){
				echo "true";
				die();
			}
		}
		echo "false";
		die();
	}

	/*
     * Removes the media from the favlist (meta field)
     * @param expects the media_id of the media to be removed
     * @param expects the favlist_id of the favlist from which the media is to be removed
     */
	function remove_media_from_favlist_meta( $media_id, $favlist_id ) {
		if ( isset( $media_id ) && $media_id != "" && isset( $favlist_id ) && $favlist_id != "" ){

			$media_list = maybe_unserialize( get_rtmedia_meta( $favlist_id, 'media_ids' ) );
			$media_key  = array_search( $media_id, $media_list );
			if ( $media_list != "" && ( $media_key = array_search( $media_id, $media_list ) ) !== false ){
				unset( $media_list[ $media_key ] ); //remove media id from the favlist meta
				if ( ! empty ( $media_list ) ){
					$update_meta = update_rtmedia_meta( $favlist_id, "media_ids", $media_list );
				} else {
					$update_meta = delete_rtmedia_meta( $favlist_id, "media_ids" );
				}
				if ( $update_meta ){
					return true;
				}
			}
		}

		return false;
	}

	/*
	 * disassociates the favlist from the media meta field.
	 * @param expects the media_id of the media from which the favlist is to be disassociated
	 * @param expects the favlist_id of the favlist from which the media is to be removed
	 */
	function remove_favlist_from_media_meta( $media_id, $favlist_id ) {
		if ( isset( $media_id ) && $media_id != "" && isset( $favlist_id ) && $favlist_id != "" ){
			$favlist_list = maybe_unserialize( get_rtmedia_meta( $media_id, 'favlists' ) );

			if ( $favlist_list != "" && ( $favlist_key = array_search( $favlist_id, $favlist_list ) ) !== false ){
				unset( $favlist_list[ $favlist_key ] ); //remove favlist id from the media meta

				if ( ! empty( $favlist_list ) ){
					$update_meta = update_rtmedia_meta( $media_id, "favlists", $favlist_list );
				} else {
					$update_meta = delete_rtmedia_meta( $media_id, "favlists" );
				}

				if ( $update_meta ){
					return true;
				}
			}
		}

		return false;
	}

	function create_new_favlist() {
		$current_user_id = get_current_user_id();
		$favlist_id      = $this->add( $_POST[ 'name' ], $current_user_id, true, false, 'profile', $current_user_id, $_POST[ 'privacy' ] );
		if ( $favlist_id ){
			echo $favlist_id;
		} else {
			echo false;
		}
		wp_die();
	}

	function add( $title = '', $author_id = false, $new = true, $post_id = false, $context = false, $context_id = false, $privacy = 20 ) {

		/* action to perform any task before adding the favlist */
		do_action( 'rtmedia_before_add_favlist' );

		$author_id = $author_id ? $author_id : get_current_user_id();

		/* Favlist Details which will be passed to Database query to add the Favlist */
		$post_vars = array(
			'post_title' => ( empty ( $title ) ) ? 'Untitled Favlist' : $title, 'post_type' => 'rtmedia_favlist', 'post_author' => $author_id, 'post_status' => 'publish'
		);

		/* Check whether to create a new favlist in wp_post table
		 * This is the case when a user creates a favlist of his own. We need to
		 * create a separte post in wp_post which will work as parent for
		 * all the media uploaded to that favlist
		 *
		 *  */
		if ( $new ){
			$favlist_id = wp_insert_post( $post_vars );
		} /**
		 * if user uploads any media directly to a post or a page or any custom
		 * post then the context in which the user is uploading a media becomes
		 * an favlist in itself. We do not need to create a separate favlist in this
		 * case.
		 */ else {
			$favlist_id = $post_id;
		}

		$current_favlist = get_post( $favlist_id, ARRAY_A );
		global $rtmedia_query;
		if ( $context === false ){
			$context = ( isset ( $rtmedia_query->query[ 'context' ] ) ) ? $rtmedia_query->query[ 'context' ] : null;
		}
		if ( $context_id === false ){

			if ( $context == 'profile' ){
				// if context is profile, the current user id should be the context_id
				$context_id = get_current_user_id();
			} else {
				$context_id = ( isset ( $rtmedia_query->query[ 'context_id' ] ) ) ? $rtmedia_query->query[ 'context_id' ] : null;
			}

		}
		// add in the media since favlist is also a media
		//defaults
		$attributes = array(
			'blog_id' => get_current_blog_id(), 'media_id' => $favlist_id, 'album_id' => null, 'media_title' => $current_favlist[ 'post_title' ], 'media_author' => $current_favlist[ 'post_author' ], 'media_type' => 'favlist', 'context' => $context, 'context_id' => $context_id, 'activity_id' => null, 'privacy' => $privacy
		);


		$model       = new RTMediaModel();
		$rtmedia_id  = $model->insert( $attributes ); //create function for insert_favlist and use here
		$rtMediaNav  = new RTMediaNav();
		$media_count = $rtMediaNav->refresh_counts( $context_id, array( "context" => $context, 'media_author' => $context_id ) );
		/* action to perform any task after adding the favlist */
		global $rtmedia_points_media_id;
		$rtmedia_points_media_id = $rtmedia_id;
		do_action( 'rtmedia_after_add_favlist', $this );

		return $rtmedia_id;
	}

	function rtmedia_pro_backbone_template_filter( $template ) {
		if ( is_rtmedia_favlist_gallery() ){
			$template = "favlist-gallery-item";
		}

		return $template;
	}

	function rtmedia_locate_favlist_template( $located, $url, $ogpath, $template_name ) {
		if ( isset( $template_name ) && in_array( $template_name, array( 'favlist-gallery.php', 'favlist-single-edit.php', 'favlist-gallery-item.php' ) ) ){
			if ( $url ){
				$located = trailingslashit( RTMEDIA_PRO_URL ) . $ogpath . $template_name;
			} else {
				$located = trailingslashit( RTMEDIA_PRO_PATH ) . $ogpath . $template_name;
			}
		}

		return $located;
	}

	function rtmedia_pro_template_filter( $template ) {
		if ( is_rtmedia_favlist_gallery() ){
			$template = "favlist-gallery";
		} elseif ( is_rtmedia_favlist() ) {
			global $rtmedia_query, $media_query_clone_favlist;
			if ( is_rtmedia_single() ){
				$template = 'media-gallery';
				remove_action( 'rtmedia_media_gallery_actions', 'add_upload_button', 99 );
				remove_action( 'rtmedia_media_gallery_actions', 'rtmedia_gallery_options', 80 );
				remove_action( 'rtmedia_before_media_gallery', 'rtmedia_login_register_modal' );
				remove_action( 'rtmedia_media_gallery_actions', 'rtmedia_add_upload_album_button', 12 );
				if ( isset ( $rtmedia_query->media_query ) && $rtmedia_query->action_query->action == 'edit' ){
					if ( ( isset ( $rtmedia_query->media_query[ 'media_author' ] ) && ( get_current_user_id() == $rtmedia_query->media_query[ 'media_author' ] ) ) || ( isset ( $media_query_clone_favlist[ 'media_author' ] ) && ( get_current_user_id() == $media_query_clone_favlist[ 'media_author' ] ) ) ){
						$template = 'favlist-single-edit';
					}
				}
			}
		}
		if ( is_page() || is_single() ){ // for post/pages where media gallery is shown using gallery shortcode and media type is set as "favlist"
			global $rtmedia_query;
			if ( isset( $rtmedia_query->media_query ) && $rtmedia_query->media_query[ 'media_type' ] == 'favlist' ){
				$template = "favlist-gallery";
			}
		}

		return $template;
	}

	function rtmedia_add_favlist_media_type( $media_type ) {
		if ( isset( $media_type ) && $media_type != "" ){
			$media_type[ 'value' ] = array_merge( $media_type[ 'value' ], array( 'favlist' ) );
		}

		return $media_type;
	}

	function add_allowed_types( $allowed_types ) {

		$favlist_type = array(
			'favlist' => array(
				'name' => 'favlist', 'plural' => 'favlists', 'label' => __( 'FavList', 'rtmedia' ), 'plural_label' => __( 'FavLists', 'rtmedia' ), 'extn' => '', 'thumbnail' => RTMEDIA_PRO_URL . 'app/assets/img/favorite-media-thumb.png', 'settings_visibility' => false
			),
		);

		if ( ! defined( 'RTMEDIA_FAVLIST_PLURAL_LABEL' ) ){
			define( 'RTMEDIA_FAVLIST_PLURAL_LABEL', $favlist_type[ 'favlist' ][ 'plural_label' ] );
		}
		if ( ! defined( 'RTMEDIA_FAVLIST_LABEL' ) ){
			define( 'RTMEDIA_FAVLIST_LABEL', $favlist_type[ 'favlist' ][ 'label' ] );
		}
		$allowed_types = array_merge( $allowed_types, $favlist_type );

		return $allowed_types;
	}

	function register_post_types() {
		/* Set up favlist labels */
		$favlist_labels = array(
			'name' => __( 'FavList', 'rtmedia' ), 'singular_name' => __( 'FavList', 'rtmedia' ), 'add_new' => __( 'Create', 'rtmedia' ), 'add_new_item' => __( 'Create FlayList', 'rtmedia' ), 'edit_item' => __( 'Edit FavList', 'rtmedia' ), 'new_item' => __( 'New FavList', 'rtmedia' ), 'all_items' => __( 'All FavList', 'rtmedia' ), 'view_item' => __( 'View FavList', 'rtmedia' ), 'search_items' => __( 'Search FavList', 'rtmedia' ), 'not_found' => __( 'No FavList found', 'rtmedia' ), 'not_found_in_trash' => __( 'No FavList found in trash', 'rtmedia' ), 'parent_item_colon' => '', 'menu_name' => __( 'FavLists', 'rtmedia' )
		);

		/* Set up favlist post type arguments */
		$favlist_args = array(
			'labels' => $favlist_labels, 'public' => false, 'publicly_queryable' => false, 'show_ui' => false, 'show_in_menu' => false, 'query_var' => false, 'capability_type' => 'post', 'has_archive' => false, 'hierarchical' => false, 'menu_position' => null, 'rewrite' => false, 'supports' => array(
				'title', 'author', 'thumbnail', 'excerpt', 'comments'
			)
		);

		/* register favlist post type */
		register_post_type( 'rtmedia_favlist', $favlist_args );
	}

	function get_profile_favlists( $user_id ) {
		if ( isset( $user_id ) && $user_id != "" ){
			$model    = new RTMediaModel();
			$favlists = $model->get( array( 'media_type' => 'favlist', 'media_author' => $user_id, 'context' => 'profile' ) );

			return apply_filters( 'get_user_favlists', $favlists, $user_id );
		}
	}
}