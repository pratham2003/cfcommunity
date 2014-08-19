<?php

/**
 * Description of RTMeidaProbbPressImporter
 *
 * @author ritz
 */
class RTMediaProbbPressImporter {

    function __construct() {
	add_filter("rtmedia_filter_admin_pages_array",array($this,"rtmedia_add_admin_page_array"), 11, 1);
	add_action ( 'admin_menu', array( $this, 'menu' ) );
	add_action ( 'wp_ajax_rtmedia_gdbbpress_migration', array( $this, "rtmedia_gdbbpress_migration" ) );
	add_action ( 'wp_ajax_rtmedia_deactivate_GD_bbPress', array( $this, "rtmedia_deactivate_GD_bbPress" ) );
	add_action ( 'wp_ajax_rtmedia_hide_gd_attachment_notice', array( $this, "rtmedia_hide_gd_attachment_notice" ) );
	add_action ( 'admin_init', array( $this, 'add_admin_notice' ) );
    }

    function add_admin_notice() {
	$pending = get_site_option ( "rtMigration-pending-count" );

	$total = $this->get_total_count ();
	$done = $this->get_done_count ();
	$pending = $total - $done;
	if ( $pending < 0 ) {
	    $pending = 0;
	}
	rtmedia_update_site_option ( "rtm-gd-migration-pending-count", $pending );
	if ( $pending > 0 ) {
	    if ( ! (isset ( $_REQUEST[ "page" ] ) && $_REQUEST[ "page" ] == "rtmedia-migration-bbpress") ) {
		$site_option  = get_site_option("rtmedia-gd-attachment-notice");
		if(!$site_option || $site_option != "hide") {
		    rtmedia_update_site_option("rtmedia-gd-attachment-notice", "show");
		    add_action ( 'admin_notices', array( &$this, 'add_rtm_gd_migration_notice' ) );
		}
	    }
	}
    }

    function rtmedia_hide_gd_attachment_notice() {
	if(rtmedia_update_site_option("rtmedia-gd-attachment-notice", "hide"))
		echo "1";
	    else
		echo "0";
	    die();
    }

    function add_rtm_gd_migration_notice() {
	if ( current_user_can ( 'manage_options' ) ) {
	    $this->create_notice ( "<p><strong>rtMedia</strong> : <a href='" . admin_url ( "admin.php?page=rtmedia-migration-bbpress&force=true" ) . "'>Click Here</a> to migrate your bbPress attachments.  <a href='#' onclick='rtmedia_hide_gd_attachment_notice()' style='float:right'>" . __ ( "Hide" ) . "</a> </p>" );
    ?>
	    <script type="text/javascript">
		function rtmedia_hide_gd_attachment_notice() {
		    var data = {action : 'rtmedia_hide_gd_attachment_notice'};
		    jQuery.post(ajaxurl,data,function(response){
			response = response.trim();
			if(response === "1")
			    jQuery('.rtmedia-gd-bbpress-migration-error').remove();
		    });
		}
	    </script>
    <?php
	}
    }

    function create_notice ( $message, $type = "error" ) {
        echo '<div class="' . $type . ' rtmedia-gd-bbpress-migration-error">' . $message . '</div>';
    }

    function rtmedia_deactivate_GD_bbPress() {
	$active_plugins = get_site_option('active_plugins');
	foreach($active_plugins as $key => $plugin) {
	    if(strpos($plugin, "gd-bbpress-attachments") !== false) {
		unset($active_plugins[$key]);
	    }
	}
	update_option('active_plugins', $active_plugins);
	echo "true";
	wp_die();
    }

    function rtmedia_add_admin_page_array($admin_pages) {
	$admin_pages[] = "rtmedia_page_rtmedia-bbpress-migration";
	return $admin_pages;
    }

