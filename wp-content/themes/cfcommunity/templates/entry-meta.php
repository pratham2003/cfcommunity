<div class="post-meta">
	<span>
		<time class="published" datetime="<?php echo get_the_time('c'); ?>"><i class="fa fa-clock-o"></i> <?php echo get_the_date(); ?></time>
	</span>
	<span class="byline author vcard">
		<a href="<?php echo get_author_posts_url(get_the_author_meta('ID')); ?>" rel="author" class="fn"><i class="fa fa-user"></i> <?php echo get_the_author(); ?></a>
	</span>

	<?php if ( ! is_category() ): ?> 
	<span>
		<i class="fa fa-comment"></i>
		<?php
			comments_popup_link(
				__( 'No Comments &#187;', 'roots' ),
				__( '1 Comment &#187;', 'roots' ),
				__( '% Comments &#187;', 'roots' )
			);
		?>
	</span>
 	<?php endif; ?>	

</div>
