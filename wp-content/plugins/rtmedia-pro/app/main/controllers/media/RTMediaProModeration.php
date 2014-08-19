<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of RTMediaProModeration
 *
 * @author ritz
 */
class RTMediaProModeration extends RTMediaUserInteraction {

    function __construct() {
	add_filter("rtmedia_add_settings_sub_tabs", array($this,"rtmedia_pro_add_moderation_tab"), 11, 1);
	$args = array(
	    'action' => 'moderate',
            'label' => __('Report'),
            'undo_label' => 'Un-Report',
            'privacy' => 20,
	    'countable' => true,
	    'undoable' => true,
            'icon_class' => 'rtmicon-warning'
	);
	//$this->get_moderate_media_info();
	parent::__construct($args);
        //removed default filter for placement of the button
        remove_filter('rtmedia_action_buttons_before_delete', array($this,'button_filter'));
        //add_filter ( 'rtmedia_addons_action_buttons', array( $this, 'button_filter') );
        add_action('rtmedia_action_buttons_after_media', array($this, 'moderate_button'), 9);
        add_action ( 'rtmedia_actions_without_lightbox', array( $this, 'moderate_button' ) );

	//add_filter('cron_schedules', array($this,'new_interval'));
	//$this->deactivate_moderation_cron();
	$this->activate_deactivate_cron();
	register_deactivation_hook( __FILE__, array($this,'deactivate_moderation_cron' ));
	$this->moderation_admin_page_hooks();
	require_once(ABSPATH . 'wp-admin/includes/template.php' );
	require_once(ABSPATH . 'wp-admin/includes/screen.php');
	if(!class_exists('WP_List_Table')) {
	    require_once(ABSPATH . 'wp-admin/includes/class-wp-list-table.php');
	}
	add_action( 'wp_ajax_rtmedia_moderate_delete_media',array($this, 'moderate_delete_media'));
	add_action( 'wp_ajax_rtmedia_moderate_allow_media',array($this, 'moderate_allow_media'));
	global $rtmedia;
	$options = $rtmedia->options;
        if(isset($options['moderation_removeContentAfterReports']) && $options['moderation_removeContentAfterReports'] == "0" && isset($options['moderation_enableModeration']) && $options['moderation_enableModeration'] != "0") {
	    add_action( 'rtmedia_after_add_media', array($this, 'moderate_after_media_upload'), 20 ,3);
	}
    }

    function moderate_button(){
        if(empty($this->media)){
                $this->init();
        }
        $button = $this->render();

        if( $button ){
            //echo "<li>" . $button . "</li>";
            echo $button;
        }
    }

    function moderate_allow_media() {
	if(isset($_POST['media_id']) && $_POST['media_id'] != "") {
	    $id = rtmedia_id($_POST['media_id']);
	    $prev_privacy = get_rtmedia_meta($id, "moderate-privacy");
	    $rtmediamedia = new RTMediaMedia();
	    $data = array(
		"privacy" => $prev_privacy,
	    );
	    $rtmediamedia->update ( $id,$data, $_POST['media_id'] );
	    echo "true";
	    wp_die();
	}
	echo "false";
	wp_die();
    }

    function moderate_delete_media() {
	if(isset($_POST['media_id']) && $_POST['media_id'] != "") {
	    $rtmediamedia = new RTMediaMedia();
	    $rtmediamedia->delete_wordpress_attachment($_POST['media_id']);
	    echo "true";
	    wp_die();
	}
	echo "false";
	wp_die();
    }

    // new_interval created for testing purpose
    function new_interval($interval) {
	$interval['minutes_1'] = array('interval' => 1*60, 'display' => 'Once a minute');
	return $interval;
    }

    function moderation_admin_page_hooks() {
	add_filter("rtmedia_filter_admin_pages_array",array($this,"rtmedia_add_admin_page_array"), 11, 1);
	add_action ( 'admin_menu', array( $this, 'add_moderate_menu' ), 100 );
    }

    function add_moderate_menu() {
	add_submenu_page ( 'rtmedia-settings', __ ( 'Moderation', 'rtmedia' ), __ ( 'Moderation ', 'rtmedia' ), 'manage_options', 'rtmedia-moderate', array( $this, 'moderate_page' ) );
    }

    function rtmedia_add_admin_page_array($admin_pages) {
	$admin_pages[] = "rtmedia_page_rtmedia-moderate";
	return $admin_pages;
    }

    function moderate_page() {
	global $plugin_page;
	$rtmedia_moderation_list = new RTMediaProModerationListTable();
	$rtmedia_moderation_list->prepare_items();
	echo "<h2>rtMedia: Media moderation</h2>";
	echo "<form id='rtmedia-moderation-form' action='' method='get'>";
	echo '<input type="hidden" name="page" value="'.esc_attr( $plugin_page ).'" />';
	echo "<div class='wrap'>";
	$rtmedia_moderation_list->display();
	echo "</div> </form>";
    }

