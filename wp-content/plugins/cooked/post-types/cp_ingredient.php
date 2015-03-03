<?php

if(!class_exists('cp_ingredient_post_type')) {

	class cp_ingredient_post_type {
		const POST_TYPE = "cp_ingredient";
		
		/**
		 * The Constructor
		 */
		public function __construct() {
			// register actions
			add_action('init', array(&$this, 'init'));
		} // END public function __construct()

		/**
		 * hook into WP's init action hook
		 */
		public function init() {
			// Initialize Post Type
			$this->create_post_type();
		} // END public function init()

		/**
		 * Create the post type
		 */
		public function create_post_type() {
			
			$editable_taxes = cp_advanced_editable_taxes_settings();
			if (in_array('ingredients',$editable_taxes)):
				$editable = true;
			else :
				$editable = false;
			endif;	
			
			register_post_type(self::POST_TYPE,
				array(
					'labels' => array(
						'name'               => __( 'Ingredients', 'cooked' ),
						'singular_name'      => __( 'Ingredient', 'cooked' ),
						'menu_name'          => __( 'Ingredients', 'cooked' ),
						'name_admin_bar'     => __( 'Ingredient', 'cooked' ),
						'add_new'            => __( 'Add New', 'cooked' ),
						'add_new_item'       => __( 'Add New Ingredient', 'cooked' ),
						'new_item'           => __( 'New Ingredient', 'cooked' ),
						'edit_item'          => __( 'Edit Ingredient', 'cooked' ),
						'view_item'          => __( 'View Ingredient', 'cooked' ),
						'all_items'          => __( 'Ingredients', 'cooked' ),
						'search_items'       => __( 'Search Ingredients', 'cooked' ),
						'parent_item_colon'  => __( 'Parent Ingredients:', 'cooked' ),
						'not_found'          => __( 'No Ingredients found.', 'cooked' ),
						'not_found_in_trash' => __( 'No Ingredients found in Trash.', 'cooked' )
					),
					'show_in_admin_bar' => $editable,
					'public' => $editable,
					'show_ui' => $editable,
					'has_archive' => false,
					'description' => __('Ingredients','cooked'),
					'supports' => array(
						'title'
					),
					'show_in_menu' => 'edit.php?post_type=cp_recipe',
					'rewrite' => false,
					'menu_icon' => 'dashicons-carrot'
				)
			);
		}

	} // END class cp_ingredient_post_type
} // END if(!class_exists('cp_ingredient_post_type'))