<div class="jumbotron no-margin-top">

  <div class="container">

    <div class="row">
      <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
        <h2 class="section-title page-top">
        <span><?php _e('Your gift will help those affected by Cystic Fibrosis', 'cfcommunity'); ?></span>
        </h2>
      </div>

      <div class="col-sm-6 col-md-6 col-lg-6 col-xs-12">
        <p class="lead">
          <span>
            <?php _e('CFCommunity is an online meeting place created by people with CF, for people with CF.', 'cfcommunity'); ?>   

            <br><br>
            <?php _e('We want to make it easy for those who live or work with CF everyday to connect.', 'cfcommunity'); ?>   

            <br><br>
            <?php _e('Learn more about us or...', 'cfcommunity'); ?>  </p>
            <a href="<?php echo bp_get_signup_page()?>" class="btn-block btn btn-success" type="button"><i class="fa fa-user"></i> <?php _e('Sign up for CFCommunity', 'cfcommunity'); ?> </a>
          </span>
        </p>
      </div>

      <div class="col-sm-6 col-md-6 col-lg-6 col-xs-12">
        <div class="container-video ">
          <a href="https://www.youtube.com/watch?v=7gtdpnKbT10" target="_self" class="litebox">
            <img class="img-responsive" src="<?php echo get_stylesheet_directory_uri(); ?>/assets/img/video.png" alt="cc-license" title="cc-license" />  
          </a>
        </div>
      </div>
    </div>

  </div>

</div>

<div class="container">
  <div class="content row row-offcanvas row-offcanvas-left">
    <div class="main col-xs-12 col-sm-12" role="main">


      <div class="row">
        <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
          <h2 class="section-title grey"><?php _e('What role does Cystic Fibrosis play in your life?', 'cfcommunity'); ?></h2>
        </div>
      </div>

      <div class="row margin-top-20">


<?php
$product_args = array(
  'post_type'   => 'download'
);
$products = new WP_Query( $product_args );

?>

<div id="edd-grid" class="content-area">
  <div id="store-front">
  <?php if ( $products->have_posts() ) : $i = 1; ?>
    <div class="store-info">
      <?php if ( get_theme_mod( 'bwpy_edd_store_archives_title' ) ) : ?>
        <h1 class="store-title"><?php echo get_theme_mod( 'bwpy_edd_store_archives_title' ); ?></h1>
      <?php endif; ?>
      <?php if ( get_theme_mod( 'bwpy_edd_store_archives_description' ) ) : ?>
        <div class="store-description">
          <?php echo wpautop( get_theme_mod( 'bwpy_edd_store_archives_description' ) ); ?>
        </div>
      <?php endif; ?>
    </div>
    <div class="product-grid clear">
      <?php while ( $products->have_posts() ) : $products->the_post(); ?>
        
        <div class="threecol product">
          <div class="product-image">
            <?php if ( has_post_thumbnail() ) { ?>
              <a href="<?php the_permalink(); ?>">
                <?php the_post_thumbnail( 'product-img' ); ?>
              </a>
            <?php } ?>
          </div>
          <div class="product-description">
            <a class="product-title" href="<?php the_permalink(); ?>">
              <?php the_title( '<h3>', '</h3>' ); ?>
            </a>
            <?php if ( get_theme_mod( 'bwpy_download_description' ) != 1 ) : // show downloads description? ?>
              <div class="product-info">
                <?php the_excerpt(); ?>
              </div>
            <?php endif; ?>
            <?php if ( get_theme_mod( 'bwpy_product_view_details' ) ) : ?>
              <a class="view-details" href="<?php the_permalink(); ?>"><?php echo get_theme_mod( 'bwpy_product_view_details' ); ?></a>
            <?php endif; ?>
          </div>
        </div>
  
        <?php $i+=1; ?>
      <?php endwhile; ?>
    </div>      
    <div class="store-pagination">
      <?php           
        $big = 999999999; // need an unlikely intege          
        echo paginate_links( array(
          'base' => str_replace( $big, '%#%', esc_url( get_pagenum_link( $big ) ) ),
          'format' => '?paged=%#%',
          'current' => max( 1, $current_page ),
          'total' => $products->max_num_pages
        ) );
      ?>
    </div>
  <?php else : ?>
  
    <h2 class="center"><?php _e( 'Not Found', 'bwpy' ); ?></h2>
    <p class="center"><?php _e( 'Sorry, but you are looking for something that isn\'t here.', 'bwpy' ); ?></p>
    <?php get_search_form(); ?>
  
  <?php endif; ?>
  </div>
</div>


      </div>
    </div>
  </div>



</div><!-- /.main -->
</div><!-- /.content -->
</div>
