<?php
/**
 * Custom template tags for this theme.
 *
 * @package Alto
 */

if ( ! function_exists( 'alto_paging_nav' ) ) :
/**
 * Display navigation to next/previous set of posts when applicable.
 *
 * @return void
 */
function alto_paging_nav() {
  // Add a class to give next/previous links styling. 

  add_filter('next_posts_link_attributes', 'alto_posts_link_attributes');
  add_filter('previous_posts_link_attributes', 'alto_posts_link_attributes');

  function alto_posts_link_attributes() {
    return 'class="btn"';
  }

  // Don't print empty markup if there's only one page.
  if ( $GLOBALS['wp_query']->max_num_pages < 2 ) {
    return;
  }
  ?>
  <nav class="navigation paging-navigation" role="navigation">
    <h1 class="screen-reader-text"><?php _e( 'Posts navigation', 'alto' ); ?></h1>
    <div class="nav-links">

      <?php if ( get_next_posts_link() ) : ?>
      <div class="nav-previous"><?php next_posts_link( __( 'Older posts', 'alto' ) ); ?></div>
      <?php endif; ?>

      <?php if ( get_previous_posts_link() ) : ?>
      <div class="nav-next"><?php previous_posts_link( __( 'Newer posts', 'alto' ) ); ?></div>
      <?php endif; ?>

    </div><!-- .nav-links -->
  </nav><!-- .navigation -->
  <?php
}
endif;

if ( ! function_exists( 'alto_post_nav' ) ) :
/**
 * Display navigation to next/previous post when applicable.
 *
 * @return void
 */
function alto_post_nav() {
  // Don't print empty markup if there's nowhere to navigate.
  $previous = ( is_attachment() ) ? get_post( get_post()->post_parent ) : get_adjacent_post( false, '', true );
  $next     = get_adjacent_post( false, '', false );

  if ( ! $next && ! $previous ) {
    return;
  }
  ?>
  <nav class="navigation post-navigation" role="navigation">
    <h1 class="screen-reader-text"><?php _e( 'Post navigation', 'alto' ); ?></h1>
    <div class="nav-links">
      <?php if ( $previous ) { ?>
        <div class="navigate-left">
          <h5><?php _e( 'Previous Post', 'alto'); ?></h5> 
          <?php previous_post_link( '<h3>%link</h3>', _x( '%title', 'Previous post link', 'alto' ) ); ?>
        </div>
      <?php } 
      if ( $next ) { ?>
        <div class="navigate-right">
          <h5><?php _e( 'Next Post', 'alto'); ?></h5> 
          <?php next_post_link( '<h3>%link</h3>', _x( '%title', 'Next post link', 'alto' ) ); ?>
        </div>
      <?php } ?>
    </div><!-- .nav-links -->
  </nav><!-- .navigation -->
  <?php
}
endif;

if ( ! function_exists( 'alto_comment' ) ) :
/**
 * Template for comments.
 * Used as a callback by wp_list_comments() for displaying the comments.
 */
