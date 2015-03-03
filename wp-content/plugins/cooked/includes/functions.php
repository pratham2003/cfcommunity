<?php	
	
function cp_format_time($time) {
	$hours = floor($time / 60);
	$minutes = $time % 60;

	$time_string = '';

	if($hours != 0) {
		if($hours == 1) {
			$time_string .= $hours . ' '.__('hr','cooked').' ';
		} else {
			$time_string .= $hours . ' '.__('hrs','cooked').' ';
		}
	}

	if($minutes != 0) {
		$time_string .= $minutes . ' '.__('min','cooked');
	}


	return $time_string;
}

function cp_pending_recipes_count(){
	$args = array(
	   'posts_per_page' => -1,
	   'post_status' => 'draft',
	   'post_type' => 'cp_recipe',
	);
	$pending_count_query = new WP_Query($args);
	return $pending_count_query->found_posts;
}

function cooked_mailer($to,$subject,$message){

	add_filter('wp_mail_content_type', 'cooked_set_html_content_type');
	
	$cooked_email_logo = get_option('cooked_email_logo');
	if ($cooked_email_logo):
		$logo = '<img src="'.$cooked_email_logo.'" style="max-width:100%; height:auto; display:block; margin:10px 0 20px;">';
	else :
		$logo = '';	
	endif;
	
	$template = file_get_contents('email-templates/default.html', true);
	$filter = array('%content%','%logo%');
	$replace = array(wpautop($message),$logo);	
	$message = str_replace($filter, $replace, $template);
	
	wp_mail($to,$subject,$message);
	
	remove_filter('wp_mail_content_type','cooked_set_html_content_type');
	
}

function cooked_set_html_content_type() {
	return 'text/html';
}

function cp_difficulty_level($l){
	switch($l):
	
		case 1:
			echo '<span title="'.__('Difficulty Level: Easy','cooked').'" class="difficulty easy"></span>';
		break;
		
		case 2:
			echo '<span title="'.__('Difficulty Level: Intermediate','cooked').'" class="difficulty intermediate"></span>';
		break;
		
		case 3:
			echo '<span title="'.__('Difficulty Level: Advanced','cooked').'" class="difficulty advanced"></span>';
		break;
	
	endswitch;
}

function cp_create_slug($string,$not_username = false){
	if ($not_username):
		$slug = preg_replace('/[^A-Za-z0-9-]+/', '-', strtolower($string));
	else :
		$slug = preg_replace('/[^A-Za-z0-9-._]+/', '-', strtolower($string));
	endif;
	return $slug;
}

function yoast_change_opengraph_type( $type ) {
	if ( is_singular( 'cp_recipe' ) ) {
		return 'recipe';
	}
}
add_filter( 'wpseo_opengraph_type', 'yoast_change_opengraph_type', 10, 1 );

add_action( 'wp_login_failed', 'cp_fe_login_fail' );  // hook failed login

function cp_fe_login_fail( $username ) {
   $referrer = $_SERVER['HTTP_REFERER'];
   $referrer = explode('?',$referrer);
   $referrer = $referrer[0];
   if ( !empty($referrer) && !strstr($referrer,'wp-login') && !strstr($referrer,'wp-admin') ) {
      wp_redirect( $referrer . '?loginfailed' );
      exit;
   }
}

function cp_recipe_image($recipe_image, $size, $container_css, $recipe_video, $small_css = false){
	
	if(!empty($recipe_image)) :
	
		?><div class="<?php echo $container_css; ?>"><?php	
			echo wp_get_attachment_image($recipe_image, $size, null, array('class' => 'fullscreen-img photo'));
			if ($recipe_video): echo '<a href="#cooked-video-lb" class="fancy-video'.$small_css.'"><i class="fa fa-play-circle"></i></a>'; endif;
		?></div><!-- /.recipe-main-img --><?php
		
	else :
	
		$size = explode('cp_',$size);
		$size = $size[1];
	
		?><div class="<?php echo $container_css; ?>"><?php	
			echo '<img src="'.CP_PLUGIN_URL.'/css/images/default_'.$size.'.png" class="fullscreen-img photo">';
			if ($recipe_video): echo '<a href="#cooked-video-lb" class="fancy-video'.$small_css.'"><i class="fa fa-play-circle"></i></a>'; endif;
		?></div><!-- /.recipe-main-img --><?php
	
	endif;
	
}

function cp_taxonomy_dropdown( $taxonomy,$empty_name,$current_values ) {
	$terms = get_terms( $taxonomy, array('hide_empty' => false));
	if ( $terms ) {
		printf( '<select multiple data-placeholder="'.$empty_name.'" name="%s[]" class="postform">', esc_attr( $taxonomy ) );
		foreach ( $terms as $term ) {
			printf( '<option value="%s"'.($current_values && in_array($term->term_id,$current_values) ? ' selected' : '').'>%s</option>', esc_attr( $term->term_id ), esc_html( $term->name ) );
		}
		print( '</select>' );
	}
}

