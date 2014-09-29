<?php
//if ( !is_super_admin() ):
add_filter('show_admin_bar', '__return_false');
//endif;

//Automatically login user after registration
add_action("gform_user_registered", "autologin", 10, 4);
function autologin($user_id, $config, $entry, $password) {
        wp_set_auth_cookie($user_id, false, '');
}

function bp_profile_homepage()
//Redirect logged in users from homepage to activity
{
	global $bp;
	if( is_user_logged_in() && bp_is_front_page() && !get_user_meta( $user->ID, 'last_activity', true ) )
	{
		wp_redirect( home_url( $bp->activity->root_slug ), 301 );
	}
}
add_action('wp','bp_profile_homepage');


// Login Shortcode
function pippin_login_form_shortcode( $atts, $content = null ) {

	extract( shortcode_atts( array(
      'redirect' => ''
      ), $atts ) );

	if (!is_user_logged_in()) {
		if($redirect) {
			$redirect_url = $redirect;
		} else {
			$redirect_url = get_permalink();
		}
		$form = wp_login_form(array('echo' => false, 'redirect' => $redirect_url ));
	}
	return $form;
}
add_shortcode('loginform', 'pippin_login_form_shortcode');



/**
 * Redirect user after successful login.
 *
 * @param string $redirect_to URL to redirect to.
 * @param string $request URL the user is coming from.
 * @param object $user Logged user's data.
 * @return string
 */
function my_login_redirect( $redirect_to, $request, $user ) {
	//is there a user to check?
	global $user;
	if ( isset( $user->roles ) && is_array( $user->roles ) ) {
		//check for admins
		if ( in_array( 'administrator', $user->roles ) ) {
			// redirect them to the default place
			return $redirect_to;
		} else {
			return home_url();
		}
	} else {
		return $redirect_to;
	}
}
add_filter( 'login_redirect', 'my_login_redirect', 10, 3 );

/**
 * Add Typekit
 *
 * @package cfcommunity
 */
function theme_typekit_inline() {
?>
    <script type="text/javascript" src="//use.typekit.net/nfj3xsx.js"></script>
    <script type="text/javascript">try{Typekit.load();}catch(e){}</script>
<?php
}
add_action( 'wp_head', 'theme_typekit_inline' );

?>