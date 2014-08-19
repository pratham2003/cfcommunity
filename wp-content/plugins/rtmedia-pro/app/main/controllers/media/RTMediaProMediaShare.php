<?php

/**
 * Created by PhpStorm.
 * Author: ritz <ritesh.patel@rtcamp.com>
 * Date: 18/4/14
 * Time: 4:14 PM
 */
class RTMediaProMediaShare {

	function __construct() {
		add_action( 'rtmedia_actions_before_description', array( $this, 'show_rtsocial_buttons' ), 20 );
		add_filter( 'rtmedia_display_content_add_itmes', array( $this, 'rtmedia_general_content_single_view_options' ), 20, 2 );
		add_action( 'rtmedia_save_admin_settings', array( $this, 'rtmedia_intsall_update_rtsocial' ), 10, 1 );
	}

	function is_media_share_enable() {
		global $rtmedia;
		$option = $rtmedia->options;
		if ( isset( $option[ 'general_enable_media_share' ] ) && $option[ 'general_enable_media_share' ] == '1' ){
			return true;
		}

		return false;
	}

	function rtmedia_general_content_single_view_options( $render_options, $options ) {
		$render_options[ 'general_enable_media_share' ] = array(
			'title'    => __( 'Enable <a href="http://wordpress.org/plugins/rtsocial/" target="_blank">rtSocial</a> share buttons', 'rtmedia' ), 'callback' => array( 'RTMediaFormHandler', 'checkbox' ), 'args' => array(
				'key' => 'general_enable_media_share', 'value' => $options[ 'general_enable_media_share' ], 'desc' => __( 'By enabling this, rtSocial plugin will be installed / activated and it will show rtSocial social media share button on single media view.', 'rtmedia' )
			), 'group' => "10"
		);
		if ( function_exists( 'rtsocial' ) ){
			$render_options[ 'general_enable_media_share' ][ 'after_content' ] = __( 'You can', 'rtmedia') . ' <a  target="_blank" href=\'' . admin_url( "options-general.php?page=rtsocial-options" ) . '\'>' . __( 'manage rtSocial options', 'rtmedia') . '</a> ' . __( 'from here.', 'rtmedia' );
		}

		return $render_options;
	}

	function show_rtsocial_buttons() {
		if ( function_exists( 'rtsocial' ) && $this->is_media_share_enable() ){
			add_filter( 'rtsocial_permalink', array( $this, 'rtmedia_rtsocial_permalink' ), 10, 3 );
			add_filter( 'rtsocial_post_object', array( $this, 'rtmedia_rtsocial_post_object' ), 10, 1 );
			add_filter( 'rtsocial_pinterest_thumb', array( $this, 'rtsocial_pinterest_thumb' ), 10, 2 );
			echo "<br />" . rtsocial( array( 'placement_options_set' => 'manual' ) );
			remove_filter( 'rtsocial_pinterest_thumb', array( $this, 'rtsocial_pinterest_thumb' ), 10, 2 );
			remove_filter( 'rtsocial_permalink', array( $this, 'rtmedia_rtsocial_permalink' ), 10, 3 );
			remove_filter( 'rtsocial_post_object', array( $this, 'rtmedia_rtsocial_post_object' ), 10, 1 );
		}
	}

	function rtsocial_pinterest_thumb( $thumb_src, $post_id ) {
		global $rtmedia_media;
		$thumb_src = rtmedia_image( apply_filters( 'rtmedia_pro_pinterest_thumb_size', 'rt_media_thumbnail' ), false, false );

		return $thumb_src;
	}

	function rtmedia_rtsocial_permalink( $perma_link, $post_id, $post ) {
		global $rtmedia_media;

		return get_rtmedia_permalink( $rtmedia_media->id );
	}

	function rtmedia_rtsocial_post_object( $post ) {
		global $rtmedia_media;
		if ( ! is_object( $post ) ){
			$post = new stdClass();
		}
		$post->ID         = $rtmedia_media->media_id;
		$post->post_title = $rtmedia_media->media_title;

		return $post;
	}

	function rtmedia_intsall_update_rtsocial( $options ) {
		// Install rtSocial
		if ( isset( $options[ 'general_enable_media_share' ] ) && $options[ 'general_enable_media_share' ] == '1' ){
			global $rtmedia_plugins;
			$rtmedia_plugins[ 'rtsocial' ] = array(
				'project_type' => 'all', 'name' => esc_html__( 'rtSocial', 'rtmedia' ), 'active' => function_exists( 'rtsocial' ), 'filename' => 'source.php',
			);
			if ( ! current_user_can( 'install_plugins' ) || ! current_user_can( 'activate_plugins' ) ){
				die( __( 'ERROR: You lack permissions to install and/or activate plugins.', 'rtmedia' ) );
			}
			rtmedia_pro_plugin_upgrader_class();
			if ( ! is_rtmedia_plugin_installed( 'rtsocial' ) ){
				rtmedia_pro_install_plugin( 'rtsocial' );
			} else {
				if ( is_rtmedia_plugin_installed( 'rtsocial' ) && ! is_rtmedia_plugin_active( 'rtsocial' ) ){
					rtmedia_pro_activate_plugin( get_path_for_rtmedia_plugins( 'rtsocial' ) );
				}
			}
		}
	}
} 