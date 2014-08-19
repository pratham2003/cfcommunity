<?php

/**
 * Created by PhpStorm.
 * Date: 8/4/14
 * Time: 6:14 PM
 * Author: ritz <ritesh.patel@rtcamp.com>
 */
class RTMediaProUploadTerms {

	function __construct() {
		add_filter( 'rtmedia_general_content_groups', array( $this, 'admin_setting_add_terms_section' ), 10, 1 );
		add_filter( 'rtmedia_general_content_add_itmes', array( $this, 'admin_setting_add_terms_option' ), 10, 2 );
		add_filter( 'rtmedia_uploader_before_start_upload_button', array( $this, 'show_terms_and_service_checkbox' ), 199, 1 );
	}

	function admin_setting_add_terms_section( $general_group ) {
		$general_group[ 40 ] = "Ask users to agree to your terms";

		return $general_group;
	}

	function admin_setting_add_terms_option( $render_options, $options ) {
		$render_options[ 'general_enable_upload_terms' ]    = array(
			'title'    => __( 'Show "Terms of Service" checkbox on upload screen', 'rtmedia' ), 'callback' => array( 'RTMediaFormHandler', 'checkbox' ), 'args' => array(
				'key' => 'general_enable_upload_terms', 'value' => $options[ 'general_enable_upload_terms' ], 'desc' => __( 'User have to check the terms and conditions before uploading the media.', 'rtmedia' )
			), 'group' => 40
		);
		$render_options[ 'general_upload_terms_page_link' ] = array(
			'title'    => __( 'Link for "Terms of Service" page', 'rtmedia' ), 'callback' => array( 'RTMediaFormHandler', 'textbox' ), 'args' => array(
				'key' => 'general_upload_terms_page_link', 'value' => $options[ 'general_upload_terms_page_link' ], 'desc' => __( 'Link to the terms and condition page where user can read terms and conditions.', 'rtmedia' )
			), 'group' => 40
		);

		return $render_options;
	}

	function show_terms_and_service_checkbox( $content ) {
		global $rtmedia;
		$options       = $rtmedia->options;
		$terms_content = '';
		if ( ( isset( $options[ 'general_enable_upload_terms' ] ) && $options[ 'general_enable_upload_terms' ] != '0' ) && ( isset( $options[ 'general_upload_terms_page_link' ] ) && $options[ 'general_upload_terms_page_link' ] != '' ) ){
			$terms_content = '<span class="rtmedia-upload-terms"> <input type="checkbox" name="rtmedia_upload_terms_conditions" id="rtmedia_upload_terms_conditions" /> <label for="rtmedia_upload_terms_conditions">' . apply_filters( "rtmedia_upload_terms_service_agree_label", __( "I agree to", "rtmedia" ) ) . "&nbsp;<a href='" . $options[ 'general_upload_terms_page_link' ] . "' target='_blank'>" . apply_filters( "rtmedia_upload_terms_service_link_label", __( "Terms of Service", "rtmedia" ) ) . '</a></label></span>';
		}

		return $content . $terms_content;
	}
} 