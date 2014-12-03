<?php
/**
 * Custom functions that act independently of the theme templates.
 *
 * @package Alto
 */

/**
 * Get our wp_nav_menu() fallback, wp_page_menu(), to show a home link.
 *
 * @param array $args Configuration arguments.
 * @return array
 */
function alto_page_menu_args( $args ) {
  $args['show_home']  = true;
  $args['menu_class'] = '';
  $args['container']  = '';
  return $args;
}
add_filter( 'wp_page_menu_args', 'alto_page_menu_args' );

/**
* Get wp_page_menu to add a class to the ul for styling.
*
*/

function alto_page_menu_add_class( $ulclass ) {
  return preg_replace( '/<ul>/', '<ul class="menu nav-menu">', $ulclass, 1 );
}
add_filter( 'wp_page_menu', 'alto_page_menu_add_class' );

/**
 * Adds custom classes to the array of body classes.
 *
 * @param array $classes Classes for the body element.
 * @return array
 */

function alto_body_classes( $classes ) {
  // Adds a class of group-blog to blogs with more than 1 published author.
  if ( is_multi_author() ) {
    $classes[] = 'group-blog';
  }

  return $classes;
}
add_filter( 'body_class', 'alto_body_classes' );

/**
 * Filters wp_title to print a neat <title> tag based on what is being viewed.
 *
 * @param string $title Default title text for current view.
 * @param string $sep Optional separator.
 * @return string The filtered title.
 */
function alto_wp_title( $title, $sep ) {
  global $page, $paged;

  if ( is_feed() ) {
    return $title;
  }

  // Add the blog name
  $title .= get_bloginfo( 'name' );

  // Add the blog description for the home/front page.
  $site_description = get_bloginfo( 'description', 'display' );
  if ( $site_description && ( is_home() || is_front_page() ) ) {
    $title .= " $sep $site_description";
  }

  // Add a page number if necessary:
  if ( $paged >= 2 || $page >= 2 ) {
    $title .= " $sep " . sprintf( __( 'Page %s', 'alto' ), max( $paged, $page ) );
  }

  return $title;
}
add_filter( 'wp_title', 'alto_wp_title', 10, 2 );

/**
 * Sets the authordata global when viewing an author archive.
 *
 * This provides backwards compatibility with
 * http://core.trac.wordpress.org/changeset/25574
 *
 * It removes the need to call the_post() and rewind_posts() in an author
 * template to print information about the author.
 *
 * @global WP_Query $wp_query WordPress Query object.
 * @return void
 */
function alto_setup_author() {
  global $wp_query;

  if ( $wp_query->is_author() && isset( $wp_query->post ) ) {
    $GLOBALS['authordata'] = get_userdata( $wp_query->post->post_author );
  }
}
add_action( 'wp', 'alto_setup_author' );

/**
* Auto Copyright
* 
* Taken from Chris Coyier at CSS Tricks (http://css-tricks.com/snippets/php/automatic-copyright-year/)
*
**/

function auto_copyright($year = 'auto'){
  if( intval( $year ) == 'auto' ) { 
    $year = '&copy;' . date('Y'); 
  }
  if( intval( $year ) == date( 'Y' ) ) { 
    echo '&copy;' . intval( $year ); 
  }
  if( intval( $year ) < date( 'Y' ) ) { 
    echo '&copy;' . intval( $year ) . ' - ' . date( 'Y' ); 
  }
  if( intval( $year ) > date( 'Y' ) ) { 
    echo '&copy;' . date('Y'); 
  }
}

/**
* Fetch Data
* Function to help with getting data using CURL.
*
* Taken from here: https://bountify.co/pull-users-instagram-feed-with-php
*/

function fetch_data( $url ){
  $ch = curl_init();
  curl_setopt( $ch, CURLOPT_URL, $url );
  curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 );
  curl_setopt( $ch, CURLOPT_TIMEOUT, 20 );
  $result = curl_exec( $ch );
  curl_close( $ch );
  return $result;
}

/**
* Convert Hexidecimal to RGB
* Used for setting custom colors on elements that need to respect rgba values.
*
* See: http://bavotasan.com/2011/convert-hex-color-to-rgb-using-php/
*/

function hex2rgb( $hex ) {
  
  $hex = str_replace( "#", "", $hex );

  if( strlen( $hex ) == 3 ) {
    $r = hexdec( substr( $hex,0,1 ).substr( $hex,0,1 ) );
    $g = hexdec( substr( $hex,1,1 ).substr( $hex,1,1 ) );
    $b = hexdec( substr( $hex,2,1 ).substr( $hex,2,1 ) );
  } else {
    $r = hexdec( substr( $hex,0,2 ) );
    $g = hexdec(substr( $hex,2,2 ) );
    $b = hexdec(substr( $hex,4,2 ) );
  }
   $rgb = array( $r, $g, $b );
   return implode(",", $rgb); // returns the rgb values separated by commas
   //return $rgb; // returns an array with the rgb values
}

/**
* Adjust Color Brightness
* Used for setting custom colors on elements where a lighter or darker color is used to set the box shadow or border.
*
* See: http://stackoverflow.com/questions/3512311/how-to-generate-lighter-darker-color-with-php
*/

function adjustBrightness( $hex, $steps ) {
  // Steps should be between -255 and 255. Negative = darker, positive = lighter
  $steps = max( -255, min( 255, $steps ) );

  // Format the hex color string
  $hex = str_replace( '#', '', $hex );
  if ( strlen( $hex ) == 3 ) {
    $hex = str_repeat( substr( $hex,0,1 ), 2 ).str_repeat( substr( $hex,1,1 ), 2 ).str_repeat( substr( $hex,2,1 ), 2 );
  }

  // Get decimal values
  $r = hexdec( substr( $hex,0,2 ) );
  $g = hexdec( substr( $hex,2,2 ) );
  $b = hexdec( substr( $hex,4,2 ) );

  // Adjust number of steps and keep it inside 0 to 255
  $r = max( 0,min( 255,$r + $steps ) );
  $g = max( 0,min( 255,$g + $steps ) );  
  $b = max( 0,min( 255,$b + $steps ) );

  $r_hex = str_pad( dechex( $r ), 2, '0', STR_PAD_LEFT );
  $g_hex = str_pad( dechex( $g ), 2, '0', STR_PAD_LEFT );
  $b_hex = str_pad( dechex( $b ), 2, '0', STR_PAD_LEFT );

  return '#'.$r_hex.$g_hex.$b_hex;
}

/**
 * Register Google Fonts
 */
function alto_google_fonts() {
  $protocol = is_ssl() ? 'https' : 'http';

  /* httptranslators: If there are characters in your language that are not supported
    by any of the following fonts, translate this to 'off'. Do not translate into your own language. */

  if ( 'off' !== _x( 'on', 'Open Sans font: on or off', 'alto' ) ) {
    wp_register_style( 'alto-open-sans', "$protocol://fonts.googleapis.com/css?family=Open+Sans:400italic,700italic,400,700,600" );
  }
  wp_enqueue_style( 'alto-open-sans' );
}
add_action( 'init', 'alto_google_fonts' );

/**
* Set Custom Excerpt Length
**/
function custom_excerpt_length( $length ) {
  return 30;
}
add_filter( 'excerpt_length', 'custom_excerpt_length', 999 );

/**
* Remove Brackets from Excerpt
**/
function excerpt_alto( $text ) {
  return '...';
}
add_filter('excerpt_more', 'excerpt_alto');
