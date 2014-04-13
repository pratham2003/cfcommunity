<?php
/**
 * Class Mlp_Redirect
 *
 * A god class, crying for refactoring, we know.
 * It redirects the user according to her/his browser preferences.
 *
 * @version 2014.03.28
 * @author  Inpsyde GmbH, toscho
 * @license GPL
 */
class Mlp_Redirect {

	/**
	 * Passed by main controller.
	 *
	 * @type Inpsyde_Property_List_Interface
	 */
	private $plugin_data;

	/**
	 * Constructor
	 *
	 * @param Inpsyde_Property_List_Interface $data
	 */
	public function __construct( Inpsyde_Property_List_Interface $data ) {

		$this->plugin_data = $data;

		// Quit here if module is turned off
		if ( ! $this->register_setting() )
			return;

		if ( is_admin() ) {
			add_filter( 'mlp_blogs_add_fields', array ( $this, 'draw_form_fields' ) );
			add_filter( 'mlp_blogs_save_fields', array ( $this, 'save_form_fields' ), 10, 1 );

			add_filter( 'mlp_add_custom_columns_header', array ( $this, 'add_custom_columns_header' ) );
			add_filter( 'mlp_add_custom_columns', array ( $this, 'add_custom_columns' ), 10, 2 );
		}
		else {
			// Browser language available? If not, no need to go any further
			if ( ! isset( $_SERVER[ 'HTTP_ACCEPT_LANGUAGE' ] ) )
				return;

			// check to only session_start if no session is active
			if ( ! isset( $_SESSION ) && ! session_id() && ! is_admin() )
				session_start();

			add_action( 'template_redirect', array ( $this, 'template_redirect' ), 1 );

			// add ?noredirect= to show_linked_elements links
			add_filter( 'mlp_linked_element_link', array ( $this, 'add_noredirect_links' ), 10, 3 );
		}
	}

	/**
	 * Register the settings.
	 *
	 * @return bool
	 */
	private function register_setting() {

		$desc = __(
			'Redirect visitors according to browser language settings.',
			'multilingualpress'
		);

		return $this->plugin_data->module_manager->register(
			array (
				'display_name'	=> __( 'HTTP Redirect', 'multilingualpress' ),
				'slug'			=> 'class-' . __CLASS__,
				'description'   => $desc
			)
		);
	}

	/**
	 * Redirect if needed.
	 *
	 * @return void
	 */
	public function template_redirect() {

		if ( ! (bool) get_option( 'inpsyde_multilingual_redirect' ) )
			return;

		$match = $this->get_target_blog();

		if ( empty ( $match ) or $match[ 'blog_id' ] == get_current_blog_id() )
			return; // no redirect possible

		if ( $this->set_cookie() )
			return; // no redirect needed

		$current_lang = mlp_get_current_blog_language();

		if ( isset ( $_SESSION[ 'noredirect' ] ) && in_array( $current_lang, $_SESSION[ 'noredirect' ] ) )
			return; // current language actively selected by user

		if ( $match[ 'language' ] === $current_lang )
			return; // current site is best match

		// now the actual redirect
		$url = $this->get_redirect_url( $match );

		if ( empty ( $url ) )
			return; // no URL found

		wp_redirect( $url ); // finally!
		exit;
	}

	/**
	 * Get the URL for the redirect.
	 *
	 * @param  array $match
	 * @return string
	 */
	private function get_redirect_url( Array $match ) {

		$url     = '';
		$blog_id = $match[ 'blog_id' ];

		if ( is_home() ) {
			$url = user_trailingslashit( get_site_url( $blog_id ) );
		}
		elseif ( is_singular() ) {

			$linked_elements = mlp_get_linked_elements();

			if ( ! empty ( $linked_elements ) ) {

				$post = get_blog_post( $blog_id, $linked_elements[ $blog_id ] );

				if ( $post && 'publish' === $post->post_status )
					$url = get_blog_permalink( $blog_id, $linked_elements[ $blog_id ] );
			}
		}

		/**
		 * Change the URL for redirects. You might add support for other types
		 * than singular posts and home here.
		 * If you return an empty value, the redirect will not happen.
		 * The result will be validated with esc_url().
		 *
		 * @param string $url
		 * @param array  $match 'blog_id'  (int)    target blog ID,
		 *                      'language' (string) target language
		 * @param int    $current_blog_id
		 */
		$url = apply_filters( 'mlp_redirect_url', $url, $match, get_current_blog_id() );

		return esc_url( $url );
	}

