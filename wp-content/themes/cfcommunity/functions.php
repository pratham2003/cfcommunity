<?php
/**
 * Theme Setup
 */
require_once locate_template('/lib/init.php');            	// Initial theme setup and constants
require_once locate_template('/lib/scripts.php');         	// Scripts and stylesheets
require_once locate_template('/lib/custom.php');          	// Custom functions
require_once locate_template('/lib/menus.php');             // BP Menu Walker
require_once locate_template('/lib/sidebars.php');          // BP Sidebars

//BuddyPress Specific
if ( function_exists( 'bp_is_member' ) ) {
	require_once locate_template('/lib/buddypress/bp-general.php');
	require_once locate_template('/lib/buddypress/bp-actions.php');
	require_once locate_template('/lib/buddypress/bp-filters.php');
	require_once locate_template('/lib/buddypress/bp-hooks.php');

	// Cover photo (needs RtMedia)
	require_once locate_template('/lib/buddypress/bp-cover-photo.php');
}

//EDD Specific
if( class_exists( 'Easy_Digital_Downloads' ) ){
	require_once locate_template('/lib/edd-functions.php');
}


// add WP Thumb for dynamic thumbnails across the theme.
if( !class_exists( 'WP_Thumb' ) ){
    require_once locate_template( '/lib/vendor/WPThumb/wpthumb.php' );
}
?>