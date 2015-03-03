<?php

function cp_edit_recipe_form($recipe_id) {

	ob_start();
	
	// Get the current user's role
	if ( is_user_logged_in() ) {
		
		global $current_user;
		$required_user_roles = get_option('cp_recipes_fes_user_roles');
		$user_display_name = $current_user->data->display_name;
		$current_user_role = $current_user->roles[0];
		
		$cooked_plugin = new cooked_plugin();
		$enabled_taxonomies = $cooked_plugin->cp_recipe_tax_settings();
		
		$recipe_info = cp_recipe_info_settings();
		
		if (!empty($required_user_roles)):
		
			if (in_array($current_user_role,$required_user_roles)){
	
				global $cp_form_complete;
				
				$recipe_title = get_the_title();
				$recipe_video = get_post_meta($recipe_id, '_cp_recipe_external_video', true);
				$description = get_post_meta($recipe_id, '_cp_recipe_short_description', true);
				$excerpt = get_post_meta($recipe_id, '_cp_recipe_excerpt', true);
				$difficulty = get_post_meta($recipe_id, '_cp_recipe_difficulty_level', true);
				
				$directions = get_post_meta($recipe_id, '_cp_recipe_detailed_directions',true);
				if (!empty($directions)):
					$detailed_directions = true;
				else :
					$directions = get_post_meta($recipe_id, '_cp_recipe_directions', true);
					$detailed_directions = false;
				endif;
				
				$ingredients = get_post_meta($recipe_id, '_cp_recipe_detailed_ingredients',true);
				if (!empty($ingredients)):
					$detailed_ingredients = true;
				else :
					$ingredients = get_post_meta($recipe_id, '_cp_recipe_ingredients', true);
					$detailed_ingredients = false;
				endif;
				
				$notes = get_post_meta($recipe_id, '_cp_recipe_additional_notes', true);
				$prep_time = get_post_meta($recipe_id, '_cp_recipe_prep_time', true);
				$cook_time = get_post_meta($recipe_id, '_cp_recipe_cook_time', true);
				$yields = get_post_meta($recipe_id, '_cp_recipe_yields', true);
				
				$servingsize = get_post_meta($recipe_id, '_cp_recipe_nutrition_servingsize', true);
				$calories = get_post_meta($recipe_id, '_cp_recipe_nutrition_calories', true);
				$sodiumcontent = get_post_meta($recipe_id, '_cp_recipe_nutrition_sodium', true);
				$potassiumcontent = get_post_meta($recipe_id, '_cp_recipe_nutrition_potassium', true);
				$proteincontent = get_post_meta($recipe_id, '_cp_recipe_nutrition_protein', true);
				$cholesterolcontent = get_post_meta($recipe_id, '_cp_recipe_nutrition_cholesterol', true);
				$sugarcontent = get_post_meta($recipe_id, '_cp_recipe_nutrition_sugar', true);
				$fatcontent = get_post_meta($recipe_id, '_cp_recipe_nutrition_fat', true);
				$saturatedfatcontent = get_post_meta($recipe_id, '_cp_recipe_nutrition_satfat', true);
				$polyunsatfat = get_post_meta($recipe_id, '_cp_recipe_nutrition_polyunsatfat', true);
				$monounsatfat = get_post_meta($recipe_id, '_cp_recipe_nutrition_monounsatfat', true);
				$transfat = get_post_meta($recipe_id, '_cp_recipe_nutrition_transfat', true);
				$carbohydratecontent = get_post_meta($recipe_id, '_cp_recipe_nutrition_carbs', true);
				$fibercontent = get_post_meta($recipe_id, '_cp_recipe_nutrition_fiber', true);
				
				?><div id="cooked-recipe-editor"><form id="cooked-submit-recipe-form" action="<?php the_permalink(); ?>/?edit" method="post" enctype="multipart/form-data">
					
					<?php do_action( 'cp_recipe_form_notice' ); ?>
					
					<div class="section-row">
						<div class="section-col">
							<div class="section-head">
								<h2><?php _e('Recipe Title', 'cooked'); ?></h2>
							</div><!-- /.section-head -->
							<div class="section-body">
								<div class="field-wrap">
									<input type="text" class="field" name="_cp_recipe_title" value="<?php echo (isset($recipe_title) ? $recipe_title : ''); ?>" />
									<p class="hint-p"><?php _e('Keep it short and descriptive.', 'cooked'); ?></p>
								</div><!-- /.field-wrap -->
							</div><!-- /.section-body -->
						</div>
						<div class="section-col">
							<div class="section-head">
								<h2><?php _e('Recipe Photo', 'cooked'); ?></h2>
							</div><!-- /.section-head -->
					
							<div class="section-body">
								<div class="field-wrap">
									<span class="cp-upload-wrap"><span><?php _e('Choose image ...','cooked'); ?></span><input<?php if (CP_DEMO_MODE): ?> disabled<?php endif; ?> class="field" name="_cp_recipe_image" type="file" id="_cp_recipe_image" value="" /></span>
									<?php wp_nonce_field( '_cp_recipe_image_upload', '_cp_recipe_image_nonce' ); ?>
									<p class="hint-p"><?php _e('Recommended size: 2000px by 600px or larger', 'cooked'); ?></p>
								</div><!-- /.upload-field-wrap -->
							</div><!-- /.section-body -->
						</div>
					</div><!-- /.section-row -->
					
					<div class="section-row">
						<div class="section-head">
							<h2><?php _e('Recipe Video', 'cooked'); ?></h2>
						</div><!-- /.section-head -->
						<div class="section-body">
							<div class="field-wrap">
								<input type="text" class="field" name="_cp_recipe_external_video" value="<?php echo (isset($recipe_video) ? $recipe_video : ''); ?>" />
							</div><!-- /.field-wrap -->
							<p class="hint-p">
								<strong><?php _e('OPTIONAL:','cooked'); ?></strong> <?php _e('If you have your recipe video on Youtube, Vimeo, or','cooked'); ?> <a href="http://codex.wordpress.org/Embeds" target="_blank"><?php _e('any of the other supported oEmbed sites','cooked'); ?></a>, <?php _e('then you\'ll want to use the field above. Just paste in the URL','cooked'); ?> (<?php _e('ex','cooked'); ?>. <em>http://youtu.be/1O8D_wTCm3s</em> <?php _e('or','cooked'); ?> <em>https://vimeo.com/26140401</em>) <?php _e('and it will show up as a popup by clicking the recipe image.','cooked'); ?>
							</p><!-- /.hint-p -->
						</div><!-- /.section-body -->
					</div><!-- /.section-row -->
					
					<?php if (!in_array('difficulty_level',$recipe_info)): ?><div class="cp-hidden"><?php endif; ?>
						<div class="section-row cookedClearFix">
							<div class="section-head"><h2><?php _e('Difficulty Level', 'cooked'); ?></h2></div>
							<?php cp_difficulty_dropdown('_cp_recipe_difficulty_level', 'Difficulty level...', $difficulty); ?>
						</div>
					<?php if (!in_array('difficulty_level',$recipe_info)): ?></div><?php endif; ?>
					
					<?php if (!in_array('category',$recipe_info) && !in_array('cuisine',$recipe_info) && !in_array('method',$recipe_info)): ?><div class="cp-hidden"><?php endif; ?>
					
						<?php $tax_columns = 0;
						if (in_array('category',$enabled_taxonomies)): $tax_columns++; endif;
						if (in_array('cuisine',$enabled_taxonomies)): $tax_columns++; endif;
						if (in_array('method',$enabled_taxonomies)): $tax_columns++; endif;
						
						if ($tax_columns): ?>
							<div class="section-row cookedClearFix">
								
								<?php if (!in_array('category',$recipe_info)): ?><div class="cp-hidden"><?php endif; ?>
								<?php if (in_array('category',$enabled_taxonomies)):
									$id_list = wp_get_post_terms( $recipe_id, 'cp_recipe_category' );
									$category = array();
									foreach($id_list as $term){
										$category[] = $term->term_id;
									} ?>
									<div class="<?php if ($tax_columns == 3) : ?>section-third<?php elseif ($tax_columns == 2) : ?>section-col<?php endif; ?>"><div class="section-head"><h2><?php _e('Recipe Category', 'cooked'); ?></h2></div><?php cp_taxonomy_dropdown('cp_recipe_category', __('Choose one or more...', 'cooked'),$category); ?></div>
								<?php endif; ?>
								<?php if (!in_array('category',$recipe_info)): ?></div><?php endif; ?>
								
								<?php if (!in_array('cuisine',$recipe_info)): ?><div class="cp-hidden"><?php endif; ?>
								<?php if (in_array('cuisine',$enabled_taxonomies)):
									$id_list = wp_get_post_terms( $recipe_id, 'cp_recipe_cuisine' );
									$cuisine = array();
									foreach($id_list as $term){
										$cuisine[] = $term->term_id;
									} ?>
									<div class="<?php if ($tax_columns == 3) : ?>section-third<?php elseif ($tax_columns == 2) : ?>section-col<?php endif; ?>"><div class="section-head"><h2><?php _e('Cuisine', 'cooked'); ?></h2></div><?php cp_taxonomy_dropdown('cp_recipe_cuisine', __('Choose one or more...', 'cooked'),$cuisine); ?></div>
								<?php endif; ?>
								<?php if (!in_array('cuisine',$recipe_info)): ?></div><?php endif; ?>
								
								<?php if (!in_array('method',$recipe_info)): ?><div class="cp-hidden"><?php endif; ?>
								<?php if (in_array('method',$enabled_taxonomies)):
									$id_list = wp_get_post_terms( $recipe_id, 'cp_recipe_cooking_method' );
									$method = array();
									foreach($id_list as $term){
										$method[] = $term->term_id;
									} ?>
									<div class="<?php if ($tax_columns == 3) : ?>section-third<?php elseif ($tax_columns == 2) : ?>section-col<?php endif; ?>"><div class="section-head"><h2><?php _e('Cooking Method', 'cooked'); ?></h2></div><?php cp_taxonomy_dropdown('cp_recipe_cooking_method', __('Choose one or more...', 'cooked'),$method); ?></div>
								<?php endif; ?>
								<?php if (!in_array('method',$recipe_info)): ?></div><?php endif; ?>
								
							</div>
						<?php endif; ?>
					
					<?php if (!in_array('category',$recipe_info) && !in_array('cuisine',$recipe_info) && !in_array('method',$recipe_info)): ?></div><?php endif; ?>
				
					<?php if (!in_array('description',$recipe_info)): ?><div class="cp-hidden"><?php endif; ?>
					<div class="section-row">
						<div class="section-head">
							<h2><?php _e('Short Description', 'cooked'); ?></h2>
						</div><!-- /.section-head -->
				
						<div class="section-body">
							<textarea class="field small" name="_cp_recipe_short_description" cols="0" rows="0"><?php echo (isset($description) ? stripslashes($description) : ''); ?></textarea>
						</div><!-- /.section-body -->
					</div><!-- /.section-row -->
					<div class="section-row">
						<div class="section-head">
							<h2><?php _e('Excerpt for List Views', 'cooked'); ?></h2>
						</div><!-- /.section-head -->
				
						<div class="section-body">
							<textarea class="field small" name="_cp_recipe_excerpt" cols="0" rows="0"><?php echo (isset($excerpt) ? stripslashes($excerpt) : ''); ?></textarea>
							<p class="hint-p">
								<strong><?php _e('OPTIONAL:','cooked'); ?></strong> <?php _e('If you include an excerpt, it will replace your short description in the recipe list views.','cooked'); ?>
							</p><!-- /.hint-p -->
						</div><!-- /.section-body -->
					</div><!-- /.section-row -->
					<?php if (!in_array('description',$recipe_info)): ?></div><?php endif; ?>
					
					<?php if (!in_array('timing',$recipe_info)): ?><div class="cp-hidden"><?php endif; ?>
					<div class="section-row">
						<div class="section-col">
							<div class="section-head">
								<h2><?php _e('Prep Time', 'cooked'); ?></h2>
							</div><!-- /.section-head -->
				
							<div class="section-body"><?php
				
								$max_prep_time = (get_option('cp_prep_time_max_hrs') ? get_option('cp_prep_time_max_hrs') : 12);
								
								?><div class="slider" data-maxval="<?php echo $max_prep_time*60*60; ?>">
									<div class="amount"><i class="fa fa-clock-o"></i> <input type="text" maxlength="5" value="00:00" class="slider-timer" disabled />
										<input type="hidden" name="_cp_recipe_prep_time" value="<?php echo (isset($prep_time) ? $prep_time : ''); ?>" class="real-value" />
									</div><!-- /.amount -->
								</div><!-- /.slider -->
								<div class="slider-hint">
									<p class="left">00:00</p><!-- /.left -->
									<p class="center">HH : MM</p><!-- /.center -->
									<p class="right"><?php echo $max_prep_time; ?>:00</p><!-- /.right -->
								</div><!-- /.slider-hint -->
							</div><!-- /.section-body -->
						</div><!-- /.section-col -->
				
						<div class="section-col">
							<div class="section-head">
								<h2><?php _e('Cook Time', 'cooked'); ?></h2>
							</div><!-- /.section-head -->
				
							<div class="section-body"><?php
				
								$max_cook_time = (get_option('cp_cook_time_max_hrs') ? get_option('cp_cook_time_max_hrs') : 12);
								
								?><div class="slider" data-maxval="<?php echo $max_cook_time*60*60; ?>">
									<div class="amount"><i class="fa fa-clock-o"></i> <input type="text" maxlength="5" value="00:00" class="slider-timer" disabled />
										<input type="hidden" name="_cp_recipe_cook_time" value="<?php echo (isset($cook_time) ? $cook_time : ''); ?>" class="real-value" />
									</div><!-- /.amount -->
								</div><!-- /.slider -->
								<div class="slider-hint">
									<p class="left">00:00</p><!-- /.left -->
									<p class="center">HH : MM</p><!-- /.center -->
									<p class="right"><?php echo $max_cook_time; ?>:00</p><!-- /.right -->
								</div><!-- /.slider-hint -->
							</div><!-- /.section-body -->	
						</div><!-- /.section-col -->
					</div><!-- /.section-row -->
					<?php if (!in_array('timing',$recipe_info)): ?></div><?php endif; ?>
				
					<div class="section-row">
						<div class="section-head">
							<h2><?php _e('Ingredients', 'cooked'); ?></h2>
						</div><!-- /.section-head -->
				
						<div class="section-body section-stats">
							<?php if ($detailed_ingredients): ?>
								<p style="color:#888;"><em><?php _e('The ingredients have been entered in "detailed mode". They can only be edited by an administrator.'); ?></em></p>
							<?php else : ?>
								<div class="section-title-box">
									<p class="section-stats-first" data-plural="Sections" data-single="Section">0 Sections</p> /
									<p class="section-stats-second" data-plural="Ingredients" data-single="Ingredient">0 Ingredients</p>
								</div><!-- /.section-title-box -->
								<textarea class="field med section-stats-field" name="_cp_recipe_ingredients" cols="0" rows="0"><?php echo (isset($ingredients) ? stripslashes($ingredients) : ''); ?></textarea>
								<p class="hint-p">
									<?php _e('Enter one ingredient per line. Use a double dash to start new section titles. (ex. --Section Title). Separate the ingredient amount from the ingredient name with double space if you want to follow Google\'s rich snippets formatting.','cooked'); ?>
								</p><!-- /.hint-p -->
							<?php endif; ?>
						</div><!-- /.section-body -->
					</div><!-- /.section-row -->
				
					<div class="section-row">
						<div class="section-head">
							<h2><?php _e('Directions', 'cooked'); ?></h2>
						</div><!-- /.section-head -->
				
						<div class="section-body section-stats">
							<?php if ($detailed_directions): ?>
								<p style="color:#888;"><em><?php _e('The directions have been entered in "detailed mode". They can only be edited by an administrator.'); ?></em></p>
							<?php else : ?>
								<div class="section-title-box">
									<p class="section-stats-first" data-plural="Sections" data-single="Section">0 Sections</p> /
									<p class="section-stats-second" data-plural="Steps" data-single="Step">0 Steps</p>
								</div><!-- /.section-title-box -->
								<textarea class="field med section-stats-field" name="_cp_recipe_directions" cols="0" rows="0"><?php echo (isset($directions) ? stripslashes($directions) : ''); ?></textarea>
								<p class="hint-p"><?php _e('Add all of the cooking steps, one per line. You can use a double dash for section titles (ex. --Section Title). You can also use the <strong>[timer length=30]30 Minutes[/timer]</strong> shortcode to add a timer link.','cooked'); ?></p><!-- /.hint-p -->
							<?php endif; ?>
						</div><!-- /.section-body -->
					</div><!-- /.section-row -->
					
					<?php if (!in_array('notes',$recipe_info)): ?><div class="cp-hidden"><?php endif; ?>
					<div class="section-row">
						<div class="section-head">
							<h2><?php _e('Additional Notes', 'cooked'); ?></h2>
						</div><!-- /.section-head -->
				
						<div class="section-body">
							<textarea class="field small" name="_cp_recipe_additional_notes" cols="0" rows="0"><?php echo (isset($notes) ? stripslashes($notes) : ''); ?></textarea>
							<p class="hint-p">
								<?php _e('Add any other notes like recipe source, cooking hints, etc. This section will show up under the cooking directions.','cooked'); ?>
							</p><!-- /.hint-p -->

						</div><!-- /.section-body -->
					</div><!-- /.section-row -->
					<?php if (!in_array('notes',$recipe_info)): ?></div><?php endif; ?>
					
					<?php if (!in_array('yields',$recipe_info)): ?><div class="cp-hidden"><?php endif; ?>
					<div class="section-row">
						<div class="section-head">
							<h2><?php _e('Yields', 'cooked'); ?></h2>
						</div><!-- /.section-head -->
						<div class="section-body">
							<div class="field-wrap">
								<input type="text" class="field" name="_cp_recipe_yields" value="<?php echo (isset($yields) ? $yields : ''); ?>" />
							</div><!-- /.field-wrap -->
							<p class="hint-p"><?php _e('ex. 4 Servings, 3 Cups, 6 Bowls, etc.','cooked'); ?></p><!-- /.hint-p -->
						</div><!-- /.section-body -->
					</div><!-- /.section-row -->
					<?php if (!in_array('yields',$recipe_info)): ?></div><?php endif; ?>
					
					<?php if (!in_array('nutrition',$recipe_info)): ?><div class="cp-hidden"><?php endif; ?>
					<div class="section-row nutrition">
						<div class="section-head">
							<h2><?php _e('Nutrition Facts', 'cooked'); ?></h2>
						</div>
						<div class="section-row">
					
							<div class="section-col">
								<div class="section-head">
									<h3><?php _e('Serving Size', 'cooked'); ?></h3>
								</div><!-- /.section-head -->
								<div class="section-body">
									<div class="field-wrap">
										<input type="text" class="field" name="_cp_recipe_nutrition_servingsize" value="<?php echo (isset($servingsize) ? $servingsize : ''); ?>" />
									</div><!-- /.field-wrap -->
								</div><!-- /.section-body -->	
							</div><!-- /.section-col -->
					
							<div class="section-col">
								<div class="section-head">
									<h3><?php _e('Calories', 'cooked'); ?></h3>
								</div><!-- /.section-head -->
								<div class="section-body">
									<div class="field-wrap">
										<input type="text" class="field" name="_cp_recipe_nutrition_calories" value="<?php echo (isset($calories) ? $calories : ''); ?>" />
									</div><!-- /.field-wrap -->
								</div><!-- /.section-body -->	
							</div><!-- /.section-col -->
							
							<div class="section-col">
								<div class="section-head">
									<h3><?php _e('Total Fat', 'cooked'); ?></h3>
								</div><!-- /.section-head -->
								<div class="section-body">
									<div class="field-wrap">
										<input type="text" class="field" name="_cp_recipe_nutrition_fat" value="<?php echo (isset($fatcontent) ? $fatcontent : ''); ?>" />
									</div><!-- /.field-wrap -->
								</div><!-- /.section-body -->	
							</div><!-- /.section-col -->
							
							<div class="section-col">
								<div class="section-head">
									<h3><?php _e('Saturated Fat', 'cooked'); ?></h3>
								</div><!-- /.section-head -->
								<div class="section-body">
									<div class="field-wrap">
										<input type="text" class="field" name="_cp_recipe_nutrition_satfat" value="<?php echo (isset($saturatedfatcontent) ? $saturatedfatcontent : ''); ?>" />
									</div><!-- /.field-wrap -->
								</div><!-- /.section-body -->	
							</div><!-- /.section-col -->
							
							<div class="section-col">
								<div class="section-head">
									<h3><?php _e('Polyunsaturated Fat', 'cooked'); ?></h3>
								</div><!-- /.section-head -->
								<div class="section-body">
									<div class="field-wrap">
										<input type="text" class="field" name="_cp_recipe_nutrition_polyunsatfat" value="<?php echo (isset($polyunsatfat) ? $polyunsatfat : ''); ?>" />
									</div><!-- /.field-wrap -->
								</div><!-- /.section-body -->	
							</div><!-- /.section-col -->
							
							<div class="section-col">
								<div class="section-head">
									<h3><?php _e('Monounsaturated Fat', 'cooked'); ?></h3>
								</div><!-- /.section-head -->
								<div class="section-body">
									<div class="field-wrap">
										<input type="text" class="field" name="_cp_recipe_nutrition_monounsatfat" value="<?php echo (isset($monounsatfat) ? $monounsatfat : ''); ?>" />
									</div><!-- /.field-wrap -->
								</div><!-- /.section-body -->	
							</div><!-- /.section-col -->
							
							<div class="section-col">
								<div class="section-head">
									<h3><?php _e('Trans Fat', 'cooked'); ?></h3>
								</div><!-- /.section-head -->
								<div class="section-body">
									<div class="field-wrap">
										<input type="text" class="field" name="_cp_recipe_nutrition_transfat" value="<?php echo (isset($transfat) ? $transfat : ''); ?>" />
									</div><!-- /.field-wrap -->
								</div><!-- /.section-body -->	
							</div><!-- /.section-col -->
							
							<div class="section-col">
								<div class="section-head">
									<h3><?php _e('Cholesterol', 'cooked'); ?></h3>
								</div><!-- /.section-head -->
								<div class="section-body">
									<div class="field-wrap">
										<input type="text" class="field" name="_cp_recipe_nutrition_cholesterol" value="<?php echo (isset($cholesterolcontent) ? $cholesterolcontent : ''); ?>" />
									</div><!-- /.field-wrap -->
								</div><!-- /.section-body -->	
							</div><!-- /.section-col -->
							
							<div class="section-col">
								<div class="section-head">
									<h3><?php _e('Sodium', 'cooked'); ?></h3>
								</div><!-- /.section-head -->
								<div class="section-body">
									<div class="field-wrap">
										<input type="text" class="field" name="_cp_recipe_nutrition_sodium" value="<?php echo (isset($sodiumcontent) ? $sodiumcontent : ''); ?>" />
									</div><!-- /.field-wrap -->
								</div><!-- /.section-body -->	
							</div><!-- /.section-col -->
							
							<div class="section-col">
								<div class="section-head">
									<h3><?php _e('Potassium', 'cooked'); ?></h3>
								</div><!-- /.section-head -->
								<div class="section-body">
									<div class="field-wrap">
										<input type="text" class="field" name="_cp_recipe_nutrition_potassium" value="<?php echo (isset($potassiumcontent) ? $potassiumcontent : ''); ?>" />
									</div><!-- /.field-wrap -->
								</div><!-- /.section-body -->	
							</div><!-- /.section-col -->
							
							<div class="section-col">
								<div class="section-head">
									<h3><?php _e('Total Carbohydrate', 'cooked'); ?></h3>
								</div><!-- /.section-head -->
								<div class="section-body">
									<div class="field-wrap">
										<input type="text" class="field" name="_cp_recipe_nutrition_carbs" value="<?php echo (isset($carbohydratecontent) ? $carbohydratecontent : ''); ?>" />
									</div><!-- /.field-wrap -->
								</div><!-- /.section-body -->	
							</div><!-- /.section-col -->
							
							<div class="section-col">
								<div class="section-head">
									<h3><?php _e('Fiber', 'cooked'); ?></h3>
								</div><!-- /.section-head -->
								<div class="section-body">
									<div class="field-wrap">
										<input type="text" class="field" name="_cp_recipe_nutrition_fiber" value="<?php echo (isset($fibercontent) ? $fibercontent : ''); ?>" />
									</div><!-- /.field-wrap -->
								</div><!-- /.section-body -->	
							</div><!-- /.section-col -->
							
							<div class="section-col">
								<div class="section-head">
									<h3><?php _e('Sugar', 'cooked'); ?></h3>
								</div><!-- /.section-head -->
								<div class="section-body">
									<div class="field-wrap">
										<input type="text" class="field" name="_cp_recipe_nutrition_sugar" value="<?php echo (isset($sugarcontent) ? $sugarcontent : ''); ?>" />
									</div><!-- /.field-wrap -->
								</div><!-- /.section-body -->	
							</div><!-- /.section-col -->
							
							<div class="section-col">
								<div class="section-head">
									<h3><?php _e('Protein', 'cooked'); ?></h3>
								</div><!-- /.section-head -->
								<div class="section-body">
									<div class="field-wrap">
										<input type="text" class="field" name="_cp_recipe_nutrition_protein" value="<?php echo (isset($proteincontent) ? $proteincontent : ''); ?>" />
									</div><!-- /.field-wrap -->
								</div><!-- /.section-body -->	
							</div><!-- /.section-col -->
							
						</div><!-- /.section-row -->
					</div>
					<?php if (!in_array('nutrition',$recipe_info)): ?></div><?php endif; ?>
					
					<input id="submit" type="submit" class="sbmt-button" value="<?php esc_attr_e( 'Update Recipe', 'cooked' ); ?>" />					
					<input type="hidden" name="action" value="edit_recipe" />
					<input type="hidden" name="recipe_id" value="<?php echo $recipe_id; ?>" />
					<?php wp_nonce_field( 'new-post' ); ?>
				
				</form></div><?php
				
			} else {
				$no_access_message = get_option('cp_fes_no_access_message');
				echo ($no_access_message ? '<div id="cooked-submit-recipe-form">'.wpautop(do_shortcode($no_access_message)).'</div>' : '');
			}
		
		else :
		
			$no_access_message = get_option('cp_fes_no_access_message');
			echo ($no_access_message ? '<div id="cooked-submit-recipe-form">'.wpautop(do_shortcode($no_access_message)).'</div>' : '');
			
		endif;
		
	} else {
		$no_access_message = get_option('cp_fes_no_access_message');
		echo ($no_access_message ? '<div id="cooked-submit-recipe-form">'.wpautop(do_shortcode($no_access_message)).'</div>' : '');
	}
	
	// Output the form
	$output = ob_get_clean();
	
	$output = str_replace(
		array('[timer','/timer]'),
		array('[[timer','/timer]]'),
		$output
	);
	
	return $output;

}
 
function cp_edit_recipe_form_submit_post(){

	if ( 'POST' == $_SERVER['REQUEST_METHOD'] && !empty( $_POST['action'] ) && $_POST['action'] == 'edit_recipe' ){
	
		if ( !is_user_logged_in() )
			return;
		global $current_user;
		
		$required_user_roles = get_option('cp_recipes_fes_user_roles');
		$current_user_role = $current_user->roles[0];
		if (in_array($current_user_role,$required_user_roles)){
		
			$cooked_plugin = new cooked_plugin();
			$enabled_taxonomies = $cooked_plugin->cp_recipe_tax_settings();
			
			$post_id 			= $_POST['recipe_id'];
			$user_id			= $current_user->ID;
			$post_title     	= $_POST['_cp_recipe_title'];
			$category 			= (in_array('category',$enabled_taxonomies) && isset($_POST['cp_recipe_category']) ? $_POST['cp_recipe_category'] : false);
			$cooking_method 	= (in_array('method',$enabled_taxonomies) && isset($_POST['cp_recipe_cooking_method']) ? $_POST['cp_recipe_cooking_method'] : false);
			$cuisine 			= (in_array('cuisine',$enabled_taxonomies) && isset($_POST['cp_recipe_cuisine']) ? $_POST['cp_recipe_cuisine'] : false);
			$photo 				= $_FILES['_cp_recipe_image'];
			$video 				= $_POST['_cp_recipe_external_video'];
			$short_desc 		= $_POST['_cp_recipe_short_description'];
			$excerpt			= $_POST['_cp_recipe_excerpt'];
			$difficulty 		= $_POST['_cp_recipe_difficulty_level'];
			$prep_time	 		= $_POST['_cp_recipe_prep_time'];
			$cook_time	 		= $_POST['_cp_recipe_cook_time'];
			$ingredients 		= $_POST['_cp_recipe_ingredients'];
			$directions 		= $_POST['_cp_recipe_directions'];
			$additional_notes 	= $_POST['_cp_recipe_additional_notes'];
			$yields		 		= $_POST['_cp_recipe_yields'];
			$servingsize 		= $_POST['_cp_recipe_nutrition_servingsize'];
			$calories 			= $_POST['_cp_recipe_nutrition_calories'];
			$sodiumcontent 		= $_POST['_cp_recipe_nutrition_sodium'];
			$potassiumcontent 	= $_POST['_cp_recipe_nutrition_potassium'];
			$proteincontent 	= $_POST['_cp_recipe_nutrition_protein'];
			$cholesterolcontent = $_POST['_cp_recipe_nutrition_cholesterol'];
			$sugarcontent 		= $_POST['_cp_recipe_nutrition_sugar'];
			$fatcontent 		= $_POST['_cp_recipe_nutrition_fat'];
			$saturatedfatcontent = $_POST['_cp_recipe_nutrition_satfat'];
			$polyunsatfat 		= $_POST['_cp_recipe_nutrition_polyunsatfat'];
			$monounsatfat 		= $_POST['_cp_recipe_nutrition_monounsatfat'];
			$transfat 			= $_POST['_cp_recipe_nutrition_transfat'];
			$carbohydratecontent = $_POST['_cp_recipe_nutrition_carbs'];
			$fibercontent 		= $_POST['_cp_recipe_nutrition_fiber'];
			
			global $error_array;
			$error_array = array();
	 
			if (empty($post_title)) $error_array[] = __('Please add a title.','cooked');
			if (empty($ingredients)) $error_array[] = __('Please add some ingredients.','cooked');
			if (empty($directions)) $error_array[] = __('Please add the directions.','cooked');
	 
			if (count($error_array) == 0){
				
				if ($post_id){
					
					if (is_array($category)):
					$category = array_map( 'intval', $category );
					$category = array_unique( $category );
					endif;
					
					if (is_array($cooking_method)):
					$cooking_method = array_map( 'intval', $cooking_method );
					$cooking_method = array_unique( $cooking_method );
					endif;
					
					if (is_array($cuisine)):
					$cuisine = array_map( 'intval', $cuisine );
					$cuisine = array_unique( $cuisine );
					endif;
					
					wp_set_object_terms( $post_id, $category, 'cp_recipe_category' );
					wp_set_object_terms( $post_id, $cooking_method, 'cp_recipe_cooking_method' );
					wp_set_object_terms( $post_id, $cuisine, 'cp_recipe_cuisine' );
				
					update_post_meta($post_id, '_cp_recipe_external_video', wp_strip_all_tags($video));
					update_post_meta($post_id, '_cp_recipe_short_description', $short_desc);
					update_post_meta($post_id, '_cp_recipe_excerpt', $excerpt);
					update_post_meta($post_id, '_cp_recipe_prep_time', $prep_time);
					update_post_meta($post_id, '_cp_recipe_cook_time', $cook_time);
					update_post_meta($post_id, '_cp_recipe_difficulty_level', $difficulty);
					update_post_meta($post_id, '_cp_recipe_ingredients', wp_strip_all_tags($ingredients));
					update_post_meta($post_id, '_cp_recipe_directions', wp_strip_all_tags($directions));
					update_post_meta($post_id, '_cp_recipe_additional_notes', $additional_notes);
					update_post_meta($post_id, '_cp_recipe_yields', wp_strip_all_tags($yields));
					update_post_meta($post_id, '_cp_recipe_nutrition_servingsize', wp_strip_all_tags($servingsize));
					update_post_meta($post_id, '_cp_recipe_nutrition_calories', wp_strip_all_tags($calories));
					update_post_meta($post_id, '_cp_recipe_nutrition_sodium', wp_strip_all_tags($sodiumcontent));
					update_post_meta($post_id, '_cp_recipe_nutrition_potassium', wp_strip_all_tags($potassiumcontent));
					update_post_meta($post_id, '_cp_recipe_nutrition_protein', wp_strip_all_tags($proteincontent));
					update_post_meta($post_id, '_cp_recipe_nutrition_cholesterol', wp_strip_all_tags($cholesterolcontent));
					update_post_meta($post_id, '_cp_recipe_nutrition_sugar', wp_strip_all_tags($sugarcontent));
					update_post_meta($post_id, '_cp_recipe_nutrition_fat', wp_strip_all_tags($fatcontent));
					update_post_meta($post_id, '_cp_recipe_nutrition_satfat', wp_strip_all_tags($saturatedfatcontent));
					update_post_meta($post_id, '_cp_recipe_nutrition_polyunsatfat', wp_strip_all_tags($polyunsatfat));
					update_post_meta($post_id, '_cp_recipe_nutrition_monounsatfat', wp_strip_all_tags($monounsatfat));
					update_post_meta($post_id, '_cp_recipe_nutrition_transfat', wp_strip_all_tags($transfat));
					update_post_meta($post_id, '_cp_recipe_nutrition_carbs', wp_strip_all_tags($carbohydratecontent));
					update_post_meta($post_id, '_cp_recipe_nutrition_fiber', wp_strip_all_tags($fibercontent));
					
					// Save content to hidden editor for search improvements
					$recipe_title = get_the_title($post_id);
					$recipe_short_desc = get_post_meta($post_id, '_cp_recipe_short_description', true);
					$recipe_yields = get_post_meta($post_id, '_cp_recipe_yields', true);
					$recipe_ingredients = get_post_meta($post_id, '_cp_recipe_ingredients', true);
					$recipe_directions = get_post_meta($post_id, '_cp_recipe_directions', true);
					$recipe_notes = get_post_meta($post_id, '_cp_recipe_additional_notes', true);
					
					$post_content = '<p>'.$recipe_short_desc.'</p><p>'.$recipe_yields.'</p><p>'.$recipe_ingredients.'</p><p>'.$recipe_directions.'</p><p>'.$recipe_notes.'</p>';
					
					// Update Post Content
					$new_post_content = array(
					    'ID'           => $post_id,
					    'post_content' => $post_content,
					    'post_title'   => $post_title
					);
					
					wp_update_post( $new_post_content );
					
				} else {
					$error_array[] = __('Error submitting recipe, please try again.','cooked');
				}
					
				// Now let's upload the photo
				if (isset($_FILES['_cp_recipe_image']) && !$_FILES['_cp_recipe_image']['error'] && isset($photo,$_POST['_cp_recipe_image_nonce'],$post_id) && $photo && wp_verify_nonce( $_POST['_cp_recipe_image_nonce'], '_cp_recipe_image_upload' )) {				
					
					require_once( ABSPATH . 'wp-admin/includes/image.php' );
					require_once( ABSPATH . 'wp-admin/includes/file.php' );
					require_once( ABSPATH . 'wp-admin/includes/media.php' );
					
					$attachment_id = media_handle_upload( '_cp_recipe_image', $post_id );
					
					if ( is_wp_error( $attachment_id ) ) {
						$error_array[] = __('Error uploading image.','cooked');
					} else {
						update_post_meta($post_id, '_thumbnail_id', $attachment_id);
					}
				}		
	 
				if (count($error_array) == 0){
				
					wp_redirect(get_permalink().'?success');
					exit;
				
				} else {
				
					add_action('cp_recipe_form_notice', 'cp_recipe_form_errors');
					
				}
				
			} else {
				add_action('cp_recipe_form_notice', 'cp_recipe_form_errors');
			}
		}
	}
}
 
add_action('init','cp_edit_recipe_form_submit_post');