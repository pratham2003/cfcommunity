<?php

/**
 * 
 *
 * @version $Id$
 * @copyright 2003 
 **/
class wlb_settings {
	var $pre_user_query=false;
	function wlb_settings(){
		global $wlb_plugin;
		$this->id = $wlb_plugin->id.'-opt';
		add_filter("pop-options_{$this->id}",array(&$this,'wlb_options'),10,1);	
		//add_filter('wlb_options_before',array(&$this,'wlb_options'),50,1);
		
		add_filter( 'editable_roles', array(&$this,'editable_roles'), 10, 1 );
		add_filter('admin_footer_text', array(&$this,'admin_footer_text'));
		
		add_action('admin_head', array(&$this,'hide_administrator'));
		if(1==$wlb_plugin->get_option('enable_hide_administrator') ){
			add_action('pre_user_query',array(&$this,'pre_user_query'),10,1);
		}
	}
	
	function pre_user_query($wp_user_query){
		global $wlb_plugin,$wpdb,$wp_roles;
		if( !$wlb_plugin->is_wlb_administrator() ){			
			if( false===strpos($wp_user_query->query_from,'usermeta') ){
				$wp_user_query->query_from.=" INNER JOIN {$wpdb->usermeta} ON ({$wpdb->users}.ID = {$wpdb->usermeta}.user_id)";
			}
			$wp_capabilities = $wpdb->get_blog_prefix( $blog_id ) . 'capabilities';
			$wp_user_query->query_where .= " AND ({$wpdb->usermeta}.meta_key = '$wp_capabilities' AND CAST({$wpdb->usermeta}.meta_value AS CHAR) NOT LIKE '%\\\"".WLB_ADMIN_ROLE."\\\"%') ";			
		}	
	}
	
	function editable_roles($roles){
		if(isset($roles[WLB_ADMIN_ROLE])){
			global $userdata;
			$WP_User = new WP_User($userdata->ID);
			if(!$WP_User->has_cap(WLB_ADMIN_ROLE)){
				unset($roles[WLB_ADMIN_ROLE]);
			}
		}
		return $roles;
	}
	function hide_administrator(){
		global $wlb_plugin;
		if(1==$wlb_plugin->get_option('enable_hide_administrator')){
			global $userdata,$wp_roles;
			if(isset($wp_roles->roles[WLB_ADMIN_ROLE])){
				$WP_User = new WP_User($userdata->ID);
				if(!$WP_User->has_cap(WLB_ADMIN_ROLE)){
					unset( $wp_roles->role_objects[WLB_ADMIN_ROLE] );
					unset( $wp_roles->role_names[WLB_ADMIN_ROLE] );
					unset( $wp_roles->roles[WLB_ADMIN_ROLE] );
					
					$wp_user_search = new WP_User_Search( '', '', WLB_ADMIN_ROLE );  
	         		$user_ids = $wp_user_search->get_results();  
					if(is_array($user_ids)&&count($user_ids)>0){
						$sel=array();
						foreach($user_ids as $user_id){
							$sel[]="#the-list #user-$user_id";
						}
						echo "<style>".implode(',',$sel)."{display:none !important;}</style>";
						echo "<script type='text/javascript' >jQuery(document).ready(function(){jQuery('".implode(',',$sel)."').remove();});</script>";
?>
<script>
jQuery(document).ready(function($){
	var total_users = 0;
	$('.users-php .subsubsub li').each(function(i,el){
		
		val = $(el).find('.count').html();
		val = val.replace(/[(),]/g,'');
		
		if(i==0)return;
		total_users += parseInt(val);
		
	});
	
	$('.users-php .subsubsub li').first().find('.count').html("("+total_users+")");
});
</script>
<?php					
					}				
				}			
			}			
		}
	}	
	function admin_footer_text(){

	}
	function wlb_options($t,$for_admin=true){
		$i = count($t);
	
		//------------------
		$i++;
		@$t[$i]->id 			= 'wlb_advanced_settings'; 
		$t[$i]->label 		= __('Advanced Settings','wlb');//title on tab
		$t[$i]->right_label	= '';//title on tab
		$t[$i]->page_title	= __('Advanced Settings','wlb');//title on content
		$t[$i]->options = array();
		
		$t[$i]->options[]=(object)array(
				'type'=>'subtitle',
				'label'=>__('Role and Capability Manager','wlb')	
			);	
		$t[$i]->options[] =	(object)array(
				'id'		=> 'enable_role_manager',
				'label'		=> __('Enable Role and Capability Manager','wlb'),
				'type'		=> 'yesno',
				'description'=> __('Select yes and save.  A new panel with Role Management options will display in this options screen.','wlb'),
				'el_properties'	=> array(),
				'save_option'=>true,
				'load_option'=>true
				);		
		$t[$i]->options[]=(object)array(
				'type'=>'subtitle',
				'label'=>__('Email branding','wlb')	
			);	
		$t[$i]->options[] =	(object)array(
				'id'		=> 'enable_email_branding',
				'label'		=> __('Enable email branding','wlb'),
				'type'		=> 'yesno',
				'default'	=> 1,
				'description'=> __('Choose no if you are having problems with the emails sent by the system.','wlb'),
				'el_properties'	=> array(),
				'save_option'=>true,
				'load_option'=>true
				);						
		$t[$i]->options[] =	(object)array(
				'id'		=> 'enable_hide_administrator',
				'label'		=> __('Hide the Administrator role from users list.','wlb'),
				'type'		=> 'yesno',
				'description'=> __('This option allows you to hide the Administrator role from the user list.','wlb'),
				'el_properties'	=> array(),
				'save_option'=>true,
				'load_option'=>true
				);		
		
		$t[$i]->options[]=(object)array(
				'type'=>'subtitle',
				'label'=>__('Custom Dashboard','wlb')	
			);	
		$t[$i]->options[] =	(object)array(
				'id'		=> 'enable_wlb_dashboard',
				'label'		=> __('Enable WLB Custom Dashboard Tool','wlb'),
				'type'		=> 'yesno',
				'description'=> __('Select yes and save.  On the admin left menu a new menu option will appear.','wlb'),
				'el_properties'	=> array(),
				'save_option'=>true,
				'load_option'=>true
				);				
		

					
		$t[$i]->options[]=(object)array('type'	=> 'clear');		
		$t[$i]->options[]=(object)array('label'=>__('Save changes','wlb'),'type'=>'submit','class'=>'button-primary', 'value'=> '' );	
		//------------------	
		return $t;
	}
	
	
}

?>