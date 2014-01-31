<?php
/**
 * Campaign sharing.
 *
 * @package Fundify
 * @since Fundify 1.5
 */

global $campaign;

$backers = $campaign->unique_backers();
?>

<div id="backers">
	
	<?php if ( empty( $backers ) ) : ?>
		<p><?php _e( 'No backers yet. Be the first!', 'fundify' ); ?></p>
	<?php else : ?>

		<ol class="backer-list">
		<?php foreach ( $backers as $payment_id ) : ?>
			<?php
				$meta       = edd_get_payment_meta( $payment_id );
				$user_info  = edd_get_payment_meta_user_info( $payment_id );

				if ( empty( $user_info ) )
					continue;

				$anon       = isset ( $meta[ 'anonymous' ] ) ? $meta[ 'anonymous' ] : 0;
			?>

			<li class="backer">
				<?php echo get_avatar( $anon ? '' : $user_info[ 'email' ], 40 ); ?>

				<div class="backer-info">
					<strong>
						<?php if ( $anon ) : ?>
							<?php _ex( 'Anonymous', 'Backer chose to hide their name', 'fundify' ); ?>
						<?php else : ?>
							<?php echo $user_info[ 'first_name' ]; ?> <?php echo $user_info[ 'last_name' ]; ?>
						<?php endif; ?>
					</strong><br />
					<?php echo edd_payment_amount( $payment_id ); ?>
					
					<?php do_action( 'fundify_campaign_backer_after', $campaign, $payment_id ); ?>
				</div>
			</li>
		<?php endforeach; ?>
		</ol>

	<?php endif; ?>

</div>