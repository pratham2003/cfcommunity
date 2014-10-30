/* ========================================================================
 * DOM-based Routing
 * Based on http://goo.gl/EUTi53 by Paul Irish
 *
 * Only fires on body classes that match. If a body class contains a dash,
 * replace the dash with an underscore when adding it to the object below.
 *
 * .noConflict()
 * The routing is enclosed within an anonymous function so that you can
 * always reference jQuery with $, even when in .noConflict() mode.
 *
 * Google CDN, Latest jQuery
 * To use the default WordPress version of jQuery, go to lib/config.php and
 * remove or comment out: add_theme_support('jquery-cdn');
 * ======================================================================== */

(function($) {

// Use this variable to set up the common and page specific functions. If you
// rename this variable, you will also need to rename the namespace below.
var Roots = {
  // All pages
  common: {
    init: function() {

      if ($("html").hasClass('touch') ) {
        FastClick.attach(document.body);
      }

      //Hide header on down scroll
      $(".navbar-fixed-top").headroom({
        "tolerance": 5,
        "offset": 50
      });


      // Member Directory
      if (!$.cookie('alert-message')) {
          $( "#member-welcome-message" ).show();
          $("#expand-hidden").click(function() {
              $( "#member-welcome-message" ).slideUp( "slow" );
              // set the cookie for 24 hours
              var date = new Date();
              date.setTime(date.getTime() + 1024 * 60 * 60 * 1000);
              $.cookie('alert-message', true, { expires: date });
          });
      }

      //Improve click functionality for member search

      $('#basic-information').click(function(){
        $('#bps_auto229').toggle();
      }
      );

      $('#relationship-information').click(function(){
        $('#bps_auto333').toggle();
      }
      );

        $('#cf-information').click(function(){
        $('#bps_auto332').toggle();
      }
      );

      $('#work-information').click(function(){
        $('#bps_auto334').toggle();
      }
      );

      jQuery('.litebox,.rtmp_link_preview_container a[href*="youtube.com"],.rtmp_link_preview_container a[href*="vimeo.com"]').liteBox({
        revealSpeed: 400,
        background: 'rgba(0,0,0,.8)'
      });

      //Add extra class with image to videos
      jQuery('.rtmp_link_preview_container a[href*="youtube.com"] img,.rtmp_link_preview_container a[href*="vimeo.com"] img').wrap("<div class='video-image'></div>");

      // Responsive Videos
      $(".activity-inner").fitVids();

      //Activity Fade
      $('#whats-new').focus(function() {
        $('#whats-new-submit').fadeIn();
      });

      //Move upload photo button on Link/Video click
      $('.bpfb_toolbar_container a').click(function() {
        $('#rtmedia-add-media-button-post-update').animate({marginTop: '-62px'});
      });

      $('#bpfb_cancel_action').click(function() {
        $('#rtmedia-add-media-button-post-update').animate({marginTop: '12px'});
      });



      // ENG Activity Tour
      if($('body').is('.directory.activity.logged-in.en-US')){
        var en_tour = new Tour({

          //REMOVE THIS BEFORE GOING LIVE

          template: "<div class='popover'> <div class='arrow'></div> <h3 class='popover-title'></h3> <img class='avatar user-2-avatar avatar-80 photo'src='http://cfcommunity.net/wp-content/themes/cfcommunity/assets/img/cfchimp-large.png'/><div class='popover-content'></div> <div class='popover-navigation'> <div class='btn-group'> <button class='btn btn-sm btn-default' data-role='prev'>&laquo; Prev</button> <button class='btn btn-sm btn-primary' data-role='next'>Next &raquo;</button> <button class='btn btn-sm btn-default' data-role='pause-resume' data-pause-text='Pause' data-resume-text='Resume'>Pause</button> </div> <button class='btn btn-sm btn-default' data-role='end'>End the tour</button> </div> </div>",

          steps: [
          {
            element: "#user-sidebar-menu",
            title: "Welcome to CFCommunity!",
            content: "Hi there! I have not seen you before! If you click the 'Next' button I will give you a quick tour of the CFCommunity homepage. Go ahead :-)",
            placement: "right"
          },
          {
            element: "#bp-adminbar-notifications-menu",
            title: "Notifications",
            content: "Sweet! So when something happens on CFCommunity that deserves your attention you will get a notification.",
            placement: "bottom"
          },
          {
            element: "#bp-profile-menu",
            title: "Your Profile Menu",
            content: "You can also quickly navigate to any profile page by using this menu. For instance to read your messages or quickly browse to one of your discussion groups.",
            placement: "bottom"
          },
          {
            element: "#activity-stream",
            title: "The latest news",
            content: "The news stream shows you the activity from your friends on CFCommunity. It might be a bit empty since you might not not made any friends yet. Poor you!",
            placement: "top"
          },
          {
            element: "#activity-all",
            title: "News Filters",
            content: "Luckily there is a solution for that! Simply use the filters to quickly change the activity that is being displayed. Try it by clicking on the 'All Members' button now!",
            placement: "right"
          },
          {
            element: "#dropdown-filter",
            title: "Only show your interests",
            content: "You might just want to see certain types of activity. For example you might just want to see Blog posts from your friends. You can use the dropdown to select any type of activity you would like to see and your stream will be instantly updated!",
            placement: "left"
          },
          {
            element: "#whats-new-form",
            title: "Write your first update!",
            content: "You can write an update to your friends on CFCommunity by typing something in this box.Go ahead, write your first message!",
            placement: "bottom",
            backdrop: true,
          },
          {
            element: "#aw-whats-new-submit",
            title: "Publish away!",
            content: "This is the end of my tour! Go ahead and press the 'Post Update' button now. I hope you like CFCommunity and you might see me monkeying around on other pages on CFCommunity to show you around! <br><br><strong>Click on 'End the Tour'to continue</strong>",
            placement: "bottom",
            backdrop: true,
          }
        ]});

        // Initialize the tour
        en_tour.init();

        // Start the tour
        en_tour.start();
      }

      // NL Activity Tour
      if($('body').is('.directory.activity.logged-in.nl-NL')){
        var nl_tour = new Tour({

          template: "<div class='popover'> <div class='arrow'></div> <h3 class='popover-title'></h3> <img class='avatar user-2-avatar avatar-80 photo'src='http://cfcommunity.net/wp-content/themes/cfcommunity/assets/img/cfchimp-large.png'/><div class='popover-content'></div> <div class='popover-navigation'> <div class='btn-group'> <button class='btn btn-sm btn-default' data-role='prev'>&laquo; Prev</button> <button class='btn btn-sm btn-primary' data-role='next'>Next &raquo;</button> <button class='btn btn-sm btn-default' data-role='pause-resume' data-pause-text='Pause' data-resume-text='Resume'>Pause</button> </div> <button class='btn btn-sm btn-default' data-role='end'>End the tour</button> </div> </div>",

          steps: [
          {
            element: "#user-sidebar-menu",
            title: "Welkom bij CFCommunity!",
            content: "Hallo! I have not seen you before! If you click the 'Next' button I will give you a quick tour of the CFCommunity homepage. Go ahead :-)",
            placement: "right"
          },
          {
            element: "#bp-adminbar-notifications-menu",
            title: "Notifications",
            content: "Sweet! So when something happens on CFCommunity that deserves your attention you will get a notification.",
            placement: "bottom"
          },
          {
            element: "#bp-profile-menu",
            title: "Your Profile Menu",
            content: "You can alsp quickly navigate to any profile page by using this menu. For instance to read your messages or quickly browse to one of your discussion groups.",
            placement: "bottom"
          },
          {
            element: "#activity-stream",
            title: "The latest news",
            content: "The news stream shows you the activity from your friends on CFCommunity. It might be a bit empty since you might not not made any friends yet. Poor you!",
            placement: "top"
          },
          {
            element: "#activity-all",
            title: "News Filters",
            content: "Luckily there is a solution for that! Simply use the filters to quickly change the activity that is being displayed. Try it by clicking on the 'All Members' button now!",
            placement: "right"
          },
          {
            element: "#dropdown-filter",
            title: "Only show your interests",
            content: "You might just want to see certain types of activity. For example you might just want to see Blog posts from your friends. You can use the dropdown to select any type of activity you would like to see and your stream will be instantly updated!",
            placement: "left"
          },
          {
            element: "#whats-new-form",
            title: "Write your first update!",
            content: "You can write an update to your friends on CFCommunity by typing something in this box.Go ahead, write your first message!",
            placement: "bottom",
            backdrop: true,
          },
          {
            element: "#aw-whats-new-submit",
            title: "Publish away!",
            content: "This is the end of my tour! Go ahead and press the 'Post Update' button now. I hope you like CFCommunity and you might see me monkeying around on other pages on CFCommunity to show you around! <br><br><strong>Click on 'End the Tour'to continue</strong>",
            placement: "bottom",
            backdrop: true,
          }
        ]});

        // Initialize the tour
        nl_tour.init();

        // Start the tour
        nl_tour.start();
      }

    }
  },
  // Home page
  home: {
    init: function() {
      // JavaScript to be fired on the home page
    }
  },
  // About us page, note the change from about-us to activity.
  activity: {
    init: function() {
      // JavaScript to be fired on the about us page
    }
  }
};

// The routing fires all common scripts, followed by the page specific scripts.
// Add additional events for more control over timing e.g. a finalize event
var UTIL = {
  fire: function(func, funcname, args) {
    var namespace = Roots;
    funcname = (funcname === undefined) ? 'init' : funcname;
    if (func !== '' && namespace[func] && typeof namespace[func][funcname] === 'function') {
      namespace[func][funcname](args);
    }
  },
  loadEvents: function() {
    UTIL.fire('common');

    $.each(document.body.className.replace(/-/g, '_').split(/\s+/),function(i,classnm) {
      UTIL.fire(classnm);
    });
  }
};

$(document).ready(UTIL.loadEvents);

})(jQuery); // Fully reference jQuery after this point.

  //Bootstrap tooltips
  jQuery(".navbar-nav li a").tooltip({
    placement: "bottom",
    delay: { show: 500, hide: 100 },
  });

  jQuery("#vertical-activity-tabs li a").tooltip({
  placement: "right",
  delay: { show: 500, hide: 100 },
  });

  jQuery( ".activity-like-count" ).prepend( "<i class='fa fa-thumbs-up'></i>" );



  //Offcanvas
  jQuery('[data-toggle=offcanvas]').click(function () {
    jQuery('.row-offcanvas').toggleClass('active');
    jQuery('.fa-chevron-circle-right').toggleClass('rotate');
    jQuery('body').toggleClass('off-canvas-sidebar-open');
  });




  //Close off canvas navigation when user clicks activity tab
  jQuery('.sidebar-offcanvas div.vertical-list-tabs ul li a').click(function () {
    jQuery('.row-offcanvas').delay(1500).queue(function(){
        jQuery(this).toggleClass('active').clearQueue();
    });
  });

