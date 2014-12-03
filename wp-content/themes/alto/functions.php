<?php
/**
 * Alto functions and definitions.
 *
 * @package Alto
 */

// Set content width

if ( ! isset( $content_width ) ) {
  $content_width = 635;
}

if ( ! function_exists( 'alto_setup' ) ) :
/**
 * Sets up theme defaults and registers support for various WordPress features.
 *
 * Note that this function is hooked into the after_setup_theme hook, which
 * runs before the init hook. The init hook is too late for some features, such
 * as indicating support for post thumbnails.
 */
function alto_setup() {

  /*
   * Make theme available for translation.
   * Translations can be filed in the /languages/ directory.
   * If you're building a theme based on Alto, use a find and replace
   * to change 'alto' to the name of your theme in all the template files
   */
  load_theme_textdomain( 'alto', get_template_directory() . '/languages' );

  // Add default posts and comments RSS feed links to head.
  add_theme_support( 'automatic-feed-links' );

  /*
   * Enable support for Post Thumbnails on posts and pages.
   *
   * @link http://codex.wordpress.org/Function_Reference/add_theme_support#Post_Thumbnails
   */
  add_theme_support( 'post-thumbnails', array( 'post', 'page' ) );

  set_post_thumbnail_size( 300, 300, true );
  add_image_size( 'recent-post', 300, 300, true );
  add_image_size( 'index-post', 860, 9999, false );
  add_image_size( 'single-post', 1200, 9999, false );
  add_image_size( 'alt-single-post-2', 1600, 9999, false );

  // This theme uses wp_nav_menu() in one location.
  register_nav_menus( array(
    'primary' => __( 'Primary Menu', 'alto' ),
    'social'  => __( 'Social', 'alto' )
  ) );

  // Enable support for Post Formats.
  add_theme_support( 'post-formats', array( 'aside', 'image', 'video', 'quote', 'link' ) );

}
endif; // alto_setup
add_action( 'after_setup_theme', 'alto_setup' );

/**
 * Register widgetized area and update sidebar with default widgets.
 */
function alto_widgets_init() {
  register_sidebar( array(
    'name'          => __( 'Sidebar', 'alto' ),
    'id'            => 'sidebar-1',
    'before_widget' => '<aside id="%1$s" class="widget %2$s">',
    'after_widget'  => '</aside>',
    'before_title'  => '<h1 class="widget-title">',
    'after_title'   => '</h1>',
  ) );
}
add_action( 'widgets_init', 'alto_widgets_init' );

/**
 * Enqueue scripts and styles.
 */

