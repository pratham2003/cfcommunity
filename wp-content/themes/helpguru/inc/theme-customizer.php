<?php

/**
 * Adds the individual sections, settings, and controls to the theme customizer
 */
function ht_customizer( $wp_customize ) {

function ht_theme_widthmode() {
    	return;
}


	/**
 	* Homepage Section
 	*/
    $wp_customize->add_section( 'ht_homepage', array(
		'title' => __( 'Homepage', 'ht-theme' ),
		'description' => '',
		'priority' => 50,
	));

	// HP - Headline
	$wp_customize->add_setting('ht_hp_headline', array(
		'default'  => 'The self-service support theme',
	));

	$wp_customize->add_control('ht_hp_headline',	array(
		'type' => 'text',
		'label' => __( 'Headline', 'ht-theme' ),
		'section' => 'ht_homepage',
		'settings'   => 'ht_hp_headline',
	));

	// HP - Tagline
	$wp_customize->add_setting( 'ht_hp_tagline', array(
		'default' => 'A premium WordPress theme with integrated Knowledge Base, providing 24/7 community based support.',
	) );
	 
	$wp_customize->add_control('ht_hp_tagline', array(
		'type' => 'textarea',
		'label'   => 'Tagline',
		'section' => 'ht_homepage',
		'settings'   => 'ht_hp_tagline',
	) );

	/**
 	* Posts Section
 	*/
    $wp_customize->add_section( 'ht_blog', array(
		'title' => __( 'Blog', 'ht-theme' ),
		'description' => '',
		'priority' => 50,
	));

	// Posts - Page Header Tagline
	$wp_customize->add_setting( 'ht_blog_tagline', array(
		'default'        => 'Important news from the super support team.',
	) );
	 
	$wp_customize->add_control( 'ht_blog_tagline', array(
		'type' => 'textarea',
		'label'   => 'Blog Tagline',
		'section' => 'ht_blog',
		'settings'   => 'ht_blog_tagline',
	) );

	// Posts - Sidebar Position
    $wp_customize->add_setting( 'ht_blog_sidebar', array(
            'default'        => 'sidebar-right',
    ) );
    $wp_customize->add_control( new HT_Layout_Picker_Custom_Control( $wp_customize, 'ht_blog_sidebar', array(
        'label'   => 'Sidebar Position',
        'section' => 'ht_blog',
        'settings'   => 'ht_blog_sidebar',
     ) ) );
	 

	/**
 	* KB Section
 	*/
    $wp_customize->add_section( 'ht_kb', array(
		'title' => __( 'Knowledge Base', 'ht-theme' ),
		'description' => '',
		'priority' => 50,
	));

	// KB - Page Title
	$wp_customize->add_setting('ht_kb_title', array(
		'default'  => __( 'Knowledge Base', 'ht-theme' ),
	));

	$wp_customize->add_control('ht_kb_title',	array(
		'type' => 'text',
		'label' => __( 'KB Title', 'ht-theme' ),
		'section' => 'ht_kb',
		'settings'   => 'ht_kb_title',
	));

    // KB - Page Header Tagline
	$wp_customize->add_setting( 'ht_kb_tagline', array(
		'default' => __( 'KB Tagline', 'ht-theme' ),
	) );
	 
	$wp_customize->add_control( 'ht_kb_tagline', array(
		'type' => 'textarea',
		'label'   => __( 'KB Tagline', 'ht-theme' ),
		'section' => 'ht_kb',
		'settings'   => 'ht_kb_tagline',
	) );

	// KB - Sidebar Position
    $wp_customize->add_setting( 'ht_kb_sidebar', array(
            'default'        => 'sidebar-right',
    ) );
    $wp_customize->add_control( new HT_Layout_Picker_Custom_Control( $wp_customize, 'ht_kb_sidebar', array(
        'label'   => 'Sidebar Position',
        'section' => 'ht_kb',
        'settings'   => 'ht_kb_sidebar',
     ) ) );

	/**
 	* BBPress Section
 	*/
    $wp_customize->add_section( 'ht_bbpress', array(
		'title' => __( 'BBPress', 'ht-theme' ),
		'description' => '',
		'priority' => 50,
	));

	// BBPress - Page Title
	$wp_customize->add_setting('ht_bbpress_title', array(
		'default'  => 'Community Forums',
	));

	$wp_customize->add_control('ht_bbpress_title',	array(
		'type' => 'text',
		'label' => __( 'Forum Title', 'ht-theme' ),
		'section' => 'ht_bbpress',
		'settings'   => 'ht_bbpress_title',
	));

	// BBPress - Page Header Tagline
	$wp_customize->add_setting( 'ht_bbpress_tagline', array(
		'default'  => __( 'Forum tagline', 'ht-theme' ),
	) );
	 
	$wp_customize->add_control( 'ht_bbpress_tagline', array(
		'type' => 'textarea',
		'label'   => __( 'BBPress integrated forums, perfect for staff to customer interaction.', 'ht-theme' ),
		'section' => 'ht_bbpress',
		'settings'   => 'ht_bbpress_tagline',
	) );

	// BBPress - Sidebar Position
    $wp_customize->add_setting( 'ht_bbpress_sidebar', array(
            'default'        => 'sidebar-right',
    ) );
    $wp_customize->add_control( new HT_Layout_Picker_Custom_Control( $wp_customize, 'ht_bbpress_sidebar', array(
        'label'   => 'Sidebar Position',
        'section' => 'ht_bbpress',
        'settings'   => 'ht_bbpress_sidebar',
     ) ) );


	/**
 	* Header Section
 	*/
	$wp_customize->add_section('ht_header', array(
		'title' => __( 'Header', 'ht-theme' ),
		'description' => '',
		'priority' => 30,
	) );
	
	$wp_customize->add_setting( 'blogname', array(
		'default'    => get_option( 'blogname' ),
		'type'       => 'option',
		'capability' => 'manage_options',
	) );

	$wp_customize->add_control( 'blogname', array(
		'label'      => __( 'Site Title', 'ht-theme' ),
		'section'    => 'ht_header',
	) );

	$wp_customize->add_setting( 'blogdescription', array(
		'default'    => get_option( 'blogdescription' ),
		'type'       => 'option',
		'capability' => 'manage_options',
	) );

	$wp_customize->add_control( 'blogdescription', array(
		'label'      => __( 'Tagline', 'ht-theme' ),
		'section'    => 'ht_header',
	) );
	
	
	// Add logo to Site Title & Tagline Section
	$wp_customize->add_setting( 'ht_site_logo', array('default' => get_template_directory_uri() . '/images/logo.png') );
 
	$wp_customize->add_control( new WP_Customize_Image_Control( $wp_customize, 'ht_site_logo', array(
		'label' => __( 'Site Logo', 'ht-theme' ),
		'section' => 'ht_header',
		'settings' => 'ht_site_logo')
	) );

	
	/**
 	* Footer Section
 	*/
    $wp_customize->add_section('ht_footer', array(
		'title' => __( 'Footer', 'ht-theme' ),
		'description' => '',
		'priority' => 35,
	) );
	
	// site coypright
	$wp_customize->add_setting( 'ht_copyright', array(
		'default'        => __( '&copy; Copyright <a href="http://herothemes.com">Hero Themes</a>.', 'ht-theme' ),
	) );
	 
	$wp_customize->add_control( 'ht_copyright', array(
		'type' => 'textarea',
		'label'   => __( 'Site Copyright', 'ht-theme' ),
		'section' => 'ht_footer',
		'settings'   => 'ht_copyright',
	) );	
	


// Panel: Styling
$wp_customize->add_panel( 'ht_panel_styling', array(
    'priority'       => 10,
    'title'          => __( 'Theme Styling', 'ht-theme' ),
) );

// Panel: Styling || Section: Width
$wp_customize->add_section( 'ht_section_width', array(
    'priority'       => 10,
    'title'          => __( 'Theme Width', 'ht-theme' ),
    'description'    => '',
    'panel'  => 'ht_panel_styling',
) );

// Panel: Styling || Section: xxx || Setting: xxx
$wp_customize->add_setting('ht_setting_widthmode', array(
	'default'  => 'fullwidth',
));
$wp_customize->add_control('ht_setting_widthmode',	array(
	'section' => 'ht_section_width',
	'type' => 'radio',
	'label' => __( 'Forum Title', 'ht-theme' ),
	'choices' => array(
                    'fullwidth' => 'Fullwidth',
                    'boxed' => 'Boxed',
                ),
));

// Panel: Styling || Section: xxx || Setting: xxx
$wp_customize->add_setting( 'ht_setting_themewidth', array( 'default' => 1200 ) );
$wp_customize->add_control( 'ht_setting_themewidth', array(
	'section'		=> 'ht_section_width',
    'type'			=> 'range',
    'priority'		=> 10,
    'label'			=> __( 'Site Max Width (px)', 'ht-theme' ),
    'description' 	=> __( 'This is the range control description.', 'ht-theme' ),
    'input_attrs' 	=> array(
        'min'   => 920,
        'max'   => 2000,
        'step'  => 1,
    ),
    'active_callback' => 'is_front_page',
) );

// Panel: Styling || Section: Background  
    $wp_customize->add_section('ht_section_bg', array(
        'title' => __( 'Background', 'ht-theme' ),
        'description' => __( 'This section only applies when using the boxed layout.', 'ht-theme' ),
        'priority' => 10,
        'panel'  => 'ht_panel_styling',
        )
    );

        // Panel: Styling || Section: Background || Setting: Custom Background (Image)
        $wp_customize->add_section( 'background_image', array(
            'title'          => __( 'Background', 'ht-theme' ),
            'theme_supports' => 'custom-background',
            'priority'       => 80,
            'panel'  => 'ht_panel_styling',
        ) );

        $wp_customize->add_setting( 'background_image', array(
            'default'        => get_theme_support( 'custom-background', 'default-image' ),
            'theme_supports' => 'custom-background',
            'sanitize_callback' => 'sanitize_text_field',
        ) );

        $wp_customize->add_setting( new WP_Customize_Background_Image_Setting( $wp_customize, 'background_image_thumb', array(
            'theme_supports' => 'custom-background',
            'sanitize_callback' => 'sanitize_text_field',
        ) ) );

        $wp_customize->add_control( new WP_Customize_Background_Image_Control( $wp_customize ) );

        $wp_customize->add_setting( 'background_repeat', array(
            'default'        => 'repeat',
            'theme_supports' => 'custom-background',
            'sanitize_callback' => 'sanitize_text_field',
        ) );

        $wp_customize->add_control( 'background_repeat', array(
            'label'      => __( 'Background Repeat', 'ht-theme' ),
            'section'    => 'background_image',
            'type'       => 'radio',
            'choices'    => array(
                'no-repeat'  => __('No Repeat', 'ht-theme'),
                'repeat'     => __('Tile', 'ht-theme'),
                'repeat-x'   => __('Tile Horizontally', 'ht-theme'),
                'repeat-y'   => __('Tile Vertically', 'ht-theme'),
            ),
        ) );

        $wp_customize->add_setting( 'background_position_x', array(
            'default'        => 'left',
            'theme_supports' => 'custom-background',
            'sanitize_callback' => 'sanitize_text_field',
        ) );

        $wp_customize->add_control( 'background_position_x', array(
            'label'      => __( 'Background Position', 'ht-theme' ),
            'section'    => 'background_image',
            'type'       => 'radio',
            'choices'    => array(
                'left'       => __('Left', 'ht-theme'),
                'center'     => __('Center', 'ht-theme'),
                'right'      => __('Right', 'ht-theme'),
            ),
        ) );

        $wp_customize->add_setting( 'background_attachment', array(
            'default'        => 'fixed',
            'theme_supports' => 'custom-background',
            'sanitize_callback' => 'sanitize_text_field',
        ) );

        $wp_customize->add_control( 'background_attachment', array(
            'label'      => __( 'Background Attachment', 'ht-theme' ),
            'section'    => 'background_image',
            'type'       => 'radio',
            'choices'    => array(
                'fixed'      => __('Fixed', 'ht-theme'),
                'scroll'     => __('Scroll', 'ht-theme'),
            ),
        ) );

        // Panel: Styling || Section: Background || Setting: Custom Background (Color)
        $wp_customize->add_setting( 'background_color', array(
            'default' => get_theme_support( 'custom-background', 'default-color' ),
            'theme_supports' => 'custom-background',
            'sanitize_callback' => 'sanitize_hex_color_no_hash',
            'sanitize_js_callback' => 'maybe_hash_hex_color',
        ) );
        $wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'background_color', array(
            'label' => __( 'Background Color', 'ht-theme' ),
            'section' => 'colors',
            'section' => 'background_image',
        ) ) ); 

