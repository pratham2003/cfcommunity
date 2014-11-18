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

function cf_member_warning() { { ?>
    <div class="abuse-message">
 <?php _e('PS: We have a zero tolerance policy regarding misuse of our search functionality. Using our search feature to contact our members for commercial/fundraising or any unwanted messages, would make us very sad. Please read our community guidelines carefully or get in touch if you have requests/questions!', 'cfcommunity'); ?>

    </div>
</div>
<?php }}
add_action('bp_before_directory_members_tabs','cf_member_warning');

function cf_group_creation_intro() { { ?>
    <div class="intro">
        <?php _e('This is an explanation for the create groups functionality', 'roots'); ?>
    </div>
<?php }}
add_action('bp_before_group_details_creation_step','cf_group_creation_intro');

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

?>