function alto_comment( $comment, $args, $depth ) {
  $GLOBALS['comment'] = $comment;

  if ( 'pingback' == $comment->comment_type || 'trackback' == $comment->comment_type ) : else : ?>

  <li id="comment-<?php comment_ID(); ?>" <?php comment_class( empty( $args['has_children'] ) ? '' : 'parent' ); ?>>
    <article id="div-comment-<?php comment_ID(); ?>" class="comment-body">
      <footer class="comment-meta">
        <div class="comment-author vcard">
          <?php if ( 0 != $args['avatar_size'] ) { echo get_avatar( $comment, '52' ); } ?>
          <div class="comment-metadata">
            <?php printf( '%s', sprintf( '<cite class="fn">%s</cite>', get_comment_author_link() ) ); ?>
            <span class="timestamp-edit">
              <a href="<?php echo esc_url( get_comment_link( $comment->comment_ID ) ); ?>">
                <time class="timeago" datetime="<?php comment_time( 'c' ); ?>">
                  <?php printf( _x( '%1$s %2$s', '1: date, 2: time', 'alto' ), get_comment_date(), get_comment_time() ); ?>
                </time>
              </a>
              <span class="bullet-separator">&bull;</span>
              <?php
                comment_reply_link( array_merge( $args, array(
                  'add_below' => 'div-comment',
                  'depth'     => $depth,
                  'max_depth' => $args['max_depth'],
                  'before'    => '',
                  'after'     => '',
                ) ) );
              ?>
              <?php edit_comment_link( __( 'Edit', 'alto' ), '<span class="edit-link">', '</span>' ); ?>              
            </span>
          </div> <!-- end .comment-metadata -->
        </div> <!-- end .comment-author -->

        <?php if ( '0' == $comment->comment_approved ) : ?>
        <p class="comment-awaiting-moderation"><?php _e( 'Your comment is awaiting moderation.', 'alto' ); ?></p>
        <?php endif; ?>
      </footer> <!-- end .comment-meta -->

      <div class="comment-content">
        <?php comment_text(); ?>
        <?php edit_comment_link( __( 'Edit', 'alto' ), '<span class="edit-link">', '</span>' ); ?>
      </div> <!-- end .comment-content -->

      <?php
        comment_reply_link( array_merge( $args, array(
          'add_below' => 'div-comment',
          'depth'     => $depth,
          'max_depth' => $args['max_depth'],
          'before'    => '<div class="reply">',
          'after'     => '</div>',
        ) ) );
      ?>
    </article> <!-- end .comment-body -->

  <?php
  endif;
}

endif; // ends check for alto_comment()

if ( ! function_exists ( 'alto_pingbacks_trackbacks' ) ) :

/**
 * Template for pingbacks and trackbacks.
 * Used as a callback by wp_list_comments() for displaying the comments.
 */

function alto_pingbacks_trackbacks( $comment, $args, $depth ) {
  $GLOBALS['comment'] = $comment;

  if ( 'pingback' == $comment->comment_type || 'trackback' == $comment->comment_type ) : ?>

  <li id="comment-<?php comment_ID(); ?>" <?php comment_class(); ?>>
    <?php comment_author_link(); ?>
    <time class="timeago" datetime="<?php comment_time( 'c' ); ?>">
      <?php printf( _x( '%1$s %2$s', '1: date, 2: time', 'alto' ), get_comment_date(), get_comment_time() ); ?>
    </time>
  </li>

  <?php else : ?>

  <?php
  endif;

}

endif; // ends check for alto_pingbacks_trackbacks

if ( ! function_exists( 'alto_posted_on' ) ) :
/**
 * Prints HTML with meta information for the current post-date/time and author.
 */
function alto_posted_on() {
  $time_string = '<time class="entry-date published" datetime="%1$s">%2$s</time>';
  if ( get_the_time( 'U' ) !== get_the_modified_time( 'U' ) ) {
    $time_string .= '<time class="updated" datetime="%3$s">%4$s</time>';
  }

  $time_string = sprintf( $time_string,
    esc_attr( get_the_date( 'c' ) ),
    esc_html( get_the_date() ),
    esc_attr( get_the_modified_date( 'c' ) ),
    esc_html( get_the_modified_date() )
  );

  echo "<div class='posted-author'>";

  printf( __( '<span class="posted-on"><strong>Published on<span class="semicolon">:</span></strong> %1$s</span><span class="byline"><strong>Author<span class="semicolon">:</span></strong> %2$s</span>', 'alto' ),
    sprintf( '<a href="%1$s" rel="bookmark">%2$s</a>',
      esc_url( get_permalink() ),
      $time_string
    ),
    sprintf( '<span class="author vcard"><a class="url fn n" href="%1$s">%2$s</a></span>',
      esc_url( get_author_posts_url( get_the_author_meta( 'ID' ) ) ),
      esc_html( get_the_author() )
    )
  );

  echo "</div>";

}
endif;

if ( ! function_exists( 'alto_jetpack_sharing_likes' ) ) :

/**
* Jetpack: Custom sharing module. Outputs a custom div with Jetpack Sharing and Likes combined.
*/

