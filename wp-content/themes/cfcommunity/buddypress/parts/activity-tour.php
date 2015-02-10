<?php

  $init_data = json_encode(array(

    //REMOVE THIS BEFORE GOING LIVE

    'template' => "<div class='popover'> <div class='arrow'></div> <h3 class='popover-title'></h3> <img class='avatar user-2-avatar avatar-80 photo'src='http://cfcommunity.net/wp-content/themes/cfcommunity/assets/img/cfchimp-large.png'/><div class='popover-content'></div> <div class='popover-navigation'> <div class='btn-group'> <button class='btn btn-sm btn-default' data-role='prev'>&laquo; Prev</button> <button class='btn btn-sm btn-primary' data-role='next'>Next &raquo;</button> <button class='btn btn-sm btn-default' data-role='pause-resume' data-pause-text='Pause' data-resume-text='Resume'>Pause</button> </div> <button class='btn btn-sm btn-default' data-role='end'>End the tour</button> </div> </div>",

      'steps' => array(
        array(
          'element' => "#user-sidebar-menu",
          'title' => __('Welcome to CFCommunity', 'cfctranslation'),
          'content' => __('Hi there! I have not seen you before! If you click the "Next" button I will give you a quick tour of the CFCommunity homepage. Go ahead :-)', 'cfctranslation'),
          'placement' => "right"
        ),
        array(
          'element' => "#bp-adminbar-notifications-menu",
          'title' => __('Notifications', 'cfctranslation'),
          'content' => __('Sweet! So when something happens on CFCommunity that deserves your attention you will get a notification.', 'cfctranslation'),  
          'placement' => "bottom"
        ),
        array(
          'element' => "#bp-profile-menu",
          'title' => __('Your Profile Menu', 'cfctranslation'),
          'content' => __('You can also quickly navigate to any profile page by using this menu. For instance to read your messages or quickly browse to one of your discussion groups.', 'cfctranslation'), 
          'placement' => "bottom"
        ),
        array(
          'element' => "#activity-stream",
          'title' => __('The latest news', 'cfctranslation'),  
          'content' => __('The news stream shows you the activity from your friends on CFCommunity. It might be a bit empty since you might not not made any friends yet. Poor you!', 'cfctranslation'), 
          'placement' => "top"
        ),
        array(
          'element' => "#activity-all",
          'title' => __('News Filters', 'cfctranslation'), 
          'content' => __('Luckily there is a solution for that! Simply use the filters to quickly change the activity that is being displayed. Try it by clicking on the "All Members" button now!', 'cfctranslation'), 
          'placement' => "right"
        ),
        array(
          'element' => "#dropdown-filter",
          'title' => __('Show your interests', 'cfctranslation'),  
          'content' => __('You might just want to see certain types of activity. For example you might just want to see Blog posts from your friends. You can use the dropdown to select any type of activity you would like to see and your stream will be instantly updated!', 'cfctranslation'),  
          'placement' => "left"
        ),
        array(
          'element' => "#whats-new-form",
          'title' => __('Write your first update!', 'cfctranslation'), 
          'content' => __('You can write an update to your friends on CFCommunity by typing something in this box.Go ahead, write your first message!', 'cfctranslation'), 
          'placement' => "bottom",
          'backdrop' => true
        ),
        array(
          'element' => "#aw-whats-new-submit",
          'title' => __('Publish away!', 'cfctranslation'),  
          'content' =>__('This is the end of my tour! Go ahead and press the "Post Update" button now. I hope you like CFCommunity and you might see me monkeying around on other pages on CFCommunity to show you around! <br><br><strong>Click on "End the Tour" to continue</strong>', 'cfctranslation'),
          'placement' => "bottom",
          'backdrop' => true
        )
      )
  ));
 ?> 

<script type="text/javascript">

 var en_tour = new Tour(<?php echo $init_data ?>);

  // Initialize the tour
  en_tour.init();

  // Start the tour
  en_tour.start();

</script>
