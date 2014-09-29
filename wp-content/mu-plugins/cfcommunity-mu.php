<?php
/*
Plugin Name: CFCommunity Multisite Tweaks
Plugin URI: http://wordpress.org/extend/plugins/menus/
Version: 3.7.1
Description: Tweaks for CFCommunity.net
Author: dsader
Author URI: http://dsader.snowotherway.org
Network: true

 This program is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 This program is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

*/

function cf_admin_css() {
  echo '<style>
	#wp-admin-bar-wdcab_root img{
  		height:23px;
  		width:auto;
  		position:relative;
  		top:-2px
  	}
  	#wp-admin-bar-wdcab_root img:hover{
  			opacity:0.8
  	}
  </style>';
}
add_action('admin_head', 'cf_admin_css');
add_action('wp_head', 'cf_admin_css');

function ra_add_author_filter() {
  add_filter( 'author_link', 'ra_bp_filter_author' );
}
add_action( 'wp_head', 'ra_add_author_filter' );

function ra_bp_filter_author( $content ) {
  if( defined( 'BP_MEMBERS_SLUG' ) ) {
    if( is_multisite() ) {
      $member_url = network_home_url( BP_MEMBERS_SLUG );
      if( !is_subdomain_install() && is_main_site() )
        $extra = '/blog';
      else
        $extra = '';

      $blog_url = get_option( 'siteurl' ) . $extra . '/author';
      return str_replace( $blog_url, $member_url, $content );
    }
    return preg_replace( '|/author(/[^/]+/)|', '/' . BP_MEMBERS_SLUG . '$1' . 'profile/', $content );
  }
  return $content;
}

// Add specific CSS class by filter
add_filter('body_class','my_class_names');
function my_class_names($classes) {
    if (is_user_logged_in() && !is_super_admin()) {
        $classes[] = 'is-not-super-admin';
    }
    $classes[] = get_bloginfo('language');
    return $classes;
}


// add conditional statements for mobile devices
function is_ipad() { // if the user is on an iPad
  $is_ipad = (bool) strpos($_SERVER['HTTP_USER_AGENT'],'iPad');
  if ($is_ipad)
    return true;
  else return false;
}
function is_iphone() { // if the user is on an iPhone
  $cn_is_iphone = (bool) strpos($_SERVER['HTTP_USER_AGENT'],'iPhone');
  if ($cn_is_iphone)
    return true;
  else return false;
}
function is_ipod() { // if the user is on an iPod Touch
  $cn_is_iphone = (bool) strpos($_SERVER['HTTP_USER_AGENT'],'iPod');
  if ($cn_is_iphone)
    return true;
  else return false;
}
function is_ios() { // if the user is on any iOS Device
  if (is_iphone() || is_ipad() || is_ipod())
    return true;
  else return false;
}
function is_android() { // detect ALL android devices
  $is_android = (bool) strpos($_SERVER['HTTP_USER_AGENT'],'Android');
  if ($is_android)
    return true;
  else return false;
}
function is_android_mobile() { // detect only Android phones
  $is_android   = (bool) strpos($_SERVER['HTTP_USER_AGENT'],'Android');
  $is_android_m = (bool) strpos($_SERVER['HTTP_USER_AGENT'],'Mobile');
  if ($is_android && $is_android_m)
    return true;
  else return false;
}
function is_android_tablet() { // detect only Android tablets
  if (is_android() && !is_android_mobile())
    return true;
  else return false;
}
function is_mobile_device() { // detect Android Phones, iPhone or iPod
  if (is_android_mobile() || is_iphone() || is_ipod())
    return true;
  else return false;
}
function is_tablet() { // detect Android Tablets and iPads
  if ((is_android() && !is_android_mobile()) || is_ipad())
    return true;
  else return false;
}

//verifies that the user's screen is a high pixel density display
function cf_is_high_res() {
  if ( isset( $_COOKIE['devicePixelRatio'] ) && $_COOKIE['devicePixelRatio'] > 1.5 )
    return true;
  else
    return false;
}

/**
 * Add color button classes to buttons depending on preset style/option
 */
function cf_is_high_res_js()
{
  // render script tag ?>
  <script type="text/javascript">
    jQuery(document).ready(function() {
      // <![CDATA[
      if( window.devicePixelRatio !== undefined ) document.cookie = 'devicePixelRatio = ' + window.devicePixelRatio;
    // ]]>
    });
  </script>
  <?php
}
add_action( 'wp_head', 'cf_is_high_res_js' );

// function wpa54064_inspect_scripts() {
//     global $wp_scripts;
//     foreach( $wp_scripts->queue as $handle ) :
//     echo '<span class="' . $handle .'">' . $handle . '</span><br>';
//     endforeach;
// }
// add_action( 'wp_print_scripts', 'wpa54064_inspect_scripts' );
?>