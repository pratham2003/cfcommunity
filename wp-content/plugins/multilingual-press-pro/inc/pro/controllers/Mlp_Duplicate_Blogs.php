<?php
/**
 * Module Name:	Duplicate Blogs
 * Description:	Create a new blog and copy all data from the old blog into the new. and replace the old siteurl
 * Author:		Inpsyde GmbH
 * Version:		0.4
 * Author URI:	http://inpsyde.com
 */

class Mlp_Duplicate_Blogs {

	protected $plugin_data;

	/**
	 * MLP Link Table
	 *
	 * @static
	 * @access	public
	 * @since	0.1
	 * @var		string
	 */
	public $link_table = FALSE;

	/**
	 * init function to register all used hooks and set the Database Table
	 *
	 * @access	public
	 * @since	0.1
	 * @uses	add_filter
	 * @return	void
	 */
	public function __construct( Inpsyde_Property_List_Interface $data ) {

		$this->plugin_data = $data;

		/*/ Quit here if module is turned off
		if ( ! $this->register_setting() )
			return; */

		add_filter( 'wpmu_new_blog', array( $this, 'wpmu_new_blog' ), 10, 2 );
		add_filter( 'admin_head', array( $this, 'admin_head' ) );
	}

	protected function register_setting() {

		$desc = __(
			'Create a new site and copy all data from the old site into the new and replace the old siteurl.',
			'multilingualpress'
		);

		return $this->plugin_data->module_manager->register(
			array (
				'display_name'	=> __( 'Duplicate Site', 'multilingualpress' ),
				'slug'			=> 'class-' . __CLASS__,
				'description'   => $desc,
				//'callback'      => array ( $this, 'extend_settings_description' )
			)
		);
	}

	/**
	 * Duplicates the old blog to the new blog
	 *
	 * @access	public
	 * @since	0.1
	 * @param	int $blog_id the new blog id
	 * @uses	switch_to_blog, restore_current_blog, get_option, update_option
	 * @global	$wpdb WordPress Database Wrapper
	 * @return	void
	 */
	public function wpmu_new_blog( $blog_id ) {
		global $wpdb;

		// Return if we don't have a blog
		if ( ! isset( $_POST[ 'blog' ][ 'basedon' ] ) || 1 > $_POST[ 'blog' ][ 'basedon' ] )
			return;

		$source_blog_id = (int) $_POST[ 'blog' ][ 'basedon' ];

		// Switch to the base blog
		switch_to_blog( $source_blog_id );

		$oldprefix       = $wpdb->prefix;
		$domain          = '';

		//load the primary domain if domainmapping is active
		if ( property_exists( $wpdb, 'dmtable' ) && '' != $wpdb->dmtable ) {
			$domain = $wpdb->get_var(
				$wpdb->prepare(
					'SELECT domain FROM ' . $wpdb->dmtable . ' WHERE active = 1 AND blog_id = %s LIMIT 1',
					get_current_blog_id()
				)
			);
			if ( '' != $domain ) {
				$protocol = ( 'on' == strtolower( $_SERVER[ 'HTTPS' ] ) ) ? 'https://' : 'http://';
				$domain = $protocol . $domain;
			}
		}

		// Switch to our new blog
		restore_current_blog();
		switch_to_blog( $blog_id );

		// Set the stuff
		$current_admin_email = get_option( 'admin_email' );
		$url                 = get_option( 'siteurl' );
		$tables              = array_diff( $wpdb->tables, $wpdb->old_tables ); // get the current available tables

		// truncate all tables
		foreach ( $tables as $table ) {
			$wpdb->query( 'TRUNCATE TABLE ' . $wpdb->prefix . $table );
			// insert content
			$wpdb->query( 'INSERT INTO ' . $wpdb->prefix . $table . ' SELECT * FROM ' . $oldprefix . $table );
		}

		$wpdb->update(
			$wpdb->options,
			array( 'option_value' => $current_admin_email ),
			array( 'option_name'  => 'admin_email' )
		);

		// if an url was used in the old blog, we set it to this url to change all content elements
		// change siteurl -> will start url rename plugin
		if ( '' != $domain )
			update_option( 'siteurl', $domain );

		update_option( 'blogname', stripslashes( $_POST [ 'blog' ][ 'title' ] ) );
		update_option( 'inpsyde_companyname', stripslashes( $_POST [ 'blog' ][ 'title' ] ) );
		update_option( 'home', $url );

		// change siteurl -> will start url rename plugin
		update_option( 'siteurl', $url );

		$wpdb->update(
			$wpdb->options,
			array( 'option_name' => $wpdb->prefix . 'user_roles' ),
			array( 'option_name' => $oldprefix . 'user_roles' )
		);

		$this->insert_post_relations( $source_blog_id, $blog_id );
		$this->copy_attachments( $source_blog_id, $blog_id, $blog_id );

		restore_current_blog();
	}

	/**
	 * Get all linked elements from source blog and set links to those in our new blog.
	 *
	 * @param int $source_blog_id
	 * @param int $target_blog_id
	 * @return int|false Number of rows affected/selected or false on error
	 */
	protected function insert_post_relations( $source_blog_id, $target_blog_id ) {

		if ( $this->has_related_blogs( $source_blog_id ) )
			return $this->copy_post_relationships( $source_blog_id, $target_blog_id );

		return $this->create_post_relationships( $source_blog_id, $target_blog_id );
	}


