<?php
/**
 * The template for displaying a "No posts found" message.
 */
?>      
                
<article id="post-0" class="post no-results not-found">
	<h1 class="entry-title"><?php _e( 'Nothing Found', 'ht-theme' ); ?></h1>

	<div class="entry-content">
		<p><?php _e('We apologize for any inconvenience, please ', 'ht-theme'); ?><a href="<?php echo home_url(); ?>/" title="<?php bloginfo('description'); ?>"><?php _e('return to the home page', 'ht-theme'); ?></a><?php _e(' or use the search form below.', 'ht-theme'); ?></p>
		<?php get_search_form(); ?>
	</div>
</article>