	/**
	 * Set $browser_languages and
	 * $defined_browser_lang.
	 *
	 * Example: if the browser has 3 languages set and
	 * we have 3 corresponding blogs, then
	 * we still need to know which blog
	 * to redirect to. We therefore take
	 * the first matching browser-lang/blog-lang combo.
	 *
	 * @return array
	 */
	public function get_target_blog() {

		// Get all blogs
		$lang_blogs = mlp_get_available_languages( FALSE );
		$lang_blogs[ get_current_blog_id() ] = mlp_get_current_blog_language();

		$http  = $this->get_local_values_for_languages( $lang_blogs );
		$user  = $this->get_user_values_for_languages();
		$match = $this->get_best_match( $user, $http );

		if ( empty ( $match ) )
			return array ();

		$_SESSION[ 'defined_browser_lang' ]        = $match[ 'language' ];
		$_SESSION[ 'defined_browser_lang_blogid' ] = $match[ 'blog_id' ];

		return $match;
	}

	/**
	 * Get blog ID and language for the best fitting site.
	 *
	 * @param array $user_values
	 * @param array $local_values
	 * @return array
	 */
	private function get_best_match( Array $user_values, Array $local_values ) {

		/** @var stdClass $p */
		foreach ( $local_values as $key => $p ) {

			if ( isset ( $user_values[ $p->http_name ] ) ) {
				$p->combined_priority = $p->priority * $user_values[ $p->http_name ];
			}
			elseif ( isset ( $user_values[ $p->iso_639_1 ] ) ) {
				$p->combined_priority = $p->priority * $user_values[ $p->iso_639_1 ];
			}
			elseif ( isset ( $p->http_name_short )
				&& isset ( $user_values[ $p->http_name_short ] )
			) {
				$p->combined_priority = $p->priority * $user_values[ $p->http_name_short ];
			}
			elseif ( isset ( $p->wp_locale_short )
				&& isset ( $user_values[ $p->wp_locale_short ] )
			) {
				$p->combined_priority = $p->priority * $user_values[ $p->wp_locale_short ];
			}
			else {
				unset ( $local_values[ $key ] );
			}
		}

		if ( empty ( $local_values ) )
			return array ();

		usort( $local_values, array ( $this, 'sort_languages_by_priority' ) );

		reset( $local_values );

		$best = key( $local_values );

		$match = array (
			'blog_id'  => $local_values[ $best ]->blog_id,
			'language' => $local_values[ $best ]->original
		);

		return $match;
	}

	/**
	 * Callback for usort().
	 *
	 * @param stdClass $first
	 * @param stdClass $second
	 * @return int
	 */
	private function sort_languages_by_priority( stdClass $first, stdClass $second ) {

		if ( $first->combined_priority === $second->combined_priority )
			return 0;

		if ( $first->combined_priority < $second->combined_priority )
			return - 1;

		return 1;
	}

