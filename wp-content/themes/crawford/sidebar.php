<?php if (is_active_sidebar('footer-sidebar-left') || is_active_sidebar('footer-sidebar-middle') || is_active_sidebar('footer-sidebar-right')) : ?>
	<div id="footer-sidebar" class="container">
		<div class="row">
			<div class="col col-md-4 left">
				<?php dynamic_sidebar('footer-sidebar-left'); ?>
			</div>
			<div class="col col-md-4 middle">
				<?php dynamic_sidebar('footer-sidebar-middle'); ?>
			</div>
			<div class="col col-md-4 right">
				<?php dynamic_sidebar('footer-sidebar-right'); ?>
			</div>
		</div>	
	</div>
<?php endif; ?>