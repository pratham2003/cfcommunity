<?php if (is_single()) : ?>
	<?php if(has_post_thumbnail()) :?>
		<div id="featured-image"><?php the_post_thumbnail('featured'); ?></div>		
	<?php endif; ?>	
	<div class="col-md-8 col-md-offset-2">			
		<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
			<p class="post-date"><?php the_date(); ?> by <?php the_author(); ?> <?php if (is_sticky()) : ?> - Featured<?php endif; ?></p>	
			<?php the_title('<h1 id="post-title">', '</h1>'); ?>						
			<?php the_content(); ?>	
			<?php $post_tags = get_the_tags(); ?>
			<?php if ($post_tags) : ?>
				<p id="post-tags">
			    	<?php foreach($post_tags as $tag) { echo '<a href="' . get_tag_link( $tag->term_id ) . '">#' . $tag->name.'</a>';} ?>
			  	</p>					  	
			<?php endif; ?>	
			<?php wp_link_pages('before=<div id="page-links">&after=</div>'); ?>		
		</article>		
		<?php comments_template(); ?>	
	</div>	
<?php else : ?>	
	<?php if (is_sticky()) : ?> 
	<div class="col-md-10 col-md-offset-1">	
	<?php else : ?>
	<div class="col-md-8 col-md-offset-2">	
	<?php endif; ?>
		<?php if ('yes' === get_theme_mod('crawford_feat_img_setting')): ?>
			<div class="featured-image"><?php the_post_thumbnail('featured'); ?></div>
		<?php endif; ?>	
		<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
			<?php $title = get_the_title(); ?>
			<?php if ($title != '') : ?>
				<p class="post-date"><?php the_time(get_option('date_format')); ?><?php if (is_sticky()) : ?> - Featured<?php endif; ?></p> 
				<?php the_title('<h3 class="post-title"><a href="' . esc_url( get_permalink()) . '" rel="bookmark">', '</a></h3>'); ?>				
			<?php else : ?>
				<p class="post-date"><a href="<?php echo esc_url( get_permalink()); ?>"><?php the_time(get_option('date_format')); ?><?php if (is_sticky()) : ?> - Featured<?php endif; ?></a></p>
			<?php endif; ?>			
			<?php the_excerpt(); ?>	       		
			<div class="divider">&bull; &bull; &bull;</div>
		</article>	
	</div>
<?php endif; ?>	