	/**
	 * Copy post relationships from source blog to target blog.
	 *
	 * @param int $source_blog_id
	 * @param int $target_blog_id
	 * @return int|FALSE Number of rows affected or FALSE on error
	 */
	protected function copy_post_relationships( $source_blog_id, $target_blog_id ) {

		global $wpdb;

		$table = "{$wpdb->base_prefix}multilingual_linked";
		$query = "INSERT INTO `$table`
		(
			`ml_source_blogid`,
			`ml_source_elementid`,
			`ml_blogid`,
			`ml_elementid`,
			`ml_type`
		)
		SELECT
			`ml_source_blogid`,
			`ml_source_elementid`,
			$target_blog_id,
			`ml_elementid`,
			`ml_type`
		FROM `$table`
		WHERE  `ml_blogid` = $source_blog_id";

		return $wpdb->query( $query );
	}

	/**
	 * Create post relationships between all posts from source blog and target blog.
	 *
	 * @param int $source_blog_id
	 * @param int $target_blog_id
	 * @return int|FALSE Number of rows affected or FALSE on error
	 */
	protected function create_post_relationships( $source_blog_id, $target_blog_id ) {

		global $wpdb;

		$table = "{$wpdb->base_prefix}multilingual_linked";

		$blogs = array( $source_blog_id, $target_blog_id );

		foreach( $blogs as $blog ) {
			$result = $wpdb->query(
				"INSERT INTO $table
				(`ml_source_blogid`, `ml_source_elementid`, `ml_blogid`, `ml_elementid`, `ml_type`)
				SELECT $source_blog_id, `ID`, $blog, ID, `post_type`
					FROM $wpdb->posts
					WHERE `post_status` IN('publish', 'future', 'draft', 'pending', 'private')"
			);
		}

		return $result;
	}

	/**
	 * Check if there are any registered relations for the source blog.
	 *
	 * @param  int $source_blog_id
	 * @return boolean
	 */
	protected function has_related_blogs( $source_blog_id ) {

		global $wpdb;

		$table = "{$wpdb->base_prefix}multilingual_linked";
		$sql   = "SELECT `ml_id` FROM $table WHERE `ml_blogid` = $source_blog_id LIMIT 2";

		return 2 == $wpdb->query( $sql );
	}

	/**
	 * Copy all attachments from source blog to new blog.
	 *
	 * @param int $from_id
	 * @param int $to_id
	 * @param int $final_id
	 * @return void
	 */
	protected function copy_attachments( $from_id, $to_id, $final_id ) {

		$copy_files = new Mlp_Copy_Attachments( $from_id, $to_id, $final_id );

		if ( $copy_files->copy_attachments() )
			$this->update_file_urls( $copy_files );
	}

	/**
	 * Replace file URLs in new blog.
	 *
	 * @param Mlp_Copy_Attachments $copy_files
	 * @return int|false Number of rows affected/selected or false on error
	 */
	protected function update_file_urls( $copy_files ) {

		global $wpdb;

		$tables = array (
			$wpdb->posts         => array (
				'guid',
				'post_content',
				'post_excerpt',
				'post_content_filtered',
			),
			$wpdb->term_taxonomy => array (
				'description'
			),
			$wpdb->comments      => array (
				'comment_content'
			)
		);

		$db_replace = new Mlp_Db_Replace(
			$tables,
			$copy_files->source_url,
			$copy_files->dest_url
		);

		return $db_replace->replace();
	}

	/**
	 * Inject form field for "Add new site" screen
	 *
	 * @access	public
	 * @since	0.1
	 * @uses	_e
	 * @global	object $wpdb WordPress Database Wrapper
	 * @global	string $pagenow Current Page Locator
	 * @return	void
	 */
	public function admin_head() {

		global $pagenow, $wpdb;

		if ( 'site-new.php' !== $pagenow || ( isset( $_GET[ 'action' ] ) && 'editblog' == $_GET[ 'action' ] ) )
			return;

		$blogs = $wpdb->get_results(
			"SELECT blog_id,domain,path FROM {$wpdb->blogs} WHERE deleted = 0 AND site_id = '{$wpdb->siteid}' ",
			ARRAY_A
		);
		$options = '<option value="0">' . __( 'Choose site', 'multilingualpress' ) . '</option>';

		foreach ( ( array ) $blogs as $blog ) {

			'/' === $blog[ 'path' ] && $blog[ 'path' ] = '';

			$options .= '<option value="' . $blog[ 'blog_id' ] . '">'
				. $blog[ 'domain' ] . $blog[ 'path' ]
				. '</option>';
		}
		?>
		<script type='text/javascript'>
			jQuery( document ).ready( function( $ ) {
				$( '.form-required:last' ).after('<tr class="form-field form-required"><th scope="row" ><?php
					_e( 'Based on site', 'multilingualpress' );
					?></th><td><select name="blog[basedon]"><?php echo $options; ?></select></td></tr>' );
			} );
		</script>
		<?php
	}
}