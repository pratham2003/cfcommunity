<?php

if(!class_exists('cp_recipe_post_type')) {
	/**
	 * A cp_recipe_post_type class that provides additional meta fields
	 */
	class cp_recipe_post_type {
		const POST_TYPE = "cp_recipe";
		private $_meta  = array(
			'_cp_recipe_external_video',
			'_cp_recipe_short_description',
			'_cp_recipe_excerpt',
			'_cp_recipe_ingredients',
			'_cp_recipe_difficulty_level',
			'_cp_recipe_prep_time',
			'_cp_recipe_cook_time',
			'_cp_recipe_total_time',
			'_cp_recipe_directions',
			'_cp_recipe_additional_notes',
			'_cp_recipe_nutrition_servingsize',
			'_cp_recipe_nutrition_calories',
			'_cp_recipe_nutrition_sodium',
			'_cp_recipe_nutrition_potassium',
			'_cp_recipe_nutrition_protein',
			'_cp_recipe_nutrition_cholesterol',
			'_cp_recipe_nutrition_sugar',
			'_cp_recipe_nutrition_fat',
			'_cp_recipe_nutrition_satfat',
			'_cp_recipe_nutrition_polyunsatfat',
			'_cp_recipe_nutrition_monounsatfat',
			'_cp_recipe_nutrition_transfat',
			'_cp_recipe_nutrition_carbs',
			'_cp_recipe_nutrition_fiber',
			'_cp_recipe_admin_rating',
			'_cp_recipe_yields',
			'_cp_recipe_detailed_ingredients',
			'_cp_recipe_detailed_directions'
		);
		
		/**
		 * The Constructor
		 */
		public function __construct() {
			// register actions
			add_action('init', array(&$this, 'init'));
			add_action('admin_init', array(&$this, 'admin_init'));
		} // END public function __construct()

		/**
		 * hook into WP's init action hook
		 */
		public function init() {
			// Initialize Post Type
			$this->create_post_type();
			add_action('save_post', array(&$this, 'save_post'));
		} // END public function init()

		/**
		 * Create the post type
		 */
		public function create_post_type() {
			
			$recipe_slug = (get_option('cp_recipe_slug') ? get_option('cp_recipe_slug') : 'recipe');
			
			register_post_type(self::POST_TYPE,
				array(
					'labels' => array(
						'name'               => __( 'Recipes', 'cooked' ),
						'singular_name'      => __( 'Recipe', 'cooked' ),
						'menu_name'          => __( 'Recipes', 'cooked' ),
						'name_admin_bar'     => __( 'Recipe', 'cooked' ),
						'add_new'            => __( 'Add New', 'cooked' ),
						'add_new_item'       => __( 'Add New Recipe', 'cooked' ),
						'new_item'           => __( 'New Recipe', 'cooked' ),
						'edit_item'          => __( 'Edit Recipe', 'cooked' ),
						'view_item'          => __( 'View Recipe', 'cooked' ),
						'all_items'          => __( 'All Recipes', 'cooked' ),
						'search_items'       => __( 'Search Recipes', 'cooked' ),
						'parent_item_colon'  => __( 'Parent Recipes:', 'cooked' ),
						'not_found'          => __( 'No Recipes found.', 'cooked' ),
						'not_found_in_trash' => __( 'No Recipes found in Trash.', 'cooked' )
					),
					'show_in_admin_bar' => true,
					'public' => true,
					'has_archive' => false,
					'description' => __('Recipes','cooked'),
					'supports' => array(
						'title', 'comments', 'author', 'tags', 'thumbnail'
					),
					'rewrite' => array(
						'with_front' => false,
						'slug' => $recipe_slug
					),
					'menu_icon' => 'dashicons-carrot'
				)
			);
		}

		/**
		 * Save the metaboxes for this custom post type
		 */
		public function save_post($post_id) {
			
			// verify if this is an auto save routine.
			// If it is our form has not been submitted, so we dont want to do anything
			if(defined('DOING_AUTOSAVE') && DOING_AUTOSAVE)
				return $post_id;
			
			if(empty($_POST))
				return $post_id;
				
			if (!isset($_POST['cp_recipe_edit_nonce']))
				return $post_id;

			if ( !wp_verify_nonce( $_POST['cp_recipe_edit_nonce'], 'cooked_save_recipe' ))
				return $post_id;

			if(isset($_POST['post_type']) && $_POST['post_type'] === self::POST_TYPE && current_user_can('edit_post', $post_id)) {
				foreach($this->_meta as $field_name) {
					
					// Update the post's meta field
					if (isset($_POST[$field_name])) {

						if($field_name === '_cp_recipe_detailed_ingredients') {
							foreach($_POST[$field_name] as $index => $entry) {
								if($entry['type'] === 'ingredient') {
									if(!empty($entry['name'])) {
										$post_title = $entry['name'];
										$post_obj = get_page_by_title($post_title, OBJECT, 'cp_ingredient');
										if(!is_object($post_obj)) {
											$post_arr = array(
												'post_type' => 'cp_ingredient',
												'post_title' => $post_title,
												'post_status' => 'publish'
											);
											wp_insert_post($post_arr);
										}
									}

									if(!empty($entry['measurement'])) {
										$measurement_name = $entry['measurement'];
										$measurement_exists = term_exists($measurement_name, 'cp_recipe_measurement');
										if(!$measurement_exists) {
											wp_insert_term($measurement_name, 'cp_recipe_measurement');
										}
									}

									if(!empty($entry['amount'])) {
										$amount = htmlentities($entry['amount']);
										switch($amount):
											case '&frac14;':
											case '&#188;':
											case '&#xBC;':
												$amount = '1/4';
											break;
											case '&frac12;':
											case '&#189;':
											case '&#xBD;':
												$amount = '1/2';
											break;
											case '&frac34;':
											case '&#190;':
											case '&#xBE;':
												$amount = '3/4';
											break;
										endswitch;
										if(strpos($amount, '.') === false && strpos($amount, '/') !== false) { // it probably is a fraction
											$amount = cp_calculate_amount($amount, 'decimal');
											$_POST[$field_name][$index]['amount'] = $amount;
										}
									}
								}
							}
						}
						update_post_meta($post_id, $field_name, $_POST[$field_name]);
					} else {
						delete_post_meta($post_id, $field_name);
					}
				}
				
				// Save content to hidden editor for search improvements
				$recipe_title = get_the_title($post_id);
				$recipe_short_desc = get_post_meta($post_id, '_cp_recipe_short_description', true);
				$recipe_yields = get_post_meta($post_id, '_cp_recipe_yields', true);
				$recipe_notes = get_post_meta($post_id, '_cp_recipe_additional_notes', true);
				
				$recipe_excerpt = get_post_meta($post_id, '_cp_recipe_excerpt', true);
				if (!$recipe_excerpt):
					$recipe_excerpt = $recipe_short_desc;
				endif;
				
				$recipe_ingredients = get_post_meta($post_id, '_cp_recipe_detailed_ingredients',true);
				if (!empty($recipe_ingredients)):
					ob_start();
					foreach($recipe_ingredients as $ingredient):
						echo cp_calculate_amount($ingredient['amount'],'fraction').' '.$ingredient['measurement'].' '.$ingredient['name'].'
						';
					endforeach;
					$recipe_ingredients = ob_get_clean();
				else :
					$recipe_ingredients = get_post_meta($post_id, '_cp_recipe_ingredients', true);
				endif;
				
				$recipe_directions = get_post_meta($post_id, '_cp_recipe_detailed_directions',true);
				if (!empty($recipe_directions)):
					ob_start();
					foreach($recipe_directions as $direction):
						echo $direction['value'].'
						';
					endforeach;
					$recipe_directions = ob_get_clean();
				else :
					$recipe_directions = get_post_meta($post_id, '_cp_recipe_directions', true);
				endif;
				
				$recipe_content = '<p>'.$recipe_short_desc.'</p><p>'.$recipe_yields.'</p><p>'.$recipe_ingredients.'</p><p>'.$recipe_directions.'</p><p>'.$recipe_notes.'</p>';
				
				// Update Post Content
				$new_recipe_content = array(
				    'ID'           => $post_id,
				    'post_content' => $recipe_content,
				    'post_excerpt' => $recipe_excerpt
				);
				
				// unhook this function so it doesn't loop infinitely
				remove_action( 'save_post', array(&$this, 'save_post') );

				// update the post, which calls save_post again
				wp_update_post( $new_recipe_content );
		
				// re-hook this function
				add_action( 'save_post', array(&$this, 'save_post') );
				
			} else {
				return;
			}
		} // END public function save_post($post_id)

		/**
		 * hook into WP's admin_init action hook
		 */
		public function admin_init() {
			// Add metaboxes
			add_action('add_meta_boxes', array(&$this, 'add_meta_boxes'));
		} // END public function admin_init()

		/**
		 * hook into WP's add_meta_boxes action hook
		 */
		public function add_meta_boxes() {
			// Add this metabox to every selected post
			add_meta_box(
				sprintf('cooked_plugin_%s_section', self::POST_TYPE),
				'Recipe Information',
				array(&$this, 'add_inner_meta_boxes'),
				self::POST_TYPE
			);
		} // END public function add_meta_boxes()

		/**
		 * called off of the add meta box
		 */
		public function add_inner_meta_boxes($post) {
			// Render the job order metabox
			include(sprintf("%s/templates/%s_metabox.php", CP_PLUGIN_DIR, self::POST_TYPE));
		} // END public function add_inner_meta_boxes($post)
	} // END class cp_recipe_post_type
} // END if(!class_exists('cp_recipe_post_type'))