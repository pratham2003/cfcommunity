<?php
/**
 * This is the recent posts template that is used on
 * the index page and on "results" pages.
 *
 * @package Alto
 */
?>

<article id="post-<?php the_ID(); ?>" <?php post_class('recent-post'); ?>>

  <?php // Declare variables to help us determine context/type of post. ?>

  <?php 
    $hasPostThumbnail = has_post_thumbnail();
    $title            = get_the_title();
  ?>

  <div class="entry-content <?php if ( $hasPostThumbnail ) { ?>has-thumbnail<?php } ?>">

    <?php if ( $hasPostThumbnail ) { ?>
      <a href="<?php the_permalink(); ?>"><?php the_post_thumbnail( 'recent-post' ); ?></a>
    <?php } ?> 

    <div class="recent-post-body">
      <h5 class="recent-post-date"><a href="<?php the_permalink(); ?>"><?php the_time( 'F d, Y' ); ?></a></h5>
      <?php if ( $title ) { ?>
        <h1 class="recent-post-title"><a href="<?php the_permalink(); ?>" rel="bookmark"><?php the_title(); ?></a></h1>
      <?php } ?>
      <span><?php the_excerpt(); ?></span>
    </div> <!-- end .recent-post-body -->

  </div> <!-- end .entry-content -->

</article> <!-- end #post-## -->