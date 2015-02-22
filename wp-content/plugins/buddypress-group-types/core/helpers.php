<?php
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Include template files for the plugin
 *
 * @param $template string Template file from /core/_part/ fodler without file extension
 * @param $options  array  Variables that we need to use inside that template
 */
function bpgt_the_template_part( $template, $options = array() ) {
	$path = apply_filters( 'bpgt_the_template_part', BPGT_PATH . '/_parts/' . $template . '.php', $template, $options );

	if ( file_exists( $path ) ) {
		// hate doing this
		extract( $options );
		include_once( $path );
	}
}

/**
 * Check whether we are on a custom group directory page
 *
 * @return bool|WP_Post
 */
function bpgt_is_directory() {
	/** @var $wpdb WPDB */
	global $wpdb, $post, $bpgt_type;

	if ( empty( $post ) ) {
		if (
			defined( 'DOING_AJAX' ) && DOING_AJAX &&
			! bp_is_groups_directory() &&
			isset( $_COOKIE['bp-groups-extras'] ) && ! empty( $_COOKIE['bp-groups-extras'] )
		) {
			$data = explode( '=', $_COOKIE['bp-groups-extras'] );
			if ( $data[0] == 'bpgt_type' && is_numeric( $data[1] ) ) {
				$bpgt_type = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->posts} WHERE ID = %d", $data[1] ) );

				if ( ! empty( $bpgt_type ) ) {
					$bpgt_type->post_name = get_post_field( 'post_name', $bpgt_type->post_parent );
				}

				return $bpgt_type;
			}
		}
	} else {
		// check that the current page is associated with Group Type
		if ( empty( $bpgt_type ) ) {
			$bpgt_type = $wpdb->get_row( $wpdb->prepare(
				"SELECT * FROM {$wpdb->posts}
                    WHERE post_type = %s
                      AND post_parent = %d",
				BPGT_CPT_TYPE,
				$post->ID
			) );
		}

		if ( ! empty( $bpgt_type ) ) {
			$bpgt_type->post_name = get_post_field( 'post_name', $bpgt_type->post_parent );
		}

		return $bpgt_type;
	}

	return false;
}

/**
 * Get the array of plugins, that extends BP_Group_Extension - so they are a proper BuddyPress Groups plugins
 *
 * @uses get_plugins()
 * @return array
 */
function bpgt_get_plugins() {
	$active_bp_plugins = $process = array();

	foreach ( get_declared_classes() as $class ) {
		if ( is_subclass_of( $class, 'BP_Group_Extension' ) ) {
			$reflector                           = new ReflectionClass( $class );
			$file                                = $reflector->getFileName();
			$active_bp_plugins[ $class ]['full'] = $file;
			$active_bp_plugins[ $class ]['dir']  = explode( '/', plugin_basename( $file ) )[0];
		}
	}

	if ( ! function_exists( 'get_plugins' ) ) {
		require_once ABSPATH . 'wp-admin/includes/plugin.php';
	}

	$all_plugins = get_plugins();

	// get plugins that are activated and extends BP_Group_Extension
	foreach ( $all_plugins as $plugin => $plugin_data ) {
		foreach ( $active_bp_plugins as $class => $paths ) {
			if ( strpos( $plugin, $paths['dir'] ) !== false ) {
				$process[ $plugin ] = array(
					'class' => $class,
					'name'  => $plugin_data['Name']
				);
			}
		}
	}

	return $process;
}

/**
 * Get the group type object
 *
 * @param $group_id
 *
 * @uses groups_get_groupmeta()
 * @uses BPGT_Type
 * @return mixed
 */
function bpgt_get_type( $group_id ) {
	if ( empty( $group_id ) ) {
		return false;
	}

	$type_id = groups_get_groupmeta( $group_id, 'bpgt_group_type' );

	// TODO add cache support

	if ( ! empty( $type_id ) ) {
		return new BPGT_Type( $type_id );
	}

	return false;
}

/**
 * Check what the type of a group
 * $group_id is required for checking groups only
 * Directories can be checked without it
 *
 * @param string $type_slug Groupt type slug, the same the as the associated WP page slug
 * @param bool|int $group_id
 *
 * @return bool
 */
function bpgt_is_type( $type_slug, $group_id = false ) {
	global $bpgt_type;

	$type_slug = wp_strip_all_tags( $type_slug );

	if ( bpgt_is_directory() ) {
		if ( ! empty( $bpgt_type->post_name ) && $bpgt_type->post_name == $type_slug ) {
			return true;
		}
	} else if ( bp_is_group() ) {
		if ( empty( $group_id ) ) {
			$group_id = bp_get_current_group_id();
		}

		$group_type = bpgt_get_type( $group_id );

		if ( $group_type && $group_type->name == $type_slug ) {
			return true;
		}
	}

	return false;
}