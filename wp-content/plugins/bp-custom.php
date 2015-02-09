<?php
define ( 'BP_AVATAR_DEFAULT', 'https://cfcommunity.net/wp-content/themes/cfcommunity/assets/img/avatar-member.jpg' );

//Remove Gravatar Calls
if ( is_main_site($blog_id) ) {
	add_filter('bp_core_fetch_avatar_no_grav', '__return_true');
}

//Keep the community private
function cfc_walled_garden()
{
	global $bp;

	if ( bp_is_register_page() || bp_is_activation_page() || is_page() )
	return;

	if( ! bp_is_blog_page() && ! is_user_logged_in() )
		bp_core_redirect( $bp->root_domain .'/' );
}
add_action( 'bp_init', 'cfc_walled_garden' );

/* Prevent RSS Feeds */
function cut_nonreg_visitor_rss_feed() {
	if ( !is_user_logged_in() ) {
		remove_action( 'bp_actions', 'bp_activity_action_sitewide_feed' ,3      );
		remove_action( 'bp_actions', 'bp_activity_action_personal_feed' ,3      );
		remove_action( 'bp_actions', 'bp_activity_action_friends_feed'  ,3      );
		remove_action( 'bp_actions', 'bp_activity_action_my_groups_feed',3      );
		remove_action( 'bp_actions', 'bp_activity_action_mentions_feed' ,3      );
		remove_action( 'bp_actions', 'bp_activity_action_favorites_feed',3      );
		remove_action( 'groups_action_group_feed', 'groups_action_group_feed',3 );
	}
}
add_action('init', 'cut_nonreg_visitor_rss_feed');


//Remove the BP widgets everywhere except the main BuddyPress site.
add_action( 'bp_include', 'cfc_include_remove_widgets', 5 ); // priority is crucial
function cfc_include_remove_widgets() {
  if ( !bp_is_root_blog() && !bp_is_multiblog_mode() )
  	remove_all_actions( 'bp_register_widgets' );
} 


// Redirect users from BP signup to Gravity Forms sign-up
function register_redirect()
{	 
	if( bp_is_register_page() ) :
	bp_core_redirect( $bp->root_domain .'/become-a-member' );
	endif;
}
add_action( 'bp_init', 'register_redirect' );

//Block certain activity types from being added
function bp_activity_dont_save( $activity_object ) {
$exclude = array(
        'updated_profile',
        'new_member',
        'new_avatar',
        'friendship_created',
        'joined_group'
    );

// if the activity type is empty, it stops BuddyPress BP_Activity_Activity::save() function
if( in_array( $activity_object->type, $exclude ) )
$activity_object->type = false;

}
add_action('bp_activity_before_save', 'bp_activity_dont_save', 10, 1 );

function myprofile_shortcode() {
  $myprofileurl = bp_get_loggedin_user_link() ;
  return $myprofileurl;
}
add_shortcode('myprofileurl', 'myprofile_shortcode');

/*
If you are using BP 2.1+, this will insert a Country selectbox.
Add the function to bp-custom.php and then visit .../wp-admin/users.php?page=bp-profile-setup
*/
 
