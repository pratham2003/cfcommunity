<?php global $custom_query, $custom_recipe_title, $custom_type; $is_page = is_page(); $no_results = false; ?>
<div id="cooked-plugin-page">

	<?php
	
	// For everything except archive templates
	if ($is_page && !$custom_query): ?>
	
		<div class="search-section">
			<?php cp_recipe_search_section(); ?>
		</div><!-- /.search-section -->
		<?php 
	
		$args = cp_search_args();
		
		if ($args):
			query_posts($args);
		else :
			$no_results = true;
		endif;
		
	endif;
	
	// For the profile pages, etc.
	if ($custom_query && !empty($custom_query)):
	
		query_posts($custom_query);
		
	endif;
	
	$recipe_info = cp_recipe_info_settings();
	
	$cooked_plugin = new cooked_plugin();
	$enabled_taxonomies = $cooked_plugin->cp_recipe_tax_settings();
	
	$percent_increase = 0;
	if (!in_array('category',$enabled_taxonomies)): $percent_increase = $percent_increase + 12; endif;
	if (!in_array('cuisine',$enabled_taxonomies)): $percent_increase = $percent_increase + 12; endif;
	if (!in_array('method',$enabled_taxonomies)): $percent_increase = $percent_increase + 12; endif;
	$final_title_percent = 38 + $percent_increase;
	?>
	
	<div class="result-section table-layout">
		<div class="table-box">
			<?php if(have_posts() && !$no_results) : ?>
				<div class="table-row table-head-row">
					<div class="table-cell cell-title"<?php if (!$custom_query): ?> style="width:<?php echo $final_title_percent; ?>%;"<?php endif; ?>><?php if ($custom_recipe_title): echo $custom_recipe_title; else: _e('Recipe','cooked'); endif; ?></div>
					<div class="table-cell cell-time"><?php _e('Time','cooked'); ?></div>
					<?php if (!$custom_query): ?>
						<?php if (in_array('category',$enabled_taxonomies)): ?><div class="table-cell cell-category"><?php _e('Category','cooked'); ?></div><?php endif; ?>
						<?php if (in_array('cuisine',$enabled_taxonomies)): ?><div class="table-cell cell-cuisine"><?php _e('Cuisine','cooked'); ?></div><?php endif; ?>
						<?php if (in_array('method',$enabled_taxonomies)): ?><div class="table-cell cell-method"><?php _e('Method','cooked'); ?></div><?php endif; ?>
					<?php endif; ?>
					<div class="table-cell cell-rating"><?php _e('Rating','cooked'); ?></div>
				</div><!-- /.table-row -->

				<div class="table-body loading-content">
					<?php $category_taxonomy = 'cp_recipe_category';
					$cuisine_taxonomy = 'cp_recipe_cuisine';
					$cooking_method_taxonomy = 'cp_recipe_cooking_method';

					while(have_posts()) : the_post();

						$entry_id = get_the_ID();
						$entry_link = get_permalink($entry_id);
						$entry_title = get_the_title($entry_id);
						$entry_rating = cp_recipe_rating($entry_id);
						$entry_image = get_post_meta($entry_id, '_thumbnail_id', true);
						$prep_time = get_post_meta($entry_id, '_cp_recipe_prep_time', true);
						$private_recipe = get_post_meta($entry_id, '_cp_private_recipe', true);
						$cook_time = get_post_meta($entry_id, '_cp_recipe_cook_time', true);
						$post_status = get_post_status($entry_id);
						$total_time = $prep_time + $cook_time;
						$category_name = '';
						$cuisine_name = '';
						$cooking_method_name = '';

						if (in_array('category',$enabled_taxonomies)):
							$categories = wp_get_post_terms($entry_id, $category_taxonomy);
							if(!is_wp_error($categories)) {
								foreach ($categories as $part):
									$list[] = $part->name;
								endforeach;
								if (!empty($list)):
									$category_name = implode(', ',$list);
								else :
									$category_name = '';
								endif;
							} else {
								$category_name = '';
							}
							
							$list = array();
						endif;

						if (in_array('cuisine',$enabled_taxonomies)):
							$cuisines = wp_get_post_terms($entry_id, $cuisine_taxonomy);
							if(!is_wp_error($cuisines)) {
								foreach ($cuisines as $part):
									$list[] = $part->name;
								endforeach;
								$cuisine_name = implode(', ',$list);
							} else {
								$cuisine_name = '';
							}
							
							$list = array();
						endif;

						if (in_array('method',$enabled_taxonomies)):
							$cooking_methods = wp_get_post_terms($entry_id, $cooking_method_taxonomy);
							if(!is_wp_error($cooking_methods)) {
								foreach ($cooking_methods as $part):
									$list[] = $part->name;
								endforeach;
								$cooking_method_name = implode(', ',$list);
								
							} else {
								$cooking_method_name = '';
							}
							
							$list = array();
						endif; ?>
						
						<div class="table-row item">
							<div class="table-cell cell-title"<?php if (!$custom_query): ?> style="width:<?php echo $final_title_percent; ?>%;"<?php endif; ?>>
								<?php if(!empty($entry_image) && $custom_query):
									echo '<a href="'.$entry_link.'" class="compact-img">'.wp_get_attachment_image($entry_image, 'thumbnail').'</a>';
								elseif(empty($entry_image) && $custom_query):
									echo '<a href="'.$entry_link.'" class="compact-img"><img src="'.CP_PLUGIN_URL.'/css/images/default_thumbnail.png"></a>';
								endif; ?>
								<div class="cell-title-wrap">
								
									<?php if ($post_status == 'publish'):			
										?><a href="<?php echo $entry_link; ?>"><?php echo $entry_title; ?></a><?php
									else :
										?><span class="recipe-title-under-review"><?php echo $entry_title; ?></span><?php
									endif;
									
									if (in_array('difficulty_level', $recipe_info)) :
										$difficulty_level = get_post_meta($entry_id, '_cp_recipe_difficulty_level', true);
										cp_difficulty_level($difficulty_level);
									endif;
									
									if ($private_recipe): ?><br><span class="cp-private-tag"><?php _e('Private','cooked'); ?></span><?php endif;
									if ($post_status != 'publish'): ?><br><span class="cp-draft-tag"><?php _e('In Review','cooked'); ?></span><?php endif;
									
									if (cp_are_actions_premium() && is_user_logged_in() || !cp_are_actions_premium()):
									
										if ($custom_query && isset($custom_type) && $custom_type == 'favorites'):
							
											$recipe_actions = cp_recipe_action_settings();
											if (in_array('favorite_button', $recipe_actions)) :
					
												if (is_user_logged_in()):
													$user_ID = get_current_user_id();
													$user_likes = get_user_meta($user_ID, 'cp_likes',true);
												endif;
												
												?><p style="padding:5px 0 0; font-size: 15px;"><a class="like-btn" href="<?php echo get_permalink(); ?>" data-cookied="<?php echo number_format(!is_user_logged_in()); ?>" data-userLiked="<?php if (is_user_logged_in() && in_array(get_the_id(),$user_likes)): ?>1<?php else : ?>0<?php endif; ?>" data-recipe-id="<?php echo get_the_id(); ?>">
													<?php
														$likes = get_post_meta( get_the_id(), '_cp_likes', true );
													?>
													<span class="like-count"><?php echo $likes ? $likes : 0; ?></span>
													<i class="fa fa-heart-o"></i>
												</a></p>
											<?php endif;
										
										endif;
									
									endif;
									
									?>
									<?php if ($custom_query): if ($category_name || $cuisine_name || $cooking_method_name): echo '<small>'; endif; ?>
										<?php if ($category_name): echo __('Category','cooked') . ': <em>' . $category_name . '</em><br />'; endif; ?>
										<?php if ($cuisine_name): echo __('Cuisine','cooked') . ': <em>' . $cuisine_name . '</em><br />'; endif; ?>
										<?php if ($cooking_method_name): echo __('Method','cooked') . ': <em>' . $cooking_method_name . '</em><br />'; endif; ?>
									<?php if ($category_name || $cuisine_name || $cooking_method_name): echo '</small>'; endif; endif; ?>
								</div>
							</div>
							<div class="table-cell cell-time"><?php echo cp_format_time($total_time); ?></div>
							<?php if (!$custom_query): ?>
								<?php if (in_array('category',$enabled_taxonomies)): ?><div class="table-cell cell-category"><?php echo $category_name; ?></div><?php endif; ?>
								<?php if (in_array('cuisine',$enabled_taxonomies)): ?><div class="table-cell cell-cuisine"><?php echo $cuisine_name; ?></div><?php endif; ?>
								<?php if (in_array('method',$enabled_taxonomies)): ?><div class="table-cell cell-method"><?php echo $cooking_method_name; ?></div><?php endif; ?>
							<?php endif; ?>
							<div class="table-cell cell-rating"><div class="rating rate-<?php echo $entry_rating ?>"></div></div>
						</div><!-- /.table-row -->
					<?php endwhile; ?>
				</div><!-- /.loading-content -->
			<?php else : ?>
				<div class="result-box item">
					<p><?php _e('No recipes found.','cooked'); ?></p>
				</div><!-- /.result-box -->
			<?php endif; ?>
		</div><!-- /.table-box -->
	</div><!-- /.result-section -->
	<?php cp_pagination();

	if($is_page || $custom_query) {
		wp_reset_query();
	} ?>
	
	<style type="text/css">
		@media screen and (max-width: <?php echo get_option('cp_responsive_break_two'); ?>px){
			#cooked-plugin-page .result-section.table-layout .table-box .table-body .table-row .table-cell.cell-title:before { content: '<?php _e('Recipe','cooked'); ?>'; }
			#cooked-plugin-page .result-section.table-layout .table-box .table-body .table-row .table-cell.cell-time:before { content: '<?php _e('Time','cooked'); ?>'; }
			#cooked-plugin-page .result-section.table-layout .table-box .table-body .table-row .table-cell.cell-category:before { content: '<?php _e('Category','cooked'); ?>'; }
			#cooked-plugin-page .result-section.table-layout .table-box .table-body .table-row .table-cell.cell-cuisine:before { content: '<?php _e('Cuisine','cooked'); ?>'; }
			#cooked-plugin-page .result-section.table-layout .table-box .table-body .table-row .table-cell.cell-method:before { content: '<?php _e('Method','cooked'); ?>'; }
			#cooked-plugin-page .result-section.table-layout .table-box .table-body .table-row .table-cell.cell-rating:before { content: '<?php _e('Rating','cooked'); ?>'; }
		}
	</style>
	
	
</div><!-- /#cooked-plugin-page -->