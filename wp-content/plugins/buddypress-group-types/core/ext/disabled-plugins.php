<?php

/**
 * Remove from WordPress classes-set those plugins, that we selected as such in types admin area
 * Works on groups pages only.
 *
 * @uses groups_get_current_group()
 * @uses bp_is_current_action()
 * @uses bp_is_group()
 * @uses bp_is_groups_component()
 * @uses bpgt_get_type()
 */
function bpgt_remove_plugins_instances() {
	global $wp_filter;

	// process only on groups pages on front-end
	if ( ! bp_is_groups_component() ) {
		return;
	}

	$group_id = 0;

	// get the group id
	if ( bp_is_current_action( 'create' ) ) { // creation
		if ( ! empty( $_COOKIE['bp_new_group_id'] ) ) {
			$group_id = $_COOKIE['bp_new_group_id'];
		}
	} elseif ( bp_is_group() ) { // single group page
		$group = groups_get_current_group();
		if ( ! empty( $group->id ) ) {
			$group_id = $group->id;
		}
	}

	// seems we are on directory page
	if ( empty( $group_id ) ) {
		return;
	}

	// now I can get the type
	$type = bpgt_get_type( $group_id );

	// in case all plugins are enabled - bail
	if ( empty( $type->disabled_plugins ) ) {
		return;
	}

	// get everything that was loaded properly with bp_register_group_extension()
	foreach ( $wp_filter['bp_actions'][8] as $hash => $class_data ) { // filter appropriate action
		foreach ( $class_data as $obj ) { // get the instance of the class, that we can reuse
			foreach ( (array) $type->disabled_plugins as $plugin_class ) {
				if ( is_object( $obj[0] ) && get_class( $obj[0] ) == $plugin_class ) { // we should remove only disabled plugins
					remove_action( "bp_actions", array(
						$obj[0],
						$obj[1]
					), 8 ); // reflects the code from bp_register_group_extension() - but opposite
				}
			}
		}
	}
}

add_action( 'bp_init', 'bpgt_remove_plugins_instances', 12 );