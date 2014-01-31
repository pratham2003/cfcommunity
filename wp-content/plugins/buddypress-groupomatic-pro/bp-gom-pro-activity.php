<?php

/**
 * Add group tabs to main activity directory if applicable
 *
 * @return void
 */
function bp_gom_pro_activity_add_tabs()
{
	// only show tabs if user logged in
	if ( !is_user_logged_in() ) {
		return;
	}

	// get matching groups meta data for logged in user
	$user_groups_meta = bp_gom_matching_groups_meta( bp_loggedin_user_id() );

	// loop all groups
	foreach ( $user_groups_meta->get_groups() as $group_id => $user_group_meta ) {
		
		// show activity for this group?
		if ( $user_group_meta->activity !== true ) {
			// nope
			continue;
		}

		// load group
		$group = new BP_Groups_Group( $group_id );
		
		// make sure group populated
		if ( empty( $group->slug ) ) {
			// that sucks
			continue;
		}
		
		// format title
		$title = sprintf( __( 'Activity for %s', 'buddypress-groupomatic' ), $group->name );

		// finally we can print the nav item markup ?>
		<li id="activity-<?php print esc_attr( $group->slug ) ?>">
			<a href="<?php echo site_url( BP_ACTIVITY_SLUG . '/#' . $group->slug . '/' ) ?>" title="<?php print esc_attr( $title ) ?>">
			  <?php print $group->name ?>
			</a>
		</li><?php
	}
}
add_action( 'bp_activity_type_tabs', 'bp_gom_pro_activity_add_tabs', 1, 2 );

function bp_gom_pro_activity_ajax_querystring_filter( $query_string, $object, $filter, $scope, $page, $search_terms, $extras )
{
    global $bp;

	// skip filtering if not applicable
	if ( !is_user_logged_in() || $object != 'activity' ) {
		return $query_string;
	}

	// parse (IN) args
	parse_str( $query_string, $args );

	// have a group slug?
	if ( $scope ) {
		// try to find the group id
		$group_id = BP_Groups_Group::get_id_from_slug( $scope );
		// get a group id?
		if ( $group_id ) {
			// switch object to groups
			$args['object'] = $bp->groups->id;
			// append group id as primary id
			$args['primary_id'] = $group_id;
			// return modified query string
			return http_build_query( $args );
		}
	}

	// do NOT alter query string
	return $query_string;
}
add_filter( 'bp_dtheme_ajax_querystring', 'bp_gom_pro_activity_ajax_querystring_filter', 1, 7 );

?>
