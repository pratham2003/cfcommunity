<?php # -*- coding: utf-8 -*-
//add_action( 'mlp_and_wp_loaded', 'mlp_feature_quicklink' );
add_action( 'inpsyde_mlp_loaded', 'mlp_feature_quicklink' );
function mlp_feature_quicklink( Inpsyde_Property_List_Interface $data ) {
	new Mlp_Quicklink( $data );
}