<?php
function templatepack_work() {
	bp_register_theme_package( array(
		'id'      => 'templates',
		'name'    => __( 'BuddyPress Templates', 'buddypress' ),
		'version' => bp_get_version(),

		// Adjust these to point to your dev server if necessary
		'dir'     => plugin_dir_path( __FILE__ ),
		'url'     => plugin_dir_url( __FILE__ ),
	) );
}
add_action( 'bp_register_theme_packages', 'templatepack_work' );

function templatepack_package_id( $package_id ) {
	return 'templates';
}
add_filter( 'pre_option__bp_theme_package_id', 'templatepack_package_id' );

// Proposed BP core change: see http://buddypress.trac.wordpress.org/ticket/3741#comment:43
function templatepack_kill_legacy_js_and_css() {
	wp_dequeue_script( 'groups_widget_groups_list-js' );
	wp_dequeue_script( 'bp_core_widget_members-js' );
}
add_action( 'wp', 'templatepack_kill_legacy_js_and_css', 999 );

if ( ! defined( 'BP_DEFAULT_COMPONENT' ) )
	define( 'BP_DEFAULT_COMPONENT', 'profile' );
