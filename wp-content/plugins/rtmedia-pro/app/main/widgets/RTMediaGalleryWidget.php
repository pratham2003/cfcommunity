<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of RTMediaMemberGalleryWidget
 *
 * @author ritz
 */

class RTMediaGalleryWidget extends WP_Widget {
    var $rtmedia_wd_time = "";
    
    function __construct() {
        $widget_ops = array('classname' => 'RTMediaGalleryWidget', 'description' => __("rtMedia Pro Gallery widget", 'rt-media'));
        parent::__construct('RTMediaGalleryWidget', __('rtMedia Pro Gallery Widget', 'rt-media'), $widget_ops);
    }

    function where_query_wdtime( $where, $table_name ) {
        global $wpdb;
        $join_table = $wpdb->posts;
        $end_time = "tomorrow";
        $all_flag = false;
        
        switch ($this->rtmedia_wd_time) {
            case "today":
                $start_time = "yesterday";
                break;
            case "this_week":
                $start_time = "sunday last week";
                break;
            case "this_month":
                $start_time = "last day of last month";
                break;
            default: 
                $all_flag = true;
                break;
        }
        
        if(!$all_flag) {
            $lastMonth = strtotime('last month');
            $start_date = date('Y-m-d', strtotime($start_time));
            $start_date.= " 23:59:59";
            $end_date = date('Y-m-d', strtotime($end_time));
            $end_date.= " 00:00:00";
            $where .= " AND ( {$table_name}.upload_date > '$start_date' and {$table_name}.upload_date < '$end_date' ) ";
            return $where;
        }
        
        return $where;
    }
    
    // Getting meta_value and meta_key for view count
    function rtmedia_select_query_view_count_column( $select, $table_name ) {
        $rtmedia_meta = new RTMediaMeta();
        // Meta table name
        $select_table = $rtmedia_meta->model->table_name;
        return $select . ', ' . $select_table . '.meta_key, ' . $select_table . '.meta_value ';
    }
    
    // Setting order for views
    function rtmedia_select_query_view_count_order( $orderby, $table_name ) {
        $rtmedia_meta = new RTMediaMeta();
        $select_table = $rtmedia_meta->model->table_name;
        $orderby =  'ORDER BY ' . $select_table . '.meta_value DESC';
        return $orderby;
    }
    
    // Function for join query with rtmedia_interaction table to get view count
    function join_query_rtmedia_interaction_view_count( $join, $table_name ) {
        $rtmedia_meta = new RTMediaMeta();
        $join_table = $rtmedia_meta->model->table_name;
        $join .= " LEFT JOIN {$join_table} ON ( {$join_table}.media_id = {$table_name}.id AND ( {$join_table}.meta_key = 'view' ) ) ";
        return $join;
    }

