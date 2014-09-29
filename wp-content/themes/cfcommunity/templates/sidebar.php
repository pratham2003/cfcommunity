
  <?php
    if ( function_exists( 'bp_is_member' ) && !bp_is_my_profile() ) {?>
      <div class="widget bp-user-navigation-widget">
		<?php
			get_template_part( 'buddypress/parts/bp-member-nav' );
		?>
		<div style="clear:both;"></div>
       </div>

    <?}
  ?>



		<?php
			do_action( 'open_sidebar' );?>

		<?php
			// Load Sidebars
			cfc_base_sidebars();
			do_action( 'close_sidebar' );
		?>

	<?php
		do_action( 'after_sidebar' );
	?>