<?php
/**
 * Plugin Name: Easy Digital Downloads - Recurring Payments
 * Plugin URI: http://easydigitaldownloads.com/extension/edd-recurring
 * Description: Adds support for recurring payments to EDD
 * Author: Pippin Williamson
 * Author URI: http://pippinsplugins.com
 * Contributors: mordauk
 * Version: 2.2.10
 */

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;



final class EDD_Recurring {


	/** Singleton *************************************************************/

	/**
	 * @var EDD_Recurring The one true EDD_Recurring
	 */
	private static $instance;

	static $plugin_path;
	static $plugin_dir;


	/**
	 * Main EDD_Recurring Instance
	 *
	 * Insures that only one instance of EDD_Recurring exists in memory at any one
	 * time. Also prevents needing to define globals all over the place.
	 *
	 * @since v1.0
	 * @staticvar array $instance
	 * @uses EDD_Recurring::setup_globals() Setup the globals needed
	 * @uses EDD_Recurring::includes() Include the required files
	 * @uses EDD_Recurring::setup_actions() Setup the hooks and actions
	 * @see EDD()
	 * @return The one true EDD_Recurring
	 */
	public static function instance() {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new EDD_Recurring;

			self::$plugin_path = untrailingslashit( plugin_dir_path( __FILE__ ) );
			self::$plugin_dir  = untrailingslashit( plugin_dir_url( __FILE__ ) );

			self::$instance->init();
		}
		return self::$instance;
	}


	/**
	 * Get things started
	 *
	 * Sets up globals, loads text domain, loads includes, inits actions and filters, starts customer class
	 *
	 * @since v1.0
	 */

	function init() {

		define( 'EDD_RECURRING_STORE_API_URL', 'https://easydigitaldownloads.com' );
		define( 'EDD_RECURRING_PRODUCT_NAME',  'Recurring Payments' );
		define( 'EDD_RECURRING_VERSION',       '2.2.10' );

		self::includes_global();

		self::load_textdomain();

		if ( is_admin() ) {
			self::includes_admin();
			if( class_exists( 'EDD_License' ) ) {
				$edd_recurring_license = new EDD_License( __FILE__, 'Recurring Payments', EDD_RECURRING_VERSION, 'Pippin Williamson', 'recurring_license_key' );
			}
		}

		self::actions();
		self::filters();

		$customers = new EDD_Recurring_Customer();
		$content_restriction = new EDD_Recurring_Content_Restriction();

	}


	/**
	 * Load global files
	 *
	 * @since  1.0
	 * @return void
	 */

	private function includes_global() {
		$files = array(
			'edd-recurring-customer.php',
			'edd-recurring-paypal-ipn.php',
			'plugin-content-restriction.php'
		);

		foreach ( $files as $file ) {
			require( sprintf( '%s/includes/%s', self::$plugin_path, $file ) );
		}
	}

	/**
	 * Load admin files
	 *
	 * @since  1.0
	 * @return void
	 */

	private function includes_admin() {
		$files = array(
			'class-subscriber-reports-table.php',
			'reports.php',
			'metabox.php',
			'settings.php'
		);

		foreach ( $files as $file ) {
			require( sprintf( '%s/includes/admin/%s', self::$plugin_path, $file ) );
		}
	}

	/**
	 * Loads the plugin language files
	 *
	 * @since v1.0
	 * @access private
	 * @uses dirname()
	 * @uses plugin_basename()
	 * @uses apply_filters()
	 * @uses load_textdomain()
	 * @uses get_locale()
	 * @uses load_plugin_textdomain()
	 *
	 */
	private function load_textdomain() {

		// Set filter for plugin's languages directory
		$edd_lang_dir  = dirname( plugin_basename( __FILE__ ) ) . '/languages/';
		$edd_lang_dir  = apply_filters( 'edd_languages_directory', $edd_lang_dir );


		// Traditional WordPress plugin locale filter
		$locale        = apply_filters( 'plugin_locale',  get_locale(), 'edd-recurring' );
		$mofile        = sprintf( '%1$s-%2$s.mo', 'edd-recurring', $locale );

		// Setup paths to current locale file
		$mofile_local  = $edd_lang_dir . $mofile;
		$mofile_global = WP_LANG_DIR . '/edd-recurring/' . $mofile;

		if ( file_exists( $mofile_global ) ) {
			// Look in global /wp-content/languages/edd-recurring folder
			load_textdomain( 'edd-recurring', $mofile_global );
		} elseif ( file_exists( $mofile_local ) ) {
			// Look in local /wp-content/plugins/edd-recurring/languages/ folder
			load_textdomain( 'edd-recurring', $mofile_local );
		} else {
			// Load the default language files
			load_plugin_textdomain( 'edd-recurring', false, $edd_lang_dir );
		}

	}


	/**
	 * Add our actions
	 *
	 * @since  1.0
	 * @return void
	 */

	private function actions() {


		if( class_exists( 'EDD_License' ) ) {
			$recurring_license = new EDD_License( __FILE__, EDD_RECURRING_PRODUCT_NAME, EDD_RECURRING_VERSION, 'Pippin Williamson', 'recurring_license_key' );
		}

		// Register our "canclled" post status
		add_action( 'init', array( $this, 'register_post_statuses' ) );

		// Maybe remove the Signup fee from the cart
		add_action( 'init', array( $this, 'maybe_add_remove_fees' ) );

		// Check for errors at checkout
		add_action( 'edd_checkout_error_checks', array( $this, 'checkout_errors' ), 10, 2 );

		// Check for subscription status on file download
		add_action( 'edd_process_verified_download', array( $this, 'process_download' ), 10, 2 );

		// Show recurring details on the [edd_receipt]
		add_action( 'edd_payment_receipt_after', array( $this, 'receipt' ), 10, 2 );

		// Process Test Mode recurring payments (initial payment only)
		add_action( 'edd_insert_payment', array( $this, 'process_test_payment' ), 10, 2 );

		// Process PayPal subscription sign ups
		add_action( 'edd_paypal_subscr_signup', array( 'EDD_Recurring_PayPal_IPN', 'process_paypal_subscr_signup' ) );

		// Process PayPal subscription payments
		add_action( 'edd_paypal_subscr_payment', array( 'EDD_Recurring_PayPal_IPN', 'process_paypal_subscr_payment' ) );

		// Process PayPal subscription cancellations
		add_action( 'edd_paypal_subscr_cancel', array( 'EDD_Recurring_PayPal_IPN', 'process_paypal_subscr_cancel' ) );

		// Process PayPal subscription end of term notices
		add_action( 'edd_paypal_subscr_eot', array( 'EDD_Recurring_PayPal_IPN', 'process_paypal_subscr_eot' ) );

		// Tells EDD to include subscription payments in Payment History
		add_action( 'edd_pre_get_payments', array( $this, 'enable_child_payments' ), 100 );

		// Adds the [edd_recurring_cancel] short code for cancelling a subscription
		add_shortcode( 'edd_recurring_cancel', array( $this, 'cancel_link' ) );

	}


	/**
	 * Add our filters
	 *
	 * @since  1.0
	 * @return void
	 */

	private function filters() {

		// Register our new payment statuses
		add_filter( 'edd_payment_statuses', array( $this, 'register_edd_cancelled_status' ) );

		// Set the payment stati that can download files
		add_filter( 'edd_allowed_download_stati', array( $this, 'add_allowed_payment_status' ) );
		add_filter( 'edd_is_payment_complete', array( $this, 'is_payment_complete' ), 10, 3 );

		// Show the Cancelled and Subscription status links in Payment History
		add_filter( 'edd_payments_table_views', array( $this, 'payments_view' ) );

		// Modify the cart details when purchasing a subscription
		add_filter( 'edd_add_to_cart_item', array( $this, 'add_subscription_cart_details' ), 10 );

		// Modify the gateway data before it goes to the gateway
		add_filter( 'edd_purchase_data_before_gateway', array( $this, 'gateway_data' ), 10, 2 );

		// Modify the PayPal redirect query with recurring details
		add_filter( 'edd_paypal_redirect_args', array( $this, 'paypal_gateway_data' ), 10, 2 );

		// Include subscription payments in the calulation of earnings
		add_filter( 'edd_get_total_earnings_args', array( $this, 'earnings_query' ) );
		add_filter( 'edd_get_earnings_by_date_args', array( $this, 'earnings_query' ) );
		add_filter( 'edd_get_users_purchases_args', array( $this, 'has_purchased_query' ) );

		// Allow PDF Invoices to be downloaded for subscription payments
		add_filter( 'eddpdfi_is_invoice_link_allowed', array( $this, 'is_invoice_allowed' ), 10, 2 );

	}

	/**
	 * Registers the cancelled post status
	 *
	 * @since  1.0
	 * @return void
	 */

	public function register_post_statuses() {
		register_post_status( 'cancelled', array(
			'label'                     => _x( 'Cancelled', 'Cancelled payment status', 'edd-recurring' ),
			'public'                    => true,
			'exclude_from_search'       => false,
			'show_in_admin_all_list'    => true,
			'show_in_admin_status_list' => true,
			'label_count'               => _n_noop( 'Cancelled <span class="count">(%s)</span>', 'Cancelled <span class="count">(%s)</span>', 'edd-recurring' )
		)  );
		register_post_status( 'edd_subscription', array(
			'label'                     => _x( 'Subscription', 'Subscription payment status', 'edd-recurring' ),
			'public'                    => true,
			'exclude_from_search'       => false,
			'show_in_admin_all_list'    => true,
			'show_in_admin_status_list' => true,
			'label_count'               => _n_noop( 'Subscription <span class="count">(%s)</span>', 'Subscription <span class="count">(%s)</span>', 'edd-recurring' )
		)  );
	}


	/**
	 * Allow file downloads for payments with a status of cancelled
	 *
	 * @since  1.4.2
	 * @return array
	 */

	public function add_allowed_payment_status( $stati ) {
		$stati[] = 'cancelled';
		return $stati;
	}


	/**
	 * Allow file downloads for payments with a status of cancelled
	 *
	 * @since  1.4.2
	 * @return array
	 */

	public function is_payment_complete( $ret, $payment_id, $status ) {

		if( 'cancelled' == $status ) {

			$ret = true;

		} elseif( 'edd_subscription' == $status ) {

			$parent = get_post_field( 'post_parent', $payment_id );
			if( edd_is_payment_complete( $parent ) ) {
				$ret = true;
			}

		}

		return $ret;
	}


	/**
	 * Tells EDD about our new payment status
	 *
	 * @since  1.0
	 * @return array
	 */

	public function register_edd_cancelled_status( $stati ) {
		$stati['edd_subscription'] = __( 'Subscription', 'edd-recurring' );
		$stati['cancelled'] = __( 'Cancelled', 'edd-recurring' );
		return $stati;
	}


	/**
	 * Displays the cancelled payments filter link
	 *
	 * @since  1.0
	 * @return array
	 */

	public function payments_view( $views ) {
		$base               = admin_url( 'edit.php?post_type=download&page=edd-payment-history' );
		$payment_count      = wp_count_posts( 'edd_payment' );
		$current            = isset( $_GET['status'] ) ? $_GET['status'] : '';

		$subscription_count = '&nbsp;<span class="count">(' . $payment_count->edd_subscription   . ')</span>';
		$views['edd_subscription'] = sprintf(
			'<a href="%s"%s>%s</a>',
			add_query_arg( 'status', 'edd_subscription', $base ),
			$current === 'edd_subscription' ? ' class="current"' : '',
			__( 'Subscription Payment', 'edd-recurring' ) . $subscription_count
		);

		$cancelled_count    = '&nbsp;<span class="count">(' . $payment_count->cancelled   . ')</span>';
		$views['cancelled'] = sprintf(
			'<a href="%s"%s>%s</a>',
			add_query_arg( 'status', 'cancelled', $base ),
			$current === 'cancelled' ? ' class="current"' : '',
			__( 'Cancelled', 'edd-recurring' ) . $cancelled_count
		);

		return $views;
	}


	/**
	 * Add or remove the signup fees
	 *
	 * @since  2.1.6
	 * @return void
	 */

	public function maybe_add_remove_fees() {
		if( is_admin() ) {
			return;
		}

		$has_recurring = false;
		$cart_details  = edd_get_cart_contents();

		if( $cart_details ) {
			foreach( $cart_details as $item ) {

				if( isset( $item['options'] ) && isset( $item['options']['recurring'] ) ) {

					$has_recurring = true;
					$fee_amount    = $item['options']['recurring']['signup_fee'];
				}

			}
		}

		if( $has_recurring && ( $fee_amount  > 0 || $fee_amount < 0 ) ) {
			$args = array(
				'amount' => $fee_amount,
				'label'  => __( 'Signup Fee', 'edd-recurring' ),
				'id'     => 'signup_fee',
				'type'   => 'fee'
			);
			EDD()->fees->add_fee( $args );
		} else {
			EDD()->fees->remove_fee( 'signup_fee' );
		}

	}

	/**
	 * Look for errors during checkout
	 *
	 * This makes sure that only one recurring item is purchased per order.
	 *
	 * This checks to ensure a user is creating an account, logged/ing in if purchasing a subscription
	 *
	 * @since  1.0
	 * @return void
	 */

	public function checkout_errors( $valid_data, $post_data ) {

		// Retrieve the cart contents
		$cart_items = edd_get_cart_contents();

		/********* Check for multiple recurring products *********/

		// If less than 2 items in the cart, get out
		if( count( $cart_items ) < 2 )
			return;

		$has_recurring = false;

		// Loops through each item to see if any of them are recurring
		foreach( $cart_items as $cart_item ) {

			$item_id   = $cart_item['id'];
			$options   = $cart_item['options'];
			$price_id  = isset( $options['price_id'] ) ? intval( $options['price_id'] ) : null;

			// Only one subscription can be purchased at a time. Throw an error is more than one.
			// This also throws an error if a recurring and non recurring product are purchased at once.
			if( ( ! empty( $price_id ) && self::is_price_recurring( $item_id, $price_id ) ) || self::is_recurring( $item_id ) ) {
				$has_recurring = true;
				edd_set_error( 'subscription_invalid', __( 'Sorry, you cannot purchase items in the same checkout session as subscriptions.', 'edd') );
				break;
			}

		}


		/********* Ensure users create an account *********/

		// Only check if guest checkout is enabled
		if( ! edd_no_guest_checkout() && $has_recurring && ! is_user_logged_in() ) {

			// If customer is purchasing as a guest, we must throw an error

			// TODO: this doesn't work yet

			if( isset( $valid_data['new_user_data'] ) && $valid_data['new_user_data'] = '-1' ) {
				//edd_set_error( 'must_be_user', __( 'You must login or register to purchase a subscription.', 'edd') );
			}

		}

	}


	/**
	 * Checks if a user has permission to download a file
	 *
	 * This allows file downloads to be limited to activesubscribers
	 *
	 * @since  1.0
	 * @return void
	 */

	public function process_download( $download_id = 0, $email = '' ) {

		global $edd_options;

		if( ! isset( $edd_options['recurring_download_limit'] ) )
			return; // Downloads not restricted to subscribers


		// Allow user to download by default
		$has_access  = true;

		// Check if this is a variable priced product
		$is_variable = isset( $_GET['price_id'] ) && (int) $_GET['price_id'] !== false ? true : false;

		if( $is_variable && edd_has_variable_prices( $download_id ) ) {
			$recurring = self::is_price_recurring( $download_id, (int) $_GET['price_id'] );
		} else {
			$recurring = self::is_recurring( $download_id );
		}

		if( ! $recurring )
			return; // Product isn't recurring

		$user_data = get_user_by( 'email', $email );

		// No user found so access is denied
		if( ! $user_data )
			$has_access = false;

		// Check for active subscription
		if( ! EDD_Recurring_Customer::is_customer_active( $user_data->ID ) ) {
			$has_access = false;
		}

		// User doesn't have an active subscription so deny access
		if( ! apply_filters( 'edd_recurring_download_has_access', $has_access, $user_data->ID, $download_id, $is_variable ) ) {

			wp_die(
				sprintf(
					__( 'You must have an active subscription to %s in order to download this file.', 'edd-recurring' ),
					get_the_title( $download_id )
				),
				__( 'Access Denied', 'edd-recurring' )
			);
		}

	}


	/**
	 * Adds recurring product details to the shopping cart
	 *
	 * This fires when items are added to the cart
	 *
	 * @since  1.0
	 * @return array
	 */

	static function add_subscription_cart_details( $cart_item ) {
		$download_id 	= $cart_item['id'];
		$price_id 		= isset( $cart_item['options']['price_id'] ) ? intval( $cart_item['options']['price_id'] ) : null;

		if( ! is_null( $price_id ) && $price_id !== false ) {
			// add the recurring info for a variable price
			if( self::is_price_recurring( $download_id, $price_id ) ) {

				$cart_item['options']['recurring'] = array(
					'period'     => self::get_period( $price_id, $download_id ),
					'times'      => self::get_times( $price_id, $download_id ),
					'signup_fee' => self::get_signup_fee( $price_id, $download_id ),
				);

			}

		} else {

			// add the recurring info for a normal priced item
			if( self::is_recurring( $download_id ) ) {

				$cart_item['options']['recurring'] = array(
					'period'    => self::get_period_single( $download_id ),
					'times'      => self::get_times_single( $download_id ),
					'signup_fee' => self::get_signup_fee_single( $download_id ),
				);

			}

		}

		return $cart_item;

	}


	/**
	 * Set up the time period IDs and labels
	 *
	 * @since  1.0
	 * @return array
	 */

	static function periods() {
		$periods = array(
			'day'   => _x( 'Daily', 'Billing period', 'edd-recurring' ),
			'week'  => _x( 'Weekly', 'Billing period', 'edd-recurring' ),
			'month' => _x( 'Monthly', 'Billing period', 'edd-recurring' ),
			'year'  => _x( 'Yearly', 'Billing period', 'edd-recurring' ),
		);

		$periods = apply_filters( 'edd_recurring_periods', $periods );

		return $periods;
	}


	/**
	 * Get the time period for a variable priced product
	 *
	 * @since  1.0
	 * @return string
	 */

	static function get_period( $price_id, $post_id = null ) {
		global $post;

		if ( ! $post_id && is_object( $post ) )
			$post_id = $post->ID;

		$prices = get_post_meta( $post_id, 'edd_variable_prices', true);

		if ( isset( $prices[ $price_id ][ 'period' ] ) )
			return $prices[ $price_id ][ 'period' ];

		return 'never';
	}


	/**
	 * Get the time period for a single-price product
	 *
	 * @since  1.0
	 * @return string
	 */

	static function get_period_single( $post_id ) {
		global $post;

		$period = get_post_meta( $post_id, 'edd_period', true );

		if ( $period )
			return $period;

		return 'never';
	}


	/**
	 * Get the number of times a price ID recurs
	 *
	 * @since  1.0
	 * @return int
	 */

	static function get_times( $price_id, $post_id = null ) {
		global $post;

		if ( empty( $post_id ) && is_object( $post ) )
			$post_id = $post->ID;

		$prices = get_post_meta( $post_id, 'edd_variable_prices', true);

		if ( isset( $prices[ $price_id ][ 'times' ] ) )
			return intval( $prices[ $price_id ][ 'times' ] );

		return 0;
	}

	/**
	 * Get the signup fee a price ID
	 *
	 * @since  1.1
	 * @return float
	 */

	static function get_signup_fee( $price_id, $post_id = null ) {
		global $post;

		if ( empty( $post_id ) && is_object( $post ) )
			$post_id = $post->ID;

		$prices = get_post_meta( $post_id, 'edd_variable_prices', true);

		if ( isset( $prices[ $price_id ][ 'signup_fee' ] ) )
			return floatval( $prices[ $price_id ][ 'signup_fee' ] );

		return 0;
	}


	/**
	 * Get the number of times a single-price product recurs
	 *
	 * @since  1.0
	 * @return int
	 */

	static function get_times_single( $post_id ) {
		global $post;

		$times = get_post_meta( $post_id, 'edd_times', true );

		if ( $times )
			return $times;

		return 0;
	}


	/**
	 * Get the signup fee of a single-price product
	 *
	 * @since  1.1
	 * @return float
	 */

	static function get_signup_fee_single( $post_id ) {
		global $post;

		$signup_fee = get_post_meta( $post_id, 'edd_signup_fee', true );

		if ( $signup_fee )
			return $signup_fee;

		return 0;
	}


	/**
	 * Check if a price is recurring
	 *
	 * @since  1.0
	 * @return bool
	 */

	static function is_price_recurring( $download_id = 0, $price_id ) {

		global $post;

		if ( empty( $download_id ) && is_object( $post ) )
			$download_id = $post->ID;

		$prices = get_post_meta( $download_id, 'edd_variable_prices', true);
		$period = self::get_period( $price_id, $download_id );

		if ( isset( $prices[ $price_id ][ 'recurring' ] ) && 'never' != $period )
			return true;

		return false;

	}


	/**
	 * Check if a product is recurring
	 *
	 * @since  1.0
	 * @return bool
	 */

	static function is_recurring( $download_id = 0 ) {

		global $post;

		if ( empty( $download_id ) && is_object( $post ) )
			$download_id = $post->ID;

		if( get_post_meta( $download_id, 'edd_recurring', true ) == 'yes' )
			return true;

		return false;

	}

	/**
	 * Modify the data sent to payment gateways
	 *
	 * @since  1.1
	 * @return array
	 */

	public function gateway_data( $purchase_data, $valid_data ) {

		// Modify the data sent to the PayPal Standard gateway
		if( 'paypal' == $purchase_data['gateway'] ) {
			if( ! empty( $purchase_data['fees']['signup_fee'] ) ) {
				//$purchase_data['price'] -= $purchase_data['fees']['signup_fee']['amount'];
				unset( $purchase_data['fees']['signup_fee'] );
			}
		}

		return $purchase_data;
	}


	/**
	 * Modify the data sent to PayPal to trigger reucrring profile setup
	 *
	 * @since  1.0
	 * @return array
	 */

	public function paypal_gateway_data( $paypal_args = array(), $purchase_data = array() ) {

		// Set a transient that lets us identify this as the original signup payment in the IPN
		set_transient( '_edd_recurring_payment_' . $paypal_args['custom'], '1', DAY_IN_SECONDS );
		
		//echo '<pre>'; print_r( $purchase_data ); echo '</pre>'; exit;
		foreach( $purchase_data['cart_details'] as $download ) {

			$options = $download['item_number']['options'];

			if( isset( $download['item_number']['options'] ) && isset( $options['recurring'] ) ) {

				// Set this purchase as a recurring payment
				$paypal_args['cmd'] = '_xclick-subscriptions';

				// Attempt to rebill failed payments
				$paypal_args['sra'] = '1';

				// Set signup fee, if any
				if( ! empty( $options['recurring']['signup_fee'] ) ) {

					$paypal_args['a1'] = $purchase_data['price'];

					// Adjust the recurring price to not include the signup fee
					$purchase_data['price'] -= $options['recurring']['signup_fee'];


				}

				// Set the recurring amount
				$paypal_args['a3']  = $download['price'];

				if( ! empty( $paypal_args['item_name_1'] ) ) {
					// Set purchase description
					$paypal_args['item_name']  = $paypal_args['item_name_1'];
				}

				// Set the recurring period
				switch( $options['recurring']['period'] ) {
					case 'day' :
						$paypal_args['t3'] = 'D';
						$paypal_args['t2'] = 'D';
					break;
					case 'week' :
						$paypal_args['t3'] = 'W';
						$paypal_args['t1'] = 'W';
					break;
					case 'month' :
						$paypal_args['t3'] = 'M';
						$paypal_args['t1'] = 'M';
					break;
					case 'year' :
						$paypal_args['t3'] = 'Y';
						$paypal_args['t1'] = 'Y';
					break;
				}

				// One period unit (every week, every month, etc)
				$paypal_args['p3'] = '1';
				$paypal_args['p1'] = '1';

				// How many times should the payment recur?
				$times = intval( $options['recurring']['times'] );

				switch( $times ) {
					// Unlimited
					case '0' :
						$paypal_args['src'] = '1';
						break;
					// Recur the number of times specified
					default :
						$paypal_args['srt'] = $times;
						break;
				}

			}

		}

		return $paypal_args;

	}


	/**
	 * Setup customer status (expiration, status, etc) for users purchasing with Test Mode
	 *
	 * This is mainly so that site admins can test integrations.
	 *
	 * Test Mode purchases are not truly recurring and only the initial payment will be recorded
	 *
	 * @since  1.0
	 * @return void
	 */

	public function process_test_payment( $payment_id, $payment_data ) {

		if( ! isset( $_POST['edd-gateway'] ) || $_POST['edd-gateway'] != 'manual' )
			return;

		foreach( $payment_data['downloads'] as $download ) {

			if( isset( $download['options'] ) && isset( $download['options']['recurring'] ) ) {

				$user_id  = $payment_data['user_info']['id'];

				// Set user as subscriber
				EDD_Recurring_Customer::set_as_subscriber( $user_id );

				// Set the customer's status to active
				EDD_Recurring_Customer::set_customer_status( $user_id, 'active' );

				// Calculate the customer's new expiration date
				$new_expiration = EDD_Recurring_Customer::calc_user_expiration( $user_id, $payment_id );

				// Set the customer's new expiration date
				EDD_Recurring_Customer::set_customer_expiration( $user_id, $new_expiration );

				// Store the original payment ID in the customer meta
				EDD_Recurring_Customer::set_customer_payment_id( $user_id, $payment_id );

			}

		}

	}


	/**
	 * Record a subscription payment
	 *
	 * @since  1.0.1
	 * @return void
	 */

	public function record_subscription_payment( $parent_id = 0, $amount = '', $txn_id = '', $unique_key = 0 ) {

		global $edd_options;

		if( $this->payment_exists( $unique_key ) )
			return;

		// increase the earnings for each product in the subscription
		$downloads = edd_get_payment_meta_downloads( $parent_id );
		if( $downloads ) {
			foreach( $downloads as $download ) {
				edd_increase_earnings( $download['id'], $amount );
			}
		}

		// setup the payment daya
	    $payment_data = array(
	    	'parent'        => $parent_id,
	        'price'         => $amount,
	        'user_email'    => edd_get_payment_user_email( $parent_id ),
	        'purchase_key'  => get_post_meta( $parent_id, '_edd_payment_purchase_key', true ),
	        'currency'      => edd_get_option( 'currency', 'usd' ),
	        'downloads'     => $downloads,
	        'user_info'     => edd_get_payment_meta_user_info( $parent_id ),
	        'cart_details'  => edd_get_payment_meta_cart_details( $parent_id ),
	        'status'        => 'edd_subscription',
	        'gateway'       => edd_get_payment_gateway( $parent_id )
	    );

	    // record the subscription payment
	    $payment = edd_insert_payment( $payment_data );

	    if( ! empty( $unique_key ) )
	    	update_post_meta( $payment, '_edd_recurring_' . $unique_key, '1' );

		// Record transaction ID
		if( ! empty( $txn_id ) )
			edd_insert_payment_note( $payment, sprintf( __( 'PayPal Transaction ID: %s', 'edd' ) , $txn_id ) );

		// Update the expiration date of license keys, if EDD Software Licensing is active
		if( function_exists( 'edd_software_licensing' ) ) {
			$licenses = edd_software_licensing()->get_licenses_of_purchase( $payment );

			if( ! empty( $licenses ) ) {
				foreach( $licenses as $license ) {
					// Update the expiration dates of the license key
					edd_software_licensing()->renew_license( $license->ID, $payment );
				}
			}
		}

		do_action( 'edd_recurring_record_payment', $payment, $parent_id, $amount, $txn_id, $unique_key );

	}

	/**
	 * Checks if a payment already exists
	 *
	 * @since  1.0.2
	 * @return bool
	 */

	public function payment_exists( $unique_key = 0 ) {
		global $wpdb;

		if( empty( $unique_key ) )
			return false;

		$unique_key = esc_sql( $unique_key );

		$purchase = $wpdb->get_var( "SELECT post_id FROM $wpdb->postmeta WHERE meta_key = '_edd_recurring_{$unique_key}' LIMIT 1" );

		if ( $purchase != NULL )
			return true;

		return false;
	}


	/**
	 * Determines if a purchase contains a recurring product
	 *
	 * @since  1.0.1
	 * @return bool
	 */

	public function is_purchase_recurring( $purchase_data ) {

		foreach( $purchase_data['downloads'] as $download ) {

			if( isset( $download['options'] ) && isset( $download['options']['recurring'] ) )
				return true;
		}

		return false;

	}


	/**
	 * Make sure subscription payments get included in earning reports
	 *
	 * @since  1.0
	 * @return array
	 */

	public function earnings_query( $args ) {
		$args['post_status'] = array( 'publish', 'revoked', 'cancelled', 'edd_subscription' );
		return $args;
	}


	/**
	 * Make sure subscription payments get included in has user purchased query
	 *
	 * @since  2.1.5
	 * @return array
	 */

	public function has_purchased_query( $args ) {
		$args['status'] = array( 'publish', 'revoked', 'cancelled', 'edd_subscription' );
		return $args;
	}

	/**
	 * Displays the recurring details on the [edd_receipt]
	 *
	 * @since  1.0
	 * @return void
	 */

	public function receipt( $payment, $receipt_args ) {

		$downloads = edd_get_payment_meta_downloads( $payment->ID );
		$download  = isset( $downloads[0] ) ? $downloads[0] : $downloads[1];
		if( ! isset( $download['options']['recurring'] ) )
			return;
		$period    = $download['options']['recurring']['period'];
		$times     = $download['options']['recurring']['times'];
		$details   = '';

		if( $times > 0 ) {
			switch( $period ) {
				case 'day' :
					$details = sprintf( _n( 'Daily, %d Time', 'Daily, %d Times', $times, 'edd-recurring' ), $times );
				break;
				case 'week' :
					$details = sprintf( _n( 'Weekly, %d Time', 'Weekly, %d Times', $times, 'edd-recurring' ), $times );
				break;
				case 'month' :
					$details = sprintf( _n( 'Monthly, %d Time', 'Monthly, %d Times', $times, 'edd-recurring' ), $times );
				break;
				case 'year' :
					$details = sprintf( _n( 'Yearly, %d Time', 'Yearly, %d Times', $times, 'edd-recurring' ), $times );
				break;
			}
		} else {
			switch( $period ) {
				case 'day' :
					$details = __( 'Daily', 'edd-recurring' );
				break;
				case 'week' :
					$details = __( 'Weekly', 'edd-recurring' );
				break;
				case 'month' :
					$details = __( 'Monthly', 'edd-recurring' );
				break;
				case 'year' :
					$details = __( 'Yearly', 'edd-recurring' );
				break;
			}
		}

		if( ! empty( $details ) ) { ?>
		<tr>
			<td><strong><?php _e( 'Recurring Details', 'edd' ); ?>:</strong></td>
			<td><?php echo $details; ?></td>
		</tr>
		<?php
		}
	}


	/**
	 * Tells EDD to include child payments in queries
	 *
	 * @since  2.2
	 * @return void
	 */
	public function enable_child_payments( $query ) {
		$query->__set( 'post_parent', null );
	}

	/**
	 * Instruct EDD PDF Invoices that subscription paymentsare eligible for Invoices
	 *
	 * @since  2.2
	 * @return bool
	 */
	public function is_invoice_allowed( $ret, $payment_id ) {

		$payment_status = get_post_status( $payment_id );

		if( 'edd_subscription' == $payment_status ) {

			$parent = get_post_field( 'post_parent', $payment_id );
			if( edd_is_payment_complete( $parent ) ) {
				$ret = true;
			}

		}

		return $ret;
	}

	/**
	 * Displays a profile cancellation link
	 *
	 * @since  1.0
	 * @return string
	 */
	public function cancel_link( $atts, $content = null ) {
		global $user_ID;
		if( ! is_user_logged_in() ) {
			return;
		}

		if( ! EDD_Recurring_Customer::is_customer_active( $user_ID ) ) {
			return;
		}

		if( 'cancelled' === EDD_Recurring_Customer::get_customer_status( $user_ID ) ) {
			return;
		}

		$atts = shortcode_atts( array(
			'text' => ''
		), $atts );

		$cancel_url = 'https://www.paypal.com/cgi-bin/customerprofileweb?cmd=_manage-paylist';
		$link       = '<a href="%s" class="edd-recurring-cancel" target="_blank" title="%s">%s</a>';
		$link       = sprintf(
			$link,
			$cancel_url,
			__( 'Cancel your subscription', 'edd-recurring' ),
			empty( $atts['text'] ) ? __( 'Cancel Subscription', 'edd-recurring' ) : esc_html( $atts['text'] )
		);

		return apply_filters( 'edd_recurring_cancel_link', $link, $user_ID );
	}

}

/**
 * The main function responsible for returning the one true EDD_Recurring Instance
 * to functions everywhere.
 *
 * Use this function like you would a global variable, except without needing
 * to declare the global.
 *
 * Example: <?php $recurring = EDD_Recurring(); ?>
 *
 * @since v1.0
 *
 * @return The one true EDD_Recurring Instance
 */

function EDD_Recurring() {
	return EDD_Recurring::instance();
}
add_action( 'plugins_loaded', 'EDD_Recurring' );