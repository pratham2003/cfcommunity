<?php
/**
 * This file contains functions that modify EDD that suit our donation needs.
 * Functions might be moved to a seperate plugin.
 */
/*
 * Remove download links from checkout page for all downloads
 */  
function cfc_edd_receipt_show_download_files() {
	return false;
}
add_filter( 'edd_receipt_show_download_files', 'cfc_edd_receipt_show_download_files' );

/*
 * Add custom text just before the "Purchase" button at checkout
 */ 
function cfc_edd_before_purchase_form() { ?>



<?php echo edd_get_price_name() ?>

	<p><?php _e('Thank you for wanting to donate to CFCommunity! Before you continue please check the amount you would like to donate.', 'cfcommunity'); ?>	</p>

<?php }
add_action( 'edd_before_purchase_form', 'cfc_edd_before_purchase_form', 1 );

/*
 * Add custom text just before the "Purchase" button at checkout
 */ 
function cfc_edd_choose_payment() { ?>
	<p class="secure-payment"><i class="fa fa-lock"></i> <?php _e('Please choose one of our <strong>secure</strong> payment methods below.', 'cfcommunity'); ?>	</p>
<?php }
add_action( 'edd_before_purchase_form', 'cfc_edd_choose_payment', 11 );

/*
 * Add custom text just before the "Purchase" button at checkout
 */ 
function cfc_edd_purchase_form_before_submit() { ?>
	<p><?php _e('Click on the button below to make your donation', 'cfcommunity'); ?>	</p>
<?php }
add_action( 'edd_purchase_form_before_submit', 'cfc_edd_purchase_form_before_submit', 1000 );

/*
 * Remove decimal places from all prices
 */
function cfc_edd_remove_decimals( $decimals ) {
 
	return 0;
}
add_filter( 'edd_format_amount_decimals', 'cfc_edd_remove_decimals' );

/*
 * Remove the default final checkout price
 */
function cfc_edd_replace_final_total() {
	remove_action( 'edd_purchase_form_before_submit', 'edd_checkout_final_total', 999 );
}
add_action( 'init', 'cfc_edd_replace_final_total' );


/**
 * Replace total at the bottom of the checkout page
 *
 */
function cfc_edd_checkout_final_total() {
?>
<p id="edd_final_total_wrap">
	<strong><?php _e( 'Donation Total:', 'cfcommunity' ); ?></strong>
	<span class="edd_cart_amount" data-subtotal="<?php echo edd_get_cart_subtotal(); ?>" data-total="<?php echo edd_get_cart_subtotal(); ?>"><?php edd_cart_total(); ?></span>
</p>
<?php
}
add_action( 'edd_purchase_form_before_submit', 'cfc_edd_checkout_final_total', 999 );
?>