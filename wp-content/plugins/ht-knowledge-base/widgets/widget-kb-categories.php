<?php


// Widget class.
class HT_KB_Categories_Widget extends WP_Widget {


	/*--------------------------------------------------*/
	/* Constructor
	/*--------------------------------------------------*/

	/**
	* Specifies the classname and description, instantiates the widget,
	* loads localization files, and includes necessary stylesheets and JavaScript.
	*/
	public function __construct() {

	// set classname and description
	parent::__construct(
	'ht-kb-categories-widget',
	__( 'Knowledge Base Categories', 'ht-knowledge-base' ),
	array(
	'classname'	=>	'HT_KB_Categories_Widget',
	'description'	=>	__( 'A widget for displaying Knowledge Base categories', 'ht-knowledge-base' )
	)
	);

	} // end constructor


	/*-----------------------------------------------------------------------------------*/
	/*	Display Widget
	/*-----------------------------------------------------------------------------------*/
		
		function widget( $args, $instance ) {
			extract( $args );
			
			$title = apply_filters('widget_title', $instance['title'] );

			/* Before widget (defined by themes). */
			echo $before_widget;

			/* Display Widget */
			?> 
	        <?php /* Display the widget title if one was input (before and after defined by themes). */
					if ( $title )
						echo $before_title . $title . $after_title;
					?>
	                            
	                <?php
	                	$args = array(
						    'hide_empty'    => 0,
							'child_of' 		=> 0,
							'pad_counts' 	=> 1,
							'hierarchical'	=> 1,
							'orderby' => 'name',
						  	'order' => 'ASC'
						); 

						$categories = get_terms('ht_kb_category', $args);
						
						echo '<ul>';
						 foreach($categories as $category) { 
						    echo '<li><span>'. $category->count . '</span><a href="' . get_term_link( $category ) . '" title="' . sprintf( __( 'View all posts in %s', 'ht-knowledge-base' ), $category->name ) . '" ' . '>' . $category->name.'</a></li> ';
						 } 
						echo '</ul>';
						?>
								
								<?php

								/* After widget (defined by themes). */
								echo $after_widget;
		}


	/*-----------------------------------------------------------------------------------*/
	/*	Update Widget
	/*-----------------------------------------------------------------------------------*/
		
		function update( $new_instance, $old_instance ) {
			
			$instance = $old_instance;
			
			/* Strip tags to remove HTML (important for text inputs). */
			$instance['title'] = strip_tags( $new_instance['title'] );

			/* No need to strip tags for.. */

			return $instance;
		}
		

	/*-----------------------------------------------------------------------------------*/
	/*	Widget Settings
	/*-----------------------------------------------------------------------------------*/
		 
		function form( $instance ) {

			/* Set up some default widget settings. */
			$defaults = array(
			'title' => __( 'Knowledge Base Categories', 'ht-knowledge-base' )		
			);
			$instance = wp_parse_args( (array) $instance, $defaults ); ?>
			
	        <!-- Widget Title: Text Input -->
			<p>
				<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e('Title:', 'ht-knowledge-base') ?></label>
				<input  type="text" class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" value="<?php echo $instance['title']; ?>" />
			</p>
		
			<?php
		}
} //end class


// Remember to change 'Widget_Name' to match the class name definition
add_action( 'widgets_init', create_function( '', 'register_widget("HT_KB_Categories_Widget");' ) );
