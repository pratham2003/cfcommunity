<?php
/*
Plugin Name: BP Activity Home
Description: When logged in, changes the "All Members" tab on the Sitewide Activity page to "Home".  Clicking on the "Home" tab filters activity to content that is relevant only to you -- your activity, your friend's activity, and your at-mentions.
Author: r-a-y
Author URI: http://profiles.wordpress.org/r-a-y
License: GPLv2 or later
*/

add_action( 'bp_loaded', array( 'BP_Home', 'init' ) );

/**
 * BP Home
 */
class BP_Home {
	/**
	 * Our activity scope name.
	 *
	 * @var string
	 */
	const SCOPE_NAME = 'swa-home';

	/**
	 * Multiple scopes we will be using for our 'Home' tab.
	 *
	 * @var string
	 */
	protected static $scopes = '';

	/**
	 * Marker to determine when to stop our object buffer
	 *
	 * @var bool
	 */
	public $should_end_buffer = false;

	/**
	 * Static initializer.
	 */
	public static function init() {
		return new self();
	}

	/**
	 * Constructor.
	 */
	public function __construct() {
		if ( ! bp_is_active( 'activity' ) ) {
			return;
		}

		// set our default scopes
		$this->set_default_scopes();

		// add in our hooks
		$this->hooks();
	}

	/**
	 * Sets the default scopes used by the "Home" tab.
	 *
	 * Filterable.
	 */
	private function set_default_scopes() {
		$scopes = array();
		$scopes[] = 'just-me';
		$scopes[] = 'mentions';

		if ( bp_is_active( 'friends' ) ) {
			$scopes[] = 'friends';
		}

		self::$scopes = apply_filters( 'bp_activity_home_scopes', $scopes );
	}

	/**
	 * Hooks.
	 */
	private function hooks() {
		add_action( 'bp_activity_screen_index', array( $this, 'set_home_scope' ) );
		add_action( 'wp_logout',                array( $this, 'reset_activity_scope_cookie' ) );

		add_filter( 'bp_after_has_activities_parse_args', array( $this, 'filter_activity_loop' ) );

		add_action( 'bp_before_activity_type_tab_all',       array( $this, 'ob_start' ), 0 );
		add_action( 'bp_before_activity_type_tab_friends',   array( $this, 'ob_end_clean' ), 0 );
		add_action( 'bp_before_activity_type_tab_groups',    array( $this, 'ob_end_clean' ), 0 );
		add_action( 'bp_before_activity_type_tab_favorites', array( $this, 'ob_end_clean' ), 0 );
	}

	/**
	 * Override some activity default / AJAX parameters if we're on the Sitewide Activity page.
	 */
	public function set_home_scope() {


		// if we have a post value already, let's add our scope to the existing cookie value
		if ( !empty( $_POST['cookie'] ) ) {
			$_POST['cookie'] .= '%3B%20bp-activity-scope%3D' . self::SCOPE_NAME;
		} else {
			$_POST['cookie'] = 'bp-activity-scope%3D' . self::SCOPE_NAME;
		}

	}


	/**
	 * Filter the activity loop with our custom scope.
	 */
	public function filter_activity_loop( $r ) {
		if ( self::SCOPE_NAME !== $r['scope'] ) {
			return $r;
		}

		// add in our custom scopes
		$r['scope'] = self::$scopes;

		// change default empty activity text as well while we're at it
		add_filter( 'gettext', array( $this, 'override_empty_message' ), 10, 3 );

		return $r;
	}


	/**
	 * Overrides the "Sorry, there was no activity found. Please try a different filter." message.
	 *
	 * @param string $translated Current translated text
	 * @param string $untranslated Untranslated text
	 * @param string $domain Domain for the string
	 * @return string
	 */
	public function override_empty_message( $translated, $untranslated, $domain ) {
		if ( 'Sorry, there was no activity found. Please try a different filter.' === $untranslated ) {
			$translated = sprintf( __( "It looks like you're new here. To see some activity, try posting an update or <a href='%s'>adding a friend</a>.", 'bp-home' ), bp_get_members_directory_permalink() );
		}

		return $translated;
	}
}