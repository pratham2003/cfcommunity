<?php
/**
 * Enqueue scripts and stylesheets
 *
 * Enqueue stylesheets in the following order:
 * 1. /theme/assets/css/main.min.css
 *
 * Enqueue scripts in the following order:
 * 1. jquery-1.10.2.min.js via Google CDN
 * 2. /theme/assets/js/vendor/modernizr-2.6.2.min.js
 * 3. /theme/assets/js/main.min.js (in footer)
 */
function roots_child_scripts() {
  wp_enqueue_style('roots_child', get_stylesheet_directory_uri() . '/assets/css/child.min.css', false, '48ab6a766c3ee4b78e3f90efe9aa9c8c');

  // jQuery is loaded using the same method from HTML5 Boilerplate:
  wp_register_script('roots_child_script', get_stylesheet_directory_uri() . '/assets/js/scripts-child.min.js', false, 'ae554f9d8574ed2ce21718b7406a8a8f', true);
  wp_enqueue_script('roots_child_script');
  
}

add_action('wp_enqueue_scripts', 'roots_child_scripts', 101);
?>