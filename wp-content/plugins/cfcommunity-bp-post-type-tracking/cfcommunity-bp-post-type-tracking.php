<?php
/*
Plugin Name: CFCommunity Custom Post Type Tracking
Plugin URI: http://cfcommunity.net
Description: A Simple Plugin that adds the custom post types used on subsites in order to be tracked by BuddyPress
Version: 0.1
Author: bowefrankema
Author Email: bowe@cfcommunity.net
License: GPL
*/

/**
 * Directly set the BuddyPress activity support when registering the post type 
 * @see https://codex.buddypress.org/plugindev/post-types-activities/#adding-the-buddypress-support-and-specific-labels-at-post-type-registration
 */
 
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;
?>