<?php

// This template only shows up if you are logged in or if you have a username after the /profile/ in the url.

global $custom_query,$custom_recipe_title,$custom_type,$error,$post;

$my_profile = false;
$my_id = false;

if (get_query_var('profile')):
	$profile_username = get_query_var('profile');
	if (is_user_logged_in()):
		global $current_user;
		get_currentuserinfo();
		if ($current_user->user_login == $profile_username):
			$my_id = $current_user->ID;
			$my_profile = true;
		endif;
	endif;
else :
	global $current_user;
	get_currentuserinfo();
	$profile_username = $current_user->user_login;
	$my_id = $current_user->ID;
	$my_profile = true;
endif;

$user_data = get_user_by( 'login', $profile_username );

?><div id="cooked-profile-page"<?php if ($my_profile): ?> class="me"<?php endif; ?>><?php

if (empty($user_data)) {

	echo '<h2>' . __('No profile here!','cooked') . '</h2>';
	echo '<p>' . __('Sorry, this user profile does not exist.','cooked') . '</p>';

} else { ?>

	<div class="cp-profile-header cookedClearFix">

		<?php
			
			$avatar = cp_avatar($user_data->ID,150);
			
			if ($avatar):
			
				?><div class="cp-avatar">
					<?php echo $avatar; ?>
				</div><?php
			
			endif;
		
			$recipe_actions = cp_recipe_action_settings();
			$fes_settings = cp_recipe_fes_settings();
			$reviews_comments = get_option('cp_reviews_comments');
		
			$user_meta = get_user_meta($user_data->ID);
			$user_url = $user_data->data->user_url;
			$user_desc = $user_meta['description'][0];
			$h3_class = '';
			
			if (in_array('fes_enabled', $fes_settings)) :
			
				// ********************
				// My Recipes
				// ********************
				
				if (is_user_logged_in() && $my_profile){
									
					$profile_recipes = array(
						'post_type' => 'cp_recipe',
						'post_status' => 'any',
						'posts_per_page' => -1,
						'orderby' => 'date',
						'order' => 'desc',
						'author' => $user_data->ID
					);
				
				} else {
					
					$profile_recipes = array(
						'post_type' => 'cp_recipe',
						'posts_per_page' => -1,
						'orderby' => 'date',
						'order' => 'desc',
						'author' => $user_data->ID,
						'meta_query' => array(
							array(
								'key' => '_cp_private_recipe',
								'compare' => 'NOT EXISTS'
							)
						)
					);
					
				}
				
				$recipe_query = new WP_Query($profile_recipes);
				$total_recipes = $recipe_query->found_posts;
				
				if (!$user_url && !$user_desc){ $h3_class = 'title-only'; }
				if ($user_url && !$user_desc){ $h3_class = 'url-only'; }
				
			endif;
			
			
			if (in_array('favorite_button', $recipe_actions)) :
			
				// ********************
				// My Favorites
				// ********************
				
				$user_likes = get_user_meta($user_data->ID, 'cp_likes',true);
				
				if (!empty($user_likes)):
					foreach($user_likes as $like_post):
						$user_likes_list[] = $like_post;
					endforeach;
					
					// Remove Private Recipes
					foreach($user_likes_list as $rkey => $rid){
						$author_id = get_post_field( 'post_author', $rid );
						if (get_post_meta($rid,'_cp_private_recipe',true)):
							if ($author_id != $my_id):
								unset($user_likes_list[$rkey]);
							endif;
						endif;
					}
					
					$user_likes_count = count($user_likes_list);
					
					$profile_favorites = array(
						'post_type' => 'cp_recipe',
						'posts_per_page' => -1,
						'orderby' => 'date',
						'order' => 'desc',
						'post__in' => $user_likes_list
					);
					
				else :
					$user_likes_list = array();
					$profile_favorites = array();
					$user_likes_count = 0;
				endif;

			endif;
			
			
			// ********************
			// My Reviews
			// ********************
			
			$args = array(
            	'order' => 'desc',
                'post_status' => 'publish',
                'post_type' => 'cp_recipe',
                'status' => 'approve',
                'user_id' => $user_data->ID,
			);
		
			$user_reviews = get_comments($args);
			
			// Remove Private Recipes
			foreach($user_reviews as $rkey => $rid){
				if (get_post_meta($rid->ID,'_cp_private_recipe',true)):
					if ($rid->post_author != $my_id):
						unset($user_reviews[$rkey]);
					endif;
				endif;
			}
			
			$user_review_count = count($user_reviews);
			
		?>
		
		<div class="cp-info"<?php if (!$avatar): ?> style="padding-left:0;"<?php endif; ?>>
			<div class="cp-user">
				<h3 class="<?php echo $h3_class; ?>"><?php echo get_user_meta( $user_data->ID, 'nickname', true ); ?></h3>
				<?php if ($user_url){ echo '<p><a href="'.$user_url.'" target="_blank">'.$user_url.'</a></p>'; } ?>
				<?php if ($user_desc){ echo wpautop($user_desc); } ?>
				<?php if ($my_profile): ?>
					<a class="cp-logout-button" href="<?php echo wp_logout_url(get_permalink($post->ID).'/'.$profile_username.'/'); ?>" title="<?php _e('Logout','cooked'); ?>"><?php _e('Logout','cooked'); ?></a>
				<?php endif; ?>
			</div>
		</div>

	</div>
	
	
	
	<ul class="cp-tabs cookedClearFix">
		<?php if (CP_WOOCOMMERCE_ACTIVE && is_user_logged_in() && $my_profile): ?><li><a href="#wcaccount"><i class="fa fa-shopping-cart"></i> <?php _e('My Account','cooked'); ?></a></li><?php endif; ?>
		<?php if (in_array('fes_enabled', $fes_settings)) : ?><li><a href="#recipes"><i class="fa fa-cutlery"></i><?php echo number_format($total_recipes); ?> <?php _e('Recipes','cooked'); ?></a></li><?php endif; ?>
		<?php if ($reviews_comments != 'admin_reviews_only') : ?><li><a href="#reviews"><i class="fa fa-star"></i><?php echo number_format($user_review_count); ?> <?php _e('Reviews','cooked'); ?></a></li><?php endif; ?>
		<?php if (in_array('favorite_button', $recipe_actions)) : ?><li><a href="#favorites"><i class="fa fa-heart"></i><?php echo number_format($user_likes_count); ?> <?php _e('Favorites','cooked'); ?></a></li><?php endif; ?>
		<?php if ( is_user_logged_in() && $my_profile ) : ?><li class="edit-button"><a href="#edit"><i class="fa fa-edit"></i><?php _e('Edit Profile','cooked'); ?></a></li><?php endif; ?>
	</ul>
	
	
	
	<?php if (CP_WOOCOMMERCE_ACTIVE && is_user_logged_in() && $my_profile): ?>
	<div id="profile-wcaccount" class="cp-tab-content">
		<div id="cooked-plugin-page">
			<div id="cooked-page-form">
			<?php echo do_shortcode('[woocommerce_my_account]'); ?>
			</div>
		</div>
	</div>
	<?php endif; ?>
	
	

	<?php if (in_array('fes_enabled', $fes_settings)) : ?>
	<div id="profile-recipes" class="cp-tab-content">
	
		<?php if ($total_recipes):
			
			$custom_recipe_title = __('My Recipes','cooked');
			$custom_query = $profile_recipes;
			$custom_type = 'recipes';
			load_template(CP_PLUGIN_VIEWS_DIR . 'compact_list.php',false);
				
		else : ?>
		
			<div id="cooked-plugin-page">
				<div class="result-section table-layout">
					<div class="table-box">
						<div class="result-box item">
							<p style="padding-top:15px;"><?php _e('No recipes found.','cooked'); ?></p>
						</div><!-- /.result-box -->
					</div>
				</div>
			</div>
		
		<?php endif; ?>
	
	</div>
	<?php endif; ?>
	
	
	
	<?php if ($reviews_comments != 'admin_reviews_only') : ?>
	<div id="profile-reviews" class="cp-tab-content">
	
		<div id="cooked-plugin-page">
			<div class="result-section table-layout">
				<div class="table-box">
	
					<?php if(!empty($user_reviews)) :  ?>
							
						<div class="table-row table-head-row">
							<div class="table-cell cell-title reviews"><?php _e('My Reviews','cooked'); ?></div>
						</div>
						
						<div class="table-body"><?php
			
						foreach($user_reviews as $review):
			
							$comment_id = $review->comment_ID;
							$comment = $review->comment_content;
							$post_id = $review->comment_post_ID;
							$entry_image = get_post_meta($post_id, '_thumbnail_id', true);
							$private_recipe = get_post_meta($post_id, '_cp_private_recipe', true);
							$comment_date = date(get_option('date_format'),strtotime($review->comment_date));
							$review_rating = get_comment_meta($comment_id, 'review_rating', true );
				
							?><div class="table-row item">
								<div class="table-cell cell-title reviews">
									<?php if(!empty($entry_image)):
										echo '<a href="'.get_permalink($post_id).'" class="compact-img">'.wp_get_attachment_image($entry_image, 'thumbnail').'</a>';
									endif; ?>
									<div class="cell-title-wrap">
										<a href="<?php echo get_permalink($post_id); ?>" class="cp-title"><?php echo get_the_title($post_id); ?></a><?php
										if ($private_recipe): ?><br><span class="cp-private-tag"><?php _e('Private','cooked'); ?></span><?php endif; ?>
										<small><?php _e('Review Posted on ','cooked'); echo $comment_date; ?></small>
										<?php if ($reviews_comments != 'admin_reviews_comments') : ?>
										<div class="rating rate-<?php echo $review_rating; ?>"></div>
										<?php endif; ?>
										<div class="cp-review"><?php echo wpautop($comment); ?></div>
									</div>
								</div>
							</div><?php
				
						endforeach;
							
						?></div>
				
					<?php else : ?>
					
						<div class="result-box item">
							<p style="padding-top:15px;"><?php _e('No reviews found.','cooked'); ?></p>
						</div><!-- /.result-box -->
						
					<?php endif; ?>
		
				</div>
			</div>
		</div>
	
	</div>
	<?php endif; ?>
	
	
	
	<?php if (in_array('favorite_button', $recipe_actions)) : ?>
	<div id="profile-favorites" class="cp-tab-content">
		
		<?php if (!empty($profile_favorites)):
			
			$custom_recipe_title = __('My Favorites','cooked');
			$custom_query = $profile_favorites;
			$custom_type = 'favorites';
			load_template(CP_PLUGIN_VIEWS_DIR . 'compact_list.php',false);
				
		else : ?>
		
			<div id="cooked-plugin-page">
				<div class="result-section table-layout">
					<div class="table-box">
						<div class="result-box item">
							<p style="padding-top:15px;"><?php _e('No favorites found.','cooked'); ?></p>
						</div><!-- /.result-box -->
					</div>
				</div>
			</div>
		
		<?php endif; ?>
	
	</div>
	<?php endif; ?>
	
	
	
	<?php if ( is_user_logged_in() && $my_profile ) : ?>
		<div id="profile-edit" class="cp-tab-content">
        <form method="post" enctype="multipart/form-data" id="cooked-page-form" action="<?php the_permalink(); ?>">
        	
        	<div class="cookedClearFix">
	            <p class="form-avatar">	
	                <label for="avatar"><?php _e('Update Avatar', 'cooked'); ?><?php if (CP_DEMO_MODE): ?> <span class="not-bold"><?php _e('(disabled in demo)', 'cooked'); ?></span><?php endif; ?></label><br>
	                <span class="cp-upload-wrap"><span><?php _e('Choose image ...','cooked'); ?></span><input<?php if (CP_DEMO_MODE): ?> disabled<?php endif; ?> class="field" name="avatar" type="file" id="avatar" value="" /></span>
	                <?php wp_nonce_field( 'avatar_upload', 'avatar_nonce' ); ?>
	                <span class="hint-p"><?php _e('Recommended size: 100px by 100px or larger', 'cooked'); ?></span>
	            </p><!-- .form-nickname -->
        	</div>
        	
            <div class="cookedClearFix">
	            <p class="form-nickname">
	                <label for="nickname"><?php _e('Display Name', 'cooked'); ?><?php if (CP_DEMO_MODE): ?> <span class="not-bold"><?php _e('(disabled in demo)', 'cooked'); ?></span><?php endif; ?></label>
	                <input<?php if (CP_DEMO_MODE): ?> disabled<?php endif; ?>  class="text-input" name="nickname" type="text" id="nickname" value="<?php the_author_meta( 'nickname', $current_user->ID ); ?>" />
	            </p><!-- .form-nickname -->
	            <p class="form-email">
	                <label for="email"><?php _e('E-mail *', 'cooked'); ?><?php if (CP_DEMO_MODE): ?> <span class="not-bold"><?php _e('(disabled in demo)', 'cooked'); ?></span><?php endif; ?></label>
	                <input<?php if (CP_DEMO_MODE): ?> disabled<?php endif; ?> class="text-input" name="email" type="text" id="email" value="<?php the_author_meta( 'user_email', $current_user->ID ); ?>" />
	            </p><!-- .form-email -->
	            <p class="form-url">
	                <label for="url"><?php _e('Website', 'cooked'); ?><?php if (CP_DEMO_MODE): ?> <span class="not-bold"><?php _e('(disabled in demo)', 'cooked'); ?></span><?php endif; ?></label>
	                <input<?php if (CP_DEMO_MODE): ?> disabled<?php endif; ?>  class="text-input" name="url" type="text" id="url" value="<?php the_author_meta( 'user_url', $current_user->ID ); ?>" />
	            </p><!-- .form-url -->
            </div>
            <div class="cookedClearFix">
	            <p class="form-password">
	                <label for="pass1"><?php _e('Change Password', 'cooked'); ?><?php if (CP_DEMO_MODE): ?> <span class="not-bold"><?php _e('(disabled in demo)', 'cooked'); ?></span><?php endif; ?></label>
	                <input<?php if (CP_DEMO_MODE): ?> disabled<?php endif; ?> class="text-input" name="pass1" type="password" id="pass1" />
	            </p><!-- .form-password -->
	            <p class="form-password">
	                <label for="pass2"><?php _e('Repeat Password', 'cooked'); ?><?php if (CP_DEMO_MODE): ?> <span class="not-bold"><?php _e('(disabled in demo)', 'cooked'); ?></span><?php endif; ?></label>
	                <input<?php if (CP_DEMO_MODE): ?> disabled<?php endif; ?> class="text-input" name="pass2" type="password" id="pass2" />
	            </p><!-- .form-password -->
            </div>
            <p class="form-textarea">
                <label for="description"><?php _e('Short Bio', 'cooked') ?><?php if (CP_DEMO_MODE): ?> <span class="not-bold"><?php _e('(disabled in demo)', 'cooked'); ?></span><?php endif; ?></label>
                <textarea<?php if (CP_DEMO_MODE): ?> disabled<?php endif; ?>  name="description" id="description" rows="3" cols="50"><?php the_author_meta( 'description', $current_user->ID ); ?></textarea>
            </p><!-- .form-textarea -->

            <?php 
                //action hook for plugin and extra fields
                do_action('edit_user_profile',$current_user); 
            ?>
            <p class="form-submit">
                <input name="updateuser" type="submit" id="updateuser" class="submit button" value="<?php _e('Update', 'cooked'); ?>" />
                <?php wp_nonce_field( 'update-user' ) ?>
                <input name="action" type="hidden" id="action" value="update-user" />
            </p><!-- .form-submit -->
        </form><!-- #adduser -->
		</div>
    <?php endif; ?>
	
	

<?php } ?>
	
</div>