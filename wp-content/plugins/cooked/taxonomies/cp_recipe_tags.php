<?php
if(!class_exists('cp_recipe_tags_taxonomy')) {

	class cp_recipe_tags_taxonomy {
		const TAXONOMY = "cp_recipe_tags";

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
			$labels = array(
				'name'                => __('Recipe Tags', 'cooked'),
				'singular_name'       => __('Tag', 'cooked'),
				'search_items'        => __('Search Tags', 'cooked'),
				'all_items'           => __('All Tags', 'cooked'),
				'parent_item'         => __('Parent Tag', 'cooked'),
				'parent_item_colon'   => __('Parent Tag:', 'cooked'),
				'edit_item'           => __('Edit Tag', 'cooked'), 
				'update_item'         => __('Update Tag', 'cooked'),
				'add_new_item'        => __('Add New Tag', 'cooked'),
				'new_item_name'       => __('New Tag Name', 'cooked'),
				'menu_name'           => __('Tags', 'cooked')
			);

			$args = array(
				'hierarchical'        => false,
				'labels'              => $labels,
				'show_ui'             => true,
				'show_admin_column'   => true,
				'query_var'           => true,
				'rewrite'             => array( 'slug' => 'recipe-tags' )
			);

			register_taxonomy(self::TAXONOMY, array('cp_recipe'), $args);
		}

	} // END class cp_recipe_cuisine_taxonomy
} // END if(!class_exists('cp_recipe_cuisine_taxonomy'))