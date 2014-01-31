<div class="content row row-offcanvas row-offcanvas-left">
  <?php while (have_posts()) : the_post(); ?>

    <?php // Load Post Thumbnail
      get_template_part( 'templates/post-thumbnail' );
    ?>   
    
    <div class="main col-sm-9 col-xs-15" role="main">
      <article <?php post_class(); ?>>
          <?php get_template_part('templates/entry-meta'); ?>
        <div class="entry-content">
          <?php the_content(); ?>
        </div>

        <?php get_template_part('templates/news-letter'); ?>

        <?php if ( function_exists( 'sharing_display' ) ) {
          echo sharing_display();
        }?> 

        <?php get_template_part('templates/related-posts'); ?>

        <footer>
          <?php wp_link_pages(array('before' => '<nav class="page-nav"><p>' . __('Pages:', 'roots'), 'after' => '</p></nav>')); ?>
        </footer>
        <?php comments_template('/templates/comments.php'); ?>
      </article>
    </div><!-- /.main --> 

  <?php endwhile; ?>
  <?php get_template_part( '/templates/sidebar' );?>    
</div><!-- /.content -->


