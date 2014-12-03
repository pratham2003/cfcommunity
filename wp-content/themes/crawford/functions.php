<?php

// theme setup
if (!function_exists('crawford_setup')):
	function crawford_setup() {	
		register_nav_menus( array(
			'primary'   => __('Primary Menu', 'crawford'),			
			'footer'   => __('Footer Menu', 'crawford')	
		));
		add_theme_support('post-thumbnails');
		add_image_size('featured', 1170, 400, true);	
		add_theme_support('automatic-feed-links');
		// editor style
		function crawford_editor_style() {
		  add_editor_style( get_template_directory_uri() . '/assets/css/editor-style.css' );
		}
		add_action('after_setup_theme', 'crawford_editor_style');
		// set content width  
		if (!isset($content_width)) {$content_width = 750;}	
	}
endif; 
add_action('after_setup_theme', 'crawford_setup');

// load css 
function crawford_css() {	
	wp_enqueue_style('crawford_oswald', '//fonts.googleapis.com/css?family=Oswald:400');	
	wp_enqueue_style('crawford_domine', '//fonts.googleapis.com/css?family=Domine:400,700');
	wp_enqueue_style('crawford_bootstrap_css', get_template_directory_uri() . '/assets/css/bootstrap.min.css');	   
	wp_enqueue_style('crawford_style', get_stylesheet_uri());
}
add_action('wp_enqueue_scripts', 'crawford_css');

// load javascript
function crawford_javascript() {	
	wp_enqueue_script('crawford_bootstrap_js', get_template_directory_uri() . '/assets/js/bootstrap.min.js', array('jquery'), '3.1.1', true); 	
	wp_enqueue_script('crawford_script', get_template_directory_uri() . '/assets/js/crawford.js', array('jquery'), '1.0', true);
	if (is_singular() && comments_open()) {wp_enqueue_script('comment-reply');}
}
add_action('wp_enqueue_scripts', 'crawford_javascript');

// html5 shiv
function crawford_html5_shiv() {
    echo '<!--[if lt IE 9]>';
    echo '<script src="'. get_template_directory_uri() .'/assets/js/html5shiv.js"></script>';
    echo '<![endif]-->';
}
add_action('wp_head', 'crawford_html5_shiv');

// widgets
function crawford_widgets_init() {	
	register_sidebar(array(
		'name' => __('Footer - Left', 'crawford'),
		'id' => 'footer-sidebar-left',
		'description' => __('Widgets in this area will appear in the left column of the footer.', 'crawford'),
		'before_widget' => '<aside id="%1$s" class="widget %2$s">',
		'after_widget' => "</aside>",
		'before_title' => '<h4>',
		'after_title' => '</h4>'
	));	
	register_sidebar(array(
		'name' => __('Footer - Middle', 'crawford'),
		'id' => 'footer-sidebar-middle',
		'description' => __('Widgets in this area will appear in the middle column of the footer.', 'crawford'),
		'before_widget' => '<aside id="%1$s" class="widget %2$s">',
		'after_widget' => "</aside>",
		'before_title' => '<h4>',
		'after_title' => '</h4>'
	));	
	register_sidebar(array(
		'name' => __('Footer - Right', 'crawford'),
		'id' => 'footer-sidebar-right',
		'description' => __('Widgets in this area will appear in the right column of the footer.', 'crawford'),
		'before_widget' => '<aside id="%1$s" class="widget %2$s">',
		'after_widget' => "</aside>",
		'before_title' => '<h4>',
		'after_title' => '</h4>'
	));	
}
add_action('widgets_init', 'crawford_widgets_init');

// page titles
function crawford_title($title, $sep) {
	global $paged, $page;
	if (is_feed()) { return $title;	}
	$title .= get_bloginfo('name');	
	$site_description = get_bloginfo('description', 'display');
	if ($site_description && (is_home() || is_front_page())) { $title = "$title $sep $site_description"; }	
	if ($paged >= 2 || $page >= 2) { $title = "$title $sep " . sprintf( __('Page %s', 'crawford'), max($paged, $page)); }
	return $title;
}
add_filter('wp_title', 'crawford_title', 10, 2);

// custom excerpt
if (!function_exists('crawford_custom_excerpt')):
    function crawford_custom_excerpt($new_excerpt) {
        global $post;
        $raw_excerpt = $new_excerpt;
        if ('' == $new_excerpt) {
            $new_excerpt = get_the_content('');
            $new_excerpt = strip_shortcodes($new_excerpt);
            $new_excerpt = apply_filters('the_content', $new_excerpt);
            $new_excerpt = substr($new_excerpt, 0, strpos( $new_excerpt, '</p>' ) + 4);
            $new_excerpt = str_replace(']]>', ']]&gt;', $new_excerpt);
            $excerpt_end = '<p class="excerpt-link"><a href="'. esc_url( get_permalink() ) . '">' . '&sim;&nbsp;' . sprintf(__('Continue Reading', 'crawford'), get_the_title()) . '&nbsp;&sim;</a></p>'; 
            $excerpt_more = apply_filters('excerpt_more', ' ' . $excerpt_end);           
            $new_excerpt .= $excerpt_end;
            return $new_excerpt;
        }
        return apply_filters('crawford_custom_excerpt', $new_excerpt, $raw_excerpt);
    }