//Add autosize for BuddyPress
jQuery('#whats-new').autosize();




// Add Button Bootstrap Styles
jQuery('.widget_bps_widget submit').addClass('btn btn-success');

//jQuery('.activity-meta .button').removeClass('btn-primary');

// Add Form Styling
jQuery('#buddypress textarea').addClass('form-control');
jQuery('.text-input input[type=text]').addClass('form-control');
jQuery('.dropdown-input select').addClass('selectpicker');
jQuery('input[type=text]').addClass('form-control');
jQuery('#whats-new-textarea #whats-new').addClass('form-control');

//Add Table Styling
jQuery('table').addClass('table table-striped');


//Add Bootstrap Labels and Badgets
// jQuery('#members-list-options a').addClass('label label-default');
// jQuery('span.activity').addClass('label label-default');
// jQuery('#object-nav span,#bp-user-navigation ul span').addClass('badge');

// //Turn Selectbox into pretty dropdown
jQuery(".ginput_container select").selectpicker({style: 'btn-hg btn-success', menuStyle: 'dropdown-inverse'});
jQuery(".widget_bps_widget select").selectpicker({style: 'btn-hg btn-info', menuStyle: 'dropdown-inverse'});

jQuery(".directory.activity #activity-filter-select select, #profile-quick-menu select,.standard-form select").selectpicker({style: 'btn-hg btn-primary', menuStyle: 'dropdown-inverse'});


