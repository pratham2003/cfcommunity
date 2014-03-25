<?php
/*
Plugin Name: BuddyPress Templates
Version: 1.0
*/

function templates_init() {
	include( plugin_dir_path(__FILE__) . 'bp-custom.php' );
}
add_action( 'bp_include', 'templates_init' );
