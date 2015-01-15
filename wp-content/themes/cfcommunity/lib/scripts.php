<?php
/**
 * Enqueue scripts and stylesheets
 *
 * Enqueue stylesheets in the following order:
 * 1. /theme/assets/css/main.min.css
 *
 * Enqueue scripts in the following order:
 * 1. jquery-1.11.0.min.js via Google CDN
 * 2. /theme/assets/js/vendor/modernizr-2.7.0.min.js
 * 3. /theme/assets/js/main.min.js (in footer)
 */
function cfc_scripts() {

  if ( defined( 'ENV_TYPE' ) && 'staging' == ENV_TYPE ) {

  wp_enqueue_style('cfc_main', get_template_directory_uri() . '/assets/css/main.min.css', false, '0bbc04009c701f8ed55e67d44ae2e05e');

  }

  if ( defined( 'ENV_TYPE' ) && 'production' == ENV_TYPE ) {

  wp_enqueue_style('cfc_main', get_template_directory_uri() . '/assets/css/main.min.css', '4.0', true );
  
  }

  // jQuery is loaded using the same method from HTML5 Boilerplate:
  // Grab Google CDN's latest jQuery with a protocol relative URL; fallback to local if offline
  // It's kept in the header instead of footer to avoid conflicts with plugins.
  if (!is_admin() && current_theme_supports('jquery-cdn')) {
    wp_deregister_script('jquery');
    wp_register_script('jquery', '//ajax.googleapis.com/ajax/libs/jquery/1.11.0/jquery.min.js', array(), null, false);
    add_filter('script_loader_src', 'cfc_jquery_local_fallback', 10, 2);
  }

  if (is_single() && comments_open() && get_option('thread_comments')) {
    wp_enqueue_script('comment-reply');
  }

  // Automatically load new updates in the stream
  // if (bp_is_activity_directory() ) {
  //    wp_register_script('bp-load-activity', get_template_directory_uri() . '/assets/js/plugins/exclude/bp-activity-loader.js', array(), null, false);
  //   wp_enqueue_script('bp-load-activity');
  // }

  // Add jQuery Fastclick for mobile devices
  if (wp_is_mobile() ) {
     wp_register_script('cf-fastclick', get_template_directory_uri() . '/assets/js/plugins/exclude/fastclick.js', array(), null, false);
    wp_enqueue_script('cf-fastclick');
  }

  wp_register_script('modernizr', get_template_directory_uri() . '/assets/js/vendor/modernizr-2.7.0.min.js', array(), null, false);

  if ( defined( 'ENV_TYPE' ) && 'staging' == ENV_TYPE ) {

   wp_register_script('cfc_scripts', get_template_directory_uri() . '/assets/js/scripts.min.js', array(), 'fc429a079c4f3cdb744458f2bf73006b', true);

  }

  if ( defined( 'ENV_TYPE' ) && 'production' == ENV_TYPE ) {

    wp_register_script('cfc_scripts', get_template_directory_uri() . '/assets/js/scripts.min.js', array(), '4.0', true );
  
  }

  wp_enqueue_script('modernizr');
  wp_enqueue_script('jquery');
  wp_enqueue_script('cfc_scripts');
}
add_action('wp_enqueue_scripts', 'cfc_scripts', 100);

// http://wordpress.stackexchange.com/a/12450
function cfc_jquery_local_fallback($src, $handle = null) {
  static $add_jquery_fallback = false;

  if ($add_jquery_fallback) {
    echo '<script>window.jQuery || document.write(\'<script src="' . get_template_directory_uri() . '/assets/js/vendor/jquery-1.11.0.min.js"><\/script>\')</script>' . "\n";
    $add_jquery_fallback = false;
  }

  if ($handle === 'jquery') {
    $add_jquery_fallback = true;
  }

  return $src;
}
add_action('wp_head', 'cfc_jquery_local_fallback');