	/**
	 * Inspect HTTP_ACCEPT_LANGUAGE and parse priority parameters.
	 *
	 * @return array
	 */
	private function get_user_values_for_languages() {

		$out   = $temp = array ();
		$parts = explode( ',', $_SERVER[ 'HTTP_ACCEPT_LANGUAGE' ] );
		$parts = array_map( 'trim', $parts );

		foreach ( $parts as $part ) {

			if ( FALSE === strpos( $part, ';' ) ) {

				if ( ! $this->is_valid_language_code( $part ) )
					continue;

				$out[ strtolower( $part ) ] = 1;

				$short = $this->get_short_form( $part );

				if ( '' !== $short )
					$temp[ $short ] = 1;
				continue;
			}

			$lang = strtok( $part, ';' );
			$lang = strtolower( $lang );

			if ( ! $this->is_valid_language_code( $lang ) )
				continue;

			strtok( '=' );
			$value = (float) strtok( ';' );

			$out[ $lang ] = $value;

			$short = $this->get_short_form( $lang );

			if ( '' !== $short )
				$temp[ $short ] = $value;
		}

		if ( empty ( $temp ) )
			return $out;

		foreach ( $temp as $lang => $value ) {
			if ( ! isset ( $out[ $lang ] ) )
				$out[ $lang ] = $value;
		}

		return $out;
	}

	/**
	 * Get the first character of a language code until an '-' or an '_'.
	 *
	 * @param  string $long
	 * @return string
	 */
	private function get_short_form( $long ) {

		if ( ! strpos( $long, '-' ) )
			return '';

		return strtok( $long, '-' );
	}

	/**
	 * Get HTTP names with priority for active languages.
	 *
	 * Receives an array like this:
	 * (
	 *     [1] => en_US
	 *     [41] => fr_FR
	 * )
	 *
	 * @param  array $languages
	 * @return array
	 */
	private function get_local_values_for_languages( Array $languages ) {

		global $wpdb;

		$iso_639_1 = $out = $temp = array ();

		foreach ( $languages as $blog_id => $lang_code ) {
			if ( ! $this->is_valid_language_code( $lang_code ) )
				unset( $languages[ $blog_id ] );
			else
				$iso_639_1[ ] = "'$lang_code'";
		}

		if ( empty ( $iso_639_1 ) )
			return array ();

		$iso_639_1 = join( ',', $iso_639_1 );
		$table     = $GLOBALS[ 'wpdb' ]->base_prefix . 'mlp_languages';

		$query = "SELECT `http_name`, `priority`, `iso_639_1`, `wp_locale` from $table
		WHERE `iso_639_1` IN($iso_639_1) OR `wp_locale` IN($iso_639_1);";

		$found = $wpdb->get_results( $query, OBJECT );

		if ( empty ( $found ) )
			return array ();

		foreach ( $found as $properties ) {

			if ( FALSE !== $blog_id = array_search( $properties->wp_locale, $languages ) )
				$out[ $blog_id ] = $properties;
			elseif ( FALSE !== $blog_id = array_search( $properties->http_name, $languages ) )
				$out[ $blog_id ] = $properties;
		}

		foreach ( $out as $blog_id => $properties ) {

			$properties->wp_locale = strtolower( $properties->wp_locale );
			$properties->http_name = strtolower( $properties->http_name );

			if ( strpos( $properties->wp_locale, '-' ) )
				$properties->wp_locale_short = strtok( $properties->wp_locale, '-' );

			if ( strpos( $properties->wp_locale, '_' ) )
				$properties->wp_locale_short = strtok( $properties->wp_locale, '_' );

			if ( strpos( $properties->http_name, '-' ) )
				$properties->http_name_short = strtok( $properties->http_name, '-' );

			if ( strpos( $properties->http_name, '_' ) )
				$properties->http_name_short = strtok( $properties->http_name, '_' );

			$properties->blog_id  = $blog_id;
			$properties->original = $languages[ $blog_id ];

			$out[ $blog_id ] = $properties;
		}

		return $out;
	}

	/**
	 * Validate language code.
	 *
	 * @param string $language_code
	 * @return bool
	 */
	private function is_valid_language_code( $language_code ) {

		return (bool) preg_match( '~[a-zA-Z_-]~', $language_code );
	}