    function activate_deactivate_cron() {
	global $rtmedia;
        $options = $rtmedia->options;
	if(!($this->check_disable())) {
	    if(isset($options['moderation_emailNotificationFreq']) && $options['moderation_emailNotificationFreq'] != "" && $options['moderation_emailNotificationFreq'] != "instant") {
		register_activation_hook( __FILE__,array($this,'initiate_moderation_cron' ));
		$this->initiate_moderation_cron();
		add_action( 'rtmedia_moderation_cron', array($this,'send_admin_notification' ));
	    } else {
		register_deactivation_hook( __FILE__, 'deactivate_moderation_cron' );
		$this->deactivate_moderation_cron();
	    }
	} else {
	    register_deactivation_hook( __FILE__, 'deactivate_moderation_cron' );
	    $this->deactivate_moderation_cron();
	}
    }

    function initiate_moderation_cron() {
	global $rtmedia;
        $options = $rtmedia->options;
	add_filter("rtmedia_pro_options_save_settings",array($this,"check_changed_rtmedia_option"),99,1);
	if (!wp_next_scheduled('rtmedia_moderation_cron')) {
	    wp_schedule_event(time(), $options['moderation_emailNotificationFreq'], 'rtmedia_moderation_cron');
	}
    }

    function check_changed_rtmedia_option($options) {
	global $rtmedia;
        $old_options = $rtmedia->options;
	if(!($old_options['moderation_emailNotificationFreq'] == $options['moderation_emailNotificationFreq'])) {
	    $this->deactivate_moderation_cron();
	    wp_schedule_event(time(), $options['moderation_emailNotificationFreq'], 'rtmedia_moderation_cron');
	}
	return $options;
    }

    function deactivate_moderation_cron() {
	wp_clear_scheduled_hook( 'rtmedia_moderation_cron' );
    }

    function rtmedia_pro_add_moderation_tab($sub_tabs) {
	$sub_tabs[] = array(
                'href' => '#rtmedia-moderation',
                'icon' => 'rtmicon-eraser',
                'title' => __ ( 'rtMedia Moderation', 'rtmedia' ),
                'name' => __ ( 'Moderation', 'rtmedia' ),
                'callback' => array( 'RTMediaProModeration', 'moderation_content' )
            );
        return $sub_tabs;
    }

    static function moderation_content() {
	global $rtmedia;
	$options = $rtmedia->options;
	$render_options = array();
	$render_options['moderation_enableModeration'] = array(
                'title' => __('Moderation' ,'rtmedia'),
                'callback' => array('RTMediaFormHandler', 'checkbox'),
                'args' => array(
                        'key' => 'moderation_enableModeration',
                        'value' => $options['moderation_enableModeration'],
                        'desc' => __('Enable moderation in rtMedia','rtmedia')
                )
        );
        $render_options['moderation_removeContentAfterReports'] = array(
                'title' => __('Remove content after reports' ,'rtmedia'),
                'callback' => array('RTMediaFormHandler', 'number'),
                'args' => array(
                        'key' => 'moderation_removeContentAfterReports',
                        'value' => $options['moderation_removeContentAfterReports'],
                        'desc' => __('Remove content automatically after specified number of reports in rtMedia','rtmedia'),
			'class'=> array('rtmedia-setting-text-box')
                )
        );
        $render_options['moderation_adminEmails'] = array(
                'title' => __('Admin emails (comma separated)' ,'rtmedia'),
                'callback' => array('RTMediaFormHandler', 'textarea'),
                'args' => array(
                        'key' => 'moderation_adminEmails',
                        'value' => (isset($options['moderation_adminEmails']) && $options['moderation_adminEmails'] != "")?$options['moderation_adminEmails']:get_site_option('admin_email'),
                        'desc' => __('Send emails to admins automatically after specified number of reports in rtMedia','rtmedia'),
			'class'=> array('rtmedia-setting-text-area')
                )
        );
	$moderation_page_url = admin_url( 'admin.php?page=rtmedia-moderate');
        $render_options['moderation_emailNotificationFreq'] = array(
                'title' => __('Email notification frequency' ,'rtmedia'),
                'callback' => array('RTMediaFormHandler', 'selectBox'),
                'args' => array(
                        'key' => 'moderation_emailNotificationFreq',
                        'default' => $options['moderation_emailNotificationFreq'],
                        'desc' => __('Email notification frequency after specified number of reports in rtMedia','rtmedia'),
			'class' => array('rtmedia-setting-select-box'),
			'selects'=>array(
				"daily"=>"Daily",
				"hourly"=>"Hourly",
				"instant"=>"Instant"
			)
                ),
		'after_content' => __('You can <a href=\''.$moderation_page_url.'\'>manage moderated medias from here</a>','rtmedia'),
        );
	?>
	    <div class="postbox metabox-holder">
		<h3 class="hndle"><span>Moderation Settings</span></h3>
	    </div>
	<?php
	foreach ($render_options as $key => $option) { ?>
		<div class="row section">
			<div class="columns large-6">
			    <?php echo $option['title']; ?>
			</div>
			<div class="columns large-6">
			    <?php call_user_func($option['callback'], $option['args']); ?>
			    <span data-tooltip class="has-tip" title="<?php echo (isset($option['args']['desc'])) ? $option['args']['desc'] : "NA"; ?>"><i class="rtmicon-info-circle"></i></span>
			</div>
		</div>
		<div class="clearfix"></div>
		<?php
			if( isset( $option['after_content'] ) ) {
		    ?>
			    <div class="row">
				<div class="columns large-12">
				    <p class="rtmedia-info rtmedia-admin-notice">
					<?php echo $option['after_content']; ?>
				    </p>
				</div>
			    </div>
		    <?php
			}
		    ?>
	<?php }
    }

