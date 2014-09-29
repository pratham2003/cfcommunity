<?php
class RTMediaProModerationListTable extends WP_List_Table {
    public function __construct() {

	    // Define singular and plural labels, as well as whether we support AJAX.
	    parent::__construct( array(
		    'ajax'     => false,
		    'plural'   => 'moderation',
		    'singular' => 'moderation',
	    ) );
    }

    function prepare_items() {
	global $rtmedia;
        $options = $rtmedia->options;
	$take_down_count = (int)$options['moderation_removeContentAfterReports'];
	global $wpdb, $_wp_column_headers;
	$screen = get_current_screen();
	$this->process_bulk_action();
	/* -- Preparing your query -- */
	$media_table = $wpdb->prefix."rt_rtm_media";
	$interaction_table = $wpdb->prefix."rt_rtm_media_interaction";
	if( is_multisite() ) {
	    $media_table = $wpdb->base_prefix."rt_rtm_media";
	    $interaction_table = $wpdb->base_prefix."rt_rtm_media_interaction";
	}
	$query = 'SELECT '.$media_table.'.*, count( '.$media_table.'.id ) as "total_count", GROUP_CONCAT('.$interaction_table.'.user_id) as "reported_by" FROM '.$media_table.' INNER JOIN '.$interaction_table.' ON ( '.$media_table.'.id = '.$interaction_table.'.media_id AND '.$interaction_table.'.action = "moderate" ) AND '.$media_table.'.privacy = "80" ';
	if( is_multisite() ) {
	    $query.= ' AND '.$media_table.'.blog_id = "'.  get_current_blog_id().'" ';
	}
	$query.= ' GROUP BY '.$interaction_table.'.media_id having count( '.$media_table.'.id ) >= '.$take_down_count.'  ORDER BY total_count DESC ';
	/* -- Pagination parameters -- */
	//Number of elements in your table?
	$totalitems = $wpdb->query($query);
	//return the total number of affected rows
	//How many to display per page?
	$perpage = 5;
	//Which page is this?
	$paged = !empty($_GET["paged"]) ? mysql_real_escape_string($_GET["paged"]) : '';
	//Page Number
	if(empty($paged) || !is_numeric($paged) || $paged<=0 ) { $paged=1; }
	//How many pages do we have in total?
	$totalpages = ceil($totalitems/$perpage);
	//adjust the query to take pagination into account
	if(!empty($paged) && !empty($perpage)) {
		$offset=($paged-1)*$perpage; $query.=' LIMIT '.(int)$offset.','.(int)$perpage;
	}

	/* -- Register the pagination -- */
	$this->set_pagination_args( array(
		"total_items" => $totalitems,
		"total_pages" => $totalpages,
		"per_page" => $perpage,
	) );
	//The pagination links are automatically built according to those parameters

	/* -- Register the Columns -- */
	$columns = $this->get_columns();
	$hidden = array();
	$sortable = $this->get_sortable_columns();
	$this->_column_headers = array($columns, $hidden, $sortable);

	/* -- Fetch the items -- */
	$this->items = $wpdb->get_results($query);
    }

    function get_column_info() {
	$this->_column_headers = array(
		$this->get_columns(),
		array(),
		$this->get_sortable_columns(),
	);
	return $this->_column_headers;
    }

    function no_items() {
	    _e( 'No Media found.', 'rtmedia' );
    }

    function display() {
	extract( $this->_args );
	$this->display_tablenav( 'top' ); ?>
	<table class="<?php echo implode( ' ', $this->get_table_classes() ); ?>" cellspacing="0">
	    <thead>
		<tr>
		    <?php $this->print_column_headers(); ?>
		</tr>
	    </thead>
	    <tfoot>
		<tr>
		    <?php $this->print_column_headers( false ); ?>
		</tr>
	    </tfoot>
	    <tbody id="the-comment-list">
		<?php $this->display_rows_or_placeholder(); ?>
	    </tbody>
	</table>
	<?php
	$this->display_tablenav( 'bottom' );
    }

    function single_row( $item ) {
	static $row_class = '';
	if ( empty( $row_class ) ) {
		$row_class = ' class="alternate"';
	} else {
		$row_class = '';
	}
	echo '<tr' . $row_class . ' >';
	echo $this->single_row_columns( $item );
	echo '</tr>';
    }

    function get_bulk_actions() {
	$actions = array();
	$actions['allow'] = __( 'Allow', 'rtmedia' ).'</a>';
	$actions['block'] = __( 'Block', 'rtmedia' ).'</a>';
	$actions['delete'] = __( 'Delete', 'rtmedia' ).'</a>';
	return $actions;
    }

    function get_columns() {
	return array(
	    'cb' => '<input type="checkbox" />',
	    'author'=>__('Uploaded By', 'rtmedia'),
	    'media'=>__('Media', 'rtmedia'),
	    'tags'=>__('Abuse Count', 'rtmedia'),
	    'categories' => __('Reported By', 'rtmedia')
	);
    }

    function column_cb( $item ) {
	    printf( '<label class="screen-reader-text" for="aid-%1$d">' . __( 'Select activity item %1$d', 'buddypress' ) . '</label><input type="checkbox" name="%1$s[]" value="%2$s" id="aid-%1$d" />', $this->_args['singular'], $item->id );
    }

    function column_author( $item ) {
	    $user_info = get_userdata($item->media_author);
	    //var_dump($user_info);
	    echo '<strong>' . get_avatar( $item->media_author, '32' ) . ' <a href="'.get_rtmedia_user_link($item->media_author).'">'.$user_info->data->display_name.'</a> </strong>';
    }

