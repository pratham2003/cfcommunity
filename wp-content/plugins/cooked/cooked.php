<?php

/*
Plugin Name: Cooked Recipe Plugin
Description: A super-powered recipe plugin for WordPress
Tags: recipes, cooked, food, cooking
Author URI: http://www.boxystudio.com
Author: Boxy Studio
Donate link: http://www.boxystudio.com/#coffee
Requires at least: 3.9
Tested up to: 4.1
Version: 2.0.3
*/

require_once('wp-updates-plugin.php');
new WPUpdatesPluginUpdater_665( 'http://wp-updates.com/api/2/plugin', plugin_basename(__FILE__));

// Generate the default stylesheets if needed
$upload_dir = wp_upload_dir();
$main_upload_dir = $upload_dir['basedir'];
$cooked_upload_dir = $upload_dir['basedir'] . DIRECTORY_SEPARATOR . 'cooked';
if (!is_dir($cooked_upload_dir) && is_writable($main_upload_dir)) {
	wp_mkdir_p($cooked_upload_dir);
	
	$color_theme_file = dirname(__FILE__) . DIRECTORY_SEPARATOR . 'css' . DIRECTORY_SEPARATOR . 'color-theme.css';
	$new_file = $cooked_upload_dir . DIRECTORY_SEPARATOR . 'color-theme.css';
	$color_theme_content = file_get_contents($color_theme_file);
	file_put_contents($new_file, $color_theme_content);
	
	$responsive_file = dirname(__FILE__) . DIRECTORY_SEPARATOR . 'css' . DIRECTORY_SEPARATOR . 'front-end-responsive.css';
	$new_file = $cooked_upload_dir . DIRECTORY_SEPARATOR . 'front-end-responsive.css';
	$responsive_content = file_get_contents($responsive_file);
	file_put_contents($new_file, $responsive_content);
}
// END Generate

define('CP_DEMO_MODE', get_option('cp_demo_mode'));
define('CP_PLUGIN_URL', WP_PLUGIN_URL . '/cooked');
define('CP_PLUGIN_DIR', dirname(__FILE__));
define('CP_STYLESHEET_DIR', get_stylesheet_directory());
define('CP_PLUGIN_TEMPLATES_DIR', dirname(__FILE__) . DIRECTORY_SEPARATOR . 'templates' . DIRECTORY_SEPARATOR);
define('CP_PLUGIN_SECTIONS_DIR', CP_PLUGIN_TEMPLATES_DIR . 'cp_recipe' . DIRECTORY_SEPARATOR);
define('CP_PLUGIN_VIEWS_DIR', CP_PLUGIN_TEMPLATES_DIR . 'views' . DIRECTORY_SEPARATOR);
define('CP_UPLOADS_DIR', $upload_dir['baseurl'] . DIRECTORY_SEPARATOR . 'cooked');

if ( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
    define('CP_WOOCOMMERCE_ACTIVE', true);
} else {
	define('CP_WOOCOMMERCE_ACTIVE', false);
}

