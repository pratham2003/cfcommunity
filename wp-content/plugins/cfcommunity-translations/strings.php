/Users/Bowromir/webserver/cfcommunity.dev/wp-content/themes/cfcommunity-support/front-page.php:
    6        <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
    7          <h2 class="section-title page-top">
    8:         <span><?php _e('Your gift will help those affected by Cystic Fibrosis', 'cfctranslation'); ?></span>
    9          </h2>
   10        </div>
   ..
   13          <p class="lead">
   14            <span>
   15:             <?php _e('CFCommunity is an online meeting place created by people with CF, for people with CF.', 'cfctranslation'); ?>   
   16  
   17              <br><br>
   18:             <?php _e('We want to make it easy for those who live or work with CF everyday to connect.', 'cfctranslation'); ?>   
   19  
   20              <br><br>
   21:             <?php _e('Learn more about us or...', 'cfctranslation'); ?>  </p>
   22:             <a href="<?php echo bp_get_signup_page()?>" class="btn-block btn btn-success" type="button"><i class="fa fa-user"></i> <?php _e('Sign up for CFCommunity', 'cfctranslation'); ?> </a>
   23            </span>
   24          </p>
   ..
   45        <div class="row">
   46          <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
   47:           <h2 class="section-title grey"><?php _e('What role does Cystic Fibrosis play in your life?', 'cfctranslation'); ?></h2>
   48          </div>
   49        </div>

/Users/Bowromir/webserver/cfcommunity.dev/wp-content/themes/cfcommunity-support/includes/edd-functions.php:
   21  <?php echo edd_get_price_name() ?>
   22  
   23:  <p><?php _e('Thank you for wanting to donate to CFCommunity! Before you continue please check the amount you would like to donate.', 'cfctranslation'); ?>  </p>
   24  
   25  <?php }
   ..
   30   */ 
   31  function cfc_edd_choose_payment() { ?>
   32:  <p class="secure-payment"><i class="fa fa-lock"></i> <?php _e('Please choose one of our <strong>secure</strong> payment methods below.', 'cfctranslation'); ?>  </p>
   33  <?php }
   34  add_action( 'edd_before_purchase_form', 'cfc_edd_choose_payment', 11 );
   ..
   38   */ 
   39  function cfc_edd_purchase_form_before_submit() { ?>
   40:  <p><?php _e('Click on the button below to make your donation', 'cfctranslation'); ?>  </p>
   41  <?php }
   42  add_action( 'edd_purchase_form_before_submit', 'cfc_edd_purchase_form_before_submit', 1000 );
   ..
   67  ?>
   68  <p id="edd_final_total_wrap">
   69:  <strong><?php _e( 'Donation Total:', 'cfctranslation' ); ?></strong>
   70   <span class="edd_cart_amount" data-subtotal="<?php echo edd_get_cart_subtotal(); ?>" data-total="<?php echo edd_get_cart_subtotal(); ?>"><?php edd_cart_total(); ?></span>
   71  </p>

