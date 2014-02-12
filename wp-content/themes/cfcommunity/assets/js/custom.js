        // Add Button Bootstrap Styles
        jQuery('.button,.bp-title-button').addClass('btn btn-primary');
        jQuery('.generic-button').addClass('btn');
         jQuery('#item-buttons .generic-button').addClass('');
        jQuery('.bp-title-button').removeClass('bp-title-button');
        jQuery('input[type=submit]').addClass('btn btn-wide btn-info btn-embossed');

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
        jQuery("select").selectpicker({style: 'btn-hg btn-primary', menuStyle: 'dropdown-inverse'});


        // Auto Resize BuddyPress Activity Update Box
        // jQuery(function(){
        //        jQuery('div#whats-new-textarea textarea#whats-new.form-control').autosize({append: "\n"});
        // });//

        // Replace Whats up Text

            //jQuery(".inner-sidebar").niceScroll();
