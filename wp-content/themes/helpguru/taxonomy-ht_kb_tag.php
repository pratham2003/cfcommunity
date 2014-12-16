<?php get_header(); ?>

<?php global $ht_knowledge_base_options; ?>

<?php get_template_part( 'page-header', 'kb' ); ?>

<!-- #primary -->
<div id="primary" class="<?php echo get_theme_mod( 'ht_kb_sidebar', 'sidebar-right' ); ?> clearfix"> 
<div class="ht-container">

<!-- #content -->
<main id="content" role="main">

<!-- #ht-kb -->
<div id="ht-kb" class="ht-kb-category">

<?php //get the title
$term = get_term_by( 'slug', get_query_var( 'term' ), get_query_var( 'taxonomy' ) ); ?>

  	<?php
  	$term_meta = get_ht_kb_term_meta($term);
  	$category_thumb_att_id = 0;
	$category_color = '#222'; 

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
	} ?>

	<!--.ht-kb-category-header-->
	<div class="ht-kb-category-header clearfix" data-ht-category-icon="true" data-ht-category-custom-icon="<?php echo $data_ht_category_custom_icon; ?>">

	<?php if( !empty( $category_thumb_att_id ) && $category_thumb_att_id!=0 ){
		$ht_kb_category_class = "ht-kb-category-hasthumb";
	} else {
		$ht_kb_category_class = "ht-kb-category-hasicon";
	} ?>

	<!--.ht-kb-category-title-wrappe-->
	<div class="ht-kb-category-title-wrapper <?php echo $ht_kb_category_class; ?> clearfix">

	<?php 
	
	if( !empty( $category_thumb_att_id ) && $category_thumb_att_id!=0 ){
		$category_thumb_obj = wp_get_attachment_image_src( $category_thumb_att_id, 'ht-kb-thumb');
		
		$category_thumb_src = $category_thumb_obj[0];
		echo '<img src="' . $category_thumb_src . '" class="ht-kb-category-thumb" />';
	} ?>

  	<h1 class="ht-kb-title"><?php echo $term->name; ?></h1>

  	</div>
  	<!-- /.ht-kb-category-title-wrapper -->

  	<?php $ht_kb_tax_desc =  $term->description; ?>
		<?php if( !empty($ht_kb_tax_desc) ): ?>
		<p class="ht-kb-category-desc"><?php echo $ht_kb_tax_desc ?></p>
	<?php endif; ?>

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
		if ( $sub_categories ): ?>
		<div class="ht-kb-sub-cats">
		<?php foreach ($sub_categories as $sub_category) { ?>
			
			<h2 class="ht-kb-sub-cat-title">
				<a href="<?php echo esc_attr(get_term_link($sub_category, 'ht_kb_category')); ?>" title="<?php printf( __( 'View all posts in %s', 'ht-theme' ), $sub_category->name ); ?>"><?php echo $sub_category->name; ?></a>
				<span class="ht-kb-category-count"><?php echo $sub_category->count ?> <?php _e(' Articles', 'ht-theme'); ?></span>
			</h2>

		<?php } ?>
		</div>
	<?php endif; // if $sub_categories ?>

	  	</div>
  	<!-- /.ht-kb-category-header -->

<?php if ( have_posts() ) : ?>
    <?php
		/* Start the Loop */
		while ( have_posts() ) : the_post(); ?>

		<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
			<h3 class="entry-title" itemprop="headline">
				<a href="<?php the_permalink(); ?>" rel="bookmark"><?php the_title(); ?></a>
			</h3>
			<?php ht_kb_entry_meta_display(); ?>
		</article>
    
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