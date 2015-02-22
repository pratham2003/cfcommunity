<?php

/**
 * bp_show_verified_badge function.
 * 
 * @access public
 * @param mixed $object
 * @return void
 */
function bp_show_verified_badge($object) {
  	global $bp;

  	$is_verified = get_user_meta( $bp->displayed_user->id, 'bp-verified', true );
	
	if ( !empty( $is_verified ) ):
		if ( $is_verified['profile'] == 'yes' ):
				if (  $is_verified['image'] == null ): 
					$object .= '<span id="bp-verified-header"><img src="' . VERIFIED_URL . '/images/1.png"></span>';
				else :
					$object .= '<span id="bp-verified-header"><img src="' . VERIFIED_URL . '/images/' . $is_verified['image'] . '.png"></span>';
				endif ;
		endif;
  	endif;
  	
  	return $object;
}
add_filter( 'bp_get_displayed_user_avatar', 'bp_show_verified_badge' );



/**
 * bp_show_verified_badge_activity function.
 * 
 * @access public
 * @param mixed $object
 * @return void
 */
function bp_show_verified_badge_activity($object) {
	global $bp, $activities_template;
	
	$comments = isset( $activities_template->activity->current_comment ) ? $activities_template->activity->current_comment->user_id : (int) $activities_template->activity->user_id;
	
	$is_verified = get_user_meta( $comments, 'bp-verified', true );
 
	if ( !empty( $is_verified ) ):
		if ( $is_verified['profile'] == 'yes' ):
			if (  $is_verified['image'] == null ): 
				$object .= '<span id="bp-verified"><img src="' . VERIFIED_URL . '/images/1.png"></span>';
			else :
				$object .= '<span id="bp-verified"><img src="' . VERIFIED_URL . '/images/' . $is_verified['image'] . '.png"></span>';
			endif ;
	  	endif;
	endif;

	return $object;
}
add_filter( 'bp_get_activity_avatar', 'bp_show_verified_badge_activity' );


/**
 * bp_show_verified_badge_members function.
 * 
 * @access public
 * @param mixed $object
 * @return void
 */
function bp_show_verified_badge_members($object) {
	global $bp, $members_template;
		
	$comments = isset( $members_template->members ) ? (int) $members_template->member->id : '';
	$is_verified = get_user_meta( $comments, 'bp-verified', true );
 
	if ( !empty( $is_verified ) ):
		if ( $is_verified['profile'] == 'yes' ):
			if (  $is_verified['image'] == null ): 
				$object .= '<span id="bp-verified"><img src="' . VERIFIED_URL . '/images/1.png"></span>';
			else :
				$object .= '<span id="bp-verified"><img src="' . VERIFIED_URL . '/images/' . $is_verified['image'] . '.png"></span>';
			endif ;
	  	endif;
	endif;

	return $object;
}
add_filter( 'bp_get_member_avatar', 'bp_show_verified_badge_members' );


/**
 * bp_show_verified_badge_avatar function.
 * 
 * @access public
 * @param mixed $object
 * @return void
 */
function bp_show_verified_badge_avatar($object) {
	global $bp, $members_template;
		
	$comments = isset( $members_template->members ) ? (int) $members_template->member->id : '';
	$is_verified = get_user_meta( $comments, 'bp-verified', true );
 
	if ( !empty( $is_verified ) ):
		if ( $is_verified['profile'] == 'yes' ):
			if (  $is_verified['image'] == null ): 
				$object .= '<span id="bp-verified"><img src="' . VERIFIED_URL . '/images/1.png"></span>';
			else :
				$object .= '<span id="bp-verified"><img src="' . VERIFIED_URL . '/images/' . $is_verified['image'] . '.png"></span>';
			endif ;
	  	endif;
	endif;

	return $object;
}
add_action( 'bp_get_member_avatar', 'bp_show_verified_badge_members' );
add_action( 'bp_get_group_member_avatar_thumb', 'bp_show_verified_badge_members' );

function bp_verified_text(){
	global $bp, $members_template;
		
	$is_verified = get_user_meta( $bp->displayed_user->id, 'bp-verified', true );
	
	$text = !empty($is_verified['text']) ? $is_verified['text'] : 'Verified User' ;
	
	echo '<div id="bp-verified-text">'.$text.'</div>';
}
add_action('bp_profile_header_meta', 'bp_verified_text');


/**
 * my_scripts_method function.
 * 
 * @access public
 * @return void
 */
function buddyverified_scripts_enqueue() {
	wp_enqueue_style( 'verified-style', plugins_url( '/css/verified.css' , __FILE__ ) );
}

add_action( 'wp_enqueue_scripts', 'buddyverified_scripts_enqueue' );