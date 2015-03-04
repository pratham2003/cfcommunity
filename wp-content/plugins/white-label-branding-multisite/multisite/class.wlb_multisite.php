<?php

/**
 * 
 *
 * @version $Id$
 * @copyright 2003 
 **/
 
class wlb_multisite {
	var $wlb_caps;
	var $save_default_branding_label;
	var $remove_default_branding_label;
	var $remove_custom_branding_label;
	function wlb_multisite(){
		global $wlb_plugin;
		$this->wlb_caps = array(
			'wlb_branding'		=> __('WLB Branding','wlb'),
			'wlb_navigation'	=> __('WLB Navigation','wlb') ,
			'wlb_login'			=> __('WLB Login','wlb'),
			'wlb_color_scheme'	=> __('WLB Admin Color Scheme','wlb'),
			'wlb_options'		=> __('WLB Options','wlb'),
			'wlb_role_manager'	=> __('WLB Role Manager','wlb'),
			//'wlb_license',
			'wlb_downloads'		=> 'WLB Downloads',
			'wlb_dashboard_tool'=> 'WLB Dashboard Tool'
		);
		
		$this->id = $wlb_plugin->id.'-ms';
		$this->save_default_branding_label = __('Save as global default branding','wlb');
		$this->remove_default_branding_label = __('Remove default branding','wlb');
		$this->remove_custom_branding_label = __('Remove custom branding','wlb');
		$this->copy_global_branding_label = __('Import global branding settings','wlb');

		if( $wlb_plugin->is_wlb_network_admin() ){
			add_filter("pop-options_{$this->id}",array(&$this,'wlb_options'),10,1);
			add_action('pop_handle_save',array(&$this,'pop_handle_save'),100,1);
			foreach(array(
				$wlb_plugin->id.'-ms',
				$wlb_plugin->id.'-bra',
				$wlb_plugin->id.'-nav',
				$wlb_plugin->id.'-log',
				$wlb_plugin->id.'-css',
				$wlb_plugin->id.'-opt',
				$wlb_plugin->id.'-cap'
			) as $id){
				add_action("pop_main_controls_{$id}",array(&$this,'save_default_in_main_controls'));
			}	
		}else{
			if( is_admin() && current_user_can(WLB_SUBSITE_ADMINISTRATOR) ){
				$this->set_subsite_admin_caps();//running in plugins_loaded
			}
		}
	}

	function set_subsite_admin_caps(){
		global $wlb_plugin;
		//shoulld only run if user is not network administrator, is multisite.
		if('1'==$wlb_plugin->get_site_option('allow_blog_branding')){
			foreach($this->wlb_caps as $cap => $label){
				if(current_user_can($cap))return;
			}			

			//-- user is administrator, accessing wp-admin, branding is allowed, its multisite, and no wlb cap is set: set all.
			$wlb_subsite_caps = $wlb_plugin->get_site_option('wlb_subsite_caps',array());
			if(is_array($wlb_subsite_caps)&&count($wlb_subsite_caps)>0){
				$WP_Roles = new WP_Roles();	
				//foreach($this->wlb_caps as $cap => $label){
				foreach($wlb_subsite_caps as $cap){
					$WP_Roles->add_cap( WLB_SUBSITE_ADMINISTRATOR, $cap );
				}			
			}
		}else{
			//blog branding is disallowed, remove capabilities if have
			foreach($this->wlb_caps as $cap => $label){
				if(current_user_can($cap)){
					//have wlb caps, remove.
					$WP_Roles = new WP_Roles();
					foreach($this->wlb_caps as $cap => $label){
						$WP_Roles->remove_cap( WLB_ADMIN_ROLE, $cap );
					}					
					return;
				}
			}				
		}
	}
	
