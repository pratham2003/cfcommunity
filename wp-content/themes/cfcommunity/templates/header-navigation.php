<?php
 $currentlang = get_bloginfo('language');
?>

<nav class="collapse navbar-collapse" role="navigation">

<?php if ( $currentlang=="en-US" || !$currentlang=="nl-NL" ): ?>
  <?php
    if (has_nav_menu('primary_navigation')) :
      wp_nav_menu(array('theme_location' => 'primary_navigation', 'menu_class' => 'nav navbar-nav'));
    endif;
  ?>

  <?php endif; ?>

<?php if($currentlang=="nl-NL"): ?>
 
  <?php
    if (has_nav_menu('primary_navigation_nl')) :
      wp_nav_menu(array('theme_location' => 'primary_navigation_nl', 'menu_class' => 'nav navbar-nav'));
    endif;
  ?>

<?php endif; ?>

  <?php
    if ( function_exists( 'bp_is_member' ) ) {
      get_template_part( 'buddypress/parts/bp-member-nav' );
    }
  ?>    
</nav>