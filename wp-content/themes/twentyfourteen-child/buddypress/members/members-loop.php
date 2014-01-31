<?php

/**
 * BuddyPress - Members Loop
 *
 * Querystring is set via AJAX in _inc/ajax.php - bp_legacy_theme_object_filter()
 *
 * @package BuddyPress
 * @subpackage bp-legacy
 */

?>

<?php do_action( 'bp_before_members_loop' ); ?>

<?php if ( bp_has_members( 'type=random&max=50&per_page=50&exclude=3,4,2,1,8,41' ) ) : ?>



	<?php do_action( 'bp_before_directory_members_list' ); ?>

	<div id="members-list" class="avatar-block" role="main">

		<div class="item-avatar">
															<a href="http://ma.tt">
					<img src="http://gravatar.com/avatar/767fc9c115a1b989744c755db47feb60?d=gravatar_default&amp;s=312&amp;r=G" class="avatar user-41-avatar avatar- photo" width="150" height="150" alt="Profile picture of Matt Mullenweg" originals="150" src-orig="http://gravatar.com/avatar/767fc9c115a1b989744c755db47feb60?d=gravatar_default&amp;s=150&amp;r=G" scale="2">					</a>
															<br>
					<div class="name-field">
						<a href="http://ma.tt">
						Matt Mullenweg						</a>
					</div>
			</div>

	<?php while ( bp_members() ) : bp_the_member(); ?>

	<?php $url= xprofile_get_field_data( 'Website URL' ,bp_get_member_user_id());?>
	<?php $name= xprofile_get_field_data( 'Full Name' ,bp_get_member_user_id());?>

			<div class="item-avatar">
					<?php if ( $message= xprofile_get_field_data( 'Your Message' ,bp_get_member_user_id() ) ) : ?>
					<span class="hint--bottom" data-hint="<?php echo $message ?>">
					<?php else: ?>
					<?php endif; ?>
					<a href="<?php echo $url ?>">
					<?php bp_member_avatar(); ?>
					</a>
					<?php if ( $message= xprofile_get_field_data( 'Your Message' ,bp_get_member_user_id() ) ) : ?>
					</span>
					<?php else: ?>
					<?php endif; ?>
					<br>
					<div class="name-field">
						<a href="<?php echo $url ?>">
						<?php echo $name ?>
						</a>
					</div>
			</div>

	<?php endwhile; ?>



	</div>

	<?php do_action( 'bp_after_directory_members_list' ); ?>

	<?php bp_member_hidden_fields(); ?>


<?php else: ?>

	<div id="message" class="info">
		<p><?php _e( "Sorry, no members were found.", 'buddypress' ); ?></p>
	</div>

<?php endif; ?>

<?php do_action( 'bp_after_members_loop' ); ?>
