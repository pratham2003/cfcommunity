<?php $quote_content = get_post_meta($post->ID, 'quote_content', true); ?>
<?php $quote_attribution = get_post_meta($post->ID, 'quote_attribution', true); ?>

<div class="post-container">
	
	<div id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
	
		<?php if ( !empty($quote_content) && !empty($quote_attribution) ) : ?>
	
			<div class="post-quote">
			
				<?php if ( !empty($quote_content) ) : ?>
			
					<blockquote><?php echo $quote_content; ?></blockquote>
				
				<?php endif; ?>
				
				<?php if ( !empty($quote_attribution) ) : ?>
				
					<cite><?php echo $quote_attribution; ?></cite>
				
				<?php endif; ?>
			
			</div> <!-- /post-quote -->
		
		<?php endif; ?>
		
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