function cp_difficulty_dropdown( $variable, $empty_name, $current_value ) {

	printf( '<select name="%s" class="postform">', esc_attr( $variable ) );
		printf( '<option value="">%s</option>', $empty_name );
		print( '<option value="1"'.($current_value == 1 ? 'selected' : '').'>'.__('Beginner','cooked').'</option>' );
		print( '<option value="2"'.($current_value == 2 ? 'selected' : '').'>'.__('Intermediate','cooked').'</option>' );
		print( '<option value="3"'.($current_value == 3 ? 'selected' : '').'>'.__('Advanced','cooked').'</option>' );
	print( '</select>' );

}

function cp_user_recipe_total($user_id){
	
	$args = array(
		'post_type' => 'cp_recipe',
		'posts_per_page' => -1,
		'post_status' => 'any',
		'author' => $user_id
	);
	
	$userRecipes = new WP_Query($args);
	return $userRecipes->found_posts;

}

function cp_format_content($content, $section = 'ingredients',$detailed = false) {
	
	if ($detailed):
		foreach($content as $i):
		
			if ($i['type'] == 'section'):
				
				// Section Title
				?><p class="em-cat"><?php echo $i['value']; ?></p><?php
					
			else :
			
				$entry_class = 'product-entry instruction';
				
				if ( $section == 'ingredients') {
					
					$amount = $i['amount'];
					$measurement = $i['measurement'];
					$name = $i['name'];
					$entry_class = 'product-entry ingredient';
					$content = '<span class="amount">' . cp_calculate_amount($amount,'fraction') . ' '. $measurement .'</span> <span class="name">' . $name . '</span>';
					?><p class="<?php echo $entry_class; ?>"><a href="#" class="hint-check"><i class="fa fa-check"></i></a><?php echo do_shortcode($content); ?></p><?php
						
				} else {
					
					$image_id = $i['image_id'];
					$value = $i['value'];
					?><p class="<?php echo $entry_class; ?>"><a href="#" class="hint-check"><i class="fa fa-check"></i></a><?php echo do_shortcode($value); ?></p><?php
					
					if ($image_id):
						$direction_image = wp_get_attachment_image( $image_id, 'cp_500_500' );
						echo '<p class="direction-image">'.$direction_image.'</p>';
					endif;
					
				}

			endif;
			
		endforeach;
		
	else :
	
		$lines = explode("\n", $content);
		foreach($lines as $content) :
			if(strpos($content, '--') === 0) : ?>
				<p class="em-cat"><?php echo substr($content, 2); ?></p>
			<?php else :
				$entry_class = 'product-entry instruction';
	
				if ( $section == 'ingredients') {
					$entry_class   = 'product-entry ingredient';
					$content_array = explode("  ", $content);
	
					if ( count( $content_array ) > 1 ) {
						$content = '<span class="amount">' . $content_array[0] . '</span> <span class="name">' . $content_array[1] . '</span>';
					} else {
						$content = '<span class="name">' . $content_array[0] . '</span>';
					}
				}
				?>
				<p class="<?php echo $entry_class; ?>"><a href="#" class="hint-check"><i class="fa fa-check"></i></a><?php echo do_shortcode($content); ?></p>
			<?php endif;
		endforeach;
		
	endif;
}

function cp_avatar($user_id,$size = 150){
	if (get_user_meta($user_id, 'avatar',true)):
		return wp_get_attachment_image( get_user_meta($user_id,'avatar',true), array($size,$size) );
	else :
		return get_avatar($user_id, $size);
	endif;
}

function cp_recipe_action_settings() {
	$recipe_actions_value = get_option('cp_action_options');
	return $recipe_actions_value != '' ? $recipe_actions_value : array();
}

function cp_are_actions_premium() {
	$recipe_actions_value = get_option('cp_premium_actions');
	if (is_array($recipe_actions_value) && in_array('active',$recipe_actions_value)):
		return true;
	else :
		return false;
	endif;
}

function cp_recipe_info_settings() {
	$recipe_actions_value = get_option('cp_info_options');
	return $recipe_actions_value != '' ? $recipe_actions_value : array();
}

function cp_advanced_editable_taxes_settings() {
	$editable_taxes_value = get_option('cp_advanced_editable_taxes');
	return $editable_taxes_value != '' ? $editable_taxes_value : array();
}

function cp_recipe_fes_settings() {
	$recipe_fes_value = get_option('cp_fes_options');
	return $recipe_fes_value != '' ? $recipe_fes_value : array();
}

function cp_recipe_section($section_name) {
	require(CP_PLUGIN_SECTIONS_DIR . 'section-'. $section_name . '.php');
}

