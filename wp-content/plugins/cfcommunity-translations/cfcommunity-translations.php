<?php
/*
Plugin Name: CFCommunity Translations
Version: 3.7.1
Text Domain: cfcommunity
Description: Translation for CFCommunity

 This program is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 This program is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

*/

add_action( 'plugins_loaded', 'cfc_load_textdomain' );
/**
 * Load plugin textdomain.
 *
 * @since 1.0.0
 */
function cfc_load_textdomain() {
  load_muplugin_textdomain( 'cfcommunity', false, dirname( plugin_basename( __FILE__ ) ) . '/langs' );
}
?>