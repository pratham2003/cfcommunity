<?php get_template_part('templates/head'); ?>

<body <?php body_class(); ?>>
  <!--[if lt IE 8]><div class="alert alert-warning"><?php _e('You are using an <strong>outdated</strong> browser. Please <a href="http://browsehappy.com/">upgrade your browser</a> to improve your experience.', 'roots'); ?></div><![endif]-->

  <?php
    do_action('get_header');
    // Use Bootstrap's navbar if enabled in config.php
    if (current_theme_supports('bootstrap-top-navbar')) {
      get_template_part('templates/header-top-navbar');
    } else {
      get_template_part('templates/header');
    }
  ?>

  <div class="container">
    <div class="content row row-offcanvas row-offcanvas-left">
      <div class="main col-xs-12 col-sm-9" role="main">

		 <div id="buddypress">


			<?php buddyboss_global_search_load_template("results-page-content"); ?>

		</div>

      </div><!-- /.main -->
    </div><!-- /.content -->
  </div><!-- /.wrap -->

  <?php get_template_part('templates/footer'); ?>

</body>
</html>


