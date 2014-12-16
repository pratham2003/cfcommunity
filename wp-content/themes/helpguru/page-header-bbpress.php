<!-- #page-header -->
<section id="page-header" class="clearfix">
<div class="ht-container">
<h1 id="page-header-title"><?php echo get_theme_mod( 'ht_bbpress_title' ); ?></h1>
<?php if (get_theme_mod( 'ht_bbpress_tagline' )) : ?><h2 id="page-header-tagline"><?php echo get_theme_mod( 'ht_bbpress_tagline' ); ?></h2><?php endif; ?>
</div>
</section>
<!-- /#page-header -->

<!-- #page-header-breadcrumbs -->
<section id="page-header-breadcrumbs" class="clearfix">
<div class="ht-container">
<?php bbp_breadcrumb(array(
	'before' => '<div class="ht-breadcrumbs bbp-breadcrumb" itemprop="breadcrumb">',
	'after' => '</div>',
	'sep' => __( '/', 'bbpress' ),
	'pad_sep' => 0
)); ?>
</div>
</section>
<!-- /#page-header -->