<?php get_header(); ?>
<div class="container">
	<div class="row" role="main">			
		<?php
			if (have_posts()) :				
				while (have_posts()) : the_post();					
					get_template_part('content', get_post_format());
				endwhile;
			else :
				get_template_part('content', 'none');
			endif;
		?>		
		<?php crawford_pagination(); ?>		
	</div>
</div>
<?php get_footer(); ?>				