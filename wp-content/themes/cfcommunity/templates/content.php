<article <?php post_class(); ?>>


<a href="<?php the_permalink(); ?>">
	<?php
		get_template_part( 'templates/post-thumbnail' );
	?>    
</a>

  <header>
    <h2 class="entry-title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
    <?php get_template_part('templates/entry-meta'); ?>
  </header>


<div class="entry-summary intro-paragraph">
	<?php the_excerpt(); ?>
</div>

</article>
