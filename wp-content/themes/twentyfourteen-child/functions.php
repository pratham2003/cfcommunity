<?php
function remove_admin_bar() {
if (!current_user_can('administrator') && !is_admin()) {
  show_admin_bar(false);
}
}

if ( !defined( 'BP_AVATAR_THUMB_WIDTH' ) ) {
  define( 'BP_AVATAR_THUMB_WIDTH', 150 );
}

if ( !defined( 'BP_AVATAR_THUMB_HEIGHT' ) ) {
  define( 'BP_AVATAR_THUMB_HEIGHT', 150 );
}

if ( !defined( 'BP_AVATAR_FULL_WIDTH' ) ) {
  define( 'BP_AVATAR_FULL_WIDTH', 150 );
}

if ( !defined( 'BP_AVATAR_FULL_HEIGHT' ) ) {
  define( 'BP_AVATAR_FULL_HEIGHT', 150 );
}

/**
 * Do Something
 */
function infinity_slider_insert()
{
	// load template for the slider ?>
<!-- AddThis Smart Layers BEGIN -->
<!-- Go to http://www.addthis.com/get/smart-layers to customize -->
<script type="text/javascript" src="//s7.addthis.com/js/300/addthis_widget.js#pubid=xa-52a458b573df51db"></script>
<script type="text/javascript">

  addthis.layers({
    'theme' : 'transparent',
    'share' : {
      'position' : 'right',
      'numPreferredServices' : 5
    }, 
    'follow' : {
      'services' : []
    }   
  });
</script>
<!-- AddThis Smart Layers END --><?php
}
add_action( 'wp_footer', 'infinity_slider_insert' );
?>