<?php
function load_fonts() {
        wp_register_style('googleFonts', 'http://fonts.googleapis.com/css?family=Lato:400,700|Merriweather:400,700');
        wp_enqueue_style( 'googleFonts');
    }

add_action('wp_print_styles', 'load_fonts');

add_action('init', 'remove_admin_bar');

function remove_admin_bar() {
if (!current_user_can('administrator') && !is_admin()) {
  show_admin_bar(false);
}
}

/**
 * Do Something
 */
function infinity_slider_insert_2()
{
  // load template for the slider
  if ( is_singular() ): ?>
<!-- AddThis Smart Layers BEGIN -->
<div id="fb-root"></div>
<script>(function(d, s, id) {
  var js, fjs = d.getElementsByTagName(s)[0];
  if (d.getElementById(id)) return;
  js = d.createElement(s); js.id = id;
  js.src = "//connect.facebook.net/en_US/all.js#xfbml=1&appId=307736899355495";
  fjs.parentNode.insertBefore(js, fjs);
}(document, 'script', 'facebook-jssdk'));</script>
<!-- AddThis Smart Layers END --><?php
  endif;
}
add_action( 'wp_head', 'infinity_slider_insert_2' );

/**
 * Do Something
 */
function infinity_slider_insert()
{
	// load template for the slider
	if ( is_singular() ): ?>
<!-- AddThis Smart Layers BEGIN -->
<!-- Go to http://www.addthis.com/get/smart-layers to customize -->
<script type="text/javascript" src="//s7.addthis.com/js/300/addthis_widget.js#pubid=xa-52a458b573df51db"></script>
<script type="text/javascript">
  addthis.layers({
    'theme' : 'transparent',
    'share' : {
      'position' : 'left',
      'numPreferredServices' : 5
    }, 
    'follow' : {
      'services' : []
    }   
  });
</script>
<!-- AddThis Smart Layers END --><?php
	endif;
}
add_action( 'wp_footer', 'infinity_slider_insert' );
?>