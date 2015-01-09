<?php

/**
* Register our settings
*
* @since  1.0
* @return array
*/

function edd_recurring_settings( $settings ) {

	$recurring_settings = array(
		array(
			'id'  	=> 'recurring_settings',
			'name'  => '<strong>' . __( 'Recurring Settings', 'edd-recurring' ) . '</strong>',
			'desc'  => __( 'Configure the Recurring Settings', 'edd-recurring' ),
			'type'  => 'header'
		),
		array(
			'id'  	=> 'recurring_download_limit',
			'name'  => __( 'Limit File Downloads', 'edd-recurring' ),
			'desc'  => __( 'Check this if you\'d like to require users have an active subscription in order to download files associated with a recurring product.', 'edd-recurring' ),
			'type'  => 'checkbox'
		)
	);

	return array_merge( $settings, $recurring_settings );
}
add_filter( 'edd_settings_general', 'edd_recurring_settings' );