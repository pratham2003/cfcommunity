<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of RTMediaProUploadLimit
 *
 * @author ritz
 */
class RTMediaProUploadLimit {

	function __construct() {
		// add upload limit admin settings
		add_action( 'rtmedia_after_media_types_settings', array( $this, 'upload_limit_admin_setting' ) );
		add_filter( 'rtmedia_allow_uploader_view', array( $this, 'rtmedia_allow_uploader_view' ), 10, 2 );
		add_filter( 'rtmedia_modify_upload_params', array( $this, 'modify_upload_params' ), 10, 1 );
		add_filter( 'rtmedia_valid_type_check', array( $this, 'rtmedia_valid_type_check' ), 99, 2 );
	}

	function rtmedia_valid_type_check( $valid, $file ) {
		global $rtmedia;
		$options = $rtmedia->options;
		if ( ( isset( $options[ 'user_storage_limit_daily' ] ) && $options[ 'user_storage_limit_daily' ] == "0" ) && ( isset( $options[ 'user_storage_limit_monthly' ] ) && $options[ 'user_storage_limit_monthly' ] == "0" ) && ( isset( $options[ 'user_storage_limit_lifetime' ] ) && $options[ 'user_storage_limit_lifetime' ] == "0" ) && ( isset( $options[ 'user_files_limit_daily' ] ) && $options[ 'user_files_limit_daily' ] == "0" ) && ( isset( $options[ 'user_files_limit_monthly' ] ) && $options[ 'user_files_limit_monthly' ] == "0" ) && ( isset( $options[ 'user_files_limit_lifetime' ] ) && $options[ 'user_files_limit_lifetime' ] == "0" ) ){
			return $valid;
		}
		if ( $valid && isset( $options[ 'user_storage_limit_daily' ] ) && $options[ 'user_storage_limit_daily' ] != "0" ){
			$allowed_size   = $options[ 'user_storage_limit_daily' ] * ( 1024 * 1024 ); // convert MB into byte
			$res_daily_size = $this->get_limits( 'daily', 'size' );
			if ( $res_daily_size && ( $res_daily_size + $file['size'] ) >= $allowed_size ){
				$valid = false;
			}
		}
		if ( $valid && isset( $options[ 'user_storage_limit_monthly' ] ) && $options[ 'user_storage_limit_monthly' ] != "0" ){
			$allowed_size    = $options[ 'user_storage_limit_monthly' ] * ( 1024 * 1024 ); // convert MB into byte
			$res_mothly_size = $this->get_limits( 'monthly', 'size' );
			if ( $res_mothly_size && ( $res_mothly_size + $file['size'] ) >= $allowed_size ){
				$valid = false;
			}
		}
		if ( $valid && isset( $options[ 'user_storage_limit_lifetime' ] ) && $options[ 'user_storage_limit_lifetime' ] != "0" ){
			$allowed_size       = $options[ 'user_storage_limit_lifetime' ] * ( 1024 * 1024 ); // convert MB into byte
			$res_life_time_size = $this->get_limits( 'lifetime', 'size' );
			if ( $res_life_time_size && ( $res_life_time_size + $file['size'] ) >= $allowed_size ){
				$valid = false;
			}
		}
		if ( $valid && isset( $options[ 'user_files_limit_daily' ] ) && $options[ 'user_files_limit_daily' ] != "0" ){
			$allowed_files        = $options[ 'user_files_limit_daily' ];
			$res_daily_file_limit = $this->get_limits( 'daily', 'files' );
			if ( $res_daily_file_limit && $res_daily_file_limit  >= $allowed_files ){
				$valid = false;
			}
		}
		if ( $valid && isset( $options[ 'user_files_limit_monthly' ] ) && $options[ 'user_files_limit_monthly' ] != "0" ){
			$allowed_files          = $options[ 'user_files_limit_monthly' ];
			$res_monthly_file_limit = $this->get_limits( 'monthly', 'files' );
			if ( $res_monthly_file_limit && $res_monthly_file_limit  >= $allowed_files ){
				$valid = false;
			}
		}
		if ( $valid && isset( $options[ 'user_files_limit_lifetime' ] ) && $options[ 'user_files_limit_lifetime' ] != "0" ){
			$allowed_files            = $options[ 'user_files_limit_lifetime' ];
			$res_life_time_file_limit = $this->get_limits( 'lifetime', 'files' );
			if ( $res_life_time_file_limit && $res_life_time_file_limit  >= $allowed_files ){
				$valid = false;
			}
		}
		return $valid;
	}

