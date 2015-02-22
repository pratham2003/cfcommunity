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
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'BPGT_VERSION', '1.0' );
define( 'BPGT_URL', plugins_url( '_inc', __FILE__ ) ); // link to all assets, without /
define( 'BPGT_PATH', dirname( __FILE__ ) . '/core' ); // without /
define( 'BPGT_CPT_TYPE', 'bpgt_type' );
define( 'BPGT_CPT_FIELD', 'bpgt_field' );
define( 'BPGT_ADMIN_SLUG', 'bp-groups-types' );
define( 'BPGT_PLUGIN_SLUG', 'buddypress-group-types/bp-groups-types.php' );
define( 'BPGT_THEME', 'bpgt' );

/**
 * What to do on activation
 */
register_activation_hook( __FILE__, 'bpgt_activation' );
function bpgt_activation() {
	/** @var $wpdb WPDB */
	global $wpdb;
	// some defaults
	$bpgt = array();

	bp_add_option( 'bpgt', $bpgt );

	// get all groups
	$groups_table = buddypress()->groups->table_name;
	$groups       = $wpdb->get_col( "SELECT id FROM {$groups_table}" );
	// all of them should get bpgt_group_type meta_key with empty value
	foreach ( $groups as $group_id ) {
		groups_add_groupmeta( $group_id, 'bpgt_group_type', '0', true );
	}
}

/**
 * What to do on deactivation
 */
