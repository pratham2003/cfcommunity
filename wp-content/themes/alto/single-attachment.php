<?php 
/**
 * Template for displaying post attachments.
 *
 * @package Alto
 */
?>

<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>

  <?php // Declare variables to help us determine context/type of post. ?>

  <?php 
    $thumbnail = has_post_thumbnail();
    $sticky    = is_sticky();
    $title     = get_the_title();
    $category  = get_the_category();
  ?>

  <div class="entry-body not-sticky <?php if ( ' ' != $thumbnail ) { ?>no-thumbnail<?php } ?>">

    <div class="entry-content">

      <?php // Move the header inline with the post content. ?>

      <header class="entry-header <?php if ( $thumbnail ) { ?>has-thumbnail<?php } ?>">

        <?php if ( $thumbnail ) { ?> 
          <?php the_post_thumbnail( 'single-post' ); ?>
        <?php } ?>

        <?php if ( $category || $title ) { ?>

          <hgroup class="entry-title">

            <?php if ( $category ) { ?>
              <h5><a href="<?php echo esc_url( get_category_link( $category[0]->term_id ) ); ?>"><?php echo esc_html( $category[0]->cat_name ); ?></a></h5>
            <?php } ?>

            <?php if ( $title ) { ?>
              <h1><?php the_title(); ?></h1>
            <?php } ?>

          </hgroup> <!-- end .entry-title -->

        <?php } ?>

      </header> <!-- end .entry-header.sticky -->

      <div class="entry-text">
    
        <div class="entry-attachment">
          <?php if ( wp_attachment_is_image( $post->id ) ) : $att_image = wp_get_attachment_image_src( $post->id, "full" ); ?>
            <p class="attachment">
              <a href="<?php echo esc_url( wp_get_attachment_url( $post->id ) ); ?>" title="<?php the_title_attribute(); ?>" rel="attachment">
                <img src="<?php echo esc_url( $att_image[0] );?>" width="<?php echo intval( $att_image[1] );?>" height="<?php echo intval( $att_image[2] );?>"  class="attachment-medium" alt="<?php $post->post_excerpt; ?>" />
              </a>
            </p>
          <?php else : ?>
            <a href="<?php echo esc_url( wp_get_attachment_url( $post->ID ) ); ?>" title="<?php echo esc_html( get_the_title( $post->ID ) ); ?>" rel="attachment">
              <?php echo basename( $post->guid ) ?>
            </a>
          <?php endif; ?>
        </div> <!-- end .entry-attachment -->

        <?php
          the_content( __( 'Continue Reading', 'alto' ) );
          
          wp_link_pages( array(
            'before' => '<div class="page-links">' . __( 'Pages:', 'alto' ),
            'after'  => '</div>',
          ) );
        ?>

      </div> <!-- end .entry-text -->

      <?php 
        $tag_list = get_the_tag_list( '', __( ', ', 'alto' ) ); 
        if ( $tag_list ) {
      ?>

        <?php // Get the tag list for this post. ?> 

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

    <div class="entry-meta">
      <?php alto_posted_on(); ?>
      <?php 
        $postId = get_the_ID();
        alto_sharing( $postId, 'attachment' ); 
       ?>
    </div> <!-- end .entry-meta -->

  </div> <!-- end .entry-body -->

  <?php edit_post_link( __( 'Edit', 'alto' ), '<span class="edit-link">', '</span>' ); ?>

</article> <!-- end #post -->