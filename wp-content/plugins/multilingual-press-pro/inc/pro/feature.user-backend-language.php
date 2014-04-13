<?php # -*- coding: utf-8 -*-
add_action( 'inpsyde_mlp_loaded', 'mlp_feature_user_backend_language', 0 );

function mlp_feature_user_backend_language( Inpsyde_Property_List_Interface $data ) {
	new Mlp_User_Backend_Language( $data );
}