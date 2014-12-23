<?php
// Groups
function cf_group_intro() { { ?>
    <?php if ( ! is_mobile_device() ): ?>
    <div class="intro intro-img">
        <img src="<?php echo get_stylesheet_directory_uri(); ?>/assets/img/cf-community-groups.jpg" alt="cc-license" title="cc-license" />
    </div>
    <?php endif; ?>
<?php }}
add_action('bp_before_directory_groups','cf_group_intro');

function cf_member_intro_text() { { ?>
    <div id="member-welcome-message" class="intro-text">
    <div id="expand-hidden"><a href="#"><i class="fa fa-times"></i> Hide this Message</a></div>
    <img class='avatar user-2-avatar avatar-80 photo'src='<?php echo home_url(); ?>/wp-content/themes/cfcommunity/assets/img/cfchimp-large.png'/>
    <p>
       <?php _e('Hi! Welcome to our Member Directory! You can use our awesome search options to quickly find people in similar situations as you. Click on the "Show Search" buttons to see all the available search options! Have fun and make some new friends!', 'cfcommunity'); ?>
    </p>
    </div>

    <h3 id="search-header">
        <span><i class="fa fa-search"></i> <?php _e('Start searching for people by clicking on a search category below', 'cfcommunity'); ?> </span>
    </h3>
    <div class="cf-search-fields js-flash">
<?php }}
add_action('bp_before_directory_members_tabs','cf_member_intro_text', 1);


function cf_group_intro_text() { { ?>
    <div id="group-welcome-message" class="intro-text">
    <div id="expand-hidden"><a href="#"><i class="fa fa-times"></i> Hide this Message</a></div>
    <img class='avatar user-2-avatar avatar-80 photo'src='<?php echo home_url(); ?>/wp-content/themes/cfcommunity/assets/img/cfchimp-large.png'/>
    <p>
       <?php printf( __( "Hey %s, below you will find an overview of all the Discussion Groups on CFCommunity. Feel free to join the ones you find interesting! You can  search and filter groups by name, spoken language and interests. Interested in starting your own discussion group? Press the 'Create a Group' button! Have fun!", 'cfcommunity' ), bp_get_user_firstname() );?>
    </p>
    </div>
<?php }}
add_action('bp_before_directory_groups','cf_group_intro_text');

function cf_member_warning() { { ?>
    <div class="abuse-message">
 <?php _e('PS: We have a zero tolerance policy regarding misuse of our search functionality. Using our search feature to contact our members for commercial/fundraising or any unwanted messages, would make us very sad. Please read our community guidelines carefully or get in touch if you have requests/questions!', 'cfcommunity'); ?>

    </div>
</div>
<?php }}
add_action('bp_before_directory_members_tabs','cf_member_warning');

function cf_group_creation_intro() { { ?>
    <div class="intro-text">
        <img class='avatar user-2-avatar avatar-80 photo'src='<?php echo home_url(); ?>/wp-content/themes/cfcommunity/assets/img/cfchimp-large.png'/>
           <p>
        <?php _e('So you want to create a discussion group? That is awesome! Before you do please <strong>make sure that there is no existing discussion group in your language that talks about the same subject</strong>. This way we keep the group directory nice and clean!. Click <a href="http://cfcommunity.net/groups">here</a> and use the "Search" field at the top right of the page to check for existing groups! <3', 'cfcommunity'); ?>
           </p>
    </div>
<?php }}
add_action('bp_before_group_details_creation_step','cf_group_creation_intro');

