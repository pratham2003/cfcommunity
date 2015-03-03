<?php
include_once(dirname(__FILE__) . '/twitter/versions-proxy.php');
include_once(dirname(__FILE__) . '/facebook/facebook.php');
include_once(dirname(__FILE__) . '/video-functions.php');
include_once(dirname(__FILE__) . '/user-functions.php');
include_once(dirname(__FILE__) . '/comments.php');

/**
 * Truncates a string to a certain word count.
 * @param  string  $input Text to be shortalized. Any HTML will be stripped.
 * @param  integer $words_limit number of words to return
 * @param  string $end the suffix of the shortalized text
 * @return string
 */
function crb_shortalize($input, $words_limit=15, $end='...') {
	$input = strip_tags($input);
	$words_limit = abs(intval($words_limit));

	if ($words_limit == 0) {
		return $input;
	}

	$words = str_word_count($input, 2, '0123456789');
	if (count($words) <= $words_limit + 1) {
		return $input;
	}
	
	$loop_counter = 0;
	foreach ($words as $word_position => $word) {
		$loop_counter++;
		if ($loop_counter==$words_limit + 1) {
			return substr($input, 0, $word_position) . $end;
		}
	}
}

/* Relative Time */
function boxy_relativeTime($ts)
{
    if(!ctype_digit($ts))
        $ts = strtotime($ts);

    $diff = time() - $ts;
    if($diff == 0)
        return __('now','basil');
    elseif($diff > 0)
    {
        $day_diff = floor($diff / 86400);
        if($day_diff == 0)
        {
            if($diff < 60) return  __('just now','basil');
            if($diff < 120) return __('1 minute ago','basil');
            if($diff < 3600) return floor($diff / 60).' '.__('minutes ago','basil');
            if($diff < 7200) return '1 hour ago';
            if($diff < 86400) return floor($diff / 3600).' '.__('hours ago','basil');
        }
        if($day_diff == 1) return __('Yesterday','basil');
        if($day_diff < 7) return $day_diff.' '.__('days ago','basil');
        if($day_diff < 31) return ceil($day_diff / 7).' '.__('weeks ago','basil');
        if($day_diff < 60) return __('last month','basil');
        return date_i18n(get_option('date_format'), $ts);
    }
    else
    {
        $diff = abs($diff);
        $day_diff = floor($diff / 86400);
        if($day_diff == 0)
        {
            if($diff < 120) return __('in a minute','basil');
            if($diff < 3600) return __('in','basil').' '.floor($diff / 60).' '.__('minutes','basil');
            if($diff < 7200) return __('in an hour','basil');
            if($diff < 86400) return __('in','basil').' '.floor($diff / 3600).' '.__('hours','basil');
        }
        if($day_diff == 1) return __('Tomorrow','basil');
        if($day_diff < 4) return date('l', $ts);
        if($day_diff < 7 + (7 - date('w'))) return __('next week','basil');
        if(ceil($day_diff / 7) < 4) return __('in','basil').' '.ceil($day_diff / 7).' '.__('weeks','basil');
        if(date('n', $ts) == date('n') + 1) return __('next month','basil');
        return date_i18n(get_option('date_format'), $ts);
    }
}

/* Clickable Links */
function boxy_char_shortalize($text, $length = 180, $append = '...') {
	$new_text = substr($text, 0, $length);
	if (strlen($text) > $length) {
		$new_text .= '...';
	}
	return $new_text;
}

function boxy_makeClickableLinks($text) {

	$text = str_replace(array('<','>'), array('&lt;','&gt;'),$text);
	return preg_replace('@(https?://([-\w\.]+)+(:\d+)?(/([\w/_\.-]*(\?\S+)?)?)?)@', '<a target="_blank" href="$1">$1</a>', $text);

}

/**
 * Crawls the taxonomy tree up to top level taxonomy ancestor and returns
 * that taxonomy as object. 
 * @param  int $term_id
 * @param  string $taxonomy Taxonomy slug
 * @return mixed object with the ancestor or false if the term or taxonomy don't exist
 */