    function rtmedia_gdbbpress_migration( $lastid = 0, $limit = 1 ) {
	global $wpdb;
	if ( ! $lastid ) {
	    $lastid = $this->get_last_imported ();
	    if ( ! $lastid ) {
		$lastid = 0;
	    }
	}
	$sql = "select p.*
		from $wpdb->posts p, $wpdb->postmeta pm
		where p.ID = pm.post_id and p.post_type = 'attachment' and pm.meta_key = '_bbp_attachment' and pm.meta_value = '1' and p.ID > '$lastid' order by p.ID limit $limit";
	$result = $wpdb->get_results($sql);
	if($result && sizeof($result) > 0) {
	    $this->migrate_single_media ( $result[0] );
	}
	$this->return_migration ();
    }

    function migrate_single_media($result) {
	global $wpdb;
	$blog_id = get_current_blog_id ();
	$prefix = "topics/";
	$media_id = $result->ID;
	$sql_reply = "select * from $wpdb->posts where ID = $result->post_parent ";
	$result_reply = $wpdb->get_results($sql_reply);
	if(sizeof($result_reply) > 0) {
	    $prefix.= $result_reply[0]->post_parent;
	}
	$mime_type = strtolower ( $result->post_mime_type );
	$old_type = "";
	if ( strpos ( $mime_type, "image" ) === 0 ) {
	    $media_type = "photo";
	} else if ( strpos ( $mime_type, "audio" ) === 0 ) {
	    $media_type = "music";
	} else if ( strpos ( $mime_type, "video" ) === 0 ) {
	    $media_type = "video";
	} else {
	    $media_type = "other";
	}
	$this->importmedia($media_id, $prefix);
	$media_model = new RTMediaModel();
	$media_table = $media_model->table_name;
	$wpdb->insert (
                $media_table, array(
            'blog_id' => $blog_id,
            'media_id' => $media_id,
            'media_type' => $media_type,
            "context" => "topic",
            "context_id" => abs ( intval ( $result->post_parent ) ),
            "privacy" => 0,
            "media_author" => $result->post_author,
            "media_title" => $result->post_title,
            "album_id" => $result->post_parent,
                ), array( '%d', '%d', '%s', '%s', '%d', '%d', '%d', '%s', '%d' )
        );
	$last_id = $wpdb->insert_id;
    }

