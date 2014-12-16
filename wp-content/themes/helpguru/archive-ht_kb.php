<?php get_header(); ?>

<?php global $ht_knowledge_base_options; ?>

<?php get_template_part( 'page-header', 'kb' ); ?>

<!-- #primary -->
<div id="primary" class="<?php echo get_theme_mod( 'ht_kb_sidebar', 'sidebar-right' ); ?> clearfix"> 
<div class="ht-container">

<!-- #content -->
<main id="content" role="main">

<!-- #ht-kb -->
<div id="ht-kb" class="ht-kb-archive">

<?php if(function_exists('ht_kb_display_archive')): ?>
	<?php ht_kb_display_archive(); ?>
<?php endif; ?>

<?php if(function_exists('ht_kb_display_uncategorized_articles')): ?>
	<?php ht_kb_display_uncategorized_articles(); ?>
<?php endif; ?>

</div>
<!-- /#ht-kb -->

</main>
<!-- /#content -->

<?php $ht_kb_sidebar = get_theme_mod( 'ht_kb_sidebar', 'sidebar-right' );
if ( $ht_kb_sidebar != 'sidebar-off') {
get_sidebar( 'kb' ); } ?>

</div>
<!-- /.ht-container -->
</div>
<!-- /#primary -->

<?php get_footer(); ?>