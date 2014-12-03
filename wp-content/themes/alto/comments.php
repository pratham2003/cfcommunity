<?php
/**
 * This is the archive template that displays comments
 * on posts and pages.
 *
 * The area of the page that contains both current comments
 * and the comment form. The actual display of comments is
 * handled by a callback to alto_comment() which is
 * located in the inc/template-tags.php file.
 *
 * @package Alto
 */

/*
 * If the current post is protected by a password and
 * the visitor has not yet entered the password we will
 * return early without loading the comments.
 */

if ( post_password_required() ) {
  return;
}
?>

<?php // Declare variables to find out what version of the comments we need. ?>

<?php
  $single           = is_single();
  $page             = is_page();
  $attachment       = is_attachment();
  $single_style     = get_theme_mod( 'alto_select_alt_post_type' );
  $comments_by_type = separate_comments( $comments );
?>

<?php if ( !$page && !$attachment && ( 'alt-1' == $single_style || 'alt-2' == $single_style ) ) { ?>
  <div class="alt-layout-comments-wrap">
<?php } ?>

<div id="comments" class="comments-area">

  <header class="block-header">
    <h3><?php _e( 'Discussion', 'alto' ); ?></h3>
  </header>

  <?php // You can start editing here -- including this comment! ?>

  <?php if ( !empty( $comments_by_type['comment'] ) ) { ?>

    <h2 class="comments-title">
      <?php
        printf( _nx( 'One response to &ldquo;%2$s&rdquo;', '%1$s responses to &lsquo;%2$s&rsquo;', get_comments_number(), 'comments title', 'alto' ),
          number_format_i18n( get_comments_number() ), '<span>' . get_the_title() . '</span>' );
      ?>
    </h2>

    <ol class="comment-list">
      <?php
        /* Loop through and list the comments. Tell wp_list_comments()
         * to use alto_comment() to format the comments.
         * If you want to override this in a child theme, then you can
         * define alto_comment() and that will be used instead.
         * See alto_comment() in inc/template-tags.php for more.
         */
        wp_list_comments( array( 'callback' => 'alto_comment' ) );
      ?>
    </ol> <!-- end .comment-list -->

    <?php // Are there comments to display? ?>

    <?php if ( get_comment_pages_count() > 1 && get_option( 'page_comments' ) ) { ?>

      <nav id="comment-nav-below" class="comment-navigation" role="navigation">
        <h1 class="screen-reader-text"><?php _e( 'Comment navigation', 'alto' ); ?></h1>
        <div class="nav-previous"><?php previous_comments_link( __( '&lsaquo; Older Comments', 'alto' ) ); ?></div>
        <div class="nav-next"><?php next_comments_link( __( 'Newer Comments &rsaquo;', 'alto' ) ); ?></div>
      </nav> <!-- end #comment-nav-below -->

    <?php } ?>

  <?php } ?>

  <?php // If comments are closed and there are comments, let's leave a little note, shall we? ?>

  <?php if ( ! comments_open() && '0' != get_comments_number() && post_type_supports( get_post_type(), 'comments' ) ) { ?>

    <p class="no-comments"><?php _e( 'Comments are closed.', 'alto' ); ?></p>

  <?php } ?>

  <?php comment_form(); ?>

  <?php // Give pingbacks and trackbacks their own home separate from the comments. If neither exists, hide the block. ?>

  <?php if( !empty( $comments_by_type['pingback'] ) || !empty( $comments_by_type['trackback'] ) ) { ?>

    <div class="pingbacks-trackbacks">

      <header class="block-header">
        <h3><?php _e( 'Pingbacks &amp; Trackbacks', 'alto' ); ?></h3>
      </header>

      <ol class="pingbacks-trackbacks-list">
        <?php
          wp_list_comments( array( 'callback' => 'alto_pingbacks_trackbacks' ) );
        ?>
      </ol> <!-- end .comment-list -->

    </div> <!-- end .pingbacks-trackbacks -->

  <?php } ?>

</div> <!-- end #comments -->

<?php // If we determined we were on a non-standard page, close the wrapper div. ?>

<?php if ( !$page && !$attachment && ( 'alt-1' == $single_style || 'alt-2' == $single_style ) ) { ?></div><?php } ?>
