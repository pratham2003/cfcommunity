<?php do_action( 'bp_before_directory_members_page' ); ?>

<div id="buddypress">



<div class="call-out">
		<h2 class="entry-title">The Hall of Fame</h1>

		Below are the pretty faces of those who've helped us with CFCommunity. We are extremely grateful for their support, contributions and helping us make CFCommunity a reality. Thank you! 

	<br><br>
		Want to join them? Make ANY donation to our <a href="http://igg.me/at/cfcommunity/x/765375">IndieGogo campaign</a> and help create a WordPress powered social network for those affected by Cystic Fibrosis! There's some really cool WordPress perks available from our sponsors (check out the "Perks" menu in the sidebar!)

					<?php if ( is_user_logged_in() ) : ?> 
		<span>
	Look at you! You're on the CFCommunity Hall of Fame! If you made a mistake filling in your website URL or message, just <a title="View your Public Profile" href="<?php echo bp_get_loggedin_user_link();?>profile/edit/group/1/">click here</a> to edit your profile.Thank you once again for your contribution! 
		</span>


		<?php else: ?>	

		<span>Did you contribute to our campaign? <a href="http://wordpress.cfcommunity.net/become-hero">Add yourself to the Hall of Fame!</a> </span>

	<?php endif;?>

</div>

		<div id="members-dir-list" class="members dir-list">
			<?php bp_get_template_part( 'members/members-loop' ); ?>
		</div><!-- #members-dir-list -->

		<?php do_action( 'bp_directory_members_content' ); ?>

		<?php wp_nonce_field( 'directory_members', '_wpnonce-member-filter' ); ?>

		<?php do_action( 'bp_after_directory_members_content' ); ?>

	<?php do_action( 'bp_after_directory_members' ); ?>

</div><!-- #buddypress -->