endif; 
remove_filter('get_the_excerpt', 'wp_trim_excerpt');
add_filter('get_the_excerpt', 'crawford_custom_excerpt');

// pagination
if (!function_exists('crawford_pagination')):
	function crawford_pagination() {
		global $wp_query;
		$big = 999999999;	
		echo '<div class="pager">';	
		echo paginate_links( array(
			'base' => str_replace($big, '%#%', esc_url(get_pagenum_link($big))),
			'format' => '?paged=%#%',
			'current' => max(1, get_query_var('paged')),
			'total' => $wp_query->max_num_pages,
			'prev_next' => true,
			'prev_text' => __('&larr; Prev', 'crawford'),
			'next_text' => __('Next &rarr;', 'crawford')
		));
		echo '</div>';
	}
endif;

// comments
if (!function_exists('crawford_comment')) :
	function crawford_comment($comment, $args, $depth) {
		$GLOBALS['comment'] = $comment;
		?>	
		<li <?php comment_class(); ?> id="li-comment-<?php comment_ID(); ?>"> 	
		<div id="comment-<?php comment_ID(); ?>" class="comment">						
			<p class="comment-author-name">
				<?php if(get_comment_author_url()) : ?>
				<a href="<?php comment_author_url_link(); ?>"><?php comment_author(); ?></a>
				<?php else : ?>
				<?php comment_author(); ?> 
				<?php endif; ?>
				- <?php echo get_comment_date() . ' @ ' . get_comment_time() ?>
			</p>				
			<div class="comment-body">			
				<?php comment_text(); ?>
				<?php if ('0' == $comment->comment_approved) : ?>				
					<p class="comment-awaiting-moderation"><?php _e('Comment is awaiting moderation!', 'crawford'); ?></p>					
				<?php endif; ?>				
			</div>			
		</div>
	<?php 
	}
endif;

// theme customizer
function crawford_customize_register($wp_customize) {
	// upload logo
	$wp_customize->add_section('crawford_logo_section', array(
		'title' => __('Upload Logo', 'crawford'),
		'priority' => 1,
		'type' => 'option'		
	));
	$wp_customize->add_setting('crawford_logo_setting', array(		
		'sanitize_callback' => 'esc_url_raw'
	));
	$wp_customize->add_control(new WP_Customize_Image_Control($wp_customize, 'logo', array(
		'label' => __('Logo', 'crawford'),
		'section' => 'crawford_logo_section',
		'settings' => 'crawford_logo_setting'
	)));	
	// link color
	$wp_customize->add_setting('crawford_link_color', array(        
        'default' => '#20b2aa',
	    'sanitize_callback' => 'sanitize_hex_color'
    )); 	
	$wp_customize->add_control(new WP_Customize_Color_Control($wp_customize, 'links', array(
		'label' => __('Links', 'crawford'),        
        'section' => 'colors',
        'settings' => 'crawford_link_color'
    )));
    // display options
    $wp_customize->add_section('crawford_display_section', array(
		'title' => __('Display Options', 'crawford'),
		'priority' => 2,
		'type' => 'option'		
	));
	// show tagline
	$wp_customize->add_setting('crawford_tagline_setting', array(        
        'default' => 'yes',
	    'sanitize_callback' => 'crawford_sanitize_yn'
    )); 	
	$wp_customize->add_control('crawford_tagline_setting', array(
    'label'      => __('Show tagline', 'crawford'),
    'section'    => 'crawford_display_section',
    'settings'   => 'crawford_tagline_setting',
    'type'       => 'radio',
    'choices'    => array(
        'yes' => 'Yes',
        'no' => 'No'        
        )
	));
	// show features images in teasers
	$wp_customize->add_setting('crawford_feat_img_setting', array(        
        'default' => 'no',
	    'sanitize_callback' => 'crawford_sanitize_yn'
    )); 	
	$wp_customize->add_control('crawford_feat_img_setting', array(
    'label'      => __('Show featured images on teasers', 'crawford'),
    'section'    => 'crawford_display_section',
    'settings'   => 'crawford_feat_img_setting',
    'type'       => 'radio',
    'choices'    => array(
        'yes' => 'Yes',
        'no' => 'No'        
        )
	));
	function crawford_sanitize_yn($value) {
	    if (!in_array($value, array('yes', 'no'))) :
	        $value = 'yes';	 
	    endif;
	    return $value;
	}
}
add_action('customize_register', 'crawford_customize_register');

// customizer CSS
function crawford_customize_css() {
    ?>
     <style type="text/css">
        a, a:hover, a:focus, header nav .menu-item-has-children:hover a, header nav .menu-item-has-children:hover .sub-menu li a:hover {color:<?php echo get_theme_mod('crawford_link_color'); ?>;}    
        article blockquote {border-color:<?php echo get_theme_mod('crawford_link_color'); ?>;}    
     </style>
    <?php
}
add_action('wp_head', 'crawford_customize_css');

?>