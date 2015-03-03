<?php
if(!class_exists('cp_recipe_measurement_taxonomy')) {
	
	class cp_recipe_measurement_taxonomy {
		const TAXONOMY = "cp_recipe_measurement";

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
			
			$editable_taxes = cp_advanced_editable_taxes_settings();
			if (in_array('measurements',$editable_taxes)):
				$editable = true;
			else :
				$editable = false;
			endif;	
				
			$labels = array(
				'name'                => __('Measurements', 'cooked'),
				'singular_name'       => __('Measurement', 'cooked'),
				'search_items'        => __('Search Measurements', 'cooked'),
				'all_items'           => __('All Measurements', 'cooked'),
				'parent_item'         => __('Parent Measurement', 'cooked'),
				'parent_item_colon'   => __('Parent Measurement:', 'cooked'),
				'edit_item'           => __('Edit Measurement', 'cooked'), 
				'update_item'         => __('Update Measurement', 'cooked'),
				'add_new_item'        => __('Add New Measurement', 'cooked'),
				'new_item_name'       => __('New Measurement Name', 'cooked'),
				'menu_name'           => __('Measurements', 'cooked')
			);

			$args = array(
				'hierarchical'        => true,
				'labels'              => $labels,
				'show_ui'             => $editable,
				'show_admin_column'   => $editable,
				'query_var'           => true,
				'rewrite'             => false
			);

			register_taxonomy(self::TAXONOMY, array('cp_recipe'), $args);
		}

	} // END class cp_recipe_measurement_taxonomy
} // END if(!class_exists('cp_recipe_measurement_taxonomy'))