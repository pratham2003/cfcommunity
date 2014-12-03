<?php
/**
 * The is the template for displaying the footer.
 *
 * Contains the closing of the #content div and all content after
 *
 * @package Alto
 */
?>

  </div><!-- #content -->

  <footer id="colophon" class="site-footer" role="contentinfo">

    <?php $social_icons = array(
      'bandcamp',
      'behance',
      'delicious',
      'deviantart',
      'digg',
      'dribbble',
      'etsy',
      'facebook',
      'flickr',
      'foursquare',
      'github',
      'google-plus',
      'instagram',
      'lastfm',
      'linkedin',
      'myspace',
      'pinboard',
      'pinterest',
      'rdio',
      'skype',
      'soundcloud',
      'spotify',
      'stumbleupon',
      'svpply',
      'twitter',
      'vimeo',
      'youtube',
      );
    ?>

    <?php // Run a count of the "active" social icons to see if we should display the wrapper div. ?>

    <?php $active = 0; ?>
    <?php foreach( $social_icons as $icon ) {
      if ( get_theme_mod( $icon ) ) { $active++; }
    } ?>

    <?php if ( $active > 0 ) { ?>
      <div class="social-media">

        <ul class="social-icons">
        <?php foreach ( $social_icons as $icon ) {
          if ( get_theme_mod( $icon ) ) : ?>
            <li>
              <a class="social-icon" href="<?php if ( $icon == 'skype' ) { ?>skype:<?php echo get_theme_mod( $icon ); ?>?userinfo<?php } else { ?><?php echo esc_url( get_theme_mod( $icon ) ); ?><?php } ?>" target="_blank">
                <i class="icon-alto-iconfont_<?php echo esc_html( $icon ); ?>"></i>
              </a>
            </li>
          <?php endif;
        } ?>
        </ul> <!-- end .social-icons -->

      </div> <!-- end .social-media -->
    <?php } ?>

    <div class="site-info">
      <?php do_action( 'alto_credits' ); ?>
      <p><?php _e( 'Proudly powered by ', 'alto' ); ?><a href="<?php echo esc_url( __( 'http://wordpress.org/', 'alto' ) ); ?>"><?php printf( __( '%s', 'alto' ), 'WordPress' ); ?></a></p>
      <p><?php printf( __( '%1$s by %2$s.', 'alto' ), '<a href="http://theme.wordpress.com/themes/alto/">Alto</a>', '<a href="http://pixelunion.net">Pixel Union</a>' ); ?></p>
    </div> <!-- end .site-info -->

  </footer> <!-- end #colophon -->

</div> <!-- end #page -->

<?php wp_footer(); ?>

</body>
</html>
