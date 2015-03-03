<?php

global $post_id; if (!$post_id): $post_id = get_the_ID(); endif;

$recipe_info = cp_recipe_info_settings();

if (in_array('nutrition',$recipe_info)):

	$servingsize = get_post_meta($post_id, '_cp_recipe_nutrition_servingsize', true);
	$calories = get_post_meta($post_id, '_cp_recipe_nutrition_calories', true);
	$sodiumcontent = get_post_meta($post_id, '_cp_recipe_nutrition_sodium', true);
	$potassiumcontent = get_post_meta($post_id, '_cp_recipe_nutrition_potassium', true);
	$proteincontent = get_post_meta($post_id, '_cp_recipe_nutrition_protein', true);
	$cholesterolcontent = get_post_meta($post_id, '_cp_recipe_nutrition_cholesterol', true);
	$sugarcontent = get_post_meta($post_id, '_cp_recipe_nutrition_sugar', true);
	$fatcontent = get_post_meta($post_id, '_cp_recipe_nutrition_fat', true);
	$saturatedfatcontent = get_post_meta($post_id, '_cp_recipe_nutrition_satfat', true);
	$polyunsatfat = get_post_meta($post_id, '_cp_recipe_nutrition_polyunsatfat', true);
	$monounsatfat = get_post_meta($post_id, '_cp_recipe_nutrition_monounsatfat', true);
	$transfat = get_post_meta($post_id, '_cp_recipe_nutrition_transfat', true);
	$carbohydratecontent = get_post_meta($post_id, '_cp_recipe_nutrition_carbs', true);
	$fibercontent = get_post_meta($post_id, '_cp_recipe_nutrition_fiber', true);
	
	if ($servingsize):
		$nutrition['servingsize']['name'] = __('Serving Size','cooked');
		$nutrition['servingsize']['data'] = $servingsize;
	endif;
	
	if ($calories):
		$nutrition['calories']['name'] = __('Calories','cooked');
		$nutrition['calories']['data'] = $calories;
	endif;
	
	if ($sodiumcontent):
		$nutrition['sodiumcontent']['name'] = __('Sodium','cooked');
		$nutrition['sodiumcontent']['data'] = $sodiumcontent;
	endif;
	
	if ($potassiumcontent):
		$nutrition['potassiumcontent']['name'] = __('Potassium','cooked');
		$nutrition['potassiumcontent']['data'] = $potassiumcontent;
	endif;
	
	if ($proteincontent):
		$nutrition['proteincontent']['name'] = __('Protein','cooked');
		$nutrition['proteincontent']['data'] = $proteincontent;
	endif;
	
	if ($cholesterolcontent):
		$nutrition['cholesterolcontent']['name'] = __('Cholesterol','cooked');
		$nutrition['cholesterolcontent']['data'] = $cholesterolcontent;
	endif;
		
	if ($sugarcontent):	
		$nutrition['sugarcontent']['name'] = __('Sugar','cooked');
		$nutrition['sugarcontent']['data'] = $sugarcontent;
	endif;
		
	if ($fatcontent):	
		$nutrition['fatcontent']['name'] = __('Total Fat','cooked');
		$nutrition['fatcontent']['data'] = $fatcontent;
	endif;
	
	if ($saturatedfatcontent):	
		$nutrition['saturatedfatcontent']['name'] = __('Saturated Fat','cooked');
		$nutrition['saturatedfatcontent']['data'] = $saturatedfatcontent;
	endif;
	
	if ($polyunsatfat):	
		$nutrition['polyunsatfat']['name'] = __('Polyunsaturated Fat','cooked');
		$nutrition['polyunsatfat']['data'] = $polyunsatfat;
	endif;
		
	if ($monounsatfat):	
		$nutrition['monounsatfat']['name'] = __('Monounsaturated Fat','cooked');
		$nutrition['monounsatfat']['data'] = $monounsatfat;
	endif;
		
	if ($transfat):	
		$nutrition['transfat']['name'] = __('Trans Fat','cooked');
		$nutrition['transfat']['data'] = $transfat;
	endif;
		
	if ($carbohydratecontent):	
		$nutrition['carbohydratecontent']['name'] = __('Total Carbohydrates','cooked');
		$nutrition['carbohydratecontent']['data'] = $carbohydratecontent;
	endif;
	
	if ($fibercontent):	
		$nutrition['fibercontent']['name'] = __('Dietary Fiber','cooked');
		$nutrition['fibercontent']['data'] = $fibercontent;
	endif;
	
	if (!empty($nutrition)): ?>
	
		<div class="cookedNutritionWrap">
	
			<h2 class="fn"><?php _e('Nutrition Facts','cooked'); ?></h2>
		
			<div class="cookedNutritionBlock cookedClearFix">
				<?php foreach($nutrition as $type => $data):
				
					echo '<div class="nutrition-block">';
						echo '<span class="nutrition-block-title">'.$data['name'].'</span>';
						echo '<span class="nutrition-block-data '.$type.'">'.$data['data'].'</span>';
					echo '</div>';
				
				endforeach; ?>
			</div>
		
		</div>
		
	<?php endif;

endif;