<?php global $post_id; if (!$post_id): $post_id = get_the_ID(); endif; ?>
<h2><?php _e('Ingredients','cooked'); ?></h2><?php
	
$ingredients = get_post_meta($post_id, '_cp_recipe_detailed_ingredients',true);
if (!empty($ingredients)):
	cp_format_content($ingredients,'ingredients',true);
else :
	$ingredients = get_post_meta($post_id, '_cp_recipe_ingredients', true);
	cp_format_content($ingredients);
endif;