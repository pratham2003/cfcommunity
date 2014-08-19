<?php

/**
 * Description of RTMediaProWidgetUploaderView
 *
 * @author Pushpak
 */
class RTMediaProWidgetUploaderView{

    function __construct () {
    }

    static function upload_nonce_generator ( $echo = true, $only_nonce = false ) {

        if ( $echo ) {
            wp_nonce_field ( 'rtmedia_upload_nonce', 'rtmedia_upload_nonce' );
        } else {
            if ( $only_nonce )
                return wp_create_nonce ( 'rtmedia_upload_nonce' );
            $token = array(
                'action' => 'rtmedia_upload_nonce',
                'nonce' => wp_create_nonce ( 'rtmedia_upload_nonce' )
            );

            return json_encode ( $token );
        }
    }

    /**
     * Render the uploader shortcode and attach the uploader panel
     *
     * @param type array('template_name' => 'sidebar-uploader','widgetid' => '','context' => '','context_id' => '','album_id' => '','privacy' => '', 'redirect' => '');
     */
    public function render ( $arguments) {

        extract($arguments, EXTR_SKIP );

        if(isset($album_id) && $album_id!=""){
            $model= new RTMediaModel();
            $media_album = $model->get(array('id'=> $album_id));
            if(!$media_album) {
                $album_id = RTMediaAlbum::get_default ();
            }
            $album_el = "<input type='hidden' name='album_id' id='rtmedia-current-album-".$widgetid."' value='".$album_id."'/>";
        }
        else{
            $album_el = '<select name="album_id" id="album-list-'.$widgetid.'" class="rtmedia-user-album-list">' . rtmedia_user_album_list (true) . '</select>';
        }
        $context_el = "";
        if(isset($context) && $context!=""){
            $context_el = "<input type='hidden' value='".$context."' name='context'/><input type='hidden' name='context_id' value='".$context_id."'/>";
        }
        if(isset($privacy) && $privacy==""){
            $provacyObj = new RTMediaPrivacy();
            $privacy_el = $provacyObj->select_privacy_ui(false);
        }
        else{
            $privacy_el = "<input type='hidden' name='privacy' value='".$privacy."'/>";
        }
        $redirect_el ="";
        if($redirect=='true')
            $redirect_el = "<input type='hidden' name='redirect' value='true' id='rt_upload_hf_redirect_".$widgetid."'/>";
        $tabs = array(
            'file_upload' => array(
                'default' => array( 'title' => __ ( 'File Upload', 'rtmedia' ), 'content' => '<div id="rtmedia-upload-container-'.$widgetid.'" ><div id="drag-drop-area-'.$widgetid.'" class="drag-drop widget-drag-drop">' . $album_el . '<input id="rtMedia-upload-button-'.$widgetid.'" value="' . __ ( "Select", "rtmedia" ) . '" type="button" class="rtmedia-upload-input rtmedia-file" />'.$context_el.  '</div><table id="rtMedia-queue-list-'.$widgetid.'" class="rtmp-uploaded-file rtMedia-queue-list"><tbody></tbody></table></div>' )),
//			'file_upload' => array( 'title' => __('File Upload','rtmedia'), 'content' => '<div id="rtmedia-uploader"><p>Your browser does not have HTML5 support.</p></div>'),
            'link_input' => array( 'title' => __ ( 'Insert from URL', 'rtmedia' ), 'content' => '<input type="url" name="bp-media-url" class="rtmedia-upload-input rtmedia-url" />' ),
        );
        $tabs = apply_filters ( 'rtmedia_upload_tabs', $tabs );

        $mode = (isset ( $_GET[ 'mode' ] ) && array_key_exists ( $_GET[ 'mode' ], $tabs )) ? $_GET[ 'mode' ] : 'file_upload';
        $upload_type = 'default';

        $uploadHelper = new RTMediaUploadHelper();
        include $this->locate_template ( $template_name );
    }

    public function register_scripts ($widget_id, $arguments) {
        wp_enqueue_script ( 'plupload-all' );
        wp_enqueue_script ( 'rtmedia-widget-backbone', RTMEDIA_PRO_URL.'app/assets/js/widget_uploader.js', array( 'plupload', 'backbone' ), false, false );
        $url = trailingslashit ( $_SERVER[ "REQUEST_URI" ] );
        if ( strpos ( $url, "/media" ) !== false ) {
            $url_array = explode ( "/media", $url );
            $url = trailingslashit ( $url_array[ 0 ] ) . "upload/";
        } else {
            $url = trailingslashit ( $url ) . "upload/";
        }

        $params = array(
            'url' => $url,
            'runtimes' => 'html5,silverlight,flash,html4',
            'browse_button' => 'rtMedia-upload-button-'.$widget_id,
            'container' => 'rtmedia-upload-container-'.$widget_id,
            'drop_element' => 'drag-drop-area-'.$widget_id,
            'filters' => apply_filters ( 'rtmedia_plupload_files_filter', array( array( 'title' => "Media Files", 'extensions' => get_rtmedia_allowed_upload_type () ) ) ),
            'max_file_size' => min ( array( ini_get ( 'upload_max_filesize' ), ini_get ( 'post_max_size' ) ) ),
            'multipart' => true,
            'urlstream_upload' => true,
            'flash_swf_url' => includes_url ( 'js/plupload/plupload.flash.swf' ),
            'silverlight_xap_url' => includes_url ( 'js/plupload/plupload.silverlight.xap' ),
            'file_data_name' => 'rtmedia_file', // key passed to $_FILE.
            'multi_selection' => true,
            'multipart_params' => apply_filters ( 'rtmedia-multi-params', array( 'redirect' => 'no', 'action' => 'wp_handle_upload', '_wp_http_referer' => $_SERVER[ 'REQUEST_URI' ], 'mode' => 'file_upload', 'rtmedia_upload_nonce' => RTMediaProWidgetUploaderView::upload_nonce_generator ( false, true ) ) ),
	    'max_file_size_msg' => apply_filters("rtmedia_plupload_file_size_msg",min ( array( ini_get ( 'upload_max_filesize' ), ini_get ( 'post_max_size' ) ) ))
        );
        if ( wp_is_mobile () )
            $params[ 'multi_selection' ] = false;
        $params = apply_filters("rtmedia_modify_upload_params",$params);
	global $rtmedia;
	$allowed_media_type = $rtmedia->allowed_types;

	if( isset( $arguments['media_type'] ) && $arguments['media_type'] != "" && isset($allowed_media_type[$arguments['media_type']])) {
	    $params['filters'][0]['extensions'] = implode(',', $allowed_media_type[$arguments['media_type']]['extn']);
	}
        wp_localize_script ( 'rtmedia-widget-backbone', 'rtMedia_widget_plupload_config_'.$widget_id, $params );
        wp_localize_script ( 'rtmedia-widget-backbone', 'rMedia_loading_file', admin_url ( "/images/loading.gif" ) );
    }

    /**
     * Template Locator
     *
     * @param type $template
     * @return string
     */
    protected function locate_template ( $template ) {
        $located = '';

        $template_name = $template . '.php';

        if ( ! $template_name )
            $located = false;
        if ( file_exists ( RTMEDIA_PRO_PATH . 'templates/' . $template_name ) ) {
            $located = RTMEDIA_PRO_PATH . 'templates/' . $template_name;
        }
        return $located;
    }
}