<?php
/**
 * The Template for displaying all single posts.
 *
 * @package Alto
 */

get_header(); ?>

  <div id="primary" class="content-area is-single">

    <main id="main" class="site-main" role="main">

    <?php while ( have_posts() ) : the_post(); ?>

      <?php 

        $single_style = get_theme_mod('alto_select_alt_post_type');

        if ( 'default' == $single_style ) {

          get_template_part( 'content', 'single' );

        } else if ( 'alt-1' == $single_style ) {

          get_template_part( 'content', 'alt-single-1' );

        } else if ( 'alt-2' == $single_style ) {

          get_template_part( 'content', 'alt-single-2' );

        } else { 
        
          get_template_part( 'content', 'single' );
      
        } 

      ?>

      <?php // If comments are open or we have at least one comment, load up the comment template. ?>

      <?php if ( comments_open() || '0' != get_comments_number() ) :
          
          comments_template();

        else: ?>
          <?php if ( 'alt-1' == $single_style || 'alt-2' == $single_style ) { ?><div class="alt-layout-comments-wrap"><?php } ?>
          
          <div id="comments" class="comments-area">
            <header class="block-header">
              <h3><?php _e( 'Discussion', 'alto' ); ?></h3>
            </header>
            <p class="comments-disabled-text"><?php _e( 'Comments disabled.', 'alto' ); ?></p>
          </div> <!-- end #comments -->
          
          <?php if ( 'alt-1' == $single_style || 'alt-2' == $single_style ) { ?></div><?php } ?>
      
      <?php endif; ?>

    <?php endwhile; ?>

    </main> <!-- end #main -->

    <?php 
      $attachment = is_attachment();
    ?>
    
    <?php if ( !$attachment && ( 'alt-1' == $single_style || 'alt-2' == $single_style ) ) { ?><div class="alt-layout-post-nav-wrap"><?php } ?>
      <?php alto_post_nav(); ?>
    <?php if ( !$attachment && ( 'alt-1' == $single_style || 'alt-2' == $single_style ) ) { ?></div><?php } ?>

  </div> <!-- end #primary -->

<?php get_footer(); ?>