<?php

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

/*
|--------------------------------------------------------------------------
| Variable Prices
|--------------------------------------------------------------------------
*/


/**
 * Meta box table header
 *
 * @access      public
 * @since       1.0
 * @return      void
 */

function edd_recurring_metabox_head( $download_id ) {
?>
	<th><?php _e( 'Recurring', 'edd-recurring' ); ?></th>
	<th><?php _e( 'Period', 'edd-recurring' ); ?></th>
	<th><?php echo _x( 'Times', 'Referring to billing period', 'edd-recurring' ); ?></th>
	<th><?php echo _x( 'Signup Fee', 'Referring to subscription signup fee', 'edd-recurring' ); ?></th>
<?php
}
add_action( 'edd_download_price_table_head', 'edd_recurring_metabox_head', 999 );


/**
 * Meta box is recurring yes/no field
 *
 * @access      public
 * @since       1.0
 * @return      void
 */

function edd_recurring_metabox_recurring( $download_id, $price_id, $args ) {

	$recurring = EDD_Recurring()->is_price_recurring( $download_id, $price_id );

?>
	<td>
		<select name="edd_variable_prices[<?php echo $price_id; ?>][recurring]" id="edd_variable_prices[<?php echo $price_id; ?>][recurring]">
			<option value="no" <?php selected( $recurring, false ); ?>><?php echo esc_attr_e( 'No', 'edd-recurring' ); ?></option>
			<option value="yes" <?php selected( $recurring, true ); ?>><?php echo esc_attr_e( 'Yes', 'edd-recurring' ); ?></option>
		</select>
	</td>
<?php
}
add_action( 'edd_download_price_table_row', 'edd_recurring_metabox_recurring', 999, 3 );


/**
 * Meta box recurring period field
 *
 * @access      public
 * @since       1.0
 * @return      void
 */

function edd_recurring_metabox_period( $download_id, $price_id, $args ) {

	$recurring = EDD_Recurring()->is_price_recurring( $download_id, $price_id );
	$periods   = EDD_Recurring()->periods();
	$period    = EDD_Recurring()->get_period( $price_id );

	$disabled  = $recurring ? '' : 'disabled="disabled" ';

?>
	<td>
		<select <?php echo $disabled; ?>name="edd_variable_prices[<?php echo $price_id; ?>][period]" id="edd_variable_prices[<?php echo $price_id; ?>][period]">
			<?php foreach ( $periods as $key => $value ) : ?>
			<option value="<?php echo $key; ?>" <?php selected( $period, $key ); ?>><?php echo esc_attr( $value ); ?></option>
			<?php endforeach; ?>
		</select>
	</td>
<?php
}
add_action( 'edd_download_price_table_row', 'edd_recurring_metabox_period', 999, 3 );


/**
 * Meta box recurring times field
 *
 * @access      public
 * @since       1.0
 * @return      void
 */

function edd_recurring_metabox_times( $download_id, $price_id, $args ) {

	$recurring = EDD_Recurring()->is_price_recurring( $download_id, $price_id );
	$times     = EDD_Recurring()->get_times( $price_id );
	$period    = EDD_Recurring()->get_period( $price_id );

	$disabled  = $recurring ? '' : 'disabled="disabled" ';

?>
	<td class="times">
		<input <?php echo $disabled; ?>type="number" min="0" step="1" name="edd_variable_prices[<?php echo $price_id; ?>][times]" id="edd_variable_prices[<?php echo $price_id; ?>][times]" size="4" style="width: 40px" value="<?php echo $times; ?>" />
	</td>
<?php
}
add_action( 'edd_download_price_table_row', 'edd_recurring_metabox_times', 999, 3 );


/**
 * Meta box recurring fee field
 *
 * @access      public
 * @since       1.1
 * @return      void
 */

