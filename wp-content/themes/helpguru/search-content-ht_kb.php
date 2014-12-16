<?php global $ht_knowledge_base_options; ?>
<article id="post-<?php the_ID(); ?>" <?php post_class('clearfix'); ?>>

	<h3 class="entry-title" itemprop="headline">
		<a href="<?php the_permalink(); ?>" rel="bookmark"><?php the_title(); ?></a>
	</h3>
	<?php if( $ht_knowledge_base_options['usefulness-display']  ||  $ht_knowledge_base_options['viewcount-display'] || $ht_knowledge_base_options['comments-display'] ): ?>
		<ul class="ht-kb-meta">
			<?php if( $ht_knowledge_base_options['usefulness-display']  &&  function_exists('ht_usefulness') ): ?>
				<?php
					$article_usefulness = ht_usefulness( get_the_ID() );
					$helpful_article = ( $article_usefulness >= 0 ) ? true : false;
					$helpful_article_class = ( $helpful_article ) ? 'ht-kb-helpful-article' : 'ht-kb-unhelpful-article';
				?>
				<li class="ht-kb-usefulness <?php echo $helpful_article_class; ?>"><?php echo $article_usefulness  ?></li>
			<?php endif; //end function exists ?>
			<?php if( $ht_knowledge_base_options['viewcount-display']  &&  function_exists('ht_kb_view_count') ): ?>
				<li class="ht-kb-view-count"><?php echo ht_kb_view_count( get_the_ID() ); ?></li>
			<?php endif; //end function exists ?>
			<?php if( $ht_knowledge_base_options['comments-display']  &&  function_exists('get_comments_number') ): ?>
				<?php $num_comments = get_comments_number(); ?>
				<?php if($num_comments>0): ?>
					<li class="ht-kb-comments-count"><?php echo $num_comments; ?></li>
				<?php endif; //end num_coments ?>
			<?php endif; //end function exists ?>
		</ul>
	<?php endif; ?>
	<?php if( $ht_knowledge_base_options['search-excerpt'] && function_exists('the_excerpt') ): ?>
		<span class="ht-kb-search-result-excerpt"><?php the_excerpt(); ?></span>
	<?php endif; ?>	

</article>