<?php

/*
* Pluggable common functions for the ht knowledge base
*/

if(!function_exists('ht_kb_breadcrumb_display')){
	/**
	 * Breadcrumbs display
	 * @pluggable
	 */
	function ht_kb_breadcrumb_display( $sep = '<span class="sep">/</span>' ) {
		//global WordPress variable $post. Needed to display multi-page navigations.
	    global $post, $cat; 

		if (!ht_kb_is_ht_kb_front_page()) { 
			$homelink = '<a href="' . home_url() . '" class="ht-breadcrumbs-home">' . __('Home') . '</a> ' . $sep;    

			echo '<div class="ht-breadcrumbs ht-kb-breadcrumbs" >';

			$taxonomy = ht_kb_get_taxonomy();
			$term_string = ht_kb_get_term();
			$visited = array();
						
			if (!empty($taxonomy) && !empty($term_string)) {
				//category terms bread crumb
				echo $homelink;

				echo '<a href="' . get_post_type_archive_link( 'ht_kb' ) . '">' . __('Knowledge Base', 'ht-knowledge-base') . '</a> ' . $sep; 

				$term = get_term_by( 'slug', $term_string, $taxonomy );
				
				if($term==false)
					return;

				if ($term->parent != 0) { 
					$parents =  get_custom_category_parents($term->term_id, 'ht_kb_category', true,'' .$sep . '', false, $visited );
					//remove last separator from parents
					$parents = substr($parents, 0, strlen($parents )- strlen($sep) );

					echo $parents;
				} else {
					echo '<a href="' . esc_attr(get_term_link($term, 'ht_kb_category')) . '" title="' . sprintf( __( "View all posts in %s" ), $term->name ) . '" ' . '>' . $term->name.'</a> ';
					$visited[] = $term->term_id;
				}
				

			} elseif ( !is_single() && 'ht_kb' == get_post_type() ) {
				//Archive		
				$ht_kb_data = get_post_type_object('ht_kb');
				echo $ht_kb_data->labels->name;	 

			} elseif ( is_single() && 'ht_kb' == get_post_type() ) {
				//Single post
				$terms = wp_get_post_terms( $post->ID , 'ht_kb_category');


				if( !empty($terms) ){
					foreach($terms as $term) {
						echo $homelink;

						echo '<a href="' . get_post_type_archive_link( 'ht_kb' ) . '">' . __('Knowledge Base', 'ht-knowledge-base') . '</a> ' . $sep; 
						
						if ($term->parent != 0) { 
							$parents =  get_custom_category_parents($term->term_id, 'ht_kb_category', true,'' .$sep . '', false, $visited );
							echo $parents;
						} else {
							echo '<a href="' . esc_attr(get_term_link($term, 'ht_kb_category')) . '" title="' . sprintf( __( "View all posts in %s" ), $term->name ) . '" ' . '>' . $term->name.'</a> ';
							echo $sep;
							$visited[] = $term->term_id;
						}
						echo get_the_title();
						echo '<br/>';

					} // End foreach
				} else {
					//uncategorized article
					echo '<a href="' . get_post_type_archive_link( 'ht_kb' ) . '">' . __('Knowledge Base', 'ht-knowledge-base') . '</a> ' . $sep; 
					echo get_the_title();
					echo '<br/>';
				}		
				
			} else {
					//Display the post title.
					echo get_the_title();
					echo '<br/>';
			}
					
			echo '</div>';	
		} //is_front_page
	} //end function
} //end function exists


if(!function_exists('get_term_parents')){
	/**
	 * Get the term parents
	 * @pluggable
	 */
	function get_term_parents( $id, $taxonomy, $link = false, $separator = '/', $nicename = false, $visited = array() ) {
	  $chain = '';
	  $parent = &get_term( $id, $taxonomy );
	  if ( is_wp_error( $parent ) )
	    return $parent;
	  if ( $nicename )
	    $name = $parent->slug;
	  else
	    $name = $parent->name;
	  if ( $parent->parent && ( $parent->parent != $parent->term_id ) && !in_array( $parent->parent, $visited ) ) {
	    $visited[] = $parent->parent;
	    $chain .= get_term_parents( $parent->parent, $taxonomy, $link, $separator, $nicename, $visited );
	  }
	  if ( $link )
	    $chain .= '<a href="' . esc_url( get_term_link( intval( $parent->term_id ), $taxonomy ) ) . '" title="' . esc_attr( sprintf( __( "View all posts in %s" ), $parent->name ) ) . '">'.$name.'</a>' . $separator;
	  else
	    $chain .= $name.$separator;
	  return $chain;
	}//end function
}//end function_exists


