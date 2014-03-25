<?php
/**
 * Group Panel
 *
 * @package BuddyPress
 * @subpackage Templatepack
 */
?>
<div id="group-panel">
	<div id="item-header-avatar">
	<a href="<?php bp_group_permalink(); ?>" title="<?php bp_group_name(); ?>">
		<?php bp_group_avatar(); ?>
	</a>
	</div><!-- #item-header-avatar -->

	<nav id="group-nav" class="nav-list no-ajax" role="navigation">
		<ul>
			<?php bp_get_options_nav(); ?>
			<?php do_action( 'bp_group_options_nav' ); ?>
		</ul>
	</nav>

	<?php bp_get_template_part( 'members/single/member-header' ) ?>

</div><!-- end #member-panel -->