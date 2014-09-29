<header class="navbar navbar-default navbar-fixed-top navbar-inverse" role="banner">
  <div class="container">
    <div class="navbar-header">

      <button type="button" class="navbar-toggle navbar-toggle-left" data-toggle="offcanvas">
        <span class="sr-only">Toggle Sidebar</span>
            <?php bp_loggedin_user_avatar( 'width=' . bp_core_avatar_thumb_width() . '&height=' . bp_core_avatar_thumb_height() ); ?>
          <i class="fa fa-chevron-circle-right"></i>
      </button>

      <?php if ( wp_is_mobile() ): ?>
        <div class="mobile-notifications">
          <?php cf_notifications_buddybar_menu(); ?>
        </div>
      <?php endif; ?>

      <button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-collapse">
        <span class="sr-only">Toggle navigation</span>
        <span class="icon-bar"></span>
        <span class="icon-bar"></span>
        <span class="icon-bar"></span>
      </button>

        <a class="navbar-brand" href="<?php echo home_url(); ?>/">
          <?php // bloginfo('name'); ?>
          <?php if ( is_user_logged_in() ) : ?>

          <img src="<?php echo get_stylesheet_directory_uri(); ?>/assets/img/logo-icon.png" alt="CFCommunity - Where people CF meet"/>

          <?php else: ?>

          <img src="<?php echo get_stylesheet_directory_uri(); ?>/assets/img/logo.png" alt="CFCommunity - Where people CF meet"/>

          <?php endif;?>

        </a>
    </div>

    <?php
      get_template_part( 'templates/header-navigation' );
    ?>

  </div>
</header>

