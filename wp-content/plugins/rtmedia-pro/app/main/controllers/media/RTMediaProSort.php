<?php

/**
 * Created by PhpStorm.
 * Date: 2/4/14
 * Time: 1:07 PM
 * Author: ritz <ritesh.patel@rtcamp.com>
 */
class RTMediaProSort {

	function __construct() {
		add_action( 'rtmedia_media_gallery_actions', array( $this, 'rtmedia_gallery_sort_option' ), 50 );
		add_action( 'rtmedia_album_gallery_actions', array( $this, 'rtmedia_album_sort_option' ), 50 );
//		add_filter( 'rtmedia_media_uploader_attributes', array( $this, 'add_sort_hidden_value' ), 10, 1 );
		add_action( 'rtmedia_after_media_gallery_title', array( $this, 'add_sort_hidden_fields' ), 10, 1 );
		add_action( 'rtmedia_after_album_gallery_title', array( $this, 'add_sort_hidden_fields' ), 10, 1 );
		add_filter( 'rtmedia_query_filter', array( $this, 'add_sort_fields_in_query' ), 10, 1 );
                add_action( 'wp_enqueue_scripts', array( &$this, 'enqueue_sorting_values' ), 999 );
	}
        
        function enqueue_sorting_values() {
            global $rtmedia, $rtmedia_query;
            
            if( isset( $rtmedia->options ) && isset( $rtmedia->options[ 'general_enable_document_other_table_view' ] ) ) {
                wp_localize_script( 'rtmedia-pro-main', 'rtmedia_document_other_table_view', $rtmedia->options[ 'general_enable_document_other_table_view' ] );
            }
            
            if( isset( $rtmedia_query->media_query ) ) {
                wp_localize_script( 'rtmedia-pro-main', 'rtmedia_sort_media_type', $rtmedia_query->media_query[ 'media_type' ] );
            }
        }

	function add_sort_fields_in_query( $args ) {
		if ( isset( $_GET[ 'json' ] ) && $_GET[ 'json' ] ){
			if ( isset( $_GET[ 'sort_by' ] ) && $_GET[ 'sort_by' ] != "" ){
				switch ( $_GET[ 'sort_by' ] ) {
					case 'date' :
						$args[ 'order_by' ] = "media_id";
					break;
					case 'size' :
						$args[ 'order_by' ] = "file_size";
					break;
					case 'title' :
						$args[ 'order_by' ] = "media_title";
					break;
				}
			}
			if ( isset( $_GET[ 'sort_order' ] ) && $_GET[ 'sort_order' ] != "" ){
				switch ( $_GET[ 'sort_order' ] ) {
					case 'asc' :
						$args[ 'order' ] = "ASC";
					break;
					default :
						$args[ 'order' ] = "DESC";
					break;
				}
			}
		}

		return $args;
	}

	function add_sort_hidden_value( $attr ) {
		if ( ! isset( $attr[ 'sort_by' ] ) ){
			$attr[ 'sort_by' ] = "date";
		}
		if ( ! isset( $attr[ 'sort_order' ] ) ){
			$attr[ 'sort_order' ] = "desc";
		}

		return $attr;
	}

	function add_sort_hidden_fields() {
	?>
		<input type="hidden" name="sort_by" id="rt_upload_hf_sort_by" value="date">
		<input type="hidden" name="sort_order" id="rt_upload_hf_sort_order" value="desc">
	<?php
	}


