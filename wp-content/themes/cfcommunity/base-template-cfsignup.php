<!DOCTYPE html>
<html class="no-js" <?php language_attributes(); ?>>
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <title><?php wp_title('|', true, 'right'); ?></title>
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <?php wp_head(); ?>

  <link rel="alternate" type="application/rss+xml" title="<?php echo get_bloginfo('name'); ?> Feed" href="<?php echo esc_url(get_feed_link()); ?>">
</head>

<body <?php body_class(); ?>>

  <!--[if lt IE 8]><div class="alert alert-warning"><?php _e('You are using an <strong>outdated</strong> browser. Please <a href="http://browsehappy.com/">upgrade your browser</a> to improve your experience.', 'roots'); ?></div><![endif]-->

  <?php
    do_action('get_header');
  ?>

    <div class="container centered-col">
    <div class="content row row-offcanvas row-offcanvas-left">
      <div class="main col-md-12 col-xs-12" role="main">

        <div class="negative-row register-image">
          <img src="<?php echo get_stylesheet_directory_uri(); ?>/assets/img/register-logo.png"/> 
        </div> 
        
        <?php while (have_posts()) : the_post(); ?>
        <?php the_content(); ?>
        <?php wp_link_pages(array('before' => '<nav class="pagination">', 'after' => '</nav>')); ?>
      <?php endwhile; ?>

      </div><!-- /.main -->
    </div><!-- /.content -->
     <?php get_template_part('templates/footer'); ?>
  </div><!-- /.wrap -->

        
</body>
</html>
