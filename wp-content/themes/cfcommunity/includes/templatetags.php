<?php
/**
 * Create template tags that we can output inside our templates. This way we can seperate PHP from HTML as much as possible.
 *
 */


/**
 * Example: Make it easier to output custom fields inside a template.
 * Can be used inside any template like: get_custom_field('age_of_majorty', TRUE); ; 
 * 
 */
function get_custom_field($key, $echo = FALSE) {
    global $post;
    $custom_field = get_post_meta($post->ID, $key, true);
    if ($echo == FALSE) return $custom_field;
    echo $custom_field;
}
?>