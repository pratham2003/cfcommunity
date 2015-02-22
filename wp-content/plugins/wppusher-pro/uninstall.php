<?php

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
    exit; // Exit if accessed directly
}

require 'autoload.php';

$db = new \Pusher\Storage\Database;
$db->uninstall();

delete_option('wppusher_token');
delete_option('gh_token');
delete_option('bb_user');
delete_option('bb_pass');
delete_option('gl_base_url');
delete_option('gl_private_token');
