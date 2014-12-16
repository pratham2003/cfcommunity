<?php
/**
 * The template for displaying heroic knowledgebase search results
 */
global $ht_knowledge_base_options, $s; ?>

<!-- #ht-kb -->
<div id="ht-kb" class="ht-kb-search">

<div class="ht-kb-search-results-page">
  	<?php if ( $ht_knowledge_base_options['search-display'] ): ?>
	  	<div id="ht-kb-search" class="clearfix">
	  		<?php ht_kb_display_search(); ?>
	  	</div>
  	<?php endif; ?>

  	<h1 class="ht-knowledge-base-title"><?php _e('Knowledge Base Search Results for ', 'ht-knowledge-base'); echo $s; ?></h1>

  	<div class="search-switch">
  		<div class="article-switch">
  			<a class="button"  href="#article-section"><?php _e('Articles', 'ht-knowledge-base'); ?></a>
  		</div>
  		<div class="category-switch">
  			<a class="button" href="#category-section"><?php _e('Categories', 'ht-knowledge-base'); ?></a>
  		</div>
  		<div class="tag-switch">
  			<a class="button" href="#tag-section"><?php _e('Tags', 'ht-knowledge-base'); ?></a>
  		</div>
  	</div>

  	<!-- ht_kb -->
  	<a id="article-section"></a>
  	<h2 class="article-section">
		<?php _e('Article Results', 'ht-knowledge-base'); ?>
	</h2>

	<?php if ( have_posts() ) : ?>
    <?php
		/* Start the Loop */
		while ( have_posts() ) : the_post(); ?>

			<h2 class="entry-title" itemprop="headline">
				<a href="<?php the_permalink(); ?>" rel="bookmark"><?php the_title(); ?></a>
			</h2>

			<?php ht_kb_entry_meta_display(); ?>
    
    <?php endwhile; ?>

    <?php posts_nav_link(); ?>

    <?php else : ?>

    	<h2><?php _e('No Results', 'ht-knowledge-base'); ?></h2>

    <?php endif; ?>

    <!-- ht_kb_category -->
	<?php $categories = get_ht_kb_categories();

	if(!is_a($categories, 'WP_Error')){
		?>
		<a id="category-section"></a>
		<h2 class="category-section">
			<?php _e('Category Results', 'ht-knowledge-base'); ?>
		</h2>
		<?php
		foreach ($categories as $key => $category) {
			//search the name
			if(stripos($category->name, $s)>-1):
				?>
				<h2 class="category-title" itemprop="headline"><a href="<?php esc_url( get_term_link( $category, 'ht_kb_category' ) );?>"><?php echo $category->name; ?></a></h2>
		  	<?php
		  	endif;
		}
	} ?>

	<!-- ht_kb_tag -->
	<?php	$tags = get_ht_kb_tags();

	if(!is_a($tags, 'WP_Error')){
		?>
		<a id="tag-section"></a>
		<h2 class="tag-section">
			<?php _e('Tag Results', 'ht-knowledge-base'); ?>
		</h2>
		<?php
		foreach ($tags as $key => $tag) {
			//search the name
			if(stripos($tag->name, $s)>-1):	?>

				<h2 class="category-title" itemprop="headline">
					<a href="<?php esc_url( get_term_link( $tag, 'ht_kb_tag' ) );?>"><?php echo $tag->name; ?></a>
				</h2>
		  	<?php endif;
		}
	}?>
    
</div> <!-- /ht-kb-search-results-page-->

</div>
<!-- /#ht-kb -->