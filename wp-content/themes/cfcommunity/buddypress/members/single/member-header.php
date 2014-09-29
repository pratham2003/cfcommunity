<?php

/**
 * BuddyPress - Users Header
 *
 * @package BuddyPress
 * @subpackage bp-legacy
 */

?>

<?php do_action( 'bp_before_member_header' ); ?>


<div id="item-buttons">
	<?php do_action( 'bp_member_header_actions' ); ?>

</div><!-- #item-buttons -->

<div id="item-header-content">

	<div class="row">


		<div class="col-xs-12 col-sm-12 profile-data">

			<div class="profile-field-about-me">

				<?php if ( $relationship_cf = bp_get_profile_field_data( 'field=Your Relationship with CF' ) ) : ?>
					<?php echo $relationship_cf ?>

				<?php if ( $mutation = bp_get_profile_field_data( 'field=CF Mutation' ) ) : ?>
					(mutation: <?php echo $mutation ?>)
				<?php endif ?>


				<?php endif ?>

				<?php if ( $city = bp_get_profile_field_data( 'field=City' ) ) : ?>
					and I live in <?php echo $city ?>,
				<?php endif ?>

				<?php if ( $state = bp_get_profile_field_data( 'field=State (US Only)' ) ) : ?>
					 <?php echo $state ?> in the
				<?php endif ?>

				<?php if ( $country = bp_get_profile_field_data( 'field=Country' ) ) : ?>
					 <?php echo $country ?>
				<?php endif ?>

			</div>

		</div>

		<div class="col-xs-6 col-sm-6 profile-data">

			<?php if ( $country = bp_get_profile_field_data( 'field=Language ' ) ) : ?>
			<div class="profile-field">
				<i class="fa fa-globe"></i><?php echo $country ?>
			</div>
			<?php endif ?>

		</div>
	</div>



	<?php if ( bp_is_active( 'activity' ) && bp_activity_do_mentions() ) : ?>
		<!-- <h2 class="user-nicename">@<?php bp_displayed_user_username(); ?></h2> -->
	<?php endif; ?>

	<span class="activity"><?php bp_last_activity( bp_displayed_user_id() ); ?></span>

	<?php do_action( 'bp_before_member_header_meta' ); ?>


	<div id="item-meta">

		<?php if ( bp_is_active( 'activity' ) ) : ?>

			<div id="latest-update">

				<?php bp_activity_latest_update( bp_displayed_user_id() ); ?>

			</div>

		<?php endif; ?>

		<?php
		/***
		 * If you'd like to show specific profile fields here use:
		 * bp_member_profile_data( 'field=About Me' ); -- Pass the name of the field
		 */
		 do_action( 'bp_profile_header_meta' );

		 ?>

	</div><!-- #item-meta -->

</div><!-- #item-header-content -->

<?php do_action( 'bp_after_member_header' ); ?>

<?php do_action( 'template_notices' ); ?>