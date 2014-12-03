<?php
/**
 * The Header for our theme.
 *
 * Displays all of the <head> section and everything up till <div id="content">.
 *
 * @package Alto
 */
?><!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?php wp_title( '|', true, 'right' ); ?></title>
<link rel="profile" href="http://gmpg.org/xfn/11">
<link rel="pingback" href="<?php bloginfo( 'pingback_url' ); ?>">
<?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>

<?php // Declare variables to help us determine context/type of post. ?>

<?php
  $single       = is_single();
  $attachment   = is_attachment();
  $single_style = get_theme_mod('alto_select_alt_post_type');
?>

<div id="page" class="hfeed site <?php if ( $single && !$attachment && 'alt-2' == $single_style ) { ?>full-width-template<?php } ?>">
  
  <?php do_action( 'before' ); ?>

  <?php // Check to see if this page is a single and has an image. ?>

  <?php if ( $single ) {  $thumbnail = has_post_thumbnail(); } ?>

  <header id="masthead" class="site-header <?php if ( $single && !$attachment && ' ' != $thumbnail ) { ?>alt-without-thumbnail<?php } ?>" role="banner">
    <div class="site-branding">
      <?php $customLogo = get_theme_mod( 'alto_upload_logo' ); ?>
      <?php if ( $customLogo ) { ?>
        <div class="custom-logo">
          <a href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home"><img src="<?php echo $customLogo ?>" alt="<?php bloginfo( 'name' ); ?>"></a>
        </div>
      <?php } else { ?>
        <div class="title-description">
          <h2 class="site-title"><a href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home"><?php bloginfo( 'name' ); ?></a></h2>
          <h2 class="site-description"><?php bloginfo( 'description' ); ?></h2>
        </div>
      <?php } ?>
      <h1 class="menu-toggle"><i class="icon-alto-iconfont_Menu"></i></h1>
    </div> <!-- end .site-branding -->

    <nav id="site-navigation" class="main-navigation" role="navigation">
      <a class="skip-link screen-reader-text" href="#content"><?php _e( 'Skip to content', 'alto' ); ?></a>
      <?php wp_nav_menu( array( 
        'theme_location' => 'primary', 
        'depth'          => 4, 
        'menu_class'     => 'menu'
        ) ); 
      ?>
      <i class="close-arrow"></i>
    </nav> <!-- end #site-navigation -->

  </header> <!-- end #masthead -->

  <?php // Start content div for entire theme. ?> 

  <div id="content" class="site-content">