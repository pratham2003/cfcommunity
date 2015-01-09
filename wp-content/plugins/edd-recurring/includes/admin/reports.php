<?php

/**
 * Show subscription payment statuses in Payment History
 *
 * @since  2.2
 * @return void
 */
function edd_recurring_subscription_status_column( $value, $payment_id, $column_name ) {

	if( 'status' == $column_name && 'edd_subscription' == get_post_status( $payment_id ) ) {
		$value = __( 'Subscription Payment', 'edd-recurring' );
	}

	return $value;
}
add_filter( 'edd_payments_table_column', 'edd_recurring_subscription_status_column', 800, 3 );

/**
 * List subscription (sub) payments of a particular parent payment
 *
 * The parent payment ID is the very first payment made. All payments made after for the profile are sub.
 *
 * @since  1.0
 * @return void
 */
function edd_recurring_list_sub_payments( $parent_id = 0 ) {

	$payments = get_posts( array(
		'post_status'    => 'edd_subscription',
		'post_type'      => 'edd_payment',
		'post_parent'    => $parent_id,
		'posts_per_page' => -1
	) );

	if( $payments ) : ?>
	<div id="edd-order-subscription-payments" class="postbox">
		<h3 class="hndle">
			<span><?php _e( 'Subscription Payments', 'edd-recurring' ); ?></span>
		</h3>
		<div class="inside">
			<ul id="edd-recurring-sub-payments">
			<?php foreach( $payments as $payment ) : ?>
				<li>
					<a href="<?php echo esc_url( admin_url( 'edit.php?post_type=download&page=edd-payment-history&view=view-order-details&id=' . $payment->ID ) ); ?>">
						<?php if( function_exists( 'edd_get_payment_number' ) ) : ?>
							<?php echo '#' . edd_get_payment_number( $payment->ID ); ?>
						<?php else : ?>
							<?php echo '#' . $payment->ID; ?>
						<?php endif; ?>&nbsp;&ndash;&nbsp;
					</a>
					<span><?php echo date_i18n( get_option( 'date_format' ), strtotime( $payment->post_date ) ); ?>&nbsp;&ndash;&nbsp;</span>
					<span><?php echo edd_payment_amount( $payment->ID ); ?></span>
				</li>
			<?php endforeach; ?>
			</ul>
		</div><!-- /.inside -->
	</div><!-- /#edd-order-subscription-payments -->
	<?php endif;
}
add_action( 'edd_view_order_details_sidebar_before', 'edd_recurring_list_sub_payments', 10 );

/**
 * List subscription (sub) payments of a particular parent payment
 *
 * The parent payment ID is the very first payment made. All payments made after for the profile are sub.
 *
 * @since  1.0
 * @return void
 */

function edd_recurring_display_parent_payment( $payment_id = 0 ) {

	$payment = get_post( $payment_id );

	if( 'edd_subscription' == $payment->post_status ) :

		$parent_url = admin_url( 'edit.php?post_type=download&page=edd-payment-history&view=view-order-details&id=' . $payment->post_parent );
		$parent_id  = function_exists( 'edd_get_payment_number' ) ? edd_get_payment_number( $payment->post_parent ) : $payment->post_parent;
?>
		<div id="edd-order-subscription-payments" class="postbox">
			<h3 class="hndle">
				<span><?php _e( 'Subscription Payments', 'edd-recurring' ); ?></span>
			</h3>
			<div class="inside">
				<p><?php printf( __( 'Parent Payment: <a href="%s">%s</a>' ), $parent_url, $parent_id ); ?></p>
			</div><!-- /.inside -->
		</div><!-- /#edd-order-subscription-payments -->
<?php
	endif;
}
add_action( 'edd_view_order_details_sidebar_before', 'edd_recurring_display_parent_payment', 10 );


/**
 * Adds "Subscribers" to the report views
 *
 * @access      public
 * @since       1.0
 * @return      array
*/

function edd_recurring_add_subscribers_view( $views ) {
    $views['subscribers'] = __( 'Subscribers', 'edd-recurring' );
    return $views;
}
add_filter( 'edd_report_views', 'edd_recurring_add_subscribers_view' );


