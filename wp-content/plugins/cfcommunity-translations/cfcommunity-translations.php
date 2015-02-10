<?php
/*
Plugin Name: CFCommunity Translations
Version: 3.7.1
Text Domain: cfcommunity
Description: Translation for CFCommunity

/**
 * Load plugin textdomain.
 *
 * @since 1.0.0
 */
function cfc_load_textdomain() {
  load_plugin_textdomain( 'cfctranslation', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
}
add_action( 'plugins_loaded', 'cfc_load_textdomain' );
?>