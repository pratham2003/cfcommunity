<?php
/**
 *
 */

global $post, $campaign;

$author = get_user_by( 'id', $post->post_author );
?>

<div class="widget widget-bio">
	<?php if ( '' != $campaign->contact_email() ) : ?>
		<div class="author-contact">
			<p><a href="http://support.cfcommunity.net/contact" class="button btn-green"><?php _e( 'Ask Question', 'fundify' ); ?></a></p>
		</div>
	<?php endif; ?>
</div>