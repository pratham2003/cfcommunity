<?php

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;


/**
 * The Cecurring Customer Class
 *
 * Includes methods for setting users as customers, setting their status, expiration, etc.
 *
 * @since  1.0
 */

class EDD_Recurring_Customer {

	/**
	 * Get us started
	 *
	 * @since  1.0
	 * @return void
	 */

	function __construct() {
		$this->init();
	}


	/**
	 * Setup EDD Subscriber role and add our filters
	 *
	 * @since  1.0
	 * @return array
	 */

	private function init() {

		// Create the EDD Subscriber user role
		$this->create_role();

		// Show the Downloads > Reports|Customers columns
		add_filter( 'edd_report_customer_columns', array( $this, 'report_columns' ) );
		add_filter( 'edd_report_column_subscriber', array( $this, 'report_column_subscriber' ), 10, 2 );
		add_filter( 'edd_report_column_status', array( $this, 'report_column_status' ), 10, 2 );
		add_filter( 'edd_report_column_expiration', array( $this, 'report_column_expiration' ), 10, 2 );

	}


	/**
	 * Create the EDD Subscriber role
	 *
	 * @since  1.0
	 * @return void
	 */

	private function create_role() {
		add_role( 'edd_subscriber', __( 'EDD Subscriber', 'edd-recurring' ), array( 'read' ) );
	}


	/**
	 * Set a user as a subscriber
	 *
	 * @since  1.0
	 * @param  $user_id INT The ID of the user we're setting as a subscriber
	 * @return void
	 */

	static public function set_as_subscriber( $user_id = 0 ) {

		$user = new WP_User( $user_id );
		$user->add_role( 'edd_subscriber' );

		do_action( 'edd_recurring_set_as_subscriber', $user_id );

	}


	/**
	 * Store a recurring customer ID
	 *
	 * @since  1.0
	 * @param  $user_id      INT The ID of the user we're setting as a subscriber
	 * @param  $recurring_id INT The recurring profile ID to set
	 * @return bool
	 */

	static public function set_customer_id( $user_id = 0, $recurring_id = '' ) {

		$id = apply_filters( 'edd_recurring_set_customer_id', $recurring_id, $user_id );

		return update_user_meta( $user_id, '_edd_recurring_id', $recurring_id );

	}


	/**
	 * Get a recurring customer ID
	 *
	 * @since  1.0
	 * @param  $user_id      INT The ID of the user we're getting an ID for
	 * @return str
	 */

	static public function get_customer_id( $user_id = 0 ) {

		return get_user_meta( $user_id, '_edd_recurring_id', true );

	}


	/**
	 * Get a user ID from the recurring customer ID
	 *
	 * @since  1.0.1
	 * @param  $recurring_id  STR The recurring ID of the user we're getting an ID for
	 * @return int
	 */
	static public function get_user_id_by_customer_id( $recurring_id = '' ) {
		global $wpdb;
		$user_id = $wpdb->get_var( $wpdb->prepare( "SELECT user_id FROM $wpdb->usermeta WHERE meta_key = '_edd_recurring_id' AND meta_value = '%s' LIMIT 1", $recurring_id ) );
		return $user_id;

	}


	/**
	 * Stores the parent payment ID for a customer
	 *
	 * @since  1.0.1
	 * @param  $user_id     INT The user ID to set a parent payment for
	 * @param  $payment_id  INT The Payment ID to set
	 * @return int
	 */
	static public function set_customer_payment_id( $user_id = 0, $payment_id = 0 ) {
		do_action( 'edd_recurring_set_customer_payment_id', $user_id, $payment_id );
		update_user_meta( $user_id, '_edd_recurring_user_parent_payment_id', $payment_id );
	}


	/**
	 * Get the parent payment ID for a customer
	 *
	 * @since  1.0.1
	 * @param  $user_id     INT The user ID to get a parent payment for
	 * @return int
	 */
	static public function get_customer_payment_id( $user_id = 0 ) {
		return get_user_meta( $user_id, '_edd_recurring_user_parent_payment_id', true );
	}


	/**
	 * Set a status for a customer
	 *
	 * @since  1.0
	 * @param  $user_id      INT The ID of the user we're setting a status for
	 * @param  $status       STRING The status to set
	 * @return bool
	 */

	static public function set_customer_status( $user_id = 0, $status = 'active' ) {

		$status = apply_filters( 'edd_recurring_set_customer_status', $status, $user_id );

		do_action( 'edd_recurring_set_user_status', $user_id, $status );

		return update_user_meta( $user_id, '_edd_recurring_status', $status );

	}


	/**
	 * Get customer status
	 *
	 * @since  1.0
	 * @param  $user_id      INT The ID of the user we're getting a status for
	 * @return bool
	 */

	static public function get_customer_status( $user_id = 0 ) {

		return get_user_meta( $user_id, '_edd_recurring_status', true );

	}