    function check_disable(){
        global $rtmedia;
        $options = $rtmedia->options;
        if(! (isset($options['moderation_enableModeration']) && ($options['moderation_enableModeration'] == "1"))) {
	    return true;
	}
	return false;
    }

    function is_moderated() {
	$rtmediainteraction = new RTMediaInteractionModel();
        $action = $this->action;
        $user_id = $this->interactor;
        $media_id = $this->action_query->id;
        $check_action = $rtmediainteraction->check($user_id, $media_id, $action);
	if($check_action) {
	    return true;
	}
	return false;
    }

    function before_render() {
	if($this->media->media_type == "playlist") {
	    return false;
	}
	if($this->is_moderated()) {
	    $this->label = $this->undo_label;
	}
    }

    function render () {
	if($this->check_disable()) {
	    return;
	}
	return parent::render();
    }

    function check_take_down_contet($media_id, $action) {
	global $rtmedia;
	$options = $rtmedia->options;
	if(isset($options['moderation_removeContentAfterReports']) && $options['moderation_removeContentAfterReports'] == "0") {
	    return true;
	}
	$rtmediainteraction = new RTMediaInteractionModel();
	$columns = array(
            'media_id' => $media_id,
            'action' => $action
        );
	$results = $rtmediainteraction->get($columns);
	if(isset($options['moderation_removeContentAfterReports']) && $options['moderation_removeContentAfterReports'] <= sizeof($results)) {
	    return true;
	} else {
	    return false;
	}
    }

    function check_instance_admin_notification() {
	global $rtmedia;
	$options = $rtmedia->options;
	if($options['moderation_emailNotificationFreq'] == "instant") {
	    return true;
	} else {
	    return false;
	}
    }

    function process() {
	if($this->check_disable()) {
	    return true;
	}
	global $rtmedia_points_media_id;
	$rtmedia_points_media_id = $this->action_query->id;
	$rtmediainteraction = new RTMediaInteractionModel();
        $action = $this->action_query->action;
        $user_id = $this->interactor;
        $media_id = $this->action_query->id;
        $check_action = $rtmediainteraction->check($user_id, $media_id, $action);
	$return = array();
	$return["next"] = "";
	$return["rt_redirect"] = "";
	if($check_action) {
	    $where = array(
		'user_id' => $user_id,
		'media_id' => $media_id,
		'action' => $action
	    );
	    if($this->check_take_down_contet($media_id, $action)) {
		$prev_privacy = get_rtmedia_meta($media_id, "moderate-privacy");
		$rtmediamedia = new RTMediaMedia();
		$data = array(
		    "privacy" => $prev_privacy,
		);
		$rtmediamedia->update ( $media_id,$data, rtmedia_media_id($media_id) );
	    }
	    $rtmediainteraction->delete($where);
	    $return["next"] = $this->label;
	} else {
	    $value = "Media moderated";
	    $take_down_content = $this->rtmedia_moderate_media($user_id,$media_id,$action,$this->media->media_id,$this->media->privacy,$value);
	    $return["next"] = $this->undo_label;
	    if($take_down_content) {
		$return["rt_redirect"] = $_SERVER["HTTP_REFERER"];
		do_action("rtmedia_pro_after_report_media", $this);
		echo json_encode($return);
		die();
	    }
	}
	do_action("rtmedia_pro_after_report_media", $this);
	if(isset($_REQUEST["json"]) && $_REQUEST["json"]=="true"){
	    echo json_encode($return);
	    die();
	}
	die();
    }

