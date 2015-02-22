<?php

namespace Pusher\Storage;

class Database
{
    public static $pusher_db_version = '1.0';

    public function install()
    {
        global $wpdb;

        $table_name = $wpdb->prefix . 'wppusher_packages';

        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE $table_name (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            package varchar(255) NOT NULL,
            repository varchar(255) NOT NULL,
            branch varchar(255) NOT NULL DEFAULT 'master',
            type int NOT NULL,
            status int NOT NULL,
            ptd int NOT NULL,
            host varchar(10) NOT NULL,
            private int NOT NULL,
            UNIQUE KEY id (id)
        ) $charset_collate;";

        require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
        dbDelta( $sql );
    }

    public function uninstall()
    {
        global $wpdb;

        $table_name = $wpdb->prefix . 'wppusher_packages';

        $sql = "DROP TABLE IF EXISTS $table_name;";

        $wpdb->query($sql);
    }
}
