<?php
/**
 * Roots includes
 */
require_once locate_template('/lib/init.php');            // Initial theme setup and constants
require_once locate_template('/lib/scripts.php');         // Scripts and stylesheets

/**
 * CFCommunity Custom Functionality
 */
require_once locate_template('/lib/cfcommunity/custom.php');          // Custom functions
require_once locate_template('/lib/cfcommunity/menus.php');             // BP Menu Walker
require_once locate_template('/lib/cfcommunity/sidebars.php');          // BP Sidebars
require_once locate_template('/lib/cfcommunity/buddypress-cbox.php');   // BuddyPress Sidebars
require_once locate_template('/lib/cfcommunity/buddypress/bp-hooks.php');   // BuddyPress Sidebars

// add WP Thumb for dynamic thumbnails across the theme.
if( !class_exists( 'WP_Thumb' ) ){
    require_once locate_template( '/lib/vendor/WPThumb/wpthumb.php' );
}
?>