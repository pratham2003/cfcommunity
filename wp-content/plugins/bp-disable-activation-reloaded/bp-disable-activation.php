<?php

function disable_validation( $user_id ) {
	global $wpdb;

	//Hook if you want to do something before the activation
	do_action('bp_disable_activation_before_activation');
	
	$wpdb->query( $wpdb->prepare( "UPDATE $wpdb->users SET user_status = 0 WHERE ID = %d", $user_id ) );
	
	//Add note on Activity Stream
	if ( function_exists( 'bp_activity_add' ) ) {
		$userlink = bp_core_get_userlink( $user_id );
		
		bp_activity_add( array(
			'user_id' => $user_id,
			'action' => apply_filters( 'bp_core_activity_registered_member', sprintf( __( '%s became a registered member', 'buddypress' ), $userlink ), $user_id ),
			'component' => 'profile',
			'type' => 'new_member'
		) );
		
	}
	//Send email to admin
	wp_new_user_notification( $user_id );
	// Remove the activation key meta
    delete_user_meta( $user_id, 'activation_key' );
	// Delete the total member cache
    wp_cache_delete( 'bp_total_member_count', 'bp' );

	//Hook if you want to do something before the login
	do_action('bp_disable_activation_before_login');
	
	if( $options['enable_login'] == 'true' )
	{
		//Automatically log the user in	.
		//Thanks to Justin Klein's  wp-fb-autoconnect plugin for the basic code to login automatically
		$user_info = get_userdata($user_id);
		wp_set_auth_cookie($user_id);

		do_action('wp_signon', $user_info->user_login);
	}
	
	//Hook if you want to do something after the login
	do_action('bp_disable_activation_after_login');
}

	

function fix_signup_form_validation_text() {
	return false;
}


function disable_activation_email() {
	return false;
}



/*START Functions to automatically activate for WPMU (multi-site)  Installs (Activates User and Blogs)*/

/*
 Credit for most of the WPMU code goes to Brajesh Singh and his plugin "BP Auto activate User and Blog at Signup"
*/


function cc_auto_activate_on_user_signup($user, $user_email, $key, $meta) {
	
	bp_core_activate_signup($key);

}

?>