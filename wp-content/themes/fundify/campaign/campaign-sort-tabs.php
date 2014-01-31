<?php
/**
 * Campaign tabs.
 *
 * @package Fundify
 * @since Fundify 1.5
 */

global $campaign;
?>

<div class="sort-tabs campaign">
	<ul>
		<?php do_action( 'fundify_campaign_tabs_before', $campaign ); ?>

		<li><a href="#description" class="campaign-view-descrption tabber"><?php _e( 'Overview', 'fundify' ); ?></a></li>
		
		<?php if ( '' != $campaign->updates() ) : ?>
		<li><a href="#updates" class="tabber"><?php _e( 'Updates', 'fundify' ); ?></a></li>
		<?php endif; ?>
		
		<li><a href="#comments" class="tabber"><?php _e( 'Comments', 'fundify' ); ?></a></li>
		<li><a href="#backers" class="tabber"><?php _e( 'Backers', 'fundify' ); ?></a></li>
		
		<?php if ( get_current_user_id() == $post->post_author || current_user_can( 'manage_options' ) ) : ?>
		<li><a href="<?php echo atcf_create_permalink( 'edit', get_permalink() ); ?>"><?php _e( 'Edit Campaign', 'fundify' ); ?></a></li>
		<?php endif; ?>

		<?php do_action( 'fundify_campaign_tabs_after', $campaign ); ?>
	</ul>
</div>