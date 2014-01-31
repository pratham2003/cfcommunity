<?php
/**
 * Campaign title.
 *
 * @package Fundify
 * @since Fundify 1.5
 */

global $campaign;
?>

<div class="title <?php echo '' == $campaign->author() ? '' : 'title-two'; ?> pattern-<?php echo rand(1,4); ?>">
	<div class="container">
		<h1><?php the_title() ;?></h1>
		
		<?php if ( '' != $campaign->author() ) : ?>
		<h3><?php printf( __( 'By %s', 'fundify' ), esc_attr( $campaign->author() ) ); ?></h3>
		<?php endif; ?>
	</div>
</div>