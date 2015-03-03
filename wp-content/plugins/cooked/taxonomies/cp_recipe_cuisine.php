<?php
if(!class_exists('cp_recipe_cuisine_taxonomy')) {

	class cp_recipe_cuisine_taxonomy {
		const TAXONOMY = "cp_recipe_cuisine";

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
			$this->create_taxonomy();
		} // END public function init()

		/**
		 * Create the post type
		 */
		public function create_taxonomy() {
			
			$recipe_tax_slug = (get_option('cp_recipe_cuisine_slug') ? get_option('cp_recipe_cuisine_slug') : 'recipe-cuisine');
			
			$labels = array(
				'name'                => __('Cuisines', 'cooked'),
				'singular_name'       => __('Cuisine', 'cooked'),
				'search_items'        => __('Search Cuisines', 'cooked'),
				'all_items'           => __('All Cuisines', 'cooked'),
				'parent_item'         => __('Parent Cuisine', 'cooked'),
				'parent_item_colon'   => __('Parent Cuisine:', 'cooked'),
				'edit_item'           => __('Edit Cuisine', 'cooked'), 
				'update_item'         => __('Update Cuisine', 'cooked'),
				'add_new_item'        => __('Add New Cuisine', 'cooked'),
				'new_item_name'       => __('New Cuisine Name', 'cooked'),
				'menu_name'           => __('Cuisines', 'cooked')
			);

			$args = array(
				'hierarchical'        => true,
				'labels'              => $labels,
				'show_ui'             => true,
				'show_admin_column'   => true,
				'query_var'           => true,
				'rewrite'             => array( 'slug' => $recipe_tax_slug )
			);

			register_taxonomy(self::TAXONOMY, array('cp_recipe'), $args);
		}

	} // END class cp_recipe_cuisine_taxonomy
} // END if(!class_exists('cp_recipe_cuisine_taxonomy'))