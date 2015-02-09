<?php
/**
 * Filter out avatar for groups only
 *
 * @param $no_grav
 *
 * @return bool
 */
function bpgt_get_group_avatar_maybe_grav( $no_grav ) {
	if ( ! $no_grav ) {
		add_filter( 'bp_core_default_avatar_group', 'bpgt_get_group_default_avatar', 10, 2 );
	}

	return true;
}

add_filter( 'bp_core_fetch_avatar_no_grav', 'bpgt_get_group_avatar_maybe_grav', 9999 );

/**
 * Change the default avatar for group types
 *
 * @param string $avatar URL to a group avatar
 * @param $params
 *
 * @return string
 */
function bpgt_get_group_default_avatar( $avatar, $params ) {
	if ( $params['object'] == 'group' && $group_type_id = groups_get_groupmeta( $params['item_id'], 'bpgt_group_type', true ) ) {
		$type = new BPGT_Type( $group_type_id );

		$avatar_raw = $type->get_avatar_img_src();
		if ( ! empty( $avatar_raw ) ) {
			$avatar = $avatar_raw;
		}
	}

	return $avatar;
}