function cp_recipe_search_section($browse_page = false) {
	
	global $manual_category, $manual_cuisine, $manual_cooking_method, $manual_sort;
	
	if (isset($manual_category) && $manual_category && !isset($_GET['category'])):
	
		$tmp_value = get_term_by( 'slug', $manual_category, 'cp_recipe_category' );
		if (empty($tmp_value)):
			$tmp_value = get_term_by( 'id', $manual_category, 'cp_recipe_category' );
		endif;
		if (empty($tmp_value)):
			$tmp_value = get_term_by( 'name', $manual_category, 'cp_recipe_category' );
		endif;
		if (!empty($tmp_value)):
			$term_id = $tmp_value->term_id;
			$_GET['category'] = $term_id;
		endif;
		
	endif;
		
	if (isset($manual_cuisine) && $manual_cuisine && !isset($_GET['cuisine'])):
	
		$tmp_value = get_term_by( 'slug', $manual_cuisine, 'cp_recipe_cuisine' );
		if (empty($tmp_value)):
			$tmp_value = get_term_by( 'id', $manual_cuisine, 'cp_recipe_cuisine' );
		endif;
		if (empty($tmp_value)):
			$tmp_value = get_term_by( 'name', $manual_cuisine, 'cp_recipe_cuisine' );
		endif;
		if (!empty($tmp_value)):
			$term_id = $tmp_value->term_id;
			$_GET['cuisine'] = $term_id;
		endif;
	
	endif;
	
	if (isset($manual_cooking_method) && $manual_cooking_method && !isset($_GET['cooking_method'])):
		
		$tmp_value = get_term_by( 'slug', $manual_cooking_method, 'cp_recipe_cooking_method' );
		if (empty($tmp_value)):
			$tmp_value = get_term_by( 'id', $manual_cooking_method, 'cp_recipe_cooking_method' );
		endif;
		if (empty($tmp_value)):
			$tmp_value = get_term_by( 'name', $manual_cooking_method, 'cp_recipe_cooking_method' );
		endif;
		if (!empty($tmp_value)):
			$term_id = $tmp_value->term_id;
			$_GET['cooking_method'] = $term_id;
		endif;
	
	endif;
	
	if (isset($manual_sort) && $manual_sort && !isset($_GET['sort'])):
	
		if ($manual_sort == 'title_desc' || $manual_sort == 'title_asc' || $manual_sort == 'date_desc' || $manual_sort == 'date_asc' || $manual_sort == 'rating_desc' || $manual_sort == 'rating_asc'):
			$_GET['sort'] = $manual_sort;
		endif;
	
	endif;

	$cooked_plugin = new cooked_plugin();
	$enabled_taxonomies = $cooked_plugin->cp_recipe_tax_settings();
	$select_count = 1;
	
	if (in_array('category',$enabled_taxonomies)): $select_count++; endif;
	if (in_array('cuisine',$enabled_taxonomies)): $select_count++; endif;
	if (in_array('method',$enabled_taxonomies)): $select_count++; endif;
	
	?><div class="search-wrap">
		<form method="get"<?php if ($browse_page): ?> action="<?php echo get_permalink($browse_page); ?>"<?php endif; ?>>
			<div class="select-row clearfix select-count-<?php echo $select_count; ?>">
				
				<?php if (in_array('category',$enabled_taxonomies)): ?>
					<div class="select-box">
						<?php $selected_category = !empty($_GET['category']) ? $_GET['category'] : false;
						$taxonomy = 'cp_recipe_category';
						$args = array(
							'orderby' => 'term_order',
							'order' => 'ASC'
						);
	
						$terms = get_terms($taxonomy, $args); ?>
						<select name="category" data-placeholder="<?php _e('All Recipe Categories','cooked'); ?>">
							<option value=""><?php _e('All Recipe Categories','cooked'); ?></option>
							<?php if(!is_wp_error($terms)) :
								foreach($terms as $term) :
									$term_id = $term->term_id; ?>
									<option value="<?php echo $term_id; ?>"<?php echo $selected_category == $term_id ? ' selected="selected"' : ''; ?>><?php echo $term->name; ?></option>
								<?php endforeach;
							endif; ?>
						</select>
					</div><!-- /.select-box -->
				<?php endif; ?>

				<?php if (in_array('cuisine',$enabled_taxonomies)): ?>
					<div class="select-box">
						<?php $selected_cuisine = !empty($_GET['cuisine']) ? $_GET['cuisine'] : false;
						$taxonomy = 'cp_recipe_cuisine';
						$args = array(
							'orderby' => 'term_order',
							'order' => 'ASC'
						);
	
						$terms = get_terms($taxonomy, $args); ?>
						<select name="cuisine" data-placeholder="<?php _e('All Recipe Cuisines','cooked'); ?>">
							<option value=""><?php _e('All Recipe Cuisines','cooked'); ?></option>
							<?php if(!is_wp_error($terms)) :
								foreach($terms as $term) :
									$term_id = $term->term_id; ?>
									<option value="<?php echo $term_id; ?>"<?php echo $selected_cuisine == $term_id ? ' selected="selected"' : ''; ?>><?php echo $term->name; ?></option>
								<?php endforeach;
							endif; ?>
						</select>
					</div><!-- /.select-box -->
				<?php endif; ?>

				<?php if (in_array('method',$enabled_taxonomies)): ?>
				<div class="select-box">
					<?php $selected_cooking_method = !empty($_GET['cooking_method']) ? $_GET['cooking_method'] : false;
					$taxonomy = 'cp_recipe_cooking_method';
					$args = array(
						'orderby' => 'term_order',
						'order' => 'ASC'
					);

					$terms = get_terms($taxonomy, $args); ?>
					<select name="cooking_method" data-placeholder="<?php _e('All Recipe Cooking Methods','cooked'); ?>">
						<option value=""><?php _e('All Recipe Cooking Methods','cooked'); ?></option>
						<?php if(!is_wp_error($terms)) :
							foreach($terms as $term) :
								$term_id = $term->term_id; ?>
								<option value="<?php echo $term_id; ?>"<?php echo $selected_cooking_method == $term_id ? ' selected="selected"' : ''; ?>><?php echo $term->name; ?></option>
							<?php endforeach;
						endif; ?>
					</select>
				</div><!-- /.select-box -->
				<?php endif; ?>
				
				<?php $sort = !empty($_GET['sort']) ? $_GET['sort'] : false;
				$sort_options = array(
					'title_desc' => __('Title (descending)','cooked'),
					'title_asc' => __('Title (ascending)','cooked'),
					'date_desc' => __('Date (newest first)','cooked'),
					'date_asc' => __('Date (oldest first)','cooked'),
					'rating_desc' => __('Rating (highest first)','cooked'),
					'rating_asc' => __('Rating (lowest first)','cooked')
				); ?>
				<div class="select-box">
					<select name="sort" data-placeholder="<?php _e('Sort Recipes by...','cooked'); ?>">
						<option value=""><?php _e('Sort Recipes by...','cooked'); ?></option>
						<?php foreach($sort_options as $option_name => $option_label) : ?>
							<option value="<?php echo $option_name; ?>"<?php echo ($sort == $option_name ? ' selected="selected"' : ''); ?>><?php echo $option_label; ?></option>
						<?php endforeach; ?>
					</select>
				</div><!-- /.select-box -->
			</div><!-- /.select-row -->
			<div class="search-row clearfix">
				<div class="field-wrap">
					<?php $rand_search = rand(1111,9999); ?>
					<label for="f1_<?php echo $rand_search; ?>"><?php _e('Search by keyword, ingredients, serving size or description...','cooked'); ?></label>
					<input type="text" name="content-search" id="f1_<?php echo $rand_search; ?>" class="field" value="<?php echo !empty($_GET['content-search']) ? $_GET['content-search'] : ''; ?>" />
				</div><!-- /.field-wrap -->
				<div class="sbmt-button"><input type="submit" value="<?php _e('Search for Recipes','cooked'); ?>" /></div><!-- /.sbmt-button -->
			</div><!-- /.search-row -->
		</form>
	</div><!-- /.search-wrap -->
<?php }