function crb_taxonomy_ancestor($term_id, $taxonomy) {
	$term_obj = get_term_by('id', $term_id, $taxonomy);
	while ($term_obj->parent!=0) {
		$term_obj = get_term_by('id', $term_obj->parent, $taxonomy);
	}
	return get_term_by('id', $term_obj->term_id, $taxonomy);
}

/**
 * Shortcut for get_post_meta. 
 * @param  string $key 
 * @param  integer $id required if the function is not called in loop context
 * @return string custom field if it exist
 */
function crb_get_meta($key, $id=null) {
	if (!isset($id)) {
		global $post;
		if (empty($post->ID)) {
			return null;
		}
		$id = $post->ID;
	}
	return get_post_meta($id, $key, true);
}

/**
 * Gets all pages / posts which have the specified custom field. Does not check
 * whether it has any value - just for existence. 
 * @param  string $meta_key
 * @return array
 */
function crb_get_content_by_meta_key($meta_key) {
	global $wpdb;
	$result = $wpdb->get_col('
		SELECT DISTINCT(post_id)
		FROM ' . $wpdb->postmeta . '
		WHERE meta_key = "' . $meta_key . '"
	');
	if(empty($result)) {
		return array();
	}
	return $result;
}

/** 
 * For Blog Section ( "Posts page", "Archive", "Search" or "Single post" )
 * returns the ID of the "Page for Posts" or 0 if it's not setup
 * 
 * For single page or the front page, returns the ID of the page.
 * 
 * In all other cases(404, single pages on CPT), returns false.
 * 
 * @return int|bool The ID of the current page context, 0 or false.
 */
function crb_get_page_context() {
	$page_ID = false;

	if (is_page()) {
		$page_ID = get_the_ID();
	} elseif ( is_home() || is_archive() || is_search() || ( is_single() && get_post_type() == 'post' ) ) {
		$page_ID = get_option('page_for_posts');
	}

	return $page_ID;
}

/**
 * Generates a version for the given file.
 * 
 * Checks if the given file actually exists and returns its
 * last modified time. Otherwise, returns false.
 * 
 * @param string $src The url to the file, which version should be returned.
 * @return int|bool The last modified time of the given file or false.
 */
function crb_generate_file_version($src) {
	# Generate the absolute path to the file
	$file_path = str_replace(
		array(home_url('/'), '/'),
		array(ABSPATH, DIRECTORY_SEPARATOR),
		$src
	);

	$version = false; # Default version

	# Check if the given file really exists
	if ( file_exists($file_path) ) {
		# Use the last modified time of the file as a version
		$version = filemtime($file_path);
	}

	# Return version
	return $version;
}

/**
 * Enqueues a single JS file
 * 
 * @see crb_generate_file_version()
 * 
 * @param string $handle [required] Name used as a handle for the JS file
 * @param string $src    [required] The URL to the JS file, which should be enqueued
 * @param array  $dependencies [optional] An array of files' handle names that this file depends on
 * @param bool $in_footer [optional] Whether to enqueue in footer or not. Defaults to false
 */
function crb_enqueue_script($handle, $src, $dependencies=array(), $in_footer=false) {
	wp_enqueue_script($handle, $src, $dependencies, crb_generate_file_version($src), $in_footer);
}

/**
 * Enqueues a single CSS file
 * 
 * @see crb_generate_file_version()
 * 
 * @param string $handle [required] Name used as a handle for the CSS file
 * @param string $src    [required] The URL to the CSS file, which should be enqueued
 * @param array  $dependencies [optional] An array of files' handle names that this file depends on
 * @param string $media  [optional] String specifying the media for which this stylesheet has been defined. Defaults to all.
 */
function crb_enqueue_style($handle, $src, $dependencies=array(), $media='all') {
	wp_enqueue_style($handle, $src, $dependencies, crb_generate_file_version($src), $media);
}