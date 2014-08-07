<?php
function bp_like_remove_user_like( $item_id = '', $type = 'activity') {
	global $bp;
	
	if ( !$item_id )
		return false;

	if ( !$user_id )
		$user_id = $bp->loggedin_user->id;
	
	if ( $user_id == 0 ) {
		echo bp_like_get_text( 'must_be_logged_in' );
		return false;
	}

	if ( $type == 'activity' ) :

		/* Remove this from the users liked activities. */
		$user_likes = get_user_meta( $user_id, 'bp_liked_activities', true );
		unset( $user_likes[$item_id] );
		update_user_meta( $user_id, 'bp_liked_activities', $user_likes );
		/* add this to user's unlike activity */
		$user_unlikes = get_user_meta( $user_id, 'bp_unliked_activities', true );
		$user_unlikes[$item_id] = 'activity_unliked';
		update_user_meta( $user_id, 'bp_unliked_activities', $user_unlikes );
		
		/* Update the total number of users who have liked this activity. */
		$users_who_like = bp_activity_get_meta( $item_id, 'liked_count', true );
		unset( $users_who_like[$user_id] );
		/* Update the total number of users who have unliked this activity. */
		$users_who_unlike = bp_activity_get_meta( $item_id, 'unliked_count', true );
		$users_who_unlike[$user_id] = 'user_unlikes';
		bp_activity_update_meta( $item_id, 'unliked_count', $users_who_unlike );
		
		/* If nobody likes the activity, delete the meta for it to save space, otherwise, update the meta */
		if ( empty( $users_who_like ) )
			bp_activity_delete_meta( $item_id, 'liked_count' );
		else
			bp_activity_update_meta( $item_id, 'liked_count', $users_who_like );
	
		$liked_count = count( $users_who_like );

		/* Remove the update on the users profile from when they liked the activity. */
		$update_id = bp_activity_get_activity_id(
			array(
				'item_id' => $item_id,
				'component' => 'bp-like',
				'type' => 'activity_liked',
				'user_id' => $user_id
			)
		);
	
		bp_activity_delete(
			array(
				'id' => $update_id,
				'item_id' => $item_id,
				'component' => 'bp-like',
				'type' => 'activity_liked',
				'user_id' => $user_id
			)
		);
		
	elseif ( $type == 'blogpost' ) :
		
		/* Remove this from the users liked activities. */
		$user_likes = get_user_meta( $user_id, 'bp_liked_blogposts', true );
		unset( $user_likes[$item_id] );
		update_user_meta( $user_id, 'bp_liked_blogposts', $user_likes );

		/* Add to the users unliked blog posts. */
		$user_unlikes = get_user_meta( $user_id, 'bp_unliked_blogposts', true);
		$user_unlikes[$item_id] = 'blogpost_unliked';
		update_user_meta( $user_id, 'bp_unliked_blogposts', $user_unlikes );
		
		/* Update the total number of users who have liked this blog post. */
		$users_who_like = get_post_meta( $item_id, 'liked_count', true );
		unset( $users_who_like[$user_id] );
		
		/* Add to the total unlikes for this blog post. */
		$users_who_unlike = get_post_meta( $item_id, 'unliked_count', true );
		$users_who_unlike[$user_id] = 'user_unlikes';
		update_post_meta( $item_id, 'unliked_count', $users_who_unlike );
		
		/* If nobody likes the blog post, delete the meta for it to save space, otherwise, update the meta */
		if ( empty( $users_who_like ) )
			delete_post_meta( $item_id, 'liked_count' );
		else
			update_post_meta( $item_id, 'liked_count', $users_who_like );

		$liked_count = count( $users_who_like );

		/* Remove the update on the users profile from when they liked the activity. */
		$update_id = bp_activity_get_activity_id(
			array(
				'item_id' => $item_id,
				'component' => 'bp-like',
				'type' => 'blogpost_liked',
				'user_id' => $user_id
			)
		);
	
		bp_activity_delete(
			array(
				'id' => $update_id,
				'item_id' => $item_id,
				'component' => 'bp-like',
				'type' => 'blogpost_liked',
				'user_id' => $user_id
			)
		);
		
	endif;

	echo bp_like_get_text( 'like' );
	if ($liked_count)
		echo ' (' . $liked_count . ')';
}
?>