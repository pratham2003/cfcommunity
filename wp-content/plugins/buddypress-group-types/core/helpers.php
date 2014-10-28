<?php
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Include template files for the plugin
 *
 * @param $template string Template file from /core/_part/ fodler without file extension
 * @param $options  array  Variables that we need to use inside that template
 */
function bpgt_the_template_part($template, $options = array()){
    $path = apply_filters( 'bpgt_the_template_part', BPGT_PATH . '/_parts/' . $template . '.php', $template, $options);

    if( file_exists($path) ){
        // hate doing this
        extract($options);
        include_once($path);
    }
}

/**
 * Check whether we are on a custom group directory page
 *
 * @return bool
 */
function bpgt_is_directory(){
    /** @var $wpdb WPDB */
    global $wpdb, $post, $bpgt_type;

    if ( empty($post) ) {
        if (
            defined('DOING_AJAX') && DOING_AJAX &&
            !bp_is_groups_directory() &&
            isset($_COOKIE['bp-groups-extras']) && !empty($_COOKIE['bp-groups-extras'])
        ) {
            $data = explode('=', $_COOKIE['bp-groups-extras']);
            if ( $data[0] == 'bpgt_type' && is_numeric($data[1]) ) {
                $bpgt_type = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->posts} WHERE ID = %d", $data[1]));
                return $bpgt_type;
            }
        }
    } else {
        // check that the current page is associated with Group Type
        if ( empty( $bpgt_type ) ) {
            $bpgt_type = $wpdb->get_row($wpdb->prepare(
                    "SELECT * FROM {$wpdb->posts}
                    WHERE post_type = %s
                      AND post_parent = %d",
                        BPGT_CPT_TYPE,
                        $post->ID
                    )
            );
        }

        return $bpgt_type;
    }

    return false;
}