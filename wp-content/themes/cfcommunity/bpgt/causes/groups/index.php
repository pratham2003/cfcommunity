
<div class="intro-text">
<div id="expand-hidden"><a href="#"><i class="fa fa-times"></i> <?php _e( 'Hide this Message', 'cfctranslation' ); ?></a></div>
    <img class="avatar user-2-avatar avatar-80 photo" src="<?php echo get_stylesheet_directory_uri(); ?>/assets/img/cfchimp-large.png" />
    <p>
        <?php printf( __( "Welcome to the Causes directory %s! Through this page we try to make it as easy as possible for you to find and follow the causes that might be interesting for you. If you would like to stay receive updates from a cause simply click the 'Follow' button on a Cause page. 

", 'cfctranslation' ), bp_get_user_firstname() ); ?>
    </p>
</div>

<div id="buddypress">

	<div id="group-dir-search" class="dir-search" role="search">
		<form id="search-groups-form" method="get" action="">
		<label><input type="text" placeholder="<?php _e('Search Causes...', 'cfctranslation'); ?>	" id="groups_search" name="s" class="form-control"></label>
		<input type="submit" value="Search" name="groups_search_submit" id="groups_search_submit">
	</form>
	</div><!-- #group-dir-search -->

	<form action="" method="post" id="groups-directory-form" class="dir-form">

		<?php do_action( 'template_notices' ); ?>

		<div class="item-list-tabs" role="navigation">
			<ul>
				<li class="selected" id="groups-all"><a href="<?php bp_groups_directory_permalink(); ?>"><?php printf( __( 'All Causes <span>%s</span>', 'cfctranslation' ), bp_get_total_group_count() ); ?></a></li>

				<?php if ( is_user_logged_in() && bp_get_total_group_count_for_user( bp_loggedin_user_id() ) ) : ?>
					<li id="groups-personal"><a href="<?php echo bp_loggedin_user_domain() . bp_get_groups_slug() . '/my-groups/'; ?>"><?php printf( __( 'Causes you Follow <span>%s</span>', 'cfctranslation' ), bp_get_total_group_count_for_user( bp_loggedin_user_id() ) ); ?></a></li>
				<?php endif; ?>

			<li id="group-create-nav"><a href="http://cfcommunity.net/starting-page-cystic-fibrosis-cause/" title="Create a Cause" class="group-create no-ajax">Add My Cause</a></li>


			</ul>
		</div><!-- .item-list-tabs -->

		<div class="item-list-tabs" id="subnav" role="navigation">
			<ul>
				<?php do_action( 'bp_groups_directory_group_types' ); ?>


				<li id="groups-order-select" class="last filter">

					<label for="groups-order-by"><?php _e( 'Order By:', 'buddypress' ); ?></label>

					<select id="groups-order-by">
						<option value="active"><?php _e( 'Last Active', 'cfctranslation' ); ?></option>
						<option value="popular"><?php _e( 'Most Followers', 'cfctranslation' ); ?></option>
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