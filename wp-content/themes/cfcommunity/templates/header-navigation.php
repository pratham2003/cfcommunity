<?php
global $current_user;
get_currentuserinfo(); // wordpress global variable to fetch logged in user info

$userID = $current_user->ID; // logged in user's ID
$currentlang = get_user_meta($userID, 'user_language', true);
?>

<nav class="collapse navbar-collapse" role="navigation">

<?php if ( $currentlang=="en_US" || !$currentlang=="nl_NL" ): ?>
  <?php
    if (has_nav_menu('primary_navigation')) :
      wp_nav_menu(array('theme_location' => 'primary_navigation', 'menu_class' => 'nav navbar-nav'));
    endif;
  ?>

  <?php endif; ?>

<?php if($currentlang=="nl_NL"): ?>
 
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