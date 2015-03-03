<?php global $post_id; if (!$post_id): $post_id = get_the_ID(); endif; ?>

<h2><?php _e('Directions','cooked'); ?></h2><?php
	
$directions = get_post_meta($post_id, '_cp_recipe_detailed_directions',true);
if (!empty($directions)):
	cp_format_content($directions,'directions',true);
else :
	$directions = get_post_meta($post_id, '_cp_recipe_directions', true);
	cp_format_content($directions,'directions');
endif;

$recipe_info = cp_recipe_info_settings();
if (in_array('notes',$recipe_info)):

	$additional_notes = get_post_meta($post_id, '_cp_recipe_additional_notes', true);
	if ($additional_notes):
		echo '<div class="recipe-notes">';
		echo wpautop(do_shortcode($additional_notes));
		echo '</div>';
	endif;
	
endif;