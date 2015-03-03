<?php
add_filter( 'cp_admin_pointers-cp_recipe', 'cp_admin_recipe_pointers' );
function cp_admin_recipe_pointers( $p ) {
	$p['ingredients'] = array(
		'target' => '#ingredients-helper',
		'options' => array(
			'content' => sprintf( '<h3> %s </h3> <p> %s </p>',
				__( 'HELP' ,'cooked'),
				wpautop('--Section Title
1 cup &nbsp;of some ingredient
2 tablespoons &nbsp;of another ingredient
--Another Section Title
1/2 cup &nbsp;of one more ingredient')
			),
			'position' => array( 'edge' => 'left', 'align' => 'middle' )
		)
	);
	$p['directions'] = array(
		'target' => '#directions-helper',
		'options' => array(
			'content' => sprintf( '<h3> %s </h3> <p> %s </p>',
				__( 'HELP' ,'cooked'),
				wpautop('--Section Title
Step one goes here
Step two has a [timer length=10]timer[/timer]
--Another Section Title
This section has only one step')
			),
			'position' => array( 'edge' => 'left', 'align' => 'middle' )
		)
	);
	return $p;
}