<?php
/**
 * Member friend requests
 *
 * @package BuddyPress
 * @subpackage Templatepack
 */
?>
<?php do_action( 'bp_before_member_friend_requests_content' ); ?>

<?php if ( bp_has_members( 'type=alphabetical&include=' . bp_get_friendship_requests() ) ) : ?>


	<div id="pagination-top" class="pagination">
		<div class="pagination-count">
			<?php bp_members_pagination_count(); ?>
		</div>

		<div class="pagination-links">
			<?php bp_members_pagination_links(); ?>
		</div>
	</div>

	<ul id="friend-list" class="item-list">

		<?php while ( bp_members() ) : bp_the_member(); ?>

			<li id="friendship-<?php bp_friend_friendship_id(); ?>">
				<div class="friend-avatar">
					<a href="<?php bp_member_link(); ?>"><?php bp_member_avatar(); ?></a>
				</div>

				<div class="friend">
					<div class="friend-title"><a href="<?php bp_member_link(); ?>"><?php bp_member_name(); ?></a></div>
					<div class="friend-meta"><span class="activity"><?php bp_member_last_active(); ?></span></div>
				</div>

				<?php do_action( 'bp_friend_requests_item' ); ?>

				<div class="friend-action">
					<a class="button accept" href="<?php bp_friend_accept_request_link(); ?>"><?php _e( 'Accept', 'buddypress' ); ?></a> &nbsp;
					<a class="button reject" href="<?php bp_friend_reject_request_link(); ?>"><?php _e( 'Reject', 'buddypress' ); ?></a>

					<?php do_action( 'bp_friend_requests_item_action' ); ?>
				</div>
			</li>

		<?php endwhile; ?>

	</ul>

	<?php do_action( 'bp_friend_requests_content' ); ?>

	<div id="pagination-bottom" class="pagination">
		<div class="pagination-count">
			<?php bp_members_pagination_count(); ?>
		</div>

		<div class="pagination-links">
			<?php bp_members_pagination_links(); ?>
		</div>
	</div>

<?php else: ?>

	<div id="message" class="info">
		<p><?php _e( 'You have no pending friendship requests.', 'buddypress' ); ?></p>
	</div>

<?php endif;?>

<?php do_action( 'bp_after_member_friend_requests_content' ); ?>

