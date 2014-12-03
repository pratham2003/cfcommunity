<?php
/**
 * The Sidebar containing the main widget areas.
 *
 * @package Alto
 */
?>

<?php if ( is_dynamic_sidebar() ) { ?>

  <div id="secondary" class="sidebar widget-area" role="complementary">

    <?php do_action( 'before_sidebar' ); ?>

    <?php dynamic_sidebar( 'sidebar-1' ); ?>

  </div> <!-- end #secondary -->

<?php } ?>