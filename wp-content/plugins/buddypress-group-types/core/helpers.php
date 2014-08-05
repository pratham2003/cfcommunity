<?php
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Include template files for the plugin
 *
 * @param $template string Template file from /core/_part/ fodler without file extension
 * @param $options  array  Variables that we need to use inside that template
 */
function bpgt_the_template_part($template, $options = array()){
    $path = apply_filters( 'bpgt_the_template_part', BPGT_PATH . '/_parts/' . $template . '.php', $template, $options);

    if( file_exists($path) ){
        // hate doing this
        extract($options);
        include_once($path);
    }
}