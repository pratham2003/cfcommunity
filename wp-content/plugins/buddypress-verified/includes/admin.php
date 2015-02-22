<?php


if( !function_exists('load_sortable_user_meta_columns') ){
	
	/**
	 * load_sortable_user_meta_columns function.
	 * 
	 * @access public
	 * @return void
	 */
	function load_buddyverified_user_columns() {
		$args = array('bp-profile-verified'=>'Verified');
		new buddyverified_user_columns( $args );
	}
	add_action('admin_init', 'load_buddyverified_user_columns');
}


if( !class_exists('buddyverified_user_columns') ) {

	/**
	 * sortable_user_meta_columns class.
	 */
	class buddyverified_user_columns {
	
		var $defaults = array(
			'nicename', 
			'email', 'url', 
			'registered',
			'user_nicename', 
			'user_email', 
			'user_url', 
			'user_registered',
			'display_name',
			'name',
			'post_count',
			'ID',
			'id',
			'user_login'
		);
		
		/**
		 * __construct function.
		 * 
		 * @access public
		 * @param mixed $args
		 * @return void
		 */
		function __construct( $args ){
			$this->args = $args;
			add_action( 'pre_user_query', array( &$this, 'query' ) );
			add_action( 'manage_users_custom_column',  array( &$this, 'content' ), 10, 3 );
			add_filter( 'manage_users_columns', array(&$this, 'columns'));
			add_filter( 'manage_users_sortable_columns', array( &$this, 'sortable' ) );
		}
		
		/**
		 * query function.
		 * 
		 * @access public
		 * @param mixed $query
		 * @return void
		 */
		function query( $query ){
			$vars = $query->query_vars;
			
			if( in_array( $vars['orderby'], $this->defaults ) ) return;
			
			if( !empty( $this->args[$vars['orderby']] ) ) {
				$title = $this->args[$vars['orderby']];
			}
			
			if( !empty( $title ) ){
				   $query->query_from .= " LEFT JOIN wp_usermeta m ON ( wp_users.ID = m.user_id AND m.meta_key = '$vars[orderby]' )";
				   	 
				   $query->query_orderby = "ORDER BY m.meta_value ". $vars['order'];
				   
			}
		}
		
		/**
		 * columns function.
		 * 
		 * @access public
		 * @param mixed $columns
		 * @return void
		 */
		function columns( $columns ) {
			foreach( $this->args as $key=>$value ){
				$columns[$key] = $value;
			}
			return $columns;
		}
		
		/**
		 * sortable function.
		 * 
		 * @access public
		 * @param mixed $columns
		 * @return void
		 */
		function sortable( $columns ){
			foreach( $this->args as $key=>$value ){
				$columns[$key] = $key;
			}
			return $columns;
		}
		
		/**
		 * content function.
		 * 
		 * @access public
		 * @param mixed $value
		 * @param mixed $column_name
		 * @param mixed $user_id
		 * @return void
		 */
		function content( $value, $column_name, $user_id ) {
			$user = get_userdata( $user_id );
			$meta = get_user_meta( $user_id, 'bp-verified', true) ? get_user_meta( $user_id, 'bp-verified', true) : null ;
			
			$value = $user->$column_name;
			
			$values = '';
			
			if( $value) {
				if( $value ) {
				
				$values = '<div style="width: 20px; height: 20px; margin: 0 auto;"><img src="' . VERIFIED_URL . '/images/'.$meta['image'].'.png"></div>' ;
				}
					
				return $values;
			}
		}
	}
}



/**
 * buddyverified_meta_box function.
 * 
 * @access public
 * @return void
 */
function buddyverified_meta_box() {
	       
	add_meta_box(
	    'buddyverified_id',
	     __( 'Verifiy User', 'buddyverified' ),
	    'buddyverified_inner_meta_box',
	    get_current_screen()->id
	);
}
add_action( 'bp_members_admin_user_metaboxes', 'buddyverified_meta_box' );


/**
 * buddyverified_inner_meta_box function.
 * 
 * @access public
 * @return void
 */