if(!function_exists('get_custom_category_parents')){
	/**
	 * Get the category parents
	 * @pluggable
	 */
	function get_custom_category_parents( $id, $taxonomy = false, $link = false, $separator = '/', $nicename = false, $visited = array() ) {

		if(!($taxonomy && is_taxonomy_hierarchical( $taxonomy )))
			return '';

		$chain = '';
		// $parent = get_category( $id );
		$parent = get_term( $id, $taxonomy);
		if ( is_wp_error( $parent ) )
			return $parent;

		if ( $nicename )
			$name = $parent->slug;
		else
			$name = $parent->name;

		//reset visited if null
		if(empty($visited))
			$visited = array( );

		if ( $parent->parent && 
			( $parent->parent != $parent->term_id ) && 
			(!in_array( $parent->parent, $visited ) ) ) {
			$visited[] = $parent->parent;
			// $chain .= get_category_parents( $parent->parent, $link, $separator, $nicename, $visited );
			$chain .= get_custom_category_parents( $parent->parent, $taxonomy, $link, $separator, $nicename, $visited );
		}

		if ( $link ) {
			// $chain .= '<a href="' . esc_url( get_category_link( $parent->term_id ) ) . '" title="' . esc_attr( sprintf( __( "View all posts in %s" ), $parent->name ) ) . '">'.$name.'</a>' . $separator;
			$chain .= '<a href="' . esc_url( get_term_link( (int) $parent->term_id, $taxonomy ) ) . '" title="' . esc_attr( sprintf( __( "View all posts in %s" ), $parent->name ) ) . '">'.$name.'</a>' . $separator;
		} else {
			$chain .= $name.$separator;
		}

		return $chain;
	}
}


if(!function_exists('ht_kb_related_articles')){
    /**
    * Display related articles
    * @pluggable
    */
    function ht_kb_related_articles(){
        global $post, $ht_knowledge_base_options;
        $orig_post = $post;
        $categories = get_the_terms($post->ID, 'ht_kb_category');

        if ($categories) {  
            $category_ids = array();
            foreach($categories as $individual_category) 
                $category_ids[] = $individual_category->term_id;

            $args=array(
	            'post_type' => 'ht_kb',
	            'tax_query' => array(
	                array(
	                    'taxonomy' => 'ht_kb_category',
	                    'field' => 'term_id',
	                    'terms' => $category_ids
	                )
	            ),
	            'post__not_in' => array($post->ID),
	            'posts_per_page'=> 6, // Number of related posts that will be shown.
	            'ignore_sticky_posts'=>1
            );

            $related_articles_query = new wp_query( $args );
            
            if( $related_articles_query->have_posts() ) { ?>
             
             <section id="ht-kb-related-articles" class="clearfix">
             <h3 id="ht-kb-related-articles-title"><?php _e('Related Articles', 'ht-knowledge-base'); ?></h3>
                <ul class="ht-kb-article-list"><?php

	            while( $related_articles_query->have_posts() ) {
	                $related_articles_query->the_post();
	            
				  	//set post format class  
	                if ( has_post_format( 'video' )) { 
	                  $ht_kb_format_class = 'format-video';
	                } else {
	                  $ht_kb_format_class = 'format-standard';
	                }

	               ?>
	                
	                <li class="<?php echo $ht_kb_format_class; ?>">
	                	<a href="<?php the_permalink()?>" rel="bookmark" title="<?php echo esc_attr( sprintf( the_title_attribute( 'echo=0' ) ) ); ?>"><?php the_title(); ?></a>
	                	<?php if ( $ht_knowledge_base_options['related-rating'] && function_exists('ht_usefulness') ) {      
					        $article_usefulness = ht_usefulness( get_the_ID() );
					        $helpful_article = ( $article_usefulness >= 0 ) ? true : false;
					        $helpful_article_class = ( $helpful_article ) ? 'ht-kb-helpful-article' : 'ht-kb-unhelpful-article';
					      ?>
					        <span class="ht-kb-usefulness <?php echo $helpful_article_class; ?>"><?php echo $article_usefulness  ?></span>
					      <?php 

					  	  } //rating ?>
	                </li>

	        	<?php } ?>
        		</ul>
    		</section>
        <?php    } //end $related_articles_query
         } //end if  categories
        $post = $orig_post;
        wp_reset_postdata();
    }
}


