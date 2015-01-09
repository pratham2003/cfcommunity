<?php
/**
 * Subscriber Reports Table Class
 *
 * @package     EDD Recurring
 * @subpackage  Subscriber Reports List Table Class
 * @copyright   Copyright (c) 2013, Pippin Williamson
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 */


// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;


// Load WP_List_Table if not loaded
if( ! class_exists( 'WP_List_Table' ) ) {
	require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

/**
 * EDD Customer Reports Table Class
 *
 * Renders the Customer Reports table
 *
 * @access      private
 */

class EDD_Subscriber_Reports_Table extends WP_List_Table {

	/**
	 * Number of results to show per page
	 *
	 * @since       1.0
	 */

	public $per_page = 30;


	/**
	 * Subscribers object
	 *
	 * @since       1.0
	 */

	public $subscribers;


	/**
	 * Get things started
	 *
	 * @access      private
	 * @since       1.0
	 * @return      void
	 */

	function __construct(){
		global $status, $page;

		// Set parent defaults
		parent::__construct( array(
			'singular'  => __( 'Subscriber', 'edd' ),     // Singular name of the listed records
			'plural'    => __( 'Subscribers', 'edd' ),    // Plural name of the listed records
			'ajax'      => false             			// Does this table support ajax?
		) );

		$this->subscribers = $this->query();

	}


	/**
	 * Render most columns
	 *
	 * @access      private
	 * @since       1.0
	 * @return      string
	 */

	function column_default( $item, $column_name ) {
		switch( $column_name ) {
			default:
				$value = isset( $item[ $column_name ] ) ? $item[ $column_name ] : null;
				return apply_filters( 'edd_subscriber_report_column_' . $column_name, $value, $item['ID'] );
		}
	}

	/**
	 * Render the edit column
	 *
	 * @access      private
	 * @since       2.0
	 * @return      string
	 */

	function column_edit( $item, $column_name = '' ) {
		return '<a href="' . esc_url( admin_url( 'edit.php?view=subscribers&post_type=download&page=edd-reports&subscriber=' . $item['ID'] ) ) . '" title="' . esc_attr( __( 'Edit this Subscriber', 'edd-recurring' ) ) . '">' . __( 'Edit', 'edd-recurring' ) . '</a>';
	}


	/**
	 * Retrieve the table columns
	 *
	 * @access      private
	 * @since       1.0
	 * @return      array
	 */

	function get_columns(){
		$columns = array(
			'ID'          => __( 'ID', 'edd-recurring' ),
			'username'    => __( 'Username', 'edd-recurring' ),
			'status'      => __( 'Status', 'edd-recurring' ),
			'expiration'  => __( 'Expiration', 'edd-recurring' ),
			'recurring_id'=> __( 'Recurring ID', 'edd-recurring' ),
			'edit'        => __( 'Edit', 'edd-recurring' ),
		);

		return apply_filters( 'edd_report_subscriber_columns', $columns );
	}


	/**
	 * Show reporting views
	 *
	 * @access      private
	 * @since       1.0
	 * @return      void
	 */

	function bulk_actions( $which = '' ) {
		// These aren't really bulk actions but this outputs the markup in the right place
		edd_report_views();
	}


	/**
	 * Retrieve the current page number
	 *
	 * @access      private
	 * @since       1.0
	 * @return      int
	 */

	function get_paged() {
		return isset( $_GET['paged'] ) ? absint( $_GET['paged'] ) : 1;
	}


	/**
	 * Subscriber query
	 *
	 * @access      private
	 * @since       1.0
	 * @return      object
	 */

	function query() {
		$paged        = $this->get_paged();
		$offset       = $this->per_page * ( $paged - 1 );
		$args         = apply_filters( 'edd_recurring_subscribers_query', array(
			'role'    => 'edd_subscriber',
			'number'  => $this->per_page,
			'offset'  => $offset
		) );
		$subscribers  = new WP_User_Query( $args );
		return $subscribers;
	}


	/**
	 * Get the total number of subscribers
	 *
	 * @access      private
	 * @since       1.0
	 * @return      int
	 */

	function get_total_subscribers() {
		$args         = array(
			'role'    => 'edd_subscriber',
			'number'  => 99999
		);
		$subscribers  = new WP_User_Query( $args );
		return $subscribers->total_users;
	}


	/**
	 * Setup final data
	 *
	 * @access      private
	 * @since       1.0
	 * @return      array
	 */

	function reports_data() {
		global $wpdb;

		$reports_data = array();
		$subscribers = $this->query();

		if ( ! empty( $subscribers->results ) ) {
			foreach ( $subscribers->results as $subscriber ) {

				$expiration     = EDD_Recurring_Customer::get_customer_expiration( $subscriber->ID );
				$exp_date       = ! empty( $expiration ) ? date( get_option( 'date_format' ), $expiration ) : '';
				$status         = EDD_Recurring_Customer::get_customer_status( $subscriber->ID );
				$status         = ! empty( $status ) ? $status : __( 'none', 'edd-recurring' );
				$recurring_id   = EDD_Recurring_Customer::get_customer_id( $subscriber->ID );
				$recurring_id   = ! empty( $recurring_id ) ? $recurring_id : __( 'none', 'edd-recurring' );

				$reports_data[] = array(
					'ID' 		   => $subscriber->ID,
					'username'     => $subscriber->user_login,
					'status'       => $status,
					'expiration'   => $exp_date,
					'recurring_id' => $recurring_id
				);
			}
		}

		return $reports_data;
	}


	/**
	 * Setup the final data for the table
	 *
	 * @access      private
	 * @since       1.0
	 * @uses        $this->_column_headers
	 * @uses        $this->items
	 * @uses        $this->get_columns()
	 * @uses        $this->get_sortable_columns()
	 * @uses        $this->get_pagenum()
	 * @uses        $this->set_pagination_args()
	 * @return      array
	 */

	function prepare_items() {
		$columns = $this->get_columns();

		$hidden = array(); // No hidden columns

		$sortable = $this->get_sortable_columns();

		$this->_column_headers = array( $columns, $hidden, $sortable );

		$current_page = $this->get_pagenum();

		$total_items = $this->get_total_subscribers();

		$this->items = $this->reports_data();

		$this->set_pagination_args( array(
			'total_items' => $total_items,                  	// WE have to calculate the total number of items
			'per_page'    => $this->per_page,                     	// WE have to determine how many items to show on a page
			'total_pages' => ceil( $total_items / $this->per_page )   // WE have to calculate the total number of pages
		) );
	}
}