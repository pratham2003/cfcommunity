<?php
/*
Plugin Name: BuddyPress Geo Locate
Plugin URI: http://cfcommunity.net
Description: Add a Geo Location Profile Field powered by GMAPS
Version: 0.1
Author: bowefrankema
Author Email: bowe@cfcommunity.net
License:

  Copyright 2011 bowefrankema (bowe@youthpolicy.org)

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License, version 2, as 
  published by the Free Software Foundation.

  This program is distributed in the hope that it will be useful,
  but WITHOUT ANY WARRANTY; without even the implied warranty of
  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
  GNU General Public License for more details.

  You should have received a copy of the GNU General Public License
  along with this program; if not, write to the Free Software
  Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
  
*/
function bpg_enqueue_scripts() {  

if ( bp_is_profile_edit() || is_page_template( 'template-cfsignup.php' ) ): 
    wp_enqueue_script('google-maps-places', 'http://maps.googleapis.com/maps/api/js?sensor=false&language=en&libraries=places', array(), '3', false);
    wp_register_script('bp_geocomplete', plugins_url('assets/js/jquery.geocomplete.min.js', __FILE__), array(), '3', false);
endif;

wp_enqueue_script('bp_geocomplete');
}
add_action( 'wp_enqueue_scripts', 'bpg_enqueue_scripts' );  

/**
 * Register menus
 *
 * @package Infinity
 * @subpackage base
 */
function bpg_target_field()
{?>
        
<?php if ( bp_is_profile_edit() || is_page_template( 'template-cfsignup.php') ): ?> 
    <script type="text/javascript">
          $(".field_city input").geocomplete()
          $(".location-field input").geocomplete()
    </script> 
<?php endif; ?>

<?}
add_action( 'wp_footer', 'bpg_target_field' ); 
?>