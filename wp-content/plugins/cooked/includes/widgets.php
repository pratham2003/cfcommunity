<?php

/* SINGLE RECIPE WIDGET */

add_action('widgets_init', create_function('', 'return register_widget("cooked_single_recipe_widget");'));

class cooked_single_recipe_widget extends WP_Widget {

    function cooked_single_recipe_widget() {
        parent::WP_Widget(false, $name = __('Single Recipe','cooked'));
    }
    
    function form($instance) {	
	
	    $title = (isset($instance['title']) ? esc_attr($instance['title']) : '');
		$selected_recipe_id = (isset($instance['recipe_id']) ? $instance['recipe_id'] : '');
		$hide_image = (isset($instance['hide_image']) ? $instance['hide_image'] : '');
		$hide_rating = (isset($instance['hide_rating']) ? $instance['hide_rating'] : '');
		$hide_desc = (isset($instance['hide_desc']) ? $instance['hide_desc'] : '');
		$hide_author = (isset($instance['hide_author']) ? $instance['hide_author'] : '');
		$hide_time = (isset($instance['hide_time']) ? $instance['hide_time'] : '');
		
		// Recipes
		$args = array(
			'post_type' => 'cp_recipe',
			'posts_per_page' => -1,
			'meta_query' => array(
				array(
					'key' => '_cp_private_recipe',
					'compare' => 'NOT EXISTS'
				)
			)
		);
		$recipe_array = array();
		$recipes = get_posts($args);
		foreach($recipes as $recipe):
			$recipe_array[$recipe->ID] = $recipe->post_title;
		endforeach;
		
		asort($recipe_array);
	
	    ?>
	
		 <p>
	      	<label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Widget Title','cooked'); ?>:</label>
	      	<input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" />
	    </p>
	
	    <p>
			<label for="<?php echo $this->get_field_id('recipe_id'); ?>"><?php _e('Choose a Recipe'); ?>:</label>
			<select name="<?php echo $this->get_field_name('recipe_id'); ?>" id="<?php echo $this->get_field_id('recipe_id'); ?>" class="widefat">
				<?php
				foreach ($recipe_array as $recipe_id => $recipe) {
					echo '<option value="' . $recipe_id . '" id="' . $recipe_id . '"', $selected_recipe_id == $recipe_id ? ' selected="selected"' : '', '>', $recipe, '</option>';
				}
				?>
			</select>
		</p>
		
		<p><label for="<?php echo $this->get_field_id('recipe_id'); ?>"><?php _e('Display Options'); ?>:</label></p>
		
		<p><input id="<?php echo $this->get_field_id('hide_image'); ?>" name="<?php echo $this->get_field_name('hide_image'); ?>" type="checkbox" value="1" <?php checked( '1', $hide_image ); ?>/>
	  	<label for="<?php echo $this->get_field_id('hide_image'); ?>"><?php _e('Hide the image'); ?></label></p>
	  	
	  	<p><input id="<?php echo $this->get_field_id('hide_rating'); ?>" name="<?php echo $this->get_field_name('hide_rating'); ?>" type="checkbox" value="1" <?php checked( '1', $hide_rating ); ?>/>
	  	<label for="<?php echo $this->get_field_id('hide_rating'); ?>"><?php _e('Hide the rating'); ?></label></p>
	  	
	  	<p><input id="<?php echo $this->get_field_id('hide_desc'); ?>" name="<?php echo $this->get_field_name('hide_desc'); ?>" type="checkbox" value="1" <?php checked( '1', $hide_desc ); ?>/>
	  	<label for="<?php echo $this->get_field_id('hide_desc'); ?>"><?php _e('Hide the description'); ?></label></p>
	  	
	  	<p><input id="<?php echo $this->get_field_id('hide_author'); ?>" name="<?php echo $this->get_field_name('hide_author'); ?>" type="checkbox" value="1" <?php checked( '1', $hide_author ); ?>/>
	  	<label for="<?php echo $this->get_field_id('hide_author'); ?>"><?php _e('Hide the author'); ?></label></p>
	  	
	  	<p><input id="<?php echo $this->get_field_id('hide_time'); ?>" name="<?php echo $this->get_field_name('hide_time'); ?>" type="checkbox" value="1" <?php checked( '1', $hide_time ); ?>/>
	  	<label for="<?php echo $this->get_field_id('hide_time'); ?>"><?php _e('Hide the cooking times'); ?></label></p>
	
	    <?php
	}

