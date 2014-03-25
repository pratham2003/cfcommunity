<?php
/*
Plugin Name: BP Mention
Plugin URI:  http://webdeveloperswall.com/buddypress/buddypress-mention-plugin
Description: This Buddypress plugin adds @username mention to status updates, comments, etc..
Version: 1.0
Revision Date: 08 21, 2012
Requires at least: WP 3.2.1, BuddyPress 1.5
Tested up to: WP 3.2.1, BuddyPress 1.6
License: GNU General Public License 2.0 (GPL) http://www.gnu.org/licenses/gpl.html
Author: webdeveloperswall
Author URI: http://webdeveloperswall.com/
Network: true
*/
// Define a constant that can be checked to see if the component is installed or not.
define( 'BP_MENTION_IS_INSTALLED', 1 );

// Define a constant that will hold the current version number of the component
// This can be useful if you need to run update scripts or do compatibility checks in the future
define( 'BP_MENTION_VERSION', '1.0' );

// Define a constant that we can use to construct file paths throughout the component
define( 'BP_MENTION_PLUGIN_DIR', dirname( __FILE__ ) );


define ( 'BP_MENTION_DB_VERSION', '1' );

/* Only load the component if BuddyPress is loaded and initialized. */
function bp_mention_init() {
	// Because our loader file uses BP_Component, it requires BP 1.5 or greater.
	if ( version_compare( BP_VERSION, '1.3', '>' ) )
		require( dirname( __FILE__ ) . '/includes/bp-mention-loader.php' );
}
add_action( 'bp_include', 'bp_mention_init' );

/* Put setup procedures to be run when the plugin is activated in the following function */
function bp_mention_activate() {
	
}
register_activation_hook( __FILE__, 'bp_mention_activate' );

/* On deacativation, clean up anything your component has added. */
function bp_mention_deactivate() {
	 
}
register_deactivation_hook( __FILE__, 'bp_mention_deactivate' );
?>
