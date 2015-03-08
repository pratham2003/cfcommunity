<?php

/**
 * Plugin Name: WP Pusher Pro
 * Plugin URI: http://wppusher.com
 * Description: Pain-free deployment of WordPress themes and plugins directly from GitHub.
 * Version: 1.0.8
 * Author: WP Pusher
 * Author URI: http://wppusher.com
 * License: GNU GENERAL PUBLIC LICENSE
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
    die;
}

require __DIR__ . '/autoload.php';

use Pusher\Pro\ProServiceProvider;
use Pusher\Pro\Pusher as PusherPro;
use Pusher\Pusher;
use Pusher\PusherServiceProvider;

if (class_exists('Pusher\Pro\Pusher')) {
    $pusher = new PusherPro;
    $pusher->register(new PusherServiceProvider);
    $pusher->register(new ProServiceProvider);
} else {
    $pusher = new Pusher;
    $pusher->register(new PusherServiceProvider);
}

register_activation_hook(__FILE__, array($pusher, 'activate'));
// register_deactivation_hook(__FILE__, array($pusher, 'deactivate'));

// Auto updates pro
require_once('wp-updates-plugin-pro.php');
new WPUpdatesPluginUpdater_891('http://wp-updates.com/api/2/plugin', plugin_basename(__FILE__));

$pusher->init();
