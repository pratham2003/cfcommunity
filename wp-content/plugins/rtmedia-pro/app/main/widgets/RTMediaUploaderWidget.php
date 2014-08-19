<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of RTMediaSideBarUploader
 *
 * @author ritz
 */
class RTMediaUploaderWidget extends WP_Widget {

    function __construct() {
        $widget_ops = array('classname' => 'RTMediaUploaderWidget', 'description' => __("rtMedia Pro Sidebar uploader widget", 'rt-media'));
        parent::__construct('RTMediaUploaderWidget', __('rtMedia Pro Sidebar Uploader Widget', 'rt-media'), $widget_ops);
    }

    function widget($args, $instance) {
        extract($args);
        if ( is_user_logged_in() ) {
		echo $before_widget;
		if(!empty($instance['title'])) {
		    echo $before_title . $instance['title'] . $after_title;
		}
		$allow_upload = apply_filters( 'rtmedia_allow_uploader_view', true, 'uploader_widget' );
		if( $allow_upload ) {
		    $widgetid = str_replace('-', "_", $args['widget_id']);
		    $media_type = ( isset($instance['media_type']) ? ( $instance['media_type'] ) : "all" );
		    $rtmedia_upload_view = new RTMediaProWidgetUploaderView();
		    $defaults = array('template_name' => 'sidebar-uploader','widgetid' => '','context' => '','context_id' => '','album_id' => '','privacy' => '', 'redirect' => 'false','media_type' => "all");
		    $arguments = array('template_name' => 'sidebar-uploader', 'widgetid' => $widgetid, 'context' => $instance['context'], 'context_id' => $instance['context_id'], 'album_id' => $instance['album'], 'privacy' => $instance['privacy'], 'redirect' => $instance['redirect'], 'media_type' => $media_type);
		    $rtmedia_upload_view->render(wp_parse_args($arguments,$defaults));
		    $rtmedia_upload_view->register_scripts($widgetid,wp_parse_args($arguments,$defaults));
		} else {
		    echo "<div class='rtmedia-upload-not-allowed'>" . apply_filters( 'rtmedia_upload_not_allowed_message', __('You are not allowed to upload/attach media.','rtmedia'), 'uploader_widget' ) . "</div>";
		}
		echo $after_widget;
	}
    }

    function update($new_instance, $old_instance) {
        $instance = $old_instance;
        $instance['context_id'] = (int) strip_tags($new_instance['context_id']);
        $instance['album'] = strip_tags($new_instance['album']);
        $instance['context'] = strip_tags($new_instance['context']);
        $instance['privacy'] = strip_tags($new_instance['privacy']);
        $instance['title'] = strip_tags($new_instance['title']);
        $instance['redirect'] = strip_tags($new_instance['redirect']);
        $instance['media_type'] = strip_tags($new_instance['media_type']);
        return $instance;
    }

