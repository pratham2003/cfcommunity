<?php
/**
 * The template for displaying search forms in Alto.
 *
 * @package Alto
 */
?>

<form role="search" method="get" class="search-form" action="<?php echo esc_url( home_url( '/' ) ); ?>">
  
  <label>
    <span class="screen-reader-text"><?php _ex( 'Search for:', 'label', 'alto' ); ?></span>
    <input type="text" class="search-field" placeholder="<?php echo esc_attr_x( 'Search for stuff', 'placeholder', 'alto' ); ?>" value="<?php echo esc_attr( get_search_query() ); ?>" name="s">
  </label>
  
  <div class="search-cap"><i class="icon-alto-iconfont_Search"></i></div>
  
  <input type="submit" class="search-submit" value="<?php echo esc_attr_x( 'Search', 'submit button', 'alto' ); ?>">

</form> <!-- end .search-form -->