<?php
/*
Template Name: About Pages
*/
?>

<?php get_template_part('templates/page', 'header'); ?>
<?php get_template_part('templates/content', 'page'); ?>

<strong>Learn more about us...</strong>
<br>
<div class="about-menu bottom">
	<?php
	  wp_nav_menu(array('theme_location' => 'about_menu', 'menu_class' => 'nav nav-pills nav-justified'));
	?>
</div>