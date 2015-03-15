<?php if ( is_page_template( 'template-about.php' )  ): ?> 
<div class="about-menu top">
	<?php
	  wp_nav_menu(array('theme_location' => 'about_menu', 'menu_class' => 'nav nav-pills nav-justified'));
	?>
</div>

<?php endif; ?>  


<?php if ( is_page() && ! is_buddypress() && ! is_page('causes') || is_single() ): ?> 
	<div class="negative-row no-padding postthumb">

	<div class="post-author-avatar">
		<?php echo get_avatar( get_the_author_meta( 'ID' ), 100 ); ?>
	</div>

	<?php if ( cf_is_high_res() ): ?>
	    <?php the_post_thumbnail( array( 'width' => 1760, 'height' => 540, 'crop' => true ) ) ?>
	<?php else: ?>
	    <?php the_post_thumbnail( array( 'width' => 880, 'height' => 270, 'crop' => true ) ) ?>
	<?php endif;?>
	</div>
<?php endif; ?>  

<?php if ( is_home() || is_archive() ): ?> 
	<div class="negative-row no-padding postthumb">

		<div class="post-author-avatar">
			<?php echo get_avatar( get_the_author_meta( 'ID' ), 100 ); ?>
		</div>

	    <?php the_post_thumbnail( array( 'width' => 880, 'height' => 270, 'crop' => true ) ) ?>
	</div>
<?php endif; ?>  