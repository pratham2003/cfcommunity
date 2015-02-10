<nav class="collapse navbar-collapse" role="navigation">
  <?php 
  //Are we on the main site? Show a multilingual menu
        global $blog_id;
        global $current_user;
        get_currentuserinfo(); // wordpress global variable to fetch logged in user info
        $userID = $current_user->ID; // logged in user's ID
        $currentlang = get_user_meta($userID, 'user_language', true);
        $user_lang = 'primary_navigation_' . $currentlang; 
    ?>

      <?php if ( $currentlang=="en_US" || ! is_user_logged_in() ): ?>
          <?php
              wp_nav_menu(array('theme_location' => 'primary_navigation', 'menu_class' => 'nav navbar-nav'));
          ?>
      <?php endif; ?>

      <?php if($currentlang=="nl_NL"): ?> 
          <?php
            if (has_nav_menu($user_lang)) :
              wp_nav_menu(array('theme_location' => $user_lang, 'menu_class' => 'nav navbar-nav'));
            endif;
          ?>
      <?php endif; ?>

  <?php
  //Is BuddyPress active? Out the member navigation
    if ( function_exists( 'bp_is_member' ) ) {
      get_template_part( 'buddypress/parts/bp-member-nav' );
    }
  ?>    
</nav>