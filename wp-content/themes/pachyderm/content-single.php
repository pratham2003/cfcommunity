<?php
/**
 * @package Pachyderm
 * @since Pachyderm 1.0
 */
?>

<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
	<header class="entry-header">
		<div class="post-format-indicator">
			<?php if ( get_post_format() ) : ?>
				<a class="entry-format" href="<?php echo esc_url( get_post_format_link( get_post_format() ) ); ?>" title="<?php echo esc_attr( sprintf( __( 'All %s posts', 'pachyderm' ), get_post_format_string( get_post_format() ) ) ); ?>"><?php echo get_post_format_string( get_post_format() ); ?></a>
			<?php endif; ?>
		</div>

		<?php the_title( '<h1 class="entry-title">', '</h1>' ); ?>

		<div class="entry-meta">
			<?php pachyderm_posted_on(); ?>
			<?php edit_post_link( __( 'Edit', 'pachyderm' ), '<span class="sep"> | </span><span class="edit-link">', '</span>' ); ?>
		</div><!-- .entry-meta -->
	</header><!-- .entry-header -->

	<div class="entry-content">
		<?php the_content(); ?>
		<?php wp_link_pages( array( 'before' => '<div class="page-links">' . __( 'Pages:', 'pachyderm' ), 'after' => '</div>' ) ); ?>
	</div><!-- .entry-content -->

	<footer class="entry-meta">
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
	</footer><!-- .entry-meta -->
</article><!-- #post-## -->