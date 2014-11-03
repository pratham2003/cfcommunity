<?php
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

//Auto accept invitations
define( 'WELCOME_PACK_AUTOACCEPT_INVITATIONS', true );

// add custom post type business to the activity stream
add_filter ( 'bp_blogs_record_post_post_types', 'activity_publish_custom_post_types',1,1 );
function activity_publish_custom_post_types( $post_types ) {
$post_types[] = 'video';
return $post_types;
}

add_filter('bp_blogs_activity_new_post_action', 'record_cpt_activity_action', 1, 3);
function record_cpt_activity_action( $activity_action, $post, $post_permalink ) {
global $bp;
if( $post->post_type == 'video' ) {

$activity_action = sprintf( __( '%1$s added the video %2$s to the <a href="http://videos.cfcommunity.net">CF Video Library</a>', 'buddypress' ), bp_core_get_userlink( (int)$post->post_author ), '<a href="' . $post_permalink . '">' . $post->post_title . '</a>', get_blog_option($blog_id, 'blogname') );

}
return $activity_action;
}

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
		       'description'	 => 'Please choose your preferred language. We use this information to display CFCommunity in your language* and put you in a special discussion group where you can talk in your native language. 
*: please note not all languages are supported yet. ',
		       'can_delete'      => false,
		       'field_order' 	 => 4,
		       'is_required'     => false,
		       'type'            => 'selectbox',
		       'order_by'	 => 'custom'
 
		);
 
		$language_list_id = xprofile_insert_field( $language_list_args );
 
		if ( $language_list_id ) {
 
			$languages = array(
				"Afaan Oromoo",
				"Afaraf",
				"Afrikaans",
				"Akan",
				"Aragonés",
				"Asụsụ Igbo",
				"Avañeẽ",
				"Avesta",
				"Aymar Aru",
				"Bahasa Indonesia",
				"Bahasa Melayu",
				"Bamanankan",
				"Basa Jawa",
				"Basa Sunda",
				"Bislama",
				"Bosanski Jezik",
				"Brezhoneg",
				"Català",
				"Chamoru",
				"Chicheŵa",
				"Chishona",
				"Corsu",
				"Cymraeg",
				"Dansk",
				"Davvisámegiella",
				"Deutsch",
				"Diné Bizaad",
				"Eesti",
				"Ekakairũ Naoero",
				"English",
				"Español",
				"Esperanto",
				"Euskara",
				"Eʋegbe",
				"Faka Tonga",
				"Fiteny Malagasy",
				"Français",
				"Frysk",
				"Fulfulde",
				"Føroyskt",
				"Gaeilge",
				"Gaelg",
				"Gagana Fa'a Samoa",
				"Galego",
				"Gjuha Shqipe",
				"Gàidhlig",
				"Gĩkũyũ",
				"Hausa",
				"Hiri Motu",
				"Hrvatski Jezik",
				"Ido",
				"Ikinyarwanda",
				"Ikirundi",
				"Interlingua",
				"Isindebele",
				"Isindebele",
				"Isixhosa",
				"Isizulu",
				"Italiano",
				"Iñupiaq",
				"Polski",
				"Kajin M̧ajeļ",
				"Kalaallisut",
				"Kanuri",
				"Kernewek",
				"Kikongo",
				"Kiswahili",
				"Kreyòl Ayisyen",
				"Kuanyama",
				"Kurdî",
				"Latine",
				"Latviešu Valoda",
				"Lietuvių Kalba",
				"Limba Română",
				"Limburgs",
				"Lingála",
				"Luganda",
				"Lëtzebuergesch",
				"Magyar",
				"Malti",
				"Nederlands",
				"Norsk",
				"Norsk Bokmål",
				"Norsk Nynorsk",
				"O'zbek",
				"Occitan",
				"Interlingue",
				"Otjiherero",
				"Owambo",
				"Português",
				"Reo Tahiti",
				"Rumantsch Grischun",
				"Runa Simi",
				"Sardu",
				"Saɯ Cueŋƅ",
				"Sesotho",
				"Setswana",
				"Siswati",
				"Slovenski Jezik",
				"Slovenčina",
				"Soomaaliga",
				"Suomi",
				"Svenska",
				"Te Reo Māori",
				"Tiếng Việt",
				"Tshiluba",
				"Tshivenḓa",
				"Twi",
				"Türkmen",
				"Türkçe",
				"Uyƣurqə",
				"Volapük",
				"Vosa Vakaviti",
				"Walon",
				"Wikang Tagalog",
				"Wollof",
				"Xitsonga",
				"Yorùbá",
				"Yângâ Tî Sängö",
				"ÍSlenska",
				"čEština",
				"ελληνικά",
				"авар мацӀ",
				"аҧсуа бызшәа",
				"башҡорт теле",
				"беларуская мова",
				"български език",
				"ирон æвзаг",
				"коми кыв",
				"Кыргызча",
				"македонски јазик",
				"монгол",
				"нохчийн мотт",
				"русский язык",
				"српски језик",
				"татар теле",
				"тоҷикӣ",
				"українська мова",
				"чӑваш чӗлхи",
				"ѩзыкъ словѣньскъ",
				"қазақ тілі",
				"Հայերեն",
				"ייִדיש",
				"עברית",
				"اردو",
				"العربية",
				"فارسی",
				"پښتو",
				"कश्मीरी",
				"नेपाली",
				"पाऴि",
				"भोजपुरी",
				"मराठी",
				"संस्कृतम्",
				"सिन्धी",
				"हिन्दी",
				"অসমীয়া",
				"বাংলা",
				"ਪੰਜਾਬੀ",
				"ગુજરાતી",
				"ଓଡ଼ିଆ",
				"தமிழ்",
				"తెలుగు",
				"ಕನ್ನಡ",
				"മലയാളം",
				"සිංහල",
				"ไทย",
				"ພາສາລາວ",
				"བོད་ཡིག",
				"རྫོང་ཁ",
				"ဗမာစာ",
				"ქართული",
				"ትግርኛ",
				"አማርኛ",
				"ᐃᓄᒃᑎᑐᑦ",
				"ᐊᓂᔑᓈᐯᒧᐎᓐ",
				"ᓀᐦᐃᔭᐍᐏᐣ",
				"ខ្មែរ"
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
add_action('bp_init', 'bp_add_custom_language_list');
?>