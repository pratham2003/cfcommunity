<?php # -*- coding: utf-8 -*-
/**
 * Module Name:	Duplicate Blogs/Copy Attachments
 * Description:	Copy all attachments from the old blog into the new one
 * Author:		Inpsyde GmbH
 * Version:		0.1
 * Author URI:	http://inpsyde.com
 */

/**
 * Copy attachments from one blog to another in a multisite.
 *
 * @since  2013.06.20
 * @author toscho
 */
class Mlp_Copy_Attachments
{
	/**
	 * ID of base blog.
	 *
	 * @type int
	 */
	protected $source_blog_id;

	/**
	 * Local path for uploads for base blog.
	 *
	 * @type string
	 */
	protected $source_dir;

	/**
	 * ID of new blog.
	 *
	 * @type int
	 */
	protected $dest_blog_id;

	/**
	 * Local path for uploads for new blog.
	 *
	 * @type string
	 */
	protected $dest_dir;

	/**
	 * ID of the blog to switch to after the work has been done.
	 *
	 * @type int
	 */
	protected $final_blog_id;

	/**
	 * Did we find any files to copy?
	 *
	 * @type bool
	 */
	protected $found_files = FALSE;

	/**
	 * Upload base URL for source blog.
	 *
	 * @type string
	 */
	public $source_url;

	/**
	 * Uploads base URL for new blog.
	 *
	 * @type string
	 */
	public $dest_url;

	/**
	 * Constructor. Set up basic variables.
	 *
	 * @param int $source_blog_id
	 * @param int $dest_blog_id
	 * @param int $final_blog_id
	 */
	public function __construct( $source_blog_id, $dest_blog_id, $final_blog_id ) {

		$this->source_blog_id = $source_blog_id;
		$this->dest_blog_id   = $dest_blog_id;
		$this->final_blog_id  = $final_blog_id;

		$this->set_base_paths( $source_blog_id, $this->source_dir, $this->source_url );
		$this->set_base_paths( $dest_blog_id, $this->dest_dir, $this->dest_url );

		switch_to_blog( $final_blog_id );
	}

	/**
	 * Fills variables with pathes for directory and public URL.
	 *
	 * Different return values depending on blog context.
	 *
	 * @param  int    $blog_id
	 * @param  string $dir
	 * @param  string $url
	 * @return void
	 */
	protected function set_base_paths( $blog_id, &$dir, &$url ) {

		switch_to_blog( $blog_id );

		$uploads  = wp_upload_dir();
		$site_url = get_option( 'siteurl' );
		$dir      = $uploads['basedir'];
		$url      = $this->real_upload_base_url( $uploads['baseurl'], $site_url );
	}

	/**
	 * Workaround for broken behavior of wp_upload_dir() in WordPress.
	 *
	 * After switch_to_blog(), 'baseurl' uses the wrong domain name.
	 *
	 * @param  string $base_url WordPress base URL
	 * @param  string $site_url Result of get_option('siteurl')
	 * @return string           Correct string
	 */
	protected function real_upload_base_url( $base_url, $site_url )
	{
		if ( ! is_subdomain_install() )
			return $base_url;

		if ( 0 === strpos( $base_url, $site_url ) )
			return $base_url;

		$b_host = parse_url( $base_url, PHP_URL_HOST );
		$s_host = parse_url( $site_url, PHP_URL_HOST );

		return str_replace( $b_host, $s_host, $base_url );
	}

	/**
	 * Move attachments from old blog to new blog.
	 *
	 * @return bool Wether or not we actually copied files.
	 */
	public function copy_attachments() {

		if ( ! is_dir( $this->source_dir ) OR ! is_readable( $this->source_dir ) )
			return FALSE;

		$source_paths = $this->get_attachment_paths();

		if ( empty ( $source_paths ) )
			return FALSE;

		// $dir is a path relative to upload dir, $paths an array of paths relative to $dir
		foreach ( $source_paths as $dir => $paths )
			$this->copy_dir( $paths, "$this->source_dir/$dir", "$this->dest_dir/$dir" );

		return $this->found_files;
	}

	/**
	 * Copy all files from base blog to new blog.
	 *
	 * @param  array  $paths      List of file paths relative to directory
	 * @param  string $source_dir Full base directory path
	 * @param  string $dest_dir   Full target directory path
	 * @return void
	 */
	protected function copy_dir( Array $paths, $source_dir, $dest_dir ) {

		if ( ! is_dir( $source_dir ) )
			return;

		if ( ! is_dir( $dest_dir ) and ! wp_mkdir_p( $dest_dir ) )
			return;

		foreach ( $paths as $path )
			$this->copy_file( "$source_dir/$path", "$dest_dir/$path" );
	}

	/**
	 * Copy a single file.
	 *
	 * @param  string $source Path to source file
	 * @param  string $dest   Path to target file destination
	 * @return void
	 */
	protected function copy_file( $source, $dest ) {

		if ( ! file_exists( $source ) )
			return;

		if ( ! file_exists( $dest ) ) {
			$copied = copy( $source, $dest );

			if ( $copied )
				$this->found_files = TRUE;
		}
	}

	/**
	 * Extract all registered paths from database.
	 *
	 * We copy only files referenced in the database, because we don't
	 * trust other files.
	 *
	 * @return array Each key is a directory relative to the blog upload
	 *               directory, the value is a list of paths.
	 */
	protected function get_attachment_paths() {

		switch_to_blog( $this->source_blog_id );

		global $wpdb;

		$meta = $wpdb->get_results( "SELECT `meta_value`
			FROM `$wpdb->postmeta`
			WHERE `meta_key` = '_wp_attachment_metadata'" );
			$out  = array();

		foreach ( $meta as $data )
			$this->add_paths_for_file( $out, $data->meta_value );

		restore_current_blog();

		return $out;
	}

	/**
	 * Prepare the raw SQL result for later usage.
	 *
	 * @param  array  $list Array to fill with URIs
	 * @param  string $meta Data from SQL query against postmeta table.
	 * @return void
	 */
	protected function add_paths_for_file( Array &$list, $meta ) {

		$meta           = maybe_unserialize( $meta );
		$dir            = dirname( $meta['file'] );
		$list[ $dir ][] = basename( $meta['file'] );

		if ( empty ( $meta['sizes'] ) )
			return;

		foreach ( $meta['sizes'] as $data )
			$list[ $dir ][] = $data['file'];
	}
}