function cp_recipe_rating($post_id,$just_count = false) {
	$rating = 0;
	$reviews_comments = get_option('cp_reviews_comments');
	if($reviews_comments == 'guest_reviews_comments') {
		$post_comments = get_comments(array(
			'post_id' => $post_id,
			'status' => 'approve'
		));
		if(!empty($post_comments)) {
			$total_rating_raw = 0;
			$total_comments = 0;

			foreach($post_comments as $comment) {
				$this_rating = get_comment_meta($comment->comment_ID, 'review_rating', true);
				if ($this_rating) :
					$total_rating_raw += $this_rating;
					$total_comments++;
				endif;
			}
			if ($total_rating_raw > 0):
				$rating = ceil($total_rating_raw / $total_comments);
			else :
				$rating = 0;
			endif;
		}
	} elseif($reviews_comments == 'admin_reviews_comments' || $reviews_comments == 'admin_reviews_only') {
		$rating = get_post_meta($post_id, '_cp_recipe_admin_rating', true);
	}
	
	if ($just_count){ return $total_comments; } else { return $rating; }
	
}

function cp_review_fields($fields) {

	$commenter = wp_get_current_commenter();
	$user_logged_in = is_user_logged_in();
	$req = get_option( 'require_name_email' );
	$aria_req = ( $req ? " aria-required='true'" : '' );

	$fields[ 'author' ] = (!$user_logged_in ? '<div class="fields-holder clearfix">' : '' ) . '<div class="review-form-author review-field-holder field-wrap">'.
		'<label for="author">' . __( 'Your name ...', 'cooked' ) . '</label>'.
		'<input id="author" name="author" type="text" value="'. esc_attr( $commenter['comment_author'] ) .
		'" size="30" tabindex="1"' . $aria_req . ' class="field" /></div>';

	$fields[ 'email' ] = '<div class="review-form-email review-field-holder field-wrap">'.
		'<label for="email">' . __( 'Your email ...', 'cooked' ) . '</label>'.
		'<input id="email" name="email" type="text" value="'. esc_attr( $commenter['comment_author_email'] ) .
		'" size="30"  tabindex="2"' . $aria_req . ' class="field" /></div>';

	$fields['url'] = '';

	return $fields;
}

