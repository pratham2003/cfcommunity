<?php get_header(); ?>

<?php global $ht_knowledge_base_options; ?>

<?php get_template_part( 'page-header', 'kb' ); ?>

<!-- #primary -->
<div id="primary" class="<?php echo get_theme_mod( 'ht_kb_sidebar', 'sidebar-right' ); ?> clearfix"> 
<div class="ht-container">

<!-- #content -->
<main id="content" role="main">

<!-- #ht-kb -->
<div id="ht-kb" class="ht-kb-category-archive">

<?php //get the title
$term = get_term_by( 'slug', get_query_var( 'term' ), get_query_var( 'taxonomy' ) ); ?>

  	<?php
  	$term_meta = get_ht_kb_term_meta($term);
  	$category_thumb_att_id = 0;
	$category_color = '#434345';
	$ht_kb_tax_desc =  $term->description;

	if(is_array($term_meta)&&array_key_exists('meta_image', $term_meta)&&!empty($term_meta['meta_image']))
		$category_thumb_att_id = $term_meta['meta_image'];

	if(is_array($term_meta)&&array_key_exists('meta_color', $term_meta)&&!empty($term_meta['meta_color']))
		$category_color = $term_meta['meta_color'];
	?>

	<?php if( !empty( $category_thumb_att_id ) && $category_thumb_att_id!=0 ){
		$ht_kb_category_class = "ht-kb-category-hasthumb";
		$data_ht_category_custom_icon = 'true';
	} else {
		$ht_kb_category_class = "ht-kb-category-hasicon";
		$data_ht_category_custom_icon = 'false';
	}

	if ( !empty( $ht_kb_tax_desc ) ) {
		$data_ht_kb_hasdesc = 'true';
	} else {
		$data_ht_kb_hasdesc = 'false';
	}
	?>

	<!--.ht-kb-category-->
	<div id="ht-kb-category-<?php echo $term->term_id; ?>" class="ht-kb-category">
	<!--.ht-kb-category-header-->
	<div class="ht-kb-category-header clearfix" data-ht-category-color="true" data-ht-category-icon="true" data-ht-category-custom-icon="<?php echo $data_ht_category_custom_icon; ?>" data-ht-category-desc="<?php echo $data_ht_kb_hasdesc; ?>">

	<?php 
	
	if( !empty( $category_thumb_att_id ) && $category_thumb_att_id!=0 ){
		$category_thumb_obj = wp_get_attachment_image_src( $category_thumb_att_id, 'ht-kb-thumb');
		
		$category_thumb_src = $category_thumb_obj[0];
		echo '<div class="ht-kb-category-thumb"><img src="' . $category_thumb_src . '" /></div>';
	} ?>

  	<h1 class="ht-kb-category-title"><?php echo $term->name; ?></h1>

  	<?php $ht_kb_tax_desc =  $term->description; ?>
		<?php if( !empty($ht_kb_tax_desc) ): ?>
		<p class="ht-kb-category-desc"><?php echo $ht_kb_tax_desc ?></p>
	<?php endif; ?>

 	</div>
  	<!-- /.ht-kb-category-header -->
  	</div>
  	<!--/.ht-kb-category-->

  	<?php
  		//get sub terms
  		$args = array(
			    'orderby'       =>  'term_order',
                'depth'         =>  1,
                'child_of'      => 	$term->term_id,
                'hide_empty'    =>  0,
                'pad_counts'   	=>	true
			); 
		$sub_categories = get_terms('ht_kb_category', $args);
		$sub_categories = wp_list_filter($sub_categories,array('parent'=>$term->term_id));
		//category count (terms)
		$ht_kb_subcategory_count = count($sub_categories);
		//category counter
		$cat_counter = 1;
		if ( $sub_categories ): ?>
		<div class="ht-kb-sub-cats clearfix">
		<div class="ht-grid ht-grid-gutter-20">
		<?php foreach ($sub_categories as $sub_category) { ?>

		<?php $term_meta = get_ht_kb_term_meta($sub_category);

		$category_thumb_att_id = 0;
		$category_color = '#434345'; 
		$ht_kb_tax_desc =  $sub_category->description; 

		if(is_array($term_meta)&&array_key_exists('meta_image', $term_meta)&&!empty($term_meta['meta_image']))
			$category_thumb_att_id = $term_meta['meta_image'];

		if(is_array($term_meta)&&array_key_exists('meta_color', $term_meta)&&!empty($term_meta['meta_color']))
			$category_color = $term_meta['meta_color'];
		?>

		<?php if( !empty( $category_thumb_att_id ) && $category_thumb_att_id!=0 ){
			$ht_kb_category_class = "ht-kb-category-hasthumb";
			$data_ht_category_custom_icon = 'true';
		} else {
			$ht_kb_category_class = "ht-kb-category-hasicon";
			$data_ht_category_custom_icon = 'false';
		}

		if ( !empty( $ht_kb_tax_desc ) ) {
			$data_ht_kb_hasdesc = 'true';
		} else {
			$data_ht_kb_hasdesc = 'false';
		}
		 ?>

			<div class="ht-grid-col ht-grid-6">

			
			<!--.ht-kb-category-->
			<div id="ht-kb-category-<?php echo $sub_category->term_id; ?>" class="ht-kb-category">
			<!-- .ht-kb-category-header -->
			<div class="ht-kb-category-header clearfix" data-ht-category-icon="true" data-ht-category-custom-icon="<?php echo $data_ht_category_custom_icon; ?>" data-ht-category-desc="<?php echo $data_ht_kb_hasdesc; ?>">

				<?php if( !empty( $category_thumb_att_id ) && $category_thumb_att_id!=0 ) :
					$category_thumb_obj = wp_get_attachment_image_src( $category_thumb_att_id, 'ht-kb-thumb');
					$category_thumb_src = $category_thumb_obj[0]; ?>
					<div class="ht-kb-category-thumb">
						<img src="<?php echo $category_thumb_src ?>" alt="" />
					</div>
				<?php endif; ?>

			<h2 class="ht-kb-category-title">
				<a href="<?php echo esc_attr(get_term_link($sub_category, 'ht_kb_category')); ?>" title="<?php printf( __( 'View all posts in %s', 'ht-theme' ), $sub_category->name ); ?>"><?php echo $sub_category->name; ?></a>
				<span class="ht-kb-category-count"><?php echo $sub_category->count ?> <?php _e(' Articles', 'ht-theme'); ?></span>
			</h2>
			<?php $ht_kb_tax_desc =  $sub_category->description; ?>
				<?php if( !empty($ht_kb_tax_desc) ): ?>
					<p class="ht-kb-category-desc"><?php echo $ht_kb_tax_desc ?></p>
				<?php endif; ?>
			</div>
			<!-- /.ht-kb-category-header -->
			</div>
			<!--/.ht-kb-category-->

			</div>
			<?php
			//increment counter
			$cat_counter+=1;
			?>

		<?php } ?>
		</div>
		</div>
		
	<?php endif; // if $sub_categories ?>



<?php if ( have_posts() ) : ?>
    <?php
		/* Start the Loop */
		while ( have_posts() ) : the_post(); ?>

		<?php get_template_part( 'content-ht_kb', get_post_format() ); ?>
    
    <?php endwhile; ?>

    <?php ht_pagination(); ?>
    
    <?php else : ?>

    <h2><?php _e('Nothing in this category.', 'ht-theme'); ?></h2>
    
<?php endif; ?>

</div>
<!-- /#ht-kb -->

</main>
<!-- /#content -->

<?php $ht_kb_sidebar = get_theme_mod( 'ht_kb_sidebar', 'sidebar-right' );
if ( $ht_kb_sidebar != 'sidebar-off') {
get_sidebar( 'kb' ); } ?>

</div>
</div>
<!-- /#primary -->

<?php get_footer(); ?>