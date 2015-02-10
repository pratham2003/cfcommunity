<nav class="collapse navbar-collapse" role="navigation">
  <?php 
  //Are we on the main site? Show a multilingual menu
    if  
    ( is_main_site() ) :
        global $blog_id;
        global $current_user;
        get_currentuserinfo(); // wordpress global variable to fetch logged in user info
        $userID = $current_user->ID; // logged in user's ID
        $currentlang = get_user_meta($userID, 'user_language', true);
        $user_lang = 'primary_navigation_' . $currentlang; 
    ?>

      <?php if ( $currentlang=="en_US" || !$currentlang=="nl_NL" ): ?>
          <?php
              wp_nav_menu(array('theme_location' => 'primary_navigation_en_US', 'menu_class' => 'nav navbar-nav'));
          ?>
      <?php endif; ?>

      <?php if($currentlang=="nl_NL"): ?> 
          <?php
            if (has_nav_menu('primary_navigation_nl_NL')) :
              wp_nav_menu(array('theme_location' => 'primary_navigation_nl_NL', 'menu_class' => 'nav navbar-nav'));
            endif;
          ?>
      <?php endif; ?>
  <?php endif; ?>

  <?php 
  //Are we on the subsite? Simply output the default menu from the main site
  if ( ! is_main_site() ) : ?>
       <?php
        //store the current blog_id being viewed
        global $blog_id;
        global $current_user;
        get_currentuserinfo(); // wordpress global variable to fetch logged in user info
        $userID = $current_user->ID; // logged in user's ID
        $currentlang = get_user_meta($userID, 'user_language', true);
        $user_lang = 'primary_navigation_' . $currentlang; 

        //switch to the main blog which will have an id of 1
        switch_to_blog(1);

              wp_nav_menu(
              array(
                'theme_location' => $user_lang,
                'menu_class' => 'nav navbar-nav'
                )
              );


        //switch back to the current blog being viewed

        switch_to_blog($current_blog_id);
        ?>
  <?php endif; ?>


  <?php
  //Is BuddyPress active? Out the member navigation
    if ( function_exists( 'bp_is_member' ) ) {
      get_template_part( 'buddypress/parts/bp-member-nav' );
    }
  ?>    
</nav>