<?php get_template_part('templates/head'); ?>
<body <?php body_class(); ?>>

  <!--[if lt IE 8]><div class="alert alert-warning"><?php _e('You are using an <strong>outdated</strong> browser. Please <a href="http://browsehappy.com/">upgrade your browser</a> to improve your experience.', 'roots'); ?></div><![endif]-->

  <?php
      do_action('get_header');
      get_template_part('templates/header-top-navbar');
  ?>


  <!-- Fold section -->
  <?php
  get_template_part( 'templates/home/fold' );
  ?>    
  <!-- Fold section End -->

  <!-- Live with CF section -->
  <div class="container-fluid life-with-cf">
    <?php
      get_template_part( 'templates/home/life-with-cf' );
    ?>    
  </div>
  <!-- Live with CF section end -->

  <!-- Feature overview section -->
  <div class="container-fluid feature-overview">
    <?php
      get_template_part( 'templates/home/feature-overview' );
    ?> 
  </div>
  <!-- Feature Overview end -->

  <!-- Why needed section -->
  <div class="container-fluid why-needed">
    <?php
      get_template_part( 'templates/home/why-needed' );
    ?> 
  </div>
  <!-- Why needed end -->


  <?php get_template_part('templates/footer'); ?>

</body>
</html>
