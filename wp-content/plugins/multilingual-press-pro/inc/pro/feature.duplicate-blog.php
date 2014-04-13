<?php # -*- coding: utf-8 -*-
add_action( 'inpsyde_mlp_loaded', 'mlp_feature_duplicate_blog' );
// feature.cpt_translator
function mlp_feature_duplicate_blog( Inpsyde_Property_List_Interface $data ) {
	new Mlp_Duplicate_Blogs( $data );
}