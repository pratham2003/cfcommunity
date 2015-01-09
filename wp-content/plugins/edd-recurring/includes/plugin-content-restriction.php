<?php


/**
 * Integrates EDD Recurring with the Content Restriction extension
 *
 * This allows content to be restricted to active subscribers only
 *
 * @since v1.0
 */

class EDD_Recurring_Content_Restriction {


	/**
	 * Get things started
	 *
	 * @since  1.0
	 * @return void
	 */

	function __construct() {
		add_action( 'admin_init', array( $this, 'admin_init' ) );

		// Pre 2.0 filter
		add_filter( 'edd_cr_is_restricted', array( $this, 'restrict' ), 10, 5 );
	
		// 2.0+ filter
		add_filter( 'edd_cr_user_can_access', array( $this, 'can_access_content' ), 10, 3 );
	}


	/**
	 * Load our admin actions
	 *
	 * @since  1.0
	 * @return void
	 */

	public function admin_init() {

		if( ! class_exists( 'EDD_Content_Restriction' ) )
			return; // Content Restriction extension not active

		add_action( 'edd_cr_metabox', array( $this, 'metabox' ), 10, 3 );
		add_action( 'edd_cr_save_meta_data', array( $this, 'save_data' ), 10, 2 );
	}


	/**
	 * Attach our extra meta box field
	 *
	 * @since  1.0
	 * @return void
	 */

	public function metabox( $post_id, $restricted_to, $restricted_variable ) {

		$active_only = get_post_meta( $post_id, '_edd_cr_active_only', true );
		echo '<p>';
			echo '<label for="edd_cr_active_only" title="' . __( 'Only customers with an active recurring subscription will be able to view the content.', 'edd' ) . '">';
				echo '<input type="checkbox" name="edd_cr_active_only" id="edd_cr_active_only" value="1"' . checked( '1', $active_only, false ) . '/>&nbsp;';
				echo __( 'Active Subscribers Only?', 'edd-recurring' );
			echo '</label>';
		echo '</p>';
	}


	/**
	 * Save data from the meta box
	 *
	 * @since  1.0
	 * @return void
	 */


	public function save_data( $post_id, $data ) {

		if( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE )
			return;

		if( isset( $data['edd_cr_active_only'] ) ) {
			update_post_meta( $post_id, '_edd_cr_active_only', '1' );
		} else {
			delete_post_meta( $post_id, '_edd_cr_active_only' );
		}
	}


	/**
	 * Check if user has access to content
	 *
	 * @since  1.0
	 * @return bool
	 */
	public function restrict( $is_restricted = false, $post_id = 0, $download_id = 0, $user_id = 0, $price_id = null ) {

		if( ! edd_cr_is_restricted( $post_id ) )
			return $is_restricted;

		if( ! get_post_meta( $post_id, '_edd_cr_active_only', true ) )
			return $is_restricted; // Leave untouched

		if( ! EDD_Recurring_Customer::is_customer_active( $user_id ) )
			return true;

		return $is_restricted;
	}

	/**
	 * Check if user has access to content
	 *
	 * @since  2.2.7
	 * @return bool
	 */
	public function can_access_content( $has_access, $user_id, $restricted_to ) {

		if( $has_access && is_array( $restricted_to ) ) {

			foreach( $restricted_to as $item ) {

				if( get_post_meta( get_the_ID(), '_edd_cr_active_only', true ) ) {

					if( ! EDD_Recurring_Customer::is_customer_active( $user_id ) ) {

						$has_access = false;
						break;

					}
					
				}

			}

		}

		return $has_access;
	}

}