    function importmedia ( $id, $prefix ) {
	$attached_file = get_attached_file ( $id );
	$attached_file_option = get_post_meta ( $id, '_wp_attached_file', true );
        $basename = wp_basename ( $attached_file );
        $file_folder_path = trailingslashit ( str_replace ( $basename, '', $attached_file ) );


        $siteurl = get_option ( 'siteurl' );
        $upload_path = trim ( get_option ( 'upload_path' ) );

        if ( empty ( $upload_path ) || 'wp-content/uploads' == $upload_path ) {
            $dir = WP_CONTENT_DIR . '/uploads';
        } elseif ( 0 !== strpos ( $upload_path, ABSPATH ) ) {
            // $dir is absolute, $upload_path is (maybe) relative to ABSPATH
            $dir = path_join ( ABSPATH, $upload_path );
        } else {
            $dir = $upload_path;
        }

        if ( ! $url = get_option ( 'upload_url_path' ) ) {
            if ( empty ( $upload_path ) || ( 'wp-content/uploads' == $upload_path ) || ( $upload_path == $dir ) ) {
		$url = WP_CONTENT_URL . '/uploads';
	    } else {
		$url = trailingslashit ( $siteurl ) . $upload_path;
	    }

        }

        // Obey the value of UPLOADS. This happens as long as ms-files rewriting is disabled.
        // We also sometimes obey UPLOADS when rewriting is enabled -- see the next block.
        if ( defined ( 'UPLOADS' ) && ! ( is_multisite () && get_site_option ( 'ms_files_rewriting' ) ) ) {
            $dir = ABSPATH . UPLOADS;
            $url = trailingslashit ( $siteurl ) . UPLOADS;
        }


	if ( is_multisite () && ! ( is_main_site () && defined ( 'MULTISITE' ) ) ) {

            if ( ! get_site_option ( 'ms_files_rewriting' ) ) {
                // If ms-files rewriting is disabled (networks created post-3.5), it is fairly straightforward:
                // Append sites/%d if we're not on the main site (for post-MU networks). (The extra directory
                // prevents a four-digit ID from conflicting with a year-based directory for the main site.
                // But if a MU-era network has disabled ms-files rewriting manually, they don't need the extra
                // directory, as they never had wp-content/uploads for the main site.)

                if ( defined ( 'MULTISITE' ) )
                    $ms_dir = '/sites/' . get_current_blog_id ();
                else
                    $ms_dir = '/' . get_current_blog_id ();

                $dir .= $ms_dir;
                $url .= $ms_dir;
            } elseif ( defined ( 'UPLOADS' ) && ! ms_is_switched () ) {
                // Handle the old-form ms-files.php rewriting if the network still has that enabled.
                // When ms-files rewriting is enabled, then we only listen to UPLOADS when:
                //   1) we are not on the main site in a post-MU network,
                //      as wp-content/uploads is used there, and
                //   2) we are not switched, as ms_upload_constants() hardcodes
                //      these constants to reflect the original blog ID.
                //
			// Rather than UPLOADS, we actually use BLOGUPLOADDIR if it is set, as it is absolute.
                // (And it will be set, see ms_upload_constants().) Otherwise, UPLOADS can be used, as
                // as it is relative to ABSPATH. For the final piece: when UPLOADS is used with ms-files
                // rewriting in multisite, the resulting URL is /files. (#WP22702 for background.)

                if ( defined ( 'BLOGUPLOADDIR' ) )
                    $dir = untrailingslashit ( BLOGUPLOADDIR );
                else
                    $dir = ABSPATH . UPLOADS;
                $url = trailingslashit ( $siteurl ) . 'files';
            }
        }

	$basedir = trailingslashit ( $dir );
        $baseurl = trailingslashit ( $url );
	$new_file_folder_path = trailingslashit ( str_replace ( $basedir, $basedir . "rtMedia/$prefix/", $file_folder_path ) );

        $year_month = untrailingslashit ( str_replace ( $basedir, '', $file_folder_path ) );


        $metadata = wp_get_attachment_metadata ( $id );


	if ( wp_mkdir_p ( $basedir . "rtMedia/$prefix/" . $year_month ) ) {
	    if ( copy ( $attached_file, str_replace ( $basedir, $basedir . "rtMedia/$prefix/", $attached_file ) ) ) {
		if ( isset ( $metadata[ 'sizes' ] ) ) {
		    foreach ( $metadata[ 'sizes' ] as $size ) {
			copy ( $file_folder_path . $size[ 'file' ], $new_file_folder_path . $size[ 'file' ] );
		    }
		}
		update_post_meta ( $id, '_wp_attached_file', "rtMedia/$prefix/" . $attached_file_option );
		$attached_meta = get_post_meta($id,'_wp_attachment_metadata', true);
		if($attached_meta) {
		    $attached_meta_array = maybe_unserialize($attached_meta);
		    if(is_array($attached_meta_array)) {
			$attached_meta_array['file'] = "rtMedia/$prefix/" . $attached_file_option;
			update_post_meta($id, '_wp_attachment_metadata', $attached_meta_array);
		    }
		}
	    }
	}

	$attachment = array( );
	$attachment[ 'ID' ] = $id;
	$old_guid = get_post_field ( 'guid', $id );
	$attachment[ 'guid' ] = trailingslashit($baseurl . "rtMedia/$prefix/".$year_month). $basename;
	wp_update_post ( $attachment );
	update_post_meta($id, "rtm-gd-migrated", "1");
    }