function alto_styles() {

  wp_enqueue_style( 'alto-style', get_stylesheet_uri() );

  /* Inline styles for customization options */

  $accent         = get_theme_mod( 'alto_accent_color' );

  $button_text    = get_theme_mod( 'alto_button_text_color' );
  $tag_background = get_theme_mod( 'alto_tag_background_color' );

  if ( $accent ) {
    $accent_as_rgb  = hex2rgb( $accent );
    $accent_darker  = adjustBrightness( $accent, -10 );
    $accent_lighter = adjustBrightness( $accent, 50 );
  } else {
    $accent = "#F23047";
    $accent_as_rgb  = hex2rgb( $accent );
    $accent_darker  = adjustBrightness( $accent, -10 );
    $accent_lighter = adjustBrightness( $accent, 50 );
  }

  $background     = get_theme_mod( 'background_color' );

  $custom_css     = "

    /**
    * Background Color
    */

    /* Block Headers */

    .block-header h3,
    .sidebar .widget_instagram .control-cap .cap-overflow {
      background: #{$background};
    }

    /**
    * Accent Colors
    */

    /* Buttons */

    .btn,
    button:not(.search-submit),
    input[type='submit'],
    #infinite-handle,
    #comment-submit {
      color: {$button_text} !important;
      background: {$accent};
      background: rgba({$accent_as_rgb},0.8);
      -webkit-box-shadow: 0 1px 0 {$accent_darker};
      -moz-box-shadow: 0 1px 0 {$accent_darker};
      -ms-box-shadow: 0 1px 0 {$accent_darker};
      -o-box-shadow: 0 1px 0 {$accent_darker};
      box-shadow: 0 1px 0 {$accent_darker};
    }

    .btn:hover,
    button:not(.search-submit):hover,
    input[type='submit']:hover,
    #infinite-handle:hover,
    #comment-submit:hover {
      color: {$button_text} !important;
      background: {$accent};
      background: rgba({$accent_as_rgb},1.0);
      -webkit-box-shadow: 0 1px 0 {$accent_darker};
      -moz-box-shadow: 0 1px 0 {$accent_darker};
      -ms-box-shadow: 0 1px 0 {$accent_darker};
      -o-box-shadow: 0 1px 0 {$accent_darker};
      box-shadow: 0 1px 0 {$accent_darker};
    }

    .btn:active,
    button:not(.search-submit):active,
    input[type='submit']:active,
    #infinite-handle:active,
    #comment-submit:active {
      color: {$button_text} !important;
      -webkit-box-shadow: 0 -1px 0 {$accent_darker};
      -moz-box-shadow: 0 -1px 0 {$accent_darker};
      -ms-box-shadow: 0 -1px 0 {$accent_darker};
      -o-box-shadow: 0 -1px 0 {$accent_darker};
      box-shadow: 0 -1px 0 {$accent_darker};
    }

    /* Sidebar Links */

    .sidebar ul li a,
    .sidebar ol li a,
    .sidebar .widget_calendar #wp-calendar tbody a,
    .sidebar .widget_calendar #wp-calendar tfoot a,
    .textwidget a {
      color: {$accent};
    }

    .sidebar .widget_text .textwidget a {
      color: {$accent};
    }

    /* Instagram Sidebar */

    .sidebar .widget_instagram .instagram-cycle-toggle:hover {
      background: {$accent};
    }

    .sidebar .widget_instagram .instagram-cycle-toggle:hover i {
      color: {$button_text};
    }

    /* Entry Content */

    #page .entry-content .category-title a,
    #page .entry-title .category-title a,
    #page .entry-content h1 a:hover,
    #page .more-link {
      color: {$accent};
    }

    #page .entry-content blockquote:not(.pull-quote) {
      border-left-color: {$accent};
    }

    #page .entry-content .category-title a:hover ,
    #page .more-link:hover {
      color: #333333;
    }

    #page .site-footer a:hover {
      color: #747474;
    }

    /* Entry Meta */

    .tags a,
    .edit-link a {
      color: {$accent};
    }

    .alt-post .entry-body .entry-meta.desktop h5 a,
    .alt-post .entry-body .entry-meta.mobile h5 a {
      color: {$accent};
    }

    /* Comments & Post Navigation */

    .comments-area .comment-list .comment.bypostauthor > .comment-body > .comment-meta > .comment-author > .avatar {
      border: 3px solid {$accent};
    }

    .comments-area .comment-list .comment-reply-link {
      color: {$accent};
    }

    .comments-area .comment-list .comment-awaiting-moderation {
      background: {$accent};
      color: {$button_text};
    }

    .comments-area .comment-list .comment-respond .comment-reply-title a {
      color: {$accent};
    }

    .nav-links h3:hover,
    .nav-links h3 a:hover {
      color: {$accent};
    }

    /*
    * Mobile Devices (sub 804px)
    */

    @media screen and (max-width: 804px) {

      /* Navigation */

      .site-branding .menu-toggle .icon-alto-iconfont_Close {
        color: {$accent};
      }

      .main-navigation.toggled .menu .current-menu-item > a,
      .main-navigation.toggled .menu .current_page_item > a,
      .main-navigation.toggled .menu li a:hover {
        background: {$accent};
        color: {$button_text};
      }

    }

    /*
    * Desktop (805px and up)
    */

    @media screen and (min-width: 805px) {

      /* Navigation */

      .main-navigation .menu > li:hover > a,
      .main-navigation.toggled .menu > li:hover > a {
        color: {$accent};
      }

      .main-navigation .menu > .menu-item-has-children:hover:after,
      .main-navigation.toggled .menu > .menu-item-has-children:hover:after,
      .main-navigation .menu > .page_item_has_children:hover:after,
      .main-navigation.toggled .menu > .page_item_has_children:hover:after {
        width: 0;
        height: 0;
        border-left: 3px solid transparent;
        border-right: 3px solid transparent;
        border-top: 3px solid {$accent};
      }

      .main-navigation .sub-menu > .menu-item-has-children:hover:after,
      .main-navigation.toggled .sub-menu > .menu-item-has-children:hover:after,
      .main-navigation .children > .page_item_has_children:hover:after,
      .main-navigation.toggled .chidlren > .page_item_has_children:hover:after {
        width: 0;
        height: 0;
        border-top: 3px solid transparent;
        border-bottom: 3px solid transparent;
        border-left: 3px solid {$button_text};
      }

      .main-navigation .menu-item-has-children .sub-menu > li:hover > a,
      .main-navigation.toggled .menu-item-has-children .sub-menu > li:hover > a,
      .main-navigation .page_item_has_children .children > li:hover > a,
      .main-navigation.toggled .page_item_has_children .children > li:hover > a  {
        background: {$accent};
        color: {$button_text};
      }

    }

    /* Sharing Module */

    .alto-sharing .alto-sharing-more .close {
      color: {$accent};
    }

  ";

  wp_add_inline_style( 'alto-style', $custom_css );

}
add_action( 'wp_enqueue_scripts', 'alto_styles' );


