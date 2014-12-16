<?php
/*
*	Plugin Name: Heroic Knowledge Base
*	Plugin URI:  http://herothemes.com
*	Description: Knowledge Base plugin for WordPress 
*	Author: Hero Themes
*	Version: 1.2
*	Author URI: http://www.herothemes.com/
*	Text Domain: ht-knowledge-base
*/


if( !class_exists( 'HT_Knowledge_Base' ) ){

	if(!defined('HT_VOTING_KEY')){
		define('HT_VOTING_KEY', '_ht_voting');
	}

	if(!defined('HT_USEFULNESS_KEY')){
		define('HT_USEFULNESS_KEY', '_ht_kb_usefulness');
	}

	if(!defined('HT_KB_POST_VIEW_COUNT_KEY')){
		define('HT_KB_POST_VIEW_COUNT_KEY', '_ht_kb_post_views_count');
	}

	//knowledge base cpt slug
	if(!defined('KB_CPT_SLUG')){
		define('KB_CPT_SLUG', 'knowledge-base');
	}

	//knowledge base category slug
	if(!defined('KB_CAT_SLUG')){
		define('KB_CAT_SLUG', 'article-categories');
	}

	//knowlege base tag slug
	if(!defined('KB_TAG_SLUG')){
		define('KB_TAG_SLUG', 'article-tags');
	}

	//required for Redux framework
	if(!defined('SECURE_AUTH_KEY')){
		define('SECURE_AUTH_KEY', 'HtKnOwLEdGEbase');
	}

	class HT_Knowledge_Base {

		private $temp_query;
		public $is_single, $is_ht_kb_category_tax, $is_ht_kb_tag_tax, $is_ht_kb_search, $ht_kb_is_ht_kb_front_page, $nothing_found, $taxonomy, $term;


		//Constructor
		function __construct(){
			load_plugin_textdomain('ht-knowledge-base', false, basename( dirname( __FILE__ ) ) . '/languages' );
			
			//register the ht_kb custom post type
			add_action( 'init', array( $this,  'register_ht_knowledge_base_cpt' ) );
			//register the ht_kb_category taxonomy
			add_action( 'init', array( $this,  'register_ht_knowledge_base_category_taxonomy' ) );
			//register the ht_kb_tag taxonomy
			add_action( 'init', array( $this,  'register_ht_knowledge_base_tag_taxonomy' ) );
			//maybe flush rewrite rules, lower priority
			add_action( 'init', array( $this,  'ht_knowledge_base_maybe_flush_rewrite' ), 30 );
			//display custom meta in the articles listing in the admin
			add_action( 'manage_ht_kb_posts_custom_column' , array( $this,  'data_kb_post_views_count_column' ), 10, 2 );
			//display notice if ht voting not installed
			add_action( 'admin_notices', array( $this,  'ht_kb_voting_warning' ), 10 );
			

			
			//filter the templates - note this will be overriden if theme uses single-ht_kb or archive-ht_kb
			add_filter( 'template_include', array( $this, 'ht_knowledge_base_custom_template' ) );
			//manage columns
			add_filter( 'manage_ht_kb_posts_columns',  array( $this,  'add_kb_post_views_count_column' ) );
			//sortable columns
			add_filter( 'manage_edit-ht_kb_sortable_columns', array( $this, 'register_kb_post_views_count_sortable_columns' ) );
			//column sortable filter
			add_filter( 'pre_get_posts', array( $this, 'kb_post_views_count_orderby' ), 10000 );
			

			//filter content for ht_kb
			add_filter( 'the_content', array( $this, 'ht_knowledge_base_custom_content' ) );

			//filter comments, disabled in this release
			add_filter( 'get_comment_text', array( $this, 'kb_filter_get_comments' ), 10, 3 );

			//search filter
			add_filter( 'pre_get_posts', array( $this, 'ht_kb_pre_get_posts_filter' ), 10 );

			//sort order
			add_filter( 'pre_get_posts', array( $this, 'ht_kb_modify_search_order_pre_get_posts' ), 50 );

			//number of posts in taxonomy
			add_filter( 'pre_get_posts', array( $this, 'ht_kb_posts_per_taxonomy' ), 50 );

			//comments open filter
			add_filter( 'comments_open', array( $this, 'ht_kb_comments_open' ), 10, 2 );

			//comments template filter
			add_filter( 'comments_template', array( $this, 'ht_kb_comments_template' ), 10 );

			//add to menu items
			add_action( 'admin_head-nav-menus.php', array( $this, 'ht_knowledge_base_menu_metabox' ) );
			add_filter( 'wp_get_nav_menu_items', array( $this,'ht_knowledge_base_archive_menu_filter'), 10, 3 );

			//add filter for ht_kb archive title
			add_filter( 'wp_title', array( $this,'ht_kb_archive_title_filter'), 10, 3 );

			//add shortcode for ht_kb display
			add_shortcode( 'ht_kb_shortcode_archive', array( $this, 'ht_kb_shortcode_archive_display' ) );

			//custom front page
			add_action( 'pre_get_posts', array( $this, 'ht_knowledge_base_custom_front_page' ), 10 );
			//remove actions
			//to keep the count accurate, remove prefetching
			//@todo - there is probably a better way to do this, eg with a hook
			remove_action( 'wp_head', 'adjacent_posts_rel_link_wp_head', 10, 0);	

			//set posts views and article helpfulness to 0
			add_action('publish_ht_kb', array( $this, 'ht_kb_article_publish' ), 10, 2);

			//add custom image size
			add_image_size( 'ht-kb-thumb', 50, 50 );

			//get_pages	filter
			add_filter( 'get_pages', array( $this, 'ht_kb_filter_get_pages' ));	

			//categories widget
			include_once('widgets/widget-kb-categories.php');
			//articles widget
			include_once('widgets/widget-kb-articles.php');
			//authors widget
			include_once('widgets/widget-kb-authors.php');
			//common functions
			include_once('php/ht-knowledge-base-common-display-functions.php');
			//meta-boxes
			include_once('php/ht-knowledge-base-meta-boxes.php');
			//category ordering
			include_once('php/ht-knowledge-base-category-ordering.php');
			//category meta
			include_once('php/ht-knowledge-base-category-meta.php');
			//live search
			include_once('php/ht-knowledge-base-live-search.php');

			//redux framework
			require_once( dirname(__FILE__) . '/redux-framework/redux-framework.php');
			//options
			require_once( 'php/ht-knowledge-base-options.php' );


			//activation hook
			register_activation_hook( __FILE__, array( 'HT_Knowledge_Base', 'ht_knowlege_base_plugin_activation_hook' ) );

			//deactivation hook
			register_deactivation_hook( __FILE__, array( 'HT_Knowledge_Base', 'ht_knowlege_base_plugin_deactivation_hook' ) );	
		}


		/**
		* Initial activation to add option flush the rewrite rules
		*/
		static function ht_knowlege_base_plugin_activation_hook(){
			//flush the rewrite rules 
		 	add_option('ht_kb_flush_rewrite_required', 'true');

		 	//perform upgrade actions
		 	HT_Knowledge_Base::ht_kb_plugin_activation_upgrade_actions();

		 	//add term_order to terms table
		 	HT_Knowledge_Base::knowledgebase_customtaxorder_activate();
		}

		/**
		* Initial activation to add option flush the rewrite rules
		*/
		static function ht_knowlege_base_plugin_deactivation_hook(){
			//remove flush the rewrite rules option
		 	delete_option('ht_kb_flush_rewrite_required');
		}

		/**
		* Register the ht_kb custom post type
		*/
		function register_ht_knowledge_base_cpt(){
			$singular_item = _x('Article', 'Post Type Singular Name', 'ht-knowledge-base');
			$plural_item = _x('Articles', 'Post Type Plural Name', 'ht-knowledge-base');
			$kb_item = __('Knowledge Base', 'ht-knowledge-base');
			$rewrite = $this->get_cpt_slug();

		  	$labels = array(
			    'name'	      =>  $singular_item,
			    'singular_name'      => __('Article', 'ht-knowledge-base'),
			    'add_new'            => __('Add New', 'ht-knowledge-base') . ' ' .  $singular_item,
			    'add_new_item'       => __('Add New', 'ht-knowledge-base') . ' ' .  $singular_item,
			    'edit_item'          => __('Edit', 'ht-knowledge-base') . ' ' .  $singular_item,
			    'new_item'           => __('New', 'ht-knowledge-base') . ' ' .  $singular_item,
			    'all_items'          => __('All', 'ht-knowledge-base') . ' ' .  $plural_item,
			    'view_item'          => __('View', 'ht-knowledge-base') . ' ' .  $singular_item,
			    'search_items'       => __('Search', 'ht-knowledge-base') . ' ' .  $plural_item,
			    'not_found'          => sprintf( __( 'No %s found', 'ht-knowledge-base' ), $plural_item ),
			    'not_found_in_trash' => sprintf( __( 'No %s found in trash', 'ht-knowledge-base' ), $plural_item ),
			    'parent_item_colon'  => '',
			    'menu_name'          => $kb_item,
		  	);

			$args = array(
				'labels'             => $labels,
				'public'             => true,
				'publicly_queryable' => true,
				'show_ui'            => true,
				'show_in_menu'       => true,
				'show_in_nav_menus'	 => false,
				'query_var'          => true,
				'rewrite'            => array( 'slug' => $rewrite, 'with_front'	=>	false ),
				'capability_type'    => 'post',
				'has_archive'        => true,
				'hierarchical'       => false,
				'menu_position'      => null,
				'supports'           => array( 'title', 'editor', 'author', 'comments', 'post-formats', 'custom-fields' )
			);

		  register_post_type( 'ht_kb', $args );
		}

		/**
		* Get the slug for the custom post type
		* @return (String) The CPT slug
		*/
		function get_cpt_slug(){
			global $ht_knowledge_base_options;
			$default = KB_CPT_SLUG;
			$user_option = $ht_knowledge_base_options['ht-kb-slug'];
			$slug = ( empty( $user_option ) ) ? $default : $user_option;
			return $slug;
		}

		/**
		* Register ht_kb_category taxonomy
		*/
		function register_ht_knowledge_base_category_taxonomy(){
			$singular_item = __('Knowledge Base', 'ht-knowledge-base');
			$rewrite = $this->get_cat_slug();

			$labels = array(
				'name'                       => _x( 'Article Category', 'Taxonomy General Name', 'ht-knowledge-base' ),
				'singular_name'              => _x( 'Article Category', 'Taxonomy Singular Name', 'ht-knowledge-base' ),
				'menu_name'                  => __( 'Article Categories', 'ht-knowledge-base' ),
				'all_items'                  => __( 'All Article Categories', 'ht-knowledge-base' ),
				'parent_item'                => __( 'Parent Article Category', 'ht-knowledge-base' ),
				'parent_item_colon'          => __( 'Parent Article Category:', 'ht-knowledge-base' ),
				'new_item_name'              => __( 'New Article Category', 'ht-knowledge-base' ),
				'add_new_item'               => __( 'Add New Article Category', 'ht-knowledge-base' ),
				'edit_item'                  => __( 'Edit Article Category', 'ht-knowledge-base' ),
				'update_item'                => __( 'Update Article Category', 'ht-knowledge-base' ),
				'separate_items_with_commas' => __( 'Separate Article Categories with commas', 'ht-knowledge-base' ),
				'search_items'               => __( 'Search Article Categories', 'ht-knowledge-base' ),
				'add_or_remove_items'        => __( 'Add or remove categories', 'ht-knowledge-base' ),
				'choose_from_most_used'      => __( 'Choose from the most used categories', 'ht-knowledge-base' ),
			);
			$args = array(
				'labels'                     => $labels,
				'hierarchical'               => true,
				'rewrite'            		 => array( 'slug' => $rewrite ),
				'public'                     => true,
				'show_ui'                    => true,
				'show_admin_column'          => true,
				'show_in_nav_menus'          => false,
				'show_tagcloud'              => true,
			);
			register_taxonomy( 'ht_kb_category', 'ht_kb', $args );
		}

		/**
		* Get the slug for the category taxonomy
		* @return (String) The category slug
		*/
		function get_cat_slug(){
			global $ht_knowledge_base_options;
			$default = KB_CAT_SLUG;
			$user_option = $ht_knowledge_base_options['ht-kb-cat-slug'];
			$slug = ( empty( $user_option ) ) ? $default : $user_option;
			return $slug;
		}

		/**
		* Register ht_kb_tag taxonomy
		*/
		function register_ht_knowledge_base_tag_taxonomy()  {
			$singular_item = __('Knowledge Base Tag', 'ht-knowledge-base');
			$rewrite = $this->get_tag_slug();

			$labels = array(
				'name'                       => _x( 'Article Tags', 'Taxonomy General Name', 'ht-knowledge-base' ),
				'singular_name'              => _x( 'Article Tag', 'Taxonomy Singular Name', 'ht-knowledge-base' ),
				'menu_name'                  => __( 'Article Tags', 'ht-knowledge-base' ),
				'all_items'                  => __( 'All Tags', 'ht-knowledge-base' ),
				'parent_item'                => __( 'Parent Tag', 'ht-knowledge-base' ),
				'parent_item_colon'          => __( 'Parent Tag:', 'ht-knowledge-base' ),
				'new_item_name'              => __( 'New Tag Name', 'ht-knowledge-base' ),
				'add_new_item'               => __( 'Add New Tag', 'ht-knowledge-base' ),
				'edit_item'                  => __( 'Edit Tag', 'ht-knowledge-base' ),
				'update_item'                => __( 'Update Tag', 'ht-knowledge-base' ),
				'separate_items_with_commas' => __( 'Separate tags with commas', 'ht-knowledge-base' ),
				'search_items'               => __( 'Search tags', 'ht-knowledge-base' ),
				'add_or_remove_items'        => __( 'Add or remove tags', 'ht-knowledge-base' ),
				'choose_from_most_used'      => __( 'Choose from the most used tags', 'ht-knowledge-base' ),
			);

			$rewrite_arr = array(
				'slug'                       => $rewrite,
				'with_front'                 => false,
				'hierarchical'               => false,
			);

			$args = array(
				'labels'                     => $labels,
				'hierarchical'               => false,
				'public'                     => true,
				'show_ui'                    => true,
				'show_admin_column'          => true,
				'show_in_nav_menus'          => true,
				'show_tagcloud'              => true,
				'query_var'                  => 'article_tag',
				'rewrite'                    => $rewrite_arr,
			);

			register_taxonomy( 'ht_kb_tag', 'ht_kb', $args );
		}

		/**
		* Get the slug for the tag taxonomy
		* @return (String) The tag slug
		*/
		function get_tag_slug(){
			global $ht_knowledge_base_options;
			$default = KB_TAG_SLUG;
			$user_option = $ht_knowledge_base_options['ht-kb-tag-slug'];
			$slug = ( empty( $user_option ) ) ? $default : $user_option;
			return $slug;
		}

		/**
		* Flush rewrite rules if required
		*/
		function ht_knowledge_base_maybe_flush_rewrite(){
			// Check the option we set on activation.
		    if (get_option('ht_kb_flush_rewrite_required') == 'true') {
		        flush_rewrite_rules();
		        delete_option('ht_kb_flush_rewrite_required');
		    }
		}
		
		/**
		* Custom template filter
		* @param (String) $template The template file
		* @param (String) The filtered template file
		*/
		function ht_knowledge_base_custom_template($template) {
		    global $wp_query, $post;



		    $this->is_single = is_single();
		    $this->is_ht_kb_category_tax = is_tax('ht_kb_category');
		    $this->is_ht_kb_tag_tax = is_tax('ht_kb_tag');
		    $this->is_ht_kb_search = ( array_key_exists('ht-kb-search', $_REQUEST) ) ? true : false;    
		    //set the taxonmy and terms if is_tax (these are used for breadcrumbs)
		    if( $this->is_ht_kb_category_tax || $this->is_ht_kb_tag_tax )
		    	$this->set_taxonomy_and_terms();


		    //use the the theme template
		    if(current_theme_supports('ht_knowledge_base_templates'))
		    	return $template;

		    //else use compatibility template

		    //Nothing found
		    if (isset($wp_query)){;
		    	$query_vars =  $wp_query->query_vars;
		    	if( ( array_key_exists('ht_kb_category', $query_vars) || array_key_exists('ht_kb_tag', $query_vars) ) &&
		    		isset($wp_query->post_count) &&
		    		$wp_query->post_count == 0 ) {


		    		//nothing found
		    		$this->is_ht_kb_category_tax = (array_key_exists('ht_kb_category', $query_vars)) ? true : false;
		    		$this->is_ht_kb_tag_tax = (array_key_exists('ht_kb_tag', $query_vars)) ? true : false;
		    		$this->nothing_found = true;
		    		//clone the old query
					$this->temp_query = clone $wp_query;

					// Reset post
						$this->ht_kb_theme_compat_reset_post( array(
							'ID'             => 0,
							'post_title'     => 'Knowledge Base',
							'post_author'    => 0,
							'post_date'      => 0,
							'post_content'   => '',
							'post_type'      => 'ht_kb',
							'is_archive'     => true,
							'comment_status' => 'closed'
						) );


					return locate_template('page.php', false, false);

		    	}
		    	

		    }
		    
			//HT KB Archive
			if (isset($post) && $post->post_type == "ht_kb"){
				//clone the old query
				$this->temp_query = clone $wp_query;

				// Reset post
					$this->ht_kb_theme_compat_reset_post( array(
						'ID'             => 0,
						'post_title'     => 'Knowledge Base',
						'post_author'    => 0,
						'post_date'      => 0,
						'post_content'   => '',
						'post_type'      => 'ht_kb',
						'is_archive'     => true,
						'comment_status' => 'closed'
					) );


				return locate_template('page.php', false, false);


			}

			//Search Results
			if (isset($post) && $this->is_ht_kb_search){

				//clone the old query
				$this->temp_query = clone $wp_query;

				// Reset post
					$this->ht_kb_theme_compat_reset_post( array(
						'ID'             => 0,
						'post_title'     => __( 'Knowledge Base', 'ht-knowledge-base' ),
						'post_author'    => 0,
						'post_date'      => 0,
						'post_content'   => '',
						'post_type'      => 'ht_kb',
						'is_archive'     => true,
						'comment_status' => 'closed'
					) );


				return locate_template('page.php', false, false);

			}
			return $template;
		}

		/**
		* Article post views
		* @param (Int) $postID The ID of the post to increment the view count
		*/
		function ht_kb_set_post_views($postID) {
		    $count_key = HT_KB_POST_VIEW_COUNT_KEY;
		    $count = get_post_meta($postID, $count_key, true);
		    if($count==''){
				$count = 0;
				delete_post_meta($postID, $count_key);
				add_post_meta($postID, $count_key, '0');
			} else {
				$count++;
				update_post_meta($postID, $count_key, $count);
		    }
		}

		/**
		 * Add kb post view count column
		 * @param (Array) $columns Current columns on the list post
		 * @return (Array) Filtered columns on the list post
		 */
		function add_kb_post_views_count_column( $columns ) {
			$column_name = __('Article Views', 'ht-knowledge-base');
		 	$column_meta = array( 'article_views' => $column_name );
			$columns = array_slice( $columns, 0, 5, true ) + $column_meta + array_slice( $columns, 5, NULL, true );
			return $columns;
		}

		/**
		 * Add kb post view count data
		 * @param (String) $column Column slug
		 * @param (String) $post_id Post ID
		 */
		function data_kb_post_views_count_column( $column, $post_id ) {
		    switch ( $column ) {
		      case 'article_views':
					echo get_post_meta( $post_id , HT_KB_POST_VIEW_COUNT_KEY , true );
					break;
		    }
		}

		/**
		 * Register the column as sortable
		 * @param (Array) $columns Current columns on the list post
		 * @return (Array) Filtered columns on the list post
		 */
		function register_kb_post_views_count_sortable_columns( $columns ) {
			$column_name = __('Article Views', 'ht-knowledge-base');
		 	$columns['article_views'] = $column_name ;
		    return $columns;
		}


		/**
		 * Allow order by HT_KB_POST_VIEW_COUNT_KEY		 
		 * @param (Array) $query Unfiltered query
		 * @return (Array) Filtered query
		 */
		function kb_post_views_count_orderby( $query ) {
		    if( ! is_admin() )
		        return;
		 
		    $orderby = $query->get( 'orderby' );

		 
		    if( 'ArticleViews' == $orderby ) {
		        $query->set('meta_key',HT_KB_POST_VIEW_COUNT_KEY);
		        $query->set('orderby','meta_value_num');
		    }
		}


		/**
		 * Comment filter
		 * @param (String) $content The comment content
		 * @return (String) Filtered comment content
		 */
		function kb_filter_get_comments( $content, $comment, $args ) {
			global $post;

			//if this is an archive, admin page or not a knowledge base post, return
			if(!is_single() || is_admin() || $post->post_type!='ht_kb' )
				return $content;

			//if voting isn't installed, return
			if(!class_exists('HT_Voting'))
				return $content;

			//comment voting
			ob_start();

			?>
			<div class="clearfix"></div>
			<div class="ht-voting-comments-section">
			<?php
				ht_voting_comment( $comment->comment_ID, 'user', 'numbers');
			?>
			</div><!--/ht-voting-comments-section-->
			<?php
			$comment_vote = ob_get_clean();

			return $content . $comment_vote;
		}

		/**
		* Set the tax and terms
		*/
		function set_taxonomy_and_terms(){
			global $wp_query;
			$this->taxonomy = @$wp_query->tax_query->queries[0]['taxonomy'];
			$this->term = @$wp_query->tax_query->queries[0]['terms'][0];
		}



		/**
		* Custom content filter
		* @param (String) $content The current content
		* @return (String) Filtered content
		*/
		function ht_knowledge_base_custom_content( $content ){
			global $post, $wp_query;


			//don't use if current theme supports template
			if(current_theme_supports('ht_knowledge_base_templates')){
				remove_filter( 'the_content', array($this, 'ht_knowledge_base_custom_content') );
				return $content;
			}

			//dont use if ajax search
			if(!empty($_GET['ajax']) ? $_GET['ajax'] : null){
				remove_filter( 'the_content', array($this, 'ht_knowledge_base_custom_content') );
				return $content;
			}


			if( (isset( $post ) && $post->post_type == 'ht_kb') ){
				ob_start();
				
				//remove filters
				remove_filter( 'the_content', array($this, 'ht_knowledge_base_custom_content') );

				//enqueue styles
				wp_enqueue_style( 'font-awesome', plugins_url( 'css/font-awesome.min.css', __FILE__ ) );
				wp_enqueue_style( 'ht-kb-default-style', plugins_url( 'css/ht-kb-default-style.css', __FILE__ ) );
				wp_enqueue_style( 'buttons' );

				//restore query
				$wp_query = $this->temp_query;

				if( $this->is_single ){
					//check the theme does not override template
					$theme_template_exists = locate_template( 'single-ht_kb.php' ) != '' ? true : false ;
	 				$load_file_name = plugin_dir_path( __FILE__ ) . '/templates/ht-knowledge-base-single-template.php';
				    if(file_exists($load_file_name) && !$theme_template_exists){
				    	include $load_file_name;
				    }
				} else {
					//check if we are in the category
					if ( $this->is_ht_kb_category_tax ){
						//check the theme does not override template
						$theme_template_exists = locate_template( 'taxonomy-ht_kb_category.php' ) != '' ? true : false ;
		 				$load_file_name = plugin_dir_path( __FILE__ ) . '/templates/ht-knowledge-base-category-template.php';
					    if(file_exists($load_file_name) && !$theme_template_exists){
					    	include $load_file_name;
					    }
					//check for tags
					} elseif ( $this->is_ht_kb_tag_tax ){
						//check the theme does not override template
						$theme_template_exists = locate_template( 'taxonomy-ht_kb_tag.php' ) != '' ? true : false ;
		 				$load_file_name = plugin_dir_path( __FILE__ ) . '/templates/ht-knowledge-base-tag-template.php';
					    if(file_exists($load_file_name) && !$theme_template_exists){
					    	include $load_file_name;
					    }
					//check for search results
					} elseif ( $this->is_ht_kb_search ){
						//check the theme does not override template
						$theme_template_exists = locate_template( 'taxonomy-ht_kb_search.php' ) != '' ? true : false ;
		 				$load_file_name = plugin_dir_path( __FILE__ ) . '/templates/ht-knowledge-base-search-template.php';
					    if(file_exists($load_file_name) && !$theme_template_exists){
					    	include $load_file_name;
					    }
					//else default to cpt archive
					} else {
						//check the theme does not override template
						$theme_template_exists = locate_template( 'archive-ht_kb.php' ) != '' ? true : false ;
		 				$load_file_name = plugin_dir_path( __FILE__ ) . '/templates/ht-knowledge-base-archive-template.php';
					    if(file_exists($load_file_name) && !$theme_template_exists){
					    	include $load_file_name;
					    }	
					}
				}

				//reset the query if single
				if(!$this->is_single)
					wp_reset_query();

				//skip over first post
				if($this->is_single)
					the_post();

				add_filter( 'the_content', array($this, 'ht_knowledge_base_custom_content') );
				$output = ob_get_clean();
				
				//return possibly hi-jacked content
				return $output;
				
			}
			return $content;
		}




		/**
		 * This function fills up some WordPress globals with dummy data to
		 * stop page template from complaining about it missing.
		 * It's based on the bbPress functionality, so messing about with the
		 * loop in this way is their idea!
		 *
		 * @credit bbPress (r3108)
		 * @global WP_Query $wp_query
		 * @global object $post
		 * @param array $args
		 */
		function ht_kb_theme_compat_reset_post( $args = array() ) {
			global $wp_query, $post;

			$post_status = isset( $wp_query->post ) ? $wp_query->post->post_status : 'published';

			// Default arguments
			$defaults = array(
				'ID'                    => -9999,
				'post_status'           => $post_status,
				'post_author'           => 0,
				'post_parent'           => 0,
				'post_type'             => 'page',
				'post_date'             => 0,
				'post_date_gmt'         => 0,
				'post_modified'         => 0,
				'post_modified_gmt'     => 0,
				'post_content'          => '',
				'post_title'            => '',
				'post_category'         => 0,
				'post_excerpt'          => '',
				'post_content_filtered' => '',
				'post_mime_type'        => '',
				'post_password'         => '',
				'post_name'             => '',
				'guid'                  => '',
				'menu_order'            => 0,
				'pinged'                => '',
				'to_ping'               => '',
				'ping_status'           => '',
				'comment_status'        => 'closed',
				'comment_count'         => 0,

				'is_404'          => false,
				'is_page'         => false,
				'is_single'       => false,
				'is_archive'      => false,
				'is_tax'          => false,
			);

			// Switch defaults if post is set
			if ( isset( $wp_query->post ) ) {		  
				$defaults = array(
					'ID'                    => $wp_query->post->ID,
					'post_status'           => $wp_query->post->post_status,
					'post_author'           => $wp_query->post->post_author,
					'post_parent'           => $wp_query->post->post_parent,
					'post_type'             => $wp_query->post->post_type,
					'post_date'             => $wp_query->post->post_date,
					'post_date_gmt'         => $wp_query->post->post_date_gmt,
					'post_modified'         => $wp_query->post->post_modified,
					'post_modified_gmt'     => $wp_query->post->post_modified_gmt,
					'post_content'          => $wp_query->post->post_content,
					'post_title'            => $wp_query->post->post_title,
					'post_category'         => $wp_query->post->post_category,
					'post_excerpt'          => $wp_query->post->post_excerpt,
					'post_content_filtered' => $wp_query->post->post_content_filtered,
					'post_mime_type'        => $wp_query->post->post_mime_type,
					'post_password'         => $wp_query->post->post_password,
					'post_name'             => $wp_query->post->post_name,
					'guid'                  => $wp_query->post->guid,
					'menu_order'            => $wp_query->post->menu_order,
					'pinged'                => $wp_query->post->pinged,
					'to_ping'               => $wp_query->post->to_ping,
					'ping_status'           => $wp_query->post->ping_status,
					'comment_status'        => $wp_query->post->comment_status,
					'comment_count'         => $wp_query->post->comment_count,

					'is_404'          => false,
					'is_page'         => false,
					'is_single'       => false,
					'is_archive'      => false,
					'is_tax'          => false,
				);
			}
			$dummy = wp_parse_args( $args, $defaults );

			// Clear out the post related globals
			unset( $wp_query->posts );
			unset( $wp_query->post );
			unset( $post );

			// Setup the dummy post object
			$wp_query->post                        = new stdClass; 
			$wp_query->post->ID                    = $dummy['ID'];
			$wp_query->post->post_status           = $dummy['post_status'];
			$wp_query->post->post_author           = $dummy['post_author'];
			$wp_query->post->post_parent           = $dummy['post_parent'];
			$wp_query->post->post_type             = $dummy['post_type'];
			$wp_query->post->post_date             = $dummy['post_date'];
			$wp_query->post->post_date_gmt         = $dummy['post_date_gmt'];
			$wp_query->post->post_modified         = $dummy['post_modified'];
			$wp_query->post->post_modified_gmt     = $dummy['post_modified_gmt'];
			$wp_query->post->post_content          = $dummy['post_content'];
			$wp_query->post->post_title            = $dummy['post_title'];
			$wp_query->post->post_category         = $dummy['post_category'];
			$wp_query->post->post_excerpt          = $dummy['post_content_filtered'];
			$wp_query->post->post_content_filtered = $dummy['post_content_filtered'];
			$wp_query->post->post_mime_type        = $dummy['post_mime_type'];
			$wp_query->post->post_password         = $dummy['post_password'];
			$wp_query->post->post_name             = $dummy['post_name'];
			$wp_query->post->guid                  = $dummy['guid'];
			$wp_query->post->menu_order            = $dummy['menu_order'];
			$wp_query->post->pinged                = $dummy['pinged'];
			$wp_query->post->to_ping               = $dummy['to_ping'];
			$wp_query->post->ping_status           = $dummy['ping_status'];
			$wp_query->post->comment_status        = $dummy['comment_status'];
			$wp_query->post->comment_count         = $dummy['comment_count'];

			// Set the $post global
			$post = $wp_query->post;

			// Setup the dummy post loop
			$wp_query->posts = array($post);


			// Prevent comments form from appearing
			$wp_query->post_count = 1;
			$wp_query->is_404     = $dummy['is_404'];
			$wp_query->is_page    = $dummy['is_page'];
			$wp_query->is_single  = $dummy['is_single'];
			$wp_query->is_archive = $dummy['is_archive'];
			$wp_query->is_tax     = $dummy['is_tax'];

			// If we are resetting a post, we are in theme compat
			//ht_kb_set_theme_compat_active();
		}

		/**
		* Custom pre get posts filter for knowledge base search and author archive
		* @param (Object) $query The WordPress query object
		* @return (Object) Filtered WordPress query object
		*/
		function ht_kb_pre_get_posts_filter( $query ) {

			//assign is_ht_kb_search
			$this->is_ht_kb_search = ( array_key_exists('ht-kb-search', $_REQUEST) ) ? true : false;


			//live search 
			if ( !is_preview() && !is_singular() && !is_admin() && $this->is_ht_kb_search ) {

				global $ht_knowledge_base_options;

				$post_types = (isset($ht_knowledge_base_options) && array_key_exists('search-types', $ht_knowledge_base_options)) ? $ht_knowledge_base_options['search-types'] : array('ht-kb');

				$existing_post_type = (!empty($query) && isset($query->query_vars) && is_array($query->query_vars) && array_key_exists('post_type', $query->query_vars) ) ? $query->query_vars['post_type'] : null; 
				if ( empty( $existing_post_type ) ) {
					//update post_type variable
					$query->set( 'post_type' , $post_types );
					//supress filters false for wpml compatibility
					//$query->set( 'suppress_filters' , 0 );
				}

			}
			
			//author archive
			if ( !is_preview() && !is_singular() && !is_admin() && $query->is_author ) {
		    	//can add more post typess here, eg forum topics/replies
				$post_types = array('ht_kb', 'post');
				
				$existing_post_type = (!empty($query) && isset($query->query_vars) && is_array($query->query_vars) && array_key_exists('post_type', $query->query_vars) ) ? $query->query_vars['post_type'] : null; 
				if ( empty( $existing_post_type ) ) {
					//update post_type variable
					$query->set( 'post_type' , $post_types );
				}

			}

			return $query;
		}

		/**
		* Comments open filter
		* @param (boolean) $open Unfiltered comments open status
		* @return (boolean) Filtered comments open
		*/
		function ht_kb_comments_open(  $open, $post_id ) {
		     global $ht_knowledge_base_options;

		     $post = get_post( $post_id );
		     
		     //check if post type is knowledge base
		     if($post->post_type == 'ht_kb'){ 
				if( $ht_knowledge_base_options['article-comments'] ){
					return $open;
				} else {
					return false;
				}
		     }

		     return $open;
		}

		/**
		* Comments template filter
		* @param (String) $comment_template The comment template file
		* @return (String) Filtered comment template file
		*/
		function ht_kb_comments_template( $comment_template ) {
		     global $post, $ht_knowledge_base_options;
		     if ( !( is_singular() && ( have_comments() || 'open' == $post->comment_status ) ) ) {
		        return;
		     }
		     //check if post type is knowledge base
		     if($post->post_type == 'ht_kb'){ 
				if( $ht_knowledge_base_options['article-comments'] ){
					return $comment_template;
				} else {
					return;
				}
		     }
		}

		/**
		* Admin warning message if Heroic Voting not installed
		*/
		function ht_kb_voting_warning() {
			if( !class_exists('HT_Voting') &&  current_user_can( 'install_plugins' ) ):
		    ?>
			    <div class="update-nag">
			        <p><?php _e( 'The Heroic Voting plugin is required to use voting features', 'ht-knowledge-base' ); ?></p>
			    </div>
		    <?php
		    endif; //end class exists
		}

		/**
		* Adds the HT Knowledge Base Menu Metabox
		*/
		function ht_knowledge_base_menu_metabox() {
	    	add_meta_box( 'add_ht_knowledge_base_item', __('Heroic Knowledge Base Archive', 'ht-knowledge-base'), array( $this, 'ht_knowledge_base_menu_metabox_content' ), 'nav-menus', 'side', 'default' );
	  	}

		/**
		* Adds the HT Knowledgebase Metabox Content
		*/
		function ht_knowledge_base_menu_metabox_content() {
	    	
	    	// Create menu items and store IDs in array
			$item_ids = array();
			$post_type = 'ht_kb';
			$post_type_obj = get_post_type_object( $post_type );

			if( ! $post_type_obj )
				continue;

			//add menu data
			$menu_item_data = array(
				 'menu-item-title'  => esc_attr( $post_type_obj->labels->menu_name ),
				 'menu-item-type'   => $post_type,
				 'menu-item-object' => esc_attr( $post_type ),
				 'menu-item-url'    => get_post_type_archive_link( $post_type )
			);

			// add the menu item
			$item_ids[] = wp_update_nav_menu_item( 0, 0, $menu_item_data );

			// Die on error
			is_wp_error( $item_ids ) AND die( '-1' );

			// Set up the menu items
			foreach ( (array) $item_ids as $menu_item_id ) {
				$menu_obj = get_post( $menu_item_id );
				if ( ! empty( $menu_obj->ID ) ) {
					$menu_obj->classes = array();
					$menu_obj->label = __('Heroic Knowledge Base Archive', 'ht-knowledge-base');
			        $menu_obj->object_id = $menu_obj->ID;
			        $menu_obj->object = 'ht-knowledge-base';						
					$menu_items[] = $menu_obj;

				}
			}

		    $menus = array_map('wp_setup_nav_menu_item', $menu_items);
			$walker = new Walker_Nav_Menu_Checklist( array() );
	
			echo '<div id="ht-knowledge-base-archive" class="posttypediv">';
			echo '<div id="tabs-panel-ht-knowledge-base-archive" class="tabs-panel tabs-panel-active">';
			echo '<ul id="ctp-archive-checklist" class="categorychecklist form-no-clear">';
			echo walk_nav_menu_tree( $menus, 0, (object) array( 'walker' => $walker) );
			echo '</ul>';
			echo '</div><!-- /.tabs-panel -->';
			echo '</div>';
			echo '<p class="button-controls">';
			echo '<span class="add-to-menu">';
			echo '<input type="submit" class="button-secondary submit-add-to-menu" value="' . __('Add to Menu', 'ht-knowledge-base') . '" name="add-ht-knowledge-base-archive-menu-item" id="submit-ht-knowledge-base-archive" />';
			echo '</span>';
			echo '</p>';
			
		}


		/**
		* Menu filter for HT Knowledge Base Archive
		* @param $items The Items
		* @param $menu Menu
		* @param $args Additional params
		*/
		function ht_knowledge_base_archive_menu_filter( $items, $menu, $args ) {
	    	foreach( $items as $item ) {
	      		if( $item->object != 'ht-knowledge-base' ) continue;
	      		$item->url = get_post_type_archive_link( $item->type );
	      
	      		if( get_query_var( 'post_type' ) == $item->type ) {
	       			$item->classes[] = 'current-menu-item';
	        		$item->current = true;
	      		}
	    	}
	    	
	    	return $items;
		}


		/**
		* Pre posts query filter for kb custom front page
		* @param (Object) $query The query to modify
		*/
		function ht_knowledge_base_custom_front_page($query) {
			global $wp_the_query;
			global $ht_knowledge_base_options;
			global $post;


			
		    if(!is_admin() && 
		    	$wp_the_query===$query && 
		    	$query->is_main_query() &&
		    	'page' == get_option( 'show_on_front' ) &&
		    	$query->is_page && 
		    	$query->query_vars['page_id'] == get_option('page_on_front')
                ){
		    			
						//check the page on front and the kb_archive ID are the same
						if(get_option('page_on_front') == $this->get_ht_kb_dummy_page_id()){
							//continue
						} else {
							//else return
							return;
						}
		    			
		    			//set dummy post
		    			$this->ht_kb_theme_compat_reset_post( array(
							'ID'             => 0,
							'post_title'     => 'Knowledge Base',
							'post_author'    => 0,
							'post_date'      => 0,
							'post_content'   => '',
							'post_type'      => 'ht_kb',
							'is_archive'     => true,
							'comment_status' => 'closed'
						) );	
						
						//set query
				        $query->set( 'post_type','ht_kb' );
				        $query->set( 'posts_per_page',-1 );
				        $query->set( 'page', 0 );
				        $query->set( 'post_status', 'public' );
				        $query->set( 'page_id', '' );
				        
				        $this->ht_kb_is_ht_kb_front_page=true;

				        //override option to edit article from wp admin bar
				        add_action( 'admin_bar_menu', array( $this, 'ht_kb_remove_edit_option_from_admin_bar' ), 999 );
				        
				        //remove breadcrumbs filter
				        add_filter( 'ht_show_breadcrumbs', array( $this, 'ht_remove_breadcrumbs' ), 10 );
				        
		    		}
			
		}

		/**
		* Filter to remove breadcrumbs for all registered hooks
		*/
		function ht_remove_breadcrumbs( $status ){
			return false;
		}




		/**
		* Remove edit option from the admin bar
		* @param (Object) $wp_admin_bar Unfiltered admin bar
		*/
		function ht_kb_remove_edit_option_from_admin_bar( $wp_admin_bar ){
			$wp_admin_bar->remove_node('edit');
		}

		/**
		* Modify <title> for ht_kb archive page
		* @param (String) $title Unfiltered page title
		* @return (String) Filtered page title
		*/
		function ht_kb_archive_title_filter( $title, $sep, $seplocation ) {
			global $post, $wp_query;

			if(!is_archive() || !is_object($post) ){
				//don't do anything if not an archive or post not object
				return $title;
			} else {
				$post_type = $post->post_type;


				$main_title = __('Knowledge Base', 'ht-knowledge-base');

				

				if( is_tax() ){
					//taxonomy
					$value = get_query_var( $wp_query->query_vars['taxonomy'] );
					$term = get_term_by( 'slug', $value, $wp_query->query_vars['taxonomy'] );
					$filtered_title = $term->name . $sep;
				} else {
					$filtered_title = $main_title . $sep; 
				}

				//filter types for post types, other posts types can go here
				$types = array(
					array( 
						'post_type' => 'ht_kb', 
						'title' => $filtered_title
					)
				);

				//iterate over types to filter the the title
				foreach ( $types as $key => $value) {
					if ( in_array($post_type, $types[$key])) {
						return $types[$key]['title'];
					}
				}

				//else just return the title
				return $title;

			}
		}

		/**
		* Custom pre get posts filter for knowledge base article order
		* @param (Object) $query The WordPress query object
		* @return (Object) Filtered WordPress query object
		*/
		function ht_kb_modify_search_order_pre_get_posts( $query ){
			global $ht_knowledge_base_options, $ht_kb_display_archive, $ht_kb_display_uncategorized_articles;

			//exit if options not set
			
			if( is_array($ht_knowledge_base_options) && array_key_exists('sort-by', $ht_knowledge_base_options) && array_key_exists('sort-order', $ht_knowledge_base_options ) ){
				//do nothing - array keys set
			} else {
				//else exit
				return $query;
			}
			

			//get the user set sort by and sort order
			$user_sort_by = ($ht_knowledge_base_options['sort-by']) ? $ht_knowledge_base_options['sort-by'] : '' ;
			$user_sort_order = ($ht_knowledge_base_options['sort-order']) ? $ht_knowledge_base_options['sort-order'] : '' ;

			if(!is_preview() && !is_singular() && !is_admin() && 
				( 	$ht_kb_display_archive==true ||
					$ht_kb_display_uncategorized_articles==true ||
				  	( $query->is_main_query() && is_post_type_archive( 'ht_kb' ) ) || 
					( $query->is_main_query() && is_tax('ht_kb_category') ) || 
					( $query->is_main_query() && is_tax('ht_kb_tag') ) )
					){

					$sort_meta_key = '';

			        $valid_sort_orders = array('date', 'title', 'comment_count', 'rand', 'modified', 'popular', 'helpful');
			        if ( in_array($user_sort_by, $valid_sort_orders) ) {
			          $sort_by = $user_sort_by;
			          $sort_order = ($user_sort_order=='asc') ? 'ASC' : 'DESC';
			        } else {
			          // by default, display latest first
			          $sort_by = 'date';
			          $sort_order = 'DESC';
			        }

			        if($user_sort_by=='popular'){
			          $sort_by = 'meta_value_num';
			          $sort_meta_key = '_ht_kb_post_views_count';
			        }

			        if($user_sort_by=='helpful'){
			          $sort_by = 'meta_value_num';
			          $sort_meta_key = HT_USEFULNESS_KEY;
			        }        

			       
			      //set query 
			       $query->set( 'orderby' ,  $sort_by );
			       $query->set( 'order' ,  $sort_order );
			       $query->set( 'meta_key' ,  $sort_meta_key );

			       return $query;	  

		      }      	
		}

		/**
		* Custom pre get posts filter for knowledge base taxonomy to set posts_per_page
		* @param (Object) $query The WordPress query object
		* @return (Object) Filtered WordPress query object
		*/
		function ht_kb_posts_per_taxonomy( $query ){
			global $ht_knowledge_base_options;

			//exit if options not set		
			if( is_array($ht_knowledge_base_options) && array_key_exists('tax-cat-article-number', $ht_knowledge_base_options) && array_key_exists('sort-order', $ht_knowledge_base_options ) ){
				//do nothing - array keys set
			} else {
				//else exit
				return $query;
			}

			//in built posts per page option from settings > reading
			$posts_per_page_option = get_option('posts_per_page');

			//get the user set sort by and sort order
			$user_number_posts = ($ht_knowledge_base_options['tax-cat-article-number']) ? $ht_knowledge_base_options['tax-cat-article-number'] : $posts_per_page_option ;
		
			if(!is_preview() && !is_singular() && !is_admin() && 
				( 	( $query->is_main_query() && is_tax('ht_kb_category') ) || 
					( $query->is_main_query() && is_tax('ht_kb_tag') ) )
				){			       
			      	//set query 
			       $query->set( 'posts_per_page' ,  $user_number_posts );
			       return $query;	  		      
			   }      	
		}

		/**
		* Post published action hook
		* @param (String) $id The post id
		* @param (Object) $post The WordPress post object
		*/
		function ht_kb_article_publish( $id, $post ){
			//set the initial meta
			HT_Knowledge_Base::ht_kb_set_initial_meta( $post->ID );
		}

		/**
		* Get pages filter for the options reading page to add a dummy page for the ht_kb archive
		* @param (Array) $args Pages unfiltered
		* @param (Array) The filtered pages
		*/
		function ht_kb_filter_get_pages($pages){
			if(!is_admin()){
				//return if not admin
				return $pages;
			}
			 $screen = get_current_screen();
			if(isset($screen) && $screen->id=='options-reading'){
				$dummy_post = $this->get_ht_kb_dummy_page();				
				array_push($pages, $dummy_post);
				return $pages;
			}
			
		}

		/**
		* Gets the ht_kb archive dummy page, creates it if one doesn't exist
		*/
		function get_ht_kb_dummy_page(){
			$dummy_page_title = __('Knowledge Base Archive', 'ht-knowledge-base');
			$dummy_page_content = __('Knowledge Base Archive - Used by the Heroic Knowledge Base, do not delete', 'ht-knowledge-base');
			
			//try to get the existing page
			$existing_page = get_page_by_title($dummy_page_title);
			if($existing_page==null){
				//create dummy post
				$id = wp_insert_post( array(	'post_title'=> $dummy_page_title, 
										'post_content' => $dummy_page_content,
										'post_type' => 'page',
										'post_status'   => 'draft',
										), true );
			} 

			return get_page_by_title($dummy_page_title);
		}

		/**
		* Gets the ht_kb archive dummy page id
		*/
		function get_ht_kb_dummy_page_id(){
			$ht_kb_archive_dummy_page = $this->get_ht_kb_dummy_page();
			if(isset($ht_kb_archive_dummy_page) && is_a($ht_kb_archive_dummy_page, 'WP_Post')){
				//return the archive ID
				return $ht_kb_archive_dummy_page->ID;
			} else {
				//else return
				return 0;
			}
		}


		/**
		* Set initial post view count and helpfulness as 0
		* @param (String) $id The post id
		*/
		static function ht_kb_set_initial_meta( $id ){
			//set post view count to 0 if none
			$post_view_count =  get_post_meta( $id, HT_KB_POST_VIEW_COUNT_KEY, true );
			if($post_view_count == ''){
				//set view count to 0
				update_post_meta($id, HT_KB_POST_VIEW_COUNT_KEY, 0);
			}
			//set post helpfulness meta to 0 if none
			$helpfulness =  get_post_meta( $id, HT_USEFULNESS_KEY, true );
			if($helpfulness == ''){
				//set helpfulness to 0
				update_post_meta($id, HT_USEFULNESS_KEY, 0);
			}
		}


		static function ht_kb_plugin_activation_upgrade_actions(){
			//upgrade - set initial meta if required

			//get all ht_kb articles
			$args = array(
					  'post_type' => 'ht_kb',
					  'posts_per_page' => -1,
					 );
			$ht_kb_posts = get_posts( $args );

			//loop and ugrade
			foreach ( $ht_kb_posts as $post ) {
				//upgrade if required
			   HT_Knowledge_Base::ht_kb_set_initial_meta( $post->ID );
			   HT_Knowledge_Base::ht_kb_upgrade_article_meta_fields( $post->ID );
			}
		}

		/**
		 * Upgrade the meta key values.
		 */
		public static function ht_kb_upgrade_article_meta_fields($postID){
			//keys to be upgraded
			HT_Knowledge_Base::ht_kb_upgrade_meta_field($postID, 'file_advanced');
			HT_Knowledge_Base::ht_kb_upgrade_meta_field($postID, 'voting_checkbox');
			HT_Knowledge_Base::ht_kb_upgrade_meta_field($postID, 'voting_reset');
			HT_Knowledge_Base::ht_kb_upgrade_meta_field($postID, 'voting_reset_confirm');
			HT_Knowledge_Base::ht_kb_upgrade_view_count_meta($postID);
		}

		/**
		 * Upgrade a post meta field.
		 * @param (String) $name The name of the meta field to be upgraded
		 */
		static function ht_kb_upgrade_meta_field($postID, $name){
			$old_prefix = 'ht_knowledge_base_';
			$new_prefix = '_ht_knowledge_base_';

			//get the old value
			$old_value = get_post_meta($postID, $old_prefix . $name, true);
			if(!empty($old_value)){
				//get the new value
				$new_value = get_post_meta($postID, $new_prefix . $name, true);
				if(empty($new_value)){
					//sync the new value to the old value
					update_post_meta($postID, $new_prefix . $name, $old_value);
				}
				
			}
			//delete old meta key
			delete_post_meta($postID, $old_prefix . $name);
		}

		/**
		 * Upgrade a view count meta field
		 */
		static function ht_kb_upgrade_view_count_meta($postID){
			$old_key = 'ht_kb_post_views_count';
			$new_key = HT_KB_POST_VIEW_COUNT_KEY;

			//get the old value
			$old_value = get_post_meta($postID, $old_key, true);
			if(!empty($old_value)){
				//get the new value
				$new_value = get_post_meta($postID, $new_key, true);
				//upgrade regardless of whether the new value is empty
				if(true){
					//sync the new value to the old value
					update_post_meta($postID, $new_key, $old_value);
				}
				
			}
			//delete old meta key
			delete_post_meta($postID, $old_key);
		}

		/**
		* Add required columns to the WP database terms table
		*/
		static function knowledgebase_customtaxorder_activate() {
			global $wpdb;
			$init_query = $wpdb->query("SHOW COLUMNS FROM $wpdb->terms LIKE 'term_order'");
			if ($init_query == 0) {	
				$wpdb->query("ALTER TABLE $wpdb->terms ADD `term_order` INT( 4 ) NULL DEFAULT '0'"); 
			}
		}

		/**
		* Remove columns from the WP database terms table that were added during installation
		* @todo Implement this function (note this should be on plugin UNINSTALL, not deactivation)
		*/
		static function knowledgebase_customtaxorder_uninstall() {
			global $wpdb;
			$init_query = $wpdb->query("SHOW COLUMNS FROM $wpdb->terms LIKE 'term_order'");
			if ($init_query != 0) {	
				$wpdb->query("ALTER TABLE $wpdb->terms DROP COLUMN `term_order`"); 
			}
		}



	} //end class HT_Knowledge_Base
}//end class exists test