	function wlb_options($t,$for_admin=true){
		$i = count($t);
		//-----
 		$i++;
		$t[$i] = (object)array();
 		$t[$i]->id 			= 'multisite'; 
 		$t[$i]->label 		= __('Multisite Settings','wlb');//title on tab
 		$t[$i]->right_label	= __('Super Admin only','wlb');//title on tab
 		$t[$i]->page_title	= __('Multisite Settings','wlb');//title on content		
			
		$t[$i]->options[] =	(object)array(
				'type'=>'callback',
				'callback'=>array(&$this,'multisite_options')
			);	
		 			
		$t[$i]->options[]=(object)array('label'=>__('Save Changes','wlb'),'type'=>'submit','class'=>'button-primary', 'value'=> '' );	
		//-----
 		$i++;
		$t[$i] = (object)array();
 		$t[$i]->id 			= 'wlb-default-branding'; 
 		$t[$i]->label 		= __('Default and Custom branding','wlb');
 		$t[$i]->right_label	= __('Super Admin only','wlb');
 		$t[$i]->page_title	= __('Default branding','wlb');		
		$t[$i]->options = array(
			(object)array(
				'type'			=> 'description',
				'description'	=>  __('Click this button to save the current site settings as the default wlb settings.','wlb')
			),
			(object)array(
				'id'		=> 'default_branding',
				'label'		=> $this->save_default_branding_label,
				'class'		=> 'button-secondary',
				'type'		=> 'callback',
				'callback'	=> array(&$this,'button')
			),
			(object)array(
				'type'			=> 'description',
				'description'	=>  __('Click this button to DELETE the <strong>default</strong> branding settings.','wlb')
			),
			(object)array(
				'id'		=> 'remove_default_branding_settings',
				'label'		=> $this->remove_default_branding_label,
				'class'		=> 'button-secondary',
				'type'		=> 'callback',
				'callback'	=> array(&$this,'button'),
				'extra'		=> sprintf('OnClick="javascript:return confirm(\'%s\');"',  str_replace("'",'',__('Please confirm that you want to remove the default branding options, observe that this applies to all subsites using the default branding.','wlb')))
			),
			(object)array(
				'type'			=> 'description',
				'description'	=>  __('Click this button to DELETE the <strong>current</strong> site branding settings.','wlb')
			),
			(object)array(
				'id'		=> 'remove_custom_branding_settings',
				'label'		=> $this->remove_custom_branding_label,
				'class'		=> 'button-secondary',
				'type'		=> 'callback',
				'callback'	=> array(&$this,'button')
			),
			(object)array(
				'type'			=> 'description',
				'description'	=>  __('Click this button to copy the Global Default Branding into this subsite local branding.  This overwrites the subsite local branding.','wlb')
			),
			(object)array(
				'id'		=> 'copy_global_branding',
				'label'		=> $this->copy_global_branding_label,
				'class'		=> 'button-secondary',
				'type'		=> 'callback',
				'callback'	=> array(&$this,'button'),
				'extra'		=> sprintf('OnClick="javascript:return confirm(\'%s\');"',  str_replace("'",'',__('This action will overwrite the local settings with the global default settings.','wlb')))
			),			
			(object)array(
				'type'			=> 'clear',
			)			
		);
		//-----
 		$i++;
		$t[$i] = (object)array();
 		$t[$i]->id 			= 'wlb-subsite-caps'; 
 		$t[$i]->label 		= __('Default WLB Subsite capabilities','wlb');
 		$t[$i]->right_label	= __('Set default WLB caps for subsite admins','wlb');
 		$t[$i]->page_title	= __('WLB Subsite capabilities','wlb');		
		$t[$i]->options = array(
			(object)array(
				'type'			=> 'description',
				'description'	=> sprintf(
					'<p>%s</p>' .
					'<p>%s</p>' .
					'<p>%s</p>' .
					'<p>%s</p>' ,
					__('If blog branding is allowed, you can specify what WLB capabilities subsite administrators will get by default.  License is only shown to network administrators.  ','wlb'),
					__('Observe that if you browse the subsite administrator role in a role manager tool, this change is not reflected until an actual subsite administrator logs in.','wlb'),
					__('Please note that changes to this settings only apply to new administrators.  Existing administrators wlb capabilities are not modified to prevent overwritting per site customization of capabilities.','wlb'),
					__('Please observe that if the role manager tool or the dashboard tool capabilities are enabled, the subsite administrator still needs to go to wlb options to enable the dashboard tool and/or role manager tool.','wlb')
				)
			)					
		);			
		
		global $wlb_plugin;
		$site_options = get_site_option($wlb_plugin->site_options_varname);
		$wlb_subsite_caps = empty($site_options)||!isset($site_options['wlb_subsite_caps'])?array_keys($this->wlb_caps):$site_options['wlb_subsite_caps'];
		foreach($this->wlb_caps as $cap => $label){
			$t[$i]->options[] = (object)array(
				'id'	=> sprintf('sub_%s',$cap),
				'name'	=> 'wlb_subsite_caps[]',
				'type'	=> 'checkbox',
				'label'	=> $label,
				'value'	=> '',//this is not the checkbox value but the checked value.
				'el_properties'=> in_array($cap,$wlb_subsite_caps)?array('checked'=>'checked'):array(),
				'option_value'=> $cap,
				'save_option'=>false,
				'load_option'=>false
			);
		}	
		$t[$i]->options[]=(object)array('type'=>'clear');
		$t[$i]->options[]=(object)array('label'=>__('Save changes','wlb'),'type'=>'submit','class'=>'button-primary', 'value'=>'' );
		$t[$i]->options[]=(object)array('type'=>'clear');
		//-----
		return $t;
	}
	
