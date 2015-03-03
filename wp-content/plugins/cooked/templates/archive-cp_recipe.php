<?php get_header();

	global $archive_args;
		
	$tax = get_queried_object();
	
	switch($tax->taxonomy):
	
		case 'cp_recipe_cooking_method' : 
			
			$tax_title = __('Cooking Method','cooked');
			$archive_args = cp_search_args(null,null,$tax->term_id,null);
			break;
		
		case 'cp_recipe_category' : 
			
			$tax_title = __('Category','cooked');
			$archive_args = cp_search_args($tax->term_id,null,null,null);
			break;
			
		case 'cp_recipe_cuisine' : 
			
			$tax_title = __('Cuisine','cooked');
			$archive_args = cp_search_args(null,$tax->term_id,null,null);
			break;
			
		case 'cp_recipe_tags' : 
			
			$tax_title = __('Recipe Tag','cooked');
			$archive_args = cp_search_args(null,null,null,$tax->slug);
			break;
			
	endswitch;
	
	echo '<div class="cookedPageWrapper">';
	
		echo '<div class="archiveTitleDesc">';
			echo '<h1><span style="font-weight:300">'.$tax_title.':</span> '.$tax->name.'</h1>';
			if ($tax->description):
				echo '<p>'.$tax->description.'</p>';
			endif;
		echo '</div>';
	
		$list_view = get_option('cp_recipe_list_view');
		if(file_exists(CP_PLUGIN_VIEWS_DIR . $list_view . '.php')) {
			load_template(CP_PLUGIN_VIEWS_DIR . $list_view . '.php');
		}
		
	echo '</div>';
	
get_footer(); ?>