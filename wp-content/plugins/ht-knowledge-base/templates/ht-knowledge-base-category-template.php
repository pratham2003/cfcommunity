<?php
/**
 * The template for displaying heroic knowledgebase category archive
 */
global $ht_knowledge_base_options; ?>

<!-- #ht-kb -->
<div id="ht-kb" class="ht-kb-category">

<?php //get the title
$term = get_term_by( 'slug', get_query_var( 'term' ), get_query_var( 'taxonomy' ) ); ?>

  	<?php if ( $ht_knowledge_base_options['search-display'] ): ?>
	  	<div id="ht-kb-search" class="clearfix">
	  		<?php ht_kb_display_search(); ?>
	  	</div>
  	<?php endif; ?>

  	<?php
  	$term_meta = get_ht_kb_term_meta($term);
  	$category_thumb_att_id = 0;
	$category_color = '#222'; ?>

	<!-- #ht-kb-category-header -->
	<div id="ht-kb-category-header">

	<?php 
	if(is_array($term_meta)&&array_key_exists('meta_image', $term_meta)&&!empty($term_meta['meta_image']))
		$category_thumb_att_id = $term_meta['meta_image'];

	if(is_array($term_meta)&&array_key_exists('meta_color', $term_meta)&&!empty($term_meta['meta_color']))
		$category_color = $term_meta['meta_color'];

	if( !empty( $category_thumb_att_id ) && $category_thumb_att_id!=0 ){
		$category_thumb_obj = wp_get_attachment_image_src( $category_thumb_att_id, 'ht-kb-thumb');
		
		$category_thumb_src = $category_thumb_obj[0];
		echo '<img src="' . $category_thumb_src . '" class="ht-kb-category-thumb" />';
	} else {
		echo '<i class="ht-kb-category-icon fa fa-folder"></i>';
	}	?>

  	<h2 class="ht-kb-title"><?php echo $term->name; ?></h2>

  	<?php if ( $ht_knowledge_base_options['breadcrumbs-display'] ): ?>
		<?php ht_kb_breadcrumb_display(); ?>
	<?php endif; ?>

  	<?php //ht_kb_breadcrumb_display(); ?>

  	</div>
  	<!-- /#ht-kb-category-header -->

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

		foreach ($sub_categories as $sub_category) { ?>
			
			<h2 class="ht-kb-sub-cat-title">
				<a href="<?php echo esc_attr(get_term_link($sub_category, 'ht_kb_category')); ?>" title="<?php printf( __( 'View all posts in %s', 'ht-knowledge-base' ), $sub_category->name ); ?>"><?php echo $sub_category->name; ?></a>
				<span class="ht-kb-category-count"><?php echo $sub_category->count . _x(' Articles', 'ht-knowledge-base'); ?></span>
			</h2>

		<?php } ?>

<?php if ( have_posts() ) : ?>
    <?php
		/* Start the Loop */
		while ( have_posts() ) : the_post(); ?>

		<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
			<h2 class="entry-title" itemprop="headline">
				<a href="<?php the_permalink(); ?>" rel="bookmark"><?php the_title(); ?></a>
			</h2>
			<?php ht_kb_entry_meta_display(); ?>
		</article>
    
    <?php endwhile; ?>

    <?php posts_nav_link(); ?>
    
    <?php else : ?>

    <h2><?php _e('Nothing in this category.', 'ht-knowledge-base'); ?></h2>
    
<?php endif; ?>

</div>
<!-- /#ht-kb -->