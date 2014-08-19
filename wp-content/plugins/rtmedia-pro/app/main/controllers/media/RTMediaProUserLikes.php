<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Show media liked by user
 *
 * @author ritz
 */
class RTMediaProUserLikes {

    function __construct() {

	if (!defined('RTMEDIA_USER_LIKES_PLURAL_LABEL')) {
                define('RTMEDIA_USER_LIKES_PLURAL_LABEL', 'likes');
        }
        if (!defined('RTMEDIA_USER_LIKES_LABEL')) {
                define('RTMEDIA_USER_LIKES_LABEL', 'like');
        }
	// init user likes feature
	add_action( 'init', array($this,'init_user_likes') );
	// add on/off switch for user likes page
	add_filter('rtmedia_general_content_add_itmes', array($this,'rtmedia_general_content_add_user_likes_option'), 10, 2);
	// add new group in Other settings
	add_filter('rtmedia_general_content_groups', array($this,'general_content_add_likes_group'), 10, 1);
    }

    function general_content_add_likes_group( $general_group ) {
	$general_group[30] = "User's Like Page";
	return $general_group;
    }

    function rtmedia_general_content_add_user_likes_option( $render_options, $options ) {
	$render_options['general_enable_user_likes'] = array(
                'title' => __('Enable user\'s liked media page' ,'rtmedia'),
                'callback' => array('RTMediaFormHandler', 'checkbox'),
                'args' => array(
                        'key' => 'general_enable_user_likes',
                        'value' => $options['general_enable_user_likes'],
                        'desc' => __('One user can view all the media liked by other user in respect of privacy. It will add a new tab under media tab along with photos, videos, etc named \'Likes\'.','rtmedia')
                ),
		'group' => 30
        );
        return $render_options;
    }

    function init_user_likes() {
	global $rtmedia;
	$option = $rtmedia->options;
	if( isset( $option['general_enable_user_likes'] ) && $option['general_enable_user_likes'] != "0" ) {
	    // add "Likes" tab in navigation
	    add_action( 'add_extra_sub_nav', array( $this, 'add_extra_sub_nav' ) );
	    // filter in action query
	    add_filter( "rtmedia_action_query_modifier_value", array( $this, "rtmedia_action_query_modifier_value"), 99, 2 );
	}
    }

    function rtmedia_action_query_modifier_value( $modifier_value, $raw_query ) {
	if( $modifier_value == "likes" ) {
	    // unset media_author from query
	    add_filter( 'rtmedia_media_query', array( $this, 'modify_media_query' ), 10, 3 );
	    // join with interaction table to get user's liked media
	    add_filter( 'rtmedia-model-join-query', array($this,'rtmedia_model_join_interaction'),  10, 2 );
	    // remove interaction join filter
	    add_action( 'bp_before_member_header', array( $this,'remove_interaction_filter' ) );
	    // set title
	    add_filter( 'rtmedia_wp_title', array($this,'rtmedia_modify_wp_title'), 10, 3 );
	    // modify template title
	    add_filter( 'rtmedia_gallery_title', array( $this,'rtmedia_gallery_title' ) );
	    return "";
	}
	return $modifier_value;
    }

    function rtmedia_gallery_title( $title ) {
	global $media_query_clone;
	$user = get_userdata ( $media_query_clone['context_id'] );
	if( get_current_user_id() == $media_query_clone['context_id'] ) {
		return apply_filters( 'rtmedia_my_likes_media_page_title', __( 'Media liked by me', 'rtmedia' ) );
	} else {
		return apply_filters( 'rtmedia_user_likes_media_page_title', __( 'Media liked by', 'rtmedia' )." ".$user->user_nicename );
	}
    }

    function rtmedia_modify_wp_title( $title, $default, $sep ) {
		return ucfirst( constant( 'RTMEDIA_USER_LIKES_PLURAL_LABEL' ) ) . $title;
    }

    function modify_media_query( $media_query, $action_query, $query ) {
	global $media_query_clone; // store media_query for reference
	global $rtmedia_like_page;
	$rtmedia_like_page = true;
	$media_query_clone = $media_query;
	if( isset( $media_query['context_id'] ) ) {
	    unset( $media_query['context_id'] );
	}
	if( isset( $media_query['media_author'] ) ) {
	    unset( $media_query['media_author'] );
	}
	return $media_query;
    }

    function remove_interaction_filter() {
		remove_filter( 'rtmedia-model-join-query', array($this,'rtmedia_model_join_interaction'),  10, 2 );
    }

    function rtmedia_model_join_interaction( $join, $table_name ) {
	global $rtmedia_interaction;
	$interaction_table = new RTMediaInteractionModel();
	$user_id = $rtmedia_interaction->context->id;
	$join.= " INNER JOIN {$interaction_table->table_name} ON ( {$table_name}.id = {$interaction_table->table_name}.media_id AND {$interaction_table->table_name}.action = 'like' AND {$interaction_table->table_name}.user_id = '{$user_id}' AND {$interaction_table->table_name}.value = '1' ) ";
	return $join;
    }

    function add_extra_sub_nav() {
	global $rtmedia_query, $rtmedia_like_page;

	if( isset( $rtmedia_query->query[ 'context' ] ) && $rtmedia_query->query[ 'context' ] == "profile" ) {
	    if ( ( isset ( $rtmedia_query->action_query->media_type ) && "likes" == $rtmedia_query->action_query->media_type ) || ( isset( $rtmedia_like_page ) && $rtmedia_like_page ) ) {
		$selected = ' class="current selected"';
	    } else {
		$selected = '';
	    }

	    $context = isset ( $rtmedia_query->query[ 'context' ] ) ? $rtmedia_query->query[ 'context' ] : 'default';
	    $context_id = isset ( $rtmedia_query->query[ 'context_id' ] ) ? $rtmedia_query->query[ 'context_id' ] : 0;
	    $profile_link = trailingslashit ( get_rtmedia_user_link ( $rtmedia_query->query[ 'context_id' ] ) );
	    $li_content = '<li id="rtmedia-nav-item-user-likes-' . $context . '-' . $context_id . '-li" ' . $selected . '> <a id="rtmedia-nav-item-user-likes" href="'.$profile_link.RTMEDIA_MEDIA_SLUG.'/likes'.'">'.  ucfirst( constant( 'RTMEDIA_USER_LIKES_PLURAL_LABEL' ) ) .'</a> </li>';
	    echo apply_filters( 'rtmedia_sub_nav_user_likes', $li_content );
	}
    }
}