if(!function_exists('ht_kb_entry_meta_display')){
	/**
	* Display entry meta
	* @pluggable
	*/
	function ht_kb_entry_meta_display(){
			global $post; ?>

			<ul class="ht-kb-entry-meta clearfix">

				<li class="ht-kb-em-date"> 
				    <span><?php _e( 'Created' , 'ht-knowledge-base' ) ?></span>
				    <a href="<?php the_permalink(); ?>" rel="bookmark" itemprop="url"><time datetime="<?php the_time('Y-m-d')?>" itemprop="datePublished"><?php the_time( get_option('date_format') ); ?></time></a>
				</li>
				<li class="ht-kb-em-author">
					<span><?php _e( 'Author' , 'ht-knowledge-base' ) ?></span>
					<a class="url fn n" href="<?php echo esc_url( get_author_posts_url( get_the_author_meta( 'ID' ) ) ); ?>" title="<?php echo esc_attr( get_the_author() ); ?>" rel="me" itemprop="author"><?php echo esc_attr( get_the_author() ); ?></a>
				</li>
				<?php if( !is_tax() ) : ?>
					<li class="ht-kb-em-category">
					    <span><?php _e( 'Category' , 'ht-knowledge-base' ) ?></span>
					    <?php 
						    $terms = get_the_term_list( $post->ID, 'ht_kb_category', ' ', ', ', '' );
						    if(empty($terms)){
						    	_e('Uncategorized', 'ht-knowledge-base');
						    } else {
						    	echo $terms;
						    }
					    ?>
					</li>
				<?php endif; //is tax ?>
				<?php if ( comments_open() && get_comments_number() > 0 ){ ?>
					<li class="ht-kb-em-comments">
					    <span><?php _e( 'Comments' , 'ht-knowledge-base' ) ?></span>
						<?php comments_popup_link( __( '0', 'ht-knowledge-base' ), __( '1', 'ht-knowledge-base' ), __( '%', 'ht-knowledge-base' ) ); ?>
					</li>
				<?php } ?>

			</ul>

		<?php
	} //end function
}//end function exists


if(!function_exists('ht_kb_display_attachments')){
	/**
	* Display article attachments
	* @pluggable
	*/
	function ht_kb_display_attachments(){
			global $post;
			$attachments = get_post_meta($post->ID, '_ht_knowledge_base_file_advanced', true);
			if( ! empty( $attachments ) ): ?>

			<section id="ht-kb-attachments">
				<h3 class="ht-kb-attachments"><?php _e('Article Attachments', 'ht-knowledge-base'); ?></h3>
				<ul>
					<?php
						foreach ($attachments as $id => $attachment) {

							$attachment_post  = get_post($id);
							$default_attachment_name = __('Attachment', 'ht-knowledge-base');
							$attachment_name = !empty($attachment_post) ? $attachment_post->post_name : $default_attachment_name;
							?>
							<li class="ht-kb-attachment-item">
								<a href="<?php echo wp_get_attachment_url($id); ?>"><?php echo $attachment_name; ?></a>
							</li>
							
							<?php
						}
					?>
				</ul>

			</section><!-- article-attachments -->

		<?php endif; //end test for 
	} //end function
} //end function exists


if(!function_exists('ht_kb_display_tags')){
	/**
	* Display article tags
	* @pluggable
	*/
	function ht_kb_display_tags(){
		global $post; ?>
		<div class="tags">
			<?php echo get_the_term_list( $post->ID, 'ht_kb_tag', __('Tagged: ', 'ht-knowledge-base'), '', '' );?>
		</div>
		<?php
	} //end function
}//end function exists


if(!function_exists('ht_kb_display_search')){
	/**
	* Display article search
	* @pluggable
	*/
	function ht_kb_display_search(){
		global $post, $ht_knowledge_base_options; 
		$placeholder_text = 	(isset($ht_knowledge_base_options) && is_array($ht_knowledge_base_options) && array_key_exists('search-placeholder-text', $ht_knowledge_base_options)) ? 
								$ht_knowledge_base_options['search-placeholder-text'] : 
								__('Search the Knowledge Base', 'ht-knowledge-base');

		?>
		<div class="ht-kb-article-search">
			<form role="search" method="get" id="searchform" class="searchform" action="<?php echo home_url( '/' ); ?>">
				<label class="screen-reader-text" for="s"><?php _e( 'Search for:', 'ht-knowledge-base' ); ?></label>
				<input type="text" value="" placeholder="<?php echo $placeholder_text; ?>" name="s" id="s" autocomplete="off" />
				<input type="hidden" name="ht-kb-search" value="1" />
				<button type="submit" id="searchsubmit"> <i class="fa fa-search"></i> <span><?php _e( 'Search', 'ht-knowledge-base' ); ?></span> </button>
		</form>
		</div>
		<?php
	} //end function
}//end function exists


