<?php
/*
* Extension to enable enable sorting of knowledge base categories
*/

if( !class_exists( 'HT_Knowledge_Base_Live_Search' ) ){
	class HT_Knowledge_Base_Live_Search {

		//Constructor
		function __construct(){
			add_filter( 'search_template', array($this, 'ht_knowledge_base_live_search_template') );
			//enqueue scripts
			add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_ht_kb_live_search_scripts_and_styles' ) );	

		}

		/**
		* Live search results functionality
		*/
		function ht_knowledge_base_live_search_template( $template ){
			global $ht_knowledge_base_options;
			//ensure this is a live search
			$ht_kb_search = ( array_key_exists('ht-kb-search', $_REQUEST) ) ? true : false;
			if( $ht_kb_search == false )
				return $template;

			if(!empty($_GET['ajax']) ? $_GET['ajax'] : null) { // Is Live Search 
				//check custom search

				//search string
				global $s;
				// Get FAQ cpt
				$ht_kb_cpt = 'ht_kb';
				?>

				<ul id="ht-kb-search-result">
				<!-- ht_kb -->
				<?php if (have_posts()) : ?>
					  <?php while (have_posts()) : the_post(); ?>
					  	<?php
					  		//post type 
					  		$post_type = get_post_type();
					  		$ht_kb_type_class = 'ht-post-type-' . $post_type;
						  	//set post format class  
			                if ( has_post_format( 'video' )) { 
			                  $ht_kb_format_class = 'format-video';
			                } else {
			                  $ht_kb_format_class = 'format-standard';
			                }
		                ?>	
						  <li class="ht-kb <?php echo $ht_kb_format_class . ' ' . $ht_kb_type_class; ?>">
						  <?php if ( $ht_kb_cpt == get_post_type() ) { ?>
						  <a href="<?php the_permalink(); ?>">
							<span class="ht-kb-search-result-title"><?php the_title(); ?></span>
							<?php if( $ht_knowledge_base_options['search-excerpt'] && function_exists('the_excerpt') ): ?>
								<span class="ht-kb-search-result-excerpt"><?php the_excerpt(); ?></span>
							<?php endif; ?>	
							<?php if( $ht_knowledge_base_options['usefulness-display']  ||  $ht_knowledge_base_options['viewcount-display'] || $ht_knowledge_base_options['comments-display'] ): ?>
								<div class="ht-kb-search-result-meta">
									<?php if( $ht_knowledge_base_options['usefulness-display']  &&  function_exists('ht_usefulness') ): ?>
										<?php
											$article_usefulness = ht_usefulness( get_the_ID() );
											$helpful_article = ( $article_usefulness >= 0 ) ? true : false;
											$helpful_article_class = ( $helpful_article ) ? 'ht-kb-helpful-article' : 'ht-kb-unhelpful-article';
										?>
										<span class="ht-kb-usefulness <?php echo $helpful_article_class; ?>"><?php echo $article_usefulness  ?></span>
									<?php endif; //end function exists ?>
									<?php if( $ht_knowledge_base_options['viewcount-display']  &&  function_exists('ht_kb_view_count') ): ?>
										<span class="ht-kb-view-count"><?php echo ht_kb_view_count( get_the_ID() ); ?></span>
									<?php endif; //end function exists ?>
									<?php if( $ht_knowledge_base_options['comments-display']  &&  function_exists('get_comments_number') ): ?>
										<?php $num_comments = get_comments_number(); ?>
										<?php if($num_comments>0): ?>
											<span class="ht-kb-comments-count"><?php echo $num_comments; ?></span>
										<?php endif; //end num_coments ?>
									<?php endif; //end function exists ?>
								</div><!-- search results meta -->
							<?php endif; ?>
						  </a>
						  <?php } else { 
						  	?>
						  		<a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
						  	<?php
						   } ?>
						  </li>

					  <?php endwhile; ?>
				<?php endif; ?>

				<!-- ht_kb_category -->
				<?php
				$categories = get_ht_kb_categories();

				if(!is_a($categories, 'WP_Error')){
					foreach ($categories as $key => $category) {
						//search the name
						if(stripos($category->name, $s)>-1):
							?>
							<li class="ht-kb-category">
						  		<a href="<?php echo esc_url( get_term_link( $category, 'ht_kb_category' ) );?>" class="ht-kb-search-result-title"><?php echo $category->name; ?></a>
						  	</li>	
					  	<?php
					  	endif;
					}
				}

				?>
				<!-- ht_kb_tag -->
				<?php
				$tags = get_ht_kb_tags();

				if(!is_a($tags, 'WP_Error')){
					foreach ($tags as $key => $tag) {
						//search the name
						if(stripos($tag->name, $s)>-1):
							?>
							<li class="ht-kb-tag">
						  		<a href="<?php echo esc_url( get_term_link( $tag, 'ht_kb_tag' ) );?>" class="ht-kb-search-result-title"><?php echo $tag->name; ?></a>
						  	</li>	
					  	<?php
					  	endif;
					}
				}

				?>

				</ul>

				<?php
				wp_reset_query();

				//required to stop 
				die();
			} else {
				//non ajax search
				return $template;
			}
		}

		/**
		* Enqueue the javascript for live search
		*/
		function enqueue_ht_kb_live_search_scripts_and_styles(){

			//register live search script
			wp_enqueue_script('ht-kb-live-search-plugin', plugins_url( 'js/jquery.livesearch.js', dirname( __FILE__ ) ), array( 'jquery' ), false, true);
			wp_enqueue_script('ht-kb-live-search', plugins_url( 'js/ht-kb-livesearch.js', dirname( __FILE__ ) ), array( 'jquery', 'ht-kb-live-search-plugin' ), false, true);
			$search_url = '?ajax=1&ht-kb-search=1&';
			//if wpml is installed append language code if not in default language
			if(defined('ICL_LANGUAGE_CODE')){
				global $sitepress;
				$default_lang = $sitepress->get_default_language();
				if($default_lang != ICL_LANGUAGE_CODE ){
					$search_url .= 'lang=' . ICL_LANGUAGE_CODE . '&';
					$search_url = ICL_LANGUAGE_CODE . '/' . $search_url;
				}				
			}
			$search_url .= 's=';
			wp_localize_script( 'ht-kb-live-search', 'framework', array( 'liveSearchUrl' => home_url($search_url) ) );
		}

    }//end class
}//end class test

//run the module
if(class_exists('HT_Knowledge_Base_Live_Search')){
	$ht_knowledge_base_live_search = new HT_Knowledge_Base_Live_Search();
}

