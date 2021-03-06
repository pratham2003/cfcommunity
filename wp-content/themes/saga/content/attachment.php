<article <?php hybrid_attr( 'post' ); ?>>

	<?php if ( is_attachment( get_the_ID() ) ) : // If viewing a single attachment. ?>

		<div class="featured-media">
			<?php hybrid_attachment(); // Function for handling non-image attachments. ?>
		</div><!-- .featured-media -->

		<div class="wrap">

			<header class="entry-header">
				<h1 <?php hybrid_attr( 'entry-title' ); ?>><?php single_post_title(); ?></h1>
			</header><!-- .entry-header -->

			<div class="entry-content">
				<?php hybrid_attachment(); // Function for handling non-image attachments. ?>
				<?php the_content(); ?>
				<?php wp_link_pages(); ?>
			</div><!-- .entry-content -->

		</div><!-- .wrap -->

	<?php else : // If not viewing a single attachment. ?>

		<?php get_the_image(
			array( 
				'size'       => 'saga-large', 
				'min_width'  => 1100, 
				'min_height' => 500, 
				'order'      => array( 'featured', 'attachment' ), 
				'before'     => '<div class="featured-media">', 
				'after'      => '</div>' 
			) 
		); ?>

		<div class="wrap">

			<header class="entry-header">
				<?php the_title( '<h2 ' . hybrid_get_attr( 'entry-title' ) . '><a href="' . get_permalink() . '" rel="bookmark" itemprop="url">', '</a></h2>' ); ?>
			</header><!-- .entry-header -->

			<div <?php hybrid_attr( 'entry-summary' ); ?>>
				<?php the_excerpt(); ?>
			</div><!-- .entry-summary -->

		</div><!-- .wrap -->

	<?php endif; // End single attachment check. ?>

</article><!-- .entry -->