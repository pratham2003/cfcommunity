<?php
function bpgt_register_cpts() {
	register_post_type( BPGT_CPT_TYPE, array(
		'label'               => __( 'Group Types', 'bpgt' ),
		'public'              => false,
		'hierarchical'        => false,
		'exclude_from_search' => true,
		'publicly_queryable'  => false,
		'show_ui'             => false,
		'show_in_menu'        => false,
		'show_in_nav_menus'   => false,
		'show_in_admin_bar'   => false,
		'capability_type'     => 'post',
		'supports'            => array( 'title', 'editor', 'thumbnail' ),
		'has_archive'         => false,
		'rewrite'             => false,
		'query_var'           => false,
		'can_export'          => true,
		'delete_with_user'    => false
	) );

	register_post_type( BPGT_CPT_FIELD, array(
		'label'               => __( 'Group Fields', 'bpgt' ),
		'public'              => false,
		'hierarchical'        => false,
		'exclude_from_search' => true,
		'publicly_queryable'  => false,
		'show_ui'             => false,
		'show_in_menu'        => false,
		'show_in_nav_menus'   => false,
		'show_in_admin_bar'   => false,
		'capability_type'     => 'post',
		'supports'            => array( 'title', 'editor' ),
		'has_archive'         => false,
		'rewrite'             => false,
		'query_var'           => false,
		'can_export'          => true,
		'delete_with_user'    => false
	) );
}

add_action( 'init', 'bpgt_register_cpts' );