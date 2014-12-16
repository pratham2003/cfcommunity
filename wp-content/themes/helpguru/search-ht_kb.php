<?php
/**
* The template for displaying Search content for knowledge base search results
*
*/
global $ht_knowledge_base_options;
?>
<!-- #content -->
<main id="content" role="main">

<!-- #ht-kb -->
<div id="ht-kb" class="ht-kb-search">

<?php if ( have_posts() ) { ?>

	<?php /* Start the Loop */ ?>
	<?php while ( have_posts() ) : the_post(); ?>

	<?php 
	if ( 'ht_kb' == get_post_type() ) {
		get_template_part( 'search-content-ht_kb', get_post_format() );
	} elseif ( 'page' == get_post_type() ) {
		get_template_part( 'search-content-page', get_post_format() );
	} elseif ( 'post' == get_post_type() ) {
		get_template_part( 'search-content', get_post_format() );
	}
	?>	
     			
	<?php endwhile;  ?>

	<?php ht_pagination(); ?>

<?php } else { ?>

	<?php get_template_part( 'content', 'none' ); ?>

<?php } ?>
    
</div>
<!-- /#ht-kb -->

</main>
<!-- /#content -->