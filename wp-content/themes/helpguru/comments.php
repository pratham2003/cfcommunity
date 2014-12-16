<?php
/**
 * The template for displaying Comments.
 *
 */

/*
 * If the current post is protected by a password and
 * the visitor has not yet entered the password we will
 * return early without loading the comments.
 */
if ( post_password_required() )
	return;
?>

<?php if ( have_comments() ) : ?>
	
<!-- #comments -->
<section id="comments" class="comments-area clearfix" itemprop="comment">

	<h3 id="comments-title">
		<?php printf( _nx( '1 Comment', '%1$s Comments', get_comments_number(), 'comments title', 'ht-theme' ), number_format_i18n( get_comments_number() ) ); ?>
	</h3>

	<!-- .comment-list -->
	<ol class="comment-list">
		<?php wp_list_comments( array( 'callback' => 'ht_comment', 'style' => 'ol', ) );
		?>
	</ol>
	<!-- /.comment-list -->

		<?php if ( get_comment_pages_count() > 1 && get_option( 'page_comments' ) ) : // are there comments to navigate through ?>
		<nav id="comment-nav-below" class="navigation-comment" role="navigation">
			<h1 class="screen-reader-text"><?php _e( 'Comment navigation', 'ht-theme' ); ?></h1>
			<div class="nav-previous"><?php previous_comments_link( __( '&larr; Older Comments', 'ht-theme' ) ); ?></div>
			<div class="nav-next"><?php next_comments_link( __( 'Newer Comments &rarr;', 'ht-theme' ) ); ?></div>
		</nav><!-- #comment-nav-below -->
		<?php endif; // check for comment navigation ?>

</section>
<!-- /#comments -->

<?php endif; // have_comments() ?>
    
    <?php
		// If comments are closed and there are comments, let's leave a little note, shall we?
		if ( ! comments_open() && '0' != get_comments_number() && post_type_supports( get_post_type(), 'comments' ) ) :
	?>
        
	<?php endif; ?>

	<?php 
	$req = get_option( 'require_name_email' );
	$aria_req = ( $req ? " aria-required='true'" : '' );
	$comments_args = array(
		'cancel_reply_link' => __( '<i>×</i> Cancel Reply', 'ht-theme' ),
		'fields' => array(
				'author' => '<p class="comment-form-author"><span class="ht-input-wrapper"><input id="author" name="author" type="text" value="' . esc_attr( $commenter['comment_author'] ) . '" placeholder="' . __( 'Name', 'ht-theme' ) . '" size="30"' . $aria_req . ' /></span></p>',
				'email' => '<p class="comment-form-email"><span class="ht-input-wrapper"><input id="email" name="email" type="email" value="' . esc_attr( $commenter['comment_author_email'] ) . '" placeholder="' . __( 'Email', 'ht-theme' ) . '" size="30"' . $aria_req . ' /></span></p>',
				'url' => '<p class="comment-form-url"><span class="ht-input-wrapper"><input id="url" name="url" type="url" value="' . esc_attr( $commenter['comment_author_url'] ) . '" placeholder="' . __( 'Website', 'ht-theme' ) . '" size="30" /></span></p>',
		),
		'comment_field' => '<p class="comment-form-comment"><textarea id="comment" name="comment" placeholder="'. __( 'Your Comment', 'ht-theme' ) .'" cols="45" rows="5" aria-required="true"></textarea></p>',
	);
	comment_form($comments_args); ?>