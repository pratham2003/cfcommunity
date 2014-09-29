<?php
/**
 * Roots includes
 */
require_once locate_template('/lib/init.php');            // Initial theme setup and constants
require_once locate_template('/lib/scripts.php');         // Scripts and stylesheets

/**
 * CFCommunity Custom Functionality
 */
require_once locate_template('/lib/custom.php');          // Custom functions
require_once locate_template('/lib/menus.php');             // BP Menu Walker
require_once locate_template('/lib/sidebars.php');          // BP Sidebars
require_once locate_template('/lib/buddypress-cfc.php');   // BuddyPress Sidebars
require_once locate_template('/lib/buddypress/bp-actions.php');   // BuddyPress Sidebars

// add WP Thumb for dynamic thumbnails across the theme.
if( !class_exists( 'WP_Thumb' ) ){
    require_once locate_template( '/lib/vendor/WPThumb/wpthumb.php' );
}
?>