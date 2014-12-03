<?php
/**
 * This is the 404 template that displays when a page
 * cannot be found.
 *
 * @package Alto
 */

get_header(); ?>

  <div id="primary" class="content-area is-page">

    <main id="main" class="site-main" role="main">

      <article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>

        <header class="entry-header">
          <h1 class="entry-title"><?php _e( 'Page not found.', 'alto' ); ?></h1>
        </header>

        <div class="entry-content">
          <p><?php _e( "It seems we can't find what you're looking for. The page have been moved or removed. Please try using the search form below:", 'alto' ); ?></p>
          <?php get_search_form(); ?>
        </div> <!-- end .entry-content -->

      </article> <!-- end #post-## -->

    </main> <!-- end #main -->

  </div> <!-- end #primary -->

<?php get_footer(); ?>