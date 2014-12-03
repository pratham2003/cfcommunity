<?php if(has_post_thumbnail()) :?>
	<div id="featured-image"><?php the_post_thumbnail('featured'); ?></div>		
<?php endif; ?>	
<div class="col-md-8 col-md-offset-2">			
	<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
		<?php the_title('<h1 id="post-title">', '</h1>'); ?>						
		<?php the_content(); ?>				
	</article>	
	<?php comments_template(); ?>	
</div>	