	function get_current_multisite_settings(){
		
	}
	
	function save_default_in_main_controls(){
		global $wlb_plugin;
		
		echo "<div class=\"wlb-network-admin-pop-controls\">";
		echo "<p>";
		if($wlb_plugin->multisite_type=='custom'){
			_e('This site is using local custom branding.');
		}else if($wlb_plugin->multisite_type=='default'){
			_e('This site is using the global default branding.');
		}
		echo "</p>";
		echo $this->button(null,null,(object)array(
			'id'	=> 'default_branding',
			'label'	=> $this->save_default_branding_label,
			'class' => 'button-primary',
			'extra'	=> ''
		));
		echo sprintf("&nbsp;<div class=\"btn-description\">%s</div>", __('This applies to all subsites that are using the default branding','wlb') ); 
		echo "</div>";		
	}
	
	function button($tab,$i,$o){
		return sprintf("<input type=\"submit\" name=\"%s\" value=\"%s\" class=\"%s\" %s/>",$o->id, $o->label, $o->class, @$o->extra);
	}
		
	function pop_handle_save($pop){
		global $wlb_plugin;
		if(!property_exists($wlb_plugin,'site_options_varname'))return;
		//------------------------------
		if (wp_verify_nonce($_REQUEST['wlb-multisite-nonce'], 'wlb-multisite-nonce') ){
			$site_options = array();
			foreach($wlb_plugin->default_site_options as $var => $default){
				$site_options[$var] = isset($_REQUEST[$var])?$_REQUEST[$var]:$default;
			}
			update_site_option($wlb_plugin->site_options_varname,$site_options);		
		}
		//------------------------------
		
		if( $wlb_plugin->is_wlb_network_admin() && isset($_POST['copy_global_branding'])&&$this->copy_global_branding_label==$_POST['copy_global_branding']){
			$current_blog_options = get_site_option($wlb_plugin->options_varname);
			update_option($wlb_plugin->options_varname,$current_blog_options);
		}		
		if( $wlb_plugin->is_wlb_network_admin() && isset($_POST['default_branding'])&&$this->save_default_branding_label==$_POST['default_branding']){
			$current_blog_options = get_option($wlb_plugin->options_varname);
			$current_blog_options = is_array($current_blog_options)?$current_blog_options:array();
			delete_site_option($wlb_plugin->options_varname);
			update_site_option($wlb_plugin->options_varname,$current_blog_options);
		}
		if($wlb_plugin->is_wlb_network_admin() && isset($_POST['remove_default_branding_settings'])&&$this->remove_default_branding_label==$_POST['remove_default_branding_settings']){
			update_site_option($wlb_plugin->options_varname,array());
			$goback = add_query_arg( 'updated', 'true', wp_get_referer() );
			$goback = add_query_arg( 'pop_open_tabs', (isset($_REQUEST['pop_open_tabs'])?$_REQUEST['pop_open_tabs']:''), $goback );
			wp_redirect( $goback );
			die();			
		}
		//------------------------------		
		if($wlb_plugin->is_wlb_network_admin() && isset($_POST['remove_custom_branding_settings'])&&$this->remove_custom_branding_label==$_POST['remove_custom_branding_settings']){
			update_option($wlb_plugin->options_varname,array());
			$goback = add_query_arg( 'updated', 'true', wp_get_referer() );
			$goback = add_query_arg( 'pop_open_tabs', (isset($_REQUEST['pop_open_tabs'])?$_REQUEST['pop_open_tabs']:''), $goback );
			wp_redirect( $goback );
			die();
		}
		//------------------------------		
	}
	