    function widget($args, $instance) {
        
        extract( $args );

		// these are our widget options
	    $title = apply_filters('widget_title', $instance['title']);
		$recipe_id = $instance['recipe_id'];
		$hide_image = $instance['hide_image'];
		$hide_rating = $instance['hide_rating'];
		$hide_desc = $instance['hide_desc'];
		$hide_author = $instance['hide_author'];
		$hide_time = $instance['hide_time'];
	
	    echo $before_widget;
	
		if ( $title ) {
			echo $before_title . $title . $after_title;
		}
		
		$recipe_info = cp_recipe_info_settings();
		$entry_id = $recipe_id;
		$entry_link = get_permalink($entry_id);
		$entry_image = get_post_meta($entry_id, '_thumbnail_id', true);
		$entry_title = get_the_title($entry_id);
		$entry_rating = cp_recipe_rating($entry_id);
		$entry_description = get_post_meta($entry_id, '_cp_recipe_short_description', true);
		$prep_time = get_post_meta($entry_id, '_cp_recipe_prep_time', true);
		$private_recipe = get_post_meta($entry_id, '_cp_private_recipe', true);
		$cook_time = get_post_meta($entry_id, '_cp_recipe_cook_time', true);
		$total_time = $prep_time + $cook_time;
		$entry_yields = get_post_meta($entry_id, '_cp_recipe_yields', true); ?>
		
		<div class="cooked-widget">
			<div class="result-box item">
				<div class="cp-box">
					
					<?php if (!$private_recipe): ?>
					
						<?php if (!$hide_image): ?>
						<div class="cp-box-img">
							<?php if(!empty($entry_image)) {
								echo '<a href="'.$entry_link.'">'.wp_get_attachment_image($entry_image, 'cp_298_192').'</a>';
							} ?>
						</div><!-- /.cp-box-img -->
						<?php endif; ?>
						<div class="cp-box-entry">
							<h2><a href="<?php echo $entry_link; ?>"><?php echo $entry_title; ?></a><?php
								if (in_array('difficulty_level', $recipe_info)) :
									$difficulty_level = get_post_meta($entry_id, '_cp_recipe_difficulty_level', true);
									cp_difficulty_level($difficulty_level);
								endif;
							?></h2>
							<?php if (in_array('rating', $recipe_info) && !$hide_rating) : ?><div class="rating rate-<?php echo $entry_rating; ?>"></div><!-- /.rating --><?php endif; ?>
							<?php if (in_array('description', $recipe_info) && !$hide_desc) : echo wpautop($entry_description); endif; ?>
							<?php if (in_array('author', $recipe_info) && !$hide_author) :
			
								echo '<p class="terms-list">';
								
								$nickname = get_the_author_meta('nickname');
								$username = get_the_author_meta('user_login');
								if (!$nickname) { $nickname = $username; }
								$username = cp_create_slug($username);
								
								$profile_page_link = (get_option('cp_profile_page') ? get_permalink(get_option('cp_profile_page')) : false);
								$profile_page_link = rtrim($profile_page_link, '/');
								
								if ($profile_page_link):
							
									echo '<span><i class="fa fa-user"></i>&nbsp;&nbsp;' . __('By','cooked') . ' <a href="' . $profile_page_link . '/' . $username . '/">' . $nickname.'</a></span>';
								
								endif;
								
								echo '</p>';
								
							endif; ?>
						</div><!-- /.cp-box-entry -->
						<?php if (!$hide_time): ?>
							<?php if ($entry_yields || $total_time): if (in_array('timing', $recipe_info) || in_array('yields', $recipe_info)) : ?>
							<div class="cp-box-footer">
								<div class="timing">
									<ul>
										<?php if (in_array('timing', $recipe_info) && $total_time) : ?>
											<li><?php _e('Prep Time','cooked'); ?>: <strong><?php echo cp_format_time($prep_time); ?></strong></li>
											<li><?php _e('Total Time','cooked'); ?>: <strong><?php echo cp_format_time($total_time); ?></strong></li>
										<?php endif; ?>
										<?php if (in_array('yields', $recipe_info) && $entry_yields) : ?><li><?php echo $entry_yields; ?></li><?php endif; ?>
									</ul>
								</div><!-- /.timing -->
							</div><!-- /.cp-box-footer -->
							<?php endif; endif; ?>
						<?php endif; ?>
						
					<?php else: ?>
					
						<p style="color:#888;">This recipe is private.</p>
					
					<?php endif; ?>
					
				</div><!-- /.cp-box -->
			</div>
		</div><!-- /.result-box --><?php
	    
	    echo $after_widget;
	
	}
	