if(!function_exists('ht_kb_display_sub_cats')){
	/**
	* Display sub categories
	* @pluggable
	*/
	function ht_kb_display_sub_cats($master_tax_terms, $parent_term_id, $depth, $display_sub_cat_count, $display_sub_cat_articles, $numberposts){
		//return if max depth reached
		if($depth==0){
			return;
		}

		//populate child tax terms
		$child_tax_terms = wp_list_filter($master_tax_terms,array('parent'=>$parent_term_id));
		if(count($child_tax_terms)>0) { ?>

			<ul class="ht-kb-sub-cats">
			<?php
			foreach ($child_tax_terms as $term) {
				?>
				<li class="ht-kb-sub-cat">
					
					<a class="ht-kb-sub-cat-title" href="<?php echo esc_attr(get_term_link($term, 'ht_kb_category')); ?>" title="<?php echo sprintf( __( 'View all posts in %s', 'ht-knowledge-base' ), $term->name ); ?>" ><?php echo $term->name; ?></a>
					<?php if($display_sub_cat_count): ?>
						<span class="ht-kb-category-count"><?php echo sprintf( _n( '1 Article', '%s Articles', $term->count, 'ht-knowledge-base' ), $term->count ); ?></span>
					<?php endif; ?>
					<?php
					//recursive
					ht_kb_display_sub_cats($master_tax_terms, $term->term_id, $depth--, $display_sub_cat_count, $display_sub_cat_articles, $numberposts);

					//continue if $display_sub_cat_articles == false
					if(!$display_sub_cat_articles){
						continue;
					}

					//else show all the articles in this category

					//get posts per category
					$args = array( 
						'post_type'  => 'ht_kb',
						'posts_per_page' => $numberposts,
						'orderby' => 'date',
						'tax_query' => array(
							array(
								'taxonomy' => 'ht_kb_category',
								'field' => 'term_id',
								'include_children' => false,
								'terms' => $term->term_id
							)
						)
					);
					$sub_cat_posts = get_posts( $args ); ?>
					<?php if ($sub_cat_posts) : ?>
					<ul class="ht-kb-article-list">

						<?php
						foreach( $sub_cat_posts as $post ) : ?>
									<?php
										  	//set post format class  
							                if ( get_post_format( $post->ID )=='video') { 
							                  $ht_kb_format_class = 'format-video';
							                } else {
							                  $ht_kb_format_class = 'format-standard';
							                }
						            ?>
									<li class="<?php echo $ht_kb_format_class; ?>"><a href="<?php echo get_permalink($post->ID); ?>" rel="bookmark"><?php echo get_the_title($post->ID); ?></a></li>
						<?php endforeach; ?>
					</ul><!-- End Get posts per Category -->
				<?php endif; // End if ($sub_cat_posts) ?>
				</li> <!--  /.ht-kb-sub-cat -->

				<?php

			}
			?>
			</ul><!--/sub-cats-->
			<?php
		}
	}//end function
}//end function exists



if(!function_exists('get_ht_kb_term_meta')){
	/**
	* Get term meta
	* @pluggable
	* @return (Array) The term meta
	*/
	function get_ht_kb_term_meta($term){
			//// put the term ID into a variable
			$t_id = $term->term_id;
		 
			// retrieve the existing value(s) for this meta field. This returns an array
			$term_meta = get_option( "taxonomy_$t_id" );

			return $term_meta;
	}//end function
}//end function exists


if(!function_exists('get_ht_kb_tags')){
	/**
	* Get all knowledgebase tags
	* @pluggable
	* @return (Array) The ht_kb_tag taxonomy terms
	*/
	function get_ht_kb_tags(){
		$taxonomies = array('ht_kb_tag');

		$args = array(
		    'orderby'       => 'name', 
		    'order'         => 'ASC',
		    'hide_empty'    => true, 
		    'exclude'       => array(), 
		    'exclude_tree'  => array(), 
		    'include'       => array(),
		    'number'        => '', 
		    'fields'        => 'all', 
		    'slug'          => '', 
		    'parent'         => '',
		    'hierarchical'  => true, 
		    'child_of'      => 0, 
		    'get'           => '', 
		    'name__like'    => '',
		    'pad_counts'    => false, 
		    'offset'        => '', 
		    'search'        => '', 
		    'cache_domain'  => 'core'
		);

		$tags = get_terms( $taxonomies, $args );

		return $tags;
	}//end function
}//end function exists