function cp_rating_fields() {
	$user_logged_in = is_user_logged_in(); ?>
	<?php if($user_logged_in) : ?>
		<div class="fields-holder clearfix">
	<?php endif;

	$reviews_comments = get_option('cp_reviews_comments');
	if($reviews_comments != 'admin_reviews_comments') : ?>
		<div class="review-form-rating review-field-holder">
			<div class="rating-holder">
				<span class="rate"></span>
				<span class="rate"></span>
				<span class="rate"></span>
				<span class="rate"></span>
				<span class="rate"></span>
			</div><!-- /.rating -->
			<input type="hidden" name="rating" value="" class="rating-real-value" />
		</div>
	<?php endif; ?>
	</div>
<?php }

function cp_no_default_comments($file) {
	return CP_PLUGIN_DIR . '/templates/comments.php';
}

function cp_widget_list_query($sort = 'rating_desc',$count = 10){
	
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
	
	switch ($sort) {
		case 'title_asc':
			$args['orderby'] = 'title';
			$args['order'] = 'ASC';
			break;

		case 'date_desc':
			$args['orderby'] = 'date';
			$args['order'] = 'DESC';
			break;

		case 'rating_desc':
			break;
		
		default:
			$args['orderby'] = 'date';
			$args['order'] = 'DESC';
			break;
	}
	
	query_posts($args);
	if(have_posts()) {
		global $wp_query;
		$recipes = cp_sort_widget_recipes($wp_query->posts,$sort);
		$recipes_ids = wp_list_pluck($recipes, 'ID');
		$args = array(
			'post_type' => 'cp_recipe',
			'posts_per_page' => $count,
			'post__in' => $recipes_ids,
			'orderby' => 'post__in',
		);
	}
	wp_reset_query();

	return $args;
}

function cp_search_args($category = null, $cuisine = null, $cooking_method = null, $tag = null) {
	$paged = (get_query_var('paged')) ? get_query_var('paged') : 1;
	$args = array(
		'post_type' => 'cp_recipe',
		'posts_per_page' => -1,
		'paged' => $paged
	);

	if(!empty($_GET['category']) && isset($_GET['category']) || $category) {
		$args['tax_query'][] = array(
			'taxonomy' => 'cp_recipe_category',
			'field' => 'term_id',
			'terms' => ($category ? $category : $_GET['category'])
		);
	}

	if(!empty($_GET['cuisine']) && isset($_GET['cuisine']) || $cuisine) {
		$args['tax_query'][] = array(
			'taxonomy' => 'cp_recipe_cuisine',
			'field' => 'term_id',
			'terms' => ($cuisine ? $cuisine : $_GET['cuisine'])
		);
	}

	if(!empty($_GET['cooking_method']) && isset($_GET['cooking_method']) || $cooking_method) {
		$args['tax_query'][] = array(
			'taxonomy' => 'cp_recipe_cooking_method',
			'field' => 'term_id',
			'terms' => ($cooking_method ? $cooking_method : $_GET['cooking_method'])
		);
	}
	
	if ($tag):
	
		$args['tag'] = $tag;
	
	endif;

	if(!empty($_GET['sort'])) {
		$sort = $_GET['sort'];
		switch ($sort) {
			case 'title_asc':
				$args['orderby'] = 'title';
				$args['order'] = 'ASC';
				break;

			case 'title_desc':
				$args['orderby'] = 'title';
				$args['order'] = 'DESC';
				break;

			case 'date_asc':
				$args['orderby'] = 'date';
				$args['order'] = 'ASC';
				break;

			case 'date_desc':
				$args['orderby'] = 'date';
				$args['order'] = 'DESC';
				break;

			case 'rating_asc':
			case 'rating_desc':
				break;
			
			default:
				$args['orderby'] = 'date';
				$args['order'] = 'DESC';
				break;
		}
	}
	
	if(!empty($args['tax_query'])) {
		if(count($args['tax_query'])) {
			$args['tax_query']['relation'] = 'AND';
		}
	}

	if(!empty($_GET['content-search'])) {
		$args['s'] = $_GET['content-search'];
	}

	query_posts($args);
	if(have_posts()) {
	
		global $wp_query;
		$recipes = cp_sort_recipes($wp_query->posts);
		$recipes_ids = wp_list_pluck($recipes, 'ID');
		
		// Remove Private Recipes
		foreach($recipes_ids as $rkey => $rid){
			if (get_post_meta($rid,'_cp_private_recipe',true)):
				unset($recipes_ids[$rkey]);
			endif;
		}
		
		$paged = (get_query_var('paged')) ? get_query_var('paged') : 1;
		
		if (!empty($recipes_ids)):
	
			$args = array(
				'post_type' => 'cp_recipe',
				'posts_per_page' => get_option('posts_per_page') ? get_option('posts_per_page') : 2,
				'post__in' => $recipes_ids,
				'orderby' => 'post__in',
				'paged' => $paged
			);
			
		else :
		
			return false;
		
		endif;
		
	}
	
	wp_reset_query();

	return $args;
}