    function widget($args, $instance) {
        extract($args);
        echo $before_widget;

        $wdType = isset($instance['wdType']) ? esc_attr($instance['wdType']) : 'recent';
        $wdTime = isset($instance['wdTime']) ? esc_attr($instance['wdTime']) : 'all';

        if($wdTime != "all") {
            $default_title = ucfirst(str_replace("_", " ", $wdTime))."'s  ".ucfirst(str_replace("_", " ", $wdType));
        } else {
            $default_title = ucfirst(str_replace("_", " ", $wdType));
        }
       // $title = apply_filters('widget_title', empty($instance['title']) ? __( $default_title . ' Media', 'rt-media') : $instance['title'], $instance, $this->id_base);
        $title = apply_filters('widget_title', $instance['title']); // Removed default title when left blank 
        $allow = array();
        $allowed = array();
        if (empty($instance['number']) || !$number = absint($instance['number'])) {
            $number = 10;
        }

        if (isset($instance['allow_all']) && (bool) $instance['allow_all'] === true)
            $allow[] = 'all';
        if (isset($instance['allow_image']) && (bool) $instance['allow_image'] === true)
            $allow[] = 'photo';
        if (isset($instance['allow_audio']) && (bool) $instance['allow_audio'] === true)
            $allow[] = 'music';
        if (isset($instance['allow_video']) && (bool) $instance['allow_video'] === true)
            $allow[] = 'video';
        if (empty($instance['thumbnail_width']) || !$thumbnail_width = absint($instance['thumbnail_width'])) {
            $thumbnail_width = 90;
        }
        if (empty($instance['thumbnail_height']) || !$thumbnail_height = absint($instance['thumbnail_height'])) {
            $thumbnail_height = 90;
        }

        global $rtmedia;
        $allowed = $allow;
        if( !empty( $title ) ) { // if title provided, show title
            echo $before_title . $title . $after_title;
        }

        $strings = array(
            'all' => __('All', 'buddypress-media'),
            'music' => __('Music', 'buddypress-media'),
            'video' => __('Videos', 'buddypress-media'),
            'photo' => __('Photos', 'buddypress-media')
        );
        $widgetid = $args['widget_id'];
        if (!is_array($allowed) || count($allowed) < 1) {
            echo '<p>';
            printf(
                    __(
                            'Please configure this widget
									<a href="%s" target="_blank"
									title="Configure BuddyPress Media Widget">
									here</a>.', 'rtPanel'
                    ), admin_url('/widgets.php')
            );
            echo '</p>';
        } else {
            if (count($allowed) > 3) {
                unset($allowed['all']);
            }

            $allowMimeType = array();
            ?>
            <div id="<?php echo $wdType; ?>-media-tabs" class="media-tabs-container media-tabs-container-tabs">
                <ul><?php
                    $active_counter = 0;
                    foreach ($allowed as $type) {
                        $active_counter++;

                        if ($type != 'all') {
                            array_push($allowMimeType, $type);
                        }
                        ?><li <?php if ($active_counter == 1) echo "class='active-tab'"; ?>><a href="#<?php echo $wdType; ?>-media-tabs-<?php echo $type; ?>-<?php echo $widgetid; ?>" onclick="return change_gallery_tabs(this, '<?php echo $wdType; ?>', '<?php echo $type; ?>', '<?php echo $widgetid; ?>');"><?php echo apply_filters( 'rtm_gallery_widget_media_type_title', $strings[$type], $type, $wdType ); ?></a></li><?php
                    }
                    $active_counter = 0;
                    ?></ul>
                <?php
                foreach ($allowed as $type) {
                    $active_counter++;
                    ?>
                    <div id="<?php echo $wdType; ?>-media-tabs-<?php echo $type; ?>-<?php echo $widgetid; ?>" class="rt-media-tab-panel <?php if ($active_counter == 1) echo "active-div"; ?>">
                        <?php
                        $columns = array();
                        if ($type != 'all') {
                            $columns["media_type"] = $type;
                        } else {
                            $columns["media_type"] = array("music", "photo", "video");
                        }
                        $this->rtmedia_wd_time = $wdTime;
                        //$columns["privacy"] = array("0");
                        $offset = 0;
                        $orderby = 'media_id DESC';
                        if ($wdType == "most_rated") {
                            $orderby = 'ratings_count DESC, ratings_total DESC';
                        } else if ($wdType == "popular") {
                            $orderby = 'likes DESC';
                        } else if ($wdType == "views") {
                            // Filter for join with wp_rt_rtm_media_interaction table
                            add_filter("rtmedia-model-join-query",array($this,"join_query_rtmedia_interaction_view_count"), 20, 2);
                            // Select meta_key and meta_value from meta table
                            add_filter( 'rtmedia-model-select-query', array( $this, 'rtmedia_select_query_view_count_column' ), 20, 2 );
                            // Assigning order according to the view count
                            add_filter( 'rtmedia-model-order-by-query', array( $this, 'rtmedia_select_query_view_count_order' ), 20, 2 );
                        }
                        
                        global $rtmediamodel;
                        $rtmediamodel = new RTMediaModel();
                        add_filter("rtmedia-model-where-query",array($this,"where_query_wdtime"), 20, 2);
                        
                        add_filter('rtmedia-model-where-query', array( $this , 'rtmedia_query_where_filter'), 10 ,3 );
                        $bp_media_widget_query = $rtmediamodel->get($columns, $offset, $number, $orderby);
                        remove_filter('rtmedia-model-where-query', array( $this , 'rtmedia_query_where_filter'), 10 ,3 );
                        remove_filter("rtmedia-model-where-query",array($this,"where_query_wdtime"), 20, 2);
                        remove_filter("rtmedia-model-join-query",array($this,"join_query_rtmedia_interaction_view_count"), 20, 2);
                        remove_filter( 'rtmedia-model-select-query', array( $this, 'rtmedia_select_query_view_count_column' ), 20, 2 );
                        remove_filter( 'rtmedia-model-order-by-query', array( $this, 'rtmedia_select_query_view_count_order' ), 20, 2 );
                        //var_dump($bp_media_widget_query);

                        if (sizeof($bp_media_widget_query) > 0) {
                            ?>
                            <ul class="widget-item-listing">
                                <?php foreach ($bp_media_widget_query as $rt_media_gallery) { ?>
                                    <li class="rtmedia-list-item">
                                    <?php do_action( "rtmedia_gallery_widget_before_media", $rt_media_gallery );?>
                                    <a href ="<?php echo get_rtmedia_permalink($rt_media_gallery->id); ?>" title="<?php echo $rt_media_gallery->media_title; ?>">
                                            <div class="rtmedia-item-thumbnail">
                                                <img src="<?php rtmedia_image("rt_media_thumbnail", $rt_media_gallery->id); ?>" alt="<?php echo $rt_media_gallery->media_title; ?>" style="height:<?php echo $thumbnail_height; ?>px;width:<?php echo $thumbnail_width ?>px;" >
                                            </div>
                                    </a>
                                    <?php do_action( "rtmedia_gallery_widget_after_media", $rt_media_gallery );?>
                                    </li>
                                <?php } ?>
                            </ul>
                            <?php
                        } else {
                            $media_string = $type;
                            if ($type === 'all') {
                                $media_string = 'media';
                            }
                            _e('No ' . str_replace("_", " ", $wdType) . ' ' . $media_string . ' found', 'buddypress-media');
                        }
                        wp_reset_query();
                        ?>
                    </div>
                    <?php                     
                }
                ?>
            </div>
            <?php
        }
        echo $after_widget;
    }