if(!class_exists('cooked_plugin')) {
	class cooked_plugin {
		/**
		 * Construct the plugin object
		 */
		public function __construct() {
		
			require_once(sprintf("%s/post-types/cp_recipe.php", CP_PLUGIN_DIR));
			$cp_recipe_post_type = new cp_recipe_post_type();
		
			require_once(sprintf("%s/post-types/cp_ingredient.php", CP_PLUGIN_DIR));
			$cp_ingredient_post_type = new cp_ingredient_post_type();
			
			$enabled_taxonomies = $this->cp_recipe_tax_settings();
			
			if (in_array('category',$enabled_taxonomies)):

				require_once(sprintf("%s/taxonomies/cp_recipe_category.php", CP_PLUGIN_DIR));
				$cp_recipe_category_taxonomy = new cp_recipe_category_taxonomy();
			
			endif;
			
			if (in_array('cuisine',$enabled_taxonomies)):

				require_once(sprintf("%s/taxonomies/cp_recipe_cuisine.php", CP_PLUGIN_DIR));
				$cp_recipe_cuisine_taxonomy = new cp_recipe_cuisine_taxonomy();
				
			endif;
			
			if (in_array('method',$enabled_taxonomies)):
			
				require_once(sprintf("%s/taxonomies/cp_recipe_cooking_method.php", CP_PLUGIN_DIR));
				$cp_recipe_cooking_method_taxonomy = new cp_recipe_cooking_method_taxonomy();
			
			endif;
			
			if (in_array('tags',$enabled_taxonomies)):
			
				require_once(sprintf("%s/taxonomies/cp_recipe_tags.php", CP_PLUGIN_DIR));
				$cp_recipe_tags_taxonomy = new cp_recipe_tags_taxonomy();
				
			endif;

			require_once(sprintf("%s/taxonomies/cp_recipe_measurement.php", CP_PLUGIN_DIR));
			$cp_recipe_measurement_taxonomy = new cp_recipe_measurement_taxonomy();

			require_once(sprintf("%s/includes/pointers.php", CP_PLUGIN_DIR));
			require_once(sprintf("%s/includes/functions.php", CP_PLUGIN_DIR));
			require_once(sprintf("%s/includes/profiles.php", CP_PLUGIN_DIR));
			require_once(sprintf("%s/includes/shortcodes.php", CP_PLUGIN_DIR));
			require_once(sprintf("%s/includes/ajax/admin-actions.php", CP_PLUGIN_DIR));
			require_once(sprintf("%s/includes/widgets.php", CP_PLUGIN_DIR));
			require_once(sprintf("%s/includes/actions.php", CP_PLUGIN_DIR));
			require_once(sprintf("%s/includes/edit-recipe.php", CP_PLUGIN_DIR));

			add_action('admin_init', array(&$this, 'admin_init'));
			add_action('admin_menu', array(&$this, 'add_menu'));
			add_action('admin_enqueue_scripts', array(&$this, 'admin_styles'));
			add_action('admin_head', array(&$this, 'check_measurements'));
			add_action('admin_enqueue_scripts', 'wp_enqueue_media');
			add_action('admin_enqueue_scripts', array(&$this, 'tooltips'));
			add_filter('single_template', array(&$this, 'post_type_templates'));
			add_filter('archive_template', array(&$this, 'archive_template'));
			add_action('admin_notices', array(&$this, 'cp_admin_settings_notice'));
			add_action('wp_enqueue_scripts', array(&$this, 'front_end_scripts'));
			add_action('wp_head', array(&$this, 'inline_scripts'));
			add_action('admin_head', array(&$this, 'admin_inline_scripts'));
			add_action('the_content', array(&$this, 'display_view_markup'));
			add_action('after_setup_theme', array(&$this, 'cp_add_thumbnail_support'));
			
			$fes_settings = cp_recipe_fes_settings();				
			if (in_array('fes_enabled', $fes_settings)) :
				add_action('admin_menu', array(&$this, 'cooked_add_pending_recipes_bubble' ));
				add_action('admin_notices', array(&$this, 'cooked_pending_notice' ));
			endif;
			
			add_filter('the_content', array(&$this, 'cooked_featured_image_in_feed' ));
			add_action('comment_post', array(&$this, 'save_comment_meta_data'));
			add_filter('preprocess_comment', array(&$this, 'verify_comment_meta_data'));
			add_filter( 'manage_cp_recipe_posts_columns', array(&$this, 'cp_add_recipe_thumbnail_column'));
			add_action( 'manage_cp_recipe_posts_custom_column', array(&$this, 'cp_add_recipe_thumbnail_value'),10,2);

			add_action('wp_ajax_nopriv_cp_handleautocomplete', 'cp_handleautocomplete');
			add_action('wp_ajax_cp_handleautocomplete', 'cp_handleautocomplete');

			add_image_size('cp_960_425', 1920, 850, true);
			add_image_size('cp_431_368', 862, 736, true);
			add_image_size('cp_431_424', 862, 848, true);
			add_image_size('cp_298_192', 596, 384, true);
			add_image_size('cp_500_500', 1000, 1000, false);

		} // END public function __construct
		
		public static function cp_admin_settings_notice() {
		
			$settings_saved = get_option('cp_settings_saved');
			$screen = get_current_screen();
			
			if (!$settings_saved && $screen->id != 'cp_recipe_page_cooked_plugin'):
			
			    ?>
			    <div class="update-nag">
			    	<p style="font-size:17px; padding:0 10px; margin:10px 0 5px;"><strong><?php _e( 'A Message from Cooked:','cooked'); ?></strong></p>
			        <?php echo '<p style="padding:0 10px; margin-top:0;">Be sure to go to the <a href="'.get_admin_url().'edit.php?post_type=cp_recipe&page=cooked_plugin">Settings</a> screen to set up Cooked. Save the settings to remove this nag.</p>'; ?>
			    </div>
			    <?php

			endif;
			
			$cp_fi_transfer_done = get_option('cp_fi_transfer_done');
			
			if (!$cp_fi_transfer_done):
			
			    ?>
			    <div class="update-nag">
			    	<p style="font-size:17px; padding:0 10px; margin:10px 0 5px;"><strong><?php _e( 'Important Update from Cooked:','cooked'); ?></strong></p>
			        <?php echo '<p style="padding:0 10px; margin-top:0;">Recipe images have been moved to use the "Featured Image" for each post. Please click the button to complete this process.</p><p style="padding:0 10px"><a href="'.get_admin_url().'/?update_cooked_recipe_images=true" class="button primary">Update Recipe Images</a></p>'; ?>
			    </div>
			    <?php

			endif;
			
			$cp_pc_transfer_done = get_option('cp_pc_transfer_done');
			
			if (!$cp_pc_transfer_done):
			
			    ?>
			    <div class="update-nag">
			    	<p style="font-size:17px; padding:0 10px; margin:10px 0 5px;"><strong><?php _e( 'Important Update from Cooked:','cooked'); ?></strong></p>
			        <?php echo '<p style="padding:0 10px; margin-top:0;">Recipe search has been drastically improved! Please click the button to complete this process.</p><p style="padding:0 10px"><a href="'.get_admin_url().'/?update_cooked_search_content=true" class="button primary">Update Search Functionality</a></p>'; ?>
			    </div>
			    <?php

			endif;
			
		}
		
		public function cp_add_thumbnail_support() {
			add_theme_support( 'post-thumbnails' );
		}
		
		// ------------------------------------------------------------
		// Customize the Recipes RSS Feed to include the image
		public function cooked_featured_image_in_feed( $content ) {
		    global $post;
		    if( is_feed() ) {
		        if ( has_post_thumbnail( $post->ID ) ){
		            $output = '<p>'.get_the_post_thumbnail( $post->ID, 'cp_960_425', array( 'style' => 'max-width:100%; width:100%; height:auto; display:block;' ) ).'</p>';
		            $content = $output . $content;
		        }
		    }
		    return $content;
		}
		
		// ------------------------------------------------------------
		// Add Thumbnails to Recipe management screen

		public function cp_add_recipe_thumbnail_column($cols) {
		    $cols['thumbnail'] = __('Image','cooked');
		    return $cols;
		}
		public function cp_add_recipe_thumbnail_value($column_name, $post_id) {
			
			if ( 'thumbnail' == $column_name ) {
	        
	        	if (has_post_thumbnail( $post_id )) :
					$image_url = wp_get_attachment_image_src( get_post_thumbnail_id( $post_id ), 'thumbnail' );
					if (is_array($image_url)) { $image_url = $image_url[0]; }
				endif;
	        
	            if ( isset($image_url) && $image_url ) {
	                echo '<img style="margin:5px 0;" src="'.$image_url.'" width="100" />';
	            } else {
	                echo __('None','cooked');
	            }
	            
	        }
	    }

		public static function activate() {
			// Do nothing
		}

		public static function deactivate() {
			// Do nothing
		}
		
		static function default_measurements() {
			$default_measurements = array(
				'count',
				'cup',
				'cups',
				'oz.',
				'tbsp',
				'tsp',
				'g',
				'mg',
				'kg',
				'quart',
				'quarts',
				'pint',
				'pints',
				'liter',
				'liters',
				'ml',
				'lb',
				'lbs',
				'dash',
				'pinch'
			);

			return $default_measurements;
		}
		
		public function check_measurements(){
			$measurement_terms = get_terms('cp_recipe_measurement',array( 'hide_empty' => false ));
			if (empty($measurement_terms)):
				$default_measurements = $this->default_measurements();
				foreach($default_measurements as $measurement_name):
					//$measurement_exists = term_exists($measurement_name, 'cp_recipe_measurement');
					//if(!$measurement_exists) {
						wp_insert_term($measurement_name, 'cp_recipe_measurement');
					//}
				endforeach;
			endif;
		}

		public function admin_init() {
			
			if (isset($_GET['update_cooked_recipe_images'])):
				
				// NEW SINCE VERSION 1.4.4
				// Update the Feature Images
				$cp_fi_transfer_done = get_option('cp_fi_transfer_done');
				
				if (!$cp_fi_transfer_done):
			
					// Let's loop through the recipe images and set them as the featured image for each recipe
					$args = array(
						'post_type' => 'cp_recipe',
						'posts_per_page' => -1
					);
					$recipes = query_posts($args);
							
					foreach($recipes as $recipe):
						$entry_image = get_post_meta($recipe->ID, '_cp_recipe_image', true);
						add_post_meta($recipe->ID, '_thumbnail_id', $entry_image);
					endforeach;
		
					update_option('cp_fi_transfer_done',true);
				
				endif;
			
			endif;
			
			if (isset($_GET['update_cooked_search_content'])):
			
				// NEW SINCE VERSION 1.5
				// Update the recipe post content for super-fast searches.
				$cp_pc_transfer_done = get_option('cp_pc_transfer_done');
				
				if (!$cp_pc_transfer_done):
			
					// Let's loop through the recipe images and set them as the featured image for each recipe
					$args = array(
						'post_type' => 'cp_recipe',
						'posts_per_page' => -1
					);
					$recipes = query_posts($args);
							
					foreach($recipes as $recipe):
						
						$recipe_short_desc = get_post_meta($recipe->ID, '_cp_recipe_short_description', true);
						$recipe_yields = get_post_meta($recipe->ID, '_cp_recipe_yields', true);
						$recipe_ingredients = get_post_meta($recipe->ID, '_cp_recipe_ingredients', true);
						$recipe_directions = get_post_meta($recipe->ID, '_cp_recipe_directions', true);
						$recipe_notes = get_post_meta($recipe->ID, '_cp_recipe_additional_notes', true);
						
						$post_content = '<p>'.$recipe_short_desc.'</p><p>'.$recipe_yields.'</p><p>'.$recipe_ingredients.'</p><p>'.$recipe_directions.'</p><p>'.$recipe_notes.'</p>';
						
						// Update Post Content
						$new_post_content = array(
						    'ID'           => $recipe->ID,
						    'post_content' => $post_content
						);
						
						wp_update_post( $new_post_content );
					endforeach;
					
					update_option('cp_pc_transfer_done',true);
				
				endif;
			
			endif;
			
			// Set up the settings for this plugin
			$this->init_settings();
			$this->export_settings();
			$this->import_settings();
			// Possibly do additional admin_init tasks
			
		} // END public static function activate

		static function plugin_settings() {
			$plugin_options = array(
				'cp_recipe_template',
				'cooked_email_logo',
				'cp_recipe_browse_options',
				'cp_recipe_fes_limit',
				'cp_fes_new_recipe_default',
				'cp_main_color',
				'cp_light_color',
				'cp_dark_color',
				'cp_sharing_options',
				'cp_facebook_app_id',
				'cp_action_options',
				'cp_premium_actions',
				'cp_reviews_comments',
				'cp_recipe_list_view',
				'cp_recipes_list_view_page',
				'cp_profile_page',
				'cp_recipe_taxonomies',
				'cp_info_options',
				'cp_advanced_editable_taxes',
				'cp_list_view_pagination',
				'cp_recipe_slug',
				'cp_recipe_category_slug',
				'cp_recipe_cuisine_slug',
				'cp_recipe_method_slug',
				'cp_star_review_options',
				'cp_fes_options',
				'cp_settings_saved',
				'cp_recipes_fes_user_roles',
				'cp_fes_welcome_message',
				'cp_fes_no_access_message',
				'cp_recipes_page_template',
				'cp_plugin_styling',
				'cp_color_theme',
				'cp_disable_plugin_styling',
				'cp_prep_time_max_hrs',
				'cp_cook_time_max_hrs',
				'cp_responsive_break_one',
				'cp_responsive_break_two',
				'cp_responsive_break_three',
				
				'cooked_registration_email_subject',
				'cooked_approval_email_subject',
				'cooked_admin_recipe_email_subject',
				'cooked_registration_email_content',
				'cooked_approval_email_content',
				'cooked_admin_recipe_email_content',
			);

			return $plugin_options;
		}
		
		public function cp_recipe_tax_settings() {
			$recipe_tax_value = get_option('cp_recipe_taxonomies');
			return $recipe_tax_value != '' ? $recipe_tax_value : array();
		}

		public function init_settings() {
			// register the settings for this plugin
			$plugin_options = $this->plugin_settings();
			foreach($plugin_options as $option_name) {
				register_setting('cooked_plugin-group', $option_name);
			}
		} // END public function init_custom_settings()


		/**********************
		ADD MENUS FUNCTION
		**********************/
		
		public function add_menu() {
			$fes_settings = cp_recipe_fes_settings();				
			if (in_array('fes_enabled', $fes_settings)) :
				add_submenu_page('edit.php?post_type=cp_recipe', __('Pending','cooked'), __('Pending','cooked'), 'manage_options', 'cooked_pending', array(&$this, 'admin_pending_list'));
			endif;
			add_submenu_page('edit.php?post_type=cp_recipe', __('Settings','cooked'), __('Settings','cooked'), 'manage_options', 'cooked_plugin', array(&$this, 'plugin_settings_page'));
		} // END public function add_menu()

		// SETTINGS MENU

		public function plugin_settings_page() {
			if(!current_user_can('manage_options')) {
				wp_die(__('You do not have sufficient permissions to access this page.', 'cooked'));
			}
			include(sprintf("%s/templates/settings.php", CP_PLUGIN_DIR));
		} // END public function plugin_settings_page()
		
		// Cooked Pending Recipes List
		public function admin_pending_list() {
			if(!current_user_can('manage_options')) {
				wp_die(__('You do not have sufficient permissions to access this page.', 'booked'));
			}
			include(sprintf("%s/templates/pending-list.php", CP_PLUGIN_DIR));
		}

		// Add Pending Recipes Bubble
		public function cooked_add_pending_recipes_bubble() {
		
		  	global $submenu;
		
		  	$pending = cp_pending_recipes_count();
		  	if ( $pending ) :
			  	foreach ( $submenu as $key => $menu_array ) :
			  	
			  		foreach($menu_array as $item_key => $menu_item):
			  			if ($menu_item[2] == 'cooked_pending'):
			  				$submenu[$key][$item_key][0] .= " <span style='position:relative; top:1px; margin:-2px 0 0 2px' class='update-plugins count-$pending' title='$pending'><span style='padding:0 6px 0 4px; min-width:7px; text-align:center;' class='update-count'>" . $pending . "</span></span>";
			  			endif;
			  		endforeach;
					
				endforeach;
			endif;
		
		}
		
		public function cooked_pending_notice() {
			
			if (current_user_can('manage_options')):
		
				$pending = cp_pending_recipes_count();
				$page = (isset($_GET['page']) ? $page = $_GET['page'] : $page = false);
				if ($pending && $page != 'cooked_pending'):
					
					echo '<div class="update-nag">';
						echo sprintf( _n( 'There is %s pending recipe.', 'There are %s pending recipes.', $pending, 'cooked' ), $pending ).' <a href="'.get_admin_url().'edit.php?post_type=cp_recipe&page=cooked_pending">'._n('View Pending Recipe','View Pending Recipes',$pending,'cooked').'</a>';
					echo '</div>';
				
				endif;
			
			endif;
		
		}

		public function admin_styles() {
			// Fonts
			wp_enqueue_style('font-google', 'http://fonts.googleapis.com/css?family=Open+Sans:400,600,700|Montserrat&subset=latin,cyrillic-ext,greek-ext,vietnamese,greek,latin-ext,cyrillic', array(), '1.0');

			// Styles
			wp_enqueue_style('jquery-ui');
			wp_enqueue_style('wp-color-picker'); 
			wp_enqueue_style('cooked-admin',			CP_PLUGIN_URL . '/css/admin-styles.css',			array(), '1.0');
			wp_enqueue_style('fontawesome',				CP_PLUGIN_URL . '/css/font-awesome.css',			array(), '1.1');
			wp_enqueue_style('jquery-ui',				CP_PLUGIN_URL . '/css/jquery-ui.css',				array(), '1.11');
			wp_enqueue_style('jquery',					CP_PLUGIN_URL . '/css/chosen.css',					array(), '1.1');

			// Scripts
			wp_enqueue_script('wp-color-picker');
			wp_enqueue_script('jquery-ui-slider');
			wp_enqueue_script('chosen',					CP_PLUGIN_URL . '/js/chosen.jquery.min.js',			array(), '1.0.0');
			wp_enqueue_script('admin-functions',		CP_PLUGIN_URL . '/js/admin-functions.js',			array(), '1.0.0');

			// Autocomplete handler
			wp_enqueue_script('jquery-ui-autocomplete');
			wp_register_script('cp-autocomplete',		CP_PLUGIN_URL . '/js/admin-autocomplete-ajax.js',	array('jquery'),	'1.0.0');
			wp_localize_script('cp-autocomplete',		'ajax_params', array('ajax_url' => admin_url('admin-ajax.php'), 'home_url' => home_url('/')));
			wp_enqueue_script('cp-autocomplete');
		}
		
		public function admin_inline_scripts() { ?>
			
			<script type="text/javascript">
			
				// Language Variables used in Javascript
				var i18n_confirm_recipe_delete	= '<?php _e('Are you sure you want to delete this recipe?','cooked'); ?>',
					i18n_confirm_recipe_approve	= '<?php _e('Are you sure you want to approve this recipe?','cooked'); ?>';
					
			</script><?php
			
		}

		public function front_end_scripts() {

			// Scripts
			wp_enqueue_script('jquery');
			wp_enqueue_script('jquery-ui-slider');
			wp_enqueue_script('chosen',					CP_PLUGIN_URL . '/js/chosen.jquery.min.js',				array(), '1.0.0');
			wp_enqueue_script('fullscreen',				CP_PLUGIN_URL . '/js/jquery.fullscreener.min.js',		array(), '1.0.0');
			wp_enqueue_script('isotope',				CP_PLUGIN_URL . '/js/isotope.pkgd.min.js',				array(), '2.0.0');
			wp_enqueue_script('countdown-plugin',		CP_PLUGIN_URL . '/js/jquery.plugin.min.js',				array(), '2.0.0');
			wp_enqueue_script('countdown',				CP_PLUGIN_URL . '/js/jquery.countdown.min.js',			array(), '2.0.0');
			wp_enqueue_script('share',					CP_PLUGIN_URL . '/js/share.min.js',						array(), '1.0.0');
			wp_enqueue_script('cookie',					CP_PLUGIN_URL . '/js/jquery.cookie.js',					array(), '1.4.1');
			wp_enqueue_script('fancybox',				CP_PLUGIN_URL . '/js/fancybox/jquery.fancybox.pack.js',	array(), '2.1.5');
			wp_enqueue_script('cp-frontend-functions',	CP_PLUGIN_URL . '/js/functions.js',						array(), '1.0.0');

		}

		public static function front_end_styles() {
			
			// Styles to always load
			wp_enqueue_style('fontawesome',				CP_PLUGIN_URL . '/css/font-awesome.css',			array(), '1.0.0');
			wp_enqueue_style('cp-frontend-style',		CP_PLUGIN_URL . '/css/front-end.css',				array(), '1.0.0');
			
			// Styles to load when printing
			if (isset($_GET['print'])):
				wp_enqueue_style('cp-print-page-style',	CP_PLUGIN_URL . '/css/print-page.css',				array(), '1.0.0');
				wp_enqueue_style('cp-print-style',		CP_PLUGIN_URL . '/css/print.css',					array(), '1.0.0', 'print');
			// Styles to load when NOT printing
			else :
				wp_enqueue_style('fancybox',			CP_PLUGIN_URL . '/js/fancybox/jquery.fancybox.css',	array(), '2.1.5');
				wp_enqueue_style('jquery-chosen',		CP_PLUGIN_URL . '/css/chosen.css',					array(), '1.0.0');
				wp_enqueue_style('jquery-ui',			CP_PLUGIN_URL . '/css/jquery-ui.css',				array(), '1.11');
			endif;
			
		}

		public static function front_end_styles_two() {
			if (!isset($_GET['print'])):
				wp_enqueue_style('cp-additional-styles',CP_UPLOADS_DIR . '/color-theme.css',				array(), '1.0.0');
			endif;
		}

		public static function front_end_styles_responsive() {
			//if (!isset($_GET['print'])):
				wp_enqueue_style('cp-responsive-styles',CP_UPLOADS_DIR . '/front-end-responsive.css');
			//endif;
		}

		public function inline_scripts() { ?>
			
			<?php if (is_singular('cp_recipe')){ ?>
				<?php global $post; $recipe_image = get_post_meta($post->ID, '_thumbnail_id', true); ?>
				<meta property="og:title" content="<?php the_title(); ?>" />
				<meta property="og:type" content="recipe" />
				<?php if(!empty($recipe_image)) : ?>
					<meta property="og:image" content="<?php $recipe_image_url = wp_get_attachment_image_src($recipe_image,'cp_431_424'); echo $recipe_image_url[0]; ?>" />
				<?php else : ?>
					<meta property="og:image" content="<?php echo CP_PLUGIN_URL.'/css/images/default_431_424.png'; ?>" />
				<?php endif; ?>
			<?php } ?>
	
			<script type="text/javascript">
				var cp_facebook_app_id = "<?php echo get_option('cp_facebook_app_id') ? get_option('cp_facebook_app_id') : 'empty'; ?>";
				var media_query_1 = <?php echo get_option('cp_responsive_break_one') ? get_option('cp_responsive_break_one') : 519; ?>;
				var media_query_2 = <?php echo get_option('cp_responsive_break_two') ? get_option('cp_responsive_break_two') : 767; ?>;
				var media_query_3 = <?php echo get_option('cp_responsive_break_three') ? get_option('cp_responsive_break_three') : 960; ?>;
				<?php
				$star_review_optional = get_option('cp_star_review_options');
				if (is_array($star_review_optional) && !empty($star_review_optional)):
					$star_review_optional = 'true';
				else :
					$star_review_optional = 'false';
				endif;
				
				echo 'var cp_star_review_optional = '.$star_review_optional.';'; ?>
			</script>
			
		<?php }

		public function save_comment_meta_data( $comment_id ) {
			
			$reviews_comments = get_option('cp_reviews_comments');
			
			if($reviews_comments != 'admin_reviews_comments') {
				if ( isset($_POST['rating']) && $_POST['rating'] != '' ):
					$rating = intval($_POST['rating']);
				
					if($rating < 1) {
						$rating = 1;
					} elseif($rating > 5) {
						$rating = 5;
					}

					add_comment_meta( $comment_id, 'review_rating', $rating );
				endif;
			}
		}

		public function verify_comment_meta_data( $commentdata ) {
		
			if ('cp_recipe' == get_post_type($commentdata['comment_post_ID'])):
			
				$reviews_comments = get_option('cp_reviews_comments');
				$star_review_optional = get_option('cp_star_review_options');
				if (is_array($star_review_optional) && !empty($star_review_optional)):
					$star_review_optional = true;
				else :
					$star_review_optional = false;
				endif;

				$reviews_comments = get_option('cp_reviews_comments');
				if($reviews_comments != 'admin_reviews_comments') {
					if (empty( $_POST['rating'] ) && !$star_review_optional)
						wp_die( __( 'You did not add a rating. Hit the Back button on your Web browser and resubmit your review with a rating.', 'cooked' ) );
				}
				
			endif;
			
			return $commentdata;
			
		}

		public function tooltips() {
			// Don't run on WP < 3.3
			if ( get_bloginfo( 'version' ) < '3.3' )
				return;

			$screen = get_current_screen();
			$screen_id = $screen->id;

			// Get pointers for this screen
			$pointers = apply_filters( 'cp_admin_pointers-' . $screen_id, array() );

			if ( ! $pointers || ! is_array( $pointers ) )
				return;

			// Get dismissed pointers
			$dismissed = explode( ',', (string) get_user_meta( get_current_user_id(), 'dismissed_wp_pointers', true ) );
			$valid_pointers =array();

			// Check pointers and remove dismissed ones.
			foreach ( $pointers as $pointer_id => $pointer ) {

				// Sanity check
				if ( in_array( $pointer_id, $dismissed ) || empty( $pointer )  || empty( $pointer_id ) || empty( $pointer['target'] ) || empty( $pointer['options'] ) )
					continue;

				$pointer['pointer_id'] = $pointer_id;

				// Add the pointer to $valid_pointers array
				$valid_pointers['pointers'][] =  $pointer;
			}

			// No valid pointers? Stop here.
			if ( empty( $valid_pointers ) )
				return;

			// Add pointers style to queue.
			wp_enqueue_style( 'wp-pointer' );

			// Add pointers script to queue. Add custom script.
			wp_enqueue_script( 'cp-pointer', CP_PLUGIN_URL . '/js/admin-pointers.js', array( 'wp-pointer' ) );

			// Add pointer options to script.
			wp_localize_script( 'cp-pointer', 'cpPointer', $valid_pointers );
		}

		public function post_type_templates($single) {
			global $wp_query, $post;

			$recipe_template = get_option('cp_recipes_page_template');
			if (isset($_GET['print'])){
				if($post->post_type == 'cp_recipe') {
					if(file_exists(CP_PLUGIN_DIR . '/templates/cp_recipe/print.php')) {
						$single = CP_PLUGIN_DIR . '/templates/cp_recipe/print.php';
					}
				}
			} else if(!$recipe_template || $recipe_template == 'cp_default') {
				if($post->post_type == 'cp_recipe') {
					if(file_exists(CP_PLUGIN_DIR . '/templates/cp_recipe/single.php')) {
						$single = CP_PLUGIN_DIR . '/templates/cp_recipe/single.php';
					}
				}
			} else {
				if($post->post_type == 'cp_recipe') {
					add_action('the_content', array(&$this, 'display_recipe_markup'));
					if (file_exists(get_template_directory() . DIRECTORY_SEPARATOR . $recipe_template)):
						$single = get_template_directory() . DIRECTORY_SEPARATOR . $recipe_template;
					else :
						$single = get_stylesheet_directory() . DIRECTORY_SEPARATOR . $recipe_template;
					endif;
				}
			}

			return $single;
		}

		public function archive_template($archive_template) {
			global $post;

			if(get_post_type() == 'cp_recipe' && file_exists(CP_PLUGIN_TEMPLATES_DIR . 'archive-cp_recipe.php') && !file_exists(TEMPLATEPATH . '/archive-cp_recipe.php')) {
				$archive_template = CP_PLUGIN_TEMPLATES_DIR . 'archive-cp_recipe.php';
			} else if (get_post_type() == 'cp_recipe' && file_exists(TEMPLATEPATH . '/archive-cp_recipe.php')){
				$archive_template = TEMPLATEPATH . '/archive-cp_recipe.php';
			}

			return $archive_template;
		}

		public function display_recipe_markup($content) {
			ob_start();
			include(CP_PLUGIN_SECTIONS_DIR . 'single-part.php');
			$content = ob_get_clean();
			return $content;
		}

		public function display_view_markup($content) {		
			if(!empty($GLOBALS['post'])) {
				$list_view_page = get_option('cp_recipes_list_view_page');
				if(!empty($list_view_page) && $GLOBALS['post']->ID == $list_view_page) {
					ob_start();
					$this->display_view_page_content();
					$content = ob_get_clean();
				}
			}
			return $content;
		}

		public function display_view_page_content() {
			$list_view = get_option('cp_recipe_list_view');
			require(CP_PLUGIN_VIEWS_DIR . $list_view . '.php');
		}

		static function cp_uninstall_plugin() {
		
			deactivate_plugins( plugin_basename( __FILE__ ) );
		
			$plugin_settings = cooked_plugin::plugin_settings();
			foreach($plugin_settings as $option_name) {

				// unregister option
				unregister_setting('cooked_plugin-group', $option_name);

				// delete option
				delete_option($option_name);

			}

			// remove posts - force deletion instead of moving to trash
			$args = array(
				'post_type' => 'cp_recipe',
				'posts_per_page' => -1,
			);
			$recipes = get_posts($args);
			if(!empty($recipes)) {
				// also remove comments (reviews)
				foreach($recipes as $recipe) {
					$entry_id = $recipe->ID;
					$post_comments = get_comments(array(
						'post_id' => $entry_id
					));
					if(!empty($post_comments)) {
						foreach($post_comments as $comment) {
							$comment_id = $comment->comment_ID;
							wp_delete_comment($comment_id, true);
						}
					}
					wp_delete_post($entry_id, true);
				}
			}

			// remove terms from taxonomies
			$taxonomies_to_remove = array(
				'cp_recipe_category',
				'cp_recipe_cooking_method',
				'cp_recipe_cuisine'
			);
			foreach($taxonomies_to_remove as $taxonomy) {
				$taxonomy_terms = get_terms($taxonomy, array(
					'hide_empty' => false
				));
				if(!is_wp_error($taxonomy_terms)) {
					foreach($taxonomy_terms as $term) {
						wp_delete_term($term->term_id, $taxonomy);
					}
				}
			}
			
		}

		public function export_settings() {
			if(isset($_GET['export-settings']) && isset($_GET['page']) && $_GET['page'] == 'cooked_plugin') {
				$fields_to_export = $this->plugin_settings();

				$options_values = array();
				foreach($fields_to_export as $field_name) {
					$options_values[$field_name] = get_option($field_name);
				}

				header("Content-type:application/json; charset=utf-8");
				header("Content-Disposition: attachment; filename=recipes-settings-" . date('m-j-Y', time()) . ".json");
				exit(json_encode($options_values));
			}
		}

		public function import_settings() {
			if(isset($_GET['page']) && $_GET['page'] == 'cooked_plugin' && isset($_POST['settings-import']) && $_POST['settings-import'] == 'yes') {
				if( ! $_FILES['import_file']['error']) {
					$filename = $_FILES['import_file']['tmp_name'];
					$json_string = file_get_contents($filename); // alternative to this???
					if($json_string) {
						$fields_to_export = $this->plugin_settings();

						$decoded_string = json_decode($json_string);
						foreach($decoded_string as $option_name => $option_value) {
							if(strpos($option_name, 'cp_') != 0) {
								wp_die('Not a valid settings file.');
							}
						}
						foreach($decoded_string as $option_name => $option_value) {
							if(in_array($option_name, $fields_to_export)) {
								update_option($option_name, $option_value);
							}
						}
					} else {
						wp_die('Not a valid JSON file.');
					}
				}
			}
		}
	} // END class cooked_plugin
} // END if(!class_exists('cooked_plugin'))

