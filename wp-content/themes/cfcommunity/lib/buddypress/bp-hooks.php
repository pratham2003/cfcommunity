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
       <?php _e('Hi! Welcome to our Member Directory! You can use our awesome search options to quickly find people in similar situations as you. Click on the "Show Search" buttons to see all the available search options! Have fun and make some new friends!', 'cfcommunity'); ?>  
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
       <?php printf( __( "Hey %s, below you will find an overview of all the Discussion Groups on CFCommunity. Feel free to join the ones you find interesting! You can  search and filter groups by name, spoken language and interests. Interested in starting your own discussion group? Press the 'Create a Group' button! Have fun!", 'cfcommunity' ), bp_get_user_firstname() );?>
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
        <?php _e('So you want to create a discussion group? That is awesome! Before you do please <strong>make sure that there is no existing discussion group in your language that talks about the same subject</strong>. This way we keep the group directory nice and clean!. Click <a href="http://cfcommunity.net/groups">here</a> and use the "Search" field at the top right of the page to check for existing groups! <3', 'cfcommunity'); ?>
    </div>
<?php }}
add_action('bp_before_group_details_creation_step','cf_group_creation_intro');

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

if (  bp_get_profile_field_data( 'field=Your Relationship with CF&user_id='.$user_id) == FALSE && !bp_is_profile_edit() )  : ?>
    <div id="complete-profile-message" class="intro-text important">
    
    <img class='avatar user-2-avatar avatar-80 photo'src='<?php echo home_url(); ?>/wp-content/themes/cfcommunity/assets/img/cfchimp-large.png'/>
       <?php printf( __( "Hey there!, you have not completed your profile yet. This is probably because you have created your account through Facebook. Please <a href='%s'>Complete Your Profile</a> and I will go back to eating those calorie rich bananas!", 'cfcommunity' ), bp_loggedin_user_domain() . $bp->profile->slug . '/edit/group/2/' );?>
    </div>
<?php endif ?>
<?php }}
add_action('wp_head','cf_profile_field_intro_text');
?>