    // filter the rtmedia_query to exclude the group media in the sidebar gallery widget
    function rtmedia_query_where_filter( $where, $table_name, $join ){
        $where .= ' AND ( ' . $table_name . '.privacy = "0" OR ' . $table_name . '.privacy is NULL )';
        return $where;
    }

    /**
     * Processes the widget form
     *
     * @param array/object $new_instance The new instance of the widget
     * @param array/object $old_instance The default widget instance
     * @return array/object filtered and corrected instance
     */
    function update($new_instance, $old_instance) {
        $instance = $old_instance;
        if( isset( $new_instance['wdType'] ) ) {
            $instance['wdType'] = strip_tags($new_instance['wdType']);
        }
        if( isset( $new_instance['wdTime'] ) ) {
            $instance['wdTime'] = strip_tags($new_instance['wdTime']);
        }
        $instance['title'] = strip_tags($new_instance['title']);
        $instance['number'] = (int) $new_instance['number'];
        $instance['allow_audio'] = !empty($new_instance['allow_audio']) ? 1 : 0;
        $instance['allow_video'] = !empty($new_instance['allow_video']) ? 1 : 0;
        $instance['allow_image'] = !empty($new_instance['allow_image']) ? 1 : 0;
        $instance['allow_all'] = !empty($new_instance['allow_all']) ? 1 : 0;
        $instance['thumbnail_width'] = (int) $new_instance['thumbnail_width'];
        $instance['thumbnail_height'] = (int) $new_instance['thumbnail_height'];
        return $instance;
    }

