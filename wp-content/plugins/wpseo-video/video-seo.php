<?php
/*
Plugin Name: Video SEO for WordPress SEO by Yoast
Version: 1.6.3
Plugin URI: http://yoast.com/wordpress/video-seo/
Description: This Video SEO module adds all needed meta data and XML Video sitemap capabalities to the metadata capabilities of WordPress SEO to fully optimize your site for video results in the search results.
Author: Joost de Valk
Author URI: http://yoast.com

Copyright 2012-2014 Joost de Valk (email: support@yoast.com)

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*/

define( 'WPSEO_VIDEO_VERSION', '1.6.3' );

load_plugin_textdomain( 'yoast-video-seo', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
register_activation_hook( __FILE__, array( 'WPSEO_Option_Video', 'clean' ) );

/**
 * All functionality for fetching video data and creating an XML video sitemap with it.
 *
 * @link       http://codex.wordpress.org/oEmbed oEmbed Codex Article
 * @link       http://oembed.com/ oEmbed Homepage
 *
 * @package    WordPress SEO
 * @subpackage WordPress SEO Video
 */

/**
 * wpseo_video_Video_Sitemap class.
 *
 * @package WordPress SEO Video
 * @since   0.1
 */
class wpseo_Video_Sitemap {

	/**
	 * The maximum number of entries per sitemap page
	 */
	private $max_entries = 5;

	private $metabox_tab;

	protected $option_instance;

	/**
	 * Return the plugin file
	 *
	 * @return string
	 */
	public static function get_plugin_file() {
		return __FILE__;
	}

	/**
	 * Constructor for the wpseo_Video_Sitemap class.
	 *
	 * @todo  upgrade from license constant WPSEO_VIDEO_LICENSE
	 * @since 0.1
	 */
	function __construct() {

		// Initialize the options
		require_once( plugin_dir_path( __FILE__ ) . 'class-wpseo-option-video.php' );
		$this->option_instance = WPSEO_Option_Video::get_instance();

		$options = get_option( 'wpseo_video' );

		// Require Yoast Product, we need to do the require here because the upgrade() also requires the Yoast Product
		require_once( plugin_dir_path( __FILE__ ) . 'product-wpseo-video.php' );

		// run upgrade routine
		$this->upgrade();

		add_filter( 'wpseo_tax_meta_special_term_id_validation__video', array( $this, 'validate_video_tax_meta' ) );


		if ( ! isset( $GLOBALS['content_width'] ) && $options['content_width'] > 0 ) {
			$GLOBALS['content_width'] = $options['content_width'];
		}

		add_action( 'setup_theme', array( $this, 'init' ) );
		add_action( 'admin_init', array( $this, 'init' ) );

		if ( is_admin() ) {

			add_action( 'admin_menu', array( $this, 'register_settings_page' ) );

			add_filter( 'wpseo_admin_pages', array( $this, 'style_admin' ) );

			add_action( 'save_post', array( $this, 'update_video_post_meta' ) );

			if ( in_array( $GLOBALS['pagenow'], array( 'edit.php', 'post.php', 'post-new.php' ) ) ) {
				include_once( plugin_dir_path( __FILE__ ) . 'class-wpseo-video-metabox.php' );
				$this->metabox_tab = new WPSEO_Video_Metabox();
			}

			// Licensing part
			$license_manager = new Yoast_Plugin_License_Manager( new Yoast_Product_WPSEO_Video() );

			// Setup constant name
			$license_manager->set_license_constant_name( 'WPSEO_VIDEO_LICENSE' );

			// Setup hooks
			$license_manager->setup_hooks();

			// Add form
			add_action( 'wpseo_licenses_forms', array( $license_manager, 'show_license_form' ) );

		} else {

			// OpenGraph
			add_action( 'wpseo_opengraph', array( $this, 'opengraph' ) );
			add_filter( 'wpseo_opengraph_type', array( $this, 'opengraph_type' ), 10, 1 );
			add_filter( 'wpseo_opengraph_image', array( $this, 'opengraph_image' ), 5, 1 );

			// XML Sitemap Index addition
			add_filter( 'wpseo_sitemap_index', array( $this, 'add_to_index' ) );

			// Content filter for non-detected video's
			add_filter( 'the_content', array( $this, 'content_filter' ), 5, 1 );

			if ( $options['fitvids'] === true ) {
				// Fitvids scripting
				add_action( 'wp_head', array( $this, 'fitvids' ) );
			}

			if ( $options['disable_rss'] !== true ) {
				// MRSS
				add_action( 'rss2_ns', array( $this, 'mrss_namespace' ) );
				add_action( 'rss2_item', array( $this, 'mrss_item' ), 10, 1 );
				add_filter( 'mrss_media', array( $this, 'mrss_add_video' ) );
			}

		}
	}

	/**
	 * Adds the fitvids JavaScript to the output if there's a video on the page that's supported by this script.
	 *
	 * @since 1.5.4
	 */
	function fitvids() {
		if ( ! is_singular() ) {
			return;
		}

		global $post;

		$video = WPSEO_Meta::get_value( 'video_meta', $post->ID );

		if ( ! is_array( $video ) || $video === array() ) {
			return;
		}

		// Check if the current post contains a YouTube, Vimeo, Blip.tv or Viddler video, if it does, add the fitvids code.
		if ( in_array( $video['type'], array( 'youtube', 'vimeo', 'blip.tv', 'viddler', 'wistia' ) ) ) {
			if ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) {
				wp_enqueue_script( 'fitvids', plugin_dir_url( __FILE__ ) . 'js/jquery.fitvids.js', array( 'jquery' ) );
			} else {
				wp_enqueue_script( 'fitvids', plugin_dir_url( __FILE__ ) . 'js/jquery.fitvids.min.js', array( 'jquery' ) );
			}
		}

		add_action( 'wp_footer', array( $this, 'fitvids_footer' ) );
	}

	/**
	 * The fitvids instantiation code.
	 *
	 * @since 1.5.4
	 */
	function fitvids_footer() {
		global $post;

		// Try and use the post class to determine the container
		$classes = get_post_class( '', $post->ID );
		$class   = "post";
		if ( is_array( $classes ) ) {
			$class = $classes[0];
		}
		?>
		<script type="text/javascript">
			jQuery(document).ready(function ($) {
				$(".<?php echo $class; ?>").fitVids({ customSelector: "iframe.wistia_embed"});
			});
		</script>
	<?php
	}

	/**
	 * Make sure the Video SEO plugin receives Yoast admin styling
	 *
	 * @param array $adminpages The array of pages that have Yoast admin styling
	 *
	 * @return array $adminpages
	 */
	function style_admin( $adminpages ) {
		array_push( $adminpages, 'wpseo_video' );

		return $adminpages;
	}

	/**
	 * Register the Video SEO submenu.
	 */
	function register_settings_page() {
		add_submenu_page( 'wpseo_dashboard', __( 'Video SEO', 'yoast-video-seo' ), __( 'Video SEO', 'yoast-video-seo' ), 'manage_options', 'wpseo_video', array( $this, 'admin_panel' ) );
	}

	/**
	 * Adds the rewrite for the video XML sitemap
	 *
	 * @since 0.1
	 */
	function init() {
		// Get options to set the entries per page
		$options           = WPSEO_Options::get_all();
		$this->max_entries = $options['entries-per-page'];

		// Add oEmbed providers
		$this->add_oembed();

		// Register the sitemap
		if ( isset( $GLOBALS['wpseo_sitemaps'] ) ) {
			$GLOBALS['wpseo_sitemaps']->register_sitemap( $this->video_sitemap_basename(), array( $this, 'build_video_sitemap' ) );
			if ( method_exists( $GLOBALS['wpseo_sitemaps'], 'register_xsl' ) ) {
				$GLOBALS['wpseo_sitemaps']->register_xsl( 'video', array( $this, 'build_video_sitemap_xsl' ) );
			}
		}
	}

	/**
	 * Execute upgrade actions when needed
	 */
	function upgrade() {

		$options = get_option( 'wpseo_video' );

		$license_manager = new Yoast_Plugin_License_Manager( new Yoast_Product_WPSEO_Video() );

		// upgrade to license manager
		if ( $license_manager->get_license_key() === '' ) {

			if ( isset( $options['yoast-video-seo-license'] ) ) {
				$license_manager->set_license_key( $options['yoast-video-seo-license'] );
				unset( $options['yoast-video-seo-license'] );
			}

			if ( isset( $options['yoast-video-seo-license-status'] ) ) {
				$license_manager->set_license_status( $options['yoast-video-seo-license-status'] );
				unset( $options['yoast-video-seo-license-status'] );
			}
		}

		// upgrade to new option & meta classes
		if ( ! isset( $options['dbversion'] ) || version_compare( $options['dbversion'], '1.6', '<' ) ) {
			$this->option_instance->clean();
			WPSEO_Meta::clean_up(); // Make sure our meta values are cleaned up even if WP SEO would have been upgraded already
		}

	}

	/**
	 * Returns the basename of the video-sitemap, the first portion of the name of the sitemap "file".
	 *
	 * Defaults to video, but it's possible to override it by using the YOAST_VIDEO_SITEMAP_BASENAME constant.
	 *
	 * @since 1.5.3
	 *
	 * @return string $basename
	 */
	function video_sitemap_basename() {
		$basename = 'video';

		if ( post_type_exists( 'video' ) ) {
			$basename = 'yoast-video';
		}

		if ( defined( 'YOAST_VIDEO_SITEMAP_BASENAME' ) ) {
			$basename = YOAST_VIDEO_SITEMAP_BASENAME;
		}

		return $basename;
	}

	/**
	 * Return the Video Sitemap URL
	 *
	 * @since 1.2.1
	 *
	 * @return string The URL to the video Sitemap.
	 */
	function sitemap_url() {
		$base = $GLOBALS['wp_rewrite']->using_index_permalinks() ? 'index.php/' : '';

		return home_url( $base . $this->video_sitemap_basename() . '-sitemap.xml' );
	}

	/**
	 * Adds the video XML sitemap to the Index Sitemap.
	 *
	 * @since  0.1
	 *
	 * @param string $str String with the filtered additions to the index sitemap in it.
	 *
	 * @return string $str String with the Video XML sitemap additions to the index sitemap in it.
	 */
	function add_to_index( $str ) {
		$options = get_option( 'wpseo_video' );

		$base = $GLOBALS['wp_rewrite']->using_index_permalinks() ? 'index.php/' : '';

		if ( is_array( $options['videositemap_posttypes'] ) && $options['videositemap_posttypes'] !== array() ) {
			// Use fields => ids to limit the overhead of fetching entire post objects,
			// fetch only an array of ids instead to count
			$args = array(
					'post_type'      => $options['videositemap_posttypes'],
					'post_status'    => 'publish',
					'posts_per_page' => - 1,
					'meta_key'       => '_yoast_wpseo_video_meta',
					'meta_compare'   => '!=',
					'meta_value'     => 'none',
					'fields'         => 'ids'
			);
			// Copy these args to be used and modify later
			$date_args = $args;

			$video_ids = get_posts( $args );
			$count     = count( $video_ids );

			$n = ( $count > $this->max_entries ) ? (int) ceil( $count / $this->max_entries ) : 1;
			for ( $i = 0; $i < $n; $i ++ ) {
				$count = ( $n > 1 ) ? $i + 1 : '';

				if ( empty( $count ) || $count == $n ) {
					$date_args['fields']         = 'all';
					$date_args['posts_per_page'] = 1;
					$date_args['offset']         = 0;
					$date_args['order']          = 'DESC';
					$date_args['orderby']        = 'modified';
				} else {
					$date_args['fields']         = 'all';
					$date_args['posts_per_page'] = 1;
					$date_args['offset']         = $this->max_entries * ( $i + 1 ) - 1;
					$date_args['order']          = 'ASC';
					$date_args['orderby']        = 'modified';
				}
				$posts = get_posts( $date_args );
				$date  = date( 'c', strtotime( $posts[0]->post_modified_gmt ) );

				$text = ( $count > 1 ) ? $count : '';
				$str .= '<sitemap>' . "\n";
				$str .= '<loc>' . home_url( $base . $this->video_sitemap_basename() . '-sitemap' . $text . '.xml' ) . '</loc>' . "\n";
				$str .= '<lastmod>' . $date . '</lastmod>' . "\n";
				$str .= '</sitemap>' . "\n";
			}

		}

		return $str;
	}

	/**
	 * Adds oembed endpoints for supported video platforms that are not supported by core.
	 *
	 * @since 1.3.5
	 */
	function add_oembed() {
		// Wistia
		wp_oembed_add_provider( '`https?:\/\/(.+)?(wistia\.com|wi\.st)\/(medias|embed)\/.*`', 'http://fast.wistia.com/oembed', true );

		// Animoto
		wp_oembed_add_provider( '`(http:\/\/animoto\.com\/play\/*)`', 'http://animoto.com/services/oembed?format=json', true );

		// Screenr
		wp_oembed_add_provider( '`http:\/\/www\.screenr\.com\/*`', 'http://www.screenr.com/api/oembed.{format}', false );

		// EVS
		$evs_location = get_option( 'evs_location' );
		if ( $evs_location && ! empty( $evs_location ) ) {
			wp_oembed_add_provider( $evs_location . '/*', $evs_location . '/oembed.php', false );
		}
	}


	/**
	 * Add the MRSS namespace to the RSS feed.
	 *
	 * @since 0.1
	 */
	function mrss_namespace() {
		echo ' xmlns:media="http://search.yahoo.com/mrss/" ';
	}

	/**
	 * Add the MRSS info to the feed
	 *
	 * Based upon the MRSS plugin developed by Andy Skelton
	 *
	 * @since     0.1
	 * @copyright Andy Skelton
	 */
	function mrss_item() {
		global $mrss_gallery_lookup;
		$media = array();

		// Honor the feed settings. Don't include any media that isn't in the feed.
		if ( get_option( 'rss_use_excerpt' ) || ! strlen( get_the_content() ) ) {
			$content = the_excerpt_rss();
		} else {
			// If any galleries are processed, we need to capture the attachment IDs.
			add_filter( 'wp_get_attachment_link', array( $this, 'mrss_gallery_lookup' ), 10, 5 );
			$content = apply_filters( 'the_content', get_the_content() );
			remove_filter( 'wp_get_attachment_link', array( $this, 'mrss_gallery_lookup' ), 10, 5 );
			$lookup = $mrss_gallery_lookup;
			unset( $mrss_gallery_lookup );
		}

		// img tags
		$images = 0;
		if ( preg_match_all( '|<img ([^>]+)|', $content, $matches ) ) {
			foreach ( $matches[1] as $attrs ) {
				$item = $img = array();
				// Construct $img array from <img> attributes
				foreach ( wp_kses_hair( $attrs, array( 'http' ) ) as $attr ) {
					$img[$attr['name']] = $attr['value'];
				}
				if ( ! isset( $img['src'] ) ) {
					continue;
				}
				$img['src'] = $this->mrss_url( $img['src'] );
				// Skip emoticons
				if ( isset( $img['class'] ) && false !== strpos( $img['class'], 'wp-smiley' ) ) {
					continue;
				}
				$id = false;
				if ( isset( $lookup[$img['src']] ) ) {
					$id = $lookup[$img['src']];
				} elseif ( isset( $img['class'] ) && preg_match( '/wp-image-(\d+)/', $img['class'], $match ) ) {
					$id = $match[1];
				}
				if ( $id ) {
					// It's an attachment, so we will get the URLs, title, and description from functions
					$attachment =& get_post( $id );
					$src        = wp_get_attachment_image_src( $id, 'full' );
					if ( ! empty( $src[0] ) ) {
						$img['src'] = $src[0];
					}
					$thumbnail = wp_get_attachment_image_src( $id, 'thumbnail' );
					if ( ! empty( $thumbnail[0] ) && $thumbnail[0] != $img['src'] ) {
						$img['thumbnail'] = $thumbnail[0];
					}
					$title = get_the_title( $id );
					if ( ! empty( $title ) ) {
						$img['title'] = trim( $title );
					}
					if ( ! empty( $attachment->post_excerpt ) ) {
						$img['description'] = trim( $attachment->post_excerpt );
					}
				}
				// If this is the first image in the markup, make it the post thumbnail
				if ( ++$images == 1 ) {
					if ( isset( $img['thumbnail'] ) ) {
						$media[]['thumbnail']['attr']['url'] = $img['thumbnail'];
					} else {
						$media[]['thumbnail']['attr']['url'] = $img['src'];
					}
				}

				$item['content']['attr']['url']    = $img['src'];
				$item['content']['attr']['medium'] = 'image';
				if ( ! empty( $img['title'] ) ) {
					$item['content']['children']['title']['attr']['type'] = 'html';
					$item['content']['children']['title']['children'][]   = $img['title'];
				} elseif ( ! empty( $img['alt'] ) ) {
					$item['content']['children']['title']['attr']['type'] = 'html';
					$item['content']['children']['title']['children'][]   = $img['alt'];
				}
				if ( ! empty( $img['description'] ) ) {
					$item['content']['children']['description']['attr']['type'] = 'html';
					$item['content']['children']['description']['children'][]   = $img['description'];
				}
				if ( ! empty( $img['thumbnail'] ) ) {
					$item['content']['children']['thumbnail']['attr']['url'] = $img['thumbnail'];
				}
				$media[] = $item;
			}
		}

		$media = apply_filters( 'mrss_media', $media );
		$this->mrss_print( $media );
	}

	function mrss_url( $url ) {
		if ( preg_match( '!^https?://!', $url ) ) {
			return $url;
		}
		if ( $url{0} == '/' ) {
			return rtrim( home_url(), '/' ) . $url;
		}

		return home_url() . $url;
	}

	function mrss_gallery_lookup( $link, $id ) {
		global $mrss_gallery_lookup;
		preg_match( '/ src="(.*?)"/', $link, $matches );
		$mrss_gallery_lookup[$matches[1]] = $id;

		return $link;
	}

	function mrss_print( $media ) {
		if ( ! empty( $media ) ) {
			foreach ( (array) $media as $element ) {
				$this->mrss_print_element( $element );
			}
		}
		echo "\n";
	}

	function mrss_print_element( $element, $indent = 2 ) {
		echo "\n";
		foreach ( (array) $element as $name => $data ) {
			echo str_repeat( "\t", $indent ) . "<media:$name";

			if ( ! empty( $data['attr'] ) ) {
				foreach ( $data['attr'] as $attr => $value ) {
					echo " $attr=\"" . esc_attr( ent2ncr( $value ) ) . "\"";
				}
			}
			if ( ! empty( $data['children'] ) ) {
				$nl = false;
				echo ">";
				foreach ( $data['children'] as $_name => $_data ) {
					if ( is_int( $_name ) ) {
						echo ent2ncr( esc_html( $_data ) );
					} else {
						$nl = true;
						$this->mrss_print_element( array( $_name => $_data ), $indent + 1 );
					}
				}
				if ( $nl ) {
					echo "\n" . str_repeat( "\t", $indent );
				}
				echo "</media:$name>";
			} else {
				echo " />";
			}
		}
	}

	/**
	 * Add the video output to the MRSS feed.
	 *
	 * @since 0.1
	 */
	function mrss_add_video( $media ) {
		global $post;

		$video = WPSEO_Meta::get_value( 'video_meta', $post->ID );

		if ( ! is_array( $video ) || $video === array() ) {
			return $media;
		}

		$video_duration = WPSEO_Meta::get_value( 'videositemap-duration', $post->ID );
		if ( $video_duration == 0 && isset( $video['duration'] ) ) {
			$video_duration = $video['duration'];
		}

		$item['content']['attr']['url']                             = $video['player_loc'];
		$item['content']['attr']['duration']                        = $video_duration;
		$item['content']['children']['player']['attr']['url']       = $video['player_loc'];
		$item['content']['children']['title']['attr']['type']       = 'html';
		$item['content']['children']['title']['children'][]         = esc_html( $video['title'] );
		$item['content']['children']['description']['attr']['type'] = 'html';
		$item['content']['children']['description']['children'][]   = esc_html( $video['description'] );
		$item['content']['children']['thumbnail']['attr']['url']    = $video['thumbnail_loc'];
		$item['content']['children']['keywords']['children'][]      = implode( ',', $video['tag'] );
		array_unshift( $media, $item );

		return $media;
	}

	/**
	 * Downloads an externally hosted thumbnail image to the local server
	 *
	 * @since 0.1
	 *
	 * @param string $url The remote URL of the image.
	 * @param string $vid Array with the video data.
	 * @param string $ext Extension to use for the image, optional.
	 *
	 * @return bool|string $img[0] The link to the now locally hosted image.
	 */
	function make_image_local( $url, $vid, $ext = '' ) {

		// Remove query parameters from the URL
		$url = strtok( $url, '?' );

		if ( isset( $vid['post_id'] ) ) {
			$att = get_posts( array(
					'numberposts' => 1,
					'post_type'   => 'attachment',
					'meta_key'    => 'wpseo_video_id',
					'meta_value'  => isset( $vid['id'] ) ? $vid['id'] : '',
					'post_parent' => $vid['post_id'],
					'fields'      => 'ids'
			) );

			if ( count( $att ) > 0 ) {
				$img = wp_get_attachment_image_src( $att[0], 'full' );

				if ( $img ) {
					if ( strpos( $img[0], 'http' ) !== 0 ) {
						return get_site_url( null, $img[0] );
					} else {
						return $img[0];
					}
				}
			}

		}

		// Disable wp smush.it to speed up the process
		remove_filter( 'wp_generate_attachment_metadata', 'wp_smushit_resize_from_meta_data' );

		$tmp = download_url( $url );

		if ( is_wp_error( $tmp ) ) {
			return false;
		} else {
			preg_match( '/[^\?]+\.(jpg|jpe|jpeg|gif|png)/i', $url, $matches );

			if ( isset( $matches[1] ) ) {
				$ext = $matches[1];
			}

			if ( ! isset( $vid['title'] ) || empty( $vid['title'] ) ) {
				$vid['title'] = get_the_title( $vid['post_id'] );
			}
			$title = sanitize_title( strtolower( $vid['title'] ) );

			$file_array = array(
					'name'     => sanitize_file_name( preg_replace( '/[^a-z0-9\s\-_]/i', '', $title ) ) . '.' . $ext,
					'tmp_name' => $tmp
			);

			if ( isset( $vid['post_id'] ) && ! defined( 'WPSEO_VIDEO_NO_ATTACHMENTS' ) ) {

				$ret = media_handle_sideload( $file_array, $vid['post_id'], 'Video thumbnail for ' . $vid['type'] . ' video ' . $vid['title'] );

				if ( isset( $vid['id'] ) ) {
					update_post_meta( $ret, 'wpseo_video_id', $vid['id'] );
				}

				$img = wp_get_attachment_image_src( $ret, 'full' );

				if ( $img ) {
					// Try and prevent relative paths to images
					if ( strpos( $img[0], 'http' ) !== 0 ) {
						$img = get_site_url( null, $img[0] );
					} else {
						$img = $img[0];
					}

					return $img;
				}

			} else {
				$file = wp_handle_sideload( $file_array, array( 'test_form' => false ) );

				if ( ! isset( $file['error'] ) ) {
					return $file['url'];
				}
			}

			return false;
		}
	}

	/**
	 * Checks whether there are oembed URLs in the post that should be included in the video sitemap.
	 *
	 * @since 0.1
	 *
	 * @param string $content the content of the post.
	 *
	 * @return array|boolean returns array $urls with type of video as array key and video URL as content, or false on negative
	 */
	function grab_embeddable_urls( $content ) {
		global $echo;

		$options      = get_option( 'wpseo_video' );
		$evs_location = get_option( 'evs_location' );

		// For compatibility with Youtube Lyte and Smart Youtube Pro
		$content = str_replace( array( 'httpv://', 'httpvh://', 'httpvp://', 'httpa://' ), 'http://', $content );

		// Catch both the single line embeds as well as the embeds using the [embed] shortcode.
		preg_match_all( '|\[embed([^\]]+)?\](https?://[^\s"]+)\[/embed\]|im', $content, $matches );
		preg_match_all( '/^\s*(<p>)?(https?:\/\/[^\s"]+)\s*$/im', $content, $matches2 );

		$matched_urls = array_merge( $matches[2], $matches2[2] );

		if ( preg_match_all( '|(<iframe.*</iframe>)|', $content, $iframes, PREG_SET_ORDER ) ) {
			foreach ( $iframes as $iframe ) {
				if ( preg_match( '/id=(\'|")vzvd-(\d+)\1/', $iframe[1], $iframesrc ) ) {
					if ( $options['vzaar_domain'] !== '' ) {
						$matched_urls[] = 'http://' . $options['vzaar_domain'] . '/' . $iframesrc[2] . '/video';
					} else {
						$matched_urls[] = 'http://view.vzaar.com/' . $iframesrc[2] . '/video';
					}
				} else {
					if ( preg_match( '/src=(\'|")(.*?)\1/', $iframe[1], $iframesrc ) ) {
						$matched_urls[] = $iframesrc[2];
					}
				}
			}
		}

		if ( preg_match_all( '|(<object.*</object>)|', $content, $objects, PREG_SET_ORDER ) ) {
			foreach ( $objects as $object ) {
				if ( preg_match( '/<param name=(\'|")src\1 value=(\'|")(.*?)\1/', $object[1], $srcmatch ) ) {
					$matched_urls[] = $srcmatch[3];
				} else {
					if ( preg_match( '/<param name=(\'|")movie\1 value=(\'|")(.*?)\1/', $object[1], $moviematch ) ) {
						$matched_urls[] = $moviematch[3];
					}
				}
			}
		}

		if ( preg_match( '/<a href=(\'|")(https?:\/\/(www\.)?(youtube|vimeo)\.com\/.*?)\1 rel=(\'|")wp-video-lightbox\1/', $content, $matches ) ) {
			$matched_urls[] = $matches[2];
		}

		if ( preg_match( '/<a class="youtubepop" href=(\'|")(https?:\/\/(www\.)?(youtube|vimeo)\.com\/.*?)\1>/', $content, $matches ) ) {
			$matched_urls[] = $matches[2];
		}

		if ( preg_match( '/<a href=(\'|")(.*?)\1 .*?data-titan-lightbox=(\'|")on\3.*?>/', $content, $matches ) ) {
			$matched_urls[] = $matches[2];
		}

		if ( $options['vzaar_domain'] !== '' ) {
			$vzaar_info = $this->parse_url( $options['vzaar_domain'] );
		}
		if ( ! isset( $vzaar_info['domain'] ) ) {
			$vzaar_info = array( 'domain' => 'vzaar.com' );
		}

		$evs_info = $this->parse_url( $evs_location );
		if ( ! isset( $evs_info ) || ! isset( $evs_info['domain'] ) ) {
			$evs_info = array( 'domain' => 'easyvideosuite.com' );
		}

		if ( count( $matched_urls ) > 0 ) {
			$urls = array();

			foreach ( $matched_urls as $match ) {
				if ( substr( $match, 0, 4 ) != 'http' ) {
					$match = 'http:' . $match;
				}
				if ( $echo && WP_DEBUG ) {
					echo $match . '<br/>';
				}

				$url_info = $this->parse_url( $match );
				if ( ! isset( $url_info['domain'] ) ) {
					continue;
				}
				if ( $echo && WP_DEBUG ) {
					echo $url_info['domain'] . '<br/>';
				}
				switch ( $url_info['domain'] ) {
					case 'animoto.com':
						$urls['animoto'] = $match;
						break;
					case 'blip.tv':
						$urls['blip'] = $match;
						break;
					case 'brightcove.com':
						if ( preg_match( '#<param name="flashVars" value="playerID=(\d+)#', $content, $bcmatch ) ) {
							$urls['brightcove'] = $bcmatch[1];
						}

						if ( preg_match( '#<param name="flashVars" value="videoId=(\d+)#', $content, $bcmatch ) ) {
							$urls['brightcove'] = $bcmatch[1];
						}
						break;
					case 'dailymotion.com':
						$urls['dailymotion'] = $match;
						break;
					case 'flickr.com':
						$urls['flickr'] = $match;
						break;
					case 'muzu.tv':
						$urls['muzutv'] = $match;
						break;
					case 'screenr.com':
						$urls['screenr'] = $match;
						break;
					case 'viddler.com':
						$urls['viddler'] = $match;
						break;
					case 'vimeo.com':
						$urls['vimeo'] = $match;
						break;
					case 'vzaar.com':
						$urls['vzaar'] = $match;
						break;
					case $vzaar_info['domain']:
						$urls['vzaar'] = $match;
						break;
					case 'wistia.com':
					case 'wistia.net':
						$urls['wistia'] = $match;
						break;
					case 'wordpress.tv':
						$urls['wordpress.tv'] = $match;
						break;
					case 'youtu.be':
						$urls['youtube'] = $match;
						break;
					case 'youtube.com':
						$urls['youtube'] = $match;
						break;
					case 'youtube-nocookie.com':
						$urls['youtube'] = $match;
						break;
					case $evs_info['domain']:
						$urls['evs'] = $match;
				}
			}

			if ( count( $urls ) > 0 ) {
				return $urls;
			} else {
				return false;
			}
		} else {
			return false;
		}
	}

	/**
	 * Checks whether there are oembed URLs in the post that should be included in the video sitemap.
	 * Uses DOMDocument and XPath to parse the content for url instead of preg matches
	 *
	 * @since 1.5.4.4
	 *
	 * @param string $content the content of the post.
	 *
	 * @return array|boolean returns array $urls with type of video as array key and video URL as content, or false on negative
	 */
	function grab_embeddable_urls_xpath( $content ) {
		global $echo;

		if ( ! is_string( $content ) || trim( $content ) === '' ) {
			return false;
		}

		$options = get_option( 'wpseo_video' );

		// For compatibility with Youtube Lyte and Smart Youtube Pro
		$content = str_replace( array( 'httpv://', 'httpvh://', 'httpvp://', 'httpa://' ), 'http://', $content );

		$dom = new DOMDocument();
		@$dom->loadHTML( $content );
		$xpath = new DOMXPath( $dom );

		$matched_urls = array();

		// for object embeds (i.e screencast.com)
		$objects = $xpath->query( '//object/param[@name="movie"] | //object/param[@name="src"]' );
		foreach ( $objects as $object ) {
			$value          = $object->getAttribute( 'value' );
			$matched_urls[] = $value;
		}

		// for iframe embeds (i.e. vidyard.com)
		$iframes = $xpath->query( '//iframe' );
		foreach ( $iframes as $iframe ) {
			$src            = $iframe->getAttribute( 'src' );
			$matched_urls[] = $src;
		}

		// additional check for vidyard embed with javascript and lightbox
		$script = $xpath->query( '//script[contains(@src,"play.vidyard.com")]' );
		foreach ( $script as $element ) {
			$src            = $element->getAttribute( 'src' );
			$matched_urls[] = $src;
		}

		// additional check for cincopa embed via javascript
		$script = $xpath->query( '//script/text()[contains(.,"cp_load_widget")]' );
		foreach ( $script as $element ) {
			// Remove CDATA
			$src            = preg_replace( '~//\s*?<!\[CDATA\[\s*|\s*//\s*\]\]>~', '', $element->wholeText );
			$src            = 'http://cincopa.com?' . $src;
			$matched_urls[] = $src;
		}

		// additional check for brightcove
		$script = $xpath->query( '//object/param[contains(@value,"brightcove.com")]/following-sibling::param[@name="flashVars"]' );
		foreach ( $script as $element ) {
			$src            = $element->getAttribute( 'value' );
			$src            = 'http://brightcove.com?' . $src;
			$matched_urls[] = $src;
		}

		// foreach ( $iframes as $iframe ) {
		// if ( preg_match( '/id=(\'|")vzvd-(\d+)\1/', $iframe[1], $iframesrc ) ) {
		// if ( $options['vzaar_domain'] !== '' )
		// $matched_urls[] = 'http://' . $options['vzaar_domain'] . '/' . $iframesrc[2] . '/video';
		// else
		// $matched_urls[] = 'http://view.vzaar.com/' . $iframesrc[2] . '/video';
		// } else if ( preg_match( '/src=(\'|")(.*?)\1/', $iframe[1], $iframesrc ) )
		// $matched_urls[] = $iframesrc[2];
		// }

		if ( count( $matched_urls ) > 0 ) {
			$urls = array();

			foreach ( $matched_urls as $match ) {
				if ( substr( $match, 0, 4 ) != 'http' ) {
					$match = 'http:' . $match;
				}
				if ( $echo && WP_DEBUG ) {
					echo $match . '<br/>';
				}

				$url_info = $this->parse_url( $match );
				if ( ! isset( $url_info['domain'] ) ) {
					continue;
				}
				if ( $echo && WP_DEBUG ) {
					echo $url_info['domain'] . '<br/>';
				}
				switch ( $url_info['domain'] ) {
					case 'animoto.com':
						$urls['animoto'] = $match;
						break;
					case 'brightcove.com':
						$urls['brightcove'] = $match;
						break;
					case 'cincopa.com':
						$urls['cincopa'] = $match;
						break;
					case 'screenr.com':
						$urls['screenr'] = $match;
						break;
					// work around for screencast.com b/c there's no connection between url and the embed code
					case 'screencast.com':
						$urls['screencast']['url']   = $match;
						$urls['screencast']['embed'] = $content;
						break;
					case 'vidyard.com':
						$urls['vidyard'] = $match;
						break;

				}
			}

			if ( count( $urls ) > 0 ) {
				return $urls;
			} else {
				return false;
			}
		} else {
			return false;
		}

		return $urls;
	}


	/**
	 * Parse a URL and find the host name and more.
	 *
	 * @since 1.1
	 *
	 * @link  http://php.net/manual/en/function.parse-url.php#83875
	 *
	 * @param string $url The URL to parse
	 *
	 * @return array
	 */
	function parse_url( $url ) {
		$r = "^(?:(?P<scheme>\w+)://)?";
		$r .= "(?:(?P<login>\w+):(?P<pass>\w+)@)?";
		$r .= "(?P<host>(?:(?P<subdomain>[-\w\.]+)\.)?" . "(?P<domain>[-\w]+\.(?P<extension>\w+)))";
		$r .= "(?::(?P<port>\d+))?";
		$r .= "(?P<path>[\w/]*/(?P<file>\w+(?:\.\w+)?)?)?";
		$r .= "(?:\?(?P<arg>[\w=&]+))?";
		$r .= "(?:#(?P<anchor>\w+))?";
		$r = "!$r!"; // Delimiters

		preg_match( $r, $url, $out );

		return $out;
	}

	/**
	 * Wrapper for the WordPress internal wp_remote_get function, making sure a proper user-agent is sent along.
	 *
	 * @since 0.1
	 *
	 * @param string $url     The URL to retrieve.
	 * @param array  $headers Optional headers to send.
	 *
	 * @return array|boolean $body Returns the body of the post when successfull, false when unsuccessfull.
	 */
	function remote_get( $url, $headers = array() ) {
		$response = wp_remote_get( $url,
				array(
						'redirection' => 1,
						'httpversion' => '1.1',
						'user-agent'  => 'WordPress Video SEO plugin ' . WPSEO_VERSION . '; WordPress (' . home_url( '/' ) . ')',
						'timeout'     => 15,
						'headers'     => $headers
				)
		);

		if ( ! is_wp_error( $response ) && $response['response']['code'] == 200 ) {
			return $response['body'];
		} else {
			return false;
		}
	}

	/**
	 * Use the "new" post data with the old video data, to prevent the need for an external video API call when the video hasn't changed.
	 *
	 * @since 0.1
	 *
	 * @param array  $vid    The "new" video array
	 * @param array  $oldvid The old video array
	 * @param string $thumb  Possibly the thumbnail, if set manually.
	 *
	 * @return array $vid With the new values from $vid and the old values from $oldvid combined.
	 */
	function use_old_video_data( $vid, $oldvid, $thumb ) {
		$oldvid['title']            = $vid['title'];
		$oldvid['description']      = $vid['description'];
		$oldvid['publication_date'] = $vid['publication_date'];
		if ( isset( $vid['category'] ) ) {
			$oldvid['category'] = $vid['category'];
		}
		if ( isset( $vid['tag'] ) ) {
			$oldvid['tag'] = $vid['tag'];
		}

		if ( $thumb != '' ) {
			$oldvid['thumbnail_loc'] = $thumb;
		}

		return $oldvid;
	}

	/**
	 * Retrieve video details from Brightcove
	 *
	 * @param array  $vid    The video array with all the data.
	 * @param array  $oldvid The video array with all the data of the previous "fetch" from Brightcove, if available.
	 * @param string $thumb  The URL to the manually set thumbnail, if available.
	 *
	 * @return array|boolean $vid Returns a filled video array when successfull, false when unsuccessful.
	 */
	function brightcove_details( $vid, $oldvid = array(), $thumb = '' ) {
		if ( ( isset( $oldvid['url'] ) && $oldvid['url'] == $vid['url'] ) || ( isset( $oldvid['id'] ) && isset( $vid['id'] ) && $oldvid['id'] == $vid['id'] ) ) {
			return $this->use_old_video_data( $vid, $oldvid, $thumb );
		}

		// grab Brightcove api key from wp_options
		$token = get_option( 'bc_api_key' );
		if ( empty( $token ) ) {
			return $vid;
		}

		if ( ! isset( $vid['id'] ) ) {
			$parse      = parse_url( $vid['url'] );
			$query_vars = array();

			parse_str( $parse['query'], $query_vars );

			if ( isset( $query_vars['vidID'] ) && $query_vars['vidID'] ) {
				$vid['id'] = $query_vars['ID'];

			} else {
				if ( isset( $query_vars['playerID'] ) && $query_vars['playerID'] ) {
					$vid['player_id'] = $query_vars['playerID'];
				}
			}

			// Player id is given which means this is a playlist so grab the first video from the playlist
			if ( isset( $vid['player_id'] ) && $vid['player_id'] ) {
				$request = 'http://api.brightcove.com/services/library?command=find_playlists_for_player_id&player_id=' . $vid['player_id'] . '&video_fields=id&token=' . $token;

				$response = $this->remote_get( $request );
				if ( $response == null || $response == 'null' ) {
					return $vid;
				}

				$bc = json_decode( $response );
				if ( WP_DEBUG ) {
					echo '<pre>' . print_r( $bc, 1 ) . '</pre>';
				}

				if ( isset( $bc->error ) ) {
					return $vid;
				}

				if ( isset( $bc->items[0]->videoIds[0] ) && $bc->items[0]->videoIds[0] ) {
					$vid['id'] = $bc->items[0]->videoIds[0];
				}
			}
		}

		if ( isset( $vid['id'] ) ) {
			$request = 'http://api.brightcove.com/services/library?command=find_video_by_id&video_id=' . $vid['id'] . '&video_fields=name,playsTotal,videoStillURL,length,FLVURL,videoFullLength&media_delivery=http&token=' . $token;

			$response = $this->remote_get( $request );
			if ( $response == null || $response == 'null' ) {
				return false;
			}

			$bc = json_decode( $response );
			if ( WP_DEBUG ) {
				echo '<pre>' . print_r( $bc, 1 ) . '</pre>';
			}

			if ( isset( $bc->error ) ) {
				return false;
			}

			$vid['type']          = 'brightcove';
			$vid['duration']      = (int) ( $bc->length / 1000 );
			$vid['view_count']    = (int) $bc->playsTotal;
			$vid['thumbnail_loc'] = $this->make_image_local( $bc->videoStillURL, $vid );
			$vid['content_loc']   = $bc->FLVURL;
			$vid['width']         = $bc->videoFullLength->frameWidth;
			$vid['height']        = $bc->videoFullLength->frameHeight;

			if ( $thumb != '' ) {
				$vid['thumbnail_loc'] = $thumb;
			} else {
				$vid['thumbnail_loc'] = $this->make_image_local( $bc->videoStillURL, $vid );
			}
		}

		return $vid;
	}

	/**
	 * Retrieve video details from Viddler
	 *
	 * @since 0.1
	 *
	 * @param array  $vid    The video array with all the data.
	 * @param array  $oldvid The video array with all the data of the previous "fetch" from Viddler, if available.
	 * @param string $thumb  The URL to the manually set thumbnail, if available.
	 *
	 * @return array|boolean $vid Returns a filled video array when successfull, false when unsuccessful.
	 */
	function viddler_details( $vid, $oldvid = array(), $thumb = '' ) {

		if ( ( isset( $oldvid['url'] ) && isset( $vid['url'] ) && $oldvid['url'] == $vid['url'] ) || ( isset( $oldvid['id'] ) && isset( $vid['id'] ) && $oldvid['id'] == $vid['id'] ) ) {
			return $this->use_old_video_data( $vid, $oldvid, $thumb );
		}

		if ( ! isset( $vid['id'] ) && isset( $vid['url'] ) ) {
			if ( preg_match( '#https?://(www.)?viddler\.com/embed/([^/]+).*#', $vid['url'], $match ) ) {
				$vid['id'] = $match[2];
			}
		}

		if ( isset( $vid['id'] ) ) {
			$response = $this->remote_get( 'http://api.viddler.com/api/v2/viddler.videos.getDetails.php?key=0118093f713643444556524f452f&video_id=' . $vid['id'] );
		} else {
			$response = $this->remote_get( 'http://api.viddler.com/api/v2/viddler.videos.getDetails.php?key=0118093f713643444556524f452f&url=' . $vid['url'] );
		}

		if ( $response ) {
			$video = unserialize( $response );

			$vid['id']         = $video['video']['id'];
			$vid['duration']   = $video['video']['length'];
			$vid['view_count'] = (int) $video['video']['view_count'];
			$vid['player_loc'] = 'http://www.viddler.com/player/' . $video['video']['id'] . '/';
			$vid['type']       = 'viddler';

			if ( isset( $video['video']['files'] ) ) {
				foreach ( $video['video']['files'] as $file ) {
					if ( $file['ext'] == 'mp4' ) {
						$vid['content_loc'] = $file['url'];
					}
				}
			}

			if ( $thumb != '' ) {
				$vid['thumbnail_loc'] = $thumb;
			} else {
				if ( isset( $video['video']['thumbnail_url'] ) ) {
					$vid['thumbnail_loc'] = $this->make_image_local( $video['video']['thumbnail_url'], $vid );
				}
			}
		}

		return $vid;
	}

	/**
	 * Retrieve video details from Flickr
	 *
	 * @since 0.1
	 *
	 * @param array  $vid    The video array with all the data.
	 * @param array  $oldvid The video array with all the data of the previous "fetch", if available.
	 * @param string $thumb  The URL to the manually set thumbnail, if available.
	 *
	 * @return array|boolean $vid Returns a filled video array when successfull, false when unsuccessful.
	 */
	function flickr_details( $vid, $oldvid = array(), $thumb = '' ) {
		if ( preg_match( '|/(\d+)/?$|', $vid['url'], $matches ) ) {
			$vid['id'] = $matches[1];

			if ( isset( $oldvid['id'] ) && $vid['id'] == $oldvid['id'] ) {
				return $this->use_old_video_data( $vid, $oldvid, $thumb );
			}

			$response = $this->remote_get( "http://api.flickr.com/services/rest/?method=flickr.photos.getInfo&api_key=2d2985adb59d21e6933368e41e5ca3b0&photo_id=" . $vid['id'] . "&format=json&nojsoncallback=1" );

			if ( $response ) {
				$flickr = json_decode( $response );

				if ( $flickr->photo->media != 'video' ) {
					return $vid;
				}

				$vid['duration']   = $flickr->photo->video->duration;
				$vid['view_count'] = (int) $flickr->photo->views;
				$vid['type']       = 'flickr';
				$vid['player_loc'] = 'http://www.flickr.com/apps/video/stewart.swf?v=109786&intl_lang=en_us&photo_secret=' . $flickr->photo->secret . '&photo_id=' . $vid['id'];

				if ( $thumb != '' ) {
					$vid['thumbnail_loc'] = $thumb;
				} else {
					$vid['thumbnail_loc'] = $this->make_image_local( 'http://farm' . $flickr->photo->farm . '.staticflickr.com/' . $flickr->photo->server . '/' . $matches[1] . '_' . $flickr->photo->secret . '.jpg', $vid );
				}
			}
		}

		return $vid;
	}

	/**
	 * Retrieve video details from Dailymotion
	 *
	 * @since 0.1
	 *
	 * @param array  $vid    The video array with all the data.
	 * @param array  $oldvid The video array with all the data of the previous "fetch", if available.
	 * @param string $thumb  The URL to the manually set thumbnail, if available.
	 *
	 * @return array|boolean $vid Returns a filled video array when successfull, false when unsuccessful.
	 */
	function dailymotion_details( $vid, $oldvid = array(), $thumb = '' ) {
		if ( ! isset( $vid['id'] ) ) {
			if ( preg_match( '|https?://(www\.)?dailymotion.com/(embed/)?video/([^_]+)(_.*)?|', $vid['url'], $matches ) ) {
				$vid['id'] = $matches[3];
			} else {
				return $vid;
			}
		}

		if ( isset( $oldvid['id'] ) && $vid['id'] == $oldvid['id'] ) {
			return $this->use_old_video_data( $vid, $oldvid, $thumb );
		}

		$response = $this->remote_get( 'https://api.dailymotion.com/video/' . $vid['id'] . '?fields=duration,embed_url,thumbnail_large_url,views_total' );

		if ( $response ) {
			$video = json_decode( $response );

			$vid['view_count'] = (int) $video->views_total;
			$vid['duration']   = $video->duration;
			$vid['player_loc'] = $video->embed_url;
			$vid['type']       = 'dailymotion';

			if ( $thumb != '' ) {
				$vid['thumbnail_loc'] = $thumb;
			} else {
				$vid['thumbnail_loc'] = $this->make_image_local( $video->thumbnail_large_url, $vid );
			}
		}

		return $vid;
	}

	/**
	 * Retrieve video details from Blip.tv
	 *
	 * @since 0.1
	 *
	 * @param array  $vid    The video array with all the data.
	 * @param array  $oldvid The video array with all the data of the previous "fetch", if available.
	 * @param string $thumb  The URL to the manually set thumbnail, if available.
	 *
	 * @return array|boolean $vid Returns a filled video array when successfull, false when unsuccessful.
	 */
	function blip_details( $vid, $oldvid = array(), $thumb = '' ) {

		if ( ! isset( $vid['id'] ) || empty( $vid['id'] ) ) {
			if ( preg_match( '/.*-(\d+)$/', $vid['url'], $matches ) ) {
				$vid['id'] = $matches[1];
			}

			// This isn't active yet as the ID here doesn't translate directly into a Blip ID...
			// if ( preg_match( '|http://blip\.tv/play/([^\.]+)\.html.*|', $vid['url'], $matches ) )
			//	$vid['id'] = $matches[1];
		}

		if ( isset( $vid['id'] ) ) {

			if ( isset( $oldvid['id'] ) && $vid['id'] == $oldvid['id'] ) {
				return $this->use_old_video_data( $vid, $oldvid, $thumb );
			}

			$response = $this->remote_get( 'http://blip.tv/rss/view/' . $vid['id'] );

			if ( $response ) {
				preg_match( "|<blip:runtime>(\d+)</blip:runtime>|", $response, $match );
				$vid['duration'] = $match[1];

				preg_match( '|<media:player url="([^"]+)">|', $response, $match );
				$vid['player_loc'] = $match[1];

				preg_match( '|<enclosure length="[\d]+" type="[^"]+" url="([^"]+)"/>|', $response, $match );
				$vid['content_loc'] = $match[1];

				$vid['type'] = 'blip.tv';

				if ( $thumb != '' ) {
					$vid['thumbnail_loc'] = $thumb;
				} else {
					preg_match( '|<media:thumbnail url="([^"]+)"/>|', $response, $match );
					$vid['thumbnail_loc'] = $this->make_image_local( $match[1], $vid );
				}
			}
		}

		return $vid;
	}

	/**
	 * Retrieve video details from Animoto
	 *
	 * @since 1.4.3
	 *
	 * @param array  $vid    The video array with all the data.
	 * @param array  $oldvid The video array with all the data of the previous "fetch", if available.
	 * @param string $thumb  The URL to the manually set thumbnail, if available.
	 *
	 * @return array|boolean $vid Returns a filled video array when successfull, false when unsuccessful.
	 */
	function animoto_details( $vid, $oldvid = array(), $thumb = '' ) {

		if ( isset( $vid['url'] ) ) {
			if ( preg_match( '/http:\/\/static\.animoto\.com\/swf\/.*?&f=([^&]+).*/', $vid['url'], $match ) ) {
				$vid['url'] = 'http://animoto.com/play/' . $match[1];
			}

			if ( isset( $oldvid['url'] ) && $vid['url'] == $oldvid['url'] ) {
				return $this->use_old_video_data( $vid, $oldvid, $thumb );
			}

			$response = $this->remote_get( 'http://animoto.com/services/oembed?format=json&url=' . urlencode( $vid['url'] ) );
			if ( ! is_wp_error( $response ) && $response ) {
				$video = json_decode( $response );

				$vid['player_loc'] = $video->video_url;

				// Animoto doesn't provide duration in the oembed API, unfortunately.
				// $vid['duration']   = $video->duration;

				$vid['type'] = 'animoto';

				if ( $thumb != '' ) {
					$vid['thumbnail_loc'] = $thumb;
				} else {
					if ( isset( $video->thumbnail_url ) ) {
						$vid['thumbnail_loc'] = $this->make_image_local( $video->thumbnail_url, $vid );
					}
				}
			}
		}

		return $vid;
	}

	/**
	 * Retrieve video details from Vimeo
	 *
	 * @since 0.1
	 *
	 * @param array  $vid    The video array with all the data.
	 * @param array  $oldvid The video array with all the data of the previous "fetch", if available.
	 * @param string $thumb  The URL to the manually set thumbnail, if available.
	 *
	 * @return array|boolean $vid Returns a filled video array when successfull, false when unsuccessful.
	 */
	function vimeo_details( $vid, $oldvid = array(), $thumb = '' ) {
		if ( ! isset( $vid['id'] ) ) {
			if ( preg_match( '#https?://(player\.|www\.)?vimeo\.com/(video/)?(\d+)#', $vid['url'], $matches ) ) {
				$vid['id'] = $matches[3];
			}

			if ( preg_match( '#https?://(www\.)?vimeo\.com/moogaloop\.swf\?clip_id=([^&]+)#', $vid['url'], $matches ) ) {
				$vid['id'] = $matches[2];
			}
		}

		if ( isset( $vid['id'] ) ) {
			$vid['url'] = 'http://vimeo.com/' . $vid['id'];
		}

		if ( isset( $oldvid['url'] ) && $vid['url'] == $oldvid['url'] ) {
			return $this->use_old_video_data( $vid, $oldvid, $thumb );
		}

		$response = $this->remote_get( 'http://vimeo.com/api/oembed.json?url=' . $vid['url'] );
		if ( $response ) {
			$video = json_decode( $response );

			$vid['id']         = $video->video_id;
			$vid['player_loc'] = 'https://www.vimeo.com/moogaloop.swf?clip_id=' . $vid['id'];
			$vid['duration']   = $video->duration;
			$vid['width']      = $video->width;
			$vid['height']     = $video->height;
			$vid['type']       = 'vimeo';

			if ( $thumb != '' ) {
				$vid['thumbnail_loc'] = $thumb;
			} else {
				if ( isset( $video->thumbnail_url ) ) {
					$vid['thumbnail_loc'] = $this->make_image_local( $video->thumbnail_url, $vid );
				}
			}
		}

		return $vid;
	}

	/**
	 * Retrieve video details from Vzaar
	 *
	 * @since 0.1
	 *
	 * @param array  $vid    The video array with all the data.
	 * @param array  $oldvid The video array with all the data of the previous "fetch", if available.
	 * @param string $thumb  The URL to the manually set thumbnail, if available.
	 *
	 * @return array|boolean $vid Returns a filled video array when successfull, false when unsuccessful.
	 */
	function vzaar_details( $vid, $oldvid = array(), $thumb = '' ) {
		$options = get_option( 'wpseo_video' );

		if ( ! isset( $vid['id'] ) ) {
			if ( preg_match( '/\/(\d+)\/(player|flashplayer|video|download)$/', $vid['url'], $match ) ) {
				$vid['id'] = $match[1];
			}
		}


		if ( isset( $vid['id'] ) ) {

			$vid['type'] = 'vzaar';

			if ( is_array( $oldvid ) && isset( $oldvid['id'] ) && $vid['id'] == $oldvid['id'] ) {
				return $this->use_old_video_data( $vid, $oldvid, $thumb );
			}

			$base_url = 'http://vzaar.com/videos/';
			if ( $options['vzaar_domain'] !== '' ) {
				$base_url = 'http://' . $options['vzaar_domain'] . '/';
			}

			if ( $thumb != '' ) {
				$vid['thumbnail_loc'] = $thumb;
			} else {
				$vid['thumbnail_loc'] = $this->make_image_local( $base_url . $vid['id'] . '/image', $vid, 'jpg' );
			}

			$vid['player_loc'] = 'https://view.vzaar.com/' . $vid['id'] . '/flasplayer';

			$response = $this->remote_get( $base_url . $vid['id'] . '.json', array( 'referer' => get_site_url() ) );

			// We don't strictly need a response, funnily enough, though we lack the duration when we don't get it.
			if ( $response ) {
				$video = json_decode( $response );

				if ( isset( $vid['duration'] ) ) {
					$vid['duration'] = round( $video->duration );
				}
				if ( isset( $vid['width'] ) ) {
					$vid['width'] = $video->width;
				}
				if ( isset( $vid['height'] ) ) {
					$vid['height'] = $video->height;
				}
			}
		}

		return $vid;
	}


	/**
	 * Retrieve video details from Vidyard
	 *
	 * @since 1.3.4.4
	 *
	 * @param array  $vid    The video array with all the data.
	 * @param array  $oldvid The video array with all the data of the previous "fetch", if available.
	 * @param string $thumb  The URL to the manually set thumbnail, if available.
	 *
	 * @return array|boolean $vid Returns a filled video array when successfull, false when unsuccessful.
	 */
	function vidyard_details( $vid, $oldvid = array(), $thumb = '' ) {
		if ( ! isset( $vid['id'] ) ) {
			if ( preg_match( '~vidyard\.com/(.*?)(\.js|\.html|\?|$)~', $vid['url'], $match ) ) {
				$vid['id'] = $match[1];
			}
		}

		if ( isset( $vid['id'] ) ) {
			if ( isset( $oldvid['id'] ) && $vid['id'] == $oldvid['id'] ) {
				return $this->use_old_video_data( $vid, $oldvid, $thumb );
			}

			$url      = 'http://play.vidyard.com/' . $vid['id'];
			$response = $this->remote_get( $url );

			if ( $response ) {
				// must use preg match because the data is in inline javascript
				preg_match( '/vidyard_chapter_data = (\[.*?\]);/s', $response, $match );
				// replace single quotes with double quotes so it can be json decoded
				$json = str_replace( '\'', '"', trim( $match[1] ) );
				$json = json_decode( $json, true );
				$json = reset( $json );

				$vid['player_loc']  = $url;
				$vid['content_loc'] = $json['sd_unsecure_url'];
				$vid['duration']    = $json['seconds'];
				$vid['type']        = 'vidyard';

				// preg match for thumbnail
				preg_match( '/vidyard_thumbnail_data = ({.*?});/s', $response, $match1 );
				$thumbnail_data = str_replace( '\'', '"', trim( $match1[1] ) );
				$thumbnail_data = json_decode( $thumbnail_data, true );
				$thumbnail_data = reset( $thumbnail_data );
				$thumbnail      = $thumbnail_data['url'];

				if ( $thumb != '' ) {
					$vid['thumbnail_loc'] = $thumb;
				} else {
					$vid['thumbnail_loc'] = $this->make_image_local( $thumbnail, $vid );
				}
			}
		}

		return $vid;
	}


	/**
	 * Retrieve video details from Vippy
	 *
	 * @since 1.3.4
	 *
	 * @param array  $vid    The video array with all the data.
	 * @param array  $oldvid The video array with all the data of the previous "fetch", if available.
	 * @param string $thumb  The URL to the manually set thumbnail, if available.
	 *
	 * @return array|boolean $vid Returns a filled video array when successfull, false when unsuccessful.
	 */
	function vippy_details( $vid, $oldvid = array(), $thumb = '' ) {
		if ( isset( $oldvid['id'] ) && isset( $vid['id'] ) && $oldvid['id'] == $vid['id'] ) {
			return $this->use_old_video_data( $vid, $oldvid, $thumb );
		}
		$vippy_id = $vid['id'];
		// Requires the Vippy plugin http://wordpress.org/extend/plugins/vippy/
		if ( ! class_exists( 'Vippy' ) ) {
			return $vid;
		}

		//Retrieve the vippy video
		$vippy       = new Vippy;
		$vippy_video = $vippy->get_video( array( 'videoId' => $vippy_id, 'statistics' => 1 ) );
		if ( isset( $vippy_video->error ) ) {
			return false;
		}

		//Fill the details
		$vippy_video = isset( $vippy_video->vippy[0] ) ? $vippy_video->vippy[0] : false;
		if ( ! $vippy_video ) {
			return false;
		}
		$vid['type']        = 'vippy';
		$vid['content_loc'] = isset( $vippy_video->open_graph_url ) ? $vippy_video->highQuality : ''; //MP4
		if ( $thumb != '' ) {
			$vid['thumbnail_loc'] = $thumb;
		} else {
			$vid['thumbnail_loc'] = isset( $vippy_video->thumbnail ) ? $this->make_image_local( $vippy_video->thumbnail, $vid ) : '';
		}
		$vid['duration']   = isset( $vippy_video->duration ) ? round( $vippy_video->duration ) : 0; //convert 30.09 to 30
		$vid['view_count'] = (int) $vippy_video->views;

		return $vid;
	}

	/**
	 * Retrieve video details from Wistia
	 *
	 * @since 0.1
	 *
	 * @param array  $vid    The video array with all the data.
	 * @param array  $oldvid The video array with all the data of the previous "fetch", if available.
	 * @param string $thumb  The URL to the manually set thumbnail, if available.
	 *
	 * @return array|boolean $vid Returns a filled video array when successfull, false when unsuccessful.
	 */
	function wistia_details( $vid, $oldvid = array(), $thumb = '' ) {

		if ( isset( $oldvid['url'] ) && isset( $vid['url'] ) && $vid['url'] == $oldvid['url'] ) {
			return $this->use_old_video_data( $vid, $oldvid, $thumb );
		}

		if ( isset( $vid['id'] ) ) {
			$vid['url'] = 'http://home.wistia.com/medias/' . $vid['id'];
		}

		if ( isset( $vid['url'] ) ) {

			// Force the embed to be an SEO embed.
			$url = urlencode( preg_replace( '`embedType=(?:api|iframe)`', 'embedType=seo', $vid['url'] ) );

			$response = $this->remote_get( 'http://fast.wistia.com/oembed?url=' . $url );

			if ( $response ) {
				$video = json_decode( $response );

				$vid['duration'] = round( $video->duration );
				$vid['type']     = 'wistia';
				$vid['width']    = $video->width;
				$vid['height']   = $video->height;

				if ( $thumb != '' ) {
					$vid['thumbnail_loc'] = $thumb;
				} else {
					if ( isset( $video->thumbnail_url ) ) {
						$vid['thumbnail_loc'] = $this->make_image_local( $video->thumbnail_url, $vid );
					}
				}

				$video->html = stripslashes( $video->html );

				if ( preg_match( '/<div itemprop="video"/', $video->html ) ) {
					preg_match( '`<meta itemprop="contentURL" content="([^"]+)" />`', $video->html, $match );
					$vid['content_loc'] = $match[1];

					preg_match( '`<meta itemprop="embedURL" content="([^"]+)" />`', $video->html, $match );
					$vid['player_loc'] = $match[1];

					return $vid;
				} else {
					if ( preg_match( '/<iframe src=(\'|")(.*?)\1/', $video->html, $match ) ) {

						$framesrc = $this->remote_get( $match[2] );

						if ( preg_match( '/<a href=(\'|")(.*?)\1 id=(\'|")wistia_fallback\3/', $framesrc, $framematch ) ) {

							$vid['content_loc'] = $framematch[2];
							$vid['duration']    = round( $video->duration );
							$vid['type']        = 'wistia';
							$vid['width']       = $video->width;
							$vid['height']      = $video->height;

							if ( preg_match( '/"type":"flv","url":"(.*?)"/', $framesrc, $tmpmatch ) ) {
								$flv = $tmpmatch[1];
							}

							if ( preg_match( '/"type":"still","url":"(.*?)"/', $framesrc, $tmpmatch ) ) {
								$still = $tmpmatch[1];
							}

							if ( preg_match( '/"accountKey":"(.*?)"/', $framesrc, $tmpmatch ) ) {
								$account_key = $tmpmatch[1];
							}

							if ( preg_match( '/"mediaKey":"(.*?)"/', $framesrc, $tmpmatch ) ) {
								$media_key = $tmpmatch[1];
							}

							if ( isset( $flv, $still, $account_key, $media_key ) ) {
								$vid['player_loc'] = 'https://wistia.sslcs.cdngc.net/flash/embed_player_v2.0.swf?videoUrl=' . $flv . '&stillUrl=' . $still . '&controlsVisibleOnLoad=false&unbufferedSeek=true&autoLoad=false&autoPlay=true&endVideoBehavior=default&embedServiceURL=http://distillery.wistia.com/x&accountKey=' . $account_key . '&mediaID=' . $media_key . '&mediaDuration=' . $video->duration . '&fullscreenDisabled=false';
							}

							return $vid;
						}
					}
				}
			}
		}

		return $vid;
	}

	/**
	 * Retrieve video details from YouTube
	 *
	 * @since 0.1
	 *
	 * @param array  $vid    The video array with all the data.
	 * @param array  $oldvid The video array with all the data of the previous "fetch", if available.
	 * @param string $thumb  The URL to the manually set thumbnail, if available.
	 *
	 * @return array|boolean $vid Returns a filled video array when successfull, false when unsuccessful.
	 */
	function youtube_details( $vid, $oldvid = array(), $thumb = '' ) {

		if ( ! isset( $vid['id'] ) ) {
			$id_match = '[0-9a-zA-Z\-_]+';
			if ( preg_match( '|https?://(www\.)?youtube\.com/(watch)?\?.*v=(' . $id_match . ')|', $vid['url'], $matches ) ) {
				$vid['id'] = $matches[3];
			} else {
				if ( preg_match( '|https?://(www\.)?youtube(-nocookie)?\.com/embed/(' . $id_match . ')|', $vid['url'], $matches ) ) {
					$vid['id'] = $matches[3];
				} else {
					if ( preg_match( '|https?://(www\.)?youtube\.com/v/(' . $id_match . ')|', $vid['url'], $matches ) ) {
						$vid['id'] = $matches[2];
					} else {
						if ( preg_match( '|http://youtu\.be/(' . $id_match . ')|', $vid['url'], $matches ) ) {
							$vid['id'] = $matches[1];
						} else {
							if ( ! preg_match( '|^http|', $vid['url'], $matches ) ) {
								$vid['id'] = $vid['url'];
							}
						}
					}
				}
			}
		}

		if ( isset( $vid['id'] ) ) {
			if ( isset( $oldvid['id'] ) && $vid['id'] == $oldvid['id'] ) {
				return $this->use_old_video_data( $vid, $oldvid, $thumb );
			}

			$vid['player_loc'] = htmlentities( 'https://www.youtube-nocookie.com/v/' . $vid['id'] );
			$vid['type']       = 'youtube';

			$response = $this->remote_get( 'http://gdata.youtube.com/feeds/api/videos/' . $vid['id'] );

			if ( $response ) {
				// Thumbnail
				if ( $thumb != '' ) {
					$vid['thumbnail_loc'] = $thumb;
				} else {
					preg_match( "|<media:thumbnail url='([^']+)'|", $response, $match );
					$vid['thumbnail_loc'] = $this->make_image_local( $match[1], $vid );
				}

				// View count
				preg_match( "|<yt:statistics favoriteCount='([\d]+)' viewCount='([\d]+)'/>|", $response, $match );
				$vid['view_count'] = (int) $match[2];

				$vid['width']  = 640;
				$vid['height'] = 390;

				// Duration
				preg_match( "|<yt:duration seconds='([0-9]+)'/>|", $response, $match );
				$vid['duration'] = $match[1];
			}
		}

		return $vid;
	}

	/**
	 * Retrieve video details from VideoPress
	 *
	 * @since 0.1
	 *
	 * @param array  $vid    The video array with all the data.
	 * @param array  $oldvid The video array with all the data of the previous "fetch", if available.
	 * @param string $thumb  The URL to the manually set thumbnail, if available.
	 *
	 * @return array|boolean $vid Returns a filled video array when successfull, false when unsuccessful.
	 */
	function videopress_details( $vid, $oldvid = array(), $thumb = '' ) {
		if ( isset( $oldvid['id'] ) && $vid['id'] == $oldvid['id'] ) {
			return $this->use_old_video_data( $vid, $oldvid, $thumb );
		}

		$domain         = parse_url( home_url(), PHP_URL_HOST );
		$request_params = array( 'guid' => $vid['id'], 'domain' => $domain );

		$url = 'https://v.wordpress.com/data/wordpress.json?' . http_build_query( $request_params, null, '&' );

		$response = $this->remote_get( $url );

		if ( $response ) {
			$video = json_decode( $response );

			$vid['duration']   = $video->duration;
			$vid['player_loc'] = 'https://v0.wordpress.com/player.swf?v=1.03&amp;guid=' . $vid['id'] . '&amp;isDynamicSeeking=true';

			$vid['type'] = 'videopress';

			if ( isset( $video->mp4 ) ) {
				$vid['content_loc'] = $video->mp4->url;
			}

			if ( $thumb != '' ) {
				$vid['thumbnail_loc'] = $thumb;
			} else {
				$vid['thumbnail_loc'] = $this->make_image_local( $video->posterframe, $vid );
			}
		}

		return $vid;
	}

	/**
	 * Retrieve video details from WordPress.tv (well grab the ID and then use the VideoPress API)
	 *
	 * @since 0.1
	 *
	 * @param array  $vid    The video array with all the data.
	 * @param array  $oldvid The video array with all the data of the previous "fetch", if available.
	 * @param string $thumb  The URL to the manually set thumbnail, if available.
	 *
	 * @return array|boolean $vid Returns a filled video array when successfull, false when unsuccessful.
	 */
	function wordpresstv_details( $vid, $oldvid = array(), $thumb = '' ) {
		if ( isset( $oldvid['url'] ) && $vid['url'] == $oldvid['url'] ) {
			return $this->use_old_video_data( $vid, $oldvid, $thumb );
		}

		$response = $this->remote_get( 'http://wordpress.tv/oembed/?url=' . $vid['url'] );
		if ( $response ) {
			$wptv = json_decode( $response );

			if ( preg_match( '|v\.wordpress\.com/([^"]+)|', $wptv->html, $match ) ) {
				$vid['id'] = $match[1];

				return $this->videopress_details( $vid, $oldvid, $thumb );
			} else {
				return $vid;
			}

		}

		return $vid;
	}

	/**
	 * Retrieve video details from Metacafe
	 *
	 * @since 0.1
	 *
	 * @link  http://help.metacafe.com/?page_id=238 Metacafe API docs.
	 *
	 * @param array  $vid    The video array with all the data.
	 * @param array  $oldvid The video array with all the data of the previous "fetch", if available.
	 * @param string $thumb  The URL to the manually set thumbnail, if available.
	 *
	 * @return array|boolean $vid Returns a filled video array when successfull, false when unsuccessful.
	 */
	function metacafe_details( $vid, $oldvid = array(), $thumb = '' ) {
		if ( ! isset( $vid['id'] ) ) {
			if ( preg_match( '|/watch/(\d+)/|', $vid['url'], $matches ) ) {
				$vid['id'] = $matches[1];
			}
		}

		if ( isset( $vid['id'] ) ) {

			if ( isset( $oldvid['id'] ) && $vid['id'] == $oldvid['id'] ) {
				return $this->use_old_video_data( $vid, $oldvid, $thumb );
			}

			$vid['type'] = 'metacafe';

			$response = $this->remote_get( 'http://www.metacafe.com/api/item/' . $vid['id'] . '/' );

			preg_match( '/duration="(\d+)"/', $response, $match );
			$vid['duration'] = $match[1];

			$vid['player_loc'] = 'http://www.metacafe.com/fplayer/' . $vid['id'] . '/.swf';

			preg_match( '/<media:content url="([^"]+)"/', $response, $match );
			$vid['content_loc'] = $match[1];

			if ( $thumb != '' ) {
				$vid['thumbnail_loc'] = $thumb;
			} else {
				preg_match( '/<media:thumbnail url="([^"]+)"/', $response, $match );
				$vid['thumbnail_loc'] = $this->make_image_local( $match[1], $vid );
			}
		}

		return $vid;
	}

	/**
	 * Retrieve video details for Veoh Video's
	 *
	 * @since 0.1
	 *
	 * @param array  $vid    The video array with all the data.
	 * @param array  $oldvid The video array with all the data of the previous "fetch", if available.
	 * @param string $thumb  The URL to the manually set thumbnail, if available.
	 *
	 * @return array|boolean $vid Returns a filled video array when successfull, false when unsuccessful.
	 */
	function veoh_details( $vid, $oldvid = array(), $thumb = '' ) {
		if ( ! isset( $vid['id'] ) ) {
			if ( preg_match( '|veoh\.com/videos/([^/]+)$|', $vid['url'], $matches ) ) {
				$vid['id'] = $matches[1];
			}
		}

		if ( isset( $vid['id'] ) ) {
			if ( isset( $oldvid['id'] ) && $vid['id'] == $oldvid['id'] ) {
				return $this->use_old_video_data( $vid, $oldvid, $thumb );
			}

			$vid['type'] = 'veoh';

			$vid['player_loc'] = 'http://www.veoh.com/veohplayer.swf?permalinkId=' . $vid['id'];

			if ( $thumb != '' ) {
				$vid['thumbnail_loc'] = $thumb;
			} else {
				$vid['thumbnail_loc'] = $this->make_image_local( 'http://ll-images.veoh.com/media/w300/thumb-' . $vid['id'] . '-1.jpg', $vid );
			}
		}

		return $vid;
	}

	/**
	 * Retrieve video details for Screencast.com
	 *
	 * @since 1.5.4.4
	 *
	 * @param array  $vid    The video array with all the data.
	 * @param array  $oldvid The video array with all the data of the previous "fetch", if available.
	 * @param string $thumb  The URL to the manually set thumbnail, if available.
	 *
	 * @return array|boolean $vid Returns a filled video array when successfull, false when unsuccessful.
	 *
	 * TODO: no api or connection from getting video details from the url so we extract the details from the embed code itself
	 */
	function screencast_details( $vid, $oldvid = array(), $thumb = '' ) {
		if ( ! isset( $vid['id'] ) ) {
			if ( preg_match( '|screencast\.com/(.*)$|', $vid['url']['url'], $matches ) ) {
				$vid['id'] = $matches[1];
			}
		}

		if ( isset( $vid['id'] ) ) {
			if ( isset( $oldvid['id'] ) && $vid['id'] == $oldvid['id'] ) {
				return $this->use_old_video_data( $vid, $oldvid, $thumb );
			}

			$response = isset( $vid['url']['embed'] ) ? $vid['url']['embed'] : '';
			// $response = $this->remote_get( 'http://screencast.com/t/eUvutAvEibx9' );

			if ( $response ) {
				$dom = new DOMDocument();
				@$dom->loadHTML( $response );
				$xpath = new DOMXPath( $dom );

				$item = $xpath->query( '//object/param[@name="flashVars"]' );
				$item = $item->item( 0 )->getAttribute( 'value' );
				parse_str( $item, $video );

				$vid['type']       = 'screencast';
				$vid['player_loc'] = $video['content'];
				$vid['width']      = $video['containerwidth'];
				$vid['height']     = $video['containerheight'];

				if ( $thumb != '' ) {
					$vid['thumbnail_loc'] = $thumb;
				} else {
					$vid['thumbnail_loc'] = $this->make_image_local( $video['thumb'], $vid );
				}
			}
		}

		return $vid;
	}

	/**
	 * Retrieve video details for Screenr
	 *
	 * @since ?
	 *
	 * @param array  $vid    The video array with all the data.
	 * @param array  $oldvid The video array with all the data of the previous "fetch", if available.
	 * @param string $thumb  The URL to the manually set thumbnail, if available.
	 *
	 * @return array|boolean $vid Returns a filled video array when successfull, false when unsuccessful.
	 */
	function screenr_details( $vid, $oldvid = array(), $thumb = '' ) {
		if ( ! isset( $vid['id'] ) ) {
			if ( preg_match( '|screenr\.com/([^/]+)$|', $vid['url'], $matches ) ) {
				$vid['id'] = $matches[1];
			}
		}

		if ( isset( $vid['id'] ) ) {
			if ( isset( $oldvid['id'] ) && $vid['id'] == $oldvid['id'] ) {
				return $this->use_old_video_data( $vid, $oldvid, $thumb );
			}

			$response = $this->remote_get( 'http://www.screenr.com/api/oembed.json?url=http://screenr.com/' . $vid['id'] );

			if ( $response ) {
				$video = json_decode( $response );

				$vid['type']       = 'screenr';
				$vid['player_loc'] = 'http://www.screenr.com/embed/' . $vid['id'];
				$vid['width']      = $video->width;
				$vid['height']     = $video->height;

				if ( $thumb != '' ) {
					$vid['thumbnail_loc'] = $thumb;
				} else {
					if ( isset( $video->thumbnail_url ) ) {
						$vid['thumbnail_loc'] = $this->make_image_local( $video->thumbnail_url, $vid );
					}
				}
			}
		}

		return $vid;
	}

	/**
	 * Retrieve video details for Muzu.tv
	 *
	 * @since ?
	 *
	 * @param array  $vid    The video array with all the data.
	 * @param array  $oldvid The video array with all the data of the previous "fetch", if available.
	 * @param string $thumb  The URL to the manually set thumbnail, if available.
	 *
	 * @return array|boolean $vid Returns a filled video array when successfull, false when unsuccessful.
	 */
	function muzutv_details( $vid, $oldvid = array(), $thumb = '' ) {
		if ( ! isset( $vid['id'] ) ) {
			// TODO: preg_match does not grab the id properly
			// example http://www.muzu.tv/sean-paul-beenie-man/greatest-gallis-music-video/1847016/
			// if ( preg_match( '|muzu\.tv/.*([^/]+)$|', $vid['url'], $matches ) )
			// $vid['id'] = $matches[1];
			$parse = parse_url( $vid['url'] );
			if ( preg_match( '|muzu\.tv$|', $parse['host'], $matches ) ) {
				$pieces    = array_filter( explode( '/', $parse['path'] ), 'strlen' );
				$vid['id'] = end( $pieces );
			}
		}

		if ( isset( $vid['id'] ) ) {
			if ( isset( $oldvid['id'] ) && $vid['id'] == $oldvid['id'] ) {
				return $this->use_old_video_data( $vid, $oldvid, $thumb );
			}

			$response = $this->remote_get( 'http://www.muzu.tv/api/video/details/' . $vid['id'] . '?muzuid=b00q0xGOTl' );

			if ( $response ) {
				$xml   = new SimpleXMLElement( $response );
				$media = $xml->channel->item->children( 'http://search.yahoo.com/mrss/' );

				$vid['type']       = 'muzutv';
				$vid['player_loc'] = 'https://player.muzu.tv/player/getPlayer/i/293053/vidId=' . $vid['id'] . '&autostart=y&dswf=y';
				$vid['duration']   = (int) $media->content->attributes()->duration;
				$vid['width']      = (string) $media->content->attributes()->width;
				$vid['height']     = (string) $media->content->attributes()->height;

				if ( $thumb != '' ) {
					$vid['thumbnail_loc'] = $thumb;
				} else {
					$thumbnail            = (string) $xml->channel->image->url;
					$vid['thumbnail_loc'] = $this->make_image_local( $thumbnail, $vid );
				}
			}
		}

		return $vid;
	}

	/**
	 * Retrieve video details for cincopa
	 *
	 * @since ?
	 *
	 * @param array  $vid    The video array with all the data.
	 * @param array  $oldvid The video array with all the data of the previous "fetch", if available.
	 * @param string $thumb  The URL to the manually set thumbnail, if available.
	 *
	 * @return array|boolean $vid Returns a filled video array when successfull, false when unsuccessful.
	 */
	function cincopa_details( $vid, $oldvid = array(), $thumb = '' ) {
		if ( ! isset( $vid['id'] ) ) {
			$parse = parse_url( $vid['url'] );
			if ( preg_match( "~cp_load_widget\('(.*?)',.*?\);~", $parse['query'], $matches ) ) {
				$vid['id'] = $matches[1];
			}
		}

		if ( isset( $vid['id'] ) ) {
			if ( isset( $oldvid['id'] ) && $vid['id'] == $oldvid['id'] ) {
				return $this->use_old_video_data( $vid, $oldvid, $thumb );
			}

			$response = $this->remote_get( 'http://www.cincopa.com/media-platform/runtime/rss200.aspx?fid=' . $vid['id'] );

			if ( $response ) {
				$xml   = new SimpleXMLElement( $response );
				$media = $xml->channel->item->children( 'http://search.yahoo.com/mrss/' );

				$vid['type']       = 'cincopa';
				$vid['player_loc'] = (string) $media->content->attributes()->url;
				// $vid['duration']   = (int) $media->content->attributes()->duration;
				// $vid['width']      = (string) $media->content->attributes()->width;
				// $vid['height']     = (string) $media->content->attributes()->height;

				// TODO: thumbnails are not working cuurent b/c $this->make_image_local() strips query parameters
				// and this service needs query params to generate thumbnails look for a more direct approach
				if ( $thumb != '' ) {
					$vid['thumbnail_loc'] = $thumb;
				} else {
					$thumbnail            = (string) $media->thumbnail->attributes()->url;
					$vid['thumbnail_loc'] = $this->make_image_local( $thumbnail, $vid );
				}
			}
		}

		return $vid;
	}

	/**
	 * Retrieve video details for Easy Video Suite (EVS)
	 *
	 * @since ?
	 *
	 * @param array  $vid    The video array with all the data.
	 * @param array  $oldvid The video array with all the data of the previous "fetch", if available.
	 * @param string $thumb  The URL to the manually set thumbnail, if available.
	 *
	 * @return array|boolean $vid Returns a filled video array when successfull, false when unsuccessful.
	 */
	function evs_details( $vid, $oldvid = array(), $thumb = '' ) {
		$evs_location = get_option( 'evs_location' );

		if ( ! $evs_location ) {
			return $vid;
		}

		if ( ! isset( $vid['id'] ) ) {
			$vid['id'] = $vid['url'];
		}

		if ( isset( $vid['id'] ) ) {
			// if ( isset( $oldvid['id'] ) && $vid['id'] == $oldvid['id'] )
			// return $this->use_old_video_data( $vid, $oldvid, $thumb );

			$api = $evs_location . '/api.php';

			// TODO: other evs metadata
			// player_loc, content_loc, duration, width, height
			$vid['content_loc'] = $vid['url'];
			$vid['player_loc']  = $vid['url'];

			// evs thumbnail info
			$video_thumb_info = wp_remote_post( $api, array(
					'method'      => 'POST',
					'timeout'     => 45,
					'redirection' => 5,
					'httpversion' => '1.0',
					'blocking'    => true,
					'headers'     => array(),
					'cookies'     => array(),
					'body'        => array(
							'page_url' => $vid['id'],
							'method'   => 'public-file-images'
					)
			) );

			$video_thumb_info = json_decode( $video_thumb_info['body'] );

			if ( $thumb != '' ) {
				$vid['thumbnail_loc'] = $thumb;
			} else {
				if ( is_object( $video_thumb_info ) && $video_thumb_info->success == true ) {
					$vid['thumbnail_loc'] = $this->make_image_local( $video_thumb_info->thumbnail, $vid );
				}
			}
		}

		return $vid;
	}


	/**
	 * Parse the content of a post or term description.
	 *
	 * @since 1.3
	 *
	 * @param string $content The content to parse for videos.
	 * @param array  $vid     The video array to update.
	 * @param array  $oldvid  The former video array.
	 *
	 * @return array $vid
	 */
	function index_content( $content, $vid, $oldvid = array() ) {
		global $shortcode_tags;
		$old_shortcode_tags = $shortcode_tags;

		$content = apply_filters( 'wpseo_video_index_content', $content, $vid );

		if ( preg_match( '/(<video.*<\/video>)/s', $content, $html5vid ) ) {

			if ( preg_match( '/src=(\'|")(.*?)\.(mpg|mpeg|mp4|m4v|mov|wmv|asf|avi|ra|ram|rm|flv|swf)\1/', $html5vid[1], $content_loc ) ) {
				$vid['content_loc'] = $content_loc[2] . '.' . $content_loc[3];

				if ( preg_match( '/poster=(\'|")(.*?)\1/', $html5vid[1], $thumbnail_loc ) ) {
					$vid['thumbnail_loc'] = $thumbnail_loc[2];
				}

				$vid['type'] = 'html5vid';
			}
		}

		if ( preg_match( '/<div id=(\'|")wistia_(.*?)\1 class=(\'|")wistia_embed\s?.*\3/', $content, $matches ) ) {
			$vid['id'] = $matches[2];
			$vid       = $this->wistia_details( $vid, $oldvid );
		}

		if ( preg_match( '/<a(.*?)href="https?:\/\/fast.wistia.(com|net)\/embed\/iframe\/(.*?)\?/', $content, $matches ) ) {
			$vid['id'] = $matches[3];
			$vid       = $this->wistia_details( $vid, $oldvid );
		}

		if ( isset( $vid['content_loc'] ) || isset( $vid['player_loc'] ) ) {
			$vid = apply_filters( 'wpseo_video_' . $vid['type'] . '_details', $vid );

			return $vid;
		}

		$shortcode_tags = array(
				'bliptv'                 => '',
				'blip.tv'                => '',
				'cincopa'                => '',
				'dailymotion'            => '',
				'embedplusvideo'         => '',
				'ez_video'               => '',
				'ez_youtube'             => '',
				'ez_vimeo'               => '',
				'flickrvideo'            => '',
				'flowplayer'             => '',
				'flv'                    => '',
				'fvplayer'               => '',
				'jwplayer'               => '',
				'lyte'                   => '',
				'metacafe'               => '',
				'pb_vidembed'            => '',
				'sublimevideo'           => '',
				'sublimevideo-lightbox'  => '',
				'tube'                   => '',
				'tubepress'              => '',
				'veoh'                   => '',
				'viddler'                => '',
				'video'                  => '',
				'videojs'                => '',
				'video_lightbox_vimeo5'  => '',
				'video_lightbox_youtube' => '',
				'vimeo'                  => '',
				'vippy'                  => '',
				'vzaarmedia'             => '',
				'weaver_vimeo'           => '',
				'weaver_youtube'         => '',
				'wpvideo'                => '',
				'wposflv'                => '',
				'youtube'                => '',
				'youtubewd'              => '',
				'youtube_sc'             => '',
				'youtube-embed'          => '',
				'youtube-white-label'    => '',
		);
		if ( preg_match( '/' . get_shortcode_regex() . '/', $content, $matches ) ) {
			$thumb = '';
			preg_match( '/image=(\'|")?(.*?)\1?/', $matches[3], $match );
			if ( isset( $match[2] ) && ! empty( $match[2] ) ) {
				$thumb = $match[2];
			}

			// support for Advanced Responsive Video Embedder plugin shortcode format
			// [provider id="id"]
			$embedder = shortcode_parse_atts( $matches[3] );

			switch ( $matches[2] ) {
				case 'bliptv':
					$vid['id'] = trim( $matches[3] );
					$vid       = $this->blip_details( $vid, $oldvid, $thumb );

					// TODO: embedder uses an embed id but to fetch the details
					// requires post_id or url. Need to find the link between them
					// [bliptv id="hdljgdbVBwI"]
					// http://blip.tv/rss/view/3516963
					// http://blip.tv/day9tv/day-9-daily-101-kawaii-rice-tvp-style-3516963
					// if( isset( $embedder['id'] ) && $embedder['id'] ) {
					// $vid['id'] = trim( $embedder['id'] );
					// $vid       = $this->blip_details( $vid, $oldvid, $thumb );
					// }

					break;
				case 'blip.tv':
					if ( preg_match( '|posts_id=(\d+)|', $matches[3], $match ) ) {
						$vid['id'] = $match[1];
						$vid       = $this->blip_details( $vid, $oldvid, $thumb );
					}
					break;
				case 'cincopa':
					if ( $embedder ) {
						$vid['id'] = $embedder[0];
						$vid       = $this->cincopa_details( $vid, $oldvid, $thumb );
					}
					break;
				case 'dailymotion':
					// TODO: SSL certificate problem, verify that the CA cert is OK. Details:
					// error:14090086:SSL routines:SSL3_GET_SERVER_CERTIFICATE:certificate verify failed

					if ( isset( $embedder['id'] ) && $embedder['id'] ) {
						$vid['id'] = $embedder['id'];
					} else {
						if ( ! empty( $matches[5] ) ) {
							$vid['url'] = $matches[5];
						} else {
							if ( ! empty( $matches[3] ) ) {
								$url_or_id = trim( $matches[3] );
								if ( strpos( $url_or_id, 'http' ) === 0 ) {
									$vid['url'] = $url_or_id;
								} else {
									$vid['id'] = $url_or_id;
								}
							}
						}
					}
					$vid = $this->dailymotion_details( $vid, $oldvid, $thumb );
					break;
				case 'embedplusvideo':
					if ( preg_match( '/standard=(\'|")(.*?)\1/', $matches[3], $match ) ) {
						$vid['url'] = $match[2];
						$vid        = $this->youtube_details( $vid, $oldvid, $thumb );
					}
					break;
				case 'ez_video':
					if ( $embedder['player'] == 'flowplayer' ) {
						$vid['type'] = 'flowplayer';
					} else {
						if ( $embedder['player'] == 'jwplayer' ) {
							$vid['type'] = 'jwplayer';
						}
					}

					$vid['url']           = $embedder['url'];
					$vid['player_loc']    = $embedder['url'];
					$vid['thumbnail_loc'] = isset( $embedder['splash'] ) ? $embedder['splash'] : '';
					$vid['width']         = $embedder['width'];
					$vid['height']        = $embedder['height'];
					break;
				case 'ez_youtube':
					$vid['url'] = $embedder['url'];
					$vid        = $this->youtube_details( $vid, $oldvid, $thumb );
					break;
				case 'ez_vimeo':
					$vid['url'] = $embedder['url'];
					$vid        = $this->vimeo_details( $vid, $oldvid, $thumb );
					break;
				case 'flickrvideo':
					$vid['url'] = $matches[5];
					$vid        = $this->flickr_details( $vid, $oldvid, $thumb );
					break;
				case 'flowplayer':
				case 'fvplayer':
					if ( preg_match( '/src=(\'|")?((.*?)\.(mpg|mpeg|mp4|m4v|mov|wmv|asf|avi|ra|ram|rm|flv|swf))\1?/', $matches[0], $match ) ) {
						$vid['content_loc'] = $match[2];

						if ( preg_match( '/splash=(\'|")?((.*?)\.(jpg|png|gif))\1?/', $matches[0], $match ) ) {
							$vid['thumbnail_loc'] = $match[2];
						}
						$vid['type'] = 'jwplayer';
					}
					break;
				case 'flv':
					$vid['content_loc']   = $matches[5];
					$vid['player_loc']    = plugins_url( '/vipers-video-quicktags/resources/jw-flv-player/player.swf?file=' . urlencode( $matches[5] ) );
					$vid['thumbnail_loc'] = $thumb;
					$vid['id']            = md5( $matches[5] );
					$vid['type']          = 'flv';
					break;
				case 'jwplayer':
					$vid['type'] = 'jwplayer';
					if ( preg_match( '/mediaid=(\'|")?(\d+)\1?/', $matches[0], $match ) ) {
						$vid['content_loc']   = WP_CONTENT_URL . '/uploads/' . get_attached_file( $match[2] );
						$vid['duration']      = get_post_meta( $match[2], 'jwplayermodule_duration', true );
						$vid['thumbnail_loc'] = get_post_meta( $match[2], 'jwplayermodule_thumbnail_url', true );
					} else {
						if ( preg_match( '/html5_file=(\'|")?(.*?)\1?/', $matches[0], $match ) ) {
							$vid['content_loc'] = $match[2];
						} else {
							if ( preg_match( '/file=(\'|")?(.*?)\1?/', $matches[0], $match ) ) {
								$vid['content_loc'] = $match[2];
							}
						}
						if ( isset( $vid['content_loc'] ) ) {
							preg_match( '/image=(\'|")?(.*?)\1?/', $matches[0], $match );
							$vid['thumbnail_loc'] = $match[2];
						}
					}
					break;
				case 'lyte':
					if ( preg_match( '/id=(\'|")(.*?)\1/', $matches[0], $match ) ) {
						$vid['type'] = 'youtube';
						$vid['url']  = $match[2];
						$vid         = $this->youtube_details( $vid );
					}
					break;
				case 'metacafe':
					if ( isset( $embedder['id'] ) && $embedder['id'] ) {
						$vid['id'] = $embedder['id'];
					} else {
						if ( ! empty( $matches[5] ) ) {
							$vid['url'] = $matches[5];
						} else {
							if ( ! empty( $matches[3] ) ) {
								$vid['id'] = trim( $matches[3] );
							}
						}
					}
					$vid = $this->metacafe_details( $vid, $oldvid, $thumb );
					break;
				case 'pb_vidembed':
					if ( preg_match( '/url=(\'|")(.*?)\1/', $matches[0], $match ) ) {
						$vid['url'] = $match[2];
						if ( strpos( $vid['url'], 'youtube' ) ) {
							$vid = $this->youtube_details( $vid, $oldvid, $thumb );
						} else {
							if ( strpos( $vid['url'], 'vimeo' ) ) {
								$vid = $this->vimeo_details( $vid, $oldvid, $thumb );
							} else {
								$vid['content_loc'] = $vid['url'];
								$vid['type']        = 'pb_embed';
							}
						}
					}
					break;
				case 'sublimevideo':
				case 'sublimevideo-lightbox':
					if ( preg_match( '/src1=(\'|")(\(hd\))?(.*?)\1/', $matches[0], $match ) ) {
						$vid['content_loc'] = $match[3];
						if ( preg_match( '/poster=(\'|")(.*?)\1/', $matches[0], $match ) ) {
							$vid['thumbnail_loc'] = $match[2];
						}

						$vid['type'] = 'Sublime';
					}
					break;
				case 'tube':
					$vid['url'] = $matches[5];
					$vid        = $this->youtube_details( $vid, $oldvid, $thumb );
					break;
				case 'tubepress':
					if ( preg_match( '/.*video=(\'|")([0-9a-zA-Z\-_]+)\1.*/', $matches[0], $match ) ) {
						$vid['id'] = $match[2];
						$vid       = $this->youtube_details( $vid, $oldvid, $thumb );
					}
					break;
				case 'veoh':
					if ( isset( $embedder['id'] ) && $embedder['id'] ) {
						$vid['id'] = $embedder['id'];
					} else {
						if ( ! empty( $matches[5] ) ) {
							$vid['url'] = $matches[5];
						} else {
							if ( ! empty( $matches[3] ) ) {
								$vid['id'] = trim( $matches[3] );
							}
						}
					}
					$vid = $this->veoh_details( $vid, $oldvid, $thumb );
					break;
				case 'viddler':
					if ( isset( $embedder['id'] ) && $embedder['id'] ) {
						$vid['id'] = $embedder['id'];
						$vid       = $this->viddler_details( $vid, $oldvid, $thumb );
					} else {
						if ( preg_match( '/.*id=([^&]+).*/', $matches[0], $match ) ) {
							$vid['id'] = $match[1];
							$vid       = $this->viddler_details( $vid, $oldvid, $thumb );
						}
					}
					break;
				case 'video':
				case 'videojs':
					if ( preg_match( '/(mp4|name|ogg|webm|src)=(\'|")(.*?)\2/', $matches[3], $match ) ) {

						$src         = $match[3];
						$vid['type'] = 'mediaelement-js';

						// If the src has an extension, use it as content_loc, otherwise, see if we can find the file
						if ( substr( $src, 0, 1 ) == '/' ) {
							$info               = parse_url( get_site_url() );
							$vid['content_loc'] = $info['scheme'] . '://' . $info['host'] . $src;
						} else {
							$vid['content_loc'] = $src;
						}

						// If a poster image was specified, use that, otherwise, try and find a suitable .jpg
						if ( preg_match( '/poster=(\'|")(.*?)\1/', $matches[3], $match ) ) {
							if ( substr( $match[2], 0, 1 ) == '/' ) {
								$info                 = parse_url( get_site_url() );
								$vid['thumbnail_loc'] = $info['scheme'] . '://' . $info['host'] . $match[2];
							} else {
								$vid['thumbnail_loc'] = $match[2];
							}
						} else {
							$img_file = preg_replace( '/\.(mpg|mpeg|mp4|m4v|mov|wmv|asf|avi|ra|ram|rm|flv|swf)/', '.jpg', $vid['content_loc'] );
							if ( file_exists( $img_file ) ) {
								$vid['thumbnail_loc'] = $img_file;
							}
						}

					}
					break;
				case 'video_lightbox_vimeo5':
					if ( preg_match( '/video_id=(\'|")?(\d+)\1?/', $matches[0], $match ) ) {
						$vid['id'] = $match[2];
						$vid       = $this->vimeo_details( $vid, $oldvid, $thumb );
					}
					break;
				case 'video_lightbox_youtube':
					if ( preg_match( '/video_id=(\'|")?([0-9a-zA-Z\-_]+)\1?/', $matches[0], $match ) ) {
						$vid['id'] = $match[2];
						$vid       = $this->youtube_details( $vid, $oldvid, $thumb );
					}
					break;
				case 'vimeo':
					if ( isset( $embedder['id'] ) && $embedder['id'] ) {
						$vid['id'] = $embedder['id'];
					} else {
						if ( ! empty( $matches[5] ) ) {
							$vid['url'] = $matches[5];
						} else {
							if ( ! empty( $matches[3] ) ) {
								$vid['id'] = trim( $matches[3] );
							} else {
								if ( preg_match( '/id=(\'|")?([0-9]+)\1?/', $matches[0], $match ) ) {
									$vid['id'] = trim( $match[2] );
								}
							}
						}
					}
					$vid = $this->vimeo_details( $vid, $oldvid, $thumb );
					break;
				case 'vippy':
					$atts      = shortcode_parse_atts( $matches[3] );
					$vid['id'] = isset( $atts['id'] ) ? $atts['id'] : 0;
					$vid       = $this->vippy_details( $vid, $oldvid, $thumb );
					break;
				case 'vzaarmedia':
					$atts      = shortcode_parse_atts( $matches[3] );
					$vid['id'] = isset( $atts['vid'] ) ? $atts['vid'] : 0;
					if ( $vid['id'] != 0 ) {
						$vid = $this->vzaar_details( $vid, $oldvid, $thumb );
					}
					break;
				case 'wpvideo':
				case 'videopress':
					if ( preg_match( '/^([^\s]+)/', trim( $matches[3] ), $match ) ) {
						$vid['id'] = $match[1];
						$vid       = $this->videopress_details( $vid, $oldvid, $thumb );
					}
					break;
				case 'weaver_vimeo':
					// [weaver_vimeo vimeo-url id=videoid sd=0 percent=100 ratio=0.5625 center=1 color=#hex autoplay=0 loop=0 portrait=1 title=1 byline=1]
					if ( preg_match( '/\[weaver_vimeo (https?:\/\/(www\.)?vimeo\.com\/\d+)/', $matches[0], $match ) ) {
						$vid['url'] = $match[1];
					} else {
						if ( preg_match( '/id=(\d+)/', $matches[3], $match ) ) {
							$vid['id'] = $match[1];
						} else {
							break;
						}
					}

					$vid = $this->vimeo_details( $vid, $oldvid, $thumb );
					break;
				case 'weaver_youtube':
					if ( preg_match( '/\[weaver_youtube (http[^\s]+)/', $matches[0], $match ) ) {
						$vid['url'] = $match[1];
					} else {
						if ( preg_match( '/id=([^\s]+)/', $matches[3], $match ) ) {
							$vid['id'] = $match[1];
						} else {
							break;
						}
					}
					$vid = $this->youtube_details( $vid, $oldvid, $thumb );
					break;
				case 'wposflv':
					if ( class_exists( 'WPOSFLV' ) ) {
						$vid['type'] = 'wposflv';

						// If the src has an extension, use it as content_loc, otherwise, see if we can find the file
						$src = $embedder['src'];
						if ( substr( $src, strlen( $src ) - 4, 1 ) == '.' ) {
							$vid['content_loc'] = $src;
						} else {
							if ( substr( $src, 0, 4 ) != 'http' ) {
								$filename = WP_CONTENT_DIR . substr( $src, strlen( WP_CONTENT_DIR ) - strrpos( WP_CONTENT_DIR, '/' ) );
							} else {
								$filename = WP_CONTENT_DIR . substr( $src, strlen( WP_CONTENT_URL ) );
							}

							if ( file_exists( $filename . '.mp4' ) ) {
								$vid['content_loc'] = $src . '.mp4';
							} elseif ( file_exists( $filename . '.m4v' ) ) {
								$vid['content_loc'] = $src . '.m4v';
							}

						}

						if ( isset( $embedder['width'] ) && $embedder['width'] ) {
							$vid['width'] = $embedder['width'];
						}

						if ( isset( $embedder['height'] ) && $embedder['height'] ) {
							$vid['height'] = $embedder['height'];
						}


						if ( $thumb != '' ) {
							$vid['thumbnail_loc'] = $thumb;
						} else {
							// If a thumbnail image was specified in the shortcode, use that, otherwise, try and find a suitable .jpg
							$thumbnail = $embedder['previewimage'];
							if ( $thumbnail ) {
								$vid['thumbnail_loc'] = $thumbnail;
							} else {
								$img_file = preg_replace( '/\.(mpg|mpeg|mp4|m4v|mov|wmv|asf|avi|ra|ram|rm|flv|swf)/', '.jpg', $vid['content_loc'] );
								if ( file_exists( $img_file ) ) {
									$vid['thumbnail_loc'] = $img_file;
								}
							}
						}
					}
					break;
				case 'youtube':
				case 'youtubewd':
				case 'youtube-embed':
				case 'youtube-white-label':
					if ( isset( $embedder['id'] ) && $embedder['id'] ) {
						$vid['id'] = $embedder['id'];
						$vid       = $this->youtube_details( $vid, $oldvid, $thumb );
					} else {
						if ( ! empty( $matches[5] ) ) {
							if ( preg_match( '/^([0-9a-zA-Z\-_]+)$/', $matches[5] ) ) {
								$vid['id'] = $matches[5];
							} else {
								$vid['url'] = $matches[5];
							}
							$vid = $this->youtube_details( $vid, $oldvid, $thumb );
						} else {
							if ( preg_match( '/id=(\'|")?([0-9a-zA-Z\-_]+)\1?/', $matches[0], $match ) ) {
								$vid['id'] = trim( $match[2] );
								$vid       = $this->youtube_details( $vid, $oldvid, $thumb );
							} else {
								if ( preg_match( '/v=([0-9a-zA-Z\-_]+)/', $matches[0], $match ) ) {
									$vid['id'] = $match[1];
									$vid       = $this->youtube_details( $vid, $oldvid, $thumb );
								}
							}
						}
					}
					break;
				case 'youtube_sc':
					if ( preg_match( '/(url|v|video)=(\'|")(.*?)\2/', $matches[3], $match ) ) {
						$vid['url'] = $match[3];
						$vid        = $this->youtube_details( $vid, $oldvid, $thumb );
					}
					break;
				default:
					if ( WP_DEBUG ) {
						echo '<pre>' . print_r( $matches, 1 ) . '</pre>';
						echo '<pre>' . print_r( $vid, 1 ) . '</pre>';
					}
					$vid = false;
					break;
			}
			if ( isset( $vid['content_loc'] ) || isset( $vid['player_loc'] ) ) {
				$vid = apply_filters( 'wpseo_video_' . $vid['type'] . '_details', $vid );

				return $vid;
			}
		}

		if ( ! isset( $vid['id'] ) && $oembed = $this->grab_embeddable_urls_xpath( $content ) ) {
			foreach ( $oembed as $type => $url ) {
				$vid['url'] = $url;
				switch ( $type ) {
					case 'animoto':
						$vid = $this->animoto_details( $vid, $oldvid );
						break;
					case 'brightcove':
						$vid = $this->brightcove_details( $vid, $oldvid );
						break;
					case 'cincopa':
						$vid = $this->cincopa_details( $vid, $oldvid );
						break;
					case 'screencast':
						$vid = $this->screencast_details( $vid, $oldvid );
						break;
					case 'vidyard':
						$vid = $this->vidyard_details( $vid, $oldvid );
						break;
				}

				if ( isset( $vid['content_loc'] ) || isset( $vid['player_loc'] ) ) {
					$vid = apply_filters( 'wpseo_video_' . $vid['type'] . '_details', $vid );

					return $vid;
				}
			}
		}

		if ( ! isset( $vid['id'] ) && $oembed = $this->grab_embeddable_urls( $content ) ) {
			foreach ( $oembed as $type => $url ) {
				$vid['url'] = $url;
				switch ( $type ) {
					case 'animoto':
						$vid = $this->animoto_details( $vid, $oldvid );
						break;
					case 'blip':
						$vid = $this->blip_details( $vid, $oldvid );
						break;
					case 'brightcove':
						$vid = $this->brightcove_details( $vid, $oldvid );
						break;
					case 'dailymotion':
						$vid = $this->dailymotion_details( $vid, $oldvid );
						break;
					case 'evs':
						$vid = $this->evs_details( $vid, $oldvid );
						break;
					case 'flickr':
						$vid = $this->flickr_details( $vid, $oldvid );
						break;
					case 'muzutv':
						$vid = $this->muzutv_details( $vid, $oldvid );
						break;
					case 'screenr':
						$vid = $this->screenr_details( $vid, $oldvid );
						break;
					case 'viddler':
						$vid = $this->viddler_details( $vid, $oldvid );
						break;
					case 'vimeo':
						$vid = $this->vimeo_details( $vid, $oldvid );
						break;
					case 'vzaar':
						$vid = $this->vzaar_details( $vid, $oldvid );
						break;
					case 'wistia':
						$vid = $this->wistia_details( $vid, $oldvid );
						break;
					case 'wordpress.tv':
						$vid = $this->wordpresstv_details( $vid, $oldvid );
						break;
					case 'youtube':
						$vid = $this->youtube_details( $vid, $oldvid );
						break;
				}

				if ( isset( $vid['content_loc'] ) || isset( $vid['player_loc'] ) ) {
					$vid = apply_filters( 'wpseo_video_' . $vid['type'] . '_details', $vid );

					return $vid;
				}
			}
		}

		// support for wordpress-automatic-youtube-video-post plugin
		if ( is_plugin_active( 'automatic-youtube-video-posts/tern_wp_youtube.php' ) ) {
			$youtube_id = get_post_meta( $vid['post_id'], '_tern_wp_youtube_video', true );

			if ( $youtube_id ) {
				$vid['id'] = $youtube_id;
				$vid       = $this->youtube_details( $vid, $oldvid );
			}

			if ( isset( $vid['content_loc'] ) || isset( $vid['player_loc'] ) ) {
				$vid = apply_filters( 'wpseo_video_' . $vid['type'] . '_details', $vid );

				return $vid;
			}
		}
		$shortcode_tags = $old_shortcode_tags;

		return 'none';
	}

	/**
	 * Check and, if applicable, update video details for a term description
	 *
	 * @since 1.3
	 *
	 * @param object  $term The term to check the description and possibly update the video details for.
	 * @param boolean $echo Whether or not to echo the performed actions.
	 *
	 * @return mixed $vid The video array that was just stored, or "none" if nothing was stored.
	 */
	function update_video_term_meta( $term, $echo = false ) {
		$options = array_merge( WPSEO_Options::get_all(), get_option( 'wpseo_video' ) );

		if ( ! is_array( $options['videositemap_taxonomies'] ) || $options['videositemap_taxonomies'] === array() ) {
			return false;
		}

		if ( ! in_array( $term->taxonomy, $options['videositemap_taxonomies'] ) ) {
			return false;
		}

		$tax_meta = get_option( 'wpseo_taxonomy_meta' );
		$oldvid   = array();
		if ( ! isset( $_POST['force'] ) ) {
			if ( isset( $tax_meta[$term->taxonomy]['_video'][$term->term_id] ) ) {
				$oldvid = $tax_meta[$term->taxonomy]['_video'][$term->term_id];
			}
		}

		$vid = array();

		$title = WPSEO_Taxonomy_Meta::get_term_meta( $term->term_id, $term->taxonomy, 'wpseo_title' );
		if ( empty( $title ) && isset( $options['title-' . $term->taxonomy] ) && $options['title-' . $term->taxonomy] !== '' ) {
			$title = wpseo_replace_vars( $options['title-' . $term->taxonomy], (array) $term );
		}
		if ( empty( $title ) ) {
			$title = $term->name;
		}
		$vid['title'] = htmlspecialchars( $title );

		$vid['description'] = WPSEO_Taxonomy_Meta::get_term_meta( $term->term_id, $term->taxonomy, 'wpseo_metadesc' );
		if ( ! $vid['description'] ) {
			$vid['description'] = htmlspecialchars( substr( preg_replace( '/\s+/', ' ', strip_tags( $this->strip_shortcodes( get_term_field( 'description', $term->term_id, $term->taxonomy ) ) ) ), 0, 300 ) );
		}

		$vid['publication_date'] = date( "Y-m-d\TH:i:s+00:00" );

		// concatenate genesis intro text and term description to index the videos for both
		$genesis_term_meta = get_option( 'genesis-term-meta' );

		$content = '';
		if ( isset( $genesis_term_meta[$term->term_id]['intro_text'] ) && $genesis_term_meta[$term->term_id]['intro_text'] ) {
			$content .= $genesis_term_meta[$term->term_id]['intro_text'];
		}

		$content .= "\n" . $term->description;
		$content = stripslashes( $content );

		$vid = $this->index_content( $content, $vid, $oldvid );

		if ( $vid != 'none' ) {
			$tax_meta[$term->taxonomy]['_video'][$term->term_id] = $vid;
			// Don't bother with the complete tax meta validation
			$tax_meta['wpseo_already_validated'] = true;
			update_option( 'wpseo_taxonomy_meta', $tax_meta );

			if ( $echo ) {
				$link = get_term_link( $term );
				if ( ! is_wp_error( $link ) ) {
					echo 'Updated <a href="' . $link . '">' . $vid['title'] . '</a> - ' . $vid['type'] . '<br/>';
				}
			}
		}

		return $vid;
	}


	/**
	 * (Don't) validate the _video taxonomy meta data array
	 * Doesn't actually validate it atm, but having this function hooked in *does* make sure that the
	 * _video taxonomy meta data is not removed as it otherwise would be (by the normal taxonomy meta validation).
	 *
	 * @since 1.6
	 *
	 * @param  array $tax_meta_data Received _video tax meta data
	 *
	 * @return  array  Validated _video tax meta data
	 */
	function validate_video_tax_meta( $tax_meta_data ) {
		return $tax_meta_data;
	}

	/**
	 * Returns the custom fields to check for posts.
	 *
	 * @param int $post_id The ID of the post to grab the custom fields for.
	 *
	 * @since 1.3.4
	 *
	 * @return array $custom_fields Array of custom field values.
	 */
	function get_custom_fields( $post_id ) {
		$custom_fields = array(
				'videoembed', // Press75 Simple Video Embedder
				'_videoembed_manual', // Press75 Simple Video Embedder
				'_videoembed', // Press75 Simple Video Embedder
				'_premise_settings', // Premise
		);
		$options       = get_option( 'wpseo_video' );
		if ( $options['custom_fields'] !== '' ) {
			$setting       = (array) explode( ',', $options['custom_fields'] );
			$setting       = array_map( 'trim', $setting );
			$custom_fields = array_merge( $custom_fields, $setting );
		}

		$values = array();
		foreach ( (array) $custom_fields as $cf ) {
			$meta_val = get_post_meta( $post_id, $cf, true );
			if ( is_array( $meta_val ) ) {
				foreach ( $meta_val as $val ) {
					$values[] = $val;
				}
			} else {
				$values[] = $meta_val;
			}
		}

		return $values;
	}

	/**
	 * Check and, if applicable, update video details for a post
	 *
	 * @since 0.1
	 *
	 * @param object  $post The post to check and possibly update the video details for.
	 * @param boolean $echo Whether or not to echo the performed actions.
	 *
	 * @return mixed $vid The video array that was just stored, or "none" if nothing was stored.
	 */
	function update_video_post_meta( $post, $echo = false ) {
		global $wp_query;

		if ( is_numeric( $post ) ) {
			$post = get_post( $post );
		}

		if ( ! isset( $post->ID ) ) {
			return false;
		}

		$options = array_merge( WPSEO_Options::get_all(), get_option( 'wpseo_video' ) );

		if ( ! is_array( $options['videositemap_posttypes'] ) || $options['videositemap_posttypes'] === array() ) {
			return false;
		}

		if ( ! in_array( $post->post_type, $options['videositemap_posttypes'] ) ) {
			return false;
		}

		$_GLOBALS['post'] = $post;

		$oldvid = array();
		if ( ! isset( $_POST['force'] ) ) {
			$oldvid = WPSEO_Meta::get_value( 'video_meta', $post->ID );
		}

		$title = WPSEO_Meta::get_value( 'title', $post->ID );
		if ( ( ! is_string( $title ) || $title === '' ) && isset( $options['title-' . $post->post_type] ) && $options['title-' . $post->post_type] !== '' ) {
			$title = wpseo_replace_vars( $options['title-' . $post->post_type], (array) $post );
		} else {
			if ( ( ! is_string( $title ) || $title === '' ) && ( ! isset( $options['title-' . $post->post_type] ) || $options['title-' . $post->post_type] === '' ) ) {
				$title = wpseo_replace_vars( "%%title%% - %%sitename%%", (array) $post );
			}
		}

		if ( ! is_string( $title ) || $title === '' ) {
			$title = $post->post_title;
		}

		$vid = array();

		if ( $post->post_type == 'post' ) {
			$wp_query->is_single = true;
			$wp_query->is_page   = false;
		} else {
			$wp_query->is_single = false;
			$wp_query->is_page   = true;
		}

		$vid['post_id'] = $post->ID;

		$vid['title']            = htmlspecialchars( $title );
		$vid['publication_date'] = mysql2date( "Y-m-d\TH:i:s+00:00", $post->post_date_gmt );

		$vid['description'] = WPSEO_Meta::get_value( 'metadesc', $post->ID );
		if ( ! is_string( $vid['description'] ) || $vid['description'] === '' ) {
			$vid['description'] = htmlspecialchars( substr( preg_replace( '/\s+/', ' ', strip_tags( $this->strip_shortcodes( $post->post_content ) ) ), 0, 300 ) );
		}

		$content = $post->post_content;
		$index   = true;
		if ( $custom_fields = $this->get_custom_fields( $post->ID ) ) {
			foreach ( $custom_fields as $cf_val ) {
				if ( is_array( $cf_val ) ) {
					$cf_val = $cf_val[0];
				}

				// Silly, silly themes _encode_ the value of the post meta field. Yeah it's ridiculous. But this fixes it.
				if ( strpos( $cf_val, '&lt;' ) !== false ) {
					$cf_val = html_entity_decode( $cf_val );
				}

				if ( preg_match( '/\.(mpg|mpeg|mp4|m4v|mov|wmv|asf|avi|ra|ram|rm|flv|swf)$/', $cf_val ) ) {
					$vid['content_loc'] = $cf_val;
					$vid['type']        = 'custom_field';

					$index = false;
				} else {
					$content .= "\n" . $cf_val . "\n";
				}
			}
		}

		if ( $index ) {
			$vid = $this->index_content( $content, $vid, $oldvid );
		}

		if ( $vid == false || ! is_array( $vid ) ) {
			$vid = 'none';
		}

		if ( 'none' != $vid ) {
			if ( ! isset( $vid['thumbnail_loc'] ) || empty( $vid['thumbnail_loc'] ) ) {
				$img = wp_get_attachment_image_src( get_post_thumbnail_id( $post->ID ), 'single-post-thumbnail' );
				if ( strpos( $img[0], 'http' ) !== 0 ) {
					$vid['thumbnail_loc'] = get_site_url( null, $img[0] );
				} else {
					$vid['thumbnail_loc'] = $img[0];
				}
			}

			// Grab the meta data from the post
			if ( isset( $_POST['yoast_wpseo_videositemap-category'] ) && ! empty( $_POST['yoast_wpseo_videositemap-category'] ) ) {
				$vid['category'] = $_POST['yoast_wpseo_videositemap-category'];
			} else {
				$cats = wp_get_object_terms( $post->ID, 'category', array( 'fields' => 'names' ) );
				if ( isset( $cats[0] ) ) {
					$vid['category'] = $cats[0];
				}
				unset( $cats );
			}

			$tags = wp_get_object_terms( $post->ID, 'post_tag', array( 'fields' => 'names' ) );

			if ( isset( $_POST['yoast_wpseo_videositemap-tags'] ) && ! empty( $_POST['yoast_wpseo_videositemap-tags'] ) ) {
				$extra_tags = explode( ',', $_POST['yoast_wpseo_videositemap-tags'] );
				$tags       = array_merge( $extra_tags, $tags );
			}

			$tag = array();
			if ( is_array( $tags ) ) {
				foreach ( $tags as $t ) {
					$tag[] = $t;
				}
			} else {
				if ( isset( $cats[0] ) ) {
					$tag[] = $cats[0]->name;
				}
			}

			$focuskw = WPSEO_Meta::get_value( 'focuskw', $post->ID );
			if ( ! empty( $focuskw ) ) {
				$tag[] = $focuskw;
			}
			$vid['tag'] = $tag;

			if ( $echo ) {
				echo 'Updated <a href="' . home_url( '?p=' . $post->ID ) . '">' . $post->post_title . '</a> - ' . $vid['type'] . '<br/>';
			}
		}

		WPSEO_Meta::set_value( 'video_meta', $vid, $post->ID );

//		echo '<pre>'.print_r( $_POST,1 ).'</pre>';
		return $vid;
	}

	/**
	 * Remove both used and unused shortcodes from content.
	 *
	 * @since 1.3.3
	 *
	 * @param string $content Content to remove shortcodes from.
	 *
	 * @return string
	 */
	function strip_shortcodes( $content ) {
		$content = preg_replace( '|\[(.+?)\](.+?\[/\\1\])?|s', '', $content );

		return $content;
	}

	/**
	 * Check whether the current visitor is really Google or Bing's bot by doing a reverse DNS lookup
	 *
	 * @since 1.2.2
	 *
	 * @return boolean
	 */
	function is_valid_bot() {
		if ( preg_match( "/(Google|bing)bot/", $_SERVER['HTTP_USER_AGENT'], $match ) ) {
			$hostname = gethostbyaddr( $_SERVER['REMOTE_ADDR'] );

			if (
					( $match[1] == 'Google' && preg_match( "/googlebot\.com$/", $hostname ) && gethostbyname( $hostname ) == $_SERVER['REMOTE_ADDR'] ) ||
					( $match[1] == 'bing' && preg_match( "/search\.msn\.com$/", $hostname ) && gethostbyname( $hostname ) == $_SERVER['REMOTE_ADDR'] )
			) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Check to see if the video thumbnail was updated, if so, update the $video array.
	 *
	 * @param int   $post_id The post to check for.
	 * @param array $video   The video array.
	 *
	 * @return array
	 */
	function get_video_image( $post_id, $video ) {
		// Allow for the video's thumbnail to be overridden by the meta box input
		$videoimg = WPSEO_Meta::get_value( 'videositemap-thumbnail', $post_id );
		if ( $videoimg !== '' ) {
			$video['thumbnail_loc'] = $videoimg;
		}

		return $video;
	}

	/**
	 * Outputs the XSL file
	 */
	function build_video_sitemap_xsl() {

		// Force a 200 header and replace other status codes.
		header( 'HTTP/1.1 200 OK', true, 200 );

		// Set the right content / mime type
		header( 'Content-Type: text/xml' );

		// Prevent the search engines from indexing the XML Sitemap.
		header( 'X-Robots-Tag: noindex, follow', true );

		// Make the browser cache this file properly.
		header( 'Pragma: public' );
		header( 'Cache-Control: maxage=31536000' );
		header( 'Expires: ' . gmdate( 'D, d M Y H:i:s', time() + 31536000 ) . ' GMT' );

		require plugin_dir_path( __FILE__ ) . 'xml-video-sitemap.php';
		die();
	}

	/**
	 * The main function of this class: it generates the XML sitemap's contents.
	 *
	 * @since 0.1
	 */
	function build_video_sitemap() {
		$options = get_option( 'wpseo_video' );

		// Restrict access to the video sitemap to admins and valid bots
		if ( $options['cloak_sitemap'] === true && ( ! current_user_can( 'manage_options' ) && ! $this->is_valid_bot() ) ) {
			header( 'HTTP/1.0 403 Forbidden', true, 403 );
			wp_die( "We're sorry, access to our video sitemap is restricted to site admins and valid Google & Bing bots." );
		}

		// Force a 200 header and replace other status codes.
		header( 'HTTP/1.1 200 OK', true, 200 );

		$output = '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:video="http://www.google.com/schemas/sitemap-video/1.1">' . "\n";

		$printed_post_ids = array();

		$steps  = 5;
		$n      = (int) get_query_var( 'sitemap_n' );
		$offset = ( $n > 1 ) ? ( $n - 1 ) * $this->max_entries : 0;
		$total  = $offset + $this->max_entries;

		if ( is_array( $options['videositemap_posttypes'] ) && $options['videositemap_posttypes'] !== array() ) {
			// Set the initial args array to get videos in chunks
			$args = array(
					'post_type'      => $options['videositemap_posttypes'],
					'post_status'    => 'publish',
					'posts_per_page' => $steps,
					'offset'         => $offset,
					'meta_key'       => '_yoast_wpseo_video_meta',
					'meta_compare'   => '!=',
					'meta_value'     => 'none',
					'order'          => 'ASC',
					'orderby'        => 'post_modified'
			);

			// TODO: add support to tax video to honor pages
			/*
				add a bool to the while loop to see if tax as been processed
				if $items is empty the posts are done so move on to tax

				do some math between $printed_post_ids and $this-max_entries to figure out how many from tax to add to this pagination
			*/

			// Add entries to the sitemap until the total is hit (rounded up by nearest $steps)
			while ( ( $total > $offset ) && ( $items = get_posts( $args ) ) ) {

				if ( ! empty( $items ) ) {
					foreach ( (array) $items as $item ) {
						if ( in_array( $item->ID, $printed_post_ids ) ) {
							continue;
						} else {
							$printed_post_ids[] = $item->ID;
						}

						if ( WPSEO_Meta::get_value( 'meta-robots-noindex', $item->ID ) == '1' ) {
							continue;
						}

						$disable = WPSEO_Meta::get_value( 'videositemap-disable', $item->ID );
						if ( $disable === 'on' ) {
							continue;
						}

						$video = WPSEO_Meta::get_value( 'video_meta', $item->ID );

						$video = $this->get_video_image( $item->ID, $video );

						// When we don't have a thumbnail and either a player_loc or a content_loc, skip this video.
						if (
								! isset( $video['thumbnail_loc'] )
								|| ( ! isset( $video['player_loc'] ) && ! isset( $video['content_loc'] ) )
						) {
							continue;
						}

						$video_duration = WPSEO_Meta::get_value( 'videositemap-duration', $item->ID );
						if ( $video_duration > 0 ) {
							$video['duration'] = $video_duration;
						}

						$video['permalink'] = get_permalink( $item );

						$rating = apply_filters( 'wpseo_video_rating', WPSEO_Meta::get_value( 'videositemap-rating', $item->ID ) );
						if ( $rating && WPSEO_Meta_Video::sanitize_rating( null, $rating, WPSEO_Meta_Video::$meta_fields['video']['videositemap-rating'] ) ) {
							$video['rating'] = number_format( $rating, 1 );
						}

						$not_family_friendly = apply_filters( 'wpseo_video_family_friendly', WPSEO_Meta::get_value( 'videositemap-not-family-friendly', $item->ID ), $item->ID );
						if ( is_string( $not_family_friendly ) && $not_family_friendly === 'on' ) {
							$video['family_friendly'] = 'no';
						} else {
							$video['family_friendly'] = 'yes';
						}

						$video['author'] = $item->post_author;

						$output .= $this->print_sitemap_line( $video );
					}
				}

				// Update these args for the next iteration
				$offset = $offset + $steps;
				$args['offset'] += $steps;
			}
		}

		$tax_meta = get_option( 'wpseo_taxonomy_meta' );
		$terms    = array();
		if ( is_array( $options['videositemap_taxonomies'] ) && $options['videositemap_taxonomies'] !== array() ) {
			// Below is a fix for a nasty bug in WooCommerce: https://github.com/woothemes/woocommerce/issues/3807
			$options['videositemap_taxonomies'][0] = '';
			$terms                                 = get_terms( $options['videositemap_taxonomies'] );
		}

		foreach ( $terms as $term ) {
			if ( is_object( $term ) && isset( $tax_meta[$term->taxonomy]['_video'][$term->term_id] ) ) {
				$video = $tax_meta[$term->taxonomy]['_video'][$term->term_id];
				if ( is_array( $video ) ) {
					$video['permalink'] = get_term_link( $term, $term->taxonomy );
					$video['category']  = $term->name;
					$output .= $this->print_sitemap_line( $video );
				}
			}
		}

		$output .= '</urlset>';
		$GLOBALS['wpseo_sitemaps']->set_sitemap( $output );
		$GLOBALS['wpseo_sitemaps']->set_stylesheet( "\n" . '<?xml-stylesheet type="text/xsl" href="' . home_url( 'video-sitemap.xsl' ) . '"?>' );
	}

	/**
	 * Print a full <url> line in the sitemap.
	 *
	 * @since 1.3
	 *
	 * @param $video array The video object to print out
	 *
	 * @return string The output generated
	 */
	function print_sitemap_line( $video ) {
		if ( ! is_array( $video ) ) {
			return '';
		}

		$output = "\t<url>\n";
		$output .= "\t\t<loc>" . htmlspecialchars( $video['permalink'] ) . '</loc>' . "\n";
		$output .= "\t\t<video:video>\n";

		foreach ( $video as $key => $val ) {
			if ( in_array( $key, array( 'id', 'url', 'type', 'permalink', 'post_id', 'hd' ) ) ) {
				continue;
			}

			if ( empty( $video['publication_date'] ) ) {
				$post = get_post( $video['post_id'] );
				if ( $post->post_date_gmt != '0000-00-00 00:00:00' ) {
					$video['publication_date'] = mysql2date( "Y-m-d\TH:i:s+00:00", $post->post_date_gmt );
				} else {
					if ( $post->post_date != '0000-00-00 00:00:00' ) {
						$video['publication_date'] = date( "Y-m-d\TH:i:s+00:00", get_gmt_from_date( $post->post_date ) );
					} else {
						return '<!-- Post with ID ' . $video['post_id'] . 'skipped, because there\'s no valid date in the DB for it. -->';
					}
				} // If we have no valid date for the post, skip the video and don't print it in the XML Video Sitemap.
			}

			if ( $key == 'author' ) {
				$output .= "\t\t\t<video:uploader info='" . get_author_posts_url( $val ) . "'>" . ent2ncr( esc_html( get_the_author_meta( 'display_name', $val ) ) ) . "</video:uploader>\n";
				continue;
			}

			$xtra = '';
			if ( $key == 'player_loc' ) {
				$xtra = ' allow_embed="yes"';
			}

			if ( $key == 'description' && empty( $val ) ) {
				$val = $video['title'];
			}

			if ( ! is_array( $val ) && ! empty ( $val ) ) {
				$val = $this->clean_string( $val );
				if ( ! empty ( $val ) ) {
					if ( in_array( $key, array( 'description', 'category', 'tag', 'title' ) ) ) {
						$val = ent2ncr( esc_html( $val ) );
					}
					$output .= "\t\t\t<video:" . $key . $xtra . ">" . $val . "</video:" . $key . ">\n";
				}
			} else {
				$i = 1;
				foreach ( (array) $val as $v ) {
					// Only 32 tags are allowed
					if ( $key == 'tag' && $i == 33 ) {
						break;
					}
					$v = $this->clean_string( $v );
					if ( in_array( $key, array( 'description', 'category', 'tag', 'title' ) ) ) {
						$v = ent2ncr( esc_html( $v ) );
					}
					if ( ! empty ( $v ) ) {
						$output .= "\t\t\t<video:" . $key . $xtra . ">" . $v . "</video:" . $key . ">\n";
					}
					$i ++;
				}
			}
		}

		// Allow custom implementations with extra tags here
		$output .= apply_filters( 'wpseo_video_item', '', isset( $video['post_id'] ) ? $video['post_id'] : 0 );

		$output .= "\t\t</video:video>\n";

		$output .= "\t</url>\n";

		return $output;
	}

	/**
	 * Cleans a string for XML display purposes.
	 *
	 * @since 1.2.1
	 *
	 * @link  http://php.net/manual/en/function.html-entity-decode.php#98697 Modified for WP from here.
	 *
	 * @param string $in     The string to clean.
	 * @param int    $offset Offset of the string to start the cleaning at.
	 *
	 * @return string Cleaned string.
	 */
	function clean_string( $in, $offset = null ) {
		$out = trim( $in );
		$out = $this->strip_shortcodes( $out );
		$out = html_entity_decode( $out, ENT_QUOTES, "ISO-8859-15" );
		$out = html_entity_decode( $out, ENT_QUOTES, get_bloginfo( 'charset' ) );
		if ( ! empty( $out ) ) {
			$entity_start = strpos( $out, '&', $offset );
			if ( $entity_start === false ) {
				// ideal
				return _wp_specialchars( $out );
			} else {
				$entity_end = strpos( $out, ';', $entity_start );
				if ( $entity_end === false ) {
					return _wp_specialchars( $out );
				} // zu lang um eine entity zu sein
				else {
					if ( $entity_end > $entity_start + 7 ) {
						// und weiter gehts
						$out = $this->clean_string( $out, $entity_start + 1 );
					} // gottcha!
					else {
						$clean = substr( $out, 0, $entity_start );
						$subst = substr( $out, $entity_start + 1, 1 );
						// &scaron; => "s" / &#353; => "_"
						$clean .= ( $subst != "#" ) ? $subst : "_";
						$clean .= substr( $out, $entity_end + 1 );
						// und weiter gehts
						$out = $this->clean_string( $clean, $entity_start + 1 );
					}
				}
			}
		}

		return _wp_specialchars( $out );
	}

	/**
	 * Roughly calculate the length of an FLV video.
	 *
	 * @since 1.3.1
	 *
	 * @param string $file The path to the video file to calculate the length for
	 *
	 * @return integer Duration of the video
	 */
	function get_flv_duration( $file ) {
		if ( $flv = fopen( $file, 'rb' ) ) {
			fseek( $flv, - 4, SEEK_END );
			$arr             = unpack( 'N', fread( $flv, 4 ) );
			$last_tag_offset = $arr[1];
			fseek( $flv, - ( $last_tag_offset + 4 ), SEEK_END );
			fseek( $flv, 4, SEEK_CUR );
			$t0                    = fread( $flv, 3 );
			$t1                    = fread( $flv, 1 );
			$arr                   = unpack( 'N', $t1 . $t0 );
			$milliseconds_duration = $arr[1];

			return $milliseconds_duration;
		} else {
			return 0;
		}
	}

	/**
	 * Outputs the admin panel for the Video Sitemaps on the XML Sitemaps page with the WP SEO admin
	 *
	 * @since 0.1
	 */
	function admin_panel() {
		$options = get_option( 'wpseo_video' );
		$xmlopt  = get_option( 'wpseo_xml' );

		if ( isset( $_GET['debug'] ) ) {
			echo '<pre>' . print_r( $options, 1 ) . '</pre>';
		}

		?>
		<div class="wrap">

			<a href="http://yoast.com/wordpress/video-seo/">
				<div id="yoast-icon"
						 style="background: url('<?php echo plugin_dir_url( __FILE__ ); ?>images/wordpress-SEO-32x32.png') no-repeat;"
						 class="icon32">
					<br />
				</div>
			</a>

			<h2 id="wpseo-title"><?php _e( "Yoast WordPress SEO: ", 'yoast-video-seo' );
				echo __( 'Video SEO Settings', 'yoast-video-seo' ); ?></h2>

			<form action="<?php echo admin_url( 'options.php' ); ?>" method="post" id="wpseo-conf">

				<?php
				settings_fields( $this->option_instance->group_name );

				if ( $xmlopt['enablexmlsitemap'] !== true ) {
					echo '<p>' . __( 'Please enable the XML sitemap under the SEO -> XML Sitemaps settings', 'yoast-video-seo' ) . '</p>';
				} else {
					echo '<h2>' . __( 'General Settings', 'yoast-video-seo' ) . '</h2>';

					if ( is_array( $options['videositemap_posttypes'] ) && $options['videositemap_posttypes'] !== array() ) {
						// Use fields => ids to limit the overhead of fetching entire post objects,
						// fetch only an array of ids instead to count
						$args          = array(
								'post_type'      => $options['videositemap_posttypes'],
								'post_status'    => 'publish',
								'posts_per_page' => - 1,
							// 'offset'         => 0,
								'meta_key'       => '_yoast_wpseo_video_meta',
								'meta_compare'   => '!=',
								'meta_value'     => 'none',
								'fields'         => 'ids'
						);
						$video_ids     = get_posts( $args );
						$count         = count( $video_ids );
						$n             = ( $count > $this->max_entries ) ? (int) ceil( $count / $this->max_entries ) : '';
						$video_lastest = str_replace( 'sitemap.xml', "sitemap$n.xml", $this->sitemap_url() );

						echo '<p>' . __( 'Please find your video sitemap here:', 'yoast-video-seo' ) . ' <a class="button" target="_blank" href="' . $video_lastest . '">' . __( 'Video Sitemap', 'yoast-video-seo' ) . '</a></p>';
					} else {
						echo '<p>' . __( 'Select at least one post type to enable the video sitemap', 'yoast-video-seo' ) . '</p>';

					}


					echo '<p><input class="checkbox double" id="cloak_sitemap" type="checkbox" name="wpseo_video[cloak_sitemap]" ' . checked( $options['cloak_sitemap'], true, false ) . '> ';
					echo '<label for="cloak_sitemap">' . __( 'Hide the sitemap from normal visitors?', 'yoast-video-seo' ) . '</label></p>';

					echo '<br class="clear"/>';

					echo '<p><input class="checkbox double" id="disable_rss" type="checkbox" name="wpseo_video[disable_rss]" ' . checked( $options['disable_rss'], true, false ) . '> ';
					echo '<label for="disable_rss">' . __( 'Disable Media RSS Enhancement', 'yoast-video-seo' ) . '</label></p>';

					echo '<br class="clear"/>';
					echo '<p><label class="textinput" for="wpseo_video_custom_fields">' . __( 'Custom fields:', 'yoast-video-seo' ) . '</label>';
					echo '<input type="text" class="textinput" size="100" id="wpseo_video_custom_fields" name="wpseo_video[custom_fields]" value="' . $options['custom_fields'] . '"></p>';
					echo '<p class="clear description">' . __( 'Custom fields the plugin should check for video content (comma separated)', 'yoast-video-seo' ) . '</p>';


					echo '<br class="clear"/>';
					echo '<h2>' . __( 'Embed Settings', 'yoast-video-seo' ) . '</h2>';


					echo '<p><input class="checkbox double" id="facebook_embed" type="checkbox" name="wpseo_video[facebook_embed]" ' . checked( $options['facebook_embed'], true, false ) . '> ';
					echo '<label for="facebook_embed">' . __( 'Allow video\'s to be played directly on Facebook.', 'yoast-video-seo' ) . '</label></p>';

					echo '<div class="clear"></div>';


					echo '<p><input class="checkbox double" id="fitvids" type="checkbox" name="wpseo_video[fitvids]" ' . checked( $options['fitvids'], true, false ) . '> ';
					echo '<label for="fitvids">' . sprintf( __( 'Try to make videos responsive using %sFitVids.js%s?', 'yoast-video-seo' ), '<a href="http://fitvidsjs.com/">', '</a>' ) . '</label></p>';

					echo '<br class="clear"/>';

					echo '<p><label class="textinput" for="wpseo_video_content_width">' . __( 'Content width:', 'yoast-video-seo' ) . '</label> ';
					echo '<input type="text" class="textinput" size="10" id="wpseo_video_content_width" name="wpseo_video[content_width]" value="' . $options['content_width'] . '"></p>';
					echo '<p class="clear description">' . __( 'This defaults to your themes content width, but if it\'s empty, setting a value here will make sure videos are embedded in the right width.', 'yoast-video-seo' ) . '</p>';


					echo '<p><label class="textinput" for="wpseo_video_vzaar_domain">' . __( 'Vzaar domain:', 'yoast-video-seo' ) . '</label> ';
					echo '<input type="text" class="textinput" size="10" id="wpseo_video_vzaar_domain" name="wpseo_video[vzaar_domain]" value="' . $options['vzaar_domain'] . '"></p>';
					echo '<p class="clear description">' . __( 'If you use Vzaar, set this to the domainname you use for your Vzaar videos, no http: or slashes needed.', 'yoast-video-seo' ) . '</p>';

					echo '<h2>' . __( 'Post Types to include in XML Video Sitemap', 'yoast-video-seo' ) . '</h2>';
					echo '<p>' . __( 'Determine which post types on your site might contain video.', 'yoast-video-seo' ) . '</p>';

					foreach ( (array) get_post_types( array( 'public' => true ), 'objects' ) as $posttype ) {
						$sel = '';
						if ( is_array( $options['videositemap_posttypes'] )
								&& $options['videositemap_posttypes'] !== array()
								&& in_array( $posttype->name, $options['videositemap_posttypes'] )
						) {
							$sel = 'checked="checked" ';
						}
						echo '<input class="checkbox double" id="include' . $posttype->name . '" type="checkbox" '
								. 'name="wpseo_video[videositemap_posttypes][' . $posttype->name . ']" ' . $sel . 'value="' . $posttype->name . '"/> '
								. '<label for="include' . $posttype->name . '">' . $posttype->labels->name . '</label><br class="clear">';
					}

					echo '<h2>' . __( 'Taxonomies to include in XML Video Sitemap', 'yoast-video-seo' ) . '</h2>';
					echo '<p>' . __( 'You can also include your taxonomy archives, for instance, if you have videos on a category page.', 'yoast-video-seo' ) . '</p>';

					foreach ( (array) get_taxonomies( array( 'public' => true ), 'objects' ) as $tax ) {
						$sel = '';
						if ( is_array( $options['videositemap_taxonomies'] )
								&& $options['videositemap_taxonomies'] !== array()
								&& in_array( $tax->name, $options['videositemap_taxonomies'] )
						) {
							$sel = 'checked="checked" ';
						}
						echo '<input class="checkbox double" id="include' . $tax->name . '" type="checkbox" '
								. 'name="wpseo_video[videositemap_taxonomies][' . $tax->name . ']" ' . $sel . 'value="' . $tax->name . '"/> '
								. '<label for="include' . $tax->name . '">' . $tax->labels->name . '</label><br class="clear">';
					}

					echo '<br class="clear"/>';
				}
				?>

				<div class="submit">
					<input type="submit" class="button-primary" name="submit"
								 value="<?php _e( "Save Settings", 'yoast-video-seo' ); ?>" />
				</div>
			</form>

			<?php
			if ( isset( $_POST['reindex'] ) ) {
				$this->reindex();
			}
			?>

			<h2><?php _e( 'Indexation of Video\'s in your content', 'yoast-video-seo' ); ?></h2>

			<p style="max-width: 600px;"><?php _e( 'This process goes through all the post types specified by you, as well as the terms of each taxonomy, to check for videos in the content. If the plugin finds a video, it updates the meta data for that piece of content, so it can add that meta data and content to the XML Video Sitemap.', 'yoast-video-seo' ); ?></p>

			<p style="max-width: 600px;"><?php _e( 'By default the plugin only checks content that hasn\'t been checked yet. However, if you check \'Force Re-Index\', it will re-check all content. This is particularly interesting if you want to check for a video embed code that wasn\'t supported before, of if you want to update thumbnail images en masse.', 'yoast-video-seo' ); ?></p>

			<form method="post" action="">
				<input class="checkbox double" type="checkbox" name="force" id="force"> <label
						for="force"><?php _e( "Force reindex of already indexed video's.", 'yoast-video-seo' ); ?></label><br />
				<br />
				<input type="submit" class="button" name="reindex"
							 value="<?php _e( 'Re-Index Videos', 'yoast-video-seo' ); ?>" />
			</form>
		</div>
	<?php

	}

	/**
	 * Based on the video type being used, this content filtering function will automatically optimize the embed codes
	 * to allow for proper recognition by search engines.
	 *
	 * This function also, since version 1.2, adds the schema.org videoObject output.
	 *
	 * @link  http://schema.org/VideoObject
	 * @link  https://developers.google.com/webmasters/videosearch/schema
	 *
	 * @since 0.1
	 *
	 * @param string $content The content of the post.
	 *
	 * @return string $content The content of the post as modified by the function, if applicable.
	 */
	function content_filter( $content ) {
		global $post, $content_width;

		if ( is_feed() || is_home() || is_archive() || is_tax() || is_tag() || is_category() ) {
			return $content;
		}

		if ( ! is_object( $post ) ) {
			return $content;
		}

		$video = WPSEO_Meta::get_value( 'video_meta', $post->ID );

		if ( ! is_array( $video ) || $video === array() ) {
			return $content;
		}

		$disable = WPSEO_Meta::get_value( 'videositemap-disable', $post->ID );
		if ( $disable === 'on' ) {
			return $content;
		}

		if ( ! is_numeric( $content_width ) ) {
			$content_width = 400;
		}

		switch ( $video['type'] ) {
			case 'vimeo':
				$content = str_replace( '<iframe src="http://player.vimeo.com', '<noframes><embed src="http://vimeo.com/moogaloop.swf?clip_id=' . $video['id'] . '" type="application/x-shockwave-flash" width="400" height="300"></embed></noframes><iframe src="http://player.vimeo.com', $content );
				break;
			case 'dailymotion':
				// If dailymotion is embedded using the Viper shortcode, we have to add a noscript version too
				if ( strpos( $content, '<iframe src="http://www.dailymotion' ) === false ) {
					$content = str_replace( '[/dailymotion]', '[/dailymotion]<noscript><iframe src="http://www.dailymotion.com/embed/video/' . $video['id'] . '" width="' . $content_width . '" height="' . floor( $content_width / 1.33 ) . '" frameborder="0"></iframe></noscript>', $content );
				}
				break;
		}

		$desc = trim( WPSEO_Meta::get_value( 'metadesc', $post->ID ) );
		if ( ! is_string( $desc ) || $desc === '' ) {
			$desc = trim( substr( $this->strip_shortcodes( $this->strip_tags( $post->post_content ) ), 0, 300 ) );
		}

		if ( empty( $desc ) ) {
			$desc = $this->strip_tags( get_the_title() );
		}

		$video = $this->get_video_image( $post->ID, $video );

		$content .= "\n\n";
		$content .= '<span itemprop="video" itemscope itemtype="http://schema.org/VideoObject">';
		$content .= '<meta itemprop="name" content="' . esc_attr( $this->strip_tags( get_the_title() ) ) . '">';
		$content .= '<meta itemprop="thumbnailURL" content="' . esc_attr( $video['thumbnail_loc'] ) . '">';
		$content .= '<meta itemprop="description" content="' . esc_attr( $desc ) . '">';
		$content .= '<meta itemprop="uploadDate" content="' . date( 'c', strtotime( $post->post_date ) ) . '">';
		if ( isset( $video['player_loc'] ) ) {
			$content .= '<meta itemprop="embedURL" content="' . $video['player_loc'] . '">';
		}
		if ( isset( $video['content_loc'] ) ) {
			$content .= '<meta itemprop="contentURL" content="' . $video['content_loc'] . '">';
		}

		$video_duration = WPSEO_Meta::get_value( 'videositemap-duration', $post->ID );
		if ( $video_duration == 0 && isset( $video['duration'] ) ) {
			$video_duration = $video['duration'];
		}

		if ( $video_duration ) {
			$content .= '<meta itemprop="duration" content="' . $this->iso_8601_duration( $video_duration ) . '">';
		}
		$content .= '</span>';

		return $content;
	}

	/**
	 * A better strip tags that leaves spaces intact (and rips out more code)
	 *
	 * @since 1.3.4
	 *
	 * @link  http://www.php.net/manual/en/function.strip-tags.php#110280
	 *
	 * @param string $string string to strip tags from
	 *
	 * @return string
	 */
	function strip_tags( $string ) {

		// ----- remove HTML TAGs -----
		$string = preg_replace( '/<[^>]*>/', ' ', $string );

		// ----- remove control characters -----
		$string = str_replace( "\r", '', $string ); // --- replace with empty space
		$string = str_replace( "\n", ' ', $string ); // --- replace with space
		$string = str_replace( "\t", ' ', $string ); // --- replace with space

		// ----- remove multiple spaces -----
		$string = trim( preg_replace( '/ {2,}/', ' ', $string ) );

		return $string;

	}

	/**
	 * Convert the duration in seconds to an ISO 8601 compatible output. Assumes the length is not over 24 hours.
	 *
	 * @link http://en.wikipedia.org/wiki/ISO_8601
	 *
	 * @param int $duration The duration in seconds.
	 *
	 * @return string $out ISO 8601 compatible output.
	 */
	function iso_8601_duration( $duration ) {
		$out = 'PT';
		if ( $duration > 3600 ) {
			$hours = floor( $duration / 3600 );
			$out .= $hours . 'H';
			$duration = $duration - ( $hours * 3600 );
		}
		if ( $duration > 60 ) {
			$minutes = floor( $duration / 60 );
			$out .= $minutes . 'M';
			$duration = $duration - ( $minutes * 60 );
		}
		if ( $duration > 0 ) {
			$out .= $duration . 'S';
		}

		return $out;
	}

	/**
	 * Filter the OpenGraph type for the post and sets it to 'video'
	 *
	 * @since 0.1
	 *
	 * @param string $type The type, normally "article"
	 *
	 * @return string $type Value 'video'
	 */
	function opengraph_type( $type ) {
		$options = get_option( 'wpseo_video' );

		if ( $options['facebook_embed'] !== true ) {
			return $type;
		}

		return $this->type_filter( $type, 'video.movie' );
	}

	/**
	 * Switch the Twitter card type to player if needed.
	 *
	 * @param string $type
	 *
	 * @return string
	 */
	function card_type( $type ) {
		return $this->type_filter( $type, 'player' );
	}

	/**
	 * Helper function for Twitter and OpenGraph card types
	 *
	 * @param string $type
	 * @param string $video_output
	 *
	 * @return string
	 */
	function type_filter( $type, $video_output ) {
		if ( is_singular() ) {
			global $post;

			$video = WPSEO_Meta::get_value( 'video_meta', $post->ID );

			if ( ! is_array( $video ) || $video === array() ) {
				return $type;
			} else {
				$disable = WPSEO_Meta::get_value( 'videositemap-disable', $post->ID );
				if ( $disable === 'on' ) {
					return $type;
				} else {
					return $video_output;
				}
			}

		} else {
			if ( is_tax() || is_category() || is_tag() ) {
				$options = get_option( 'wpseo_video' );

				$term = get_queried_object();

				if ( is_array( $options['videositemap_taxonomies'] )
						&& $options['videositemap_taxonomies'] !== array()
						&& in_array( $term->taxonomy, $options['videositemap_taxonomies'] )
				) {
					$tax_meta = get_option( 'wpseo_taxonomy_meta' );
					if ( isset( $tax_meta[$term->taxonomy]['_video'][$term->term_id] ) ) {
						return $video_output;
					}
				}

			}
		}

		return $type;
	}

	/**
	 * Filter the OpenGraph image for the post and sets it to the video thumbnail
	 *
	 * @since 0.1
	 *
	 * @param string $image URL to the image
	 *
	 * @return string $image URL to the video thumbnail image
	 */
	function opengraph_image( $image ) {
		if ( ! empty( $image ) ) {
			return $image;
		}

		if ( is_singular() ) {
			global $post;

			$video = WPSEO_Meta::get_value( 'video_meta', $post->ID );

			if ( ! is_array( $video ) || $video === array() ) {
				return $image;
			}

			$disable = WPSEO_Meta::get_value( 'videositemap-disable', $post->ID );
			if ( $disable === 'on' ) {
				return $image;
			}

			return $video['thumbnail_loc'];
		} else {
			if ( is_tax() || is_category() || is_tag() ) {
				$options = get_option( 'wpseo_video' );

				$term = get_queried_object();

				if ( is_array( $options['videositemap_taxonomies'] )
						&& $options['videositemap_taxonomies'] !== array()
						&& in_array( $term->taxonomy, $options['videositemap_taxonomies'] )
				) {
					$tax_meta = get_option( 'wpseo_taxonomy_meta' );
					if ( isset( $tax_meta[$term->taxonomy]['_video'][$term->term_id] ) ) {
						$video = $tax_meta[$term->taxonomy]['_video'][$term->term_id];

						return $video['thumbnail_loc'];
					}
				}
			}
		}

		return $image;
	}

	/**
	 * Add OpenGraph video info if present
	 *
	 * @since 0.1
	 */
	function opengraph() {
		$options = get_option( 'wpseo_video' );

		if ( $options['facebook_embed'] !== true ) {
			return false;
		}

		if ( is_singular() ) {
			global $post;

			$video = WPSEO_Meta::get_value( 'video_meta', $post->ID );

			if ( ! is_array( $video ) || $video === array() ) {
				return false;
			}

			$disable = WPSEO_Meta::get_value( 'videositemap-disable', $post->ID );
			if ( $disable === 'on' ) {
				return false;
			}

			$video = $this->get_video_image( $post->ID, $video );
		} else {
			if ( is_tax() || is_category() || is_tag() ) {

				$term = get_queried_object();

				if ( is_array( $options['videositemap_taxonomies'] )
						&& $options['videositemap_taxonomies'] !== array()
						&& in_array( $term->taxonomy, $options['videositemap_taxonomies'] )
				) {
					$tax_meta = get_option( 'wpseo_taxonomy_meta' );
					if ( isset( $tax_meta[$term->taxonomy]['_video'][$term->term_id] ) ) {
						$video = $tax_meta[$term->taxonomy]['_video'][$term->term_id];
					} else {
						return false;
					}
				}
			} else {
				return false;
			}
		}

		if ( ! isset( $video['player_loc'] ) ) {
			return false;
		}

		echo '<meta property="og:video" content="' . $video['player_loc'] . '" />' . "\n";
		echo '<meta property="og:video:type" content="application/x-shockwave-flash" />' . "\n";
		if ( isset( $video['width'] ) && isset( $video['height'] ) ) {
			echo '<meta property="og:video:width" content="' . $video['width'] . '" />' . "\n";
			echo '<meta property="og:video:height" content="' . $video['height'] . '" />' . "\n";
		}
		global $wpseo_og;
		$wpseo_og->image_output( $video['thumbnail_loc'] );
	}

	/**
	 * Make the get_terms query only return terms with a non-empty description.
	 *
	 * @since 1.3
	 *
	 * @param $pieces array The separate pieces of the terms query to filter.
	 *
	 * @return mixed
	 */
	function filter_terms_clauses( $pieces ) {
		$pieces['where'] .= " AND tt.description != ''";

		return $pieces;
	}

	/**
	 * Reindex the video info from posts
	 *
	 * @since 0.1
	 */
	function reindex() {
		require_once ABSPATH . '/wp-admin/includes/media.php';

		echo "<strong>" . __( "Reindex starts....", "yoast-video-seo" ) . "</strong><br/>";

		$options = get_option( 'wpseo_video' );

		if ( is_array( $options['videositemap_posttypes'] ) && $options['videositemap_posttypes'] !== array() ) {
			$args = array(
					'post_type'   => $options['videositemap_posttypes'],
					'post_status' => 'publish',
					'numberposts' => 100,
					'offset'      => 0,
			);

			global $wp_version;
			if ( ! isset( $_POST['force'] ) ) {
				if ( version_compare( $wp_version, '3.5', ">=" ) ) {
					$args['meta_query'] = array(
							'key'     => '_yoast_wpseo_video_meta',
							'compare' => 'NOT EXISTS'
					);
				}
			}

			$post_count_total = 0;
			foreach ( $options['videositemap_posttypes'] as $post_type ) {
				$post_count_total += wp_count_posts( $post_type )->publish;
			}

			while ( $post_count_total > $args['offset'] ) {
				$results = get_posts( $args );

				echo "<br/><strong>" . sprintf( __( "Found %d pieces of content to search through", "yoast-video-seo" ), count( $results ) ) . "</strong><br/><br/>";

				foreach ( (array) $results as $post ) {
					$this->update_video_post_meta( $post, true );
					flush();
				}
				$args['offset'] += 99;
			}
		}

		// Get all the non-empty terms.
		add_filter( 'terms_clauses', array( $this, 'filter_terms_clauses' ) );
		$terms = array();
		if ( is_array( $options['videositemap_taxonomies'] ) && $options['videositemap_taxonomies'] !== array() ) {
			foreach ( $options['videositemap_taxonomies'] as $key => $val ) {
				$new_terms = get_terms( $val );
				if ( is_array( $new_terms ) ) {
					$terms = array_merge( $terms, $new_terms );
				}
			}
		}
		remove_filter( 'terms_clauses', array( $this, 'filter_terms_clauses' ) );

		if ( count( $terms ) >= 1 ) {
			echo "<br/><strong>" . sprintf( __( "Found %d terms to search through", "yoast-video-seo" ), count( $terms ) ) . "</strong><br/><br/>";

			foreach ( $terms as $term ) {
				$this->update_video_term_meta( $term, true );
				flush();
			}
		}

		// Ping the search engines with our updated XML sitemap, we ping with the index sitemap because
		// we don't know which video sitemap, or sitemaps, have been updated / added.
		wpseo_ping_search_engines();

		echo "<br/><strong>" . __( 'Reindex completed.', 'yoast-video-seo' ) . "</strong>";
	}


	/********************** DEPRECATED METHODS **********************/

	/**
	 * Register the wpseo_video setting
	 *
	 * @deprecated 1.6.0 - now auto-handled by class WPSEO_Option_Video
	 */
	function options_init() {
		_deprecated_function( __CLASS__ . '::' . __METHOD__, 'Video SEO 1.6.0', null );
	}


	/**
	 * Register defaults for the video sitemap
	 *
	 * @since      0.2
	 * @deprecated 1.6.0 - now auto-handled by class WPSEO_Option_Video
	 */
	function set_defaults() {
		_deprecated_function( __CLASS__ . '::' . __METHOD__, 'Video SEO 1.6.0', null );
	}

	/**
	 * Adds the header for the Video tab in the WordPress SEO meta box on edit post pages.
	 *
	 * @since      0.1
	 * @deprecated 1.6.0
	 * @deprecated use WPSEO_Video_Metabox::tab_header()
	 * @see        WPSEO_Video_Metabox::tab_header()
	 */
	function tab_header() {
		_deprecated_function( __CLASS__ . '::' . __METHOD__, 'Video SEO 1.6.0', 'WPSEO_Video_Metabox::tab_header()' );
		WPSEO_Video_Metabox::tab_header();
	}

	/**
	 * Outputs the content for the Video tab in the WordPress SEO meta box on edit post pages.
	 *
	 * @since      0.1
	 * @deprecated 1.6.0
	 * @deprecated use WPSEO_Video_Metabox::tab_content()
	 * @see        WPSEO_Video_Metabox::tab_content()
	 */
	function tab_content() {
		_deprecated_function( __CLASS__ . '::' . __METHOD__, 'Video SEO 1.6.0', 'WPSEO_Video_Metabox::tab_content()' );
		WPSEO_Video_Metabox::tab_content();
	}

	/**
	 * Output a tab in the WP SEO Metabox
	 *
	 * @since      0.2
	 * @deprecated 1.6.0
	 * @deprecated use WPSEO_Video_Metabox::do_tab()
	 * @see        WPSEO_Video_Metabox::do_tab()
	 *
	 * @param string $id      CSS ID of the tab.
	 * @param string $heading Heading for the tab.
	 * @param string $content Content of the tab.
	 */
	function do_tab( $id, $heading, $content ) {
		_deprecated_function( __CLASS__ . '::' . __METHOD__, 'Video SEO 1.6.0', 'WPSEO_Video_Metabox::do_tab()' );
		WPSEO_Video_Metabox::do_tab( $id, $heading, $content );
	}

	/**
	 * Adds a line in the meta box
	 *
	 * @since      0.2
	 * @deprecated 1.6.0
	 * @deprecated use WPSEO_Video_Metabox::do_meta_box()
	 * @see        WPSEO_Video_Metabox::do_meta_box()
	 *
	 * @param array $meta_box Contains the vars based on which output is generated.
	 *
	 * @return string
	 */
	function do_meta_box( $meta_box ) {
		_deprecated_function( __CLASS__ . '::' . __METHOD__, 'Video SEO 1.6.0', 'WPSEO_Video_Metabox::do_meta_box()' );

		return WPSEO_Video_Metabox::do_meta_box( $meta_box );
	}

	/**
	 * Defines the meta box inputs
	 *
	 * @since      0.1
	 * @deprecated 1.6.0
	 * @deprecated use WPSEO_Meta::get_meta_field_defs()
	 * @see        WPSEO_Meta::get_meta_field_defs()
	 *
	 * @return array $mbs meta box inputs
	 */
	function get_meta_boxes( $post_type = 'post' ) {
		_deprecated_function( __CLASS__ . '::' . __METHOD__, 'Video SEO 1.6.0', 'WPSEO_Meta::get_meta_field_defs()' );

		return WPSEO_Meta::get_meta_field_defs( 'video', $post_type );
	}

	/**
	 * Save the values from the meta box inputs
	 *
	 * @since      0.1
	 * @deprecated 1.6.0
	 * @deprecated use WPSEO_Video_Metabox::save_meta_boxes()
	 * @see        WPSEO_Video_Metabox::save_meta_boxes()
	 *
	 * @param array $mbs meta boxes to merge the inputs with.
	 *
	 * @return array $mbs meta box inputs
	 */
	function save_meta_boxes( $mbs ) {
		_deprecated_function( __CLASS__ . '::' . __METHOD__, 'Video SEO 1.6.0', 'WPSEO_Video_Metabox::save_meta_boxes()' );

		return WPSEO_Video_Metabox::save_meta_boxes( $mbs );
	}

	/**
	 * Replace the default snippet with a video snippet by hooking this function into the wpseo_snippet filter.
	 *
	 * @since      0.1
	 * @deprecated 1.6.0
	 * @deprecated use WPSEO_Video_Metabox::snippet_preview()
	 * @see        WPSEO_Video_Metabox::snippet_preview()
	 *
	 * @param string $content The original snippet content.
	 * @param object $post    The post object of the post for which the snippet was generated.
	 * @param array  $vars    An array of variables for use within the snippet, containing title, description, date and slug
	 *
	 * @return string $content The new video snippet if video metadata was found for the post.
	 */
	function snippet_preview( $content, $post, $vars ) {
		_deprecated_function( __CLASS__ . '::' . __METHOD__, 'Video SEO 1.6.0', 'WPSEO_Video_Metabox::snippet_preview()' );

		return WPSEO_Video_Metabox::snippet_preview( $content, $post, $vars );
	}


	/**
	 * Restricts the length of the meta description in the snippet preview and throws appropriate warnings.
	 *
	 * @since      0.1
	 * @deprecated 1.6.0
	 * @deprecated use WPSEO_Video_Metabox::meta_length()
	 * @see        WPSEO_Video_Metabox::meta_length()
	 *
	 * @param int $length The snippet length as defined by default.
	 *
	 * @return int $length The max snippet length for a video snippet.
	 */
	function meta_length( $length ) {
		_deprecated_function( __CLASS__ . '::' . __METHOD__, 'Video SEO 1.6.0', 'WPSEO_Video_Metabox::meta_length()' );

		return WPSEO_Video_Metabox::meta_length( $length );
	}

	/**
	 * Explains the length restriction of the meta description
	 *
	 * @since      0.1
	 * @deprecated 1.6.0
	 * @deprecated use WPSEO_Video_Metabox::meta_length_reason()
	 * @see        WPSEO_Video_Metabox::meta_length_reason()
	 *
	 * @param string $reason Input string.
	 *
	 * @return string $reason  The reason why the meta description is limited.
	 */
	function meta_length_reason( $reason ) {
		_deprecated_function( __CLASS__ . '::' . __METHOD__, 'Video SEO 1.6.0', 'WPSEO_Video_Metabox::meta_length_reason()' );

		return WPSEO_Video_Metabox::meta_length_reason( $reason );
	}

	/**
	 * Filter the Page Analysis results to make sure we're giving the correct hints.
	 *
	 * @since      1.4
	 * @deprecated 1.6.0
	 * @deprecated use WPSEO_Video_Metabox::filter_linkdex_results()
	 * @see        WPSEO_Video_Metabox::filter_linkdex_results()
	 *
	 * @param array  $results The results array to filter and update.
	 * @param array  $job     The current jobs variables.
	 * @param object $post    The post object for the current page.
	 *
	 * @return array $results
	 */
	function filter_linkdex_results( $results, $job, $post ) {
		_deprecated_function( __CLASS__ . '::' . __METHOD__, 'Video SEO 1.6.0', 'WPSEO_Video_Metabox::filter_linkdex_results()' );

		return WPSEO_Video_Metabox::filter_linkdex_results( $results, $job, $post );
	}

} /* End of class wpseo_Video_Sitemap */


/**
 * Throw an error if WordPress SEO is not installed.
 *
 * @since 0.2
 */
function yoast_wpseo_missing_error() {
	echo '<div class="error"><p>' . sprintf( __( 'Please %sinstall &amp; activate WordPress SEO by Yoast%s and then enable its XML sitemap functionality to allow the Video SEO module to work.' ), '<a href="' . admin_url( 'plugin-install.php?tab=search&type=term&s=wordpress+seo&plugin-search-input=Search+Plugins' ) . '">', '</a>' ) . '</p></div>';
}

/**
 * Throw an error if WordPress is out of date.
 *
 * @since 1.5.4
 */
function yoast_wordpress_upgrade_error() {
	echo '<div class="error"><p>' . __( 'Please upgrade WordPress to the latest version to allow WordPress and the Video SEO module to work properly.', 'yoast-video-seo' ) . '</p></div>';
}

/**
 * Throw an error if WordPress SEO is out of date.
 *
 * @since 1.5.4
 */
function yoast_wpseo_upgrade_error() {
	echo '<div class="error"><p>' . __( 'Please upgrade the WordPress SEO plugin to the latest version to allow the Video SEO module to work.', 'yoast-video-seo' ) . '</p></div>';
}

/**
 * Initialize the Video SEO module on plugins loaded, so WP SEO should have set its constants and loaded its main classes.
 *
 * @since 0.2
 */
function yoast_wpseo_video_seo_init() {
	global $wp_version;

	if ( ! version_compare( $wp_version, '3.4', '>=' ) ) {
		add_action( 'all_admin_notices', 'yoast_wordpress_upgrade_error' );
	} else {
		if ( defined( 'WPSEO_VERSION' ) ) {
			if ( version_compare( WPSEO_VERSION, '1.4.99', '>=' ) ) { // Allow beta version
				add_action( 'plugins_loaded', 'yoast_wpsoe_video_seo_meta_init', 10 );
				add_action( 'plugins_loaded', 'yoast_wpsoe_video_seo_sitemap_init', 20 );
			} else {
				add_action( 'all_admin_notices', 'yoast_wpseo_upgrade_error' );
			}
		} else {
			add_action( 'all_admin_notices', 'yoast_wpseo_missing_error' );
		}
	}
}

function yoast_wpsoe_video_seo_sitemap_init() {
	$GLOBALS['wpseo_video_xml'] = new wpseo_Video_Sitemap();
}

function yoast_wpsoe_video_seo_meta_init() {
	require_once( plugin_dir_path( __FILE__ ) . 'class-wpseo-meta-video.php' );
	WPSEO_Meta_Video::init();
}

add_action( 'plugins_loaded', 'yoast_wpseo_video_seo_init', 5 );
