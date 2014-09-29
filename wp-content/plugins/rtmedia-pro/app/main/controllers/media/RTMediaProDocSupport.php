<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates and open the template in the editor.
 */
/**
 * Description of RTMediaProDocSupport
 *
 * @author Pushpak Patel <pushpak.patel@rtcamp.com>
 */
class RTMediaProDocSupport {

    var $thumbnail = 'app/assets/img/documents-icon.png';

    public function __construct () {

                global $document_extensions;
                $document_extensions = array('txt','doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'pdf', 'xps', 'pages');

                add_filter( 'rtmedia_query_media_type_filter' , array( $this, 'rtmedia_add_document_media_type'), 10, 1);
                if(!defined ('RTMEDIA_DOCUMENT_SLUG')) {
                    define ( 'RTMEDIA_DOCUMENT_SLUG', apply_filters('rtmedia_documents_slug','document') );
                }
                add_filter('rtmedia_allowed_types', array( $this, "add_allowed_types"), 10,1);
                add_filter('rtmedia_single_content_filter', array($this, 'rtmedia_document_content_filter'), 10,2);
                add_filter('rtmedia_filter_featured_checkbox', array($this, 'rtmedia_hide_featured_checkbox_for_documents'), 10,2);
                add_filter('rtmedia_single_activity_filter', array( $this, 'rtmedia_document_single_activity_filter'), 10, 3);
    }

    /*
     * Filters the single media content for the activity
     */
    function rtmedia_document_single_activity_filter ( $html, $media, $status ) {
        if( isset( $media->media_type ) && $media->media_type == 'document') {
            $html = '<a href ="' . get_rtmedia_permalink ( $media->id ) . '">';
            //$src = RTMEDIA_PRO_URL . $this->thumbnail;
            
            // use rtmedia_image function for image src
            $src = rtmedia_image( 'rt_media_activity_image', $media->id, false );
            $html .='<img src="' .  $src . '" height="70" width="70" />';
            $html .= "</a>";
        }
        return $html;
    }

    /*
     * Function to hide the "Featured" enable/disable button for the Document media type
     * @params accepts the current "featured checkbox" markup
     * @params accepts the current "media type"
     * Returns the filtered "Featured" button markup if media type is "Document"
     */
    function rtmedia_hide_featured_checkbox_for_documents( $featured_checkbox , $media_type) {

        if( isset( $media_type ) && $media_type == 'document'){
            $featured_checkbox = "--"
                    . '<input type="hidden" name="rtmedia-options[allowedTypes_document_featured]" value="0">';
        }
        return $featured_checkbox;
    }

    /*
     * Function to filter the Single Media Content for the Document media type
     * @param accepts the current "html" markup of the media single.
     * @param accepts the current rtmedia_media object
     * Returns the filtered markeup for the media single if media type is "Document"
     */
    function rtmedia_document_content_filter ( $html, $rtmedia_media ) {
        $url = wp_get_attachment_url (rtmedia_media_id( $rtmedia_media->id ));
        $url = urldecode($url);
		global $rtmedia;
		$options = $rtmedia->options;
		if( is_ssl() ){
			$protocol_type = 'https';
		} else {
			$protocol_type = 'http';
		}
		if( is_rtmedia_document() ) {
			if( isset( $options['general_enable_google_docs'] ) && $options['general_enable_google_docs'] != "0" ) {
				$html = '<iframe src="' . $protocol_type . '://docs.google.com/viewer?url=' . $url . '&embedded=true" class="rtm-google-doc-container"></iframe>';
			}else{
				$html = '<span class="rtm-font-color-ccc">'. apply_filters( 'rtmedia_disable_google_doc_service', __( 'Google docs service has been disabled. This file can\'t be loaded.', 'rtmedia' ) ) .'</span>';
			}
		}
        return $html;
    }

    /*
     * Function to add Document media type into the media query
     * @params accepts the current media_type array
     * Returns the filtered media_type array
     */
    function rtmedia_add_document_media_type ( $media_type ) {
        if( isset( $media_type['value'] ) && $media_type['value'] != "" ) {
            $media_type['value'][] = 'document';
        }
        return $media_type;
    }

    /**
     * filters the allowed media types and adds "documents" as allowed media type.
     * @params accepts the array of currently allowed media types
     */
    function add_allowed_types ( $allowed_types ) {

        global $document_extensions;
        $document_type = array(
        'document' => array(
            'name' => 'document',
            'plural' => 'documents',
            'label' => __('Document', 'rtmedia'),
            'plural_label' => __('Documents', 'rtmedia'),
            'extn' => $document_extensions,
            'thumbnail' => RTMEDIA_PRO_URL . $this->thumbnail ,
            // For extension wise custom thumbnails
//            'ext_thumb' => array(
//                'pdf' => 'thumbnail path'
//            ) ,
	    'settings_visibility' => true)
        );

        if (!defined('RTMEDIA_DOCUMENT_PLURAL_LABEL')) {
                define('RTMEDIA_DOCUMENT_PLURAL_LABEL', $document_type['document']['plural_label']);
        }
        if (!defined('RTMEDIA_DOCUMENT_LABEL')) {
                define('RTMEDIA_DOCUMENT_LABEL', $document_type['document']['label']);
        }

        $allowed_types = array_merge ( $allowed_types , $document_type );

        return $allowed_types;

    }

}