    /**
     * Displays the form for the widget settings on the Widget screen
     *
     * @param object/array $instance The widget instance
     */
    function form($instance) {
        $wdType = isset($instance['wdType']) ? esc_attr($instance['wdType']) : '';
        $wdTime = isset($instance['wdTime']) ? esc_attr($instance['wdTime']) : '';
        $title = isset($instance['title']) ? esc_attr($instance['title']) : '';
        $number = isset($instance['number']) ? absint($instance['number']) : 10;
        $allowAudio = isset($instance['allow_audio']) ? (bool) $instance['allow_audio'] : true;
        $allowVideo = isset($instance['allow_video']) ? (bool) $instance['allow_video'] : true;
        $allowImage = isset($instance['allow_image']) ? (bool) $instance['allow_image'] : true;
        $allowAll = isset($instance['allow_all']) ? (bool) $instance['allow_all'] : true;
        $thumbnailWidth = isset($instance['thumbnail_width']) ? esc_attr($instance['thumbnail_width']) : '';
        $thumbnailHeight = isset($instance['thumbnail_height']) ? esc_attr($instance['thumbnail_height']) : '';
        ?>
        <p>
            <label for="<?php echo $this->get_field_id('wdType'); ?>"><?php _e('Widget Type:', 'rt-media'); ?></label>
            <select  class="widefat" id="<?php echo $this->get_field_id('wdType'); ?>" name="<?php echo $this->get_field_name('wdType'); ?>" data-value="<?php echo $wdType; ?>">
                <option value="most_rated"><?php _e('Most Rated Media', 'rt-media'); ?></option>
                <option value="recent" ><?php _e('Recent Media', 'rt-media'); ?></option>
                <option value="popular" ><?php _e('Popular Media', 'rt-media'); ?></option>
                <option value="views" ><?php _e('Most Viewed Media', 'rt-media'); ?></option>
            </select>
        </p>
        <p>
            <label for="<?php echo $this->get_field_id('wdTime'); ?>"><?php _e('Media Uploaded Time:', 'rt-media'); ?></label>
            <select  class="widefat" id="<?php echo $this->get_field_id('wdTime'); ?>" name="<?php echo $this->get_field_name('wdTime'); ?>" data-value="<?php echo $wdTime; ?>">
                <option value="today"><?php _e('Today', 'rt-media'); ?></option>
                <option value="this_week" ><?php _e('This Week', 'rt-media'); ?></option>
                <option value="this_month" ><?php _e('This Month', 'rt-media'); ?></option>
                <option value="all" ><?php _e('All Time', 'rt-media'); ?></option>
            </select>
        </p>
        <p>
            <label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:', 'buddypress-media'); ?></label>
            <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" />
        </p>
        <p>
            <label for="<?php echo $this->get_field_id('number'); ?>"><?php _e('Number of posts to show:', 'buddypress-media'); ?></label>
            <input id="<?php echo $this->get_field_id('number'); ?>" name="<?php echo $this->get_field_name('number'); ?>" type="text" value="<?php echo $number; ?>" size="3" />
        </p>
        <p>
            <input role="checkbox" type="checkbox" name="<?php echo $this->get_field_name('allow_all'); ?>" id="<?php echo $this->get_field_id('allow_all'); ?>" <?php checked($allowAll); ?> />
            <label for="<?php echo $this->get_field_id('allow_all'); ?>"><?php _e('Show All', 'buddypress-media'); ?></label>
        </p>
        <p>
            <input role="checkbox" type="checkbox" name="<?php echo $this->get_field_name('allow_image'); ?>" id="<?php echo $this->get_field_id('allow_image'); ?>" <?php checked($allowImage); ?> />
            <label for="<?php echo $this->get_field_id('allow_image'); ?>"><?php _e('Show Photos', 'buddypress-media'); ?></label>
        </p>
        <p>
            <input role="checkbox" type="checkbox" name="<?php echo $this->get_field_name('allow_audio'); ?>" id="<?php echo $this->get_field_id('allow_audio'); ?>" <?php checked($allowAudio); ?> />
            <label for="<?php echo $this->get_field_id('allow_audio'); ?>"><?php _e('Show Music', 'buddypress-media'); ?></label>
        </p>
        <p>
            <input role="checkbox" type="checkbox" name="<?php echo $this->get_field_name('allow_video'); ?>" id="<?php echo $this->get_field_id('allow_video'); ?>" <?php checked($allowVideo); ?> />
            <label for="<?php echo $this->get_field_id('allow_video'); ?>"><?php _e('Show Videos', 'buddypress-media'); ?></label>
        </p>
        <p>
            <label for="<?php echo $this->get_field_id('thumbnail_width'); ?>"><?php _e('Thumbnail Width:', 'buddypress-media'); ?></label>
            <input id="<?php echo $this->get_field_id('thumbnail_width'); ?>" name="<?php echo $this->get_field_name('thumbnail_width'); ?>" type="text" value="<?php echo $thumbnailWidth; ?>" size="3" />
            <label>px</label>
        </p>
        <p>
            <label for="<?php echo $this->get_field_id('thumbnail_height'); ?>"><?php _e('Thumbnail Height:', 'buddypress-media'); ?></label>
            <input id="<?php echo $this->get_field_id('thumbnail_width'); ?>" name="<?php echo $this->get_field_name('thumbnail_height'); ?>" type="text" value="<?php echo $thumbnailHeight; ?>" size="3" />
            <label>px</label>
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