if(class_exists('cooked_plugin')) {
	// Installation and uninstallation hooks
	register_activation_hook(__FILE__, array('cooked_plugin', 'activate'));
	register_deactivation_hook(__FILE__, array('cooked_plugin', 'deactivate'));

	// instantiate the plugin class
	$cooked_plugin = new cooked_plugin();

	// Add a link to the settings page onto the plugin page
	if(isset($cooked_plugin)) {
		// Add the settings link to the plugins page
		function plugin_settings_link($links) {
			$settings_link = '<a href="edit.php?post_type=cp_recipe&page=cooked_plugin">Settings</a>';
			array_unshift($links, $settings_link);
			return $links;
		}

		$plugin = plugin_basename(__FILE__);
		add_filter("plugin_action_links_$plugin", 'plugin_settings_link');

		// TODO load depending on STYLE settings
		$plugin_styling = get_option('cp_plugin_styling');
		if (!$plugin_styling): update_option('cp_plugin_styling','all_styles'); $plugin_styling = "all_styles"; endif;
		$disable_responsive_layouts = get_option('cp_disable_plugin_styling');
		if($plugin_styling == 'all_styles') {
			add_action('wp_enqueue_scripts', array('cooked_plugin', 'front_end_styles'));
			add_action('wp_enqueue_scripts', array('cooked_plugin', 'front_end_styles_two'));
			if(empty($disable_responsive_layouts)) {
				add_action('wp_enqueue_scripts', array('cooked_plugin', 'front_end_styles_responsive'));
			}
		} elseif($plugin_styling == 'layout_styles') {
			add_action('wp_enqueue_scripts', array('cooked_plugin', 'front_end_styles'));
			if(empty($disable_responsive_layouts)) {
				add_action('wp_enqueue_scripts', array('cooked_plugin', 'front_end_styles_responsive'));
			}
		}
	
	}
}

// Localization
function cooked_local_init(){
	$domain = 'cooked';
    $locale = apply_filters('plugin_locale', get_locale(), $domain);
    load_textdomain($domain, WP_LANG_DIR.'/cooked/'.$domain.'-'.$locale.'.mo');
    load_plugin_textdomain($domain, FALSE, dirname(plugin_basename(__FILE__)).'/languages/');
}
add_action('after_setup_theme', 'cooked_local_init');