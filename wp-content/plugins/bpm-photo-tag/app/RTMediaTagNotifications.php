<?php
/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of BPMediaTagNotifications
 *
 * @author saurabh
 */
class RTMediaTagNotifications {

	function __construct() {
		add_filter( 'bp_members_notification_callback', array( $this, 'notifications' ) );
		add_filter( 'rtmedia_notifications', array( $this, 'format_notifications' ) );
		add_action( 'rtmedia_after_photo', array( $this, 'remove_notifications' ) );
		add_action( 'bp_notification_settings', array( $this, 'settings' ) );
	}

	function url( $media_id ) {
		$model = new RTMediaModel();
		$media = $model->get( array( 'id' => $media_id ) );
		if ( $media ) {
			$media = $media[ 0 ];
		}
		$url = trailingslashit( get_rtmedia_user_link( $media->media_author ) ) .'media/'. $media_id . '/';

		return $url;
	}

	function notifications() {
		return 'rtm_notifications_callback';
	}

	function format_notifications( $params ) {
		$action = $params[ 'action' ];
		$media_id = $params[ 'media_id' ];
		$initiator_id = $params[ 'initiator_id' ];
		$total_items = $params[ 'total_items' ];
		$format = $params[ 'format' ];
		//We are not handling multiple notifications because a user can't be tagged more than once!
		$media_url = $this->url( $media_id );


		if ( 'rtm_new_tag' == $action ) {
			$text = sprintf( __( '%s tagged you in a photo', 'rtm-photo-tagging' ), bp_core_get_user_displayname( $initiator_id ) );
			$link = $media_url;
			if ( $format == 'string' ) {
				$return = apply_filters( 'rtmedia_tagging_notification', '<a href="' . $link . '">' . $text . '</a>', (int) $total_items );
			} else {
				$return = apply_filters( 'rtmedia_tagging_notification', array(
					'link' => $link,
					'text' => $text
						), (int) $total_items );
			}

			do_action( 'rtmedia_tagging_format_notifications', $params );
			return $return;
		}
	}

	function remove_notifications( $media_id ) {
		global $bp;
		if( function_exists( 'bp_notifications_delete_notifications_by_item_id' ) ){
			bp_notifications_delete_notifications_by_item_id(
				$bp->loggedin_user->id,
				$media_id,
				'members',
				'rtm_new_tag' );
		} else {
			bp_core_delete_notifications_by_item_id(
				$bp->loggedin_user->id,
				$media_id,
				'members',
				'rtm_new_tag' );
		}
	}

	function notify( $media_id, $tagged_id, $tagger_id ) {
		if ( $tagger_id == $tagged_id )
			return;

		// Don't leave multiple notifications for the same activity item
		if( class_exists( 'BP_Notifications_Notification' ) ){
			$notifications = BP_Notifications_Notification::get_all_for_user( $tagged_id, 'all' );
		} else {
			$notifications = BP_Core_Notification::get_all_for_user( $tagged_id, 'all' );
		}


		foreach ( $notifications as $notification ) {
			if ( $media_id == $notification->item_id ) {
				return;
			}
		}


		$subject = '';
		$message = '';
		$content = '';

		// Add the BP notification
		if( function_exists( 'bp_notifications_add_notification' ) ){
			$args_add_noification = array(
				'item_id' => $media_id,
				'user_id' => $tagged_id,
				'component_name' => 'members',
				'component_action' => 'rtm_new_tag',
				'secondary_item_id' => $tagger_id
			);
			bp_notifications_add_notification( $args_add_noification );
		} else {
			bp_core_add_notification( $media_id, $tagged_id, 'members', 'rtm_new_tag', $tagger_id );
		}

		// Now email the user with the contents of the message (if they have enabled email notifications)
		if ( 'no' != bp_get_user_meta( $tagged_id, 'RTMEDIA_new_tag_notification', true ) ) {
			$poster_name = bp_core_get_user_displayname( $tagger_id );

			$message_link = $this->url( $media_id );
			$settings_slug = function_exists( 'bp_get_settings_slug' ) ? bp_get_settings_slug() : 'settings';
			$settings_link = bp_core_get_user_domain( $tagged_id ) . $settings_slug . '/notifications/';

			$poster_name = stripslashes( $poster_name );

			// Set up and send the message
			$ud = bp_core_get_core_userdata( $tagged_id );
			$to = $ud->user_email;
			$subject = bp_get_email_subject( array( 'text' => sprintf( __( '%s tagged you in a photo', 'rtm-photo-tagging' ), $poster_name ) ) );


			$message = sprintf( __(
							'%1$s tagged you in an <a href="%2$s">photo</a>
<br />
---------------------
<br />
', 'rtm-photo-tagging' ), $poster_name, $message_link );

			$message .= sprintf( __( 'To disable these notifications please log in and go to: %s', 'rtm-photo-tagging' ), $settings_link );

			/* Send the message */
			$to = apply_filters( 'bp_activity_at_message_notification_to', $to );
			$subject = apply_filters( 'bp_activity_at_message_notification_subject', $subject, $poster_name );
			$message = apply_filters( 'bp_activity_at_message_notification_message', $message, $poster_name, $content, $message_link, $settings_link );
			add_filter( 'wp_mail_content_type', create_function( '', 'return "text/html";' ) );
			wp_mail( $to, $subject, $message );
		}

		do_action( 'rtmedia_sent_new_tag_email', $subject, $message, $media_id );
	}

	function settings() {
		if ( ! $tagging = bp_get_user_meta( bp_displayed_user_id(), 'rtmedia_new_tag_notification', true ) )
			$tagging = 'yes';
		?>

		<table class="notification-settings" id="activity-notification-settings">
			<thead>
				<tr>
					<th class="icon">&nbsp;</th>
					<th class="title"><?php _e( 'Photo Tagging', 'rtm-photo-tagging' ) ?></th>
					<th class="yes"><?php _e( 'Yes', 'rtm-photo-tagging' ) ?></th>
					<th class="no"><?php _e( 'No', 'rtm-photo-tagging' ) ?></th>
				</tr>
			</thead>

			<tbody>
				<tr id="rtm-photo-tagging-notification-settings">
					<td>&nbsp;</td>
					<td><?php printf( __( 'A friend tags you in a photo', 'rtm-photo-tagging' ), bp_core_get_username( bp_displayed_user_id() ) ) ?></td>
					<td class="yes"><input type="radio" name="notifications[rtmedia_new_tag_notification]" value="yes" <?php checked( $tagging, 'yes', true ) ?>/></td>
					<td class="no"><input type="radio" name="notifications[rtmedia_new_tag_notification]" value="no" <?php checked( $tagging, 'no', true ) ?>/></td>
				</tr>
				<?php do_action( 'rtmedia_photo_tagging_notification_settings' ) ?>
			</tbody>
		</table>
		<?php
	}

}

function rtm_notifications_callback( $action, $media_id, $initiator_id, $total_items, $format = 'string' ) {
	$params = array(
		'action' => $action,
		'media_id' => $media_id,
		'initiator_id' => $initiator_id,
		'total_items' => $total_items,
		'format' => $format
	);

	return apply_filters( 'rtmedia_notifications', $params );
}