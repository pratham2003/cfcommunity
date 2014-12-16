<?php global $ht_knowledge_base_options; ?>

<?php
$ht_page_header_class = '';
if ( is_post_type_archive() ) {
//$ht_page_header_class = 'ph-large';
} ?>

<!-- #page-header -->
<section id="page-header" class="clearfix <?php echo $ht_page_header_class; ?>">
<div class="ht-container">
	<h1 id="page-header-title"><?php echo get_theme_mod( 'ht_kb_title' ); ?></h1>
	<?php if ( get_theme_mod( 'ht_kb_tagline' ) && is_post_type_archive() ) : ?>
		<h2 id="page-header-tagline"><?php echo get_theme_mod( 'ht_kb_tagline' ); ?></h2>
	<?php endif; ?>
	<?php if ( $ht_knowledge_base_options['search-display'] ): ?>
		<div id="ht-kb-search" class="clearfix">
	  		<?php ht_kb_display_search(); ?>
	  	</div>
	<?php endif; ?>
</div>
</section>
<!-- /#page-header -->

<!-- #page-header-breadcrumbs -->
<?php if ( !is_post_type_archive() && !is_search() && apply_filters('ht_show_breadcrumbs', true) ): ?>
<section id="page-header-breadcrumbs" class="clearfix">
<div class="ht-container">
	<?php if ( $ht_knowledge_base_options['breadcrumbs-display'] ): ?>
		<?php ht_kb_breadcrumb_display(); ?>
	<?php endif; ?>
</div>
</section>
<?php endif; ?>
<!-- /#page-header -->