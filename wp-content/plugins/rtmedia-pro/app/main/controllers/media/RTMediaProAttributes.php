<?php
/**
 * Don't load this file directly!
 */
if ( ! defined( 'ABSPATH' ) ){
	exit;
}

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of RTMediaProAttributes
 *
 * @author ritz
 */
if ( ! class_exists( 'RTMediaProAttributes' ) ){
	class RTMediaProAttributes {
		public function __construct( $init = true ) {
			if ( $init ){
				$this->attributes_admin_page_hooks();
				add_action( 'init', array( $this, 'init_modules' ) );
				add_action( 'rtmedia_add_edit_fields', array( $this, 'add_media_attributes_edit_fields' ), 30, 1 );
				add_action( 'rtmedia_after_edit_media', array( $this, 'rtmedia_add_media_attributes' ), 10, 2 );
				add_action( 'rtmedia_actions_before_description', array( $this, 'show_media_attributes' ), 999, 1 );
				add_action( 'rtmedia_add_bulk_edit_buttons', array( $this, 'add_attribute_bulk_edit' ), 99 );
				add_filter( 'rtmedia_uploader_before_start_upload_button', array( $this, 'add_media_attributes_file_upload' ), 99, 1 );
				add_action( 'rtmedia_add_bulk_edit_content', array( $this, 'add_media_attributes_bulk_edit_screen' ), 99 );
				add_action( 'wp_ajax_rtmedia_add_media_attributes_after_upload', array( $this, 'add_media_attributes_after_upload' ) );
				add_action( "rtmedia_set_query", array( $this, "rtmedia_set_query_filters" ), 99 );
				add_filter( "rtmedia_allowed_query", array( $this, "rtmedia_allowed_attributes_parameter_in_query" ), 99 );
			}
		}

		function rtmedia_allowed_attributes_parameter_in_query( $param = array() ) {
			$param[] = 'attribute_slug';
			$param[] = 'term_slug';
			return $param;
		}

		function rtmedia_set_query_filters() {
			// support for shortcode
			add_filter( 'rtmedia_media_query', array( $this, 'modify_media_query' ), 10, 3);
		}

		function modify_media_query( $media_query, $action_query, $query ) {
			global $rtmedia_query;
			global $media_query_clone_attributes; // store media_query for reference
			$media_query_clone_attributes = $media_query;
			if( isset( $media_query['attribute_slug'] ) && $media_query['attribute_slug']  != '' && isset( $media_query['term_slug'] ) && $media_query['term_slug']  != '' ){
				add_filter( 'rtmedia-model-where-query', array( $this, 'rtmedia_model_shortcode_where_query_attributes' ), 10, 3 );
				add_action( 'rtmedia_before_media_gallery', array( $this, 'remove_rtmedia_model_shortcode_where_query_attributes' ), 10, 3 );
				unset( $media_query[ 'attribute_slug' ] );
				unset( $media_query[ 'term_slug' ] );

				// unset from global query so that multiple gallery shortcode can work
				if( isset( $rtmedia_query->query ) && isset( $rtmedia_query->query['attribute_slug'] ) ) {
					unset( $rtmedia_query->query['attribute_slug'] );
					unset( $rtmedia_query->query['term_slug'] );
				}
				if ( isset( $media_query[ 'context_id' ] ) ){
					unset( $media_query[ 'context_id' ] );
				}
				if ( isset( $media_query[ 'context' ] ) ){
					unset( $media_query[ 'context' ] );
				}
			}
			return $media_query;
		}

		function rtmedia_model_shortcode_where_query_attributes( $where, $table_name, $join ) {
			global $media_query_clone_attributes;
			$attr_slug = $media_query_clone_attributes['attribute_slug'];
			$term_slug = $media_query_clone_attributes['term_slug'];
			if( strpos( $term_slug, "," ) ) {
				$term_slug_array = explode( ",", $term_slug );
			} else {
				$term_slug_array = array( $term_slug );
			}
			if( strpos( $attr_slug, "," ) ) {
				$attr_slug_array = explode( ",", $attr_slug );
			} else {
				$attr_slug_array = array( $attr_slug );
			}
			$tax_query_args = array();
//			if( sizeof( $attr_slug_array ) > 1 ){
//				foreach( $attr_slug_array as $slug ){
//					$taxonomy_name = rtmedia_pro_attribute_taxonomy_name( $attr_slug );
//					$tax_query_args['tax_query'][0][] = array(
//						'taxonomy' => $taxonomy_name,
//						'terms' => $term_slug_array,
//						'field' => 'slug',
//					);
//					$tax_query_args['tax_query'][0]['relation'] = 'OR';
//				}
//			} else {
//
//			}
			$taxonomy_name = rtmedia_pro_attribute_taxonomy_name( $attr_slug );
			$tax_query_args['tax_query'] = array(
				array(
					'taxonomy' => $taxonomy_name,
					'terms' => $term_slug_array,
					'field' => 'slug',
				),
			);
			$rtmedia_attr_model = new RTMediaAttributesModel();
			$args = array( 'attribute_name' => $attr_slug );
			$attribute = $rtmedia_attr_model->get( $args );
			$posts_array = array();
			if( is_array( $attribute ) && sizeof( $attribute ) > 0 ){
				$allow = false;
				foreach( $term_slug_array as $term ){
					if( term_exists( $term, $taxonomy_name ) ){
						$allow = true;
						break;
					}
				}
				if( sizeof( $term_slug_array ) > 0  && $allow) {
					$args = array(
						'posts_per_page' => -1,
						'post_type' => 'attachment',
						'post_status' => 'inherit',
						'tax_query' => $tax_query_args['tax_query'],
					);
//					$query = new WP_Query( $args );
					$posts = get_posts( $args );
					if( is_array( $posts ) ) {
						foreach( $posts as $post ) {
							$posts_array[] = $post->ID;
						}
					}
					if( is_array( $posts_array ) && sizeof( $posts_array ) > 0 ){
						$media_ids = implode( ',', $posts_array );
						$where .= " AND {$table_name}.media_id in (" . $media_ids . ") ";
					} else {
						$where .= " AND {$table_name}.media_id in (-1) ";
					}
					/* Restore original Post Data */
					wp_reset_postdata();
				} else {
					$where .= " AND {$table_name}.media_id in (-1) ";
				}
			} else {
				$where .= " AND {$table_name}.media_id in (-1) ";
			}
			return $where;
		}

		function remove_rtmedia_model_shortcode_where_query_attributes() {
			remove_filter( 'rtmedia-model-where-query', array( $this, 'rtmedia_model_shortcode_where_query_attributes' ), 10, 3 );
		}

		function add_media_attributes_after_upload() {

			$rtmedia_attr_model = new RTMediaAttributesModel();
			$attributes         = $rtmedia_attr_model->get_all_attributes();
			$new_attributes     = $_POST[ 'media_attributes' ];

			$post_id = rtmedia_media_id( $_POST[ 'media_id' ] );

			$this->reset_media_terms( $post_id, $attributes );

			// build array of custom taxonomy with associated terms
			// $new_attributes came like [0] => ( [0] => rtmedia_attr[attribute_name][], [1] => term_id )
			$attributes_array = array();
			foreach ( $new_attributes as $attribute_arr ) {
				$attr_name = substr( $attribute_arr[ 0 ], strpos( $attribute_arr[ 0 ], "[" ) + 1, ( strpos( $attribute_arr[ 0 ], "]" ) - strpos( $attribute_arr[ 0 ], "[" ) ) - 1 );
				if ( ! isset( $attributes_array[ $attr_name ] ) ){
					$attributes_array[ $attr_name ] = array();
				}
				$attributes_array[ $attr_name ][ ] = $attribute_arr[ 1 ];
			}

			$this->set_media_terms( $post_id, $attributes_array );
		}

		function rtmedia_bulk_edit_change_attributes( $selected_ids, $new_attributes ) {
			$rtmedia_attr_model = new RTMediaAttributesModel();
			$attributes         = $rtmedia_attr_model->get_all_attributes();

			foreach ( $selected_ids as $id ) {

				$post_id = rtmedia_media_id( $id );

				$this->reset_media_terms( $post_id, $attributes );

				// build array of custom taxonomy with associated terms
				// $new_attributes came like [0] => ( [0] => rtmedia_attr[attribute_name][], [1] => term_id )
				$attributes_array = array();
				foreach ( $new_attributes as $attribute_arr ) {
					$attr_name = substr( $attribute_arr[ 0 ], strpos( $attribute_arr[ 0 ], "[" ) + 1, ( strpos( $attribute_arr[ 0 ], "]" ) - strpos( $attribute_arr[ 0 ], "[" ) ) - 1 );
					if ( ! isset( $attributes_array[ $attr_name ] ) ){
						$attributes_array[ $attr_name ] = array();
					}
					$attributes_array[ $attr_name ][ ] = $attribute_arr[ 1 ];
				}

				$this->set_media_terms( $post_id, $attributes_array );
			}

			return 1;
		}

		function allow_add_attribute_render() {

			$rtmedia_attr_model = new RTMediaAttributesModel();
			$attributes         = $rtmedia_attr_model->get_all_attributes();

			if ( ! sizeof( $attributes ) > 0 ){
				return false;
			}
			$allow_render = false;
			foreach ( $attributes as $attr ) {
				if ( $attr->attribute_store_as == 'taxonomy' ){
					$taxonomy_name = rtmedia_pro_attribute_taxonomy_name( $attr->attribute_name );
					$terms         = get_terms( $taxonomy_name, array( 'hide_empty' => false, ) );
					if ( ! empty( $terms ) ){
						$allow_render = true;
						break;
					}
				}
			}

			return apply_filters( 'rtmedia_allow_add_attribute_render', $allow_render );
		}

		function add_media_attributes_file_upload( $content ) {

			// do not render if attributes aren't configured
			$allow_render = $this->allow_add_attribute_render();

			$add_terms = '<div class="rtmedia-upload-add-attributes clear"> <input type="button" id="rtmedia-upload-add-attributes-button" value="' . apply_filters( "rtmedia_upload_add_attribute_label", __( "Add Attributes", "rtmedia" ) ) . '" /> <input type="hidden" name="rtmedia_allow_upload_attribute" value="0" class="rtmedia-allow-upload-attribute" /> </div>';

			if ( $allow_render ){
				return $content . $add_terms . $this->add_media_attributes_edit_screen();
			} else {
				return $content;
			}

		}

		function add_attribute_bulk_edit() {

			// do not render if attributes aren't configured
			$allow_render = $this->allow_add_attribute_render();
			if ( ! $allow_render ){
				return;
			}

			?>
			<button type="button" class="rtmedia-bulk-change-attributes"
					title="<?php _e( 'Change attributes of selected medias' ); ?>"><?php _e( 'Change Attributes' ); ?></button>
		<?php
		}

		function add_media_attributes_bulk_edit_screen() {

			// do not render if attributes aren't configured
			$allow_render = $this->allow_add_attribute_render();
			if ( ! $allow_render ){
				return;
			}

			?>
			<div class="rtmedia-bulk-edit-attributes">
				<div class="row">
					<span
						class="rtm-bulk-edit-attributes-warning">*All the existing attributes will be overridden.</span>
				</div>
				<?php
				echo $this->add_media_attributes_edit_screen();
				?>
				<div class="row">
					<input type="button" class="rtmedia-bulk-media-attribute-save"
						   name="change_bulk_media_attribute_save"
						   value="<?php _e( 'Save Attributes', 'rtmedia' ); ?>"/>
				</div>
			</div>
		<?php
		}

		function show_media_attributes( $id ) {
			$rtmedia_attr_model = new RTMediaAttributesModel();
			$attributes         = $rtmedia_attr_model->get_all_attributes();
			$media_post_id      = rtmedia_media_id( $id );
			$terms_available    = false;
			$terms_content      = "";
			if ( apply_filters( 'rtmedia_view_media_attributes', true ) ){
				foreach ( $attributes as $attr ) {
					if ( $attr->attribute_store_as == 'taxonomy' ){
						if ( $attr->attribute_orderby == "name" ){
							$term_order = "name";
						} else {
							$term_order = "id";
						}
						$taxonomy_name = rtmedia_pro_attribute_taxonomy_name( $attr->attribute_name );
						$terms         = wp_get_post_terms( $media_post_id, $taxonomy_name, array( 'orderby' => $term_order, "fields" => "names" ) );
						if ( ! empty( $terms ) ){
							$terms_available = true;
							$terms_content .= "<span class='rtmedia-media-attribute'><label>" . $attr->attribute_label . ":</label>&nbsp;" . implode( ", ", $terms ) . "</span>";
						}
					}
				}
				if ( $terms_available ){
					do_action( 'rtmedia_before_media_attributes' );
					?>
					<div class="rtmedia-media-attributes">
						<?php echo $terms_content; ?>
					</div>
					<?php
					do_action( 'rtmedia_after_media_attributes' );
				}
			}
		}

		function reset_media_terms( $post_id, $attributes ) {
			foreach ( $attributes as $attr ) {
				if ( $attr->attribute_store_as == 'taxonomy' ){
					wp_set_post_terms( $post_id, '0', rtmedia_pro_attribute_taxonomy_name( $attr->attribute_name ), false );
				}
			}
		}

		function set_media_terms( $post_id, $media_attributes ) {
			if ( is_array( $media_attributes ) && sizeof( $media_attributes ) > 0 ){
				foreach ( $media_attributes as $attribute => $term ) {
					wp_set_post_terms( $post_id, $term, rtmedia_pro_attribute_taxonomy_name( $attribute ), false );
				}
			}
		}

		function rtmedia_add_media_attributes( $id, $state ) {
			if ( $state ){
				$media_attributes   = $_POST[ 'rtmedia_attr' ];
				$rtmedia_attr_model = new RTMediaAttributesModel();
				$attributes         = $rtmedia_attr_model->get_all_attributes();
				//$attr_array = array();
				//$attr_array[] = rtmedia_pro_attribute_taxonomy_name( $attr->attribute_name );
				//wp_delete_object_term_relationships( rtmedia_media_id( $id ), $attr_array );
				$post_id = rtmedia_media_id( $id );
				$this->reset_media_terms( $post_id, $attributes );
				$this->set_media_terms( $post_id, $media_attributes );
			}
		}

		function add_media_attributes_edit_fields( $media_type ) {
			if ( apply_filters( 'rtmedia_edit_media_attribute_select', true ) ){
				// do not render if attributes aren't configured
				$allow_render = $this->allow_add_attribute_render();
				if ( ! $allow_render ){
					return;
				}
				global $rtmedia_query;
				echo $this->add_media_attributes_edit_screen( $media_type, $rtmedia_query->media[ 0 ]->media_id );
			}
		}

		function add_media_attributes_edit_screen( $media_type = false, $media_post_id = false ) {
			$attribute_content = '';
			if ( apply_filters( 'rtmedia_allow_attribute_' . $media_type, true ) ){
				$rtmedia_attr_model = new RTMediaAttributesModel();
				$attributes         = $rtmedia_attr_model->get_all_attributes();
				if ( is_array( $attributes ) && sizeof( $attributes ) > 0 ){
					$attribute_content .= ' <div class="rtmedia-editor-attributes"> ';

					$attribute_content .= ' <label class="rtm-attr-edit-heading">' . __( apply_filters( 'rtmedia_editor_attribute_label', 'Media Attributes' ) . ":", 'rtmedia' ) . ' </label> ';

					foreach ( $attributes as $attr ) {
						if ( $attr->attribute_store_as == 'taxonomy' ){
							if ( $attr->attribute_orderby == "name" ){
								$term_order = "name";
							} else {
								$term_order = "id";
							}
							$taxonomy_name = rtmedia_pro_attribute_taxonomy_name( $attr->attribute_name );
							$terms         = get_terms( $taxonomy_name, array( 'orderby' => $term_order, 'hide_empty' => false, ) );
							if ( ! empty( $terms ) ){
								switch ( $attr->attribute_render_type ) {
									case "dropdown" :
									{
										$options    = array();
										$options[ ] = array( '--' => '0', 'selected' => '0' );
										foreach ( $terms as $term ) {
											if ( $media_post_id && has_term( $term->term_id, $taxonomy_name, $media_post_id ) ){
												$selected = 1;
											} else {
												$selected = 0;
											}
											$options[ ] = array( $term->name => $term->term_id, 'selected' => $selected );
										}

										$attribute_content .= '<div class="rtmedia-edit-attr-select row">';

										$attribute_content .= '<label class="rtm-attr-label columns large-5">' . $attr->attribute_label . ':</label>';

										$attribute_content .= '<div class="columns large-7">';
										$attribute_content .= $this->render_dropdown( $attr, $options );
										$attribute_content .= '</div>';

										$attribute_content .= '</div>';
									}
										break;

									case "checklist" :
										$options = array();
										foreach ( $terms as $term ) {
											if ( $media_post_id && has_term( $term->term_id, $taxonomy_name, $media_post_id ) ){
												$selected = 1;
											} else {
												$selected = 0;
											}
											$options[ ] = array( $term->name => $term->term_id, 'checked' => $selected );
										}
										$attribute_content .= ' <div class="rtmedia-edit-attr-checkbox row"> ';

										$attribute_content .= '<label class="rtm-attr-label columns large-5">' . $attr->attribute_label . ':</label>';

										$attribute_content .= '<div class="columns large-7">';
										$attribute_content .= $this->render_checklist( $attr, $options );
										$attribute_content .= '</div>';

										$attribute_content .= '</div>';
										break;
								}
							}
						}
					}
					$attribute_content .= '</div>';
				}
			}

			return $attribute_content;
		}

		function init_modules() {
			$rtmedia_attr_model = new RTMediaAttributesModel();
			$attributes         = $rtmedia_attr_model->get_all_attributes();
			foreach ( $attributes as $attr ) {
				if ( $attr->attribute_store_as == 'taxonomy' ){
					$this->register_taxonomy( 'attachment', $attr->id );
				}
			}
		}

		function attributes_admin_page_hooks() {
			add_filter( "rtmedia_filter_admin_pages_array", array( $this, "rtmedia_add_admin_page_array" ), 10, 1 );
			add_action( 'admin_menu', array( $this, 'add_category_menu' ), 100 );
		}

		function add_category_menu() {
			add_submenu_page( 'rtmedia-settings', __( 'Attributes', 'rtmedia' ), __( 'Attributes ', 'rtmedia' ), 'manage_options', 'rtmedia-attributes', array( $this, 'attributes_page' ) );
		}

		function rtmedia_add_admin_page_array( $admin_pages ) {
			$admin_pages[ ] = "rtmedia_page_rtmedia-categories";

			return $admin_pages;
		}

		function attributes_page() {
			$this->rtmedia_pro_attributes();
		}

		function register_taxonomy( $post_type, $attr_id ) {
			$rtmedia_attr_model = new RTMediaAttributesModel();
			$tax                = $rtmedia_attr_model->get_attribute( $attr_id );
			$name               = rtmedia_pro_attribute_taxonomy_name( $tax->attribute_name );
			$hierarchical       = true;
			if ( $name ){

				$label = ( isset( $tax->attribute_label ) && $tax->attribute_label ) ? $tax->attribute_label : $tax->attribute_name;

				$show_in_nav_menus = apply_filters( 'rtmedia_pro_attribute_show_in_nav_menus', true, $name );

				register_taxonomy(
					$name,
					apply_filters( 'rtmedia_pro_taxonomy_objects_' . $name, $post_type ),
					apply_filters( 'rtmedia_pro_taxonomy_args_' . $name, array(
						'hierarchical' => $hierarchical, //
						'update_count_callback' 	=> array( $this, 'rtmedia_pro_update_post_term_count' ),
						'labels'       => array(
							'name' => $label, 'singular_name' => $label, 'search_items' => __( 'Search' ) . ' ' . $label, 'all_items' => __( 'All' ) . ' ' . $label, 'parent_item' => __( 'Parent' ) . ' ' . $label, 'parent_item_colon' => __( 'Parent' ) . ' ' . $label . ':', 'edit_item' => __( 'Edit' ) . ' ' . $label, 'update_item' => __( 'Update' ) . ' ' . $label, 'add_new_item' => __( 'Add New' ) . ' ' . $label, 'new_item_name' => __( 'New' ) . ' ' . $label
							),
						'show_ui'   => true,
						'query_var' => true,
						'show_in_nav_menus' => $show_in_nav_menus,
//						'rewrite' 					=> array( 'slug' => $product_attribute_base . sanitize_title( $tax->attribute_name ), 'with_front' => false, 'hierarchical' => $hierarchical ),
						'rewrite'      => true,
				) ) );
			}
		}

		function rtmedia_pro_update_post_term_count( $terms, $taxonomy ) {
			global $wpdb;

			$object_types = (array) $taxonomy->object_type;

			foreach ( $object_types as &$object_type )
				list( $object_type ) = explode( ':', $object_type );

			$object_types = array_unique( $object_types );

			if ( false !== ( $check_attachments = array_search( 'attachment', $object_types ) ) ) {
				unset( $object_types[ $check_attachments ] );
				$check_attachments = true;
			}

			if ( $object_types )
				$object_types = esc_sql( array_filter( $object_types, 'post_type_exists' ) );

			foreach ( (array) $terms as $term ) {
				$count = 0;

				// Attachments can be 'inherit' status, we need to base count off the parent's status if so
				if ( $check_attachments )
					$count += (int) $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM $wpdb->term_relationships, $wpdb->posts p1 WHERE p1.ID = $wpdb->term_relationships.object_id  AND post_type = 'attachment' AND term_taxonomy_id = %d", $term ) );

				if ( $object_types )
					$count += (int) $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM $wpdb->term_relationships, $wpdb->posts WHERE $wpdb->posts.ID = $wpdb->term_relationships.object_id  AND post_type IN ('" . implode("', '", $object_types ) . "') AND term_taxonomy_id = %d", $term ) );

				do_action( 'edit_term_taxonomy', $term, $taxonomy );
				$wpdb->update( $wpdb->term_taxonomy, compact( 'count' ), array( 'term_taxonomy_id' => $term ) );
				do_action( 'edited_term_taxonomy', $term, $taxonomy );
			}
		}

		function perform_action() {
			global $wpdb;
			$rtmedia_attr_model = new RTMediaAttributesModel();
			$action_completed   = false;

			// Action to perform: add, edit, delete or none
			$action = '';
			if ( ! empty( $_POST[ 'add_new_attribute' ] ) ){
				$action = 'add';
			} elseif ( ! empty( $_POST[ 'save_attribute' ] ) && ! empty( $_GET[ 'edit' ] ) ) {
				$action = 'edit';
			} elseif ( ! empty( $_GET[ 'delete' ] ) ) {
				$action = 'delete';
			}

			// Add or edit an attribute
			if ( 'add' === $action || 'edit' === $action ){

				if ( 'edit' === $action ){
					$attribute_id = absint( $_GET[ 'edit' ] );
				}

				// Grab the submitted data
				$attribute_label       = ( isset( $_POST[ 'attribute_label' ] ) ) ? (string)stripslashes( $_POST[ 'attribute_label' ] ) : '';
				$attribute_name        = ( isset( $_POST[ 'attribute_name' ] ) ) ? rtmedia_pro_sanitize_taxonomy_name( stripslashes( (string)$_POST[ 'attribute_name' ] ) ) : '';
				$attribute_store_as    = ( isset( $_POST[ 'attribute_store_as' ] ) ) ? (string)stripslashes( $_POST[ 'attribute_store_as' ] ) : '';
				$attribute_render_type = ( isset( $_POST[ 'attribute_render_type' ] ) ) ? (string)stripslashes( $_POST[ 'attribute_render_type' ] ) : '';
				$attribute_orderby     = ( isset( $_POST[ 'attribute_orderby' ] ) ) ? (string)stripslashes( $_POST[ 'attribute_orderby' ] ) : '';

				// Auto-generate the label or slug if only one of both was provided
				if ( ! $attribute_label ){
					$attribute_label = ucwords( $attribute_name );
				} elseif ( ! $attribute_name ) {
					$attribute_name = rtmedia_pro_sanitize_taxonomy_name( stripslashes( $attribute_label ) );
				}

				// Forbidden attribute names
				// http://codex.wordpress.org/Function_Reference/register_taxonomy#Reserved_Terms
				$reserved_terms = array(
					'attachment', 'attachment_id', 'author', 'author_name', 'calendar', 'cat', 'category', 'category__and', 'category__in', 'category__not_in', 'category_name', 'comments_per_page', 'comments_popup', 'cpage', 'day', 'debug', 'error', 'exact', 'feed', 'hour', 'link_category', 'm', 'minute', 'monthnum', 'more', 'name', 'nav_menu', 'nopaging', 'offset', 'order', 'orderby', 'p', 'page', 'page_id', 'paged', 'pagename', 'pb', 'perm', 'post', 'post__in', 'post__not_in', 'post_format', 'post_mime_type', 'post_status', 'post_tag', 'post_type', 'posts', 'posts_per_archive_page', 'posts_per_page', 'preview', 'robots', 's', 'search', 'second', 'sentence', 'showposts', 'static', 'subpost', 'subpost_id', 'tag', 'tag__and', 'tag__in', 'tag__not_in', 'tag_id', 'tag_slug__and', 'tag_slug__in', 'taxonomy', 'tb', 'term', 'type', 'w', 'withcomments', 'withoutcomments', 'year',
				);

				// Error checking
				if ( ! $attribute_name || ! $attribute_name || ! $attribute_render_type || ! $attribute_store_as ){
					$error = __( 'Please, provide an attribute name, slug, storage type and render type.' );
				} elseif ( strlen( $attribute_name ) >= 28 ) {
					$error = sprintf( __( 'Slug "%s" is too long (28 characters max). Shorten it, please.' ), sanitize_title( $attribute_name ) );
				} elseif ( in_array( $attribute_name, $reserved_terms ) ) {
					$error = sprintf( __( 'Slug "%s" is not allowed because it is a reserved term. Change it, please.' ), sanitize_title( $attribute_name ) );
				} else {
					$taxonomy_exists = $rtmedia_attr_model->attribute_exists( rtmedia_pro_sanitize_taxonomy_name( $attribute_name ) );

					if ( 'add' === $action && $taxonomy_exists ){
						$error = sprintf( __( 'Slug "%s" is already in use. Change it, please.' ), sanitize_title( $attribute_name ) );
					}
					if ( 'edit' === $action ){
						$old_attribute_name = $rtmedia_attr_model->get_attribute_name( $attribute_id );
						if ( $old_attribute_name != $attribute_name && rtmedia_pro_sanitize_taxonomy_name( $old_attribute_name ) != $attribute_name && $taxonomy_exists ){
							$error = sprintf( __( 'Slug "%s" is already in use. Change it, please.' ), sanitize_title( $attribute_name ) );
						}
					}
				}

				// Show the error message if any
				if ( ! empty( $error ) ){
					echo '<div id="rtmedia_pro_errors" class="error fade"><p>' . $error . '</p></div>';
				} else {

					// Add new attribute
					if ( 'add' === $action ){

						$attribute = array(
							'attribute_label' => $attribute_label, 'attribute_name' => $attribute_name, 'attribute_store_as' => $attribute_store_as, 'attribute_render_type' => $attribute_render_type, 'attribute_orderby' => $attribute_orderby,
						);

						$rtmedia_attr_model->add_attribute( $attribute );

						do_action( 'rtmedia_pro_attribute_added', $wpdb->insert_id, $attribute );

						$action_completed = true;
					}

					// Edit existing attribute
					if ( 'edit' === $action ){

						$attribute = array(
							'attribute_label' => $attribute_label, 'attribute_name' => $attribute_name, 'attribute_store_as' => $attribute_store_as, 'attribute_render_type' => $attribute_render_type, 'attribute_orderby' => $attribute_orderby,
						);

						$rtmedia_attr_model->update_attribute( $attribute, array( 'id' => $attribute_id ) );

						do_action( 'rtmedia_pro_attribute_updated', $attribute_id, $attribute, $old_attribute_name );

						if ( $old_attribute_name != $attribute_name && ! empty( $old_attribute_name ) ){
							// Update taxonomies in the wp term taxonomy table
							$wpdb->update( $wpdb->term_taxonomy, array( 'taxonomy' => rtmedia_pro_attribute_taxonomy_name( $attribute_name ) ), array( 'taxonomy' => 'rt_' . $old_attribute_name ) );

							//							// Update taxonomy ordering term meta
							//							$wpdb->update(
							//								$wpdb->prefix . 'woocommerce_termmeta',
							//								array( 'meta_key' => 'order_pa_' . sanitize_title( $attribute_name ) ),
							//								array( 'meta_key' => 'order_pa_' . sanitize_title( $old_attribute_name ) )
							//							);

							//							// Update product attributes which use this taxonomy
							//							$old_attribute_name_length = strlen( $old_attribute_name ) + 3;
							//							$attribute_name_length = strlen( $attribute_name ) + 3;
							//
							//							$wpdb->query( "
							//								UPDATE {$wpdb->postmeta}
							//								SET meta_value = REPLACE( meta_value, 's:{$old_attribute_name_length}:\"pa_{$old_attribute_name}\"', 's:{$attribute_name_length}:\"pa_{$attribute_name}\"' )
							//								WHERE meta_key = '_product_attributes'"
							//							);

							//							// Update variations which use this taxonomy
							//							$wpdb->update(
							//								$wpdb->postmeta,
							//								array( 'meta_key' => 'attribute_pa_' . sanitize_title( $attribute_name ) ),
							//								array( 'meta_key' => 'attribute_pa_' . sanitize_title( $old_attribute_name ) )
							//							);
						}

						$action_completed = true;
					}

					flush_rewrite_rules();
				}
			}

			// Delete an attribute
			if ( 'delete' === $action ){
				$attribute_id = absint( $_GET[ 'delete' ] );

				$attribute_name = $rtmedia_attr_model->get_attribute_name( $attribute_id );

				if ( $attribute_name && $rtmedia_attr_model->delete( array( 'id' => $attribute_id ) ) ){

					$taxonomy = rtmedia_pro_attribute_taxonomy_name( $attribute_name );

					if ( taxonomy_exists( $taxonomy ) ){
						$terms = get_terms( $taxonomy, 'orderby=name&hide_empty=0' );
						foreach ( $terms as $term ) {
							wp_delete_term( $term->term_id, $taxonomy );
						}
					}

					do_action( 'rtmedia_pro_attribute_deleted', $attribute_id, $attribute_name, $taxonomy );

					$action_completed = true;
				}
			}

			return $action_completed;
		}

		function rtmedia_pro_attributes() {

			$action_completed = $this->perform_action();

			// If an attribute was added, edited or deleted: clear cache and redirect
			if ( ! empty( $action_completed ) ){
				wp_redirect( admin_url( 'admin.php?page=rtmedia-attributes' ) );
			}

			// Show admin interface
			if ( ! empty( $_GET[ 'edit' ] ) ){
				$this->rtmedia_pro_edit_attribute();
			} else {
				$this->rtmedia_pro_add_attribute();
			}
		}

		function rtmedia_pro_edit_attribute() {
			global $wpdb;
			$rtmedia_attr_model = new RTMediaAttributesModel();
			$edit               = absint( $_GET[ 'edit' ] );

			$attribute_to_edit = $rtmedia_attr_model->get_attribute( $edit );

			$att_store_as    = $attribute_to_edit->attribute_store_as;
			$att_render_type = $attribute_to_edit->attribute_render_type;
			$att_label       = $attribute_to_edit->attribute_label;
			$att_name        = $attribute_to_edit->attribute_name;
			$att_orderby     = $attribute_to_edit->attribute_orderby;
			?>
			<div class="wrap">
				<h2><i class="icon-tag"></i> <?php _e( 'Edit Attribute' ) ?></h2>

				<form action="admin.php?page=rtmedia-attributes&amp;edit=<?php echo absint( $edit ); ?>" method="post">
					<table class="form-table">
						<tbody>
						<tr class="form-field form-required">
							<th scope="row" valign="top">
								<label for="attribute_label"><?php _e( 'Name' ); ?></label>
							</th>
							<td>
								<input name="attribute_label" id="attribute_label" type="text"
									   value="<?php echo esc_attr( $att_label ); ?>"/>

								<p class="description"><?php _e( 'Name for the attribute (shown on the front-end).' ); ?></p>
							</td>
						</tr>
						<tr class="form-field form-required">
							<th scope="row" valign="top">
								<label for="attribute_name"><?php _e( 'Slug' ); ?></label>
							</th>
							<td>
								<input name="attribute_name" id="attribute_name" type="text"
									   value="<?php echo esc_attr( $att_name ); ?>" maxlength="28"/>

								<p class="description"><?php _e( 'Unique slug/reference for the attribute; must be shorter than 28 characters.' ); ?></p>
								<input type="hidden" name="attribute_store_as" value="taxonomy">
							</td>
						</tr>
						<!--						<tr class="form-field form-required">-->
						<!--							<th scope="row" valign="top">-->
						<!--								<label for="attribute_store_as">-->
						<?php //_e( 'Store As' ); ?><!--</label>-->
						<!--							</th>-->
						<!--							<td>-->
						<!--								<select name="attribute_store_as" id="attribute_store_as">-->
						<!--									<option-->
						<!--										value="taxonomy" -->
						<?php //selected( $att_store_as, 'tax' ); ?><!--><?php //_e( 'Taxonomy' ); ?><!--</option>-->
						<!--									<option-->
						<!--										value="meta" -->
						<?php //selected( $att_store_as, 'meta' ); ?><!--><?php //_e( 'Meta Value' ); ?><!--</option>-->
						<!--									--><?php //do_action( 'rtmedia_pro_admin_attribute_store_as' ); ?>
						<!--								</select>-->
						<!---->
						<!--								<p class="description">-->
						<?php //_e( 'Determines how you want to store attributes.' ); ?><!--</p>-->
						<!--							</td>-->
						<!--						</tr>-->
						<tr class="form-field form-required">
							<th scope="row" valign="top">
								<label for="attribute_render_type"><?php _e( 'Render Type' ); ?></label>
							</th>
							<td>
								<select name="attribute_render_type" id="attribute_render_type">
									<!--									<optgroup label="Taxonomy">-->
									<!--<option value="autocomplete" <?php selected( $att_render_type, 'autocomplete' ); ?>><?php _e( 'Autocomplete' ); ?></option>-->
									<option
										value="dropdown" <?php selected( $att_render_type, 'dropdown' ); ?>><?php _e( 'Dropdown (Single Select)', 'rtmedia' ); ?></option>
									<option
										value="checklist" <?php selected( $att_render_type, 'checklist' ); ?>><?php _e( 'Checklist (Multiple Select)', 'rtmedia' ); ?></option>
									<!--<option value="radio" <?php selected( $att_render_type, 'radio' ); ?>><?php _e( 'Radio' ); ?></option>-->
									<!--										<option value="rating-stars" -->
									<?php //selected( $att_render_type, 'rating-stars' ); ?><!-->-->
									<?php //_e( 'Rating Stars' ); ?><!--</option>-->
									<!--									</optgroup>-->
									<!--									<optgroup label="Meta">-->
									<!--										<option value="date" -->
									<?php //selected( $att_render_type, 'date' ); ?><!-->-->
									<?php //_e( 'Date' ); ?><!--</option>-->
									<!--										<option value="datetime" -->
									<?php //selected( $att_render_type, 'datetime' ); ?><!-->-->
									<?php //_e( 'Date & Time' ); ?><!--</option>-->
									<!--										<option value="currency" -->
									<?php //selected( $att_render_type, 'currency' ); ?><!-->-->
									<?php //_e( 'Currency' ); ?><!--</option>-->
									<!--										<option value="text" -->
									<?php //selected( $att_render_type, 'text' ); ?><!-->-->
									<?php //_e( 'Text' ); ?><!--</option>-->
									<!--<option value="richtext" <?php selected( $att_render_type, 'richtext' ); ?>><?php _e( 'Rich Text' ); ?></option>-->
									<!--									</optgroup>-->
									<?php do_action( 'rtmedia_pro_admin_attribute_render_types' ); ?>
								</select>

								<p class="description"><?php _e( 'Determines how you select attributes.' ); ?></p>
							</td>
						</tr>
						<tr class="form-field form-required">
							<th scope="row" valign="top">
								<label for="attribute_orderby"><?php _e( 'Default sort order' ); ?></label>
							</th>
							<td>
								<select name="attribute_orderby" id="attribute_orderby">
									<!--									<option value="menu_order" -->
									<?php //selected( $att_orderby, 'menu_order' ); ?><!-->
									<?php //_e( 'Custom ordering' ); ?><!--</option>-->
									<option
										value="name" <?php selected( $att_orderby, 'name' ); ?>><?php _e( 'Name' ); ?></option>
									<option
										value="id" <?php selected( $att_orderby, 'id' ); ?>><?php _e( 'Term ID' ); ?></option>
								</select>

								<p class="description"><?php _e( 'Determines the sort order on the frontend for this attribute.' ); ?></p>
							</td>
						</tr>
						</tbody>
					</table>
					<p class="submit"><input type="submit" name="save_attribute" id="submit" class="button-primary"
											 value="<?php _e( 'Update' ); ?>"></p>
					<?php //nonce ?>
				</form>
			</div>
		<?php

		}


		/**
		 * Add Attribute admin panel
		 *
		 * Shows the interface for adding new attributes
		 *
		 * @access public
		 * @return void
		 */
		function rtmedia_pro_add_attribute() {
			$rtmedia_attr_model = new RTMediaAttributesModel();
			?>
			<div class="wrap">
				<h2><i class="icon-tags"></i> <?php _e( 'Attributes' ); ?></h2>
				<br class="clear"/>

				<div id="col-container">
					<div id="col-right">
						<div class="col-wrap">
							<table class="widefat fixed" style="width:100%">
								<thead>
								<tr>
									<th scope="col"><?php _e( 'Name' ); ?></th>
									<th scope="col"><?php _e( 'Slug' ); ?></th>
									<th scope="col"><?php _e( 'Store As' ); ?></th>
									<th scope="col"><?php _e( 'Render Type' ); ?></th>
									<th scope="col"><?php _e( 'Order by' ); ?></th>
									<th scope="col" colspan="2"><?php _e( 'Terms' ); ?></th>
								</tr>
								</thead>
								<tbody>
								<?php
								$attribute_taxonomies = $rtmedia_attr_model->get_all_attributes();
								if ( $attribute_taxonomies ) :
									foreach ( $attribute_taxonomies as $tax ) :
										?>
										<tr>
										<td>
											<?php echo esc_html( $tax->attribute_label ); ?>
											<div class="row-actions"><span class="edit"><a
														href="<?php echo esc_url( add_query_arg( 'edit', $tax->id, 'admin.php?page=rtmedia-attributes' ) ); ?>"><?php _e( 'Edit' ); ?></a> | </span><span
													class="delete"><a class="delete"
																	  href="<?php echo esc_url( add_query_arg( 'delete', $tax->id, 'admin.php?page=rtmedia-attributes' ) ); ?>"><?php _e( 'Delete' ); ?></a></span>
											</div>
										</td>
										<td><?php echo esc_html( $tax->attribute_name ); ?></td>
										<td><?php echo esc_html( ucwords( str_replace( '-', ' ', $tax->attribute_store_as ) ) ); ?></td>
										<td><?php echo esc_html( ucwords( str_replace( '-', ' ', $tax->attribute_render_type ) ) ); ?></td>
										<td><?php
											switch ( $tax->attribute_orderby ) {
												case 'name' :
													_e( 'Name' );
													break;
												case 'id' :
													_e( 'Term ID' );
													break;
												default:
													_e( 'Custom ordering' );
													break;
											}
											?></td>
										<td><?php
											if ( taxonomy_exists( rtmedia_pro_attribute_taxonomy_name( $tax->attribute_name ) ) ) :
												$terms_array = array();
												$terms       = get_terms( rtmedia_pro_attribute_taxonomy_name( $tax->attribute_name ), 'orderby=name&hide_empty=0' );
												if ( $terms ) :
													foreach ( $terms as $term ) :
														$terms_array[ ] = $term->name;
													endforeach;
													echo implode( ', ', $terms_array );
												else :
													echo '<span class="na">&ndash;</span>';
												endif;
											else :
												echo '<span class="na">&ndash;</span>';
											endif;
											?></td>
										<td>
											<a href="edit-tags.php?taxonomy=<?php echo esc_html( rtmedia_pro_attribute_taxonomy_name( $tax->attribute_name ) ); ?>&amp;post_type=attachment"
											   class="button alignright"><?php _e( 'Configure', 'rtmedia' ); ?></a>
										</td>
										</tr><?php
									endforeach;
								else :
									?>
									<tr>
									<td colspan="6"><?php _e( 'No attributes currently exist.' ) ?></td></tr><?php
								endif;
								?>
								</tbody>
							</table>
						</div>
					</div>
					<div id="col-left">
						<div class="col-wrap">
							<div class="form-wrap">
								<h3><?php _e( 'Add New Attribute' ) ?></h3>

								<form action="admin.php?page=rtmedia-attributes" method="post">
									<div class="form-field">
										<label for="attribute_label"><?php _e( 'Name' ); ?></label>
										<input name="attribute_label" id="attribute_label" type="text" value=""/>

										<p class="description"><?php _e( 'Name for the attribute (shown on the front-end).' ); ?></p>
									</div>

									<div class="form-field">
										<label for="attribute_name"><?php _e( 'Slug' ); ?></label>
										<input name="attribute_name" id="attribute_name" type="text" value=""
											   maxlength="28"/>

										<p class="description"><?php _e( 'Unique slug/reference for the attribute; must be shorter than 28 characters.' ); ?></p>
									</div>

									<!--									<div class="form-field">-->
									<!--										<label for="attribute_store_as">-->
									<?php //_e( 'Store As' ); ?><!--</label>-->
									<!--										<select name="attribute_store_as" id="attribute_store_as">-->
									<!--											<option value="taxonomy">-->
									<?php //_e( 'Taxonomy' ); ?><!--</option>-->
									<!--											<option value="meta">-->
									<?php //_e( 'Meta Value' ); ?><!--</option>-->
									<!--											--><?php //do_action( 'rtmedia_pro_admin_attribute_store_as' ); ?>
									<!--										</select>-->
									<!---->
									<!--										<p class="description">-->
									<?php //_e( 'Determines the sort order on the frontend for this attribute.' ); ?><!--</p>-->
									<!--									</div>-->

									<input type="hidden" name="attribute_store_as" value="taxonomy">

									<div class="form-field">
										<label for="attribute_render_type"><?php _e( 'Render Type' ); ?></label>
										<select name="attribute_render_type" id="attribute_render_type">
											<!--											<optgroup label="Taxonomy">-->
											<!--<option value="autocomplete"><?php _e( 'Autocomplete' ); ?></option>-->
											<option value="dropdown"><?php _e( 'Dropdown (Single Select)' ); ?></option>
											<option
												value="checklist"><?php _e( 'Checklist (Multiple select)' ); ?></option>
											<!--<option value="radio"><?php _e( 'Radio' ); ?></option>-->
											<!--												<option value="rating-stars">-->
											<?php //_e( 'Rating Stars' ); ?><!--</option>-->
											<!--											</optgroup>-->
											<!--											<optgroup label="Meta">-->
											<!--												<option value="date">-->
											<?php //_e( 'Date' ); ?><!--</option>-->
											<!--												<option value="datetime">-->
											<?php //_e( 'Date & Time' ); ?><!--</option>-->
											<!--												<option value="currency">-->
											<?php //_e( 'Currency' ); ?><!--</option>-->
											<!--												<option value="text">-->
											<?php //_e( 'Text' ); ?><!--</option>-->
											<!--<option value="richtext"><?php _e( 'Rich Text' ); ?></option>-->
											<!--											</optgroup>-->
											<?php do_action( 'rtmedia_pro_admin_attribute_render_types' ); ?>
										</select>

										<p class="description"><?php _e( 'Determines the sort order on the frontend for this attribute.' ); ?></p>
									</div>

									<div class="form-field">
										<label for="attribute_orderby"><?php _e( 'Default sort order' ); ?></label>
										<select name="attribute_orderby" id="attribute_orderby">
											<!--											<option value="menu_order">-->
											<?php //_e( 'Custom ordering' ); ?><!--</option>-->
											<option value="name"><?php _e( 'Name' ); ?></option>
											<option value="id"><?php _e( 'Term ID' ); ?></option>
										</select>

										<p class="description"><?php _e( 'Determines the sort order on the frontend for this attribute.' ); ?></p>
									</div>

									<p class="submit"><input type="submit" name="add_new_attribute" id="submit"
															 class="button" value="<?php _e( 'Add Attribute' ); ?>"></p>
									<?php //nonce ?>
								</form>
							</div>
						</div>
					</div>
				</div>
				<script type="text/javascript">
					/* <![CDATA[ */

					jQuery( 'a.delete' ).click( function () {
						var answer = confirm( "<?php _e( 'Are you sure you want to delete this attribute?' ); ?>" );
						if ( answer ) return true;
						return false;
					} );

					/* ]]> */
				</script>
			</div>
		<?php
		}

		//		function attribute_diff( $attr, $post_id, $newLead ) {
		//
		//			$diffHTML = '';
		//			switch ( $attr->attribute_store_as ) {
		//				case 'taxonomy':
		//					$diffHTML = $this->taxonomy_diff( $attr, $post_id, $newLead );
		//					break;
		//				case 'meta':
		//					$diffHTML = $this->meta_diff( $attr, $post_id, $newLead );
		//					break;
		//				default:
		//					$diffHTML = apply_filters( 'rtmedia_pro_attribute_diff', $diffHTML, $attr, $post_id, $newLead );
		//					break;
		//			}
		//			return $diffHTML;
		//		}

		//		function taxonomy_diff( $attr, $post_id, $newLead ) {
		//			$diffHTML = '';
		//			switch ( $attr->attribute_render_type ) {
		//				//				case 'autocomplete':
		//				//					break;
		//				case 'dropdown':
		//				case 'rating-stars':
		//					if ( !isset( $newLead[$attr->attribute_name] ) ) {
		//						$newLead[$attr->attribute_name] = array();
		//					}
		//					$newVals = $newLead[$attr->attribute_name];
		//					$newVals = array_unique($newVals);
		//
		//					$get_post_terms = wp_get_post_terms( $post_id, rtmedia_pro_attribute_taxonomy_name( $attr->attribute_name ) );
		//					if ( $get_post_terms ) {
		//						$post_term_slug = $get_post_terms[0]->term_id;
		//						$post_term_name = $get_post_terms[0]->name;
		//					} else {
		//						$post_term_slug = '';
		//						$post_term_name = '';
		//					}
		//					if ( !empty( $newVals ) ) {
		//						$newTerms = get_term_by( 'id', $newVals[0], rtmedia_pro_attribute_taxonomy_name( $attr->attribute_name ) );
		//						$post_new_term_slug = $newVals[0];
		//						$post_new_term_name = $newTerms->name;
		//					} else {
		//						$post_new_term_slug = '';
		//						$post_new_term_name = '';
		//					}
		//					$diff = rtmedia_pro_text_diff( $post_term_name, $post_new_term_name );
		//					if ( $diff ) {
		//						$diffHTML .= '<tr><th style="padding: .5em;border: 0;">'.$attr->attribute_label.'</th><td>' . $diff . '</td><td></td></tr>';
		//					}
		//					break;
		//				case 'checklist':
		//					if ( !isset( $newLead[$attr->attribute_name] ) ) {
		//						$newLead[$attr->attribute_name] = array();
		//					}
		//					$newVals = $newLead[$attr->attribute_name];
		//					$newVals = array_unique( $newVals );
		//					$oldTermString = rtmedia_pro_post_term_to_string( $post_id, rtmedia_pro_attribute_taxonomy_name( $attr->attribute_name ) );
		//					$newTermString = '';
		//					if(!empty($newVals)) {
		//						$newTermArr = array();
		//						foreach ( $newVals as $value ) {
		//							$newTerm = get_term_by( 'id', $value, rtmedia_pro_attribute_taxonomy_name( $attr->attribute_name ) );
		//							$newTermArr[] = $newTerm->name;
		//						}
		//						$newTermString = implode(',', $newTermArr);
		//					}
		//					$diff = rtmedia_pro_text_diff( $oldTermString, $newTermString );
		//					if ( $diff ) {
		//						$diffHTML .= '<tr><th style="padding: .5em;border: 0;">'.$attr->attribute_label.'</th><td>' . $diff . '</td><td></td></tr>';
		//					}
		//					break;
		//				default:
		//					$diffHTML = apply_filters( 'rtmedia_pro_attribute_diff', $diffHTML, $attr, $post_id, $newLead );
		//					break;
		//			}
		//			return $diffHTML;
		//		}

		//		function meta_diff( $attr, $post_id, $newLead ) {
		//			$diffHTML = '';
		//
		//			$oldattr = get_post_meta( $post_id, $attr->attribute_name, true );
		//			if ( $oldattr != $newLead[$attr->attribute_name] ) {
		//				$diffHTML .= '<tr><th style="padding: .5em;border: 0;">'.$attr->attribute_label.'</th><td>' . rtcrm_text_diff( $oldattr, $newLead[$attr->attribute_name] ) . '</td><td></td></tr>';
		//			}
		//			update_post_meta($post_id, $attr->attribute_name, $newLead[$attr->attribute_name]);
		//			return $diffHTML;
		//		}

		function save_attributes( $attr, $post_id, $newLead ) {
			switch ( $attr->attribute_store_as ) {
				case 'taxonomy':
					if ( ! isset( $newLead[ $attr->attribute_name ] ) ){
						$newLead[ $attr->attribute_name ] = array();
					}
					wp_set_post_terms( $post_id, implode( ',', $newLead[ $attr->attribute_name ] ), rtmedia_pro_attribute_taxonomy_name( $attr->attribute_name ) );
					break;
				case 'meta':
					update_post_meta( $post_id, $attr->attribute_name, $newLead[ $attr->attribute_name ] );
					break;
				default:
					do_action( 'rtmedia_pro_update_attribute', $attr, $post_id, $newLead );
					break;
			}
		}

		function render_attribute( $attr, $post_id, $edit = true ) {
			switch ( $attr->attribute_store_as ) {
				case 'taxonomy':
					$this->render_taxonomy( $attr, $post_id, $edit );
					break;
				case 'meta':
					$this->render_meta( $attr, $post_id, $edit );
					break;
				default:
					do_action( 'rtmedia_pro_render_attribute', $attr, $post_id, $edit );
					break;
			}
		}

		function render_taxonomy( $attr, $post_id, $edit = true ) {
			switch ( $attr->attribute_render_type ) {
				//				case 'autocomplete':
				//					break;
				case 'dropdown':
					$options   = array();
					$terms     = get_terms( rtmedia_pro_attribute_taxonomy_name( $attr->attribute_name ), array( 'hide_empty' => false, 'orderby' => $attr->attribute_orderby, 'order' => 'asc' ) );
					$post_term = wp_get_post_terms( $post_id, rtmedia_pro_attribute_taxonomy_name( $attr->attribute_name ), array( 'fields' => 'ids' ) );
					// Default Selected Term for the attribute. can beset via settings -- later on
					$selected_term = '-11111';
					if ( ! empty( $post_term ) ){
						$selected_term = $post_term[ 0 ];
					}
					foreach ( $terms as $term ) {
						$options[ ] = array(
							$term->name => $term->term_id, 'selected' => ( $term->term_id == $selected_term ) ? true : false,
						);
					}
					if ( $edit ){
						$this->render_dropdown( $attr, $options );
					} else {
						$term = get_term( $selected_term, rtmedia_pro_attribute_taxonomy_name( $attr->attribute_name ) );
						echo '<span class="rtmedia_pro_view_mode">' . $term->name . '</span>';
					}
					break;
				case 'checklist':
					$options    = array();
					$terms      = get_terms( rtmedia_pro_attribute_taxonomy_name( $attr->attribute_name ), array( 'hide_empty' => false, 'orderby' => $attr->attribute_orderby, 'order' => 'asc' ) );
					$post_terms = wp_get_post_terms( $post_id, rtmedia_pro_attribute_taxonomy_name( $attr->attribute_name ), array( 'fields' => 'ids' ) );
					if ( empty( $post_terms ) ){
						$post_terms = array();
					}
					foreach ( $terms as $term ) {
						$options[ ] = array(
							$term->name => $term->term_id, 'checked' => ( in_array( $term->term_id, $post_terms ) ) ? true : false,
						);
					}
					if ( $edit ){
						$this->render_checklist( $attr, $options );
					} else {
						$selected_terms = array();
						foreach ( $terms as $term ) {
							if ( in_array( $term->term_id, $post_terms ) ){
								$selected_terms[ ] = $term->name;
							}
						}
						echo '<span class="rtmedia_pro_view_mode">' . implode( ',', $selected_terms ) . '</span>';
					}
					break;
				case 'rating-stars':
					$options   = array();
					$terms     = get_terms( rtmedia_pro_attribute_taxonomy_name( $attr->attribute_name ), array( 'hide_empty' => false, 'orderby' => $attr->attribute_orderby, 'order' => 'asc' ) );
					$post_term = wp_get_post_terms( $post_id, rtmedia_pro_attribute_taxonomy_name( $attr->attribute_name ), array( 'fields' => 'ids' ) );
					// Default Selected Term for the attribute. can beset via settings -- later on
					$selected_term = '-11111';
					if ( ! empty( $post_term ) ){
						$selected_term = $post_term[ 0 ];
					}
					foreach ( $terms as $term ) {
						$options[ ] = array(
							//							'' => $term->term_id,
							'title' => $term->name, 'checked' => ( $term->term_id == $selected_term ) ? true : false,
						);
					}
					if ( $edit ){
						$this->render_rating_stars( $attr, $options );
					} else {
						$term = get_term( $selected_term, rtmedia_pro_attribute_taxonomy_name( $attr->attribute_name ) );
						echo '<span class="rtmedia_pro_view_mode">' . $term->name . '</span>';
					}
					break;
				default:
					do_action( 'rtmedia_pro_render_taxonomy', $attr, $post_id, $edit );
					break;
			}
		}

		function render_meta( $attr, $post_id, $edit = true ) {
			switch ( $attr->attribute_render_type ) {
				case 'dropdown':
					$options   = array();
					$terms     = get_terms( rtmedia_pro_attribute_taxonomy_name( $attr->attribute_name ), array( 'hide_empty' => false, 'orderby' => $attr->attribute_orderby, 'order' => 'asc' ) );
					$post_term = wp_get_post_terms( $post_id, rtmedia_pro_attribute_taxonomy_name( $attr->attribute_name ), array( 'fields' => 'ids' ) );
					// Default Selected Term for the attribute. can beset via settings -- later on
					$selected_term = '-11111';
					if ( ! empty( $post_term ) ){
						$selected_term = $post_term[ 0 ];
					}
					foreach ( $terms as $term ) {
						$options[ ] = array(
							$term->name => $term->term_id, 'selected' => ( $term->term_id == $selected_term ) ? true : false,
						);
					} ?>
					<div class="large-4 small-4 columns <?php echo ( ! $edit ) ? 'rtmedia_pro_attr_border' : ''; ?>">
						<span class="prefix" title="<?php echo $attr->attribute_label; ?>"><label
								for="post[<?php echo $attr->attribute_name; ?>]"><?php echo $attr->attribute_label; ?></label></span>
					</div>
					<div class="large-8 mobile-large-2 columns">
						<?php if ( $edit ){
							$this->render_dropdown( $attr, $options );
						} else {
							$term = get_term( $selected_term, rtmedia_pro_attribute_taxonomy_name( $attr->attribute_name ) );
							echo '<span class="rtmedia_pro_view_mode">' . $term->name . '</span>';
						} ?>
					</div>
					<?php break;
				case 'rating-stars':
					$options   = array();
					$terms     = get_terms( rtmedia_pro_attribute_taxonomy_name( $attr->attribute_name ), array( 'hide_empty' => false, 'orderby' => $attr->attribute_orderby, 'order' => 'asc' ) );
					$post_term = wp_get_post_terms( $post_id, rtmedia_pro_attribute_taxonomy_name( $attr->attribute_name ), array( 'fields' => 'ids' ) );
					// Default Selected Term for the attribute. can beset via settings -- later on
					$selected_term = '-11111';
					if ( ! empty( $post_term ) ){
						$selected_term = $post_term[ 0 ];
					}
					foreach ( $terms as $term ) {
						$options[ ] = array(
							//							$term->name => $term->term_id,
							'' => $term->term_id, 'title' => $term->name, 'checked' => ( $term->term_id == $selected_term ) ? true : false,
						);
					} ?>
					<div class="large-4 small-4 columns">
						<span class="prefix" title="<?php echo $attr->attribute_label; ?>"><label
								for="post[<?php echo $attr->attribute_name; ?>]"><?php echo $attr->attribute_label; ?></label></span>
					</div>
					<div class="large-8 mobile-large-2 columns rtmedia_pro_attr_border">
						<?php if ( $edit ){
							$this->render_rating_stars( $attr, $options );
						} else {
							$term = get_term( $selected_term, rtmedia_pro_attribute_taxonomy_name( $attr->attribute_name ) );
							echo '<div class="rtmedia_pro_attr_border rtmedia_pro_view_mode">' . $term->name . '</div>';
						} ?>
					</div>
					<?php break;
				case 'date':
					$value = get_post_meta( $post_id, $attr->attribute_name, true ); ?>
					<div class="large-4 mobile-large-1 columns">
						<span class="prefix" title="<?php echo $attr->attribute_label; ?>"><label
								for="post[<?php echo $attr->attribute_name; ?>]"><?php echo $attr->attribute_label; ?></label></span>
					</div>
					<div
						class="large-7 mobile-large-2 columns <?php echo ( ! $edit ) ? 'rtmedia_pro_attr_border' : ''; ?>">
						<?php if ( $edit ){
							$this->render_date( $attr, $value );
						} else {
							echo '<span class="rtmedia_pro_view_mode moment-from-now">' . $value . '</span>';
						} ?>
					</div>
					<?php if ( $edit ){ ?>
					<div class="large-1 mobile-large-1 columns">
						<span class="postfix datepicker-toggle"
							  data-datepicker="<?php echo $attr->attribute_name; ?>"><label
								class="foundicon-calendar"></label></span>
					</div>
				<?php
				}
					break;
				case 'datetime':
					$value = get_post_meta( $post_id, $attr->attribute_name, true ); ?>
					<div class="large-4 mobile-large-1 columns">
						<span class="prefix" title="<?php echo $attr->attribute_label; ?>"><label
								for="post[<?php echo $attr->attribute_name; ?>]"><?php echo $attr->attribute_label; ?></label></span>
					</div>
					<div
						class="large-7 mobile-large-2 columns <?php echo ( ! $edit ) ? 'rtmedia_pro_attr_border' : ''; ?>">
						<?php if ( $edit ){
							$this->render_datetime( $attr, $value );
						} else {
							echo '<span class="rtmedia_pro_view_mode moment-from-now">' . $value . '</span>';
						} ?>
					</div>
					<?php if ( $edit ){ ?>
					<div class="large-1 mobile-large-1 columns">
						<span class="postfix datetimepicker-toggle"
							  data-datetimepicker="<?php echo $attr->attribute_name; ?>"><label
								class="foundicon-calendar"></label></span>
					</div>
				<?php
				}
					break;
				case 'currency':
					$value = get_post_meta( $post_id, $attr->attribute_name, true ); ?>
					<div class="large-4 mobile-large-1 columns">
						<span class="prefix" title="<?php echo $attr->attribute_label; ?>"><label
								for="post[<?php echo $attr->attribute_name; ?>]"><?php echo $attr->attribute_label; ?></label></span>
					</div>
					<div
						class="large-7 mobile-large-2 columns <?php echo ( ! $edit ) ? 'rtmedia_pro_attr_border' : ''; ?>">
						<?php if ( $edit ){
							$this->render_currency( $attr, $value );
						} else {
							echo '<span class="rtmedia_pro_view_mode">' . $value . '</span>';
						} ?>
					</div>
					<?php if ( $edit ){ ?>
					<div class="large-1 mobile-large-1 columns">
						<span class="postfix">$</span>
					</div>
				<?php
				}
					break;
				case 'text':
					$value = get_post_meta( $post_id, $attr->attribute_name, true ); ?>
					<div class="large-4 small-4 columns">
						<span class="prefix" title="<?php echo $attr->attribute_label; ?>"><label
								for="post[<?php echo $attr->attribute_name; ?>]"><?php echo $attr->attribute_label; ?></label></span>
					</div>
					<div
						class="large-8 mobile-large-2 columns <?php echo ( ! $edit ) ? 'rtmedia_pro_attr_border' : ''; ?>">
						<?php if ( $edit ){
							$this->render_text( $attr, $value );
						} else {
							echo '<span class="rtmedia_pro_view_mode">' . $value . '</span>';
						} ?>
					</div>
					<?php break;
				default:
					do_action( 'rtmedia_pro_render_meta', $attr, $post_id, $edit );
					break;
			}
		}

		function render_dropdown( $attr, $options ) {
			$rtForm = new rtForm();
			$args   = array(
				'id'             => $attr->attribute_name, 'name' => 'rtmedia_attr[' . $attr->attribute_name . '][]', //				'class' => array('scroll-height'),
				'rtForm_options' => $options,
			);

			return $rtForm->get_select( $args );
		}

		function render_rating_stars( $attr, $options ) {
			$rtForm = new rtForm();
			$args   = array(
				'id'                => $attr->attribute_name, 'name' => 'rtmedia_attr[' . $attr->attribute_name . '][]', 'class' => array( 'rtmedia-stars' ), 'misc' => array(
					'class' => 'star',
				), 'rtForm_options' => $options,
			);

			return $rtForm->get_radio( $args );
		}

		function render_checklist( $attr, $options ) {
			$rtForm = new rtForm();
			$args   = array(
				'id' => $attr->attribute_name, 'name' => 'rtmedia_attr[' . $attr->attribute_name . '][]', 'class' => array( 'scroll-height' ), 'rtForm_options' => $options,
			);

			return $rtForm->get_checkbox( $args );
		}

		function render_date( $attr, $value ) {
			$rtForm = new rtForm();
			$args   = array(
				'id'       => $attr->attribute_name, 'class' => array(
					'datepicker', 'moment-from-now',
				), 'misc'  => array(
					'placeholder' => 'Select ' . $attr->attribute_label, 'readonly' => 'readonly', 'title' => $value,
				), 'value' => $value,
			);

			return $rtForm->get_textbox( $args );
			$args = array(
				'name' => 'rtmedia_attr[' . $attr->attribute_name . ']', 'value' => $value,
			);

			return $rtForm->get_hidden( $args );
		}

		function render_datetime( $attr, $value ) {
			$rtForm = new rtForm();
			$args   = array(
				'id'       => $attr->attribute_name, 'class' => array(
					'datetimepicker', 'moment-from-now',
				), 'misc'  => array(
					'placeholder' => 'Select ' . $attr->attribute_label, 'readonly' => 'readonly', 'title' => $value,
				), 'value' => $value,
			);
			echo $rtForm->get_textbox( $args );
			$args = array(
				'name' => 'rtmedia_attr[' . $attr->attribute_name . ']', 'value' => $value,
			);
			echo $rtForm->get_hidden( $args );
		}

		function render_currency( $attr, $value ) {
			$rtForm = new rtForm();
			$args   = array(
				'name' => 'rtmedia_attr[' . $attr->attribute_name . ']', 'value' => $value,
			);
			echo $rtForm->get_textbox( $args );
		}

		function render_text( $attr, $value ) {
			$rtForm = new rtForm();
			$args   = array(
				'name' => 'post[' . $attr->attribute_name . ']', 'value' => $value,
			);
			echo $rtForm->get_textbox( $args );
		}
	}
}