if(!function_exists('get_ht_kb_categories')){
	/**
	* Get all knowledgebase categories
	* @pluggable
	* @return (Array) The ht_kb_category taxonomy terms
	*/
	function get_ht_kb_categories(){
		$taxonomies = array('ht_kb_category');

		$args = array(
		    'orderby'       => 'name', 
		    'order'         => 'ASC',
		    'hide_empty'    => true, 
		    'exclude'       => array(), 
		    'exclude_tree'  => array(), 
		    'include'       => array(),
		    'number'        => '', 
		    'fields'        => 'all', 
		    'slug'          => '', 
		    'parent'         => '',
		    'hierarchical'  => true, 
		    'child_of'      => 0, 
		    'get'           => '', 
		    'name__like'    => '',
		    'pad_counts'    => false, 
		    'offset'        => '', 
		    'search'        => '', 
		    'cache_domain'  => 'core'
		);

		$categories = get_terms( $taxonomies, $args );

		return $categories;
	}//end function
}//end function exists

if(!function_exists('get_most_helpful_article_id')){
	/**
	* Get the id of the most helpful article
	* @pluggable
	* @return (Int) ID of most helpful article
	*/
	function get_most_helpful_article_id(){
		$most_helpful_article_id = 0;
		
		$most_helpful = new WP_Query('meta_key=_ht_kb_usefulness&post_type=ht_kb&orderby=meta_value_num&order=DESC');
		if ($most_helpful->have_posts()) : 
			while ($most_helpful->have_posts()) : $most_helpful->the_post(); 
				$most_helpful_article_id = get_the_ID();
				//this is the most helpful, break.
				break;
			endwhile; 
		endif;
		wp_reset_postdata();
		return $most_helpful_article_id;
	}//end function
}//end function exists

if(!function_exists('is_most_helpful_article_id')){
	/**
	* Is the ID of the most helpful article
	* @pluggable
	* @param (Int) $article_id The test article ID
	* @return (Boolean) True when article ID matches most helpful article ID
	*/
	function is_most_helpful_article_id($article_id){
		$most_helpful_article_id = get_most_helpful_article_id();
		return $most_helpful_article_id == $article_id;
	}//end function
}//end function exists

if(!function_exists('display_is_most_helpful_article')){
	/**
	* Displays badge if most helpful article
	* @pluggable
	*/
	function display_is_most_helpful_article(){
		global $post;
		if(is_most_helpful_article_id($post->ID)){
			?>
			<span class="ht-kb-most-helpful-article">Most Helpful Article</span>
			<?php
		}
	}//end function
}//end function exists

if(!function_exists('get_most_viewed_article_id')){
	/**
	* Get the id of the most viewed article
	* @pluggable
	* @return (Int) ID of most viewed article
	*/
	function get_most_viewed_article_id(){
		
		$most_viewed_article_id = 0;
		$most_viewed = new WP_Query('meta_key=_ht_kb_post_views_count&post_type=ht_kb&orderby=meta_value_num&order=DESC');
		if ($most_viewed->have_posts()) : 
			while ($most_viewed->have_posts()) : $most_viewed->the_post(); 
				$most_viewed_article_id = get_the_ID();
				//this is the most viewed, break.
				break;
			endwhile; 
		endif;
		wp_reset_postdata();
		return $most_viewed_article_id;

		return 0;
	}//end function
}//end function exists

if(!function_exists('is_most_viewed_article_id')){
	/**
	* Is the id of the most viewed article
	* @pluggable
	* @param (Int) $article_id The test article ID
	* @return (Boolean) True when article ID matches most viewed article ID
	*/
	function is_most_viewed_article_id($article_id){
		$most_viewed_article_id = get_most_viewed_article_id();
		return $most_viewed_article_id == $article_id;
	}//end function
}//end function exists

if(!function_exists('display_is_most_viewed_article')){
	/**
	* Display badge if most viewed article
	* @pluggable
	*/
	function display_is_most_viewed_article(){
		global $post;
		if(is_most_viewed_article_id($post->ID)){ ?>
			<span class="ht-kb-most-viewed-article">Most Viewed Article</span>
		<?php	}
	}//end function
}//end function exists

