<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates and open the template in the editor.
 *
 * Description of RTMediaProOtherTypeSupport
 *
 * @author Pushpak Patel <pushpak.patel@rtcamp.com>
 */
class RTMediaProOtherTypeSupport {

    var $thumbnail = 'app/assets/img/other-types-icon.png';
    var $no_preview_img = 'app/assets/img/nopreview.png';

    public function __construct () {

                add_filter( 'rtmedia_query_media_type_filter' , array( $this, 'rtmedia_add_other_media_type'), 10, 1);
                if(!defined ('RTMEDIA_OTHER_SLUG')) {
                    define ( 'RTMEDIA_OTHER_SLUG', apply_filters('rtmedia_other_type_slug','other') );
                }
                add_filter('rtmedia_allowed_types', array( $this, "add_other_allowed_types"), 20,1);
                add_filter('rtmedia_single_content_filter', array($this, 'rtmedia_other_content_filter'), 10,2);
                add_filter('rtmedia_filter_featured_checkbox', array($this, 'rtmedia_hide_featured_checkbox_for_other_types'), 10,2);
                add_filter('rtmedia_filter_allow_upload_checkbox', array($this, 'rtmedia_filter_allow_upload_for_other_types'), 10,3);
                // add notice after the Media Type settings table

                add_filter('rtmedia_type_settings_filter_extension', array( $this, 'rtmedia_type_settings_filter_other_extension'), 10, 2);
                add_filter('rtmedia_single_activity_filter', array( $this, 'rtmedia_other_media_single_activity_filter'), 10, 3);
                //add_action('rtmedia_before_uploader', array( $this, 'rtmedia_after_uploader_message'), 10);
                add_filter('rtmedia_pro_options_save_settings', array( $this, 'rtmedia_pro_save_other_extensions'), 10, 1);
    }

    /*
     * Filter the extensions for Other file types before saving
     * @params accepts the settings options to be saved
     * Returns the filtered settings options
     */
    function rtmedia_pro_save_other_extensions ( $options ) {

        if(isset ($options['rtmedia_other_file_extensions']) && $options['rtmedia_other_file_extensions'] != "") {

            $extensions = explode(",", trim($options[ "rtmedia_other_file_extensions"]));
            $new_extn = array();
            foreach( $extensions as $extn ) {
              $extn = preg_replace('/[^A-Za-z0-9]/', '', $extn);;
              if( $extn != "" && $this->rtm_is_new_extension($extn))
                  $new_extn[] = $extn;
            }
            if( $new_extn != "" ) {
                $options['rtmedia_other_file_extensions'] = implode(',', $new_extn);
            } else {
                $options['allowedTypes_other_enabled'] = 0; //disable the other media type
            }
        }
        return $options;
    }
    /*
     * Checks if the provided extension already exists
     * @params extension to be checked
     * Returns boolean
     */
    function rtm_is_new_extension ($extn) {
        global $rtmedia;
        if( isset( $rtmedia->allowed_types )) {
            foreach( $rtmedia->allowed_types as $allowed_types ) {
                if( isset($allowed_types['name']) && $allowed_types['name'] != 'other' && $allowed_types['extn'] != "" && in_array( $extn, $allowed_types['extn'])){
                    return false;
                }
            }
        }
        return true;
    }

    /*
     * Displays a message before the uploader under the "Others" tab with list of new file extensions allowed for upload
     */
    function rtmedia_after_uploader_message () {
        global $rtmedia_query, $rtmedia;
        if( isset( $rtmedia_query->media_query['media_type'] ) && !is_array( $rtmedia_query->media_query['media_type']) && $rtmedia_query->media_query['media_type'] == "other" && isset($rtmedia->options['allowedTypes_other_enabled']) && $rtmedia->options['allowedTypes_other_enabled']==1) {
            if( isset($rtmedia->options[ "rtmedia_other_file_extensions" ]) && $rtmedia->options[ "rtmedia_other_file_extensions" ] != "") {
                echo "<p>" . __('You can also upload file with following formats  : ', 'rtmedia') . str_replace(",", ', ', $rtmedia->options[ "rtmedia_other_file_extensions" ]) . "</p>";
            }
        }
    }

    function rtmedia_filter_allow_upload_for_other_types( $allow_upload_checkbox , $media_type, $args ) {
        if( isset( $media_type ) && $media_type == 'other') {
            $args['class'] = array('rtm_allow_other_upload'); // this class is used to disable the extension text-area in js
            $allow_upload_checkbox = RTMediaFormHandler::checkbox( $args, $echo = false );
        }
        return $allow_upload_checkbox;
    }