function alto_jetpack_sharing_likes( $postId ) {

  $sharing_disabled = get_post_meta( $postId, 'sharing_disabled' );
  $likes_disabled   = get_post_meta( $postId, 'switch_like_status' );

  if ( class_exists( 'Jetpack') ) {

    if ( $likes_disabled ) { 
      echo "<div class='alto-sharing-more likes-disabled'>"; 
    } else {
      echo "<div class='alto-sharing-more'>"; 
    }
    
    echo "<div class='close'><i class='icon-alto-iconfont_Close-Rounded'></i></div>";

    if ( function_exists( 'sharing_display' ) ) {

      if ( !$sharing_disabled ) {
        echo sharing_display( '', true );
      }

    }

    if ( class_exists( 'Jetpack_Likes' ) ) {

      if ( !$likes_disabled ) {
        $custom_likes = new Jetpack_Likes;
        echo "<div class='alto-likes'>";
        echo $custom_likes->post_likes( '' );
        echo "</div>";
      }

    }

    echo "</div>";

  }

}

endif;

if ( ! function_exists( 'alto_sharing' ) ) :
/**
* Jetpack: Wrapper Module for Sharing Links, Jetpack Sharing, and Jetpack Likes.
*/
function alto_sharing( $postId, $location ) {

  // Make sure buttons are enabled in this location.

  $get_locations = get_option( 'sharing-options', false );
  $get_locations = $get_locations['global']['show'];

  if ( !empty( $get_locations ) ) {

    if ( in_array( $location, $get_locations ) ) {

      $sharing_disabled    = get_post_meta( $postId, 'sharing_disabled' );
      $likes_disabled      = get_post_meta( $postId, 'switch_like_status' );

      $before_alto_sharing = "<div class='alto-sharing'><h5>Share<span class='semicolon'>:</span></h5>";
      $after_alto_sharing  = "</div>";

      $page_url            = get_permalink();
      $page_title          = get_the_title();

      $facebook_url        = "https://www.facebook.com/sharer/sharer.php?u=" . $page_url . "&t=" . $page_title . " ";
      $twitter_url         = "http://twitter.com/intent/tweet/?text=" . $page_title . "&url=" . $page_url . " ";

      if ( !$sharing_disabled || !$likes_disabled ) {

        // See if individual services are enabled in Jetpack. This needs a good cleaning.

        $get_services  = get_option( 'sharing-services', false );
        $get_visible   = $get_services['visible'];
        $get_hidden    = $get_services['hidden'];
        $get_jpmods    = ( function_exists( 'sharing_display' ) || class_exists( 'Jetpack_Likes' ) );
        $has_hidden    = count( $get_hidden );

        if ( $get_services ) {
          $check_facebook = ( in_array( 'facebook', $get_visible )  || in_array( 'facebook', $get_hidden ) );
          $check_twitter  = ( in_array( 'twitter', $get_visible )  || in_array( 'twitter', $get_hidden ) );
          $check_email    = ( in_array( 'email', $get_visible )  || in_array( 'email', $get_hidden ) );
        } else {
          $check_facebook = false;
          $check_twitter  = false;
          $check_email    = false;
        }

        // Output the sharing module. 

        if ( $get_jpmods && $has_hidden > 0 ) {

          echo $before_alto_sharing;
          echo "<ul class='alto-sharing-list'>"; 
          if ( $check_facebook ) {
            echo "<li class='alto-share-facebook'><a href='".$facebook_url."'><i class='icon-alto-iconfont_facebook'></i> <span>Facebook</span></a></li>";
          }
          if ( $check_twitter ) {
            echo "<li class='alto-share-twitter'><a href='".$twitter_url."'><i class='icon-alto-iconfont_twitter'></i> <span>Twitter</span<</a></li>";
          }
          if ( $check_email ) {
            echo "<li class='alto-share-mail'><a href='mailto:?subject=Check this out: ".$page_title."&body=Check this out: ".$page_url."'><i class='icon-alto-iconfont_Mail'></i> <span>Mail</span></a></li>";
          }
          if ( $has_hidden > 0 && $get_jpmods ) {
            echo "<li class='alto-share-more'><a href='#'><i class='icon-alto-iconfont_Share---Rounded'></i><span>" . __( 'More', 'alto' ) . "</span></a></li>";
          }
          echo "</ul>";
          alto_jetpack_sharing_likes($postId);
          echo $after_alto_sharing;

        }

      } // ( !$sharing_disabled || !$likes_disabled )

    } // end in_array( $location, $get_locations )

  }

}
endif;