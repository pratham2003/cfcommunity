<?php

function bp_gom_pro_admin_xprofile_save_options( $meta )
{
	// activity tab option
	if ( isset( $_POST[ BP_Gom_Field_Meta::KEY_ACTIVITY ] ) ) {
		$meta->activity = $_POST[ BP_Gom_Field_Meta::KEY_ACTIVITY ];
	}

	// blocking option
	if ( isset( $_POST[ BP_Gom_Field_Meta::KEY_BLOCKING ] ) ) {
		$meta->blocking = $_POST[ BP_Gom_Field_Meta::KEY_BLOCKING ];
	}
}
add_action( 'bp_gom_admin_xprofile_before_save_options', 'bp_gom_pro_admin_xprofile_save_options', 10, 1 );

function bp_gom_pro_admin_xprofile_after_save_options()
{
	delete_metadata( 'user', 0, BP_GOM_META_KEY_PROFILE_COMPLETE, null, true );
	delete_metadata( 'user', 0, BP_GOM_META_KEY_BLOCKING_STEP, null, true );
}
add_action( 'bp_gom_admin_xprofile_after_save_options', 'bp_gom_pro_admin_xprofile_after_save_options' );

function bp_gom_pro_admin_render_options( $meta )
{
	// render pro options
	bp_gom_pro_admin_render_blocking_option( $meta );

	// render activity option if component enabled
	if ( bp_is_active( 'activity' ) ) {
		bp_gom_pro_admin_render_activity_option( $meta );
	}
}
add_action( 'bp_gom_admin_render_options_fieldset', 'bp_gom_pro_admin_render_options', 10, 1 );

function bp_gom_pro_admin_matching_option_operators( $meta )
{
	// render additonal operators ?>
	<option value="matches"<?php if ( 'matches' == $meta->operator ) { ?> selected="selected"<?php } ?>><?php _e( 'Matches', 'buddypress-groupomatic' ); ?></option>
	<option value="pcre"<?php if ( 'pcre' == $meta->operator ) { ?> selected="selected"<?php } ?>><?php _e( 'Matches PCRE', 'buddypress-groupomatic' ); ?></option><?php
}
add_action( 'bp_gom_admin_render_matching_option_operators', 'bp_gom_pro_admin_matching_option_operators', 10, 1 );

function bp_gom_pro_admin_render_activity_option( BP_Gom_Field_Meta $meta )
{
	// get meta value
	$enabled = $meta->activity;

	// render field ?>
	<div id="titlediv">
		<h3><label for="buddypress-groupomatic-activity"><?php _e( "Show group activity tab on main stream?", 'buddypress-groupomatic' ); ?></label></h3>
		<select name="<?php print BP_Gom_Field_Meta::KEY_ACTIVITY ?>" id="buddypress-groupomatic-activity" style="width: 30%">
			<option value="1"<?php if ( $enabled ) { ?> selected="selected"<?php } ?>><?php _e( 'Yes', 'buddypress-groupomatic' ); ?></option>
			<option value="0"<?php if ( !$enabled ) { ?> selected="selected"<?php } ?>><?php _e( 'No', 'buddypress-groupomatic' ); ?></option>
		</select>
	</div><?php
}

function bp_gom_pro_admin_render_blocking_option( BP_Gom_Field_Meta $meta )
{
	// get meta value
	$enabled = $meta->blocking;

	// render field ?>
	<div id="titlediv">
		<h3><label for="buddypress-groupomatic-blocking"><?php _e( "Force to edit profile if incomplete?", 'buddypress-groupomatic' ); ?></label></h3>
		<select name="<?php print BP_Gom_Field_Meta::KEY_BLOCKING ?>" id="buddypress-groupomatic-blocking" style="width: 30%">
			<option value="1"<?php if ( $enabled ) { ?> selected="selected"<?php } ?>><?php _e( 'Yes', 'buddypress-groupomatic' ); ?></option>
			<option value="0"<?php if ( !$enabled ) { ?> selected="selected"<?php } ?>><?php _e( 'No', 'buddypress-groupomatic' ); ?></option>
		</select>
	</div><?php
}

?>