    function return_migration () {
        $total = $this->get_total_count ();
        $done = $this->get_done_count ();
        $pending = $total - $done;
        if ( $pending < 0 ) {
            $pending = 0;
            $done = $total;
        }
        if ( $done > $total ) {
            $done = $total;
        }
        if ( $done == $total ) {
            global $wp_rewrite;
            //Call flush_rules() as a method of the $wp_rewrite object
            $wp_rewrite->flush_rules ( true );
        }
        rtmedia_update_site_option ( "rtm-gd-migration-pending-count", $pending );
        $pending_time = $this->formatSeconds ( $pending );

        echo json_encode ( array( "status" => true, "done" => $done, "total" => $total, "pending" => $pending_time ) );
        die ();
    }

    function get_last_imported() {
	global $wpdb;
	$query_last_imported = "select p.ID
				from $wpdb->posts p, $wpdb->postmeta pm
				where pm.meta_key = 'rtm-gd-migrated' and pm.post_id = p.ID and pm.meta_value = '1' order by pm.meta_id DESC";
	$attachments_last_imported = $wpdb->get_results( $query_last_imported );
	if($attachments_last_imported && sizeof($attachments_last_imported) > 0) {
	    return $attachments_last_imported[0]->ID;
	}
	return false;
    }

    function menu () {
        add_submenu_page ( 'rtmedia-setting', __ ( 'GD Migration', 'buddypress-media' ), __ ( 'GD Migration', 'buddypress-media' ), 'manage_options', 'rtmedia-migration-bbpress', array( $this, 'init' ) );
    }

    function get_total_count() {
	global $wpdb;
	$query_total = "select count(*) as total
			from $wpdb->posts p, $wpdb->postmeta pm
			where pm.meta_key = '_bbp_attachment' and pm.post_id = p.ID and pm.meta_value = '1'";
	$attachments_total = $wpdb->get_results( $query_total );
	if($attachments_total && sizeof($attachments_total) > 0) {
	    return $attachments_total[0]->total;
	}
	return 0;
    }

    function get_done_count() {
	global $wpdb;
	$media_model = new RTMediaModel();
	$media_table = $media_model->table_name;
	$query_done = "select count(*) as total
			from $wpdb->posts p, $wpdb->postmeta pm, $media_table m
			where pm.meta_key = '_bbp_attachment' and pm.post_id = p.ID and pm.meta_value = '1' and m.media_id = p.ID and m.blog_id = '".get_current_blog_id()."' ";
	$attachments_done = $wpdb->get_results( $query_done );
	if($attachments_done && sizeof($attachments_done) > 0) {
	    return $attachments_done[0]->total;
	}
	return 0;
    }