register_deactivation_hook( __FILE__, 'bpgt_deactivation' );
function bpgt_deactivation() {
	require_once( BPGT_PATH . '/uninstall.php' );
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
include_once( BPGT_PATH . '/cache.php' );
include_once( BPGT_PATH . '/cpt.php' );
include_once( BPGT_PATH . '/templates.php' );

/**
 * Data Object
 */
include_once( BPGT_PATH . '/class-type.php' );
include_once( BPGT_PATH . '/class-field.php' );

/**
 * Extensions
 */
function bpgt_load_ext() {
	include_once( BPGT_PATH . '/ext/create-group.php' );
	include_once( BPGT_PATH . '/ext/default-avatars.php' );
	include_once( BPGT_PATH . '/ext/disabled-plugins.php' );
}

add_action( 'bp_include', 'bpgt_load_ext' );

/**
 * Admin area
 */
if ( is_admin() ) {
	include_once( BPGT_PATH . '/admin.php' );
}

/**
 * Filter WordPress pages templating loading to load own things
 */
function bpgt_directory() {
	global $bpgt_type;
	if ( ! bpgt_is_directory() ) {
		setcookie( 'bp-groups-extras', '', time() - 3600 );

		return false;
	}

	$group_dir = new BP_Groups_Theme_Compat;

	bp_update_is_directory( true, 'groups' );

	setcookie( 'bp-groups-extras', 'bpgt_type=' . $bpgt_type->ID );

	do_action( 'groups_directory_groups_setup' );

	add_filter( 'bp_get_buddypress_template', array( $group_dir, 'directory_template_hierarchy' ) );
	add_action( 'bp_template_include_reset_dummy_post_data', array( $group_dir, 'directory_dummy_post' ) );
	add_filter( 'bp_replace_the_content', array( $group_dir, 'directory_content' ) );
}

add_action( 'wp', 'bpgt_directory' );

/**
 * Filter Directory title
 *
 * @param $title
 * @param $component
 *
 * @return string|void
 */
function bpgt_get_directory_title( $title, $component ) {
	/** @var $bpgt_type WP_POST */
	global $bpgt_type;

	if ( $component == buddypress()->groups->id && bpgt_is_directory() ) {
		$title = esc_attr( $bpgt_type->post_title );
	}

	return apply_filters( 'bpgt_get_directory_title', $title );
}

add_filter( 'bp_get_directory_title', 'bpgt_get_directory_title', 10, 2 );

/**
 * Filter Directory group types counter
 *
 * @param int $old_count
 *
 * @return int
 */
function bpgt_get_total_group_type_count( $old_count ) {
	/** @var $wpdb WPDB */
	global $bp, $wpdb, $bpgt_type;

	$count = $old_count;

	// check the cache
	if ( bpgt_is_directory() ) {
		if ( false === ( $count = get_transient( 'bpgt_total_group_count_type_' . $bpgt_type->ID ) ) ) {
			$count = $wpdb->get_var( $wpdb->prepare(
				"SELECT COUNT( DISTINCT group_id )
                        FROM {$bp->groups->table_name_groupmeta}
                        WHERE meta_key = 'bpgt_group_type'
                          AND meta_value = %d",
				$bpgt_type->ID
			) );

			set_transient( 'bpgt_total_group_count_type_' . $bpgt_type->ID, $count, WEEK_IN_SECONDS );
		}
	} else {
		if ( false === ( $count = get_transient( 'bpgt_total_group_count_type_0' ) ) ) {
			$count = $wpdb->get_var(
				"SELECT COUNT(g.id)
                        FROM {$bp->groups->table_name} g, {$bp->groups->table_name_groupmeta} gm
                        WHERE gm.group_id = g.id
                          AND gm.meta_key = 'bpgt_group_type'
                          AND gm.meta_value = 0" );

			set_transient( 'bpgt_total_group_count_type_0', $count, WEEK_IN_SECONDS );
		}
	}

	return $count;
}

add_filter( 'bp_get_total_group_count', 'bpgt_get_total_group_type_count' );

/**
 * Filter Directory My Groups group types counter
 *
 * @param int $old_count
 * @param int $user_id
 *
 * @return int
 */
function bpgt_get_total_group_count_for_user( $old_count, $user_id ) {
	/** @var $wpdb WPDB */
	global $bp, $wpdb, $bpgt_type;

	$count = $old_count;

	if ( bpgt_is_directory() ) {
		// check the cache
		if ( false === ( $count = get_transient( 'bpgt_my_group_count_type_' . $bpgt_type->ID . '_user_' . $user_id ) ) ) { // ok, nothing in cache
			if ( $user_id != bp_loggedin_user_id() && ! bp_current_user_can( 'bp_moderate' ) ) {
				$count = $wpdb->get_var( $wpdb->prepare(
					"SELECT COUNT(DISTINCT m.group_id)
                        FROM {$bp->groups->table_name_members} m, {$bp->groups->table_name} g, {$bp->groups->table_name_groupmeta} gm
                        WHERE m.group_id = g.id
                          AND g.status != 'hidden'
                          AND m.user_id = %d
                          AND m.is_confirmed = 1
                          AND m.is_banned = 0
                          AND gm.group_id = g.id
                          AND gm.meta_key = 'bpgt_group_type'
                          AND gm.meta_value = %d",
					$user_id,
					$bpgt_type->ID
				) );
			} else {
				$count = $wpdb->get_var( $wpdb->prepare(
					"SELECT COUNT(DISTINCT m.group_id)
                        FROM {$bp->groups->table_name_members} m, {$bp->groups->table_name} g, {$bp->groups->table_name_groupmeta} gm
                        WHERE m.group_id = g.id
                          AND m.user_id = %d
                          AND m.is_confirmed = 1
                          AND m.is_banned = 0
                          AND gm.group_id = g.id
                          AND gm.meta_key = 'bpgt_group_type'
                          AND gm.meta_value = %d",
					$user_id,
					$bpgt_type->ID
				) );
			}

			set_transient( 'bpgt_my_group_count_type_' . $bpgt_type->ID . '_user_' . $user_id, $count, WEEK_IN_SECONDS );
		}
	} else {
		if ( false === ( $count = get_transient( 'bpgt_my_group_count_type_0_user_' . $user_id ) ) ) { // ok, nothing in cache
			if ( $user_id != bp_loggedin_user_id() && ! bp_current_user_can( 'bp_moderate' ) ) {
				$count = $wpdb->get_var( $wpdb->prepare(
					"SELECT COUNT(DISTINCT m.group_id)
                        FROM {$bp->groups->table_name_members} m, {$bp->groups->table_name} g, {$bp->groups->table_name_groupmeta} gm
                        WHERE m.group_id = g.id
                          AND g.status != 'hidden'
                          AND m.user_id = %d
                          AND m.is_confirmed = 1
                          AND m.is_banned = 0
                          AND gm.group_id = g.id
                          AND gm.meta_key = 'bpgt_group_type'
                          AND gm.meta_value = 0",
					$user_id
				)
				);
			} else {
				$count = $wpdb->get_var( $wpdb->prepare(
					"SELECT COUNT(DISTINCT m.group_id)
                        FROM {$bp->groups->table_name_members} m, {$bp->groups->table_name} g, {$bp->groups->table_name_groupmeta} gm
                        WHERE m.group_id = g.id
                          AND m.user_id = %d
                          AND m.is_confirmed = 1
                          AND m.is_banned = 0
                          AND gm.group_id = g.id
                          AND gm.meta_key = 'bpgt_group_type'
                          AND gm.meta_value = 0",
					$user_id
				)
				);
			}
		}

		set_transient( 'bpgt_my_group_count_type_0_user_' . $user_id, $count, WEEK_IN_SECONDS );
	}

	return $count;
}

add_action( 'bp_get_total_group_count_for_user', 'bpgt_get_total_group_count_for_user', 10, 2 );

/**
 * Modify the group type dir guery
 *
 * @param $bp_ajax_querystring string
 * @param $object string
 *
 * @return string
 */
function bpgt_ajax_querystring( $bp_ajax_querystring, $object ) {
	/** @var $bpgt_type WP_POST */
	global $bpgt_type;

	if ( ! bp_is_active( 'groups' ) ) {
		return $bp_ajax_querystring;
	}

	if ( $object != 'groups' ) {
		return $bp_ajax_querystring;
	}

	$args = wp_parse_args( $bp_ajax_querystring, array() );

	if ( bpgt_is_directory() ) {
		$args['meta_query'] = array(
			array(
				'key'     => 'bpgt_group_type',
				'value'   => $bpgt_type->ID,
				'compare' => '='
			)
		);
	} else {
		$args['meta_query'] = array(
			array(
				'key'     => 'bpgt_group_type',
				'value'   => '0',
				'compare' => '='
			)
		);
	}

	return $args;
}

add_filter( 'bp_ajax_querystring', 'bpgt_ajax_querystring', 20, 2 );

/**
 * Add classes to <body> - whether it's a group or directory, and what is its type
 *
 * @param array $classes
 *
 * @return array
 */
function bpgt_body_class( $classes ) {
	/** @var $bpgt_type WP_POST */
	global $bpgt_type;

	if ( bpgt_is_directory() ) {
		$classes[] = 'bpgt-directory';
		$classes[] = 'bp-group-type-' . $bpgt_type->post_name;
	} else if ( bp_is_group() ) {
		$type = bpgt_get_type( bp_get_current_group_id() );
		if ( ! empty($type->name) ) {
			$classes[] = 'bpgt-group';
			$classes[] = 'bp-group-type-' . $type->name;
		}
	}

	return $classes;
}

add_filter( 'body_class', 'bpgt_body_class' );
