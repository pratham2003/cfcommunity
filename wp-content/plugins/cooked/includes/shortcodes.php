<?php



/* TIMER SHORTCODE */

if (!shortcode_exists('timer')) {
	add_shortcode('timer', 'cp_timer_shortcode');
}

function cp_timer_shortcode($atts, $content = null) {
	$atts = shortcode_atts(
		array(
			'length' => 10,
		), $atts );

	return '<a class="timer-trigger" href="#" data-time-in-seconds="' . $atts['length'] * 60 . '"><i class="fa fa-clock-o"></i> ' . $content . '</a>';
}



/* SEARCH BOX */

if (!shortcode_exists('cooked-search')) {
	add_shortcode('cooked-search', 'cp_search_shortcode');
}

function cp_search_shortcode($atts, $content = null) {

	$atts = shortcode_atts(
		array(
			'style' => false,
		), $atts );
	
	ob_start();
	$browse_page = (get_option('cp_recipes_list_view_page') ? get_option('cp_recipes_list_view_page') : false);
	echo '<div id="cooked-plugin-page"><div class="search-section'.($atts['style'] ? ' '.$atts['style'] : '').'">';
	if ($browse_page):
		cp_recipe_search_section($browse_page);
	else :
		echo '<p style="color:#888;">You need to choose a <em>Browse Recipes</em> page from the "Recipes > Settings" panel before the search shortcode will work.</p>';
	endif;
	echo '</div></div>';
	return ob_get_clean();
}



/* PROFILE CONTENT SHORTCODE */

if (!shortcode_exists('cooked-profile')) {
	add_shortcode('cooked-profile','cp_profile_page');
}

function cp_profile_page(){
	ob_start();
	require(CP_PLUGIN_TEMPLATES_DIR . 'profile.php');
	return ob_get_clean();
}



/* BROWSE RECIPES SHORTCODE */

if (!shortcode_exists('cooked-browse')) {
	add_shortcode('cooked-browse','cp_browse_recipes');
}

function cp_browse_recipes($atts) {
	
	$atts = shortcode_atts(
		array(
			'category' => null,
			'cuisine' => null,
			'method' => null,
			'sort' => null
		), $atts );
	
	$list_view = get_option('cp_recipe_list_view');
	if ($list_view):

		ob_start();
		
		global $manual_category, $manual_cuisine, $manual_cooking_method, $manual_sort;
		
		$manual_category = $atts['category'];
		$manual_cuisine = $atts['cuisine'];
		$manual_cooking_method = $atts['method'];
		$manual_sort = $atts['sort'];
		
		require(CP_PLUGIN_VIEWS_DIR . $list_view . '.php');
		
		return ob_get_clean();

	else :
		return 'Choose a List View from the Recipes > Settings panel.';
	endif;
}



/* SINGLE RECIPE SHORTCODE */

if (!shortcode_exists('cooked-recipe')) {
	add_shortcode('cooked-recipe','cp_single_recipe');
}

function cp_single_recipe($atts, $content = null){

	$atts = shortcode_atts(
		array(
			'id' => null,
		), $atts );
	
	if ($atts['id']):
		global $post_id;
		$post_id = $atts['id'];
		ob_start();
		require(CP_PLUGIN_SECTIONS_DIR . 'single-part.php');
		return ob_get_clean();
	else :
		return 'This shortcode requires an "id" attribute be present.';
	endif;
	
}


/* USER DIRECTORY */
if (!shortcode_exists('cooked-directory')) {
	add_shortcode( 'cooked-directory', 'cp_user_directory' );
}

function cp_user_directory($atts,$content = null) {
	ob_start();
	$args = array(
	);
	$user_list = get_users($args);
	echo '<div id="cooked-profile-page">';
	foreach($user_list as $user_data):
	
		?><div class="cp-profile-header cookedClearFix directory-pane">
		
			<?php
			
				$username = get_the_author_meta('user_login',$user_data->ID);
				$username = cp_create_slug($username);
				$profile_page_link = (get_option('cp_profile_page') ? get_permalink(get_option('cp_profile_page')) : false);
				$profile_page_link = rtrim($profile_page_link, '/');
				if ($profile_page_link): $profile_page_link = $profile_page_link . '/' . $username; endif;
			
			?>

			<div class="cp-avatar">
				<?php echo ($profile_page_link ? '<a href="'.$profile_page_link.'">' : '') . cp_avatar($user_data->ID,150) . ($profile_page_link ? '</a>' : ''); ?>
			</div>
			
			<?php
			
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
					
					$profile_recipes = array(
						'post_type' => 'cp_recipe',
						'posts_per_page' => -1,
						'orderby' => 'date',
						'order' => 'desc',
						'author' => $user_data->ID
					);
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
						$user_likes_count = count($user_likes);
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
				$user_review_count = count($user_reviews);
				
			?>
			
			<div class="cp-info">
				<div class="cp-user">
					<h3 class="<?php echo $h3_class; ?>"><?php echo ($profile_page_link ? '<a href="'.$profile_page_link.'">' : '') . get_user_meta( $user_data->ID, 'nickname', true ) . ($profile_page_link ? '</a>' : ''); ?></h3>
					<?php if ($user_desc){ echo wpautop($user_desc); } ?>
					<p class="cp-directory-counts">
					<?php if (in_array('fes_enabled', $fes_settings)) : ?><i class="fa fa-cutlery"></i>&nbsp;&nbsp;<?php echo number_format($total_recipes); ?> <?php _e('Recipes','cooked'); ?>&nbsp;&nbsp;&nbsp;<?php endif; ?>
					<?php if ($reviews_comments != 'admin_reviews_only') : ?><i class="fa fa-star"></i>&nbsp;&nbsp;<?php echo number_format($user_review_count); ?> <?php _e('Reviews','cooked'); ?>&nbsp;&nbsp;&nbsp;<?php endif; ?>
					<?php if (in_array('favorite_button', $recipe_actions)) : ?><i class="fa fa-heart"></i>&nbsp;&nbsp;<?php echo number_format($user_likes_count); ?> <?php _e('Favorites','cooked'); ?>&nbsp;&nbsp;&nbsp;<?php endif; ?>
					</p>
				</div>
			</div>
	
		</div><?php
		
	endforeach;
	echo '</div>';
	return ob_get_clean();
}



