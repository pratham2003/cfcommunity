<?php
/**
 * This is the template that displays content on pages.
 *
 * @package Alto
 */
?>

<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>

  <?php // Declare variables to help us determine context/type of post. ?>

  <?php 
    $hasPostThumbnail = has_post_thumbnail();
    $isSticky         = is_sticky();
    $title            = get_the_title();
  ?>

  <?php if ( $hasPostThumbnail ) { the_post_thumbnail( 'single-post' ); } ?>

  <?php if ( $title ) { ?>

    <header class="entry-header">
      <h1 class="entry-title"><?php the_title(); ?></h1>
    </header>

  <?php } ?>

  <div class="entry-content">
    <?php the_content(); ?>
    <?php
      wp_link_pages( array(
        'before' => '<div class="page-links">' . __( 'Pages:', 'watson' ),
        'after'  => '</div>',
      ) );
    ?>

  </div> <!-- end .entry-content -->

  <?php edit_post_link( __( 'Edit', 'watson' ), '<span class="edit-link">', '</span>' ); ?>

</article> <!-- end #post-## -->