function cp_sort_by_rating_desc($a, $b) {
	$a_rating = cp_recipe_rating($a->ID);
	$b_rating = cp_recipe_rating($b->ID);
	
	if ($a_rating == $b_rating) {
		return 0;
	}
	return ($a_rating > $b_rating) ? -1 : 1;
}

function cp_sort_by_rating_asc($a, $b) {
	$a_rating = cp_recipe_rating($a->ID);
	$b_rating = cp_recipe_rating($b->ID);
	
	if ($a_rating == $b_rating) {
		return 0;
	}
	return ($a_rating < $b_rating) ? -1 : 1;
}

function cp_search_where($where) {
	global $wpdb;
	$where .= " OR (" . $wpdb->prefix . "posts.post_title LIKE '%" . esc_sql($_GET['content-search']) . "%' AND " . $wpdb->prefix . "posts.post_type = 'cp_recipe' )";

	return $where;
}

function cp_sort_recipes($posts) {
	if(!empty($_GET['sort']) && ($_GET['sort'] == 'rating_asc' || $_GET['sort'] == 'rating_desc')) {
		if($_GET['sort'] == 'rating_asc') {
			usort($posts, 'cp_sort_by_rating_asc');
		} else {
			usort($posts, 'cp_sort_by_rating_desc');
		}
	}
	return $posts;
}

function cp_sort_widget_recipes($posts,$sort) {
	if(!empty($sort) && $sort == 'rating_desc') {
		usort($posts, 'cp_sort_by_rating_asc');
	}
	return $posts;
}

function cp_pagination() {
	global $wp_query;
	if(have_posts() && $wp_query->max_num_pages > 1) {

		$pagination = get_option('cp_list_view_pagination');
		if($pagination == 'numbered_pagination') {
			
			echo cp_display_next_posts_link(false,'pagination');
			
		} elseif($pagination == 'load_more_button') {
		
			$next_posts_link = get_next_posts_link();
			echo cp_display_next_posts_link($next_posts_link,'load-button');
		
		} else {
		
			$next_posts_link = get_next_posts_link();
			echo cp_display_next_posts_link($next_posts_link,'image');
			
		}

	}

}

function cp_display_next_posts_link($next_posts_link = false,$type){

	global $wp_query;
	$wp_query_saved = $wp_query;
	wp_reset_query();
	$post_id = get_the_id();
	if (!is_page() && !is_single()):
		$http_type = strtolower(substr($_SERVER["SERVER_PROTOCOL"],0,strpos( $_SERVER["SERVER_PROTOCOL"],'/'))).'://';
		$list_view_page_url = $http_type . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];	
	else:
		$list_view_page_url = _get_page_link( $post_id );
	endif;
	
	if ($type != 'pagination'):
	
		$npl = explode('"',$next_posts_link);
		$npl_url = $npl[1];
		
		if ($list_view_page_url && get_option('permalink_structure')):
		
			if (!is_page() && !is_single()):
			
				$list_view_page_url = explode('page',$list_view_page_url);
				$list_view_page_url = $list_view_page_url[0];
				
				$npl_page = explode('page',$npl_url);
				$npl_url = $list_view_page_url . 'page'.$npl_page[1];
			
			else :
		
				$npl_page = explode('page',$npl_url);
				$npl_url = $list_view_page_url . 'page'.$npl_page[1];
				
			endif;
		
		elseif ($list_view_page_url && !get_option('permalink_structure')):
		
			if (!is_page() && !is_single()):
			
				$list_view_page_url = explode('&paged',$list_view_page_url);
				$list_view_page_url = $list_view_page_url[0];
			
				$npl_page = explode('paged',$npl_url);
				$npl_url = $list_view_page_url . '&paged'.$npl_page[1];	
			
			else :
		
				$npl_page = explode('paged',$npl_url);
				$npl_url = $list_view_page_url . '&paged'.$npl_page[1];
		
			endif;
		
		endif;

	endif;
	
	switch ($type) :
	
		case 'image' :
		
			if ($npl_page[1]):
		
				$next_posts_link = '<a href="'.$npl_url.'" class="load-more">image_tag</a>';
				$next_posts_link = str_replace('image_tag', '<img src="' . CP_PLUGIN_URL . '/css/images/ajax-loader.gif" width="32" height="32" alt="" />', $next_posts_link);
				return $next_posts_link;
				
			endif;
		
		break;
		
		case 'load-button' :
		
			if ($npl_page[1]):
			
				$next_posts_link = '<div class="recipes-pagination"><a href="'.$npl_url.'" class="btn load-more-button">Load More</a></div>';
				$next_posts_link = str_replace('image_tag', '<img src="' . CP_PLUGIN_URL . '/css/images/ajax-loader.gif" width="32" height="32" alt="" />', $next_posts_link);
				return $next_posts_link;
				
			endif;
		
		break;
		
		case 'pagination' :
		
			$wp_query = $wp_query_saved;
			
			if ($list_view_page_url && get_option('permalink_structure')):
				
				$format = '?paged=%#%';
			
			elseif ($list_view_page_url && !get_option('permalink_structure')):
			
				$format = '&paged=%#%';
			
			endif;
	
			ob_start();
			
			?><div class="recipes-pagination">
				<?php $big = 999999999;

				echo paginate_links(array(
					'base' => str_replace( $big, '%#%', esc_url( get_pagenum_link( $big ) ) ),
					'base' => $list_view_page_url.'%_%',
					'format' => $format,
					'current' => max( 1, get_query_var('paged') ),
					'total' => $wp_query_saved->max_num_pages,
					'next_text' => 'Next&nbsp;&nbsp;<i class="fa fa-arrow-right"></i>',
					'prev_text' => '<i class="fa fa-arrow-left"></i>&nbsp;&nbsp;Prev',
				)); ?>
			</div><?php
		
			return ob_get_clean();
		
		break;
		
	endswitch;
	
	return false;
	
}

