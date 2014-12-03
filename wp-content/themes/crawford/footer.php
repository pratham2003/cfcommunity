<footer>
	<?php get_sidebar() ?>
	<div id="footer-meta" class="container">
		<div class="row">
			<p>&copy; <?php echo date("Y"); ?> <a href="<?php echo home_url('/'); ?>" title="<?php bloginfo('name'); ?>"><?php bloginfo('name'); ?></a> &bull; <a href="<?php echo esc_url(__('http://www.wpmultiverse.com/themes/crawford/', 'crawford')); ?>" title="Crawford WordPress Theme" target="_blank">Crawford Theme</a></p>
			<nav><?php wp_nav_menu(array('theme_location' => 'footer','container' => false,'depth' => 1,'fallback_cb' => false)); ?></nav>
		</div>
	</div>
</footer>
<?php wp_footer(); ?>   
</body>
</html>