/Users/Bowromir/webserver/cfcommunity.dev/wp-content/themes/cfcommunity/bpgt/causes/groups/index.php:
    1  
    2  <div class="intro-text">
    3: <div id="expand-hidden"><a href="#"><i class="fa fa-times"></i> <?php _e( 'Hide this Message', 'cfctranslation' ); ?></a></div>
    4      <img class="avatar user-2-avatar avatar-80 photo" src="<?php echo get_stylesheet_directory_uri(); ?>/assets/img/cfchimp-large.png" />
    5      <p>
    6          <?php printf( __( "Welcome to the Causes directory %s! Through this page we try to make it as easy as possible for you to find and follow the causes that might be interesting for you. If you would like to stay receive updates from a cause simply click the 'Follow' button on a Cause page. 
    7  
    8: ", 'cfctranslation' ), bp_get_user_firstname() ); ?>
    9      </p>
   10  </div>
   ..
   14   <div id="group-dir-search" class="dir-search" role="search">
   15     <form id="search-groups-form" method="get" action="">
   16:    <label><input type="text" placeholder="<?php _e('Search Causes...', 'cfctranslation'); ?> " id="groups_search" name="s" class="form-control"></label>
   17     <input type="submit" value="Search" name="groups_search_submit" id="groups_search_submit">
   18   </form>
   ..
   25     <div class="item-list-tabs" role="navigation">
   26       <ul>
   27:        <li class="selected" id="groups-all"><a href="<?php bp_groups_directory_permalink(); ?>"><?php printf( __( 'All Causes <span>%s</span>', 'cfctranslation' ), bp_get_total_group_count() ); ?></a></li>
   28  
   29         <?php if ( is_user_logged_in() && bp_get_total_group_count_for_user( bp_loggedin_user_id() ) ) : ?>
   30:          <li id="groups-personal"><a href="<?php echo bp_loggedin_user_domain() . bp_get_groups_slug() . '/my-groups/'; ?>"><?php printf( __( 'Causes you Follow <span>%s</span>', 'cfctranslation' ), bp_get_total_group_count_for_user( bp_loggedin_user_id() ) ); ?></a></li>
   31         <?php endif; ?>
   32  
   ..
   44  
   45           <select id="groups-order-by">
   46:            <option value="active"><?php _e( 'Last Active', 'cfctranslation' ); ?></option>
   47:            <option value="popular"><?php _e( 'Most Followers', 'cfctranslation' ); ?></option>
   48             <option value="newest"><?php _e( 'Newly Created', 'buddypress' ); ?></option>
   49             <option value="alphabetical"><?php _e( 'Alphabetical', 'buddypress' ); ?></option>

/Users/Bowromir/webserver/cfcommunity.dev/wp-content/themes/cfcommunity/buddypress/parts/activity-tabs.php:
    4        <?php $userLink = bp_get_loggedin_user_link();?>
    5        <?php echo bp_core_get_user_displayname( bp_loggedin_user_id() );?><br>
    6:      <a class="no-ajax" href="<?php echo $userLink; ?>"><?php _e('View Profile.', 'cfctranslation'); ?>  </a>
    7   </div><!-- #item-header-avatar -->
    8  <?php endif; ?>
    .
   53  
   54   <div id="user-sidebar-groups" class="widget">
   55:    <i class="fa fa-life-ring"></i> <?php _e('Your Groups ', 'cfctranslation'); ?>  <a href="http://cfcommunity.net/members/cfcommunity/groups/"><?php _e('Manage', 'cfctranslation'); ?></a>
   56   </div><!-- #item-header-avatar -->
   57  

/Users/Bowromir/webserver/cfcommunity.dev/wp-content/themes/cfcommunity/buddypress/parts/activity-tour.php:
   10          array(
   11            'element' => "#user-sidebar-menu",
   12:           'title' => __('Welcome to CFCommunity', 'cfctranslation'),
   13:           'content' => __('Hi there! I have not seen you before! If you click the "Next" button I will give you a quick tour of the CFCommunity homepage. Go ahead :-)', 'cfctranslation'),
   14            'placement' => "right"
   15          ),
   16          array(
   17            'element' => "#bp-adminbar-notifications-menu",
   18:           'title' => __('Notifications', 'cfctranslation'),
   19:           'content' => __('Sweet! So when something happens on CFCommunity that deserves your attention you will get a notification.', 'cfctranslation'),  
   20            'placement' => "bottom"
   21          ),
   22          array(
   23            'element' => "#bp-profile-menu",
   24:           'title' => __('Your Profile Menu', 'cfctranslation'),
   25:           'content' => __('You can also quickly navigate to any profile page by using this menu. For instance to read your messages or quickly browse to one of your discussion groups.', 'cfctranslation'), 
   26            'placement' => "bottom"
   27          ),
   28          array(
   29            'element' => "#activity-stream",
   30:           'title' => __('The latest news', 'cfctranslation'),  
   31:           'content' => __('The news stream shows you the activity from your friends on CFCommunity. It might be a bit empty since you might not not made any friends yet. Poor you!', 'cfctranslation'), 
   32            'placement' => "top"
   33          ),
   34          array(
   35            'element' => "#activity-all",
   36:           'title' => __('News Filters', 'cfctranslation'), 
   37:           'content' => __('Luckily there is a solution for that! Simply use the filters to quickly change the activity that is being displayed. Try it by clicking on the "All Members" button now!', 'cfctranslation'), 
   38            'placement' => "right"
   39          ),
   40          array(
   41            'element' => "#dropdown-filter",
   42:           'title' => __('Show your interests', 'cfctranslation'),  
   43:           'content' => __('You might just want to see certain types of activity. For example you might just want to see Blog posts from your friends. You can use the dropdown to select any type of activity you would like to see and your stream will be instantly updated!', 'cfctranslation'),  
   44            'placement' => "left"
   45          ),
   46          array(
   47            'element' => "#whats-new-form",
   48:           'title' => __('Write your first update!', 'cfctranslation'), 
   49:           'content' => __('You can write an update to your friends on CFCommunity by typing something in this box.Go ahead, write your first message!', 'cfctranslation'), 
   50            'placement' => "bottom",
   51            'backdrop' => true
   ..
   53          array(
   54            'element' => "#aw-whats-new-submit",
   55:           'title' => __('Publish away!', 'cfctranslation'),  
   56:           'content' =>__('This is the end of my tour! Go ahead and press the "Post Update" button now. I hope you like CFCommunity and you might see me monkeying around on other pages on CFCommunity to show you around! <br><br><strong>Click on "End the Tour" to continue</strong>', 'cfctranslation'),
   57            'placement' => "bottom",
   58            'backdrop' => true

/Users/Bowromir/webserver/cfcommunity.dev/wp-content/themes/cfcommunity/buddypress/parts/bp-member-nav.php:
   14  <?php else: ?>
   15   <li class="menu-register">
   16:    <a href="<?php echo bp_get_signup_page()?>"><i class="fa fa-user"></i> <?php _e('Register', 'cfctranslation'); ?> </a>
   17   </li>
   18  <li class="dropdown menu-groups">
   19:    <a href="/menu/" data-target="#" data-toggle="dropdown" class="dropdown-toggle"><i class="fa fa-sign-in"></i> <?php _e('Log In', 'cfctranslation'); ?>  </a>
   20     <ul class="dropdown-menu">
   21     <li>
   ..
   40       <li class="search nav">
   41       <form role="search" method="get" action="<?php echo home_url('/'); ?>">
   42:          <input type="search" value="<?php if (is_search()) { echo get_search_query(); } ?>" name="s" class="search-field form-control" placeholder="<?php _e('Search for anything on CFCommunity', 'cfctranslation'); ?>">
   43         <button type="submit" class="btn"><i class="fa fa-search"></i></button>
   44        </form>

/Users/Bowromir/webserver/cfcommunity.dev/wp-content/themes/cfcommunity/buddypress/parts/member-navigation.php:
   22                      <select name="forma" onchange="location = this.options[this.selectedIndex].value;">
   23  
   24:                     <optgroup label="<?php _e('Quick Links', 'cfctranslation'); ?>">
   25:                         <option value="<?php echo $userLink; ?>profile/edit"><?php _e('Edit Profile', 'cfctranslation'); ?></option>
   26:                         <option value="<?php echo $userLink; ?>profile/change-avatar"><?php _e('Change Avatar', 'cfctranslation'); ?></option>
   27                      </optgroup>
   28:                     <optgroup label="<?php _e('Settings', 'cfctranslation'); ?>">
   29:                         <option value="<?php echo $userLink; ?>settings"><?php _e('Email and Password settings', 'cfctranslation'); ?> </option>
   30                          <option value="<?php echo wp_logout_url( wp_guess_url() ); ?>"><?php _e('Log Out', 'buddypress'); ?>   </option>
   31                      </optgroup>

/Users/Bowromir/webserver/cfcommunity.dev/wp-content/themes/cfcommunity/buddypress/parts/page-header.php:
   32    <?php if ( wp_is_mobile() ) : ?>
   33          <button type="button" class="navbar-toggle navbar-toggle-left" data-toggle="offcanvas">
   34:         <span class="sr-only"><?php _e('Toggle Sidebar', 'cfctranslation'); ?> </span>
   35:           <i class="fa fa-bars"></i><?php _e('More about', 'cfctranslation'); ?> <?php bp_displayed_user_username(); ?>
   36        </button>
   37    <div class="mobile-avatar">
   ..
   55          <button type="button" class="navbar-toggle navbar-toggle-left" data-toggle="offcanvas">
   56          <span class="sr-only">Toggle Sidebar</span>
   57:           <i class="fa fa-bars"></i><?php _e('Group Navigation', 'cfctranslation'); ?> 
   58        </button>
   59    <div class="mobile-avatar">

/Users/Bowromir/webserver/cfcommunity.dev/wp-content/themes/cfcommunity/buddypress/parts/profile-tour.php:
   11          array(
   12            'element' => "#field_4",
   13:           'title' => __('Welcome to CFCommunity', 'cfctranslation'),
   14:           'content' => __('Hi there! I have not seen you before! If you click the "Next" button I will give you a quick tour of the CFCommunity homepage. Go ahead :-)', 'cfctranslation'),
   15            'placement' => "right"
   16          ),
   17          array(
   18            'element' => "#bp-adminbar-notifications-menu",
   19:           'title' => __('Notifications', 'cfctranslation'),
   20:           'content' => __('Sweet! So when something happens on CFCommunity that deserves your attention you will get a notification.', 'cfctranslation'),  
   21            'placement' => "bottom"
   22          ),
   23          array(
   24            'element' => "#bp-profile-menu",
   25:           'title' => __('Your Profile Menu', 'cfctranslation'),
   26:           'content' => __('You can also quickly navigate to any profile page by using this menu. For instance to read your messages or quickly browse to one of your discussion groups.', 'cfctranslation'), 
   27            'placement' => "bottom"
   28          ),
   29          array(
   30            'element' => "#activity-stream",
   31:           'title' => __('The latest news', 'cfctranslation'),  
   32:           'content' => __('The news stream shows you the activity from your friends on CFCommunity. It might be a bit empty since you might not not made any friends yet. Poor you!', 'cfctranslation'), 
   33            'placement' => "top"
   34          ),
   35          array(
   36            'element' => "#activity-all",
   37:           'title' => __('News Filters', 'cfctranslation'), 
   38:           'content' => __('Luckily there is a solution for that! Simply use the filters to quickly change the activity that is being displayed. Try it by clicking on the "All Members" button now!', 'cfctranslation'), 
   39            'placement' => "right"
   40          ),
   41          array(
   42            'element' => "#dropdown-filter",
   43:           'title' => __('Show your interests', 'cfctranslation'),  
   44:           'content' => __('You might just want to see certain types of activity. For example you might just want to see Blog posts from your friends. You can use the dropdown to select any type of activity you would like to see and your stream will be instantly updated!', 'cfctranslation'),  
   45            'placement' => "left"
   46          ),
   47          array(
   48            'element' => "#whats-new-form",
   49:           'title' => __('Write your first update!', 'cfctranslation'), 
   50:           'content' => __('You can write an update to your friends on CFCommunity by typing something in this box.Go ahead, write your first message!', 'cfctranslation'), 
   51            'placement' => "bottom",
   52            'backdrop' => true
   ..
   54          array(
   55            'element' => "#aw-whats-new-submit",
   56:           'title' => __('Publish away!', 'cfctranslation'),  
   57:           'content' =>__('This is the end of my tour! Go ahead and press the "Post Update" button now. I hope you like CFCommunity and you might see me monkeying around on other pages on CFCommunity to show you around! <br><br><strong>Click on "End the Tour" to continue</strong>', 'cfctranslation'),
   58            'placement' => "bottom",
   59            'backdrop' => true

/Users/Bowromir/webserver/cfcommunity.dev/wp-content/themes/cfcommunity/front-page.php:
    6       <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
    7        <h2 class="section-title page-top">
    8:         <span><?php _e('A community for people affected by Cystic Fibrosis!', 'cfctranslation'); ?></span>
    9        </h2>
   10      </div>
   ..
   13        <p class="lead">
   14          <span>
   15:           <?php _e('CFCommunity is an online meeting place created by people with CF, for people with CF.', 'cfctranslation'); ?>   
   16  
   17            <br><br>
   18:           <?php _e('We want to make it easy for those who live or work with CF everyday to connect.', 'cfctranslation'); ?>   
   19  
   20            <br><br>
   21:           <?php _e('Learn more about us or...', 'cfctranslation'); ?>  </p>
   22:           <a href="<?php echo bp_get_signup_page()?>" class="btn-block btn btn-success" type="button"><i class="fa fa-user"></i> <?php _e('Sign up for CFCommunity', 'cfctranslation'); ?> </a>
   23          </span>
   24        </p>
   ..
   45    <div class="row">
   46      <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
   47:       <h2 class="section-title grey"><i class="fa fa-reply"></i> <?php _e('What role does Cystic Fibrosis play in your life?', 'cfctranslation'); ?></h2>
   48      </div>
   49    </div>
   ..
   53        <div class="col-xs-4 col-sm-4 col-md-3 col-lg-3">
   54          <ul id="myTab" class="nav nav-tabs">
   55:           <li class="active"><a href="#noimage" data-toggle="tab"><i class="fa fa-chevron-circle-right"></i> <?php _e('I have CF', 'cfctranslation'); ?></a></li>
   56:           <li class=""><a href="#leftimage" data-toggle="tab"><i class="fa fa-chevron-circle-right"></i> <?php _e('My family member/partner has CF', 'cfctranslation'); ?> </a></li>
   57:           <li class=""><a href="#1-2-col" data-toggle="tab"><i class="fa fa-chevron-circle-right"></i> <?php _e('I work with CF', 'cfctranslation'); ?>  </a></li>
   58:           <li class=""><a href="#rightimage" data-toggle="tab"><i class="fa fa-chevron-circle-right"></i> <?php _e('I have a CF related cause', 'cfctranslation'); ?></a></li>
   59          </ul>
   60        </div>
   ..
   68  
   69                    <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
   70:                    <h3><?php _e('Breaking news: Life with CF is different!', 'cfctranslation'); ?> </h3>
   71                   </div>
   72  
   ..
   74  
   75  
   76:                   <p><strong><?php _e('Just kidding, you already knew that.</strong> There is always a bunch of stuff going on that only others with CF truly understand. With CFCommunity we have created a place where we can hang out and talk about all the things that make life with CF different/awesome/lame/special.', 'cfctranslation'); ?>  
   77                    </p>
   78  
   79                    <p>
   80:                     <?php _e('Hanging out online is not as good as throwing real life rave parties all across the world, but sadly that plan was scrapped early in our brainstorming process (segregation, pseudomonas blablabla). Instead we wasted blood, sweat and salty salty tears on creating an online meeting place where we would like to hang out and meet others with CF. ', 'cfctranslation'); ?>  
   81                    </p>
   82                    <p>
   83:                     <?php _e('<strong>On CFCommunity you can create your profile, start a blog, talk to others with CF and share pictures of your cat nebulizing (that is a joke. do not actually do that!)</strong>. It is kinda similar to Facebook but 100% less lame and with 100% more privacy! It is just for people affected by CF and we have made it easy to find and connect with people in similar situations as you.', 'cfctranslation'); ?> 
   84                    </p>
   85  
   86                    <p>
   87  
   88:                     <?php _e('We hope to see you on CFCommunity soon!', 'cfctranslation'); ?>  <br><br>
   89  
   90                      <?php _e('A virtual pseudomonas-free hug from,<br>
   91:                     Bowe, Sarah & the rest of CFCommunity Team', 'cfctranslation'); ?> 
   92  
   93  
   ..
  107  
  108                    <a href="http://cfcommunity.net/register" class="btn-block btn btn-success" type="button">
  109:                     <i class="fa fa-user"></i> <?php _e('Sign up for CFCommunity', 'cfctranslation'); ?> 
  110                    </a>
  111  
  ...
  127  
  128  
  129:             <h3 class="big-title"><?php _e('A meeting place for everyone affected by Cystic Fibrosis ', 'cfctranslation'); ?></h3>
  130  
  131                   </div>
  ...
  133                   <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
  134  
  135:                   <p><strong><?php _e('A baby sister. an older brother. a wife. a girlfriend. All the people in the pictures above deal with CF every day.', 'cfctranslation'); ?>  </strong></p>
  136  
  137:                   <p><?php _e('CFCommunity is not just for people who have CF. We have also made it for you; someone who loves someone with CF. A (grand)child, a sibling or your partner, we want to make it easy for you to connect with others in the same situation.', 'cfctranslation'); ?> </p>
  138  
  139:                   <p><?php _e('Every person who becomes a member of our community fills in their relationship with CF. We use this information (along with your location and age) to let you easily search for and connect with people on CFCommunity! ', 'cfctranslation'); ?></p>
  140  
  141:                   <p><?php _e('By using our Discussion Groups you can talk about specific subjects in-depth and in private. Finally if you need further support or want to stay up to date about all the medical news, our Causes page lets you easily find and connect with all the CF related initiatives out there! ', 'cfctranslation'); ?> </p>
  142  
  143:                   <p><?php _e('We hope to see you on CFCommunity soon!', 'cfctranslation'); ?> <br><br>
  144  
  145:                   <?php _e('Bowe, Sarah & the rest of the CFCommunity Team<', 'cfctranslation'); ?></p>
  146  
  147                   <span>Join us and...</span>
  148  
  149                    <a href="http://cfcommunity.net/register" class="btn-block btn btn-success" type="button">
  150:                     <i class="fa fa-user"></i> <?php _e('Sign up for CFCommunity', 'cfctranslation'); ?> 
  151                    </a>
  152  
  ...
  165  
  166                <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
  167:                    <h3><?php _e('Bringing all the Cystic Fibrosis related causes from across the world under one roof', 'cfctranslation'); ?>  </h3>
  168                </div>
  169  
  ...
  171  
  172  
  173:                   <p><strong><?php _e('By creating a page for your cause on CFCommunity you can share news, post updates, brochures and connect with the people in our community.</strong> If you currently manage a Facebook Page for your cause you get the idea (with the big difference that we actually care about your cause and you reaching your audience without having to pay! ;-) ', 'cfctranslation'); ?>  </p> 
  174  
  175:                   <p><?php _e('<strong>With CFCommunity we are not trying to get in the way of any of the existing CF related causes.</strong> It is our goal to make it as easy as possible for our community members to find and follow the causes that are important to them. If you would like to directly engage with our community you can choose to open a discussion forum but this is completely optional. ', 'cfctranslation'); ?>  </p> 
  176  
  177:                   <p><?php _e('You can learn more about starting a Cause Page on CFCommunity by clicking here!', 'cfctranslation'); ?></p> 
  178  
  179                  </div>

/Users/Bowromir/webserver/cfcommunity.dev/wp-content/themes/cfcommunity/lib/buddypress/bp-cover-photo.php:
    5      $profile_link = bp_loggedin_user_domain() . $bp->profile->slug . '/';
    6      $args = array(
    7:                 'name' => __('Profile Cover Photo','cfctranslation'),
    8                  'slug' => 'change-cover',
    9                  'parent_url' => $profile_link,
   ..
   25  
   26  function page_title(){
   27:         echo __('Change your Profile Cover Photo','cfctranslation');
   28  }
   29  
   ..
   35          ?>
   36          </div>
   37:         <span class="small-text"><?php _e('Upload an image you would like to use as your Cover Photo! As soon as you have uploaded your photo you can leave this page!', 'cfctranslation'); ?>  </span>
   38  
   39          <?php

/Users/Bowromir/webserver/cfcommunity.dev/wp-content/themes/cfcommunity/lib/buddypress/bp-hooks.php:
   20      ?>
   21      <div id="member-welcome-message" class="intro-text">
   22:     <div id="expand-hidden"><a href="#"><i class="fa fa-times"></i> <?php _e( 'Hide this Message', 'cfctranslation' ); ?></a></div>
   23      <img class='avatar user-2-avatar avatar-80 photo'src='<?php echo get_stylesheet_directory_uri(); ?>/assets/img/cfchimp-large.png'/>
   24      <p>
   25:        <?php _e( 'Hi! Welcome to our Member Directory! You can use our awesome search options to quickly find people in similar situations as you. Click on the "Show Search" buttons to see all the available search options! Have fun and make some new friends!', 'cfctranslation' ); ?>
   26      </p>
   27      </div>
   28  
   29      <h3 id="search-header">
   30:         <span><i class="fa fa-search"></i> <?php _e( 'Start searching for people by clicking on a search category below', 'cfctranslation' ); ?> </span>
   31      </h3>
   32      <div class="cf-search-fields js-flash">
   ..
   38      ?>
   39      <div id="group-welcome-message" class="intro-text">
   40:     <div id="expand-hidden"><a href="#"><i class="fa fa-times"></i> <?php _e( 'Hide this Message', 'cfctranslation' ); ?></a></div>
   41          <img class="avatar user-2-avatar avatar-80 photo" src="<?php echo get_stylesheet_directory_uri(); ?>/assets/img/cfchimp-large.png" />
   42          <p>
   43:             <?php printf( __( "Hey %s, below you will find an overview of all the Discussion Groups on CFCommunity. Feel free to join the ones you find interesting! You can  search and filter groups by name, spoken language and interests. Interested in starting your own discussion group? Press the 'Create a Group' button! Have fun!", 'cfctranslation' ), bp_get_user_firstname() ); ?>
   44          </p>
   45      </div>
   ..
   51      ?>
   52      <div id="activity-filter-notice">
   53:         <i class="fa fa-lightbulb-o"></i> <?php _e('You are filtering your newsfeed to only see <span></span>. <a href="#" id="reset">Click here to reset</a>', 'cfctranslation'); ?>    
   54      </div>
   55      <?php
   ..
   60      ?>
   61      <div class="abuse-message">
   62:     <?php _e( 'PS: We have a zero tolerance policy regarding misuse of our search functionality. Using our search feature to contact our members for commercial/fundraising or any unwanted messages, would make us very sad. Please read our community guidelines carefully or get in touch if you have requests/questions!', 'cfctranslation' ); ?>
   63      </div>
   64      </div><!-- extra div? -->
   ..
   72          <img class="avatar user-2-avatar avatar-80 photo" src="<?php echo get_stylesheet_directory_uri(); ?>/assets/img/cfchimp-large.png" />
   73          <p>
   74:         <?php _e( 'So you want to create a discussion group? That is awesome! Before you do please <strong>make sure that there is no existing discussion group in your language that talks about the same subject</strong>. This way we keep the group directory nice and clean!. Click <a href="http://cfcommunity.net/groups">here</a> and use the "Search" field at the top right of the page to check for existing groups! <3', 'cfctranslation' ); ?>
   75          </p>
   76      </div>
   ..
   84          <img class="avatar" src="<?php echo get_stylesheet_directory_uri(); ?>/assets/img/cfchimp-large.png" />
   85          <p>
   86:             <h4><?php _e('I am super excited that you want to create your site on CFCommunity!', 'cfctranslation'); ?></h4>
   87          </p>
   88  
   89          <ul>
   90:             <li> <?php printf( __( "<strong>Creating a your site on CFCommunity is great for blogging, your cause, a fundraiser or just a personal website.</strong><br>Your site is powered by %s, the most popular publishing platform in the world.",'cfctranslation' ), '<a class="litebox" href="https://www.youtube.com/watch?v=G6xWZoCFmOw">WordPress <i class="fa fa-video-camera"></i></a>' );?></li>
   91:             <li><?php _e('<strong>Your site will be linked to your CFCommunity profile</strong><br>This means that every time you publish a post a new update will be posted to your stream for your friends to see.', 'cfctranslation'); ?></li>
   92  
   93              <li><?php _e('<strong>Your site will be added to our Sites directory</strong><br>
   94:                     Our members can easily find your site and even subscribe to it (so they will receive updates when you publish something new!).', 'cfctranslation'); ?></li>
   95  
   96              <li><?php _e('<strong>Your site is 100% yours</strong><br>
   ..
  100          <?php bp_loggedin_user_avatar( 'width=' . bp_core_avatar_thumb_width() . '&height=' . bp_core_avatar_thumb_height() ); ?>
  101          <div class="intro-note">
  102:          <h4><?php _e('"But CFChimp, I already have a website?!"', 'cfctranslation'); ?></h4>
  103:        <?php printf( __( "No worries! If you already have a website and want to keep writing there, you can link you site to your profile by going here:<br><br><a href='%s'><i class='fa fa-link'></i> Link my existing site to my CFCommunity profile</a>", 'cfctranslation' ), bp_loggedin_user_domain() . $bp->profile->slug . '/settings/rss-feed/' );?>
  104         </div>
  105  
  ...
  108  
  109      <div class="create-site-instructions">
  110:         <h3><?php printf( __( "Ready %s? Let's make your site!", 'cfctranslation' ), bp_get_user_firstname() );?></h3>
  111  
  112          <?php _e( 'Please fill in the url and the name of your site below. For example:' ); ?>
  113          <br>
  114  
  115:         <?php printf( __( "Site Domain: <strong>'bananarecipes'</strong>", 'cfctranslation' ), bp_get_user_firstname() ); ?>
  116          <br>
  117  
  118:         <?php printf( __( "Site Title: <strong>'Amazing Banana Recipes from %s'</strong>", 'cfctranslation' ), bp_get_user_firstname() ); ?>
  119      </div>
  120      <?php
  ...
  127          <img class="avatar user-2-avatar avatar-80 photo" src="<?php echo get_stylesheet_directory_uri(); ?>/assets/img/cfchimp-large.png" />
  128          <p>
  129:         <?php _e( 'Almost done! Below you can choose a theme for your new site. We have made these to help you get started quickly with different types of blogs/sites. Pick the one that fits your needs the best! Not sure what to pick or want to change themes later? <strong>You can change themes at any time once you have created your site!</strong>', 'cfctranslation' ); ?>
  130          </p>
  131      </div>
  ...
  137      ?>
  138      <div class="intro-text final">
  139:         <?php _e( 'All done! Press the big button below to create your super awesome site!', 'cfctranslation' ); ?>
  140      </div>
  141      <?php
  ...
  146      ?>
  147      <div class="intro-text important fixed">
  148:         <?php printf( __( "All done! We have created your new site succesfully! We also sent you an email with your site details. Your website is listed on your profile and you can see it here <a href='%s'>View My Site</a>!", 'cfctranslation' ), bp_loggedin_user_domain() . $bp->profile->slug . 'blogs/' );?>
  149          <br><br>
  150:         <?php printf( __( "Now that you have created your site, you should totally join our <a href='%s'>Bloggers Group</a> where we have weekly blogging subjects and your fellow bloggers can help you get started with your site! <strong>Have fun!</strong>", 'cfctranslation' ),  'http://cfcommunity.net/groups/cfcommunity-bloggers/' );?>
  151      </div>
  152      <?php
  ...
  158      <br>
  159      <strong>
  160:     <?php _e( 'By creating a new group you get a little bit of responsibility to keeping things friendly and awesome here on CFCommunity. Please take a few moments to read our', 'cfctranslation' ); ?>
  161:     <a href="http://cfcommunity.net/house-rules/#discussion-groups"><?php _e( 'Guidelines for Group administrators', 'cfctranslation' ); ?> :-)</a>
  162      </strong>
  163      <?php
  ...
  214      <img class='avatar user-2-avatar avatar-80 photo'src='<?php echo home_url(); ?>/wp-content/themes/cfcommunity/assets/img/cfchimp-large.png'/>
  215         <p>
  216:        <?php printf( __( "Hey there!, you have not completed your profile yet. This is probably because you have created your account through Facebook. Please <a href='%s'>Complete Your Profile</a> and I will go back to eating those calorie rich bananas!", 'cfctranslation' ), bp_loggedin_user_domain() . $bp->profile->slug . '/edit/group/2/' );?>
  217            </p>
  218      </div>
  ...
  223  // Filter RT Media Add Photos
  224  function cfc_rtmedia_attach_file_message_custom( $label ) {
  225:     return __( '<i class="fa fa-picture-o"></i> Add Photo(s)', 'cfctranslation' );
  226  }
  227  add_filter( 'rtmedia_attach_file_message', 'cfc_rtmedia_attach_file_message_custom' );

/Users/Bowromir/webserver/cfcommunity.dev/wp-content/themes/cfcommunity/lib/edd-functions.php:
   21  <?php echo edd_get_price_name() ?>
   22  
   23:  <p><?php _e('Thank you for wanting to donate to CFCommunity! Before you continue please check the amount you would like to donate.', 'cfctranslation'); ?>  </p>
   24  
   25  <?php }
   ..
   30   */ 
   31  function cfc_edd_choose_payment() { ?>
   32:  <p class="secure-payment"><i class="fa fa-lock"></i> <?php _e('Please choose one of our <strong>secure</strong> payment methods below.', 'cfctranslation'); ?>  </p>
   33  <?php }
   34  add_action( 'edd_before_purchase_form', 'cfc_edd_choose_payment', 11 );
   ..
   38   */ 
   39  function cfc_edd_purchase_form_before_submit() { ?>
   40:  <p><?php _e('Click on the button below to make your donation', 'cfctranslation'); ?>  </p>
   41  <?php }
   42  add_action( 'edd_purchase_form_before_submit', 'cfc_edd_purchase_form_before_submit', 1000 );
   ..
   67  ?>
   68  <p id="edd_final_total_wrap">
   69:  <strong><?php _e( 'Donation Total:', 'cfctranslation' ); ?></strong>
   70   <span class="edd_cart_amount" data-subtotal="<?php echo edd_get_cart_subtotal(); ?>" data-total="<?php echo edd_get_cart_subtotal(); ?>"><?php edd_cart_total(); ?></span>
   71  </p>

/Users/Bowromir/webserver/cfcommunity.dev/wp-content/themes/cfcommunity/lib/init.php:
  150  
  151  function cfc_excerpt_more($more) {
  152:   return '<a class="read-more-link" href="' . get_permalink() . '"><i class="fa fa-arrow-circle-right"></i> ' . __('Read this post', 'cfctranslation') . '</a>';
  153  }
  154  add_filter('excerpt_length', 'cfc_excerpt_length');

/Users/Bowromir/webserver/cfcommunity.dev/wp-content/themes/cfcommunity/template-supporter.php:
   12  <div class="intro"><?php _e('Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. 
   13  Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo 
   14: consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur.  ', 'cfctranslation'); ?>
   15  </div>
   16  

/Users/Bowromir/webserver/cfcommunity.dev/wp-content/themes/cfcommunity/templates/footer.php:
    7         </div>
    8         <h4 class="text-center ">
    9:          <span><?php _e('Support our Cause', 'cfctranslation'); ?> </span>
   10         </h4>
   11         <p class="text-center">
   12:          <?php _e('With CFCommunity we are creating an international community for all people affected by Cystic Fibrosis, and we are completely dependent on donations.', 'cfctranslation'); ?>  
   13:          <a href="http://cfcommunity.net/support-us/"><i class="fa fa-arrow-circle-right"></i> <?php _e('Be awesome, Support us!', 'cfctranslation'); ?></a>
   14         </p>
   15       </div>
   ..
   19         </div>
   20         <h4 class="text-center ">
   21:          <span><?php _e('Meet the Team', 'cfctranslation'); ?> </span>
   22         </h4>
   23         <p class="text-center">
   24:          <?php _e('Without these lovely people CFCommunity would be nowhere. Come check out their pretty faces and even get to know them a little!', 'cfctranslation'); ?> 
   25:          <a href="http://cfcommunity.net/team/"><i class="fa fa-arrow-circle-right"></i> <?php _e('Check out the Team!', 'cfctranslation'); ?> </a>
   26         </p>
   27       </div>
   ..
   31         </div>
   32         <h4 class="text-center ">
   33:          <span><?php _e('We Love Our Supporters', 'cfctranslation'); ?>  </span>
   34         </h4>
   35         <p class="text-center">
   36:          <?php _e('Where would we be without them? We could not be more grateful to those who are making CFCommunity possible with their donations and sponsorships.', 'cfctranslation'); ?> 
   37:          <a href="http://cfcommunity.net/support-us/"><i class="fa fa-arrow-circle-right"></i> <?php _e('See our Heroes', 'cfctranslation'); ?>  </a>
   38         </p>
   39       </div>
   ..
   43         </div>
   44         <h4 class="text-center">
   45:          <span><?php _e('We are so social!', 'cfctranslation'); ?> </span>
   46         </h4>
   47         <p class="text-center">
   48:          <?php _e('As much as we love hanging out here, you can also find us at other places. Connect with us by clicking on one of those pretty icons below!', 'cfctranslation'); ?>  
   49           <div class="social-links">
   50             <a target="_blank" href="https://www.facebook.com/pages/CFCommunitynet-Where-people-with-Cystic-Fibrosis-meet/176854133478"><i class="fa fa-facebook-square fa social-fb"></i></a>
   ..
   56     </div>
   57     <div class="col-sm-12 bottom-links text-center">
   58:      <?php printf( __( "Powered by %s and made with lots of <span class='fa fa-heart'></span> by the CFCommunity<span>",'cfctranslation' ), '<a href="http://wordpress.org"><i class="fa fa-wordpress"></i>ordPress</a></span>' );?>
   59     </div>
   60  </footer>

268 matches across 21 files
