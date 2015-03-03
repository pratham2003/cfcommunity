<?php

add_action('admin_init', 'cp_admin_ajax_callbacks', 11);
function cp_admin_ajax_callbacks() {
	
	if (current_user_can('manage_options') && isset($_GET['action']) && $_GET['action'] == 'delete_recipe' && isset($_GET['recipe_id']) && isset($_GET['recipe_id']))
	{		
		
		$recipe_id = $_GET['recipe_id'];
		wp_delete_post($recipe_id,true);
		exit;
		
	}
	
	if (current_user_can('manage_options') && isset($_GET['action']) && $_GET['action'] == 'approve_recipe' && isset($_GET['recipe_id']) && isset($_GET['recipe_id']))
	{		
		$recipe_id = $_GET['recipe_id'];
		$this_recipe = array(
			'ID'          => $recipe_id,
		    'post_status' => 'publish'
		);
		
		wp_update_post( $this_recipe );
		
		$recipe = get_post($recipe_id);
		$user_id = $recipe->post_author;
		$recipe_name = $recipe->post_title;
		$recipe_link = get_permalink($recipe_id);
		$recipe_link = '<a href="'.$recipe_link.'">'.$recipe_link.'</a>';
		
		// Send an email to the user?
		$email_content = get_option('cooked_approval_email_content');
		$email_subject = get_option('cooked_approval_email_subject');
		if ($email_content && $email_subject):
			$user_name = get_user_meta( $user_id, 'first_name', true );
			$user_data = get_userdata( $user_id );
			$email = $user_data->user_email;
			$tokens = array('%name%','%recipename%','%recipelink%');
			$replacements = array($user_name,$recipe_name,$recipe_link);
			$email_content = str_replace($tokens,$replacements,$email_content);
			$email_subject = str_replace($tokens,$replacements,$email_subject);
			cooked_mailer( $email, $email_subject, $email_content );
		endif;
		
		exit;
	}	
	
}