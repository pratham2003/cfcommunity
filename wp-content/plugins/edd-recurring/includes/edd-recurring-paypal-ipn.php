<?php

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

class EDD_Recurring_PayPal_IPN {


	/**
	 * Processes the "signup" IPN notice
	 *
	 * @since  1.0
	 * @return void
	 */

	static public function process_paypal_subscr_signup( $ipn_data ) {

		$parent_payment_id = absint( $ipn_data['custom'] );

		edd_update_payment_status( $parent_payment_id, 'publish' );

		// Record transaction ID
		edd_insert_payment_note( $parent_payment_id, sprintf( __( 'PayPal Subscription ID: %s', 'edd' ) , $ipn_data['subscr_id'] ) );

		// Store the IPN track ID
		update_post_meta( $parent_payment_id, '_edd_recurring_ipn_track_id', $ipn_data['ipn_track_id'] );

		$user_id   = edd_get_payment_user_id( $ipn_data['custom'] );

		// Set user as subscriber
		EDD_Recurring_Customer::set_as_subscriber( $user_id );

		// store the customer recurring ID
		EDD_Recurring_Customer::set_customer_id( $user_id, $ipn_data['payer_id'] );

		// Store the original payment ID in the customer meta
		EDD_Recurring_Customer::set_customer_payment_id( $user_id, $ipn_data['custom'] );

		// Set the customer's status to active
		EDD_Recurring_Customer::set_customer_status( $user_id, 'active' );

		// Calculate the customer's new expiration date
		$new_expiration = EDD_Recurring_Customer::calc_user_expiration( $user_id, $parent_payment_id );

		// Set the customer's new expiration date
		EDD_Recurring_Customer::set_customer_expiration( $user_id, $new_expiration );

	}


	/**
	 * Processes the recurring payments as they come in
	 *
	 * @since  1.0
	 * @return void
	 */

	static public function process_paypal_subscr_payment( $ipn_data ) {

		global $edd_options;

		$parent_payment_id = absint( $ipn_data['custom'] );

		if( false !== get_transient( '_edd_recurring_payment_' . $parent_payment_id ) ) {
			die('2'); // This is the initial payment
		}

		$payment_amount    = $ipn_data['mc_gross'];
		$currency_code     = strtolower( $ipn_data['mc_currency'] );
		$user_id           = edd_get_payment_user_id( $parent_payment_id );
		// verify details
		if( $currency_code != strtolower( $edd_options['currency'] ) ) {
			// the currency code is invalid
			edd_record_gateway_error( __( 'IPN Error', 'edd' ), sprintf( __( 'Invalid currency in IPN response. IPN data: ', 'edd' ), json_encode( $ipn_data ) ) );
			return;
		}

		$key = md5( serialize( $ipn_data ) );

		// Store the payment
		EDD_Recurring()->record_subscription_payment( $parent_payment_id, $payment_amount, $ipn_data['txn_id'], $key );

		// Set the customer's status to active
		EDD_Recurring_Customer::set_customer_status( $user_id, 'active' );

		// Calculate the customer's new expiration date
		$new_expiration = EDD_Recurring_Customer::calc_user_expiration( $user_id, $parent_payment_id );

		// Set the customer's new expiration date
		EDD_Recurring_Customer::set_customer_expiration( $user_id, $new_expiration );

	}


	/**
	 * Processes the "cancel" IPN notice
	 *
	 * @since  1.0
	 * @return void
	 */

	static public function process_paypal_subscr_cancel( $ipn_data ) {

		$user_id = edd_get_payment_user_id( $ipn_data['custom'] );

		// set the customer status
		//EDD_Recurring_Customer::set_customer_status( $user_id, 'cancelled' );

		// Set the payment status to cancelled
		edd_update_payment_status( $ipn_data['custom'], 'cancelled' );

	}


	/**
	 * Processes the "end of term (eot)" IPN notice
	 *
	 * @since  1.0
	 * @return void
	 */

	static public function process_paypal_subscr_eot( $ipn_data ) {

		$user_id   = edd_get_payment_user_id( $ipn_data['custom'] );

		// set the customer status
		EDD_Recurring_Customer::set_customer_status( $user_id, 'expired' );

	}

}