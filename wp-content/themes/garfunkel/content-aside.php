<div class="post-container">			    	

	<div id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
	
		<?php if ( is_sticky() ) : ?>
				
			<div class="is-sticky">
				<div class="genericon genericon-pinned"></div>
			</div>
		
		<?php endif; ?>
	
		<div class="post-inner">
		
			<?php the_excerpt(); ?>
		
			<div class="post-meta">
			
				<a class="post-meta-date" href="<?php the_permalink(); ?>" title="<?php the_title_attribute(); ?>">
					<div class="genericon genericon-time"></div>
					<?php the_time( get_option('date_format') ); ?>
				</a>
				
				<?php if ( comments_open() ) : ?>
					<a class="post-meta-comments" href="<?php the_permalink(); ?>#comments" title="<?php comments_number( '0', '1', '%'); ?> <?php _e('comments to','garfunkel'); ?> <?php the_title_attribute(); ?>">
						<div class="genericon genericon-comment"></div>
						<?php comments_number( '0', '1', '%'); ?>
					</a>
				<?php endif; ?>
			
				<div class="clear"></div>
			
			</div> <!-- /post-meta -->
		
		</div> <!-- /post-inner -->
	
	</div> <!-- /post -->

</div>