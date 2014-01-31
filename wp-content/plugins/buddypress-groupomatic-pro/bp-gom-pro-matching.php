<?php

function bp_gom_pro_matching_glob_to_pcre( $pattern )
{
	$pattern = str_replace( '*', '.*', $pattern );
	$pattern = str_replace( '?', '.{1}', $pattern );
	$pattern = str_replace( '[!', '[^', $pattern );

	return $pattern;
}

function bp_gom_pro_matching_group_lookup_pattern_filter( $pattern, $field_meta )
{
	// handle special operator cases
	if ( $field_meta->operator == 'matches' ) {
		// convert glob
		$pattern = bp_gom_pro_matching_glob_to_pcre( $pattern );
	}

	return $pattern;
}
add_filter( 'bp_gom_matching_group_lookup_pattern', 'bp_gom_pro_matching_group_lookup_pattern_filter', 10, 4 );

function bp_gom_pro_matching_group_lookup_operator_filter( $operator, $field_meta )
{
	// handle special operator cases
	switch ( $field_meta->operator ) {
		case 'matches':
		case 'pcre':
			// matches and pcre are both regular expressions
			return 'REGEXP';
	}

	// return original operator
	return $operator;
}
add_filter( 'bp_gom_matching_group_lookup_operator', 'bp_gom_pro_matching_group_lookup_operator_filter', 10, 4 );

?>
