
    <div id="group-welcome-message" class="intro-text">
    <div id="expand-hidden"><a href="#"><i class="fa fa-times"></i> <?php _e( 'Hide this Message', 'cfcommunity' ); ?></a></div>
        <img class="avatar user-2-avatar avatar-80 photo" src="<?php echo get_stylesheet_directory_uri(); ?>/assets/img/cfchimp-large.png" />
        <p>
            <?php printf( __( "Hey %s, below you will find an overview of all the Discussion Groups on CFCommunity. Feel free to join the ones you find interesting! You can  search and filter groups by name, spoken language and interests. Interested in starting your own discussion group? Press the 'Create a Group' button! Have fun!", 'cfcommunity' ), bp_get_user_firstname() ); ?>
        </p>
    </div>

<div id="buddypress">






	<div id="group-dir-search" class="dir-search" role="search">
		<?php bp_directory_groups_search_form(); ?>
	</div><!-- #group-dir-search -->

	<form action="" method="post" id="groups-directory-form" class="dir-form">

		<?php do_action( 'template_notices' ); ?>

		<div class="item-list-tabs" role="navigation">
			<ul>
				<li class="selected" id="groups-all"><a href="<?php bp_groups_directory_permalink(); ?>"><?php printf( __( 'All Groups <span>%s</span>', 'buddypress' ), bp_get_total_group_count() ); ?></a></li>

				<?php if ( is_user_logged_in() && bp_get_total_group_count_for_user( bp_loggedin_user_id() ) ) : ?>
					<li id="groups-personal"><a href="<?php echo bp_loggedin_user_domain() . bp_get_groups_slug() . '/my-groups/'; ?>"><?php printf( __( 'My Groups <span>%s</span>', 'buddypress' ), bp_get_total_group_count_for_user( bp_loggedin_user_id() ) ); ?></a></li>
				<?php endif; ?>

				<?php do_action( 'bp_groups_directory_group_filter' ); ?>

			</ul>
		</div><!-- .item-list-tabs -->

		<div class="item-list-tabs" id="subnav" role="navigation">
			<ul>
				<?php do_action( 'bp_groups_directory_group_types' ); ?>

				<li id="groups-order-select" class="last filter">

					<label for="groups-order-by"><?php _e( 'Order By:', 'buddypress' ); ?></label>

					<select id="groups-order-by">
						<option value="active"><?php _e( 'Last Active', 'buddypress' ); ?></option>
						<option value="popular"><?php _e( 'Most Members', 'buddypress' ); ?></option>
						<option value="newest"><?php _e( 'Newly Created', 'buddypress' ); ?></option>
						<option value="alphabetical"><?php _e( 'Alphabetical', 'buddypress' ); ?></option>

						<?php do_action( 'bp_groups_directory_order_options' ); ?>
					</select>
				</li>
			</ul>
		</div>

		<div id="groups-dir-list" class="groups dir-list">
			<?php bp_get_template_part( 'groups/groups-loop' ); ?>
		</div><!-- #groups-dir-list -->

		<?php do_action( 'bp_directory_groups_content' ); ?>

		<?php wp_nonce_field( 'directory_groups', '_wpnonce-groups-filter' ); ?>

		<?php do_action( 'bp_after_directory_groups_content' ); ?>

	</form><!-- #groups-directory-form -->

	<?php do_action( 'bp_after_directory_groups' ); ?>

</div><!-- #buddypress -->

<?php do_action( 'bp_after_directory_groups_page' ); ?>