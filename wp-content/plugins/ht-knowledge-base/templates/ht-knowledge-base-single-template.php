<?php
/**
 * The template for displaying heroic knowledgebase single item
 */
global $ht_knowledge_base_options; ?>

<!-- #ht-kb -->
<div id="ht-kb" class="ht-kb-single">

<?php while ( have_posts() ) : the_post(); ?>
	<?php
	//important - register page view
		ht_kb_set_post_views($post->ID);
		$voting =  get_post_meta( get_the_ID(), '_ht_knowledge_base_voting_checkbox', true );
		$allow_voting_on_this_article = $voting ? true : false;

	?>

	<article id="post-<?php the_ID(); ?>" <?php post_class(); ?> itemprop="blogPost" itemtype="http://schema.org/BlogPosting" itemscope="itemscope">

		<?php if ( $ht_knowledge_base_options['search-display'] ): ?>
			<div id="ht-kb-search" class="clearfix">
		  		<?php ht_kb_display_search(); ?>
		  	</div>
		<?php endif; ?>

		<header class="entry-header">
			<h2 class="entry-title" itemprop="headline">
				<a href="<?php the_permalink(); ?>" rel="bookmark"><?php the_title(); ?></a>
			</h2>

			<?php display_is_most_helpful_article(); ?>
			<?php display_is_most_viewed_article(); ?>
			<?php if ( $ht_knowledge_base_options['breadcrumbs-display'] ): ?>
				<?php ht_kb_breadcrumb_display(); ?>
			<?php endif; ?>
			<?php if ( $ht_knowledge_base_options['meta-display'] ): ?>
				<?php ht_kb_entry_meta_display(); ?>
			<?php endif; ?>

		<?php if ( has_post_thumbnail() ) { ?>
		<div class="entry-thumb">
			<a href="<?php the_permalink(); ?>" rel="nofollow">
				<?php the_post_thumbnail('post'); ?>
		    </a>
		</div>
		<?php } ?>
		    
		</header>

		<div class="entry-content clearfix">
		<?php if ( is_single() ) { ?>
			<?php the_content(); ?>
			
			<?php ht_kb_display_tags(); ?>

			<div id="ht-kb-rate-article">
			<?php			
			// voting
			if(class_exists('HT_Voting') && $ht_knowledge_base_options['voting-display'] && $allow_voting_on_this_article ){ ?>
				<h3 id="ht-kb-rate-article-title"><?php _e('Rate This Article', 'ht-knowledge-base'); ?></h3>
				<?php if( $ht_knowledge_base_options['anon-voting'])
					echo do_shortcode('[ht_voting allow="anon"]');
				else
					echo do_shortcode('[ht_voting allow="user"]');
			}

			?>
			</div>

			<?php include_once( 'ht-knowledge-base-author-template.php' ); ?>

			<?php wp_link_pages( array( 'before' => '<div class="page-links">' . __( 'Articles:', 'ht-knowledge-base' ), 'after' => '</div>' ) ); ?>
		<?php } else { ?>
			<?php the_excerpt(); ?>
		<?php }?>
		</div>

		
	 
	</article>

	<?php ht_kb_display_attachments(); ?>

	<?php ht_kb_related_articles(); ?>

<?php endwhile; // end of the loop. ?>

</div>
<!-- /#ht-kb -->