<?php
/**
 * @package Pachyderm
 * @since Pachyderm 1.0
 */
?>

<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
	<header class="entry-header clear">
		<div class="post-format-indicator">
			<?php if ( get_post_format() ) : ?>
				<a class="entry-format" href="<?php echo esc_url( get_post_format_link( get_post_format() ) ); ?>" title="<?php echo esc_attr( sprintf( __( 'All %s posts', 'pachyderm' ), get_post_format_string( get_post_format() ) ) ); ?>"><?php echo get_post_format_string( get_post_format() ); ?></a>
			<?php endif; ?>
		</div>
		<?php if ( ! post_password_required() && ( comments_open() || '0' != get_comments_number() ) ) : ?>
			<span class="comments-link"><?php comments_popup_link( __( '0', 'pachyderm' ), __( '1', 'pachyderm' ), __( '%', 'pachyderm' ) ); ?></span>
		<?php endif; ?>
		<h1 class="entry-title"><a href="<?php the_permalink(); ?>" title="<?php echo esc_attr( sprintf( __( 'Permalink to %s', 'pachyderm' ), the_title_attribute( 'echo=0' ) ) ); ?>" rel="bookmark"><?php the_title(); ?></a></h1>

		<?php if ( 'post' == get_post_type() ) : ?>
		<div class="entry-meta">
			<?php pachyderm_posted_on(); ?>
			<?php edit_post_link( __( 'Edit', 'pachyderm' ), '<span class="sep"> | </span><span class="edit-link">', '</span>' ); ?>
		</div><!-- .entry-meta -->
		<?php endif; ?>
	</header><!-- .entry-header -->

	<?php if ( is_search() || '' != get_the_post_thumbnail() && ! get_post_format() ) : // Only display Excerpts for Search; only display excerpts when post thumbnails are assigned and the post is not a formatted post ?>
	<div class="entry-summary">
		<?php if ( ! is_search() ) : ?>
			<?php the_post_thumbnail(); ?>
		<?php endif; ?>
		<?php the_excerpt(); ?>
	</div><!-- .entry-summary -->
	<?php else : ?>
	<div class="entry-content">
		<?php the_content( __( 'Continue reading <span class="meta-nav">&rarr;</span>', 'pachyderm' ) ); ?>
		<?php wp_link_pages( array( 'before' => '<div class="page-links">' . __( 'Pages:', 'pachyderm' ), 'after' => '</div>' ) ); ?>
	</div><!-- .entry-content -->
	<?php endif; ?>

	<footer class="entry-meta">
		<?php if ( 'post' == get_post_type() ) : // Hide category and tag text for pages on Search ?>
			<?php
				/* translators: used between list items, there is a space after the comma */
				$categories_list = get_the_category_list( __( ' ', 'pachyderm' ) );
				if ( $categories_list && pachyderm_categorized_blog() ) :
			?>
			<span class="cat-links">
				<?php echo $categories_list; ?>
			</span>
			<?php endif; // End if categories ?>

			<?php
				/* translators: used between list items, there is a space after the comma */
				$tags_list = get_the_tag_list( '' );
				if ( $tags_list ) :
			?>
			<span class="tags-links">
				<?php echo $tags_list; ?>
			</span>
			<?php endif; // End if $tags_list ?>
		<?php endif; // End if 'post' == get_post_type() ?>

	</footer><!-- .entry-meta -->
</article><!-- #post-## -->