function bp_add_custom_country_list() {
 
  if ( !xprofile_get_field_id_from_name('Country') && 'bp-profile-setup' == $_GET['page'] ) {
 
		$country_list_args = array(
		       'field_group_id'  => 1,
		       'name'            => 'Country',
		       'description'	 => 'Please select your country',
		       'can_delete'      => false,
		       'field_order' 	 => 2,
		       'is_required'     => false,
		       'type'            => 'selectbox',
		       'order_by'	 => 'custom'
 
		);
 
		$country_list_id = xprofile_insert_field( $country_list_args );
 
		if ( $country_list_id ) {
 
			$countries = array(
				"United States",			
				"Afghanistan",
				"Albania",
				"Algeria",
				"Andorra",
				"Angola",
				"Antigua and Barbuda",
				"Argentina",
				"Armenia",
				"Australia",
				"Austria",
				"Azerbaijan",
				"Bahamas",
				"Bahrain",
				"Bangladesh",
				"Barbados",
				"Belarus",
				"Belgium",
				"Belize",
				"Benin",
				"Bhutan",
				"Bolivia",
				"Bosnia and Herzegovina",
				"Botswana",
				"Brazil",
				"Brunei",
				"Bulgaria",
				"Burkina Faso",
				"Burundi",
				"Cambodia",
				"Cameroon",
				"Canada",
				"Cape Verde",
				"Central African Republic",
				"Chad",
				"Chile",
				"China",
				"Colombi",
				"Comoros",
				"Congo (Brazzaville)",
				"Congo",
				"Costa Rica",
				"Cote d'Ivoire",
				"Croatia",
				"Cuba",
				"Cyprus",
				"Czech Republic",
				"Denmark",
				"Djibouti",
				"Dominica",
				"Dominican Republic",
				"East Timor (Timor Timur)",
				"Ecuador",
				"Egypt",
				"El Salvador",
				"Equatorial Guinea",
				"Eritrea",
				"Estonia",
				"Ethiopia",
				"Fiji",
				"Finland",
				"France",
				"Gabon",
				"Gambia, The",
				"Georgia",
				"Germany",
				"Ghana",
				"Greece",
				"Grenada",
				"Guatemala",
				"Guinea",
				"Guinea-Bissau",
				"Guyana",
				"Haiti",
				"Honduras",
				"Hungary",
				"Iceland",
				"India",
				"Indonesia",
				"Iran",
				"Iraq",
				"Ireland",
				"Israel",
				"Italy",
				"Jamaica",
				"Japan",
				"Jordan",
				"Kazakhstan",
				"Kenya",
				"Kiribati",
				"Korea, North",
				"Korea, South",
				"Kuwait",
				"Kyrgyzstan",
				"Laos",
				"Latvia",
				"Lebanon",
				"Lesotho",
				"Liberia",
				"Libya",
				"Liechtenstein",
				"Lithuania",
				"Luxembourg",
				"Macedonia",
				"Madagascar",
				"Malawi",
				"Malaysia",
				"Maldives",
				"Mali",
				"Malta",
				"Marshall Islands",
				"Mauritania",
				"Mauritius",
				"Mexico",
				"Micronesia",
				"Moldova",
				"Monaco",
				"Mongolia",
				"Morocco",
				"Mozambique",
				"Myanmar",
				"Namibia",
				"Nauru",
				"Nepal",
				"Netherlands",
				"New Zealand",
				"Nicaragua",
				"Niger",
				"Nigeria",
				"Norway",
				"Oman",
				"Pakistan",
				"Palau",
				"Panama",
				"Papua New Guinea",
				"Paraguay",
				"Peru",
				"Philippines",
				"Poland",
				"Portugal",
				"Qatar",
				"Romania",
				"Russia",
				"Rwanda",
				"Saint Kitts and Nevis",
				"Saint Lucia",
				"Saint Vincent",
				"Samoa",
				"San Marino",
				"Sao Tome and Principe",
				"Saudi Arabia",
				"Senegal",
				"Serbia and Montenegro",
				"Seychelles",
				"Sierra Leone",
				"Singapore",
				"Slovakia",
				"Slovenia",
				"Solomon Islands",
				"Somalia",
				"South Africa",
				"Spain",
				"Sri Lanka",
				"Sudan",
				"Suriname",
				"Swaziland",
				"Sweden",
				"Switzerland",
				"Syria",
				"Taiwan",
				"Tajikistan",
				"Tanzania",
				"Thailand",
				"Togo",
				"Tonga",
				"Trinidad and Tobago",
				"Tunisia",
				"Turkey",
				"Turkmenistan",
				"Tuvalu",
				"Uganda",
				"Ukraine",
				"United Arab Emirates",
				"United Kingdom",
				"Uruguay",
				"Uzbekistan",
				"Vanuatu",
				"Vatican City",
				"Venezuela",
				"Vietnam",
				"Yemen",
				"Zambia",
				"Zimbabwe"
			);
			
			foreach (  $countries as $country ) {
				
				xprofile_insert_field( array(
					'field_group_id'	=> 1,
					'parent_id'		=> $country_list_id,
					'type'			=> 'option',
					'name'			=> $country,
					'option_order'   	=> $i++
				));
				
			}
 
		}
	}
}
add_action('bp_init', 'bp_add_custom_country_list');

