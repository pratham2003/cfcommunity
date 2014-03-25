<?php
/**
 * Group Header
 *
 * @package BuddyPress
 * @subpackage Templatepack
 */
?>

<div id="item-actions">
<?php do_action( 'bp_before_group_header' ); ?>

	<div id="item-header-content">
		<span class="highlight"><?php bp_group_type(); ?></span>
		<span class="activity-span"><?php printf( __( 'active %s', 'buddypress' ), bp_get_group_last_active() ); ?></span>

		<?php do_action( 'bp_before_group_header_meta' ); ?>

		<div id="item-meta">

			<?php bp_group_description(); ?>
			<?php do_action( 'bp_group_header_actions' ); ?>
			<?php do_action( 'bp_group_header_meta' ); ?>
		</div>
	</div>

	<div id="item-actions">

		<?php if ( bp_group_is_visible() ) : ?>

			<h3><?php _e( 'Group Admins', 'buddypress' ); ?></h3>

			<?php bp_group_list_admins();

			do_action( 'bp_after_group_menu_admins' );

			if ( bp_group_has_moderators() ) :
				do_action( 'bp_before_group_menu_mods' ); ?>

				<h3><?php _e( 'Group Mods' , 'buddypress' ); ?></h3>

				<?php bp_group_list_mods();

				do_action( 'bp_after_group_menu_mods' );

			endif;

		endif; ?>

	</div>

	<?php
	do_action( 'bp_after_group_header' );
	do_action( 'template_notices' );
	?>
</div>