function edd_recurring_metabox_signup_fee( $download_id, $price_id, $args ) {

	$recurring  = EDD_Recurring()->is_price_recurring( $download_id, $price_id );
	$signup_fee = EDD_Recurring()->get_signup_fee( $price_id, $download_id );

	$disabled   = $recurring ? '' : 'disabled="disabled" ';

?>
	<td class="signup_fee">
		<input <?php echo $disabled; ?>type="number" step="0.01" name="edd_variable_prices[<?php echo $price_id; ?>][signup_fee]" id="edd_variable_prices[<?php echo $price_id; ?>][signup_fee]" size="4" style="width: 60px" value="<?php echo $signup_fee; ?>" />
	</td>
<?php
}
add_action( 'edd_download_price_table_row', 'edd_recurring_metabox_signup_fee', 999, 3 );


/**
 * Meta fields for EDD to save
 *
 * @access      public
 * @since       1.0
 * @return      array
 */

function edd_recurring_save_single( $fields ) {
	$fields[] = 'edd_period';
	$fields[] = 'edd_times';
	$fields[] = 'edd_recurring';
	$fields[] = 'edd_signup_fee';

	return $fields;
}
add_filter( 'edd_metabox_fields_save', 'edd_recurring_save_single' );


/**
 * Set colspan on submit row
 *
 * This is a little hacky, but it's the best way to adjust the colspan on the submit row to make sure it goes full width
 *
 * @access      private
 * @since       1.0
 * @return      void
 */

function edd_recurring_metabox_colspan() {
	echo '<script type="text/javascript">jQuery(function($){ $("#edd_price_fields td.submit").attr("colspan", 7)});</script>';
}
add_action( 'edd_meta_box_fields', 'edd_recurring_metabox_colspan', 20 );


/*
|--------------------------------------------------------------------------
| Single Price Options
|--------------------------------------------------------------------------
*/


/**
 * Meta box is recurring yes/no field
 *
 * @access      public
 * @since       1.0
 * @return      void
 */

function edd_recurring_metabox_single_recurring( $download_id ) {

	$recurring = EDD_Recurring()->is_recurring( $download_id );

?>
	<label><?php _e( 'Recurring', 'edd-recurring' ); ?></label>
	<select name="edd_recurring" id="edd_recurring">
		<option value="no" <?php selected( $recurring, false ); ?>><?php echo esc_attr_e( 'No', 'edd-recurring' ); ?></option>
		<option value="yes" <?php selected( $recurring, true ); ?>><?php echo esc_attr_e( 'Yes', 'edd-recurring' ); ?></option>
	</select>
<?php
}
add_action( 'edd_price_field', 'edd_recurring_metabox_single_recurring', 10 );


/**
 * Meta box recurring period field
 *
 * @access      public
 * @since       1.0
 * @return      void
 */

function edd_recurring_metabox_single_period( $download_id ) {

	$periods = EDD_Recurring()->periods();
	$period  = EDD_Recurring()->get_period_single( $download_id );
?>
	<label><?php _e( 'Period', 'edd-recurring' ); ?></label>
	<select name="edd_period" id="edd_period">
		<?php foreach ( $periods as $key => $value ) : ?>
		<option value="<?php echo $key; ?>" <?php selected( $period, $key ); ?>><?php echo esc_attr( $value ); ?></option>
		<?php endforeach; ?>
	</select>
<?php
}
add_action( 'edd_price_field', 'edd_recurring_metabox_single_period', 10 );


/**
 * Meta box recurring times field
 *
 * @access      public
 * @since       1.0
 * @return      void
 */

function edd_recurring_metabox_single_times( $download_id ) {

	$times   = EDD_Recurring()->get_times_single( $download_id );
?>

	<span class="times">
		<label><?php _e( 'Times', 'edd-recurring' ); ?></label>
		<input type="number" min="0" step="1" name="edd_times" id="edd_times" size="4" style="width: 40px" value="<?php echo $times; ?>" />
	</span>
<?php
}
add_action( 'edd_price_field', 'edd_recurring_metabox_single_times', 20 );


/**
 * Meta box recurring signup fee field
 *
 * @access      public
 * @since       1.1
 * @return      void
 */

