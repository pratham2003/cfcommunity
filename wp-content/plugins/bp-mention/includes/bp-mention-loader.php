<?php

if ( !defined( 'ABSPATH' ) ) exit;

if ( file_exists( dirname( __FILE__ ) . '/includes/languages/' . get_locale() . '.mo' ) )
	load_plugin_textdomain( 'bp-mention', dirname( __FILE__ ) . '/includes/languages/' . get_locale() . '.mo' );


add_action('wp_ajax_wdw_mentions_autoload', 'wdw_mentions_load_frnds');
function wdw_mentions_load_frnds() {
	global $bp;
	$friend = array(
		'name'		=> '',/*'@' handle*/
		'id'		=> 0, /*user id*/
		'fullname'	=> ''
	);
	$friends = array();
	$ret_arr = array(
		'status'		=> 1,/* [0-success | 1-error | 2-no rows | 3-not logged in ]*/
		'message'		=> '',/*succes or error message*/
		'count'			=> 0,/*count of friends found*/
		'friends'		=> array()/*an array of type $friend*/
	);
	if ( empty( $_POST['limit'] ) || empty( $_POST['search'] ) ){
		$ret_arr['status'] = 2;
		$ret_arr['message'] = 'no friends found';
		echo json_encode($ret_arr);
		exit;
	}

	// Sanitise input
	$search_query = implode( '', (array) preg_replace( array( '|^https?://|i', '|\*|', '|@|' ), '', $_POST['search'] ) );
	if ( empty( $search_query ) ){
		$ret_arr['status'] = 2;
		$ret_arr['message'] = 'no friends found';
		echo json_encode($ret_arr);
		exit;
	}

	$args = array(
		'max'				=> (int) $_POST['limit'],
		'search_terms'      => "{$search_query}"
	);
	
	if ( !empty( $bp->loggedin_user->id ) ){
		$args['exclude'] = $bp->loggedin_user->id;
		$args['user_id'] = $bp->loggedin_user->id;
	}
	
	/*enough setup - now time to poke db*/
	if ( bp_has_members( $args ) ) :
		$count = 0;
		while ( bp_members() ) : bp_the_member();
			$friend['id'] = bp_get_member_user_id();
			$friend['name'] = bp_get_member_user_login();
			$friend['fullname'] = bp_get_member_name();
			/*$location= xprofile_get_field_data( "Location" ,bp_get_member_user_id());//fetch the text for location*/
			$friends[] = $friend;
			
			$count++;
		endwhile;
		$ret_arr['status'] = 0;
		$ret_arr['message'] = 'friends found';
		$ret_arr['friends'] = $friends;
		$ret_arr['count'] = $count;
	else:
		$ret_arr['status'] = 2;
		$ret_arr['message'] = 'no friends found';		
	endif;
	echo json_encode($ret_arr);
	exit;
}

add_action('wp_footer', 'initialise_mention');
function initialise_mention(){
	global $bp;

	if($bp->current_component == "activity" && ($bp->current_action == "just-me" || $bp->current_action == "")){
		/*add_action( 'bp_actions', 'messages_add_autocomplete_js2' );*/
		wp_enqueue_script( 'jquery-textchange',    WP_PLUGIN_URL . '/bp-mention/includes/jquery.textchange.min.js',   array( 'jquery' ), bp_get_version() );
		wp_enqueue_script( 'bp-jquery-mention',    WP_PLUGIN_URL . '/bp-mention/includes/jquery.mention.js',   array( 'jquery' ), bp_get_version() );
		wp_enqueue_style('bp-mention-css', WP_PLUGIN_URL . '/bp-mention/includes/jquery.mention.css');
		?>
		<script type="text/javascript">
			jQuery("document").ready(function (){
				/*jQuery("#whats-new-textarea #whats-new").addClass('send-to-input');
				var acfb = jQuery("#whats-new-textarea").autoCompletefb({urlLookup: ajaxurl});*/
			});
			jQuery(document).ready(function() {
				setuppreview(jQuery("textarea#whats-new"));
				jQuery("textarea.ac-input").each(function(){
					setuppreview(jQuery(this));
				});
			});
		</script>
	<?php
	}
}
?>