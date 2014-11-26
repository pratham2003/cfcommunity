<div id="profile-sidebar" class="widget">
    <div id="item-header-avatar">
        <?php
        $userLink = bp_get_loggedin_user_link();
        $profile_edit_link = bp_loggedin_user_domain() . $bp->profile->slug . 'profile';
        if ( bp_is_my_profile() ): ?>
        <a href="<?php echo $userLink ?>profile/change-avatar">
          <i class="fa fa-camera"></i>
      </a>
    <?php endif; ?>

    <a href="<?php bp_user_link(); ?>"><?php bp_displayed_user_avatar( 'type=full' ); ?></a>

    <?php bp_add_friend_button() ?>

    </div><!-- #item-header-avatar -->

    <!-- Profile Data -->
    <div id="profile-data">
        <ul>
            <?php if ( $city = bp_get_profile_field_data( 'field=City' ) ) : ?>
                <li>
                    <a href="<?php echo home_url(); ?>/members/?s=<?php echo $city ?>">
                     <i class="fa fa-home"></i> <?php echo $city ?>
                    </a>

                <?php if ( $state = bp_get_profile_field_data( 'field=State (US Only)' ) ) : ?>
                    , <a href="<?php echo home_url(); ?>/members/?s=<?php echo $state ?>">
                    <?php echo $state ?>
                    </a>
                <?php endif ?>

                </li>
            <?php endif ?>

            <?php if ( $country = bp_get_profile_field_data( 'field=Country' ) ) : ?>
                <li>
                    <a href="<?php echo home_url(); ?>/members/?s=<?php echo $country ?>">
                         <i class="fa fa-globe"></i> <?php echo $country ?>
                    </a>
                </li>
            <?php endif ?>

            <?php if ( $hospital = bp_get_profile_field_data( 'field=Hospital' ) ) : ?>
                <li>
                    <a href="<?php echo home_url(); ?>/members/?s=<?php echo $hospital ?>">
                         <i class="fa fa-hospital-o"></i> <?php echo $hospital ?>
                    </a>
                </li>
            <?php endif ?>

            <?php if ( $work_study = bp_get_profile_field_data( 'field=Work or Study' ) ) : ?>
                <li>
                    <a href="<?php echo home_url(); ?>/members/?s=<?php echo $work_study ?>">
                         <i class="fa fa-suitcase"></i> <?php echo $work_study ?>
                    </a>
                </li>
            <?php endif ?>

        </ul>

    </div>
</div>

<!-- Profile Tabs -->
<div class="sidebar-activity-tabs no-ajax item-list-tabs vertical-list-tabs widget" role="navigation">
   <ul class="sidebar-nav">
      <?php bp_get_displayed_user_nav(); ?>
  </ul>
</div>
