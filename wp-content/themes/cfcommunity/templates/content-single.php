<?php while (have_posts()) : the_post(); ?>
  <article <?php post_class(); ?>>
<div class="negative-row no-padding">
<?php if ( cf_is_high_res() ): ?>
    <?php the_post_thumbnail( array( 'width' => 1760, 'height' => 540, 'crop' => true ) ) ?>
<?php else: ?>
    <?php the_post_thumbnail( array( 'width' => 880, 'height' => 270, 'crop' => true ) ) ?>
<?php endif;?>
</div>
    <header>
      <h1 class="entry-title"><?php the_title(); ?></h1>
      <?php get_template_part('templates/entry-meta'); ?>
    </header>
    <div class="entry-content">
      <?php the_content(); ?>
    </div>
    <footer>
      <?php wp_link_pages(array('before' => '<nav class="page-nav"><p>' . __('Pages:', 'roots'), 'after' => '</p></nav>')); ?>
    </footer>
    <?php comments_template('/templates/comments.php'); ?>
  </article>
<?php endwhile; ?>