function edd_recurring_metabox_single_signup_fee( $download_id ) {

	$signup_fee   = EDD_Recurring()->get_signup_fee_single( $download_id );
?>

	<span class="signup_fee">
		<label><?php _e( 'Signup Fee', 'edd-recurring' ); ?></label>
		<input type="number" step="0.01" name="edd_signup_fee" id="edd_signup_fee" size="4" style="width: 60px" value="<?php echo $signup_fee; ?>" />
	</span>
<?php
}
add_action( 'edd_price_field', 'edd_recurring_metabox_single_signup_fee', 20 );


/**
 * After pricing information is saved, create or update any payment plans with the registered payment gateway.
 *
 * @param   integer $post_id
 * @since   1.0
 * @return  void
 */

function edd_create_payment_plans( $post_id ) {
	global $post;

	// verify nonce
	if ( isset( $_POST['edd_download_meta_box_nonce'] ) && ! wp_verify_nonce( $_POST['edd_download_meta_box_nonce'], basename( __FILE__ ) ) ) {
		return $post_id;
	}

	// check autosave
	if ( ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) || ( defined( 'DOING_AJAX' ) && DOING_AJAX ) || isset( $_REQUEST['bulk_edit'] ) ) {
		return $post_id;
	}

	//don't save if only a revision
	if ( isset( $post->post_type ) && $post->post_type == 'revision' ) {
		return $post_id;
	}

	// check permissions
	if ( isset( $_POST['post_type'] ) && 'page' == $_POST['post_type'] ) {
		if ( ! current_user_can( 'edit_page', $post_id ) ) {
			return $post_id;
		}
	} elseif ( ! current_user_can( 'edit_post', $post_id ) ) {
		return $post_id;
	}

	$payment_plans = array();

	// Check that this is a recurring single price
	if ( 'yes' === get_post_meta( $post_id, 'edd_recurring', true ) && get_post_meta( $post_id, '_variable_pricing', true ) ) {
		$single_payment = new stdClass();
		$single_payment->name = "edd_single";
		$single_payment->price = get_post_meta( $post_id, 'edd_price', true );
		$single_payment->period = get_post_meta( $post_id, 'edd_period', true );
		$single_payment->times = get_post_meta( $post_id, 'edd_times', true );

		$payment_plans[] = $single_payment;
	}

	// Check that this has recurring variable prices
	$variable_prices = get_post_meta( $post_id, 'edd_variable_prices', true );
	if ( is_array( $variable_prices ) ) {
		foreach( $variable_prices as $variable ) {
			$variable_plan = new stdClass();
			$variable_plan->name = $variable['name'];
			$variable_plan->price = $variable['amount'];
			$variable_plan->period = $variable['period'];
			$variable_plan->times = $variable['times'];

			$payment_plans[] = $variable_plan;
		}
	}

	// Allow individual gateways to handle payment plans
	do_action( 'edd_recurring_payment_plans', $payment_plans );
}
//add_action( 'save_post', 'edd_create_payment_plans', 11, 1 );



/*
|--------------------------------------------------------------------------
| Scripts
|--------------------------------------------------------------------------
*/


/**
 * Load the admin javascript
 *
 * @access      public
 * @since       1.0
 * @return      void
 */

function edd_recurring_admin_scripts( $hook ) {
	global $post, $edd_recurring;

	if( ! is_object( $post ) )
		return;

	if ( 'download' != $post->post_type )
		return;

	$pages = array( 'post.php', 'post-new.php' );

	if ( ! in_array( $hook, $pages ) )
		return;

	wp_enqueue_script( 'edd-recurring', EDD_Recurring::$plugin_dir . '/assets/js/edd-recurring.js');

	$trans_args = array(
		'singular' => _x( 'time', 'Referring to billing period', 'edd' ),
		'plural'   => _x( 'times', 'Referring to billing period', 'edd' )
	);

	wp_localize_script( 'edd-recurring', 'EDD_Recurring_Vars', $trans_args );
}
add_action( 'admin_enqueue_scripts', 'edd_recurring_admin_scripts' );
