<div class="post-container">

	<div id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
	
		<div class="featured-media">
		
			<a href="<?php the_permalink(); ?>" rel="bookmark" title="<?php the_title(); ?>">
	
			<?php				
				$attachment_parent = get_the_ID();
			
				if($images = get_posts(array(
					'post_parent'    => $attachment_parent,
					'post_type'      => 'attachment',
					'numberposts'    => 1,
					'post_status'    => null,
					'post_mime_type' => 'image',
			                'orderby'        => 'menu_order',
			                'order'           => 'ASC',
				))) { ?>
				
					<?php foreach($images as $image) { 
						$attimg = wp_get_attachment_image($image->ID, 'post-thumbnail'); ?>
						
						<?php echo $attimg; ?>
						
					<?php } ?>

				<?php } ?>
									
			</a>
							
		</div> <!-- /featured-media -->
		
		<?php if ( is_sticky() ) : ?>
				
			<div class="is-sticky">
				<div class="genericon genericon-pinned"></div>
			</div>
		
		<?php endif; ?>
		
		<div class="post-inner">
		
			<?php $title_var = get_the_title(); ?>
		
			<?php if ( !empty( $title_var ) ) : ?>
		
				<div class="post-header">
					
				    <h2 class="post-title"><a href="<?php the_permalink(); ?>" rel="bookmark" title="<?php the_title_attribute(); ?>"><?php the_title(); ?></a></h2>
				    	    
				</div> <!-- /post-header -->
			
			<?php endif; ?>
				    		            			            	                                                                                            
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
	
	</div>

</div>