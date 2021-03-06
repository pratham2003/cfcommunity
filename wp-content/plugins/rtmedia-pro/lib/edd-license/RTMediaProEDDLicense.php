<?php

/**
 * Created by PhpStorm.
 * User: ritz
 * Date: 8/10/14
 * Time: 4:46 PM
 */
class RTMediaProEDDLicense {

	function __construct() {

		if ( ! class_exists( 'EDD_SL_Plugin_Updater' ) ){
			// load our custom updater
			include_once( RTMEDIA_PRO_PATH . 'lib/edd-license/EDD_SL_Plugin_Updater.php' );
		}

		add_action( 'admin_init', array( $this, 'edd_sl_sample_plugin_updater' ) );
		add_action( 'rtmedia_addon_license_details', array( $this, 'edd_sample_license_page' ), 10 );
		add_action( 'admin_init', array( $this, 'edd_sample_activate_license' ) );
		add_action( 'admin_init', array( $this, 'edd_sample_deactivate_license' ) );
	}

	function edd_sample_license_page() {
		$license = get_option( 'edd_rtmedia_pro_license_key' );
		$status  = get_option( 'edd_rtmedia_pro_license_status' );

		if ( $status !== false && $status == 'valid' ){
			$status_class = 'rtm-addon-status-activated';
			$status_value = __( 'Activated', 'rtmedia' );
		} else {
			$status_class = 'rtm-addon-status-deactivated';
			$status_value = __( 'Deactivated', 'rtmedia' );
		}
		?>
		<div class="rtm-addon-license">
			<div class="row">
				<div class="columns large-12 rtm-addon-license-status"><span class="rtm-addon-license-status-label">rtMedia Pro:</span>
					<span
						class="rtm-addon-license-status <?php echo $status_class ?>"><?php echo $status_value; ?></span>
				</div>
			</div>
			<div class="row">
				<div class="columns large-12">
					<form method="post">
						<table class="form-table">
							<tbody>
							<tr valign="top">
								<th scope="row" valign="top">
									<?php _e( 'License Key', 'rtmedia' ); ?>
								</th>
								<td>
									<input id="edd_rtmedia_pro_license_key" name="edd_rtmedia_pro_license_key" type="text"
										   class="regular-text" value="<?php esc_attr_e( $license ); ?>"/>
								</td>
							</tr>
							<?php if ( false !== $license ){ ?>
								<tr valign="top">
									<th scope="row" valign="top">
										<?php _e( 'Activate / Deactivate License', 'rtmedia' ); ?>
									</th>
									<td>
										<?php if ( $status !== false && $status == 'valid' ){ ?>
											<?php wp_nonce_field( 'edd_rtmedia_pro_nonce', 'edd_rtmedia_pro_nonce' ); ?>
											<input type="submit" class="button-secondary" name="edd_rtmedia_pro_license_deactivate"
												   value="<?php _e( 'Deactivate License', 'rtmedia' ); ?>"/>
										<?php
										} else {
											wp_nonce_field( 'edd_rtmedia_pro_nonce', 'edd_rtmedia_pro_nonce' ); ?>
											<input type="submit" class="button-secondary" name="edd_rtmedia_pro_license_activate"
												   value="<?php _e( 'Activate License', 'rtmedia' ); ?>"/>
										<?php } ?>
									</td>
								</tr>
							<?php } ?>
							</tbody>
						</table>
						<?php submit_button( 'Save Key' ); ?>
					</form>
				</div>
			</div>
		</div>
	<?php
	}

	function edd_sl_sample_plugin_updater() {

		// retrieve our license key from the DB
		$license_key = trim( get_option( 'edd_rtmedia_pro_license_key' ) );

		// setup the updater
		$edd_updater = new EDD_SL_Plugin_Updater( EDD_RTMEDIA_PRO_STORE_URL, RTMEDIA_PRO_BASE_NAME, array(
				'version'   => RTMEDIA_PRO_VERSION, // current version number
				'license'   => $license_key, // license key (used get_option above to retrieve from DB)
				'item_name' => EDD_RTMEDIA_PRO_ITEM_NAME, // name of this plugin
				'author'    => 'rtCamp' // author of this plugin
			) );

	}

