<?php
/**
 * Module Name:	default-actions
 * Description:	Set default actions for every blog
 * Author:		Inpsyde GmbH
 * Version:		0.2
 * Author URI:	http://inpsyde.com
 *
 * Changelog
 *
 * 0.1
 * - Added always translate post option
 */

class Mlp_Default_Actions extends Multilingual_Press {

	/**
	 * Static class object variable
	 *
	 * @var object $class_object
	 */
	static protected $class_object = NULL;

	/**
	 * Load the object and get the current state
	 *
	 * @access public
	 * @since 0.1
	 * @return $class_object
	 */
	public static function get_object() {

		if ( NULL == self::$class_object ) {
			self::$class_object = new self;
		}
		return self::$class_object;
	}

	/**
	 * init function to register all used hooks
	 *
	 * @access public
	 * @since 0.1
	 * @uses add_filter
	 * @return void
	 */
	public function __construct() {

		// Quit here if module is turned off
		if ( FALSE === $this->module_init() )
			return;

		add_filter( 'mlp_blogs_add_fields', array( $this, 'draw_form_fields' ) );
		add_filter( 'mlp_blogs_save_fields', array( $this, 'save_form_fields' ), 10, 1 );

		// handle translate this post checkbox
		add_filter( 'inpsyde_multilingualpress_translate_this_post_checkbox', array( $this, 'translate_this_post_checkbox' ) );

	}

	/**
	 * Determine the current module state (on/off).
	 *
	 * @since 0.1
	 * @return FALSE  | if turned off
	 */
	private function module_init() {

		// Check module state
		$module_init = array(
			'display_name'	=> __( 'Default Actions', 'multilingualpress' ),
			'slug'			=> 'class-' . __CLASS__
		);

		if ( 'off' === parent::get_module_state( $module_init ) )
			return FALSE;
	}

	/**
	 * return the module description
	 *
	 * @access	public
	 * @since	0.2
	 * @uses	__
	 * @return	string
	 */
	public function get_module_description() {

		return __( 'Set default actions for every site.', 'multilingualpress' );
	}

	/**
	 * Draw form fields for blog settings page
	 *
	 * @since 0.1
	 */
	public function draw_form_fields() {

		$id = isset ( $_REQUEST[ 'id' ] ) ? intval( $_REQUEST[ 'id' ] ) : 0;

		// Get current setting
		$default_actions = get_blog_option( $id, 'inpsyde_multilingual_default_actions', array() );

		if ( ! isset( $default_actions[ 'always_translate_posts' ] ) )
			$default_actions[ 'always_translate_posts' ] = FALSE;
		// Draw table & form fields
		?>
		<div class="postbox">
			<div title="Click to toggle" class="handlediv"><br></div>
			<h3 class="hndle" style="padding: 7px 10px"><?php _e( 'Default Actions', 'multilingualpress' ); ?></h3>
			<div class="inside">
				<table class="form-table">
					<tbody>
						<tr>
							<th scope="row">
								<?php _e( 'Always translate posts', 'multilingualpress' ); ?>
							</th>
							<td>
								<input type="checkbox" <?php checked( $default_actions[ 'always_translate_posts' ], TRUE ); ?> id="inpsyde_action_always_translate_posts" value="true" name="inpsyde_action_always_translate_posts" />
								<span class="description"><?php _e( 'Always enable the "Translate this post" checkbox', 'multilingualpress' ); ?></span>
							</td>
						</tr>
					</tbody>
				</table>
			</div>
		</div>
		<?php
	}

	/**
	 * Validate and save user input
	 *
	 * @since 0.1
	 * @param array $postdata | user input
	 */
	public function save_form_fields( $postdata ) {

		// Process user input
		$default_actions = array(
			'always_translate_posts' => ! isset( $postdata[ 'inpsyde_action_always_translate_posts' ] ) || 'true' != $postdata[ 'inpsyde_action_always_translate_posts' ] ? FALSE : TRUE
		);

		// Save blog actions setting
		if ( 0 !== intval( $postdata[ 'id' ] ) )
			update_blog_option( intval( $postdata[ 'id' ] ), 'inpsyde_multilingual_default_actions', $default_actions );
	}

	/**
	 * set the checkbox "Translate this post" to checked if enabled in blog settings
	 *
	 * @since 0.1
	 * @param array $postdata | user input
	 */
	public function translate_this_post_checkbox() {

		$default_actions = get_blog_option( get_current_blog_id(), 'inpsyde_multilingual_default_actions', array() );

		return isset( $default_actions[ 'always_translate_posts' ] ) && $default_actions[ 'always_translate_posts' ] == TRUE ? TRUE : FALSE;
	}

}

if ( function_exists( 'add_filter' ) )
	Multilingual_Press_Default_Actions::get_object();
?>