if(!function_exists('get_most_helpful_user_id')){
	/**
	* Get the id helpful user id
	* @pluggable
	* @return (Int) ID of most helpful user
	*/
	function get_most_helpful_user_id(){
		//start here use WP_User_Query
		//this *should* be orderby meta_value_num, but not available 
		$users = get_users('meta_key=_ht_kb_usefulness&orderby=meta_value&order=DESC');
		if (!empty($users)) : 
			foreach ($users as $key => $user) {
				return $user->ID;
			}
		endif;
		return 0;
	}//end function
}//end function exists


if(!function_exists('is_most_helpful_user_id')){
	/**
	* Is the id of the most helpful user
	* @pluggable
	* @param (String) $user_id The test user ID
	* @return (Boolean) True when user ID matches most helpful user ID
	*/
	function is_most_helpful_user_id($user_id){
		$most_helpful_user_id = get_most_helpful_user_id();
		return $most_helpful_user_id == $user_id;
	}//end function
}//end function exists


if(!function_exists('display_is_most_helpful_user')){
	/**
	* Is the id of the most helpful user
	* @pluggable
	* @param (String) $user_id The test user ID
	*/
	function display_is_most_helpful_user($user_id){
		if( is_most_helpful_user_id( $user_id ) ){ ?>
			<span class="ht-kb-most-helpful-user">Most Helpful User</span>
		<?php	}
	}//end function
}//end function exists

if(!function_exists('ht_kb_display_uncategorized_articles')){
	/**
	* Display uncategorized articles
	* @pluggable
	*/
	function ht_kb_display_uncategorized_articles(){
		global $ht_kb_display_uncategorized_articles, $ht_knowledge_base_options;
		//now getting uncategorized posts
		$ht_kb_display_uncategorized_articles = true;

		//set number of articles to fetch
		$numberposts = 100;
		//$numberposts = (array_key_exists('tax-cat-article-number', $ht_knowledge_base_options)) ? $ht_knowledge_base_options['tax-cat-article-number'] : 10;

		//get the master tax terms
		$args = array(
			'orderby'       =>  'term_order',
			'depth'         =>  0,
			'child_of'      => 	0,
			'hide_empty'    =>  0,
			'pad_counts'   	=>	true
		); 
		$master_tax_terms = get_terms('ht_kb_category', $args);
		//get the top level terms, now unused
		$top_level_tax_terms = wp_list_filter($master_tax_terms,array('parent'=>0));
		$tax_terms_ids = array();
		if( !empty ($master_tax_terms ) && !is_a( $master_tax_terms, 'WP_Error' ) && is_array( $master_tax_terms ) ){
			foreach ( (array)$master_tax_terms as $key => $term ) {
				array_push($tax_terms_ids, $term->term_id);
			}
		}
		$args = array( 
				'numberposts' => $numberposts, 
				'post_type'  => 'ht_kb',
				'orderby' => 'date',
				'suppress_filters' => false,
				'tax_query' => array(
					array(
						'taxonomy' => 'ht_kb_category',
						'field' => 'term_id',
						'include_children' => false,
						'terms' => $tax_terms_ids,
						'operator'  => 'NOT IN'
					)
				)
			);

		$uncategorized_posts = get_posts( $args );  ?>
		<?php if( !empty( $uncategorized_posts ) && !is_a( $uncategorized_posts, 'WP_Error' ) ): ?>
			<div class="ht-kb-category-header">
			<h2 class="ht-kb-category-title">
							<?php _e( 'Uncategorized', 'ht-knowledge-base'); ?>
						</h2>
						</div>
			<ul class="ht-kb-article-list">

				<?php foreach( $uncategorized_posts as $post ) : ?>

					<?php
						  	//set post format class  
			                if ( get_post_format( $post->ID )=='video') { 
			                  $ht_kb_format_class = 'format-video';
			                } else {
			                  $ht_kb_format_class = 'format-standard';
			                }
		            ?>

					<li class="<?php echo $ht_kb_format_class; ?>"><a href="<?php echo get_permalink($post->ID); ?>" rel="bookmark"><?php echo get_the_title($post->ID); ?></a></li>
						    
				<?php endforeach; ?>

			</ul><!-- end get posts per category -->
		<?php endif; 

		//finished getting uncategorized posts
		$ht_kb_display_uncategorized_articles = false;
	}//end function
}//end function exists

