<?php
/**
 * @package WordPress
 * @subpackage BuddyPress Global Search
 */
// Exit if accessed directly
if (!defined('ABSPATH'))
	exit;

if (!class_exists('BuddyBoss_Global_Search_Admin')):

	/**
	 *
	 * BuddyPress Global Search Admin
	 * ********************
	 *
	 *
	 */
	class BuddyBoss_Global_Search_Admin {
		/* Options/Load
		 * ===================================================================
		 */

		/**
		 * Plugin options
		 *
		 * @var array
		 */
		public $options = array();

		private $network_activated = false,
			$plugin_slug = 'buddyboss-globalsearch',
			$menu_hook = 'admin_menu',
			$settings_page = 'options-general.php',
			$capability = 'manage_options',
			$form_action = 'options.php',
			$plugin_settings_url;
		
		/**
		 * Empty constructor function to ensure a single instance
		 */
		public function __construct() {
			// ... leave empty, see Singleton below
		}

		/* Singleton
		 * ===================================================================
		 */

		/**
		 * Admin singleton
		 *
		 * @since 1.0.0
		 *
		 * @param  array  $options [description]
		 *
		 * @uses BuddyBoss_Global_Search_Admin::setup() Init admin class
		 *
		 * @return object BuddyBoss_Global_Search_Admin
		 */
		public static function instance() {
			static $instance = null;

			if (null === $instance) {
				$instance = new BuddyBoss_Global_Search_Admin();
				$instance->setup();
			}

			return $instance;
		}

		/* Utility functions
		 * ===================================================================
		 */

		/**
		 * Get option
		 *
		 * @since BuddyPress Global Search (1.0.0)
		 *
		 * @param  string $key Option key
		 *
		 * @uses BuddyBoss_Global_Search_Plugin::option() Get option
		 *
		 * @return mixed      Option value
		 */
		public function option($key) {
			$value = buddyboss_global_search()->option($key);
			return $value;
		}

		/* Actions/Init
		 * ===================================================================
		 */

		/**
		 * Setup admin class
		 *
		 * @since BuddyPress Global Search (1.0.0)
		 *
		 * @uses buddyboss_global_search() Get options from main BuddyBoss_Global_Search_Plugin class
		 * @uses is_admin() Ensures we're in the admin area
		 * @uses curent_user_can() Checks for permissions
		 * @uses add_action() Add hooks
		 */
		public function setup() {
			if ((!is_admin() && !is_network_admin() ) || !current_user_can('manage_options')) {
				return;
			}

			$this->plugin_settings_url = admin_url( 'options-general.php?page=' . $this->plugin_slug );

			$this->network_activated = $this->is_network_activated();

			//if the plugin is activated network wide in multisite, we need to override few variables
			if ( $this->network_activated ) {
				// Main settings page - menu hook
				$this->menu_hook = 'network_admin_menu';

				// Main settings page - parent page
				$this->settings_page = 'settings.php';

				// Main settings page - Capability
				$this->capability = 'manage_network_options';

				// Settins page - form's action attribute
				$this->form_action = 'edit.php?action=' . $this->plugin_slug;

				// Plugin settings page url
				$this->plugin_settings_url = network_admin_url('settings.php?page=' . $this->plugin_slug);
			}

			//if the plugin is activated network wide in multisite, we need to process settings form submit ourselves
			if ( $this->network_activated ) {
				add_action('network_admin_edit_' . $this->plugin_slug, array( $this, 'save_network_settings_page' ));
			}
		
			/**
			 * Previously, settings were saved in options table to main site in network.
			 * Now, since network settings are saved and retrieved using update_site_option/get_site_option, 
			 * all the sites who had the plugin activated netowrk wide, will loose their settings.
			 * Let's display a message.
			 */
			if ( $this->network_activated && current_user_can( 'manage_network_options' ) ) {
				add_action( 'network_admin_notices',	array( $this, 'admin_notice_update_settings' ) );
			}
			
			add_action( 'admin_init', array( $this, 'admin_init' ) );
			add_action( $this->menu_hook, array( $this, 'admin_menu' ) );

			add_filter( 'plugin_action_links', array( $this, 'add_action_links' ), 10, 2 );
			add_filter( 'network_admin_plugin_action_links', array( $this, 'add_action_links' ), 10, 2 );
		}

		/**
		 * Check if the plugin is activated network wide(in multisite).
		 * 
		 * @return boolean
		 */
		private function is_network_activated() {
		   $network_activated = false;
		   if ( is_multisite() ) {
			   if ( !function_exists('is_plugin_active_for_network') )
				   require_once( ABSPATH . '/wp-admin/includes/plugin.php' );

			   if ( is_plugin_active_for_network('buddypress-global-search/buddypress-global-search.php') ) {
				   $network_activated = true;
			   }
		   }
		   return $network_activated;
		}
		
		/**
		 * Register admin settings
		 *
		 * @since 1.0.0
		 *
		 * @uses register_setting() Register plugin options
		 * @uses add_settings_section() Add settings page option sections
		 * @uses add_settings_field() Add settings page option
		 */
		public function admin_init() {
			register_setting( 'buddyboss_global_search_plugin_options', 'buddyboss_global_search_plugin_options', array($this, 'plugin_options_validate'));
			add_settings_section( 'general_section', __( 'General Settings', 'buddypress-global-search' ), array($this, 'section_general'), __FILE__);
			//add_settings_section( 'style_section', 'Style Settings', array( $this, 'section_style' ), __FILE__ );
			//general options
			add_settings_field('items-to-search', __( 'Items To Search', 'buddypress-global-search' ), array($this, 'setting_items_to_search'), __FILE__, 'general_section');
			add_settings_field('enable-ajax-search', __( 'AutoSuggest', 'buddypress-global-search' ), array($this, 'setting_enable_ajax_search'), __FILE__, 'general_section');
		}

		/**
		 * Add plugin settings page
		 *
		 * @since BuddyPress Global Search (1.0.0)
		 *
		 * @uses add_options_page() Add plugin settings page
		 */
		public function admin_menu() {
			//add_options_page('BP Global Search', 'BP Global Search', 'manage_options', __FILE__, array($this, 'options_page'));
			add_submenu_page(
				$this->settings_page, 'BP Global Search', 'BP Global Search', $this->capability, $this->plugin_slug, array( $this, 'options_page' )
			);
		}

		/* Settings Page + Sections
		 * ===================================================================
		 */

		/**
		 * Render settings page
		 *
		 * @since 1.0.0
		 *
		 * @uses do_settings_sections() Render settings sections
		 * @uses settings_fields() Render settings fields
		 * @uses esc_attr_e() Escape and localize text
		 */
		public function options_page() {
			?>
			<div class="wrap">
				<h2><?php _e( 'BuddyPress Global Search', 'buddypress-global-search' ); ?></h2>
				<div class="updated fade">
					<p><?php _e( 'Need BuddyPress customizations?', 'buddypress-global-search' ); ?>  &nbsp;<a href="http://buddyboss.com/buddypress-developers/" target="_blank"><?php _e( 'Say hello.', 'buddypress-global-search' ); ?></a></p>
				</div>
				<div class="content-wrapper clearfix">
					<div class="settings">
						<div class="padder">
							<form method="post" action="<?php echo $this->form_action; ?>">

								<?php
								if ( $this->network_activated && isset($_GET['updated']) ) {
									echo "<div class='updated'><p>" . __('Settings updated.', 'buddypress-edit-activity') . "</p></div>";
								}
								?>
								
								<?php settings_fields('buddyboss_global_search_plugin_options'); ?>
								<?php do_settings_sections(__FILE__); ?>

								<p class="submit">
									<input name="bboss_g_s_settings_submit" type="submit" class="button-primary" value="<?php esc_attr_e('Save Changes'); ?>" />
								</p>
							</form>
						</div>
					</div>
					<div style="clear: both"></div>
				</div>
			</div>
			<?php
		}

		/**
		 * General settings section
		 *
		 * @since BuddyPress Global Search (1.0.0)
		 */
		public function section_general() {
			
		}

		/**
		 * Style settings section
		 *
		 * @since BuddyPress Global Search (1.0.0)
		 */
		public function section_style() {
			
		}

		/**
		 * Validate plugin option
		 *
		 * @since 1.0.0
		 */
		public function plugin_options_validate($input) {
			if( !isset( $input['enable-ajax-search'] ) || !$input['enable-ajax-search'] )
				$input['enable-ajax-search'] = 'no';

			return $input; // return validated input
		}

		/* Settings Page Options
		 * ===================================================================
		 */
		
		/**
		 * Setting > Whether to have autosuggest search dropdown
		 *
		 * @since 1.0.3
		 *
		 * @uses BuddyBoss_Global_Search_Admin::option() Get plugin option
		 */
		public function setting_enable_ajax_search(){
			$enabled = $this->option('enable-ajax-search');
			$checked = $enabled=='yes' ? ' checked' : '';
			echo '<label><input type="checkbox" name="buddyboss_global_search_plugin_options[enable-ajax-search]" value="yes" '. $checked . '>' . __( 'Enable AutoSuggest dropdown in search inputs.', 'buddypress-global-search' ) . '</label>';
		}
		
		/**
		 * Setting > what to search?
		 *
		 * @since 1.0.0
		 *
		 * @uses BuddyBoss_Global_Search_Admin::option() Get plugin option
		 */
		public function setting_items_to_search() {
			$items_to_search = $this->option('items-to-search');

			echo '<p class="description">' . __('Search the following components:', 'buddypress-global-search') . '</p><br />';
			
			$items = array(
				'posts'		=> __( 'Blog Posts', 'buddypress-global-search' ),
				'members'	=> __( 'Members', 'buddypress-global-search' ),
			);
			
			//forums?
			if ( is_plugin_active( 'bbpress/bbpress.php' ) ) {
				$items['forums'] = __( 'Forums', 'buddypress-global-search' );
			}
			
			//other buddypress components
			$bp_components = array(
				'groups'		=> __( 'Groups', 'buddypress-global-search' ),
				'activity'		=> __( 'Activity', 'buddypress-global-search' ),
				'messages'		=> __( 'Messages', 'buddypress-global-search' ),
				/* should we search notifications as well?
				'notifications'	=> __( 'Notifications', 'buddypress-global-search' ), */
			);
			//only the active ones please!
			foreach( $bp_components as $component=>$label ){
				if( function_exists( 'bp_is_active' ) && bp_is_active( $component ) ){
					$items[$component] = $label;
				}
			}
			
			//now print those items
			foreach( $items as $item=>$label ){
				$checked = !empty( $items_to_search ) && in_array( $item, $items_to_search ) ? ' checked' : '';
				echo "<label><input type='checkbox' value='{$item}' name='buddyboss_global_search_plugin_options[items-to-search][]' {$checked}>{$label}</label><br>";
			}
			
			/**
			 * Use the action below to add more things in the list of searchable items.
			 * This will just print those new items in admin section. You'll have hook into other actions/filters to actually perform the search.
			 */
			do_action( 'bboss_global_search_settings_items_to_search', $items_to_search );
		}
		
		public function add_action_links( $links, $file ) {
			// Return normal links if not this plugin
			if ( plugin_basename(basename(constant('BUDDYBOSS_GLOBAL_SEARCH_PLUGIN_DIR')) . '/buddypress-global-search.php') != $file ) {
				return $links;
			}

			$mylinks = array(
				'<a href="' . esc_url($this->plugin_settings_url) . '">' . __("Settings", "buddypress-global-search") . '</a>',
			);
			return array_merge($links, $mylinks);
		}

		public function save_network_settings_page() {
			if ( !check_admin_referer('buddyboss_global_search_plugin_options-options') )
				return;

			if ( !current_user_can($this->capability) )
				die('Access denied!');

			if ( isset( $_POST['bboss_g_s_settings_submit'] ) ) {
				$submitted = stripslashes_deep($_POST['buddyboss_global_search_plugin_options']);
				$submitted = $this->plugin_options_validate($submitted);

				update_site_option('buddyboss_global_search_plugin_options', $submitted);
			}

			// Where are we redirecting to?
			$base_url = trailingslashit(network_admin_url()) . 'settings.php';
			$redirect_url = add_query_arg(array( 'page' => $this->plugin_slug, 'updated' => 'true' ), $base_url);

			// Redirect
			wp_redirect($redirect_url);
			die();
		}
		
		public function admin_notice_update_settings(){
			//hide notice if user has selected to do so
			if( isset( $_GET['page'] ) && $_GET['page']==$this->plugin_slug && isset( $_GET['hidenotice'] ) ){
				update_site_option( 'bboss_g_s_upgrade_from_1_0_7', 'yes' );
			}
			
			//dont display it if user had hidden this message
			if( get_site_option( 'bboss_g_s_upgrade_from_1_0_7', 'no' ) == 'yes' )
				return;
			
			// Where are we redirecting to?
			$base_url = trailingslashit( network_admin_url() ) . 'settings.php';
			$settings_url = add_query_arg( array( 'page' => $this->plugin_slug ), $base_url );
			$settings_link = "<a href='" . esc_url( $settings_url ) . "'>" . __( 'Settings', 'buddypress-global-search' ) . "</a>";
			
			$notice = sprintf( __( "Hey! BuddyPress Global Search has better integration with multisite now. Your settings might have been reset to defaults after update. Please check your %s.", 'buddypress-global-search' ), $settings_link );

			$hide_notice_url = add_query_arg( array( 'hidenotice' => true ), $settings_url );
			
			echo "<div class='update-nag'><p>{$notice}</p><p><a href='{$hide_notice_url}' class='button'>". __( 'Hide this notice', 'buddypress-global-search' ) ."</a></div>";
		}
	}

// End class BuddyBoss_Global_Search_Admin

endif;
?>