    function update($new_instance, $old_instance) {
		$instance = $old_instance;
		$instance['title'] = strip_tags($new_instance['title']);
		$instance['recipe_id'] = $new_instance['recipe_id'];
		$instance['hide_image'] = $new_instance['hide_image'];
		$instance['hide_rating'] = $new_instance['hide_rating'];
		$instance['hide_desc'] = $new_instance['hide_desc'];
		$instance['hide_author'] = $new_instance['hide_author'];
		$instance['hide_time'] = $new_instance['hide_time'];
		return $instance;
    }

}


/* RECIPES LIST WIDGET */

add_action('widgets_init', create_function('', 'return register_widget("cooked_recipe_list_widget");'));

class cooked_recipe_list_widget extends WP_Widget {

    function cooked_recipe_list_widget() {
        parent::WP_Widget(false, $name = __('Recipe List','cooked'));
    }
    
    function form($instance) {	
	
	    $title = (isset($instance['title']) ? esc_attr($instance['title']) : '');
		$selected_list_type = (isset($instance['list_type']) ? $instance['list_type'] : '');
		$hide_rating = (isset($instance['hide_rating']) ? $instance['hide_rating'] : '');
		$hide_author = (isset($instance['hide_author']) ? $instance['hide_author'] : '');
		$count = (isset($instance['count']) ? $instance['count'] : 10);
	
	    ?>
	
		 <p>
	      	<label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Widget Title','cooked'); ?>:</label>
	      	<input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" />
	    </p>
	
	    <p>
			<label for="<?php echo $this->get_field_id('list_type'); ?>"><?php _e('List Type'); ?>:</label>
			<select name="<?php echo $this->get_field_name('list_type'); ?>" id="<?php echo $this->get_field_id('list_type'); ?>" class="widefat">
				<?php echo '<option value="rating_desc" id="rating_desc"', $selected_list_type == 'rating_desc' ? ' selected="selected"' : '', '>'.__('Top Rated','cooked').'</option>'; ?>
				<?php echo '<option value="date_desc" id="date_desc"', $selected_list_type == 'date_desc' ? ' selected="selected"' : '', '>'.__('Newest Recipes','cooked').'</option>'; ?>
				<?php echo '<option value="title_asc" id="title_asc"', $selected_list_type == 'title_asc' ? ' selected="selected"' : '', '>'.__('Alphabetical','cooked').'</option>'; ?>
			</select>
		</p>
		
		<p>
			<label for="<?php echo $this->get_field_id('count'); ?>"><?php _e('How many to display?'); ?>:</label>
			<select name="<?php echo $this->get_field_name('count'); ?>" id="<?php echo $this->get_field_id('count'); ?>" class="widefat">
				<?php echo '<option value="1" id="count_1"', $count == 1 ? ' selected="selected"' : '', '>1</option>'; ?>
				<?php echo '<option value="2" id="count_2"', $count == 2 ? ' selected="selected"' : '', '>2</option>'; ?>
				<?php echo '<option value="3" id="count_3"', $count == 3 ? ' selected="selected"' : '', '>3</option>'; ?>
				<?php echo '<option value="4" id="count_4"', $count == 4 ? ' selected="selected"' : '', '>4</option>'; ?>
				<?php echo '<option value="5" id="count_5"', $count == 5 ? ' selected="selected"' : '', '>5</option>'; ?>
				<?php echo '<option value="6" id="count_6"', $count == 6 ? ' selected="selected"' : '', '>6</option>'; ?>
				<?php echo '<option value="7" id="count_7"', $count == 7 ? ' selected="selected"' : '', '>7</option>'; ?>
				<?php echo '<option value="8" id="count_8"', $count == 8 ? ' selected="selected"' : '', '>8</option>'; ?>
				<?php echo '<option value="9" id="count_9"', $count == 9 ? ' selected="selected"' : '', '>9</option>'; ?>
				<?php echo '<option value="10" id="count_10"', $count == 10 ? ' selected="selected"' : '', '>10</option>'; ?>
				<?php echo '<option value="15" id="count_15"', $count == 15 ? ' selected="selected"' : '', '>15</option>'; ?>
				<?php echo '<option value="20" id="count_20"', $count == 20 ? ' selected="selected"' : '', '>20</option>'; ?>
			</select>
		</p>
		
		<p><label for="<?php echo $this->get_field_id('recipe_id'); ?>"><?php _e('Display Options'); ?>:</label></p>
	  	
	  	<p><input id="<?php echo $this->get_field_id('hide_rating'); ?>" name="<?php echo $this->get_field_name('hide_rating'); ?>" type="checkbox" value="1" <?php checked( '1', $hide_rating ); ?>/>
	  	<label for="<?php echo $this->get_field_id('hide_rating'); ?>"><?php _e('Hide the rating'); ?></label></p>
	  	
	  	<p><input id="<?php echo $this->get_field_id('hide_author'); ?>" name="<?php echo $this->get_field_name('hide_author'); ?>" type="checkbox" value="1" <?php checked( '1', $hide_author ); ?>/>
	  	<label for="<?php echo $this->get_field_id('hide_author'); ?>"><?php _e('Hide the author'); ?></label></p>
	
	    <?php
	}