	function rtmedia_gallery_sort_option() {
		$option_buttons = "";
		$options        = array();
		$options[ ]     = "<span id='rtm-sort-date-asc' onclick='rtmedia_sort_gallery(this, \"date\", \"asc\")'><i class='rtmicon-sort-numeric-asc'></i>" . __( 'Upload Date (ASC)', 'rtmedia' ) . "</span>";
		$options[ ]     = "<span id='rtm-sort-date-desc' onclick='rtmedia_sort_gallery(this, \"date\", \"desc\")'><i class='rtmicon-sort-numeric-desc'></i>" . __( 'Upload Date (DESC)', 'rtmedia' ) . "</span>";
		$options[ ]     = "<span id='rtm-sort-size-asc' onclick='rtmedia_sort_gallery(this, \"size\", \"asc\")'><i class='rtmicon-sort-amount-asc'></i>" . __( 'Size (ASC)', 'rtmedia' ) . "</span>";
		$options[ ]     = "<span id='rtm-sort-size-desc' onclick='rtmedia_sort_gallery(this, \"size\", \"desc\")'><i class='rtmicon-sort-amount-desc'></i>" . __( 'Size (DESC)', 'rtmedia' ) . "</span>";
		$options[ ]     = "<span id='rtm-sort-alpha-asc' onclick='rtmedia_sort_gallery(this, \"title\", \"asc\")'><i class='rtmicon-sort-alpha-asc'></i>" . __( 'Title (ASC)', 'rtmedia' ) . "</span>";
		$options[ ]     = "<span id='rtm-sort-alpha-desc' onclick='rtmedia_sort_gallery(this, \"title\", \"desc\")'><i class='rtmicon-sort-alpha-desc'></i>" . __( 'Title (DESC)', 'rtmedia' ) . "</span>";
		$options        = apply_filters( 'rtmedia_gallery_sort_actions', $options );
		if ( ! empty( $options ) ){

			$options_start = '<span class="click-nav" id="rtm-media-sort-list">
                <span class="no-js">
                <span class="clicker rtmedia-action-buttons"><i class="rtmicon-sort"></i>' . __( 'Sort', 'rtmedia' ) . '</span>
                <ul class="rtm-options">';
			foreach ( $options as $action ) {
				if ( $action != "" ){
					$option_buttons .= "<li>" . $action . "</li>";
				}
			}

			$options_end = "</ul></span></span>";

			if ( $option_buttons != "" ){
				$output = $options_start . $option_buttons . $options_end;
			}

			if ( $output != "" ){
				echo $output;
			}
		}
	}
        
        function rtmedia_album_sort_option() {
		$option_buttons = "";
		$options        = array();
		$options[ ]     = "<span id='rtm-sort-date-asc' onclick='rtmedia_sort_gallery(this, \"date\", \"asc\")'><i class='rtmicon-sort-numeric-asc'></i>" . __( 'Upload Date (ASC)', 'rtmedia' ) . "</span>";
		$options[ ]     = "<span id='rtm-sort-date-desc' onclick='rtmedia_sort_gallery(this, \"date\", \"desc\")'><i class='rtmicon-sort-numeric-desc'></i>" . __( 'Upload Date (DESC)', 'rtmedia' ) . "</span>";
		$options[ ]     = "<span id='rtm-sort-alpha-asc' onclick='rtmedia_sort_gallery(this, \"title\", \"asc\")'><i class='rtmicon-sort-alpha-asc'></i>" . __( 'Title (ASC)', 'rtmedia' ) . "</span>";
		$options[ ]     = "<span id='rtm-sort-alpha-desc' onclick='rtmedia_sort_gallery(this, \"title\", \"desc\")'><i class='rtmicon-sort-alpha-desc'></i>" . __( 'Title (DESC)', 'rtmedia' ) . "</span>";
		$options        = apply_filters( 'rtmedia_gallery_sort_actions', $options );
		if ( ! empty( $options ) ){

			$options_start = '<span class="click-nav" id="rtm-media-sort-list">
                <span class="no-js">
                <span class="clicker rtmedia-action-buttons"><i class="rtmicon-sort"></i>' . __( 'Sort', 'rtmedia' ) . '</span>
                <ul class="rtm-options">';
			foreach ( $options as $action ) {
				if ( $action != "" ){
					$option_buttons .= "<li>" . $action . "</li>";
				}
			}

			$options_end = "</ul></span></span>";

			if ( $option_buttons != "" ){
				$output = $options_start . $option_buttons . $options_end;
			}

			if ( $output != "" ){
				echo $output;
			}
		}
	}
} 