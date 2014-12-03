<?php
/**
* Custom Widget Definitions & Default Widget Overrides
*
* @package Alto
*/

/**
* Hours & Information
*
* Allows user's to share contact information, the location of their business, and hours of operation.
*/

add_action( 'widgets_init', 'alto_register_hours_information' );

function alto_register_hours_information() {
  register_widget( 'alto_hours_information' );
}

class alto_hours_information extends WP_Widget {

  function alto_hours_information() {

    $widget_options = array(
      'classname' => 'widget_hours_information',
      'description' => __( 'Share the location of your business, contact information, and your hours of operation.', 'alto' )
      );

    $control_options = array(
      'width' => 300,
      'height' => 350,
      'id_base' => 'hours-information'
      );

    $this->WP_Widget( 'hours-information', __('Hours & Information', 'alto'), $widget_options, $control_options );

  }

  function update( $new_instance, $old_instance ) {
    $instance              = $old_instance;
    $instance['title']     = strip_tags( $new_instance['title'] );
    $instance['address']   = strip_tags( $new_instance['address'] );
    $instance['show_map']  = $new_instance['show_map'];
    $instance['telephone'] = strip_tags( $new_instance['telephone'] );
    $instance['hours']     = strip_tags( $new_instance['hours'] );
    return $instance;
  }

  function widget( $args, $instance ) {

    extract( $args );

    $title     = apply_filters('widget_title', $instance['title'] );
    $address   = $instance['address'];
    $show_map  = isset( $instance['show_map'] ) ? true : false;
    $telephone = $instance['telephone'];
    $hours     = $instance['hours'];

    echo $before_widget;

    if ( $title )
      echo $before_title . $title . $after_title;

    if ( $show_map )
      printf( '<iframe class="google-maps-embed" height="250" frameborder="0" scrolling="no" marginheight="0" marginwidth="0" src="https://maps.google.com/maps?q=%1$s&output=embed&z=16&iwloc=near"></iframe>', $address );

    if ( $address )
      printf( '<p>' . __('<strong>%1$s</strong> <span>%2$s</span>', 'alto') . '</p>', 'Address:', $address );

    if ( $telephone )
      printf( '<p>' . __('<strong>%1$s</strong> <span>%2$s</span>', 'alto') . '</p>', 'Telephone:', $telephone );

    if ( $hours )
      printf( '<p>' . __('<strong>%1$s</strong> <span>%2$s</span>', 'alto') . '</p>', 'Hours:', $hours );

    echo $after_widget;

  }

  function form( $instance ) {

    $defaults = array(
      'title'     => __( 'Hours & Information', 'alto' ),
      'show_map'  => true,
      'address'   => '',
      'telephone' => '',
      'hours'     => '',
      );

    $instance = wp_parse_args( (array) $instance, $defaults );

    //Widget Title: Text Input.

    ?>

    <p>
      <label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e('Title:', 'alto'); ?></label>
      <input id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" value="<?php echo $instance['title']; ?>" style="width:100%;" />
    </p>

    <p>
      <input class="checkbox" type="checkbox" <?php checked( isset( $instance['show_map'] ) ? true : false ); ?> id="<?php echo $this->get_field_id( 'show_map' ); ?>" name="<?php echo $this->get_field_name( 'show_map' ); ?>" />
      <label for="<?php echo $this->get_field_id( 'show_map' ); ?>"><?php _e('Show map?', 'alto'); ?></label>
    </p>

    <p>
      <label for="<?php echo $this->get_field_id( 'address' ); ?>"><?php _e('Address:', 'alto'); ?></label>
      <input id="<?php echo $this->get_field_id( 'address' ); ?>" name="<?php echo $this->get_field_name( 'address' ); ?>" value="<?php echo $instance['address']; ?>" style="width:100%;" />
    </p>

    <p>
      <label for="<?php echo $this->get_field_id( 'telephone' ); ?>"><?php _e('Telephone Number:', 'alto'); ?></label>
      <input id="<?php echo $this->get_field_id( 'telephone' ); ?>" name="<?php echo $this->get_field_name( 'telephone' ); ?>" value="<?php echo $instance['telephone']; ?>" style="width:100%;" />
    </p>

    <p>
      <label for="<?php echo $this->get_field_id( 'hours' ); ?>"><?php _e('Hours:', 'alto'); ?></label>
      <input id="<?php echo $this->get_field_id( 'hours' ); ?>" name="<?php echo $this->get_field_name( 'hours' ); ?>" value="<?php echo $instance['hours']; ?>" style="width:100%;" />
    </p>

    <?php

  }

}

