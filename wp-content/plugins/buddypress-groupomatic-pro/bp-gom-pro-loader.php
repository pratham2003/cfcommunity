<?php
/*
Plugin Name: BuddyPress Group-O-Matic Pro
Plugin URI: http://community.presscrew.com/
Description: This plugin adds premium features to the freely available BuddyPress Group-O-Matic plugin
Author: Marshall Sorenson (MrMaz)
Author URI: http://community.presscrew.com/
License: GNU GENERAL PUBLIC LICENSE 2.0 or later http://www.gnu.org/licenses/gpl.txt
Version: 1.0.3
Text Domain: buddypress-groupomatic
*/

////////////////////////////////
// Important Internal Constants
// *** DO NOT MODIFY THESE ***

// Configuration
define( 'BP_GOM_PRO_VERSION', '1.0.3' );
define( 'BP_GOM_PRO_VERSION_ID', '100' );
define( 'BP_GOM_PRO_PLUGIN_NAME', basename( dirname( __FILE__ ) ) );
// user meta keys
define( 'BP_GOM_META_KEY_PROFILE_COMPLETE', 'bp_gom_profile_complete' );
define( 'BP_GOM_META_KEY_BLOCKING_STEP', 'bp_gom_blocking_step' );

// core Paths
define( 'BP_GOM_PRO_PLUGIN_DIR', WP_PLUGIN_DIR . '/' . BP_GOM_PRO_PLUGIN_NAME );
define( 'BP_GOM_PRO_PLUGIN_URL', WP_PLUGIN_URL . '/' . BP_GOM_PRO_PLUGIN_NAME );

// ***************************
///////////////////////////////

//
// Plugin Bootstrap Functions
//

/**
 * Handle special BP initialization
 */
function bp_gom_pro_init() {

	// this plugin is useless without the free version
	if ( !defined( 'BP_GOM_VERSION' ) ) {
		return;
	}

	// load core
	require_once BP_GOM_PRO_PLUGIN_DIR . '/bp-gom-pro-matching.php';
	require_once BP_GOM_PRO_PLUGIN_DIR . '/bp-gom-pro-activity.php';

	if ( is_admin() ) {
		require_once BP_GOM_PRO_PLUGIN_DIR . '/bp-gom-pro-admin.php';
	} else {
		require_once BP_GOM_PRO_PLUGIN_DIR . '/bp-gom-pro-blocking.php';
	}

	do_action( 'bp_gom_pro_init' );
}
add_action( 'bp_gom_init', 'bp_gom_pro_init' );

?>
