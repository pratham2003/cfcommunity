<?php

function rtmedia_main_template_include($template, $new_rt_template) {
	global $wp_query;
$wp_query->is_page = true;
	return locate_template('base-index.php');
}
add_filter('rtmedia_main_template_include', 'rtmedia_main_template_include', 98, 99);

/**
 * Roots includes
 */
require_once locate_template('/lib/utils.php');           // Utility functions
require_once locate_template('/lib/init.php');            // Initial theme setup and constants
require_once locate_template('/lib/wrapper.php');         // Theme wrapper class
require_once locate_template('/lib/sidebar.php');         // Sidebar class
require_once locate_template('/lib/config.php');          // Configuration
//require_once locate_template('/lib/activation.php');      // Theme activation
require_once locate_template('/lib/titles.php');          // Page titles
require_once locate_template('/lib/cleanup.php');         // Cleanup
require_once locate_template('/lib/nav.php');             // Custom nav modifications
require_once locate_template('/lib/gallery.php');         // Custom [gallery] modifications
require_once locate_template('/lib/comments.php');        // Custom comments modifications
//require_once locate_template('/lib/relative-urls.php');   // Root relative URLs
//require_once locate_template('/lib/widgets.php');         // Sidebars and widgets
require_once locate_template('/lib/scripts.php');         // Scripts and stylesheets

/**
 * CFCommunity Custom Functionality
 */
require_once locate_template('/lib/cfcommunity/setup.php');          // Custom functions

// add WP Thumb for dynamic thumbnails across the theme.
if( !class_exists( 'WP_Thumb' ) ){
    require_once locate_template( '/lib/vendor/WPThumb/wpthumb.php' );
}
?>