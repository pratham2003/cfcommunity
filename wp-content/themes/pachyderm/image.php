<?php
/**
 * The template for displaying image attachments.
 *
 * @package Pachyderm
 * @since Pachyderm 1.0
 */

get_header();
?>

		<div id="primary" class="site-content image-attachment">
			<div id="content" role="main">

			<?php while ( have_posts() ) : the_post(); ?>

				<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
					<header class="entry-header">
						<div class="post-format-indicator"></div>
						<h1 class="entry-title"><?php the_title(); ?></h1>

						<div class="entry-meta">
							<?php
								$metadata = wp_get_attachment_metadata();
								printf( __( 'Published: <span class="entry-date"><time class="entry-date" datetime="%1$s" pubdate>%2$s</time></span><br />Full size: <a href="%3$s" title="Link to full-size image">%4$s &times; %5$s</a><br />Attached: <a href="%6$s" title="Return to %7$s" rel="gallery">%7$s</a>', 'pachyderm' ),
									esc_attr( get_the_date( 'c' ) ),
									esc_html( get_the_date() ),
									esc_url( wp_get_attachment_url() ),
									$metadata['width'],
									$metadata['height'],
									esc_url( get_permalink( $post->post_parent ) ),
									esc_attr( strip_tags( get_the_title( $post->post_parent ) ) )
								);
							?>
							<br /><?php edit_post_link( __( 'Edit', 'pachyderm' ), '<span class="edit-link">', '</span>' ); ?>
							<nav id="image-navigation">
								<span class="previous-image"><?php previous_image_link( false, __( '&laquo;' , 'pachyderm' ) ); ?></span>
								<span class="previous-image-thumb"><?php previous_image_link( 'thumbnail' ); ?></span>
								<span class="next-image"><?php next_image_link( false, __( '&raquo;' , 'pachyderm' ) ); ?></span>
								<span class="next-image-thumb"><?php next_image_link( 'thumbnail' ); ?></span>
							</nav><!-- #image-navigation -->
						</div><!-- .entry-meta -->

					</header><!-- .entry-header -->

					<div class="entry-content">

						<div class="entry-attachment">
							<div class="attachment">
								<?php
									/**
									 * Grab the IDs of all the image attachments in a gallery so we can get the URL of the next adjacent image in a gallery,
									 * or the first image (if we're looking at the last image in a gallery), or, in a gallery of one, just the link to that image file
									 */
									$attachments = array_values( get_children( array( 'post_parent' => $post->post_parent, 'post_status' => 'inherit', 'post_type' => 'attachment', 'post_mime_type' => 'image', 'order' => 'ASC', 'orderby' => 'menu_order ID' ) ) );
									foreach ( $attachments as $k => $attachment ) {
										if ( $attachment->ID == $post->ID )
											break;
									}
									$k++;
									// If there is more than 1 attachment in a gallery
									if ( count( $attachments ) > 1 ) {
										if ( isset( $attachments[ $k ] ) )
											// get the URL of the next image attachment
											$next_attachment_url = get_attachment_link( $attachments[ $k ]->ID );
										else
											// or get the URL of the first image attachment
											$next_attachment_url = get_attachment_link( $attachments[ 0 ]->ID );
									} else {
										// or, if there's only 1 image, get the URL of the image
										$next_attachment_url = wp_get_attachment_url();
									}
								?>

								<a href="<?php echo esc_url( $next_attachment_url ); ?>" title="<?php echo esc_attr( strip_tags( get_the_title() ) ); ?>" rel="attachment"><?php
								$attachment_size = apply_filters( '_s_attachment_size', 1200 );
								echo wp_get_attachment_image( $post->ID, array( $attachment_size, $attachment_size ) ); // filterable image width with, essentially, no limit for image height.
								?></a>
							</div><!-- .attachment -->

							<?php if ( ! empty( $post->post_excerpt ) ) : ?>
							<div class="entry-caption">
								<?php the_excerpt(); ?>
							</div>
							<?php endif; ?>
						</div><!-- .entry-attachment -->

						<?php the_content(); ?>
						<?php wp_link_pages( array( 'before' => '<div class="page-links">' . __( 'Pages:', 'pachyderm' ), 'after' => '</div>' ) ); ?>

					</div><!-- .entry-content -->

				</article><!-- #post-<?php the_ID(); ?> -->

				<?php comments_template(); ?>

			<?php endwhile; // end of the loop. ?>

			</div><!-- #content -->
		</div><!-- #primary .site-content -->

<?php get_footer(); ?>