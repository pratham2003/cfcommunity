
<div id="profile-sidebar" class="widget">
	<div id="item-header-avatar">

        <?php
        $userLink = bp_get_loggedin_user_link();
        if ( bp_is_my_profile() ): ?>
            <a href="<?php echo $userLink ?>profile/change-avatar">
              <i class="fa fa-camera"></i>
            </a>
        <?php endif; ?>
		<a href="<?php bp_user_link(); ?>"><?php bp_displayed_user_avatar( 'type=full' ); ?></a>
	</div><!-- #item-header-avatar -->


</div>
<!-- Profile Tabs -->
<div class="sidebar-activity-tabs no-ajax item-list-tabs vertical-list-tabs widget" role="navigation">
	<ul class="sidebar-nav">
		<?php bp_get_displayed_user_nav(); ?>
	</ul>
</div>
