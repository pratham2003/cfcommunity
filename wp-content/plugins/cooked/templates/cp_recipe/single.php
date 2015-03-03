<?php get_header();
	the_post();
	global $post_id;
	$post_id = get_the_ID();
	load_template(CP_PLUGIN_SECTIONS_DIR . 'single-part.php');
get_footer(); ?>