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

      // JavaScript to be fired on the home page
      new WOW(
      {
        offset: 0
      }
      ).init();

      jQuery('.litebox,.rtmp_link_preview_container a[href*="youtube.com"],.rtmp_link_preview_container a[href*="vimeo.com"]').liteBox({
        revealSpeed: 400,
        background: 'rgba(0,0,0,.8)'
      });

      //Add extra class with image to videos
      jQuery('.rtmp_link_preview_container a[href*="youtube.com"] img,.rtmp_link_preview_container a[href*="vimeo.com"] img').wrap("<div class='video-image'></div>");

      //Hide Unstyled Flash
      $('.cf-search-fields').removeClass('js-flash');
      $('#quick-menu-wrap').removeClass('js-flash');
      $('#dropdown-filter').removeClass('js-flash');
      $('#whats-new-js-wrap').removeClass('js-flash');

      if ($("html").hasClass('touch') ) {
        FastClick.attach(document.body);
      }

      $('.about-menu li').removeClass('active');

      //Hide header on down scroll
      $(".navbar-fixed-top").headroom({
        "tolerance": 5,
        "offset": 50
      });

      // Member Directory Message
      if (!$.cookie('member-alert-message')) {
          $( "#member-welcome-message" ).show();
          $("#expand-hidden").click(function() {
              $( "#member-welcome-message" ).slideUp( "slow" );
              // set the cookie for 24 hours
              var date = new Date();
              date.setTime(date.getTime() + 1024 * 60 * 60 * 1000);
              $.cookie('member-alert-message', true, { expires: date });
          });
      }

     // group Directory Message
      if (!$.cookie('group-alert-message')) {
          $( "#group-welcome-message" ).show();
          $("#expand-hidden").click(function() {
              $( "#group-welcome-message" ).slideUp( "slow" );
              // set the cookie for 24 hours
              var date = new Date();
              date.setTime(date.getTime() + 1024 * 60 * 60 * 1000);
              $.cookie('group-alert-message', true, { expires: date });
          });
      }

     // Profile Alert Message
      if (!$.cookie('profile-alert-message')) {
          $( "#profile-field-welcome-message" ).show();
          $("#expand-hidden").click(function() {
              $( "#profile-field-welcome-message" ).slideUp( "slow" );
              // set the cookie for 24 hours
              var date = new Date();
              date.setTime(date.getTime() + 1024 * 60 * 60 * 1000);
              $.cookie('profile-alert-message', true, { expires: date });
          });
      }

      // Enable Read More text for Group Description
      $('#item-meta,.item-desc').readmore({
        speed: 75,
        maxHeight: 100,
        embedCSS: false,
        moreLink: '<a href="#">Read More <i class="fa fa-sort-desc"></i></a>',
        lessLink: '<a href="#"><i class="fa fa-sort-asc"></i></a>'
      });

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


      //Activity Fade
      $('#whats-new').focus(function() {
        $('#whats-new-submit').fadeIn();
      });

      //See if a user has a filter enabled
      $('#activity-filter-by,#activity-filter-select').on('change', function() {
        if(this.value === '-1' || this.value === 0) {
          // Everything is selected 
          $("#activity-filter-notice").removeClass('visible').hide();
        } else {
          // Filter is on
          $("#activity-filter-notice").addClass('visible');
          var text = $("#activity-filter-by option[value='"+$(this).val()+"']").html();
          $("#activity-filter-notice span").html(text);
        }
      }).trigger('change');

      // Reset value on click

      $('#reset').click(function(){
          $('#activity-filter-by').val('-1').trigger('change');
      });


      //Move upload photo button on Link/Video click
      $('.bpfb_toolbar_container a').click(function() {
        $('#rtmedia-add-media-button-post-update').animate({marginTop: '-62px'});
      });

      $('#bpfb_cancel_action').click(function() {
        $('#rtmedia-add-media-button-post-update').animate({marginTop: '12px'});
      });

      //Add extra class on Blog Template selection
        $('.blog_template-option input').click(function () {
            $('input:not(:checked)').parent().removeClass("style1");
            $('input:checked').parent().addClass("style1");
        });
        $('input:checked').parent().addClass("style1");

    }
  },
  // Home page
  home: {
    init: function() {

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
  jQuery(".navbar-nav li a,a.pin-group,.about-menu li a").tooltip({
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
jQuery('#whats-new,#invite-anyone-custom-message,#invite-anyone-custom-subject,#invite-anyone-email-addresses').autosize();

// Add Button Bootstrap Styles
jQuery('.widget_bps_widget submit,.bbp-submit-wrapper button').addClass('btn btn-success');
jQuery('.create-blog .main submit').addClass('btn btn-lg btn-success');

// Add Form Styling
jQuery('#buddypress textarea,.cf-search-fields select').addClass('form-control');
jQuery('.text-input input[type=text]').addClass('form-control');
jQuery('.dropdown-input select').addClass('selectpicker');
jQuery('input[type=text],input[type=password]').addClass('form-control');
jQuery('#whats-new-textarea #whats-new, #invite-anyone-by-email input[type=text]').addClass('form-control');

//Add Table Styling
jQuery('table').addClass('table table-striped');

// //Turn Selectbox into pretty dropdown
jQuery(".relationship-cf-field select").selectpicker({style: 'btn-hg btn-success', menuStyle: 'dropdown-inverse'});


jQuery(".directory.activity #activity-filter-select select, #profile-quick-menu select").selectpicker({style: 'btn-hg btn-primary', menuStyle: 'dropdown-inverse'});
