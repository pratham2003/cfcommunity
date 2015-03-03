<?php if(!empty($_POST['cp_do_uninstall']) && $_POST['cp_do_uninstall'] == 'yes') {
	cooked_plugin::cp_uninstall_plugin();
	wp_redirect('index.php');
	exit;
}

?><div class="cp-settings-wrap wrap"><?php

	if(!empty($_GET['settings-updated'])) {
		cp_update_responsive_layouts();
		cp_update_color_theme();
		
		$fixed_slug = cp_create_slug(get_option('cp_recipe_slug'),true);
		update_option('cp_recipe_slug',$fixed_slug);
		
		$fixed_slug = cp_create_slug(get_option('cp_recipe_category_slug'),true);
		update_option('cp_recipe_category_slug',$fixed_slug);
		
		$fixed_slug = cp_create_slug(get_option('cp_recipe_cuisine_slug'),true);
		update_option('cp_recipe_cuisine_slug',$fixed_slug);
		
		$fixed_slug = cp_create_slug(get_option('cp_recipe_method_slug'),true);
		update_option('cp_recipe_method_slug',$fixed_slug);
		
		flush_rewrite_rules();
	} ?>
	
	<?php
			
	$upload_dir = wp_upload_dir();
	$main_upload_dir = $upload_dir['basedir'];
	$cooked_upload_dir = $upload_dir['basedir'] . DIRECTORY_SEPARATOR . 'cooked';
	
	$main_upload_dir_writeable = is_writable($main_upload_dir);
	$cooked_dir_writeable = is_writable($cooked_upload_dir);
	
	$responsive_file = $cooked_upload_dir . DIRECTORY_SEPARATOR . 'front-end-responsive.css';
	$responsive_file_writeable = is_writable($responsive_file);
	
	$color_theme_file = $cooked_upload_dir . DIRECTORY_SEPARATOR . 'color-theme.css';
	$color_theme_file_writeable = is_writable($color_theme_file);
	
	if (!$main_upload_dir_writeable):
		echo '<div id="setting-error-settings_updated" class="error settings-error below-h2">';
			echo '<p><strong>Warning:</strong> You need make the uploads folder writeable:<code>'.$main_upload_dir.'</code></p>';
		echo '</div>';
	endif;
	
	if (!$cooked_dir_writeable):
		echo '<div id="setting-error-settings_updated" class="error settings-error below-h2">';
			echo '<p><strong>Warning:</strong> You need make the /cooked/ folder writeable:<code>'.$cooked_upload_dir.'</code></p>';
		echo '</div>';
	endif;
	
	if (!$responsive_file_writeable):
		echo '<div id="setting-error-settings_updated" class="error settings-error below-h2">';
			echo '<p><strong>Warning:</strong> The <em>layout styles</em> might not save correctly unless you make the following file writeable:<code>'.$responsive_file.'</code></p>';
		echo '</div>';
	endif;
	
	if (!$color_theme_file_writeable):
		echo '<div id="setting-error-settings_updated" class="error settings-error below-h2">';
			echo '<p><strong>Warning:</strong> The <em>color theme</em> might not save correctly unless you make the following file writeable:<code>'.$color_theme_file.'</code></p>';
		echo '</div>';
	endif;
	
	?>
	
	<?php settings_errors(); ?>
	
	<div class="cooked-settings-title"><i class="fa fa-cutlery"></i>&nbsp;&nbsp;<?php _e('Cooked Settings','cooked'); ?></div>
	
	<div id="cooked-admin-panel-container">
	
		<ul class="cooked-admin-tabs cookedClearFix">
			<li class="active"><a href="#templates"><i class="fa fa-desktop"></i>&nbsp;&nbsp;<?php _e('Templates','cooked'); ?></a></li>
			<li><a href="#features"><i class="fa fa-cutlery"></i>&nbsp;&nbsp;<?php _e('Recipes','cooked'); ?></a></li>
			<li><a href="#fes"><i class="fa fa-user"></i>&nbsp;&nbsp;<?php _e('Submissions','cooked'); ?></a></li>
			<li><a href="#user-emails"><i class="fa fa-envelope"></i>&nbsp;&nbsp;<?php _e('User Emails','cooked'); ?></a></li>
			<li><a href="#admin-emails"><i class="fa fa-envelope-o"></i>&nbsp;&nbsp;<?php _e('Admin Emails','cooked'); ?></a></li>
			<li><a href="#social"><i class="fa fa-share-alt"></i>&nbsp;&nbsp;<?php _e('Social Settings','cooked'); ?></a></li>
			<li><a href="#styling"><i class="fa fa-pencil"></i>&nbsp;&nbsp;<?php _e('Styling','cooked'); ?></a></li>
			<li><a href="#import_export_uninstall"><i class="fa fa-wrench"></i>&nbsp;&nbsp;<?php _e('Import/Export / Uninstall','cooked'); ?></a></li>
		</ul>
	
		<div class="form-wrapper">
	
			<form action="options.php" method="post">
			
				<input type="hidden" name="cp_settings_saved" value="true" />
			
				<?php settings_fields('cooked_plugin-group'); ?>
				
				<div id="cooked-templates" class="tab-content">
				
					<div class="section-row">
						
						<div class="section-head">
							<?php $section_title = __('Recipe Template Settings', 'cooked'); ?>
							<h2><?php echo esc_attr($section_title); ?></h2>
							<p><?php _e('Choose from the following templates to display your recipes in one of eight unique ways.','cooked'); ?></p>
						</div><!-- /.section-head -->
		
						<div class="section-body">
							<?php $option_name = 'cp_recipe_template';
		
							$templates_list = array(
								'upper_right_image_right_sidebar' => 'template-01.jpg',
								'upper_image_left_sidebar' => 'template-02.jpg',
								'middle_image_right_sidebar' => 'template-03.jpg',
								'upper_image_middle_header_right_sidebar' => 'template-04.jpg',
								'upper_left_image_left_sidebar' => 'template-05.jpg',
								'upper_image_right_sidebar' => 'template-06.jpg',
								'middle_image_left_sidebar' => 'template-07.jpg',
								'upper_image_middle_header_left_sidebar' => 'template-08.jpg',
								'skinny_upper_image' => 'template-09.jpg',
								'skinny_middle_image' => 'template-10.jpg',
							); ?>
		
							<div class="template-list">
								<?php $selected_template = get_option($option_name) ? get_option($option_name) : 'upper_right_image_right_sidebar';
								foreach($templates_list as $entry_input_value => $template_image) : ?>
									<div class="radio custom-radio">
										<input type="radio" id="<?php echo $entry_input_value; ?>" name="<?php echo $option_name; ?>" value="<?php echo $entry_input_value; ?>"<?php echo ($selected_template == $entry_input_value ? ' checked="checked"' : ''); ?> />
										<label for="<?php echo $entry_input_value; ?>">
											<span class="custom-radio-fake">
												<img src="<?php echo CP_PLUGIN_URL; ?>/css/images/<?php echo $template_image; ?>" width="162" height="197" alt="" />
											</span>
										</label>
									</div><!-- /.radio -->
								<?php endforeach; ?>
							</div><!-- /.radio-list -->
		
						</div><!-- /.section-body -->
						
					</div><!-- /.section-row -->
					
					<div class="section-row">
						<div class="section-head">
							<?php $section_title = __('Recipe List View Style', 'cooked'); ?>
							<h2><?php echo esc_attr($section_title); ?></h2>
							<p><?php _e('Choose between a full-width, grid, or compact (text-only) list view.','cooked'); ?></p>
						</div><!-- /.section-head -->
			
						<div class="section-body">
							<?php $option_name = 'cp_recipe_list_view';
			
							$recipe_list_options = array(
								__('Full-Width') => 'full_width',
								__('Recipe Grid') => 'recipe_grid',
								__('Compact List') => 'compact_list'
							); ?>
							<div class="radio-list">
								<?php $selected_value = get_option($option_name);
								foreach($recipe_list_options as $entry_label => $entry_input_value) : ?>
									<div class="radio custom-radio">
										<input type="radio" id="radio-group-<?php echo $entry_input_value; ?>" name="<?php echo $option_name; ?>" value="<?php echo $entry_input_value; ?>"<?php echo ($selected_value == $entry_input_value ? ' checked="checked"' : ''); ?>/>
										<label for="radio-group-<?php echo $entry_input_value; ?>">
											<span class="custom-radio-fake"><i class="fa fa-check"></i></span>
											<?php echo esc_attr($entry_label); ?>
										</label>
									</div><!-- /.radio -->
								<?php endforeach; ?>
							</div><!-- /.radio-list -->
			
						</div><!-- /.section-body --> 
					</div><!-- /.section-row -->
					
					<div class="section-row">
						<div class="section-head">
							<?php $section_title = __('Pagination Style', 'cooked'); ?>
							<h2><?php echo esc_attr($section_title); ?></h2>
							<p><?php _e('There are three unique pagination styles to choose from.','cooked'); ?></p>
						</div><!-- /.section-head -->
		
						<div class="section-body">
							<?php $option_name = 'cp_list_view_pagination';
		
							$list_view_options = array(
								__('Numbered Pagination') => 'numbered_pagination',
								__('Load More Button') => 'load_more_button',
								__('Load More on Scroll') => 'load_more_on_scroll'
							); ?>
							<div class="radio-list">
								<?php $selected_value = get_option($option_name);
								foreach($list_view_options as $entry_label => $entry_input_value) : ?>
									<div class="radio custom-radio">
										<input type="radio" id="radio-group-<?php echo $entry_input_value; ?>" name="<?php echo $option_name; ?>" value="<?php echo $entry_input_value; ?>"<?php echo ($selected_value == $entry_input_value ? ' checked="checked"' : ''); ?>/>
										<label for="radio-group-<?php echo $entry_input_value; ?>">
											<span class="custom-radio-fake"><i class="fa fa-check"></i></span>
											<?php echo esc_attr($entry_label); ?>
										</label>
									</div><!-- /.radio -->
								<?php endforeach; ?>
							</div><!-- /.radio-list -->
		
						</div><!-- /.section-body -->
					</div><!-- /.section-row -->
						
					<div class="section-row">
						<div class="section-head">
							<?php $section_title = __('Browse Recipes Page', 'cooked'); ?>
							<h2><?php echo esc_attr($section_title); ?></h2>
							<p><?php _e('Create a blank page that uses a <strong>full-width</strong> template and then choose it from this dropdown.','cooked'); ?><br />
							<?php _e('You can also use the <strong>[cooked-browse]</strong> shortcode to display the Browse Recipes content anywhere.','cooked'); ?></p>
						</div><!-- /.section-head -->
						<div class="section-body">
							<?php $option_name = 'cp_recipes_list_view_page';
		
							$pages = get_posts(array(
								'post_type' => 'page',
								'posts_per_page' => -1
							));
		
							$selected_value = get_option($option_name); ?>
							<div class="select-box">
								<select name="<?php echo $option_name; ?>">
									<option value=""><?php _e('Choose a page to use for list view...','cooked'); ?></option>
									<?php if(!empty($pages)) :
										foreach($pages as $p) :
											$entry_id = $p->ID;
											$entry_title = get_the_title($entry_id); ?>
											<option value="<?php echo $entry_id; ?>"<?php echo ($selected_value == $entry_id ? ' selected="selected"' : ''); ?>><?php echo $entry_title; ?></option>
										<?php endforeach;
		
									endif; ?>
								</select>
							</div><!-- /.select-box -->
							
						</div><!-- /.section-body -->
					</div><!-- /.section-row -->
					
					<div class="section-row">
						<div class="section-head">
							<?php $section_title = __('Profile Page', 'cooked'); ?>
							<h2><?php echo esc_attr($section_title); ?></h2>
							<p><?php _e('Create a page that includes the <strong>[cooked-login]</strong> shortcode for your profile template then choose it from this dropdown.','cooked'); ?><br />
							<?php _e('You can also use the <strong>[cooked-profile]</strong> shortcode to display the Profile content anywhere.','cooked'); ?></p>
						</div><!-- /.section-head -->
						<div class="section-body">
							<?php $option_name = 'cp_profile_page';
		
							$pages = get_posts(array(
								'post_type' => 'page',
								'posts_per_page' => -1
							));
		
							$selected_value = get_option($option_name); ?>
							<div class="select-box">
								<select name="<?php echo $option_name; ?>">
									<option value=""><?php _e('Choose a page to use for profile page...','cooked'); ?></option>
									<?php if(!empty($pages)) :
										foreach($pages as $p) :
											$entry_id = $p->ID;
											$entry_title = get_the_title($entry_id); ?>
											<option value="<?php echo $entry_id; ?>"<?php echo ($selected_value == $entry_id ? ' selected="selected"' : ''); ?>><?php echo $entry_title; ?></option>
										<?php endforeach;
		
									endif; ?>
								</select>
							</div><!-- /.select-box -->
						</div><!-- /.section-body -->
					</div><!-- /.section-row -->
			
					<div class="section-row">
						<div class="section-head">
							<?php $section_title = __('Single Recipe Page Template', 'cooked'); ?>
							<h2><?php echo esc_attr($section_title); ?></h2>
							<p><?php _e('Choose a <strong>full-width</strong> page template to use for your single recipe posts.','cooked'); ?><br />
							<?php _e('You can also use the <strong>[cooked-recipe id=<span style="color:#888; font-weight:300;">###</span>]</strong> shortcode to display a single recipe anywhere.','cooked'); ?></p>

						</div><!-- /.section-head -->
						<div class="section-body">
							<?php $option_name = 'cp_recipes_page_template';
		
							$templates = get_page_templates();
		
							$selected_value = get_option($option_name); ?>
							<div class="select-box">
								<select name="<?php echo $option_name; ?>">
									<option value=""><?php _e('Choose a page template to use for single recipes...','cooked'); ?></option>
									<option value="cp_default"<?php echo ($selected_value == 'cp_default' ? ' selected="selected"' : ''); ?>><?php _e('Default recipes template','cooked'); ?></option>
									<?php if(!empty($templates)) :
										ksort($templates);
										foreach($templates as $template_label => $template_slug) : ?>
											<option value="<?php echo $template_slug; ?>"<?php echo ($selected_value == $template_slug ? ' selected="selected"' : ''); ?>><?php echo $template_label; ?></option>
										<?php endforeach;
									endif; ?>
								</select>
							</div><!-- /.select-box -->
						</div><!-- /.section-body -->
					</div><!-- /.section-row -->
				</div><!-- /templates -->
				
				
				
				
				<div id="cooked-features" class="tab-content">
				
					<div class="section-row cookedClearFix">
						<div class="section-head">
							<?php $option_name = 'cp_recipe_slug';
							$recipe_slug = (get_option($option_name) ? get_option($option_name) : 'recipe'); ?>
							<?php $section_title = __('Recipe Slug', 'cooked'); ?>
							<h2><?php echo esc_attr($section_title); ?></h2>
							<p><?php _e('The default recipe slug is "recipe". Feel free to change it to something else here. No spaces, numbers or symbols allowed, just letters and dashes!','cooked'); ?>
							<br><?php _e('<strong>NOTE:</strong> Make sure the slug you choose doesn\'t conflict with any other slugs (pages, posts, taxonomies etc.)','cooked'); ?></p>
						</div>
						<div class="recipe-slug-wrap">
							<span class="recipe-slug-text"><?php echo get_site_url(); ?>/</span><input type="text" name="<?php echo $option_name; ?>" value="<?php echo $recipe_slug; ?>" class="regular-text cp-left" /> <span class="recipe-slug-text">/recipe-name-here/</span>
						</div>
					</div><!-- /.section-row -->
					
					<div class="section-row cookedClearFix">
						<div class="section-head">
							<?php $option_name = 'cp_recipe_category_slug';
							$recipe_category_slug = (get_option($option_name) ? get_option($option_name) : 'recipe-category'); ?>
							<?php $section_title = __('Recipe Category Slug', 'cooked'); ?>
							<h2><?php echo esc_attr($section_title); ?></h2>
							<p><?php _e('The default recipe slug is "recipe-category". Feel free to change it to something else here. No spaces, numbers or symbols allowed, just letters and dashes!','cooked'); ?>
							<br><?php _e('<strong>NOTE:</strong> Make sure the slug you choose doesn\'t conflict with any other slugs (pages, posts, taxonomies etc.)','cooked'); ?></p>
						</div>
						<div class="recipe-slug-wrap">
							<span class="recipe-slug-text"><?php echo get_site_url(); ?>/</span><input type="text" name="<?php echo $option_name; ?>" value="<?php echo $recipe_category_slug; ?>" class="regular-text cp-left" /> <span class="recipe-slug-text">/recipe-category-name/</span>
						</div>
					</div><!-- /.section-row -->
					
					<div class="section-row cookedClearFix">
						<div class="section-head">
							<?php $option_name = 'cp_recipe_cuisine_slug';
							$recipe_cuisine_slug = (get_option($option_name) ? get_option($option_name) : 'recipe-cuisine'); ?>
							<?php $section_title = __('Recipe Cuisine Slug', 'cooked'); ?>
							<h2><?php echo esc_attr($section_title); ?></h2>
							<p><?php _e('The default recipe slug is "recipe-cuisine". Feel free to change it to something else here. No spaces, numbers or symbols allowed, just letters and dashes!','cooked'); ?>
							<br><?php _e('<strong>NOTE:</strong> Make sure the slug you choose doesn\'t conflict with any other slugs (pages, posts, taxonomies etc.)','cooked'); ?></p>
						</div>
						<div class="recipe-slug-wrap">
							<span class="recipe-slug-text"><?php echo get_site_url(); ?>/</span><input type="text" name="<?php echo $option_name; ?>" value="<?php echo $recipe_cuisine_slug; ?>" class="regular-text cp-left" /> <span class="recipe-slug-text">/recipe-cuisine-name/</span>
						</div>
					</div><!-- /.section-row -->
					
					<div class="section-row cookedClearFix">
						<div class="section-head">
							<?php $option_name = 'cp_recipe_method_slug';
							$recipe_method_slug = (get_option($option_name) ? get_option($option_name) : 'recipe-cooking_method'); ?>
							<?php $section_title = __('Recipe Cooking Method Slug', 'cooked'); ?>
							<h2><?php echo esc_attr($section_title); ?></h2>
							<p><?php _e('The default recipe slug is "recipe-cooking_method". Feel free to change it to something else here. No spaces, numbers or symbols allowed, just letters and dashes!','cooked'); ?>
							<br><?php _e('<strong>NOTE:</strong> Make sure the slug you choose doesn\'t conflict with any other slugs (pages, posts, taxonomies etc.)','cooked'); ?></p>
						</div>
						<div class="recipe-slug-wrap">
							<span class="recipe-slug-text"><?php echo get_site_url(); ?>/</span><input type="text" name="<?php echo $option_name; ?>" value="<?php echo $recipe_method_slug; ?>" class="regular-text cp-left" /> <span class="recipe-slug-text">/recipe-cooking-method-name/</span>
						</div>
					</div><!-- /.section-row -->
					
					<div class="section-row">
						<div class="section-head">
							<?php $section_title = __('Ingredients and Measurements', 'cooked'); ?>
							<h2><?php echo esc_attr($section_title); ?></h2>
							<p><?php _e('You <strong>DO NOT</strong> need to check these to use ingredients and measurements. However, if you need to manually edit the ingredients and/or measurement lists, you can check the following boxes.','cooked'); ?><br><strong><?php _e('NOTE:','cooked'); ?></strong> <?php _e('If you delete a measurement that a recipe is currently using, that measurement will no longer display. <em>Be careful!</em>','cooked'); ?></p>
						</div><!-- /.section-head -->
		
						<div class="section-body">
							<?php $option_name = 'cp_advanced_editable_taxes';
		
							$action_options = array(
								__('Ingredients', 'cooked') => 'ingredients',
								__('Measurements', 'cooked') => 'measurements',
							); ?>
							<div class="checkbox-list">
								<?php $action_options_options = get_option($option_name) ? get_option($option_name) : array();
								foreach($action_options as $action_option_label => $action_option_input_value) : ?>
									<div class="checkbox custom-checkbox">
										<input type="checkbox" id="checkbox-info-<?php echo $action_option_input_value; ?>" name="<?php echo $option_name; ?>[]" value="<?php echo $action_option_input_value; ?>"<?php echo (in_array($action_option_input_value, $action_options_options) ? ' checked="checked"' : ''); ?>/>
										<label for="checkbox-info-<?php echo $action_option_input_value; ?>">
											<span class="custom-checkbox-fake"><i class="fa fa-check"></i></span>
											<?php echo esc_attr($action_option_label); ?>
										</label>
									</div><!-- /.checkbox -->
								<?php endforeach; ?>
							</div><!-- /.checkbox-list -->
						</div><!-- /.section-body -->
					</div><!-- /.section-row -->
			
					<div class="section-row">
						<div class="section-head">
							<?php $section_title = __('Recipe Actions', 'cooked'); ?>
							<h2><?php echo esc_attr($section_title); ?></h2>
							<p><?php _e('Your visitors can print, heart and view your recipes in a full-screen mode.','cooked'); ?></p>
						</div><!-- /.section-head -->
		
						<div class="section-body">
							<?php $option_name = 'cp_action_options';
		
							$action_options = array(
								__('Print Button', 'cooked') => 'print_button',
								__('Full-screen Button', 'cooked') => 'full_screen_button',
								__('Favorite Button', 'cooked') => 'favorite_button',
							); ?>
							<div class="checkbox-list">
								<?php $action_options_options = get_option($option_name) ? get_option($option_name) : array();
								foreach($action_options as $action_option_label => $action_option_input_value) : ?>
									<div class="checkbox custom-checkbox">
										<input type="checkbox" id="checkbox-terms-<?php echo $action_option_input_value; ?>" name="<?php echo $option_name; ?>[]" value="<?php echo $action_option_input_value; ?>"<?php echo (in_array($action_option_input_value, $action_options_options) ? ' checked="checked"' : ''); ?>/>
										<label for="checkbox-terms-<?php echo $action_option_input_value; ?>">
											<span class="custom-checkbox-fake"><i class="fa fa-check"></i></span>
											<?php echo esc_attr($action_option_label); ?>
										</label>
									</div><!-- /.checkbox -->
								<?php endforeach; ?>
							</div><!-- /.checkbox-list -->
						</div><!-- /.section-body -->
					</div><!-- /.section-row -->
					
					<div class="section-row">
						<div class="section-head">
							<?php $section_title = __('Premium Actions', 'cooked'); ?>
							<h2><?php echo esc_attr($section_title); ?></h2>
							<p><?php _e('Check the box below to make the actions listed above "Premium" (make them only available if logged in).','cooked'); ?></p>
						</div><!-- /.section-head -->
		
						<div class="section-body">
							<?php $option_name = 'cp_premium_actions';
		
							$action_options = array(
								__('Make Actions "Premium"', 'cooked') => 'active',
							); ?>
							<div class="checkbox-list">
								<?php $action_options_options = get_option($option_name) ? get_option($option_name) : array();
								foreach($action_options as $action_option_label => $action_option_input_value) : ?>
									<div class="checkbox custom-checkbox">
										<input type="checkbox" id="checkbox-terms-<?php echo $action_option_input_value; ?>" name="<?php echo $option_name; ?>[]" value="<?php echo $action_option_input_value; ?>"<?php echo (in_array($action_option_input_value, $action_options_options) ? ' checked="checked"' : ''); ?>/>
										<label for="checkbox-terms-<?php echo $action_option_input_value; ?>">
											<span class="custom-checkbox-fake"><i class="fa fa-check"></i></span>
											<?php echo esc_attr($action_option_label); ?>
										</label>
									</div><!-- /.checkbox -->
								<?php endforeach; ?>
							</div><!-- /.checkbox-list -->
						</div><!-- /.section-body -->
					</div><!-- /.section-row -->

					
					<div class="section-row">
						<div class="section-head">
							<?php $section_title = __('Recipe Taxonomies', 'cooked'); ?>
							<h2><?php echo esc_attr($section_title); ?></h2>
							<p><?php _e('Choose which taxonomies you want to use and display.','cooked'); ?></p>
						</div><!-- /.section-head -->
			
						<div class="section-body">
							<?php $option_name = 'cp_recipe_taxonomies';
			
							$recipe_tax_options = array(
								__('Category') => 'category',
								__('Cuisine') => 'cuisine',
								__('Cooking Method') => 'method',
								__('Tags') => 'tags'
							); ?>
							<div class="checkbox-list">
								<?php $recipe_tax_options_options = get_option($option_name) ? get_option($option_name) : array();
								foreach($recipe_tax_options as $recipe_tax_option_label => $recipe_tax_option_input_value) : ?>
									<div class="checkbox custom-checkbox">
										<input type="checkbox" id="checkbox-terms-<?php echo $recipe_tax_option_input_value; ?>" name="<?php echo $option_name; ?>[]" value="<?php echo $recipe_tax_option_input_value; ?>"<?php echo (in_array($recipe_tax_option_input_value, $recipe_tax_options_options) ? ' checked="checked"' : ''); ?>/>
										<label for="checkbox-terms-<?php echo $recipe_tax_option_input_value; ?>">
											<span class="custom-checkbox-fake"><i class="fa fa-check"></i></span>
											<?php echo esc_attr($recipe_tax_option_label); ?>
										</label>
									</div><!-- /.checkbox -->
								<?php endforeach; ?>
							</div><!-- /.checkbox-list -->
			
						</div><!-- /.section-body --> 
					</div><!-- /.section-row -->
					
					<div class="section-row">
						<div class="section-head">
							<?php $section_title = __('Recipe Information', 'cooked'); ?>
							<h2><?php echo esc_attr($section_title); ?></h2>
							<p><?php _e('Choose which items you want to display on the recipe information panels.','cooked'); ?><br><strong><?php _e('NOTE:','cooked'); ?></strong> <?php _e('The items you hide here will also be hidden from the front-end submission form.','cooked'); ?></p>
						</div><!-- /.section-head -->
		
						<div class="section-body">
							<?php $option_name = 'cp_info_options';
		
							$action_options = array(
								__('Difficulty Level', 'cooked') => 'difficulty_level',
								__('Author', 'cooked') => 'author',
								__('Author Avatar', 'cooked') => 'author_avatar',
								__('Rating', 'cooked') => 'rating',
								__('Short Description', 'cooked') => 'description',
								__('Category', 'cooked') => 'category',
								__('Cuisine', 'cooked') => 'cuisine',
								__('Cooking Method', 'cooked') => 'method',
								__('Timing', 'cooked') => 'timing',
								__('Yields', 'cooked') => 'yields',
								__('Additional Notes', 'cooked') => 'notes',
								__('Nutritional Facts', 'cooked') => 'nutrition',
							); ?>
							<div class="checkbox-list">
								<?php $action_options_options = get_option($option_name) ? get_option($option_name) : array();
								foreach($action_options as $action_option_label => $action_option_input_value) : ?>
									<div class="checkbox custom-checkbox">
										<input type="checkbox" id="checkbox-info-<?php echo $action_option_input_value; ?>" name="<?php echo $option_name; ?>[]" value="<?php echo $action_option_input_value; ?>"<?php echo (in_array($action_option_input_value, $action_options_options) ? ' checked="checked"' : ''); ?>/>
										<label for="checkbox-info-<?php echo $action_option_input_value; ?>">
											<span class="custom-checkbox-fake"><i class="fa fa-check"></i></span>
											<?php echo esc_attr($action_option_label); ?>
										</label>
									</div><!-- /.checkbox -->
								<?php endforeach; ?>
							</div><!-- /.checkbox-list -->
						</div><!-- /.section-body -->
					</div><!-- /.section-row -->
					
					<div class="section-row">
						<div class="section-head">
							<?php $section_title = __('Max Prep Time', 'cooked'); ?>
							<h2><?php echo esc_attr($section_title); ?></h2>
							<p><?php _e('Change the maximum prep time here, in hours.','cooked'); ?></p>
						</div>
						<div class="sliders-wrapper">
							<div class="prep-slider">
								<div class="slider-px slider-left-px">1 hr</div>
								<div class="prp-slider"></div><!-- /.slider -->
								<div class="slider-px slider-right-px">12 hrs</div>
							</div><!-- /.resp-slider -->
							<?php $break_points = array(
								'hrs' => array(
									'label' => 'Hour(s)',
									'default' => 12
								),
							);
	
							$options_prefix = 'cp_prep_time_max_';
							$js_values = array();
							foreach($break_points as $suffix => $data) :
	
								$option_name = $options_prefix . $suffix;
								$real_db_value = get_option($option_name);
								$db_value = $real_db_value != 0 ? $real_db_value : $data['default'];
								$js_values[] = $db_value; ?>
								<input type="hidden" name="<?php echo $option_name; ?>" value="<?php echo $db_value; ?>" class="slider-inputs" />
								
							<?php endforeach; ?>
							<script type="text/javascript">
								var prep_slider_value = [<?php echo implode(',', $js_values); ?>];
							</script>
						</div>
					</div><!-- /.section-row -->
					
					<div class="section-row">
						<div class="section-head">
							<?php $section_title = __('Max Cook Time', 'cooked'); ?>
							<h2><?php echo esc_attr($section_title); ?></h2>
							<p><?php _e('Change the maximum cook time here, in hours.','cooked'); ?></p>
						</div>
						<div class="sliders-wrapper">
							<div class="cook-slider">
								<div class="slider-px slider-left-px">1 hr</div>
								<div class="ck-slider"></div><!-- /.slider -->
								<div class="slider-px slider-right-px">12 hrs</div>
							</div><!-- /.resp-slider -->
							<?php $break_points = array(
								'hrs' => array(
									'label' => 'Hour(s)',
									'default' => 12
								),
							);
	
							$options_prefix = 'cp_cook_time_max_';
							$js_values = array();
							foreach($break_points as $suffix => $data) :
	
								$option_name = $options_prefix . $suffix;
								$real_db_value = get_option($option_name);
								$db_value = $real_db_value != 0 ? $real_db_value : $data['default'];
								$js_values[] = $db_value; ?>
								<input type="hidden" name="<?php echo $option_name; ?>" value="<?php echo $db_value; ?>" class="slider-inputs" />
								
							<?php endforeach; ?>
							<script type="text/javascript">
								var cook_slider_value = [<?php echo implode(',', $js_values); ?>];
							</script>
						</div>
					</div><!-- /.section-row -->
		
					<div class="section-row">
						<div class="section-head">
							<?php $section_title = __('Reviews &amp; Comments', 'cooked'); ?>
							<h2><?php echo esc_attr($section_title); ?></h2>
							<p><?php _e('How do you want your comments and/or reviews to work?','cooked'); ?></p>
						</div><!-- /.section-head -->
		
						<div class="section-body">
							<?php $option_name = 'cp_reviews_comments';
		
							$reviews_comments = array(
								__('Guest Reviews w/Comments') => 'guest_reviews_comments',
								__('Admin Reviews w/Comments (no guest reviews)') => 'admin_reviews_comments',
								__('Admin Reviews Only (no comments or guest reviews)') => 'admin_reviews_only'
							); ?>
							<div class="radio-list">
								<?php $selected_value = get_option($option_name);
								foreach($reviews_comments as $entry_label => $entry_input_value) : ?>
									<div class="radio custom-radio">
										<input class="cp_reviews_comments" type="radio" id="radio-group-<?php echo $entry_input_value; ?>" name="<?php echo $option_name; ?>" value="<?php echo $entry_input_value; ?>"<?php echo ($selected_value == $entry_input_value ? ' checked="checked"' : ''); ?>/>
										<label for="radio-group-<?php echo $entry_input_value; ?>">
											<span class="custom-radio-fake"><i class="fa fa-check"></i></span>
											<?php echo esc_attr($entry_label); ?>
										</label>
									</div><!-- /.radio -->
								<?php endforeach; ?>
							</div><!-- /.radio-list -->
		
						</div><!-- /.section-body -->
					</div><!-- /.section-row -->
					
					<div class="section-row">
						<div class="section-head">
							<?php $section_title = __('Star Reviews', 'cooked'); ?>
							<h2><?php echo esc_attr($section_title); ?></h2>
							<p><?php _e('If you\'d like, you can make the star review an optional item.','cooked'); ?></p>
						</div><!-- /.section-head -->
		
						<div class="section-body">
							<?php $option_name = 'cp_star_review_options';
		
							$action_options = array(
								__('Star review is optional', 'cooked') => 'cp_star_review_optional',
							); ?>
							
							<div class="checkbox-list">
								<?php $action_options_options = get_option($option_name) ? get_option($option_name) : array();
								foreach($action_options as $action_option_label => $action_option_input_value) : ?>
									<div class="checkbox custom-checkbox">
										<input type="checkbox" id="checkbox-terms-<?php echo $action_option_input_value; ?>" name="<?php echo $option_name; ?>[]" value="<?php echo $action_option_input_value; ?>"<?php echo (in_array($action_option_input_value, $action_options_options) ? ' checked="checked"' : ''); ?>/>
										<label for="checkbox-terms-<?php echo $action_option_input_value; ?>">
											<span class="custom-checkbox-fake"><i class="fa fa-check"></i></span>
											<?php echo esc_attr($action_option_label); ?>
										</label>
									</div><!-- /.checkbox -->
								<?php endforeach; ?>
							</div><!-- /.checkbox-list -->
						</div><!-- /.section-body -->
					</div><!-- /.section-row -->
				
				</div><!-- /general -->	
				
				
				
				
				<div id="cooked-fes" class="tab-content">
					
					<div class="section-row">
						<div class="section-head">
							<?php $section_title = __('Front-End Submissions', 'cooked'); ?>
							<h2><?php echo esc_attr($section_title); ?></h2>
							<p><?php _e('Check the box to enable front-end recipe submissions.','cooked'); ?></p>
						</div><!-- /.section-head -->
		
						<div class="section-body">
							<?php $option_name = 'cp_fes_options';
		
							$action_options = array(
								__('Enable Front-End Submissions', 'cooked') => 'fes_enabled',
							); ?>
							
							<div class="checkbox-list">
								<?php $action_options_options = get_option($option_name) ? get_option($option_name) : array();
								foreach($action_options as $action_option_label => $action_option_input_value) : ?>
									<div class="checkbox custom-checkbox">
										<input class="cp_fes_controller" type="checkbox" id="checkbox-terms-<?php echo $action_option_input_value; ?>" name="<?php echo $option_name; ?>[]" value="<?php echo $action_option_input_value; ?>"<?php echo (in_array($action_option_input_value, $action_options_options) ? ' checked="checked"' : ''); ?>/>
										<label for="checkbox-terms-<?php echo $action_option_input_value; ?>">
											<span class="custom-checkbox-fake"><i class="fa fa-check"></i></span>
											<?php echo esc_attr($action_option_label); ?>
										</label>
									</div><!-- /.checkbox -->
								<?php endforeach; ?>
							</div><!-- /.checkbox-list -->
						</div><!-- /.section-body -->
					</div><!-- /.section-row -->
					
					<div class="section-row" data-controller="cp_fes_controller" data-controlled_by="fes_enabled">
						<div class="section-head">
							<h2><?php _e('Next steps:','cooked'); ?></h2>
							<ol style="margin-bottom:-20px;">
								<li><?php _e('Create a page and add the <strong>[cooked-submit]</strong> shortcode to display the form.','cooked'); ?></li>
								<li><?php _e('Choose which user-roles have access to the front-end submission form.','cooked'); ?></li>
								<li><?php _e('Fill out the fields below to set what displays to both logged-in and logged-out users.','cooked'); ?></li>
							</ol>
						</div>
					</div>
					
					<div class="section-row" data-controller="cp_fes_controller" data-controlled_by="fes_enabled">
						<div class="section-head">
							<?php $section_title = __('Who can submit recipes?', 'cooked'); ?>
							<h2><?php echo esc_attr($section_title); ?></h2>
							<p><?php _e('Choose the user roles from which you want to allow front-end submissions.','cooked'); ?></p>
						</div><!-- /.section-head -->
						<div class="section-body">
							<?php
							
							global $wp_roles;
							
							$option_name = 'cp_recipes_fes_user_roles';
							$selected_roles = get_option($option_name);
							$all_roles = $wp_roles->roles;
							$role_options = array();
							
							foreach($all_roles as $role => $r):
								$role_options[$r['name']] = $role;
							endforeach;
		
							?><div class="checkbox-list">
								<?php $selected_roles = get_option($option_name) ? get_option($option_name) : array();
								foreach($role_options as $role_option_label => $role_option_input_value) : ?>
									<div class="checkbox custom-checkbox">
										<input type="checkbox" id="checkbox-terms-<?php echo $role_option_input_value; ?>" name="<?php echo $option_name; ?>[]" value="<?php echo $role_option_input_value; ?>"<?php echo (in_array($role_option_input_value, $selected_roles) ? ' checked="checked"' : ''); ?>/>
										<label for="checkbox-terms-<?php echo $role_option_input_value; ?>">
											<span class="custom-checkbox-fake"><i class="fa fa-check"></i></span>
											<?php echo esc_attr($role_option_label); ?>
										</label>
									</div><!-- /.checkbox -->
								<?php endforeach; ?>
							</div><!-- /.checkbox-list -->
						
						</div><!-- /.section-body -->
					</div><!-- /.section-row -->
					
					<div class="section-row" data-controller="cp_fes_controller" data-controlled_by="fes_enabled">
						<div class="section-head">
							<?php $section_title = __('Recipe Limit', 'cooked'); ?>
							<h2><?php echo esc_attr($section_title); ?></h2>
							<p><?php _e('To prevent users from submitting too many recipes, you can set a recipe limit.','cooked'); ?></p>

							<?php $option_name = 'cp_recipe_fes_limit';
							$selected_value = get_option($option_name);
							
							$interval_options = array(
								'0' 				=> __('No limit','cooked'),
								'1' 				=> __('1 recipe','cooked'),
								'2' 				=> __('2 recipes','cooked'),
								'3' 				=> __('3 recipes','cooked'),
								'4' 				=> __('4 recipes','cooked'),
								'5' 				=> __('5 recipes','cooked'),
								'6' 				=> __('6 recipes','cooked'),
								'7' 				=> __('7 recipes','cooked'),
								'8' 				=> __('8 recipes','cooked'),
								'9' 				=> __('9 recipes','cooked'),
								'10' 				=> __('10 recipes','cooked'),
								'15' 				=> __('15 recipes','cooked'),
								'20' 				=> __('20 recipes','cooked'),
								'25' 				=> __('25 recipes','cooked'),
								'50' 				=> __('50 recipes','cooked'),
							); ?>
							
							<div class="select-box">
								<select name="<?php echo $option_name; ?>">
									<?php foreach($interval_options as $current_value => $option_title):
										echo '<option value="'.$current_value.'"' . ($selected_value == $current_value ? ' selected' : ''). '>' . $option_title . '</option>';
									endforeach; ?>
								</select>
							</div><!-- /.select-box -->
						</div><!-- /.section-body -->
					</div><!-- /.section-row -->
					
					<div class="section-row" data-controller="cp_fes_controller" data-controlled_by="fes_enabled">
						<div class="section-head">
							<?php $section_title = __('New Recipe Default', 'cooked'); ?>
							<h2><?php echo esc_attr($section_title); ?></h2>
							<p><?php _e('Would you like the submitted recipes to go in as Drafts or should they be Published immediately?','cooked'); ?></p>

							<?php $option_name = 'cp_fes_new_recipe_default';
							$selected_value = get_option($option_name);
							
							$interval_options = array(
								'draft' 	=> __('Set as Draft','cooked'),
								'publish' 	=> __('Publish Immediately','cooked')
							); ?>
							
							<div class="select-box">
								<select name="<?php echo $option_name; ?>">
									<?php foreach($interval_options as $current_value => $option_title):
										echo '<option value="'.$current_value.'"' . ($selected_value == $current_value ? ' selected' : ''). '>' . $option_title . '</option>';
									endforeach; ?>
								</select>
							</div><!-- /.select-box -->
						</div><!-- /.section-body -->
					</div><!-- /.section-row -->
					
					<div class="section-row" data-controller="cp_fes_controller" data-controlled_by="fes_enabled">
						<div class="section-head">
							<?php $option_name = 'cp_fes_welcome_message';
							$fes_message = get_option($option_name); ?>
							<?php $section_title = __('Message to Logged-in Users', 'cooked'); ?>
							<h2><?php echo esc_attr($section_title); ?></h2>
							<p><?php _e('Some things to remember','cooked'); ?>:</p>
							<ul class="cp-list">
								<li><?php _e('This is a message to display to the people who HAVE access to the form.','cooked'); ?></li>
								<li><?php _e('HTML allowed','cooked'); ?></li>
								<li><?php _e('Use the <strong>%username%</strong> token to display the user\'s name in the message.','cooked'); ?></li>
							</ul>
						</div>
						<textarea name="<?php echo $option_name; ?>" class="field med"><?php echo $fes_message; ?></textarea>
					</div><!-- /.section-row -->
					
					<div class="section-row" data-controller="cp_fes_controller" data-controlled_by="fes_enabled">
						<div class="section-head">
							<?php $option_name = 'cp_fes_no_access_message';
							$fes_message = get_option($option_name); ?>
							<?php $section_title = __('Message to Logged-out Users', 'cooked'); ?>
							<h2><?php echo esc_attr($section_title); ?></h2>
							<p><?php _e('Some things to remember','cooked'); ?>:</p>
							<ul class="cp-list">
								<li><?php _e('This is a message to display to the people who DO NOT HAVE access to the form.','cooked'); ?></li>
								<li><?php _e('HTML allowed','cooked'); ?></li>
								<li><?php _e('Put the <strong>[cooked-login]</strong> shortcode somewhere so users can register and/or log in.','cooked'); ?></li>
							</ul>
						</div>
						<textarea name="<?php echo $option_name; ?>" class="field med"><?php echo $fes_message; ?></textarea>
					</div><!-- /.section-row -->
					
				</div>
				
				
				
				
				<div id="cooked-user-emails" class="tab-content">
					
					<div class="section-row">
						<div class="section-head">
							<p><strong style="font-size:17px; line-height:1.7;"><?php _e('If you do not want to send email notifications for any or all of the following actions, you can just delete the text and an email will not be sent.','cooked'); ?></strong></p>
						</div>
					</div>
					
					<div class="section-row">
						<div class="section-head"><?php
							
							$option_name = 'cooked_email_logo';
							$cooked_email_logo = get_option($option_name);
							$section_title = __('Email Content - Logo Image', 'cooked'); ?>
							
							<h2><?php echo esc_attr($section_title); ?></h2>
							<p><?php _e('Choose an image for your custom emails. Keep it 600px or less for best results.','cooked'); ?></p>
							
							<input id="<?php echo $option_name; ?>" name="<?php echo $option_name; ?>" value="<?php echo $cooked_email_logo; ?>" type="hidden" />
							<input id="cooked_email_logo_button" class="button" name="cooked_email_logo_button" type="button" value="Upload Logo" />
						
							<img src="<?php echo $cooked_email_logo; ?>" id="cooked_email_logo-img">
						</div>
					</div>
					
					<div class="section-row">
						<div class="section-head">
							<?php $option_name = 'cooked_registration_email_content';
								
