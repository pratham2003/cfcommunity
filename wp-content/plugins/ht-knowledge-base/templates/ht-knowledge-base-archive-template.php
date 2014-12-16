<?php
/**
 * The template for displaying heroic knowledgebase CPT archive
 */
global $ht_knowledge_base_options; ?>

<!-- #ht-kb -->
<div id="ht-kb" class="ht-kb-archive">

<?php if ( $ht_knowledge_base_options['search-display'] ): ?>
	<div id="ht-kb-search" class="clearfix">
  		<?php ht_kb_display_search(); ?>
  	</div>
 <?php endif; ?>

<!-- <h1 class="ht-kb-title"><?php _e('Knowledge Base Archive', 'ht-knowledge-base'); ?></h1> -->

<?php if(function_exists('ht_kb_display_archive')): ?>
	<?php ht_kb_display_archive(); ?>
<?php endif; ?>



<?php if(function_exists('ht_kb_display_uncategorized_articles')): ?>
	<?php ht_kb_display_uncategorized_articles(); ?>
<?php endif; ?>


</div>
<!-- /#ht-kb -->