    function init() {
	global $rtmedia;
	//var_dump($rtmedia->allowed_types);
	$prog = new rtProgress();
        $total = $this->get_total_count ();
        $done = $this->get_done_count ();
	if ( $done >= $total ) {
            $done = $total;
        }
    ?>
	<div class="wrap">
	    <h2>rtMedia GD bbPress Migration</h2>
	    <?php
		if(class_exists("gdbbPressAttachments")) {
	    ?>
		    <h3>First <a href="#" id="rtm-deactivate-gd-bbpress">deactivate</a> <i>GD bbPress Attachments plugin</i> and than start migration process.</h3>
	    <?php
		}
	    	echo '<span class="pending">' . $this->formatSeconds ( $total - $done ) . '</span><br />';
		echo '<span class="finished">' . $done . '</span>/<span class="total">' . $total . '</span>';
		echo '<img src="images/loading.gif" alt="syncing" id="rtMediaSyncing" style="display:none" />';

		$temp = $prog->progress ( $done, $total );
		$prog->progress_ui ( $temp, true );
            ?>
	    <script type="text/javascript">
                jQuery(document).ready(function(e) {
		    jQuery("#toplevel_page_rtmedia-settings").addClass("wp-has-current-submenu")
		    jQuery("#toplevel_page_rtmedia-settings").removeClass("wp-not-current-submenu")
		    jQuery("#toplevel_page_rtmedia-settings").addClass("wp-menu-open")
		    jQuery("#toplevel_page_rtmedia-settings>a").addClass("wp-menu-open")
		    jQuery("#toplevel_page_rtmedia-settings>a").addClass("wp-has-current-submenu")
                    if (db_total < 1)
                        jQuery("#submit").attr('disabled', "disabled");
                })
                function db_start_migration(db_done, db_total) {

                    if (db_done < db_total) {
                        jQuery("#rtMediaSyncing").show();
                        jQuery.ajax({
                            url: rtmedia_admin_ajax,
                            type: 'post',
                            data: {
                                "action": "rtmedia_gdbbpress_migration",
                                "done": db_done
                            },
                            success: function(sdata) {

                                try {
                                    data = JSON.parse(sdata);
                                } catch (e) {
                                    jQuery("#submit").attr('disabled', "");
                                }
                                if (data.status) {
                                    done = parseInt(data.done);
                                    total = parseInt(data.total);
                                    var progw = Math.ceil((done / total) * 100);
                                    if (progw > 100) {
                                        progw = 100;
                                    }
                                    ;
                                    jQuery('#rtprogressbar>div').css('width', progw + '%');
                                    jQuery('span.finished').html(done);
                                    jQuery('span.total').html(total);
                                    jQuery('span.pending').html(data.pending);
                                    db_start_migration(done, total);
                                } else {
                                    alert("Migration completed.");
                                    jQuery("#rtMediaSyncing").hide();
                                }
                            },
                            error: function() {
                                alert("Error During Migration, Please Refresh Page then try again");
                                jQuery("#submit").removeAttr('disabled');
                            }
                        });
                    } else {
                        alert("Migration completed.");
                        jQuery("#rtMediaSyncing").hide();
                    }
                }
                var db_done = <?php echo $done; ?>;
                var db_total = <?php echo $total; ?>;
                jQuery(document).on('click', '#submit', function(e) {
                    e.preventDefault();
                    db_start_migration(db_done, db_total);
                    jQuery(this).attr('disabled', 'disabled');
                });
            </script>
	    <hr />
            <?php if ( ! (isset ( $rtmedia_error ) && $rtmedia_error === true) ) { ?>
                <input type="button" id="submit" value="start" class="button button-primary" />
            <?php } ?>
	</div>
    <?php
    }

    function formatSeconds ( $secondsLeft ) {

        $minuteInSeconds = 60;
        $hourInSeconds = $minuteInSeconds * 60;
        $dayInSeconds = $hourInSeconds * 24;

        $days = floor ( $secondsLeft / $dayInSeconds );
        $secondsLeft = $secondsLeft % $dayInSeconds;

        $hours = floor ( $secondsLeft / $hourInSeconds );
        $secondsLeft = $secondsLeft % $hourInSeconds;

        $minutes = floor ( $secondsLeft / $minuteInSeconds );

        $seconds = $secondsLeft % $minuteInSeconds;

        $timeComponents = array( );

        if ( $days > 0 ) {
            $timeComponents[ ] = $days . " day" . ($days > 1 ? "s" : "");
        }

        if ( $hours > 0 ) {
            $timeComponents[ ] = $hours . " hour" . ($hours > 1 ? "s" : "");
        }

        if ( $minutes > 0 ) {
            $timeComponents[ ] = $minutes . " minute" . ($minutes > 1 ? "s" : "");
        }

        if ( $seconds > 0 ) {
            $timeComponents[ ] = $seconds . " second" . ($seconds > 1 ? "s" : "");
        }
        if ( count ( $timeComponents ) > 0 ) {
            $formattedTimeRemaining = implode ( ", ", $timeComponents );
            $formattedTimeRemaining = trim ( $formattedTimeRemaining );
        } else {
            $formattedTimeRemaining = "No time remaining.";
        }

        return $formattedTimeRemaining;
    }

}