    function rtmedia_moderate_media($user_id,$media_id,$action,$media_post_id,$privacy,$value="Media moderated") {
	$columns = array(
                'user_id' =>  $user_id,
                'media_id' => $media_id,
                'action' => $action,
                'value' => $value
            );
	$rtmediainteraction = new RTMediaInteractionModel();
	$insert_id = $rtmediainteraction->insert($columns);
	if($this->check_instance_admin_notification()) {
	    $this->send_admin_notification($media_id);
	}
	if($this->check_take_down_contet($media_id, $action)) {
	    $curr_privacy = $privacy;
	    $rtmediameta = new RTMediaMeta();
	    $rtmediameta->update_meta($media_id,"moderate-privacy",$curr_privacy);
	    $rtmediamedia = new RTMediaMedia();
	    $data = array(
		"privacy" => "80",
	    );
	    $rtmediamedia->update ( $media_id,$data, $media_post_id );
	    return true;
	}
	return false;
    }

    function moderate_after_media_upload( $media_ids, $file_object, $uploaded ) {
	if(is_array($uploaded) && sizeof($uploaded) > 0 ) {
	    $media_id = 0;
	    if($media_ids && is_array($media_ids) && isset($media_ids[0])) {
		$media_id = $media_ids[0];
	    }
	    $user_id = get_current_user_id();
	    $action = $this->action;
	    $privacy = (isset($uploaded['privacy']) && $uploaded['privacy'] != "")?$uploaded['privacy'] : 0;
	    $media_post_id = rtmedia_media_id($media_id);
	    $this->rtmedia_moderate_media($user_id, $media_id, $action,$media_post_id,$privacy);
	}
    }

    function where_query_moderate($where, $table_name, $join) {
	$where.= " and $table_name.privacy = '80' ";
	return $where;
    }

    function join_query_moderate($join, $table_name) {
	global $rtmedia;
	$rtmediainteaction = new RTMediaInteractionModel();
	$join_table = $rtmediainteaction->table_name;
	$option = $rtmedia->options;
	if(isset($option['moderation_emailNotificationFreq']) && $option['moderation_emailNotificationFreq']!= "") {
	    $mail_freq = $option['moderation_emailNotificationFreq'];
	    switch ($mail_freq) {
		case "hourly" :
		    $start_date = "DATE_ADD( NOW(), INTERVAL -1 HOUR )";
		    break;
		default :
		    $start_date = "DATE_ADD( NOW(), INTERVAL -1 DAY )";
	    }
	    $join .= " INNER JOIN {$join_table} ON ( {$table_name}.id = {$join_table}.media_id AND ( {$join_table}.action_date > $start_date and {$join_table}.action_date < now() ) ) AND ( {$join_table}.action = '$this->action' ) ";
	}
	return $join;
    }

    function get_moderate_media_info($media_id = false) {
	$rtmediamodel = new RTMediaModel();
	$columns = array();
	if($media_id) {
	    $columns["id"] = $media_id;
	    $moderate_media = $rtmediamodel->get($columns);
	} else {
	    add_filter("rtmedia-model-join-query",array($this,"join_query_moderate"), 20, 2);
	    add_filter("rtmedia-model-where-query",array($this,"where_query_moderate"), 20, 3);
	    $columns["media_type"] = array("music", "photo", "video");
	    $moderate_media = $rtmediamodel->get($columns);
	    remove_filter("rtmedia-model-where-query",array($this,"where_query_moderate"), 20, 3);
	    remove_filter("rtmedia-model-join-query",array($this,"join_query_moderate"), 20, 2);
	}
	return $moderate_media;
    }

    static function get_mail_content($media_info = "") {
	if(is_array($media_info) && $media_info != "" && sizeof($media_info) != 0) {
	    $html_tr = "";
	    foreach($media_info as $key=>$media) {
		$html_tr.= "<tr>
				<td style='padding: 10px;border-bottom: 1px solid #CCC;'>".  get_rtmedia_permalink($media->id)."</td>
			    </tr>";
	    }

	    $html = '<html>
			<head>
			    <title>Moderate media information</title>
			</head>
			<body>
			    <table border="0" cellpeding="0" cellspacing="0" width="100%">
				<tr>
				    <th style="background: #ccc;padding: 10px;">' .   __("Reported Media") . '</th>
				</tr> '.$html_tr.'
			    </table>
			</body>
		    </html>';
	    return $html;
	} else {
	    return false;
	}
    }

    function send_admin_notification($media_id = false) {
	global $rtmedia;
	$to = $rtmedia->options['moderation_adminEmails'];
	$subject = "Moderation Notification";
	$media_info = $this->get_moderate_media_info($media_id);
	$header = "";
	if($media_info != "") {
	    add_filter('wp_mail_content_type', create_function('', 'return "text/html";'));
	    $message = self::get_mail_content($media_info);
	    //error_log(var_export($message));
	    if($message && $message != "") {
		wp_mail( $to, $subject, $message, $header);
	    }
	}
    }
}
