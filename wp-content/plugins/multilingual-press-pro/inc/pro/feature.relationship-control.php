<?php # -*- coding: utf-8 -*-

is_admin() && add_action( 'mlp_and_wp_loaded', 'mlp_feature_relationship_control' );

/**
 * Init the relinking feature.
 *
 * @param Inpsyde_Property_List_Interface $data
 * @return void
 */
function mlp_feature_relationship_control( Inpsyde_Property_List_Interface $data ) {
	new Mlp_Relationship_Control( $data );
}