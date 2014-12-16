<?php

/* Load child theme stylesheet */
function ht_theme_style() {
	wp_enqueue_style( 'ht-theme-style', get_template_directory_uri() . '/css/style.css' );
	wp_enqueue_style( 'ht-childtheme-style', get_stylesheet_uri(), array('ht-theme-style') );
}
add_action( 'wp_enqueue_scripts', 'ht_theme_style' );

/**
 * Add Typekit
 *
 * @package cfcommunity
 */
function theme_typekit_inline() {
?>
    <script type="text/javascript" src="//use.typekit.net/nfj3xsx.js"></script>
    <script type="text/javascript">try{Typekit.load();}catch(e){}</script>
<?php
}
add_action( 'wp_head', 'theme_typekit_inline' );


/* Insert custom functions below */