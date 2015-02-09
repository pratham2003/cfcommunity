<?php
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Change the group type slug
 */
function bpgt_ajax_type_slug() {
	check_ajax_referer( 'samplepermalink', 'samplepermalinknonce' );

	$post_id = isset( $_POST['post_id'] ) ? intval( $_POST['post_id'] ) : 0;
	$title   = isset( $_POST['new_title'] ) ? $_POST['new_title'] : '';
	$slug    = isset( $_POST['new_slug'] ) ? $_POST['new_slug'] : null;

	$data = get_sample_permalink( $post_id, $title, $slug );

	if ( wp_update_post( array( 'ID' => $post_id, 'post_name' => $data[1] ) ) ) {
		wp_die( $data[1] );
	} else {
		wp_die( $slug );
	}
}

add_action( 'wp_ajax_bpgt_ajax_type_slug', 'bpgt_ajax_type_slug' );