	function multisite_options(){
		global $wlb_plugin;		
		ob_start();
		foreach($wlb_plugin->default_site_options as $var => $default){
			$$var = $wlb_plugin->get_site_option($var);
		}
?>
<input type="hidden" name="wlb-multisite-nonce" value="<?php echo  wp_create_nonce('wlb-multisite-nonce');?>" />

<div class="description-holder">
	<div class="description"><?php _e('Specify how branding on blogs should work.<br /><b>Option 1</b>: Will use blog Branding Settings, if there are non will use Default Settings.<br /><b>Option 2</b>: All sites will use the default branding.<br /><b>Option 3</b>: Will use blog Branding Settings, if there are non will use no settings.','wlb')?><?php _e('<br /><b>Option 4</b>: Will use blog local custom branding settings, empty fields will be filled with the global default branding settings.','wlb')?></div>
	<div class="description-bg"><?php _e('Specify how branding on blogs should work.<br /><b>Option 1</b>: Will use blog Branding Settings, if there are non will use Default Settings.<br /><b>Option 2</b>: All sites will use the default branding.<br /><b>Option 3</b>: Will use blog Branding Settings, if there are non will use no settings.','wlb')?><?php _e('<br /><b>Option 4</b>: Will use blog local custom branding settings, empty fields will be filled with the global default branding settings.','wlb')?></div>
</div>

<div class="pt-option">
	<span class="pt-label"><?php _e('Blog Branding Type','wlb')?></span>
	<br />
	<input type="radio" value="0" <?php echo $blog_branding_type=='0'||$blog_branding_type==''?'checked="checked"':''?> name="blog_branding_type" id="blog_branding_type_0" class="blog_branding_type">&nbsp;
	<label><?php _e('Option 1: Custom Branding (or Default)','wlb') ?></label>&nbsp;&nbsp;
	<br />
	<input type="radio" value="1" <?php echo $blog_branding_type=='1'?'checked="checked"':''?> name="blog_branding_type" id="blog_branding_type_1" class="blog_branding_type">&nbsp;
	<label><?php _e('Option 2: All sub-sites use the Default Branding','wlb')?></label>&nbsp;&nbsp;
	<br />
	<input type="radio" value="2" <?php echo $blog_branding_type=='2'?'checked="checked"':''?> name="blog_branding_type" id="blog_branding_type_2" class="blog_branding_type">&nbsp;
	<label><?php _e('Option 3: Custom Branding (no branding)','wlb')?></label>	
	<br />
	<input type="radio" value="3" <?php echo $blog_branding_type=='3'?'checked="checked"':''?> name="blog_branding_type" id="blog_branding_type_3" class="blog_branding_type">&nbsp;
	<label><?php _e('Option 4: Custom Branding (or Default fallback)','wlb')?></label>	
	<br />

</div>
<div class="pt-clear"></div>

<div class="description-holder">
	<div class="description"><?php _e('Allow blog Administrators to manage their own WLB settings.','wlb')?></div>
	<div class="description-bg"><?php _e('Allow blog Administrators to manage their own WLB settings.','wlb')?></div>
</div>
<div class="pt-option pt-option-yesno ">
	<span class="pt-label pt-type-yesno"><?php _e('Allow blog branding','wlb')?></span>
	<input type="radio" value="1" <?php echo $allow_blog_branding=='1'||$allow_blog_branding==''?'checked="checked"':''?> name="allow_blog_branding" id="allow_blog_branding_0">&nbsp;<label><?php _e('Yes','wlb')?></label>&nbsp;&nbsp;
	<input type="radio" value="0" <?php echo $allow_blog_branding=='0'?'checked="checked"':''?> name="allow_blog_branding" id="allow_blog_branding_1">&nbsp;<label><?php _e('No','wlb')?></label>&nbsp;&nbsp;</div>
<div class="pt-clear"></div>

<?php
		$output=ob_get_contents();
		ob_end_clean();
		return $output;	
	}
	
	function init_network_option_panels(){
	
	}
}

?>