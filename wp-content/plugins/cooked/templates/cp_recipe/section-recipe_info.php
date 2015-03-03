<?php global $post_id; if (!$post_id): $post_id = get_the_ID(); endif;

$recipe_info = cp_recipe_info_settings();
$cooked_plugin = new cooked_plugin();
$enabled_taxonomies = $cooked_plugin->cp_recipe_tax_settings();
$terms_list = array();
	
// Get the Category & Taxonomies
if (in_array('category',$enabled_taxonomies)):
	if (in_array('category', $recipe_info)) : $terms_list[] = get_the_term_list( $post_id, 'cp_recipe_category', '<span><i class="fa fa-book"></i>&nbsp;&nbsp;', ', ', '</span>' ); endif;
endif;
if (in_array('cuisine',$enabled_taxonomies)):
	if (in_array('cuisine', $recipe_info)) : $terms_list[] = get_the_term_list( $post_id, 'cp_recipe_cuisine', '<span><i class="fa fa-flag"></i>&nbsp;&nbsp;', ', ', '</span>' ); endif;
endif;
if (in_array('method',$enabled_taxonomies)):
	if (in_array('method', $recipe_info)) : $terms_list[] = get_the_term_list( $post_id, 'cp_recipe_cooking_method', '<span><i class="fa fa-cutlery"></i>&nbsp;&nbsp;', ', ', '</span>' ); endif;
endif;

?><h2 class="fn"><?php
	
	echo get_the_title($post_id);
	
	if (in_array('difficulty_level', $recipe_info)) :
		$difficulty_level = get_post_meta($post_id, '_cp_recipe_difficulty_level', true);
		cp_difficulty_level($difficulty_level);
	endif;

?></h2>

<?php if (!empty($terms_list) || in_array('author', $recipe_info)) :

	echo '<p class="terms-list">';
	
	if (in_array('author', $recipe_info)) :

		global $post; 
		$author_id = $post->post_author;
		$nickname = get_the_author_meta('nickname',$author_id);
		$username = get_the_author_meta('user_login',$author_id);
		if (!$nickname) { $nickname = $username; }
		$username = cp_create_slug($username);
		
		$profile_page_link = (get_option('cp_profile_page') ? get_permalink(get_option('cp_profile_page')) : false);
		$profile_page_link = rtrim($profile_page_link, '/');
		
		$avatar_image = false;
		if (in_array('author_avatar', $recipe_info)) :
			$avatar_image = cp_avatar($author_id,50);
		endif;
		
		if ($profile_page_link):
	
			echo '<span>'.($avatar_image ? $avatar_image : '<i class="fa fa-user"></i>&nbsp;&nbsp;') . __('By','cooked') . ' <a href="' . $profile_page_link . '/' . $username . '/">' . $nickname.'</a></span>';
		
		endif;
	
	endif;
	
	if (!empty($terms_list)):
		
		echo implode('',$terms_list);
	
	endif;
	
	echo '</p>';
	
endif; ?>

<p class="published"><?php the_time( 'F j, Y' ); ?><span class="value-title" title="<?php the_time( 'Y-m-d' ); ?>"></span></p>
<?php

if (in_array('rating', $recipe_info)) :

	$recipe_rating = cp_recipe_rating($post_id); ?>
	<div class="review hreview-aggregate">
		<div class="rating rate-<?php echo $recipe_rating; ?>">
			<span class="average"><?php echo $recipe_rating; ?>.0</span>
		    <span class="count"><?php echo cp_recipe_rating($post_id,true); ?></span>
		</div><!-- /.rating -->
	</div>

<?php endif; ?>

<?php if (in_array('description', $recipe_info)) :

	if($recipe_short_description = get_post_meta($post_id, '_cp_recipe_short_description', true)) : ?>
		<div class="info-entry summary">
			<?php echo wpautop($recipe_short_description); ?>
		</div><!-- /.entry -->
	<?php endif;

endif;

$prep_time = get_post_meta($post_id, '_cp_recipe_prep_time', true);
$cook_time = get_post_meta($post_id, '_cp_recipe_cook_time', true);
$total_time = $cook_time + $prep_time;
$yields = get_post_meta($post_id, '_cp_recipe_yields', true);

