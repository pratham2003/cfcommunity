<?php
/**
 * This is the archive template that displays on archive
 * pages (e.g. month or year archive).
 *
 * Learn more: http://codex.wordpress.org/Template_Hierarchy
 *
 * @package Alto
 */

get_header(); ?>

  <section id="primary" class="content-area is-archive">

    <main id="main" class="site-main" role="main">

    <?php if ( have_posts() ) : ?>

      <header class="page-header">

        <h1 class="page-title">
          <?php
            if ( is_category() ) :
              $category = single_cat_title( 'All posts in: ', true);
              printf( __( '%s', 'alto' ), $category );

            elseif ( is_tag() ) :
              $tag = single_tag_title( 'Posts filed under ', true);
              printf( __( '%s', 'alto' ), $tag );

            elseif ( is_author() ) :
              printf( __( 'Author: %s', 'alto' ), '<span class="vcard">' . get_the_author() . '</span>' );

            elseif ( is_day() ) :
              printf( __( 'Day: %s', 'alto' ), '<span>' . get_the_date() . '</span>' );

            elseif ( is_month() ) :
              printf( __( 'Month: %s', 'alto' ), '<span>' . get_the_date( _x( 'F Y', 'monthly archives date format', 'alto' ) ) . '</span>' );

            elseif ( is_year() ) :
              printf( __( 'Year: %s', 'alto' ), '<span>' . get_the_date( _x( 'Y', 'yearly archives date format', 'alto' ) ) . '</span>' );

            elseif ( is_tax( 'post_format', 'post-format-aside' ) ) :
              _e( 'Asides', 'alto' );

            elseif ( is_tax( 'post_format', 'post-format-gallery' ) ) :
              _e( 'Galleries', 'alto');

            elseif ( is_tax( 'post_format', 'post-format-image' ) ) :
              _e( 'Images', 'alto');

            elseif ( is_tax( 'post_format', 'post-format-video' ) ) :
              _e( 'Videos', 'alto' );

            elseif ( is_tax( 'post_format', 'post-format-quote' ) ) :
              _e( 'Quotes', 'alto' );

            elseif ( is_tax( 'post_format', 'post-format-link' ) ) :
              _e( 'Links', 'alto' );

            elseif ( is_tax( 'post_format', 'post-format-status' ) ) :
              _e( 'Statuses', 'alto' );

            elseif ( is_tax( 'post_format', 'post-format-audio' ) ) :
              _e( 'Audios', 'alto' );

            elseif ( is_tax( 'post_format', 'post-format-chat' ) ) :
              _e( 'Chats', 'alto' );

            else :
              _e( 'Archives', 'alto' );

            endif;
          ?>
        </h1>

      </header><!-- end .page-header -->

      <?php // Start the Loop ?>

      <div class="recent-posts">

        <?php while ( have_posts() ) : the_post(); ?>

          <?php
            /* Include the Post-Format-specific template for the content.
             * If you want to override this in a child theme, then include a file
             * called content-___.php (where ___ is the Post Format name) and that will be used instead.
             */
            get_template_part( 'content-recent-posts', get_post_format() );
          ?>

        <?php endwhile; ?>

        <?php alto_paging_nav(); ?>

      </div> <!-- end .recent-posts -->

      <?php else : ?>

        <?php get_template_part( 'content', 'none' ); ?>

      <?php endif; ?>

    </main> <!-- end #main -->

  </section><!-- end #primary -->

<?php get_sidebar(); ?>
<?php get_footer(); ?>