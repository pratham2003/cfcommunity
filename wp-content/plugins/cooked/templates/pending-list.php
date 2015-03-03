<div class="cooked-settings-wrap wrap">
	
	<div class="topSavingState savingState"><i class="fa fa-refresh fa-spin"></i>&nbsp;&nbsp;<?php _e('Updating, please wait...','cooked'); ?></div>
	<div class="cooked-settings-title"><?php _e('Pending Recipes','cooked'); ?></div>
	<div id="data-ajax-url"><?php echo get_admin_url(); ?></div>
	
	<?php
	
	echo '<div class="cooked-pending-headings cookedClearFix">';
		echo '<div class="left">'.__('Recipe Details','cooked').'</div>';
		echo '<div class="right">'.__('Options','cooked').'</div>';
	echo '</div>';
	
	echo '<div class="cooked-pending-recipe-list">';
	
		/*
		Set some variables
		*/
		
		$time_format = get_option('time_format');
		$date_format = get_option('date_format');
		
		/*
		Grab all of the recipes for this day
		*/
		
		$args = array(
			'post_type' => 'cp_recipe',
			'posts_per_page' => -1,
			'post_status' => 'draft',
			'orderby' => 'date',
			'order' => 'DESC'
		);
		
		$recipes_array = array();
		
		$cookedRecipes = new WP_Query($args);
		if($cookedRecipes->have_posts()):
			while ($cookedRecipes->have_posts()):
				
				$cookedRecipes->the_post();
				global $post;

				$recipes_array[$post->ID]['post_id'] = $post->ID;
				$recipes_array[$post->ID]['user'] = $post->post_author;
			
			endwhile;
		endif;
		
		echo '<div class="pending-recipe'.(!empty($recipes_array) ? ' no-pending-message' : '').'">';
			echo '<p style="text-align:center;">'.__('There are no pending recipes.','cooked').'</p>';
		echo '</div>';
		
		/*
		Let's loop through the pending recipes
		*/
			
		foreach($recipes_array as $recipe):
		
			echo '<div class="pending-recipe cookedClearFix" data-recipe-id="'.$recipe['post_id'].'">';
				
				$user_info = get_userdata($recipe['user']);
				
				if (has_post_thumbnail( $recipe['post_id'] )) :
					$image_url = wp_get_attachment_image_src( get_post_thumbnail_id( $recipe['post_id'] ), 'thumbnail' );
					if (is_array($image_url)) { $image_url = $image_url[0]; }
				endif;
				
				echo '<span class="recipe-block" data-recipe-id="'.$recipe['post_id'].'">';
					echo '<a href="#" class="delete">&times;</a>';
					echo '<button data-recipe-id="'.$recipe['post_id'].'" class="approve button button-primary">'.__('Approve','cooked').'</button>';
					
					if ( isset($image_url) && $image_url ) {
		                echo '<a href="'.get_the_permalink($recipe['post_id']).'" target="_blank"><img src="'.$image_url.'" /></a>';
		            }
					
					echo '<a href="'.get_the_permalink($recipe['post_id']).'" target="_blank">'.get_the_title($recipe['post_id']).'</a><br>';
					
					if (isset($user_info->ID)):
						echo '<span class="user">by '.$user_info->display_name.'</span>';
					else :
						_e('(this user no longer exists)','cooked');
					endif;
					
				echo '</span>';
			
			echo '</div>';
			
		endforeach;
			
	echo '</div>';
	
	?>
	
</div>