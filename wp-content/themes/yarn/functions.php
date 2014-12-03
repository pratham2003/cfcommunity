<?php
/**
 * @package    Yarn
 * @author     Kris Hocker <kris@krishocker.com>
 * @copyright  Copyright (c) 2014, Kris Hocker
 * @link       http://themehybrid.com/themes/yarn
 * @license    http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 */

/* Add the child theme setup function to the 'after_setup_theme' hook. */
add_action( 'after_setup_theme', 'yarn_theme_setup' );

/**
 * Setup function.
 *
 * @since  1.0.0
 * @access public
 * @return void
 */
function yarn_theme_setup() {

	/* Add a custom background to overwrite the defaults. */
	add_theme_support(
		'custom-background',
		array(
			'default-color' => '352922',
			'default-image' => '',
		)
	);

	/* Add a custom header to overwrite the defaults.
	 *
	 * @link http://codex.wordpress.org/Custom_Headers
	 */
	add_theme_support( 
		'custom-header', 
		array(
			'default-text-color' => 'b6a999',
			'default-image'      => '',
			'random-default'     => false,
		)
	);


	/* Add a custom default icon for the "header_icon" option. */
	add_filter( 'theme_mod_header_icon', 'yarn_header_icon' );

	/* Add a custom default color for the "menu" color option. */
	add_filter( 'theme_mod_color_menu', 'yarn_color_menu' );

	/* Add a custom default color for the "primary" color option. */
	add_filter( 'theme_mod_color_primary', 'yarn_color_primary' );
    
    /* Add customs styles to head. */
	add_action( 'wp_head', 'yarn_custom_styles', 95 );
	
}


/**
 * Change the default header icon option.  
 *
 * @since  1.0.0
 * @access public
 * @param  string  $hex
 * @return string
 */
function yarn_header_icon( $icon ) {
	return 'default' === $icon ? 'icon-book' : $icon;
}

/**
 * Add a default custom color for the theme's "menu" color option. 
 *
 * @since  1.0.0
 * @access public
 * @param  string  $hex
 * @return string
 */
function yarn_color_menu( $hex ) {
	return $hex ? $hex : 'eb9f9f';
}

/**
 * Add a default custom color for the theme's "primary" color option.
 *
 * @since  1.0.0
 * @access public
 * @param  string  $hex
 * @return string
 */
function yarn_color_primary( $hex ) {
	return $hex ? $hex : 'eb9f9f';
}

/**
 * Add custom styles using the primary color to header.
 *
 * @since  1.0.0
 * @access public
 * @return string
 */
 
 function yarn_custom_styles() {
 	
 	$hex = get_theme_mod( 'color_primary', '' );
 	$rgb = join( ', ', hybrid_hex_to_rgb( $hex ) );
 	
 	$output = "<style> 
 				.more-link { background-color: rgba( {$rgb}, 0.75); } 
 				.plural .format-link, .plural .entry.format-link > .wrap { background-color: #{$hex}; }
 				.plural .format-link a:hover .entry-subtitle, .plural .format-link a:focus .entry-subtitle { color: rgba( 250, 247, 237, 0.5); }
 				</style>";
 	
 	echo $output;

 }