	function edd_sample_activate_license() {

		if ( isset( $_POST[ 'edd_rtmedia_pro_license_key' ] ) ){
			update_option( 'edd_rtmedia_pro_license_key', $_POST[ 'edd_rtmedia_pro_license_key' ] );
		}

		// listen for our activate button to be clicked
		if ( isset( $_POST[ 'edd_rtmedia_pro_license_activate' ] ) ){
			// run a quick security check
			if ( ! check_admin_referer( 'edd_rtmedia_pro_nonce', 'edd_rtmedia_pro_nonce' ) ){
				return;
			} // get out if we didn't click the Activate button

			// retrieve the license from the database
			$license = trim( get_option( 'edd_rtmedia_pro_license_key' ) );


			// data to send in our API request
			$api_params = array(
				'edd_action' => 'activate_license', 'license' => $license, 'item_name' => urlencode( EDD_RTMEDIA_PRO_ITEM_NAME ), // the name of our product in EDD
				'url'        => home_url()
			);

			// Call the custom API.
			$response = wp_remote_get( add_query_arg( $api_params, EDD_RTMEDIA_PRO_STORE_URL ), array( 'timeout' => 15, 'sslverify' => false ) );

			//		var_dump($response);

			// make sure the response came back okay
			if ( is_wp_error( $response ) ){
				return false;
			}

			// decode the license data
			$license_data = json_decode( wp_remote_retrieve_body( $response ) );

			// $license_data->license will be either "valid" or "invalid"

			update_option( 'edd_rtmedia_pro_license_status', $license_data->license );

		}
	}

	function edd_sample_deactivate_license() {

		// listen for our activate button to be clicked
		if ( isset( $_POST[ 'edd_rtmedia_pro_license_deactivate' ] ) ){

			// run a quick security check
			if ( ! check_admin_referer( 'edd_rtmedia_pro_nonce', 'edd_rtmedia_pro_nonce' ) ){
				return;
			} // get out if we didn't click the Activate button

			// retrieve the license from the database
			$license = trim( get_option( 'edd_rtmedia_pro_license_key' ) );


			// data to send in our API request
			$api_params = array(
				'edd_action' => 'deactivate_license', 'license' => $license, 'item_name' => urlencode( EDD_RTMEDIA_PRO_ITEM_NAME ), // the name of our product in EDD
				'url'        => home_url()
			);

			// Call the custom API.
			$response = wp_remote_get( add_query_arg( $api_params, EDD_RTMEDIA_PRO_STORE_URL ), array( 'timeout' => 15, 'sslverify' => false ) );

			//		var_dump($response);

			// make sure the response came back okay
			if ( is_wp_error( $response ) ){
				return false;
			}

			// decode the license data
			$license_data = json_decode( wp_remote_retrieve_body( $response ) );

			//		var_dump($license_data);

			// $license_data->license will be either "deactivated" or "failed"
			if ( $license_data->license == 'deactivated' ){
				delete_option( 'edd_rtmedia_pro_license_status' );
			}

		}
	}

	function edd_sample_check_license() {

		global $wp_version;

		$license = trim( get_option( 'edd_rtmedia_pro_license_key' ) );

		$api_params = array(
			'edd_action' => 'check_license', 'license' => $license, 'item_name' => urlencode( EDD_RTMEDIA_PRO_ITEM_NAME ), 'url' => home_url()
		);

		// Call the custom API.
		$response = wp_remote_get( add_query_arg( $api_params, EDD_RTMEDIA_PRO_STORE_URL ), array( 'timeout' => 15, 'sslverify' => false ) );


		if ( is_wp_error( $response ) ){
			return false;
		}

		$license_data = json_decode( wp_remote_retrieve_body( $response ) );

		if ( $license_data->license == 'valid' ){
			echo 'valid';
			exit;
			// this license is still valid
		} else {
			echo 'invalid';
			exit;
			// this license is no longer valid
		}
	}
}