/**
 * Render the Subscribers view
 *
 * @access      public
 * @since       1.0
 * @return      void
*/
function edd_recurring_subscribers_view() {
  ?>
    <div class="wrap">
<?php

        if( isset( $_GET['subscriber'] ) ) : ?>

        	<?php

        	// Load jQuery datepicker
        	$suffix = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';
        	wp_enqueue_script( 'jquery-ui-datepicker' );
			$ui_style = ( 'classic' == get_user_option( 'admin_color' ) ) ? 'classic' : 'fresh';
			wp_enqueue_style( 'jquery-ui-css', EDD_PLUGIN_URL . 'assets/css/jquery-ui-' . $ui_style . $suffix . '.css' );

        	$subscriber_id = absint( $_GET['subscriber'] );
        	$recurring_id  = EDD_Recurring_Customer::get_customer_id( $subscriber_id );
        	$expiration    = EDD_Recurring_Customer::get_customer_expiration( $subscriber_id );
        	$expiration    = ! empty( $expiration ) ? date( 'm/d/Y', $expiration ) : '';
        	$status        = EDD_Recurring_Customer::get_customer_status( $subscriber_id );
        	$active        = EDD_Recurring_Customer::is_customer_active( $subscriber_id );
        	?>

        	<h2><?php printf( __( 'Edit Subscriber: %d', 'edd-recurring' ), absint( $_GET['subscriber'] ) ); ?></h2>

			<form id="edd-edit-subscriber" action="" method="post">
				<?php do_action( 'edd_edit_subscriber_form_top', $subscriber_id ); ?>
				<table class="form-table">
					<tbody>
						<tr class="form-field">
							<th scope="row" valign="top">
								<label for="edd-expiration"><?php _e( 'Expiration date', 'edd-recurring' ); ?></label>
							</th>
							<td>
								<input name="expiration" id="edd-expiration" type="text" value="<?php echo esc_attr( $expiration ); ?>" style="width: 120px;" class="edd_datepicker"/>
								<p class="description"><?php _e( 'Enter the expiration date for subscriber.', 'edd-recurring' ); ?></p>
							</td>
						</tr>
						<tr class="form-field">
							<th scope="row" valign="top">
								<label for="edd-status"><?php _e( 'Status', 'edd-recurring' ); ?></label>
							</th>
							<td>
								<select name="status" id="edd-status">
									<option value="active" <?php selected( $status, 'active' ); ?>><?php _e( 'Active', 'edd-recurring' ); ?></option>
									<option value="expired"<?php selected( $status, 'expired' ); ?>><?php _e( 'Expired', 'edd-recurring' ); ?></option>
								</select>
								<p class="description"><?php _e( 'The status of this subscriber\'s subscription..', 'edd-recurring' ); ?></p>
							</td>
						</tr>
						<tr class="form-field">
							<th scope="row" valign="top">
								<label for="edd-recurring-id"><?php _e( 'Recurring ID', 'edd-recurring' ); ?></label>
							</th>
							<td>
								<input name="recurring-id" id="edd-recurring-id" type="text" value="<?php echo esc_attr( $recurring_id ); ?>" style="width: 120px;"/>
								<p class="description"><?php _e( 'Enter the recurring ID for the subscriber. This is the unique ID that identifies this user in the payment processor. Leave blank if unknown or unavailable.', 'edd-recurring' ); ?></p>
							</td>
						</tr>
					</tbody>
				</table>
				<?php do_action( 'edd_edit_subscriber_form_bottom', $subscriber_id ); ?>
				<p class="submit">
					<input type="hidden" name="edd-action" value="edit_subscriber"/>
					<input type="hidden" name="subscriber-id" value="<?php echo absint( $_GET['subscriber'] ); ?>"/>
					<input type="hidden" name="edd-subscriber-nonce" value="<?php echo wp_create_nonce( 'edd_subscriber_nonce' ); ?>"/>
					<input type="submit" value="<?php _e( 'Update Subscriber', 'edd-recurring' ); ?>" class="button-primary"/>
				</p>
			</form>

        <?php else : ?>
	
	        <h2><?php _e('Subscribers', 'edd-recurring'); ?></h2>
	        <?php
	        $subscribers_table = new EDD_Subscriber_Reports_Table();

	        $subscribers_table->prepare_items();

	        ?>

	        <form id="subscribers-filter" method="get">

	            <input type="hidden" name="post_type" value="download" />
	            <input type="hidden" name="page" value="edd-reports" />
	            <input type="hidden" name="view" value="subscribers" />
	            <!-- Now we can render the completed list table -->
	            <?php $subscribers_table->views() ?>

	            <?php $subscribers_table->display() ?>

	        </form>
      <?php endif; ?>
   </div>
<?php
}
add_action('edd_reports_view_subscribers', 'edd_recurring_subscribers_view');

/**
 * Saves a subscriber after editing
 *
 * @access      public
 * @since       2.0
 * @return      void
*/
function edd_recurring_save_subscriber_edit( $data ) {

	if ( ! wp_verify_nonce( $data['edd-subscriber-nonce'], 'edd_subscriber_nonce' ) )
		wp_die( __( 'Cheating, eh?', 'edd-recurring' ) );

	if( empty( $data['subscriber-id'] ) )
		return;

	$subscriber_id = absint( $data['subscriber-id'] );
	$recurring_id  = $data['recurring-id'];
	$expiration    = strtotime( $data['expiration'], current_time( 'timestamp' ) );
	$status        = $data['status'];

	EDD_Recurring_Customer::set_customer_id( $subscriber_id, $recurring_id );
	EDD_Recurring_Customer::set_customer_status( $subscriber_id, $status );
	EDD_Recurring_Customer::set_customer_expiration( $subscriber_id, $expiration );

	wp_safe_redirect( admin_url( 'edit.php?view=subscribers&post_type=download&page=edd-reports&edd-message=subscriber-updated' ) ); exit;
}
add_action( 'edd_edit_subscriber', 'edd_recurring_save_subscriber_edit' );

/**
 * Displays Subscriber Updated notice
 *
 * @access      public
 * @since       2.0
 * @return      void
*/
function edd_recurring_subscriber_saved_notice() {

	if( !isset( $_GET['edd-message'] ) || 'subscriber-updated' != $_GET['edd-message'] )
		return;

	echo '<div class="updated"><p>' . __( 'Subscriber updated successfully', 'edd-recurring' ) . '</p></div>';

}
add_action( 'admin_notices', 'edd_recurring_subscriber_saved_notice' );

/**
 * Adds an Edit Subscription link to user row actions
 *
 * @access      public
 * @since       2.0
 * @return      Array
*/
function edd_recurring_user_row_actions( $actions, $user ) {
	$actions['edit_subscriber'] = '<a href="' . esc_url( admin_url( 'edit.php?view=subscribers&post_type=download&page=edd-reports&subscriber=' . $user->ID ) ) . '" title="' . esc_attr( __( 'Edit this Subscriber', 'edd-recurring' ) ) . '">' . __( 'Edit Subscription', 'edd-recurring' ) . '</a>';
	return $actions;
}
add_filter( 'user_row_actions', 'edd_recurring_user_row_actions', 999, 2 );