function bp_add_custom_state_list() {
 
  if ( !xprofile_get_field_id_from_name('State') && 'bp-profile-setup' == $_GET['page'] ) {
 
		$state_list_args = array(
		       'field_group_id'  => 1,
		       'name'            => 'State',
		       'description'	 => 'Please select your state',
		       'can_delete'      => false,
		       'field_order' 	 => 2,
		       'is_required'     => false,
		       'type'            => 'selectbox',
		       'order_by'	 => 'custom'
 
		);
 
		$state_list_id = xprofile_insert_field( $state_list_args );
 
		if ( $state_list_id ) {
 
			$states = array(
			    "Alabama",
			    "Alaska",
			    "Arizona",
			    "Arkansas",
			    "California",
			    "Colorado",
			    "Connecticut",
			    "Delaware",
			    "Florida",
			    "Georgia",
			    "Hawaii",
			    "Idaho",
			    "Illinois",
			    "Indiana",
			    "Iowa",
			    "Kansas",
			    "Kentucky",
			    "Louisiana",
			    "Maine",
			    "Maryland",
			    "Massachusetts",
			    "Michigan",
			    "Minnesota",
			    "Mississippi",
			    "Missouri",
			    "Montana",
			    "Nebraska",
			    "Nevada",
			    "New Hampshire",
			    "New Jersey",
			    "New Mexico",
			    "New York",
			    "North Carolina",
			    "North Dakota",
			    "Ohio",
			    "Oklahoma",
			    "Oregon",
			    "Pennsylvania",
			    "Rhode Island",
			    "South Carolina",
			    "South Dakota",
			    "Tennessee",
			    "Texas",
			    "Utah",
			    "Vermont",
			    "Virginia",
			    "Washington",
			    "West Virginia",
			    "Wisconsin",
				"Wyoming"
			);
			
			foreach (  $states as $state ) {
				
				xprofile_insert_field( array(
					'field_group_id'	=> 1,
					'parent_id'		=> $state_list_id,
					'type'			=> 'option',
					'name'			=> $state,
					'option_order'   	=> $i++
				));
				
			}
 
		}
	}
}
add_action('bp_init', 'bp_add_custom_state_list');

function bp_add_custom_language_list() {
 
  if ( !xprofile_get_field_id_from_name('language') && 'bp-profile-setup' == $_GET['page'] ) {
 
		$language_list_args = array(
		       'field_group_id'  => 1,
		       'name'            => 'Language',
		       'description'	 => 'Please choose your preferred language. We use this information to display CFCommunity in your language*. Want to add your language? <a target="_blank" href="http://cfcommunity.net/groups/translators/">Help us!</a>. 
*: please note not all languages are supported yet.',
		       'can_delete'      => false,
		       'field_order' 	 => 4,
		       'is_required'     => false,
		       'type'            => 'selectbox',
		       'order_by'	 => 'custom'
 
		);
 
		$language_list_id = xprofile_insert_field( $language_list_args );
 
		if ( $language_list_id ) {
 
			$languages = array(
				"English",
				"Nederlands"
			);
			
			foreach (  $languages as $language ) {
				
				xprofile_insert_field( array(
					'field_group_id'	=> 1,
					'parent_id'		=> $language_list_id,
					'type'			=> 'option',
					'name'			=> $language,
					'option_order'   	=> $i++
				));
				
			}
 
		}
	}
}
//add_action('bp_init', 'bp_add_custom_language_list');

/**
 * Hook bp_init as we are using BuddyPress data to set the component id
 * But you could hook init if you don't need buddypress()->blogs->id
 */
