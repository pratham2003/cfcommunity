<?php
/**
 * Member messages
 *
 * @package BuddyPress
 * @subpackage Templatepack
 */
?>
<nav id="subnav" class="item-list-tabs no-ajax" role="navigation">
	<ul>

		<?php bp_get_options_nav(); ?>

	</ul>

	<?php if ( bp_is_messages_inbox() || bp_is_messages_sentbox() ) : ?>

		<div class="message-search"><?php bp_message_search_form(); ?></div>

	<?php endif; ?>

</nav><!-- .item-list-tabs -->

<?php
switch ( bp_current_action() ) :

	// Inbox/Sentbox
	case 'inbox'   :
	case 'sentbox' :
	case 'view'    :
 ?>


			<?php bp_get_template_part( 'members/single/messages/messages-loop' ); ?>

			<?php switch ( bp_current_action() ) :

				// View full message
				case 'view' :

					do_action( 'bp_before_member_messages_content' );

					bp_get_template_part( 'members/single/messages/single' );

					do_action( 'bp_after_member_messages_content' );

				break;

			// Compose new mesage or reply to
			case 'compose' :

				bp_get_template_part( 'members/single/messages/compose' );

			break;

			default:

				bp_get_template_part( 'members/single/messages/compose' );

			break;
			endswitch;

		break;

	// Compose
	case 'compose' :
		do_action( 'bp_before_member_messages_content' ); ?>

		<div class="messages">
			<?php bp_get_template_part( 'members/single/messages/compose' );; ?>
		</div><!-- .messages -->

		<?php do_action( 'bp_after_member_messages_content' );
		break;

	// Sitewide Notices
	case 'notices' :
		do_action( 'bp_before_member_messages_content' ); ?>

		<div class="messages">
			<?php bp_get_template_part( 'members/single/messages/notices-loop' );; ?>
		</div><!-- .messages -->

		<?php do_action( 'bp_after_member_messages_content' );
		break;

	// Any other
	default :
		bp_get_template_part( 'members/single/plugins' );
		break;
endswitch;
