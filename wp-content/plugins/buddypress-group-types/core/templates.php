<?php
/**
 * Implement ability to redefine type design per type basis
 * Get the folder from a theme, one for each type.
 * It will basically reflect the standard Groups files structure
 */
function bpgt_init_custom_templates() {
	global $bpgt_type;

	$is_gt_dir = empty( $bpgt_type ) ? false : true;

	if ( bp_is_group() || $is_gt_dir ) {
		bp_register_template_stack( 'bpgt_get_templates_dir', 1 );
	}
}

add_action( 'bp_screens', 'bpgt_init_custom_templates' );

/**
 * Define the path to templates, where you can override group types and directory pages
 *
 * @return string Empty string, if not GT dir, or single GT page
 */
function bpgt_get_templates_dir() {
	global $bpgt_type;

	$dir  = $slug = '';
	$type = bpgt_get_type( bp_get_current_group_id() );

	// we are on a single group page
	if ( ! empty( $type ) ) {
		$slug = $type->name;
	} else if ( ! empty( $bpgt_type ) ) { // we are on a group types directory page
		$slug = get_post_field( 'post_name', $bpgt_type->post_parent );
	}

	if ( empty( $slug ) ) {
		return '';
	}

	$theme_parent = get_template_directory() . '/' . BPGT_THEME . '/' . $slug;
	$theme_child  = get_stylesheet_directory() . '/' . BPGT_THEME . '/' . $slug;

	if ( is_dir( $theme_child ) ) {
		$dir = $theme_child;
	} else if ( is_dir( $theme_parent ) ) {
		$dir = $theme_parent;
	}

	return $dir;
}