    /*
     * Function to filter the markup for the extensions and show a textarea for user to enter the required extensions for the other media types.
     * @params accepts the current media type
     * @params accepts the current markup for the extensions
     * Returns the filtered markeup
     */
    function rtmedia_type_settings_filter_other_extension ( $extensions, $media_type ) {
        if( isset( $media_type ) && $media_type == 'other') {
            global $rtmedia;
            $value = '';
            if( isset($rtmedia->options[ "rtmedia_other_file_extensions" ]) && $rtmedia->options[ "rtmedia_other_file_extensions" ] != "")
                $value = $rtmedia->options[ "rtmedia_other_file_extensions" ];
            $args = array(
			'id' => 'rtm_other_extensions',
			'key' => 'rtmedia_other_file_extensions',
			'value' => $value ,
		    );
            $extensions = RTMediaFormHandler::textarea( $args, $echo = false).' <span data-tooltip class="has-tip" title="Provide comma seperated values for other file types.</br>Allowing other file types for upload could be dangerous." style="font-size: 13px;"><i class="rtmicon-info-circle"></i></span>';
        }
        return $extensions;
    }

    /*
     * Function to hide the "Featured" enable/disable button for the Other media type
     * @params accepts the current "featured checkbox" markup
     * @params accepts the current "media type"
     * Returns the filtered "Featured" button markup if media type is "Other"
     */
    function rtmedia_hide_featured_checkbox_for_other_types( $featured_checkbox , $media_type) {

        if( isset( $media_type ) && $media_type == 'other'){
            $featured_checkbox = "--"
                    . '<input type="hidden" name="rtmedia-options[allowedTypes_other_featured]" value="0">';
        }
        return $featured_checkbox;
    }

    /**
     * Function to filter the Single Media Content for the Document media type
     * @params accepts the current html markup of single media
     * @params accepts the rtmedia_media object
     * Returns the filtered markup for the single medias
     */
    function rtmedia_other_content_filter ( $html, $rtmedia_media ) {
        if( is_rtmedia_other_file_type() ) {
            $html = '<img src="'. RTMEDIA_PRO_URL . $this->no_preview_img . '" alt="'. __('No preview available') .'">';
        }
        return $html;
    }

    /**
     * Function to add Other media type into the media query.
     * @params accepts the array of current media types
     * Returns the filtered media_type array
     */
    function rtmedia_add_other_media_type ( $media_type ) {
        if( isset( $media_type['value'] ) && $media_type['value'] != "" ) {
            $media_type['value'][] = 'other';
        }
        return $media_type;
    }

    /**
     * filters the allowed media types and adds "documents" as allowed media type.
     * @params accepts the array of currently allowed media types
     */
    function add_other_allowed_types ( $allowed_types ) {

        global $rtmedia;
        $extensions = array('');
        if( isset( $rtmedia->options[ "rtmedia_other_file_extensions" ]) && $rtmedia->options[ "rtmedia_other_file_extensions" ] != "") {
            $extensions = explode(",", $rtmedia->options[ "rtmedia_other_file_extensions"]);
        }
        $other_type = array(
        'other' => array(
            'name' => 'other',
            'plural' => 'others',
            'label' => __('Other', 'rtmedia'),
            'plural_label' => __('Others', 'rtmedia'),
            'extn' => $extensions,
            'thumbnail' => RTMEDIA_PRO_URL . $this->thumbnail,
	    'settings_visibility' => true)
        );

        if (!defined('RTMEDIA_OTHER_PLURAL_LABEL')) {
                define('RTMEDIA_OTHER_PLURAL_LABEL', $other_type['other']['plural_label']);
        }
        if (!defined('RTMEDIA_OTHER_LABEL')) {
                define('RTMEDIA_OTHER_LABEL', $other_type['other']['label']);
        }
        $allowed_types = array_merge ( $allowed_types , $other_type );
        return $allowed_types;
    }

    /*
     * Filters the single media content for the activity
     */
    function rtmedia_other_media_single_activity_filter ( $html, $media, $status ) {
        if( isset( $media->media_type ) && $media->media_type == 'other') {
            $html = '<a href ="' . get_rtmedia_permalink ( $media->id ) . '">';
            //$src = RTMEDIA_PRO_URL . $this->thumbnail;
            
            // use rtmedia_image function for image src
            $src = rtmedia_image( 'rt_media_activity_image', $media->id, false );
            $html .='<img src="' .  $src . '" height="70" width="70" />';
            $html .= "</a>";
        }
        return $html;
    }

}
