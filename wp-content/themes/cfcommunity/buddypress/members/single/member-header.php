<?php

/**
 * BuddyPress - Users Header
 *
 * @package BuddyPress
 * @subpackage bp-legacy
 */

?>

<?php 
$profile_edit_link = bp_loggedin_user_domain() . $bp->profile->slug . 'profile';
do_action( 'bp_before_member_header' ); 
?>


<div id="item-buttons">
	<?php do_action( 'bp_member_header_actions' ); ?>

</div><!-- #item-buttons -->

<div id="item-header-content">

	<div class="row">

		<div class="col-xs-12 col-sm-12 profile-data">

			<div class="profile-field-about-me">


				<?php if ( $relationship_cf = bp_get_profile_field_data( 'field=Your Relationship with CF' ) == 'I have a (grand)kid with CF' ) : ?>
				<div class="hide-field">
				<?php endif ?>

					<!-- CF Info -->
					<?php if ( $relationship_cf = bp_get_profile_field_data( 'field=Your Relationship with CF' ) ) : ?>
						<a href="<?php echo home_url(); ?>/members/?s=<?php echo $relationship_cf ?>">
						<?php echo $relationship_cf ?>
						</a>
					<?php endif ?>

				<?php if ( $relationship_cf = bp_get_profile_field_data( 'field=Your Relationship with CF' ) == 'I have a (grand)kid with CF' ) : ?>
				</div>
				<?php endif ?>



				<?php if ( $mutation = bp_get_profile_field_data( 'field=CF Mutation' ) ) : ?>
					(mutation: <a href="<?php echo home_url(); ?>/members/?s=<?php echo $mutation?>"><?php echo $mutation ?>)</a>
				<?php endif ?>

				<!-- Parents/Grandparents -->
				<?php if ( $relationship = bp_get_profile_field_data( 'field=Relationship?' ) ) : ?>
					<a href="<?php echo home_url(); ?>/members/?s=<?php echo $relationship?>"><?php echo $relationship ?>)</a>
					<?php echo $relationship ?>
					</a>
				<?php endif ?>

				<?php if ( $relation_status = bp_get_profile_field_data( 'field=Relationship Status' ) ) : ?>
						<a href="<?php echo home_url(); ?>/members/?s=<?php echo $relation_status?>"><?php echo $relation_status ?>)
						</a>
					
				<?php endif ?>

				<?php if ( $kids = bp_get_profile_field_data( 'field=Are you planning to have kids?' ) ) : ?>
						<a href="<?php echo home_url(); ?>/members/?s=<?php echo $kids?>">
						<?php echo $kids ?>)
						</a>
				<?php endif ?>

				<?php if ( $kids_amount = bp_get_profile_field_data( 'field=How many kids do you have?' ) ) : ?>
					with <a href="<?php echo home_url(); ?>/members/?s=<?php echo $kids_amount?>">
						<?php echo $kids_amount ?>)
						</a>
				<?php endif ?>

				<?php if ( $grandkids = bp_get_profile_field_data( 'field=How many grandkids do you have?' ) ) : ?>
					with <a href="<?php echo home_url(); ?>/members/?s=<?php echo $grandkids?>">
						<?php echo $grandkids ?>)
						</a>
				<?php endif ?>

				<?php if ( $age_range = bp_get_profile_field_data( 'field=Age Range' ) ) : ?>
					 ( <a href="<?php echo home_url(); ?>/members/?s=<?php echo $age_range?>">
						<?php echo $age_range ?>)
						</a>).
				<?php endif ?>

				<!-- Working with CF -->
				<?php if ( $work = bp_get_profile_field_data( 'field=Your job' ) ) : ?>
					as a 
					 <a href="<?php echo home_url(); ?>/members/?s=<?php echo $work ?>">
					 <?php echo $work ?>
					 </a>
				<?php endif ?>

				<?php if ( $job_title = bp_get_profile_field_data( 'field=Job Title' ) ) : ?>
					as a 
					 <a href="<?php echo home_url(); ?>/members/?s=<?php echo $job_title ?>">
					 <?php echo $job_title ?>
					 </a>
				<?php endif ?>

				<?php if ( $work_time = bp_get_profile_field_data( 'field=How long have you been working with people with CF?' ) ) : ?>
					 (for <a href="<?php echo home_url(); ?>/members/?s=<?php echo $work_time ?>">
					 <?php echo $work_time ?>
					 </a>)
				<?php endif ?>
				
				<?php if (bp_is_my_profile() ) :?>
					<a href="<?php echo $profile_edit_link ?>/edit/group/1/"> <i class="fa fa-pencil-square-o"></i></a>
				 <?php endif;?>
  
			</div>

			<div class="profile-field-details">


				<?php if ( $about = bp_get_profile_field_data( 'field=About Me' ) ) : ?>
					<?php echo $about ?>
				<?php endif ?>
              <?php if (bp_is_my_profile() ) :?>
                <a href="<?php echo $profile_edit_link ?>/edit/group/1/"> <i class="fa fa-pencil-square-o"></i></a>
            <?php endif;?>
			</div>

							<?php if ( $about = bp_get_profile_field_data( 'field=About your family' ) ) : ?>
					<h3>About my Family</h3>
					<?php echo $about ?>
				<?php endif ?>

				<?php if ( $about_my_work = bp_get_profile_field_data( 'field=About your work' ) ) : ?>
					<hr>
					<h3>About my work</h3>
					<?php echo $about_my_work ?>
				<?php endif ?>

		</div>

	</div>



	<?php if ( bp_is_active( 'activity' ) && bp_activity_do_mentions() ) : ?>
		<!-- <h2 class="user-nicename">@<?php bp_displayed_user_username(); ?></h2> -->
	<?php endif; ?>


	<?php do_action( 'bp_before_member_header_meta' ); ?>


	<div id="item-meta">


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