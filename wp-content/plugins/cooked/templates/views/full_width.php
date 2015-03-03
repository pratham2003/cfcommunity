<?php $is_page = is_page(); $no_results = false; ?>
<div id="cooked-plugin-page">

	<?php if($is_page) : ?>
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
		
	endif; ?>
	<div class="result-section full-width-box-layout">
		<div class="loading-content">
			<?php if(have_posts() && !$no_results) :
			
				$recipe_info = cp_recipe_info_settings();

				while(have_posts()) : the_post();

					$entry_id = get_the_ID();
					$entry_link = get_permalink($entry_id);
					$entry_image = get_post_meta($entry_id, '_thumbnail_id', true);
					$entry_title = get_the_title($entry_id);
					$entry_rating = cp_recipe_rating($entry_id);
					$entry_description = get_post_meta($entry_id, '_cp_recipe_short_description', true);
					$entry_excerpt = get_post_meta($entry_id, '_cp_recipe_excerpt', true);
					$prep_time = get_post_meta($entry_id, '_cp_recipe_prep_time', true);
					$cook_time = get_post_meta($entry_id, '_cp_recipe_cook_time', true);
					$total_time = $prep_time + $cook_time;
					$entry_yields = get_post_meta($entry_id, '_cp_recipe_yields', true); ?>
					
					<div class="result-box item">
						<div class="cp-box">
							<div class="cp-box-img">
								<?php if(!empty($entry_image)) {
									echo '<a href="'.$entry_link.'">'.wp_get_attachment_image($entry_image, 'cp_431_424').'</a>';
								} else {
									echo '<a href="'.$entry_link.'"><img src="'.CP_PLUGIN_URL.'/css/images/default_431_424.png"></a>';
								}
								?>
							</div><!-- /.cp-box-img -->
							<div class="cp-box-info">
								<h2><a href="<?php echo $entry_link; ?>"><?php echo $entry_title; ?></a><?php
									if (in_array('difficulty_level', $recipe_info)) :
										$difficulty_level = get_post_meta($entry_id, '_cp_recipe_difficulty_level', true);
										cp_difficulty_level($difficulty_level);
									endif;
								?></h2>
								<?php if (in_array('rating', $recipe_info)) : ?><div class="rating rate-<?php echo $entry_rating; ?>"></div><!-- /.rating --><?php endif; ?>
								<?php if (in_array('description', $recipe_info)) :
									if ($entry_excerpt):
										echo wpautop($entry_excerpt);
									else :
										echo wpautop($entry_description);
									endif;
								endif; ?>
								
								<?php if (in_array('author', $recipe_info)) :

									echo '<p class="terms-list">';
									
									$author_id = get_the_author_meta('ID');
									$nickname = get_the_author_meta('nickname');
									$username = get_the_author_meta('user_login');
									if (!$nickname) { $nickname = $username; }
									$username = cp_create_slug($username);
									
									$avatar_image = false;
									if (in_array('author_avatar', $recipe_info)) :
										$avatar_image = cp_avatar($author_id,50);
									endif;
									
									$profile_page_link = (get_option('cp_profile_page') ? get_permalink(get_option('cp_profile_page')) : false);
									$profile_page_link = rtrim($profile_page_link, '/');
									
									if ($profile_page_link):
								
										echo '<span>'.($avatar_image ? $avatar_image : '<i class="fa fa-user"></i>&nbsp;&nbsp;') . __('By','cooked') . ' <a href="' . $profile_page_link . '/' . $username . '/">' . $nickname.'</a></span>';
									
									endif;
									
									echo '</p>';
									
								endif; ?>
								
								<?php if ($entry_yields || $total_time): if (in_array('timing', $recipe_info) || in_array('yields', $recipe_info)) : ?>
								<div class="timing">
									<ul>
										<?php if (in_array('timing', $recipe_info) && $total_time) : ?>
											<li><strong><?php _e('Prep','cooked'); ?>:</strong> <?php echo cp_format_time($prep_time); ?></li>
											<li><strong><?php _e('Cook','cooked'); ?>:</strong> <?php echo cp_format_time($cook_time); ?></li>
										<?php endif; ?>
										<?php if (in_array('yields', $recipe_info) && $entry_yields) : ?><li><strong><?php _e('Yields','cooked'); ?>:</strong> <?php echo $entry_yields; ?></li><?php endif; ?>
									</ul>
								</div><!-- /.timing -->
								<?php endif; endif; ?>
							</div><!-- /.cp-box-info -->
						</div><!-- /.cp-box -->
					</div><!-- /.result-box -->
				<?php endwhile;

			else : ?>
				<div class="result-box item">
					<p><?php _e('No recipes found.','cooked'); ?></p>
				</div><!-- /.result-box -->
			<?php endif; ?>
		</div><!-- /.loading-content -->
	</div><!-- /.result-section -->
	<?php cp_pagination();

	if($is_page) {
		wp_reset_query();
	} ?>
</div><!-- /#cooked-plugin-page -->