$default_content = 'Hey %name%!

Thanks for registering at '.get_bloginfo('name').'. You can now login to manage your account and recipes using the following credentials:

Username: %username%
Password: %password%

Sincerely,
Your friends at '.get_bloginfo('name');

							$email_content_registration = get_option($option_name,$default_content);
							$section_title = __('Email Content - Registration', 'cooked'); ?>
							
							<h2><?php echo esc_attr($section_title); ?></h2>
							<p><?php _e('The email content that is sent to the user upon registration (using the Booked registration form). Some tokens you can use:','cooked'); ?></p>
							<ul class="cp-list">
								<li><strong>%name%</strong> &mdash; <?php _e("To display the person's name.","cooked"); ?></li>
								<li><strong>%username%</strong> &mdash; <?php _e("To display the username for login.","cooked"); ?></li>
								<li><strong>%password%</strong> &mdash; <?php _e("To display the password for login.","cooked"); ?></li>
							</ul><br>
							
							<?php
							
							$subject_var = 'cooked_registration_email_subject';
							$subject_default = 'Thank you for registering!';
							$current_subject_value = get_option($subject_var,$subject_default); ?>
							
							<input name="<?php echo $subject_var; ?>" value="<?php echo $current_subject_value; ?>" type="text" class="regular-text large">
							<textarea name="<?php echo $option_name; ?>" class="field large"><?php echo $email_content_registration; ?></textarea>
						</div>
					</div><!-- /.section-row -->
					
					<div class="section-row" data-controller="cp_fes_controller" data-controlled_by="fes_enabled">
						<div class="section-head">
							<?php $option_name = 'cooked_approval_email_content';
								
