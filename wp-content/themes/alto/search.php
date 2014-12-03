<?php
/**
 * The template for displaying Search Results pages.
 *
 * @package Alto
 */

get_header(); ?>

  <section id="primary" class="content-area is-search">

    <main id="main" class="site-main" role="main">

    <?php if ( have_posts() ) : ?>

      <header class="page-header">
        <h1 class="page-title"><?php printf( __( 'Search Results for: %s', 'alto' ), '<span>' . get_search_query() . '</span>' ); ?></h1>
      </header><!-- .page-header -->

      <div class="recent-posts">

        <?php // Start the loop. ?>

        <?php while ( have_posts() ) : the_post(); ?>

          <?php get_template_part( 'content-recent-posts', get_post_format() ); ?>

        <?php endwhile; ?>

        <?php alto_paging_nav(); ?>
      
      </div> <!-- end .recent-posts -->

      <?php else : ?>

        <?php get_template_part( 'content', 'none' ); ?>

      <?php endif; ?>

    </main> <!-- end #main -->
    
  </section> <!-- end #primary -->

<?php get_sidebar(); ?>
<?php get_footer(); ?>