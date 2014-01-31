<?php

function bp_gom_pro_blocking_check_profile( $user_id, $use_cache = true )
{
	if ( is_admin() ) {
		return;
	}
	
	// check cache if applicable
	if ( $use_cache && bp_gom_pro_blocking_profile_complete( $user_id ) === true ) {
		// profile is complete (according to cache)
		return true;
	}

	// loop all fields
	foreach ( bp_gom_matching_all_fields() as $field ) {

		// is this a required field?
		if ( $field->is_required ) {

			// get group-o-matic field meta
			$field_meta = new BP_Gom_Field_Meta( $field->id, false );

			// is blocking enabled?
			if ( $field_meta->blocking !== true ) {
				// blocking NOT enabled so skip this loop
				continue;
			}

		} else {
			// field not required, blocking not applicable
			continue;
		}

		// try to get the data and value
		$field_data = $field->get_field_data( $user_id );
		$field_value = maybe_unserialize( $field_data->value );

		// anything?
		if ( is_bool( $field_value ) ) {
			if ( $field_value ) {
				continue;
			}
		} elseif ( is_array( $field_value ) ) {
			if ( count( $field_value ) ) {
				continue;
			}
		} else {
			$field_as_string = (string) $field_value;
			if ( strlen( $field_as_string ) ) {
				continue;
			}
		}

		// field not set
		return false;
	}

	// made it!
	bp_gom_pro_blocking_profile_complete( $user_id, true );

	do_action( 'bp_gom_pro_blocking_profile_complete', $user_id );

	return true;
}

function bp_gom_pro_blocking_profile_complete( $user_id, $value = null )
{
	if ( func_num_args() > 1 ) {
		update_user_meta( $user_id, BP_GOM_META_KEY_PROFILE_COMPLETE, $value );
	}

	return (boolean) get_user_meta( $user_id, BP_GOM_META_KEY_PROFILE_COMPLETE, true );
}

function bp_gom_pro_blocking_step( $user_id, $value = null )
{
	if ( func_num_args() > 1 ) {
		update_user_meta( $user_id, BP_GOM_META_KEY_BLOCKING_STEP, $value );
	}

	return (integer) get_user_meta( $user_id, BP_GOM_META_KEY_BLOCKING_STEP, true );
}

function bp_gom_pro_blocking_message_one()
{
	$message = apply_filters(
		'bp_gom_pro_blocking_message_one_text',
		__( 'Thanks for signing up! We have almost everything we need to get you started, and all you need to do now is fill in the fields below. We need this information to complete your profile! You will not be able to navigate our community until these fields are filled in!', 'buddypress-groupomatic' )
	);

	// print incomplete message ?>
	<div id="message" class="info buddypress-groupomatic-blocking-message-one">
		<p><?php print $message ?></p>
	</div><?php
}

function bp_gom_pro_blocking_message_two()
{
	$message = apply_filters(
		'bp_gom_pro_blocking_message_two_text',
		__( "We don't have enough information to get started yet. Please fill in the required profile fields below.", 'buddypress-groupomatic' )
	);

	// print incomplete message ?>
	<div id="message" class="info buddypress-groupomatic-blocking-message-two">
		<p>
			<?php print $message  ?>
		</p>
	</div><?php
}

/**
 * Maybe redirect back to profile edit screen if required fields are incomplete
 *
 * @return void
 */
function bp_gom_pro_blocking_maybe_redirect()
{
	$user_id = get_current_user_id();

	// ignore if no user id
	if ( !$user_id ) {
		return;
	}

	// ignore if this is admin area
	if ( is_admin() ) {
		return;
	}

	// ignore this if its the login screen
	if ( basename( $_SERVER['PHP_SELF'] ) == 'wp-login.php' ) {
		return;
	}

	// ignore if this is profile edit page (else infinite loop)
	if ( bp_current_component() == BP_XPROFILE_SLUG && bp_current_action() == 'edit' ) {
		return;
	}

	// check profile
	if ( bp_gom_pro_blocking_check_profile( $user_id ) ) {
		// profile is complete
		return;
	} else {
		// redirect to profile edit screen
		$url = bp_core_get_user_domain( $user_id ) . BP_XPROFILE_SLUG . '/edit/';
		bp_core_redirect( $url );
	}
}
add_action( 'bp_init', 'bp_gom_pro_blocking_maybe_redirect' );

/**
 * Display profile incomplete message if applicable
 *
 * @return void
 */
function bp_gom_pro_blocking_show_incomplete_message()
{
	$user_id = get_current_user_id();

	if ( bp_gom_pro_blocking_check_profile( $user_id, false ) ) {
		return;
	}

	switch ( bp_gom_pro_blocking_step( $user_id ) ) {
		case 0:
			bp_gom_pro_blocking_message_one();
			bp_gom_pro_blocking_step( $user_id, 1 );
			return;
		case 1:
			bp_gom_pro_blocking_step( $user_id, 2 );
		case 2:
			bp_gom_pro_blocking_message_two();
			return;
		default:
			return;
	}
}
add_action( 'bp_before_profile_content', 'bp_gom_pro_blocking_show_incomplete_message' );

?>