	function modify_upload_params( $params ) {
        if( apply_filters( 'rtmedia_pro_allow_upload_limit_params', true ) ) {
            global $rtmedia;
            $options = $rtmedia->options;

            $upload_limit = array();
            $upload_limit['size']['daily'] = ( isset( $options[ 'user_storage_limit_daily' ] ) ? $options[ 'user_storage_limit_daily' ] : "0" );
            $upload_limit['size']['monthly'] = ( isset( $options[ 'user_storage_limit_monthly' ] ) ? $options[ 'user_storage_limit_monthly' ] : "0" );
            $upload_limit['size']['lifetime'] = ( isset( $options[ 'user_storage_limit_lifetime' ] ) ? $options[ 'user_storage_limit_lifetime' ] : "0" );
            $upload_limit['files']['daily'] = ( isset( $options[ 'user_files_limit_daily' ] ) ? $options[ 'user_files_limit_daily' ] : "0" );
            $upload_limit['files']['monthly'] = ( isset( $options[ 'user_files_limit_monthly' ] ) ? $options[ 'user_files_limit_monthly' ] : "0" );
            $upload_limit['files']['lifetime'] = ( isset( $options[ 'user_files_limit_lifetime' ] ) ? $options[ 'user_files_limit_lifetime' ] : "0" );
            $params[ 'rtmedia_pro_upload_limits' ] = $upload_limit;

            $upload_limit_current = array();
            $upload_limit_current['size']['daily'] = $this->get_limits( 'daily', 'size' );
            $upload_limit_current['size']['monthly'] = $this->get_limits( 'monthly', 'size' );
            $upload_limit_current['size']['lifetime'] = $this->get_limits( 'lifetime', 'size' );
            $upload_limit_current['files']['daily'] = $this->get_limits( 'daily', 'files' );
            $upload_limit_current['files']['monthly'] = $this->get_limits( 'monthly', 'files' );
            $upload_limit_current['files']['lifetime'] = $this->get_limits( 'lifetime', 'files' );
            $params[ 'rtmedia_pro_upload_limits_current_stats' ] = $upload_limit_current;
        }

		return $params;
	}

	function get_limits( $time_span, $context ) {
		global $wpdb;
		switch ( $time_span ) {
			case "daily" : {
				$start_date = date( 'Y-m-d', strtotime( 'yesterday' ) );
				$start_date .= " 23:59:59";
			}
				break;
			case "monthly" : {
				$start_date = date( 'Y-m-d', strtotime( 'last day of last month' ) );
				$start_date .= " 23:59:59";
			}
				break;
			case "lifetime" : {
				// do not set $start_date so that it will understand that this is lifetime context
			}
				break;
		   	default : {
				return false;
			}
		}

		switch ( $context ) {
			case "size" : {
				$column = " SUM(file_size) ";
			}
				break;
			default : {
				$column = " count(*) ";
			}
		}
		$rtmedia_model = new RTMediaModel();
		$user_id       = get_current_user_id();
		$end_date      = date( 'Y-m-d', strtotime( 'tomorrow' ) );
		$end_date .= " 00:00:00";
		if( isset( $start_date ) && $start_date != "" ) {
			$sql_limit = "	SELECT " . $column  . "
			from {$rtmedia_model->table_name}
			WHERE media_author = '" . $user_id . "' AND upload_date > '" . $start_date . "' AND upload_date < '" . $end_date . "' ";
		} else {
			$sql_limit = "	SELECT " . $column  . "
			from {$rtmedia_model->table_name}
			WHERE media_author = '" . $user_id . "' ";
		}
		$res_limit = $wpdb->get_results( $sql_limit, ARRAY_N );
		if( isset( $res_limit ) && isset( $res_limit[0] ) && isset( $res_limit[0][0] ) ) {
			return $res_limit[0][0];
		} else {
			return false;
		}
	}

