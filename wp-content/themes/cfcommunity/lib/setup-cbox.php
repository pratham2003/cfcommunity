<?php

/**
 * Don't allow WordPress to move widgets over to this theme, as it messes with
 * our own widget setup routine
 */
remove_action( 'after_switch_theme', '_wp_sidebars_changed' );
add_action( 'after_switch_theme', 'retrieve_widgets' );

/**
 * Options page title and menu title
 */
function cbox_theme_menu_title()
{
	return __( 'CBOX Theme Options', 'cbox' );
}
add_filter( 'infinity_dashboard_menu_setup_page_title', 'cbox_theme_menu_title' );
add_filter( 'infinity_dashboard_menu_setup_menu_title', 'cbox_theme_menu_title' );

//
// Slider
//

/**
 * Load metaboxes class callback: https://github.com/jaredatch/Custom-Metaboxes-and-Fields-for-WordPress
 */
function cbox_theme_init_cmb()
{
	if ( !class_exists( 'cmb_Meta_Box' ) ) {
		require_once( 'metaboxes/init.php' );
	}
}

/**
 * add WP Thumb for dynamic thumbnails across the theme: https://github.com/humanmade/WPThumb
 */ 
if( !class_exists( 'WP_Thumb' ) ){
	require_once( 'WPThumb/wpthumb.php' );
}

/**
 * Slider setup
 */
function cbox_theme_slider_setup()
{
	// load slider setup
	//require_once( 'feature-slider/setup.php' );

	// load meta box lib (a bit later)
	add_action( 'init', 'cbox_theme_init_cmb', 9999 );
}
add_action( 'after_setup_theme', 'cbox_theme_slider_setup' );

//
// Template Tags
//

if ( false === function_exists( 'the_post_name' ) ) {
	/**
	* Echo the post name (slug)
	*/
	function the_post_name()
	{
		// use global post
		global $post;

		// post_name property is the slug
		echo $post->post_name;
	}
}

/**
 * Automagically create a front page if one has not been set already
 */
function cbox_theme_auto_create_home_page()
{
	$is_root_blog = function_exists( 'bp_is_root_blog' ) ? bp_is_root_blog() : is_main_site();

	// if we're not on the root blog, do not auto create the homepage
	if ( ! $is_root_blog ) {
		return;
	}

	// get frontpage ID
	$front_page = get_option( 'page_on_front' );

	// no frontpage?
	if ( ! $front_page ) {

		// set our flag to create a page to true by default
		$create_page = true;

		// grab current auto-created home page id
		$home_page_id = get_option( '_cbox_theme_auto_create_home_page' );

		// we have a page ID, but does it still exist?
		if ( is_numeric( $home_page_id ) ) {
			// page exists, so set $create_page flag to false
			if ( get_post( $home_page_id ) ) {
				$create_page = false;
			}

		}
		
		// we need to create a new page
		if ( $create_page ) {
			// create the new page
			$home_page_id = wp_insert_post( array(
				'post_type'   => 'page',
				'post_title'  => 'Home Page',
				'post_status' => 'publish',
			) );

			// set the new page as the frontpage and use our homepage template
			update_post_meta( $home_page_id, '_wp_page_template', 'templates/homepage-template.php' );
			update_option( 'show_on_front', 'page' );
			update_option( 'page_on_front', $home_page_id );
			update_option( '_cbox_theme_auto_create_home_page', $home_page_id );
		}

	// check if front page still exists
	} else {
		// do this check only on 404 pages b/c if the front page doesn't exist,
		// the front page will 404, so we can run our check then to prevent
		// unnecessary DB queries on other pages
		if ( is_404() && get_post( $front_page ) === NULL ) {
			// front page no longer exists so purge the following options
			delete_option( 'page_on_front' );
			delete_option( '_cbox_theme_auto_create_home_page' );
			
			// redirect back to homepage
			wp_redirect( get_home_url() ); die();
		}
	}
}
add_action( 'wp', 'cbox_theme_auto_create_home_page' );
