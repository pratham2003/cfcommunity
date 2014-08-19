<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of RTMediaProPoints
 *
 * @author ritz
 */
class RTMediaProPoints {

    var $rtmedia_key = array(	"after_upload_image" => array("action" => "rtmedia_after_add_photo"),
			"after_upload_music" => array("action" => "rtmedia_after_add_music"),
			"after_upload_video" => array("action" => "rtmedia_after_add_video"),
			"after_album_create" => array("action" => "rtmedia_after_add_album"),
			"after_playlist_create" => array("action" => "rtmedia_after_add_playlist"),
			"after_media_rate" => array("action" => "rtmedia_pro_after_rating_media"),
			"after_media_download" => array("action" => "rtmedia_pro_before_download_media"),
			"after_media_like" => array("action" => "rtmedia_after_like_media"),
			"after_media_view" => array("action" => "rtmedia_after_view_media"),
			"after_media_edit" => array("action" => "rtmedia_after_edit_media"),
			"after_media_delete" => array("action" => "rtmedia_after_delete_media"),
			"after_media_report" => array("action" => "rtmedia_pro_after_report_media"),
			"after_set_album_cover" => array("action" => "rtmedia_pro_after_set_album_cover"),
			"after_set_featured" => array("action" => "rtmedia_after_set_featured"),
			"after_comment" => array("action" => "rtmedia_after_add_comment"),
			"after_edit_album" => array("action" => "rtmedia_after_update_album")
		);

    public function __construct() {
	add_filter("rtmedia_add_settings_sub_tabs", array($this,"rtmedia_pro_add_reward_point_tab"), 10, 1);
	$this->init();
    }

    function rtmedia_pro_add_reward_point_tab( $sub_tabs ) {
	$sub_tabs[] = array(
                'href' => '#rtmedia-reward-points',
                'icon' => 'rtmicon-star',
                'title' => __ ( 'rtMedia Reward Point', 'rtmedia' ),
                'name' => __ ( 'Reward Points', 'rtmedia' ),
                'callback' => array( $this, 'admin_point_content' )
            );
        return $sub_tabs;
    }

    function admin_point_content() {
	?>
	    <div class="postbox metabox-holder">
		<h3 class="hndle"><span>Reward Point Settings</span></h3>
	    </div>
	    <div class="row">
		<div class="columns large-12">
		    <p><?php _e('We support', 'rtmedia'); ?> <b><a href="http://wordpress.org/plugins/cubepoints/" target="_blank">CubePoints</a></b> <?php _e('and', 'rtmedia'); ?> <b><a href="http://wordpress.org/plugins/mycred/" target="_blank">myCRED</a></b>.</p>
	    </div>
	    </div>
	    <div class="row">
		<div class="columns large-12">
		    <?php
			if(function_exists("cp_module_register")) {
		    ?>
			    <fieldset><legend>CubePoints</legend>
		    <?php
			    if( ! cp_module_activated( 'rtmedia' ) ) {
		    ?>
		    <p><?php echo __('Active') . " CubePoints " . __('for') . " rtMedia" ; ?> <a href="<?php echo get_admin_url(); ?>admin.php?page=cp_admin_modules#rtmedia-cp" target ="_blank">here</a>.</p>
		    <?php
			    }
		    ?>
		    <?php
			    if( cp_module_activated( 'rtmedia' ) ) {
		    ?>
				<p><?php _e('Set reward points', 'rtmedia'); ?> <a href="<?php echo get_admin_url(); ?>admin.php?page=cp_admin_config#rtmedia-cp" target="_blank">here</a>.</p>
		    <?php
			    }
		    ?>
				</fieldset>
		    <?php
			}
		    ?>
		    <?php
			if(class_exists("myCRED_Hook")) {
		    ?>
			<fieldset><legend>myCRED</legend>
			    <p><?php _e('Setup', 'rtmedia'); ?> myCRED <a href="<?php echo get_admin_url(); ?>admin.php?page=myCRED_page_hooks#rtmedia-mycred" target ="_blank" target="_blank"><?php _e('here', 'rtmedia'); ?></a>.</p>
			</fieldset>
		    <?php
			}
		    ?>
		</div>
	    </div>
	<?php
    }

    function init() {
	if(function_exists("cp_module_register")) {
	    cp_module_register( __( 'Points for rtMedia', 'cp' ) , 'rtmedia' , '1.0', '<a id="rtmedia-cp" href="http://rtcamp.com/">rtCamp</a>', 'http://rtcamp.com/?utm_source=dashboard&utm_medium=plugin&utm_campaign=buddypress-media', 'http://rtcamp.com/' , __( 'Points for add photos, videos or music.', 'cp' ), 1 );
	    if( ! cp_module_activated( 'rtmedia' ) ) {
		add_action( 'cp_module_rtmedia_activate', array($this,'cp_rtmedia_install' ));
	    }
	    if ( cp_module_activated( 'rtmedia' ) ) {
		//$this->configure_cp_rtmedia_options();
		// Add a function to display the form inputs.
		add_action( 'cp_config_form',array($this,'cp_rtmedia_config' ));
		// Create a function to process the form inputs when the form is submitted.
		add_action( 'cp_config_process', array($this,'cp_rtmedia_config_process' ));
		add_action('cp_logs_description',array($this,'cp_rtmedia_log'), 10, 4);
	    }
	    // bind actions dynamically
	    $rtmedia_points = maybe_unserialize(get_site_option("rtmedia_points"));
	    if(is_array($rtmedia_points) && sizeof($rtmedia_points) > 0 ) {
		foreach($rtmedia_points as $key => $val) {
		    add_action($val['action'],array($this,$key));
		}
	    }

	}

	if(class_exists("myCRED_Hook")) {
	    $this->cp_rtmedia_install();
	    //add_filter( 'mycred_setup_addons', array($this,'rtmedia_mycred_addon' ));
            add_filter( 'mycred_setup_hooks', array( $this, 'rtmedia_mycred_hook' ) );
	}
    }