/**
* Instagram Cycle Widget
*
* Pulls in the user's Instagram feed and displays in a custom widget.
* This widget uses the Instagram Cycle plugin located in
* /scripts/instagram-cycle.js file.
*/

add_action( 'widgets_init', 'alto_register_instagram' );

function alto_register_instagram() {
  register_widget( 'alto_instagram' );
}

class alto_instagram extends WP_Widget {

  function alto_instagram() {

    $widget_options = array(
      'classname' => 'widget_instagram',
      'description' => __( 'Connect to and share photos from your Instagram account.', 'alto' )
      );

    $control_options = array(
      'width' => 300,
      'height' => 350,
      'id_base' => 'instagram'
      );

    $this->WP_Widget( 'instagram', __('Instagram', 'alto'), $widget_options, $control_options );

  }

  function update( $new_instance, $old_instance ) {
    $instance = $old_instance;
    $instance['title'] = strip_tags( $new_instance['title'] );
    $instance['instagram_token'] = strip_tags( $new_instance['instagram_token'] );
    return $instance;
  }

  function widget( $args, $instance ) {

    extract( $args );

    $title = apply_filters('widget_title', $instance['title'] );
    $instagram_token = $instance['instagram_token'];
    $before_instagram = "<div class='instagram-cycle'>";
    $after_instagram = "</div>";

    echo $before_widget;

    echo $before_title . $title . $after_title;

    echo $before_instagram;

    echo "<div class='instagram-cycle-toggle'><i class='icon-alto-iconfont_Right-angle-arrow'></i></div>";
    echo "<div class='panes'>";

    if ( $instagram_token ) {

      $count = 18;
      $display_size = "thumbnail";
      $result = fetch_data("https://api.instagram.com/v1/users/self/media/recent/?access_token={$instagram_token}&count={$count}");
      $photos = json_decode($result);
      $chunks = array_chunk( $photos->data, 6 );
      $panes  = 1;
      $index  = count( $chunks ) * 1000;
      $offset = $index;

      foreach( $chunks as $chunk ) {

        if ( $chunk === reset( $chunks ) ) {

          echo "<div class='pane pane-{$panes} current' style='z-index: {$index};'><ul class='image-list'>";

          foreach( $chunk as $photo ) {
            $img = $photo->images->{$display_size};
            if ( isset( $photo->caption->text ) ) {
              $caption = $photo->caption->text;
            } else {
              $caption = __( 'Instagram photo.', 'alto' );
            }
            $link = esc_url( $photo->link );
            $url  = esc_url( $img->url );
            echo "<li class='unused'><a href='{$link}'><img src='{$url}' alt='{$caption}' /></a></li>";
          }

        } else {

          echo "<div class='pane pane-{$panes}' style='z-index: {$offset};'><ul class='image-list'>";

          foreach( $chunk as $photo ) {
            $img = $photo->images->{$display_size};
            if ( isset( $photo->caption->text ) ) {
              $caption = $photo->caption->text;
            } else {
              $caption = __( 'Instagram photo.', 'alto' );
            }
            $link = esc_url( $photo->link );
            $url  = esc_url( $img->url );
            echo "<li class='used'><a href='{$link}'><img src='{$url}' alt='{$caption}' style='opacity: 0;' /></a></li>";
          }

        }

        echo "</ul></div>";
        $panes++;
        $offset = $offset - 1000;
      }

    } else {
      _e( '<p>No Instagram photos found.</p>' );
    }

    // I'm not crazy about this, but...

    echo "</div>";
    echo "<div class='placeholder'>"
       . "<ul>"
       . "<li></li>"
       . "<li></li>"
       . "<li></li>"
       . "<li></li>"
       . "<li></li>"
       . "<li></li>"
       . "</div>";

    echo $after_instagram;
    echo $after_widget;

  }

  function form( $instance ) {

    $defaults = array(
      'title'           => __( 'Instagram', 'alto' ),
      'instagram_token' => ''
      );

    $instance = wp_parse_args( (array) $instance, $defaults );

    ?>

    <p>
      <label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e('Title:', 'alto'); ?></label>
      <input id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" value="<?php echo $instance['title']; ?>" style="width:100%;" />
    </p>

    <p>
      <label for="<?php echo $this->get_field_id( 'instagram_token' ); ?>"><?php _e('Instagram Access Token (<a href="http://blog.pixelunion.net/instagram" target="_blank">You can generate this here</a>):', 'alto'); ?></label>
      <input id="<?php echo $this->get_field_id( 'instagram_token' ); ?>" name="<?php echo $this->get_field_name( 'instagram_token' ); ?>" value="<?php echo $instance['instagram_token']; ?>" style="width:100%;" />
    </p>

    <?php

  }

}