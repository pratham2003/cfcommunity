<?php
/**
 * Member plugins
 *
 * @package BuddyPress
 * @subpackage Templatepack
 */
?>

		<?php do_action( 'bp_before_member_plugin_template' ); ?>
		<?php if ( ! bp_is_current_component_core() ) : ?>

		<nav id="subnav" class="item-list-tabs no-ajax">
			<ul>
				<?php bp_get_options_nav(); ?>

				<?php do_action( 'bp_member_plugin_options_nav' ); ?>
			</ul>
		</nav><!-- .item-list-tabs -->

		<?php endif; ?>

		<h3><?php do_action( 'bp_template_title' ); ?></h3>

		<?php do_action( 'bp_template_content' ); ?>

		<?php do_action( 'bp_after_member_plugin_template' ); ?>
