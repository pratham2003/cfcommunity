<?php

/*
  Plugin Name: rtMedia Photo Tagging
  Plugin URI: http://rtcamp.com/buddypress-media/?utm_source=dashboard&utm_medium=plugin&utm_campaign=buddypress-media
  Description: Add user tagging to rtMedia photos.
  Version: 2.2.8
  Author: rtCamp
  Text Domain: rtm-photo-tagging
  Author URI: http://rtcamp.com/?utm_source=dashboard&utm_medium=plugin&utm_campaign=buddypress-media
 */
if ( ! defined ( 'RTMEDIA_PHOTO_TAGGING_PATH' ) ) {
    define ( 'RTMEDIA_PHOTO_TAGGING_PATH', plugin_dir_path ( __FILE__ ) );
}
if ( ! defined ( 'RTMEDIA_PHOTO_TAGGING_URL' ) ) {
    define ( 'RTMEDIA_PHOTO_TAGGING_URL', plugin_dir_url ( __FILE__ ) );
}
if ( ! defined( 'RTMEDIA_PHOTO_TAGGING_BASE_NAME' ) ){
	define ( 'RTMEDIA_PHOTO_TAGGING_BASE_NAME', plugin_basename( __FILE__ ) );
}
if ( ! defined ( 'RTMEDIA_PHOTO_TAGGING_VERSION' ) ) {
	define ( 'RTMEDIA_PHOTO_TAGGING_VERSION', '2.2.8' );
}

if ( ! defined ( 'EDD_RTMEDIA_PHOTO_TAGGING_STORE_URL' ) ) {
    // this is the URL our updater / license checker pings. This should be the URL of the site with EDD installed
    define( 'EDD_RTMEDIA_PHOTO_TAGGING_STORE_URL', 'https://rtcamp.com/' ); 
}

if ( ! defined ( 'EDD_RTMEDIA_PHOTO_TAGGING_ITEM_NAME' ) ) {
    // the name of your product. This should match the download name in EDD exactly
    define( 'EDD_RTMEDIA_PHOTO_TAGGING_ITEM_NAME', 'rtMedia Photo Tagging' ); 
}

set_site_transient( 'update_plugins', null );

add_filter ( 'rtmedia_class_construct', 'rtmedia_photo_tagging' );

function rtmedia_photo_tagging ( $class_construct ) {
    require_once RTMEDIA_PHOTO_TAGGING_PATH . 'app/RTMediaPhotoTag.php';
    $class_construct[ 'phototag' ] = false;
    return $class_construct;
}

add_action( 'plugins_loaded', 'rtm_photo_tagging_load_language', 10 );
function rtm_photo_tagging_load_language(){
    load_plugin_textdomain('rtm-photo-tagging', false,basename( RTMEDIA_PHOTO_TAGGING_PATH) . '/languages/');
}

include_once( RTMEDIA_PHOTO_TAGGING_PATH . 'lib/edd-license/RTMediaPhotoTaggingEDDLicense.php' );
new RTMediaPhotoTaggingEDDLicense();