function alto_scripts() {

  // All pages and posts.
  wp_enqueue_script( 'alto-navigation', get_template_directory_uri() . '/javascripts/navigation.js', array(), '20130225', true );
  wp_enqueue_script( 'fix-navigation', get_template_directory_uri() . '/javascripts/fix-navigation.js', array( 'jquery' ), '20140214', true );
  wp_enqueue_script( 'alto-skip-link-focus-fix', get_template_directory_uri() . '/javascripts/skip-link-focus-fix.js', array(), '20130115', true );
  wp_enqueue_script( 'alto-instagram', get_template_directory_uri() . '/javascripts/instagram-cycle.js', array(), '20140225', true );
  wp_enqueue_script( 'alto-search', get_template_directory_uri() . '/javascripts/search.js', array( 'jquery' ), '20140120', true );
  wp_enqueue_script( 'fitvids', get_template_directory_uri() . '/javascripts/fitvids.js', array( 'jquery' ), '20140214', true );

  // Singles and pages.
  if ( is_page() || is_single() ) {
    wp_enqueue_script( 'alto-sharing', get_template_directory_uri() . '/javascripts/sharing.js', array( 'jquery' ), '20140120', true );
  }

  // Comments
  if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
    wp_enqueue_script( 'timeago', get_template_directory_uri() . '/javascripts/timeago.js', array( 'jquery' ), '20140120', true );
    wp_enqueue_script( 'comment-reply' );
  }

}
add_action( 'wp_enqueue_scripts', 'alto_scripts' );

/**
 * Custom template tags for this theme.
 */
require get_template_directory() . '/includes/template-tags.php';

/**
 * Custom functions that act independently of the theme templates.
 */
require get_template_directory() . '/includes/extras.php';

/**
* Custom widgets & widget overrides.
*/
require get_template_directory() . '/includes/custom-widgets.php';

/**
 * Customizer additions.
 */
require get_template_directory() . '/includes/customizer.php';

/**
 * Load Jetpack compatibility file.
 */
require get_template_directory() . '/includes/jetpack.php';
