<?php
/**
* Hero Themes - Core Theme Functions
* by Hero Themes (http://herothemes.com)
*/

/**
* Feature Handles
* 'wp-thumb' = Load dynamic responsive images feature
* 'theme-updates' = 
* 'font-awesome' = Load Font Awesome
* 'tgm-plugin-activation' = Load TGM Class
* 'cmb_meta_boxes' = Meta Box Library
*/

/*
* Hero Themes HT Core support filter
* Usage (in theme) add_theme_support('ht-core', 'feature1', 'feature2') or add_theme_support('ht')
* Usage (in plugin) current_theme_support('ht-core', 'feature1')
*/
function ht_core_features_filter($support, $features, $theme_supports){	
	if(!is_array($theme_supports))
		return false;
	else
		return !array_diff($features,$theme_supports);	
}
add_filter('current_theme_supports-ht-core', 'ht_core_features_filter', 10, 3);


/**
* TGM Class
*/
require("libraries/tgm-plugin-activation/class-tgm-plugin-activation.php");

/**
* Load Theme Updates
*/
if(current_theme_supports('ht-core', 'theme-updates' )){
	require_once('libraries/ht-theme-updates/ht-theme-updates.php');
}

/**
 * Initialize the metabox class.
 */
function cmb_initialize_cmb_meta_boxes() {
	require_once 'libraries/cmb2/init.php';
}
if(current_theme_supports('ht-core', 'cmb_meta_boxes' )){
	add_action( 'init', 'cmb_initialize_cmb_meta_boxes',  10);
}

/**
 * Load Font Awesome
 */
function ht_core_font_awesome() {
	wp_enqueue_style( 'font-awesome', get_template_directory_uri() . '/inc/ht-core/libraries/font-awesome/css/font-awesome.min.css', array('ht-theme-style') );		
}
if(current_theme_supports('ht-core', 'font-awesome' )){
	add_action( 'wp_enqueue_scripts', 'ht_core_font_awesome' );
}

/**
* Load Post Formats
*/
if( current_theme_supports('post-formats') ) {
	require_once('ht-post-formats.php');
}	

/**
* Load WP Thumb Library & Reponsive Image Functions
*/
if ( current_theme_supports('ht-core', 'wp-thumb' ) ) {
	if ( !class_exists( 'WP_Thumb' ) ) {
	require("libraries/wpthumb/wpthumb.php");
	}
	require("ht-responsive-images.php");
}

/**
* Load jQuery.appear Library
*/
if( current_theme_supports('ht-core', 'jquery-appear') ) {
	function ht_core_jquery_appear_scripts() {
		// Load jQuery Script
		wp_enqueue_script('jquery-appear', get_template_directory_uri() . '/inc/ht-core/js/jquery.appear.js', array( 'jquery' ), false, true);
	}
	add_action('wp_enqueue_scripts', 'ht_core_jquery_appear_scripts');
}

/**
* Load Custom Customizer Control Library
*/
if( current_theme_supports('ht-core', 'custom-customizer') ) {
	require("ht-customizer-controls.php");
}

/**
* Load HT Core Functions
*/
require("ht-core-functions.php");

/**
* Load Display Functions
*/
require("ht-display-functions.php");

/**
* Load Color Functions
*/
require("ht-color-functions.php");