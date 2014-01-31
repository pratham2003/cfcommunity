<?php
/**
 * Campaign updates.
 *
 * @package Fundify
 * @since Fundify 1.5
 */

global $campaign;
?>

<?php if ( '' != $campaign->updates() ) : ?>
	<div id="updates">
		<h3 class="campaign-updates-title sans"><?php _e( 'Updates', 'fundify' ); ?></h3>

		<?php echo $campaign->updates(); ?>
	</div>
<?php endif; ?>