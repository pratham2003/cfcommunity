<?php
function enqueue_make_theme_style() {
    wp_enqueue_style( 'make-style', get_template_directory_uri().'/style.css' );
}
add_action( 'wp_enqueue_scripts', 'enqueue_make_theme_style', 11  );
?>