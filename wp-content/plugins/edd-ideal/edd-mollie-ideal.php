<?php
/**
 * Plugin Name:         Easy Digital Downloads - Mollie iDEAL
 * Plugin URI:          http://shop.mgates.me/?p=327
 * Description:         Use Mollie iDEAL to accept payments on your shop!
 * Author:              Matt Gates
 * Author URI:          http://mgates.me
 *
 * Version:             1.0
 * Requires at least:   3.2.1
 * Tested up to:        3.5
 *
 * Text Domain:         edd_mollie_ideal
 * Domain Path:         /languages/
 *
 * @category            Payment Method
 * @copyright           Copyright © 2012 Matt Gates.
 * @author              Matt Gates
 * @package             Easy Digital Downloads
 */

/**
 * Plugin updates
 */
if ( ! function_exists( 'is_edd_activated' ) ) require_once 'mg-includes/mg-functions.php';
if ( is_admin() ) new MGates_Plugin_Updater( __FILE__, '74665459789dd66607e452b81bf89b77' );

if ( is_edd_activated() ) {

class EDD_Mollie_Ideal
{

	/**
	 * Absolute path to our plugin directory
	 *
	 * @var string
	 *
	 * @access public
	 * @static
	 */
	public static $plugin_dir;


	/**
	 * Initial hooks
	 */
	function __construct()
	{
		self::$plugin_dir = trailingslashit( plugin_dir_path( __FILE__ ) );

		if ( $valid = $this->is_valid() ) {
			$this->load_hooks();
			$this->setup_config();
		}

	}

	public function is_valid() {
		global $edd_options;
		if ( $edd_options['currency'] != 'EUR' ) {
			add_action( 'admin_notices', array( &$this, 'invalid_currency' ) );
			return false;
		}

		return true;
	}

	/**
	 * Simple hooks to setup with EDD
	 */
	public function load_hooks()
	{
		add_action( 'edd_mollie_ideal_cc_form'  , array( &$this, 'mollie_ideal_cc_form' ) );
		add_action( 'edd_gateway_mollie_ideal'  , array( &$this, 'process_payment' ) );

		add_action( 'init'                        , array( &$this, 'check_return' ) );

		add_filter( 'edd_payment_gateways'        , array( &$this, 'register_gateway' ) );
		add_filter( 'edd_settings_gateways'       , array( &$this, 'gateway_settings' ) );
	}

	public function invalid_currency()
	{
		echo '<div class="error">
				<p>' . __( '<b>Mollie iDEAL disabled.</b> Your currency must be set to Euros in order to use the Mollie iDEAL gateway.', 'edd_mollie_ideal' ) .'</p>
			</div>';
	}

	/**
	 * Initial configuration for Mollie iDEAL API
	 *
	 * @return unknown
	 */
	public function setup_config()
	{
		global $edd_options;

		if ( empty( $edd_options ) ) return false;

		require_once self::$plugin_dir . 'lib/ideal.class.php';

		$this->iDEAL = new Mollie_iDEAL_Payment( $edd_options['mollie_ideal_partner_id'] );

		if ( edd_is_test_mode() ) {
			$this->iDEAL->setTestmode( true );
		}

		if ( !empty( $edd_options['mollie_ideal_profile_key'] ) ) {
			$this->iDEAL->setProfileKey( $edd_options['mollie_ideal_profile_key'] );
		}

		$this->banks = $this->iDEAL->getBanks();
		$this->response_url = str_replace( 'https:', 'http:', add_query_arg( 'edd-api', 'mollie_ideal_confirmation', home_url( '/' ) ) );
	}

	/**
	 * Check the response from Mollie
	 *
	 * @return unknown
	 */
	public function check_return()
	{
		// Background return from Mollie
		if ( !empty( $_GET['edd-api'] ) && $_GET['edd-api'] == 'mollie_ideal_confirmation' ) {
			if ( empty( $_GET['transaction_id'] ) ) return false;

			$transaction_id = esc_attr( $_GET[ 'transaction_id' ] );
			$args = array(
				'meta_key'    => '_order_transaction_id',
				'meta_value'  => $transaction_id,
				'post_type'   => 'edd_payment',
				'post_status' => 'pending',
				'numberposts' => 1,
			);

			$post = get_posts( $args );
			$post = $post[0];

			if ( $this->iDEAL->checkPayment( $transaction_id ) ) {

				if ( $this->iDEAL->getPaidStatus() ) {
					edd_update_payment_status( $post->ID, 'complete' );
				} else {
					edd_update_payment_status( $post->ID, 'failed' );
				}

			}

			die();
		}

		// Customer return
		if ( !empty( $_GET['payment-confirmation'] ) && $_GET['payment-confirmation'] == 'mollie_ideal' ) {
			edd_empty_cart();
		}
	}


	/**
	 * Register the gateway with EDD
	 *
	 * @param array   $gateways
	 * @return array
	 */
	public function register_gateway( $gateways )
	{
		$gateways['mollie_ideal'] = array(
			'admin_label'    => 'Mollie iDEAL',
			'checkout_label' => __( 'Mollie iDEAL', 'edd_mollie_ideal' ),
		);

		return $gateways;
	}


	/**
	 * Remove the default credit card form added by EDD
	 *
	 * Instead, display the bank select box
	 */
	public function mollie_ideal_cc_form()
	{
		// Check for EUR currency
		if ( ! $this->banks ) {
			echo '<p>' . __( 'Error getting list of available banks: ', 'edd_mollie_ideal' ) . $this->iDEAL->getErrorMessage() . '</p>';
			exit;
		} ?>

		<label class="edd-label" for="bank_id"><?php _e( 'Kies uw bank voor de beveiligde IDeal betaling', 'edd_mollie_ideal' ); ?></label>

		<p id="edd-last-name-wrap">
			<select name="bank_id" id="bank_id" class="edd-select">
				<?php foreach ( $this->banks as $bank_id => $bank_name ) {
			echo '<option value="' . $bank_id . '">' . $bank_name . '</option>';
		} ?>
			</select>
			<img class="ideal-logo" src="http://cfcommunity.net/wp-content/themes/fundify-child/assets/images/ideal-logo.png">
		</p>
		<?php
	}


	/**
	 * Process the payment
	 *
	 * @param array   $purchase_data
	 */
	public function process_payment( $purchase_data )
	{
		global $edd_options;

		$fail = false;

		// Check for any stored errors
		$errors = edd_get_errors();
		if ( $errors ) $fail = true;

		$payment = array(
			'price'        => $purchase_data['price'],
			'date'         => $purchase_data['date'],
			'user_email'   => $purchase_data['user_email'],
			'purchase_key' => $purchase_data['purchase_key'],
			'currency'     => $edd_options['currency'],
			'downloads'    => $purchase_data['downloads'],
			'cart_details' => $purchase_data['cart_details'],
			'user_info'    => $purchase_data['user_info'],
			'status'       => 'pending'
		);

		// record the pending payment
		$payment = edd_insert_payment( $payment );

		if ( !$fail ) {

			// Amount should be in cents
			$amount = $purchase_data['price'] * 100;

			$amount = str_replace( '.', '', $amount );
			$amount = str_replace( ',', '', $amount );

			$description = sprintf( __( 'iDEAL payment: Order %d', 'edd_mollie_ideal' ), $payment );
			$return_url  = add_query_arg( 'payment-confirmation', 'mollie_ideal', get_permalink( $edd_options['success_page'] ) );
			$report_url  = $this->response_url;

			if ( $this->iDEAL->createPayment( $_POST['bank_id'], $amount, $description, $return_url, $report_url ) ) {

				update_post_meta( $payment, '_order_transaction_id', $this->iDEAL->getTransactionId() );
				wp_redirect( $this->iDEAL->getBankURL() );
				exit;

			} else {
				edd_record_gateway_error( __( 'Mollie iDEAL error', 'edd_mollie_ideal' ), sprintf( __( 'Payment Error: %s ', 'edd_mollie_ideal' ), $this->iDEAL->getErrorMessage() ) );
				edd_update_payment_status( $payment, 'failed' );
				$fail = true;
			}

		}

		if ( $fail ) {
			// if errors are present, send the user back to the purchase page so they can be corrected
			edd_send_back_to_checkout( '?payment-mode=' . $purchase_data['post_data']['edd-gateway'] );
		}

	}


	/**
	 * Adds the settings to the Payment Gateways section
	 *
	 * @param array   $settings
	 * @return array
	 */
	public function gateway_settings( $settings )
	{

		$settings[] =  array(
			'id'   => 'mollie_ideal_header',
			'name' => '<strong>' . __( 'Mollie iDEAL', 'edd_mollie_ideal' ) . '</strong>',
			'desc' => __( 'Configure the gateway settings', 'edd_mollie_ideal' ),
			'type' => 'header'
		);

		$settings[] =  array(
			'id'   => 'mollie_ideal_partner_id',
			'name' => __( 'Partner ID', 'edd_mollie_ideal' ),
			'desc' => __( 'This ID can be found in your Mollie control panel.', 'edd_mollie_ideal' ),
			'type' => 'text',
			'size' => 'regular'
		);

		$settings[] =  array(
			'id'   => 'mollie_ideal_profile_key',
			'name' => __( 'Profile key', 'edd_mollie_ideal' ),
			'desc' => __( 'Provide an optional profile key so you can distinguish transactions from multiple stores in a single account.', 'edd_mollie_ideal' ),
			'type' => 'text',
			'size' => 'regular'
		);

		return $settings;
	}


}


// Call our plugin on init
add_action( 'init', 'edd_load_mollie_ideal', 1 );


/**
 *
 */
function edd_load_mollie_ideal()
{
	new EDD_Mollie_Ideal;
}

}