/* LOGIN SHORTCODE */

if (!shortcode_exists('cooked-login')) {
	add_shortcode( 'cooked-login', 'cp_login_form' );
}

function cp_registration_validation( $username, $email, $captcha_value = false, $captcha_from_user = false )  {
	global $reg_errors;
	$reg_errors = new WP_Error;
	$errors = array();
	
	if ($captcha_value):
		if ($captcha_value != $captcha_from_user):
			$reg_errors->add('captcha', __('The text you\'ve entered does not match the image.','cooked'));
		else :
			$captcha = new ReallySimpleCaptcha();
			$captcha->remove($captcha_value);
		endif;
	endif;
	
	if ( empty( $username ) || empty( $email ) ) {
	    $reg_errors->add('field', __('All fields are required to register.','cooked'));
	}
	
	if ( 4 > strlen( $username ) ) {
	    $reg_errors->add( 'username_length', __('That username is too short; at least 4 characters is required.','cooked'));
	}
	
	if ( username_exists( $username ) ) {
    	$reg_errors->add('user_name', __('That username already exists.','cooked'));
    }
    
    if ( ! validate_username( $username ) ) {
	    $reg_errors->add( 'username_invalid', __('That username is not valid.'.$username,'cooked'));
	}    
    
    if ( !is_email( $email ) && !empty( $email ) ) {
	    $reg_errors->add( 'email_invalid', __('That email address is not valid.','cooked'));
	}
	
	if ( email_exists( $email ) ) {
	    $reg_errors->add( 'email', __('That email is already in use.','cooked'));
	}
	
	if ( is_wp_error( $reg_errors ) ) {
	
		foreach ( $reg_errors->get_error_messages() as $error ) {
	    	$errors[] = $error;
	    }
	
	}
	
	return $errors;

}

function cp_complete_registration() {
    global $reg_errors, $username, $first_name, $last_name, $password, $email;
    
    if ( 1 > count( $reg_errors->get_error_messages() ) ) {
	    
        $userdata = array(
        	'user_login'    =>   $username,
			'user_email'    =>   $email,
			'user_pass'     =>   $password,
			'first_name'	=>	 $first_name,
			'last_name'		=>	 $last_name
        );
        $user_id = wp_insert_user( $userdata );
        
        $nickname = $first_name . ($last_name ? ' '.$last_name : '');
        
        update_user_meta( $user_id, 'nickname', $nickname );
		wp_update_user( array ('ID' => $user_id, 'display_name' => $nickname ) );
        
        // Send a registration welcome email to the new user?
		$email_content = get_option('cooked_registration_email_content');
		$email_subject = get_option('cooked_registration_email_subject');
		if ($email_content && $email_subject):
			$tokens = array('%name%','%username%','%password%');
			$replacements = array($first_name,$username,$password);
			$email_content = str_replace($tokens,$replacements,$email_content);
			$email_subject = str_replace($tokens,$replacements,$email_subject);
			cooked_mailer( $email, $email_subject, $email_content );
		endif;
		
        return '<p class="cp-form-notice"><strong>'.__('Success!','cooked').'</strong><br />'.__('Registration complete, please check your email for login information.','cooked').'</p>';

    } else {
	    return false;
    }
}

function cp_registration_form($first_name, $last_name, $email){
	
	?><form action="<?php echo $_SERVER['REQUEST_URI']; ?>" method="post" class="wp-user-form">
	
		<p class="first_name">
			<label for="first_name"><?php _e('First Name','cooked'); ?></label>
			<input type="text" name="first_name" value="<?php echo ( isset( $_POST['first_name'] ) ? $first_name : null ); ?>" id="first_name" tabindex="101" />
		</p>
		<p class="last_name">
			<label for="last_name"><?php _e('Last Name','cooked'); ?></label>
			<input type="text" name="last_name" value="<?php echo ( isset( $_POST['last_name'] ) ? $last_name : null ); ?>" id="last_name" tabindex="102" />
		</p>
		<p class="email">
			<label for="email"><?php _e('Your Email','cooked'); ?></label>
			<input type="text" name="email" value="<?php echo ( isset( $_POST['email'] ) ? $email : null ); ?>" id="email" tabindex="103" />
		</p>
		
		<?php if (class_exists('ReallySimpleCaptcha')) :
			
			?><p class="captcha">
				<label for="captcha_code"><?php _e('Please enter the following text:','cooked'); ?></label><?php
			
				$rsc_url = WP_PLUGIN_URL . '/really-simple-captcha/';
				
		        $captcha = new ReallySimpleCaptcha();
		        $captcha->fg = array(150,150,150);
	            $captcha_word = $captcha->generate_random_word(); //generate a random string with letters
	            $captcha_prefix = mt_rand(); //random number
	            $captcha_image = $captcha->generate_image($captcha_prefix, $captcha_word); //generate the image file. it returns the file name
	            $captcha_file = rtrim(get_bloginfo('wpurl'), '/') . '/wp-content/plugins/really-simple-captcha/tmp/' . $captcha_image; //construct the absolute URL of the captcha image
		        
		        echo '<img class="captcha-image" src="'.$rsc_url.'tmp/'.$captcha_image.'">';
		        
		        ?><input type="text" name="captcha_code" value="" tabindex="104" />
			    <input type="hidden" name="captcha_word" value="<?php echo $captcha_word; ?>" />
			</p><?php
				
		endif; ?>
		
		<input type="submit" name="submit" value="<?php _e('Register','cooked'); ?>" class="user-submit" tabindex="105" />
		
	</form><?php
						
}