function mars_video_register_post_type() {
	global $mars;

	$rewrite_slug = 'video';

	if ( ! empty( $mars['rewrite_slug'] ) ) {
		$rewrite_slug = trim( $mars['rewrite_slug'] );
	}

	/**
	 * Post Type activities should work fine except for one thing :
	 * displaying the activity options of dropdown filters in front end and back end
	 * when multisite config and in the case the post type is set within a subsite but *not* in the main site.
	 * 
	 * So if you're using the video post type on the main site, i suggest you add these lines into the bp-custom.php file
	 * and remove it from the theme as you won't need them anymore.
	 * 
	 * If that's a trouble, simply edit your register_post_type() function within your theme by adding the code between :
	 */
	/* BuddyPress */
	/**
	 * The only problem then is that you won't have the dropdown filters populated unless the theme is active on the main site ;)
	 */


	/**
	 * These arguments are optionals and help you adjust
	 * the component id eg: groups / blogs / activity...
	 * the action id
	 * the contexts, whether to display the option in the dropdown filters (activity directory, member's page, groups page..)
	 * the position helps you ajust the order. here i will make sure Video will be listed just after Posts
	 * 
	 * Of course if the post type is not defined on main site, you don't need this.
	 */ 
	$activity_extra_args = array(
		'component_id' => buddypress()->blogs->id,
 		'action_id'    => 'new_video',
		'contexts'     => array( 'activity', 'member' ), // not in the single Group dropdown
		'position'     => 40,
	);

	$mars_video_args = array(
		'label'           => __('Videos','mars'),
		'description'     => '',
		'public'          => true,
		'has_archive'	  =>true,
		'show_ui'         => true,
		'show_in_menu'    => true,
		'capability_type' => 'post',
		'map_meta_cap'    => true,
		'hierarchical'    => false,
		'rewrite'         => array(
			'slug' => $rewrite_slug,
			'with_front'  => true
		),
		'query_var'       => true,
		'supports'        => array( 
			'title',
			'editor',
			'publicize',
			'comments',
			'thumbnail',
			'author',
			'post-formats',
			/* BuddyPress */
			'buddypress-activity'
			/* BuddyPress */
		),
		'labels' => array (
			'name'                     => 'Videos',
			'singular_name'            => __( 'Videos',                                                       'mars' ),
			'menu_name'                => __( 'Videos',                                                       'mars' ),
			'add_new'                  => __( 'Add Videos',                                                   'mars' ),
			'add_new_item'             => __( 'Add New Videos',                                               'mars' ),
			'edit'                     => __( 'Edit',                                                         'mars' ),
			'edit_item'                => __( 'Edit Videos',                                                  'mars' ),
			'new_item'                 => __( 'New Videos',                                                   'mars' ),
			'view'                     => __( 'View Videos',                                                  'mars' ),
			'view_item'                => __( 'View Videos',                                                  'mars' ),
			'search_items'             => __( 'Search Videos',                                                'mars' ),
			'not_found'                => __( 'No Videos Found',                                              'mars' ),
			'not_found_in_trash'       => __( 'No Videos Found in Trash',                                     'mars' ),
			'parent'                   => __( 'Parent Videos',                                                'mars' ),
			/* BuddyPress */
			'bp_activity_admin_filter' => __( 'New video published',                                          'mars' ),
			'bp_activity_front_filter' => __( 'Videos',                                                       'mars' ),
			'bp_activity_new_post'     => __( '%1$s posted a new <a href="%2$s">video</a>',                   'mars' ),
			'bp_activity_new_post_ms'  => __( '%1$s posted a new <a href="%2$s">video</a>, on the site %3$s', 'mars' ),
			/* BuddyPress */
		),
		/* BuddyPress */
		'bp_activity' => $activity_extra_args,
		/* BuddyPress */
	);

	register_post_type( 'video', $mars_video_args ); 
}
add_action( 'bp_init', 'mars_video_register_post_type' );

function at_add_custom_thumbnail( $content, $matches, $args ) {

    // we don't want images for blog comments
    if ( 'new_video' == $args['type'] ) {
     
	    $post_id = $args['secondary_item_id'];

	    $attachments = get_posts( array( 'post_type' => 'attachment', 'numberposts' => 1, 'post_status' => null, 'order'=> 'DESC', 'post_mime_type' => 'image', 'post_parent' => $post_id ) );

	    // post has thumbnail so use that
	    if ( has_post_thumbnail( $post_id ) )
	        $image_url = get_the_post_thumbnail( $post_id, 'activity-stream' );
	    elseif ( $attachments )
	        $image_url = wp_get_attachment_image( $attachments[0]->ID, 'activity-stream' );
	    else
	        return $content;
	    
	    $image = '<a href="' . get_permalink( $post_id ) . '">' . $image_url . '</a>';

	    // strip out the img bp uses & other html tags    
	    $content = strip_tags( $content );
	    
	    // prepend our thumbnail to the content
	    $content = $image . $content;
	    
	    return $content;

     }

}
add_filter( 'bp_activity_thumbnail_content_images', 'at_add_custom_thumbnail', 10, 3 );

/**
 * Adds a "New Content" activity dropdown filter.
 *
 * This filters the activity loop by new blog posts, blog comments and forum
 * topics.
 */
function activity_dropdown_filter_forum_activity() {
        $types = 'bbp_topic_create,new_forum_topic';
?>
        <option value="<?php esc_attr_e( $types ); ?>">Forum Activity</option>
<?php
}
add_action( 'bp_activity_filter_options', 'activity_dropdown_filter_forum_activity' );

/**
 * Adds a "New Content" activity dropdown filter.
 *
 * This filters the activity loop by new blog posts, blog comments and forum
 * topics.
 */
function activity_dropdown_filter_blog_activity() {
        $types = 'new_blog_post,new_blog_comment,activity_rss_item,groups_rss_item';
?>
        <option value="<?php esc_attr_e( $types ); ?>">Blog Activity</option>
<?php
}
add_action( 'bp_activity_filter_options', 'activity_dropdown_filter_blog_activity' );
?>