	/**
	 * Check if a customer is active
	 *
	 * @since  1.0
	 * @param  $user_id      INT The ID of the user we're checking
	 * @return bool
	 */

	static public function is_customer_active( $user_id = 0 ) {

		if( empty( $user_id ) )
			$user_id = get_current_user_id();

		$status = self::get_customer_status( $user_id );

		// Check if expired and set to expired if so
		if( self::is_customer_expired( $user_id ) ) {
			$status = 'expired';
			self::set_customer_status( $user_id, $status );
		}

		$active = $status == 'active' || $status == 'cancelled' ? true : false;

		return apply_filters( 'edd_recurring_is_user_active', $active, $user_id, $status );

	}


	/**
	 * Set an expiration date
	 *
	 * @since  1.0
	 * @param  $user_id      INT The ID of the user we're setting an expiration for
	 * @param  $expiration   INT The expiration timestamp
	 * @return bool
	 */

	static public function set_customer_expiration( $user_id = 0, $expiration = 0 ) {

		$date = apply_filters( 'edd_recurring_set_customer_expiration', $expiration, $user_id );

		do_action( 'edd_recurring_set_user_expiration', $user_id, $expiration );

		return update_user_meta( $user_id, '_edd_recurring_exp', $date );

	}


	/**
	 * Get an expiration date
	 *
	 * @since  1.0
	 * @param  $user_id      INT The ID of the user we're getting an expiration for
	 * @return int
	 */

	static public function get_customer_expiration( $user_id = 0 ) {
		$date = get_user_meta( $user_id, '_edd_recurring_exp', true );
		return $date;

	}


	/**
	 * Check if expired
	 *
	 * @since  1.0
	 * @param  $user_id      INT The ID of the user we're checking
	 * @return bool
	 */

	static public function is_customer_expired( $user_id = 0 ) {

		if( empty( $user_id ) )
			$user_id = get_current_user_id();

		$expiration = self::get_customer_expiration( $user_id );

		return time() > $expiration ? true : false;

	}


	/**
	 * Calculate a new expiration date
	 *
	 * @since  1.0
	 * @param  $user_id      INT The ID of the user we're setting an expiration for
	 * @param  $payment_id   INT The original payment ID
	 * @return int
	 */

	static public function calc_user_expiration( $user_id = 0, $payment_id = 0 ) {

		// Retrieve the items purchased from the original payment
		$downloads  = edd_get_payment_meta_downloads( $payment_id );
		$download   = $downloads[0]; // We only care about the first (and only) item
		$period     = $download['options']['recurring']['period'];
		$expiration = strtotime( '+ 1 ' . $period . ' 23:59:59' );

		return apply_filters( 'edd_recurring_calc_expiration', $expiration, $user_id, $payment_id, $period );
	}


	/**
	 * Setup customer report page columns
	 *
	 * @since  1.0
	 * @param  $columns   array Existing table columns
	 * @return array
	 */

	public function report_columns( $columns ) {
		$columns['subscriber'] = __( 'Subscriber', 'edd-recurring' );
		$columns['status']     = __( 'Status', 'edd-recurring' );
		$columns['expiration'] = __( 'Expiration', 'edd-recurring' );
		return $columns;
	}


	/**
	 * Display the subscriber column
	 *
	 * @since  1.0
	 * @param  $value      STRING The column's current value
	 * @param  $user_id    INT The ID of the user we're dispalying info for
	 * @return string
	 */

	public function report_column_subscriber( $value, $user_id ) {
		$subscriber = false;
		if( ! empty( $user_id ) ) {
			$user = new WP_User( $user_id );
			if( ( ( isset( $user->roles[0] ) && $user->roles[0] == 'edd_subscriber' ) || user_can( $user_id, 'edit_posts' ) ) && self::is_customer_active( $user_id ) )
				$subscriber = true;
		}
		return $subscriber ? __( 'Yes', 'edd-recurring' ) : __( 'No', 'edd-recurring' );
	}


	/**
	 * Display the Status column
	 *
	 * @since  1.0
	 * @param  $value      STRING The column's current value
	 * @param  $user_id    INT The ID of the user we're dispalying info for
	 * @return string
	 */

	public function report_column_status( $value, $user_id ) {
		$status = self::get_customer_status( $user_id );
		$status = ! empty( $status ) ? $status : __( 'N/A', 'edd-recurring' );
		return $status;
	}


	/**
	 * Display the expiration column
	 *
	 * @since  1.0
	 * @param  $value      STRING The column's current value
	 * @param  $user_id    INT The ID of the user we're dispalying info for
	 * @return string
	 */

	public function report_column_expiration( $value, $user_id ) {
		$expiration = self::get_customer_expiration( $user_id );
		$expiration = ! empty( $expiration ) ? date_i18n( get_option( 'date_format' ), $expiration ) : __( 'N/A', 'edd-recurring' );
		return $expiration;
	}

}