<?php
/*
Plugin Name: CFCommunity Cleanup
Plugin URI: http://wordpress.org/extend/plugins/menus/
Description: Clean up some stuff!
*/

//Remove Yoast SEO Boxes
if ( ! is_main_site() ) {
  add_filter( 'wpseo_use_page_analysis', '__return_false' );
  add_filter( 'wpseo_use_page_analysis', '__return_false' );
}

//Remove version numbers
if ( defined( 'ENV_TYPE' ) && 'production' == ENV_TYPE ) {
  
  function rssv_scripts() {
    global $wp_scripts;
    if ( !is_a( $wp_scripts, 'WP_Scripts' ) )
      return;
    foreach ( $wp_scripts->registered as $handle => $script )
      $wp_scripts->registered[$handle]->ver = null;
  }

  function rssv_styles() {
    global $wp_styles;
    if ( !is_a( $wp_styles, 'WP_Styles' ) )
      return;
    foreach ( $wp_styles->registered as $handle => $style )
      $wp_styles->registered[$handle]->ver = null;
  }

  add_action( 'wp_print_scripts', 'rssv_scripts', 100 );
  add_action( 'wp_print_footer_scripts', 'rssv_scripts', 100 );

  add_action( 'admin_print_styles', 'rssv_styles', 100 );
  add_action( 'wp_print_styles', 'rssv_styles', 100 );

}


// Remove certain stylesheets from loading
function cfc_remove_style() {
    wp_deregister_style( 'rtmedia-font-awesome' );
    wp_dequeue_style( 'rtmedia-font-awesome' );

    wp_deregister_style( 'rtmedia-pro-rating-simple' );
    wp_dequeue_style( 'rtmedia-pro-rating-simple' );

    wp_deregister_style( 'rtmedia-pro-popular-photos-css' );
    wp_dequeue_style( 'rtmedia-pro-popular-photos-css' );

    wp_deregister_style( 'rtmedia-pro-playlist' );
    wp_dequeue_style( 'rtmedia-pro-playlist' );

    wp_deregister_style( 'bp-parent-css' );
    wp_dequeue_style( 'bp-parent-css' );

    wp_deregister_style( 'jfb' );
    wp_dequeue_style( 'jfb' );

}
//Deregister scripts
add_action( 'wp_enqueue_scripts', 'cfc_remove_style', 9999 );

// Remove certain scripts from loading
function cfc_remove_script() {

wp_deregister_script( 'rtmedia-pro-most-rated-photos-widget' );
wp_dequeue_script( 'rtmedia-pro-most-rated-photos-widget' );  

}
//Deregister scripts
add_action( 'wp_enqueue_scripts', 'cfc_remove_script', 9999 );
?>