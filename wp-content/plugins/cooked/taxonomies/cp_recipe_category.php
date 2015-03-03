<?php
if(!class_exists('cp_recipe_category_taxonomy')) {

	class cp_recipe_category_taxonomy {
		const TAXONOMY = "cp_recipe_category";

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
				
			$recipe_tax_slug = (get_option('cp_recipe_category_slug') ? get_option('cp_recipe_category_slug') : 'recipe-category');
			
			$labels = array(
				'name'                => __('Recipe Categories', 'cooked'),
				'singular_name'       => __('Category', 'cooked'),
				'search_items'        => __('Search Categories', 'cooked'),
				'all_items'           => __('All Categories', 'cooked'),
				'parent_item'         => __('Parent Category', 'cooked'),
				'parent_item_colon'   => __('Parent Category:', 'cooked'),
				'edit_item'           => __('Edit Category', 'cooked'), 
				'update_item'         => __('Update Category', 'cooked'),
				'add_new_item'        => __('Add New Category', 'cooked'),
				'new_item_name'       => __('New Category Name', 'cooked'),
				'menu_name'           => __('Categories', 'cooked')
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

	} // END class cp_recipe_category_taxonomy
} // END if(!class_exists('cp_recipe_category_taxonomy'))