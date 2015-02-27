<?php if ( is_user_logged_in() ) : ?>
	<div id="user-sidebar-menu" class="widget">
		<?php bp_loggedin_user_avatar( 'width=' . bp_core_avatar_thumb_width() . '&height=' . bp_core_avatar_thumb_height() ); ?>
			 <?php $userLink = bp_get_loggedin_user_link();?>
			 <?php echo bp_core_get_user_displayname( bp_loggedin_user_id() );?><br>
			<a class="no-ajax" href="<?php echo $userLink; ?>"><?php _e('View Profile.', 'cfctranslation'); ?>	</a>
	</div><!-- #item-header-avatar -->
<?php endif; ?>


<div id="vertical-activity-tabs" class="activity-type-tabs item-list-tabs widget vertical-list-tabs" role="navigation">

	<ul>
		<?php do_action( 'bp_before_activity_type_tab_all' ); ?>


		<li id="activity-swa-home">
		<a title="" href="<?php echo bp_loggedin_user_domain() . bp_get_activity_slug() . '/friends'; ?>" data-original-title="<?php _e( 'The activity of you and your friends.', 'cfctranslation' ); ?>"><?php _e('My Friends', 'cfctranslation'); ?></a>
		</li>

		<?php if ( is_user_logged_in() ) : ?>

			<?php do_action( 'bp_before_activity_type_tab_friends' ) ?>


			<?php do_action( 'bp_before_activity_type_tab_groups' ) ?>


			<?php do_action( 'bp_before_activity_type_tab_favorites' ); ?>

			<?php if ( bp_get_total_favorite_count_for_user( bp_loggedin_user_id() ) ) : ?>

				<li id="activity-favorites"><a href="<?php echo bp_loggedin_user_domain() . bp_get_activity_slug() . '/favorites/'; ?>" title="<?php _e( "The activity I've marked as a favorite.", 'buddypress' ); ?>"><?php printf( __( 'My Favorites <span>%s</span>', 'buddypress' ), bp_get_total_favorite_count_for_user( bp_loggedin_user_id() ) ); ?></a></li>

			<?php endif; ?>

			<?php do_action( 'bp_before_activity_type_tab_mentions' ); ?>

			<li id="activity-mentions"><a href="<?php echo bp_loggedin_user_domain() . bp_get_activity_slug() . '/mentions/'; ?>" title="<?php _e( 'Activity that I have been mentioned in.', 'buddypress' ); ?>"><?php _e( 'Mentions', 'buddypress' ); ?><?php if ( bp_get_total_mention_count_for_user( bp_loggedin_user_id() ) ) : ?> <strong><?php printf( __( '<span>%s new</span>', 'buddypress' ), bp_get_total_mention_count_for_user( bp_loggedin_user_id() ) ); ?></strong><?php endif; ?></a></li>

		<?php endif; ?>

		
			<li id="activity-all"><a href="<?php echo bp_loggedin_user_domain() . bp_get_activity_slug() . '/'; ?>" title="<?php _e( 'The public activity for everyone on this site.', 'buddypress' ); ?>"><?php printf( __( 'All Members <span>%s</span>', 'buddypress' ), bp_get_total_site_member_count() ); ?></a>
			</li>

	</ul>
</div><!-- .item-list-tabs -->

	<div id="user-sidebar-groups" class="widget">
		<i class="fa fa-life-ring"></i> <?php _e('Groups Newsfeed', 'cfctranslation'); ?>	<a href="http://cfcommunity.net/members/cfcommunity/groups/"><?php _e('Manage', 'cfctranslation'); ?></a>
	</div><!-- #item-header-avatar -->


<div id="vertical-activity-groups" class="activity-type-tabs item-list-tabs widget vertical-list-tabs" role="navigation">

<ul>
		<?php if ( bp_is_active( 'groups' ) ) : ?>

				<?php if ( bp_get_total_group_count_for_user( bp_loggedin_user_id() ) ) : ?>

					<li id="activity-groups"><a href="<?php echo bp_loggedin_user_domain() . bp_get_activity_slug() . '/' . bp_get_groups_slug() . '/'; ?>" title="<?php _e( 'The activity of groups I am a member of.', 'buddypress' ); ?>"><?php printf( __( 'All Your Groups <span>%s</span>', 'cfctranslation' ), bp_get_total_group_count_for_user( bp_loggedin_user_id() ) ); ?></a></li>

				<?php endif; ?>
		<?php endif; ?>

		<?php do_action( 'bp_activity_type_tabs' ); ?>

</ul>

</div>

