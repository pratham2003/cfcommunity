
		<?php
			do_action( 'open_sidebar' );?>
			
		<?php	
			// Load Sidebars
			infinity_base_sidebars();
			do_action( 'close_sidebar' );
		?>
	
	<?php
		do_action( 'after_sidebar' );
	?>