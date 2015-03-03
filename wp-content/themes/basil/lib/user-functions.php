<?php
/**
 * Returns currently logged in user's ID or NULL if the user is not logged in
 * @return int
 */
function crb_is_logged_in() {
	get_currentuserinfo();
	global $user_ID;
	return $user_ID;
}


/**
 * Returns the currently logged in user's object
 * @return array
 */
function crb_get_current_user() {
	global $userdata;
	get_currentuserinfo();
	return $userdata;
}

/**
 * Redirects if the current user is not logged in. Be careful with the $redirect -
 * may cause infinite redirection loop if the redirect requires login as well
 * @param  string $redirect URL
 */
function crb_require_login($redirect = '') {
	if (!crb_is_logged_in()) {
		$redirect = ($redirect) ? $redirect : home_url();
		header('Location: ' . $redirect);
		exit;
	}
}

/**
 * Redirects if the current user is not of the specified level. Admins are always alowed.
 * @param  string $level required user capability
 * @param  string $redirect URL address to redirect when the user doesn't have the required capability
 */
function crb_require_user_level($level, $redirect = '') {
	$u = _get_current_user();
	if (!crb_user_is($u->ID, 'administrator') && !crb_user_is($u->ID, $level)) {
		$redirect = ($redirect) ? $redirect : home_url();
		header('Location: ' . $redirect);
	}
}

/**
 * Returns boolean indicating whether the specified user has the specified role
 * @param  int $user_id
 * @param  string $capability
 * @return boolean
 */
function crb_user_is($user_id, $capability) {
	global $wpdb;
	$all_capabilities = get_user_meta($user_id, $wpdb->prefix . 'capabilities', true);
	return isset($all_capabilities[$capability]);
}