function cp_update_responsive_layouts() {
	$template_file = CP_PLUGIN_DIR . DIRECTORY_SEPARATOR . 'css' . DIRECTORY_SEPARATOR . 'responsive-template.css';
	$css_string = file_get_contents($template_file);
	if($css_string !== false) {
	
		$upload_dir = wp_upload_dir();
		$cooked_upload_dir = $upload_dir['basedir'] . DIRECTORY_SEPARATOR . 'cooked';
		
		if (!is_dir($cooked_upload_dir)) {
			wp_mkdir_p($cooked_upload_dir);
		}
		
		$new_file = $cooked_upload_dir . DIRECTORY_SEPARATOR . 'front-end-responsive.css';
		$breakpoints = array(
			'{breakpoint_1}',
			'{breakpoint_2}',
			'{breakpoint_3}',
		);

		$breakpoint_values = array(
			get_option('cp_responsive_break_one'),
			get_option('cp_responsive_break_two'),
			get_option('cp_responsive_break_three')
		);
		$css_string = str_replace($breakpoints, $breakpoint_values, $css_string);
		file_put_contents($new_file, $css_string);
	} else {
		wp_die('Please make sure that the responsive template css file exists.');
	}
}

function cp_update_color_theme() {
	$template_file = CP_PLUGIN_DIR . DIRECTORY_SEPARATOR . 'css' . DIRECTORY_SEPARATOR . 'color-theme-template.css';
	$css_string = file_get_contents($template_file);
	if($css_string !== false) {
	
		$upload_dir = wp_upload_dir();
		$cooked_upload_dir = $upload_dir['basedir'] . DIRECTORY_SEPARATOR . 'cooked';
		if (!is_dir($cooked_upload_dir)) {
			wp_mkdir_p($cooked_upload_dir);
		}
	
		$new_file = $cooked_upload_dir . DIRECTORY_SEPARATOR . 'color-theme.css';
		$breakpoints = array(
			'{main_color}',
			'{light_color}',
			'{dark_color}',
		);

		$breakpoint_values = array(
			get_option('cp_main_color','#6DC02B'),
			get_option('cp_light_color','#aade80'),
			get_option('cp_dark_color','#3b8400')
		);
		$css_string = str_replace($breakpoints, $breakpoint_values, $css_string);
		file_put_contents($new_file, $css_string);
	} else {
		wp_die('Please make sure that the color theme template css file exists.');
	}
}