function cf_site_creation_intro() { { ?>
    <div class="intro-text">
        <img class='avatar'src='<?php echo home_url(); ?>/wp-content/themes/cfcommunity/assets/img/cfchimp-large.png'/>
             <p> <h4><?php _e('I am super excited that you want to create your site on CFCommunity!', 'cfcommunity'); ?></h4></p>

            <ul>
                <li> <?php printf( __( "<strong>Creating a your site on CFCommunity is great for blogging, your cause, a fundraiser or just a personal website.</strong><br>Your site is powered by %s, the most popular publishing platform in the world.",'cfcommunity' ), '<a class="litebox" href="https://www.youtube.com/watch?v=G6xWZoCFmOw">WordPress <i class="fa fa-video-camera"></i></a>' );?></li>
                <li><?php _e('<strong>Your site will be linked to your CFCommunity profile</strong><br>This means that every time you publish a post a new update will be posted to your stream for your friends to see.', 'cfcommunity'); ?></li>

                <li><?php _e('<strong>Your site will be added to our Sites directory</strong><br>
                    Our members can easily find your site and even subscribe to it (so they will receive updates when you publish something new!).', 'cfcommunity'); ?></li>

                <li><?php _e('<strong>Your site is 100% yours</strong><br>
                It will always remain free, without advertisements or other lame stuff. Super pinky promise!'); ?></li>
            </ul>

        <?php bp_loggedin_user_avatar( 'width=' . bp_core_avatar_thumb_width() . '&height=' . bp_core_avatar_thumb_height() ); ?>
        <div class="intro-note">
         <h4><?php _e('"But CFChimp, I already have a website?!"', 'cfcommunity'); ?></h4>
       <?php printf( __( "No worries! If you already have a website and want to keep writing there, you can link you site to your profile by going here:<br><br><a href='%s'><i class='fa fa-link'></i> Link my existing site to my CFCommunity profile</a>", 'cfcommunity' ), bp_loggedin_user_domain() . $bp->profile->slug . '/settings/rss-feed/' );?>
       </div>

    </div>


<div class="create-site-instructions">
    <h3><?php printf( __( "Ready %s? Let's make your site!", 'cfcommunity' ), bp_get_user_firstname() );?></h3>

        <?php _e('Please fill in the url and the name of your site below. For example:'); ?>
        <br>

       <?php printf( __( "Site Domain: <strong>'bananarecipes'</strong>", 'cfcommunity' ), bp_get_user_firstname() );?>
       <br>
      <?php printf( __( "Site Title: <strong>'Amazing Banana Recipes from %s'</strong>", 'cfcommunity' ), bp_get_user_firstname() );?>

</div>

<?php }}
add_action('bp_before_create_blog_content_template','cf_site_creation_intro');

function cf_site_creation_template_selection() { { ?>
    <div class="intro-text bottom">
        <img class='avatar user-2-avatar avatar-80 photo'src='<?php echo home_url(); ?>/wp-content/themes/cfcommunity/assets/img/cfchimp-large.png'/>
        <p>
        <?php _e('Almost done! Below you can choose a theme for your new site. We have made these to help you get started quickly with different types of blogs/sites. Pick the one that fits your needs the best! Not sure what to pick or want to change themes later? <strong>You can change themes at any time once you have created your site!</strong>', 'cfcommunity'); ?>
        </p>
    </div>
<?php }}
add_action('signup_blogform','cf_site_creation_template_selection', 1);

function cf_site_creation_final_step() { { ?>
    <div class="intro-text final">
        <?php _e('All done! Press the big button below to create your super awesome site!', 'cfcommunity'); ?>
    </div>
<?php }}
add_action('signup_blogform','cf_site_creation_final_step', 11);

function cf_site_creation_confirmation() { { ?>
    <div class="intro-text important fixed">

    <?php printf( __( "All done! We have created your new site succesfully! We also sent you an email with your site details. Your website is listed on your profile and you can see it here <a href='%s'>View My Site</a>!", 'cfcommunity' ), bp_loggedin_user_domain() . $bp->profile->slug . 'blogs/' );?>
    <br><br>
        <?php printf( __( "Now that you have created your site, you should totally join our <a href='%s'>Bloggers Group</a> where we have weekly blogging subjects and your fellow bloggers can help you get started with your site! <strong>Have fun!</strong>", 'cfcommunity' ),  'http://cfcommunity.net/groups/cfcommunity-bloggers/' );?>
    </div>
<?php }}
add_action('signup_finished','cf_site_creation_confirmation');

function cf_group_creation_after() { { ?>
<br> <strong>
<?php _e('By creating a new group you get a little bit of responsibility to keeping things friendly and awesome here on CFCommunity. Please take a few moments to read our', 'cfcommunity'); ?>
    <a href="http://cfcommunity.net/house-rules/#discussion-groups"><?php _e('Guidelines for Group administrators', 'cfcommunity'); ?> :-)</a>
</strong>
<?php }}
add_action('bp_after_group_details_creation_step','cf_group_creation_after');

// Members
function cf_member_intro() { { ?>
    <div class="intro intro-img">
        <img src="<?php echo get_stylesheet_directory_uri(); ?>/assets/img/cf-community-blogs.jpg" alt="cc-license" title="cc-license" />
    </div>
<?php }}
add_action('bp_before_directory_blogs_content','cf_member_intro');

function cf_language_stats()
//Redirect logged in users from homepage to activity
{
    global $bp;
    if( bp_is_groups_component() && 'translators' == bp_current_item() )
    {?>
        <div class="translation-stats">
            <div class="col-sm-7">
            <h4>About</h4>
                In this group we'll explain you exactly how you can help translate CFCommunity in your own language. Press the "Join Group" button to get started :-)

                <br>
                <br>
                <a href="https://www.youtube.com/watch?v=7gtdpnKbT10" target="_self" class="litebox">
                        Watch a quick video on how you can help us translate!
                 </a>
            </div>
           <div class="col-sm-5">
                <h4>Translation Progress</h4>
                <a href="https://www.transifex.com/projects/p/cfcommunity/">
                    <img border="0" src="https://www.transifex.com/projects/p/cfcommunity/resource/cfcommunity/chart/image_png"/>
                </a>
            </div>
        </div>
    <?}
}
add_action('bp_group_header_meta','cf_language_stats');

// Profile Edit Message
function cf_profile_field_intro_text() { { ?>
<?php
global $bp;
$user_id = $bp->loggedin_user->id;
$profile_edit_link = bp_loggedin_user_domain() . $bp->profile->slug . 'profile/edit/group/2/';

if (  bp_get_profile_field_data( 'field=Your Relationship with CF&user_id='.$user_id) == FALSE && !bp_is_profile_edit() && is_user_logged_in() )  : ?>
    <div id="complete-profile-message" class="intro-text important">

    <img class='avatar user-2-avatar avatar-80 photo'src='<?php echo home_url(); ?>/wp-content/themes/cfcommunity/assets/img/cfchimp-large.png'/>
       <p>
       <?php printf( __( "Hey there!, you have not completed your profile yet. This is probably because you have created your account through Facebook. Please <a href='%s'>Complete Your Profile</a> and I will go back to eating those calorie rich bananas!", 'cfcommunity' ), bp_loggedin_user_domain() . $bp->profile->slug . '/edit/group/2/' );?>
          </p>
    </div>
<?php endif ?>
<?php }}
add_action('wp_head','cf_profile_field_intro_text');

//Filter RT Media Add Photos
function rtmedia_attach_file_message_custom( $label ) {
    return __('<i class="fa fa-picture-o"></i> Add Photo(s)', 'cfcommunity');
}
add_filter('rtmedia_attach_file_message', 'rtmedia_attach_file_message_custom');


//Invite Anyone
function cf_invite_anyone() { { ?>
    <div class="invite-anyone-image">
        <a class="cs_import">
            <img src="<?php echo get_stylesheet_directory_uri(); ?>/assets/img/invite-anyone.png" alt="cc-license" title="cc-license" />
        </a>
    </div>
<?php }}
add_action('invite_anyone_after_addresses','cf_invite_anyone');
?>