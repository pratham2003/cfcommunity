<?php
/**
 * This is the attachment template that displays a single
 * attachment from a given post (e.g. an image or video).
 *
 * @package Alto
 */

get_header(); ?>

  <div id="primary" class="content-area is-single">

    <main id="main" class="site-main" role="main">

    <?php while ( have_posts() ) : the_post(); ?>

      <?php get_template_part( 'single', 'attachment' ); ?>

      <?php // If comments are open or we have at least one comment, load up the comment template. ?>

      <?php 

        if ( comments_open() || '0' != get_comments_number() ) :
  
          comments_template();

        else: 

      ?>

        <div id="comments" class="comments-area">

          <header class="block-header">
            <h3><?php _e( 'Discussion', 'alto' ); ?></h3>
          </header>
        
          <p class="comments-disabled-text"><?php _e( 'Comments disabled.', 'alto' ); ?></p>
        
        </div> <!-- end #comments -->
      
      <?php endif; ?>

    <?php endwhile; ?>

    </main> <!-- end #main -->

    <?php alto_post_nav(); ?>

  </div> <!-- end #primary -->

<?php get_footer(); ?>