	/**
	 * We assume that by using the link provided
	 * by the MlP widget, an user wants to read
	 * the blog in it's original language. We
	 * therefore set a cookie to turn off redirection
	 * for this specific blog.
	 *
	 * @return bool TRUE if the cookie was set, FALSE otherwise
	 */
	public function set_cookie() {

		// This GET parameter is the identifier of a widget link
		if ( ! isset ( $_GET[ 'noredirect' ] ) )
			return FALSE;

		if ( ! $this->is_valid_language_code( $_GET[ 'noredirect' ] ) )
			return FALSE;

		$lang_code                = esc_attr( $_GET[ 'noredirect' ] );

		if ( isset ( $_SESSION[ 'noredirect' ] ) )
			$_SESSION[ 'noredirect' ] = (array) $_SESSION[ 'noredirect' ];

		// Language not in SESSION var yet?
		if ( isset ( $_SESSION[ 'noredirect' ] )
			&& in_array( $lang_code, $_SESSION[ 'noredirect' ] )
		)
			return FALSE;

		$_SESSION[ 'noredirect' ][ ] = $lang_code;

		return TRUE;
	}

	/**
	 * Add custom columns header
	 * to network site overview
	 *
	 * @since 0.1
	 * @param array $columns | All columns for the current table
	 * @return array $colums | Extended columns
	 */
	public function add_custom_columns_header( $columns ) {

		//add extra header to table
		$columns[ 'mlp_redirect' ] = __( 'Redirect', 'multilingualpress' );

		return $columns;
	}

	/**
	 * Add a check.gif when browser-redirect = true
	 *
	 * @since 0.1
	 * @param string $column_name | The column name currently used
	 * @param $blog_id | the blog ID for the current table row
	 * @return string $column_name | Column name
	 */
	public function add_custom_columns( $column_name, $blog_id ) {

		$blogoption = get_blog_option( $blog_id, 'inpsyde_multilingual_redirect' );

		//render column value
		if ( 'mlp_redirect' === $column_name ) {

			if ( TRUE == $blogoption ) {
				?><img src="<?php echo plugins_url( '/images', dirname( __FILE__ ) ); ?>/check.png" alt="Redirect" /><?php
			}
		}

		return $column_name;
	}

	/**
	 * Draw form fields for blog settings page
	 *
	 * @since 0.1
	 */
	public function draw_form_fields() {

		$id = isset ( $_REQUEST[ 'id' ] ) ? intval( $_REQUEST[ 'id' ] ) : 0;

		// Get current setting
		$do_redirect = (int) get_blog_option( $id, 'inpsyde_multilingual_redirect' );

		$label   = esc_attr__( 'Enable automatic redirection', 'multilingualpress' );
		$name    = 'inpsyde_multilingual_redirect';
		$id      = "{$name}_id";
		?>
		<tr>
			<td><?php esc_html_e( 'Redirection', 'multilingualpress' ); ?></td>
			<td>
				<label for="<?php print $id; ?>">
					<input type="checkbox" <?php
						checked( 1, $do_redirect );
						?> id="<?php
						print $id;
						?>" value="1" name="<?php
						print $name;
						?>" />
						<?php print $label; ?>
				</label>
			</td>
		</tr>
		<?php
	}

	/**
	 * Validate and save user input
	 *
	 * @since 0.1
	 * @param array $postdata | user input
	 */
	public function save_form_fields( $postdata ) {

		$id = empty ( $postdata[ 'id' ] ) ? get_current_blog_id() : (int) $postdata[ 'id' ];

		if ( isset( $postdata[ 'inpsyde_multilingual_redirect' ] ) )
			$value = (int) isset( $postdata[ 'inpsyde_multilingual_redirect' ] );
		else
			$value = 0;

		if ( 1 < $value )
			$value = 1;

		update_blog_option( $id, 'inpsyde_multilingual_redirect', $value );
	}

	/**
	 * add noredirect query var to the links
	 *
	 * @since 0.1
	 * @param string $link | the current link to the blog
	 * @param string $blog_id | Blog ID of the linked blog
	 * @return string
	 */
	public function add_noredirect_links( $link, $blog_id ) {

		$languages = mlp_get_available_languages();
		// if language is available add to link
		if ( isset( $languages[ $blog_id ] ) )
			$link = add_query_arg( 'noredirect', $languages[ $blog_id ], $link );

		return $link;
	}
}