//run the plugin
if( class_exists( 'HT_Knowledge_Base' ) ){
	$ht_knowledge_base_init = new HT_Knowledge_Base();

	//register global functions
	function ht_kb_set_post_views($postID) {
		global $ht_knowledge_base_init;
		$ht_knowledge_base_init->ht_kb_set_post_views($postID);
	}

	function ht_kb_get_taxonomy(){
		global $ht_knowledge_base_init;
		return $ht_knowledge_base_init->taxonomy;
	}

	function ht_kb_get_term(){
		global $ht_knowledge_base_init;
		return $ht_knowledge_base_init->term;
	}

	function ht_kb_is_ht_kb_search(){
		global $ht_knowledge_base_init;
		return $ht_knowledge_base_init->is_ht_kb_search;
	}

	function ht_kb_is_ht_kb_front_page(){
		global $ht_knowledge_base_init;
		return $ht_knowledge_base_init->ht_kb_is_ht_kb_front_page;
	}

	function ht_kb_view_count($post_id=null){
		global $post;
		//set the post id
		$post_id = ( empty( $post_id ) ) ? $post->ID : $post_id;
		//get the post usefulness meta
		$post_view_count = get_post_meta( $post_id, HT_KB_POST_VIEW_COUNT_KEY, true );
		//convert to integer
		$post_view_count_int = empty($post_view_count) ? 0 : intval($post_view_count);
		//return as integer
		return $post_view_count_int;
	}

	function get_ht_kb_dummy_page_id(){
		global $ht_knowledge_base_init;
		return $ht_knowledge_base_init->get_ht_kb_dummy_page_id();
	}
}