$default_content = 'Hey %name%!

The recipe you submitted to '.get_bloginfo('name').' has been approved! Here\'s your recipe information:

Recipe Name: %recipename%
Recipe Link: %recipelink%

Sincerely,
Your friends at '.get_bloginfo('name');

							$email_content_approval = get_option($option_name,$default_content);
							$section_title = __('Email Content - Recipe Approval', 'cooked'); ?>
							
							<h2><?php echo esc_attr($section_title); ?></h2>
							<p><?php _e('The email content that is sent to the user upon recipe approval. Some tokens you can use:','cooked'); ?></p>
							<ul class="cp-list">
								<li><strong>%name%</strong> &mdash; <?php _e("To display the person's name.","cooked"); ?></li>
								<li><strong>%recipename%</strong> &mdash; <?php _e("To display the recipe's name.","cooked"); ?></li>
								<li><strong>%recipelink%</strong> &mdash; <?php _e("To display the recipe's link.","cooked"); ?></li>
							</ul><br>
							
							<?php
							
							$subject_var = 'cooked_approval_email_subject';
							$subject_default = 'Your recipe has been approved!';
							$current_subject_value = get_option($subject_var,$subject_default); ?>
							
							<input name="<?php echo $subject_var; ?>" value="<?php echo $current_subject_value; ?>" type="text" class="regular-text large">
							<textarea name="<?php echo $option_name; ?>" class="field large"><?php echo $email_content_approval; ?></textarea>
						</div>
					</div><!-- /.section-row -->
				
				</div><!-- /templates -->
				
				
				
				
				
				<div id="cooked-admin-emails" class="tab-content">
					
					<div class="section-row">
						<div class="section-head">
							<p><strong style="font-size:17px; line-height:1.7;"><?php _e('If you do not want to send email notifications for any or all of the following actions, you can just delete the text and an email will not be sent.','cooked'); ?></strong></p>
						</div>
					</div>
						
					<div class="section-row">
						<div class="section-head">
							<?php $option_name = 'cooked_admin_recipe_email_content';
								
