<?php
/**
 * File includes for this Child Theme. Mimics the parent folder structure. Include more if needed.
 */

add_filter('show_admin_bar', '__return_false');

if( !class_exists( 'WP_Thumb' ) ){
	require_once locate_template('/lib/WPThumb/wpthumb.php');	
}

require_once locate_template('/includes/functions.php');					// Utility functions
require_once locate_template('/includes/scripts.php');            			// Initial theme setup and constants
require_once locate_template('/includes/templatetags.php');         		// Theme wrapper class

/**
 * Extended Roots functionality
 */
require_once locate_template('/lib/base.php');				// Post Formats
require_once locate_template('/lib/menus.php');				// BP Menu Walker
require_once locate_template('/lib/sidebars.php');			// BP Sidebars
require_once locate_template('/lib/setup-cbox.php');		// CBOX Compatability functions
require_once locate_template('/lib/sidebars-cbox.php');		// CBOX Sidebars
require_once locate_template('/lib/buddypress-cbox.php');	// BuddyPress Sidebars
?>