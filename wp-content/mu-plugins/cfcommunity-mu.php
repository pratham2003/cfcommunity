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

 // Simple 1st version. More to come.

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

/*
Plugin Name: My Widget
Plugin URI: http://mydomain.com
Description: My first widget
Author: Me
Version: 1.0
Author URI: http://mydomain.com
*/

// Block direct requests
if ( !defined('ABSPATH') )
  die('-1');
  
  
add_action( 'widgets_init', function(){
     register_widget( 'My_Widget' );
}); 

/**
 * Adds My_Widget widget.
 */
class My_Widget extends WP_Widget {

  /**
   * Register widget with WordPress.
   */
  function __construct() {
    parent::__construct(
      'My_Widget', // Base ID
      __('My Widget', 'text_domain'), // Name
      array( 'description' => __( 'My first widget!', 'text_domain' ), ) // Args
    );
  }

  /**
   * Front-end display of widget.
   *
   * @see WP_Widget::widget()
   *
   * @param array $args     Widget arguments.
   * @param array $instance Saved values from database.
   */
  public function widget( $args, $instance ) {
  
      echo $args['before_widget'];
    if ( ! empty( $instance['title'] ) ) {
      echo $args['before_title'] . apply_filters( 'widget_title', $instance['title'] ). $args['after_title'];
    }?>

  

     <?php 
      global $bp;
      $thisblog = $current_blog->blog_id;
      echo $thisblog;
     ?>

    <?
    echo $args['after_widget'];
  }



  /**
   * Back-end widget form.
   *
   * @see WP_Widget::form()
   *
   * @param array $instance Previously saved values from database.
   */
  public function form( $instance ) {
    if ( isset( $instance[ 'title' ] ) ) {
      $title = $instance[ 'title' ];
    }
    else {
      $title = __( 'New title', 'text_domain' );
    }
    ?>
    <p>
      <label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:' ); ?></label> 
      <input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>">
    </p>
    <?php 
  }

  /**
   * Sanitize widget form values as they are saved.
   *
   * @see WP_Widget::update()
   *
   * @param array $new_instance Values just sent to be saved.
   * @param array $old_instance Previously saved values from database.
   *
   * @return array Updated safe values to be saved.
   */
  public function update( $new_instance, $old_instance ) {
    $instance = array();
    $instance['title'] = ( ! empty( $new_instance['title'] ) ) ? strip_tags( $new_instance['title'] ) : '';

    return $instance;
  }

} // class My_Widget
?>