<?php # -*- coding: utf-8 -*-
// add_action( 'inpsyde_mlp_loaded', 'mlp_feature_redirect' );
add_action( 'inpsyde_mlp_loaded', 'mlp_feature_redirect' );
function mlp_feature_redirect( Inpsyde_Property_List_Interface $data ) {
	new Mlp_Redirect( $data );
}