$recipe_sharing_networks = get_option('cp_sharing_options');
$recipe_link = get_permalink($post_id);

$recipe_actions = cp_recipe_action_settings();

if (in_array('timing', $recipe_info) || in_array('yields', $recipe_info)):
	if ($prep_time || $cook_time || $yields): ?>
	
		<div class="timing">
			<ul>
				<?php if (in_array('timing', $recipe_info) && $total_time) : ?>
					<?php if($prep_time) : ?><li><strong><?php _e('Prep','cooked'); ?>:</strong> <?php echo cp_format_time($prep_time); ?></li><?php endif; ?>
					<?php if($cook_time) : ?><li><strong><?php _e('Cook','cooked'); ?>:</strong> <?php echo cp_format_time($cook_time); ?></li><?php endif; ?>
					<?php if ($prep_time || $cook_time || $yields): ?>
						<li class="microformat-time">
							<?php if($prep_time) : ?><p class="preptime"><?php echo cp_format_time($prep_time); ?><span class="value-title" title="PT<?php echo $prep_time; ?>M"></span></p><?php endif; ?>
							<?php if($cook_time) : ?><p class="cooktime"><?php echo cp_format_time($cook_time); ?><span class="value-title" title="PT<?php echo $cook_time; ?>M"></span></p><?php endif; ?>
							<?php if($total_time) : ?><p class="duration"><?php echo cp_format_time($total_time); ?><span class="value-title" title="PT<?php echo $total_time; ?>M"></span></p><?php endif; ?>
						</li>
					<?php endif; ?>
				<?php endif; ?>
				<?php if (in_array('yields', $recipe_info) && $yields) : ?>
					<li><strong><?php _e('Yields','cooked'); ?>:</strong> <?php echo $yields; ?></li>
				<?php endif; ?>
			</ul>
		</div><!-- /.timing --><?php
	
	endif;
endif; ?>

<div class="recipe-action">
	<?php if(!empty($recipe_sharing_networks)) : ?>
		<a class="btn btn-share" href="#" data-networks="<?php echo implode(' ', $recipe_sharing_networks); ?>" data-share_url="<?php echo $recipe_link; ?>"><i class="fa fa-share-square-o"></i> <span><?php _e('Share','cooked'); ?></span></a>
	<?php endif;

	if (cp_are_actions_premium() && is_user_logged_in() || !cp_are_actions_premium()):

		if (in_array('favorite_button', $recipe_actions)) :
		
			if (is_user_logged_in()):
				$user_ID = get_current_user_id();
				$user_likes = get_user_meta($user_ID, 'cp_likes',true);
			endif;
			
			?><a class="like-btn" href="<?php echo get_permalink(); ?>" data-cookied="<?php echo number_format(!is_user_logged_in()); ?>" data-userLiked="<?php if (is_user_logged_in() && in_array($post_id,$user_likes)): ?>1<?php else : ?>0<?php endif; ?>" data-recipe-id="<?php echo $post_id; ?>">
				<?php
					$likes = get_post_meta( $post_id, '_cp_likes', true );
				?>
				<span class="like-count"><?php echo $likes ? $likes : 0; ?></span>
				<i class="fa fa-heart-o"></i>
			</a>
		<?php endif;
	
		if(in_array('print_button', $recipe_actions)) : ?>
			<a class="print-btn" href="<?php echo get_permalink($post_id); ?>?print" target="_blank"><i class="fa fa-print"></i></a>
		<?php endif;
	
		if(in_array('full_screen_button', $recipe_actions)) : ?>
			<a class="fs-btn" href="#">
				<span class="fa-stack fa-lg">
				  <i class="fa fa-square fa-stack-2x"></i>
				  <i class="fa fa-expand fa-stack-1x fa-inverse"></i>
				</span>
				<span class="fa-btn-text"><?php _e('Full-screen','cooked'); ?></span>
			</a>
		<?php endif;
		
	endif; ?>
	
</div><!-- /.recipe-action -->