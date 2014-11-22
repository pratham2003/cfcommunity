<?php


function enqueue_child_theme_style() {
    wp_enqueue_style( 'child-style', get_stylesheet_directory_uri().'/style.css' );
}
add_action( 'wp_enqueue_scripts', 'enqueue_child_theme_style', 11  );

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
?>