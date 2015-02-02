<?php
/**
 * Set up the menus (including the different language menus)
 */
function cfc_setup_menu() {

  // Register wp_nav_menu() menus (http://codex.wordpress.org/Function_Reference/register_nav_menus)
  register_nav_menus(array(
    'primary_navigation_en_US' => __('Primary Navigation', 'roots'),
  ));

  register_nav_menus(array(
    'primary_navigation_nl_NL' => __('Dutch Navigation', 'roots'),
  ));

  register_nav_menus(array(
    'primary_navigation_de' => __('German Navigation', 'roots'),
  ));

  register_nav_menus(array(
    'primary_navigation_it' => __('Italian Navigation', 'roots'),
  ));

  register_nav_menus(array(
    'primary_navigation_es' => __('Spanish Navigation', 'roots'),
  ));

  add_theme_support('post-thumbnails');

  // Tell the TinyMCE editor to use a custom stylesheet
  add_editor_style('/assets/css/editor-style.css');
}
add_action('after_setup_theme', 'cfc_setup_menu');


/**
 * Infinity Theme: menus
 *
 * @author Marshall Sorenson <marshall@presscrew.com>
 * @link http://infinity.presscrew.com/
 * @copyright Copyright (C) 2010-2012 Marshall Sorenson
 * @license http://www.gnu.org/licenses/gpl.html GPLv2 or later
 * @package Infinity
 * @subpackage base
 * @since 1.1
 */

/**
 * Add a filter for every displayed user navigation item
 */
function cfc_bp_nav_inject_options_setup()
{
	global $bp;

	// loop all nav components
	foreach ( (array)$bp->bp_nav as $user_nav_item ) {
		// add navigation filter
		add_filter(
			'bp_get_displayed_user_nav_' . $user_nav_item['css_id'],
			'cfc_bp_nav_inject_options_filter',
			999,
			2
		);
	}
}

/**
 * Inject options nav onto end of active displayed user nav component
 *
 * @param string $html
 * @param array $user_nav_item
 * @return string
 */
function cfc_bp_nav_inject_options_filter( $html, $user_nav_item )
{
	// slug of nav item being filtered
	$component = $user_nav_item[ 'slug' ];

	// show options nav?
	$show = bp_is_current_component( $component );

	// special hack to handle profile in BP versions < 1.7
	if (
		'profile' == $component &&
		-1 == version_compare( BP_VERSION, '1.7') &&
		false == bp_is_my_profile()
	) {
		// force hide it
		$show = false;
	}

	// filter the show var because i love developers
	$show = (boolean) apply_filters( 'cfc_bp_nav_inject_options_show', $show, $user_nav_item );

	// ok, finally... should we show it?
	if ( true === $show ) {

		// yes, need to capture options nav output
		ob_start();

		// run options nav template tag
		bp_get_options_nav();

		// grab buffer and wipe it
		$nav = trim( (string) ob_get_clean() );

		// make sure the result has some meat
		if ( '' != $nav ) {
			// yep, inject options nav onto end of list item wrapped in special <ul>
			return preg_replace(
				'/(<\/li>.*)$/',
				'<ul class="profile-subnav">' . $nav . '</ul>$1',
				$html,
				1
			);
		}
	}

	// no changes
	return $html;
}


/**
 * Cleaner walker for wp_nav_menu()
 *
 * Walker_Nav_Menu (WordPress default) example output:
 *   <li id="menu-item-8" class="menu-item menu-item-type-post_type menu-item-object-page menu-item-8"><a href="/">Home</a></li>
 *   <li id="menu-item-9" class="menu-item menu-item-type-post_type menu-item-object-page menu-item-9"><a href="/sample-page/">Sample Page</a></l
 *
 * cfc_Nav_Walker example output:
 *   <li class="menu-home"><a href="/">Home</a></li>
 *   <li class="menu-sample-page"><a href="/sample-page/">Sample Page</a></li>
 */
class cfc_Nav_Walker extends Walker_Nav_Menu {
  function check_current($classes) {
    return preg_match('/(current[-_])|active|dropdown/', $classes);
  }

  function start_lvl(&$output, $depth = 0, $args = array()) {
    $output .= "\n<ul class=\"dropdown-menu\">\n";
  }

  function start_el(&$output, $item, $depth = 0, $args = array(), $id = 0) {
    $item_html = '';
    parent::start_el($item_html, $item, $depth, $args);

    if ($item->is_dropdown && ($depth === 0)) {
      $item_html = str_replace('<a', '<a class="dropdown-toggle" data-toggle="dropdown" data-target="#"', $item_html);
      $item_html = str_replace('</a>', ' <b class="caret"></b></a>', $item_html);
    }
    elseif (stristr($item_html, 'li class="divider')) {
      $item_html = preg_replace('/<a[^>]*>.*?<\/a>/iU', '', $item_html);
    }
    elseif (stristr($item_html, 'li class="dropdown-header')) {
      $item_html = preg_replace('/<a[^>]*>(.*)<\/a>/iU', '$1', $item_html);
    }

    $item_html = apply_filters('cfc_wp_nav_menu_item', $item_html);
    $output .= $item_html;
  }

  function display_element($element, &$children_elements, $max_depth, $depth = 0, $args, &$output) {
    $element->is_dropdown = ((!empty($children_elements[$element->ID]) && (($depth + 1) < $max_depth || ($max_depth === 0))));

    if ($element->is_dropdown) {
      $element->classes[] = 'dropdown';
    }

    parent::display_element($element, $children_elements, $max_depth, $depth, $args, $output);
  }
}

/**
 * Remove the id="" on nav menu items
 * Return 'menu-slug' for nav menu classes
 */
function cfc_nav_menu_css_class($classes, $item) {
  $slug = sanitize_title($item->title);
  $classes = preg_replace('/(current(-menu-|[-_]page[-_])(item|parent|ancestor))/', 'active', $classes);
  $classes = preg_replace('/^((menu|page)[-_\w+]+)+/', '', $classes);

  $classes[] = 'menu-' . $slug;

  $classes = array_unique($classes);

  return array_filter($classes, 'is_element_empty');
}
add_filter('nav_menu_css_class', 'cfc_nav_menu_css_class', 10, 2);
add_filter('nav_menu_item_id', '__return_null');

/**
 * Clean up wp_nav_menu_args
 *
 * Remove the container
 * Use cfc_Nav_Walker() by default
 */
function cfc_nav_menu_args($args = '') {
  $cfc_nav_menu_args['container'] = false;

  if (!$args['items_wrap']) {
    $cfc_nav_menu_args['items_wrap'] = '<ul class="%2$s">%3$s</ul>';
  }

  if (current_theme_supports('bootstrap-top-navbar') && !$args['depth']) {
    $cfc_nav_menu_args['depth'] = 2;
  }

  if (!$args['walker']) {
    $cfc_nav_menu_args['walker'] = new cfc_Nav_Walker();
  }

  return array_merge($args, $cfc_nav_menu_args);
}
add_filter('wp_nav_menu_args', 'cfc_nav_menu_args');
