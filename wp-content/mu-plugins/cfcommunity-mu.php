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
  		top:5px
  	}
  	#wp-admin-bar-wdcab_root img:hover{
  			opacity:0.8
  	}
  </style>';
}
add_action('admin_head', 'cf_admin_css');
?>