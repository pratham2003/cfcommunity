<?php
/*
Plugin Name: BuddyVerified
Plugin URI: http://taptappress.com
Description: Allows admins to specify verified accounts. Adds a badge to verified user avatars.
Author: modemlooper
Version: 2.2
Author URI: http://twitter.com/modemlooper
*/

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;


define( 'VERIFIED_URL', plugin_dir_url( __FILE__ ) );

/**
 * buddyverified_init function.
 * 
 * @access public
 * @return void
 */
function buddyverified_init() {
		require ( dirname( __FILE__ ) . '/includes/admin.php' );
		require ( dirname( __FILE__ ) . '/includes/functions.php' );
}
add_action( 'bp_include', 'buddyverified_init' );



/**
 * buddyverified_textdomain_init function.
 * 
 * @access public
 * @return void
 */
function buddyverified_textdomain_init() {
  load_plugin_textdomain( 'buddyverified', false, dirname( ( __FILE__ ) . '/languages/' ) );
}
add_action('plugins_loaded', 'buddyverified_textdomain_init');