<?php
/*
Plugin Name: Easy Digital Downloads - Email Templates
Plugin URL: http://easydigitaldownloads.com/extension/email-templates
Description: Adds beautiful purchase receipt templates to EDD
Version: 1.0.3
Author: Pippin Williamson and Adam Pickering
Author URI: http://pippinsplugins.com
Contributors: mordauk
*/

if(!defined('EDDET_PLUGIN_DIR')) {
	define('EDDET_PLUGIN_DIR', dirname(__FILE__));
}

if(!defined('EDDET_PLUGIN_URL')) {
    define('EDDET_PLUGIN_URL', plugin_dir_url( __FILE__ ));
}

define( 'EDD_ETEMPLATES_STORE_API_URL', 'http://easydigitaldownloads.com' ); 
define( 'EDD_ETEMPLATES_PRODUCT_NAME', 'Email Templates' ); 
define( 'EDD_ETEMPLATES_VERSION', '1.0.3' ); 



/*
|--------------------------------------------------------------------------
| INTERNATIONALIZATION
|--------------------------------------------------------------------------
*/

function edd_et_textdomain() {
	load_plugin_textdomain( 'edd_et', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
}
add_action('init', 'edd_et_textdomain');



/**
 * Registers the Templates
 * *
 * @access      private
 * @since       1.0
 * @param		$templates array all existing templates 
 * @return      array
*/

function edd_et_register_templates( $templates ) {

	$new_templates = array(
		'post_card' => __('Post Card', 'edd_et'),
		'clean' 	=> __('Clean', 'edd_et'),
		'smooth' 	=> __('Smooth', 'edd_et'),
		'purple' 	=> __('Purple', 'edd_et'),
		'blue' 		=> __('Blue', 'edd_et'),
		'green' 	=> __('Green', 'edd_et'),
		'red' 		=> __('Red', 'edd_et'),
		'yellow' 	=> __('Yellow', 'edd_et'),
		'beigegreen'=> __('Beige Green', 'edd_et'),
		'orange' 	=> __('Orange', 'edd_et')
	);

	return array_merge( $templates, $new_templates );

}
add_filter('edd_email_templates', 'edd_et_register_templates');


/**
 * Registers the new Logo option in Settings > Emails
 * *
 * @access      private
 * @since       1.0
 * @param 		$settings array the existing plugin settings
 * @return      array
*/

function edd_et_logo_settings( $settings ) {

	$logo_settings = array(
		array(
			'id' => 'email_logo',
			'name' => __('Email Logo', 'edd_et'),
			'desc' => __('Upload or choose a logo to be displayed at the top of the email', 'edd_et'),
			'type' => 'upload',
			'size' => 'regular'
		),
		array(
			'id' => 'edd_et_license_key',
			'name' => __('License Key', 'edd_et'),
			'desc' => __('Enter your license for Email Templates to receive automatic upgrades', 'edd_et'),
			'type' => 'text',
			'size' => 'regular'
		)
	);

	return array_merge( $logo_settings, $settings );

}
add_filter('edd_settings_emails', 'edd_et_logo_settings');


/**
 * Sets up the HTML for the Post Card Template
 * *
 * @access      private
 * @since       1.0
 * @return      void
*/

function edd_et_post_card() {	
	
	global $edd_options;

	echo '<div style="min-width: 610px; padding:10px; background: url(' . EDDET_PLUGIN_URL . 'images/postcard/pattern.png);">';
		echo '<div style="width: 610px; margin: 0 auto; padding: 24px 0 0 0; background:url(' . EDDET_PLUGIN_URL . 'images/postcard/stripes.png) no-repeat;">';
			echo '<div id="edd-email-content" style="padding: 20px 30px 40px; text-align: center; background: url(' . EDDET_PLUGIN_URL . 'images/postcard/middle.png) repeat-y;">';
				echo '<div style="background:url(' . EDDET_PLUGIN_URL . 'images/postcard/stamp.gif) no-repeat right top;min-height:98px;">';
					if( isset( $edd_options['email_logo']) ) {
						echo '<img src="' . $edd_options['email_logo'] . '" style="margin:10px 0 30px;"/>';
					}
				echo '</div>';
				echo '<div style="background:url(' . EDDET_PLUGIN_URL . 'images/postcard/paid.gif) no-repeat bottom right; padding-bottom:50px;">{email}</div>'; // this tag is required in order for the contents of the email to be shown
			echo '</div>';	
			echo '<div style="height: 34px; display:block!important; background: url(' . EDDET_PLUGIN_URL . 'images/postcard/bottom.png) no-repeat left bottom;"></div>';
		echo '</div>';
	echo '</div>';
	
}
add_action('edd_email_template_post_card', 'edd_et_post_card');


/**
 * Adds extra styling to certain HTML tags in the Post Card Template
 * *
 * @access      private
 * @since       1.0
 * @param 		$email_body string the contents of the email
 * @return      void
*/

function edd_et_post_card_extra_styling( $email_body ) {

	$email_body = str_replace('<ul>', '<ul style="margin:0;padding:0;">', $email_body );
	$email_body = str_replace('<li>', '<li style="line-height: 18px; margin: 0 0 10px; padding: 0; color: #505050; font-family: verdana, arial, sans-serif; display:block;">', $email_body );
	$email_body = str_replace('<p>', '<p style="line-height: 24px; margin: 0 0 20px; color: #505050; font-family: verdana, arial, sans-serif;">', $email_body );
	$email_body = str_replace('<h1>', '<h1 style="line-height: 24px; margin: 0 0 29px; text-transform: uppercase; font-size: 20px; color: #505050; font-family: verdana, arial, sans-serif;">', $email_body );
	$email_body = str_replace('<h2>', '<h2 style="line-height: 24px; margin: 0 0 29px; text-transform: uppercase; font-size: 20px; color: #505050; font-family: verdana, arial, sans-serif;">', $email_body );
	$email_body = str_replace('<h3>', '<h3 style="line-height: 24px; margin: 0 0 29px; text-transform: uppercase; font-size: 20px; color: #505050; font-family: verdana, arial, sans-serif;">', $email_body );

	return $email_body;
}
add_filter('edd_purchase_receipt_post_card', 'edd_et_post_card_extra_styling');


/**
 * Sets up the HTML for the Clean Template
 * *
 * @access      private
 * @since       1.0
 * @return      void
*/

function edd_et_clean() {	
	
	global $edd_options;

	// wrap
	echo '<div style="background: #437E98;padding: 0 0 30px;">';
		
		// header
		echo '<div style="margin:0 0 25px;">';
			echo '<div style="width:640px;margin: 0 auto;">';
				if( isset( $edd_options['email_logo']) ) {
					echo '<img src="' . $edd_options['email_logo'] . '" style="margin:15px 30px 0;position:relative;z-index:2;"/>';
				}
			echo '</div>';
		echo '</div>';

		// main content
		echo '<div style="width: 610px; margin: 0 auto; background: url(' . EDDET_PLUGIN_URL . 'images/clean/middle.png) repeat-y;">';

			echo '<div style="background:url(' . EDDET_PLUGIN_URL . 'images/clean/top.png); height: 105px;"></div>';

			echo '<div id="edd-email-content" style="position:relative; text-align: left; padding: 0 50px; margin: -50px 0 0;">';
				echo '{email}'; // this tag is required in order for the contents of the email to be shown
			echo '</div>';

			echo '<div style="display:block!important;height: 10px; background: url(' . EDDET_PLUGIN_URL . 'images/clean/bottom.png) no-repeat left bottom;"></div>';
		
		echo '</div>'; // end main content

	echo '</div>'; // end wrap
	
}
add_action('edd_email_template_clean', 'edd_et_clean');


/**
 * Adds extra styling to certain HTML tags in the Clean Template
 * *
 * @access      private
 * @since       1.0
 * @param 		$email_body string the contents of the email
 * @return      void
*/

function edd_et_clean_extra_styling( $email_body ) {

	$email_body = str_replace('<ul>', '<ul style="margin:0;padding:0;">', $email_body );
	$email_body = str_replace('<li>', '<li style="line-height: 18px; margin: 0 0 10px; padding: 0; color: #505050; font-family: verdana, arial, sans-serif; display:block;">', $email_body );
	$email_body = str_replace('<p>', '<p style="line-height: 24px; margin: 0 0 20px; color: #505050; font-family: verdana, arial, sans-serif;">', $email_body );
	$email_body = str_replace('<h1>', '<h1 style="line-height: 24px; margin: 0 0 29px; text-transform: uppercase; font-size: 20px; color: #505050; font-family: verdana, arial, sans-serif;">', $email_body );
	$email_body = str_replace('<h2>', '<h2 style="line-height: 24px; margin: 0 0 29px; text-transform: uppercase; font-size: 20px; color: #505050; font-family: verdana, arial, sans-serif;">', $email_body );
	$email_body = str_replace('<h3>', '<h3 style="line-height: 24px; margin: 0 0 29px; text-transform: uppercase; font-size: 20px; color: #505050; font-family: verdana, arial, sans-serif;">', $email_body );

	return $email_body;
}
add_filter('edd_purchase_receipt_clean', 'edd_et_clean_extra_styling');


/**
 * Sets up the HTML for the Smooth Template
 * *
 * @access      private
 * @since       1.0
 * @return      void
*/

function edd_et_smooth() {	
	
	global $edd_options;

	// wrap
	echo '<div style="background: #fff;">';
		// header
		echo '<div style="width: 620px; padding: 0 160px; margin: 0 auto 20px;">';
		if( isset( $edd_options['email_logo']) ) {
			echo '<img src="' . $edd_options['email_logo'] . '" style="margin:15px 30px 0;position:relative;z-index:2;"/>';
		}
		echo '</div>';
		// main content
		echo '<div style="width: 620px; padding: 36px 160px 0; margin: 0 auto; background: url(' . EDDET_PLUGIN_URL . 'images/smooth/gradient.gif) no-repeat;">';

			echo '<div id="edd-email-content" style="position:relative; text-align: left;">';
				echo '<div style="position:relative;z-index:2;">{email}</div>'; // this tag is required in order for the contents of the email to be shown
			echo '</div>';

		
		echo '</div>'; // end main content

		echo '<div style="width: 620px; padding: 36px 160px 0; margin: 0 auto; background: url(' . EDDET_PLUGIN_URL . 'images/smooth/gradient.gif) no-repeat;"></div>';

	echo '</div>'; // end wrap
	
}
add_action('edd_email_template_smooth', 'edd_et_smooth');


/**
 * Adds extra styling to certain HTML tags in the Smooth Template
 * *
 * @access      private
 * @since       1.0
 * @param 		$email_body string the contents of the email
 * @return      void
*/

function edd_et_smooth_extra_styling( $email_body ) {

	$email_body = str_replace('<ul>', '<ul style="margin:0;padding:0;">', $email_body );
	$email_body = str_replace('<li>', '<li style="line-height: 18px; margin: 0 0 10px; padding: 0; color: #505050; font-family: verdana, arial, sans-serif; display:block;">', $email_body );
	$email_body = str_replace('<p>', '<p style="line-height: 24px; margin: 0 0 20px; color: #505050; font-family: verdana, arial, sans-serif;">', $email_body );
	$email_body = str_replace('<h1>', '<h1 style="line-height: 32px; margin: 0 0 29px; text-transform: uppercase; font-weight: normal; font-size: 26px; color: #575757; font-family: Georgia, \'Times New Roman\', sans-serif;">', $email_body );
	$email_body = str_replace('<h2>', '<h2 style="line-height: 30px; margin: 0 0 29px; text-transform: uppercase; font-weight: normal; font-size: 24px; color: #575757; font-family: Georgia, \'Times New Roman\', sans-serif;">', $email_body );
	$email_body = str_replace('<h3>', '<h3 style="line-height: 28px; margin: 0 0 29px; text-transform: uppercase; font-weight: normal; font-size: 22px; color: #575757; font-family: Georgia, \'Times New Roman\', sans-serif;">', $email_body );

	return $email_body;
}
add_filter('edd_purchase_receipt_smooth', 'edd_et_smooth_extra_styling');


/**
 * Sets up the HTML for the Purple Template
 * *
 * @access      private
 * @since       1.0
 * @return      void
*/

function edd_et_purple() {	
	
	global $edd_options;

	// wrap
	echo '<div style="background: url(' . EDDET_PLUGIN_URL . 'images/purple/sand.png); padding: 0 0 30px;">';
		// header
		echo '<div style="background: url(' . EDDET_PLUGIN_URL . 'images/purple/pattern.png);">';
			echo '<div style="width: 605px; padding: 50px 0; margin: 0 auto;">';
				if( isset( $edd_options['email_logo']) ) {
					echo '<img src="' . $edd_options['email_logo'] . '"/>';
				}
			echo '</div>';
		echo '</div>';
		// main content
		echo '<div style="width: 620px; padding: 36px 160px 0; margin: 0 auto;">';

			echo '<div id="edd-email-content" style="position:relative; text-align: left;">';
				echo '<div style="position:relative;z-index:2;">{email}</div>'; // this tag is required in order for the contents of the email to be shown
			echo '</div>';
		
		echo '</div>'; // end main content

		echo '<div style="background: url(' . EDDET_PLUGIN_URL . 'images/purple/pattern.png); height:40px;"></div>';

	echo '</div>'; // end wrap
	
}
add_action('edd_email_template_purple', 'edd_et_purple');

/**
 * Sets up the HTML for the Orange Template
 * *
 * @access      private
 * @since       1.0
 * @return      void
*/

function edd_et_orange() {	
	
	global $edd_options;

	// wrap
	echo '<div style="background: url(' . EDDET_PLUGIN_URL . 'images/sand.png); padding: 0 0 30px;">';
		// header
		echo '<div style="background: url(' . EDDET_PLUGIN_URL . 'images/orange/pattern.png);">';
			echo '<div style="width: 605px; padding: 50px 0; margin: 0 auto;">';
				if( isset( $edd_options['email_logo']) ) {
					echo '<img src="' . $edd_options['email_logo'] . '"/>';
				}
			echo '</div>';
		echo '</div>';
		// main content
		echo '<div style="width: 620px; padding: 36px 160px 0; margin: 0 auto;">';

			echo '<div id="edd-email-content" style="position:relative; text-align: left;">';
				echo '<div style="position:relative;z-index:2;">{email}</div>'; // this tag is required in order for the contents of the email to be shown
			echo '</div>';
		
		echo '</div>'; // end main content

		echo '<div style="background: url(' . EDDET_PLUGIN_URL . 'images/orange/pattern.png); height:40px;"></div>';

	echo '</div>'; // end wrap
	
}
add_action('edd_email_template_orange', 'edd_et_orange');


/**
 * Sets up the HTML for the Red Template
 * *
 * @access      private
 * @since       1.0
 * @return      void
*/

function edd_et_red() {	
	
	global $edd_options;

	// wrap
	echo '<div style="background: url(' . EDDET_PLUGIN_URL . 'images/sand.png); padding: 0 0 30px;">';
		// header
		echo '<div style="background: url(' . EDDET_PLUGIN_URL . 'images/red/pattern.png);">';
			echo '<div style="width: 605px; padding: 50px 0; margin: 0 auto;">';
				if( isset( $edd_options['email_logo']) ) {
					echo '<img src="' . $edd_options['email_logo'] . '"/>';
				}
			echo '</div>';
		echo '</div>';
		// main content
		echo '<div style="width: 620px; padding: 36px 160px 0; margin: 0 auto;">';

			echo '<div id="edd-email-content" style="position:relative; text-align: left;">';
				echo '<div style="position:relative;z-index:2;">{email}</div>'; // this tag is required in order for the contents of the email to be shown
			echo '</div>';
		
		echo '</div>'; // end main content

		echo '<div style="background: url(' . EDDET_PLUGIN_URL . 'images/red/pattern.png); height:40px;"></div>';

	echo '</div>'; // end wrap
	
}
add_action('edd_email_template_red', 'edd_et_red');


/**
 * Sets up the HTML for the Green Template
 * *
 * @access      private
 * @since       1.0
 * @return      void
*/

function edd_et_green() {	
	
	global $edd_options;

	// wrap
	echo '<div style="background: url(' . EDDET_PLUGIN_URL . 'images/sand.png); padding: 0 0 30px;">';
		// header
		echo '<div style="background: url(' . EDDET_PLUGIN_URL . 'images/green/pattern.png);">';
			echo '<div style="width: 605px; padding: 50px 0; margin: 0 auto;">';
				if( isset( $edd_options['email_logo']) ) {
					echo '<img src="' . $edd_options['email_logo'] . '"/>';
				}
			echo '</div>';
		echo '</div>';
		// main content
		echo '<div style="width: 620px; padding: 36px 160px 0; margin: 0 auto;">';

			echo '<div id="edd-email-content" style="position:relative; text-align: left;">';
				echo '<div style="position:relative;z-index:2;">{email}</div>'; // this tag is required in order for the contents of the email to be shown
			echo '</div>';
		
		echo '</div>'; // end main content

		echo '<div style="background: url(' . EDDET_PLUGIN_URL . 'images/green/pattern.png); height:40px;"></div>';

	echo '</div>'; // end wrap
	
}
add_action('edd_email_template_green', 'edd_et_green');


/**
 * Sets up the HTML for the Yellow Template
 * *
 * @access      private
 * @since       1.0
 * @return      void
*/

function edd_et_yellow() {	
	
	global $edd_options;

	// wrap
	echo '<div style="background: url(' . EDDET_PLUGIN_URL . 'images/sand.png); padding: 0 0 30px;">';
		// header
		echo '<div style="background: url(' . EDDET_PLUGIN_URL . 'images/yellow/pattern.png);">';
			echo '<div style="width: 605px; padding: 50px 0; margin: 0 auto;">';
				if( isset( $edd_options['email_logo']) ) {
					echo '<img src="' . $edd_options['email_logo'] . '"/>';
				}
			echo '</div>';
		echo '</div>';
		// main content
		echo '<div style="width: 620px; padding: 36px 160px 0; margin: 0 auto;">';

			echo '<div id="edd-email-content" style="position:relative; text-align: left;">';
				echo '<div style="position:relative;z-index:2;">{email}</div>'; // this tag is required in order for the contents of the email to be shown
			echo '</div>';
		
		echo '</div>'; // end main content

		echo '<div style="background: url(' . EDDET_PLUGIN_URL . 'images/yellow/pattern.png); height:40px;"></div>';

	echo '</div>'; // end wrap
	
}
add_action('edd_email_template_yellow', 'edd_et_yellow');


/**
 * Sets up the HTML for the Blue Template
 * *
 * @access      private
 * @since       1.0
 * @return      void
*/

function edd_et_blue() {	
	
	global $edd_options;

	// wrap
	echo '<div style="background: url(' . EDDET_PLUGIN_URL . 'images/sand.png); padding: 0 0 30px;">';
		// header
		echo '<div style="background: url(' . EDDET_PLUGIN_URL . 'images/blue/pattern.png);">';
			echo '<div style="width: 605px; padding: 50px 0; margin: 0 auto;">';
				if( isset( $edd_options['email_logo']) ) {
					echo '<img src="' . $edd_options['email_logo'] . '"/>';
				}
			echo '</div>';
		echo '</div>';
		// main content
		echo '<div style="width: 620px; padding: 36px 160px 0; margin: 0 auto;">';

			echo '<div id="edd-email-content" style="position:relative; text-align: left;">';
				echo '<div style="position:relative;z-index:2;">{email}</div>'; // this tag is required in order for the contents of the email to be shown
			echo '</div>';
		
		echo '</div>'; // end main content

		echo '<div style="background: url(' . EDDET_PLUGIN_URL . 'images/blue/pattern.png); height:40px;"></div>';

	echo '</div>'; // end wrap
	
}
add_action('edd_email_template_blue', 'edd_et_blue');


/**
 * Sets up the HTML for the Red Template
 * *
 * @access      private
 * @since       1.0
 * @return      void
*/

function edd_et_beigegreen() {	
	
	global $edd_options;

	// wrap
	echo '<div style="background: url(' . EDDET_PLUGIN_URL . 'images/sand.png); padding: 0 0 30px;">';
		// header
		echo '<div style="background: url(' . EDDET_PLUGIN_URL . 'images/beigegreen/pattern.png);">';
			echo '<div style="width: 605px; padding: 50px 0; margin: 0 auto;">';
				if( isset( $edd_options['email_logo']) ) {
					echo '<img src="' . $edd_options['email_logo'] . '"/>';
				}
			echo '</div>';
		echo '</div>';
		// main content
		echo '<div style="width: 620px; padding: 36px 160px 0; margin: 0 auto;">';

			echo '<div id="edd-email-content" style="position:relative; text-align: left;">';
				echo '<div style="position:relative;z-index:2;">{email}</div>'; // this tag is required in order for the contents of the email to be shown
			echo '</div>';
		
		echo '</div>'; // end main content

		echo '<div style="background: url(' . EDDET_PLUGIN_URL . 'images/beigegreen/pattern.png); height:40px;"></div>';

	echo '</div>'; // end wrap
	
}
add_action('edd_email_template_beigegreen', 'edd_et_beigegreen');


/**
 * Adds extra styling to certain HTML tags in the Colored Templates
 * *
 * @access      private
 * @since       1.0
 * @param 		$email_body string the contents of the email
 * @return      void
*/

function edd_et_colors_extra_styling( $email_body ) {

	$email_body = str_replace('<ul>', '<ul style="margin:0;padding:0;">', $email_body );
	$email_body = str_replace('<li>', '<li style="line-height: 18px; margin: 0 0 10px; padding: 0; color: #505050; font-family: verdana, arial, sans-serif; display:block;">', $email_body );
	$email_body = str_replace('<p>', '<p style="line-height: 24px; margin: 0 0 20px; color: #434343; font-size: 14px; font-family: verdana, arial, sans-serif;">', $email_body );
	$email_body = str_replace('<p>', '<p style="line-height: 24px; margin: 0 0 20px; color: #434343; font-size: 14px; font-family: verdana, arial, sans-serif;">', $email_body );
	$email_body = str_replace('<h1>', '<h1 style="line-height: 32px; margin: 0 0 29px; text-transform: uppercase; font-weight: bold; font-size: 26px; color: #434343; font-family: verdana, arial, sans-serif;">', $email_body );
	$email_body = str_replace('<h2>', '<h2 style="line-height: 30px; margin: 0 0 29px; text-transform: uppercase; font-weight: bold; font-size: 24px; color: #434343; font-family: verdana, arial, sans-serif;">', $email_body );
	$email_body = str_replace('<h3>', '<h3 style="line-height: 28px; margin: 0 0 29px; text-transform: uppercase; font-weight: bold; font-size: 22px; color: #434343; font-family: verdana, arial, sans-serif;">', $email_body );
	$email_body = str_replace('<hr/>', '<hr style="height:3px;background:#434343;border:none;outline:none;margin: 0 0 25px;"/>', $email_body );
	$email_body = str_replace('<hr>', '<hr style="height:3px;background:#434343;border:none;outline:none;margin: 0 0 25px;"/>', $email_body );

	return $email_body;
}
add_filter('edd_purchase_receipt_purple', 'edd_et_colors_extra_styling');
add_filter('edd_purchase_receipt_orange', 'edd_et_colors_extra_styling');
add_filter('edd_purchase_receipt_red', 'edd_et_colors_extra_styling');
add_filter('edd_purchase_receipt_beigegreen', 'edd_et_colors_extra_styling');
add_filter('edd_purchase_receipt_green', 'edd_et_colors_extra_styling');
add_filter('edd_purchase_receipt_yellow', 'edd_et_colors_extra_styling');
add_filter('edd_purchase_receipt_blue', 'edd_et_colors_extra_styling');


function edd_et_activate_license() {
	global $edd_options;
	if( ! isset( $_POST['edd_settings_emails'] ) )
		return;
	if( ! isset( $_POST['edd_settings_emails']['edd_et_license_key'] ) )
		return;

	if( get_option( 'eddc_license_active' ) == 'valid' )
		return;

	$license = sanitize_text_field( $_POST['edd_settings_emails']['edd_et_license_key'] );

	// data to send in our API request
	$api_params = array( 
		'edd_action'=> 'activate_license', 
		'license' 	=> $license, 
		'item_name' => urlencode( EDD_ETEMPLATES_PRODUCT_NAME ) // the name of our product in EDD
	);
	
	// Call the custom API.
	$response = wp_remote_get( add_query_arg( $api_params, EDD_ETEMPLATES_STORE_API_URL ), array( 'sslverify' => false, 'timeout' => 15 ) );

	// make sure the response came back okay
	if ( is_wp_error( $response ) )
		return false;

	// decode the license data
	$license_data = json_decode( wp_remote_retrieve_body( $response ) );

	update_option( 'eddc_license_active', $license_data->license );

}
add_action( 'admin_init', 'edd_et_activate_license' );


function edd_et_updater() {

	if( !class_exists( 'EDD_SL_Plugin_Updater' ) ) {
		// load our custom updater
		include( EDDET_PLUGIN_DIR . '/EDD_SL_Plugin_Updater.php' );
	}

	global $edd_options;

	// retrieve our license key from the DB
	$edd_et_license_key = isset( $edd_options['edd_et_license_key'] ) ? trim( $edd_options['edd_et_license_key'] ) : '';

	// setup the updater
	$edd_cr_updater = new EDD_SL_Plugin_Updater( EDD_ETEMPLATES_STORE_API_URL, __FILE__, array( 
			'version' 	=> EDD_ETEMPLATES_VERSION, 			// current version number
			'license' 	=> $edd_et_license_key, 			// license key (used get_option above to retrieve from DB)
			'item_name' => EDD_ETEMPLATES_PRODUCT_NAME, 	// name of this plugin
			'author' 	=> 'Pippin Williamson'  			// author of this plugin
		)
	);

}
add_action( 'admin_init', 'edd_et_updater' );