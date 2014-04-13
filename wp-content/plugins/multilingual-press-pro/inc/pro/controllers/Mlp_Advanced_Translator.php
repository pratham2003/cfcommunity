<?php
/**
 * Class Mlp_Advanced_Translator
 *
 * @version 2014.03.18
 * @author  Inpsyde GmbH, toscho
 * @license GPL
 */
class Mlp_Advanced_Translator {

	/**
	 * Passed by main controller.
	 *
	 * @type Inpsyde_Property_List_Interface
	 */
	private $plugin_data;

	/**
	 * @var Mlp_Advanced_Translator_Data
	 */
	private $translation_data;

	/**
	 * Handle for script and stylesheet.
	 *
	 * @var string
	 */
	private $handle = 'mlp_advanced_translator';

	/**
	 * init function to register all used hooks and set the Database Table
	 *
	 * @param  Inpsyde_Property_List_Interface $data
	 */
	public function __construct( Inpsyde_Property_List_Interface $data ) {

		$this->plugin_data = $data;

		// Quit here if module is turned off
		if ( ! $this->register_setting() )
			return;

		add_action( 'wp_loaded', array ( $this, 'register_script' ) );
		add_action( 'wp_loaded', array ( $this, 'register_style' ) );

		add_action( 'mlp_post_translator_init', array ( $this, 'setup' ) );
		add_filter( 'mlp_external_save_method', '__return_true' );

		// Disable default actions
		add_action(
			'mlp_translation_meta_box_registered',
			array ( $this, 'register_metabox_view_details' ),
			10,
			2
		);
	}

	/**
	 * @wp-hook mlp_post_translator_init
	 * @param  array $base_data
	 * @return void
	 */
	public function setup( Array $base_data ) {

		$this->translation_data = new Mlp_Advanced_Translator_Data(
			$base_data['request_validator'],
			$base_data['basic_data'],
			$base_data['allowed_post_types']
		);

		if ( 'POST' === $_SERVER['REQUEST_METHOD'] )
			add_action( 'save_post', array( $this->translation_data, 'save' ), 10, 2 );

		// Disable the checkbox, we can translate auto-drafts.
		add_filter( 'mlp_post_translator_activation_checkbox', '__return_false' );
		add_filter( 'mlp_translation_meta_box_view_callbacks', '__return_empty_array' );
		add_action( 'admin_enqueue_scripts', array ( $this, 'enqueue_assets' ) );
	}

	/**
	 *
	 * @wp-hook mlp_translation_meta_box_registered
	 * @param  WP_Post $post
	 * @param  int     $blog_id
	 * @return void
	 */
	public function register_metabox_view_details( WP_Post $post, $blog_id ) {

		$view = new Mlp_Advanced_Translator_View( $this->translation_data );
		$base = 'mlp_translation_meta_box_';

		add_action( $base . 'top_' . $blog_id, array ( $view, 'blog_id_input' ), 10, 3 );

		if ( post_type_supports( $post->post_type, 'title' ) )
			add_action( $base . 'top_' . $blog_id, array ( $view, 'show_title' ), 10, 3 );

		if ( post_type_supports( $post->post_type, 'editor' ) )
			add_action( $base . 'main_' . $blog_id, array ( $view, 'show_editor' ), 10, 3 );

		if ( post_type_supports( $post->post_type, 'thumbnail' ) )
			add_action( $base . 'main_' . $blog_id, array ( $view, 'show_thumbnail_checkbox' ), 11, 3 );

		$taxonomies = get_object_taxonomies( $post, 'objects' );

		if ( ! empty ( $taxonomies ) )
			add_action( $base . 'bottom_' . $blog_id, array ( $view, 'show_taxonomies' ), 10, 3 );
	}

	public function enqueue_assets() {

		wp_enqueue_style( $this->handle );
		wp_enqueue_script( $this->handle );
	}

	/**
	 * Register stylesheet.
	 *
	 * @return void
	 */
	public function register_style() {

		wp_register_style(
			$this->handle,
			$this->plugin_data->css_url . 'advanced-translator.css'
		);
	}

	/**
	 * Register admin javascript
	 *
	 * @return  void
	 */
	public function register_script() {

			wp_register_script(
				$this->handle,
				$this->plugin_data->js_url . 'advanced-translator.js'
			);
	}

	/**
	 * @return bool
	 */
	private function register_setting() {

		$desc = __(
			'Use the WYSIWYG editor to write all translations on one screen, including thumbnails and taxonomies.',
			'multilingualpress'
		);

		return $this->plugin_data->module_manager->register(
			array (
				'display_name' => __( 'Advanced Translator', 'multilingualpress' ),
				'slug'         => 'class-' . __CLASS__,
				'description'  => $desc
			)
		);
	}
}