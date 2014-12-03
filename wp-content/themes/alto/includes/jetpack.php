<?php
/**
 * Jetpack Compatibility File
 * See: http://jetpack.me/
 *
 * @package Alto
 */

/**
 * Add theme support for Infinite Scroll. Additionally, define a custom render function to use a custom template
 * for posts appended by the infinite scroll.
 *
 * See: http://jetpack.me/support/infinite-scroll/
 */

function alto_jetpack_setup() {
  add_theme_support( 'infinite-scroll', array(
    'container' => 'latest-posts',
    'footer'    => 'colophon'
  ) );
}
add_action( 'after_setup_theme', 'alto_jetpack_setup' );

/**
 * Jetpack: Allow for custom placement of Sharing module. 
 * See: http://jetpack.me/2013/06/10/moving-sharing-icons/
 */
function alto_jptweak_remove_share() {
  remove_filter( 'the_content', 'sharing_display', 19 );
  remove_filter( 'the_excerpt', 'sharing_display', 19 );
  if ( class_exists( 'Jetpack_Likes' ) ) {
      remove_filter( 'the_content', array( Jetpack_Likes::init(), 'post_likes' ), 30, 1 );
  }
}
add_action( 'loop_start', 'alto_jptweak_remove_share' );