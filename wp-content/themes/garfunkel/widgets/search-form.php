<?php 

class garfunkel_search_form extends WP_Widget {

	function garfunkel_search_form() {
		parent::WP_Widget(false, $name = 'Search form', array('description' => __('Displays a search form.', 'garfunkel') ));	
	}
	
	function widget($args, $instance) {
	
		// Outputs the content of the widget
		extract($args); // Make before_widget, etc available.
		
		$widget_title = apply_filters('widget_title', $instance['widget_title']);
		
		echo $before_widget;
		
		
		if (!empty($widget_title)) {
		
			echo $before_title . $widget_title . $after_title;
			
		} ?>
		
		<?php get_search_form(); ?>
					
		<?php echo $after_widget; 
	}
	
	
	function update($new_instance, $old_instance) {
	
		//update and save the widget
		return $new_instance;
		
	}
	
	function form($instance) {
	
		// Get the options into variables, escaping html characters on the way
		$widget_title = $instance['widget_title'];
		?>
		
		<p>
			<label for="<?php echo $this->get_field_id('widget_title'); ?>"><?php  _e('Title', 'garfunkel'); ?>:
			<input id="<?php echo $this->get_field_id('widget_title'); ?>" name="<?php echo $this->get_field_name('widget_title'); ?>" type="text" class="widefat" value="<?php echo $widget_title; ?>" /></label>
		</p>
						
		
		<?php
	}
}
register_widget('garfunkel_search_form'); ?>