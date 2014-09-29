<?php
/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of RTMediaProDownload
 *
 * @author Pushpak
 */
class RTMediaProDownload extends RTMediaUserInteraction {

    function __construct(){

        if($this->check_disable())
            return true;

        $args = array(
            'action' => 'download',
            'label' => __('Download'),
            'privacy' => 20,
            'repeatable' => true,
            'icon_class' => 'rtmicon-download'
            );
        parent::__construct ($args);
        //removed default filter for placement of the button and added new filter
        remove_filter('rtmedia_action_buttons_before_delete', array($this,'button_filter'));
        add_action ( 'rtmedia_actions_without_lightbox', array( $this, 'download_button' ) );
        add_action('rtmedia_action_buttons_after_media', array($this,'download_button'), 10);
    }

    function download_button(){
        if(empty($this->media)){
            $this->init();
        }
        $button = $this->render();
        
        if($button){
            //echo "<li>" . $this->render() . "</li>";
            echo $this->render();
        }
    }
    /*
     * Checks if download button is enabled from the rtemdia settings
     */
    function check_disable(){
         global $rtmedia;
        $options = $rtmedia->options;
        if(! (isset($options['general_enableDownloads']) && ($options['general_enableDownloads'] == "1")))
            return true;
        else
            return false;
    }

    /*
     *
     */
    function before_render() {
        if( $this->media->media_type == "playlist" ) {
            $media_list = $this->get_playlist_media_urls( $this->media->id );
            if( !$media_list ) { // if there are no media available in the current playlist for the current interactor, then dont show the download button
                return false;
            }
        }
        return true;
    }

    /*
     * Renders the download button
     */
    function render() {

        if($this->check_disable())
            return true;

        if(!is_user_logged_in())
            return false;

        $before_render = $this->before_render();
        if($before_render === false )
            return false;

        if($this->is_visible()){
            $link = trailingslashit(get_rtmedia_permalink($this->media->id)).
                            $this->action.'/';
            $disabled = '';
            if(!$this->is_clickable()){
                    $disabled = ' disabled';
            }
        }
        $button =  $button_start = $button_end = $icon = '';
        if(isset( $this->icon_class ) && $this->icon_class != "") {
            $icon = "<i class='rtmicon-download rtmicon-fw'></i>";
        }
        $button_start .= '<form action="' . $link . '" method="get" id="download-media-form" class="standard-form ' . $disabled . '">';
        $button .= '<button class="rtmedia-download-media rtmedia-action-buttons rtmedia-'. $this->action . '">'. $icon . $this->label.'</button>';
        $button = apply_filters( 'rtmedia_' . $this->action . '_button_filter', $button);
        $button_end .= "</form>";

        return $button_start . $button . $button_end;
    }

    /*
     * Processes the download request
     */
    function process() {

	    global $rtmedia_points_media_id;
	    $rtmedia_points_media_id = $this->media->id;
	    do_action("rtmedia_pro_before_download_media",$this);
	    if( $this->media->media_type == "playlist") { //if media is playlist, get the associated medias and promt a .m3u file download.

                $source = $this->get_playlist_media_urls( $this->media->id );

                if( $source ) {
                    $this->download_playlist( $source , $title = $this->media->media_title);
                }

            } else { // for all other medias, promt the actual file download
                $file = wp_get_attachment_url( $this->media->media_id);
                $file = $this->get_path_from_url($file);
                $this->download_file($file);
            }
	    die();
    }

    /*
     * Gets the urls of the medias under the current playlist
     */
    function get_playlist_media_urls ( $playlist_id ) {
        $medialist = maybe_unserialize( get_rtmedia_meta( $playlist_id , 'media_ids' , true ) );
        $source = "";
        if( isset( $medialist ) && $medialist != "") {
            foreach ( $medialist as $key=>$value ) {
                $media_id = rtmedia_media_id( $value );
                $url = wp_get_attachment_url( $media_id );
                if( $url ) {
                    $source .= $url."\n";
                }
            }
        }
        if( $source != "") { return $source; }
        else { return false; }
    }

    /*
     * Prompt .M3U file as playlist download
     * @param file content ( list of urls )
     * @param title of the playlist which will be set as filename
     */
    function download_playlist ( $source , $title ) {

        header('Content-type: text/plain');
        header( 'Content-Disposition: attachment; filename="' . $title . '.m3u"');
        header( 'Expires: 0' );
        header( 'Cache-Control: must-revalidate, post-check=0, pre-check=0' );
        header( 'Pragma: public' );

        echo $source;
        exit;
    }


    /*
     * Gets the path from the URL
     * @params expects the URL as parameter
     * Returns File Path
     */
    function get_path_from_url($file){
        $upload_info = wp_upload_dir();
        if(empty($upload_info['error'])){
            $upload_base_path = $upload_info['basedir'];
            $upload_base_url = $upload_info['baseurl'];

            $file_path = str_replace($upload_base_url,$upload_base_path,$file);
            $path_info= pathinfo($file_path);
            return $file_path;
        }
        return false;
    }

    /*
     * Downloads the file
     * @params expects the PATH of the file to be downloaded as parameter
     */
    function download_file($file) {

        if ( file_exists( $file ) ) {
            header( 'Content-Type: octet-stream' );
            header( 'Content-Disposition: attachment; filename="' . basename( $file ) . '"');
            header( 'Expires: 0' );
            header( 'Cache-Control: must-revalidate, post-check=0, pre-check=0' );
            header( 'Pragma: public' );
            header( 'Content-Length: ' . filesize( $file ) );
            $read_file = readfile( $file );

            if($read_file && $read_file > 0){
                $this->update_download_counts();
            }
            exit;
        }
        return false;
    }

    /*
     * Increments the downloads counter of a media by 1
     */
    function update_download_counts() {

        $media_result = $this->model->get( array('id' => $this->media->id));
        $current_downloads = 0;
        if(isset($media_result) && $media_result != ""){
            $current_downloads = $media_result[0]->downloads;
        }
        $update_count = $this->model->update( array( 'downloads' => $current_downloads+1 ), array( 'id' => $this->media->id ));
        exit;

    }
}