function cp_login_form( $atts, $content = null ) {

	global $post;

	if (!is_user_logged_in()) {
	
		ob_start();
	
		?><div id="cooked-profile-page">
		
			<div id="cooked-page-form">
		
				<ul class="cp-tabs login cookedClearFix">
					<li<?php if ( !isset($_POST['submit'] ) ) { ?> class="active"<?php } ?>><a href="#login"><i class="fa fa-user"></i><?php _e('Login','cooked'); ?></a></li>
					<?php if (get_option('users_can_register')): ?><li<?php if ( isset($_POST['submit'] ) ) { ?> class="active"<?php } ?>><a href="#register"><i class="fa fa-edit"></i><?php _e('Register','cooked'); ?></a></li><?php endif; ?>
					<li><a href="#forgot"><i class="fa fa-question"></i><?php _e('Forgot your password?','cooked'); ?></a></li>
				</ul>
			
				<div id="profile-login" class="cp-tab-content">
		
					<?php if (isset($reset) && $reset == true) { ?>
		
						<p class="cp-form-notice">
						<strong><?php _e('Success!','cooked'); ?></strong><br />
						<?php _e('Check your email to reset your password.','cooked'); ?>
						</p>
		
					<?php } ?>
		
					<div class="cp-form-wrap cookedClearFix">
						<div class="cp-custom-error"><?php _e('Both fields are required to log in.','cooked'); ?></div>
						<?php if (isset($_GET['loginfailed'])): ?><div class="cp-custom-error not-hidden"><?php _e('Sorry, those login credentials are incorrect.'); ?></div><?php endif; ?>
						<?php echo wp_login_form( array( 'echo' => false, 'redirect' => get_the_permalink($post->ID) ) ); ?>
					</div>
				</div>
				
				<?php if (get_option('users_can_register')): ?>
				
				<div id="profile-register" class="cp-tab-content">
					<div class="cp-form-wrap cookedClearFix">
					
						<?php if ( isset($_POST['submit'] ) ) {
						
					        // sanitize user form input
					        global $username, $first_name, $last_name, $password, $email;
					        
					        $first_name =   sanitize_user( $_POST['first_name'] );
					        $last_name 	=   sanitize_user( $_POST['last_name'] );
					        $password 	= 	wp_generate_password();
					        $email      =   sanitize_email( $_POST['email'] );
					        
					        if (isset($_POST['captcha_word'])):
					        	$captcha_word = strtolower($_POST['captcha_word']);
								$captcha_code = strtolower($_POST['captcha_code']);
					        else :
					        	$captcha_word = false;
								$captcha_code = false;
					        endif;
					        
					        if ($last_name): $username = $first_name.$last_name; else : $username = $first_name; endif;
							$username = preg_replace("/&([a-z])[a-z]+;/i", "$1", htmlentities($username));
							$errors = cp_registration_validation($username,$email,$captcha_word,$captcha_code);
							
							if (!empty($errors)):
								$rand = rand(111,999);
								if ($last_name): $username = $first_name.$last_name.'_'.$rand; else : $username = $first_name.'_'.$rand; endif;
								$username = preg_replace("/&([a-z])[a-z]+;/i", "$1", htmlentities($username));
								$errors = cp_registration_validation($username,$email,$captcha_word,$captcha_code);
							endif;
	
							if (empty($errors)):
					        	$registration_complete = cp_complete_registration();
					        else :
					        	$registration_complete = 'error';
					        endif;
					        
					    } else {
					    
						    $registration_complete = false;
						    
					    }
					    
					    if ($registration_complete && $registration_complete != 'error'){
					    
						    echo $registration_complete;
						    
					    } else {
					    
					    	if ($registration_complete == 'error'){
						    	?><div class="cp-custom-error" style="display:block"><?php echo implode('<br>', $errors); ?></div><?php
					    	}
					    
						    $first_name = (isset($_POST['first_name']) ? $_POST['first_name'] : '');
						    $last_name = (isset($_POST['last_name']) ? $_POST['last_name'] : '');
							$email = (isset($_POST['email']) ? $_POST['email'] : '');
							
							cp_registration_form($first_name,$last_name,$email);
							
					    }
						?>
					
					</div>
				</div>
				
				<?php endif; ?>
				
				<div id="profile-forgot" class="cp-tab-content">
					<div class="cp-form-wrap cookedClearFix">
						<div class="cp-custom-error"><?php _e('A username or email address is required to reset your password.','cooked'); ?></div>
						<form method="post" action="<?php echo site_url('wp-login.php?action=lostpassword', 'login_post') ?>" class="wp-user-form">
							<p class="username">
								<label for="user_login" class="hide"><?php _e('Username or Email'); ?></label>
								<input type="text" name="user_login" value="" size="20" id="user_login" tabindex="1001" />
							</p>
								
							<?php do_action('login_form', 'resetpass'); ?>
							<input type="submit" name="user-submit" value="<?php _e('Reset my password'); ?>" class="user-submit" tabindex="1002" />
							<input type="hidden" name="redirect_to" value="<?php the_permalink(); ?>?reset=true" />
							<input type="hidden" name="user-cookie" value="1" />
								
						</form>
					</div>
				</div>
			</div><!-- END #cooked-page-form -->
			
		</div><?php
		
		$content = ob_get_clean();
	}
	
	return $content;
	
}



/* FRONT-END RECIPE FORM */

if (!shortcode_exists('recipe-form')) {
	$fes_settings = cp_recipe_fes_settings();
	if (in_array('fes_enabled', $fes_settings)) :
		add_shortcode('recipe-form', 'cp_recipe_form');
	endif;
}

if (!shortcode_exists('cooked-submit')) {
	$fes_settings = cp_recipe_fes_settings();
	if (in_array('fes_enabled', $fes_settings)) :
		add_shortcode('cooked-submit', 'cp_recipe_form');
	endif;
}
	
