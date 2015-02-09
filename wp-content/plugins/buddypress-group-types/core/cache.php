<?php
/**
 * Clear user caches
 *
 * @param int $group_id
 * @param int $user_id
 */
function bpgt_clear_cache_join_leave_group( $group_id, $user_id ) {
	// general groups
	delete_transient( 'bpgt_my_group_count_type_0_user_' . $user_id );

	// group types
	if ( $group_type_id = groups_get_groupmeta( $group_id, 'bpgt_group_type', true ) ) {
		delete_transient( 'bpgt_my_group_count_type_' . $group_type_id . '_user_' . $user_id );
	}
}

add_action( 'groups_leave_group', 'bpgt_clear_cache_join_leave_group', 10, 2 );
add_action( 'groups_join_group', 'bpgt_clear_cache_join_leave_group', 10, 2 );

/**
 * Clear general caches
 *
 * @param int $group_id
 * @param int $type_id
 * @param int $old_type_id
 */
function bpgt_clear_cache_change_type( $group_id, $type_id, $old_type_id ) {
	delete_transient( 'bpgt_total_group_count_type_0' );
	delete_transient( 'bpgt_total_group_count_type_' . $old_type_id );
	delete_transient( 'bpgt_total_group_count_type_' . $type_id );

	// All personal counters

	// get group members
	$members = groups_get_group_members( array(
		                                     'group_id'            => $group_id,
		                                     'per_page'            => false,
		                                     'page'                => false,
		                                     'exclude_admins_mods' => false,
		                                     'exclude_banned'      => false,
		                                     'exclude'             => false,
		                                     'group_role'          => array(),
		                                     'search_terms'        => false,
		                                     'type'                => 'last_joined',
	                                     ) );
	foreach ( $members['members'] as $member ) {
		delete_transient( 'bpgt_my_group_count_type_' . $old_type_id . '_user_' . $member->ID );
		delete_transient( 'bpgt_my_group_count_type_' . $type_id . '_user_' . $member->ID );
	}
}

add_action( 'bpgt_change_group_type', 'bpgt_clear_cache_change_type', 10, 3 );

/**
 * Group deletion
 *
 * @param int $group_id
 */
function bpgt_clear_cache_delete_group( $group_id ) {
	// general counter for the type
	$group_type_id = groups_get_groupmeta( $group_id, 'bpgt_group_type', true );
	if ( ! $group_type_id ) {
		$group_type_id = '0';
	}

	delete_transient( 'bpgt_total_group_count_type_' . $group_type_id );

	// All personal counters

	// get group members
	$members = groups_get_group_members( array(
		                                     'group_id'            => $group_id,
		                                     'per_page'            => false,
		                                     'page'                => false,
		                                     'exclude_admins_mods' => false,
		                                     'exclude_banned'      => false,
		                                     'exclude'             => false,
		                                     'group_role'          => array(),
		                                     'search_terms'        => false,
		                                     'type'                => 'last_joined',
	                                     ) );

	foreach ( $members['members'] as $member ) {
		delete_transient( 'bpgt_my_group_count_type_' . $group_type_id . '_user_' . $member->ID );
	}
}

do_action( 'groups_before_delete_group', 'bpgt_clear_cache_delete_group' );

/**
 * Group creation
 *
 * @param $group_id
 * @param $group
 */
function bpgt_clear_cache_create_group( $group_id, $group ) {
	groups_add_groupmeta( $group_id, 'bpgt_group_type', '0' );

	if ( $group_type_id = groups_get_groupmeta( $group_id, 'bpgt_group_type', true ) ) {
		delete_transient( 'bpgt_total_group_count_type_' . $group_type_id );
		delete_transient( 'bpgt_my_group_count_type_' . $group_type_id . '_user_' . bp_loggedin_user_id() );
	}
}

add_action( 'groups_created_group', 'bpgt_clear_cache_create_group', 10, 2 );