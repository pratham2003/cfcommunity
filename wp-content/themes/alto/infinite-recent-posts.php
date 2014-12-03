<?php
/**
 * This is the template for displaying recent posts when
 * infinite scroll is activated in Jetpack.
 *
 * @package Alto
 */
?>

<?php while ( have_posts() ) : the_post(); ?>

  <?php get_template_part( 'content-recent-posts', get_post_format() ); ?>

<?php endwhile; ?>