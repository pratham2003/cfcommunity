<?php

add_action('template_redirect', 'cp_like_callback');
function cp_like_callback() {
	if ( isset( $_GET['action'] ) && $_GET['action'] == 'cp_like' && isset( $_GET['likeAction'] ) ) {
	
		$user_id = false;
	
		if ( is_user_logged_in() ) {
		
			$current_user = wp_get_current_user();
			$user_id = $current_user->ID;
		
		}
		
		$this_id = get_the_id();
		$action = $_GET['likeAction'];
		$likes  = get_post_meta( get_the_id(), '_cp_likes', true );

		if ( $action == 'like' ) {
			$likes = $likes ? intval( $likes ) + 1 : 1;
			if ($user_id):
				$user_likes = get_user_meta($user_id, 'cp_likes', true);
				$user_likes[] = $this_id;
				update_user_meta( $user_id, 'cp_likes', $user_likes );
			endif;
		} else {
			$likes = $likes ? intval( $likes ) - 1 : 0;
			if ($user_id):
				$user_likes = get_user_meta($user_id, 'cp_likes', true);
		        $user_likes = array_diff($user_likes, array($this_id));
		        $user_likes = array_values($user_likes);
		        update_user_meta( $user_id, 'cp_likes', $user_likes );
			endif;
		}
		update_post_meta( $this_id, '_cp_likes', $likes );

		echo $likes;
		exit;		
	}
}