$default_content = 'You have a new recipe submission! Here\'s the recipe information:

Customer: %name%
Recipe Name: %recipename%
Recipe Link: %recipelink%

Log into your website here: '.get_admin_url().' to approve this recipe.

(Sent via the '.get_bloginfo('name').' website)';

							$email_content_registration = get_option($option_name,$default_content);
							$section_title = __('New Recipe Submission', 'cooked'); ?>
							
							<h2><?php echo esc_attr($section_title); ?></h2>
							<p><?php _e('The email content that is sent to the site admin upon recipe submission. Some tokens you can use:','cooked'); ?></p>
							<ul class="cp-list">
								<li><strong>%name%</strong> &mdash; <?php _e("To display the person's name.","cooked"); ?></li>
								<li><strong>%recipename%</strong> &mdash; <?php _e("To display the recipe's name.","cooked"); ?></li>
								<li><strong>%recipelink%</strong> &mdash; <?php _e("To display the recipe's link.","cooked"); ?></li>
							</ul><br>
							
							<?php
							
							$subject_var = 'cooked_admin_recipe_email_subject';
							$subject_default = 'You have a new recipe submission!';
							$current_subject_value = get_option($subject_var,$subject_default); ?>
							
							<input name="<?php echo $subject_var; ?>" value="<?php echo $current_subject_value; ?>" type="text" class="regular-text large">
							<textarea name="<?php echo $option_name; ?>" class="field large"><?php echo $email_content_registration; ?></textarea>
						</div>
					</div><!-- /.section-row -->
				
				</div><!-- /templates -->
				
				
				
				
				<div id="cooked-social" class="tab-content">
			
					<div class="section-row">
						<div class="section-head">
							<?php $option_name = 'cp_facebook_app_id';
							$app_id = get_option($option_name); ?>
							<?php $section_title = __('Facebook App ID', 'cooked'); ?>
							<h2><?php echo esc_attr($section_title); ?></h2>
							<p><?php _e('To activate the Facebook sharing feature, you need to enter a valid Facebook App ID.','cooked'); ?> <a href="#" class="view-instructions"><?php _e('View Instructions','cooked'); ?></a></p>
							<ol class="fb-instructions">
								<li><a target="_blank" href="https://developers.facebook.com/apps/"><?php _e('Click here', 'cooked'); ?></a> <?php _e('to create a new Facebook app.', 'cooked'); ?></li>
								<li><?php _e('Click the green <strong>+ Create new app</strong> button at the top right.', 'cooked'); ?></li>
								<li><?php _e('In the popup window, enter a <em>Display Name</em> and choose a <em>Category</em>. Then click <strong>Create App</strong>.', 'cooked'); ?></li>
								<li><?php _e('Fill in the required captcha and click <strong>Submit</strong>.', 'cooked'); ?></li>
								<li><?php _e('On the next page, click the <strong>Settings</strong> tab on the left.', 'cooked'); ?></li>
								<li><?php _e('Click the <strong>+ Add Platform</strong> button under the fields.', 'cooked'); ?></li>
								<li><?php _e('Choose <em>Website</em> and enter the URL of the website where Cooked is installed. Click <strong>Save Changes</strong>.', 'cooked'); ?></li>
								<li><?php _e('Enter the same URL in the <em>App Domains</em> field above. Click <strong>Save Changes</strong> again.', 'cooked'); ?></li>
								<li><?php _e('Now you can copy and paste the <em>App ID</em> at the top into the field on this page.', 'cooked'); ?></li>
							</ol>
						</div>
						<input type="text" name="<?php echo $option_name; ?>" value="<?php echo $app_id; ?>" class="regular-text" />
					</div><!-- /.section-row -->
		
					<div class="section-row">
						<div class="section-head">
							<?php $section_title = __('Sharing Options', 'cooked'); ?>
							<h2><?php echo esc_attr($section_title); ?></h2>
							<p><?php _e('Choose which sharing options you want to offer your visitors.','cooked'); ?></p>
						</div><!-- /.section-head -->
		
						<div class="section-body">
							<?php $option_name = 'cp_sharing_options';
								
							$sharing_options = array(
								'Pinterest' => 'pinterest',
								'Twitter' => 'twitter',
								'Google Plus' => 'google_plus',
								'Email' => 'email'
							);
							
							if ($app_id){ $sharing_options['Facebook'] = 'facebook'; } ?>
		
							<div class="checkbox-list">
								<?php $selected_sharing_options = get_option($option_name) ? get_option($option_name) : array();
								foreach($sharing_options as $network_name_label => $option_input_value) : ?>
									<div class="checkbox custom-checkbox">
										<input type="checkbox" id="checkbox-terms-<?php echo $option_input_value; ?>" name="<?php echo $option_name; ?>[]" value="<?php echo $option_input_value; ?>"<?php echo (in_array($option_input_value, $selected_sharing_options) ? ' checked="checked"' : ''); ?>/>
										<label for="checkbox-terms-<?php echo $option_input_value; ?>">
											<span class="custom-checkbox-fake"><i class="fa fa-check"></i></span>
											<?php echo esc_attr($network_name_label); ?>
										</label>
									</div><!-- /.checkbox -->
								<?php endforeach; ?>
							</div><!-- /.checkbox-list -->
		
						</div><!-- /.section-body -->
					</div><!-- /.section-row -->
				
				</div><!-- /social -->	
				
				
				
						
				<div id="cooked-styling" class="tab-content">
			
					<div class="section-row">
						<div class="section-head">
							<?php $section_title = __('PLUGIN STYLING OPTIONS', 'cooked'); ?>
							<h2><?php echo esc_attr($section_title); ?></h2>
						</div><!-- /.section-head -->
		
						<div class="section-body">
							<?php $option_name = 'cp_plugin_styling';
		
							$plugin_styling_options = array(
								__('Load all styles','cooked') => 'all_styles',
								__('Only layout styles','cooked') => 'layout_styles',
								__('Disable plugin styling','cooked') => 'disable_plugin_styles'
							); ?>
							<div class="radio-list">
								<?php $selected_value = get_option($option_name);
								foreach($plugin_styling_options as $entry_label => $entry_input_value) : ?>
									<div class="radio custom-radio">
										<input type="radio" id="radio-group-<?php echo $entry_input_value; ?>" class="cp_controller" name="<?php echo $option_name; ?>" value="<?php echo $entry_input_value; ?>"<?php echo ($selected_value == $entry_input_value ? ' checked="checked"' : ''); ?>/>
										<label for="radio-group-<?php echo $entry_input_value; ?>">
											<span class="custom-radio-fake"><i class="fa fa-check"></i></span>
											<?php echo esc_attr($entry_label); ?>
										</label>
									</div><!-- /.radio -->
								<?php endforeach; ?>
							</div><!-- /.radio-list -->
		
						</div><!-- /.section-body -->
					</div><!-- /.section-row -->
					
					<div class="section-row" data-controller="cp_controller" data-controlled_by="all_styles">
						<div class="section-head">
							<?php $section_title = __('COLOR SETTINGS', 'cooked'); ?>
							<h2><?php echo esc_attr($section_title); ?></h2><?php // TODO - WIP ?>
						</div><!-- /.section-head -->
						<div class="section-body">
						
							<?php
							$color_options = array(
								array(
									'name' => 'cp_main_color',
									'title' => 'Main Color',
									'val' => get_option('cp_main_color','#6DC02B'),
									'default' => '#6dc02b'
								),
								array(
									'name' => 'cp_light_color',
									'title' => 'Light Color',
									'val' => get_option('cp_light_color','#aade80'),
									'default' => '#aade80'

								),
								array(
									'name' => 'cp_dark_color',
									'title' => 'Dark Color',
									'val' => get_option('cp_dark_color','#3b8400'),
									'default' => '#3b8400'

								),
							);
							
							foreach($color_options as $color_option):
							
								echo '<label class="cp-color-label" for="'.$color_option['name'].'">'.$color_option['title'].'</label>';
								echo '<input data-default-color="'.$color_option['default'].'" type="text" name="'.$color_option['name'].'" value="'.$color_option['val'].'" id="'.$color_option['name'].'" class="cp-color-field" />';
								
							endforeach;
							?>
		
						</div><!-- /.section-body -->
					</div>
		
					<div class="section-row" data-controller="cp_controller" data-controlled_by="all_styles layout_styles">
						<div class="section-head">
							<?php $section_title = __('RESPONSIVE BREAKPOINTS', 'cooked'); ?>
							<h2><?php echo esc_attr($section_title); ?></h2>
						</div><!-- /.section-head -->
		
						<div class="section-body responsive-sliders">
							<?php $option_name = 'cp_disable_plugin_styling';
							$selected_value = get_option($option_name); ?>
							<div class="checkbox custom-checkbox">
								<input type="checkbox" id="checkbox-terms-cp_disable_plugin_styling" class="cp_sub_controller" name="<?php echo $option_name; ?>" value="1"<?php echo ($selected_value == 1 ? ' checked="checked"' : ''); ?>/>
								<label for="checkbox-terms-cp_disable_plugin_styling">
									<span class="custom-checkbox-fake"><i class="fa fa-check"></i></span>
									<?php _e('Disable responsive styling','cooked'); ?>
								</label>
							</div><!-- /.checkbox -->
							<div class="sliders-wrapper" data-controller="cp_sub_controller" data-controlled_by="1">
								<div class="resp-slider">
									<div class="slider-px slider-left-px">300px</div>
									<div class="rsp-slider"></div><!-- /.slider -->
									<div class="slider-px slider-right-px">1200px</div>
								</div><!-- /.resp-slider -->
								<?php $break_points = array(
									'one' => array(
										'label' => '<strong>Phone:</strong> lower than',
										'default' => 519
									),
									'two' => array(
										'label' => '<strong>7" Tablet:</strong>',
										'default' => 767
									),
									'three' => array(
										'label' => '<strong>10" Tablet:</strong>',
										'default' => 960
									),
								);
		
								$options_prefix = 'cp_responsive_break_';
								$js_values = array();
								foreach($break_points as $suffix => $data) :
		
									$option_name = $options_prefix . $suffix;
									$real_db_value = get_option($option_name);
									$db_value = $real_db_value != 0 ? $real_db_value : $data['default'];
									$js_values[] = $db_value; ?>
									<label for="dummy_<?php echo $suffix; ?>" class="slider-labels single-label"><?php echo $data['label']; ?></label>
									<input type="hidden" name="<?php echo $option_name; ?>" value="<?php echo $db_value; ?>" class="slider-inputs" />
									
								<?php endforeach; ?>
								<label for="dummy_desktop" class="slider-labels single-label desktop-label"><strong><?php _e('Desktop','cooked'); ?>:</strong> <?php _e('above','cooked'); ?> <span>960</span></label>
								<script type="text/javascript">
									var rsp_slider_values = [<?php echo implode(',', $js_values); ?>];
								</script>
							</div>
						</div><!-- /.section-body -->
					</div><!-- /.section-row -->
				
				</div>
				
				
				
				
				
				<div class="section-row submit-section" style="padding:0;">
					<?php @submit_button(); ?>
				</div><!-- /.section-row -->
				
				
				
				
				
			</form>
			
			
			
			
			
			<div id="cooked-import_export_uninstall" class="tab-content">
			
				<div class="import-export-row section-row" style="padding:0;">
					<div class="section-head">
						<?php $section_title = __('Import / Export', 'cooked'); ?>
						<h2><?php echo esc_attr($section_title); ?></h2>
					</div><!-- /.section-head -->
		
					<div class="section-body">
						<form method="post" enctype="multipart/form-data" class="import-form cp-import-form">
							<div class="import-holder">
								<a href="#" class="button button-primary button-large button-imex btn-import"> <?php _e('Import Settings','cooked'); ?></a>
								<input type="file" name="import_file" class="hidden-upload" id="upload-field" />
								<input type="hidden" name="settings-import" value="yes">
							</div>
						</form>
						<a href="<?php echo add_query_arg(array('export-settings' => true), remove_query_arg('settings-updated')); ?>" class="button button-primary button-large button-imex"> <?php _e('Export Settings','cooked'); ?></a>
					</div><!-- /.section-body -->
				</div><!-- /.section-row -->
		
				<form method="post" class="uninstall-row" action="<?php echo admin_url('edit.php?post_type=cp_recipe&page=cooked_plugin&noheader=true'); ?>">
					<div class="uninstall-row section-row" style="padding:30px 0 0 0; margin:0 200px 0 0;">
						<div class="section-head">
							<?php $section_title = __('Uninstall', 'cooked'); ?>
							<h2><?php echo esc_attr($section_title); ?></h2>
						</div><!-- /.section-head -->
		
						<div class="section-body">
							<div class="un-msg">
								<i class="fa fa-exclamation-triangle"></i>
								<p><?php _e('<strong>Warning!</strong> This operation deletes ALL &ldquo;Cooked&rdquo; data and disables the plugin. If you <br />continue, you will not be able to retrieve or restore your recipes or reviews.','cooked'); ?></p>
							</div><!-- /.un-msg -->
							<a href="#" class="button button-primary button-large button-unn"><i class="fa fa-times"></i> <?php _e('I understand, uninstall the plugin please.','cooked'); ?></a>
							<input type="hidden" name="cp_do_uninstall" value="yes" />
						</div><!-- /.section-body -->
					</div><!-- /.section-row -->
				</form>
				
			</div>
			
			
			
			
				
		</div>
		
	</div>
</div>