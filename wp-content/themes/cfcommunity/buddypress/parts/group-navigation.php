<div id="group-navigation" class="widget"> 
        <div id="item-header-avatar">
          <?php bp_group_avatar() ?>
        </div>
    <div id="item-buttons">

			<?php do_action( 'bp_group_header_actions' ); ?>

		</div><!-- #item-buttons -->
</div>     
<div class="sidebar-activity-tabs no-ajax item-list-tabs vertical-list-tabs widget" role="navigation">
	<ul class="sidebar-nav">
		<?php bp_get_options_nav(); ?>
		<?php do_action( 'bp_group_options_nav' ); ?>
	</ul>
</div>