    function rtmedia_mycred_addon( $installed ) {
	$installed['buddypress-media'] = array(
		'title'       => __( 'rtMedia','rtMedia' ),
		'name'       => __( 'rtMedia','rtMedia' ),
		'addon_uri'   => 'http://rtcamp.com/',
		'version'     => '1.0',
		'description' => __( 'The rtMedia add-on, to work with rtMedia to allow you to hook into most rtMedia related actions.', 'rtMedia'),
		'author'      => 'rtCamp',
		'author_uri'  => 'http://rtcamp.com',
		'file'        => 'myCRED_rtMedia.php',
		'folder'      => trailingslashit( RTMEDIA_PRO_PATH ) . 'app/main/controllers/',
	);
	return $installed;
    }

    function rtmedia_mycred_hook( $installed ) {
	$installed['rtmedia_media'] = array(
		'title'       => "<span id='rtmedia-mycred'> " .   __( 'Points for rtMedia', 'rtMedia' ) . "</span>",
		'description' => __( 'Points for media', 'rtMedia' ),
		'callback'    => array( 'RTMediaProMyCredHook' )
	);
	return $installed;
    }

    function cp_rtmedia_log ($type,$uid,$points,$data) {
	if($type!='cp_rtmedia') { return; }
	_e($data,'cp_rtmedia');
    }

    function cp_rtmedia_install()
    {
	$rtmedia_points = maybe_unserialize(get_site_option("rtmedia_points"));
	if(!is_array($rtmedia_points) ) {
	    $rtmedia_points = array();
	    foreach($this->rtmedia_key as $key => $val) {
		$val['message'] = array( "cp_message" => "rtMedia ".str_replace("_", " ", $key));
		$val['points'] = array( "cp_points" => 0);
		$rtmedia_points[$key] = $val;
	    }
	} else {
	    foreach($this->rtmedia_key as $key => $val) {
		if( ( !isset($rtmedia_points[$key]['points']['cp_points']) ) || ( $rtmedia_points[$key]['action'] != $val['action']) ) {
		    $val['message']['cp_message'] = "rtMedia ".str_replace("_", " ", $key);
		    $val['points']['cp_points'] = 0;
		    $rtmedia_points[$key] = $val;
		}
	    }
	    if(sizeof($this->rtmedia_key) < sizeof($rtmedia_points)) {
		foreach($rtmedia_points as $key => $val) {
		    if(!isset($this->rtmedia_key[$key])) {
			unset($rtmedia_points[$key]);
		    }
		}
	    }
	}
	rtmedia_update_site_option("rtmedia_points", $rtmedia_points);
    }

    function cp_rtmedia_config()
    {
	$rtmedia_points = maybe_unserialize(get_site_option("rtmedia_points"));
    ?>
	<br />
	<h3 id="rtmedia-cp"><?php _e( 'Points for rtMedia','cp' ); ?></h3>
	<table class="form-table">
    <?php
	foreach($rtmedia_points as $key => $val) {
    ?>
	    <tr valign="top">
		<th scope="row">
		    <label for="cp_rtmedia_value"><?php _e( ucfirst(str_replace("_", " ", $key)), 'cp' ); ?>:</label>
		</th>
		<td valign="middle">
		    <input type="text" id="rtmedia_points" name="rtmedia_points[<?php echo $key ?>]" value="<?php echo( $val['points']['cp_points'] ); ?>" size="30" />
		</td>
	    </tr>
    <?php
	}
    ?>
	</table>
    <?php
    }

    function cp_rtmedia_config_process()
    {
	$rtmedia_points = maybe_unserialize(get_site_option("rtmedia_points"));
	foreach ($rtmedia_points as $key => $val) {
	    $rtmedia_points[$key]['points']['cp_points'] = (int)$_POST['rtmedia_points'][$key];
	}
	rtmedia_update_site_option( 'rtmedia_points', $rtmedia_points );
    }

    function __call($name, $arguments) {
	$rtmedia_points = maybe_unserialize(get_site_option("rtmedia_points"));
	if(is_array($rtmedia_points) && sizeof($rtmedia_points) > 0 ) {
	    if(function_exists("cp_module_register")) {
		$user = get_current_user_id();
		$user_meta = maybe_unserialize ( get_user_meta ( $user, "rtmedia_points_key", true ) );
		global $rtmedia_points_media_id;
		if(! is_array($user_meta) || $user_meta == "" ) {
		    $user_meta = array();
		}
		if(!isset($user_meta[$name]['cp_points'])) {
		    $user_meta[$name]['cp_points'] = array();
		}
		if(isset($rtmedia_points[$name]['points']['cp_points']) && ($rtmedia_points[$name]['points']['cp_points'] != "") && ( !in_array($rtmedia_points_media_id, $user_meta[$name]['cp_points'])) && isset($user) && $user != 0) {
		    cp_points('cp_rtmedia', $user, $rtmedia_points[$name]['points']['cp_points'], ucfirst(str_replace("_", " ", $name)) );
		    $user_meta[$name]['cp_points'][] = $rtmedia_points_media_id;
		    update_user_meta($user, "rtmedia_points_key", $user_meta);
		}
	    }
	}
    }
}