	function exceed_daily_storage_message( $message, $section ) {
		return __( 'You can not upload any media today as you had exceeded daily limit of media size.', 'rtmedia' );
	}

	function exceed_monthly_storage_message( $message, $section ) {
		return __( 'You can not upload any media in this month as you had exceeded monthly limit of media size.', 'rtmedia' );
	}

	function exceed_lifetime_storage_message( $message, $section ) {
		return __( 'You can not upload any media as you had exceeded upload limit of media size.', 'rtmedia' );
	}

	function exceed_daily_files_message( $message, $section ) {
		return __( 'You can not upload any media today as you had exceeded daily limit to upload media.', 'rtmedia' );
	}

	function exceed_monthly_files_message( $message, $section ) {
		return __( 'You can not upload any media in this month as you had exceeded monthly limit to upload media.', 'rtmedia' );
	}

	function exceed_lifetime_files_message( $message, $section ) {
		return __( 'You can not upload any media as you had exceeded the limit to upload media.', 'rtmedia' );
	}

	function rtmedia_allow_uploader_view( $allow, $section ) {
		global $rtmedia;
		$options = $rtmedia->options;
		if ( ( isset( $options[ 'user_storage_limit_daily' ] ) && $options[ 'user_storage_limit_daily' ] == "0" ) && ( isset( $options[ 'user_storage_limit_monthly' ] ) && $options[ 'user_storage_limit_monthly' ] == "0" ) && ( isset( $options[ 'user_storage_limit_lifetime' ] ) && $options[ 'user_storage_limit_lifetime' ] == "0" ) && ( isset( $options[ 'user_files_limit_daily' ] ) && $options[ 'user_files_limit_daily' ] == "0" ) && ( isset( $options[ 'user_files_limit_monthly' ] ) && $options[ 'user_files_limit_monthly' ] == "0" ) && ( isset( $options[ 'user_files_limit_lifetime' ] ) && $options[ 'user_files_limit_lifetime' ] == "0" ) ){
			return $allow;
		}
		if ( $allow && isset( $options[ 'user_storage_limit_daily' ] ) && $options[ 'user_storage_limit_daily' ] != "0" ){
			$allowed_size   = $options[ 'user_storage_limit_daily' ] * ( 1024 * 1024 ); // convert MB into byte
			$res_daily_size = $this->get_limits( 'daily', 'size' );
			if ( $res_daily_size && $res_daily_size >= $allowed_size ){
				$allow = false;
				add_filter( 'rtmedia_upload_not_allowed_message', array( $this, 'exceed_daily_storage_message' ) , 10, 2 );
			}
		}
		if ( $allow && isset( $options[ 'user_storage_limit_monthly' ] ) && $options[ 'user_storage_limit_monthly' ] != "0" ){
			$allowed_size    = $options[ 'user_storage_limit_monthly' ] * ( 1024 * 1024 ); // convert MB into byte
			$res_mothly_size = $this->get_limits( 'monthly', 'size' );
			if ( $res_mothly_size && $res_mothly_size >= $allowed_size ){
				$allow = false;
				add_filter( 'rtmedia_upload_not_allowed_message', array( $this, 'exceed_monthly_storage_message' ) , 10, 2 );
			}
		}
		if ( $allow && isset( $options[ 'user_storage_limit_lifetime' ] ) && $options[ 'user_storage_limit_lifetime' ] != "0" ){
			$allowed_size       = $options[ 'user_storage_limit_lifetime' ] * ( 1024 * 1024 ); // convert MB into byte
			$res_life_time_size = $this->get_limits( 'lifetime', 'size' );
			if ( $res_life_time_size && $res_life_time_size >= $allowed_size ){
				$allow = false;
				add_filter( 'rtmedia_upload_not_allowed_message', array( $this, 'exceed_lifetime_storage_message' ) , 10, 2 );
			}
		}
		if ( $allow && isset( $options[ 'user_files_limit_daily' ] ) && $options[ 'user_files_limit_daily' ] != "0" ){
			$allowed_files        = $options[ 'user_files_limit_daily' ]; // convert MB into byte
			$res_daily_file_limit = $this->get_limits( 'daily', 'files' );
			if ( $res_daily_file_limit && $res_daily_file_limit >= $allowed_files ){
				$allow = false;
				add_filter( 'rtmedia_upload_not_allowed_message', array( $this, 'exceed_daily_files_message' ) , 10, 2 );
			}
		}
		if ( $allow && isset( $options[ 'user_files_limit_monthly' ] ) && $options[ 'user_files_limit_monthly' ] != "0" ){
			$allowed_files          = $options[ 'user_files_limit_monthly' ]; // convert MB into byte
			$res_monthly_file_limit = $this->get_limits( 'monthly', 'files' );
			if ( $res_monthly_file_limit && $res_monthly_file_limit >= $allowed_files ){
				$allow = false;
				add_filter( 'rtmedia_upload_not_allowed_message', array( $this, 'exceed_monthly_files_message' ) , 10, 2 );
			}
		}
		if ( $allow && isset( $options[ 'user_files_limit_lifetime' ] ) && $options[ 'user_files_limit_lifetime' ] != "0" ){
			$allowed_files            = $options[ 'user_files_limit_lifetime' ]; // convert MB into byte
			$res_life_time_file_limit = $this->get_limits( 'lifetime', 'files' );
			if ( $res_life_time_file_limit && $res_life_time_file_limit >= $allowed_files ){
				$allow = false;
				add_filter( 'rtmedia_upload_not_allowed_message', array( $this, 'exceed_lifetime_files_message' ) , 10, 2 );
			}
		}
		return $allow;
	}

