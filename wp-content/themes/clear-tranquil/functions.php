<?php

add_action( 'init','clear_tranquil_customise_highwind', 999 );
function clear_tranquil_customise_highwind() {
	// Sidebar
	remove_action( 'highwind_content_after', 'highwind_sidebar' );

	// Footer widget regions
	remove_action( 'highwind_footer', 'highwind_footer_widgets', 10 );
	unregister_sidebar( 'primary-sidebar' );
	unregister_sidebar( 'footer-sidebar-1' );
	unregister_sidebar( 'footer-sidebar-2' );
	unregister_sidebar( 'footer-sidebar-3' );

	// Customisations
	add_action( 'customize_register', 'clear_tranquil_customizer_overrides' );

	// Remove open sans
	add_action( 'wp_enqueue_scripts', 'clear_tranquil_customise_scripts' );

	// Custom style
	add_filter( 'highwind_header_color_color_selectors', 'clear_tranquil_custom_heading_colors' );
	add_filter( 'highwind_desktop_background_color_background_selectors', 'clear_tranquil_custom_background_color' );
	add_filter( 'highwind_background_color_color_selectors', 'clear_tranquil_custom_background_color_color' );
	add_filter( 'highwind_link_color_background_selectors', 'clear_tranquil_custom_link_background' );
	add_filter( 'highwind_link_textcolor_default', 'clear_tranquil_default_link_color' );
	add_filter( 'highwind_textcolor_default', 'clear_tranquil_default_text_color' );
	add_filter( 'highwind_background_color_default', 'clear_tranquil_default_background_color' );
	add_filter( 'highwind_content_background_color_default', 'clear_tranquil_default_content_background_color' );
	add_filter( 'highwind_headercolor_default', 'clear_tranquil_default_header_color' );
}

function clear_tranquil_customizer_overrides( $wp_customize ) {
	$wp_customize->remove_section( 'highwind_layout' );
}

function clear_tranquil_customise_scripts() {
	wp_dequeue_style( 'open-sans' );
	wp_enqueue_style( 'droid-serif', '//fonts.googleapis.com/css?family=Droid+Serif:400,700,400italic,700italic' );
	wp_enqueue_style( 'lato', '//fonts.googleapis.com/css?family=Lato:300,400,700,400italic' );
}

function clear_tranquil_custom_background_color_color( $selectors ) {
	$new_selectors = 'article.post blockquote, article.page blockquote, .single article.post .article-content > p:first-of-type:first-letter, .single article.page .article-content > p:first-of-type:first-letter';
	return $new_selectors . ' ' . $selectors;
}

function clear_tranquil_custom_heading_colors( $selectors ) {
	$new_selectors = '.site-title, .site-description, h1 a, h2 a, h3 a, h4 a, h5 a, h6 a';
	return $new_selectors . ' ' . $selectors;
}

function clear_tranquil_custom_background_color( $selectors ) {
	$new_selectors = 'body, .header, .main-nav, .footer,';
	return $new_selectors . ' ' . $selectors;
}

function clear_tranquil_custom_link_background( $selectors ) {
	$selectors = '.single article.post .article-content > p:first-of-type:first-letter, .single article.page .article-content > p:first-of-type:first-letter, article.post blockquote, article.page blockquote, input[type="submit"], .button, input[type="button"], .navigation-post a, .navigation-paging a, .comments .bypostauthor > .comment-body .comment-content';
	return $selectors;
}

function clear_tranquil_default_link_color( $color ) {
	$color = '#5fb9c6';
	return $color;
}

function clear_tranquil_default_text_color( $color ) {
	$color = '#6c6b69';
	return $color;
}

function clear_tranquil_default_background_color( $color ) {
	$color = '#f5f6f6';
	return $color;
}

function clear_tranquil_default_content_background_color( $color ) {
	$color = '#f5f6f6';
	return $color;
}

function clear_tranquil_default_header_color( $color ) {
	$color = '#383737';
	return $color;
}