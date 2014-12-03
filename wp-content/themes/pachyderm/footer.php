<?php
/**
 * The template for displaying the footer.
 *
 * Contains the closing of the id=main div and all content after
 *
 * @package Pachyderm
 * @since Pachyderm 1.0
 */
?>

	</div><!-- #main -->
	<?php get_sidebar(); ?>
	<footer id="colophon" class="site-footer" role="contentinfo">
		<?php pachyderm_content_nav( 'nav-below' ); ?>
		<div class="site-info">
			<?php do_action( 'pachyderm_credits' ); ?>
			<a href="http://wordpress.org/" title="<?php esc_attr_e( 'A Semantic Personal Publishing Platform', 'pachyderm' ); ?>" rel="generator"><?php printf( __( 'Proudly powered by %s', 'pachyderm' ), 'WordPress' ); ?></a>
			<span class="sep"> | </span>
			<?php printf( __( 'Theme: %1$s by %2$s.', 'pachyderm' ), 'pachyderm', '<a href="http://carolinemoore.net/" rel="designer">Caroline Moore</a>' ); ?>
		</div><!-- .site-info -->
	</footer><!-- #colophon -->
</div><!-- #page -->

<?php wp_footer(); ?>

</body>
</html>