if(!function_exists('ht_kb_display_archive')){
	/**
	* Display archive articles
	* @pluggable
	*/
	function ht_kb_display_archive($columns=2, $sub_cat_depth=2, $display_sub_cat_count=true, $display_sub_cat_articles=true, $sort_by='date', $sort_order='asc'){
		global $ht_kb_display_archive, $ht_knowledge_base_options;
		//now displaying archive posts
		$ht_kb_display_archive = true;

		//set user options
		$columns = (array_key_exists('archive-columns', $ht_knowledge_base_options)) ? $ht_knowledge_base_options['archive-columns'] : $columns;
		$sort_by = (array_key_exists('sort-by', $ht_knowledge_base_options)) ? $ht_knowledge_base_options['sort-by'] : $sort_by;
		$sort_order = (array_key_exists('sort-order', $ht_knowledge_base_options)) ? $ht_knowledge_base_options['sort-order'] : $sort_order;
		$sub_cat_display = (array_key_exists('sub-cat-display', $ht_knowledge_base_options)) ? $ht_knowledge_base_options['sub-cat-display'] : $sub_cat_display;
		$sub_cat_depth = (array_key_exists('sub-cat-depth', $ht_knowledge_base_options)) ? $ht_knowledge_base_options['sub-cat-depth'] : $sub_cat_depth;
		$display_sub_cat_count = (array_key_exists('sub-cat-article-count', $ht_knowledge_base_options)) ? $ht_knowledge_base_options['sub-cat-article-count'] : $display_sub_cat_count;
		$display_sub_cat_articles = (array_key_exists('sub-cat-article-display', $ht_knowledge_base_options)) ? $ht_knowledge_base_options['sub-cat-article-display'] : $display_sub_cat_articles;

		//set number of posts to sub cat article number or global posts_per_page option
		$numberposts = (array_key_exists('sub-cat-article-number', $ht_knowledge_base_options)) ? $ht_knowledge_base_options['sub-cat-article-number'] : get_option('posts_per_page');

		//list terms in a given taxonomy
		$args = array(
			'orderby'       =>  'term_order',
			'depth'         =>  0,
			'child_of'      => 	0,
			'hide_empty'    =>  0,
			'pad_counts'   	=>	true,
		); 
		$master_tax_terms = get_terms('ht_kb_category', $args);
		$tax_terms = wp_list_filter($master_tax_terms,array('parent'=>0));	?>


		<?php 
		//category count (terms)
		$ht_kb_category_count = count($tax_terms);
		//category counter
		$cat_counter = 0;
		foreach ($tax_terms as $tax_term) { ?>

			<?php if( $cat_counter%$columns == 0 ): ?>
				<!--.ht-grid-->
				<div class="ht-grid ht-grid-gutter-20 ht-grid-gutter-bottom-40">
			<?php else: ?>
				
			<?php endif; ?>

			<?php $grid_class_int = 12/$columns; ?>

			<!--.ht-grid-col-->
			<div class="ht-grid-col ht-grid-<?php echo $grid_class_int; ?>">

				<!--.ht-kb-category-->
				<div id="ht-kb-category-<?php echo $tax_term->term_id; ?>" class="ht-kb-category">

					<?php $term_meta = get_ht_kb_term_meta($tax_term);
					$category_thumb_att_id = 0;
					$category_color = '#434345'; 
					$ht_kb_tax_desc =  $tax_term->description;

					if(is_array($term_meta)&&array_key_exists('meta_image', $term_meta)&&!empty($term_meta['meta_image']))
						$category_thumb_att_id = $term_meta['meta_image'];

					if(is_array($term_meta)&&array_key_exists('meta_color', $term_meta)&&!empty($term_meta['meta_color']))
						$category_color = $term_meta['meta_color'];
					?>

					<?php if( !empty( $category_thumb_att_id ) && $category_thumb_att_id!=0 ){
						$ht_kb_category_class = "ht-kb-category-hasthumb";
						$data_ht_category_custom_icon = 'true';
					} else {
						$ht_kb_category_class = "ht-kb-category-hasicon";
						$data_ht_category_custom_icon = 'false';
					} 

					if ( !empty( $ht_kb_tax_desc ) ) {
						$data_ht_kb_hasdesc = 'true';
					} else {
						$data_ht_kb_hasdesc = 'false';
					}
					?>

					<!--.ht-kb-category-header-->
					<div class="ht-kb-category-header clearfix" data-ht-category-color="false" data-ht-category-color-hex="<?php echo $category_color; ?>" data-ht-category-icon="true" data-ht-category-custom-icon="<?php echo $data_ht_category_custom_icon; ?>" data-ht-category-desc="<?php echo $data_ht_kb_hasdesc; ?>">

						<?php if( !empty( $category_thumb_att_id ) && $category_thumb_att_id!=0 ) :
							$category_thumb_obj = wp_get_attachment_image_src( $category_thumb_att_id, 'ht-kb-thumb');
							$category_thumb_src = $category_thumb_obj[0]; ?>
							<div class="ht-kb-category-thumb">
								<img src="<?php echo $category_thumb_src ?>" alt="" />
							</div>

						<?php endif; ?>

						<h2 class="ht-kb-category-title">
							<a href="<?php echo esc_attr(get_term_link($tax_term, 'ht_kb_category')) ?>" title="<?php echo sprintf( __( 'View all posts in %s', 'ht-knowledge-base' ), $tax_term->name ) ?>"><?php echo $tax_term->name ?></a>
							<?php if($display_sub_cat_count): ?>
								<span class="ht-kb-category-count"><?php echo sprintf( _n( '1 Article', '%s Articles', $tax_term->count, 'ht-knowledge-base' ), $tax_term->count ); ?></span>
							<?php endif; ?>
						</h2>

						<?php $ht_kb_tax_desc =  $tax_term->description; ?>
						<?php if( !empty($ht_kb_tax_desc) ): ?>
							<p class="ht-kb-category-desc"><?php echo $ht_kb_tax_desc ?></p>
						<?php endif; ?>

					</div>	
					<!--/.ht-kb-category-header-->

					<?php 
					if($sub_cat_display && $sub_cat_depth){
						ht_kb_display_sub_cats($master_tax_terms, $tax_term->term_id, $sub_cat_depth, $display_sub_cat_count, $display_sub_cat_articles, $numberposts);
					}
					
					//get posts per category
					$args = array( 
						'post_type'  => 'ht_kb',
						'posts_per_page' => $numberposts,
						'order' => $sort_order,
						'orderby' => $sort_by,
						'suppress_filters' => 0,
						'tax_query' => array(
							array(
								'taxonomy' => 'ht_kb_category',
								'field' => 'term_id',
								'include_children' => false,
								'terms' => $tax_term->term_id
							)
						)
					);

					$cat_posts = get_posts( $args ); ?>

						<?php if( !empty( $cat_posts ) && !is_a( $cat_posts, 'WP_Error' ) ): ?>

							<ul class="ht-kb-article-list">
								<?php foreach( $cat_posts as $post ) : ?>
									<?php
										  	//set post format class  
							                if ( get_post_format( $post->ID )=='video') { 
							                  $ht_kb_format_class = 'format-video';
							                } else {
							                  $ht_kb_format_class = 'format-standard';
							                }
						            ?>
										<li class="<?php echo $ht_kb_format_class; ?>"><a href="<?php echo get_permalink($post->ID); ?>" rel="bookmark"><?php echo get_the_title($post->ID); ?></a></li>
								<?php endforeach; ?>
								<!-- show all articles --> 
								<?php 
									global $wp_query; 
									$term_link = get_term_link( $tax_term );
									$link = is_wp_error( $term_link ) ? '#' : esc_url( $term_link );
								?>
								<?php if( count($cat_posts) < $tax_term->count ): ?>
									<li class="ht-kb-show-all-articles">
										<a href="<?php echo $link; ?>" rel="bookmark"><?php echo sprintf( __('Show all %s articles', 'ht-knowledge-base'), $tax_term->count ); ?></a>
									</li>					
								<?php endif; ?>
							</ul><!-- End Get posts per Category -->

						<?php endif; ?>

				</div>
				<!--/.ht-kb-category--> 

			</div>
			<!--/.ht-grid-col--> 

			<?php
				//increment counter
				$cat_counter+=1;
			?>

			<?php if( ($cat_counter)%$columns == 0 || $cat_counter == $ht_kb_category_count ) : ?>
				</div>
				<!-- /.ht-grid --> 
			<?php endif; ?>


			<?php


		} // close list terms in a given taxonomy
		
		//finished displaying archive posts
		$ht_kb_display_archive = false;

	}//end function
}//end function exists


if(!function_exists('ht_kb_display_view_count')){
	/**
	* Display article view count
	* @pluggable
	*/
	function ht_kb_display_view_count( $post_id=null ){
		global $ht_knowledge_base_options, $post;

		//set post_id
		$post_id = (empty($post_id)) ? $post->ID : $post_id;
		$count_key = HT_KB_POST_VIEW_COUNT_KEY;
		$views = get_post_meta($postID, $count_key, true);
		
		//hard set
		if(empty($views)){
			$views = 0;
		}

		?>
			<div class="ht-kb-view-count">
				<span><?php echo sprintf( _n( '1 View', '%s Views', $views, 'ht-knowledge-base' ), $views ); ?></span>
			</div>
		<?php


	}//end function
}//end function exists
