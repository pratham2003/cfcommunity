<?php global $post_id; if (!$post_id): $post_id = get_the_ID(); endif;
$recipe_cook_time = get_post_meta($post_id, '_cp_recipe_cook_time', true);
$recipe_cook_time *= 60;

$recipe_actions = cp_recipe_action_settings();
if(in_array('full_screen_button', $recipe_actions)) :?>
	<div class="fullscreen-actions">
		<a href="#" class="x-fs-btn"><i class="fa fa-times"></i><?php _e('close full screen','cooked'); ?></a>
		<div class="tab-links">
			<a href="#tab-ingredients"><i class="fa fa-list-ul"></i></a>
			<a class="current" href="#tab-info"><i class="fa fa-info-circle"></i></a>
			<a href="#tab-directions"><i class="fa fa-list-ol"></i></a>
		</div><!-- /.tabs-links -->
	</div><!-- /.fullscreen-actions -->
<?php endif; ?>

<div class="timer-wrap"><!-- data time is in seconds -->
	<div class="inner-wrap">
		<div id="output" class="time">00:00</div><!-- /.time -->
		<div class="timer-actions">
			<a class="pp-btn" href="start"><i class="fa fa-play"></i><i class="fa fa-pause"></i><span class="txt"></span></a>
		</div><!-- /.timer-actions -->
		<div class="timer">
			<span></span>
		</div><!-- /.timer -->
		<a class="x-timer" href="#"><i class="fa fa-times"></i></a>
	</div><!-- /.inner-wrap -->
	<audio id="readysound" src="<?php echo CP_PLUGIN_URL . '/sounds/ready.mp3'; ?>" preload="auto" controls></audio>
</div><!-- /.timer-wrap -->