// Panel: Styling || Section: Custom  
    $wp_customize->add_section('ht_section_custom', array(
        'title' => __( 'Custom CSS', 'ht-theme' ),
        'description' => '',
        'priority' => 100,
        'panel'  => 'ht_panel_styling',
        )
    );

        // Panel: Styling || Section: Custom  || Setting: Custom CSS

        $wp_customize->add_setting('ht_custom_css', array(
            'default' => '',
            'sanitize_callback' => 'sanitize_text_field',
            )
        );
        $wp_customize->add_control( 'ht_custom_css', array(
            'label' => __( 'Custom CSS', 'ht-theme' ),
            'description' => 'Enter any Custom CSS here',
            'type'     => 'textarea',
            'section'  => 'ht_section_custom',
        ) );


	/**
 	* Styling Section
 	*/
	$wp_customize->add_section( 'ht_styling', array(
		'title' => __( 'Theme Colors', 'ht-theme' ),
		'description' => '',
		'priority' => 40,
		'panel'  => 'ht_panel_styling',
	));

	// Link Color
	$wp_customize->add_setting( 'ht_theme_link', array('default' => '#32a3cb', 'sanitize_callback' => 'sanitize_hex_color' )	);
	
	$wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'ht_theme_link', array(
		'label'        => __( 'Link Color', 'ht-theme' ),
		'section'    => 'ht_styling',
	) ) );

	// Link:Hover Color
	$wp_customize->add_setting('ht_theme_link_hover', array('default' => '#32a3cb','sanitize_callback' => 'sanitize_hex_color',)	);
	
	$wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'ht_theme_link_hover', array(
		'label'        => __( 'Link Color (Hover)', 'ht-theme' ),
		'section'    => 'ht_styling',
	) ) );
	
	// Header BG Color
	$wp_customize->add_setting('ht_theme_header_bg', array('default' => '#2e97bd','sanitize_callback' => 'sanitize_hex_color',)	);
	
	$wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'ht_theme_header_bg', array(
		'label'        => __( 'Header BG Color', 'ht-theme' ),
		'section'    => 'ht_styling',
	) ) );
	
	// Header Font Color
	$wp_customize->add_setting('ht_theme_header_fontcolor', array('default' => '#ffffff','sanitize_callback' => 'sanitize_hex_color',)	);
	
	$wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'ht_theme_header_fontcolor', array(
		'label'        => __( 'Header Font Color', 'ht-theme' ),
		'section'    => 'ht_styling',
	) ) );

	// Page Header BG Color
	$wp_customize->add_setting('ht_theme_pageheader_bg', array('default' => '#32a3cb','sanitize_callback' => 'sanitize_hex_color',)	);
	
	$wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'ht_theme_pageheader_bg', array(
		'label'        => __( 'Page Header BG Color', 'ht-theme' ),
		'section'    => 'ht_styling',
	) ) );
	
	// Page Header Font Color
	$wp_customize->add_setting('ht_theme_pageheader_fontcolor', array('default' => '#ffffff','sanitize_callback' => 'sanitize_hex_color',)	);
	
	$wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'ht_theme_pageheader_fontcolor', array(
		'label'        => __( 'Page Header Font Color', 'ht-theme' ),
		'section'    => 'ht_styling',
	) ) );
	
}
add_action( 'customize_register', 'ht_customizer' );

function ht_customize_preview_js() {
	wp_enqueue_script( 'ht-theme-customizer', get_template_directory_uri() . '/js/customizer.js', array( 'customize-preview' ), '', true );
}
add_action( 'customize_preview_init', 'ht_customize_preview_js' );