    function form($instance) {
     $context_id = isset($instance['context_id']) ? esc_attr($instance['context_id']) : '';
     $album = isset($instance['album']) ? esc_attr($instance['album']) : '';
     $context = isset($instance['context']) ? esc_attr($instance['context']) : '';
     $media_type = isset($instance['media_type']) ? esc_attr($instance['media_type']) : 'all';
     $privacy = isset($instance['privacy']) ? esc_attr($instance['privacy']) : '';
     $title = isset($instance['title']) ? esc_attr($instance['title']) : '';
     $redirect = isset($instance['redirect']) ? esc_attr($instance['redirect']) : 'false';

     $post_types = get_post_types(array('show_ui'=>true));
     $context_option = "";
    if(is_array($post_types) && $post_types != "")
	foreach($post_types as $post_type) {
	    if($post_type == "attachment")
		continue;
	    $context_option.=  "<option value='$post_type'>".  ucfirst(str_replace("_", " ", $post_type))."</option>";
	}
    $context_option .= "<option value='profile'>Profile</option><option value='group'>Group</option>";

    $media_type_option = "";
    global $rtmedia;
    foreach ( $rtmedia->allowed_types as $value ) {
	if( $value['name'] == "playlist" ) {
	    continue;
	}
	$media_type_option.= "<option value='$value[name]'>$value[label]</option>";
    }
    ?>
    <p>
        <label for="<?php echo $this->get_field_id( 'title' );?>"><?php _e('Title:', 'rtmedia-pro'); ?></label>
        <input type="text" class="widefat" name="<?php echo $this->get_field_name( 'title' );?>" id="<?php echo $this->get_field_id( 'title' );?>" value="<?php echo $title ;?>"/>
    </p>
    <p>
        <label for="<?php echo $this->get_field_id( 'context' );?>"><?php _e('Context:', 'rtmedia-pro'); ?></label>
        <select name="<?php echo $this->get_field_name( 'context' );?>" id="<?php echo $this->get_field_id( 'context' );?>" class="context_option" data-value="<?php echo $context ;?>" >
            <option selected='selected' value="">Default</option>
            <?php echo $context_option; ?>
        </select>
    </p>
    <p>
        <label for="<?php echo $this->get_field_id( 'context_id' );?>"><?php _e('Context ID:', 'rtmedia-pro'); ?></label>
        <input type="number" placeholder="leave blank for default" name="<?php echo $this->get_field_name( 'context_id' );?>" id="<?php echo $this->get_field_id( 'context_id' );?>" <?php if($context_id != "" && $context_id != "0") echo "value='$context_id'"  ?>>
    </p>
    <p>
        <label for="<?php echo $this->get_field_id( 'media_type' );?>"><?php _e('Media Type:', 'rtmedia-pro'); ?></label>
        <select name="<?php echo $this->get_field_name( 'media_type' );?>" id="<?php echo $this->get_field_id( 'media_type' );?>" class="context_option" data-value="<?php echo $media_type ;?>" >
            <option value='all' selected='selected'>All</option>
            <?php echo $media_type_option; ?>
        </select>
    </p>
    <p>
        <label for="<?php echo $this->get_field_id( 'album' );?>"><?php _e('Album:', 'rtmedia-pro'); ?></label>
        <select name="<?php echo $this->get_field_name( 'album' );?>" id="<?php echo $this->get_field_id( 'album' );?>" class="album_list" data-value="<?php echo $album ;?>">
            <option selected='selected' value="">Let User Select</option>
            <?php  echo rtmedia_user_album_list(); ?>
        </select>
    </p>
    <p>
        <label for="<?php echo $this->get_field_id( 'privacy' );?>"><?php _e('Privacy:', 'rtmedia-pro'); ?></label>
        <select id="<?php echo $this->get_field_id( 'privacy' );?>" name="<?php echo $this->get_field_name( 'privacy' );?>" class="privacy" data-value="<?php echo $privacy; ?>">
            <option selected='selected' value="">Let User Select</option>
            <?php
		$rtmediaprivacy = new RTMediaPrivacy(false);
		$test = $rtmediaprivacy->select_privacy_ui(false);
                echo strip_tags($test, '<option>');
            ?>
        </select>
    </p>
    <p>
        <label for=''><?php _e('Redirect:', 'rtmedia-pro'); ?></label>
        <input type="radio" value="true" name="<?php echo $this->get_field_name( 'redirect' );?>" id="<?php echo $this->get_field_id( 'redirect_true' );?>" <?php if($redirect=='true') echo "checked='checked'"; ?> /><label for="<?php echo $this->get_field_id( 'redirect_true' );?>"> <?php _e('True', 'rtmedia');?></label>
        <input type="radio" value="false" name="<?php echo $this->get_field_name( 'redirect' );?>" id="<?php echo $this->get_field_id( 'redirect_false' );?>" <?php if($redirect=="false") echo "checked='checked'"; ?>/><label for="<?php echo $this->get_field_id( 'redirect_false' );?>"> <?php _e('False', 'rtmedia');?></label>

    </p>
    <script type="text/javascript">
         jQuery(document).ready(function() {
              jQuery("select").each(function(){
                  jQuery(this).val(jQuery(this).data('value'));
              });
        });
    </script>

<?php
    }

}