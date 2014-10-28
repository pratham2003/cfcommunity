<div class="rtmedia-container">
    <?php do_action ( 'rtmedia_before_media_gallery' ); ?>
    <?php 
        $title = get_rtmedia_gallery_title();
        global $rtmedia_query;
        if( isset($rtmedia_query->is_gallery_shortcode) && $rtmedia_query->is_gallery_shortcode == true) { // if gallery is displayed using gallery shortcode
        ?>            
            <h2><?php _e( 'Media Gallery', 'rtmedia' ); ?></h2>
        <?php }
        else { ?>
            <div id="rtm-gallery-title-container" class="row">
                <h2 class="rtm-gallery-title columns large-5 small-12 medium-5">
                    <?php if( $title ) { echo $title; }
                            else { _e( 'Media Gallery', 'rtmedia' ); } ?>
                </h2>
                <div id="rtm-media-options" class="columns large-7 small-12 medium-7"><?php do_action ( 'rtmedia_media_gallery_actions' ); ?></div>
            </div>
			<div class="clear"></div>
            <div id="rtm-media-gallery-uploader">
                <?php rtmedia_uploader ( array('is_up_shortcode'=> false) ); ?>
            </div>
        <?php }
        ?>
     <?php do_action ( 'rtmedia_after_media_gallery_title' ); ?>
    
    
        <?php if ( have_rtmedia () ) { ?>
            
        <div class="rtmedia-list rtm-no-masonry <?php echo rtmedia_media_gallery_class (); ?>">
            
             <?php 
             $source = '';
             global $rtmedia_query;
             if( isset( $rtmedia_query->media )){
                foreach ( $rtmedia_query->media as $music_media){
                   $url = wp_get_attachment_url( $music_media->media_id );
                   $source .= '<source src="' . $url . '" data-permalink="' . get_rtmedia_permalink( $music_media->id ) . '" title="' . $music_media->media_title . '"/>';
                }
             }
             
            if( $source != "") {

                $html = '<div id="rtmedia-playlist-view">'
                        . '<audio controls="controls" class="rtmp-media-playlist" id="bp_media_audio">';
                $html .= $source;
                $html .= "</audio></div>";
	    }
            echo $html;
            ?>

        </div>

    <?php } else { ?>
        <p>
            <?php 
                $message = __ ( "Oops !! There's no media found for the request !!", "rtmedia" );
                echo apply_filters('rtmedia_no_media_found_message_filter', $message);
                ?>
            </p>
    <?php } ?>
<?php do_action ( 'rtmedia_after_media_gallery' ); ?>

</div>