	function upload_limit_admin_setting() {
		global $rtmedia;
		$options       = $rtmedia->options;
		$render_limits = array(
			'user_storage_limit_daily'       => array(
				'title' => __( 'User\'s daily storage limit ', 'rtmedia' ), 'callback' => array( 'RTMediaFormHandler', 'number' ), 'args' => array(
					'key' => 'user_storage_limit_daily', 'value' => $options[ 'user_storage_limit_daily' ], 'class' => array( 'rtmedia-setting-text-box' ), 'desc' => __( 'Set how much amount a user can upload in terms of daily storage. 0 means unlimited.', 'rtmedia' ), 'min' => 0, 'id' => "rtmedia_storage_limit_daily",
				),
			), 'user_storage_limit_monthly'  => array(
				'title' => __( 'User\'s monthly storage limit ', 'rtmedia' ), 'callback' => array( 'RTMediaFormHandler', 'number' ), 'args' => array(
					'key' => 'user_storage_limit_monthly', 'value' => $options[ 'user_storage_limit_monthly' ], 'class' => array( 'rtmedia-setting-text-box' ), 'desc' => __( 'Set how much amount a user can upload in terms of monthly storage. 0 means unlimited.', 'rtmedia' ), 'min' => 0, 'id' => "rtmedia_storage_limit_monthly",
				),
			), 'user_storage_limit_lifetime' => array(
				'title' => __( 'User\'s lifetime storage limit ', 'rtmedia' ), 'callback' => array( 'RTMediaFormHandler', 'number' ), 'args' => array(
					'key' => 'user_storage_limit_lifetime', 'value' => $options[ 'user_storage_limit_lifetime' ], 'class' => array( 'rtmedia-setting-text-box' ), 'desc' => __( 'Set how much amount a user can upload in terms of lifetime storage (One time only). 0 means unlimited.', 'rtmedia' ), 'min' => 0, 'id' => "rtmedia_storage_limit_lifetime",
				)
			), 'user_files_limit_daily'      => array(
				'title' => __( 'User\'s daily file limit ', 'rtmedia' ), 'callback' => array( 'RTMediaFormHandler', 'number' ), 'args' => array(
					'key' => 'user_files_limit_daily', 'value' => $options[ 'user_files_limit_daily' ], 'class' => array( 'rtmedia-setting-text-box' ), 'desc' => __( 'Set how many files user can upload daily. 0 means unlimited.', 'rtmedia' ), 'min' => 0, 'id' => "rtmedia_files_limit_daily",
				)
			), 'user_files_limit_monthly'    => array(
				'title' => __( 'User\'s monthly file limit ', 'rtmedia' ), 'callback' => array( 'RTMediaFormHandler', 'number' ), 'args' => array(
					'key' => 'user_files_limit_monthly', 'value' => $options[ 'user_files_limit_monthly' ], 'class' => array( 'rtmedia-setting-text-box' ), 'desc' => __( 'Set how many files user can upload monthly. 0 means unlimited.', 'rtmedia' ), 'min' => 0, 'id' => "rtmedia_files_limit_monthly",
				)
			), 'user_files_limit_lifetime'   => array(
				'title' => __( 'User\'s lifetime file limit ', 'rtmedia' ), 'callback' => array( 'RTMediaFormHandler', 'number' ), 'args' => array(
					'key' => 'user_files_limit_lifetime', 'value' => $options[ 'user_files_limit_lifetime' ], 'class' => array( 'rtmedia-setting-text-box' ), 'desc' => __( 'Set how many files user can upload lifetime (One time only). 0 means unlimited.', 'rtmedia' ), 'min' => 0, 'id' => "rtmedia_files_limit_lifetime",
				)
			),
		);
		$render_limits = apply_filters( 'rtmedia_pro_upload_limit', $render_limits );
		?>
		<hr>
		<div class="rt-table large-12">
			<div class="row rt-header">
				<div class="columns large-6"><h4><?php _e( "Upload Limits Per User", "rtmedia" ) ?></h4></div>
				<div class="columns large-2"><h4><?php _e( "Daily", "rtmedia" ); ?></h4></div>
				<div class="columns large-2"><h4><?php _e( "Monthly", "rtmedia" ); ?></h4></div>
				<div class="columns large-2"><h4><?php _e( "Lifetime", "rtmedia" ); ?></h4></div>
			</div>

			<?php
			$render_groups = array( 'user_storage_limit' => __( 'Maximum storage a user can use (in MB)', 'rtmedia' ), 'user_files_limit' => __( 'Maximum number of files a user can upload', 'rtmedia' ) );
			$even = 0;
			foreach ( $render_groups as $key => $desc ) {
				if ( ++$even % 2 ){
					echo '<div class="row rt-odd">';
				} else {
					echo '<div class="row rt-even">';
				}
				echo '<div class="columns large-6">' . $desc . '</div>';
				echo '<div class="columns large-2">';
				$args_daily = $render_limits [ $key . '_daily' ];
				call_user_func( $args_daily [ 'callback' ], $args_daily [ 'args' ] );
				echo '</div>';
				echo '<div class="columns large-2">';
				$args_monthly = $render_limits [ $key . '_monthly' ];
				call_user_func( $args_monthly [ 'callback' ], $args_monthly [ 'args' ] );
				echo '</div>';
				echo '<div class="columns large-2">';
				$args_lifetime = $render_limits [ $key . '_lifetime' ];
				call_user_func( $args_lifetime [ 'callback' ], $args_lifetime [ 'args' ] );
				echo '</div>';
				echo '</div>';
			}
			?>
		</div>
	<?php
	}

}