    function column_media( $item ) {
	$user_info = get_userdata($item->media_author);
	if ( $item->media_type == 'photo' ) {
            $src = wp_get_attachment_image_src ( $item->media_id, "rt_media_thumbnail" );
            $html = "<img src='" . $src[ 0 ] . "' alt='' />";
        } elseif ( $item->media_type == 'video' ) {
            $size = " width=\"" . $rtmedia->options[ "defaultSizes_video_singlePlayer_width" ] . "\" height=\"" . $rtmedia->options[ "defaultSizes_video_singlePlayer_height" ] . "\" ";

            $html = '<video src="' . wp_get_attachment_url ( $item->media_id ) . '" ' . $size . ' type="video/mp4" class="wp-video-shortcode" id="bp_media_video_' . $item->id . '" controls="controls" preload="true"></video>';
        } elseif ( $item->media_type == 'music' ) {
            $size = ' width="600" height="0" ';
            $html = '<audio src="' . wp_get_attachment_url ( $item->media_id ) . '" ' . $size . ' type="audio/mp3" class="wp-audio-shortcode" id="bp_media_audio_' . $item->id . '" controls="controls" preload="none"></audio>';
        } else {
            $html = false;
        }
	$perma_link = get_rtmedia_permalink($item->id);
	$blog_info = get_bloginfo();
	$author_email = $user_info->data->user_email."?subject=".$blog_info." - Reported Media";
	echo '<div> <a href="'.stripslashes($perma_link).'" target="_blank">'.$html.'</a> </div> <div class="row-actions"><a href="'.stripslashes($perma_link).'" target="_blank">View</a> | <a href="mailto:'.$author_email.'">Email Uploader</a> | <span> <a href="#" id="allow-'.$item->media_id.'" onclick="rtmedia_allow_content(this);">Allow</a> </span> | <span class="delete"> <a href="#" id="del-'.$item->media_id.'" onclick="rtmedia_delete_media(this)">Delete</a> </span> | <span class="delete"> <a href="#" onclick="rtmedia_block_user('.$item->media_author.')">Block User</a> </span>  </div>';
    }

    function column_tags( $item ) {
	    echo $item->total_count;
    }

    function column_categories($item) {
	    $reported_by = explode(",",$item->reported_by);
	    $content = "";
	    foreach($reported_by as $user) {
		$user_info = get_userdata($user);
		$content.= '<a href="'.get_rtmedia_user_link($user).'">'.$user_info->data->display_name.'</a>, ';
	    }
	    echo substr($content, 0, strlen($content) - 2);
    }

    function process_bulk_action() {

	if(isset($_REQUEST['moderation'])) {
	    $media_id = ( is_array( $_REQUEST['moderation'] ) ) ? $_REQUEST['moderation'] : array( $_REQUEST['moderation'] );
	    if ( 'delete' === $this->current_action() ) {
		$rtmediamedia = new RTMediaMedia();
		foreach ( $media_id as $id ) {
		    $rtmediamedia->delete_wordpress_attachment(rtmedia_media_id($id));
		}
	    } elseif( 'block' === $this->current_action() ) {
		$rtmedia_site_option = maybe_unserialize(rtmedia_get_site_option("rtmedia-blocked-users"));
		$rtmodel = new RTMediaModel();
		$curr_user = wp_get_current_user();
		foreach ( $media_id as $id ) {
		    $media = $rtmodel->get(array("id" => $id));
		    $author = $media[0]->media_author;
		    if($author == $curr_user->data->ID) {
			continue;
		    }
		    if($rtmedia_site_option== "") {
			$rtmedia_site_option = array();
			$rtmedia_site_option[] = $author;
		    } else {
			if(!in_array($author, $rtmedia_site_option)) {
			    $rtmedia_site_option[] = $author;
			}
		    }
		}
		rtmedia_update_site_option("rtmedia-blocked-users", $rtmedia_site_option);
	    } elseif( 'allow' === $this->current_action() ) {
		$rtmediamodel = new RTMediaModel();
		foreach ( $media_id as $id ) {
		    $prev_privacy = get_rtmedia_meta($id, "moderate-privacy");
		    $data = array(
			"privacy" => $prev_privacy,
		    );
		    $where = array( 'id' => $id );
		    $rtmediamodel->update ( $data, $where );

			// insert/update activity details in rtmedia activity table
			if( class_exists( 'RTMediaActivityModel' ) ){
				$media_model = new RTMediaModel();
				$media = $media_model->get( array( 'id' => $id ) );
				$rtmedia_activity_model = new RTMediaActivityModel();
				$similar_media = $media_model->get( array( 'activity_id' => $media[0]->activity_id ) );
				$max_privacy = 0;

				foreach( $similar_media as $s_media ){
					if( $s_media->privacy > $max_privacy ){
						$max_privacy = $s_media->privacy;
					}
				}

				if( ! $rtmedia_activity_model->check( $media[0]->activity_id ) ){
					$rtmedia_activity_model->insert( array( 'activity_id' => $media[0]->activity_id, 'user_id' => $media[0]->media_author, 'privacy' => $max_privacy ) );
				} else {
					$rtmedia_activity_model->update( array( 'activity_id' => $media[0]->activity_id, 'user_id' => $media[0]->media_author, 'privacy' => $max_privacy ), array( 'activity_id' => $media[0]->activity_id ) );
				}
			}
		}
	    }
	}
    }

}