function cp_recipe_form($atts, $content = null) {

	ob_start();
	
	// Get the current user's role
	if ( is_user_logged_in() ) {
		global $current_user;
		
		$required_user_roles = get_option('cp_recipes_fes_user_roles');
		$user_display_name = $current_user->data->display_name;
		$current_user_role = (isset($current_user->roles[0]) ? $current_user->roles[0] : false);
		
		$cooked_plugin = new cooked_plugin();
		$enabled_taxonomies = $cooked_plugin->cp_recipe_tax_settings();
		
		$recipe_info = cp_recipe_info_settings();
		
		if (!empty($required_user_roles)):
		
			if (in_array($current_user_role,$required_user_roles)){
	
				global $cp_form_complete;
				
				$recipe_limit = get_option('cp_recipe_fes_limit');
				if ($recipe_limit):
					$user_recipe_total = cp_user_recipe_total($current_user->ID,true);
					if ($user_recipe_total >= $recipe_limit):
						$reached_limit = true;
					else :
						$reached_limit = false;
					endif;
				else :
					$reached_limit = false;
				endif;
				
				if (isset($_GET['success'])):
				
					// Form has been submitted, display "thank you" notice.
					echo '<div id="cooked-plugin-page">'.wpautop(__('Thank you for submitting a recipe. Your recipe will be reviewed by a member of our staff.','cooked').'<br><br><a class="btn" href="'.get_permalink().'">'.__('Submit another recipe?','cooked').'</a>').'</div>';
					
				elseif ($reached_limit):
				
					?><div id="cooked-plugin-page"><p><?php echo sprintf(_n("Sorry, but you've hit the recipe submission limit. Each user may only have %d recipe at a time.","Sorry, but you've hit the recipe submission limit. Each user may only have %d recipes at a time.", $recipe_limit, "cooked" ), $recipe_limit); ?></p></div><?php
				
				else :
				
					?><div id="cooked-plugin-page"><form id="cooked-submit-recipe-form" action="<?php the_permalink(); ?>" method="post" enctype="multipart/form-data">
					
						<?php echo (get_option('cp_fes_welcome_message') ? wpautop(do_shortcode(str_replace(array('%UserName%','%username%','%USERNAME%','%Username%'),$user_display_name,get_option('cp_fes_welcome_message')))) : ''); ?>
						
						<?php do_action( 'cp_recipe_form_notice' ); ?>
						
						<div class="section-row">
							<div class="section-col">
								<div class="section-head">
									<h2><?php _e('Recipe Title', 'cooked'); ?></h2>
								</div><!-- /.section-head -->
								<div class="section-body">
									<div class="field-wrap">
										<input type="text" class="field" name="_cp_recipe_title" value="<?php echo (isset($_POST['_cp_recipe_title']) ? $_POST['_cp_recipe_title'] : ''); ?>" />
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
									<input type="text" class="field" name="_cp_recipe_external_video" value="<?php echo (isset($_POST['_cp_recipe_external_video']) ? $_POST['_cp_recipe_external_video'] : ''); ?>" />
								</div><!-- /.field-wrap -->
								<p class="hint-p">
									<strong><?php _e('OPTIONAL:','cooked'); ?></strong> <?php _e('If you have your recipe video on Youtube, Vimeo, or','cooked'); ?> <a href="http://codex.wordpress.org/Embeds" target="_blank"><?php _e('any of the other supported oEmbed sites','cooked'); ?></a>, <?php _e('then you\'ll want to use the field above. Just paste in the URL','cooked'); ?> (<?php _e('ex','cooked'); ?>. <em>http://youtu.be/1O8D_wTCm3s</em> <?php _e('or','cooked'); ?> <em>https://vimeo.com/26140401</em>) <?php _e('and it will show up as a popup by clicking the recipe image.','cooked'); ?>
								</p><!-- /.hint-p -->
							</div><!-- /.section-body -->
						</div><!-- /.section-row -->
						
						<?php if (!in_array('difficulty_level',$recipe_info)): ?><div class="cp-hidden"><?php endif; ?>
							<div class="section-row cookedClearFix">
								<?php $difficulty = (isset($_POST['_cp_recipe_difficulty_level']) ? $_POST['_cp_recipe_difficulty_level'] : ''); ?>
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
										$category = (isset($_POST['cp_recipe_category']) ? $_POST['cp_recipe_category'] : ''); ?>
										<div class="<?php if ($tax_columns == 3) : ?>section-third<?php elseif ($tax_columns == 2) : ?>section-col<?php endif; ?>"><div class="section-head"><h2><?php _e('Recipe Category', 'cooked'); ?></h2></div><?php cp_taxonomy_dropdown('cp_recipe_category', __('Choose one or more...', 'cooked'),$category); ?></div>
									<?php endif; ?>
									<?php if (!in_array('category',$recipe_info)): ?></div><?php endif; ?>
									
									<?php if (!in_array('cuisine',$recipe_info)): ?><div class="cp-hidden"><?php endif; ?>
									<?php if (in_array('cuisine',$enabled_taxonomies)):
										$cuisine = (isset($_POST['cp_recipe_cuisine']) ? $_POST['cp_recipe_cuisine'] : ''); ?>
										<div class="<?php if ($tax_columns == 3) : ?>section-third<?php elseif ($tax_columns == 2) : ?>section-col<?php endif; ?>"><div class="section-head"><h2><?php _e('Cuisine', 'cooked'); ?></h2></div><?php cp_taxonomy_dropdown('cp_recipe_cuisine', __('Choose one or more...', 'cooked'),$cuisine); ?></div>
									<?php endif; ?>
									<?php if (!in_array('cuisine',$recipe_info)): ?></div><?php endif; ?>
									
									<?php if (!in_array('method',$recipe_info)): ?><div class="cp-hidden"><?php endif; ?>
									<?php if (in_array('method',$enabled_taxonomies)):
										$method = (isset($_POST['cp_recipe_cooking_method']) ? $_POST['cp_recipe_cooking_method'] : ''); ?>
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
								<textarea class="field small" name="_cp_recipe_short_description" cols="0" rows="0"><?php echo (isset($_POST['_cp_recipe_short_description']) ? stripslashes($_POST['_cp_recipe_short_description']) : ''); ?></textarea>
							</div><!-- /.section-body -->
						</div><!-- /.section-row -->
						<div class="section-row">
							<div class="section-head">
								<h2><?php _e('Excerpt for List Views', 'cooked'); ?></h2>
							</div><!-- /.section-head -->
					
							<div class="section-body">
								<textarea class="field small" name="_cp_recipe_excerpt" cols="0" rows="0"><?php echo (isset($_POST['_cp_recipe_short_description']) ? stripslashes($_POST['_cp_recipe_short_description']) : ''); ?></textarea>
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
											<input type="hidden" name="_cp_recipe_prep_time" value="<?php echo (isset($_POST['_cp_recipe_prep_time']) ? $_POST['_cp_recipe_prep_time'] : ''); ?>" class="real-value" />
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
											<input type="hidden" name="_cp_recipe_cook_time" value="<?php echo (isset($_POST['_cp_recipe_cook_time']) ? $_POST['_cp_recipe_cook_time'] : ''); ?>" class="real-value" />
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
								<div class="section-title-box">
									<p class="section-stats-first" data-plural="Sections" data-single="Section">0 Sections</p> /
									<p class="section-stats-second" data-plural="Ingredients" data-single="Ingredient">0 Ingredients</p>
								</div><!-- /.section-title-box -->
								<textarea class="field med section-stats-field" name="_cp_recipe_ingredients" cols="0" rows="0"><?php echo (isset($_POST['_cp_recipe_ingredients']) ? stripslashes($_POST['_cp_recipe_ingredients']) : ''); ?></textarea>
								<p class="hint-p">
									<?php _e('Enter one ingredient per line. Use a double dash to start new section titles. (ex. --Section Title). Separate the ingredient amount from the ingredient name with double space if you want to follow Google\'s rich snippets formatting.','cooked'); ?>
								</p><!-- /.hint-p -->
							</div><!-- /.section-body -->
						</div><!-- /.section-row -->
					
						<div class="section-row">
							<div class="section-head">
								<h2><?php _e('Directions', 'cooked'); ?></h2>
							</div><!-- /.section-head -->
					
							<div class="section-body section-stats">
								<div class="section-title-box">
									<p class="section-stats-first" data-plural="Sections" data-single="Section">0 Sections</p> /
									<p class="section-stats-second" data-plural="Steps" data-single="Step">0 Steps</p>
								</div><!-- /.section-title-box -->
								<textarea class="field med section-stats-field" name="_cp_recipe_directions" cols="0" rows="0"><?php echo (isset($_POST['_cp_recipe_directions']) ? stripslashes($_POST['_cp_recipe_directions']) : ''); ?></textarea>
								<p class="hint-p"><?php _e('Add all of the cooking steps, one per line. You can use a double dash for section titles (ex. --Section Title). You can also use the <strong>[timer length=30]30 Minutes[/timer]</strong> shortcode to add a timer link.','cooked'); ?></p><!-- /.hint-p -->
							</div><!-- /.section-body -->
						</div><!-- /.section-row -->
						
						<?php if (!in_array('notes',$recipe_info)): ?><div class="cp-hidden"><?php endif; ?>
						<div class="section-row">
							<div class="section-head">
								<h2><?php _e('Additional Notes', 'cooked'); ?></h2>
							</div><!-- /.section-head -->
					
							<div class="section-body">
								<textarea class="field small" name="_cp_recipe_additional_notes" cols="0" rows="0"><?php echo (isset($_POST['_cp_recipe_additional_notes']) ? stripslashes($_POST['_cp_recipe_additional_notes']) : ''); ?></textarea>
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
									<input type="text" class="field" name="_cp_recipe_yields" value="<?php echo (isset($_POST['_cp_recipe_yields']) ? $_POST['_cp_recipe_yields'] : ''); ?>" />
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
											<input type="text" class="field" name="_cp_recipe_nutrition_servingsize" value="<?php echo (isset($_POST['_cp_recipe_nutrition_servingsize']) ? $_POST['_cp_recipe_nutrition_servingsize'] : ''); ?>" />
										</div><!-- /.field-wrap -->
									</div><!-- /.section-body -->	
								</div><!-- /.section-col -->
						
								<div class="section-col">
									<div class="section-head">
										<h3><?php _e('Calories', 'cooked'); ?></h3>
									</div><!-- /.section-head -->
									<div class="section-body">
										<div class="field-wrap">
											<input type="text" class="field" name="_cp_recipe_nutrition_calories" value="<?php echo (isset($_POST['_cp_recipe_nutrition_calories']) ? $_POST['_cp_recipe_nutrition_calories'] : ''); ?>" />
										</div><!-- /.field-wrap -->
									</div><!-- /.section-body -->	
								</div><!-- /.section-col -->
								
								<div class="section-col">
									<div class="section-head">
										<h3><?php _e('Total Fat', 'cooked'); ?></h3>
									</div><!-- /.section-head -->
									<div class="section-body">
										<div class="field-wrap">
											<input type="text" class="field" name="_cp_recipe_nutrition_fat" value="<?php echo (isset($_POST['_cp_recipe_nutrition_fat']) ? $_POST['_cp_recipe_nutrition_fat'] : ''); ?>" />
										</div><!-- /.field-wrap -->
									</div><!-- /.section-body -->	
								</div><!-- /.section-col -->
								
								<div class="section-col">
									<div class="section-head">
										<h3><?php _e('Saturated Fat', 'cooked'); ?></h3>
									</div><!-- /.section-head -->
									<div class="section-body">
										<div class="field-wrap">
											<input type="text" class="field" name="_cp_recipe_nutrition_satfat" value="<?php echo (isset($_POST['_cp_recipe_nutrition_satfat']) ? $_POST['_cp_recipe_nutrition_satfat'] : ''); ?>" />
										</div><!-- /.field-wrap -->
									</div><!-- /.section-body -->	
								</div><!-- /.section-col -->
								
								<div class="section-col">
									<div class="section-head">
										<h3><?php _e('Polyunsaturated Fat', 'cooked'); ?></h3>
									</div><!-- /.section-head -->
									<div class="section-body">
										<div class="field-wrap">
											<input type="text" class="field" name="_cp_recipe_nutrition_polyunsatfat" value="<?php echo (isset($_POST['_cp_recipe_nutrition_polyunsatfat']) ? $_POST['_cp_recipe_nutrition_polyunsatfat'] : ''); ?>" />
										</div><!-- /.field-wrap -->
									</div><!-- /.section-body -->	
								</div><!-- /.section-col -->
								
								<div class="section-col">
									<div class="section-head">
										<h3><?php _e('Monounsaturated Fat', 'cooked'); ?></h3>
									</div><!-- /.section-head -->
									<div class="section-body">
										<div class="field-wrap">
											<input type="text" class="field" name="_cp_recipe_nutrition_monounsatfat" value="<?php echo (isset($_POST['_cp_recipe_nutrition_monounsatfat']) ? $_POST['_cp_recipe_nutrition_monounsatfat'] : ''); ?>" />
										</div><!-- /.field-wrap -->
									</div><!-- /.section-body -->	
								</div><!-- /.section-col -->
								
								<div class="section-col">
									<div class="section-head">
										<h3><?php _e('Trans Fat', 'cooked'); ?></h3>
									</div><!-- /.section-head -->
									<div class="section-body">
										<div class="field-wrap">
											<input type="text" class="field" name="_cp_recipe_nutrition_transfat" value="<?php echo (isset($_POST['_cp_recipe_nutrition_transfat']) ? $_POST['_cp_recipe_nutrition_transfat'] : ''); ?>" />
										</div><!-- /.field-wrap -->
									</div><!-- /.section-body -->	
								</div><!-- /.section-col -->
								
								<div class="section-col">
									<div class="section-head">
										<h3><?php _e('Cholesterol', 'cooked'); ?></h3>
									</div><!-- /.section-head -->
									<div class="section-body">
										<div class="field-wrap">
											<input type="text" class="field" name="_cp_recipe_nutrition_cholesterol" value="<?php echo (isset($_POST['_cp_recipe_nutrition_cholesterol']) ? $_POST['_cp_recipe_nutrition_cholesterol'] : ''); ?>" />
										</div><!-- /.field-wrap -->
									</div><!-- /.section-body -->	
								</div><!-- /.section-col -->
								
								<div class="section-col">
									<div class="section-head">
										<h3><?php _e('Sodium', 'cooked'); ?></h3>
									</div><!-- /.section-head -->
									<div class="section-body">
										<div class="field-wrap">
											<input type="text" class="field" name="_cp_recipe_nutrition_sodium" value="<?php echo (isset($_POST['_cp_recipe_nutrition_sodium']) ? $_POST['_cp_recipe_nutrition_sodium'] : ''); ?>" />
										</div><!-- /.field-wrap -->
									</div><!-- /.section-body -->	
								</div><!-- /.section-col -->
								
								<div class="section-col">
									<div class="section-head">
										<h3><?php _e('Potassium', 'cooked'); ?></h3>
									</div><!-- /.section-head -->
									<div class="section-body">
										<div class="field-wrap">
											<input type="text" class="field" name="_cp_recipe_nutrition_potassium" value="<?php echo (isset($_POST['_cp_recipe_nutrition_potassium']) ? $_POST['_cp_recipe_nutrition_potassium'] : ''); ?>" />
										</div><!-- /.field-wrap -->
									</div><!-- /.section-body -->	
								</div><!-- /.section-col -->
								
								<div class="section-col">
									<div class="section-head">
										<h3><?php _e('Total Carbohydrate', 'cooked'); ?></h3>
									</div><!-- /.section-head -->
									<div class="section-body">
										<div class="field-wrap">
											<input type="text" class="field" name="_cp_recipe_nutrition_carbs" value="<?php echo (isset($_POST['_cp_recipe_nutrition_carbs']) ? $_POST['_cp_recipe_nutrition_carbs'] : ''); ?>" />
										</div><!-- /.field-wrap -->
									</div><!-- /.section-body -->	
								</div><!-- /.section-col -->
								
								<div class="section-col">
									<div class="section-head">
										<h3><?php _e('Fiber', 'cooked'); ?></h3>
									</div><!-- /.section-head -->
									<div class="section-body">
										<div class="field-wrap">
											<input type="text" class="field" name="_cp_recipe_nutrition_fiber" value="<?php echo (isset($_POST['_cp_recipe_nutrition_fiber']) ? $_POST['_cp_recipe_nutrition_fiber'] : ''); ?>" />
										</div><!-- /.field-wrap -->
									</div><!-- /.section-body -->	
								</div><!-- /.section-col -->
								
								<div class="section-col">
									<div class="section-head">
										<h3><?php _e('Sugar', 'cooked'); ?></h3>
									</div><!-- /.section-head -->
									<div class="section-body">
										<div class="field-wrap">
											<input type="text" class="field" name="_cp_recipe_nutrition_sugar" value="<?php echo (isset($_POST['_cp_recipe_nutrition_sugar']) ? $_POST['_cp_recipe_nutrition_sugar'] : ''); ?>" />
										</div><!-- /.field-wrap -->
									</div><!-- /.section-body -->	
								</div><!-- /.section-col -->
								
								<div class="section-col">
									<div class="section-head">
										<h3><?php _e('Protein', 'cooked'); ?></h3>
									</div><!-- /.section-head -->
									<div class="section-body">
										<div class="field-wrap">
											<input type="text" class="field" name="_cp_recipe_nutrition_protein" value="<?php echo (isset($_POST['_cp_recipe_nutrition_protein']) ? $_POST['_cp_recipe_nutrition_protein'] : ''); ?>" />
										</div><!-- /.field-wrap -->
									</div><!-- /.section-body -->	
								</div><!-- /.section-col -->
								
							</div><!-- /.section-row -->
						</div>
						<?php if (!in_array('nutrition',$recipe_info)): ?></div><?php endif; ?>
						
						<?php if (class_exists('ReallySimpleCaptcha')) :
			
							?><br><br><div class="section-row">
								<div class="section-head">
									<h2><?php _e('Please enter the following text:','cooked'); ?></h2>
								</div><!-- /.section-head -->
								<div class="section-body">
									<div class="field-wrap"><?php
										$rsc_url = WP_PLUGIN_URL . '/really-simple-captcha/';
								
								        $captcha = new ReallySimpleCaptcha();
								        $captcha->fg = array(150,150,150);
							            $captcha_word = $captcha->generate_random_word(); //generate a random string with letters
							            $captcha_prefix = mt_rand(); //random number
							            $captcha_image = $captcha->generate_image($captcha_prefix, $captcha_word); //generate the image file. it returns the file name
							            $captcha_file = rtrim(get_bloginfo('wpurl'), '/') . '/wp-content/plugins/really-simple-captcha/tmp/' . $captcha_image; //construct the absolute URL of the captcha image
								        
								        echo '<img class="captcha-image" src="'.$rsc_url.'tmp/'.$captcha_image.'">';
								        
								        ?><input type="text" name="captcha_code" value="" tabindex="104" />
									    <input type="hidden" name="captcha_word" value="<?php echo $captcha_word; ?>" />
									</div><!-- /.field-wrap -->
								</div><!-- /.section-body -->
							</div><!-- /.section-row --><?php
								
						endif; ?>
						
						<input id="submit" type="submit" class="sbmt-button" value="<?php esc_attr_e( 'Submit Recipe', 'cooked' ); ?>" />					
						<input type="hidden" name="action" value="post" />
						<?php wp_nonce_field( 'new-post' ); ?>
					
					</form></div><?php
					
				endif; // endif form is complete
				
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
	
	return $output;

}

function cp_recipe_form_errors(){
	
	?><style>
		.cp-form-error{ border:1px solid #EECECE; border-radius:5px; background-color: #FFF5F4; margin: 0 0 15px 0px !important; padding: 10px 15px !important; }
	</style><?php
	
	global $error_array;
	foreach($error_array as $error){
		echo '<p class="cp-form-error">' . $error . '</p>';
	}
	
}
 
function cp_recipe_form_notices(){
 
	global $notice_array;
	foreach($notice_array as $notice){
		echo '<p class="cp-form-notice">' . $notice . '</p>';
	}
	
}
 
function cp_recipe_form_submit_post(){

	if ( 'POST' == $_SERVER['REQUEST_METHOD'] && !empty( $_POST['action'] ) && $_POST['action'] == 'post' ){
		if ( !is_user_logged_in() )
			return;
		global $current_user;
		
		$required_user_roles = get_option('cp_recipes_fes_user_roles');
		$current_user_role = $current_user->roles[0];
		if (in_array($current_user_role,$required_user_roles)){
		
			$cooked_plugin = new cooked_plugin();
			$enabled_taxonomies = $cooked_plugin->cp_recipe_tax_settings();
 
			$user_id			= $current_user->ID;
			$post_title     	= $_POST['_cp_recipe_title'];
			$category 			= (in_array('category',$enabled_taxonomies) && isset($_POST['cp_recipe_category']) ? $_POST['cp_recipe_category'] : false);
			$cooking_method 	= (in_array('method',$enabled_taxonomies) && isset($_POST['cp_recipe_cooking_method']) ? $_POST['cp_recipe_cooking_method'] : false);
			$cuisine 			= (in_array('cuisine',$enabled_taxonomies) && isset($_POST['cp_recipe_cuisine']) ? $_POST['cp_recipe_cuisine'] : false);
			$photo 				= $_FILES['_cp_recipe_image'];
			$video 				= $_POST['_cp_recipe_external_video'];
			$short_desc 		= $_POST['_cp_recipe_short_description'];
			$excerpt 			= $_POST['_cp_recipe_excerpt'];
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
			
			if (isset($_POST['captcha_word'])):
	        	$captcha_word = strtolower($_POST['captcha_word']);
				$captcha_code = strtolower($_POST['captcha_code']);
	        else :
	        	$captcha_word = false;
				$captcha_code = false;
	        endif;
	        
	        if ($captcha_word != $captcha_code):
	        	$error_array[] = __('The captcha text you\'ve entered does not match the image.','cooked');
	        endif;
	 
			if (empty($post_title)) $error_array[] = __('Please add a title.','cooked');
			if (empty($ingredients)) $error_array[] = __('Please add some ingredients.','cooked');
			if (empty($directions)) $error_array[] = __('Please add the directions.','cooked');
			
			$recipe_default_status = get_option('cp_fes_new_recipe_default','draft');
	 
			if (count($error_array) == 0){
	 
				$post_id = wp_insert_post( array(
					'post_author'	=> $user_id,
					'post_title'	=> wp_strip_all_tags($post_title),
					'post_type'     => 'cp_recipe',
					'post_status'	=> $recipe_default_status
				));
				
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
				
					add_post_meta($post_id, '_cp_recipe_external_video', wp_strip_all_tags($video));
					add_post_meta($post_id, '_cp_recipe_short_description', $short_desc);
					add_post_meta($post_id, '_cp_recipe_excerpt', $excerpt);
					add_post_meta($post_id, '_cp_recipe_prep_time', $prep_time);
					add_post_meta($post_id, '_cp_recipe_cook_time', $cook_time);
					add_post_meta($post_id, '_cp_recipe_difficulty_level', $difficulty);
					add_post_meta($post_id, '_cp_recipe_ingredients', wp_strip_all_tags($ingredients));
					add_post_meta($post_id, '_cp_recipe_directions', wp_strip_all_tags($directions));
					add_post_meta($post_id, '_cp_recipe_additional_notes', $additional_notes);
					add_post_meta($post_id, '_cp_recipe_yields', wp_strip_all_tags($yields));
					add_post_meta($post_id, '_cp_recipe_nutrition_servingsize', wp_strip_all_tags($servingsize));
					add_post_meta($post_id, '_cp_recipe_nutrition_calories', wp_strip_all_tags($calories));
					add_post_meta($post_id, '_cp_recipe_nutrition_sodium', wp_strip_all_tags($sodiumcontent));
					add_post_meta($post_id, '_cp_recipe_nutrition_potassium', wp_strip_all_tags($potassiumcontent));
					add_post_meta($post_id, '_cp_recipe_nutrition_protein', wp_strip_all_tags($proteincontent));
					add_post_meta($post_id, '_cp_recipe_nutrition_cholesterol', wp_strip_all_tags($cholesterolcontent));
					add_post_meta($post_id, '_cp_recipe_nutrition_sugar', wp_strip_all_tags($sugarcontent));
					add_post_meta($post_id, '_cp_recipe_nutrition_fat', wp_strip_all_tags($fatcontent));
					add_post_meta($post_id, '_cp_recipe_nutrition_satfat', wp_strip_all_tags($saturatedfatcontent));
					add_post_meta($post_id, '_cp_recipe_nutrition_polyunsatfat', wp_strip_all_tags($polyunsatfat));
					add_post_meta($post_id, '_cp_recipe_nutrition_monounsatfat', wp_strip_all_tags($monounsatfat));
					add_post_meta($post_id, '_cp_recipe_nutrition_transfat', wp_strip_all_tags($transfat));
					add_post_meta($post_id, '_cp_recipe_nutrition_carbs', wp_strip_all_tags($carbohydratecontent));
					add_post_meta($post_id, '_cp_recipe_nutrition_fiber', wp_strip_all_tags($fibercontent));
					
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
					    'post_content' => $post_content
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
						add_post_meta($post_id, '_thumbnail_id', $attachment_id);
					}
				}		
	 
				if (count($error_array) == 0){
					
					$recipe = get_post($post_id);
					$user_id = $recipe->post_author;
					$recipe_name = $recipe->post_title;
					$recipe_link = get_permalink($post_id);
					$recipe_link = '<a href="'.$recipe_link.'">'.$recipe_link.'</a>';
					
					// Send an email to the user?
					$email_content = get_option('cooked_admin_recipe_email_content');
					$email_subject = get_option('cooked_admin_recipe_email_subject');
					if ($email_content && $email_subject):
						$user_name = get_user_meta( $user_id, 'first_name', true );
						$user_data = get_userdata( $user_id );
						$admin_email = get_option( 'admin_email' );
						$tokens = array('%name%','%recipename%','%recipelink%');
						$replacements = array($user_name,$recipe_name,$recipe_link);
						$email_content = str_replace($tokens,$replacements,$email_content);
						$email_subject = str_replace($tokens,$replacements,$email_subject);
						cooked_mailer( $admin_email, $email_subject, $email_content );
					endif;
				
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
 
add_action('init','cp_recipe_form_submit_post');


if (!shortcode_exists('cooked-recipe-card')) {
	add_shortcode('cooked-recipe-card', 'cp_recipe_card');
}
	
function cp_recipe_card($atts, $content = null) {

	ob_start();

	$atts = shortcode_atts(
		array(
			'id' => null,
			'style' => 'vertical',
		), $atts );
	
	$entry_id = $atts['id'];
	if ($entry_id):
		
		$recipe_info = cp_recipe_info_settings();
		$entry_link = get_permalink($entry_id);
		$entry_image = get_post_meta($entry_id, '_thumbnail_id', true);
		$entry_title = get_the_title($entry_id);
		$entry_rating = cp_recipe_rating($entry_id);
		$entry_description = get_post_meta($entry_id, '_cp_recipe_short_description', true);
		$prep_time = get_post_meta($entry_id, '_cp_recipe_prep_time', true);
		$cook_time = get_post_meta($entry_id, '_cp_recipe_cook_time', true);
		$total_time = $prep_time + $cook_time;
		$entry_yields = get_post_meta($entry_id, '_cp_recipe_yields', true); ?>
	
		<?php if ($atts['style'] == 'horizontal'): ?>
		
			<div id="cooked-plugin-page" class="cooked-recipe-card cookedClearFix">	
				<div class="result-section full-width-box-layout">
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
								<?php if (in_array('description', $recipe_info)) : echo wpautop($entry_description); endif; ?>
								
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
											<li><?php _e('Prep Time','cooked'); ?>: <strong><?php echo cp_format_time($prep_time); ?></strong></li>
											<li><?php _e('Total Time','cooked'); ?>: <strong><?php echo cp_format_time($total_time); ?></strong></li>
										<?php endif; ?>
										<?php if (in_array('yields', $recipe_info) && $entry_yields) : ?><li><?php echo $entry_yields; ?></li><?php endif; ?>
									</ul>
								</div><!-- /.timing -->
								<?php endif; endif; ?>
							</div><!-- /.cp-box-info -->
						</div><!-- /.cp-box -->
					</div><!-- /.result-box -->
				</div>
			</div>
			
		<?php else : ?>
	
			<div id="cooked-plugin-page" class="cooked-recipe-card cookedClearFix">	
				<div class="result-section masonry-layout">
					<div class="result-box item">
						<div class="cp-box">
							<div class="cp-box-img">
								<?php if(!empty($entry_image)) {
									echo '<a href="'.$entry_link.'">'.wp_get_attachment_image($entry_image, 'cp_298_192').'</a>';
								} else {
									echo '<a href="'.$entry_link.'"><img src="'.CP_PLUGIN_URL.'/css/images/default_298_192.png"></a>';
								}
								?>
							</div><!-- /.cp-box-img -->
							<div class="cp-box-entry">
								<h2><a href="<?php echo $entry_link; ?>"><?php echo $entry_title; ?></a><?php
									if (in_array('difficulty_level', $recipe_info)) :
										$difficulty_level = get_post_meta($entry_id, '_cp_recipe_difficulty_level', true);
										cp_difficulty_level($difficulty_level);
									endif;
								?></h2>
								<?php if (in_array('rating', $recipe_info)) : ?><div class="rating rate-<?php echo $entry_rating; ?>"></div><!-- /.rating --><?php endif; ?>
								<?php if (in_array('description', $recipe_info)) : echo wpautop($entry_description); endif; ?>
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
							</div><!-- /.cp-box-entry -->
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
						</div><!-- /.cp-box -->
					</div><!-- /.result-box -->
				</div>
			</div><?php

		endif;
	
	endif;
	
	return ob_get_clean();

}		