    function widget($args, $instance) {
        
        extract( $args );

		// these are our widget options
	    $title = apply_filters('widget_title', $instance['title']);
		$list_type = $instance['list_type'];
		$hide_rating = $instance['hide_rating'];
		$hide_author = $instance['hide_author'];
		$count = $instance['count'];
	
	    echo $before_widget;
	
		if ( $title ) {
			echo $before_title . $title . $after_title;
		}
		
		$args = cp_widget_list_query($list_type,$count);
		
		$recipe_query = new WP_Query($args);
		if ($recipe_query->have_posts()):
		
			echo '<div class="cooked-widget">';
			
			while($recipe_query->have_posts()) :
		
				$recipe_query->the_post();
				$entry_id = $recipe_query->post->ID;
				$entry_rating = cp_recipe_rating($entry_id);
				?><div class="recipe-list-item">
				
					<p class="recipe-title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></p><?php
					
					if (!$hide_author):
						echo '<p class="recipe-author">';
							$nickname = get_the_author_meta('nickname');
							$username = get_the_author_meta('user_login');
							if (!$nickname) { $nickname = $username; }
							$username = cp_create_slug($username);
							
							$profile_page_link = (get_option('cp_profile_page') ? get_permalink(get_option('cp_profile_page')) : false);
							$profile_page_link = rtrim($profile_page_link, '/');
							
							if ($profile_page_link):
								echo '<span><i class="fa fa-user"></i>&nbsp;&nbsp;' . __('By','cooked') . ' <a href="' . $profile_page_link . '/' . $username . '/">' . $nickname.'</a></span>';
							endif;
						echo '</p>';
					endif;
	
					if (!$hide_rating):
						?><div class="tiny-rating rate-<?php echo $entry_rating ?>"></div><?php
					endif;
					
				?></div><?php
	    
			endwhile;
	    	
	    	echo '</div>';
	    
	    endif;
	    
	    echo $after_widget;
	
	}
	
    function update($new_instance, $old_instance) {
		$instance = $old_instance;
		$instance['title'] = strip_tags($new_instance['title']);
		$instance['list_type'] = $new_instance['list_type'];
		$instance['hide_rating'] = $new_instance['hide_rating'];
		$instance['hide_author'] = $new_instance['hide_author'];
		$instance['count'] = $new_instance['count'];
		return $instance;
    }

}


/* RECIPES SEARCH WIDGET */

add_action('widgets_init', create_function('', 'return register_widget("cooked_recipe_search_widget");'));

class cooked_recipe_search_widget extends WP_Widget {

    function cooked_recipe_search_widget() {
        parent::WP_Widget(false, $name = __('Recipe Search','cooked'));
    }
    
    function form($instance) {	
	
	    $title = (isset($instance['title']) ? esc_attr($instance['title']) : '');
	
		?><p>
	      	<label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Widget Title','cooked'); ?>:</label>
	      	<input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" />
	    </p><?php
	    
	}

    function widget($args, $instance) {
        
        extract( $args );

		// these are our widget options
	    $title = apply_filters('widget_title', $instance['title']);
	
	    echo $before_widget;
	
		if ( $title ) {
			echo $before_title . $title . $after_title;
		}
		
		echo '<div class="cooked-widget">';
			
			echo do_shortcode('[cooked-search style="stacked"]');
	    	
	    echo '</div>';
	    
	    echo $after_widget;
	
	}
	
    function update($new_instance, $old_instance) {
		$instance = $old_instance;
		$instance['title'] = strip_tags($new_instance['title']);
		return $instance;
    }

}