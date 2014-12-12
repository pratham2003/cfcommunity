<?php if ( is_page() && ! is_buddypress() || is_single() ): ?> 
	<div class="negative-row no-padding postthumb">
	<?php if ( cf_is_high_res() ): ?>
	    <?php the_post_thumbnail( array( 'width' => 1760, 'height' => 540, 'crop' => true ) ) ?>
	<?php else: ?>
	    <?php the_post_thumbnail( array( 'width' => 880, 'height' => 270, 'crop' => true ) ) ?>
	<?php endif;?>
	</div>
<?php endif; ?>  