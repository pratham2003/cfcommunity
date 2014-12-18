<?php
/**
 * Sidebar containing the main widget area.
 */

if ( function_exists( 'bp_is_member' ) && ! bp_is_my_profile() ) :
?>
	<div class="widget bp-user-navigation-widget">
		<?php get_template_part( 'buddypress/parts/bp-member-nav' ); ?>
		<div style="clear:both;"></div>
    </div>
<?php endif; ?>

<?php do_action( 'open_sidebar' ); ?>

<?php  cfc_base_sidebars(); ?>

<?php do_action( 'close_sidebar' ); ?>

<?php do_action( 'after_sidebar' ); ?>