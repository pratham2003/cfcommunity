<div id="cooked-admin-panel-container">
	<?php $post_id = isset($_GET['post']) ? $_GET['post'] : false;

	$measurements = get_terms('cp_recipe_measurement', array(
		'hide_empty' => false
	)); ?>
	
	<?php wp_nonce_field( 'cooked_save_recipe', 'cp_recipe_edit_nonce' ); ?>
	
	<div class="section-row">
		<div class="section-head">
			<h2><?php _e('Ingredients','cooked'); ?></h2>
		</div><!-- /.section-head -->
		<?php $detailed_ingredients = get_post_meta($post_id, '_cp_recipe_detailed_ingredients', true);
		if(empty($detailed_ingredients)) {
			$number_of_ingredients = 0;
		} else {
			$number_of_ingredients = count($detailed_ingredients);
		} ?>
		<ul class="cooked-admin-tabs-alt cookedClearFix">
			<li<?php echo $number_of_ingredients <= 0 ? ' class="active"' : ''; ?>><a href="#ingClassic"><?php _e('Simple Entry','cooked'); ?></a></li>
			<li<?php echo $number_of_ingredients >= 1 ? ' class="active"' : ''; ?>><a href="#ingDetailed"><i class="fa fa-list-ul"></i>&nbsp;&nbsp;<?php _e('Detailed Entry','cooked'); ?></a></li>
		</ul>

		<div id="cooked-ingClassic" class="tab-content">
			<div class="section-body section-stats">
				<div class="section-title-box">
					<p class="section-stats-first" data-plural="Sections" data-single="Section">0 <?php _e('Sections','cooked'); ?></p> /
					<p class="section-stats-second" data-plural="Ingredients" data-single="Ingredient">0 <?php _e('Ingredients','cooked'); ?></p>
				</div><!-- /.section-title-box -->
				<?php $ingredients = get_post_meta($post_id, '_cp_recipe_ingredients', true); ?>
				<textarea class="field med section-stats-field" name="_cp_recipe_ingredients" cols="0" rows="0"><?php echo $ingredients; ?></textarea>
				<p class="hint-p">
					<?php _e('Enter one ingredient per line. Use a double dash to start new section titles. (ex. --Section Title). Separate the ingredient amount from the ingredient name with double space if you want to follow Google\'s rich snippets formatting.','cooked'); ?>
					<a href="#" id="ingredients-helper" class="cp-helper"></a>
				</p><!-- /.hint-p -->
			</div><!-- /.section-body -->
		</div>

		<div id="cooked-ingDetailed" class="tab-content fields-container">
			<div class="section-fields">
				<table class="fields-templates">
					<tr class="sortable-row field-title field-row">
						<td class="button-re-order">
							<span class="fa fa-reorder"></span>
						</td>
						<td class="field-wrapper field-wrapper-section">
							<table>
								<tr>
									<td class="field-holder">
										<input type="hidden" name="" value="section" data-partial-name="[type]" />
										<input type="text" name="" value="" placeholder="<?php _e('Section title...', 'cooked'); ?>" data-partial-name="[value]" />
									</td>
									<td class="actions-holder">
										<a href="#" class="action-button remove-button fa fa-times-circle" data-action="remove" title="Remove"></a>
									</td>
								</tr>
							</table>
						</td>
					</tr>
					<tr class="sortable-row field-standard field-ingredient field-row">
						<td class="button-re-order">
							<span class="fa fa-reorder"></span>
						</td>
						<td class="field-wrapper">
							<table>
								<tr>
									<td class="field-label"><label><?php _e('Amount', 'cooked'); ?></label></td>
									<td class="field-label"><label><?php _e('Measurement', 'cooked'); ?></label></td>
									<td class="field-label"><label><?php _e('Ingredient', 'cooked'); ?></label></td>
									<td class="actions-holder">
										<a href="#" class="action-button clone-button fa fa-copy" data-action="duplicate" title="Clone"></a>
										<a href="#" class="action-button remove-button fa fa-times-circle" data-action="remove" title="Remove"></a>
									</td>
								</tr>
								<tr class="inner-fields-wrapper">
									<td class="field-one">
										<input type="hidden" name="" value="ingredient" data-partial-name="[type]" />
										<input type="text" name="" value="" data-partial-name="[amount]" />
									</td>
									<td class="field-two">
										<div class="select-style-two">
											<select name="" id="" data-placeholder="<?php _e('Choose...', 'cooked'); ?>" class="inactive" data-partial-name="[measurement]" >
												<option value=""></option>
												<?php if(!empty($measurements)) :
												
													foreach($measurements as $measurement) : ?>
														<option value="<?php echo $measurement->name; ?>"><?php echo $measurement->name; ?></option>
													<?php endforeach;
												
												endif; ?>
											</select>
										</div>
									</td>
									<td class="field-three" colspan="2">
										<input type="text" name="" value="" class="autocomplete-field" data-partial-name="[name]" />
									</td>
								</tr>
							</table>
						</td>
					</tr>
				</table>
				<table class="fields-live sortable-table" data-field-index="<?php echo $number_of_ingredients; ?>" data-name="_cp_recipe_detailed_ingredients">
					<?php if(!empty($detailed_ingredients)) :

						foreach($detailed_ingredients as $index => $entry) :

							$partial_name = '_cp_recipe_detailed_ingredients[' . $index . ']';

							switch ($entry['type']) :

								case 'ingredient': ?>
									<tr class="sortable-row field-standard field-ingredient field-row">
										<td class="button-re-order">
											<span class="fa fa-reorder"></span>
										</td>
										<td class="field-wrapper">
											<table>
												<tr>
													<td class="field-label"><label><?php _e('Amount', 'cooked'); ?></label></td>
													<td class="field-label"><label><?php _e('Measurement', 'cooked'); ?></label></td>
													<td class="field-label"><label><?php _e('Ingredient', 'cooked'); ?></label></td>
													<td class="actions-holder">
														<a href="#" class="action-button clone-button fa fa-copy" data-action="duplicate" title="Clone"></a>
														<a href="#" class="action-button remove-button fa fa-times-circle" data-action="remove" title="Remove"></a>
													</td>
												</tr>
												<tr class="inner-fields-wrapper">
													<td class="field-one">
														<input type="hidden" name="<?php echo $partial_name; ?>[type]" value="ingredient" data-partial-name="[type]" />
														<input type="text" name="<?php echo $partial_name; ?>[amount]" value="<?php echo !empty($entry['amount']) ? cp_calculate_amount($entry['amount'], 'fractional') : ''; ?>" data-partial-name="[amount]" />
													</td>
													<td class="field-two">
														<div class="select-style-two">
															<select name="<?php echo $partial_name; ?>[measurement]" id="" data-placeholder="<?php _e('Choose...', 'cooked'); ?>" data-partial-name="[measurement]" >
																<option value=""></option>
																<?php if(!empty($measurements)) :
																
																	foreach($measurements as $measurement) : ?>
																		<option<?php echo !empty($entry['measurement']) && $entry['measurement'] === $measurement->name ? ' selected="selected"' : ''; ?> value="<?php echo $measurement->name; ?>"><?php echo $measurement->name; ?></option>
																	<?php endforeach;
																
																endif; ?>
															</select>
														</div>
													</td>
													<td class="field-three" colspan="2">
														<input type="text" name="<?php echo $partial_name; ?>[name]" value="<?php echo !empty($entry['name']) ? $entry['name'] : ''; ?>" class="autocomplete-field" data-partial-name="[name]" />
													</td>
												</tr>
											</table>
										</td>
									</tr>
									<?php break;

								default: ?>
									<tr class="sortable-row field-title field-row">
										<td class="button-re-order">
											<span class="fa fa-reorder"></span>
										</td>
										<td class="field-wrapper field-wrapper-section">
											<table>
												<tr>
													<td class="field-holder">
														<input type="hidden" name="<?php echo $partial_name; ?>[type]" value="section" data-partial-name="[type]" />
														<input type="text" name="<?php echo $partial_name; ?>[value]" value="<?php echo !empty($entry['value']) ? $entry['value'] : ''; ?>" placeholder="<?php _e('Section title...', 'cooked'); ?>" data-partial-name="[value]" />
													</td>
													<td class="actions-holder">
														<a href="#" class="action-button remove-button fa fa-times-circle" data-action="remove" title="Remove"></a>
													</td>
												</tr>
											</table>
										</td>
									</tr>
									<?php break;

							endswitch;

						endforeach;

					endif; ?>
				</table>
			</div>
			<div class="section-body section-repeater-actions">
				<button class="button" data-action="duplicate" data-duplicate="field-ingredient"><?php _e('Add an Ingredient','cooked'); ?></button>&nbsp;&nbsp;<button class="button" data-action="duplicate" data-duplicate="field-title"><?php _e('Add a Section','cooked'); ?></button>
			</div><!-- /.section-body -->
		</div>

	</div><!-- /.section-row -->

	<div class="section-row">
		<div class="section-head">
			<h2><?php _e('Directions','cooked'); ?></h2>
		</div><!-- /.section-head -->
		<?php $detailed_directions = get_post_meta($post_id, '_cp_recipe_detailed_directions', true);
		if(empty($detailed_directions)) {
			$number_of_directions = 0;
		} else {
			$number_of_directions = count($detailed_directions);
		} ?>
		<ul class="cooked-admin-tabs-alt cookedClearFix">
			<li<?php echo $number_of_directions <= 0 ? ' class="active"' : ''; ?>><a href="#drClassic"><?php _e('Simple Entry','cooked'); ?></a></li>
			<li<?php echo $number_of_directions >= 1 ? ' class="active"' : ''; ?>><a href="#drDetailed"><i class="fa fa-list-ol"></i>&nbsp;&nbsp;<?php _e('Detailed Entry','cooked'); ?></a></li>
		</ul>

		<div id="cooked-drClassic" class="tab-content">
			<div class="section-body section-stats">
				<div class="section-title-box">
					<p class="section-stats-first" data-plural="Sections" data-single="Section">0 <?php _e('Sections','cooked'); ?></p> /
					<p class="section-stats-second" data-plural="Steps" data-single="Step">0 <?php _e('Steps','cooked'); ?></p>
				</div><!-- /.section-title-box -->
				<?php $directions = get_post_meta($post_id, '_cp_recipe_directions', true); ?>
				<textarea class="field large section-stats-field" name="_cp_recipe_directions" cols="0" rows="0"><?php echo $directions; ?></textarea>
				<p class="hint-p"><?php _e('Add all of the cooking steps, one per line. You can use a double dash for section titles (ex. --Section Title). You can also use the <strong>[timer length=30]30 Minutes[/timer]</strong> shortcode to add a timer link.','cooked'); ?> <a href="#" id="directions-helper" class="cp-helper"></a></p><!-- /.hint-p -->
			</div><!-- /.section-body -->
		</div>

		<div id="cooked-drDetailed" class="tab-content fields-container">
			<div class="section-fields">
				<table class="fields-templates">
					<tr class="sortable-row field-title field-row">
						<td class="button-re-order">
							<span class="fa fa-reorder"></span>
						</td>
						<td class="field-wrapper field-wrapper-section">
							<table>
								<tr>
									<td class="field-holder">
										<input type="hidden" name="" value="section" data-partial-name="[type]" />
										<input type="text" name="" value="" placeholder="<?php _e('Section title...', 'cooked'); ?>" data-partial-name="[value]" />
									</td>
									<td class="actions-holder">
										<a href="#" class="action-button remove-button fa fa-times-circle" data-action="remove" title="Remove"></a>
									</td>
								</tr>
							</table>
						</td>
					</tr>
					<tr class="sortable-row field-standard field-direction field-row">
						<td class="button-re-order">
							<span class="fa fa-reorder"></span>
						</td>
						<td class="field-wrapper field-wrapper-alt">
							<table>
								<tr>
									<td class="attachment-field cp-media-uploader-wrapper">
										<span class="attachment-droparea cp-media-action" data-window-button-label="Select direction image" data-window-label="Direction image"><i class="fa fa-file-image-o"></i></span>
										<div class="img-holder no-image">
											<input type="hidden" name="" value="" class="real-value" data-partial-name="[image_id]" />
											<span class="image-preview" data-empty_src="data:image/gif;base64,R0lGODlhAQABAAAAACH5BAEKAAEALAAAAAABAAEAAAICTAEAOw=="></span>
											<a href="#" class="x-btn">&times;</a>
										</div>
									</td>
									<td class="description-field">
										<table>
											<tr>
												<td class="field-label-alt">
													<label><?php _e('Direction', 'cooked'); ?></label>
												</td>
												<td class="actions-holder">
													<a href="#" class="action-button clone-button fa fa-copy" data-action="duplicate" title="Clone"></a>
													<a href="#" class="action-button remove-button fa fa-times-circle" data-action="remove" title="Remove"></a>
												</td>
											</tr>
											<tr>
												<td colspan="2">
													<input type="hidden" name="" value="direction" data-partial-name="[type]" />
													<textarea name="" id="" cols="30" rows="4" data-partial-name="[value]"></textarea>
												</td>
											</tr>
										</table>
									</td>
								</tr>
							</table>
						</td>
					</tr>
				</table>
				<table class="fields-live sortable-table" data-field-index="<?php echo $number_of_directions; ?>" data-name="_cp_recipe_detailed_directions">
					<?php if(!empty($detailed_directions)) :

						foreach($detailed_directions as $index => $entry) :

							$partial_name = '_cp_recipe_detailed_directions[' . $index . ']';

							switch ($entry['type']) :

								case 'direction':

									if(!empty($entry['image_id'])) {
										$img_obj = wp_get_attachment_image_src($entry['image_id'], array(103, 103));
										$image_src = $img_obj[0];
									} else {
										$image_src = 'data:image/gif;base64,R0lGODlhAQABAAAAACH5BAEKAAEALAAAAAABAAEAAAICTAEAOw==';
									} ?>
									<tr class="sortable-row field-standard field-direction field-row">
										<td class="button-re-order">
											<span class="fa fa-reorder"></span>
										</td>
										<td class="field-wrapper field-wrapper-alt">
											<table>
												<tr>
													<td class="attachment-field cp-media-uploader-wrapper">
														<span class="attachment-droparea cp-media-action" data-window-button-label="Select direction image" data-window-label="Direction image"<?php if(!empty($entry['image_id'])) : ?> style="display: none;"<?php endif; ?>><i class="fa fa-file-image-o"></i></span>
														<?php if(!empty($entry['image_id'])) : ?>
															<div class="img-holder">
															<input type="hidden" name="<?php echo $partial_name; ?>[image_id]" value="<?php echo $entry['image_id']; ?>" class="real-value" data-partial-name="[image_id]" />
														<?php else : ?>
															<input type="hidden" name="<?php echo $partial_name; ?>[image_id]" value="" class="real-value" data-partial-name="[image_id]" />
															<div class="img-holder no-image">
														<?php endif; ?>
															<span class="image-preview" style="background: url(<?php echo $image_src; ?>) no-repeat center center;" data-empty_src="data:image/gif;base64,R0lGODlhAQABAAAAACH5BAEKAAEALAAAAAABAAEAAAICTAEAOw=="></span>
															<a href="#" class="x-btn">&times;</a>
														</div>
													</td>
													<td class="description-field">
														<table>
															<tr>
																<td class="field-label-alt">
																	<label><?php _e('Direction', 'cooked'); ?></label>
																</td>
																<td class="actions-holder">
																	<a href="#" class="action-button clone-button fa fa-copy" data-action="duplicate" title="Clone"></a>
																	<a href="#" class="action-button remove-button fa fa-times-circle" data-action="remove" title="Remove"></a>
																</td>
															</tr>
															<tr>
																<td colspan="2">
																	<input type="hidden" name="<?php echo $partial_name; ?>[type]" value="direction" data-partial-name="[type]" />
																	<textarea name="<?php echo $partial_name; ?>[value]" id="" rows="4" data-partial-name="[value]"><?php echo !empty($entry['value']) ? $entry['value'] : ''; ?></textarea>
																</td>
															</tr>
														</table>
													</td>
												</tr>
											</table>
										</td>
									</tr>
									<?php break;

								default: ?>
								
									<tr class="sortable-row field-title field-row">
										<td class="button-re-order">
											<span class="fa fa-reorder"></span>
										</td>
										<td class="field-wrapper field-wrapper-section">
											<table>
												<tr>
													<td class="field-holder">
														<input type="hidden" name="<?php echo $partial_name; ?>[type]" value="section" data-partial-name="[type]" />
														<input type="text" name="<?php echo $partial_name; ?>[value]" value="<?php echo !empty($entry['value']) ? $entry['value'] : ''; ?>" placeholder="<?php _e('Section title...', 'cooked'); ?>" data-partial-name="[value]" />
													</td>
													<td class="actions-holder">
														<a href="#" class="action-button remove-button fa fa-times-circle" data-action="remove" title="Remove"></a>
													</td>
												</tr>
											</table>
										</td>
									</tr>
									<?php break;

							endswitch;

						endforeach;

					endif; ?>
				</table>
			</div>
			<div class="section-body section-repeater-actions">
				<button class="button" data-action="duplicate" data-duplicate="field-direction"><?php _e('Add a Direction','cooked'); ?></button>&nbsp;&nbsp;<button class="button" data-action="duplicate" data-duplicate="field-title"><?php _e('Add a Section','cooked'); ?></button>
			</div><!-- /.section-body -->
		</div>

	</div><!-- /.section-row -->
	
	<div class="section-row">
		<div class="section-head">
			<h2><?php _e('Recipe Video (oEmbed)','cooked'); ?></h2>
		</div><!-- /.section-head -->

		<div class="section-body">
			<?php $external_video = get_post_meta($post_id, '_cp_recipe_external_video', true); ?>
			<input type="text" class="field" name="_cp_recipe_external_video" value="<?php echo $external_video; ?>" />
			<p class="hint-p">
				<strong><?php _e('OPTIONAL:','cooked'); ?></strong> <?php _e('If you have your recipe video on Youtube, Vimeo, or','cooked'); ?> <a href="http://codex.wordpress.org/Embeds" target="_blank"><?php _e('any of the other supported oEmbed sites','cooked'); ?></a>, <?php _e('then you\'ll want to use the field above. Just paste in the URL','cooked'); ?> (<?php _e('ex','cooked'); ?>. <em>http://youtu.be/1O8D_wTCm3s</em> <?php _e('or','cooked'); ?> <em>https://vimeo.com/26140401</em>) <?php _e('and it will show up as a popup by clicking the recipe image.','cooked'); ?>
			</p><!-- /.hint-p -->
		</div><!-- /.section-body -->
	</div><!-- /.section-row -->

	<div class="section-row">
		<div class="section-head">
			<h2><?php _e('Short Description','cooked'); ?></h2>
		</div><!-- /.section-head -->

		<div class="section-body">
			<?php $short_decription = get_post_meta($post_id, '_cp_recipe_short_description', true); ?>
			<textarea class="field small" name="_cp_recipe_short_description" cols="0" rows="0"><?php echo $short_decription; ?></textarea>
		</div><!-- /.section-body -->
	</div><!-- /.section-row -->
	
	<div class="section-row">
		<div class="section-head">
			<h2><?php _e('Excerpt for List Views','cooked'); ?></h2>
		</div><!-- /.section-head -->

		<div class="section-body">
			<?php $short_decription = get_post_meta($post_id, '_cp_recipe_excerpt', true); ?>
			<textarea class="field small" name="_cp_recipe_excerpt" cols="0" rows="0"><?php echo $short_decription; ?></textarea>
			<p class="hint-p">
				<strong><?php _e('OPTIONAL:','cooked'); ?></strong> <?php _e('If you include an excerpt, it will replace your short description in the recipe list views.','cooked'); ?>
			</p><!-- /.hint-p -->
		</div><!-- /.section-body -->
	</div><!-- /.section-row -->
	
	<div class="section-row thirds">
		<div class="section-col">
			<div class="section-head">
				<h2><?php _e('Difficulty Level','cooked'); ?></h2>
			</div><!-- /.section-head -->

			<div class="section-body">
				<div class="slider difficulty" data-maxval="3">
					<?php $difficulty_level = get_post_meta($post_id, '_cp_recipe_difficulty_level', true); ?>
					<div class="amount active"><input type="text" value="" class="slider-difficulty" disabled />
						<input type="hidden" name="_cp_recipe_difficulty_level" value="<?php echo $difficulty_level; ?>" class="real-value" />
					</div><!-- /.amount -->
				</div><!-- /.slider -->
				<div class="slider-hint">
					<p class="left"><i class="fa fa-circle"></i></p><!-- /.left -->
					<p class="left-center"><i style="color:#96D437" class="fa fa-circle"></i></p><!-- /.left-center -->
					<p class="right-center"><i style="color:#F7C735" class="fa fa-circle"></i></p><!-- /.right-center -->
					<p class="right"><i style="color:#EC5A31" class="fa fa-circle"></i></p><!-- /.right -->
				</div><!-- /.slider-hint -->
			</div><!-- /.section-body -->
		</div><!-- /.section-col -->
		
		<div class="section-col">
			<div class="section-head">
				<h2><?php _e('Prep Time','cooked'); ?></h2>
			</div><!-- /.section-head -->

			<div class="section-body"><?php
					
					$max_prep_time = (get_option('cp_prep_time_max_hrs') ? get_option('cp_prep_time_max_hrs') : 12);
					$prep_time = get_post_meta($post_id, '_cp_recipe_prep_time', true);
				
				?><div class="slider time" data-maxval="<?php echo $max_prep_time*60*60; ?>">
					<div class="amount<?php if ($prep_time): echo ' active'; endif; ?>"><i class="fa fa-clock-o"></i> <input type="text" maxlength="5" value="00:00" class="slider-timer" disabled />
						<input type="hidden" name="_cp_recipe_prep_time" value="<?php echo $prep_time; ?>" class="real-value" />
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
				<h2><?php _e('Cook Time','cooked'); ?></h2>
			</div><!-- /.section-head -->

			<div class="section-body"><?php
					
					$max_cook_time = (get_option('cp_cook_time_max_hrs') ? get_option('cp_cook_time_max_hrs') : 12);
					$prep_time = get_post_meta($post_id, '_cp_recipe_prep_time', true);
				
				?><div class="slider time" data-maxval="<?php echo $max_cook_time*60*60; ?>">
					<?php $cook_time = get_post_meta($post_id, '_cp_recipe_cook_time', true); ?>
					<div class="amount<?php if ($cook_time): echo ' active'; endif; ?>"><i class="fa fa-clock-o"></i> <input type="text" maxlength="5" value="00:00" class="slider-timer" disabled />
						<input type="hidden" name="_cp_recipe_cook_time" value="<?php echo $cook_time; ?>" class="real-value" />
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
	
	<div class="section-row">
		<div class="section-head">
			<h2><?php _e('Additional Recipe Notes','cooked'); ?></h2>
		</div><!-- /.section-head -->

		<div class="section-body">
			<?php $additional_notes = get_post_meta($post_id, '_cp_recipe_additional_notes', true); ?>
			<textarea class="field small" name="_cp_recipe_additional_notes" cols="0" rows="0"><?php echo $additional_notes; ?></textarea>
			<p class="hint-p">
				<?php _e('Add any other notes like recipe source, cooking hints, etc. This section will show up under the cooking directions.','cooked'); ?>
			</p><!-- /.hint-p -->
		</div><!-- /.section-body -->
	</div><!-- /.section-row -->
	
	<div class="section-row">
		<?php $rating_by = get_option('cp_reviews_comments');
		if($rating_by == 'guest_reviews_comments') : ?>
			<div class="section-col">
				<div class="section-head">
					<h2><?php _e('Guest Rating','cooked'); ?></h2>
				</div><!-- /.section-head -->

				<div class="section-body">
					<?php if(isset($_GET['post'])) {
						$guest_rating = cp_recipe_rating($_GET['post']);
					} else {
						$guest_rating = 0;
					} ?>
					<div class="rating-holder rate-<?php echo $guest_rating; ?>">
					</div><!-- /.rating -->
					<input type="hidden" name="_cp_recipe_guest_rating" value="<?php echo $guest_rating; ?>" class="rating-real-value" />
					<p class="hint-p"><?php _e('This is the Guest rating. You can switch to Admin reviews only from the <a href="edit.php?post_type=cp_recipe&page=cooked_plugin">Cooked Settings</a> panel.','cooked'); ?></p><!-- /.hint-p -->
				</div><!-- /.section-body -->
			</div><!-- /.section-col -->
		<?php else : ?>
			<div class="section-col">
				<div class="section-head">
					<h2><?php _e('Admin Rating','cooked'); ?></h2>
				</div><!-- /.section-head -->

				<div class="section-body">
					<?php $admin_rating = get_post_meta($post_id, '_cp_recipe_admin_rating', true); ?>
					<div class="rating-holder<?php if($admin_rating) : ?> rate-<?php echo $admin_rating; endif; ?>"<?php if($admin_rating) : ?> data-rated="<?php echo $admin_rating; ?>"<?php endif; ?>>
						<span class="rate"></span>
						<span class="rate"></span>
						<span class="rate"></span>
						<span class="rate"></span>
						<span class="rate"></span>
					</div><!-- /.rating -->
					<a href="#" class="clear-rating"><?php _e('Clear Rating','cooked'); ?></a>
					<input type="hidden" name="_cp_recipe_admin_rating" value="<?php echo $admin_rating; ?>" class="rating-real-value" />
					<p class="hint-p"><?php _e('This is an Admin rating. You can switch to guest reviews and comments from the <a href="edit.php?post_type=cp_recipe&page=cooked_plugin">Cooked Settings</a> panel.','cooked'); ?></p><!-- /.hint-p -->
				</div><!-- /.section-body -->
			</div><!-- /.section-col -->
		<?php endif; ?>

		<div class="section-col">
			<div class="section-head">
				<h2><?php _e('YIELDS', 'cooked'); ?></h2>
			</div><!-- /.section-head -->
			<div class="section-body">
				<div class="field-wrap">
					<?php $yields = get_post_meta($post_id, '_cp_recipe_yields', true); ?>
					<input type="text" class="field" name="_cp_recipe_yields" value="<?php echo $yields; ?>" />
				</div><!-- /.field-wrap -->
				<p class="hint-p"><?php _e('ex. 4 Servings, 3 Cups, 6 Bowls, etc.','cooked'); ?></p><!-- /.hint-p -->
			</div><!-- /.section-body -->	
		</div><!-- /.section-col -->
	</div><!-- /.section-row -->
	
	<div class="section-row nutrition">
		<div class="section-head">
			<h2><?php _e('NUTRITION FACTS', 'cooked'); ?></h2>
		</div>
		<div class="section-row">
	
			<div class="section-col">
				<div class="section-head">
					<h3><?php _e('Serving Size', 'cooked'); ?></h3>
				</div><!-- /.section-head -->
				<div class="section-body">
					<div class="field-wrap">
						<?php $servingsize = get_post_meta($post_id, '_cp_recipe_nutrition_servingsize', true); ?>
						<input type="text" class="field" name="_cp_recipe_nutrition_servingsize" value="<?php echo $servingsize; ?>" />
					</div><!-- /.field-wrap -->
				</div><!-- /.section-body -->	
			</div><!-- /.section-col -->
	
			<div class="section-col">
				<div class="section-head">
					<h3><?php _e('Calories', 'cooked'); ?></h3>
				</div><!-- /.section-head -->
				<div class="section-body">
					<div class="field-wrap">
						<?php $calories = get_post_meta($post_id, '_cp_recipe_nutrition_calories', true); ?>
						<input type="text" class="field" name="_cp_recipe_nutrition_calories" value="<?php echo $calories; ?>" />
					</div><!-- /.field-wrap -->
				</div><!-- /.section-body -->	
			</div><!-- /.section-col -->
			
			<div class="section-col">
				<div class="section-head">
					<h3><?php _e('Total Fat', 'cooked'); ?></h3>
				</div><!-- /.section-head -->
				<div class="section-body">
					<div class="field-wrap">
						<?php $fat = get_post_meta($post_id, '_cp_recipe_nutrition_fat', true); ?>
						<input type="text" class="field" name="_cp_recipe_nutrition_fat" value="<?php echo $fat; ?>" />
					</div><!-- /.field-wrap -->
				</div><!-- /.section-body -->	
			</div><!-- /.section-col -->
			
			<div class="section-col">
				<div class="section-head">
					<h3><?php _e('Saturated Fat', 'cooked'); ?></h3>
				</div><!-- /.section-head -->
				<div class="section-body">
					<div class="field-wrap">
						<?php $satfat = get_post_meta($post_id, '_cp_recipe_nutrition_satfat', true); ?>
						<input type="text" class="field" name="_cp_recipe_nutrition_satfat" value="<?php echo $satfat; ?>" />
					</div><!-- /.field-wrap -->
				</div><!-- /.section-body -->	
			</div><!-- /.section-col -->
			
			<div class="section-col">
				<div class="section-head">
					<h3><?php _e('Polyunsaturated Fat', 'cooked'); ?></h3>
				</div><!-- /.section-head -->
				<div class="section-body">
					<div class="field-wrap">
						<?php $polyunsatfat = get_post_meta($post_id, '_cp_recipe_nutrition_polyunsatfat', true); ?>
						<input type="text" class="field" name="_cp_recipe_nutrition_polyunsatfat" value="<?php echo $polyunsatfat; ?>" />
					</div><!-- /.field-wrap -->
				</div><!-- /.section-body -->	
			</div><!-- /.section-col -->
			
			<div class="section-col">
				<div class="section-head">
					<h3><?php _e('Monounsaturated Fat', 'cooked'); ?></h3>
				</div><!-- /.section-head -->
				<div class="section-body">
					<div class="field-wrap">
						<?php $monounsatfat = get_post_meta($post_id, '_cp_recipe_nutrition_monounsatfat', true); ?>
						<input type="text" class="field" name="_cp_recipe_nutrition_monounsatfat" value="<?php echo $monounsatfat; ?>" />
					</div><!-- /.field-wrap -->
				</div><!-- /.section-body -->	
			</div><!-- /.section-col -->
			
			<div class="section-col">
				<div class="section-head">
					<h3><?php _e('Trans Fat', 'cooked'); ?></h3>
				</div><!-- /.section-head -->
				<div class="section-body">
					<div class="field-wrap">
						<?php $transfat = get_post_meta($post_id, '_cp_recipe_nutrition_transfat', true); ?>
						<input type="text" class="field" name="_cp_recipe_nutrition_transfat" value="<?php echo $transfat; ?>" />
					</div><!-- /.field-wrap -->
				</div><!-- /.section-body -->	
			</div><!-- /.section-col -->
			
			<div class="section-col">
				<div class="section-head">
					<h3><?php _e('Cholesterol', 'cooked'); ?></h3>
				</div><!-- /.section-head -->
				<div class="section-body">
					<div class="field-wrap">
						<?php $cholesterol = get_post_meta($post_id, '_cp_recipe_nutrition_cholesterol', true); ?>
						<input type="text" class="field" name="_cp_recipe_nutrition_cholesterol" value="<?php echo $cholesterol; ?>" />
					</div><!-- /.field-wrap -->
				</div><!-- /.section-body -->	
			</div><!-- /.section-col -->
			
			<div class="section-col">
				<div class="section-head">
					<h3><?php _e('Sodium', 'cooked'); ?></h3>
				</div><!-- /.section-head -->
				<div class="section-body">
					<div class="field-wrap">
						<?php $sodium = get_post_meta($post_id, '_cp_recipe_nutrition_sodium', true); ?>
						<input type="text" class="field" name="_cp_recipe_nutrition_sodium" value="<?php echo $sodium; ?>" />
					</div><!-- /.field-wrap -->
				</div><!-- /.section-body -->	
			</div><!-- /.section-col -->
			
			<div class="section-col">
				<div class="section-head">
					<h3><?php _e('Potassium', 'cooked'); ?></h3>
				</div><!-- /.section-head -->
				<div class="section-body">
					<div class="field-wrap">
						<?php $potassium = get_post_meta($post_id, '_cp_recipe_nutrition_potassium', true); ?>
						<input type="text" class="field" name="_cp_recipe_nutrition_potassium" value="<?php echo $potassium; ?>" />
					</div><!-- /.field-wrap -->
				</div><!-- /.section-body -->	
			</div><!-- /.section-col -->
			
			<div class="section-col">
				<div class="section-head">
					<h3><?php _e('Total Carbohydrate', 'cooked'); ?></h3>
				</div><!-- /.section-head -->
				<div class="section-body">
					<div class="field-wrap">
						<?php $carbs = get_post_meta($post_id, '_cp_recipe_nutrition_carbs', true); ?>
						<input type="text" class="field" name="_cp_recipe_nutrition_carbs" value="<?php echo $carbs; ?>" />
					</div><!-- /.field-wrap -->
				</div><!-- /.section-body -->	
			</div><!-- /.section-col -->
			
			<div class="section-col">
				<div class="section-head">
					<h3><?php _e('Fiber', 'cooked'); ?></h3>
				</div><!-- /.section-head -->
				<div class="section-body">
					<div class="field-wrap">
						<?php $fiber = get_post_meta($post_id, '_cp_recipe_nutrition_fiber', true); ?>
						<input type="text" class="field" name="_cp_recipe_nutrition_fiber" value="<?php echo $fiber; ?>" />
					</div><!-- /.field-wrap -->
				</div><!-- /.section-body -->	
			</div><!-- /.section-col -->
			
			<div class="section-col">
				<div class="section-head">
					<h3><?php _e('Sugar', 'cooked'); ?></h3>
				</div><!-- /.section-head -->
				<div class="section-body">
					<div class="field-wrap">
						<?php $sugar = get_post_meta($post_id, '_cp_recipe_nutrition_sugar', true); ?>
						<input type="text" class="field" name="_cp_recipe_nutrition_sugar" value="<?php echo $sugar; ?>" />
					</div><!-- /.field-wrap -->
				</div><!-- /.section-body -->	
			</div><!-- /.section-col -->
			
			<div class="section-col">
				<div class="section-head">
					<h3><?php _e('Protein', 'cooked'); ?></h3>
				</div><!-- /.section-head -->
				<div class="section-body">
					<div class="field-wrap">
						<?php $protein = get_post_meta($post_id, '_cp_recipe_nutrition_protein', true); ?>
						<input type="text" class="field" name="_cp_recipe_nutrition_protein" value="<?php echo $protein; ?>" />
					</div><!-- /.field-wrap -->
				</div><!-- /.section-body -->	
			</div><!-- /.section-col -->
			
		</div><!-- /.section-row -->
	</div>

</div>