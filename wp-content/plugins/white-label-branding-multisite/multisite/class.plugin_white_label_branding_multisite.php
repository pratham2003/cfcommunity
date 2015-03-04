<?php

/**
 * 
 *
 * @version $Id$
 * @copyright 2003 
 **/
require_once WLB_PATH.'includes/class.plugin_white_label_branding.php';
 
class plugin_white_label_branding_multisite extends plugin_white_label_branding {
	var $multisite_type;
	function plugin_white_label_branding_multisite($args=array()){
		$args['multisite']=true;
		if(is_admin()){
			require_once WLB_PATH.'multisite/class.wlb_multisite.php';
		}
		
		$this->plugin_white_label_branding($args);
		$this->default_site_options['wlb_subsite_caps']=array();
		
		add_action('after_setup_theme',array(&$this,'ms_plugins_loaded'),15);
	}
	
	function get_user_role() {
		global $userdata;
		global $current_user;

		$user_role = '';
		
		$user_roles = $current_user->roles;
		if(is_array($user_roles)&&count($user_roles)>0)
			$user_role = array_shift($user_roles);
		
		if(trim($user_role)=='' && is_super_admin())return WLB_ADMIN_ROLE;
			
		return @$user_role;
	}
	
	function load_options(){
		//---- site options -----
		if(function_exists('get_site_option')){
			$this->site_options = get_site_option( $this->site_options_varname, false );
			$this->site_options = is_array($this->site_options)?$this->site_options:$this->default_site_options;
		}
		//---- blog options -----
		$blog_branding_type = $this->get_site_option('blog_branding_type',0);
		if(1==$blog_branding_type){//Option 2
			$this->multisite_type = 'default';
			//Default branding
			$this->options = get_site_option($this->options_varname);
			$this->options = is_array($this->options)?$this->options:array();							
		}else if(2==$blog_branding_type){//Option 3
			$this->multisite_type = 'custom';
			//Custom Branding
			$this->options = get_option($this->options_varname);
			$this->options = is_array($this->options)?$this->options:array();				
					
		}else if(3==$blog_branding_type){//Option 4
			$this->multisite_type = 'custom';
			//Default branding
			//$this->options = array();
			$this->options = get_site_option($this->options_varname);
			$this->options = is_array($this->options)?$this->options:array();
			$custom = get_option($this->options_varname);
			$custom = is_array($custom)?$custom:array();					
			if(count($this->options)>0){
				foreach($this->options as $field => $value){
					if(isset($custom[$field]) && !empty($custom[$field]) && !$this->considered_empty($field,$custom[$field]) ){
						$this->multisite_type = 'custom';
						$this->options[$field] = $custom[$field];
					}
				}
			}
			
			if(count($custom)>0){
				foreach($custom as $field => $value){
					if(!isset($this->options[$field])){
						$this->options[$field] = $value;
					}
				}
			}
		}else{//Option 1
			$this->multisite_type = 'custom';
			//Custom Branding
			$this->options = get_option($this->options_varname);
			$this->options = is_array($this->options)?$this->options:array();		
			//or Default Branding
			if( empty($this->options) ){
				$this->multisite_type = 'default';
				$this->options = get_site_option($this->options_varname);
				$this->options = is_array($this->options)?$this->options:array();			
			}
		}

		do_action('wlb_options_loaded');
	}
	
	function considered_empty($field,$value){
		if(is_string($value) && trim($value)=='#'){
			return true;
		}
		return false;
	}
	
	function ms_plugins_loaded(){
		if(is_admin()):
			if( $this->multisite && class_exists('wlb_multisite')){
				new wlb_multisite();	
			}
			
			if( $this->is_wlb_network_admin() ){
				//add the multisite panel to network admins only.
				$settings = array(				
					'id'					=> $this->id,
					'plugin_id'				=> $this->id,
					'capability'			=> $this->options_capability,
					'options_varname'		=> $this->options_varname,
					'menu_id'				=> $this->id,
					'page_title'			=> __('White Label Branding Options','wlb'),
					'menu_text'				=> __('White Label Branding','wlb'),
					'option_menu_parent'	=> $this->id,
					'notification'			=> (object)array(
						'plugin_version'=> WLB_VERSION,
						'plugin_code' 	=> WLB_PLUGIN_CODE,
						'message'		=> __('White Label Branding update %s is available!','wlb').' <a href="%s">'.__('Please update now','wlb').'</a>'
					),
					'theme'					=> false,
					'extracss'				=> 'extracss-'.$this->id,
					'rangeinput'			=> true,
					'fileuploader'			=> true,
					'dc_options'			=> @$dc_options,
					'pluginurl'				=> WLB_URL,
					'tdom'					=> 'wlb',
					'path'					=> WLB_PATH.'options-panel/',
					'url'					=> WLB_URL.'options-panel/',
					'pluginslug'			=> WLB_SLUG,
					//'api_url' 			=> "http://localhost",
					'api_url' 				=> "http://plugins.righthere.com",
					'autoupdate'			=> false
				);	
				
				$settings['id'] 		= $this->id.'-ms';
				$settings['menu_id'] 	= $this->get_pop_menu_id('-ms','wlb_options');//$this->id.'-opt';
				$settings['menu_text'] 	= __('Multisite','wlb');
				$settings['import_export'] = false;
				$settings['import_export_options'] = false;
				$settings['capability'] = 'wlb_options';
				//$settings['bundles'] = true; Not really needed. TODO for next release.
				new PluginOptionsPanelModule($settings);	
			}
		
		endif;	
	}	
}
?>