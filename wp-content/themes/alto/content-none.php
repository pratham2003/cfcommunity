<?php
/**
 * This is the no content found template that when no content can
 * be found for a given post or search query.
 *
 * Learn more: http://codex.wordpress.org/Template_Hierarchy
 *
 * @package Alto
 */
?>

<section class="no-results not-found">

  <header class="page-header">
    <h1 class="page-title"><?php printf( __( 'Nothing found for "%1$s."', 'alto' ), get_search_query() ); ?></h1>
  </header> <!-- end .page-header -->

  <div class="entry-body">

    <div class="page-content">

      <?php if ( is_home() && current_user_can( 'publish_posts' ) ) : ?>

        <div class="entry-text">
          <p><?php printf( __( 'Ready to publish your first post? <a href="%1$s">Get started here</a>.', 'alto' ), esc_url( admin_url( 'post-new.php' ) ) ); ?></p>
        </div>

      <?php elseif ( is_search() ) : ?>
        
        <?php get_search_form(); ?>

      <?php else : ?>

        <?php get_search_form(); ?>

      <?php endif; ?>

    </div> <!-- end .page-content -->

  </div> <!-- end .entry-body -->

</section> <!-- end .no-results -->