<?php get_header(); ?>

<?php global $ht_knowledge_base_options; ?>

<?php get_template_part( 'page-header', 'kb' ); ?>

<!-- #primary -->
<div id="primary" class="sidebar-right clearfix"> 
<div class="ht-container">

<!-- #content -->
<main id="content" role="main" itemprop="mainContentOfPage">

<!-- #ht-kb -->
<div id="ht-kb" class="ht-kb-single">

<?php while ( have_posts() ) : the_post(); ?>
	<?php
	//important - register page view
	ht_kb_set_post_views($post->ID);

	//get the voting parameter, we can remove voting_old in subsequent revisions of the theme
	$voting_old =  get_post_meta( get_the_ID(), 'ht_knowledge_base_voting_checkbox', true );
	$voting =  get_post_meta( get_the_ID(), '_ht_knowledge_base_voting_checkbox', true );
	$allow_voting_on_this_article = ($voting_old||$voting) ? true : false;	?>

	<article id="post-<?php the_ID(); ?>" <?php post_class(); ?> itemtype="http://schema.org/CreativeWork" itemscope="itemscope">

		<!-- .entry-header -->
		<header class="entry-header">

		<?php if ( has_post_format( 'video' )) { ?>
			<div class="entry-video clearfix">
				<?php
				// Get post format meta
				$ht_pf_video_picker = get_post_meta( get_the_ID(), '_ht_pf_video_picker', true );
				$ht_pf_video_oembed = get_post_meta( get_the_ID(), '_ht_pf_video_oembed', true );
				$ht_pf_video_upload = get_post_meta( get_the_ID(), '_ht_pf_video_upload', true );

				// Echo video
				if ( $ht_pf_video_picker == 'oembed' ) {
					echo wp_oembed_get( $ht_pf_video_oembed );
				} elseif ( $ht_pf_video_picker == 'custom' ) {
					echo do_shortcode('[video src="'. $ht_pf_video_upload .'" width="1920" height="1080"]');
				};
				?>
			</div>
		<?php } // End if has_post_format(video) ?>

			<h1 class="entry-title" itemprop="headline">
				<?php the_title(); ?>
			</h1>

			<?php display_is_most_helpful_article(); ?>
			<?php display_is_most_viewed_article(); ?>
			<?php if ( $ht_knowledge_base_options['meta-display'] ): ?>
				<?php ht_kb_entry_meta_display(); ?>
			<?php endif; ?>

		<?php if ( has_post_thumbnail() ) { ?>
		<div class="entry-thumb">
			<?php if ( is_single() ) { ?>
	            <?php ht_responsive_post_thumbnail(); ?>
	        <?php } else { ?> 
	            <a href="<?php the_permalink(); ?>" rel="nofollow">
	               <?php ht_responsive_post_thumbnail(); ?>
	            </a>
	        <?php }?> 
		</div>
		<?php } ?>
		    
		</header>
		<!-- /.entry-header -->

		<!-- .entry-content -->
		<div class="entry-content clearfix" itemprop="text">
			<?php the_content(); ?>
			<?php wp_link_pages( array( 'before' => '<div class="page-links">' . __( 'Articles:', 'ht-theme' ), 'after' => '</div>' ) ); ?>
			<?php ht_kb_display_tags(); ?>
		</div>
		<!-- /.entry-content -->

		<?php ht_kb_display_attachments(); ?>

		<?php if( class_exists('HT_Voting') ): ?>
		<?php // voting
		if( $ht_knowledge_base_options['voting-display'] && $allow_voting_on_this_article ){ ?>
			<section id="ht-kb-rate-article">
			
				<h3 id="ht-kb-rate-article-title"><?php _e('Rate This Article', 'ht-theme'); ?></h3>
				<?php if( $ht_knowledge_base_options['anon-voting'])
					echo do_shortcode('[ht_voting allow="anon"]');
				else
					echo do_shortcode('[ht_voting allow="user"]');
				} ?>
			</section>
		<?php endif; ?>

		<?php include_once( 'ht-knowledge-base-author-template.php' ); ?>

		<?php ht_kb_related_articles(); ?>

	<?php
        // If comments are open or we have at least one comment, load up the comment template
        if ( comments_open() || '0' != get_comments_number() )
        comments_template();
    ?>
	 
	</article>	

<?php endwhile; // end of the loop. ?>

</div>
<!-- /#ht-kb -->

</main>
<!-- /#content -->

<?php get_sidebar( 'kb' ); ?>

</div>
</div>
<!-- /#primary -->

<?php get_footer(); ?>