function cp_profile_update_submit(){
	
	if (is_user_logged_in()):
	
		global $error,$current_user,$post;

		get_currentuserinfo();
		
		$error = array();    
		
		if ( 'POST' == $_SERVER['REQUEST_METHOD'] && !empty( $_POST['action'] ) && $_POST['action'] == 'update-user' ) {
		
		    /* Update user password. */
		    if ( !empty($_POST['pass1'] ) && !empty( $_POST['pass2'] ) ) {
		        if ( $_POST['pass1'] == $_POST['pass2'] )
		            wp_update_user( array( 'ID' => $current_user->ID, 'user_pass' => esc_attr( $_POST['pass1'] ) ) );
		        else
		            $error[] = __('The passwords you entered do not match.  Your password was not updated.', 'profile');
		    }
		
		    /* Update user information. */
		    if ( !empty( $_POST['url'] ) )
		    	wp_update_user( array( 'ID' => $current_user->ID, 'user_url' => esc_url( $_POST['url'] ) ) );
		    if ( !empty( $_POST['email'] ) ){
		    
		    	$email_exists = email_exists(esc_attr( $_POST['email'] ));
		    	
		        if (!is_email(esc_attr( $_POST['email'] )))
		            $error[] = __('The Email you entered is not valid.  please try again.', 'profile');
		        elseif( $email_exists && $email_exists != $current_user->ID )
		            $error[] = __('This email is already used by another user.  try a different one.', 'profile');
		        else{
		            wp_update_user( array ('ID' => $current_user->ID, 'user_email' => esc_attr( $_POST['email'] )));
		        }
		    }
		
		    if ( !empty( $_POST['nickname'] ) ):
		        update_user_meta( $current_user->ID, 'nickname', esc_attr( $_POST['nickname'] ) );
		        wp_update_user( array ('ID' => $current_user->ID, 'display_name' => esc_attr( $_POST['nickname'] )));
		    endif;
		        
		    if ( !empty( $_POST['description'] ) )
		        update_user_meta( $current_user->ID, 'description', esc_attr( $_POST['description'] ) );
		        
	        // Avatar Upload
	        $avatar = $_FILES['avatar'];
			if (isset($avatar,$_POST['avatar_nonce']) && $avatar && wp_verify_nonce( $_POST['avatar_nonce'], 'avatar_upload' )) {				
				require_once( ABSPATH . 'wp-admin/includes/image.php' );
				require_once( ABSPATH . 'wp-admin/includes/file.php' );
				require_once( ABSPATH . 'wp-admin/includes/media.php' );
				
				$attachment_id = media_handle_upload( 'avatar', 0 );
				
				if ( is_wp_error( $attachment_id ) ) {
					$error[] = __('Error uploading avatar.','cooked');
				} else {
					update_user_meta( $current_user->ID, 'avatar', $attachment_id );
				}
			} else {
				$error[] = __('Avatar uploader security check failed.','cooked');	
			}
			// END AVATAR
		
		    /* Redirect so the page will show updated info.*/
		    if ( count($error) == 0 ) {
		        //action hook for plugins and extra fields saving
		        do_action('edit_user_profile_update', $current_user->ID);
				wp_redirect( get_permalink($post->ID) );
		        exit;
		    }
		}
	
	endif;
	
}

add_action('get_header','cp_profile_update_submit');

function cp_handleautocomplete() {
	$queried_entries = array();

	$query_string = esc_sql($_POST['query_string']);

	global $wpdb;
	$prefix = $wpdb->prefix;
	$sql = "SELECT *
			FROM {$prefix}posts
			WHERE `post_type` = 'cp_ingredient'
				AND `post_title` LIKE '%{$query_string}%'";

	$results = $wpdb->get_results($sql);
	if(!empty($results)) {
		foreach($results as $entry) {
			$queried_entries[$entry->ID] = $entry->post_title;
		}
	}

	exit(json_encode($queried_entries));
}

function cp_do_math($expression) {
	eval('$o = ' . preg_replace('/[^0-9\+\-\*\/\(\)\.]/', '', $expression) . ';');
	return $o;
}

function cp_calculate_amount($amount, $type = 'decimal') {
	if($type === 'decimal') {
		$amount_parts = explode(' ', $amount);
		$total_parts = count($amount_parts);

		if($total_parts === 1) {
			$amount = cp_do_math($amount);
		} elseif($total_parts === 2) {
			$full_part = $amount_parts[0];
			$fractional_part = cp_do_math($amount_parts[1]);
			$amount = $full_part + $fractional_part;
		} else {
			$amount = floatval($amount);
		}
		$amount = (float)number_format($amount, 10);
	} else {
		$amount_parts = explode('.', $amount);
		$total_parts = count($amount_parts);

		if($total_parts === 2) {
			$full_part = intval($amount_parts[0]);
			$fractional_part = float2rat($amount - $full_part);
			if($full_part === 0) {
				$amount = $fractional_part;
			} else {
				$amount = $full_part . ' ' . $fractional_part;
			}
		} else {
			if($total_parts !== 1) {
				$amount = float2rat($amount);
			}
		}
	}

	return $amount;
}

function float2rat($n, $tolerance = 1.e-6) {
	$h1=1; $h2=0;
	$k1=0; $k2=1;
	$b = 1/$n;
	do {
		$b = 1/$b;
		$a = floor($b);
		$aux = $h1; $h1 = $a*$h1+$h2; $h2 = $aux;
		$aux = $k1; $k1 = $a*$k1+$k2; $k2 = $aux;
		$b = $b-$a;
	} while (abs($n-$h1/$k1) > $n*$tolerance);

	return "$h1/$k1";
}