function buddyverified_inner_meta_box() {

	$user_id = isset($_GET['user_id']) ? $_GET['user_id'] : 0;
	$meta = get_user_meta( $user_id, 'bp-verified', true) ? get_user_meta( $user_id, 'bp-verified', true) : null ;
	$verified = get_user_meta( $user_id, 'bp-profile-verified', true) ? get_user_meta( $user_id, 'bp-profile-verified', true) : null ;
		
	?>
	<table cellspacing="3px" style="border-collapse: collapse;">
		<thead>
			<tr>
				<th></th>
			</tr>
		</thead>
		
		<tbody>
			<tr>
				<td style="vertical-align:middle">Verify User:</td>
				<td><input type="checkbox" name="verified" value="1" <?php if ($verified) echo 'checked="checked"'; ?> /></td>
			</tr>
			<tr class="alt">
				<td style="vertical-align:middle">Badge:</td>
				<td style="vertical-align:middle">Choose image to display</td>
				<td><img src="<?php echo VERIFIED_URL; ?>/images/1.png"></td>
				<td><img src="<?php echo VERIFIED_URL; ?>/images/2.png"></td>
				<td><img src="<?php echo VERIFIED_URL; ?>/images/3.png"></td>
				<td><img src="<?php echo VERIFIED_URL; ?>/images/4.png"></td>
				<td><img src="<?php echo VERIFIED_URL; ?>/images/5.png"></td>
				<td><img src="<?php echo VERIFIED_URL; ?>/images/6.png"></td>
			</tr>
			<tr class="alt">
				<td></td>
				<td></td>
				<td><input type="radio" name="verified_image" value="1" <?php if ($meta['image'] == '1') echo 'checked="checked"' ?> /></td>
				<td><input type="radio" name="verified_image" value="2"  <?php if ($meta['image'] == '2') echo 'checked="checked"' ?>/></td>
				<td><input type="radio" name="verified_image" value="3" <?php if ($meta['image'] == '3') echo 'checked="checked"' ?> /></td>
				<td><input type="radio" name="verified_image" value="4" <?php if ($meta['image'] == '4') echo 'checked="checked"' ?> /></td>
				<td><input type="radio" name="verified_image" value="5" <?php if ($meta['image'] == '5') echo 'checked="checked"' ?> /></td>
				<td><input type="radio" name="verified_image" value="6" <?php if ($meta['image'] == '6') echo 'checked="checked"' ?> /></td>
			</tr>
			<tr>
				<td style="vertical-align:middle">Badge Text:</td>
				<td><input type="text" id="verified_text" name="verified_text" placeholder="Verified User" value="<?php echo $meta['text'] ?>" size="25" /></td>
			</tr>
			<tr class="alt">
				<td style="vertical-align:middle">Activity Badge:</td>
				<td>
					<input type="radio" name="verified_activity" value="yes" <?php if ( $meta['activity'] == 'yes' ) echo 'checked="checked"' ?> />Yes
					<input type="radio" name="verified_activity" value="no"  <?php if ( $meta['activity'] == 'no' ) echo 'checked="checked"' ?>/>No
					<p>Adds badge to activity stream avatar</p>
				</td>
				<td></td>
				<td></td>
				<td></td>
				<td></td>
				<td></td>
				<td></td>
			</tr>
			<tr>
				<td style="vertical-align:middle">Profile Badge:</td>
				<td>
					<input type="radio" name="verified_profile" value="yes" <?php if ( $meta['profile'] == 'yes') echo 'checked="checked"' ?> />Yes
					<input type="radio" name="verified_profile" value="no"  <?php if ($meta['profile'] == 'no') echo 'checked="checked"' ?>/>No
					<p>Adds badge to profile avatar</p>
				</td>
			</tr>
		</tbody>
	</table>	
	
	<?php

}


/**
 * buddyverified_save_metabox function.
 * 
 * @access public
 * @return void
 */
function buddyverified_save_metabox() {
		
		if( isset( $_POST['save'] ) ) {
			$user_id = isset($_GET['user_id']) ? $_GET['user_id'] : 0;
			$text = isset($_POST['verified_text']) ? $_POST['verified_text'] : '';
			$profile = isset($_POST['verified_profile']) ? $_POST['verified_profile'] : '';
			$activity = isset($_POST['verified_activity']) ? $_POST['verified_activity'] : '';
			$image = isset($_POST['verified_image']) ? $_POST['verified_image'] : '';
			$verify = isset($_POST['verified']) ? $_POST['verified'] : '';
			
			$bp_verified_arr = array(
			'profile' => $profile, 
			'activity' => $activity,
			'text' => $text,
			'image' => $image
			);
			
			update_user_meta( $user_id, 'bp-verified', $bp_verified_arr);
			update_user_meta( $user_id, 'bp-profile-verified', $verify);
		}
}
add_action( 'bp_members_admin_update_user', 'buddyverified_save_metabox' );


function buddyverified_admin_css() {
	?>
	<style>
		th#bp-profile-verified.manage-column.column-bp-profile-verified {
			text-align:center;
			width: 10%;
		}
		#buddyverified_id table td {
			padding: 5px;
		}
	</style>
	<?php
}
add_action('admin_head', 'buddyverified_admin_css');