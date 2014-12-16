<?php
/**
* Functions and definitions
* by Hero Themes (http://herothemes.com)
*/


/**
* Set the content width based on the theme's design and stylesheet.
*/
if ( ! isset( $content_width ) ) $content_width = 920;

if ( ! function_exists( 'ht_theme_setup' ) ):
/**
* Sets up theme defaults and registers support for various WordPress features.
*/
function ht_theme_setup() {
		
	/**
	 * Make theme available for translation
	 * Translations can be filed in the /languages/ directory
	 */
	load_theme_textdomain( 'ht-theme', get_template_directory() . '/languages' );

	/**
	 * This theme uses wp_nav_menu() in one location.
	 */
	register_nav_menus( array(
		'primary-nav' => __( 'Primary Navigation', 'ht-theme' )
	));
	
	/*
	 * This theme styles the visual editor to resemble the theme style,
	 * specifically font, colors, and column width.
	 */
	add_editor_style( array( 'css/editor-style.css', ht_google_font_url() ) );
	
	// Adds RSS feed links to <head> for posts and comments.
	add_theme_support( 'automatic-feed-links' );

	// Enable support for Post Thumbnails
	add_theme_support( 'post-thumbnails' );
	set_post_thumbnail_size( 60, 60, true );	
	add_image_size( 'post', 920, '', true );
	add_image_size( 'post-mid', 600, '', true );
	add_image_size( 'post-small', 320, '', true );

	// This theme uses its own gallery styles.
	add_filter( 'use_default_gallery_style', '__return_false' );
	
	// Enable HTML5 markup
	add_theme_support( 'html5', array( 'comment-list', 'comment-form', 'search-form', 'gallery', 'caption' ) );

	// Enable Custom BG
	$ht_custom_background_defaults = array(
		'default-color'          => 'cccccc',
		'default-image'          => '',
		'default-repeat'         => '',
		'default-position-x'     => '',
		'wp-head-callback'       => '_custom_background_cb',
		'admin-head-callback'    => '',
		'admin-preview-callback' => ''
	);
	add_theme_support( 'custom-background', $ht_custom_background_defaults );

	// Add HT Knowledge Base Support
	add_theme_support( 'ht_knowledge_base_templates' );
	add_theme_support( 'ht_kb_category_icons' );
	//add_theme_support( 'ht_kb_category_colors' );
	
	// Add HT Voting Support
	add_theme_support( 'hero-voting-frontend-styles' );

	// Add HT Posts Widget Support
	add_theme_support( 'ht_posts_widget_styles' );

	// Add post-formats to Knowledge Base
	add_theme_support( 'post-formats', array( 'video' ) );
	

	// Add Heroic Webfonts Support
	//add_theme_support( 'ht-webfonts' );

	// Load HT Core (must be loaded last in ht_theme_setup)
	add_theme_support( 'ht-core', 'wp-thumb', 'cmb_meta_boxes', 'jquery-appear', 'font-awesome', 'custom-customizer' );
	require_if_theme_supports( 'ht-core', get_template_directory() . '/inc/ht-core/ht-core.php' );

	
}
endif; // ht_theme_setup
add_action( 'after_setup_theme', 'ht_theme_setup' );

function ht_gdrgrd() {
	remove_post_type_support( 'post', 'post-formats' );
}
//add_action( 'init', 'ht_gdrgrd' );

add_filter( 'ht_post_formats_post_types', 'ht_post_formats_post_types_supported');

function ht_post_formats_post_types_supported($supported){
	if(is_array($supported)){
		//could remove
		if (($key = array_search('post', $supported)) !== false) {
    		unset($supported[$key]);
		}
	}

	array_push($supported, 'ht_kb');

	return $supported;
}

/**
* Enqueues scripts and styles for front-end.
*/
 
require("inc/scripts.php");
require("inc/styles.php");

/**
 * Register widgetized & Add Widgets
 */
require("inc/register-sidebars.php");


// Meta Boxes Framework
require("inc/meta-boxes.php");

/**
* Add Template Navigation Functions
*/
require("inc/template-tags.php");

/**
* Comment Functions
*/
require("inc/comment-functions.php");

/**
* Theme Customizer Functions
*/
require("inc/theme-customizer.php");


// Inlcude TGM Plugins
require("inc/tgm-load-plugins.php");


