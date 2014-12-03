<?php
/**
 * This is the first alternative single post template. Users can
 * set this template via the Customization panel in the dashboard.
 *
 * @package Alto
 */
?>

<article id="post-<?php the_ID(); ?>" <?php post_class('alt-post alt-post-1'); ?>>

  <?php // Declare variables to help us determine context/type of post. ?>

  <?php 
    $thumbnail = has_post_thumbnail();
    $sticky    = is_sticky();
    $title     = get_the_title();
    $category  = get_the_category();
  ?>

  <div class="entry-body not-sticky <?php if ( ' ' != $thumbnail ) { ?>no-thumbnail<?php } ?>">

  <?php if ( $thumbnail ) { the_post_thumbnail( 'single-post' ); } ?>

    <div class="entry-meta desktop">

      <?php if ( $category ) { ?>
        <h5 class="category-title"><a href="<?php echo esc_url( get_category_link( $category[0]->term_id ) ); ?>"><?php echo esc_html( $category[0]->cat_name ); ?></a></h5>
      <?php } ?>
    
      <?php alto_posted_on(); ?>
    
    </div> <!-- end. entry-meta -->

    <div class="entry-content">

      <?php // Move the header inline with the post content. ?>

      <header class="entry-header <?php if ( $thumbnail ) { ?>has-thumbnail<?php } ?>">

        <?php if ( $category || $title ) { ?>

          <hgroup class="entry-title">

            <?php if ( $category ) { ?>
              <h5 class="category-title"><a href="<?php echo esc_url( get_category_link( $category[0]->term_id ) ); ?>"><?php echo esc_html( $category[0]->cat_name ); ?></a></h5>
            <?php } ?>

            <?php if ( $title ) { ?>
              <h1><?php the_title(); ?></h1>
            <?php } ?>

          </hgroup> <!-- end .entry-title -->

        <?php } ?>

      </header> <!-- end .entry-header.sticky -->

      <div class="entry-text">

        <?php
          the_content( __( 'Continue Reading', 'alto' ) );
          
          wp_link_pages( array(
            'before' => '<div class="page-links">' . __( 'Pages:', 'alto' ),
            'after'  => '</div>',
          ) );
        ?>

      </div> <!-- end .entry-text -->

      <?php // Get the tag list for this post. ?> 

      <?php 
        $tag_list = get_the_tag_list( '', __( ', ', 'alto' ) ); 
        if ( $tag_list ) {
      ?>

        <div class="tags">    
      
        <?php 
          $tags = __( '<strong>Tags:</strong> %1$s', 'alto' );
          if ( $tag_list ) {
            printf( $tags, $tag_list );
          }
        ?>

        </div> <!-- end .tags -->
      
      <?php } ?>

    </div> <!-- end .entry-content -->

    <div class="entry-meta mobile">

      <?php if ( $category ) { ?>
        <h5 class="category-title"><a href="<?php echo esc_url( get_category_link( $category[0]->term_id ) ); ?>"><?php echo esc_html( $category[0]->cat_name ); ?></a></h5>
      <?php } ?>
    
      <?php alto_posted_on(); ?>
    
    </div> <!-- end .entry-meta -->

    <div class="entry-meta-right">
      <?php 
        $postId = get_the_ID();
        alto_sharing( $postId, 'post' ); 
      ?>
    </div>

  </div> <!-- end .entry-body -->

</article> <!-- end #post -->