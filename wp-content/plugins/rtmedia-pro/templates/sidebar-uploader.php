<?php

/*
 * 
 * Template for rtMedia Pro Sidebar uploader widget
 */

if ( is_array ( $tabs ) && count ( $tabs )){ ?>
    <div class="rtmedia-container">
        <div class="rtmedia-uploader no-js">
            <form id="rtmedia-uploader-form-<?php echo $widgetid;?>" method="post" action="upload" enctype="multipart/form-data">
                <?php
                    echo '<div class="rtm-tab-content-wrapper">';
                    echo '<div id="rtm-' . $mode . '-ui-'.$widgetid.'" class="rtm-tab-content">';
                    echo $tabs[ $mode ][ $upload_type ][ 'content' ];
                    echo '<input type="hidden" name="mode" value="' . $mode . '" />';
                    echo $privacy_el;
                    echo '</div>';
                    echo '</div>';
                ?>

                <?php RTMediaProWidgetUploaderView::upload_nonce_generator ( true ); ?>

                <input type="submit" id='rtMedia-start-upload-<?php echo $widgetid;?>' name="rtmedia-upload" value="<?php echo RTMEDIA_UPLOAD_LABEL; ?>" />
            </form>
            <?php echo $redirect_el; ?>
        </div>
    </div>
    <?php
}