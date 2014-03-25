<?php
/**
 * Member messages compose
 *
 * @package BuddyPress
 * @subpackage Templatepack
 */
?>
<?php
/**
* This is a basic copy over of compose markup, with adjustments for autofocus input/removal of jq script
* ToDo: markup for autocomplete needs reviewing e.g input not necessarilly making accessible/semantic sense
* in that ul construct.
*/
?>
<div class="messages-content-wrap">

<form action="<?php bp_messages_form_action('compose' ); ?>" method="post" id="send_message_form" class="standard-form" role="main" enctype="multipart/form-data">

	<?php do_action( 'bp_before_messages_compose_content' ); ?>

	<label for="send-to-input"><?php _e("Send To (Username or Friend's Name)", 'buddypress' ); ?></label>
	<ul class="first acfb-holder">
		<li>
			<?php bp_message_get_recipient_tabs(); ?>
			<input type="text" name="send-to-input" class="send-to-input add-focus" id="send-to-input" autofocus />
		</li>
	</ul>

	<?php if ( bp_current_user_can( 'bp_moderate' ) ) : ?>
		<input type="checkbox" id="send-notice" name="send-notice" value="1" /> <?php _e( "This is a notice to all users.", "buddypress" ); ?>
	<?php endif; ?>

	<label for="subject"><?php _e( 'Subject', 'buddypress' ); ?></label>
	<input type="text" name="subject" id="subject" value="<?php bp_messages_subject_value(); ?>" />

	<label for="content"><?php _e( 'Message', 'buddypress' ); ?></label>
	<textarea name="content" id="message_content" rows="15" cols="40"><?php bp_messages_content_value(); ?></textarea>

	<input type="hidden" name="send_to_usernames" id="send-to-usernames" value="<?php bp_message_get_recipient_usernames(); ?>" class="<?php bp_message_get_recipient_usernames(); ?>" />

	<?php do_action( 'bp_after_messages_compose_content' ); ?>

	<div class="submit">
		<input type="submit" value="<?php _e( "Send Message", 'buddypress' ); ?>" name="send" id="send" />
	</div>

	<?php wp_nonce_field( 'messages_send_message' ); ?>
</form>

<!-- remove paragraph elements below -->

<p>Compose messages</p>
<p>This is dummy text (temporary), for the 'compose' template file. Compose.php is set in 'messages.php' as the default item to show on mesages top level, for want of something better to show.</p>
<p>Options ? we could remove submenu leaving access only by returning to top level messages or we can re-include the include for compose so the link works then we'll navigate to a view for just the compose  elements without the message thread list; so in effect 'Compose' will appear as a default screen on main messages untill you select a message thread to view or it can be accessed via submenu link.</p>


</div>
