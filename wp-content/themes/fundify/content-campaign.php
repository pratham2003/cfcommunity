<?php
/**
 * @package Fundify
 * @since Fundify 1.0
 */

global $post;

$campaign = atcf_get_campaign( $post );
?>

<article id="post-<?php the_ID(); ?>" <?php post_class( 'item' ); ?>>
	<?php if ( ! $campaign->is_active() && $campaign->is_funded() ) : ?>
	<div class="campaign-ribbon success">
		<a href="<?php the_permalink(); ?>"><?php _e( 'Successful', 'fundify' ); ?></a>
	</div>
	<?php elseif ( ! $campaign->is_active() && ! $campaign->is_funded() ) : ?>
	<div class="campaign-ribbon unsuccess">
		<a href="<?php the_permalink(); ?>"><?php _e( 'Unsuccessful', 'fundify' ); ?></a>
	</div>
	<?php elseif ( $campaign->hours_remaining() <= 12 && $campaign->is_active() && ! $campaign->is_endless() ) : ?>
	<div class="campaign-ribbon">
		<a href="<?php the_permalink(); ?>"><?php _e( 'Ending Soon', 'fundify' ); ?></a>
	</div>
	<?php elseif ( get_post_meta( $post->ID, '_campaign_featured', true ) ) : ?>
	<div class="campaign-ribbon featured">
		<a href="<?php the_permalink(); ?>"><?php _e( 'Staff Pick', 'fundify' ); ?></a>
	</div>
	<?php endif; ?>

	<a href="<?php the_permalink(); ?>" title="<?php the_title_attribute(); ?>" rel="bookmark">
		<?php the_post_thumbnail( 'campaign' ); ?>
	</a>
	
	<h3 class="entry-title">
		<a href="<?php the_permalink(); ?>" title="<?php the_title_attribute(); ?>" rel="bookmark"><?php the_title(); ?></a>
	</h3>

	<?php the_excerpt(); ?>

	<div class="digits">
		<div class="bar"><span style="width: <?php echo $campaign->percent_completed(); ?>"></span></div>
		<ul>
			<li><?php printf( __( '<strong>%s</strong> Funded', 'fundify' ), $campaign->percent_completed() ); ?></li>
			<li><?php printf( _x( '<strong>%s</strong> Funded', 'Amount funded in single campaign stats', 'fundify' ), $campaign->current_amount() ); ?></li>
			<?php if ( ! $campaign->is_endless() ) : ?>
			<li>
				<?php if ( $campaign->days_remaining() > 0 ) : ?>
					<?php printf( __( '<strong>%s</strong> Days to Go', 'fundify' ), $campaign->days_remaining() ); ?>
				<?php else : ?>
					<?php printf( __( '<strong>%s</strong> Hours to Go', 'fundify' ), $campaign->hours_remaining() ); ?>
				<?php endif; ?>
			</li>
			<?php endif; ?>
		</ul>
	</div>
</article>