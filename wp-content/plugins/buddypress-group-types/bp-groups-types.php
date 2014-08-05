<?php
/**
 * Plugin Name: BuddyPress Group Types
 * Plugin URI:  https://github.com/CFCommunity-net/buddypress-group-types
 * Description: Create different types of groups and enable separate fields for each group type
 * Version:     1.0
 * Author:      slaFFik
 * Author URI:  http://ovirium.com
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

define( 'BPGT_VERSION',   '1.0' );
define( 'BPGT_URL',       plugins_url('_inc', __FILE__ )); // link to all assets, without /
define( 'BPGT_PATH',      dirname(__FILE__) . '/core'); // without /
define( 'BPGT_CPT_TYPE',  'bpgt_type' );
define( 'BPGT_CPT_FIELD', 'bpgt_field' );

/**
 * What to do on activation
 */
register_activation_hook( __FILE__, 'bpgt_activation');
function bpgt_activation() {
    // some defaults
    $bpgt = array();

    bp_add_option('bpgt', $bpgt);
}

/**
 * What to do on deactivation
 */
register_deactivation_hook( __FILE__, 'bpgt_deactivation');
function bpgt_deactivation() {
    $bpgt = bp_get_option('bpgt');

    require_once(BPGT_PATH .'/uninstall.php');
}

/**
 * In case somebody will want to translate the plugin
 */
add_action( 'plugins_loaded', 'bpgt_load_textdomain' );
function bpgt_load_textdomain() {
    load_plugin_textdomain( 'bpgt', false, dirname( plugin_basename( __FILE__ ) ) . '/langs/' );
}

/**
 * All the helpers functions used everywhere
 */
include_once( BPGT_PATH . '/helpers.php' );
include_once( BPGT_PATH . '/ajax.php' );

/**
 * Data Object
 */
include_once( BPGT_PATH . '/class-type.php' );
include_once( BPGT_PATH . '/class-field.php' );

/**
 * Admin area
 */
if ( is_admin() ) {
    include_once( BPGT_PATH . '/admin.php' );
}

function bpgt_register_cpts(){
    register_post_type( BPGT_CPT_TYPE, array(
        'label'                => __('Group Types', 'bpgt'),
		'public'               => false,
		'hierarchical'         => false,
		'exclude_from_search'  => true,
		'publicly_queryable'   => false,
		'show_ui'              => false,
		'show_in_menu'         => false,
		'show_in_nav_menus'    => false,
		'show_in_admin_bar'    => false,
		'capability_type'      => 'post',
		'supports'             => array('title', 'editor', 'thumbnail'),
		'has_archive'          => false,
		'rewrite'              => false,
		'query_var'            => false,
		'can_export'           => true,
		'delete_with_user'     => false
    ));

    register_post_type( BPGT_CPT_FIELD, array(
        'label'                => __('Group Fields', 'bpgt'),
        'public'               => false,
        'hierarchical'         => false,
        'exclude_from_search'  => true,
        'publicly_queryable'   => false,
        'show_ui'              => false,
        'show_in_menu'         => false,
        'show_in_nav_menus'    => false,
        'show_in_admin_bar'    => false,
        'capability_type'      => 'post',
        'supports'             => array('title', 'editor'),
        'has_archive'          => false,
        'rewrite'              => false,
        'query_var'            => false,
        'can_export'           => true,
        'delete_with_user'     => false
    ));
}
add_action('init', 'bpgt_register_cpts');

/**
 * Filter WordPress pages templating loading to load own things
 */
function bpgt_directory(){
    /** @var $post WP_POST */
    global $post;
    /** @var $wpdb WPDB */
    global $wpdb;
    global $bpgt_type;

    if ( empty($post) ) {
        return false;
    }

    // check that the current page is associated with Group Type
    $type = $wpdb->get_row($wpdb->prepare(
                            "SELECT * FROM {$wpdb->posts}
                            WHERE post_type = %s
                              AND post_parent = %d",
                            BPGT_CPT_TYPE,
                            $post->ID
    ));

    if ( !empty($type) ) {
        $bpgt_type = $type;
        $group_dir = new BP_Groups_Theme_Compat;
        bp_update_is_directory( true, 'groups' );

        do_action( 'groups_directory_groups_setup' );

        add_filter( 'bp_get_buddypress_template',                array( $group_dir, 'directory_template_hierarchy' ) );
        add_action( 'bp_template_include_reset_dummy_post_data', array( $group_dir, 'directory_dummy_post' ) );
        add_filter( 'bp_replace_the_content',                    array( $group_dir, 'directory_content'    ) );
    }
}
add_filter( 'wp', 'bpgt_directory' );

/**
 * Modify the group type dir guery
 * @param $bp_ajax_querystring string
 * @param $object string
 * @return string
 */
function bpgt_ajax_querystring($bp_ajax_querystring, $object){
    /** @var $bpgt_type WP_POST */
    global $bpgt_type;

    if ( empty($bpgt_type) || !bp_is_active('groups') || $object != 'groups' ) {
        return $bp_ajax_querystring;
    }

    $args = wp_parse_args( $bp_ajax_querystring, array() );

    $args['meta_query'] = array(array(
        'key'   => 'bpgt_group_type',
        'value' => $bpgt_type->ID
    ));

    return $args;
}
add_filter( 'bp_ajax_querystring', 'bpgt_ajax_querystring', 99, 2 );