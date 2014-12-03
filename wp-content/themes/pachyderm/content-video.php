<?php
/**
 * @package Pachyderm
 * @since Pachyderm 1.0
 */
?>

<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
	<header class="entry-header">
		<div class="post-format-indicator">
			<a class="entry-format" href="<?php echo esc_url( get_post_format_link( 'video' ) ); ?>" title="<?php echo esc_attr( sprintf( __( 'All %s posts', 'pachyderm' ), get_post_format_string( 'video' ) ) ); ?>"><?php echo get_post_format_string( 'video' ); ?></a>
		</div>
		<?php if ( ! post_password_required() && ( comments_open() || '0' != get_comments_number() ) ) : ?>
			<span class="comments-link"><?php comments_popup_link( __( '0', 'pachyderm' ), __( '1', 'pachyderm' ), __( '%', 'pachyderm' ) ); ?></span>
		<?php endif; ?>
		<?php if ( is_single() ) : ?>
			<?php the_title( '<h1 class="entry-title">', '</h1>' ); ?>
		<?php else : ?>
			<?php the_title( '<h1 class="entry-title"><a href="' . esc_url( get_permalink() ) . '" title="' . esc_attr( sprintf( 'Permalink to %s', the_title_attribute( 'echo=0' ), 'pachyderm' ) ) . '" rel="bookmark">', '</a></h1>' ); ?>
		<?php endif; ?>

	</header><!-- .entry-header -->

	<?php if ( is_search() ) : // Only display Excerpts for Search ?>
	<div class="entry-summary">
		<?php the_excerpt(); ?>
	</div><!-- .entry-summary -->
	<?php else : ?>
	<div class="entry-content">
		<?php if ( is_home() && '' !== get_the_post_thumbnail() ) : ?>
			<?php the_post_thumbnail(); ?>
		<?php endif; ?>
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
			<?php pachyderm_posted_on(); ?>
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
			<?php edit_post_link( __( 'Edit', 'pachyderm' ), '<span class="edit-link">', '</span>' ); ?>
		<?php endif; // End if 'post' == get_post_type() ?>

	</footer><!-- .entry-meta -->
</article><!-- #post-## -->