// Return logo src
if ( ! function_exists( 'ht_theme_logo' ) ) {
function ht_theme_logo() {

	$ht_theme_logo = get_theme_mod( 'ht_site_logo' );
	
	if ( !empty($ht_theme_logo) ) {
		$ht_theme_logo_src = get_theme_mod( 'ht_site_logo' );
	} else {
		$ht_theme_logo_src = get_template_directory_uri()."/images/logo.png";
	}
	return $ht_theme_logo_src;

}
}

// Get responsive thumbnails, if function is available
function ht_responsive_post_thumbnail() {
	if ( function_exists( 'ht_get_responsive_post_thumbnail' ) ) {
    	ht_get_responsive_post_thumbnail();
	} else {
		the_post_thumbnail('post');
	}
}

if ( ! function_exists( 'ht_get_responsive_post_thumbnail' ) ) :
/**
* Responsive Post Thumbnails
*
*/
function ht_get_responsive_post_thumbnail() {
 
	$ht_post_thumbnail_id = get_post_thumbnail_id( get_the_id() );
	$ht_post_thumbnail_id_alt = get_post_meta($ht_post_thumbnail_id , '_wp_attachment_image_alt', true);
	$ht_post_thumbnail_320 = wp_get_attachment_image_src( $ht_post_thumbnail_id, 'width=320&height=0&crop=resize-crop' );
	$ht_post_thumbnail_480 = wp_get_attachment_image_src( $ht_post_thumbnail_id, 'width=480&height=0&crop=resize-crop' );
	$ht_post_thumbnail_600 = wp_get_attachment_image_src( $ht_post_thumbnail_id, 'width=600&height=0&crop=resize-crop' );
	$ht_post_thumbnail_920 = wp_get_attachment_image_src( $ht_post_thumbnail_id, 'width=920&height=0&crop=resize-crop' );
	$ht_post_thumbnail_1200 = wp_get_attachment_image_src( $ht_post_thumbnail_id, 'width=1200&height=800&crop=resize-crop' ); ?>
	
    <picture itemprop="image">
    <source src="<?php echo $ht_post_thumbnail_320[0]; ?>" type="image/jpeg" itemprop="thumbnailUrl" />
	    <source src="<?php echo $ht_post_thumbnail_480[0]; ?>" media="(min-width: 320px)" type="image/jpeg" />
	    <source  src="<?php echo $ht_post_thumbnail_600[0]; ?>" media="(min-width: 480px)" type="image/jpeg" />
	    <source src="<?php echo $ht_post_thumbnail_920[0]; ?>" media="(min-width: 600px)" type="image/jpeg" />
	    <source src="<?php echo $ht_post_thumbnail_1200[0]; ?>" media="(min-width: 920px)" type="image/jpeg" />
		<img src="<?php echo $ht_post_thumbnail_1200[0] ?>" alt="<?php echo $ht_post_thumbnail_id_alt; ?>" />
  	</picture>

	<?php	
}
endif;


// Change default widget tag cloud settings
add_filter('widget_tag_cloud_args','ht_set_tag_cloud_args');
function ht_set_tag_cloud_args($args) {
	$args['largest'] = 16;
	$args['smallest'] = 10;
	$args['unit'] = 'px';
	return $args;
}

//webfonts
add_filter('ht_webfonts_themefonts', 'ht_webfonts_filter');
function ht_webfonts_filter($fonts){
	$fonts[] = new HT_Custom_Webfont(
				array(
					'name' => 'Search Submit',
					'name-visibility' => HT_WEBFONT_EDITABLE,
					'selector' => '#searchsubmit span',
					'selector-visibility' => HT_WEBFONT_HIDDEN,
					'source' => 'websafe',
					'source-visibility' => HT_WEBFONT_EDITABLE,
					'family' => 'Arial, Helvetica, sans-serif',
					'family-visibility' => HT_WEBFONT_EDITABLE,
					'style' => 'default',
					'style-visibility' => HT_WEBFONT_EDITABLE,
					'color' => '#b2b2b2',
					'color-visibility' => HT_WEBFONT_EDITABLE,
					'size'	=> '10',
					'size-visibility'	=> HT_WEBFONT_EDITABLE,
					'height' => '10',
					'height-visibility' => HT_WEBFONT_EDITABLE,
					'spacing' => '0',
					'spacing-visibility' => HT_WEBFONT_EDITABLE,

					)
				);
	return $fonts;
}